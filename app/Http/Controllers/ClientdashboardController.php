<?php

namespace App\Http\Controllers;
use App\User,App\EmailTemplate,App\Countries;
use Illuminate\Http\Request;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\ContractUserCase,App\CaseMaster,App\ContractUserPermission,App\ContractAccessPermission;
use App\DeactivatedUser,App\ClientGroup,App\UsersAdditionalInfo,App\CaseClientSelection,App\CaseStaff,App\TempUserSelection;
use App\Firm,App\ClientActivity,App\ClientNotes;
use Illuminate\Support\Facades\Crypt;
use App\Task,App\CaseTaskReminder,App\CaseTaskLinkedStaff,App\TaskChecklist;
use App\TaskReminder,App\TaskActivity,App\TaskTimeEntry,App\TaskComment;
use App\TaskHistory,App\LeadAdditionalInfo;
use App\TrustHistory,App\RequestedFund,App\Messages;
use mikehaertl\wkhtmlto\Pdf;
use ZipArchive,File;
use App\ClientCompanyImport,App\ClientCompanyImportHistory;
class ClientdashboardController extends BaseController
{
    public function __construct()
    {
        // $this->middleware("auth");
    }
    public function clientDashboardView(Request $request,$id)
    {
        Session::forget('caseLinkToClient');
        Session::forget('clientId');
        $contractUserID=$client_id=$id;
        $userProfile = User::select("users.*","countries.name as countryname")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$contractUserID)->where("users.firm_name",Auth::User()->firm_name)->first();
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
           

            $case =  CaseMaster::join('case_client_selection','case_master.id','=','case_client_selection.case_id')
            ->select("case_master.case_title","case_master.id as cid","case_master.case_unique_number as case_unique_number")
            ->where('case_client_selection.selected_user',$client_id)
            ->where("case_master.is_entry_done","1")
            ->where("case_close_date",NULL)
            ->groupBy("case_master.id")  
            ->orderBy("case_master.id","DESC")  
            ->get();
            
            $closed_case =CaseMaster::join('case_client_selection','case_master.id','=','case_client_selection.case_id')
            ->select("case_master.case_title","case_master.id as cid","case_master.case_unique_number as case_unique_number")
            ->where('case_client_selection.selected_user',$client_id)
            ->where("case_master.is_entry_done","1")
            ->where("case_close_date","!=",NULL)
            ->groupBy("case_master.id")  
            ->orderBy("case_master.id","DESC")  
            ->get();
             


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
            $token=BASE_URL.'activate_account/web_token?='.$user->token."&security_patch=".Crypt::encryptString($email);
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
        ->where("case_master.is_entry_done","1")
        ->where("case_close_date",NULL)
        ->orwhere("case_close_date","!=",NULL);
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
        $UserType=User::find($client_id);
        $user_level=$UserType['user_level'];
        return view('client_dashboard.addExistingCase',compact('client_id','user_level'));
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
                    return response()->json(['errors'=>['Client name is already linked to this case']]);
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
    
    
                    return response()->json(['errors'=>'','user_id'=>$request->client_id]);
                    exit;
                }else{
                    return response()->json(['errors'=>['Client name is already linked to this case']]);
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

        return view('client_dashboard.addNoteForDashboard',compact('CaseMasterClient','CaseMasterCompany','CaseMasterData','client_id','note_id','case_id'));
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
            
            $LeadNotes->note_date=date('Y-m-d',strtotime($request->note_date));
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
            $LeadNotes->note_date=date('Y-m-d',strtotime($request->note_date));
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
            $LeadNotes->note_date=date('Y-m-d',strtotime($request->note_date));
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
            if($dataNotes['client_id']!=NULL){
                $data['client_id']=$dataNotes['client_id'];
                $data['notes_for_client']=$dataNotes['client_id'];
            }else if($dataNotes['case_id']!=NULL){
                $data['case_id']=$dataNotes['case_id'];
                $data['notes_for_case']=$dataNotes['case_id'];
            }else if($dataNotes['company_id']!=NULL){
                $data['company_id']=$dataNotes['company_id'];
                $data['notes_for_company']=$dataNotes['company_id'];
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
        $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();
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
            $TaskTimeEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
            $TaskTimeEntry->entry_rate=str_replace(",","",$request->rate_field_id);
            $TaskTimeEntry->rate_type=$request->rate_type_field_id;
            $TaskTimeEntry->duration =$request->duration_field;
            $TaskTimeEntry->created_by=Auth::User()->id; 
            $TaskTimeEntry->save();
            return response()->json(['errors'=>'','id'=>$TaskTimeEntry->id]);
        exit;
        }
    } 

    public function savebulkTimeEntry(Request $request)
    {
        
        $validator = \Validator::make($request->all(), [
            'case_or_lead' => 'required',
            'staff_user' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            for($i=1;$i<=count($request->case_or_lead)-1;$i++){
                $TaskTimeEntry = new TaskTimeEntry; 
                $TaskTimeEntry->case_id =$request->case_or_lead[$i];
                $TaskTimeEntry->user_id =$request->staff_user;
                $TaskTimeEntry->activity_id=$request->activity[$i];
                if($request->billable[$i]=="on"){
                    $TaskTimeEntry->time_entry_billable="yes";
                }else{
                    $TaskTimeEntry->time_entry_billable="no";
                }
                $TaskTimeEntry->description=$request->description[$i];
                $TaskTimeEntry->entry_date=date('Y-m-d',strtotime($request->start_date));
                $TaskTimeEntry->entry_rate=Auth::User()->default_rate;
                $TaskTimeEntry->rate_type='hr';
                $TaskTimeEntry->duration =$request->duration[$i];
                $TaskTimeEntry->created_by=Auth::User()->id; 
                $TaskTimeEntry->save();
            }
            return response()->json(['errors'=>'','id'=>$TaskTimeEntry->id]);
        exit;
        }
    } 

    public function loadTrustHistory()
    {   
        $requestData= $_REQUEST;

        
       
        $allLeads = TrustHistory::leftJoin('users','trust_history.client_id','=','users.id');
        $allLeads = $allLeads->leftJoin('users as u1','trust_history.client_id','=','u1.id');
        $allLeads = $allLeads->leftJoin('users_additional_info as u2','trust_history.client_id','=','u2.id');
        $allLeads = $allLeads->select("users.user_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as client_name'),DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as note_created_by'),"u1.user_title as created_by_user_title","trust_history.*","trust_history.client_id as client_id","u2.minimum_trust_balance","u2.trust_account_balance");        
        $allLeads = $allLeads->where("trust_history.client_id",$requestData['user_id']);   
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
     
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy('trust_history.id','DESC');
        $allLeads = $allLeads->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allLeads 
        );
        echo json_encode($json_data);  
    }

    public function addTrustEntry(Request $request)
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
            $TrustInvoice->created_by=Auth::user()->id; 
            $TrustInvoice->save();

            $firmData=Firm::find(Auth::User()->firm_name);
            $msg="Thank you. Your deposit of $".number_format($request->amount,2)." has been sent to ".$firmData['firm_name']." ";
            return response()->json(['errors'=>'','msg'=>$msg]);
            exit;   
        }
    }

    
    public function withdrawFromTrust(Request $request)
    {
        $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($request->user_id);
        $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$request->user_id)->first();
        return view('client_dashboard.billing.withdrawTrustEntry',compact('userData','UsersAdditionalInfo'));     
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
            $TrustInvoice->payment_method=$request->payment_method;
            $TrustInvoice->amount_paid="0.00";
            $TrustInvoice->withdraw_amount=$request->amount;
            $TrustInvoice->current_trust_balance=$UsersAdditionalInfo->trust_account_balance;
            $TrustInvoice->payment_date=date('Y-m-d',strtotime($request->payment_date));
            $TrustInvoice->notes=$request->notes;
            $TrustInvoice->fund_type='withdraw';
            $TrustInvoice->created_by=Auth::user()->id; 
            if(isset($request->select_account)){
                $TrustInvoice->withdraw_from_account=$request->select_account;
            }
            $TrustInvoice->refund_ref_id=$request->transaction_id;
            $TrustInvoice->save();
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
        $request['amount']=str_replace(",","",$request->amount);
        $GetAmount=TrustHistory::find($request->transaction_id);
        if($GetAmount->fund_type=="withdraw"){
            $mt=$GetAmount->withdraw_amount;
        }else{
            $mt=$GetAmount->amount_paid;
        } 
        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric|max:'.$mt,
        ],[
            'amount.max' => 'Refund cannot be more than $'.number_format($mt,2),
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                
            if($GetAmount->fund_type=="withdraw"){
                DB::table('users_additional_info')->where('user_id',$request->client_id)->increment('trust_account_balance', $request['amount']);
                $GetAmount->is_refunded="yes";
                $GetAmount->save();

            }else{
                DB::table('users_additional_info')->where('user_id',$request->client_id)->decrement('trust_account_balance', $request['amount']);
                $GetAmount->is_refunded="yes";
                $GetAmount->save();
            }
            
            $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance")->where("user_id",$request->client_id)->first();
       
            $TrustInvoice=new TrustHistory;
            $TrustInvoice->client_id=$request->client_id;
            $TrustInvoice->payment_method='Trust Refund';
            $TrustInvoice->amount_paid="0.00";
            $TrustInvoice->withdraw_amount="0.00";
            $TrustInvoice->refund_amount=$request['amount'];
            $TrustInvoice->current_trust_balance=$UsersAdditionalInfo->trust_account_balance;
            $TrustInvoice->payment_date=date('Y-m-d',strtotime($request->payment_date));
            $TrustInvoice->notes=$request->notes;
            if($GetAmount->fund_type=="withdraw"){
                $TrustInvoice->fund_type='refund_withdraw';
            
            }else{
                $TrustInvoice->fund_type='refund_deposit';
            
            }
            $TrustInvoice->refund_ref_id=$request->transaction_id;
            $TrustInvoice->created_by=Auth::user()->id; 
            $TrustInvoice->save();
            session(['popup_success' => 'Withdraw fund successful']);
            return response()->json(['errors'=>'']);
            exit;   
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
            $TrustInvoice=TrustHistory::find($request->payment_id);
            if($TrustInvoice->fund_type=="refund_deposit"){
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->increment('trust_account_balance', $TrustInvoice->refund_amount);

                $updateRedord=TrustHistory::find($TrustInvoice->refund_ref_id);
                $updateRedord->is_refunded="no";
                $updateRedord->save();

            }else if($TrustInvoice->fund_type=="refund_withdraw"){
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->decrement('trust_account_balance', $TrustInvoice->refund_amount);
                $updateRedord=TrustHistory::find($TrustInvoice->refund_ref_id);
                $updateRedord->is_refunded="no";
                $updateRedord->save();
            }else if($TrustInvoice->fund_type=="diposit"){
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->decrement('trust_account_balance', $TrustInvoice->amount_paid);
            }


            $updateBalaance=UsersAdditionalInfo::where("user_id",$TrustInvoice->client_id)->first();
            if($updateBalaance['trust_account_balance']<=0){
                DB::table('users_additional_info')->where('user_id',$TrustInvoice->client_id)->update(['trust_account_balance'=> "0.00"]);
            }

            TrustHistory::where('id',$request->payment_id)->delete();
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
            $allHistory = $allHistory->where("trust_history.client_id",$id); 
            if(isset($request->from_date) && isset($request->to_date)){
                $allHistory = $allHistory->whereBetween('trust_history.payment_date', [date('Y-m-d',strtotime($request->from_date)), date('Y-m-d',strtotime($request->to_date))]); 
            }  
            $allHistory = $allHistory->orderBy('trust_history.payment_date','ASC');
            $allHistory = $allHistory->get();
            // return view('client_dashboard.billing.trustHistoryPdf',compact('userData','country','firmData','firmAddress','UsersAdditionalInfo','allHistory'));

            $filename='trust_export_'.time().'.pdf';
            $PDFData=view('client_dashboard.billing.trustHistoryPdf',compact('userData','country','firmData','firmAddress','UsersAdditionalInfo','allHistory'));
            $pdf = new Pdf;
            if($_SERVER['SERVER_NAME']=='localhost'){
                $pdf->binary = EXE_PATH;
            }
            $pdf->addPage($PDFData);
            $pdf->setOptions(['javascript-delay' => 5000]);
            $pdf->setOptions(["footer-right"=> "Page [page] from [topage]"]);
            // $pdf->setOptions(["footer-left"=> "Completed on ". date('m/d/Y',strtotime($caseIntakeForm['submited_at']))]);
            $pdf->saveAs(public_path("download/pdf/".$filename));
            $path = public_path("download/pdf/".$filename);
            // return response()->download($path);
            // exit;
            return response()->json([ 'success' => true, "url"=>url('public/download/pdf/'.$filename),"file_name"=>$filename,'errors'=>''], 200);
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
        $allLeads = $allLeads->get();
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
        //Get all client related to firm
        $ClientList = User::select("email","first_name","last_name","id","user_level",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where('user_level',2)->where("parent_user",Auth::user()->id)->get();

        //Get all company related to firm
        $CompanyList = User::select("email","first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        
        $userData=User::select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as cname'),"id")->find($request->user_id);
        $UsersAdditionalInfo=UsersAdditionalInfo::select("trust_account_balance","minimum_trust_balance")->where("user_id",$request->user_id)->first();
        
        return view('client_dashboard.billing.addFundRequestEnrty',compact('ClientList','CompanyList','client_id','userData','UsersAdditionalInfo'));     
        exit;    
    } 

    public function reloadAmount(Request $request)
    {
        $client_id=$request->user_id;
        $UsersAdditionalInfo=UsersAdditionalInfo::select("user_id","trust_account_balance","minimum_trust_balance")->where("user_id",$client_id)->first(); 
        $trust_account_balance=$minimum_trust_balance=0.00;
        if(!empty($UsersAdditionalInfo)){
            $trust_account_balance=number_format($UsersAdditionalInfo->trust_account_balance,2);
            $minimum_trust_balance=number_format($UsersAdditionalInfo->minimum_trust_balance,2);
        }
        return response()->json(['errors'=>'','freshData'=>$UsersAdditionalInfo,'trust_account_balance'=>$trust_account_balance,'minimum_trust_balance'=>$minimum_trust_balance]);
        exit;

    } 

    public function saveRequestFundPopup(Request $request)
    {
        $request['amount']=str_replace(",","",$request->amount);

        $validator = \Validator::make($request->all(), [
            'contact' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
       
            $RequestedFund=new RequestedFund;
            $RequestedFund->client_id=$request->contact;
            $RequestedFund->deposit_into=$request->deposit_into;
            $RequestedFund->amount_requested=$request->amount;
            $RequestedFund->amount_due=$request->amount;
            $RequestedFund->amount_paid="0.00";
            $RequestedFund->email_message=$request->message;
            $RequestedFund->due_date=date('Y-m-d',strtotime($request->due_date));
            $RequestedFund->status='sent';
            $RequestedFund->created_by=Auth::user()->id; 
            $RequestedFund->save();

            $data=[];
            $data['deposit_id']=$RequestedFund->id;
            $data['deposit_for']=$RequestedFund->client_id;
            $data['user_id']=Auth::User()->id;
            $data['activity']='sent deposit request';
            $data['type']='deposit';
            $data['action']='add';
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
                $sendEmail = $this->sendMail($user);
                $message.="<p>You successfully requested funds from ".$nameIs." .</p>";
                $url="<a href='".BASE_URL."/contacts/clients/".$clientData->id."?load_funds=true'>Contact Billing Page</a>";
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
            return response()->json(['errors'=>'']);
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
        $request['amount']=str_replace(",","",$request->amount);
        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
       
            $RequestedFund=RequestedFund::find($request->id);
            $RequestedFund->amount_due=$request->amount;
            $RequestedFund->amount_requested=$request->amount;
            if(isset($request->due_date)){
                $RequestedFund->due_date=date('Y-m-d',strtotime($request->due_date));
            }
            $RequestedFund->save();

            $data=[];
            $data['deposit_id']=$RequestedFund->id;
            $data['deposit_for']=$RequestedFund->client_id;
            $data['user_id']=Auth::User()->id;
            $data['activity']='updated deposit request';
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
            $data['type']='deposit';
            $data['action']='delete';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            session(['popup_success' => 'Request #R-'.sprintf('%06d',$getRequestedFund->id).' deleted successfully']);
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


            $firmData=Firm::find(Auth::User()->firm_name);
            $getTemplateData = EmailTemplate::find(17);
            $mail_body = $getTemplateData->content;
            $mail_body = str_replace('{message}', date('F d, Y',strtotime($RequestedFund->due_date)), $mail_body);
            $mail_body = str_replace('{amount}', number_format($RequestedFund->amount_due,2), $mail_body);
            $mail_body = str_replace('{duedate}', date('m/d/Y',strtotime($RequestedFund->due_date)), $mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
            $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
            $mail_body = str_replace('{year}', date('Y'), $mail_body);        

            $clientData=User::find($RequestedFund->client_id);
            $user = [
                "from" => FROM_EMAIL,
                "from_title" => FROM_EMAIL_TITLE,
                "subject" => "Reminder: Request #R-".sprintf('%06d', $RequestedFund->id)." is due ".date('F d, Y',strtotime($RequestedFund->due_date))." for ".$firmData->firm_name,
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
        $loadFirmUser = User::select("first_name","last_name","id")
        ->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->orderBy("id","DESC")->get();


        //Get all active case list with client portal enabled.
        $getChildUsers =$this->getParentAndChildUserIds();
        $CaseMasterDataIds = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
        $caseIds=[];
        foreach($CaseMasterDataIds as $k=>$v){
            $caseCllientSelection = CaseClientSelection::select("case_client_selection.selected_user")
            ->where("case_client_selection.case_id",$v['id'])
            ->get()
            ->pluck("selected_user");

            $compnayIdWithEnablePortal = DB::table("users_additional_info")
            ->select("id")
            ->whereIn("user_id",$caseCllientSelection)
            ->where("client_portal_enable",1)
            ->get();
          
            if(!$compnayIdWithEnablePortal->isEmpty()){
                $caseIds[]=$v['id'];
            }
        }
        $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->whereIn('id',$caseIds)->get();
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
            if(isset($request->message['global_lawyers'])){
                //Get firm user list 
                $loadFirmUser = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->get();
                foreach($loadFirmUser as $k=>$v){
                    $this->sendMailGlobal($request->all(),$v->id);
                }
            
            
            }
            if(isset($request->message['global_clients'])){
                //Get client list with client enable portal is active
                $clientLists = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')
                ->select("first_name","last_name","users.id","user_level")
                ->where("users.user_level",2)
                ->where("parent_user",Auth::user()->id)
                ->where("lead_additional_info.client_portal_enable",1)
                ->get();
                foreach($clientLists as $k=>$v){
                    $this->sendMailGlobal($request->all(),$v->id);
                }
                
            }
            session(['popup_success' => 'Your message has been sent']);
            return response()->json(['errors'=>'']);
            exit;   
        }else{
            $validator = \Validator::make($request->all(), [
                'send_to' => 'required|array|min:1'
            ], ['min'=>'No users selected','required'=>'No users selected']);
            if ($validator->fails())
            {
                return response()->json(['errors'=>$validator->errors()->all()]);
            }else{
                foreach($request->send_to as $k=>$v){
                    $Messages=new Messages;
                    $decideCode=explode("-",$v);
                    if($decideCode[0]=='case'){
                        $Messages->case_id=$decideCode[1];
                    }else{
                        $Messages->user_id=$decideCode[1];
                    }
                    if($request->message['private_reply']=="false"){
                        $Messages->replies_is='public';
                    }else{
                        $Messages->replies_is='private';
                    }
                    $Messages->subject=$request->subject;
                    $Messages->message=$request->delta;
                    $Messages->created_by =Auth::User()->id;
                    $Messages->save();
    
                    if($decideCode[0]=='case'){
                        $caseCllientSelection = CaseClientSelection::select("case_client_selection.selected_user")
                        ->where("case_client_selection.case_id",$decideCode[1])
                        ->get()
                        ->pluck("selected_user");
                        
                        $compnayIdWithEnablePortal = DB::table("users_additional_info")
                        ->select("id")
                        ->whereIn("user_id",$caseCllientSelection)
                        ->where("client_portal_enable",1)
                        ->get();
                        foreach($compnayIdWithEnablePortal as $k=>$v){
                            $this->sendMailGlobal($request->all(),$v->id);
                        }
                    
                    }else{
                        $this->sendMailGlobal($request->all(),$decideCode[1]);
                    }
                }
                session(['popup_success' => 'Your message has been sent']);
                return response()->json(['errors'=>'']);
                exit;       
            }
           
        }
    }
    public function sendMailGlobal($request,$id)
    {
        $firmData=Firm::find(Auth::User()->firm_name);
        $getTemplateData = EmailTemplate::find(11);
        $mail_body = $getTemplateData->content;
        $senderName=Auth::User()->first_name." ".Auth::User()->last_name;
        $mail_body = str_replace('{sender}', $senderName, $mail_body);
        $mail_body = str_replace('{subject}', $request['subject'], $mail_body);
        $mail_body = str_replace('{loginurl}', BASE_URL.'login', $mail_body);
        $mail_body = str_replace('{url}', BASE_URL.'messages/t/'.$id, $mail_body);
        $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
        $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
        $mail_body = str_replace('{regards}', $firmData->firm_name, $mail_body);
        $mail_body = str_replace('{year}', date('Y'), $mail_body);        

        $clientData=User::find($id);
            if(isset($clientData->email)){
            $user = [
                "from" => FROM_EMAIL,
                "from_title" => FROM_EMAIL_TITLE,
                "subject" => "You have a new message on ".$firmData->firm_name,
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
            $user->token  = str_random(40);
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
            $token=url('firmuser/verify', $user->token);
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
                $token=BASE_URL.'activate_account/web_token?='.$user->token."&security_patch=".Crypt::encryptString($email);
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
        $file = $request->user_id."_profile" . "." . $type[0];
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
            $filename = BASE_URL.'public/import/Case_Contact_Import_Template.csv';
        }elseif($request->section=="cases"){
            $filename = BASE_URL.'public/import/Legalcase_Case_Import_Template.csv';
        }else{
             $filename =BASE_URL.'public/import/Case_Company_Import_Template.csv';
        }
        return response()->json(['errors'=>'','url'=>$filename]);
    }

    public function createAndImports(Request $request)
    {
        File::deleteDirectory(public_path('import/'.date('Y-m-d').'/'.Auth::User()->firm_name));
        if(!is_dir('public/import/'.date('Y-m-d').'/'.Auth::User()->firm_name)) {
            File::makeDirectory('public/import/'.date('Y-m-d').'/'.Auth::User()->firm_name, $mode = 0777, true, true);
        }
        if($request->format=="vcard"){
            $this->generateContactvCard($request->all());
            $CSV[] = "public/import/".date('Y-m-d').'/'.Auth::User()->firm_name."/contacts.vcf" ;

            if($request->include_companies=="1"){
                $this->generateCompanyvCard($request->all());
                $CSV[] = "public/import/".date('Y-m-d').'/'.Auth::User()->firm_name."/companies.vcf" ;
            }
        }  
        if($request->format=="outlook_csv" || $request->format=="mycase_csv"){
            $this->generateClientCSV($request->all());
            $CSV[] = "public/import/".date('Y-m-d').'/'.Auth::User()->firm_name."/contact.csv" ;
    
            if($request->include_companies=="1"){
                $CSV[] = "public/import/".date('Y-m-d').'/'.Auth::User()->firm_name."/companies.csv" ;
                $this->generateCompanyCSV($request->all());
            }
        }  
        $zip = new ZipArchive;
        $storage_path = 'public/import/'.date('Y-m-d').'/'.Auth::User()->firm_name;
        $firmData=Firm::find(Auth::User()->firm_name);
        $timeName = str_replace(" ","_",$firmData->firm_name)."-".Auth::User()->id."-contacts-".date("m-d-Y");
        $zipFileName = $storage_path . '/' . $timeName . '.zip';
        
     
        $zipPath = asset($zipFileName);
        if ($zip->open(($zipFileName), ZipArchive::CREATE) === true) {
            foreach ($CSV as $relativName) {
                $zip->addFile($relativName,basename($relativName));
            }
            $zip->close();
            if ($zip->open($zipFileName) === true) {
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
        $clientHeader=config('app.name')." ID,First Name,Middle Name,Last Name,Company,Job Title,Home Street,Home Street 2,Home City,Home State,Home Postal Code,Home Country/Region,Home Fax,Work Phone,Home Phone,Mobile Phone,Contact Group,E-mail Address,Web Page,Outstanding Trust Balance,Login Enabled,Archived,Birthday,Private Notes,License Number,License State,Welcome Message,:Notes,Cases,Case Link IDs,Created Date";
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
           if(!empty(explode(",",$clientVal->multiple_compnay_id))){
                $getCompanyList=$this->getCompanyList("$clientVal->multiple_compnay_id");
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
            $clientCsvData[]=$clientVal->uid.",".$clientVal->first_name.",".$clientVal->middle_name.",".$clientVal->last_name.",".$getCompanyList.",".$clientVal->job_title.",".$clientVal->street." " .$clientVal->apt_unit.",".$clientVal->address2.",".$clientVal->city.",".$clientVal->state.",".$clientVal->postal_code.",".$countryName.",".$clientVal->fax_number.",".$clientVal->work_phone.",".$clientVal->home_phone.",".$clientVal->mobile_number.",".$contactGroup.",".$clientVal->email.",".$webpage.",0".",".$Portal.",".$Archive.",".$DOB.",".$clientVal->notes.",".$clientVal->driver_license.",".$clientVal->license_state.",".$welcomeMsg.",".$notes.",".$cases.",".$casesLinkId.",".$createdAt.",";
        }
        // print_r($clientCsvData);
        // exit;
        
        $filename="contact.csv";
        $file_path=public_path().'/import/'.date('Y-m-d').'/'.Auth::User()->firm_name."/".$filename;   
        $file = fopen($file_path,"w+");
        foreach ($clientCsvData as $exp_data){
          fputcsv($file,explode(',',$exp_data));
        }   
        fclose($file);   
        return true; 
    }

    public function generateCompanyCSV($request){
        $CompanyCsvData=[];
        $clientHeader=config('app.name')." ID,Company,Business Street,Business Street 2,Business City,Business State,Business Postal Code,Business Country/Region,Business Fax,Company Main Phone,E-mail Address,Web Page,Outstanding Trust Balance,Archived,Private Notes,Contacts,Cases,Case Link IDs,:Notes,Created Date";
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
                $contacts=$contactList;
            }else{
                $contacts='';
            }
            $createdAt=date('m/d/Y',strtotime($clientVal->uct));
            $CompanyCsvData[]=$clientVal->uid.",".$clientVal->first_name.",".$clientVal->street." " .$clientVal->apt_unit.",".$clientVal->address2.",".$clientVal->city.",".$clientVal->state.",".$clientVal->postal_code.",".$countryName.",".$clientVal->fax_number.",".$clientVal->mobile_number.",".$clientVal->email.",".$clientVal->website.",0".",".$Archive.",".$clientVal->notes.",".$contacts.",".$cases.",".$casesLinkId.",".$notes.",".",".$createdAt.",";
        }
       
        $filename="companies.csv";
        $file_path=public_path().'/import/'.date('Y-m-d').'/'.Auth::User()->firm_name."/".$filename;   
        $file = fopen($file_path,"w+");
        foreach ($CompanyCsvData as $exp_data){
          fputcsv($file,explode(',',$exp_data));
        }   
        fclose($file);  
    }

    public function generateContactvCard($request){
      
        $user = User::select("*")->where("user_level","2")->where("parent_user",Auth::user()->id);
        if(isset($request['include_archived']) && $request['include_archived']=="1"){
            $user = $user->where("users.user_status","4");  
        }else{
            $user = $user->where("users.user_status","1"); 
        }
        $user = $user->get();
        $vCard = '';
        foreach($user as $k=>$v){
            if($v->country!=NULL){
                $countryName=$this->getCountryName($v->country);
            }else{
                $countryName="";
            }
            $vCard = "BEGIN:VCARD\r\n";
            $vCard .= "VERSION:3.0\r\n";
            $vCard .= "N:".$v->last_name.";".$v->first_name.";".$v->middle_name.";\r\n";
            $vCard .= "FN:".$v->first_name." ".$v->middle_name." ".$v->last_name."\r\n";
            $vCard .= "ADR:TYPE=work,pref:".$v->street.";".$v->apt_unit.";".$v->city.";".$v->state.";".$v->postal_code.";".$countryName.";\r\n";
            $vCard .= "EMAIL;TYPE=work,pref:".$v->email."\r\n";
            $vCard .= "TEL;TYPE=work,voice:".$v->mobile_number."\r\n"; 
            $vCard .= "END:VCARD\r\n";

        }

        $filePath = 'public/import/'.date('Y-m-d').'/'.Auth::User()->firm_name."/contacts.vcf"; // you can specify path here where you want to store file.
        $file = fopen($filePath,"w");
        fwrite($file,$vCard);
        fclose($file);

    }

    public function generateCompanyvCard($request){
      
        $user = User::select("*")->where("user_level","4")->where("parent_user",Auth::user()->id);
        if(isset($request['include_archived']) && $request['include_archived']=="1"){
            $user = $user->where("users.user_status","4");  
        }else{
            $user = $user->where("users.user_status","1"); 
        }
        $user = $user->get();
        $vCard = '';
        foreach($user as $k=>$v){
            if($v->country!=NULL){
                $countryName=$this->getCountryName($v->country);
            }else{
                $countryName="";
            }
            $vCard = "BEGIN:VCARD\r\n";
            $vCard .= "VERSION:3.0\r\n";
            $vCard .= "N:".$v->first_name.";\r\n";
            $vCard .= "FN:".$v->first_name."\r\n";
            $vCard .= "ADR:TYPE=work,pref:".$v->street.";".$v->apt_unit.";".$v->city.";".$v->state.";".$v->postal_code.";".$countryName.";\r\n";
            $vCard .= "EMAIL;TYPE=work,pref:".$v->email."\r\n";
            $vCard .= "TEL;TYPE=work,voice:".$v->mobile_number."\r\n"; 
            $vCard .= "END:VCARD\r\n";
        }
        $filePath = 'public/import/'.date('Y-m-d').'/'.Auth::User()->firm_name."/companies.vcf"; // you can specify path here where you want to store file.
        $file = fopen($filePath,"w");
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

            if($request->import_format=="csv"){
         
                $path = $request->file('upload_file')->getRealPath();
                $csv_data = array_map('str_getcsv', file($path));
                $ClientCompanyImport->file_type="2";
                $ClientCompanyImport->save(); 
                $UserArray=[];
               if(!empty($csv_data)){
                    if($csv_data[0][0]=="first_name"){
                        $user_level="2";
                        unset($csv_data[0]);
                        $ClientCompanyImport->total_record=count($csv_data);
                        $ClientCompanyImport->save();
                        foreach($csv_data as $key=>$val){
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
                                $errorString.="<li>Mobile number was invalid: ".$finalOperationVal['mobile_number']."</li>";
                            }
                            
                            if(!is_numeric($finalOperationVal['home_phone'])){
                                $errorString.="<li>Home phone was invalid: ".$finalOperationVal['home_phone']."</li>";
                            }
                            
                            if(!is_numeric($finalOperationVal['work_phone'])){
                                $errorString.="<li>Work phone was invalid: ".$finalOperationVal['work_phone']."</li>";
                            }

                            if(!is_numeric($finalOperationVal['fax_number'])){
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
                        $ClientCompanyImport->save();
                       
                        
                    }else{
                        $user_level="4";
                        unset($csv_data[0]);
                        $ClientCompanyImport->total_record=count($csv_data);
                        $ClientCompanyImport->save();
                        foreach($csv_data as $key=>$val){
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
                            $UserArray[$key]['notes']=$val[12];
                            $UserArray[$key]['user_level']=$user_level;
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
                        $ClientCompanyImport->save();
                    }
                  
                }
            }
            if($request->import_format=="vcf"){
                $ClientCompanyImport->file_type="1";
                $ClientCompanyImport->save();

                $path = $request->file('upload_file')->getRealPath();
                $csv_data = array_map('str_getcsv', file($path));
                // echo implode("",$csv_data[0]);
                // print_r($csv_data);exit;
                $ClientCompanyImport->file_type="2";
                $ClientCompanyImport->save(); 
                $arrayGroup=[];
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
                if(count($arrayGroup)>1){
                    foreach($arrayGroup as $finalOperationKey=>$finalOperationVal){

                        $org=explode(":",$finalOperationVal[1]);
                        if($org[0]=="ORG"){
                            $userLevel=4;
                            $fullNameString[1]=$org[1];
                            $fullName=$org[1];
                            $User=new User;
                            $User->first_name=$fullName;
                        }else{
                            $userLevel=2;
                            $fullNameString=explode(":",$finalOperationVal[1]);
                            $fullName=explode(" ",$fullNameString[1]);
                            $User=new User;
                            $User->first_name=$fullName[0];
                            $User->middle_name=$fullName[1];
                            $User->last_name=$fullName[2];
                        }
                        
                        $email=explode(":",$finalOperationVal[3]);
                        if($email[0]=="EMAIL;TYPE=home"){
                            $User->email=$email[1];
                        }
                        $User->user_level=$userLevel;
                        $User->parent_user=Auth::User()->id;
                        $User->firm_name=Auth::User()->firm_name;
                        $User->save();

                        $UsersAdditionalInfo= new UsersAdditionalInfo;
                        $UsersAdditionalInfo->user_id=$User->id; 
                        $notes=explode(":",$finalOperationVal[4]);
                        if($notes[0]=="NOTE"){
                            $UsersAdditionalInfo->notes=$notes[1];
                        }
                        $UsersAdditionalInfo->created_by =Auth::User()->id;
                        $UsersAdditionalInfo->save();


                        $ClientCompanyImportHistory=new ClientCompanyImportHistory;
                        $ClientCompanyImportHistory->client_company_import_id=$ClientCompanyImport->id;
                        $ClientCompanyImportHistory->full_name=$fullNameString[1];
                        $ClientCompanyImportHistory->company_name=NULL;
                        $ClientCompanyImportHistory->email=$email[1];
                        $ClientCompanyImportHistory->contact_group=NULL;
                        $ClientCompanyImportHistory->outstanding_amount=0;
                        $ClientCompanyImportHistory->status="1";
                        $ClientCompanyImportHistory->warning_list=NULL;
                        $ClientCompanyImportHistory->firm_id=Auth::User()->firm_name;
                        $ClientCompanyImportHistory->created_by=Auth::User()->id;
                        $ClientCompanyImportHistory->save();
                        
                    }
                }else{

                    $ClientCompanyImport->error_code=implode("",$csv_data[0]);
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
                    $companyUser->user_title="";
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
            $country = Countries::where("name",$cid)->first();
            return $country['id'];
        }else{
            return "";
        }
    }
    
    public function getContactGroupId($cgroup){
        if($cgroup!=NULL){
            $name=ClientGroup::select("group_name")->where("group_name",$cgroup)->first();
            return $name['id'];
        }else{
            return "";

        }
    }

    public function loadImportHistory()
    {   
        $columns = array('id', 'file_name', 'case_desc', 'case_number', 'case_status','case_unique_number');
        $requestData= $_REQUEST;
        $hisotryImport = ClientCompanyImport::join("users","client_company_import.created_by","=","users.id")->select('client_company_import.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_role as userrole");
        $hisotryImport = $hisotryImport->where("client_company_import.firm_id",Auth::User()->firm_name);
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
    
}
  
