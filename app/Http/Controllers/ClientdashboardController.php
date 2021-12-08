<?php

namespace App\Http\Controllers;
use App\User,App\EmailTemplate,App\Countries;
use Illuminate\Http\Request;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\ContractUserCase,App\CaseMaster;
use App\DeactivatedUser,App\ClientGroup,App\UsersAdditionalInfo,App\CaseClientSelection,App\CaseStaff,App\TempUserSelection;
use App\Firm,App\ClientActivity,App\ClientNotes;
use Illuminate\Support\Facades\Crypt;
use App\Task,App\CaseTaskLinkedStaff,App\TaskChecklist;
use App\TaskReminder,App\TaskActivity,App\TaskTimeEntry,App\TaskComment;
use App\TaskHistory,App\LeadAdditionalInfo;
use App\TrustHistory,App\RequestedFund,App\Messages,App\ReplyMessages;
use mikehaertl\wkhtmlto\Pdf;
use ZipArchive,File;
use App\ClientCompanyImport,App\ClientCompanyImportHistory;
use App\DepositIntoCreditHistory;
use App\InvoiceHistory;
use App\InvoicePayment;
use App\Invoices;
use App\Traits\CreditAccountTrait;
use Illuminate\Support\Str;
// use Datatables;
use Yajra\Datatables\Datatables;
use App\CasePracticeArea,App\CaseStage,App\ClientCasesImportHistory,App\CaseNotes,App\CaseStageUpdate,App\ExpenseEntry,App\CaseEvent,App\ClientFullBackup,App\AccountActivity;
use App\Traits\FundRequestTrait;
use App\Traits\TrustAccountTrait;
use Exception;
use App\Http\Controllers\CommonController;

class ClientdashboardController extends BaseController
{
    use CreditAccountTrait, FundRequestTrait, TrustAccountTrait;
    public function __construct()
    {
        // $this->middleware("auth");
    }
    public function clientDashboardView(Request $request,$id)
    {
        Session::forget('caseLinkToClient');
        Session::forget('clientId');
        $contractUserID=$client_id=$id;
        $userProfile = User::select("users.*","countries.name as countryname")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$contractUserID)->where("users.firm_name",Auth::User()->firm_name)->with('clientCases')->first();
        if(empty($userProfile)){
            return view('pages.404');
        }else{
            
            $userProfileCreatedBy='';
            if(!empty($userProfile)){
                //if parent user then data load using user id itself other wise load using parent user
                if($userProfile->parent_user==0){
                    $userProfileCreatedBy = User::select('users.id as pid' ,'users.user_title as ptitle',DB::raw('CONCAT_WS(" ",first_name,last_name) as name'))->where("users.id",$contractUserID)->get();
                }else{
                    $userProfileCreatedBy = User::select('users.id as pid' ,'users.user_title as ptitle',DB::raw('CONCAT_WS(" ",first_name,last_name) as name'))->where("users.id",$userProfile->parent_user)->get();             
                }
            }
            $UsersAdditionalInfo = UsersAdditionalInfo::select("*","client_group.group_name")->leftJoin('client_group','users_additional_info.contact_group_id',"=","client_group.id")->where("user_id",$contractUserID)->first();
            $companyList = User::select("users.first_name","users.id")->whereIn("users.id",explode(",",$UsersAdditionalInfo['multiple_compnay_id']))->get();

            $totalData=0;
            
            if(\Route::current()->getName()=="contacts_clients_billing_trust_request_fund"){
                $allLeads = RequestedFund::leftJoin('users','requested_fund.client_id','=','users.id');
                $allLeads = $allLeads->leftJoin('users as u1','requested_fund.client_id','=','u1.id');
                $allLeads = $allLeads->leftJoin('users_additional_info as u2','requested_fund.client_id','=','u2.id');
                $allLeads = $allLeads->select("users.user_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as client_name'),DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as note_created_by'),"u1.user_title as created_by_user_title","requested_fund.*","requested_fund.client_id as client_id","u2.minimum_trust_balance","u2.trust_account_balance");        
                $allLeads = $allLeads->where("requested_fund.client_id",$contractUserID);   
                $totalData=$allLeads->count();
            }
           

            
            /* $case =  CaseMaster::join('case_client_selection','case_master.id','=','case_client_selection.case_id')
            ->select("case_master.case_title","case_master.id as cid","case_master.case_unique_number as case_unique_number")
            ->where('case_client_selection.selected_user',$client_id)
            ->where("case_master.is_entry_done","1")
            ->where("case_close_date",NULL)
            ->groupBy("case_master.id")  
            ->orderBy("case_master.id","DESC")  
            ->get(); */
            $case = $userProfile->clientCases;
            
            $closed_case =CaseMaster::join('case_client_selection','case_master.id','=','case_client_selection.case_id')
            ->select("case_master.case_title","case_master.id as cid","case_master.case_unique_number as case_unique_number")
            ->where('case_client_selection.selected_user',$client_id)
            ->where("case_master.is_entry_done","1")
            ->where("case_close_date","!=",NULL)
            ->groupBy("case_master.id")  
            ->orderBy("case_master.id","DESC")  
            ->get();
             
            // dd(67);

            return view('client_dashboard.cientView',compact('userProfile','userProfileCreatedBy','id','companyList','UsersAdditionalInfo','client_id','totalData','case','closed_case'));
        }

    } 
    public function changeAccessFromDashboard(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'client_id' => 'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            if(isset($request->disable) && $request->disable=="yes"){
                UsersAdditionalInfo::where('user_id',$request->client_id)->update(['client_portal_enable'=>"0"]);
            }else{
                UsersAdditionalInfo::where('user_id',$request->client_id)->update(['client_portal_enable'=>"1"]);
                $request->request->add(['user_id' => $request->client_id]);
                $this->ReSendWelcomeEmail($request);
            }
            return response()->json(['errors'=>'','user_id'=>$request->client_id]);
          exit;
        }
    }

    public function saveTrustAmount(Request $request)
    {
        $request['trust_amount']=str_replace(",","",$request->trust_amount);
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'trust_amount' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            UsersAdditionalInfo::where('user_id',$request->user_id)->update(['minimum_trust_balance'=>$request->trust_amount]);
            return response()->json(['errors'=>'','user_id'=>$request->user_id]);
          exit;
        }
    }


      //Send welcome email to user as many times want to send.
      public function ReSendWelcomeEmail(Request $request)
      {
            $token = bin2hex(openssl_random_pseudo_bytes(78));;
            User::where('id',$request->user_id)->update(['token'=>$token]);
            $user =  User::where(["id" => $request->user_id])->first();
            $getTemplateData = EmailTemplate::find(9);
            $fullName=$user->first_name. ' ' .$user->last_name;
            $email=$user->email;
            // $email="testing.testuser6@gmail.com";
            $firmData=Firm::find($user->firm_name);
            // echo $decrypted = Crypt::decryptString($encrypted);
            // $token=BASE_URL.'activate_account/web_token?='.$user->token."&security_patch=".Crypt::encryptString($email);
            $token= route("client/activate/account", $user->token)."?security_patch=".Crypt::encryptString($email);
            $mail_body = $getTemplateData->content;
            $mail_body = str_replace('{name}', $fullName, $mail_body);
            $mail_body = str_replace('{firm}', $firmData['firm_name'], $mail_body);
            $mail_body = str_replace('{email}', $email,$mail_body);
            $mail_body = str_replace('{token}', $token,$mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
            $mail_body = str_replace('{regards}', REGARDS, $mail_body);  
            $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
            $mail_body = str_replace('{refuser}', Auth::User()->first_name, $mail_body);                          
            $mail_body = str_replace('{phone_number}', '', $mail_body);                          
            $mail_body = str_replace('{year}', date('Y'), $mail_body);        
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);       

            $userEmail = [
                "from" => FROM_EMAIL,
                "from_title" => FROM_EMAIL_TITLE,
                "subject" => $getTemplateData->subject ." ".$firmData['firm_name'],
                "to" => $user->email,
                "full_name" => $fullName,
                "mail_body" => $mail_body
            ];
           $sendEmail = $this->sendMail($userEmail);
            if($sendEmail=="1"){
                $user->is_sent_welcome_email  = "1";  // Welcome email sent to user.
                $user->save();
                session(['popup_success' => 'Success! Welcome email sent!']);
            }else{
                session(['popup_error' => 'Error! Unable to send email!']);
            }
            return response()->json(['errors'=>'','user_id'=>$user->id]);
        exit;
      }
 
    public function index()
    {
     
        $user = User::latest()->get();
        $country = Countries::get();
        return view('contract.index',compact('user','country'));
    }

    public function clientCaseList()
    {   
        $columns = array('id', 'case_title', 'case_desc', 'case_number', 'case_status','case_unique_number');
        $requestData= $_REQUEST;
        $getClientWiseCaseList=$this->getClientWiseCaseList($requestData['user_id']);
        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_role as userrole");
        $case = $case->whereIn("case_master.id",$getClientWiseCaseList);
        $case = $case->where("case_master.is_entry_done","1")
        ->where("case_master.is_entry_done","1");
        // ->where("case_close_date",NULL)
        // ->orwhere("case_close_date","!=",NULL);
        $totalData=$case->count();
        $totalFiltered = $totalData; 
        
        $case = $case->offset($requestData['start'])->limit($requestData['length']);
        $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $case = $case->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $case 
        );
        echo json_encode($json_data);  
    }

    public function unlinkFromCase(Request $request)
    {
      
        $validator = \Validator::make($request->all(), [
            'id' => 'required|numeric',
            'user_delete_contact_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            CaseClientSelection::where('case_id',$request->id)->where('selected_user',$request->user_delete_contact_id)->delete();
            
            $ClientActivityHistory=[];
            $ClientActivityHistory['acrtivity_title']='unlinked contact';
            $ClientActivityHistory['activity_by']=Auth::User()->id;
            $ClientActivityHistory['activity_for']=($request->user_delete_contact_id)??NULL;
            $ClientActivityHistory['type']="2";
            $ClientActivityHistory['task_id']=NULL;
            $ClientActivityHistory['case_id']=$request->id;
            $ClientActivityHistory['created_by']=Auth::User()->id;
            $ClientActivityHistory['created_at']=date('Y-m-d H:i:s');
            $this->saveClientActivity($ClientActivityHistory);
            
            return response()->json(['errors'=>'','user_id'=>$request->user_delete_contact_id]);
            exit;
        }
    }
    public function addExistingCase(Request $request)
    {
        $client_id=base64_decode($request->user_id);
        $UserInfo=User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
        ->select('users.*','users_additional_info.client_portal_enable')
        ->where('users.id',$client_id)->first();
        return view('client_dashboard.addExistingCase',compact('client_id','UserInfo'));
    }

    public function loadCaseData(Request $request)
    {
        
        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");

        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $case = $case->whereIn("case_master.created_by",$getChildUsers);
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $case = $case->whereIn("case_master.id",$childUSersCase);
        }

        if($request->search!=""){
            $case = $case->where("case_master.case_title",'LIKE',"%$request->search%");
        }
        $case = $case->where("case_master.is_entry_done","1")->get();
        
        return response()->json(["total_count"=>$case->count(),"incomplete_results"=>false,"items"=>$case]);
    }

    public function saveLinkCase(Request $request)
    {
        // print_r($request->all());exit;
        $validator = \Validator::make($request->all(), [
            'case_id' => 'required|numeric',
            'client_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            if($request->user_level=="3"){
                $isLinked= CaseStaff::where('case_id',$request->case_id)->where('user_id',$request->client_id)->count();
                if($isLinked<=0){
                   
                    $CaseClientSelection=new CaseStaff;
                    $CaseClientSelection->case_id=$request->case_id;
                    $CaseClientSelection->user_id=$request->client_id;
                    $CaseClientSelection->save();
                    
                    $ClientActivityHistory=[];
                    $ClientActivityHistory['acrtivity_title']='linked contact';
                    $ClientActivityHistory['activity_by']=Auth::User()->id;
                    $ClientActivityHistory['activity_for']=($request->client_id)??NULL;
                    $ClientActivityHistory['type']="2";
                    $ClientActivityHistory['task_id']=NULL;
                    $ClientActivityHistory['case_id']=$request->case_id;
                    $ClientActivityHistory['created_by']=Auth::User()->id;
                    $ClientActivityHistory['created_at']=date('Y-m-d H:i:s');
                    $this->saveClientActivity($ClientActivityHistory);
    
    
                    return response()->json(['errors'=>'','user_id'=>$request->client_id]);
                    exit;
                }else{
                    return response()->json(['errors'=>['Staff member is already linked to this case']]);
                    exit;
                }
            }else{
                $isLinked= CaseClientSelection::where('case_id',$request->case_id)->where('selected_user',$request->client_id)->count();
                if($isLinked<=0){
                    $CaseClientSelection=new CaseClientSelection;
                    $CaseClientSelection->case_id=$request->case_id;
                    $CaseClientSelection->selected_user=$request->client_id;
                    $CaseClientSelection->save();
               
                    $ClientActivityHistory=[];
                    $ClientActivityHistory['acrtivity_title']='linked contact';
                    $ClientActivityHistory['activity_by']=Auth::User()->id;
                    $ClientActivityHistory['activity_for']=($request->client_id)??NULL;
                    $ClientActivityHistory['type']="2";
                    $ClientActivityHistory['task_id']=NULL;
                    $ClientActivityHistory['case_id']=$request->case_id;
                    $ClientActivityHistory['created_by']=Auth::User()->id;
                    $ClientActivityHistory['created_at']=date('Y-m-d H:i:s');
                    $this->saveClientActivity($ClientActivityHistory);
    
                    $data=[];
                    $data['user_id']=$request->client_id;
                    $data['client_id']=$request->client_id;
                    $data['case_id']=$request->case_id;
                    $data['activity']='link contact';
                    $data['type']='contact';
                    $data['action']='link';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);
                    return response()->json(['errors'=>'','user_id'=>$request->client_id]);
                    exit;
                }else{
                    return response()->json(['errors'=>['Staff member is already linked to this case']]);
                    exit;
                }
            }
            
           
        }
        
    }
    public function saveStaffLinkCase(Request $request)
    {
        // print_r($request->all());exit;
        $validator = \Validator::make($request->all(), [
            'case_id' => 'required|numeric',
            'client_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            // $isLinked= CaseClientSelection::where('case_id',$request->case_id)->where('selected_user',$request->client_id)->count();
            $isLinked= CaseStaff::where('case_id',$request->case_id)->where('user_id',$request->client_id)->count();
            if($isLinked<=0){
                // $CaseClientSelection=new CaseClientSelection;
                // $CaseClientSelection->case_id=$request->case_id;
                // $CaseClientSelection->selected_user=$request->client_id;
                // $CaseClientSelection->save();

                $CaseClientSelection=new CaseStaff;
                $CaseClientSelection->case_id=$request->case_id;
                $CaseClientSelection->user_id=$request->client_id;
                $CaseClientSelection->save();
                
                $ClientActivityHistory=[];
                $ClientActivityHistory['acrtivity_title']='linked contact';
                $ClientActivityHistory['activity_by']=Auth::User()->id;
                $ClientActivityHistory['activity_for']=($request->client_id)??NULL;
                $ClientActivityHistory['type']="2";
                $ClientActivityHistory['task_id']=NULL;
                $ClientActivityHistory['case_id']=$request->case_id;
                $ClientActivityHistory['created_by']=Auth::User()->id;
                $ClientActivityHistory['created_at']=date('Y-m-d H:i:s');
                $this->saveClientActivity($ClientActivityHistory);


                return response()->json(['errors'=>'','user_id'=>$request->client_id]);
                exit;
            }else{
                return response()->json(['errors'=>['Client name is already linked to this case']]);
                exit;
            }
           
        }
        
    }
    public function ClientActivityHistory()
    {   
        $requestData= $_REQUEST;
        $allLeads = ClientActivity::leftJoin('users','client_activity.activity_by','=','users.id');
        $allLeads = $allLeads->select("users.user_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),"client_activity.*","client_activity.created_at as note_created_at");        
        $allLeads = $allLeads->where("client_activity.activity_for",$requestData['user_id']);   
        $allLeads = $allLeads->orderBy("client_activity.created_at","DESC");    
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
     
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy('client_activity.created_at','DESC');
        $allLeads = $allLeads->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allLeads 
        );
        echo json_encode($json_data);  
    }
    public function ClientNotes()
    {   
        $requestData= $_REQUEST;
        $allLeads = ClientNotes::leftJoin('users','client_notes.client_id','=','users.id');
        $allLeads = $allLeads->leftJoin('users as u1','client_notes.created_by','=','u1.id');
        $allLeads = $allLeads->leftJoin('users as u2','client_notes.updated_by','=','u2.id');
        $allLeads = $allLeads->select("users.user_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as client_name'),DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as note_created_by'),"u1.user_title as created_by_user_title",DB::raw('CONCAT_WS(" ",u2.first_name,u2.middle_name,u2.last_name) as note_updated_by'),"u2.user_title as updated_by_user_title","client_notes.*","client_notes.created_at as note_created_at");        
        if(isset($requestData['case_id'])){
            $allLeads = $allLeads->where("client_notes.case_id",$requestData['case_id']);  
        }
        if(isset($requestData['user_id'])){
            $allLeads = $allLeads->where("client_notes.client_id",$requestData['user_id']);   
        }
        $allLeads = $allLeads->orderBy("client_notes.created_at","DESC");    
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
     
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy('client_notes.created_at','DESC');
        $allLeads = $allLeads->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allLeads 
        );
        echo json_encode($json_data);  
    }
    public function addNotes(Request $request)
    {
        DB::table("client_notes")->where("note_date",NULL)->where("note_subject",NULL)->where("notes",NULL)->delete();
        $case_id=$client_id=$caseMaster=$userData='';
        if($request->case_id!=''){
            $case_id=$request->case_id;
            $caseMaster=CaseMaster::find($case_id);
        }else{
            $client_id=$request->user_id;
            $userData=User::find($client_id);
        }
        
        $LeadNotes = new ClientNotes; 
        $LeadNotes->client_id=NULL;
        $LeadNotes->note_date=NULL;
        $LeadNotes->note_subject=NULL;
        $LeadNotes->notes=NULL;
        $LeadNotes->status="0";
        $LeadNotes->created_by=NULL;
        $LeadNotes->created_at=NULL;            
        $LeadNotes->updated_by=NULL;
        $LeadNotes->updated_at=NULL;
        $LeadNotes->save();
        $note_id=$LeadNotes->id;
        return view('client_dashboard.addNote',compact('userData','client_id','note_id','caseMaster','case_id'));
    }

    public function addNotesFromDashboard(Request $request)
    {
        $getChildUsers=$this->getParentAndChildUserIds();
        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
        $LeadNotes = new ClientNotes; 
        $LeadNotes->client_id=NULL;
        $LeadNotes->note_date=NULL;
        $LeadNotes->note_subject=NULL;
        $LeadNotes->notes=NULL;
        $LeadNotes->status="0";
        $LeadNotes->created_by=NULL;
        $LeadNotes->created_at=NULL;            
        $LeadNotes->updated_by=NULL;
        $LeadNotes->updated_at=NULL;
        $LeadNotes->save();
        $note_id=$LeadNotes->id;

        return view('client_dashboard.addNoteForDashboard',compact('CaseMasterClient','CaseMasterCompany','CaseMasterData','note_id'));
    }
    public function saveNoteForDashboard(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'note_date' => 'required',
            'delta' => 'required'
        ],[
            'note_date.required' => 'Date is a required field',
            'delta.required' => 'Note cant be blank',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $data=[];
            $data['case_id']=NULL;
            $data['user_id']=Auth::User()->id;
            $data['activity']='added a note';

            $LeadNotes = ClientNotes::find($request->note_id); 
            if($request->text_case_id!=''){
                $LeadNotes->client_id=NULL;
                $LeadNotes->company_id=NULL;
                $LeadNotes->case_id=$request->text_case_id;
                $uid=$LeadNotes->case_id;
                $data['notes_for_case']=$request->text_case_id;
            }
            if($request->text_company_id!=''){
                $LeadNotes->client_id=NULL;
                $LeadNotes->company_id=$request->text_company_id;
                $LeadNotes->case_id=NULL;
                $data['notes_for_company']=$request->text_company_id;
            }
            if($request->text_client_id!=''){
                $LeadNotes->client_id=$request->text_client_id;
                $LeadNotes->company_id=NULL;
                $LeadNotes->case_id=NULL;
                $data['notes_for_client']=$request->text_client_idd;
            }
            
            $LeadNotes->note_date=date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->note_date)))), auth()->user()->user_timezone ?? 'UTC')));
            $LeadNotes->note_subject=($request->note_subject)??NULL;
            $LeadNotes->notes=$request->delta;
            $LeadNotes->status="0";
            $LeadNotes->created_by=Auth::User()->id;
            $LeadNotes->created_at=date('Y-m-d H:i:s');            
            $LeadNotes->updated_by=Auth::User()->id;
            $LeadNotes->updated_at=date('Y-m-d H:i:s');
            $LeadNotes->is_draft="yes";
            $LeadNotes->save();

            
            if($request->current_submit=="savenote" || in_array($request->currentButton,["s","st"])){
                $LeadNotes->is_publish="yes";
                $LeadNotes->is_draft="no";
                $LeadNotes->save();
                $LeadNotes->original_content=json_encode($LeadNotes);
                $LeadNotes->save();

                
                $data['type']='notes';
                $data['action']='add';
                
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);

                session(['popup_success' => 'Your note has been created']);
            }else{
                session(['popup_success' => 'Your draft has been autosaved']);
            }
            return response()->json(['errors'=>'','note_id'=>$LeadNotes->id]);
            exit;
        }
        
    }
    public function saveNote(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'note_date' => 'required',
            'delta' => 'required'
        ],[
            'note_date.required' => 'Date is a required field',
            'delta.required' => 'Note cant be blank',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
         
            $LeadNotes = ClientNotes::find($request->note_id); 
            $LeadNotes->client_id=$request->client_id;
            $LeadNotes->case_id=$request->case_id;
            $LeadNotes->note_date=date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->note_date)))), auth()->user()->user_timezone ?? 'UTC')));
            $LeadNotes->note_subject=($request->note_subject)??NULL;
            $LeadNotes->notes=$request->delta;
            $LeadNotes->status="0";
            $LeadNotes->created_by=Auth::User()->id;
            $LeadNotes->created_at=date('Y-m-d H:i:s');            
            $LeadNotes->updated_by=Auth::User()->id;
            $LeadNotes->updated_at=date('Y-m-d H:i:s');
            $LeadNotes->is_draft="yes";
            $LeadNotes->save();

            
            if($request->current_submit=="savenote" || in_array($request->currentButton,["s","st"])){
                $LeadNotes->is_publish="yes";
                $LeadNotes->is_draft="no";
                $LeadNotes->save();
                $LeadNotes->original_content=json_encode($LeadNotes);
                $LeadNotes->save();

                $data=[];
                if(isset($request->case_id) && $request->case_id!=''){
                    $data['case_id']=$request->case_id;
                    $data['notes_for_case']=$request->case_id;
                }     
                if(isset($request->company_id) && $request->company_id!=''){
                    $data['company_id']=$request->company_id;
                    $data['notes_for_company']=$request->company_id;
                }
                if(isset($request->client_id) && $request->client_id!=''){
                    $data['client_id']=$request->client_id;
                    $data['notes_for_client']=$request->client_id;
                }
               
                $data['user_id']=Auth::User()->id;
                $data['activity']='added a note';
                $data['type']='notes';
                $data['action']='add';
                
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);

                session(['popup_success' => 'Your note has been created']);
            }else{
                session(['popup_success' => 'Your draft has been autosaved']);
            }
            return response()->json(['errors'=>'','id'=>$request->client_id,'note_id'=>$LeadNotes->id]);
            exit;
        }
        
    }
    public function discardNote(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'note_id' => 'required'
        ],[
            'note_id.required' => 'Note id is a required field',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
         
            ClientNotes::where("id",$request->note_id)->delete();
            session(['popup_success' => 'Your Draft has been deleted']);
            return response()->json(['errors'=>'','id'=>$request->note_id]);
            exit;
        }
        
    }
    public function discardDeleteNote(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'note_id' => 'required'
        ],[
            'note_id.required' => 'Note id is a required field',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $beforeCheck=ClientNotes::find($request->note_id);
            if($beforeCheck->is_draft=="yes"){
                // ClientNotes::where("id",$request->note_id)->delete();
            }else{
                $beforeCheckData=ClientNotes::find($request->note_id);
                $original_content=json_decode($beforeCheckData->original_content);
                // print_r($original_content);
                $beforeCheckData->client_id=$original_content->client_id;
                $beforeCheckData->note_date=$original_content->note_date;
                $beforeCheckData->note_subject=$original_content->note_subject;
                $beforeCheckData->notes=$original_content->notes;
                $beforeCheckData->status=$original_content->status;
                $beforeCheckData->is_draft=$original_content->is_draft;
                $beforeCheckData->is_publish=$original_content->is_publish;
                // print_r($beforeCheckData);
                $beforeCheckData->save();
            }
            return response()->json(['errors'=>'','id'=>$request->note_id]);
            exit;
        }
        
    }
    public function editNotes(Request $request)
    {
        $note_id=$request->id;
        $ClientNotes=ClientNotes::find($note_id);
        $case_id=$client_id=$caseMaster=$userData='';
        if($request->case_id!=''){
            $case_id=$request->case_id;
            $caseMaster=CaseMaster::find($case_id);
        }else{
            $client_id=$request->user_id;
            $userData=User::find($client_id);
        }
        return view('client_dashboard.editNote',compact('userData','client_id','ClientNotes','note_id','caseMaster','case_id'));
    }
    
    public function updateNote(Request $request)
    {
       
        $validator = \Validator::make($request->all(), [
            'note_date' => 'required',
            'delta' => 'required',
            'note_id' => 'required'
        ],[
            'note_date.required' => 'Date is a required field',
            'note_id.required' => 'Note id is a required field',
            'delta.required' => 'Note cant be blank',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
         
            $LeadNotes = ClientNotes::find($request->note_id); 
            $LeadNotes->client_id=$request->client_id;
            $LeadNotes->case_id=$request->case_id;
            $LeadNotes->note_date=date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->note_date)))), auth()->user()->user_timezone ?? 'UTC')));
            $LeadNotes->note_subject=($request->note_subject)??NULL;
            $LeadNotes->notes=$request->delta;
            $LeadNotes->status="0";
            $LeadNotes->updated_by=Auth::User()->id;
            $LeadNotes->updated_at=date('Y-m-d H:i:s');
            $LeadNotes->save();

            if($request->current_submit=="publish_note"){
              
                $LeadNotes->is_publish="yes";
                $LeadNotes->is_draft="no";
                $LeadNotes->save();

                $data=[];
                if($LeadNotes['client_id']!=NULL){
                    $data['client_id']=$LeadNotes['client_id'];
                    $data['notes_for_client']=$LeadNotes['client_id'];
                }else if($LeadNotes['case_id']!=NULL){
                    $data['case_id']=$LeadNotes['case_id'];
                    $data['notes_for_case']=$LeadNotes['case_id'];
                }else if($LeadNotes['company_id']!=NULL){
                    $data['company_id']=$LeadNotes['company_id'];
                    $data['notes_for_company']=$LeadNotes['company_id'];
                }
                $data['user_id']=Auth::User()->id;
                $data['activity']='updated a note';
                $data['type']='notes';
                $data['action']='update';
                
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);


            }
            if($request->current_submit=="save_draft"){
                $LeadNotes->is_draft="yes";
                $LeadNotes->save();
            }

            return response()->json(['errors'=>'','id'=>$request->client_id]);
            exit;
        }
        
    }
    public function deleteNote(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'note_id' => 'required'
        ],[
            'note_id.required' => 'Note id is a required field',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $dataNotes=ClientNotes::find($request->note_id);
            $data=[];
            if($dataNotes->client_id != NULL){
                $data['client_id']=$dataNotes->client_id;
                $data['notes_for_client']=$dataNotes->client_id;
            }else if($dataNotes->case_id != NULL){
                $data['case_id']=$dataNotes->case_id;
                $data['notes_for_case']=$dataNotes->case_id;
            }else if($dataNotes->company_id != NULL){
                $data['company_id']=$dataNotes->company_id;
                $data['notes_for_company']=$dataNotes->company_id;
            }
            $data['user_id']=Auth::User()->id;
            $data['activity']='deleted a note';
            $data['type']='notes';
            $data['action']='delete';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            ClientNotes::where("id",$request->note_id)->delete();
            session(['popup_success' => 'Your note has been deleted']);
            return response()->json(['errors'=>'','id'=>$request->note_id]);
            exit;
        }
        
    }

    public function loadTimeEntryPopup(Request $request)
    {
        $defaultRate=$company_id=$case_id=$client_id='';
        $dataNotes=ClientNotes::find($request->note_id);
        if($dataNotes['client_id']!=NULL){
            $client_id=$dataNotes['client_id'];
        }else if($dataNotes['case_id']!=NULL){
            $case_id=$dataNotes['case_id'];
        }else if($dataNotes['company_id']!=NULL){
            $company_id=$dataNotes['company_id'];
        }
        if(Auth::user()->parent_user==0){
            $getChildUsers=$this->getParentAndChildUserIds();
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
        }
        // $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
        $loadFirmStaff = firmUserList();
        $TaskActivity=TaskActivity::where('status','1')->where("firm_id",Auth::user()->firm_name)->get();
        
        return view('client_dashboard.loadTimeEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity','defaultRate','company_id','case_id','client_id'));     
        exit;    
    } 
    public function saveTimeEntryPopup(Request $request)
    {
        
        $validator = \Validator::make($request->all(), [
            'case_or_lead' => 'required',
            'staff_user' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $TaskTimeEntry = new TaskTimeEntry;
            $TaskTimeEntry->case_id =$request->case_or_lead;
            $TaskTimeEntry->user_id =$request->staff_user;
            if(isset($request->activity_text)){
                $TaskAvtivity = new TaskActivity;
                $TaskAvtivity->title=$request->activity_text;
                $TaskAvtivity->status="1";
                $TaskAvtivity->firm_id=Auth::User()->firm_name; 
                $TaskAvtivity->created_by=Auth::User()->id; 
                $TaskAvtivity->save();
                $TaskTimeEntry->activity_id=$TaskAvtivity->id;
            }else{
                $TaskTimeEntry->activity_id=$request->activity;
            }
            if($request->time_tracking_enabled=="on"){
                $TaskTimeEntry->time_entry_billable="yes";
            }else{
                $TaskTimeEntry->time_entry_billable="no";
            }
            $TaskTimeEntry->description=$request->case_description;
            $TaskTimeEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
            $TaskTimeEntry->entry_rate=str_replace(",","",$request->rate_field_id);
            $TaskTimeEntry->rate_type=$request->rate_type_field_id;
            $TaskTimeEntry->duration =$request->duration_field;
            $TaskTimeEntry->created_by=Auth::User()->id; 
            $TaskTimeEntry->save();
            return response()->json(['errors'=>'','id'=>$TaskTimeEntry->id]);
        exit;
        }
    } 

    /* public function loadTrustHistory()
    {   
        $requestData= $_REQUEST;

        
       
        $allLeads = TrustHistory::leftJoin('users','trust_history.client_id','=','users.id');
        $allLeads = $allLeads->leftJoin('users as u1','trust_history.client_id','=','u1.id');
        $allLeads = $allLeads->leftJoin('users_additional_info as u2','trust_history.client_id','=','u2.id');
        $allLeads = $allLeads->select("users.user_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as client_name'),DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as note_created_by'),"u1.user_title as created_by_user_title","trust_history.*","trust_history.client_id as client_id","u2.minimum_trust_balance","u2.trust_account_balance");        
        $allLeads = $allLeads->where("trust_history.client_id",$requestData['user_id']);   
        if($requestData['case_id'] != '') {
            $allLeads = $allLeads->where("trust_history.allocated_to_case_id",$requestData['case_id']);
        }
        $allLeads = $allLeads->with('invoice', 'fundRequest', 'allocateToCase');   
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
     
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy('trust_history.id','DESC');
        $allLeads = $allLeads->get();

        $userAddInfo = UsersAdditionalInfo::where('user_id', $requestData['user_id'])->first();

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allLeads,
            "trust_total"     => $userAddInfo->trust_account_balance ?? 0.00
        );
        echo json_encode($json_data);  
    } */
    public function loadTrustHistory(Request $request)
    {   
        $data = TrustHistory::where("client_id", $request->client_id);
        if($request->case_id) {
            $data = $data->where('allocated_to_case_id', $request->case_id);
        }
        $data = $data->orderBy("payment_date", "desc")->orderBy("created_at", "desc")
                ->with("invoice", 'fundRequest', 'allocateToCase', 'user', 'leadAdditionalInfo')->get();
        $userAddInfo = UsersAdditionalInfo::where("user_id", $request->client_id)->first();
        return Datatables::of($data)
            ->addColumn('action', function ($data) use($userAddInfo) {
                $action = '';
                if($data->is_refunded == "yes") {
                    $action .= '<span data-toggle="popover" data-trigger="hover" title="" data-content="Edit" data-placement="top" data-html="true"><a ><button type="button" disabled="" class="py-0 btn btn-link disabled">Refund</button></a></span>';
                    $action .= '<span data-toggle="popover" data-trigger="hover" title="" data-content="Delete" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#deleteLocationModal" data-placement="bottom" href="javascript:;" onclick="deleteEntry('.$data->id.');"><button type="button" disabled="" class="py-0 btn btn-link disabled">Delete</button></a></span>';
                } else {
                    if($data->fund_type=="allocate_trust_fund" || $data->fund_type=="deallocate_trust_fund"){
                        $action .= '<span><a ><button type="button" disabled="" class="py-0 btn btn-link disabled">Refund</button></a></span>';
                        $action .= '<span><a ><button type="button" disabled="" class="py-0 btn btn-link disabled">Delete</button></a></span>';
                    } else {
                        if($data->fund_type=="refund_withdraw" || $data->fund_type=="refund_deposit" || $data->fund_type=="refund payment" || $data->fund_type=="refund payment deposit"){
                            $action .= '<span data-toggle="popover" data-trigger="hover" title="" data-content="Edit" data-placement="top" data-html="true"><a ><button type="button" disabled="" class="py-0 btn btn-link disabled">Refund</button></a></span>';
                        }else{
                            $action .= '<span data-toggle="popover" data-trigger="hover" title="" data-content="Edit" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#RefundPopup" data-placement="bottom" href="javascript:;"  onclick="RefundPopup('.$data->id.');"><button type="button"  class="py-0 btn btn-link ">Refund</button></a></span>';
                        }
                        if($userAddInfo->unallocate_trust_balance < $data->amount_paid && !$data->allocated_to_case_id && !$data->allocated_to_lead_case_id && $data->fund_type != "withdraw" && $data->fund_type != "payment") {
                            $action .= '<span><a ><button type="button" disabled="" class="py-0 btn btn-link disabled">Delete</button></a></span>';
                        } else if($userAddInfo->unallocate_trust_balance < $data->amount_paid && $data->allocated_to_case_id) {
                            $allocatedAmount = CaseClientSelection::where("case_id", $data->allocated_to_case_id)->where("selected_user", $userAddInfo->user_id)->select("allocated_trust_balance")->first();
                            if($allocatedAmount->allocated_trust_balance < $data->amount_paid && $data->fund_type != "withdraw" && $data->fund_type != "payment")
                                $action .= '<span><a ><button type="button" disabled="" class="py-0 btn btn-link disabled">Delete</button></a></span>';
                            else
                                $action .= '<span data-toggle="popover" data-trigger="hover" title="" data-content="Delete" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#deleteLocationModal" data-placement="bottom" href="javascript:;" onclick="deleteEntry('.$data->id.');"><button type="button" class="py-0 btn btn-link">Delete</button></a></span>';
                        } else {
                            $action .= '<span data-toggle="popover" data-trigger="hover" title="" data-content="Delete" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#deleteLocationModal" data-placement="bottom" href="javascript:;" onclick="deleteEntry('.$data->id.');"><button type="button" class="py-0 btn btn-link">Delete</button></a></span>';
                        }
                    }
                }
                return '<div class="text-center">'.$action.'<div role="group" class="btn-group-sm btn-group-vertical"></div></div>';
            })
            ->editColumn('payment_date', function ($data) {
                // return $data->payment_date ?? "--";
                if($data->payment_date) {
                    $pDate = @convertUTCToUserDate(@$data->payment_date, auth()->user()->user_timezone);
                    if ($pDate->isToday()) {
                        return "Today";
                    } else if($pDate->isYesterday()) {
                        return "Yesterday";
                    } else {
                        return $pDate->format("M d, Y");
                    }
                } else {
                    return "";
                }
            })
            ->addColumn('related_to', function ($data) {
                if($data->related_to_invoice_id)
                    return '<a href="'.route("bills/invoices/view", $data->invoice->decode_id).'" >#'.$data->invoice->invoice_id.'</a>';
                else if($data->related_to_fund_request_id)
                    return $data->fundRequest->padding_id;
                else
                    return '--';
            })
            ->addColumn('allocated_to', function ($data) {
                if($data->allocated_to_case_id != null && $data->fund_type != "payment") {
                    $clientLink ='<a class="name" href="'.route('info', $data->allocateToCase->case_unique_number).'">'.$data->allocateToCase->case_title.'</a>';
                } else if($data->allocated_to_lead_case_id != null && $data->leadAdditionalInfo) {
                    $clientLink = @$data->leadAdditionalInfo->potential_case_title;
                } else if($data->related_to_fund_request_id && $data->allocated_to_lead_case_id && $data->leadAdditionalInfo) {
                    $clientLink = @$data->leadAdditionalInfo->potential_case_title;
                } else {
                    $clientLink ='<a class="name" href="'.route("contacts/clients/view", $data->client_id).'">'.@$data->user->full_name.' ('.@$data->user->user_type_text.')</a>';
                }
                return $clientLink;
            })
            ->editColumn('payment_method', function ($data) {
                $isRefund = ($data->is_refunded == "yes") ? "(Refunded)" : "";
                if($data->fund_type == "withdraw")
                    $pMethod = "Trust";
                else
                    $pMethod = $data->payment_method;
                return ucwords($pMethod).' '.$isRefund;
            })
            ->editColumn('total_balance', function ($data) {
                return $data->trust_balance;
            })
            ->editColumn('deposit_amount', function ($data) {
                if($data->fund_type=="withdraw"){
                    $amt = '-$'.number_format($data->withdraw_amount ?? 0, 2);
                }else if($data->fund_type=="refund_withdraw" || $data->fund_type=="refund payment"){
                    $amt = '$'.number_format($data->refund_amount, 2);
                }else if($data->fund_type=="refund_deposit" || $data->fund_type=="refund payment deposit"){
                    $amt = '-$'.number_format($data->refund_amount, 2);
                }else if($data->fund_type=="payment"){
                    $amt = '-$'.number_format($data->amount_paid, 2);
                }else if($data->fund_type=="allocate_trust_fund"){
                    $amt = '($'.number_format($data->amount_paid, 2).')';
                }else if($data->fund_type=="deallocate_trust_fund"){
                    $amt = '(-$'.number_format($data->amount_paid, 2).')';
                }else if($data->fund_type=="payment deposit"){
                    $amt = '$'.number_format($data->amount_paid, 2);
                }else{
                    $amt = '$'.number_format($data->amount_paid, 2);
                }
                return $amt ?? 0.00;
            })
            ->addColumn('detail', function ($data) {
                $isRefund = ($data->is_refunded == "yes") ? "(Refunded)" : "";
                if($data->user->user_level == 5) {
                    if($data->fund_type == "diposit") {
                        $ftype = "Payment into Trust (Trust Account)";
                    }else if($data->fund_type=="refund_deposit"){
                        $ftype="Refund Deposit into Trust (Trust Account)";
                    }else if($data->fund_type=="payment"){
                        $ftype = "Payment from Trust (Trust Account) to Operating (Operating Account)";
                    }else if($data->fund_type=="refund payment"){
                        $ftype = "Refund Payment from Trust (Trust Account) to Operating (Operating Account)";
                    } else {
                        $ftype = '';
                    }
                    $noteContent = '';
                    if($data->notes != '') {
                        $noteContent = '<br>
                        <a tabindex="0" class="" data-toggle="popover" data-html="true" data-placement="bottom" 
                        data-trigger="focus" title="Notes" data-content="'.$data->notes.'">View Notes</a>';
                    }
                    return $ftype.$isRefund.$noteContent;
                } else {
                    if($data->fund_type=="withdraw"){
                        if($data->withdraw_from_account!=null){
                            $ftype="Withdraw from Trust (Trust Account) to Operating(".$data->withdraw_from_account.")";
                        }else{
                            $ftype="Withdraw from Trust (Trust Account)";
                        }
                        $noteContent = '';
                        if($data->notes != '') {
                            $noteContent = '<br>
                            <a tabindex="0" class="" data-toggle="popover" data-html="true" data-placement="bottom" 
                            data-trigger="focus" title="Notes" data-content="'.$data->notes.'">View Notes</a>';
                        }
                        return $ftype.' '.$isRefund.$noteContent;
                    }else if($data->fund_type=="refund_withdraw"){
                        $ftype="Refund Withdraw from Trust (Trust Account)";
                    }else if($data->fund_type=="allocate_trust_fund"){
                        $notes = $data->notes;
                        $myString = substr($notes, strpos($notes, "#"));
                        $ftype = str_replace($myString, '<a class="name" href="'.route('contacts/clients/view', $data->client_id).'">'.@$data->user->full_name.' ('.@$data->user->user_type_text.')</a>', $notes);
                    }else if($data->fund_type=="deallocate_trust_fund"){
                        $notes = $data->notes;
                        $myString = substr($notes, strpos($notes, "#"));
                        $ftype = str_replace($myString, '<a class="name" href="'.route('info', @$data->allocateToCase->case_unique_number).'">'.@$data->allocateToCase->case_title.'</a>', $notes);
                    }else if($data->fund_type=="payment"){
                        return $ftype = "Payment from Trust (Trust Account) to Operating (Operating Account)";
                        $noteContent = '';
                        if($data->notes != '') {
                            $noteContent = '<br>
                            <a tabindex="0" class="" data-toggle="popover" data-html="true" data-placement="bottom" 
                            data-trigger="focus" title="Notes" data-content="'.$data->notes.'">View Notes</a>';
                        }
                        return $ftype.' '.$isRefund.$noteContent;
                    }else if($data->fund_type=="refund payment"){
                        $ftype = "Refund Payment from Trust (Trust Account) to Operating (Operating Account)";
                        $noteContent = '';
                        if($data->notes != '') {
                            $noteContent = '<br>
                            <a tabindex="0" class="" data-toggle="popover" data-html="true" data-placement="bottom" 
                            data-trigger="focus" title="Notes" data-content="'.$data->notes.'">View Notes</a>';
                        }
                        return $ftype.' '.$isRefund.$noteContent;
                    }else if($data->fund_type=="payment deposit"){
                        $ftype = "Payment into Trust (Trust Account)";
                        $noteContent = '';
                        if($data->notes != '') {
                            $noteContent = '<br>
                            <a tabindex="0" class="" data-toggle="popover" data-html="true" data-placement="bottom" 
                            data-trigger="focus" title="Notes" data-content="'.$data->notes.'">View Notes</a>';
                        }
                        return $ftype.' '.$isRefund.$noteContent;
                    }else if($data->fund_type=="refund payment deposit"){
                        $ftype = "Refund Payment into Trust (Trust Account)";
                        $noteContent = '';
                        if($data->notes != '') {
                            $noteContent = '<br>
                            <a tabindex="0" class="" data-toggle="popover" data-html="true" data-placement="bottom" 
                            data-trigger="focus" title="Notes" data-content="'.$data->notes.'">View Notes</a>';
                        }
                        return $ftype.' '.$isRefund.$noteContent;
                    }else if($data->fund_type=="refund_deposit"){
                        $ftype="Refund Deposit into Trust (Trust Account)";
                    }else{
                        $ftype="Deposit into Trust (Trust Account)".$isRefund;
                    }
                }
                return $ftype;
            })
            ->rawColumns(['action', 'detail', 'related_to', 'allocated_to'])
            ->with("trust_total", number_format($userAddInfo->trust_account_balance ?? 0.00, 2))
            ->make(true);
    }

    /* public function addTrustEntry(Request $request)
    {
        $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($request->user_id);
        $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$request->user_id)->first();
        $clientList = RequestedFund::select('requested_fund.*')->where("requested_fund.client_id",$request->user_id)->where("amount_due",">",0)->get();
        return view('client_dashboard.billing.depositTrustEntry',compact('userData','UsersAdditionalInfo','clientList'));     
        exit;    
    } 

    public function saveTrustEntry(Request $request)
    {
        $request['amount']=str_replace(",","",$request->amount);
        if(isset($request->applied_to) && $request->applied_to!=0){
            $requestData=RequestedFund::find($request->applied_to);
            $amount_requested=$requestData['amount_requested'];
            $amount_due=$requestData['amount_due'];
            $amount_paid=$requestData['amount_paid'];
            $finalAmt=$amount_requested-$amount_paid;
    
            $validator = \Validator::make($request->all(), [
                'payment_method' => 'required',
               'amount' => 'required|numeric|min:1|max:'.$finalAmt,
            ],[
                'amount.min'=>"Amount must be greater than $0.00",
                'amount.max' => 'Amount exceeds requested balance of $'.number_format($finalAmt,2),
            ]);
        }else{
            $validator = \Validator::make($request->all(), [
                'payment_method' => 'required',
                'amount' => 'required|numeric'
            ]);
        }
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            if(isset($request->applied_to) && $request->applied_to!=0){
                $refundRequest=RequestedFund::find($request->applied_to);
                $refundRequest->amount_due=($refundRequest->amount_due-$request->amount);                
                $refundRequest->amount_paid=($refundRequest->amount_paid+$request->amount);
                $refundRequest->payment_date=date('Y-m-d');
                $refundRequest->status='partial';
                $refundRequest->save();
            }
                
            DB::table('users_additional_info')->where('user_id',$request->client_id)->increment('trust_account_balance', $request['amount']);

            $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$request->client_id)->first();
       
            $TrustInvoice=new TrustHistory;
            $TrustInvoice->client_id=$request->client_id;
            $TrustInvoice->payment_method=$request->payment_method;
            $TrustInvoice->amount_paid=$request->amount;
            $TrustInvoice->current_trust_balance=$UsersAdditionalInfo->trust_account_balance;
            $TrustInvoice->payment_date=date('Y-m-d',strtotime($request->payment_date));
            $TrustInvoice->notes=$request->notes;
            $TrustInvoice->fund_type='diposit';
            $TrustInvoice->related_to_fund_request_id = @$refundRequest->id;
            $TrustInvoice->created_by=Auth::user()->id; 
            $TrustInvoice->save();

            $firmData=Firm::find(Auth::User()->firm_name);
            $msg="Thank you. Your deposit of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
            
            
            $data=[];
            $data['user_id']=$request->client_id;
            $data['client_id']=$request->client_id;
            $data['activity']="accepted a deposit into trust of $".number_format($request->amount,2)." (".$request->payment_method.") for";
            if(isset($request->applied_to) && $request->applied_to != 0) {
                $data['activity']="accepted a payment of $".number_format($request->amount,2)." (".$request->payment_method.") for deposit request ".@$refundRequest->padding_id;
            }
            $data['type']='deposit';
            $data['action']='add';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
            
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;   
        }
    } */

    
    public function withdrawFromTrust(Request $request)
    {
        $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($request->user_id);
        $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$request->user_id)->first();
        $userCases = CaseMaster::whereHas('caseAllClient', function($query) use($request) {
                        $query->where('users.id', $request->user_id);
                    })->select("id", "case_title", "total_allocated_trust_balance")->get();

        return view('client_dashboard.billing.withdrawTrustEntry',compact('userData','UsersAdditionalInfo', 'userCases'));     
        exit;    
    } 
    public function saveWithdrawFromTrust(Request $request)
    {
        $request['amount']=str_replace(",","",$request->amount);

        $validator = \Validator::make($request->all(), [
            'trust_account' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                
            DB::table('users_additional_info')->where('user_id',$request->client_id)->decrement('trust_account_balance', $request['amount']);

            $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$request->client_id)->first();
       
            $TrustInvoice=new TrustHistory;
            $TrustInvoice->client_id=$request->client_id;
            $TrustInvoice->payment_method=$request->payment_method ?? 'Trust';
            $TrustInvoice->amount_paid="0.00";
            $TrustInvoice->withdraw_amount=$request->amount;
            $TrustInvoice->current_trust_balance=$UsersAdditionalInfo->trust_account_balance;
            // $TrustInvoice->payment_date=date('Y-m-d',strtotime($request->payment_date));
            $TrustInvoice->payment_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone);
            $TrustInvoice->notes=$request->notes;
            $TrustInvoice->fund_type='withdraw';
            $TrustInvoice->created_by=Auth::user()->id; 
            if(isset($request->select_account)){
                $TrustInvoice->withdraw_from_account=$request->select_account;
            }
            $TrustInvoice->refund_ref_id=$request->transaction_id;
            $TrustInvoice->allocated_to_case_id = $request->case_id;
            $TrustInvoice->save();

            if($request->case_id != '') {
                $this->withdrawAllocateTrustBalance($TrustInvoice);
            }

            $this->updateNextPreviousTrustBalance($TrustInvoice->client_id);

            // For account activity
            $request->request->add(["trust_account" => @$TrustInvoice->client_id]);
            $request->request->add(['trust_history_id' => $TrustInvoice->id]);
            $request->request->add(["payment_type" => $TrustInvoice->fund_type]);
            $this->updateTrustAccountActivity($request, $amtAction = "sub", null, $isDebit = "yes");

            // For account activity > payment history
            if(isset($request->select_account)) {
                $request->request->add(["contact_id" => @$TrustInvoice->client_id]);
                $this->updateClientPaymentActivity($request, null);
            }

            session(['popup_success' => 'Withdraw fund successful']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }

    public function refundPopup(Request $request)
    {
        $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($request->user_id);
        $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$request->user_id)->first();
        $TrustHistory=TrustHistory::find($request->transaction_id);
        return view('client_dashboard.billing.refundEntry',compact('userData','UsersAdditionalInfo','TrustHistory'));     
        exit;    
    } 
    public function saveRefundPopup(Request $request)
    {
        // return $request->all();
        $request['amount']=str_replace(",","",$request->amount);
        $GetAmount=TrustHistory::find($request->transaction_id);
        if($GetAmount->fund_type=="withdraw"){
            $mt=$GetAmount->withdraw_amount;
        }else{
            $mt=$GetAmount->amount_paid;
        } 
        $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$request->client_id)->first();
        $lessAmount = $UsersAdditionalInfo->unallocate_trust_balance;
        if($GetAmount->allocated_to_lead_case_id) {
            $lessAmount = $UsersAdditionalInfo->trust_account_balance;
        }
        if($GetAmount->allocated_to_case_id) {
            $allocateAmount = CaseClientSelection::where("case_id", $GetAmount->allocated_to_case_id)->where("selected_user", $request->client_id)->select("allocated_trust_balance")->first();
            $lessAmount = $allocateAmount->allocated_trust_balance;
        }
        // return $lessAmount;
        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric|max:'.$mt.'|lte:'.$lessAmount,
        ],[
            'amount.max' => 'Refund cannot be more than $'.number_format($mt,2),
            'amount.lt' => "Cannot refund. Refunding this transaction would cause the contact's balance to go below zero.",
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            try {
                dbStart(); 
                if($GetAmount->fund_type=="withdraw"){
                    $fund_type='refund_withdraw';
                    DB::table('users_additional_info')->where('user_id',$request->client_id)->increment('trust_account_balance', $request['amount']);
                } else if($GetAmount->fund_type=="payment") {
                    $fund_type='refund payment';
                    DB::table('users_additional_info')->where('user_id',$request->client_id)->increment('trust_account_balance', $request['amount']);
                    // For allocated to lead case
                    if($GetAmount->allocated_to_lead_case_id) {
                        LeadAdditionalInfo::where("user_id", $GetAmount->allocated_to_lead_case_id)->increment('allocated_trust_balance', $request['amount']);
                    }
                } else if($GetAmount->fund_type=="payment deposit") {
                    $fund_type='refund payment deposit';
                    DB::table('users_additional_info')->where('user_id',$request->client_id)->decrement('trust_account_balance', $request['amount']);
                }else{
                    $fund_type='refund_deposit';
                    DB::table('users_additional_info')->where('user_id',$request->client_id)->decrement('trust_account_balance', $request['amount']);
                    // For allocated to lead case
                    if($GetAmount->allocated_to_lead_case_id) {
                        LeadAdditionalInfo::where("user_id", $GetAmount->allocated_to_lead_case_id)->decrement('allocated_trust_balance', $request['amount']);
                    }
                }
                $UsersAdditionalInfo->refresh();
                $GetAmount->is_refunded="yes";
                $GetAmount->save();
        
                $TrustInvoice=new TrustHistory;
                $TrustInvoice->client_id=$request->client_id;
                $TrustInvoice->payment_method='Trust Refund';
                $TrustInvoice->amount_paid="0.00";
                $TrustInvoice->withdraw_amount="0.00";
                $TrustInvoice->refund_amount=$request['amount'];
                $TrustInvoice->current_trust_balance=$UsersAdditionalInfo->trust_account_balance;
                // $TrustInvoice->payment_date=date('Y-m-d',strtotime($request->payment_date));
                $TrustInvoice->payment_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->payment_date)))), auth()->user()->user_timezone);
                $TrustInvoice->notes=$request->notes;
                $TrustInvoice->fund_type=$fund_type;
                $TrustInvoice->refund_ref_id=$request->transaction_id;
                $TrustInvoice->related_to_invoice_id = @$GetAmount->related_to_invoice_id;
                $TrustInvoice->related_to_fund_request_id = @$GetAmount->related_to_fund_request_id;
                $TrustInvoice->allocated_to_case_id = $GetAmount->allocated_to_case_id;
                $TrustInvoice->allocated_to_lead_case_id = $GetAmount->allocated_to_lead_case_id;
                $TrustInvoice->created_by=Auth::user()->id; 
                $TrustInvoice->save();
                $request->request->add(['trust_history_id' => $TrustInvoice->id]);

                if($TrustInvoice->fund_type == "refund payment" || $TrustInvoice->fund_type == "refund payment deposit") {
                    $newInvPaymentId = $this->updateInvoicePaymentAfterTrustRefund($GetAmount->id, $request, $TrustInvoice);
                    $TrustInvoice->fill(["related_to_invoice_payment_id" => $newInvPaymentId])->save();
                }

                // For request refund
                if($TrustInvoice->related_to_fund_request_id) {
                    $this->refundTrustRequest($TrustInvoice->id);
                }

                // For allocated case refund
                if($TrustInvoice->allocated_to_case_id) {
                    if($GetAmount->fund_type=="withdraw" || $TrustInvoice->fund_type == "refund payment"){
                        $this->deleteRefundedAllocateTrustBalance($TrustInvoice); // Refund withdraw amount to case trust balance
                    } else {
                        $this->refundAllocateTrustBalance($TrustInvoice);
                    }
                }

                // For account activity
                if($TrustInvoice->fund_type == "refund_deposit") {
                    $findInvoice = Invoices::whereId($GetAmount->related_to_invoice_id)->first();
                    $request->request->add(["applied_to" => $TrustInvoice->related_to_fund_request_id]);
                    $request->request->add(["trust_account" => @$UsersAdditionalInfo->user_id]);
                    $request->request->add(['invoice_history_id' => null]);
                    $request->request->add(["payment_type" => $TrustInvoice->fund_type]);
                    $this->updateTrustAccountActivity($request, $amtAction = "sub", $findInvoice ?? null, $isDebit = "yes");
                }
                // For update next/previous trust balance
                $this->updateNextPreviousTrustBalance($request->client_id);

                dbCommit();
                session(['popup_success' => 'Refund successful']);
                return response()->json(['errors'=>'']);
                exit;   
            } catch (Exception $e) {
                dbEnd();
                return response()->json(['errors'=> $e->getMessage()]);
            }
        }
    }

    public function deletePaymentEntry(Request $request)
    {
        
        $validator = \Validator::make($request->all(), [
            'payment_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            dbStart();
            $TrustInvoice=TrustHistory::find($request->payment_id);
            if($TrustInvoice->fund_type=="refund_deposit"){
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->increment('trust_account_balance', $TrustInvoice->refund_amount);

                $updateRedord=TrustHistory::find($TrustInvoice->refund_ref_id);
                $updateRedord->is_refunded="no";
                $updateRedord->save();

                // For allocated case refund
                if($TrustInvoice->allocated_to_case_id) {
                    $this->deleteRefundedAllocateTrustBalance($TrustInvoice);
                }

                // For allocated lead case
                if($TrustInvoice->allocated_to_lead_case_id) {
                    LeadAdditionalInfo::where("user_id", $TrustInvoice->allocated_to_lead_case_id)->increment('allocated_trust_balance', $TrustInvoice->refund_amount);
                }

            }else if($TrustInvoice->fund_type=="refund_withdraw"){
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->decrement('trust_account_balance', $TrustInvoice->refund_amount);
                $updateRedord=TrustHistory::find($TrustInvoice->refund_ref_id);
                $updateRedord->is_refunded="no";
                $updateRedord->save();

                if($TrustInvoice->allocated_to_case_id) {
                    $this->refundAllocateTrustBalance($TrustInvoice);
                }
                // For allocated lead case
                if($TrustInvoice->allocated_to_lead_case_id) {
                    LeadAdditionalInfo::where("user_id", $TrustInvoice->allocated_to_lead_case_id)->decrement('allocated_trust_balance', $TrustInvoice->refund_amount);
                }
            } else if($TrustInvoice->fund_type == "refund payment") {
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->decrement('trust_account_balance', $TrustInvoice->refund_amount);
                $updateRedord= TrustHistory::find($TrustInvoice->refund_ref_id);
                $updateRedord->is_refunded="no";
                $updateRedord->save();

                if($TrustInvoice->allocated_to_case_id) {
                    $this->refundAllocateTrustBalance($TrustInvoice);
                }
                // For allocated lead case
                if($TrustInvoice->allocated_to_lead_case_id) {
                    LeadAdditionalInfo::where("user_id", $TrustInvoice->allocated_to_lead_case_id)->decrement('allocated_trust_balance', $TrustInvoice->refund_amount);
                }
            } else if($TrustInvoice->fund_type == "refund payment deposit") {
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->increment('trust_account_balance', $TrustInvoice->refund_amount);
                $updateRedord= TrustHistory::find($TrustInvoice->refund_ref_id);
                $updateRedord->is_refunded="no";
                $updateRedord->save();

                // For allocated case refund payment deposit
                if($TrustInvoice->allocated_to_case_id) {
                    $this->deleteRefundedAllocateTrustBalance($TrustInvoice);
                }
                // For allocated lead case
                if($TrustInvoice->allocated_to_lead_case_id) {
                    LeadAdditionalInfo::where("user_id", $TrustInvoice->allocated_to_lead_case_id)->increment('allocated_trust_balance', $TrustInvoice->refund_amount);
                }
            } else if($TrustInvoice->fund_type == "payment deposit") {
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->decrement('trust_account_balance', $TrustInvoice->amount_paid);
                // For allocated case refund payment deposit
                if($TrustInvoice->allocated_to_case_id) {
                    $this->deleteAllocateTrustBalance($TrustInvoice);
                }
                $this->deleteTrustAccountActivity($TrustInvoice->id);
                // For allocated lead case
                if($TrustInvoice->allocated_to_lead_case_id) {
                    LeadAdditionalInfo::where("user_id", $TrustInvoice->allocated_to_lead_case_id)->decrement('allocated_trust_balance', $TrustInvoice->amount_paid);
                }
            }else if($TrustInvoice->fund_type=="payment"){
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->increment('trust_account_balance', $TrustInvoice->amount_paid);
                $this->deletePaymentTrustBalance($TrustInvoice);
                // For allocated lead case
                if($TrustInvoice->allocated_to_lead_case_id) {
                    LeadAdditionalInfo::where("user_id", $TrustInvoice->allocated_to_lead_case_id)->increment('allocated_trust_balance', $TrustInvoice->amount_paid);
                }
            }else if($TrustInvoice->fund_type=="diposit"){
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->decrement('trust_account_balance', $TrustInvoice->amount_paid);

                if($TrustInvoice->allocated_to_case_id) {
                    $this->deleteAllocateTrustBalance($TrustInvoice);
                }
                // For allocated lead case
                if($TrustInvoice->allocated_to_lead_case_id) {
                    LeadAdditionalInfo::where("user_id", $TrustInvoice->allocated_to_lead_case_id)->decrement('allocated_trust_balance', $TrustInvoice->amount_paid);
                }
            } else if($TrustInvoice->fund_type=="withdraw"){
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->increment('trust_account_balance', $TrustInvoice->withdraw_amount);

                if($TrustInvoice->allocated_to_case_id) {
                    $this->deleteWithdrawAllocateTrustBalance($TrustInvoice);
                }
            }


            $updateBalaance=UsersAdditionalInfo::where("user_id",$TrustInvoice->client_id)->first();
            if($updateBalaance['trust_account_balance']<=0){
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->update(['trust_account_balance'=> "0.00"]);
            }

            if($TrustInvoice->fund_type == "payment" || $TrustInvoice->fund_type == "refund payment" || $TrustInvoice->fund_type == "refund payment deposit" || $TrustInvoice->fund_type == "payment deposit") {
                $this->deleteInvoicePaymentHistoryTrust($TrustInvoice->id);
            }

            // For related to fund request
            if($TrustInvoice->related_to_fund_request_id) {
                $this->deletePaymentTrustRequest($TrustInvoice->id);
            }

            $data=[];
            $data['user_id']=$TrustInvoice->client_id;
            $data['client_id']=$TrustInvoice->client_id;
            $data['activity']="deleted a payment";
            $data['type']='deposit';
            $data['action']='delete';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            // For account activity
            $this->deleteTrustAccountActivity(null, $TrustInvoice->id);
            $client_id = $TrustInvoice->client_id;
            TrustHistory::where('id',$request->payment_id)->delete();
            // Update next/previous records
            $this->updateNextPreviousTrustBalance($client_id);

            dbCommit();
            session(['popup_success' => 'Trust entry was deleted']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }
    public function downloadTrustActivityOld(Request $request)
    {
        $id=$request->id;
        $firmData=Firm::find(Auth::User()->firm_name);
        $userData=User::find($id);
        $country = Countries::get();
        $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
        $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$id)->first();

        $allHistory = TrustHistory::leftJoin('users','trust_history.client_id','=','users.id');
        $allHistory = $allHistory->select("trust_history.*");        
        $allHistory = $allHistory->where("trust_history.client_id",$id);   
        $allHistory = $allHistory->orderBy('trust_history.payment_date','ASC');
        $allHistory = $allHistory->get();
        return view('client_dashboard.billing.trustHistoryPdf',compact('userData','country','firmData','firmAddress','UsersAdditionalInfo','allHistory'));
    }


    public function downloadTrustActivity(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'from_date'    => 'date|nullable',
            'to_date'      => 'date|after_or_equal:from_date|nullable',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                
            $id=$request->user_id;
            $firmData=Firm::find(Auth::User()->firm_name);
            $userData=User::find($id);
            $country = Countries::get();
            $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
            $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$id)->first();

            $allHistory = TrustHistory::leftJoin('users','trust_history.client_id','=','users.id');
            $allHistory = $allHistory->select("trust_history.*");        
            $allHistory = $allHistory->where("trust_history.client_id",$id)->whereIn("trust_history.fund_type", ['diposit','withdraw','refund_withdraw','refund_deposit','payment']); 
            if(isset($request->from_date) && isset($request->to_date)){
                $allHistory = $allHistory->whereBetween('trust_history.payment_date', [date('Y-m-d',strtotime($request->from_date)), date('Y-m-d',strtotime($request->to_date))]); 
            }  
            $allHistory = $allHistory->orderBy('trust_history.payment_date','ASC');
            $allHistory = $allHistory->get();
            // return view('client_dashboard.billing.trustHistoryPdf',compact('userData','country','firmData','firmAddress','UsersAdditionalInfo','allHistory'));

            $filename='trust_export_'.time().'.pdf';
            $startDate = $request->from_date; $endDate = $request->to_date;
            $PDFData=view('client_dashboard.billing.trustHistoryPdf',compact('userData','country','firmData','firmAddress','UsersAdditionalInfo','allHistory', 'startDate', 'endDate'));
            $pdf = new Pdf;
            if($_SERVER['SERVER_NAME']=='localhost'){
                $pdf->binary = EXE_PATH;
            }
            $pdf->addPage($PDFData);
            $pdf->setOptions(['javascript-delay' => 5000]);
            $pdf->setOptions(["footer-right"=> "Page [page] from [topage]"]);
            // $pdf->setOptions(["footer-left"=> "Completed on ". date('m/d/Y',strtotime($caseIntakeForm['submited_at']))]);
            // $pdf->saveAs(public_path("download/pdf/".$filename));
            // $path = public_path("download/pdf/".$filename);
            // return response()->download($path);
            // exit;
            // return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename,'errors'=>''], 200);
            $pdf->saveAs(storage_path("app/public/download/pdf/".$filename));
            $path = storage_path("app/public/download/pdf/".$filename);
            return response()->json([ 'success' => true, "url"=>asset(Storage::url('download/pdf/'.$filename)),"file_name"=>$filename,'errors'=>''], 200);
            exit;
        }
    }
    public function loadRequestedFundHistory()
    {   
        $requestData= $_REQUEST;
        $allLeads = RequestedFund::leftJoin('users','requested_fund.client_id','=','users.id');
        $allLeads = $allLeads->leftJoin('users as u1','requested_fund.client_id','=','u1.id');
        $allLeads = $allLeads->leftJoin('users_additional_info as u2','requested_fund.client_id','=','u2.id');
        $allLeads = $allLeads->select("users.user_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as client_name'),DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as note_created_by'),"u1.user_title as created_by_user_title","requested_fund.*","requested_fund.client_id as client_id","u2.minimum_trust_balance","u2.trust_account_balance");        
        $allLeads = $allLeads->where("requested_fund.client_id",$requestData['user_id']);   
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
     
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy('requested_fund.created_at','DESC');
        $allLeads = $allLeads->withCount('fundPaymentHistory');
        $allLeads = $allLeads->with('user', 'allocateToCase')->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allLeads 
        );
        echo json_encode($json_data);  
    }

    public function addRequestFundPopup(Request $request)
    {
        $client_id=$request->user_id;
        $authUser = auth()->user();
        //Get all client related to firm
        // $ClientList = User::select("email","first_name","last_name","id","user_level",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $ClientList = firmClientList();
        //Get all company related to firm
        // $CompanyList = User::select("email","first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        $CompanyList = firmCompanyList();
        $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($request->user_id);
        $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance","minimum_trust_balance")->where("user_id",$request->user_id)->first();
        // Get All Lead related to firm
        $LeadList = firmLeadList();
        if($request->case_id) {
            $authUser = auth()->user();
            $ClientList = User::whereHas("clientCases", function($query) use($request) {
                $query->where("case_master.id", $request->case_id);
            })->select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'user_level', 'email')->where("firm_name", $authUser->firm_name)
            ->where('user_level', 2)->whereIn("user_status", [1,2])->get();

            $CompanyList = User::whereHas("clientCases", function($query) use($request) {
                $query->where("case_master.id", $request->case_id);
            })->select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'user_level', 'email')
            ->where("firm_name", $authUser->firm_name)->whereIn("user_status", [1,2])->where('user_level', 4)->get();
        }
        
        return view('client_dashboard.billing.addFundRequestEnrty',compact('ClientList','CompanyList','LeadList','client_id','userData','UsersAdditionalInfo'));     
        exit;    
    } 

    public function reloadAmount(Request $request)
    {
        $client_id=$request->user_id;
        $user = User::whereId($client_id)->select("user_level")->first();
        $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$client_id)->with('user')->first(); 
        $clientCases = CaseClientSelection::where("selected_user", $client_id)->where('case_id', '>', 0)->with("case")->get();
        $leadCases = LeadAdditionalInfo::where("user_id", $client_id)->select("user_id", "potential_case_title", "allocated_trust_balance")->get();
        $trust_account_balance=$minimum_trust_balance=0.00;
        if(!empty($UsersAdditionalInfo)){
            $trust_account_balance=number_format($UsersAdditionalInfo->trust_account_balance,2);
            $minimum_trust_balance=number_format($UsersAdditionalInfo->minimum_trust_balance,2);
        }
        $requestDefaultMessage = @getInvoiceSetting()->request_funds_preferences_default_msg ?? "";
        $is_non_trust_retainer = @getInvoiceSetting()->is_non_trust_retainers_credit_account ?? "no";
        return response()->json(['errors'=>'','freshData'=>$UsersAdditionalInfo,'trust_account_balance'=>$trust_account_balance,
            'minimum_trust_balance'=>$minimum_trust_balance, 'request_default_message' => $requestDefaultMessage, 'is_non_trust_retainer' => $is_non_trust_retainer, 
            'clientCases' => $clientCases, 'leadCases' => $leadCases]);
        exit;

    } 

    public function saveRequestFundPopup(Request $request)
    {
        // return $request->all();
        $request['amount']=str_replace(",","",$request->amount);

        $validator = \Validator::make($request->all(), [
            'contact' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user = User::whereId($request->contact)->select("user_level")->first();
            $RequestedFund=new RequestedFund;
            $RequestedFund->client_id=$request->contact;
            $RequestedFund->deposit_into=$request->contact;
            $RequestedFund->deposit_into_type=$request->deposit_into;
            $RequestedFund->amount_requested=$request->amount;
            $RequestedFund->amount_due=$request->amount;
            $RequestedFund->amount_paid="0.00";
            $RequestedFund->email_message=$request->message;
            $RequestedFund->due_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->due_date)))), auth()->user()->user_timezone ?? 'UTC');
            $RequestedFund->status='sent';
            $RequestedFund->allocated_to_case_id= ($user->user_level != 5 && $request->case_id != "") ? $request->case_id : NULL;
            $RequestedFund->allocated_to_lead_case_id= ($user->user_level == 5 && $request->case_id != "") ? $request->contact : NULL;
            $RequestedFund->created_by=Auth::user()->id; 
            $RequestedFund->save();

            $data=[];
            $data['deposit_id']=$RequestedFund->id;
            $data['deposit_for']=$RequestedFund->client_id;
            $data['user_id']=$RequestedFund->client_id;
            $data['client_id']=$RequestedFund->client_id;
            $data['case_id']=($user->user_level != 5 && $request->case_id != "") ? $request->case_id : NULL;
            $data['activity']='sent deposit request';
            $data['type']='fundrequest';
            $data['action']='share';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);


            $firmData=Firm::find(Auth::User()->firm_name);
            $getTemplateData = EmailTemplate::find(10);
            $mail_body = $getTemplateData->content;
            $mail_body = str_replace('{message}', $request->message, $mail_body);
            $mail_body = str_replace('{amount}', number_format($request->amount,2), $mail_body);
            $mail_body = str_replace('{duedate}', date('m/d/Y',strtotime($request->due_date)), $mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
            $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
            $mail_body = str_replace('{year}', date('Y'), $mail_body);        
            $mail_body = str_replace('{request_url}', route('client/bills/request/detail', base64_encode($RequestedFund->id)), $mail_body);        

            $clientData=User::find($request->contact);
            $message="";
            if($clientData->email){
                $nameIs=$clientData->first_name." ".$clientData->middle_name." ".$clientData->last_name;
                $user = [
                    "from" => FROM_EMAIL,
                    "from_title" => FROM_EMAIL_TITLE,
                    "subject" => "Please deposit funds for ".$firmData->firm_name."'s Firm",
                    "to" => $clientData->email,
                    "full_name" => "",
                    "mail_body" => $mail_body
                ];
                // print_r($user);
                $sendEmail = $this->sendMail($user);
                $message.="<p>You successfully requested funds from ".$nameIs." .</p>";
                // $url="<a href='".BASE_URL."/contacts/clients/".$clientData->id."?load_funds=true'>Contact Billing Page</a>";
                $url="<a href='".route('contacts_clients_billing_trust_request_fund', $clientData->id) ."'>Contact Billing Page</a>";
                $message.="<p>You can manage this contact's requests by going to their ".$url."</p>";
                return response()->json(['errors'=>'','msg'=>$message]);
            }else{
                $message.="<p>Selected client contact has no e-mail address. So, please added the e-mail address and try again.</p>";
                return response()->json(['errors'=>$message]);
            }
            
            exit;   
        }
    }
    public function addEmailtouser(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|unique:users,email,NULL,id,firm_name,'.Auth::User()->firm_name,
            'client_id'=>'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
       
            $clientData=User::find($request->client_id);
            $clientData->email=$request->email;
            $clientData->save();
            return response()->json(['errors'=>'', 'user_id' => $clientData->id]);
            exit;   
        }
    }

    public function editFundRequest(Request $request)
    {
        $id=$request->id;
        $RequestedFund=RequestedFund::find($id);
        $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($RequestedFund->client_id);
        
        return view('client_dashboard.billing.editFundRequest',compact('RequestedFund','userData'));     
        exit;    
    } 
    public function saveEditFundRequest(Request $request)
    {
        // return $request->all();
        $request['amount']=str_replace(",","",$request->amount);
        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
       
            $RequestedFund=RequestedFund::find($request->id);
            $RequestedFund->amount_due=$request->amount - $RequestedFund->amount_paid;
            $RequestedFund->amount_requested=$request->amount;
            if(isset($request->due_date)){
                $RequestedFund->due_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->due_date)))), auth()->user()->user_timezone ?? 'UTC');
            } else {
                $RequestedFund->due_date = $request->due_date;
            }
            $RequestedFund->status='sent';
            $RequestedFund->save();

            $data=[];
            $data['deposit_id']=$RequestedFund->id;
            $data['deposit_for']=$RequestedFund->client_id;
            $data['activity']='updated deposit request';
            $data['user_id']=$RequestedFund->client_id;
            $data['client_id']=$RequestedFund->client_id;
            $data['type']='deposit';
            $data['action']='update';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            session(['popup_success' => ' Deposit request updated']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }

    public function deleteRequestedFundEntry(Request $request)
    {
        
        $validator = \Validator::make($request->all(), [
            'fund_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $getRequestedFund=RequestedFund::find($request->fund_id);
            RequestedFund::where('id',$request->fund_id)->delete();
            
            $data=[];
            $data['deposit_id']=$getRequestedFund->id;
            $data['deposit_for']=$getRequestedFund->client_id;
            $data['user_id']=Auth::User()->id;
            $data['activity']='deleted deposit request ';
            $data['type']='fundrequest';
            $data['action']='delete';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            session(['popup_success' => 'Request #R-'.sprintf('%05d',$getRequestedFund->id).' deleted successfully']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }

    public function sendFundReminder(Request $request)
    {
        $id=$request->id;
        $RequestedFund=RequestedFund::find($id);
        $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($RequestedFund->client_id);
        
        return view('client_dashboard.billing.sendReminderPopup',compact('RequestedFund','userData'));     
        exit;    
    } 

    public function saveSendFundReminder(Request $request)
    {
     
        $validator = \Validator::make($request->all(), [
            'client' => 'required|array|min:1'
        ],
        ['min'=>'No users selected',
        'required'=>'No users selected']);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $RequestedFund=RequestedFund::find($request->id);
            $RequestedFund->last_reminder_sent_on=date('Y-m-d h:i:s');
            $c=$RequestedFund->reminder_sent_counter;
            $RequestedFund->reminder_sent_counter=$c+1;
            $RequestedFund->save();

            $data=[];
            $data['deposit_id']=$RequestedFund->id;
            $data['deposit_for']=$RequestedFund->client_id;
            $data['user_id']=Auth::User()->id;
            $data['activity']='sent a reminder for deposit request';
            $data['type']='deposit';
            $data['action']='share';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            $RequestedFundDueDate = ($RequestedFund->due_date) ? date('Y-m-d', strtotime(convertUTCToUserDate($RequestedFund->due_date, auth()->user()->user_timezone))) : '';            
            $firmData=Firm::find(Auth::User()->firm_name);
            $getTemplateData = EmailTemplate::find(17);
            $mail_body = $getTemplateData->content;
            $mail_body = str_replace('{message}', ($RequestedFundDueDate) ? date('F d, Y',strtotime($RequestedFundDueDate)) : '', $mail_body);
            $mail_body = str_replace('{amount}', number_format($RequestedFund->amount_due,2), $mail_body);
            $mail_body = str_replace('{duedate}', ($RequestedFundDueDate) ? date('m/d/Y',strtotime($RequestedFundDueDate)) : '', $mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
            $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
            $mail_body = str_replace('{year}', date('Y'), $mail_body);             
            $mail_body = str_replace('{request_url}', route('client/bills/request/detail', base64_encode($RequestedFund->id)), $mail_body);   

            $clientData=User::find($RequestedFund->client_id);
            $user = [
                "from" => FROM_EMAIL,
                "from_title" => FROM_EMAIL_TITLE,
                "subject" => "Reminder: Request #R-".sprintf('%05d', $RequestedFund->id)." is due ".date('F d, Y',strtotime($RequestedFundDueDate))." for ".$firmData->firm_name,
                "to" => $clientData->email,
                "full_name" => "",
                "mail_body" => $mail_body
            ];
            $sendEmail = $this->sendMail($user);
            if($sendEmail==0){
                session(['popup_error' => 'Reminders could not sent']);
            }else{
                session(['popup_success' => 'Reminders have been sent']);
            }
            return response()->json(['errors'=>'']);
            exit;   
        }
    }

    public function addNewMessagePopup(Request $request)
    {

        //For company List
        // $CaseMasterCompanyList = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        // foreach($CaseMasterCompanyList as $k=>$v){
        //     $compnayIdWithEnablePortal = DB::table("users_additional_info")
        //     ->select("id")
        //     ->whereRaw("find_in_set($v->id,`multiple_compnay_id`)")
        //     ->where("client_portal_enable",1)
        //     ->get()
        //     ->pluck("id");
        // }
        // $CaseMasterCompany = User::select("first_name","last_name","id","user_level")
        // ->whereIn("id",$CaseMasterCompanyList)
        // ->get();


        //For company list
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level","user_status");
        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $CaseMasterCompany = $CaseMasterCompany->whereIn("parent_user",$getChildUsers);              
        }else{
            $CaseMasterCompany = $CaseMasterCompany->where("parent_user",Auth::user()->id); //Logged in user not visible in grid
        }
        $CaseMasterCompany=$CaseMasterCompany->where('user_level',"4")->whereIn('user_status',["1","2"])->get();
        
        //Get client list with client enable portal is active
         $user = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')->leftJoin('client_group','client_group.id','=','users_additional_info.contact_group_id')->select('users.*',DB::raw('CONCAT_WS(" ",first_name,last_name) as name'),'users_additional_info.contact_group_id','client_group.group_name',"users.id as id");
        $user = $user->where("user_level","2"); //Load all client 

        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $user = $user->whereIn("parent_user",$getChildUsers);
        }else{
            $user = $user->where("parent_user",Auth::user()->id); //Logged in user not visible in grid
        }
        $user = $user->whereIn("users.user_status",[1,2]);
        $clientLists=$user->get();

        //Get firm user list 
        $loadFirmUser = firmUserList();
        
        //Get all active case list with client portal enabled.
        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $case = $case->whereIn("case_master.created_by",$getChildUsers);
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $case = $case->whereIn("case_master.id",$childUSersCase);
        }
        $case = $case->where("case_close_date", NULL);
        $case = $case->where("case_master.is_entry_done","1");
        $CaseMasterData = $case->get();
        return view('client_dashboard.sendMessage',compact('CaseMasterData','CaseMasterCompany','loadFirmUser','clientLists'));     
        exit;
    }
    // public function checkBeforProceed(Request $request)
    // {
    //     $returnMsg='';
    //     $v=json_decode($request->selections);
    //     $lastElement=end($v);
    //     $TypeAndId= explode("-",$lastElement->id);
    //     if($TypeAndId[0]=='case'){
    //         $case_id=$TypeAndId[1];
    //         $caseCllientSelection = CaseClientSelection::join('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select("users_additional_info.id","users_additional_info.client_portal_enable","users_additional_info.user_id")->where("case_client_selection.case_id",$case_id)->get();
    //         $canAdd="no";
    //         foreach($caseCllientSelection as $k=>$v){
    //             if($v->client_portal_enable=="yes"){
    //                 $canAdd="yes"; 
    //             }
    //         }
    //         if($canAdd=="no"){
    //             $returnMsg="The court case  you selected had no contacts that can receive messages";

    //         }
    //     }
    //     return response()->json(['errors'=>'','msg'=>$returnMsg]);
    //     exit;   
    // }
    public function checkBeforProceed(Request $request)
    {
        $returnMsg='';
        $id=$request->userid;
        $type= $request->type;
        if($type=='case'){
            $case_id=$request->id;
            $caseCllientSelection = CaseClientSelection::join('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select("users_additional_info.id","users_additional_info.client_portal_enable","users_additional_info.user_id")->where("case_client_selection.case_id",$case_id)->get();
            $canAdd="no";
            foreach($caseCllientSelection as $k=>$v){
                if($v->client_portal_enable=="yes"){
                    $canAdd="yes"; 
                }
            }
            if($canAdd=="no"){
                $returnMsg="The court case  you selected had no contacts that can receive messages";
            }
        }
        return response()->json(['errors'=>'','msg'=>$returnMsg]);
        exit;   
    }
    public function searchValue(Request $request)
    {
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level")
        ->where('user_level',4)
        ->where("parent_user",Auth::user()->id)
        ->Where('first_name', 'like', '%' . $request->search . '%')
        ->get();

        $loadFirmUser = User::select("first_name","last_name","id",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as fullname'))
        ->where("parent_user",Auth::user()->id)
        ->where("user_level","3")
        ->Where(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', '%' . $request->search . '%')
        ->get();

        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->Where('case_title', 'like', '%' . $request->search . '%')->get();
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->where('case_title', 'like', '%' . $request->search . '%')->get();
        }

        return view('client_dashboard.searchVal',compact('CaseMasterData','CaseMasterCompany','loadFirmUser'));    
    }
    public function sendNewMessageToUser(Request $request)
    {
        if(isset($request->send_global) && $request->send_global=="on"){
            $validator = \Validator::make($request->all(), [
                'message' => 'required',
            ], ['required'=>'No users selected']);
            if ($validator->fails())
            {
                return response()->json(['errors'=>$validator->errors()->all()]);
            }else{
                if(isset($request->message['global_lawyers'])){
                    //Get firm user list 
                    $loadFirmUser = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->get();
                    foreach($loadFirmUser as $k=>$v){
                        $Messages=new Messages;
                        $Messages->user_id=$v->id;
                        $Messages->replies_is='private';
                        $Messages->case_id=NUll;
                        $Messages->is_global = 1;
                        $Messages->subject=$request['subject'];
                        $Messages->message=substr(strip_tags($request->delta),0,50);
                        $Messages->created_by =Auth::User()->id;
                        $Messages->save();

                        $ReplyMessages=new ReplyMessages;
                        $ReplyMessages->message_id=$Messages->id;
                        $ReplyMessages->reply_message=$request['delta'];
                        $ReplyMessages->created_by =Auth::User()->id;
                        $ReplyMessages->save();

                        $this->sendMailGlobal($request->all(),$v->id, $Messages->id);
                    }            
                
                }
                if(isset($request->message['global_clients'])){
                    //Get client list with client enable portal is active
                    $clientLists = UsersAdditionalInfo::leftJoin('users','users_additional_info.user_id','=','users.id')
                    ->select("first_name","last_name","users.id","user_level","users_additional_info.client_portal_enable")
                    ->where("users_additional_info.client_portal_enable", "1")
                    ->where("users.user_level","2")
                    ->where("users.parent_user",Auth::user()->id)                    
                    ->get();
                    
                    foreach($clientLists as $k=>$v){
                        if($v->client_portal_enable == '1'){
                            $Messages=new Messages;
                            $Messages->user_id=$v->id;
                            $Messages->replies_is='private';
                            $Messages->case_id=NUll;
                            $Messages->is_global = 1;
                            $Messages->subject=$request['subject'];
                            $Messages->message=substr(strip_tags($request->delta),0,50);
                            $Messages->created_by =Auth::User()->id;
                            $Messages->save();

                            $ReplyMessages=new ReplyMessages;
                            $ReplyMessages->message_id=$Messages->id;
                            $ReplyMessages->reply_message=$request['delta'];
                            $ReplyMessages->created_by =Auth::User()->id;
                            $ReplyMessages->save();

                            $this->sendMailGlobal($request->all(),$v->id, $Messages->id);
                        }
                    }
                    
                }
                session(['popup_success' => 'Your message has been sent']);
                return response()->json(['errors'=>'']);
                exit;  
            } 
        }else{
            $validator = \Validator::make($request->all(), [
                'send_to' => 'required|array|min:1',
                'case_link' => 'required',
            ], ['min'=>'No users selected']);
            if ($validator->fails())
            {
                return response()->json(['errors'=>$validator->errors()->all()]);
            }else{
                // dd($request->all());

                $error = [];
                foreach($request->send_to as $k=>$v){
                    $decideCode=explode("-",$v); 
                    if($decideCode[0]=='client'){     
                        $userInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
                        ->select('users.*',DB::raw('CONCAT_WS(" ",first_name,last_name) as name'),'users_additional_info.client_portal_enable')
                        ->where('users.id',$decideCode[1])->first();
                        if(!empty($userInfo)){
                            if(!$userInfo->client_portal_enable){
                                $error[$k] = "Please enable client portal for ".$userInfo->name;
                            }
                        }                        
                    }   
                }
                if(count($error)>0){
                    return response()->json(['errors'=>$error]);
                }else{
                $sendToClient = 0;
                foreach($request->send_to as $k=>$v){
                    $decideCode=explode("-",$v);                        
                   
                    if($decideCode[0]=='case'){
                        $caseCllientSelection = CaseClientSelection::select("case_client_selection.selected_user")
                        ->where("case_client_selection.case_id",$decideCode[1])
                        ->get()
                        ->pluck("selected_user");
                        
                        $compnayIdWithEnablePortal = DB::table("users_additional_info")
                        ->select("id")
                        ->whereIn("user_id",$caseCllientSelection)
                        ->where("client_portal_enable","1")
                        ->get()
                        ->pluck("id");;

                        $selectUser = [];
                        foreach($compnayIdWithEnablePortal as $k=>$v){
                            array_push($selectUser,$v);
                        }
                        $Messages=new Messages;
                        $Messages->user_id=implode(',',$selectUser);
                        if($request['message']['private_reply'] =="false"){
                            $Messages->replies_is='public';
                        }else{
                            $Messages->replies_is='private';
                        }
                        $Messages->case_id=$request['case_link'];
                        $Messages->subject=$request['subject'];
                        $Messages->message=substr(strip_tags($request->delta),0,50);
                        $Messages->created_by =Auth::User()->id;
                        $Messages->save();

                        $ReplyMessages=new ReplyMessages;
                        $ReplyMessages->message_id=$Messages->id;
                        $ReplyMessages->reply_message=$request['delta'];
                        $ReplyMessages->created_by =Auth::User()->id;
                        $ReplyMessages->save();

                        foreach($compnayIdWithEnablePortal as $k=>$v){  
                            $this->sendMailGlobal($request->all(),$v, $Messages->id);
                        }                    
                    }
                    
                    if($decideCode[0]=='company'){                                                
                        $userIdWithEnablePortal = DB::table("users_additional_info")
                        ->select("user_id")
                        ->where("multiple_compnay_id","like",'%'.$decideCode[1].'%')
                        ->where("client_portal_enable","1")
                        ->get()
                        ->pluck("user_id");

                        $selectUser = [];
                        foreach($userIdWithEnablePortal as $k=>$v){
                            array_push($selectUser,$v);
                        }
                        $Messages=new Messages;
                        $Messages->user_id=implode(',',$selectUser);
                        if($request['message']['private_reply'] =="false"){
                            $Messages->replies_is='public';
                        }else{
                            $Messages->replies_is='private';
                        }
                        $Messages->case_id=$request['case_link'];
                        $Messages->subject=$request['subject'];
                        $Messages->message=substr(strip_tags($request->delta),0,50);
                        $Messages->created_by =Auth::User()->id;
                        $Messages->save();

                        $ReplyMessages=new ReplyMessages;
                        $ReplyMessages->message_id=$Messages->id;
                        $ReplyMessages->reply_message=$request['delta'];
                        $ReplyMessages->created_by =Auth::User()->id;
                        $ReplyMessages->save();

                        foreach($userIdWithEnablePortal as $k=>$v){
                            $this->sendMailGlobal($request->all(),$v,$Messages->id);
                        }                    
                    }
                    
                    if($decideCode[0]=='client' || $decideCode[0]=='staff'){
                        $sendToClient = $sendToClient + 1; 
                    }
                }}
                if($sendToClient > 0){                    
                    $Messages=new Messages;
                    $Messages->user_id=str_replace(['client-','staff-'],'',implode(',',$request->send_to));
                    if($request['message']['private_reply'] =="false"){
                        $Messages->replies_is='public';
                    }else{
                        $Messages->replies_is='private';
                    }
                    $Messages->case_id=$request['case_link'];
                    $Messages->subject=$request['subject'];
                    $Messages->message=substr(strip_tags($request->delta),0,50);
                    $Messages->created_by =Auth::User()->id;
                    $Messages->save();

                    $ReplyMessages=new ReplyMessages;
                    $ReplyMessages->message_id=$Messages->id;
                    $ReplyMessages->reply_message=$request['delta'];
                    $ReplyMessages->created_by =Auth::User()->id;
                    $ReplyMessages->save();

                    foreach($request->send_to as $k=>$v){     
                        $decideCode=explode("-",$v);
                        $this->sendMailGlobal($request->all(),$decideCode[1],$Messages->id);
                    }  
                }
                session(['popup_success' => 'Your message has been sent']);
                return response()->json(['errors'=>'']);
                exit;       
            }
           
        }
    }
    public function replyMessageToUserCase(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'selected_user_id' => 'required',
            'selected_case_id' => 'required'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            // dd($request->delta);
            $ReplyMessages=new ReplyMessages;
            $ReplyMessages->message_id=$request->message_id;
            $ReplyMessages->reply_message=$request->delta;
            $ReplyMessages->created_by =Auth::User()->id;
            $ReplyMessages->save();

            $Messages=Messages::find($request->message_id);
            $Messages->message=substr(strip_tags($request->delta),0,50);
            $Messages->save();

            $userlist = explode(",",$request->selected_user_id);
            foreach ($userlist as $k=>$v){
                $this->sendMailGlobal($request->all(),$v,$request->message_id);
            }
            session(['popup_success' => 'Your message has been sent']);
            return response()->json(['errors'=>'']);
            exit;       
        }
           
    }

    public function archiveMessageToUserCase(Request $request){
        $Messages=Messages::find($request->message_id);
        $Messages->is_archive = "1";
        $Messages->save();
        return response()->json(['errors'=>'']);
    }

    public function unarchiveMessageToUserCase(Request $request){
        $Messages=Messages::find($request->message_id);
        $Messages->is_archive = 0;
        $Messages->save();
        return response()->json(['errors'=>'']);
    }

    public function messageInfo(Request $request){
        $messagesData = Messages::leftJoin("case_master","case_master.id","=","messages.case_id")
        ->select('messages.*',DB::raw("DATE_FORMAT(messages.updated_at,'%d %M %H:%i %p') as last_post"),"case_master.case_title","case_master.case_unique_number")
        ->where('messages.id', $request->id)
        ->first();

        $messageList = ReplyMessages::leftJoin("messages","reply_messages.message_id","=","messages.id")
        ->select('reply_messages.*',DB::raw("DATE_FORMAT(messages.updated_at,'%d %M %H:%i %p') as last_post"))
        ->where('reply_messages.message_id', $request->id)
        ->get();
    
        $clientList = [];    
        $userlist = explode(',', $messagesData->user_id);
        foreach ($userlist as $key => $value) {
            $userInfo =  User::where('id',$value)->select('first_name','last_name','user_level')->first();
            $clientList[$value] = $userInfo['first_name'].' '.$userInfo['last_name'].'|'.$userInfo['user_level'];
        }

        return view('communications.messages.viewMessage',compact('messagesData','messageList','clientList'));            
    }

    public function sendMailGlobal($request,$id, $messageID)
    {
        $firmData=Firm::find(Auth::User()->firm_name);
        $getTemplateData = EmailTemplate::find(11);
        $mail_body = $getTemplateData->content;
        $senderName=Auth::User()->first_name." ".Auth::User()->last_name;
        $mail_body = str_replace('{sender}', $senderName, $mail_body);
        $mail_body = str_replace('{subject}', $request['subject'], $mail_body);
        $mail_body = str_replace('{loginurl}', BASE_URL.'login', $mail_body);
        $mail_body = str_replace('{url}', BASE_URL.'messages/'.$messageID.'/info', $mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
        $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        

        $clientData=User::find($id);
            if(isset($clientData->email)){
            $user = [
                "from" => FROM_EMAIL,
                // "from_title" => FROM_EMAIL_TITLE,
                "from_title" => $firmData->firm_name,
                "replyto"=>DO_NOT_REPLAY_FROM_EMAIL,
                "replyto_title"=>DO_NOT_REPLAY_FROM_EMAIL_TITLE,
                "subject" => "You have a new message from ".$firmData->firm_name,
                "to" => $clientData->email,
                "full_name" => "",
                "mail_body" => $mail_body
            ];
            $sendEmail = $this->sendMail($user);
        }
        return true;
    }
    public function loadStep1(Request $request)
    {
        $case_id=($request->case_id)??NULL;
        $country = Countries::get();
        return view('contract.loadStep1',compact('country','case_id'));
    }
    public function changeAccess(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users,email,NULL,id,deleted_at,NULL',
            'user_type' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user = new User;
            $user->first_name=$request->first_name;
            $user->last_name=$request->last_name;
            $user->email=$request->email;
            if(isset($request->middle_name)){ $user->middle_name=trim($request->middle_name); }
            if(isset($request->street)) { $user->street=trim($request->street); }
            if(isset($request->apt_unit)) { $user->apt_unit=trim($request->apt_unit); }
            if(isset($request->city)) { $user->city=trim($request->city); }
            if(isset($request->state)) { $user->state=trim($request->state); }
            if(isset($request->postal_code)) { $user->postal_code=trim($request->postal_code); }
            if(isset($request->country)) { $user->country=trim($request->country); }
            if(isset($request->home_phone)) { $user->home_phone=trim($request->home_phone); }
            if(isset($request->work_phone)) { $user->work_phone=trim($request->work_phone); }
            if(isset($request->cell_phone)) { $user->mobile_number=trim($request->cell_phone); }
            if($request->user_title==''){
                if($request->user_type=="1") { 
                    $user->user_title='Attorney';
                 }else if($request->user_type=="2") 
                 { 
                    $user->user_title='Paralegal'; 
                }else{
                    $user->user_title='Staff';
                }
                
            }else{
                $user->user_title=$request->user_title;
            }
            if(isset($request->default_rate)) { $user->default_rate=trim(str_replace(",","",$request->default_rate)); }
            if(isset($request->user_type)) { $user->user_type=trim($request->user_type); }
            $user->firm_name=Auth::User()->firm_name;
            $user->token  = Str::random(40);
            $user->parent_user =Auth::User()->id;
            $user->user_status  = "2";  // Default status is inactive once verified account it will activated.
            $user->created_by =Auth::User()->id;
            // print_r($user);exit;
            $user->save();

            //If case id is exist then only save in to staff table.
            if(isset($request->case_id) && $request->case_id!=''){
                $CaseStaff = new CaseStaff;
                $CaseStaff->case_id=$request->case_id; 
                $CaseStaff->user_id=$user->id; 
                $CaseStaff->created_by=Auth::user()->id; 
                $CaseStaff->lead_attorney=NULL;
                $CaseStaff->originating_attorney=NULL;
                $CaseStaff->rate_type="0";
                $CaseStaff->rate_amount="0.00";
                $CaseStaff->save();
            }
            
            $getTemplateData = EmailTemplate::find(6);
            $fullName=$request->first_name. ' ' .$request->last_name;
            $email=$request->email;
            $token=url('user/verify', $user->token);
            $mail_body = $getTemplateData->content;
            $mail_body = str_replace('{name}', $fullName, $mail_body);
            $mail_body = str_replace('{email}', $email,$mail_body);
            $mail_body = str_replace('{token}', $token,$mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
            $mail_body = str_replace('{regards}', REGARDS, $mail_body);  
            $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
            $mail_body = str_replace('{refuser}', Auth::User()->first_name, $mail_body);                          
            $mail_body = str_replace('{year}', date('Y'), $mail_body);        
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);       
            $refUser = Auth::User()->first_name . " ". Auth::User()->last_name;
            $userEmail = [
                "from" => FROM_EMAIL,
                "from_title" => FROM_EMAIL_TITLE,
                "subject" => $refUser." ".$getTemplateData->subject. " ". TITLE,
                "to" => $request->email,
                "full_name" => $fullName,
                "mail_body" => $mail_body
                ];
        //   $sendEmail = $this->sendMail($userEmail);
        //   if($sendEmail=="1"){
        //     $user->is_sent_welcome_email  = "1";  // Welcome email sent to user.
        //     $user->save();
        //   }
            return response()->json(['errors'=>'','user_id'=>$user->id]);
          exit;
        }
    }
    public function archiveContactForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'contact_id'=>'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user=User::find($request->contact_id);
            $user->user_status=4;  /* 4=Archive */
            $user->save();

            if(isset($request->disabledLogin)){
                UsersAdditionalInfo::where('user_id',$request->contact_id)->update(['client_portal_enable'=>"0"]);
            }
            $data=[];
            $data['user_id']=$request->contact_id;
            $data['client_id']=$request->contact_id;
            $data['activity']='archived contact';
            $data['type']='contact';
            $data['action']='archive';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
            session(['popup_success' => 'Contact have been archived.']);
            return response()->json(['errors'=>'','contact_id'=>$request->contact_id]);
          exit;
        }
    }
    public function unarchiveContactForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'contact_id'=>'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user=User::find($request->contact_id);
            $user->user_status=1;  /* 4=Archive */
            $user->save();

            if(isset($request->disabledLogin)){
                UsersAdditionalInfo::where('user_id',$request->contact_id)->update(['client_portal_enable'=>"1"]);
                
                $token = bin2hex(openssl_random_pseudo_bytes(78));;
                User::where('id',$request->contact_id)->update(['token'=>$token]);
                $user =  User::where(["id" => $request->contact_id])->first();
                $getTemplateData = EmailTemplate::find(9);
                $fullName=$user->first_name. ' ' .$user->last_name;
                $email=$user->email;
                // $email="testing.testuser6@gmail.com";
                $firmData=Firm::find($user->firm_name);
                // echo $decrypted = Crypt::decryptString($encrypted);
                // $token=BASE_URL.'activate_account/web_token?='.$user->token."&security_patch=".Crypt::encryptString($email);
                $token= route("client/activate/account", $user->token)."?security_patch=".Crypt::encryptString($email);
                $mail_body = $getTemplateData->content;
                $mail_body = str_replace('{name}', $fullName, $mail_body);
                $mail_body = str_replace('{firm}', $firmData['firm_name'], $mail_body);
                $mail_body = str_replace('{email}', $email,$mail_body);
                $mail_body = str_replace('{token}', $token,$mail_body);
                $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
                $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
                $mail_body = str_replace('{regards}', REGARDS, $mail_body);  
                $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
                $mail_body = str_replace('{refuser}', Auth::User()->first_name, $mail_body);                          
                $mail_body = str_replace('{phone_number}', '', $mail_body);                          
                $mail_body = str_replace('{year}', date('Y'), $mail_body);        
                $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);       

                $userEmail = [
                    "from" => FROM_EMAIL,
                    "from_title" => FROM_EMAIL_TITLE,
                    "subject" => $getTemplateData->subject ." ".$firmData['firm_name'],
                    "to" => $user->email,
                    "full_name" => $fullName,
                    "mail_body" => $mail_body
                ];
                $sendEmail = $this->sendMail($userEmail);
            }

            $data=[];
            $data['user_id']=$request->contact_id;
            $data['client_id']=$request->contact_id;
            $data['activity']='unarchived contact';
            $data['type']='contact';
            $data['action']='unarchive';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            session(['popup_success' => 'Contact have been unarchived.']);
            return response()->json(['errors'=>'','contact_id'=>$request->contact_id]);
          exit;
        }
    }
    
    public function deleteContactForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'contact_id'=>'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user=User::where("id",$request->contact_id)->delete();
            $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$request->contact_id)->delete();
            $CaseClientSelection=CaseClientSelection::where("selected_user",$request->contact_id)->delete();
            session(['popup_success' => 'Contact have been deleted.']);
            return response()->json(['errors'=>'','contact_id'=>$request->contact_id]);
          exit;
        }
    }

    public function imageUploadSync(Request $request)
    {
       
        $validator = \Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ],['file.required'=>"Please select image"]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            if ($files = $request->file('file')) {
                
                $User= User::find($request->user_id);

                if($User->profile_image!=NULL && file_exists(public_path()."/profile/".$User->profile_image)){
                    $path = public_path()."/profile/".$User->profile_image;
                    unlink($path);
                }

                $destinationPath = public_path('/profile/'); 
                $profileImage = $request->user_id."_profile" . "." . $files->getClientOriginalExtension();
                $files->move($destinationPath, $profileImage);
          
                 // Save In Database
                $User->profile_image="$profileImage";
                $User->is_published="no";
                $User->save();
            }
            return response()->json(['errors'=>'','contact_id'=>$request->user_id]);
            exit;
        }
    }
    public function cropImageSync(Request $request)
    {
        $image='';
        $User= User::find($request->user_id);
        if($User->profile_image!=NULL){
            $image=BASE_URL.'profile/'.$User->profile_image."?t=".microtime();
        }
        return response()->json(['errors'=>'','image'=>$image]);
        exit;
    }

    public function deleteProfileImageForm(Request $request)
    {
        $User= User::find($request->user_id);
        $path = BASE_URL."public/profile/".$User->profile_image;
        if(file_exists($path)){
            unlink($path);
        }
        $User->profile_image=NULL;
        $User->is_published="no";
        $User->save();
        session(['popup_success' => 'Picture was removed']);
        return response()->json(['errors'=>'','contact_id'=>$request->user_id]);
        exit;
    } 
    public function submitAndSaveImageForm(Request $request)
    {
        $User= User::find($request->user_id);
        $path = BASE_URL."public/profile/".$User->profile_image;
        if(file_exists($path)){
            unlink($path);
        }
        $image = $request->imageCode;
        $img = explode(',', $request->imageCode);
        $ini =substr($img[0], 11);
        $type = explode(';', $ini);
        $img = str_replace('data:image/'.$type[0].';base64,', '', $image);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $file = $request->user_id."_".time()."_profile" . "." . $type[0];
        $destinationPath = public_path('/profile/'); 
        $success = file_put_contents($destinationPath."/".$file, $data);

        $User->profile_image=$file;
        $User->is_published="yes";
        $User->save();
        session(['popup_success' => 'Profile picture saved']);
        return response()->json(['errors'=>'','contact_id'=>$request->user_id]);
        exit;
    }
    public function updateProfileImageForm(Request $request)
    {
        $User= User::find($request->user_id);
        $User->is_published="yes";
        $User->save();
        session(['popup_success' => 'Profile picture saved']);
        return response()->json(['errors'=>'','contact_id'=>$request->user_id]);
        exit;
    } 

    /************************ Import Contacts**************************/
    public function imports_contacts(Request $request)
    {
        return view('import.import_export');
    } 
    
    public function downloadFormat(Request $request)
    {
        if($request->section=="contact"){
            $filename = asset('import/Case_Contact_Import_Template.csv');
        }elseif($request->section=="cases"){
            $filename = asset('import/Legalcase_Case_Import_Template.csv');
        }else{
             $filename =asset('import/Case_Company_Import_Template.csv');
        }
        return response()->json(['errors'=>'','url'=>$filename]);
    }

    public function createAndImports(Request $request)
    {
        File::deleteDirectory(public_path('import/'.date('Y-m-d').'/'.Auth::User()->firm_name));
        if(!is_dir(public_path("import/".date('Y-m-d').'/'.Auth::User()->firm_name))) {
            File::makeDirectory(public_path("import/".date('Y-m-d').'/'.Auth::User()->firm_name), $mode = 0777, true, true);
        }
        if($request->format=="vcard"){
            $this->generateContactvCard($request->all());
            $CSV[] = public_path('import/'.date('Y-m-d').'/'.Auth::User()->firm_name."/contacts.vcf");

            if($request->include_companies=="1"){
                $this->generateCompanyvCard($request->all());
                $CSV[] = public_path('import/'.date('Y-m-d').'/'.Auth::User()->firm_name."/companies.vcf");
            }
        }  
        if($request->format=="outlook_csv" || $request->format=="mycase_csv"){
            $this->generateClientCSV($request->all());
            $CSV[] = public_path('import/'.date('Y-m-d').'/'.Auth::User()->firm_name."/contact.csv");
    
            if($request->include_companies=="1"){
                $this->generateCompanyCSV($request->all());
                $CSV[] = public_path('import/'.date('Y-m-d').'/'.Auth::User()->firm_name."/companies.csv");
            }
        }  
        $zip = new ZipArchive;
        $storage_path = '/import/'.date('Y-m-d').'/'.Auth::User()->firm_name;
        $firmData=Firm::find(Auth::User()->firm_name);
        $timeName = str_replace(" ","_",$firmData->firm_name)."-".Auth::User()->id."-contacts-".date("m-d-Y");
        $zipFileName = $storage_path . '/' . $timeName . '.zip';
        
        $zipPath = asset($zipFileName);
        if ($zip->open((public_path($zipFileName)), ZipArchive::CREATE) === true) {
            foreach ($CSV as $relativName) {
                $zip->addFile($relativName,basename($relativName));
            }
            $zip->close();
            if ($zip->open(public_path($zipFileName)) === true) {
                $Path= $zipPath;
            } else {
                $Path="";
            }
        }
        return response()->json(['errors'=>'','url'=>$Path,'msg'=>" Building File... it will downloading automaticaly"]);
        exit;
    } 
    
    public function getCompanyList($ids){
        $DyncamicList=explode(",",$ids);
        // return $getCompanyList=User::select("first_name")->whereIn("id",$DyncamicList)->get()->pluck('first_name');
        $getCompanyList=User::select("first_name")->whereIn("id",$DyncamicList)->first();
        if(!empty($getCompanyList)){
            return $getCompanyList['first_name'];
        }else{
            return "";
        }
    }

    public function getCompanyLists($ids){
        $DyncamicList=explode(",",$ids);
        return  User::select("first_name")->whereIn("id",$DyncamicList)->get()->pluck('first_name');
    }

    public function getCountryName($cid){
        $NameOfCountry=Countries::select("name")->where("id",$cid)->first();
        if(!empty($NameOfCountry)){
            return $NameOfCountry['name'];
        }else{
            return "";
        }
        
    }

    public function getContactGroup($id){
        $name=ClientGroup::select("group_name")->where("id",$id)->first();
        if(!empty($name)){
            return $name['group_name'];
        }else{
            return "";
        }
    }

    public function getLastCase($client_id){
        $CaseClientSelection =CaseClientSelection::select("case_id")->where("selected_user",$client_id)->orderBy("created_at","DESC")->first();
        if(!empty($CaseClientSelection)){
            $caseMaster=CaseMaster::select("case_title","case_unique_number")->where("id",$CaseClientSelection['case_id'])->first();
            return $caseMaster;
        }else{
            return "";
        }
    }
    public function getCompanyContact($company_id){
        return User::select("*")->join('users_additional_info',"users_additional_info.user_id","=",'users.id')
        ->select("users.id as cid","users.first_name","users.last_name",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as fullname'))
        ->whereRaw("find_in_set($company_id,`multiple_compnay_id`)")
        ->get()->pluck("fullname");

    }
    public function generateClientCSV($request){
        $clientCsvData=[];
        $clientHeader=config('app.name')." ID|First Name|Middle Name|Last Name|Company|Job Title|Home Street|Home Street 2|Home City|Home State|Home Postal Code|Home Country/Region|Home Fax|Work Phone|Home Phone|Mobile Phone|Contact Group|E-mail Address|Web Page|Outstanding Trust Balance|Login Enabled|Archived|Birthday|Private Notes|License Number|License State|Welcome Message|:Notes|Cases|Case Link IDs|Created Date";
        $clientCsvData[]=$clientHeader;

        $user = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')->leftJoin('client_group','client_group.id','=','users_additional_info.contact_group_id')->select('users.*',DB::raw('CONCAT_WS(" ",first_name,last_name) as name'),'users_additional_info.contact_group_id','client_group.group_name',"users.id as uid","users_additional_info.*");
        $user = $user->where("user_level","2");  
        $user = $user->where("parent_user",Auth::user()->id);
        if(isset($request['include_archived']) && $request['include_archived']=="1"){
            $user = $user->whereIn("users.user_status",[1,2,4]); 
        }else{
            $user = $user->whereIn("users.user_status",[1,2]); 
        }
        $user = $user->get();
        foreach($user as $clientKey=>$clientVal){
            $getCompanyName = '';
            if(!empty(explode(",",$clientVal->multiple_compnay_id))){
                $getCompanyList=$this->getCompanyLists($clientVal->multiple_compnay_id);
                if(count($getCompanyList) > 0){
                    $companyCount =  count($getCompanyList);
                    $getCompanyName .= ($companyCount > 0) ? $getCompanyList[$companyCount-1] : $getCompanyList[$companyCount];
                }
           }
           $countryName='';
           if($clientVal->country !=NULL){
            $countryName=$this->getCountryName($clientVal->country);
           }  
           
           $contactGroup='';
           if($clientVal->contact_group_id !=NULL){
            $contactGroup=$this->getContactGroup($clientVal->contact_group_id);
           }
           
           if($clientVal->client_portal_enable=="0"){
               $Portal="FALSE";
           }else{
                $Portal="TRUE";
           }
           if($clientVal->user_status=="4"){
                $Archive="TRUE";
            }else{
                $Archive="FALSE";
            }
            if($clientVal->dob!=NULL){
                $DOB=date('m/d/Y',strtotime($clientVal->dob));
            }else{
                $DOB="";
            }     
            $welcomeMsg='';
            $notes='';
            $caseData=$this->getLastCase($clientVal->uid);
            if(!empty($caseData)){
                $cases=$caseData['case_title'];
                $casesLinkId=$caseData['case_unique_number'];
            }else{
                $cases='';
                $casesLinkId='';
            }
            $webpage=$clientVal->website;
            $createdAt=date('m/d/Y',strtotime($clientVal->created_at));
            $clientCsvData[]=$clientVal->uid."|".$clientVal->first_name."|".$clientVal->middle_name."|".$clientVal->last_name."|".$getCompanyName."|".$clientVal->job_title."|".$clientVal->street." " .$clientVal->apt_unit."|".$clientVal->address2."|".$clientVal->city."|".$clientVal->state."|".$clientVal->postal_code."|".$countryName."|".$clientVal->fax_number."|".$clientVal->work_phone."|".$clientVal->home_phone."|".$clientVal->mobile_number."|".$contactGroup."|".$clientVal->email."|".$webpage."|0"."|".$Portal."|".$Archive."|".$DOB."|".$clientVal->notes."|".$clientVal->driver_license."|".$clientVal->license_state."|".$welcomeMsg."|".$notes."|".$cases."|".$casesLinkId."|".$createdAt;
        }
        // print_r($clientCsvData);
        // exit;
        
        $folderPath = public_path('import/'.date('Y-m-d').'/'.Auth::User()->firm_name);
        if(!File::isDirectory($folderPath)){
            File::makeDirectory($folderPath, 0777, true, true);    
        }
        $file_path =  $folderPath.'/contact.csv';  
        $file = fopen($file_path,"w+");
        foreach ($clientCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 
        return true; 
    }

    public function generateCompanyCSV($request){
        $CompanyCsvData=[];
        $clientHeader=config('app.name')." ID|Company|Business Street|Business Street 2|Business City|Business State|Business Postal Code|Business Country/Region|Business Fax|Company Main Phone|E-mail Address|Web Page|Outstanding Trust Balance|Archived|Private Notes|Contacts|Cases|Case Link IDs|:Notes|Created Date";
        $CompanyCsvData[]=$clientHeader;

        $user = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')->leftJoin('client_group','client_group.id','=','users_additional_info.contact_group_id')->select('users.*',DB::raw('CONCAT_WS(" ",first_name,last_name) as name'),'users_additional_info.contact_group_id','client_group.group_name',"users.id as uid","users_additional_info.*",'users.created_at as uct');
        $user = $user->where("user_level","4");  
        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $user = $user->whereIn("parent_user",$getChildUsers);
        }else{
            $user = $user->where("parent_user",Auth::user()->id);
        }

        if(isset($request['include_archived']) && $request['include_archived']=="1"){
            // $user = $user->where("users.user_status","4");  
            $user = $user->whereIn("users.user_status",[1,2,4]); 
        }else{
            // $user = $user->where("users.user_status","1"); 
            $user = $user->whereIn("users.user_status",[1,2]); 
        }
        // dd($user->toSql());
        $user = $user->get();
        // print_r($user);exit;
        foreach($user as $clientKey=>$clientVal){
            $countryName='';
            if($clientVal->country !=NULL){
                $countryName=$this->getCountryName($clientVal->country);
            }  
            $contactGroup='';
            if($clientVal->contact_group_id !=NULL){
                $contactGroup=$this->getContactGroup($clientVal->contact_group_id);
            }
            if($clientVal->user_status=="4"){
                $Archive="TRUE";
            }else{
                $Archive="FALSE";
            }
            $notes='';
            $caseData=$this->getLastCase($clientVal->uid);
            if(!empty($caseData)){
                $cases=$caseData['case_title'];
                $casesLinkId=$caseData['case_unique_number'];
            }else{
                $cases='';
                $casesLinkId='';
            }
            $contactList=$this->getCompanyContact($clientVal->uid);
            if(!($contactList->isEmpty())){
                $contacts=str_replace(['[',']','"'],"",$contactList);
            }else{
                $contacts='';
            }
            $createdAt=date('m/d/Y',strtotime($clientVal->uct));
            $CompanyCsvData[]=$clientVal->uid."|".$clientVal->first_name."|".$clientVal->street." " .$clientVal->apt_unit."|".$clientVal->address2."|".$clientVal->city."|".$clientVal->state."|".$clientVal->postal_code."|".$countryName."|".$clientVal->fax_number."|".$clientVal->mobile_number."|".$clientVal->email."|".$clientVal->website."|0"."|".$Archive."|".$clientVal->notes."|".$contacts."|".$cases."|".$casesLinkId."|".$notes."|".$createdAt;
        }
       
        // $file_path=public_path().'/import/'.date('Y-m-d').'/'.Auth::User()->firm_name."/".$filename;   
        $folderPath = public_path('import/'.date('Y-m-d').'/'.Auth::User()->firm_name);
        if(!File::isDirectory($folderPath)){
            File::makeDirectory($folderPath, 0777, true, true);    
        }
        $file_path =  $folderPath.'/companies.csv'; 
        $file = fopen($file_path,"w+");
        foreach ($CompanyCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file);  
    }

    public function generateContactvCard($request){
      
        $user = User::select("*")->where("user_level","2")->where("parent_user",Auth::user()->id);
        if(isset($request['include_archived']) && $request['include_archived']=="1"){
            $user = $user->orWhere("users.user_status","4");  
        }
        $user = $user->where("users.user_status","1"); 
        $user = $user->get();
        $vCard = '';
        foreach($user as $k=>$v){
            if($v->country!=NULL){
                $countryName=$this->getCountryName($v->country);
            }else{
                $countryName="";
            }
            $vCard .= "BEGIN:VCARD\r\n";
            $vCard .= "VERSION:3.0\r\n";
            $vCard .= "N:".$v->last_name.";".$v->first_name.";".$v->middle_name.";\r\n";
            $vCard .= "FN:".$v->first_name." ".$v->middle_name." ".$v->last_name."\r\n";
            $vCard .= "ADR:TYPE=work,pref:".$v->street.";".$v->apt_unit.";".$v->city.";".$v->state.";".$v->postal_code.";".$countryName.";\r\n";
            $vCard .= "EMAIL;TYPE=work,pref:".$v->email."\r\n";
            $vCard .= "TEL;TYPE=work,voice:".$v->mobile_number."\r\n"; 
            $vCard .= "END:VCARD\r\n";

        }

        // $filePath = '/import/'.date('Y-m-d').'/'.Auth::User()->firm_name."/contacts.vcf"; // you can specify path here where you want to store file.
        $folderPath = public_path('import/'.date('Y-m-d').'/'.Auth::User()->firm_name);
        if(!File::isDirectory($folderPath)){
            File::makeDirectory($folderPath, 0777, true, true);    
        }
        $file_path =  $folderPath.'/contacts.vcf'; 
        $file = fopen($file_path,"w");
        fwrite($file,$vCard);
        fclose($file);

    }

    public function generateCompanyvCard($request){
      
        $user = User::select("*")->where("user_level","4")->where("parent_user",Auth::user()->id);
        if(isset($request['include_archived']) && $request['include_archived']=="1"){
            $user = $user->orWhere("users.user_status","4"); 
        }
        $user = $user->whereIn("users.user_status",["1","2"]);
        $user = $user->get();
        $vCard = '';
        foreach($user as $k=>$v){
            if($v->country!=NULL){
                $countryName=$this->getCountryName($v->country);
            }else{
                $countryName="";
            }
            $vCard .= "BEGIN:VCARD\r\n";
            $vCard .= "VERSION:3.0\r\n";
            $vCard .= "N:;\r\n";
            $vCard .= "FN:;\r\n";
            $vCard .= "ORG:".$v->first_name."\r\n";
            $vCard .= "ADR:TYPE=work,pref:".$v->street.";".$v->apt_unit.";".$v->city.";".$v->state.";".$v->postal_code.";".$countryName.";\r\n";
            $vCard .= "EMAIL;TYPE=work,pref:".$v->email."\r\n";
            $vCard .= "TEL;TYPE=work,voice:".$v->mobile_number."\r\n"; 
            $vCard .= "END:VCARD\r\n";
        }
        // $filePath = '/import/'.date('Y-m-d').'/'.Auth::User()->firm_name."/companies.vcf"; // you can specify path here where you want to store file.
        $folderPath = public_path('import/'.date('Y-m-d').'/'.Auth::User()->firm_name);
        if(!File::isDirectory($folderPath)){
            File::makeDirectory($folderPath, 0777, true, true);    
        }
        $file_path =  $folderPath.'/companies.vcf'; 
        $file = fopen($file_path,"w");
        fwrite($file,$vCard);
        fclose($file);

    }

    public function importContacts(Request $request)
    {
        // print_r($request->all());exit;
        $validator = \Validator::make($request->all(), [
            'upload_file' => 'required|max:8192', //8 mb
        ],['upload_file.required'=>"Please select file"]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $uploadFile = $request->upload_file;
            $namewithextension = $uploadFile->getClientOriginalName(); 

            $ClientCompanyImport=new ClientCompanyImport;
            $ClientCompanyImport->file_name=$namewithextension;
            $ClientCompanyImport->total_record=0;
            $ClientCompanyImport->total_imported=0;
            $ClientCompanyImport->status="2";
            $ClientCompanyImport->firm_id=Auth::User()->firm_name;
            $ClientCompanyImport->created_by=Auth::User()->id;
            $ClientCompanyImport->save();
            $waringCount = 0;
            if($request->import_format=="csv"){
                $path = $request->file('upload_file')->getRealPath();
                $csv_data = array_map('str_getcsv', file($path));
                $ClientCompanyImport->file_type="2";
                $ClientCompanyImport->save(); 
                $UserArray=[];
                if(!empty($csv_data)){
                    if($csv_data[0][0]=="Legalcase ID" ){
                        $errorString='<ul><li>please remove Legalcase ID or 1st column from csv and try again.</li></ui>';
                        $ClientCompanyImport->error_code=$errorString;
                        $ClientCompanyImport->status=2;
                        $ClientCompanyImport->save();

                        return response()->json(['errors'=>$errorString,'contact_id'=>'']);
                        exit;
                    } else if($csv_data[0][0]=="first_name" || $csv_data[0][0]=="First Name" || $csv_data[0][0]=="Legalcase ID" ){
                        
                        $user_level="2";
                        unset($csv_data[0]);
                        if(trim($csv_data[1][0]) == ""){
                            $errorString='<ul><li>please fill the correct data into file. No blank data added into import.</li></ui>';
                            $ClientCompanyImport->error_code=$errorString;
                            $ClientCompanyImport->status=2;
                            $ClientCompanyImport->save();
                            return response()->json(['errors'=>$errorString,'contact_id'=>'']);
                            exit;
                        }
                        $ClientCompanyImport->total_record=count($csv_data);
                        $ClientCompanyImport->save();                        
                        try {    
                        $csv_data = array_map('array_values', $csv_data);                    
                        foreach($csv_data as $key=>$val){
                            if($val[0] != ''){
                            $UserArray[$key]['first_name']=$val[0];
                            $UserArray[$key]['middle_name']=$val[1];
                            $UserArray[$key]['last_name']=$val[2];
                            $UserArray[$key]['multiple_compnay_id']=$this->createOrReturn($val[3]);
                            $UserArray[$key]['company_name']=$val[3];
                            $UserArray[$key]['job_title']=$val[4];
                            $UserArray[$key]['street']=$val[5];
                            $UserArray[$key]['apt_unit']=$val[6];
                            $UserArray[$key]['city']=$val[7];
                            $UserArray[$key]['state']=$val[8];
                            $UserArray[$key]['postal_code']=$val[9];
                            $UserArray[$key]['country']=$this->getCountryId($val[10]);
                            $UserArray[$key]['fax_number']=$val[11];
                            $UserArray[$key]['work_phone']=$val[12];
                            $UserArray[$key]['home_phone']=$val[13];
                            $UserArray[$key]['mobile_number']=$val[14];
                            $UserArray[$key]['contact_group_id']=$this->getContactGroupId($val[15]);
                            $UserArray[$key]['contact_group_name']=$val[15];
                            $UserArray[$key]['email']=$val[16];
                            $UserArray[$key]['website']=$val[17];
                            $UserArray[$key]['outstanding_amount']=$val[18];
                            if(strtolower($val[19])=="true"){
                                $UserArray[$key]['client_portal_enable']=1;
                            }else{
                                $UserArray[$key]['client_portal_enable']=0;
                            }
                            if(strtolower($val[20])=="true"){
                                $UserArray[$key]['user_status']=4;
                            }else{
                                $UserArray[$key]['user_status']=1;
                            }

                            if($val[21]!=""){
                                $UserArray[$key]['dob']=date('Y-m-d',strtotime($val[21]));
                            }else{
                                $UserArray[$key]['dob']=NULL;
                            }
                            $UserArray[$key]['notes']=$val[22];
                            $UserArray[$key]['driver_license']=$val[23];
                            $UserArray[$key]['license_state']=$val[24];
                            $UserArray[$key]['user_level']=$user_level;
                            }
                            
                        }
                        $ic=0;
                        foreach($UserArray as $finalOperationKey=>$finalOperationVal){
                            $errorString='<ul>';
                            $User=new User;
                            $User->first_name=$finalOperationVal['first_name'];
                            $User->middle_name=$finalOperationVal['middle_name'];
                            $User->last_name=$finalOperationVal['last_name'];
                            $User->email=$finalOperationVal['email'];
                            $User->street=$finalOperationVal['street'];
                            $User->apt_unit=$finalOperationVal['apt_unit'];
                            $User->city=$finalOperationVal['city'];
                            $User->state=$finalOperationVal['state'];
                            $User->postal_code=$finalOperationVal['postal_code'];
                            $User->country=$finalOperationVal['country'];
                            $User->work_phone=$finalOperationVal['work_phone'];
                            $User->home_phone=$finalOperationVal['home_phone'];
                            $User->mobile_number=$finalOperationVal['mobile_number'];
                            $User->user_level=$finalOperationVal['user_level'];
                            $User->bulk_id=$ClientCompanyImport->id;
                            $User->parent_user=Auth::User()->id;
                            $User->firm_name=Auth::User()->firm_name;
                            $User->save();


                            
                            $UsersAdditionalInfo= new UsersAdditionalInfo;
                            $UsersAdditionalInfo->user_id=$User->id; 
                            $UsersAdditionalInfo->contact_group_id=$finalOperationVal['contact_group_id']; 
                            $UsersAdditionalInfo->multiple_compnay_id=$finalOperationVal['multiple_compnay_id']; 
                            $UsersAdditionalInfo->fax_number=$finalOperationVal['fax_number']; 
                            $UsersAdditionalInfo->job_title=$finalOperationVal['job_title']; 
                            $UsersAdditionalInfo->driver_license=$finalOperationVal['driver_license']; 
                            $UsersAdditionalInfo->license_state=$finalOperationVal['license_state']; 
                            $UsersAdditionalInfo->website=$finalOperationVal['website']; 
                            $UsersAdditionalInfo->notes=$finalOperationVal['notes']; 
                            $UsersAdditionalInfo->dob=date('Y-m-d',strtotime($finalOperationVal['dob'])); 
                            $UsersAdditionalInfo->client_portal_enable=$finalOperationVal['client_portal_enable']; 
                            $UsersAdditionalInfo->created_by =Auth::User()->id;
                            $UsersAdditionalInfo->save();

                            if(!is_numeric($finalOperationVal['mobile_number'])){
                                $waringCount = $waringCount + 1;
                                $errorString.="<li>Mobile number was invalid: ".$finalOperationVal['mobile_number']."</li>";
                            }
                            
                            if(!is_numeric($finalOperationVal['home_phone'])){
                                $waringCount = $waringCount + 1;
                                $errorString.="<li>Home phone was invalid: ".$finalOperationVal['home_phone']."</li>";
                            }
                            
                            if(!is_numeric($finalOperationVal['work_phone'])){
                                $waringCount = $waringCount + 1;
                                $errorString.="<li>Work phone was invalid: ".$finalOperationVal['work_phone']."</li>";
                            }

                            if(!is_numeric($finalOperationVal['fax_number'])){
                                $waringCount = $waringCount + 1;
                                $errorString.="<li>Home fax was invalid: ".$finalOperationVal['fax_number']."</li>";
                            }

                            $errorString.="</ul>";

                            $ClientCompanyImportHistory=new ClientCompanyImportHistory;
                            $ClientCompanyImportHistory->client_company_import_id=$ClientCompanyImport->id;
                            $ClientCompanyImportHistory->full_name=$finalOperationVal['first_name']." ".$finalOperationVal['middle_name']." ".$finalOperationVal['last_name'];
                            $ClientCompanyImportHistory->company_name=$finalOperationVal['company_name'];
                            $ClientCompanyImportHistory->email=$finalOperationVal['email'];
                            $ClientCompanyImportHistory->contact_group=$finalOperationVal['contact_group_name'];
                            $ClientCompanyImportHistory->company_name=$finalOperationVal['company_name'];
                            $ClientCompanyImportHistory->outstanding_amount=$finalOperationVal['outstanding_amount'];
                            $ClientCompanyImportHistory->status="1";
                            $ClientCompanyImportHistory->warning_list=$errorString;
                            $ClientCompanyImportHistory->created_by=Auth::User()->id;
                            $ClientCompanyImportHistory->save();
                            $errorString='';

                            $ic++;
                        }
                        $ClientCompanyImport->status="1";
                        $ClientCompanyImport->total_imported=$ic;
                        $ClientCompanyImport->total_warning=$waringCount;
                        $ClientCompanyImport->save();

                        $data=[];
                        $data['user_id']=Auth::User()->id;
                        $data['activity']='imported '.$ic.' Contacts';
                        $data['type']='staff';
                        $data['action']='import';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);

                        } catch (\Exception $e) {
                            $errorString='<ul><li>'.$e->getMessage().' on line number '.$e->getLine().'</li></ui>';
                            $ClientCompanyImport->error_code=$errorString;
                            $ClientCompanyImport->status=2;
                            $ClientCompanyImport->save();
                        }
                        
                    }else{
                        if($csv_data[0][0]=="Company" || $csv_data[0][0]=="company" || $csv_data[0][0]=="Legalcase ID" ){
                        $user_level="4";
                        unset($csv_data[0]);
                        if(trim($csv_data[1][0]) == ""){
                            $errorString='<ul><li>please fill the correct data into file. No blank data passed into import.</li></ui>';
                            $ClientCompanyImport->error_code=$errorString;
                            $ClientCompanyImport->status=2;
                            $ClientCompanyImport->save();
                            return response()->json(['errors'=>$errorString,'contact_id'=>'']);
                            exit;
                        }
                        $ClientCompanyImport->total_record=count($csv_data);
                        $ClientCompanyImport->save();
                        try {
                        $csv_data = array_map('array_values', $csv_data);
                        foreach($csv_data as $key=>$val){
                            if($val[0] != ''){
                            $UserArray[$key]['first_name']=$val[0];
                            $UserArray[$key]['street']=$val[1];
                            $UserArray[$key]['apt_unit']=$val[2];
                            $UserArray[$key]['city']=$val[3];
                            $UserArray[$key]['state']=$val[4];
                            $UserArray[$key]['postal_code']=$val[5];
                            $UserArray[$key]['country']=$this->getCountryId($val[6]);
                            $UserArray[$key]['fax_number']=$val[7];
                            $UserArray[$key]['mobile_number']=$val[8];
                            $UserArray[$key]['email']=$val[9];
                            $UserArray[$key]['website']=$val[10];
                            if(strtolower($val[11])=="true"){
                                $UserArray[$key]['user_status']=4;
                            }else{
                                $UserArray[$key]['user_status']=1;
                            }
                            $UserArray[$key]['notes']=$val[13];
                            $UserArray[$key]['user_level']=$user_level;
                            }
                        }
                        $ic=0;
                        foreach($UserArray as $finalOperationKey=>$finalOperationVal){
                            $errorString='<ul>';
                            $User=new User;
                            $User->first_name=$finalOperationVal['first_name'];
                            $User->email=$finalOperationVal['email'];
                            $User->street=$finalOperationVal['street'];
                            $User->apt_unit=$finalOperationVal['apt_unit'];
                            $User->city=$finalOperationVal['city'];
                            $User->state=$finalOperationVal['state'];
                            $User->postal_code=$finalOperationVal['postal_code'];
                            $User->country=$finalOperationVal['country'];
                            $User->mobile_number=$finalOperationVal['mobile_number'];
                            $User->user_level=$finalOperationVal['user_level'];
                            $User->bulk_id=$ClientCompanyImport->id;
                            $User->parent_user=Auth::User()->id;
                            $User->firm_name=Auth::User()->firm_name;
                            $User->save();

                            
                            $UsersAdditionalInfo= new UsersAdditionalInfo;
                            $UsersAdditionalInfo->user_id=$User->id; 
                            $UsersAdditionalInfo->fax_number=$finalOperationVal['fax_number']; 
                            $UsersAdditionalInfo->website=$finalOperationVal['website']; 
                            $UsersAdditionalInfo->notes=$finalOperationVal['notes']; 
                            $UsersAdditionalInfo->created_by =Auth::User()->id;
                            $UsersAdditionalInfo->save();

                      
                            if(!is_numeric($finalOperationVal['fax_number'])){
                                $errorString.="<li>Home fax was invalid: ".$finalOperationVal['fax_number']."</li>";
                                $waringCount = $waringCount + 1;
                            }

                            $errorString.="</ul>";

                            $ClientCompanyImportHistory=new ClientCompanyImportHistory;
                            $ClientCompanyImportHistory->client_company_import_id=$ClientCompanyImport->id;
                            $ClientCompanyImportHistory->full_name=$finalOperationVal['first_name'];
                            $ClientCompanyImportHistory->company_name=NULL;
                            $ClientCompanyImportHistory->email=$finalOperationVal['email'];
                            $ClientCompanyImportHistory->contact_group=NULL;
                            $ClientCompanyImportHistory->outstanding_amount=0;
                            $ClientCompanyImportHistory->status="1";
                            $ClientCompanyImportHistory->warning_list=$errorString;
                            $ClientCompanyImportHistory->created_by=Auth::User()->id;
                            $ClientCompanyImportHistory->save();
                            $errorString='';
                            $ic++;
                        }


                        $ClientCompanyImport->status="1";
                        $ClientCompanyImport->total_imported=$ic;
                        $ClientCompanyImport->total_warning=$waringCount;
                        $ClientCompanyImport->save();    
                        
                        $data=[];
                        $data['user_id']=Auth::User()->id;
                        $data['activity']='imported '.$ic.' Contacts';
                        $data['type']='staff';
                        $data['action']='import';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);
                            
                        } catch (\Exception $e) {
                            $errorString='<ul><li>'.$e->getMessage().' on line number '.$e->getLine().'</li></ui>';
                            $ClientCompanyImport->error_code=$errorString;
                            $ClientCompanyImport->status=2;
                            $ClientCompanyImport->save();
                        }
                    }else{
                            $errorString='<ul><li>Wrong file use for imports because columns are not matched. Make sure that you are copying the data into the right columns. Please Use Legal Case Import Template Spreadsheet...</li></ui>';
                            $ClientCompanyImport->error_code=$errorString;
                            $ClientCompanyImport->status=2;
                            $ClientCompanyImport->save();
    
                            return response()->json(['errors'=>$errorString,'contact_id'=>'']);
                            exit;
                        } 
                    }
                  
                }else{
                    $errorString='<ul><li>No records founds in file</li></ui>';
                    $ClientCompanyImport->error_code=$errorString;
                    $ClientCompanyImport->status=2;
                    $ClientCompanyImport->save();
                }
            }
            if($request->import_format=="vcf"){
                $ClientCompanyImport->file_type="1";
                $ClientCompanyImport->save();
                try {
                    
                $path = $request->file('upload_file')->getRealPath();
                $csv_data = array_map('str_getcsv', file($path));
                // echo implode("",$csv_data[0]);
                // print_r($csv_data);exit;
                $ClientCompanyImport->file_type="2";
                $ClientCompanyImport->save(); 
                $arrayGroup=[];
                $UserArray=[];
                $counter=0;
                foreach($csv_data as $k=>$v){
                    if($v[0]=="BEGIN:VCARD"){
                            $counter++;
                    }else{
                            if($v[0]=="VERSION:3.0" || $v[0]=="END:VCARD" ){
                            }else{
                                $arrayGroup[$counter][]=$v[0];
                            }
                    }
                }               
                // echo count($arrayGroup);
                // exit;
                // print_r($arrayGroup);
                // exit;       
                $ClientCompanyImport->total_record=count($arrayGroup);
                $ClientCompanyImport->save();
                if(count($arrayGroup)>=1){
                    $ic=0;                   
                    foreach($arrayGroup as $finalOperationKey => $finalOperationVal){
                        $finalOperationKey = $finalOperationKey - 1;                        
                        $org=explode(":",$finalOperationVal[0]);                        
                        if($org[0]=="ORG"){
                            $UserArray[$finalOperationKey]['user_level']=4;
                            $UserArray[$finalOperationKey]['first_name']=$org[1];
                            $UserArray[$finalOperationKey]['fullNameString']=$org[1];
                        }else{
                            if(isset($org[1])){
                                $fullName=explode(" ",$org[1]);
                                $UserArray[$finalOperationKey]['user_level']=2;
                                $UserArray[$finalOperationKey]['first_name']=$fullName[0];
                                $UserArray[$finalOperationKey]['last_name']=$fullName[1] ?? NULL;
                                $UserArray[$finalOperationKey]['fullNameString']=$org[1];
                            }else{
                                $ClientCompanyImport->error_code="<ul><li>Worng format selected, Column of selected files are list below </li></ui></br>".implode(", ",$csv_data[0]);
                                $ClientCompanyImport->status=2;
                                $ClientCompanyImport->save();
                                return response()->json(['errors'=>'','contact_id'=>'']);
                                exit;
                            }
                        }
                        if (strpos($org[0], 'FN')  !== false) {
                            foreach($finalOperationVal as $k => $v){
                                if (strpos($v, 'EMAIL')  !== false) { 
                                    $email=explode(":",$v);
                                    $UserArray[$finalOperationKey]['email']=($email[1] != '') ? $email[1] : NULL;
                                    $UserArray[$finalOperationKey]['emailSting']=($email[1] != '') ? $email[1] : NULL;
                                }
                                
                                if (strpos($v, 'TEL') !== false) { 
                                    $phone=explode(":",$v);
                                    $UserArray[$finalOperationKey]['mobile_number'] = ($phone[1] != '') ? str_replace(" ", "", $phone[1]) : NULL;
                                }

                                if (strpos($v, 'ORG') !== false) { 
                                    $company_name = explode(":",$v);
                                    $UserArray[$finalOperationKey]['multiple_compnay_id'] = ($company_name[1] != '') ? $this->createOrReturn($company_name[1]) : NULL;
                                    $UserArray[$finalOperationKey]['company_name'] = ($company_name[1] != '') ? $company_name[1] : NULL;
                                }

                                if (strpos($v, 'NOTE') !== false) { 
                                    $notes = explode(":",$v);
                                    $UserArray[$finalOperationKey]['UsersAdditionalInfoNotes'] = ($notes[1] != '') ? $notes[1] : NULL;
                                } 
                            }
                        }
                    }
                    foreach($UserArray as $userKey=>$userVal){
                        $User = New User;
                        $User->user_level=$userVal['user_level'];
                        $User->first_name=$userVal['first_name'];
                        $User->last_name=$userVal['last_name'] ?? NULL;
                        $User->email=$userVal['email']?? NULL; 
                        $User->mobile_number=$userVal['mobile_number']?? NULL; 
                        $User->parent_user=Auth::User()->id;
                        $User->firm_name=Auth::User()->firm_name;
                        $User->save();
                        
                        $UsersAdditionalInfo= new UsersAdditionalInfo;
                        $UsersAdditionalInfo->user_id=$User->id; 
                        $UsersAdditionalInfo->multiple_compnay_id=$userVal['multiple_compnay_id'] ?? NULL; 
                        $UsersAdditionalInfo->notes=$userVal['UsersAdditionalInfoNotes'] ?? NULL; 
                        $UsersAdditionalInfo->created_by = Auth::User()->id;
                        $UsersAdditionalInfo->save();

                        $ClientCompanyImportHistory=new ClientCompanyImportHistory;
                        $ClientCompanyImportHistory->client_company_import_id=$ClientCompanyImport->id;
                        $ClientCompanyImportHistory->full_name=$userVal['fullNameString'] ?? NULL;
                        $ClientCompanyImportHistory->company_name=$userVal['company_name'] ?? NULL;
                        $ClientCompanyImportHistory->email=$userVal['emailSting'] ?? NULL;
                        $ClientCompanyImportHistory->contact_group=NULL;
                        $ClientCompanyImportHistory->outstanding_amount=0;
                        $ClientCompanyImportHistory->status="1";
                        $ClientCompanyImportHistory->warning_list=NULL;
                        $ClientCompanyImportHistory->firm_id=Auth::User()->firm_name;
                        $ClientCompanyImportHistory->created_by=Auth::User()->id;
                        $ClientCompanyImportHistory->save();
                        $ic++;
                        
                    }
                    $ClientCompanyImport->status="1";
                    $ClientCompanyImport->total_imported=$ic;
                    $ClientCompanyImport->save();   

                    $data=[];
                    $data['user_id']=Auth::User()->id;
                    $data['activity']='imported '.$ic.' Contacts';
                    $data['type']='staff';
                    $data['action']='import';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);
                }else{
                    $ClientCompanyImport->error_code="<ul><li>Worng file selected, Column of selected files are list below </li></ui></br>".implode(", ",$csv_data[0]);
                    $ClientCompanyImport->status=2;
                    $ClientCompanyImport->save();
                }
            }  catch (\Exception $e) {
                $errorString='<ul><li>'.$e->getMessage().' on line number '.$e->getLine().'</li></ui>';
                $ClientCompanyImport->error_code=$errorString;
                $ClientCompanyImport->status=2;
                $ClientCompanyImport->save();
            }
                
            }
            return response()->json(['errors'=>'','contact_id'=>'']);
            exit;
        }
    }

    public function createOrReturn($jsonList){
        $fetchedIds=[];
        $allConpnay=explode(",",$jsonList);
        if(!empty($allConpnay)){
            foreach($allConpnay as $key=>$val){
                $User=User::where("user_level","4")->where("first_name",$val)->first();
                if(!empty($User)){
                    $fetchedIds[]=$User['id'];
                }else{
                    $companyUser=new User;
                    $companyUser->first_name=$val; 
                    $companyUser->created_by =Auth::User()->id;
                    $companyUser->user_level="4";
                    $companyUser->user_title="Company";
                    $companyUser->parent_user=Auth::User()->id;
                    $companyUser->save();
                    $fetchedIds[]=$companyUser->id;
                }
            }
            return implode(",",$fetchedIds);
        }else{
            return NULL;
        }
    }
    public function getCountryId($cid){
        if($cid!=NULL){
            $country = Countries::where("name",'like','%'.$cid.'%')->first();
            if(!empty($country)){
                return $country->id;
            }else{
                return "";
            }
        }else{
            return "";
        }
    }
    
    public function getContactGroupId($cgroup){
        if($cgroup!=NULL){
            $getChildUsers = User::select("id")->where('firm_name',Auth::user()->firm_name)->get()->pluck('id');
            $ClientGroupCheck=ClientGroup::select('*')->whereIn('created_by',$getChildUsers)->where('group_name',$cgroup)->first();
            if(empty($ClientGroupCheck)){
                $ClientGroup=new ClientGroup;
                $ClientGroup->group_name=$cgroup; 
                $ClientGroup->status="1";
                $ClientGroup->created_by=Auth::User()->id;
                $ClientGroup->save();
                return $ClientGroup->id;
            }else{
                return $ClientGroupCheck->id;
            }
        }else{
            return NULL;
        }
    }

    public function loadImportHistory()
    {   
        $columns = array('id', 'file_name', 'case_desc', 'case_number', 'case_status','case_unique_number');
        $requestData= $_REQUEST;
        $hisotryImport = ClientCompanyImport::join("users","client_company_import.created_by","=","users.id")->select('client_company_import.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_role as userrole");
        $hisotryImport = $hisotryImport->where("client_company_import.firm_id",Auth::User()->firm_name);
        $hisotryImport = $hisotryImport->where("client_company_import.import_for",'contact');
        $totalData=$hisotryImport->count();
        $totalFiltered = $totalData; 
        
        $hisotryImport = $hisotryImport->offset($requestData['start'])->limit($requestData['length']);
        $hisotryImport = $hisotryImport->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $hisotryImport = $hisotryImport->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $hisotryImport 
        );
        echo json_encode($json_data);  
    }
    

    public function viewLog($id)
    {
        $uid=base64_decode($id);
        $ClientCompanyImport=ClientCompanyImport::find($uid);
        $ClientCompanyImportHistory=ClientCompanyImportHistory::where("client_company_import_id",$uid)->get();
        if($ClientCompanyImport['status']=="3"){
            return view('import.view_revert_log',compact('ClientCompanyImport','ClientCompanyImportHistory'));
    
        }else{
            return view('import.view_log',compact('ClientCompanyImport','ClientCompanyImportHistory'));
        }

    }
    public function revertImport(Request $request)
    {
        $UserRemove=User::where("bulk_id",$request->import_id)->pluck("id");
        $UsersAdditionalInfoRemove=UsersAdditionalInfo::whereIn("user_id",$UserRemove)->delete();
        User::where("bulk_id",$request->import_id)->delete();

        ClientCompanyImport::where('id',$request->import_id)->update(['status'=>"3"]);
        return response()->json(['errors'=>'','contact_id'=>'']);
        exit;
    }

    
    public function loadErrorData(Request $request)
    {
      
        $ClientCompanyImport=ClientCompanyImport::find($request->id);
        return view('import.view_error',compact('ClientCompanyImport'));

    }
    /************************ Import Contacts**************************/
    
    /**
     * Load credit history list data
     */
    public function loadCreditHistory(Request $request)
    {
        $data = DepositIntoCreditHistory::where("user_id", $request->client_id)->orderBy("payment_date", "desc")->orderBy("created_at", "desc")->with("invoice", "user")->get();
        $userAddInfo = UsersAdditionalInfo::where("user_id", $request->client_id)->first();
        return Datatables::of($data)
            ->addColumn('action', function ($data) use($userAddInfo){
                $action = '';
                if($data->is_refunded == "yes") {

                } else if($data->payment_method == "refund") {
                    $action .= '<a data-toggle="modal"  data-target="#deleteLocationModal" data-placement="bottom" href="javascript:;" onclick="deleteCreditEntry('.$data->id.');">Delete</a>';
                } else {
                    $action .= '<a href="javascript:;" class="refund-payment-link" data-target="#RefundPopup" data-toggle="modal" onclick="RefundCreditPopup('.$data->id.')">Refund</a><br>';
                    if($userAddInfo->credit_account_balance < $data->deposit_amount && $data->payment_type != "withdraw" && $data->payment_type != "payment")
                        $action .= '<a href="javascript:;" onclick="deleteCreditWarningPopup(\''.@$data->user->full_name.'\')">Delete</a>';
                    else
                        $action .= '<a data-toggle="modal"  data-target="#deleteLocationModal" data-placement="bottom" href="javascript:;" onclick="deleteCreditEntry('.$data->id.');">Delete</a>';
                }
                return $action;
            })
            ->editColumn('payment_date', function ($data) {
                // return $data->payment_date ?? "--";
                if($data->payment_date) {
                    $pDate = @convertUTCToUserDate(@$data->payment_date, auth()->user()->user_timezone);
                    if ($pDate->isToday()) {
                        return "Today";
                    } else if($pDate->isYesterday()) {
                        return "Yesterday";
                    } else {
                        return $pDate->format("M d, Y");
                    }
                } else {
                    return "";
                }
            })
            ->editColumn('related_to_invoice_id', function ($data) {
                if($data->related_to_invoice_id)
                    return '<a href="'.route("bills/invoices/view", $data->invoice->decode_id).'" >#'.$data->invoice->invoice_id.'</a>';
                else if($data->related_to_fund_request_id)
                    return $data->fundRequest->padding_id;
                else
                    return '--';
            })
            ->editColumn('payment_method', function ($data) {
                $isRefund = ($data->is_refunded == "yes") ? "(Refunded)" : "";
                if($data->payment_method == "withdraw" || $data->payment_method == "payment")
                    $pMethod = "Non-Trust Credit Account";
                else
                    $pMethod = $data->payment_method;
                return ucwords($pMethod).' '.$isRefund;
            })
            ->editColumn('total_balance', function ($data) {
                return '$'.number_format($data->total_balance, 2);
            })
            ->editColumn('deposit_amount', function ($data) {
                if($data->payment_type == "deposit" || $data->payment_type == "refund withdraw" || $data->payment_type == "payment deposit") {
                    $amt = '$'.number_format($data->deposit_amount, 2);
                } else if($data->payment_type == "refund payment") {
                    $amt = '$'.number_format($data->deposit_amount, 2);
                } else {
                    $amt = '-$'.number_format($data->deposit_amount, 2);
                }
                return $amt;
            })
            ->addColumn('detail', function ($data) {
                $isRefund = ($data->is_refunded == "yes") ? "(Refunded)" : "";
                if($data->payment_type == "withdraw")
                    $dText = "Withdraw from Credit (Operating Account)";
                else if($data->payment_type == "refund withdraw")
                    $dText = "Refund Withdraw from Credit (Operating Account)";
                else if($data->payment_type == "payment")
                    $dText = "Payment from Credit (Operating Account)";
                else if($data->payment_type == "refund payment")
                    $dText = "Refund Payment from Credit (Operating Account)";
                else if($data->payment_type == "refund deposit")
                    $dText = "Refund Deposit into Credit (Operating Account)";
                else if($data->payment_type == "payment deposit")
                    $dText = "Payment into Credit (Operating Account)";
                else
                    $dText = "Deposit into Credit (Operating Account)";

                $noteContent = '<div>'.$data->notes.'</div>';
                return $dText.' '.$isRefund.'<br>
                        <a tabindex="0" class="" data-toggle="popover" data-html="true" data-placement="bottom" 
                        data-trigger="focus" title="Notes" data-content="'.$noteContent.'">View Notes</a>';
            })
            ->rawColumns(['action', 'detail', 'related_to_invoice_id'])
            ->with("credit_total", number_format($userAddInfo->credit_account_balance ?? 0.00, 2))
            ->make(true);
    }

    /**
     * Withdraw credit fund
     */
    public function withdrawFromCredit(Request $request)
    {
        $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($request->user_id);
        $UsersAdditionalInfo=UsersAdditionalInfo::select("credit_account_balance")->where("user_id",$request->user_id)->first();
        return view('client_dashboard.billing.withdraw_credit_fund',compact('userData','UsersAdditionalInfo'));     
    } 
    /**
     * Save withdraw credit fund
     */
    public function saveWithdrawFromCredit(Request $request)
    {
        $request['amount']=str_replace(",","",$request->amount);

        $validator = \Validator::make($request->all(), [
            'credit_account' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            dbStart();
            DB::table('users_additional_info')->where('user_id',$request->client_id)->decrement('credit_account_balance', $request['amount']);

            $UsersAdditionalInfo=UsersAdditionalInfo::select("credit_account_balance")->where("user_id",$request->client_id)->first();
            
            DepositIntoCreditHistory::create([
                "user_id" => $request->client_id,
                "payment_method" => "withdraw",
                "deposit_amount" => $request->amount,
                // "payment_date" => date('Y-m-d',strtotime($request->payment_date)),
                "payment_date" => convertDateToUTCzone(date("Y-m-d", strtotime($request->payment_date)), auth()->user()->user_timezone),
                "payment_type" => "withdraw",
                "total_balance" => $UsersAdditionalInfo->credit_account_balance,
                "notes" => $request->notes,
                "created_by" => auth()->id(),
                "firm_id" => auth()->user()->firm_name,
            ]);

            $this->updateNextPreviousCreditBalance($request->client_id);

            dbCommit();
            session(['popup_success' => 'Withdraw fund successful']);
            return response()->json(['errors'=>'']);
        }
    }

    /**
     * Refund credit amount
     */
    public function refundCreditPopup(Request $request)
    {
        $creditHistory = DepositIntoCreditHistory::find($request->transaction_id);
        $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($request->user_id ?? $creditHistory->user_id);
        $UsersAdditionalInfo=UsersAdditionalInfo::select("credit_account_balance")->where("user_id",$request->user_id ?? $creditHistory->user_id)->first();
        return view('client_dashboard.billing.refund_credit_fund',compact('userData','UsersAdditionalInfo','creditHistory'));   
    } 
    /**
     * Save refunded amount of credit fund
     */
    public function saveCreditRefund(Request $request)
    {
        // return $request->all();
        $request['amount']=str_replace(",","",$request->amount);
        $creditHistory = DepositIntoCreditHistory::find($request->transaction_id);
        $UsersAdditionalInfo = UsersAdditionalInfo::where("user_id",$request->client_id)->first();
        
        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric|max:'.$creditHistory->deposit_amount.'|lte:'.$UsersAdditionalInfo->credit_account_balance,
        ],[
            'amount.max' => 'Refund cannot be more than $'.number_format($creditHistory->deposit_amount,2),
            'amount.lt' => "Cannot refund. Refunding this transaction would cause the contact's balance to go below zero.",
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            try {
                dbStart();
                if($creditHistory->payment_type == "withdraw") {
                    $fund_type='refund withdraw';
                    $UsersAdditionalInfo->fill(['credit_account_balance' => ($UsersAdditionalInfo->credit_account_balance + $request->amount)])->save();
                } elseif($creditHistory->payment_type == "payment") {
                    $fund_type='refund payment';
                    $UsersAdditionalInfo->fill(['credit_account_balance' => ($UsersAdditionalInfo->credit_account_balance + $request->amount)])->save();
                } elseif($creditHistory->payment_type == "payment deposit") {
                    $fund_type='refund payment deposit';
                    $UsersAdditionalInfo->fill(['credit_account_balance' => ($UsersAdditionalInfo->credit_account_balance - $request->amount)])->save();
                } else {
                    $fund_type='refund deposit';
                    $UsersAdditionalInfo->fill(['credit_account_balance' => ($UsersAdditionalInfo->credit_account_balance - $request->amount)])->save();
                }
                $creditHistory->is_refunded="yes";
                $creditHistory->save();

                $UsersAdditionalInfo->refresh();

                $depCredHis = DepositIntoCreditHistory::create([
                    "user_id" => $request->client_id,
                    "deposit_amount" => $request->amount,
                    // "payment_date" => date('Y-m-d',strtotime($request->payment_date)),
                    "payment_date" => convertDateToUTCzone(date("Y-m-d", strtotime($request->payment_date)), auth()->user()->user_timezone),
                    "payment_method" => "refund",
                    "payment_type" => $fund_type,
                    "total_balance" => $UsersAdditionalInfo->credit_account_balance,
                    "notes" => $request->notes,
                    "refund_ref_id" => $creditHistory->id,
                    "created_by" => auth()->id(),
                    "firm_id" => auth()->user()->firm_name,
                    "related_to_invoice_id" => $creditHistory->related_to_invoice_id,
                    "related_to_fund_request_id" => $creditHistory->related_to_fund_request_id,
                ]);

                if($fund_type == "refund payment" || $fund_type == "refund payment deposit") {
                    $newInvPaymentId = $this->updateInvoicePaymentAfterRefund($creditHistory->id, $request);
                    $depCredHis->fill(["related_to_invoice_payment_id" => $newInvPaymentId])->save();
                }

                // For request refund
                if($depCredHis->related_to_fund_request_id) {
                    $this->refundCreditRequest($depCredHis->id);
                }

                // For account activity
                if($depCredHis->payment_type == "refund deposit") {
                    $findInvoice = Invoices::whereId($creditHistory->related_to_invoice_id)->first();
                    $request->request->add(["applied_to" => $depCredHis->related_to_fund_request_id]);
                    $request->request->add(["contact_id" => $depCredHis->user_id]);
                    $request->request->add(['credit_history_id' => $depCredHis->id]);
                    $request->request->add(["payment_type" => $depCredHis->payment_type]);
                    $this->updateClientPaymentActivity($request, $findInvoice ?? null, $isDebit = "yes", $amtAction = "sub");
                }

                $this->updateNextPreviousCreditBalance($request->client_id);
                dbCommit();
                session(['popup_success' => 'Refund successful']);
                return response()->json(['errors'=>'']);
                exit;   
            } catch(Exception $e) {
                dbEnd();
                return response()->json(['errors' => $e->getMessage()]);
            }
        }
    }

    /**
     * Delete credit history entry
     */
    public function deleteCreditHistoryEntry(Request $request)
    {
        // return $request->all();
        $validator = \Validator::make($request->all(), [
            'delete_credit_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            try {
                dbStart();
                $creditHistory = DepositIntoCreditHistory::find($request->delete_credit_id);
                if($creditHistory->payment_type == "refund deposit") {
                    DB::table('users_additional_info')->where('user_id',$creditHistory->user_id)->increment('credit_account_balance', $creditHistory->deposit_amount);

                    $updateRedord= DepositIntoCreditHistory::find($creditHistory->refund_ref_id);
                    $updateRedord->is_refunded="no";
                    $updateRedord->save();
                } else if($creditHistory->payment_type == "refund withdraw") {
                    DB::table('users_additional_info')->where('user_id',$creditHistory->user_id)->decrement('credit_account_balance', $creditHistory->deposit_amount);
                    $updateRedord= DepositIntoCreditHistory::find($creditHistory->refund_ref_id);
                    $updateRedord->is_refunded="no";
                    $updateRedord->save();
                } else if($creditHistory->payment_type == "refund payment") {
                    DB::table('users_additional_info')->where('user_id',$creditHistory->user_id)->decrement('credit_account_balance', $creditHistory->deposit_amount);
                    $updateRedord= DepositIntoCreditHistory::find($creditHistory->refund_ref_id);
                    $updateRedord->is_refunded="no";
                    $updateRedord->save();
                } else if($creditHistory->payment_type == "refund payment deposit") {
                    DB::table('users_additional_info')->where('user_id',$creditHistory->user_id)->increment('credit_account_balance', $creditHistory->deposit_amount);
                    $updateRedord= DepositIntoCreditHistory::find($creditHistory->refund_ref_id);
                    $updateRedord->is_refunded="no";
                    $updateRedord->save();
                } else if($creditHistory->payment_type == "payment" || $creditHistory->payment_type == "withdraw") {
                    DB::table('users_additional_info')->where('user_id',$creditHistory->user_id)->increment('credit_account_balance', $creditHistory->deposit_amount);
                } else if($creditHistory->payment_type == "deposit" || $creditHistory->payment_type == "payment deposit"){
                    DB::table('users_additional_info')->where('user_id',$creditHistory->user_id)->decrement('credit_account_balance', $creditHistory->deposit_amount);
                } 


                $userAddInfo=UsersAdditionalInfo::where("user_id",$creditHistory->user_id)->first();
                if($userAddInfo->credit_account_balance <= 0){
                    DB::table('users_additional_info')->where('user_id',$creditHistory->user_id)->update(['credit_account_balance'=> "0.00"]);
                }

                if($creditHistory->payment_type == "refund payment" || $creditHistory->payment_type == "refund payment deposit" || $creditHistory->payment_type == "payment" || $creditHistory->payment_type == "payment deposit") {
                    $this->deleteInvoicePaymentHistory($creditHistory->id);
                }

                if($creditHistory->related_to_fund_request_id) {
                    $this->deletePaymentCreditRequest($creditHistory->id);
                }

                $data=[];
                $data['user_id']=$creditHistory->client_id;
                $data['client_id']=$creditHistory->client_id;
                $data['activity']="deleted a payment";
                $data['type']='deposit';
                $data['action']='delete';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);

                $clientId = $creditHistory->user_id;
                $creditHistory->delete();
                $this->updateNextPreviousCreditBalance($clientId);
                dbCommit();
                session(['popup_success' => 'Credit entry was deleted']);
                return response()->json(['errors'=>'']);
                exit;   
            } catch (Exception $e) {
                dbEnd();
                return response()->json(['errors'=> $e->getMessage()]);
            }
        }
    }

    /**
     * client > billing > invoices list
     */
    public function loadInvoices(Request $request)
    {
        $data = Invoices::where("user_id", $request->client_id)->orderBy("created_at", "desc")
                    ->with(['invoiceForwardedToInvoice', 'invoiceShared' => function($query) use($request) {
                        $query->where('user_id', $request->client_id);
                    }])->get();
        $userAddInfo = UsersAdditionalInfo::where("user_id", $request->client_id)->first();
        return Datatables::of($data)
            ->addColumn('action', function ($data) {
                $action = '';
                if($data->status == "Forwarded") {
                } else {
                    if(auth()->user()->hasAllPermissions(['billing_add_edit'])) {
                    if($data->status=="Partial" || $data->status=="Draft" || $data->status=="Unsent"){
                        $action .='<span data-toggle="tooltip" data-placement="top" title="Send Reminder"><a data-toggle="modal"  data-target="#sendInvoiceReminder" data-placement="bottom" href="javascript:;"  onclick="sendInvoiceReminder('.$data->case_id.','.$data->id.');"><i class="fas fa-bell align-middle p-2"></i></a></span>';
                    }
                    if($data->status!="Paid"){
                        $action .='<span data-toggle="tooltip" data-placement="top" title="Record Payment"><a data-toggle="modal"  data-target="#payInvoice" data-placement="bottom" href="javascript:;"  onclick="payinvoice('.$data->id.');"><i class="fas fa-dollar-sign align-middle p-2"></i></a></span>';
                    }
                    $action .='<span data-toggle="tooltip" data-placement="top" title="Delete"><a data-toggle="modal"  data-target="#deleteInvoice" data-placement="bottom" href="javascript:;"  onclick="deleteInvoice('.$data->id.');"><i class="fas fa-trash align-middle p-2"></i></a></span>';
                    }
                }
                return $action;
            })
            ->addColumn('viewed', function ($data) {
                if(count($data->invoiceShared) && $data->invoiceShared[0]->is_viewed=="yes"){
                    return date('M j, Y',strtotime(convertUTCToUserTime($data->invoiceShared[0]->last_viewed_at, Auth::User()->user_timezone)));
                }else{
                    return 'Never';
                }
            })
            ->editColumn('status', function ($data) {
                if($data->status=="Paid"){
                    $curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-success" style="display: inline;"></i>'.$data->status;
                }else if($data->status=="Partial"){
                    $curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-warning" style="display: inline;"></i>'.$data->status;
                }else if($data->status=="Overdue"){
                    $curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-danger" style="display: inline;"></i>'.$data->status;
                }else {
                    $curSetatus=$data->status;
                }
                return $curSetatus;
            })
            ->editColumn('due_amount', function ($data) {
                $fwd = "";
                if($data->status == "Forwarded") {
                    foreach($data->invoiceForwardedToInvoice as $invkey => $invitem) {
                        $fwd = '<div style="font-size: 11px;">Forwarded to <a href="'.route("bills/invoices/view", $invitem->decode_id).'">'.$invitem->invoice_id.'</a></div>';
                    }
                }
                return '$'.$data->due_amount_new.'<br>'.$fwd;
            })
            ->editColumn('total_amount', function ($data) {
                return '$'.$data->total_amount_new;
            })
            ->editColumn('paid_amount', function ($data) {
                return '$'.$data->paid_amount_new;
            })
            ->editColumn('due_date', function ($data) {
                return $data->due_date_new;
            })
            ->editColumn('created_at', function ($data) {
                return $data->created_date_new;
            })
            ->addColumn('invoice_number', function ($data) {
                if($data->is_lead_invoice == 'yes'){
                    return '<a href="'.route("bills/invoices/potentialview", $data->decode_id).'">'.$data->invoice_id.'</a>';
                }else{
                    return '<a href="'.route("bills/invoices/view", $data->decode_id).'">'.$data->invoice_id.'</a>';
                }
            })
            ->addColumn('view', function ($data) {
                if($data->is_lead_invoice == 'yes'){
                    return '<a href="'.route("bills/invoices/potentialview", $data->decode_id).'"><button class="btn btn-primary btn-rounded" type="button" id="button">View</button> </a>';
                }else{
                    return '<a href="'.route("bills/invoices/view", $data->decode_id).'"><button class="btn btn-primary btn-rounded" type="button" id="button">View</button> </a>';
                }
            })
            ->rawColumns(['action', 'view', 'invoice_number', 'status', 'due_amount'])
            ->with("credit_balance", $userAddInfo->credit_account_balance ?? 0.00)
            ->with("trust_balance", $userAddInfo->trust_account_balance ?? 0.00)
            ->make(true);
    }

    public function loadMessagesEntryPopup(Request $request){
        
        $messagesData = Messages::leftJoin("case_master","case_master.id","=","messages.case_id")
        ->select('messages.*',"messages.updated_at as last_post","case_master.case_title","case_master.case_unique_number")
        ->where('messages.id', $request->message_id)
        ->first();

        $messageList = ReplyMessages::leftJoin("messages","reply_messages.message_id","=","messages.id")
        ->select('reply_messages.*',"messages.updated_at as last_post")
        ->where('reply_messages.message_id', $request->message_id)
        ->get();
    
        $clientList = [];    
        $userlist = explode(',', $messagesData->user_id);
        foreach ($userlist as $key => $value) {
            $userInfo =  User::where('id',$value)->select('first_name','last_name','user_level')->first();
            $clientList[$value] = $userInfo['first_name'].' '.$userInfo['last_name'].'|'.$userInfo['user_level'];
        }
        return view('client_dashboard.viewMessage',compact('messagesData','messageList','clientList'));   
        exit;  
    }
    /**
     * Export/Download credit history
     */
    public function exportCreditHistory(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'from_date'    => 'date|nullable',
            'to_date'      => 'date|after_or_equal:from_date|nullable',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                
            $id=$request->user_id;
            $firmData=Firm::find(Auth::User()->firm_name);
            $userData=User::find($id);
            $country = Countries::get();
            $firmAddress = Firm::select("firm.*","firm_address.*","countries.name as countryname")->leftJoin('firm_address','firm_address.firm_id',"=","firm.id")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",$userData['firm_name'])->first();
            $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$id)->first();

            $creditHistory = DepositIntoCreditHistory::where("user_id", $request->user_id);
            if(isset($request->from_date) && isset($request->to_date)){
                $creditHistory = $creditHistory->whereBetween('payment_date', [date('Y-m-d',strtotime($request->from_date)), date('Y-m-d',strtotime($request->to_date))]); 
            }  
            $creditHistory = $creditHistory->orderBy('payment_date','asc')->with("invoice")->get();
            $creditHistoryFirstRow = $creditHistory->first();
            $initialBalance = DepositIntoCreditHistory::where("user_id", $request->user_id)
                        ->whereDate('payment_date', '<', ($request->from_date) ? date('Y-m-d',strtotime($request->from_date)) : date('Y-m-d', strtotime(@$creditHistoryFirstRow->payment_date)))
                        ->orderBy('payment_date', 'desc')->orderBy("created_at", "desc")->first();

            $filename='credit_export_'.time().'.pdf';
            $startDate = $request->from_date; $endDate = $request->to_date;
            $PDFData=view('client_dashboard.billing.credit_history_pdf',compact('userData','country','firmData','firmAddress','UsersAdditionalInfo','creditHistory', 'startDate', 'endDate', 'initialBalance'));
            $pdf = new Pdf;
            if($_SERVER['SERVER_NAME']=='localhost'){
                $pdf->binary = EXE_PATH;
            }
            $pdf->addPage($PDFData);
            $pdf->setOptions(['javascript-delay' => 5000]);
            $pdf->setOptions(["footer-right"=> "Page [page] from [topage]"]);
            // $pdf->setOptions(["footer-left"=> "Completed on ". date('m/d/Y',strtotime($caseIntakeForm['submited_at']))]);
            $pdf->saveAs(storage_path("app/public/download/pdf/".$filename));
            $path = storage_path("app/public/download/pdf/".$filename);
            // return response()->download($path);
            // exit;
            return response()->json([ 'success' => true, "url"=>asset(Storage::url('download/pdf/'.$filename)),"file_name"=>$filename,'errors'=>''], 200);
            exit;
        }
    }

    public function exportCases(Request $request)
    {
        File::deleteDirectory(public_path('export/'.date('Y-m-d').'/'.Auth::User()->firm_name));
        if(!is_dir(public_path("export/".date('Y-m-d').'/'.Auth::User()->firm_name))) {
            File::makeDirectory(public_path("export/".date('Y-m-d').'/'.Auth::User()->firm_name), $mode = 0777, true, true);
        }
        
        $this->generateCasesCSV($request->all());
        $CSV[] = public_path('export/'.date('Y-m-d').'/'.Auth::User()->firm_name."/cases.csv");
        $CSV[] = public_path('export/'.date('Y-m-d').'/'.Auth::User()->firm_name."/notes.csv");

        $zip = new ZipArchive;
        $storage_path = '/export/'.date('Y-m-d').'/'.Auth::User()->firm_name;
        $firmData=Firm::find(Auth::User()->firm_name);
        $timeName = str_replace(" ","_",$firmData->firm_name)."-".Auth::User()->id."-cases-".date("m-d-Y");
        $zipFileName = $storage_path . '/' . $timeName . '.zip';
        
        $zipPath = asset($zipFileName);
        if ($zip->open((public_path($zipFileName)), ZipArchive::CREATE) === true) {
            foreach ($CSV as $relativName) {
                $zip->addFile($relativName,basename($relativName));
            }
            $zip->close();
            if ($zip->open(public_path($zipFileName)) === true) {
                $Path= $zipPath;
            } else {
                $Path="";
            }
        }
        return response()->json(['errors'=>'','url'=>$Path,'msg'=>" Building File... it will downloading automaticaly"]);
        exit;
    } 

    public function generateCasesCSV($request){
        $casesCsvData=[];
        $casesHeader="Case/Matter Name|Number|Open Date|Practice Area|Case Description|Case Closed|Closed Date|Lead Attorney|Originating Attorney|SOL Date|Outstanding Balance|LegalCase ID|Contacts|Billing Type|Billing Contact|Flat fee|Case Stage|Case Balance|Conflict Check?|Conflict Check Notes";
        $casesCsvData[]=$casesHeader;

        $caseNotesCsvData=[];
        $caseNotesHeader="Case Name|Created By|Date|Created at|Updated at|Subject|Note";
        $caseNotesCsvData[]=$caseNotesHeader;

        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");

        if($request['export_cases'] == 0){
            $case = $case->where("case_master.is_entry_done","1");
            if(Auth::user()->parent_user==0){
                $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
                $getChildUsers[]=Auth::user()->id;
                $case = $case->whereIn("case_master.created_by",$getChildUsers);
            }else{
                $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
                $case = $case->whereIn("case_master.id",$childUSersCase);
            }
            if(!isset($request['include_archived'])){   
                $case = $case->where("case_master.case_close_date", NULL);
            }
        }else{
            if(!isset($request['include_archived'])){
                $case = $case->where("case_master.case_close_date", NULL);
            }
        }
        $case = $case->get();        
        foreach($case as $k=>$v){
            $practiceArea = '';
            if($v->practice_area > 0){
                $practiceAreaList = CasePracticeArea::where("status","1")->where("id",$v->practice_area)->first();  
                $practiceArea = $practiceAreaList->title;
            }

            $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
            ->leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
            ->leftJoin('user_role','user_role.id','case_client_selection.user_role')
            ->leftJoin('client_group','client_group.id','users_additional_info.contact_group_id')
            ->select("users.first_name","users.last_name","case_client_selection.is_billing_contact")
            ->where("case_client_selection.case_id",$v->id)
            ->get();
            
            $contactList = '';
            $is_billing_contact = '';
            if(count($caseCllientSelection) > 0){
                foreach($caseCllientSelection as $key=>$val){
                    if($val->is_billing_contact == 'yes'){
                        $is_billing_contact = $val->first_name.' '.$val->last_name;
                    }
                    if($val->user_level==4){
                        $contactList .= $val->first_name.' '.$val->last_name.'(Attorney)'.PHP_EOL;
                    }else{
                        $contactList .= $val->first_name.' '.$val->last_name.'(Client)'.PHP_EOL;
                    }
                }                
            }
            
            $caseStage = 'Not Specified';
            if($v->case_status > 0){
                $caseStageList = CaseStage::select("*")->where("status","1")->where("id",$v->case_status)->first();
                $caseStage = $caseStageList->title ?? 'Not Specified';
            }

            $flatFee = 0;
            if($v->billing_method =='flat' || $v->billing_method =='mixed'){ 
                $flatFee = $v->billing_amount;
            }

            $leadAttorney = CaseStaff::join('users','users.id','=','case_staff.lead_attorney')->select("users.first_name","users.last_name")->where("case_id",$v->id)->where("lead_attorney","!=",null)->first();
            $originatingAttorney = CaseStaff::join('users','users.id','=','case_staff.originating_attorney')->select("users.first_name","users.last_name")->where("case_id",$v->id)->where("originating_attorney","!=",null)->first();
          

            $casesCsvData[]=$v->case_title."|".$v->case_number."|".date("m/d/Y",strtotime($v->case_open_date))."|".$practiceArea."|".$v->case_description."|".(($v->case_close_date != NUll) ? 'true' : 'false')."|".(($v->case_close_date != NUll) ? date("m/d/Y",strtotime($v->case_close_date)) : '')."|".( (!empty($leadAttorney)) ?  $leadAttorney->first_name.' '.$leadAttorney->last_name : '')."|".( (!empty($originatingAttorney)) ?  $originatingAttorney->first_name.' '.$originatingAttorney->last_name : '')."||0|".$v->id."|".$contactList."|".$v->billing_method."|".$is_billing_contact."|".$flatFee."|".$caseStage."|0|".(($v->conflict_check == '0') ? 'false' : 'true')."|".(($v->conflict_check_description == NULL) ? 'No Conflict Check Notes' : $v->conflict_check_description);
            
            $ClientNotesData = ClientNotes::where("case_id",$v->id)->get();
            if(count($ClientNotesData) > 0){
                foreach($ClientNotesData as $key=>$notes)
                $caseNotesCsvData[]=$v->case_title."|".$v->created_by_name."|".date("m/d/Y",strtotime($v->case_open_date))."|".$notes->created_at."|".$notes->updated_at."|".$notes->note_subject."|". strip_tags($notes->notes);
            }
        }
        // echo json_encode($casesCsvData);
        // exit;
        
        $folderPath = public_path('export/'.date('Y-m-d').'/'.Auth::User()->firm_name);
        if(!File::isDirectory($folderPath)){
            File::makeDirectory($folderPath, 0777, true, true);    
        }
        $file_path =  $folderPath.'/cases.csv';  
        $file = fopen($file_path,"w+");
        foreach ($casesCsvData as $exp_data){
          fputcsv($file,explode('|',$exp_data));
        }   
        fclose($file); 

        $file_path_notes =  $folderPath.'/notes.csv';  
        $file_notes = fopen($file_path_notes,"w+");
        foreach ($caseNotesCsvData as $exp_data_notes){
          fputcsv($file_notes,explode('|',$exp_data_notes));
        }   
        fclose($file_notes); 

        return true; 
    }

    public function importCases(Request $request){
        $validator = \Validator::make($request->all(), [
            'upload_file' => 'required|max:8192', //8 mb
        ],['upload_file.required'=>"Please select file"]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $path = $request->file('upload_file')->getRealPath();
            $csv_data = array_map('str_getcsv', file($path));
            if(!empty($csv_data)){
                if(count($csv_data) >= 1000){
                    return response()->json(['errors'=>'We recommend importing less than 1000 records at a time or the import may error out. If you have more than 1000 records to import, we suggest breaking the import up into multiple spreadsheets and try again']);
                    exit;
                }else{
                if(trim($csv_data[0][0])=="Case/Matter Name" && trim($csv_data[0][1]) =="Number" && trim($csv_data[0][2])=="Open Date" && trim($csv_data[0][3]) =="Practice Area" && trim($csv_data[0][4])=="Case Description" && trim($csv_data[0][5]) =="Case Closed" && trim($csv_data[0][6])=="Closed Date" && trim($csv_data[0][7]) =="Lead Attorney" && trim($csv_data[0][8])=="Originating Attorney" && trim($csv_data[0][9]) =="SOL Date" && trim($csv_data[0][10])=="Outstanding Balance" && trim($csv_data[0][11]) =="Case Stage" && trim($csv_data[0][12])=="Conflict Check?" && trim($csv_data[0][13]) =="Conflict Check Notes" && trim($csv_data[0][14])=="Note: <Imported Note 1>" && trim($csv_data[0][15]) =="Note: <Imported Note 2>"){                    
                    unset($csv_data[0]);
                    
                    $uploadFile = $request->upload_file;
                    $namewithextension = $uploadFile->getClientOriginalName(); 

                    $ClientCompanyImport=new ClientCompanyImport;
                    $ClientCompanyImport->file_name=$namewithextension;
                    $ClientCompanyImport->total_record=count($csv_data);
                    $ClientCompanyImport->total_imported=0;
                    $ClientCompanyImport->status="2";
                    $ClientCompanyImport->import_for="case";
                    $ClientCompanyImport->firm_id=Auth::User()->firm_name;
                    $ClientCompanyImport->created_by=Auth::User()->id;
                    $ClientCompanyImport->file_type="2";
                    $ClientCompanyImport->save();

                    $waringCount = 0;
                    $caseArray = [];
                    try{                        
                        foreach($csv_data as $key=>$val){
                            $caseArray[$key]['case_title'] = $val[0];
                            $caseArray[$key]['case_number'] = $val[1];
                            $caseArray[$key]['case_open_date'] = $val[2];
                            $caseArray[$key]['practice_area'] = $val[3];
                            $caseArray[$key]['case_description'] = $val[4];
                            $caseArray[$key]['case_close'] = $val[5];
                            $caseArray[$key]['case_close_date'] = $val[6];
                            $caseArray[$key]['lead_attorney'] = $val[7];
                            $caseArray[$key]['originating_attorney'] = $val[8];
                            $caseArray[$key]['sol_date'] = $val[9];
                            $caseArray[$key]['outstanding_balance'] = $val[10];
                            $caseArray[$key]['case_stage'] = $val[11];
                            $caseArray[$key]['conflict_check'] = $val[12];
                            $caseArray[$key]['conflict_check_notes'] = $val[13];
                            $caseArray[$key]['case_note_1'] = $val[14];
                            $caseArray[$key]['case_note_2'] = $val[15];
                        }

                        $ic=0;
                        foreach($caseArray as $finalOperationKey=>$finalOperationVal){
                            $errorString='<ul>';

                            $CaseMaster = new CaseMaster;
                            $CaseMaster->case_title=$finalOperationVal['case_title'];
                            $CaseMaster->case_number=$finalOperationVal['case_number'];                            
                            $CaseMaster->case_description=$finalOperationVal['case_description'];
                            $CaseMaster->case_open_date= date('Y-m-d', strtotime($finalOperationVal['case_open_date']));
                            if($finalOperationVal['sol_date'] != ''){
                                $CaseMaster->case_statute_date= date('Y-m-d', strtotime($finalOperationVal['sol_date']));
                                $CaseMaster->sol_satisfied="yes";
                            }
                            if($finalOperationVal['conflict_check'] == 'true' || $finalOperationVal['conflict_check'] == 'TRUE'){
                                $CaseMaster->conflict_check=1;
                                $CaseMaster->conflict_check_description=$finalOperationVal['conflict_check_notes'];
                            }

                            if($finalOperationVal['practice_area'] != '') { 
                                $caseArea =  CasePracticeArea::where('title','like','%'.$finalOperationVal['practice_area'].'%')->select('id')->first();
                                if(!empty($caseArea)){
                                    $CaseMaster->practice_area=$caseArea->id;
                                }else{
                                    $CasePracticeArea = new CasePracticeArea;
                                    $CasePracticeArea->title=$finalOperationVal['practice_area']; 
                                    $CasePracticeArea->firm_id =Auth::User()->firm_name;
                                    $CasePracticeArea->created_by=Auth::User()->id; 
                                    $CasePracticeArea->save();
                                    
                                    $CaseMaster->practice_area=$CasePracticeArea->id;
                                }
                            }
                            $caseStageId = 0;
                            if($finalOperationVal['case_stage'] != '') { 
                                $caseStage =  CaseStage::where('title','like','%'.$finalOperationVal['case_stage'].'%')->select('id')->first();
                                if(!empty($caseStage)){
                                    $CaseMaster->case_status=$caseStage->id;
                                    $caseStageId = $caseStage->id;
                                }else{
                                    $stage_order = DB::table('case_stage')->max('stage_order');
                                    $CaseStage = new CaseStage;
                                    $CaseStage->stage_order=$stage_order; 
                                    $CaseStage->title=substr($finalOperationVal['case_stage'],0,255); 
                                    $CaseStage->stage_color='#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
                                    $CaseStage->created_by=Auth::user()->id; 
                                    $CaseStage->save();
                                    
                                    $CaseMaster->case_status=$CaseStage->id;
                                    $caseStageId = $CaseStage->id;
                                }
                            }
                            if($finalOperationVal['case_close']) { 
                                $CaseMaster->case_close_date= date('Y-m-d', strtotime($finalOperationVal['case_close_date']));
                            }

                            $CaseMaster->case_unique_number=strtoupper(uniqid());
                            $CaseMaster->created_by=Auth::User()->id; 
                            $CaseMaster->is_entry_done="1"; 
                            $CaseMaster->firm_id = auth()->user()->firm_name; 
                            $CaseMaster->save();

                            $data=[];
                            $data['activity_title']='added case';
                            $data['case_id']=$CaseMaster->id;
                            $data['activity_type']='';
                            $this->caseActivity($data);
                            
                            $data=[];
                            $data['case_id']=$CaseMaster->id;
                            $data['activity']='added case';
                            $data['type']='case';
                            $data['action']='add';
                            $CommonController= new CommonController();
                            $CommonController->addMultipleHistory($data);

                            if($finalOperationVal['lead_attorney'] != ''){
                                $leadName = explode(" ",$finalOperationVal['lead_attorney']);
                                $leadAttorney = User::where('first_name','like','%'.$leadName[0].'%')->where('last_name','like','%'.$leadName[1].'%')->select('id')->first(); 
                                if(!empty($leadAttorney)){
                                    $CaseStaff = new CaseStaff;
                                    $CaseStaff->case_id=$CaseMaster->id; 
                                    $CaseStaff->user_id=Auth::user()->id; 
                                    $CaseStaff->lead_attorney=$leadAttorney->id; 
                                    $CaseStaff->created_by=Auth::user()->id;      

                                    if($finalOperationVal['originating_attorney'] != ''){
                                        $originatingName = explode(" ",$finalOperationVal['originating_attorney']);
                                        $originatingAttorney = User::where('first_name','like','%'.$originatingName[0].'%')->where('last_name','like','%'.$originatingName[1].'%')->select('id')->first(); 
                                        if(!empty($originatingAttorney)){
                                            $CaseStaff->originating_attorney=$originatingAttorney->id; 
                                        }else{
                                            $waringCount = $waringCount + 1;
                                            $errorString.='<li>Invalid Originating Attorney: '.$finalOperationVal['originating_attorney'] .' </li>';
                                        }
                                    }
                                    $CaseStaff->save();
                                    //Activity tab
                                    $datauser=[];
                                    $datauser['activity_title']='linked staff';
                                    $datauser['case_id']=$CaseMaster->id;
                                    $datauser['staff_id']=Auth::user()->id;
                                    $this->caseActivity($datauser);

                                    $data=[];
                                    $data['user_id']=Auth::user()->id;
                                    $data['client_id']=Auth::user()->id;
                                    $data['case_id']=$CaseMaster->id;
                                    $data['activity']='linked attorney';
                                    $data['type']='contact';
                                    $data['action']='link';
                                    $CommonController= new CommonController();
                                    $CommonController->addMultipleHistory($data);
                                }else{
                                    $CaseStaff = new CaseStaff;
                                    $CaseStaff->case_id=$CaseMaster->id; 
                                    $CaseStaff->user_id=Auth::user()->id; 
                                    $CaseStaff->created_by=Auth::user()->id; 
                                    $CaseStaff->save();
                                    //Activity tab
                                    $datauser=[];
                                    $datauser['activity_title']='linked staff';
                                    $datauser['case_id']=$CaseMaster->id;
                                    $datauser['staff_id']=Auth::user()->id;
                                    $this->caseActivity($datauser);  

                                    $data=[];
                                    $data['user_id']=Auth::user()->id;
                                    $data['client_id']=Auth::user()->id;
                                    $data['case_id']=$CaseMaster->id;
                                    $data['activity']='linked attorney';
                                    $data['type']='contact';
                                    $data['action']='link';
                                    $CommonController= new CommonController();
                                    $CommonController->addMultipleHistory($data);

                                    $waringCount = $waringCount + 1;
                                    $errorString.='<li>Invalid Lead Attorney: '.$finalOperationVal['lead_attorney'] .' </li>';
                                }
                            }else{
                                $CaseStaff = new CaseStaff;
                                $CaseStaff->case_id=$CaseMaster->id; 
                                $CaseStaff->user_id=Auth::user()->id; 
                                $CaseStaff->created_by=Auth::user()->id; 
                                $CaseStaff->save();
                                //Activity tab
                                $datauser=[];
                                $datauser['activity_title']='linked staff';
                                $datauser['case_id']=$CaseMaster->id;
                                $datauser['staff_id']=Auth::user()->id;
                                $this->caseActivity($datauser);  

                                $data=[];
                                $data['user_id']=Auth::user()->id;
                                $data['client_id']=Auth::user()->id;
                                $data['case_id']=$CaseMaster->id;
                                $data['activity']='linked attorney';
                                $data['type']='contact';
                                $data['action']='link';
                                $CommonController= new CommonController();
                                $CommonController->addMultipleHistory($data);
                            }                            

                            $caseStageHistory = new CaseStageUpdate;
                            $caseStageHistory->stage_id=$caseStageId;
                            $caseStageHistory->case_id=$CaseMaster->id;
                            $caseStageHistory->start_date = date('Y-m-d',strtotime($finalOperationVal['case_open_date']));
                            $caseStageHistory->end_date = date('Y-m-d',strtotime($finalOperationVal['case_open_date']));
                            $caseStageHistory->created_by=Auth::user()->id; 
                            $caseStageHistory->created_at=$finalOperationVal['case_open_date']; 
                            $caseStageHistory->save();

                            if($finalOperationVal['case_note_1'] != ''){
                                $LeadNotes = new ClientNotes; 
                                $LeadNotes->case_id=$CaseMaster->id;
                                $LeadNotes->note_date=date('Y-m-d');
                                $LeadNotes->note_subject='Imported Note 1';
                                $LeadNotes->notes=$finalOperationVal['case_note_1'];
                                $LeadNotes->status="0";
                                $LeadNotes->created_by=Auth::User()->id;
                                $LeadNotes->created_at=date('Y-m-d H:i:s');            
                                $LeadNotes->updated_by=Auth::User()->id;
                                $LeadNotes->updated_at=date('Y-m-d H:i:s');
                                $LeadNotes->save();
                            }
                            if($finalOperationVal['case_note_2'] != ''){
                                $LeadNotes = new ClientNotes; 
                                $LeadNotes->case_id=$CaseMaster->id;
                                $LeadNotes->note_date=date('Y-m-d');
                                $LeadNotes->note_subject='Imported Note 2';
                                $LeadNotes->notes=$finalOperationVal['case_note_2'];
                                $LeadNotes->status="0";
                                $LeadNotes->created_by=Auth::User()->id;
                                $LeadNotes->created_at=date('Y-m-d H:i:s');            
                                $LeadNotes->updated_by=Auth::User()->id;
                                $LeadNotes->updated_at=date('Y-m-d H:i:s');
                                $LeadNotes->save();
                            }
                            $errorString.="</ul>";

                            $ClientCasesImportHistory=new ClientCasesImportHistory;
                            $ClientCasesImportHistory->client_company_import_id=$ClientCompanyImport->id;
                            $ClientCasesImportHistory->case_title = $finalOperationVal['case_title'];
                            $ClientCasesImportHistory->case_number = $finalOperationVal['case_number'];
                            $ClientCasesImportHistory->case_open_date = $finalOperationVal['case_open_date'];
                            $ClientCasesImportHistory->practice_area = $finalOperationVal['practice_area'];
                            $ClientCasesImportHistory->case_description = $finalOperationVal['case_description'];
                            $ClientCasesImportHistory->case_close = $finalOperationVal['case_close'];
                            $ClientCasesImportHistory->case_close_date = $finalOperationVal['case_close_date'];
                            $ClientCasesImportHistory->lead_attorney = $finalOperationVal['lead_attorney'];
                            $ClientCasesImportHistory->originating_attorney = $finalOperationVal['originating_attorney'];
                            $ClientCasesImportHistory->sol_date = $finalOperationVal['sol_date'];
                            $ClientCasesImportHistory->outstanding_balance = $finalOperationVal['outstanding_balance'];
                            $ClientCasesImportHistory->case_stage = $finalOperationVal['case_stage'];
                            $ClientCasesImportHistory->conflict_check = ($finalOperationVal['conflict_check'] == 'TRUE') ? '1' : '0';
                            $ClientCasesImportHistory->conflict_check_notes = $finalOperationVal['conflict_check_notes'];
                            $ClientCasesImportHistory->case_note_1 = $finalOperationVal['case_note_1'];
                            $ClientCasesImportHistory->case_note_2 = $finalOperationVal['case_note_2'];
                            $ClientCasesImportHistory->status="1";
                            $ClientCasesImportHistory->warning_list=$errorString;
                            $ClientCasesImportHistory->case_id=$CaseMaster->id; 
                            $ClientCasesImportHistory->created_by=Auth::User()->id;
                            $ClientCasesImportHistory->save();
                           

                            $ic++;
                        }
                        $ClientCompanyImport->status="1";
                        $ClientCompanyImport->total_imported=$ic;
                        $ClientCompanyImport->total_warning=$waringCount;
                        $ClientCompanyImport->save();

                        $data=[];
                        $data['user_id']=Auth::User()->id;
                        $data['activity']='imported '.$ic.' Cases';
                        $data['type']='staff';
                        $data['action']='import';
                        $CommonController= new CommonController();
                        $CommonController->addMultipleHistory($data);

                    }  catch (\Exception $e) {
                        $errorString='<ul><li>'.$e->getMessage().' on line number '.$e->getLine().'</li></ui>';
                        $ClientCompanyImport->error_code=$errorString;
                        $ClientCompanyImport->status=2;
                        $ClientCompanyImport->save();
                    }
                }else{
                    return response()->json(['errors'=>'Wrong file use for imports because columns are not matched. Make sure that you are copying the data into the right columns. Please Use Legal Case Import Template Spreadsheet... ',]);
                    exit;
                }
                }
            }
            return response()->json(['errors'=>'']);
            exit;
        }
    }

    public function loadImportCasesHistory()
    {   
        $columns = array('id', 'file_name', 'case_desc', 'case_number', 'case_status','case_unique_number');
        $requestData= $_REQUEST;
        $hisotryImport = ClientCompanyImport::join("users","client_company_import.created_by","=","users.id")->select('client_company_import.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_role as userrole");
        $hisotryImport = $hisotryImport->where("client_company_import.firm_id",Auth::User()->firm_name);
        $hisotryImport = $hisotryImport->where("client_company_import.import_for",'case');
        $totalData=$hisotryImport->count();
        $totalFiltered = $totalData; 
        
        $hisotryImport = $hisotryImport->offset($requestData['start'])->limit($requestData['length']);
        $hisotryImport = $hisotryImport->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $hisotryImport = $hisotryImport->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $hisotryImport 
        );
        echo json_encode($json_data);  
    }

    public function imports_cases(Request $request)
    {
        return view('import.import_cases');
    }    

    public function viewCasesLog($id)
    {
        $uid=base64_decode($id);
        $ClientCompanyImport=ClientCompanyImport::find($uid);
        $ClientCompanyImportHistory=ClientCasesImportHistory::where("client_company_import_id",$uid)->get();
        if($ClientCompanyImport['status']=="3"){
            return view('import.view_cases_revert_log',compact('ClientCompanyImport','ClientCompanyImportHistory'));
        }else{
            return view('import.view_cases_log',compact('ClientCompanyImport','ClientCompanyImportHistory'));
        }

    }

    public function revertImportCases(Request $request)
    {
        $importID = $request->import_id;
        $ClientCompanyImportHistory = ClientCasesImportHistory::where("client_company_import_id",$request->import_id)->get();
        
        foreach($ClientCompanyImportHistory as $key => $history){            
            CaseStaff::where('case_id',$history->case_id)->delete();
            ClientNotes::where('case_id',$history->case_id)->delete();
            CaseStageUpdate::where('case_id',$history->case_id)->delete();
            Task::where("case_id", $history->case_id)->delete();
            TaskTimeEntry::where("case_id", $history->case_id)->delete();
            ExpenseEntry::where("case_id", $history->case_id)->delete();
            CaseNotes::where("case_id", $history->case_id)->delete();
            Invoices::where("case_id", $history->case_id)->delete();
            CaseEvent::where("case_id", $history->case_id)->delete();
            Messages::where("case_id", $history->case_id)->delete();
            CaseMaster::where("id", $history->case_id)->delete();
        }
        ClientCompanyImport::where('id',$request->import_id)->update(['status'=>"3"]);
        return response()->json(['errors'=>'']);
        exit;
    }

    public function exportFullBackup(Request $request)
    {
        $ClientFullBackup = ClientFullBackup::where('export_for', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        return view('import.exports', compact('ClientFullBackup'));
    }
    public function loadFullBackupHistory()
    {
        $columns = array('id', 'file_name', 'options', 'status', 'download_link');
        $requestData= $_REQUEST;
        $hisotryImport = ClientFullBackup::where("export_for",Auth::User()->id);
        $totalData=$hisotryImport->count();
        $totalFiltered = $totalData; 
        
        $hisotryImport = $hisotryImport->offset($requestData['start'])->limit($requestData['length']);
        $hisotryImport = $hisotryImport->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $hisotryImport = $hisotryImport->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $hisotryImport 
        );
        echo json_encode($json_data); 
    }
    
    public function backupCases(Request $request){

        $options = '.csv files, ';
        $options .= ($request->export_cases == 0) ? 'My cases, ' : 'All cases, ';
        $options .= ($request->include_archived == 1) ? 'Includes archived items, ' : '';
        $options .= ($request->include_mail == 1) ? 'Send me an email when the backup is finished' : '';

        $storage_path = '/backup/'.convertUTCToUserDate(date("Y-m-d"), auth()->user()->user_timezone)->format('Y-m-d').'/'.Auth::User()->firm_name;
        $firmData=Firm::find(Auth::User()->firm_name);
        $zipFileName = $storage_path . '/' . str_replace(" ","_",$firmData->firm_name)."-".Auth::User()->id."-full-backup-".convertUTCToUserDate(date("Y-m-d"), auth()->user()->user_timezone)->format('m-d-Y') . '.zip';
        $zipPath = asset($zipFileName);

        $clientFullBackup = New ClientFullBackup();
        $clientFullBackup->file_name = str_replace(" ","_",$firmData->firm_name)."-".Auth::User()->id."-full-backup-".convertUTCToUserDate(date("Y-m-d"), auth()->user()->user_timezone)->format('m-d-Y');
        $clientFullBackup->export_for = Auth::User()->id;
        $clientFullBackup->options = $options;
        $clientFullBackup->request_json = json_encode($request->all());
        $clientFullBackup->status = 1;
        $clientFullBackup->download_link = asset($zipFileName);
        $clientFullBackup->save();
        
        $authUser = auth()->user();

        \App\Jobs\FullBackUpOfApplication::dispatch($clientFullBackup, $zipPath, $zipFileName, $request->all(), $authUser);

        return response()->json(['errors'=>'','url'=>'', 'msg'=>"Your backup is being created. Building File in backgourd..."]);
        exit;        
    }

    /**
     * Get trust allocation list
     */
    public function listTrustAllocation(Request $request)
    {
        $userProfile = User::where("id", $request->client_id)->where("firm_name", auth()->user()->firm_name)->with('clientCases', 'userAdditionalInfo')->first();
        $case = $userProfile->clientCases;
        $UsersAdditionalInfo = $userProfile->userAdditionalInfo;
        return view("client_dashboard.billing.load_trust_allocation_list", compact('case', 'UsersAdditionalInfo'));
    }

    /**
     * Save minimum trust balance of client cases
     */
    public function saveMinTrustBalance(Request $request)
    {
        // return $request->all();
        if(!$request->case_id && $request->client_id) {
            UsersAdditionalInfo::where("user_id", $request->client_id)->update(["minimum_trust_balance" => $request->min_balance]);
        } else {
            CaseClientSelection::where("case_id", $request->case_id)->where("selected_user", $request->client_id)->update(["minimum_trust_balance" => $request->min_balance]);
        }
        return response()->json(['errors'=>'', 'msg'=>"Minimum trust balance successfully updated"]);
    }

    /**
     * Get trust allocation detail of client's case
     */
    public function getTrustAllocationDetail(Request $request)
    {
        // return $request->all();
        $clientCaseInfo = CaseClientSelection::where("case_id", $request->case_id)->where("selected_user", $request->client_id)->with('case')->first();
        $userAddInfo = UsersAdditionalInfo::where("user_id", $request->client_id)->first();
        $page = $request->page;
        return view("client_dashboard.billing.load_trust_allocation_detail", compact('clientCaseInfo', 'userAddInfo', 'page'));
    }

    /**
     * save trust allocation data
     */
    public function saveTrustAllocation(Request $request)
    {
        // return $request->all();
        $clientCaseInfo = CaseClientSelection::where("case_id", $request->case_id)->where("selected_user", $request->client_id)->first();
        $diffAmt = $clientCaseInfo->allocated_trust_balance - $request->allocated_balance;
        $clientCaseInfo->fill(["allocated_trust_balance" => $request->allocated_balance])->save();
        $diffAmtAbs = abs($diffAmt);
        if($diffAmtAbs > 0) {
            $userAddInfo = UsersAdditionalInfo::where("user_id", $request->client_id)->first();
            $case = CaseMaster::whereId($request->case_id)->first();
            if($case) {
                $case->fill([
                    'total_allocated_trust_balance' => ($diffAmt > 0) ? ($case->total_allocated_trust_balance - $diffAmtAbs) : ($case->total_allocated_trust_balance + $diffAmtAbs),
                ])->save();
            }
            TrustHistory::create([
                "client_id" => $request->client_id,
                "payment_method" => 'Trust Allocation',
                "amount_paid" => $diffAmtAbs,
                "current_trust_balance" => $userAddInfo->trust_account_balance,
                "payment_date" => date('Y-m-d'),
                "notes" => ($diffAmt > 0) ? 'Deallocate Trust Funds from #'.$request->case_id : 'Allocate Trust Funds from #'.$request->client_id,
                "fund_type" => ($diffAmt > 0) ? 'deallocate_trust_fund' : 'allocate_trust_fund',
                "allocated_to_case_id" => $request->case_id,
                "created_by" => Auth::user()->id,
            ]);
            return response()->json(['errors'=>'', 'msg'=>"Balance allocation is successful"]);
        }
        return response()->json(['errors'=>'', 'msg'=>""]);
    }


}
  
