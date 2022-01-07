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
use App\TrustHistory,App\RequestedFund,App\Messages;
use mikehaertl\wkhtmlto\Pdf;
class CompanydashboardController extends BaseController
{
    public function __construct()
    {
        // $this->middleware("auth");
    }
    public function companyDashboardView(Request $request,$id)
    {
        Session::forget('caseLinkToClient');
        Session::forget('clientId');
        $contractUserID=$client_id=$company_id=$id;
        $userProfile = User::select("users.*","countries.name as countryname")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$contractUserID)->where("users.firm_name",Auth::User()->firm_name)->with("userCreditAccountHistory", "clientCases")->first();

        if(empty($userProfile)){
            $User= User::find($id);
            $User->firm_name =Auth::User()->firm_name;
            $User->save();
            $userProfile = User::select("users.*","countries.name as countryname")->leftJoin('countries','users.country',"=","countries.id")->where("users.id",$contractUserID)->where("users.firm_name",Auth::User()->firm_name)->first();

        }

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
            if(empty($UsersAdditionalInfo)){
                $UsersAdditionalInfo= new UsersAdditionalInfo;
                $UsersAdditionalInfo->user_id=$id; 
                $UsersAdditionalInfo->created_by =Auth::User()->id;
                $UsersAdditionalInfo->save();
            }

            $companyList = User::select("users.first_name","users.id")->whereIn("users.id",explode(",",$UsersAdditionalInfo['multiple_compnay_id']))->get();

            //Get Active Case List
            $getCompanyWiseClientList=$this->getCompanyWiseCaseList($company_id);
            $CaseClientSelection = CaseClientSelection::select("case_id")->whereIn("selected_user",$getCompanyWiseClientList)->get()->pluck('case_id');
            // $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid") ->whereIn("case_master.id",$CaseClientSelection)->where("case_master.is_entry_done","1")->where("case_close_date",NULL)->get(); 
            $case = $userProfile->clientCases;
            $closed_case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid") ->whereIn("case_master.id",$CaseClientSelection)->where("case_master.is_entry_done","1")->where("case_close_date","!=",NULL)->get(); 

            $caseCllientSelection =UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
            ->select("first_name","middle_name","last_name","users.id","user_level","email")->where("users.user_level","2")->where("parent_user",Auth::user()->id)->whereRaw("find_in_set($client_id,`multiple_compnay_id`)")->get();



            $totalData=$clientLinkCount=$clientArchiveLinkCount=0;
            if(\Route::current()->getName()=="contacts_company_client"){
                $clientList = UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
                ->select("first_name","middle_name","last_name","users.id","user_level")->where("users.user_level","2");
                $clientList = $clientList->where("parent_user",Auth::user()->id);
                $clientList = $clientList->whereIn("users.user_status",[1,2,3]);
                $clientList = $clientList->whereRaw("find_in_set($client_id,`multiple_compnay_id`)");
                 $clientLinkCount=$clientList->count();

                 $clientArchiveList = UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
                 ->select("first_name","middle_name","last_name","users.id","user_level")->where("users.user_level","2");
                 $clientArchiveList = $clientArchiveList->where("parent_user",Auth::user()->id);
                 $clientArchiveList = $clientArchiveList->where("users.user_status",4);
                 $clientArchiveList = $clientArchiveList->whereRaw("find_in_set($client_id,`multiple_compnay_id`)");
                  $clientArchiveLinkCount=$clientArchiveList->count();
                }
            if(\Route::current()->getName()=="contacts_company_billing_trust_request_fund"){
                $allLeads = RequestedFund::leftJoin('users','requested_fund.client_id','=','users.id');
                $allLeads = $allLeads->leftJoin('users as u1','requested_fund.client_id','=','u1.id');
                $allLeads = $allLeads->leftJoin('users_additional_info as u2','requested_fund.client_id','=','u2.id');
                $allLeads = $allLeads->select("users.user_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as client_name'),DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as note_created_by'),"u1.user_title as created_by_user_title","requested_fund.*","requested_fund.client_id as client_id","u2.minimum_trust_balance","u2.trust_account_balance");        
                $allLeads = $allLeads->where("requested_fund.client_id",$contractUserID);   
                $totalData=$allLeads->count();
            }
           

            return view('company_dashboard.companyView',compact('userProfile','userProfileCreatedBy','id','companyList','UsersAdditionalInfo','client_id','totalData','clientLinkCount','company_id','case','closed_case','caseCllientSelection','clientArchiveLinkCount'));
        }

    } 
    public function clientList()
    {   
        $columns = array('id');
        $requestData= $_REQUEST;
        $clientList = UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
        ->select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'),"users.id","user_level")->where("users.user_level","2");
        $clientList = $clientList->where("parent_user",Auth::user()->id);
        $clientList = $clientList->whereIn("users.user_status",[1,2,3]); //1 Active
        $clientList = $clientList->whereRaw("find_in_set($requestData[company_id],`multiple_compnay_id`)");
        $totalData=$clientList->count();
        $totalFiltered = $totalData; 
        
        $clientList = $clientList->offset($requestData['start'])->limit($requestData['length']);
        $clientList = $clientList->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $clientList = $clientList->get()->each->setAppends(['caselist', 'lastloginnewformate']);
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $clientList 
        );
        echo json_encode($json_data);  
    }

    public function clientArchiveList()
    {   
        $columns = array('id');
        $requestData= $_REQUEST;
        $clientList = UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
        ->select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'),"users.id","user_level")->where("users.user_level","2");
        $clientList = $clientList->where("parent_user",Auth::user()->id);
        $clientList = $clientList->where("users.user_status",4); //1 Active
        $clientList = $clientList->whereRaw("find_in_set($requestData[company_id],`multiple_compnay_id`)");
        $totalData=$clientList->count();
        $totalFiltered = $totalData; 
        
        
        $clientList = $clientList->offset($requestData['start'])->limit($requestData['length']);
        $clientList = $clientList->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $clientList = $clientList->get()->each->setAppends(['caselist', 'lastloginnewformate']);
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $clientList 
        );
        echo json_encode($json_data);  
    }
    public function addExistingContact(Request $request)
    {
        $company_id=$request->company_id;
        return view('company_dashboard.addExistingContact',compact('company_id'));
    }
    public function loadClientData(Request $request)
    {
        
        $clientList = UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
        ->select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as fullname'),"users.id","user_level")
        ->where("users.user_level","2")->where("parent_user",Auth::user()->id)->where("firm_name",Auth::user()->firm_name);
        if($request->search!=""){
            $clientList = $clientList->where(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name)'),'LIKE',"%$request->search%");
        }
        $clientList = $clientList->get();
        
        return response()->json(["total_count"=>$clientList->count(),"incomplete_results"=>false,"items"=>$clientList]);
    }
    public function saveLinkContact(Request $request)
    {
      
        $validator = \Validator::make($request->all(), [
            'company_id' => 'required|numeric',
            'client_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $isLinked= UsersAdditionalInfo::where('user_id',$request->client_id)->whereRaw("find_in_set($request->company_id,`multiple_compnay_id`)")->count();
            if($isLinked<=0){
                $UsersAdditionalInfo=UsersAdditionalInfo::select("*")->where("user_id",$request->client_id)->first();
                if($UsersAdditionalInfo['multiple_compnay_id']==NULL){
                    $mCompnay=array();
                    $mCompnay[0]=$request->company_id;
                }else{
                    $mCompnay=explode(",",$UsersAdditionalInfo['multiple_compnay_id']);
                   array_push($mCompnay,$request->company_id);
                }
                $UsersAdditionalInfo=UsersAdditionalInfo::find($UsersAdditionalInfo['id']);
                $UsersAdditionalInfo->multiple_compnay_id=implode(",",$mCompnay);
                $UsersAdditionalInfo->save();
           
                return response()->json(['errors'=>'','user_id'=>$request->client_id]);
                exit;
            }else{
                return response()->json(['errors'=>['Client name is already linked to this company']]);
                exit;
            }
           
        }
        
    }
    public function saveUnLinkContact(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'company_id' => 'required|numeric',
            'client_id' => 'required|numeric',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $isLinked= UsersAdditionalInfo::where('user_id',$request->client_id)->whereRaw("find_in_set($request->company_id,`multiple_compnay_id`)")->count();
            if($isLinked<=0){
                return response()->json(['errors'=>['Client name is already unlinked from this company']]);
                exit;
            }else{
                $UsersAdditionalInfo=UsersAdditionalInfo::select("*")->where("user_id",$request->client_id)->first();
                $mCompnay=explode(",",$UsersAdditionalInfo['multiple_compnay_id']);
                unset($mCompnay[array_search($request->company_id,$mCompnay)]);
                
                $UsersAdditionalInfo=UsersAdditionalInfo::find($UsersAdditionalInfo['id']);
                $UsersAdditionalInfo->multiple_compnay_id=implode(",",$mCompnay);
                $UsersAdditionalInfo->save();
                return response()->json(['errors'=>'','user_id'=>$request->client_id]);
                exit;
            }
           
        }
        
    }
    public function clientCaseList()
    {   
        $columns = array('id', 'case_title', 'case_desc', 'case_number', 'case_status','case_unique_number');
        $requestData= $_REQUEST;
        
        $CaseClientSelection = CaseClientSelection::select("case_id")->where("selected_user",$requestData['company_id'])->get()->pluck('case_id');
        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
        $case = $case->whereIn("case_master.id",$CaseClientSelection);
        $case = $case->where("case_master.is_entry_done","1"); 
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
    public function addExistingCase(Request $request)
    {
        $company_id=$request->company_id;
        $companyData=User::find($company_id);
        $clientList = UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
        ->select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'),"users.id","user_level")->where("users.user_level","2")->where("parent_user",Auth::user()->id)->whereRaw("find_in_set($company_id,`multiple_compnay_id`)")->get();
        return view('company_dashboard.addExistingCase',compact('company_id','companyData','clientList'));
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
            'company_id' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $isLinked= CaseClientSelection::where('case_id',$request->case_id)->where('selected_user',$request->company_id)->count();
            if($isLinked<=0){
                $CaseClientSelection=new CaseClientSelection;
                $CaseClientSelection->case_id=$request->case_id;
                $CaseClientSelection->selected_user=$request->company_id;
                $CaseClientSelection->save();
                
                if(!empty($request->client_links)){
                    foreach($request->client_links as $k=>$v){
                        $linkedContact= CaseClientSelection::where('case_id',$request->case_id)->where('selected_user',$v)->count();
                        if($linkedContact<=0){
                            $CaseClientSelection=new CaseClientSelection;
                            $CaseClientSelection->case_id=$request->case_id;
                            $CaseClientSelection->selected_user=$v;
                            $CaseClientSelection->save();
                        }
                    }
                }

                return response()->json(['errors'=>'','user_id'=>$request->company_id]);
                exit;
            }else{
                return response()->json(['errors'=>['Client name is already linked to this case']]);
                exit;
            }
            
            // foreach($request->client_links as $k=>$v){
            //     $isLinked= CaseClientSelection::where('case_id',$request->case_id)->where('selected_user',$v)->count();
            //     if($isLinked<=0){
            //         $CaseClientSelection=new CaseClientSelection;
            //         $CaseClientSelection->case_id=$request->case_id;
            //         $CaseClientSelection->selected_user=$v;
            //         $CaseClientSelection->save();
            //     }else{
            //         return response()->json(['errors'=>['Client name is already linked to this case']]);
            //         exit;
            //     }
            // }
            // return response()->json(['errors'=>'','user_id'=>$request->client_links]);
            // exit;
        }
        
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
            $company_id=$request->user_delete_contact_id;
            $case_id=$request->id;
            CaseClientSelection::where('case_id',$case_id)->where('selected_user',$company_id)->delete();
            if(isset($request->user_delete_company_contact) && $request->user_delete_company_contact=="on"){
                $clientList = UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
                ->select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'),"users.id","user_level")->where("users.user_level","2")->where("parent_user",Auth::user()->id)->whereRaw("find_in_set($company_id,`multiple_compnay_id`)")->get();
                foreach($clientList as $k=>$v){
                   CaseClientSelection::where('case_id',$case_id)->where('selected_user',$v->id)->delete();
                }
            }
            return response()->json(['errors'=>'','user_id'=>$request->user_delete_contact_id]);
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
    public function ClientNotes()
    {   
        $requestData= $_REQUEST;
        $allLeads = ClientNotes::leftJoin('users','client_notes.client_id','=','users.id');
        $allLeads = $allLeads->leftJoin('users as u1','client_notes.created_by','=','u1.id');
        $allLeads = $allLeads->leftJoin('users as u2','client_notes.updated_by','=','u2.id');
        $allLeads = $allLeads->select("users.user_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as client_name'),DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as note_created_by'),"u1.user_title as created_by_user_title",DB::raw('CONCAT_WS(" ",u2.first_name,u2.middle_name,u2.last_name) as note_updated_by'),"u2.user_title as updated_by_user_title","client_notes.*","client_notes.created_at as note_created_at");        
        $allLeads = $allLeads->where("client_notes.client_id",$requestData['user_id']);   
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
        $client_id=$request->user_id;
        $userData=User::find($client_id);
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
        return view('company_dashboard.addNote',compact('userData','client_id','note_id'));
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

            
            if($request->current_submit=="savenote"){
                $LeadNotes->is_publish="yes";
                $LeadNotes->is_draft="no";
                $LeadNotes->save();
                $LeadNotes->original_content=json_encode($LeadNotes);
                $LeadNotes->save();
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
        $client_id=$request->user_id;
        $userData=User::find($client_id);
        return view('company_dashboard.editNote',compact('userData','client_id','ClientNotes','note_id'));
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
                $data['case_id']=NULL;
                if($LeadNotes['client_id']!=NULL){
                    $data['notes_for_client']=$LeadNotes['client_id'];
                }else{
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
            $data['case_id']=NULL;
            if($dataNotes['client_id']!=NULL){
                $data['notes_for_client']=$dataNotes['client_id'];
            }else{
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
        $defaultRate='';
        
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
        
        return view('company_dashboard.loadTimeEntryPopup',compact('CaseMasterData','loadFirmStaff','TaskActivity','defaultRate'));     
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
            $TaskTimeEntry->firm_id =auth()->user()->firm_name;
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

    
    public function loadTrustHistory()
    {   
        $requestData= $_REQUEST;
        $allLeads = TrustHistory::leftJoin('users','trust_history.client_id','=','users.id');
        $allLeads = $allLeads->leftJoin('users as u1','trust_history.client_id','=','u1.id');
        $allLeads = $allLeads->leftJoin('users_additional_info as u2','trust_history.client_id','=','u2.id');
        $allLeads = $allLeads->select("users.user_title",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as client_name'),DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as note_created_by'),"u1.user_title as created_by_user_title","trust_history.*","trust_history.client_id as client_id","u2.minimum_trust_balance","u2.trust_account_balance");        
        $allLeads = $allLeads->where("trust_history.client_id",$requestData['user_id']);   
        $allLeads = $allLeads->with('invoice', 'fundRequest', 'allocateToCase');  
        $totalData=$allLeads->count();
        $totalFiltered = $totalData; 
     
        $allLeads = $allLeads->offset($requestData['start'])->limit($requestData['length']);
        $allLeads = $allLeads->orderBy('trust_history.payment_date','ASC');
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
        return view('company_dashboard.billing.trustHistoryPdf',compact('userData','country','firmData','firmAddress','UsersAdditionalInfo','allHistory'));
    }
    public function downloadTrustActivity(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'from_date'    => 'nullable|date',
            'to_date'      => 'nullable|date|after_or_equal:from_date',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                
            $id=$request->user_id;
            $userData=User::find($id);
            $firmData=Firm::find(Auth::User()->firm_name);
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
            // return view('company_dashboard.billing.trustHistoryPdf',compact('userData','country','firmData','firmAddress','UsersAdditionalInfo','allHistory'));

            $filename='trust_export_'.time().'.pdf';
            $startDate = $request->from_date; $endDate = $request->to_date;
            $PDFData=view('client_dashboard.billing.trustHistoryPdf',compact('userData','country','firmData','firmAddress','UsersAdditionalInfo','allHistory', 'startDate', 'endDate'));
            // $PDFData=view('company_dashboard.billing.trustHistoryPdf',compact('userData','country','firmData','firmAddress','UsersAdditionalInfo','allHistory'));
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

    public function addNewMessagePopup(Request $request)
    {

        //For company List
        $CaseMasterCompanyList = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        foreach($CaseMasterCompanyList as $k=>$v){
            $compnayIdWithEnablePortal = DB::table("users_additional_info")
            ->select("id")
            ->whereRaw("find_in_set($v->id,`multiple_compnay_id`)")
            ->where("client_portal_enable",1)
            ->get()
            ->pluck("id");
        }
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level")
        ->whereIn("id",$CaseMasterCompanyList)
        ->get();


        //Get client list with client enable portal is active
        $clientLists = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')
        ->select("first_name","last_name","users.id","user_level")
        ->where("users.user_level",2)
        ->where("parent_user",Auth::user()->id)
        ->where("lead_additional_info.client_portal_enable",1)
        ->get();

        //Get firm user list 
        $loadFirmUser = User::select("first_name","last_name","id")
        ->where("firm_name",Auth::user()->firm_name)
        ->whereIn("user_level",['1','3'])
        ->doesntHave("deactivateUserDetail")
        ->orderBy("id","DESC")->get();
        
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
        return view('company_dashboard.sendMEssage',compact('CaseMasterData','CaseMasterCompany','loadFirmUser','clientLists'));     
        exit;
    }
  
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

        return view('company_dashboard.searchVal',compact('CaseMasterData','CaseMasterCompany','loadFirmUser'));    
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
                // "from" => FROM_EMAIL,
                // "from_title" => FROM_EMAIL_TITLE,
                "from" => FROM_EMAIL,
                // "from_title" => FROM_EMAIL_TITLE,
                "from_title" => $firmData->firm_name,
                "replyto"=>DO_NOT_REPLAY_FROM_EMAIL,
                "replyto_title"=>DO_NOT_REPLAY_FROM_EMAIL_TITLE,
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
    public function deleteCompanyPopup(Request $request)
    {
        $company_id=$request->company_id;
        $getCompanyWiseClientList=$this->getCompanyWiseCaseList($company_id);
        $CaseClientSelection = CaseClientSelection::select("case_id")->whereIn("selected_user",$getCompanyWiseClientList)->get()->pluck('case_id');
        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid") ->whereIn("case_master.id",$CaseClientSelection)->where("case_master.is_entry_done","1")->where("case_close_date",NULL)->count(); 
            
        return view('company_dashboard.deleteCompany',compact('company_id','CaseClientSelection','case'));     
        exit;    
    } 
    public function deleteCompany(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'company_id'=>'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user=User::where("id",$request->company_id)->delete();
            $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$request->company_id)->delete();
            $CaseClientSelection=CaseClientSelection::where("selected_user",$request->company_id)->delete();
            session(['popup_success' => 'Company was deleted.']);
            $data=[];
            $data['user_id']=$request->company_id;
            $data['client_id']=$request->company_id;
            $data['activity']='deleted company';
            $data['type']='contact';
            $data['action']='delete';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
            return response()->json(['errors'=>'','company_id'=>$request->company_id]);
          exit;
        }
    }
    public function archiveCompanyPopup(Request $request)
    {
        $company_id=$request->company_id;
        $getCompanyWiseClientList=$this->getCompanyWiseCaseList($company_id);
        $CaseClientSelection = CaseClientSelection::select("case_id")->whereIn("selected_user",$getCompanyWiseClientList)->get()->pluck('case_id');
        $caseCllientSelection =UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
            ->select("first_name","middle_name","last_name","users.id","user_level","email",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as name'))->where("users.user_level","2")->where("parent_user",Auth::user()->id)->whereRaw("find_in_set($company_id,`multiple_compnay_id`)")->get();

        return view('company_dashboard.archiveCompany',compact('company_id','CaseClientSelection','caseCllientSelection'));     
        exit;    
    } 
    public function archiveCompany(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'client_links' => 'nullable|array',
            'company_id'=>'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user=User::find($request->company_id);
            $user->user_status=4;  /* 4=Archive */
            $user->save();
            if(!empty($request->client_links) ){
                foreach($request->client_links as $k=>$v){
                    if(isset($request->disable_login) && $request->disable_login=="on"){
                        $user=User::find($v);
                        $user->user_status=4;  /* 4=Archive */
                        $user->save();
                        UsersAdditionalInfo::where('user_id',$v)->update(['client_portal_enable'=>0]);
                    }else{
                        $user=User::find($v);
                        $user->user_status=4;  /* 4=Archive */
                        $user->save();
                    }
                }
                session(['popup_success' => 'Company and '.count($request->client_links).' contacts have been archived.']);
            }
            $data=[];
            $data['user_id']=$request->company_id;
            $data['client_id']=$request->company_id;
            $data['activity']='archived company';
            $data['type']='contact';
            $data['action']='archive';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
            return response()->json(['errors'=>'','company_id'=>$request->company_id]);
          exit;
        }
    }

    public function unarchiveCompanyPopup(Request $request)
    {
        $company_id=$request->company_id;
        $getCompanyWiseClientList=$this->getCompanyWiseCaseList($company_id);
        $CaseClientSelection = CaseClientSelection::select("case_id")->whereIn("selected_user",$getCompanyWiseClientList)->get()->pluck('case_id');
        $caseCllientSelection =UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
            ->select("first_name","middle_name","last_name","users.id","user_level","email",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as name'))->where("users.user_level","2")->where("users.user_status",4)->where("parent_user",Auth::user()->id)->whereRaw("find_in_set($company_id,`multiple_compnay_id`)")->get();

        return view('company_dashboard.unarchiveCompany',compact('company_id','CaseClientSelection','caseCllientSelection'));     
        exit;    
    } 
    public function unarchiveCompany(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'client_links' => 'nullable|array',
            'company_id'=>'required|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $user=User::find($request->company_id);
            $user->user_status=1;  /* 4=Archive */
            $user->save();
            if(!empty($request->client_links) ){
                foreach($request->client_links as $k=>$v){
                    if(isset($request->disable_login) && $request->disable_login=="on"){
                        $user=User::find($v);
                        $user->user_status=1;  /* 4=Archive */
                        $user->save();
                        UsersAdditionalInfo::where('user_id',$v)->update(['client_portal_enable'=>1]);
                    }else{
                        $user=User::find($v);
                        $user->user_status=1;  /* 4=Archive */
                        $user->save();
                    }
                }
                session(['popup_success' => 'Company and '.count($request->client_links).' contacts have been unarchived.']);
            }
            $data=[];
            $data['user_id']=$request->company_id;
            $data['client_id']=$request->company_id;
            $data['activity']='unarchived company';
            $data['type']='contact';
            $data['action']='unarchive';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
            return response()->json(['errors'=>'','company_id'=>$request->company_id]);
          exit;
        }
    }

}
  
