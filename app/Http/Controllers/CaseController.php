<?php

namespace App\Http\Controllers;
use App\User,App\EmailTemplate,App\Countries;
use Illuminate\Http\Request,DateTime;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\ContractUserCase,App\CaseMaster,App\ContractUserPermission,App\ContractAccessPermission;
use App\DeactivatedUser,App\TempUserSelection,App\CasePracticeArea,App\CaseStage,App\CaseClientSelection;
use App\CaseStaff,App\CaseUpdate,App\CaseStageUpdate,App\CaseActivity;
use App\CaseEvent,App\CaseEventLocation,App\EventType;
use Carbon\Carbon,App\CaseEventReminder,App\CaseEventLinkedStaff;
use App\Http\Controllers\CommonController,App\CaseSolReminder;
use DateInterval,DatePeriod,App\CaseEventComment;
use App\Task,App\LeadAdditionalInfo,App\UsersAdditionalInfo;
use App\Invoices,App\TaskTimeEntry,App\CaseEventLinkedContactLead;
use App\Calls,App\FirmAddress,App\PotentialCaseInvoicePayment;
use App\Event;
use App\EventRecurring;
use App\EventUserReminder;
use App\ViewCaseState,App\ClientNotes,App\CaseTaskLinkedStaff;
use App\ExpenseEntry,App\CaseNotes,App\Firm,App\IntakeForm,App\CaseIntakeForm;
use App\FirmEventReminder;
use App\FlatFeeEntry,App\Messages,App\UserPreferanceReminder;
use App\Jobs\CaseAddEventJob;
use App\Jobs\CaseAllEventJob;
use App\Jobs\CaseFollowingEventJob;
use App\Jobs\CaseSingleEventJob;
use Illuminate\Support\Str;
use App\Jobs\EventCommentEmailJob;
use App\Jobs\EventReminderEmailJob;
use App\Traits\CaseEventTrait;
use App\Traits\EventTrait;
use App\Traits\UserCaseSharingTrait;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CaseController extends BaseController
{
    use /* CaseEventTrait, */ EventTrait, UserCaseSharingTrait;
    public function __construct()
    {
       
    }
    public function index()
    {
        // TempUserSelection::where("user_id",Auth::user()->id)->delete();
        DB::table('temp_user_selection')->where("user_id",Auth::user()->id)->delete();

        $CaseMaster = CaseMaster::latest()->get();
        $country = Countries::get();
        $getChildUsers=$this->getParentAndChildUserIds();
        $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
      
        $getChildUsers=$this->getParentAndChildUserIds();
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();          

        
        $CaseLeadAttorney = CaseStaff::join('users','users.id','=','case_staff.lead_attorney')->select("users.id","users.first_name","users.last_name",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'))->where("users.firm_name",Auth::user()->firm_name)->groupBy('case_staff.lead_attorney')->get();

        $user_id='';
        if(isset($request->user_id)){
            $user_id=$request->user_id;
        }
        if(isset($request->link) && $request->link=="yes"){
            session(['caseLinkToClient' => "yes"]);
            session(['clientId' => $request->user_id]);
        }
        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();

        $getChildUsers=$this->getParentAndChildUserIds();
        $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
      
        // $caseStageList = CaseStage::where("status","1")->get();
        $getChildUsers=$this->getParentAndChildUserIds();
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();  


        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();

        // $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
        // $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        // $getChildUsers[]=Auth::user()->id;
        // $getChildUsers[]="0"; //This 0 mean default category need to load in each user
        // $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_status","1")->where("user_level","3")->get();
        $loadFirmUser = firmUserList();
        // return view('case.loadStep1',compact('CaseMasterClient','CaseMasterCompany','user_id','practiceAreaList','caseStageList','selectdUSerList','loadFirmUser'));
        $firmAddress = FirmAddress::select("firm_address.*","countries.name as countryname")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->orderBy('firm_address.is_primary','ASC')->get();
        return view('case.index',compact('CaseMaster','country','practiceAreaList','caseStageList','CaseLeadAttorney','CaseMasterClient','CaseMasterCompany','user_id','practiceAreaList','caseStageList','selectdUSerList','loadFirmUser','firmAddress'));
    }

    public function loadCase()
    {   

        // TempUserSelection::where("user_id",Auth::user()->id)->delete();
        DB::table('temp_user_selection')->where("user_id",Auth::user()->id)->delete();
        $columns = array('id', 'case_title', 'case_desc', 'case_number', 'case_status','case_unique_number');
        $requestData= $_REQUEST;
        
        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
        
        //Filter applied for practice area
        if(isset($requestData['pa']) && $requestData['pa']!=''){
            $case = $case->where("practice_area",$requestData['pa']);
        }
        
        //Filter applied for case stage
        if(isset($requestData['cs']) && $requestData['cs']!=''){
            $case = $case->where("case_status",$requestData['cs']);
        }

        //Filter applied for lead attorney
        if(isset($requestData['la']) && $requestData['la']!=''){
            $CaseLeadAttorneySearch = CaseStaff::select("case_id")->where('lead_attorney',$requestData['la'])->get()->pluck('case_id');
            $case = $case->whereIn("case_master.id",$CaseLeadAttorneySearch);
        }

         //Load only closed case
         if(isset($requestData['i']) && $requestData['i']!=''){
            $case = $case->where("case_close_date","!=", NULL);
        }else{
            $case = $case->where("case_close_date", NULL);
        }
        if(isset($requestData['mc']) && $requestData['mc']!=''){
            $ownAssignedCase = CaseStaff::select("case_id","user_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $case = $case->whereIn("case_master.id",$ownAssignedCase);
        }
        
        if(auth()->user()->hasPermissionTo('access_all_cases')) { // Show cases as per user permission
            $case = $case->where('firm_id', auth()->user()->firm_name);
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $case = $case->whereIn("case_master.id",$childUSersCase);
        }
     
        $case = $case->where("case_master.is_entry_done","1"); 
        $totalData=$case->count();
        $totalFiltered = $totalData; 
        if( !empty($requestData['search']['value']) ) {   
            $case = $case->where( function($q) use ($requestData){
                $q->where( function($select) use ($requestData){
                    $select->orWhere( DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%".$requestData['search']['value']."%");
                    $select->orWhere('email', 'like', "%".$requestData['search']['value']."%" );
                });
            });
        }
        if( !empty($requestData['search']['value']) ) { 
            $totalFiltered = $case->count(); 
        }
        $case = $case->offset($requestData['start']??0)->limit($requestData['length']);
        $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $case = $case->with([/* "upcomingEvent", */ "upcomingTask", "caseStaffDetails" => function($query) {
                    $query->select("users.id", "users.first_name","users.last_name","case_staff.lead_attorney");
                }, "caseUpdate" => function($query) {
                    $query->with("createdByUser");
                    // $query->leftjoin('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","case_update.update_status","case_update.created_at");
                }])->withCount("overdueTasks")->get()
                ->each->setAppends(["created_new_date", "case_stage_text", "practice_area_text", "createdby", "upcoming_event"]);
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $case 
        );
        echo json_encode($json_data);  
    }
    public function loadAllStep(Request $request)
    {
        $user_id='';
        if(isset($request->user_id)){
            $user_id=$request->user_id;
        }
        if(isset($request->link) && $request->link=="yes"){
            session(['caseLinkToClient' => "yes"]);
            session(['clientId' => $request->user_id]);
        }
        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();

        $getChildUsers=$this->getParentAndChildUserIds();
        $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
      
        // $caseStageList = CaseStage::where("status","1")->get();
        $getChildUsers=$this->getParentAndChildUserIds();
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();  


        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();

        // $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
        // $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        // $getChildUsers[]=Auth::user()->id;
        // $getChildUsers[]="0"; //This 0 mean default category need to load in each user
        // $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_status","1")->where("user_level","3")->get();
        $loadFirmUser = firmUserList();
        return view('case.loadStep1',compact('CaseMasterClient','CaseMasterCompany','user_id','practiceAreaList','caseStageList','selectdUSerList','loadFirmUser'));
    }  

    public function saveAllStep(Request $request)
    {
        // return response()->json(['errors'=>'','user_id'=>'',case_unique_number'=>'6045FB829C823']);
        
        if(isset($request->default_rate)) {$request['default_rate']=str_replace(",","",$request->default_rate); }
      
        $validator = \Validator::make($request->all(), [
            'case_name' => 'required|unique:case_master,case_title,NULL,id,firm_id,'.Auth::User()->firm_name,
            'default_rate' => 'nullable|numeric',
            'selectedUSer'=>'required|array'
        ],['selectedUSer.required'=>'Please select at least one staff member.']);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $CaseMaster = new CaseMaster;
            if(isset($request->case_name)) { $CaseMaster->case_title=$request->case_name; }
            if(isset($request->case_number)) { $CaseMaster->case_number =$request->case_number; }
            if(isset($request->case_status)) { $CaseMaster->case_status=$request->case_status; }
            if(isset($request->case_description)) { $CaseMaster->case_description=$request->case_description; }
            if(isset($request->case_open_date)) {
                $var =convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->case_open_date)))), auth()->user()->user_timezone ?? 'UTC');
                $CaseMaster->case_open_date=$var;
            }

            if(isset($request->case_office)) { $CaseMaster->case_office=$request->case_office; }
            if(isset($request->case_statute)) {
                $var =convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->case_statute)))), auth()->user()->user_timezone ?? 'UTC');
                $CaseMaster->case_statute_date= $var;
                $CaseMaster->sol_satisfied="yes";
            }
            if(isset($request->conflict_check)) { 
                $CaseMaster->conflict_check="1"; 
                $CaseMaster->conflict_check_at=date('Y-m-d h:i:s');                
            }
            if(isset($request->conflict_check_description)) { 
                $CaseMaster->conflict_check_description=$request->conflict_check_description; 
            }
            $CaseMaster->case_unique_number=strtoupper(uniqid()); 
            if(isset($request->practice_area_text)) { 
                $CasePracticeArea = new CasePracticeArea;
                $CasePracticeArea->title=$request->practice_area_text; 
                $CasePracticeArea->firm_id =Auth::User()->firm_name;
                $CasePracticeArea->created_by=Auth::User()->id; 
                $CasePracticeArea->save();
                
                $CaseMaster->practice_area=$CasePracticeArea->id;
            }else{
                if(isset($request->practice_area)) { $CaseMaster->practice_area=$request->practice_area; }
            }
           
            $CaseMaster->billing_method=$request->billingMethod; 
            $CaseMaster->billing_amount=$request->default_rate; 

            $CaseMaster->created_by=Auth::User()->id; 
            $CaseMaster->is_entry_done="0"; 
            $CaseMaster->firm_id = auth()->user()->firm_name; 
            $CaseMaster->save();

            if(isset($request->case_statute)){
                for($i=0;$i<count($request->reminder_type)-1;$i++){
                    $CaseSolReminder = new CaseSolReminder;
                    $CaseSolReminder->case_id=$CaseMaster->id; 
                    $CaseSolReminder->reminder_type=$request['reminder_type'][$i]; 
                    $CaseSolReminder->reminer_number=$request['reminder_days'][$i];
                    $CaseSolReminder->created_by=Auth::User()->id; 
                    $reminderDate = \Carbon\Carbon::createFromFormat('Y-m-d', $CaseMaster->case_statute_date)->subDay($request['reminder_days'][$i])->format('Y-m-d'); // Subtracts reminder date day for case_statute_date 
                    $CaseSolReminder->remind_at=$reminderDate;  
                    $CaseSolReminder->save();
                }
            }

            //Activity tab
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
            
            $selectdUSerList = TempUserSelection::where("temp_user_selection.user_id",Auth::user()->id)->get();
            if(!$selectdUSerList->isEmpty()){
                foreach($selectdUSerList as $key=>$val){
                    $CaseClientSelection = new CaseClientSelection;
                    $CaseClientSelection->case_id=$CaseMaster->id; 
                    $CaseClientSelection->selected_user=$val->selected_user; 
                    $CaseClientSelection->created_by=Auth::user()->id; 
                    if($val->selected_user == $request->billing_contact){
                        $CaseClientSelection->is_billing_contact='yes';
                        if(isset($request->billingMethod)) { $CaseClientSelection->billing_method=$request->billingMethod; }
                        if(isset($request->default_rate)) { $CaseClientSelection->billing_amount=$request->default_rate; }
                    }   
                    $CaseClientSelection->save();
                    
                    // Flat fees entry
                    if(isset($request->billingMethod)) {
                        if($request->billingMethod == "flat" || $request->billingMethod == "mixed") {
                            FlatFeeEntry::create([
                                'case_id' => $CaseMaster->id,
                                'user_id' => Auth::user()->id,
                                'firm_id' => Auth::user()->firm_name,
                                'entry_date' => Carbon::now(),
                                'cost' =>  $request->default_rate ?? 0,
                                'time_entry_billable' => 'yes',
                                'created_by' => Auth::user()->id, 
                            ]);
                        }
                    }

                    //Activity tab
                    $datauser=[];
                    $datauser['activity_title']='linked client';
                    $datauser['case_id']=$CaseMaster->id;
                    $datauser['staff_id']=$val->selected_user;
                    $this->caseActivity($datauser);

                    $data=[];
                    $data['user_id']=$val->selected_user;
                    $data['client_id']=$val->selected_user;
                    $data['case_id']=$CaseMaster->id;
                    $data['activity']='linked Contact';
                    $data['type']='contact';
                    $data['action']='link';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);
                }
            }


            if(isset($request['selectedUSer'])){
          
                foreach($request['selectedUSer'] as $key=>$val){
                    $CaseStaff = new CaseStaff;
                    $CaseStaff->case_id=$CaseMaster->id; 
                    $CaseStaff->user_id=$key; 
                    $CaseStaff->created_by=Auth::user()->id; 
                    $CaseStaff->lead_attorney=$request['lead_attorney'];
                    $CaseStaff->originating_attorney=$request['originating_attorney'];
    
                    $CaseStaff->rate_type=($request['rate_type'][$key]=='Case_Rate')? "1" : "0";
                    // if( $CaseStaff->rate_type == "1"){ 
                    if( $request['rate_type'][$key]=='Case_Rate' ){ 
                        $CaseStaff->rate_amount=str_replace(",","",$request['new_rate'][$key]);
                    } else {
                        $CaseStaff->rate_amount=str_replace(",","",$request['new_rate'][$key]);
                    }
                    $CaseStaff->save();
    
                    //Activity tab
                    $datauser=[];
                    $datauser['activity_title']='linked staff';
                    $datauser['case_id']=$CaseMaster->id;
                    $datauser['staff_id']=$key;
                    $this->caseActivity($datauser);

                    $data=[];
                    $data['user_id']=$key;
                    $data['client_id']=$key;
                    $data['case_id']=$CaseMaster->id;
                    $data['activity']='linked attorney';
                    $data['type']='contact';
                    $data['action']='link';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);    
                }
    
                $caseStatusChange=CaseMaster::find($CaseMaster->id);
                $caseStatusChange->is_entry_done="1";
                $caseStatusChange->save();
    
                $caseStageHistory = new CaseStageUpdate;
                $caseStageHistory->stage_id=($caseStatusChange->case_status)??NULL;
                $caseStageHistory->case_id=$caseStatusChange->id;
                $caseStageHistory->start_date = convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($caseStatusChange->case_open_date)))), auth()->user()->user_timezone ?? 'UTC');
                $caseStageHistory->end_date = convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($caseStatusChange->case_open_date)))), auth()->user()->user_timezone ?? 'UTC');
                $caseStageHistory->created_by=Auth::user()->id; 
                $caseStageHistory->created_at=$caseStatusChange->case_open_date; 
                $caseStageHistory->save();
    
                if($CaseMaster->case_statute_date !=NULL) {
                    $this->saveSOLEventIntoCalender($CaseMaster->id);
                }                
                
                DB::table('temp_user_selection')->where("user_id",Auth::user()->id)->delete();
                session(['popup_success' => 'Case has been created.']);
            }
            return response()->json(['errors'=>'','user_id'=>'','case_unique_number'=>$CaseMaster->case_unique_number,'case_id'=>$CaseMaster->id]);

        }
    }
    public function loadBillingContact(Request $request)
    {
       

        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();

       
        return view('case.loadBillingContact',compact('selectdUSerList'));
    }  
    public function loadStep1(Request $request)
    {
        $user_id='';
        if(isset($request->user_id)){
            $user_id=$request->user_id;
        }
        if(isset($request->link) && $request->link=="yes"){
            session(['caseLinkToClient' => "yes"]);
            session(['clientId' => $request->user_id]);
        }
        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        return view('case.loadStep1',compact('CaseMasterClient','CaseMasterCompany','user_id'));
    }  
    public function saveSelectdUser(Request $request)
    {
        $firstCheck=TempUserSelection::where("selected_user",$request->selectdValue)->where("user_id",Auth::user()->id)->get();
        
        if($firstCheck->isEmpty()){
            $TempUserSelection = new TempUserSelection;
            $TempUserSelection->selected_user=$request->selectdValue;
            $TempUserSelection->user_id=Auth::user()->id;
            $TempUserSelection->save();
        }
        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
        // echo "<pre>";
        // print_r($selectdUSerList);

        return view('case.showSelectdUser',compact('selectdUSerList'));
    }
    public function loadStep1FromCompany(Request $request)
    {
        $user_id='';
        if(isset($request->company_id)){
            $user_id=$request->company_id;
        }
        if(isset($request->companylink) && $request->companylink=="yes"){
            session(['caseLinkToCompany' => "yes"]);
            session(['companyId' => $request->company_id]);
        }
        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        return view('case.loadStep1FromCompany',compact('CaseMasterClient','CaseMasterCompany','user_id'));
    }
    
    public function saveSelectdUserFromCompany(Request $request)
    {
        DB::table('temp_user_selection')->where("user_id",Auth::user()->id)->delete();

        $TempUserSelection = new TempUserSelection;
        $TempUserSelection->selected_user=$request->selectdValue;
        $TempUserSelection->user_id=Auth::user()->id;
        $TempUserSelection->save();

        $clientList = UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
        ->select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'),"users.id","user_level")->where("users.user_level","2");
        $clientList = $clientList->where("parent_user",Auth::user()->id);
        $clientList = $clientList->whereRaw("find_in_set($request->selectdValue,`multiple_compnay_id`)");
        $clientList = $clientList->get();
        foreach($clientList as $k=>$v){
            $TempUserSelection = new TempUserSelection;
            $TempUserSelection->selected_user=$v['id'];
            $TempUserSelection->user_id=Auth::user()->id;
            $TempUserSelection->save();
        }

        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
        // echo "<pre>";
        // print_r($selectdUSerList);

        return view('case.showSelectdUser',compact('selectdUSerList'));
    }

    public function removeTempSelectedUser(Request $request){
        DB::table('temp_user_selection')->where("user_id",Auth::user()->id)->delete();
    }

    public function remomeSelectedUser(Request $request)
    {
        $firstCheck=TempUserSelection::where("selected_user",$request->selectdValue)->where("user_id",Auth::user()->id)->delete();
        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
        return view('case.showSelectdUser',compact('selectdUSerList'));
    }
    public function saveStep1(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            // 'user_type' => 'required',
        ]);
        if ($validator->fails())
        {
           
            
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            return response()->json(['errors'=>'','user_id'=>$request->user_id]);
          exit;
        }
    }
    // Load step 2 when click from step 1
    public function loadStep2(Request $request)
    {
        $getChildUsers=$this->getParentAndChildUserIds();
        $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
      
        // $caseStageList = CaseStage::where("status","1")->get();
        $getChildUsers=$this->getParentAndChildUserIds();
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();          

        return view('case.loadStep2',compact('practiceAreaList','caseStageList'));
    }
    // Save step 2 data to database.
    public function saveStep2(Request $request)
    {
        // DB::table('case_master')->where("created_by",Auth::user()->id)->where("is_entry_done","0")->delete();

        $validator = \Validator::make($request->all(), [
            'case_name' => 'required|unique:case_master,case_title'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $CaseMaster = new CaseMaster;
            if(isset($request->case_name)) { $CaseMaster->case_title=$request->case_name; }
            if(isset($request->case_number)) { $CaseMaster->case_number =$request->case_number; }
            if(isset($request->case_status)) { $CaseMaster->case_status=$request->case_status; }
            if(isset($request->case_description)) { $CaseMaster->case_description=$request->case_description; }
            if(isset($request->case_open_date)) {
                $var =convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->case_open_date)))), auth()->user()->user_timezone ?? 'UTC');
                // $date = str_replace('/', '-', $var);
                $CaseMaster->case_open_date= date('Y-m-d', strtotime($var));
            }

            if(isset($request->case_office)) { $CaseMaster->case_office=$request->case_office; }
            if(isset($request->case_statute)) {
                $var =convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->case_statute)))), auth()->user()->user_timezone ?? 'UTC');
                // $date = str_replace('/', '-', $var);
                $CaseMaster->case_statute_date= date('Y-m-d', strtotime($var));
            }
             if(isset($request->conflict_check)) { 
                $CaseMaster->conflict_check="1";                
                $CaseMaster->conflict_check_at=date('Y-m-d h:i:s');                 
            }
            if(isset($request->conflict_check_description)) { 
                $CaseMaster->conflict_check_description=$request->conflict_check_description; 
            }
            $CaseMaster->case_unique_number=strtoupper(uniqid()); 
            if(isset($request->practice_area_text)) { 
                $CasePracticeArea = new CasePracticeArea;
                $CasePracticeArea->title=$request->practice_area_text; 
                $CasePracticeArea->firm_id=Auth::User()->firm_name; 
                $CasePracticeArea->created_by=Auth::User()->id; 
                $CasePracticeArea->save();
                
                $CaseMaster->practice_area=$CasePracticeArea->id;
            }else{
                if(isset($request->practice_area)) { $CaseMaster->practice_area=$request->practice_area; }
            }
           
            $CaseMaster->created_by=Auth::User()->id; 
            $CaseMaster->is_entry_done="0"; 
            $CaseMaster->firm_id = auth()->user()->firm_name; 
            $CaseMaster->save();
            
            session(['case_no' => $CaseMaster->id]);

            if(isset($request->case_statute)){
                for($i=0;$i<count($request->reminder_type)-1;$i++){
                    $CaseSolReminder = new CaseSolReminder;
                    $CaseSolReminder->case_id=$CaseMaster->id; 
                    $CaseSolReminder->reminder_type=$request['reminder_type'][$i]; 
                    $CaseSolReminder->reminer_number=$request['reminder_days'][$i];
                    $CaseSolReminder->created_by=Auth::User()->id;                    
                    $reminderDate = \Carbon\Carbon::createFromFormat('Y-m-d', $CaseMaster->case_statute_date)->subDay($request['reminder_days'][$i])->format('Y-m-d'); // Subtracts reminder date day for case_statute_date 
                    $CaseSolReminder->remind_at=$reminderDate; 
                    $CaseSolReminder->save();
                }
            }
            
            // add sol in event calender
            if($CaseMaster->case_statute_date !=NULL) {
                $this->saveSOLEventIntoCalender($CaseMaster->id);
            } 

            //Activity tab
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

        }
        return response()->json(['errors'=>'','case_id'=>$CaseMaster->id]);
        exit;
    }
    //Load step 3 when click next button in step 2
    public function loadStep3(Request $request)
    {
        $case_id=$request->case_id;
        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
        return view('case.loadStep3',compact('selectdUSerList','case_id'));
    }
    //Save step 3 data to database.
    public function saveStep3(Request $request)
    {
        // dd($request->all());
        if(isset($request->default_rate)) {$request->default_rate=str_replace(",","",$request->default_rate); }
   
        $validator = \Validator::make([$request->default_rate], [
            'default_rate' => 'nullable|numeric'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $selectdUSerList = TempUserSelection::where("temp_user_selection.user_id",Auth::user()->id)->get();
            if(!$selectdUSerList->isEmpty()){
                $CaseMaster = CaseMaster::find($request->case_id);
                    
                foreach($selectdUSerList as $key=>$val){
                    $CaseClientSelection = new CaseClientSelection;
                    $CaseClientSelection->case_id=$request->case_id; 
                    $CaseClientSelection->selected_user=$val->selected_user; 
                    $CaseClientSelection->created_by=Auth::user()->id; 
                    if($val->selected_user == $request->billing_contact){
                        $CaseClientSelection->is_billing_contact='yes';
                        if(isset($request->billingMethod)) { 
                            $CaseClientSelection->billing_method=$request->billingMethod; 
                            $CaseMaster->billing_method=$request->billingMethod; 
                            $CaseMaster->save();
                        }
                        if(isset($request->default_rate)) { 
                            $CaseClientSelection->billing_amount=$request->default_rate; 
                            $CaseMaster->billing_amount=$request->default_rate; 
                            $CaseMaster->save();
                        }                        
                    }   
                    $CaseClientSelection->save();
                    
                    //Activity tab
                    $datauser=[];
                    $datauser['activity_title']='linked client';
                    $datauser['case_id']=$request->case_id;
                    $datauser['staff_id']=$val->selected_user;
                    $this->caseActivity($datauser);

                    $data=[];
                    $data['user_id']=$val->selected_user;
                    $data['client_id']=$val->selected_user;
                    $data['case_id']=$CaseMaster->id;
                    $data['activity']='linked Contact';
                    $data['type']='contact';
                    $data['action']='link';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);
                }
            }
            return response()->json(['errors'=>'','case_id'=>$request->case_id]);
            exit;
        }
    }
    
    public function loadStep4(Request $request)
    {
        $case_id=$request->case_id;

        // $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
        // $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        // $getChildUsers[]=Auth::user()->id;
        // $getChildUsers[]="0"; //This 0 mean default category need to load in each user
        // $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_status","1")->where("user_level","3")->get();
        $loadFirmUser = firmUserList();
        return view('case.loadStep4',compact('loadFirmUser','case_id'));
    }

    public function saveStep4(Request $request)
    {
        
        if(isset($request['selectedUSer'])){
          
            foreach($request['selectedUSer'] as $key=>$val){
                $CaseStaff = new CaseStaff;
                $CaseStaff->case_id=$request->case_id; 
                $CaseStaff->user_id=$key; 
                $CaseStaff->created_by=Auth::user()->id; 
                $CaseStaff->lead_attorney=$request['lead_attorney'];
                $CaseStaff->originating_attorney=$request['originating_attorney'];

                $CaseStaff->rate_type=($request['rate_type'][$key]=='Case_Rate')? "1" : "0";
                if( $CaseStaff->rate_type == "1"){ 
                    $CaseStaff->rate_amount=str_replace(",","",$request['new_rate'][$key]);
                }
                $CaseStaff->save();

                //Activity tab
                $datauser=[];
                $datauser['activity_title']='linked staff';
                $datauser['case_id']=$request->case_id;
                $datauser['staff_id']=$key;
                $this->caseActivity($datauser);

                $data=[];
                $data['user_id']=$key;
                $data['client_id']=$key;
                $data['case_id']=$request->case_id;
                $data['activity']='linked attorney';
                $data['type']='contact';
                $data['action']='link';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);
            }

            $caseStatusChange=CaseMaster::find($request->case_id);
            $caseStatusChange->is_entry_done="1";
            $caseStatusChange->save();

            $caseStageHistory = new CaseStageUpdate;
            $caseStageHistory->stage_id=($caseStatusChange->case_status)??NULL;
            $caseStageHistory->case_id=$caseStatusChange->id;
            $caseStageHistory->start_date = convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($caseStatusChange->case_open_date)))), auth()->user()->user_timezone ?? 'UTC'); 
            $caseStageHistory->end_date = convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($caseStatusChange->case_open_date)))), auth()->user()->user_timezone ?? 'UTC'); 
            $caseStageHistory->created_by=Auth::user()->id; 
            $caseStageHistory->created_at=$caseStatusChange->case_open_date; 
            $caseStageHistory->save();

            if($caseStatusChange->case_statute_date !=NULL){
                $this->saveSOLEventIntoCalender($request->case_id);
            }

            
            // $s=Session::get('caseLinkToClient');
            // if(isset($s))
            // {
            //     $clientId=Session::get('clientId');
            //     $CaseClientSelection=new CaseClientSelection;
            //     $CaseClientSelection->case_id=$request->case_id;
            //     $CaseClientSelection->selected_user=$clientId;
            //     $CaseClientSelection->save();
           
            //     $ClientActivityHistory=[];
            //     $ClientActivityHistory['acrtivity_title']='linked contact';
            //     $ClientActivityHistory['activity_by']=Auth::User()->id;
            //     $ClientActivityHistory['activity_for']=($clientId)??NULL;
            //     $ClientActivityHistory['type']="2";
            //     $ClientActivityHistory['task_id']=NULL;
            //     $ClientActivityHistory['case_id']=$request->case_id;
            //     $ClientActivityHistory['created_by']=Auth::User()->id;
            //     $ClientActivityHistory['created_at']=date('Y-m-d H:i:s');
            //     $this->saveClientActivity($ClientActivityHistory);
            //     return response()->json(['errors'=>'','reload'=>'true']);
            //     exit;
            // }
            // $sCompany=Session::get('caseLinkToCompany');
            // if(isset($sCompany))
            // {
            //     $companyId=Session::get('companyId');
            //     $CaseClientSelection=new CaseClientSelection;
            //     $CaseClientSelection->case_id=$request->case_id;
            //     $CaseClientSelection->selected_user=$companyId;
            //     $CaseClientSelection->save();
           
            //     $ClientActivityHistory=[];
            //     $ClientActivityHistory['acrtivity_title']='linked contact';
            //     $ClientActivityHistory['activity_by']=Auth::User()->id;
            //     $ClientActivityHistory['activity_for']=($companyId)??NULL;
            //     $ClientActivityHistory['type']="2";
            //     $ClientActivityHistory['task_id']=NULL;
            //     $ClientActivityHistory['case_id']=$request->case_id;
            //     $ClientActivityHistory['created_by']=Auth::User()->id;
            //     $ClientActivityHistory['created_at']=date('Y-m-d H:i:s');
            //     $this->saveClientActivity($ClientActivityHistory);
            //     return response()->json(['errors'=>'','reload'=>'true']);
            //     exit;
            // }
          


            DB::table('temp_user_selection')->where("user_id",Auth::user()->id)->delete();
            session(['popup_success' => 'Case has been created.']);

            
        }else{
            return response()->json(['errors'=>'Please select at least one staff member.']);
        }
       
        return response()->json(['errors'=>'','user_id'=>$request->user_id,'case_unique_number'=>$caseStatusChange->case_unique_number]);
        exit;
    }

    public function loadStatus(Request $request)
    {        
//        $caseStageList = CaseStage::where("status","1")->get();
        $getChildUsers=$this->getParentAndChildUserIds();
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();          

        $CaseMaster = CaseMaster::where("id",$request->case_id)->get();
        return view('case.changeStatus',compact('CaseMaster','caseStageList'));
    }
    public function saveStatus(Request $request)
    {
        // print_r($request->all());exit;
        $CaseMaster = CaseMaster::find($request->case_id);        

        $caseStageHistory = CaseStageUpdate::firstOrNew(array('case_id' => $CaseMaster->id,'stage_id'=>$CaseMaster->case_status));
        $caseStageHistory->stage_id=($CaseMaster->case_status)??0;
        $caseStageHistory->case_id=$CaseMaster->id;
        $caseStageHistory->start_Date=date("Y-m-d");
        $caseStageHistory->end_Date=date("Y-m-d"); 
        $caseStageHistory->created_by=Auth::user()->id; 
        $caseStageHistory->save();

        $CaseMaster->case_status=$request->case_status;
        $CaseMaster->save();
        session(['popup_success' => 'Case status has been changed.']);
        return response()->json(['errors'=>'','case_id'=>$CaseMaster->id]);
        exit;
    }

    public function loadCaseUpdate(Request $request)
    {   
        $case_id=$request->case_id;     
        return view('case.updateStatus',compact('case_id'));
    }
    public function saveCaseUpdate(Request $request)
    {
        $CaseUpdate = new CaseUpdate;
        $CaseUpdate->update_status =$request->case_update;
        $CaseUpdate->case_id =$request->case_id;
        $CaseUpdate->created_by=Auth::User()->id; 
        $CaseUpdate->save();
        session(['popup_success' => 'Your status update has been added']);
        return response()->json(['errors'=>'','case_id'=>$request->case_id]);
        exit;
    }
    public function updateCaseUpdate(Request $request)
    {
        $CaseUpdate = CaseUpdate::find($request->id);
        $CaseUpdate->update_status =$request->case_update;
        $CaseUpdate->updated_by=Auth::User()->id; 
        $CaseUpdate->save();
        session(['popup_success' => 'Your status update has been updated']);
        return redirect()->back();
    }
    public function deleteCaseUpdate(Request $request)
    {
        $id=$request->id;
        CaseUpdate::where("id", $id)->delete();

        return response()->json(['errors'=>'','id'=>$id]);
        exit;
    }
    public function editCase(Request $request)
    {   
        $CaseMaster = CaseMaster::find($request->case_id);
        $getChildUsers=$this->getParentAndChildUserIds();
        $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
      
        $CaseSolReminder = CaseSolReminder::where("case_id",$request->case_id)->get();

        
        //$caseStageList = CaseStage::where("status","1")->get();
        $getChildUsers=$this->getParentAndChildUserIds();
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();          

        $firmAddress = FirmAddress::select("firm_address.*","countries.name as countryname")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->orderBy('firm_address.is_primary','ASC')->get();

        return view('case.editCase',compact('CaseMaster','practiceAreaList','caseStageList','CaseSolReminder','firmAddress'));
    }
    public function saveEditCase(Request $request)
    {
       

        $validator = \Validator::make($request->all(), [
            'case_name' => 'required|unique:case_master,case_title,'.$request->case_id
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $CaseMaster = CaseMaster::find($request->case_id);
            if(isset($request->case_name)) { $CaseMaster->case_title=$request->case_name; }
            if(isset($request->case_number)) { $CaseMaster->case_number =$request->case_number; }
            if(isset($request->case_status)) { $CaseMaster->case_status=$request->case_status; }
            if(isset($request->case_description)) { $CaseMaster->case_description=$request->case_description; }
            if(isset($request->case_open_date)) { 
                $var =convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->case_open_date)))), auth()->user()->user_timezone ?? 'UTC');
                $CaseMaster->case_open_date=date('Y-m-d',strtotime($var)); 
            }

            if(isset($request->case_office)) { $CaseMaster->case_office=$request->case_office; }
            if(isset($request->case_statute)) { 
                $var =convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->case_statute)))), auth()->user()->user_timezone ?? 'UTC');
                $CaseMaster->case_statute_date=date('Y-m-d',strtotime($var)); 
            }else{ 
                $CaseMaster->case_statute_date=NULL;
                // remove events also from
                Event::where('case_id',$CaseMaster->id)->where('is_SOL', 'yes')->delete();
            }
            if(isset($request->conflict_check)) { 
                $CaseMaster->conflict_check="1"; 
                
            }
            if(isset($request->conflict_check_description)) { 
                $CaseMaster->conflict_check_description=$request->conflict_check_description; 
            }
           
            if(isset($request->practice_area_text)) { 
                $CasePracticeArea = new CasePracticeArea;
                $CasePracticeArea->title=$request->practice_area_text; 
                $CasePracticeArea->created_by=Auth::User()->id; 
                $CasePracticeArea->firm_id=Auth::User()->firm_name; 
                $CasePracticeArea->save();
                
                $CaseMaster->practice_area=$CasePracticeArea->id;
            }else{
                if(isset($request->practice_area)) { $CaseMaster->practice_area=$request->practice_area; }
            }
            $CaseMaster->updated_by=Auth::User()->id; 
            $CaseMaster->firm_id = auth()->user()->firm_name; 
            $CaseMaster->save();

            CaseSolReminder::where('case_id',$request->case_id)->delete();
            if(isset($request->case_statute)){
                for($i=0;$i<count($request->reminder_type)-1;$i++){
                    $CaseSolReminder = new CaseSolReminder;
                    $CaseSolReminder->case_id=$request->case_id; 
                    $CaseSolReminder->reminder_type=$request['reminder_type'][$i]; 
                    $CaseSolReminder->reminer_number=$request['reminder_days'][$i];
                    $CaseSolReminder->created_by=Auth::User()->id;
                    $reminderDate = \Carbon\Carbon::createFromFormat('Y-m-d', $CaseMaster->case_statute_date)->subDay($request['reminder_days'][$i])->format('Y-m-d'); // Subtracts reminder date day for case_statute_date 
                    $CaseSolReminder->remind_at=$reminderDate;  
                    $CaseSolReminder->save();
                }
            }
            
            // add sol in event calender
            if($CaseMaster->case_statute_date != null) {
                $this->saveSOLEventIntoCalender($request->case_id);
            } 

            $data=[];
            $data['case_id']=$request->case_id;
            $data['activity']='updated case';
            $data['type']='case';
            $data['action']='update';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
        }
        session(['popup_success' => 'Case details has been updated.']);
        return response()->json(['errors'=>'','case_id'=>$request->case_id]);
        exit;
    }
    public function showCaseDetails(Request $request)
    {
        DB::statement("DELETE t1 FROM case_client_selection t1 INNER JOIN case_client_selection t2 WHERE t1.id < t2.id AND t1.`case_id`=t2.`case_id` AND t1.`selected_user`=t2.`selected_user`");
        $allStatus=$mainArray=$caseCreatedDate=$lastStatusUpdate=$caseStatusHistory=$caseStageListArray='';
        $allEvents=$taskCountNextDays=$taskCompletedCounter=$overdueTaskList=$upcomingTaskList=$eventCountNextDays=$upcomingEventList='';
        $InvoicesOverdueCase=0;

        $CaseMaster = CaseMaster::join('users','users.id','=','case_master.created_by')->select("case_master.*","case_master.id as case_id","users.id","users.first_name","users.last_name","users.user_level","users.email","case_master.created_at as case_created_date","case_master.created_by as case_created_by")
                        ->where("case_unique_number",$request->id);
        if(auth()->user()->hasPermissionTo('access_only_linked_cases')) {
            $CaseMaster = $CaseMaster->whereHas('caseStaffAll', function($query) {
                            $query->where('user_id', auth()->id());
                        });
        }
        $CaseMaster = $CaseMaster->with('caseOffice')->first();
        if(!empty($CaseMaster)){
            $CaseMaster = $CaseMaster->setAppends(["uninvoiced_balance"]);
            $case_id= $CaseMaster->case_id;
            // DB::delete('DELETE t1 FROM case_event_linked_staff t1 INNER JOIN case_event_linked_staff t2 WHERE t1.id < t2.id AND t1.event_id = t2.event_id AND t1.user_id = t2.user_id');

            //Removed deleted record from this table
            $clientListForDuplication = CaseClientSelection::leftJoin('users','users.id','=','case_client_selection.selected_user')->where("case_id",$case_id)->where("users.id",NULL)->pluck("selected_user");
            if(!empty($clientListForDuplication))
            {
                CaseClientSelection::whereIn("selected_user",$clientListForDuplication)->delete();
            }
           
             if(\Route::current()->getName()=="info"){
                $lastStatusUpdate = CaseUpdate::join('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","case_update.update_status","case_update.created_at")->where("case_id",$case_id)->orderBy('created_at','DESC')->first();
               
                $caseStatusHistory=CaseStageUpdate::leftJoin('case_stage','case_stage.id','=','case_stage_history.stage_id')->select('case_stage_history.*','case_stage.stage_color')->where("case_stage_history.case_id",$case_id)->orderBy('case_stage_history.start_date','ASC')->get();
                if(!$caseStatusHistory->isEmpty()){
                    $caseStatusHistory=$caseStatusHistory->toArray();
                       
                    foreach($caseStatusHistory as $k=>$v){
                        $caseStatusHistory[$k]['days']=$caseStatusHistory[$k]['days'];
                        $caseStatusHistory[$k]['color']=$caseStatusHistory[$k]['stage_color'];
                        $caseStatusHistory[$k]['startDate']=$caseStatusHistory[$k]['start_date'];
                        $caseStatusHistory[$k]['endDate']=$caseStatusHistory[$k]['end_date'];                       
                    }
                }

                    //If Parent user logged in then show all child case to parent
                    if(Auth::user()->parent_user==0){
                        $caseStageListArray = CaseStage::where("status","1")->where("created_by",Auth::user()->id)->pluck('title','id');
                    }else{
                        $caseStageListArray = CaseStage::where("status","1")->where("created_by",Auth::user()->parent_user_id)->pluck('title','id');
                    }
                    //In Next 30 days upcoming task counter 
                    $taskCountNextDays=Task::select('id')->where('case_id',$case_id)->where('status',"0")->where("task_due_on","<=",date("Y-m-d", strtotime("+30 days")))->count();
                     
                    //Total completed task counter 
                     $taskCompletedCounter=Task::select('id')->where('case_id',$case_id)->where('status',"1")->count();

                      //Overdue task list 
                    $overdueTaskList=Task::select('*')->where('case_id',$case_id)->where('status',"0")->where("task_due_on","<=",date("Y-m-d"))->get();

                     //Upcoming task list 
                     $upcomingTaskList=Task::select('*')->where('case_id',$case_id)->where('status',"0")->where("task_due_on","<=",date("Y-m-d", strtotime("+30 days")))->where("task_due_on",">=",date("Y-m-d"))->get();

                      //In Next 365  days upcoming event counter 
                    $eventCountNextDays = EventRecurring::whereHas('event', function($query) use($case_id) {
                                        $query->where('case_id',$case_id);
                                    })->whereDate("start_date","<=",date("Y-m-d", strtotime("+365 days")))
                                    /* ->whereJsonContains('event_linked_staff', ['user_id' => (string)auth()->id()]) */->count();

                     //Upcoming event list 
                     $upcomingEventList = EventRecurring::whereHas('event', function($query) use($case_id) {
                                    $query->where('case_id',$case_id);
                                })->where("start_date","<=",date("Y-m-d", strtotime("+365 days")))->where("start_date",">=",date("Y-m-d"))
                                ->whereJsonContains('event_linked_staff', ['user_id' => (string)auth()->id()])
                                ->orderBy("start_date","ASC")->with('event')->limit("4")->get();

                     $startDate=date('Y-m-d');
                     $InvoicesOverdueCase=Invoices::where("invoices.case_id",$case_id)->where('invoices.due_date',"<",$startDate)->count();


            }
            if(\Route::current()->getName()=="tasks"){
            }
            if(\Route::current()->getName()=="status_updates"){
                $allStatus = CaseUpdate::join('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","case_update.update_status","case_update.created_at","case_update.id as case_update_id")->where("case_id",$case_id)->orderBy('created_at','DESC')->get();
               
            } 
            /* if(\Route::current()->getName()=="calendars"){
                $allStatus = CaseUpdate::join('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","case_update.update_status","case_update.created_at","case_update.id as case_update_id")->where("case_id",$case_id)->orderBy('created_at','DESC')->get();

                //Get all event by 
                $allEvents = CaseEvent::select("*")->where("case_id",$case_id);
                if($request->upcoming_events && $request->upcoming_events == 'on') {
                    $allEvents = $allEvents->whereDate("start_date", ">=", Carbon::now(auth()->user()->user_timezone ?? 'UTC')->format('Y-m-d'));
                }
                // $allEvents = $allEvents->whereHas('eventLinkedStaff', function($query) {
                //     $query->where('users.id', auth()->id());
                // })
                if(auth()->user()->parent_user != 0) {
                    $allEvents = $allEvents->whereHas('case.caseStaffAll', function($query) {
                        $query->where('user_id', auth()->id());
                    });
                }
                $allEvents = $allEvents->orderBy('start_date','ASC')->orderBy('start_time','ASC')
                ->with("eventLinkedStaff", "eventType", "eventLinkedContact", "eventLinkedLead")
                ->paginate(15)
                // ->groupBy(function($val) {
                //     return Carbon::parse($val->start_date)->format('Y');
                // })
                ;
                if($request->ajax()) {
                    return view('case.view.load_event_list', compact('allEvents'));
                }
            } */
            
            if(\Route::current()->getName()=="calendars"){
                $allStatus = CaseUpdate::join('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","case_update.update_status","case_update.created_at","case_update.id as case_update_id")->where("case_id",$case_id)->orderBy('created_at','DESC')->get();
                $allEvents = EventRecurring::whereHas('event', function($query) use($case_id) {
                                $query->where('case_id', $case_id);
                            });
                if(!isset($request->upcoming_events) || $request->upcoming_events == 'on') {
                    $allEvents = $allEvents->whereDate("start_date", ">=", Carbon::now(auth()->user()->user_timezone ?? 'UTC')->format('Y-m-d'));
                }
                $allEvents = $allEvents->orderBy("start_date", "ASC")->with("event", "event.eventType")->get();

                $allEvents = $allEvents->sortBy(function ($product, $key) {
                    return $product['start_date'].$product['event']['start_time'];
                })->values();
            }

            if(\Route::current()->getName()=="recent_activity"){
                $mainArray=[];
                $allStatus = CaseActivity::where("case_id",$case_id)->orderBy('case_activity.created_at','DESC')->get();
                foreach($allStatus as $k=>$vv){
                    $caseData=$this->getCaseData($vv->case_id);
                    $createdUSer=$this->getCreatedByUserData($vv->created_by);
                    $staffUSer=$this->getCreatedByUserData($vv->staff_id);

                    $mainArray[$k]['id']=$createdUSer->id;
                    $mainArray[$k]['title']=$vv->activity_title;
                    $mainArray[$k]['created_id']=$vv->created_by;
                    $mainArray[$k]['created_by']=$createdUSer->first_name.' '.$createdUSer->last_name;
                    $mainArray[$k]['case_name']=$caseData->case_title;
                    $mainArray[$k]['staff_id']=$vv->staff_id;
                    $mainArray[$k]['staff_name']=(isset($staffUSer->first_name)) ?  $staffUSer->first_name.' '.$staffUSer->last_name:  '';
                    $mainArray[$k]['extra_notes']=$vv->extra_notes;
                    $mainArray[$k]['activity_type']=$vv->activity_type;

                    $CommonController= new CommonController();
                    $timezone=Auth::User()->user_timezone;
                    $convertedDate=$CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($vv->created_at)),$timezone);
                    $mainArray[$k]['created_at']=$convertedDate;

                    $caseCreatedAt=$CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($CaseMaster->created_at)),$timezone);
                    $caseCreatedDate=date('m-d-Y h:i a',strtotime($caseCreatedAt));
                    
                }
                // print_r($mainArray);
                // exit;
            }

            
            if(\Route::current()->getName()=="case_link"){
                $allStatus = CaseUpdate::join('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","case_update.update_status","case_update.created_at","case_update.id as case_update_id","users.is_published")->where("case_id",$case_id)->orderBy('created_at','DESC')->get();
               
            } 
            $caseBiller=[];
            $flatFeeEntryData=$timeEntryData=$expenseEntryData=$caseClients=$InvoicesTotal=$InvoicesCollectedTotal=$InvoicesPendingTotal='';
            if(\Route::current()->getName()=="overview"){
                $flatFeeEntryData=$this->getFlatfeeEntryTotalByCase($case_id);    
                $timeEntryData=$this->getTimeEntryTotalByCase($case_id);    
                $expenseEntryData=$this->getExpenseEntryTotalByCase($case_id);    
                // $trustUSers=$this->getTrustBalance($case_id); 
                $caseClients = CaseClientSelection::where("case_id", $case_id)->with("user", "user.userAdditionalInfo")->get();
                $InvoicesTotal= Invoices::/* where("invoices.created_by",Auth::User()->id)-> */where("case_id",$case_id)->where("status", "!=", "Forwarded")->sum("total_amount");
                $InvoicesCollectedTotal= Invoices::/* where("invoices.created_by",Auth::User()->id)-> */where("case_id",$case_id)->sum("paid_amount");
                $InvoicesPendingTotal= Invoices::/* where("invoices.created_by",Auth::User()->id)-> */where("case_id",$case_id)->where("status", "!=", "Forwarded")->sum("due_amount");
            }
            if(\Route::current()->getName()=="overview" || \Route::current()->getName()=="invoices"){
                $caseBiller = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
                ->leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
                ->select("users.id","users.id as uid","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","case_client_selection.user_role as user_role","contact_group_id","case_client_selection.billing_method","case_client_selection.billing_amount")->where("case_client_selection.case_id",$case_id)->where("is_billing_contact","yes")->first();
            }
            $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
            ->leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
            ->leftJoin('user_role','user_role.id','case_client_selection.user_role')
            ->leftJoin('client_group','client_group.id','users_additional_info.contact_group_id')
            ->select("users.id","users.id as uid","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","case_client_selection.is_billing_contact","contact_group_id","users.profile_image","users.is_published","multiple_compnay_id","user_role.role_name","client_group.group_name")->where("case_client_selection.case_id",$case_id)->get();

            $CompanyList=User::where("user_level","4")->where("parent_user",Auth::User()->id)->pluck("first_name","id");

            $linkedCompany=CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
           ->where("case_client_selection.case_id",$case_id)->where("user_level","4")->get()->pluck("first_name","selected_user");
            
            $totalCalls=$getAllFirmUser='';
            if(\Route::current()->getName()=="communications/calls"){

                $totalCalls = Calls::where('case_id', $case_id)->count();
                
                $getAllFirmUser=firmUserList();
                
                // $getAllFirmUser =  Calls::select("calls.id as cid","u1.id","u1.first_name","u1.last_name","calls.call_for");
                // $getAllFirmUser = $getAllFirmUser->leftJoin('users as u1','calls.call_for','=','u1.id')->where("case_id",$case_id)->groupBy("call_for")->get();
            }

            $getChildUsers=$this->getParentAndChildUserIds();
            $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
          
      
            $caseStageList = CaseStage::select("*")->where("status","1");
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $caseStageList = $caseStageList->whereIn("created_by",$getChildUsers);          
            $caseStageList=$caseStageList->orderBy('stage_order','ASC')->get();

            
            $leadAttorney = CaseStaff::join('users','users.id','=','case_staff.lead_attorney')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","case_staff.originating_attorney","case_staff.lead_attorney")->where("case_id",$case_id)->where("lead_attorney","!=",null)->get();
            // print_r($leadAttorney);
            $originatingAttorney = CaseStaff::join('users','users.id','=','case_staff.originating_attorney')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","case_staff.originating_attorney","case_staff.lead_attorney")->where("case_id",$case_id)->where("originating_attorney","!=",null)->get();
          
            $staffList = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","users.id as uid")->where("case_id",$case_id)->get();
        
            $caseStat = DB::table('view_case_state')->select("*")->where("id",$case_id)->first();


            $totalCaseIntakeForm=0;
            if(\Route::current()->getName()=="intake_forms"){
                $totalCaseIntakeForm= $allForms = CaseIntakeForm::leftJoin('intake_form','intake_form.id','=','case_intake_form.intake_form_id')->select("intake_form.id as intake_form_id","case_intake_form.created_at as case_intake_form_created_at","intake_form.*","case_intake_form.*")->where("case_id",$case_id)->count();
            }
            
            if(\Route::current()->getName()=="documents"){
            }
            //Get total number of case avaulable in system 
            $caseCount = CaseMaster::where("created_by",Auth::User()->id)->where('is_entry_done',"1")->count();
            return view('case.viewCase',compact("CaseMaster","caseCllientSelection","practiceAreaList","caseStageList","leadAttorney","originatingAttorney","staffList","lastStatusUpdate","caseStatusHistory","caseStageListArray","allStatus","mainArray","caseCreatedDate","allEvents","caseCount","taskCountNextDays","taskCompletedCounter","overdueTaskList","upcomingTaskList","eventCountNextDays","upcomingEventList",'flatFeeEntryData','timeEntryData','expenseEntryData','caseClients','InvoicesTotal','InvoicesPendingTotal','InvoicesCollectedTotal','caseBiller','getAllFirmUser','totalCalls','caseStat','InvoicesOverdueCase','totalCaseIntakeForm','linkedCompany','CompanyList'));
        } else {
            abort(404);
        }
    }

    public function editBillingContactPopup(Request $request)
    {        
        $case_id=$request->case_id;
        $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
        ->leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
        ->select("users.id","users.id as uid","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","case_client_selection.user_role as user_role","contact_group_id","case_client_selection.*")->where("case_client_selection.case_id",$case_id)->get();

        $caseCllientUpdateCreated = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
        ->select("users.id","users.id as uid","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","case_client_selection.user_role as user_role","case_client_selection.*")->where("case_client_selection.case_id",$case_id)->first();
        $caseCllientUpdateUpdated='';
        if((isset($caseCllientUpdateCreated)) && ($caseCllientUpdateCreated['updated_by']!=NULL)){
            $caseCllientUpdateUpdated = CaseClientSelection::join('users','users.id','=','case_client_selection.updated_by')
            ->select("users.id","users.id as uid","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","case_client_selection.user_role as user_role","case_client_selection.*")->where("case_client_selection.case_id",$case_id)->first();
        }
        $caseDefaultBiller = CaseMaster::where("id",$case_id)->first();
        $caseMasterDefaultBiller = CaseClientSelection::where("case_id",$case_id)->where("is_billing_contact","yes")->first();

        // return view('case.view.timebilling.editBillingContactPopup',compact('case_id','caseCllientSelection','caseCllientUpdateCreated','caseCllientUpdateUpdated','caseDefaultBiller','caseMasterDefaultBiller'));
        $view = view('case.view.timebilling.editBillingContactPopup',compact('case_id','caseCllientSelection','caseCllientUpdateCreated','caseCllientUpdateUpdated','caseDefaultBiller','caseMasterDefaultBiller'))->render();
        return response()->json(['case' => $caseDefaultBiller, 'view' => $view]);
    }

    public function saveBillingContactPopup(Request $request)
    {
        // print_r($request->all());exit;
        $validator = \Validator::make($request->all(), [
           'case_id' => 'required',
       ]);

       if ($validator->fails())
       {
           return response()->json(['errors'=>$validator->errors()->all()]);
       }else{
        $caseMaster=CaseMaster::find($request->case_id);
        $caseMaster->billing_method=$request->billingMethod;
        $caseMaster->billing_amount=($request->default_rate)??$caseMaster->billing_amount;
        $caseMaster->save();
        
        if($request->billing_method != "flat" || $request->billing_method != "mixed") {
            FlatFeeEntry::where('case_id', $request->case_id)->where("flat_fee_entry.status","unpaid")->where("flat_fee_entry.invoice_link",NULL)->forceDelete();
        }
        /* if($request->billingMethod == "flat") {
            FlatFeeEntry::updateOrCreate(
                [
                    'case_id' => $caseMaster->id,
                    'is_primary_flat_fee' => 'yes'
                ], [
                'case_id' => $caseMaster->id,
                'user_id' => auth()->id(),
                'entry_date' => Carbon::now(),
                'cost' =>  $request->default_rate ?? 0.00,
                'time_entry_billable' => 'yes',
                'created_by' => auth()->id(), 
                'is_primary_flat_fee' => "yes",
            ]);
        } */

            $CaseClientSelection =CaseClientSelection::where("case_id",$request->case_id)->get();
            if(!$CaseClientSelection->isEmpty()){
                foreach($CaseClientSelection as $k=>$v){
                    $updateBilling=CaseClientSelection::find($v->id);
                    if($v->selected_user==$request->staff_user_id){
                        $updateBilling->is_billing_contact="yes";
                    }else{
                        $updateBilling->is_billing_contact="no";
                    }
                    $updateBilling->billing_method=$request->billingMethod;
                    $updateBilling->billing_amount=($request->default_rate)??0.00;
                    $updateBilling->save();
                }
            }
        
            // //Get Flat fees entry
            if($request->billingMethod == "flat" || $caseMaster->billing_method == "mixed") {
                $totalFlatFee = FlatFeeEntry::where('case_id', $request->case_id)->sum('cost');
                $remainFlatFee = $request->default_rate - $totalFlatFee;
                if($remainFlatFee > 0) {
                    FlatFeeEntry::create([
                        'case_id' => $caseMaster->id,
                        'user_id' => auth()->id(),
                        'entry_date' => Carbon::now(),
                        'cost' =>  $remainFlatFee,
                        'time_entry_billable' => 'yes',
                        'firm_id' => Auth::User()->firm_name,
                        'created_by' => auth()->id(),
                    ]);
                }                                
            }
            return response()->json(['errors'=>'']);
            exit;
       }
    }
    public function loadTimeEntryBlocks(Request $request)
    {        
        $case_id=$request->case_id;
        $CaseMaster = CaseMaster::where("id",$case_id)->first();
        $requestData= $_REQUEST;
        $timeSlot=explode(" - ",$requestData['time_slot']);
        $from=$timeSlot[0];
        $to=$timeSlot[1]??convertUTCToUserTimeZone('dateOnly');
        $timeTotalBillable=$timeTotalNonBillable=$timeTotalNonBillableHours=$timeTotalBillableHours=$invoiceEntry=$invoiceEntryHours=0;
        $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($timeSlot[0]))))), auth()->user()->user_timezone ?? 'UTC')));
        $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim(($timeSlot[1]??convertUTCToUserTimeZone('dateOnly'))))))), auth()->user()->user_timezone ?? 'UTC')));
            
        $TimeEntryLog=[];
        $TimeEntry=TaskTimeEntry::leftJoin("users","task_time_entry.user_id","=","users.id")
        ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
        ->leftJoin("case_master","case_master.id","=","task_time_entry.case_id")
        ->leftJoin("invoices","invoices.id","=","task_time_entry.invoice_link")
        ->select('task_time_entry.*')
        ->where("case_master.id",$case_id)
        ->whereBetween("task_time_entry.entry_date",[$startDt,$endDt])
        ->get();
        foreach($TimeEntry as $TK=>$TE){
            if($TE['rate_type']=="flat"){
                if($TE['time_entry_billable']=="yes"){
                        $timeTotalBillable+= str_replace(",","",$TE['entry_rate']);
                        $timeTotalBillableHours+=str_replace(",","",$TE['duration']);
                }else{
                        $timeTotalNonBillable+= str_replace(",","",$TE['entry_rate']);
                        $timeTotalNonBillableHours+=str_replace(",","",$TE['duration']);
                }
            }else{
                if($TE['time_entry_billable']=="yes"){
                    $timeTotalBillable+=( str_replace(",","",$TE['entry_rate'])*str_replace(",","",$TE['duration']));
                    $timeTotalBillableHours+=str_replace(",","",$TE['duration']);
                }else{
                    $timeTotalNonBillable+=( str_replace(",","",$TE['entry_rate'])*str_replace(",","",$TE['duration']));
                    $timeTotalNonBillableHours+=str_replace(",","",$TE['duration']);

                }
            }

            if($TE['status']=="paid"){
                if($TE['rate_type']=="flat"){
                    if($TE['time_entry_billable']=="yes"){
                            $invoiceEntry+= str_replace(",","",$TE['entry_rate']);
                            $invoiceEntryHours+=str_replace(",","",$TE['duration']);
                    }
                }else{
                    if($TE['time_entry_billable']=="yes"){
                        $invoiceEntry+=( str_replace(",","",$TE['entry_rate'])*str_replace(",","",$TE['duration']));
                        $invoiceEntryHours+=str_replace(",","",$TE['duration']);
                    }
                }
    
            }
        }
        \Log::info("timeTotalBillableHours > " .$timeTotalBillableHours);
        $TimeEntryLog['billable_entry_hours']=$timeTotalBillableHours;
        $TimeEntryLog['non_billable_entry_hours']=$timeTotalNonBillableHours;
        $TimeEntryLog['billable_entry']=$timeTotalBillable;
        $TimeEntryLog['non_billable_entry']=$timeTotalNonBillable;
        $TimeEntryLog['total_entry']= $TimeEntryLog['billable_entry']+$TimeEntryLog['non_billable_entry'];
        $TimeEntryLog['total_entry_hours']= $TimeEntryLog['billable_entry_hours']+$TimeEntryLog['non_billable_entry_hours'];
        $TimeEntryLog['invoice_hours']= $invoiceEntryHours;
        $TimeEntryLog['invoice_hours_total']= $invoiceEntry;

        return view('case.view.timebilling.time_entries_block',compact('case_id','CaseMaster','TimeEntryLog','from','to'));
    }

    public function getCreatedByUserData($id){
        $user = User::select("id","first_name","last_name")->where("id",$id)->first();
        if(!empty($user)){
           return $user;
        }
    }

    public function getCaseData($id){
        $case = CaseMaster::select("id","case_title")->where("id",$id)->first();
        if(!empty($case)){
           return $case;
        }
    }
    public function loadFinishStep(Request $request)
    {
        return view('contract.loadFinal');
    }
    public function loadColorPicker(Request $request)
    {        
        $user = User::where("id",$request->user_id)->get();
        return view('contract.loadColorPicker',compact('user'));
    }
    public function saveColorCode(Request $request)
    {
        $user = User::find($request->user_id);
        $user->default_color="#".$request->colorcode;
        $user->save();
        return response()->json(['errors'=>'','user_id'=>$user->id]);
        exit;
    }
    public function loadRateBox(Request $request)
    {        
        $user = User::where("id",$request->user_id)->get();
        return view('contract.loadRateBox',compact('user'));
    }
    public function saveRate(Request $request)
    {
        $user = User::find($request->user_id);
        $user->default_rate=($request->default_rate)??"0.0";
        $user->save();
        return response()->json(['errors'=>'','user_id'=>$user->id]);
        exit;
    }

     public function dashboard()
     {
        $lastLoginUsers = User::where("parent_user",Auth::User()->id)->orderBy('last_login','desc')->limit(5)->get();
        return view('contract.dashboard',['lastLoginUsers'=>$lastLoginUsers]);

     }
     public function attorneysView(Request $request,$id)
     {
        $contractUserID=base64_decode($id);
        $userProfile = User::select("users.*","countries.name as countryname")->join('countries','users.country',"=","countries.id")->where("users.id",$contractUserID)->first();
        if(!empty($userProfile)){
            $userProfileCreatedBy = User::select('users.id as pid' ,'users.user_title as ptitle',DB::raw('CONCAT(first_name, " ",last_name) as name'))->where("users.id",$userProfile->parent_user)->get();
        }
        return view('contract.attorneysView',['userProfile'=>$userProfile,'userProfileCreatedBy'=>$userProfileCreatedBy,'id'=>$id]);
     }   
        

     public function loadProfile(Request $request)
     {
        $contractUserID=base64_decode($request->user_id);
         $country = Countries::get();
         $userProfile = User::select("users.*","countries.name as countryname")->join('countries','users.country',"=","countries.id")->where("users.id",$contractUserID)->first();
         if(!empty($userProfile)){
             $userProfileCreatedBy = User::select('users.id as pid' ,'users.user_title as ptitle',DB::raw('CONCAT(first_name, " ",last_name) as name'))->where("users.id",$userProfile->parent_user)->get();
         }
         return view('contract.loadProfile',compact('userProfile','country','userProfileCreatedBy'));
     }
     public function saveProfile(Request $request)
     {
         $validator = \Validator::make($request->all(), [
             'first_name' => 'required',
             'last_name' => 'required',
             'email' => 'required|unique:users,email,'.base64_decode($request->uid).',id,deleted_at,NULL',
             'user_type' => 'required',
         ]);
         if ($validator->fails())
         {
             return response()->json(['errors'=>$validator->errors()->all()]);
         }else{
             $user =User::firstOrNew(array('id' => base64_decode($request->uid)));
           
             if(isset($request->first_name)){ $user->first_name=trim($request->first_name); }
             if(isset($request->middle_name)){ $user->middle_name=trim($request->middle_name); }
             if(isset($request->last_name)){ $user->last_name=trim($request->last_name); }
             if(isset($request->street)) { $user->street=trim($request->street); }
             if(isset($request->apt_unit)) { $user->apt_unit=trim($request->apt_unit); }
             if(isset($request->city)) { $user->city=trim($request->city); }
             if(isset($request->state)) { $user->state=trim($request->state); }
             if(isset($request->postal_code)) { $user->postal_code=trim($request->postal_code); }
             if(isset($request->country)) { $user->country=trim($request->country); }
             if(isset($request->home_phone)) { $user->home_phone=trim($request->home_phone); }
             if(isset($request->work_phone)) { $user->work_phone=trim($request->work_phone); }
             if(isset($request->cell_phone)) { $user->mobile_number=trim($request->cell_phone); }
             if(isset($request->user_title)) { $user->user_title=trim($request->user_title); }
             if(isset($request->default_rate)) { $user->default_rate=trim($request->default_rate); }
             if(isset($request->user_type)) { $user->user_type=trim($request->user_type); }
             $user->updated_by =Auth::User()->id;
             $user->save();

             if($user->email!=$request->email){
                $user->email=$request->email;
                $user->token  = Str::random(40);
                $user->user_status  = "2";  // Default status is inactive once verified account it will activated.
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
                $sendEmail = $this->sendMail($userEmail);
                if($sendEmail=="1"){
                    $user->is_sent_welcome_email  = "1";  // Welcome email sent to user.
                    $user->save();
                }
             }
            return response()->json(['errors'=>'','user_id'=>$user->id]);
            exit;
         }
     }

     public function loadDeactivateUser(Request $request)
     {
        $contractUserID=base64_decode($request->user_id);
        $user = User::select("users.*")->where("users.id",$contractUserID)->first();
        $allUser = User::select("*")->where("users.parent_user",$user->parent_user)->where("users.id","!=",$user->id)->get();
        return view('contract.loadDeactivateUser',compact('user','allUser'));
     }

     public function saveDeactivate(Request $request)
     {
         $validator = \Validator::make($request->all(), [
             'reason' => 'required'
         ]);
         if ($validator->fails())
         {
             return response()->json(['errors'=>$validator->errors()->all()]);
         }else{
             $user =User::find($request->user_id);
             if(isset($request->user_id)) { $user->user_status="3"; }
             $user->updated_by =Auth::User()->id;
             $user->save();

             $userDeactivate =new DeactivatedUser;
             $userDeactivate->user_id= $request->user_id;
             if(isset($request->reason)) { $userDeactivate->reason=$request->reason; }
             if(isset($request->other_reason)) { $userDeactivate->other_reason=$request->other_reason; }
             if(isset($request->assign_to)) { $userDeactivate-> assigned_to=$request->assign_to; }
             $userDeactivate->created_by =Auth::User()->id;
             $userDeactivate->save();

            return response()->json(['errors'=>'']);
            exit;
         }
     }

     public function caseInnerShot(Request $request)
     {
        if (\Route::current()->getName()=='info') {
            
        }
        
     }



     ////Practice Area
     public function practice_areas()
     {
     
        $getChildUsers=$this->getParentAndChildUserIds();
        $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
         return view('practice_area.index',compact('practiceAreaList'));
     }
 
     public function loadPracticeArea()
     {   
         $columns = array('id', 'title', 'status','created_at');
         $requestData= $_REQUEST;
         $getChildUsers=$this->getParentAndChildUserIds();
         $case = CasePracticeArea::select('case_practice_area.*')->where("firm_id",Auth::User()->firm_name);
         $totalData=$case->count();
         $totalFiltered = $totalData; 
         if( !empty($requestData['search']['value']) ) {   
             $case = $case->where( function($q) use ($requestData){
                 $q->where( function($select) use ($requestData){
                     $select->orWhere( DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%".$requestData['search']['value']."%");
                 });
             });
         }
         if( !empty($requestData['search']['value']) ) { 
             $totalFiltered = $case->count(); 
         }
         $case = $case->offset($requestData['start'])->limit($requestData['length']);
         $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
         $case = $case->get()->each->setAppends(["linked_case_count","created_by_name","decode_id"]);
         $json_data = array(
             "draw"            => intval( $requestData['draw'] ),   
             "recordsTotal"    => intval( $totalData ),  
             "recordsFiltered" => intval( $totalFiltered ), 
             "data"            => $case 
         );
         echo json_encode($json_data);  
     }

     public function deletePracticeArea(Request $request)
    {
        $id=$request->id;
        CasePracticeArea::where("id", $id)->delete();
      //  session(['popup_success' => 'Practice area was deleted']);

        return response()->json(['errors'=>'','id'=>$id]);
        exit;
    }
    public function loadAddPracticeArea()
    {
        return view('practice_area.addPracticeArea');
    }

    public function saveAddPracticeArea(Request $request)
    {
        $user_id=$request->user_id;
        $validator = \Validator::make($request->all(), [
            'area_name' => 'required|max:255'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CasePracticeArea=new CasePracticeArea;
            $CasePracticeArea->title=$request->area_name; 
            $CasePracticeArea->status="1";
            $CasePracticeArea->firm_id =Auth::User()->firm_name;
            $CasePracticeArea->created_by =Auth::User()->id;
            $CasePracticeArea->save();
            session(['popup_success' => 'Your practice area has been created.']);
            return response()->json(['errors'=>'','group_id'=>$CasePracticeArea->id]);
            exit;
        }
    }
    public function loadEditPracticeArea(Request $request)
    {
        $id=$request->id;
        $CasePracticeArea=CasePracticeArea::find($id);    
        return view('practice_area.editPracticeArea',compact("CasePracticeArea"));
    }
    public function saveEditPracticeArea(Request $request)
    {
        $id=$request->id;
        $validator = \Validator::make($request->all(), [
            'area_name' => 'required|max:255'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CasePracticeArea=CasePracticeArea::find($id);
            $CasePracticeArea->title=$request->area_name; 
            $CasePracticeArea->updated_by=Auth::User()->id; 
            $CasePracticeArea->save();
            session(['popup_success' => 'Your practice area has been updated.']);

            return response()->json(['errors'=>'','group_id'=>$CasePracticeArea->id]);
            exit;
        }
    }

    public function loadTypeSelection(Request $request)
    {   
        return view('case.view.type_selection');
    }

    public function loadAfterFirstCase(Request $request)
    {   
        return view('case.view.show_after_first_case');
    }
    public function unlinkSelection(Request $request)
    {
        $id=$request->id;
        $CaseClientSelection = CaseClientSelection::find($id);
        $data=[];
        $data['user_id']=$CaseClientSelection->selected_user;
        $data['client_id']=$CaseClientSelection->selected_user;
        $data['case_id']=$CaseClientSelection->case_id;
        $data['activity']='unlinked Contact';
        $data['type']='contact';
        $data['action']='unlink';
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);

        $data1=[];
        $data1['activity_title']='unlinked contact';
        $data1['case_id']=$CaseClientSelection->case_id;
        $data1['activity_type']='';
        $data1['staff_id']=$CaseClientSelection->selected_user;
        $this->caseActivity($data1);
        
        // remove from task_linked_staff and case_allocation staff
        /* $CaseEventData = CaseEvent::where('case_id',$CaseClientSelection->case_id)->get();
        
        if(count($CaseEventData) > 0) {
            foreach ($CaseEventData as $k=>$v){
                CaseEventLinkedStaff::where('user_id',$CaseClientSelection->selected_user)->where('event_id',$v->id)->delete();
                CaseEventLinkedContactLead::where('contact_id',$CaseClientSelection->selected_user)->where('event_id',$v->id)->delete();
            }
        } */
        // Unlink client from events
        $this->eventUnlinkClient($CaseClientSelection->case_id, $CaseClientSelection->selected_user);

        $TaskData = Task::where('case_id',$CaseClientSelection->case_id)->get();
        if(count($TaskData) > 0) {
            foreach ($TaskData as $k=>$v){
                CaseTaskLinkedStaff::where('user_id',$CaseClientSelection->selected_user)->where('task_id',$v->id)->delete();
            }
        }
        CaseClientSelection::where("id", $id)->delete();
        session(['popup_success' => 'Unlink '.$request->username.' from case']);
        return response()->json(['errors'=>'','id'=>$id]);
        exit;
    }
    public function loadExisting(Request $request)
    {   
        $case_id=$request->case_id;
        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        return view('case.view.addExisting',compact('CaseMasterClient','CaseMasterCompany','case_id'));
    }
    public function checkBeforeLinking(Request $request)
    {
        $id=$request->selectdValue;
        $case_id=$request->case_id;
        $isExists=CaseClientSelection::where("selected_user", $id)->where("case_id", $case_id)->count();
        
        $getUserInfo = User::leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')->select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as sel_name'),"users.id","user_level","client_portal_enable")->where("users.id",$id)->first();
        $clientList = [];
        if(!empty($getUserInfo) && $getUserInfo['user_level']=="4" ){
            $clientList = UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
            ->select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'),"users.id","user_level","client_portal_enable")->where("users.user_level","2");
            $clientList = $clientList->where("parent_user",Auth::user()->id);
            $clientList = $clientList->whereRaw("find_in_set($id,`multiple_compnay_id`)");
            $clientList = $clientList->get();
        }
        $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","users.id as user_id","users_additional_info.client_portal_enable")->where("case_client_selection.case_id",$case_id)->get();


        return view('case.link_contact_view',compact('caseCllientSelection','case_id','clientList','caseCllientSelection','getUserInfo','isExists'));

        // return response()->json(['errors'=>'','count'=>$isExists]);
        exit;
    }
    public function saveLinkSelection(Request $request)
    {
        // return $request->all();
       if(isset($request->case_id)) {
        $checkBeforAdd=CaseClientSelection::where("case_id",$request->case_id);
        $checkCaseHasClient = $checkBeforAdd->count();
        $checkBeforAdd = $checkBeforAdd->where("selected_user",$request->user_type)->count();
        if($checkBeforAdd<=0){
            $CaseClientSelection = new CaseClientSelection;
            $CaseClientSelection->case_id=$request->case_id; 
            $CaseClientSelection->selected_user=$request->user_type; 
            $CaseClientSelection->created_by=Auth::user()->id; 
            if($checkCaseHasClient == 0) {
                $CaseClientSelection->is_billing_contact = 'yes';
            }
            $CaseClientSelection->save();

            $data=[];
            $data['user_id']=$request->user_type;
            $data['client_id']=$request->user_type;
            $data['case_id']=$request->case_id;
            $data['activity']='linked Contact';
            $data['type']='contact';
            $data['action']='link';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            $data1=[];
            $data1['activity_title']='linked staff';
            $data1['case_id']=$request->case_id;
            $data1['activity_type']='';
            $data1['staff_id']=$request->user_type;
            $this->caseActivity($data1);

            $data2=[];
            $data2['user_id']=$request->user_type;
            $data2['client_id']=$request->user_type;
            $data2['case_id']=$request->case_id;
            $data2['activity']='linked attorney';
            $data2['type']='contact';
            $data2['action']='link';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data2);

            if(!empty($request->client_links)){
                foreach($request->client_links as $k=>$v ){
                    $CaseClientSelection = new CaseClientSelection;
                    $CaseClientSelection->case_id=$request->case_id; 
                    $CaseClientSelection->selected_user=$v; 
                    $CaseClientSelection->created_by=Auth::user()->id; 
                    $CaseClientSelection->save();                    
                }
            }            
            // Link user to events
            if(isset($request->user_link_share)) {
                $user = User::whereId($request->user_type)->first();
                if($user && $user->user_level == 4) {
                    if(count($request->client_links)) {
                        foreach($request->client_links as $item) {
                            $this->shareEventToClient($request->case_id, $item, (isset($request->user_link_share_read)) ? 'yes' : 'no');
                        }
                    }
                } else {
                    $this->shareEventToClient($request->case_id, $request->user_type, (isset($request->user_link_share_read)) ? 'yes' : 'no');
                }
            }

            session(['popup_success' => 'Your contact has been added']);
            return response()->json(['errors'=>'','count'=>$CaseClientSelection->id]);
            exit;
        }
   
    }
        // if(isset($request->case_id)) {
        //     $checkBeforAdd=CaseClientSelection::where("case_id",$request->case_id)->where("selected_user",$request->user_type)->count();
        //     if($checkBeforAdd<=0){
        //         $CaseClientSelection = new CaseClientSelection;
        //         $CaseClientSelection->case_id=$request->case_id; 
        //         $CaseClientSelection->selected_user=$request->user_type; 
        //         $CaseClientSelection->created_by=Auth::user()->id; 
        //         $CaseClientSelection->save();
        //         session(['popup_success' => 'Your contact has been added']);
        //         return response()->json(['errors'=>'','count'=>$CaseClientSelection->id]);
        //         exit;
        //     }else{
        //         $deleteBeforAdd=CaseClientSelection::where("case_id",$request->case_id)->where("selected_user",$request->user_type)->get();
        //         foreach($deleteBeforAdd as $k=>$v){
        //             if($k>0){
        //                 CaseClientSelection::where("id",$v->id)->delete();
        //             }
        //         }
        //         return response()->json(['errors'=>'Selected contact already linked.']);
        //         exit;
        //     }
        // }else{
        //     return response()->json(['errors'=>'Not saved']);
        //     exit;
        // }
    }


    //Staff Tab
    
    public function loadLeadAttorney(Request $request)
    {
        $case_id=$request->case_id;
        $caseStaff = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","case_staff.originating_attorney","case_staff.lead_attorney")->where("case_id",$case_id)->get();
        return view('case.view.lead_attorney',compact('case_id','caseStaff'));
    }

    public function saveLeadAttorney(Request $request)
    {
       
        $lead_attorney_id=$request->lead_attorney_id;
        $case_id=$request->case_id;
        if($lead_attorney_id==null){
            CaseStaff::where('case_id',$case_id)->update(['lead_attorney' => NULL]);
        }else{
            CaseStaff::where('lead_attorney',"!=", $lead_attorney_id)->where('case_id',$case_id)->update(['lead_attorney' => NULL]);
            CaseStaff::where('user_id', $lead_attorney_id)->update(['lead_attorney' => $lead_attorney_id]);
        }
        session(['popup_success' => 'Lead Attorney updated.']);
        return response()->json(['errors'=>'','case_id'=>$case_id]);
        exit;

    }

    public function loadOriginatingAttorney(Request $request)
    {
        $case_id=$request->case_id;
        $caseStaff = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","case_staff.originating_attorney","case_staff.lead_attorney")->where("case_id",$case_id)->get();
        return view('case.view.originating_attorney',compact('case_id','caseStaff'));
    }

    public function saveOriginatingAttorney(Request $request)
    {
       
        $lead_originating_id=$request->lead_originating_id;
        $case_id=$request->case_id;
        if($lead_originating_id==null){
            CaseStaff::where('case_id',$case_id)->update(['originating_attorney' => NULL]);
        }else{
            CaseStaff::where('originating_attorney',"!=", $lead_originating_id)->where('case_id',$case_id)->update(['originating_attorney' => NULL]);
            CaseStaff::where('user_id', $lead_originating_id)->update(['originating_attorney' => $lead_originating_id]);
        }
        session(['popup_success' => 'Lead Originating updated.']);
        return response()->json(['errors'=>'','case_id'=>$case_id]);
        exit;

    }

    public function UnlinkAttorney(Request $request)
    {
        $id=$request->id;
        $CaseStaffSelection = CaseStaff::where("id", $id)->first();
        if(!empty($CaseStaffSelection)){
            $data=[];
            $data['user_id']=$CaseStaffSelection->user_id;
            $data['client_id']=$CaseStaffSelection->user_id;
            $data['case_id']=$CaseStaffSelection->case_id;
            $data['activity']='unlinked Staff';
            $data['type']='staff';
            $data['action']='unlink';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            $data1=[];
            $data1['activity_title']='unlinked staff';
            $data1['case_id']=$CaseStaffSelection->case_id;
            $data1['activity_type']='';
            $data1['staff_id']=$CaseStaffSelection->user_id;
            $this->caseActivity($data1);
            // remove from task_linked_staff and case_allocation staff
            // Unlink user from event
            $this->eventUnlinkUser($CaseStaffSelection->case_id, $CaseStaffSelection->user_id);
            
            $TaskData = Task::where('case_id',$CaseStaffSelection->case_id)->get();
            if(count($TaskData) > 0) {
                foreach ($TaskData as $k=>$v){
                    CaseTaskLinkedStaff::where('user_id',$CaseStaffSelection->user_id)->where('task_id',$v->id)->delete();
                }
            }
            CaseStaff::where("id", $id)->delete();
            session(['popup_success' => 'Unlink '.$request->username.' from case']);
            return response()->json(['errors'=>'','id'=>$id]);
            exit;
        }else{
            return response()->json(['errors'=>'Not Found','id'=>$id]);
            exit;                    
        }
        
    }
   
    public function loadExistingStaff(Request $request)
    {
        $case_id=$request->case_id;
        $caseStaff = firmUserList();
        return view('case.view.addExistingStaff',compact('case_id','caseStaff'));
    }

    public function checkStaffBeforeLinking(Request $request)
    {
        $id=$request->selectdValue;
        $case_id=$request->case_id;
        $isExists=CaseStaff::where("user_id", $id)->where("case_id", $case_id)->count();
        return response()->json(['errors'=>'','count'=>$isExists]);
        exit;
    } 
    public function saveStaffLinkSelection(Request $request)
    {
        // return $request->all();
        if(isset($request->case_id)) {
            $checkBeforAdd=CaseStaff::where("case_id",$request->case_id)->where("user_id",$request->staff_user_id)->count();
            if($checkBeforAdd<=0){
                $CaseStaff = new CaseStaff;
                $CaseStaff->case_id=$request->case_id; 
                $CaseStaff->user_id=$request->staff_user_id; 
                $CaseStaff->lead_attorney=NULL;
                $CaseStaff->originating_attorney=NULL; 
                $CaseStaff->rate_type="0";
                if(isset($request->default_rate) && $request->default_rate!=''){
                    $CaseStaff->rate_type="1";
                    $CaseStaff->rate_amount=$request->default_rate;
                }
                $CaseStaff->created_by=Auth::user()->id; 
                $CaseStaff->save();

                // Link user with events
                if(isset($request->user_link_share_events)) {
                    $this->shareEventToUser($request->case_id, $request->staff_user_id, (isset($request->user_link_share_read)) ? 'yes' : 'no');
                    /* $eventRecurrings = EventRecurring::whereHas('event', function($query) use($request) {
                        $query->where('case_id', $request->case_id)->where('is_event_private', 'no');
                    })->get();
                    if($eventRecurrings) {
                        foreach($eventRecurrings as $key => $item) {
                            $decodeStaff = encodeDecodeJson($item->event_linked_staff);
                            if($decodeStaff->where('user_id', $request->staff_user_id)->where('is_linked', 'no')->first()) {
                                $newArray = [];
                                foreach($decodeStaff as $skey => $sitem) {
                                    if($sitem->user_id == $request->staff_user_id) {
                                        $sitem->is_linked = 'yes';
                                    }
                                    $newArray[] = $sitem;
                                }
                                $item->fill(['event_linked_staff' => encodeDecodeJson($newArray, 'encode')])->save();
                            } else {
                                $eventLinkedStaff = [
                                    'event_id' => $item->event_id,
                                    'user_id' => $request->staff_user_id,
                                    'is_linked' => 'yes',
                                    'attending' => "no",
                                    'comment_read_at' => Carbon::now(),
                                    'created_by' => auth()->id(),
                                    'is_read' => (isset($request->user_link_share_read)) ? 'yes' : 'no',
                                ];
                                $decodeStaff->push($eventLinkedStaff);
                                $item->fill(['event_linked_staff' => encodeDecodeJson($decodeStaff, 'encode')])->save();
                            }
                        }
                    } */
                }

                // add activity
                $userInfo = User::find($request->staff_user_id);
                $data=[];
                $data['user_id']=$request->staff_user_id;
                $data['client_id']=$request->staff_user_id;
                $data['case_id']=$request->case_id;
                $data['activity']='linked '.$userInfo->user_title;
                $data['type']='contact';
                $data['action']='link';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);

                session(['popup_success' => 'Your contact has been added']);
                return response()->json(['errors'=>''   ]);
                exit;
            }else{
                $deleteBeforAdd=CaseStaff::where("case_id",$request->case_id)->where("user_id",$request->staff_user_id)->get();
                foreach($deleteBeforAdd as $k=>$v){
                    if($k>0){
                        CaseStaff::where("id",$v->id)->delete();
                    }
                }
                return response()->json(['errors'=>'Selected contact already linked.']);
                exit;
            }
        }else{
            return response()->json(['errors'=>'Not saved']);
            exit;
        }
    }

    //Case Stages
    public function case_stages(Request $request)
    {
        $caseStage = CaseStage::select("*")->where("status","1");

        $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        $getChildUsers[]=Auth::user()->id;
        $caseStage = $caseStage->whereIn("created_by",$getChildUsers);          
         
        $caseStage=$caseStage->orderBy('stage_order','ASC')->get();
        return view('case.case_stages',compact('caseStage'));
    }
    public function saveCaseStages(Request $request)
    { 
        $stage_order = DB::table('case_stage')->max('stage_order');  
        $CaseStage = new CaseStage;
        $CaseStage->stage_order=$stage_order; 
        $CaseStage->title=substr($request->stage_name,0,255); 
        $CaseStage->stage_color=$request->stage_color;
        $CaseStage->created_by=Auth::user()->id; 
        $CaseStage->save();
        // session(['popup_success' => 'New stage name has been added.']);
        return response()->json(['errors'=>''   ]);
        exit;
    }
    public function deleteCaseStages(Request $request)
    {
        $id=$request->id;
        CaseStage::where("id", $id)->delete();
        CaseMaster::where('case_status',$id)->update(['case_status' => '0']);
        // session(['popup_success' => 'Stage name has been deleted']);
        return response()->json(['errors'=>'','id'=>$id]);
        exit;
    }
    public function reorderStages(Request $request)
    {
        $i = 1;
        foreach ($request['item'] as $value) {
            CaseStage::where('id',$value)->update(['stage_order' => $i]);
            $i++;
        }
        // session(['popup_success' => 'Stage order has been updated']);
        return response()->json(['errors'=>'']);
        exit;
    }
    public function editCaseStage(Request $request)
    {
        $CaseStage =CaseStage::find($request->id);
        return view('case.editCaseStage',compact('CaseStage'));
    }
    public function saveEditCaseStage(Request $request)
    {
        $CaseStage = CaseStage::find($request->id);
        $CaseStage->title=substr($request->stage_name,0,255);
        $CaseStage->stage_color=$request->stage_color; 
        $CaseStage->save();
        // session(['popup_success' => 'Stage name has been updated.']);
        return response()->json(['errors'=>''   ]);
        exit;
    }

    public function reloadCaserStages(Request $request)
    {
        $caseStage = CaseStage::select("*")->where("status","1");
        $getChildUsers = User::select("id")->where('firm_name',Auth::user()->firm_name)->get()->pluck('id');
        $getChildUsers[]=Auth::user()->id;
        $caseStage = $caseStage->whereIn("created_by",$getChildUsers);          
        $caseStage=$caseStage->orderBy('stage_order','ASC')->get();
        return view('case.reload_case_stages',compact('caseStage'));          
   }

    //Calender tab
      public function loadAddEventPage(Request $request)
      {
        $authUser = auth()->user();
        $lead_id="";
        if(isset($request->lead_id)){
            $lead_id=$request->lead_id;
        }
        $case_id=$request->case_id;
        // $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();

        $CaseMasterData = CaseMaster::where('firm_id', $authUser->firm_name)->where('is_entry_done',"1")->get();

        $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")
            ->where("users.user_type","5")->where("users.user_level","5")->where('firm_id', $authUser->firm_name)
            ->where("lead_additional_info.is_converted","no")->get();

        $country = Countries::get();
        $eventLocation = CaseEventLocation::where("location_future_use","yes")->get();
        $currentDateTime=$this->getCurrentDateAndTime();
        $currentDate = $request->selectedate;

        $UserPreferanceReminder = UserPreferanceReminder::where("user_id",$authUser->id)->where("type","event")->get();
        //Get event type 
        $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',$authUser->firm_name)->orderBy("status_order","ASC")->get();
        $fromPageRoute = $request->from_page_route ?? Null;
        return view('case.event.loadAddEvent',compact(/* 'CaseMasterClient', */'CaseMasterData','country','currentDateTime','eventLocation','allEventType','case_id','caseLeadList','lead_id','UserPreferanceReminder', 'currentDate', 'fromPageRoute'));          
     }
    
    /**
     * Save events with new logic
     */
    public function saveAddEventPage(Request $request)
    {
        // return $request->all();
        $validator = \Validator::make($request->all(), [
            'linked_staff_checked_share' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>['You must share with at least one firm user<br>You must share with at least one user'],]);
        }

        $authUser = auth()->user();

        //If new location is creating.
        if($request->location_name != ''){
            $locationID= $this->saveLocationOnce($request);
        } else {
            $locationID = $request->case_location_list;
        }

        //Single event
        if(!isset($request->recuring_event)){
            $startDate = strtotime(date("Y-m-d", strtotime($request->start_date)));
            $endDate = strtotime(date("Y-m-d",strtotime($request->end_date)));
        }else{
            //recurring event
            $startDate = strtotime(date("Y-m-d",  strtotime($request->start_date)));
            $endDate = strtotime(date("Y-m-d",  strtotime($request->end_date)));
            $recurringEndDate =  strtotime(date('Y-m-d',strtotime('+365 days')));
            if($request->end_on!=''){
                $recurringEndDate =  strtotime(date('Y-m-d',strtotime($request->end_on)));
            }
        }

        // Start-End time for all events convert into UTC
        $start_time = date("H:i:s", strtotime(convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->start_date.' '.$request->start_time)), $authUser->user_timezone)));
        $end_time = date("H:i:s", strtotime(convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->end_date.' '.$request->end_time)), $authUser->user_timezone)));

        if(!isset($request->recuring_event)) {
            $start_date = convertDateToUTCzone(date("Y-m-d", $startDate), $authUser->user_timezone);
            $end_date = convertDateToUTCzone(date("Y-m-d", $endDate), $authUser->user_timezone);

            $caseEvent = Event::create([
                "event_title" => $request->event_name,
                "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                "event_type_id" => $request->event_type ?? NULL,
                "start_date" => convertDateToUTCzone($start_date, $authUser->user_timezone),
                "end_date" => convertDateToUTCzone($end_date, $authUser->user_timezone),
                "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                "event_description" => $request->description,
                "is_recurring" => "no",
                "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                "is_event_read" => (isset($request->is_event_private)) ? 'yes' : 'no',
                "firm_id" => $authUser->firm_name,
                "created_by" => $authUser->id,
            ]);

            $eventRecurring = EventRecurring::create([
                "event_id" => $caseEvent->id,
                "start_date" => $start_date,
                "end_date" => $end_date,
                // "event_reminders" => $this->getEventReminderJson($caseEvent, $request),
                "event_linked_staff" => $this->getEventLinkedStaffJson($caseEvent, $request),
                "event_linked_contact_lead" => $this->getEventLinkedContactLeadJson($caseEvent, $request),
            ]);  

            if($request->reminder_user_type && count($request['reminder_user_type']) > 1) {
                EventUserReminder::create([
                    'user_id' => $authUser->id,
                    'event_id' => $caseEvent->id,
                    'event_recurring_id' => $eventRecurring->id,
                    'event_reminders' =>$this->getEventReminderJson($caseEvent, $request),
                    'created_by' => $authUser->id,
                ]);
            }

            $this->saveEventRecentActivity($request, $caseEvent->id, $eventRecurring->id, 'add');
        } else {  
            $start_date = convertDateToUTCzone(date("Y-m-d", $startDate), $authUser->user_timezone);
            $end_date = convertDateToUTCzone(date("Y-m-d", $endDate), $authUser->user_timezone);

            $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $recurringEndDate, $locationID);
        }
        session(['popup_success' => 'Event was added.']);
        return response()->json(['errors'=>''   ]);
    }

    /**
     * Update events with new logic
     */
    public function saveEditEventPage(Request $request)
    {
        // return $request->all();
        if(!isset($request->no_case_link)){
            $validator = \Validator::make($request->all(), [
                // 'linked_staff_checked_share' => 'required_if:share_checkbox_nonlinked,=,null'
                'linked_staff_checked_share' => (!isset($request->share_checkbox_nonlinked)) ? 'required' : 'nullable'
            ]);
            if($validator->fails())
            {
                return response()->json(['errors'=>['You must share with at least one firm user<br>You must share with at least one user'],]);
            }
        }
        $authUser = auth()->user();

        //If new location is creating.
        if($request->location_name!='' && !isset($request->case_location_list)){
            $locationID= $this->saveLocationOnce($request);
        } else {
            $locationID = $request->case_location_list;
        }

        //Single event
        if(!isset($request->recuring_event)) {
            $startDate = strtotime(date("Y-m-d", strtotime($request->start_date)));
            $endDate = strtotime(date("Y-m-d",strtotime($request->end_date)));
        }else{
            //recurring event
            $startDate = strtotime(date("Y-m-d",  strtotime($request->start_date)));
            $endDate = strtotime(date("Y-m-d",  strtotime($request->end_date)));
            $recurringEndDate =  strtotime(date('Y-m-d',strtotime('+365 days')));
            if($request->end_on!=''){
                $recurringEndDate =  strtotime(date('Y-m-d',strtotime($request->end_on)));
            }
        }

        // Start-End time for all events convert into UTC
        $start_time = date("H:i:s", strtotime(convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->start_date.' '.$request->start_time)), $authUser->user_timezone)));
        $end_time = date("H:i:s", strtotime(convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->end_date.' '.$request->end_time)), $authUser->user_timezone)));

        if($request->delete_event_type=='SINGLE_EVENT') {
            
            $start_date = convertDateToUTCzone(date("Y-m-d", $startDate), $authUser->user_timezone);
            $end_date = convertDateToUTCzone(date("Y-m-d", $endDate), $authUser->user_timezone);

            $caseEvent = Event::find($request->event_id);
            if($caseEvent && $caseEvent->is_recurring == 'no' && !isset($request->recuring_event)) {
                $caseEvent->fill([
                    "event_title" => $request->event_name,
                    "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                    "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                    "event_type_id" => $request->event_type ?? NULL,
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                    "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                    "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                    "event_description" => $request->description,
                    "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                    "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                    "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                    "firm_id" => $authUser->firm_name,
                    "updated_by" => $authUser->id,
                ])->save();
                $caseEvent->refresh();
                $recurringEvent = EventRecurring::whereId($request->event_recurring_id)->first();
                $recurringEvent->fill([
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    // 'event_reminders' => $this->getEventReminderJson($caseEvent, $request),
                    'event_linked_staff' => $this->getEventLinkedStaffJson($caseEvent, $request),
                    'event_linked_contact_lead' => $this->getEventLinkedContactLeadJson($caseEvent, $request),
                    'event_comments' => $this->getEditEventHistoryJson($caseEvent->id, $recurringEvent),
                ])->save();
                // Update user's event reminders
                if($request->is_reminder_updated == 'yes') {
                    $this->updateEventUserReminder($caseEvent, $recurringEvent, $request);
                }

                $this->saveEventRecentActivity($request, $caseEvent->id, @$recurringEvent->id);

            } else if($caseEvent && $caseEvent->is_recurring == 'yes' && !isset($request->recuring_event)) {
                $oldEventIds = Event::where("parent_event_id", $caseEvent->id)->orWhere("id", $caseEvent->id)->pluck('id')->toArray();
                EventRecurring::whereIn("event_id", $oldEventIds)->forceDelete();
                EventUserReminder::whereIn("event_id", $oldEventIds)->forceDelete();

                $caseEvent = Event::create([
                    "event_title" => $request->event_name,
                    "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                    "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                    "event_type_id" => $request->event_type ?? NULL,
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                    "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                    "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                    "event_description" => $request->description,
                    "is_recurring" => "no",
                    "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                    "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                    "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                    "firm_id" => $authUser->firm_name,
                    "created_by" => $authUser->id,
                ]);
              
                $recurringEvent = EventRecurring::create([
                    "event_id" => $caseEvent->id,
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    // 'event_reminders' => $this->getEventReminderJson($caseEvent, $request),
                    'event_linked_staff' => $this->getEventLinkedStaffJson($caseEvent, $request),
                    'event_linked_contact_lead' => $this->getEventLinkedContactLeadJson($caseEvent, $request),
                    'event_comments' => $this->getAddEventHistoryJson($caseEvent->id),
                    "created_by" => $authUser->id,
                ]);

                // Update user's event reminders
                if($request->is_reminder_updated == 'yes') {
                    $this->updateEventUserReminder($caseEvent, $recurringEvent, $request);
                }

                // Delete old events
                Event::whereIn("id", $oldEventIds)->forceDelete();

                $this->saveEventRecentActivity($request, $caseEvent->id, @$recurringEvent->id);

            } else if($caseEvent && $caseEvent->is_recurring == 'yes' && isset($request->recuring_event)) {
                if($caseEvent->edit_recurring_pattern == 'single event' && $caseEvent->parent_event_id != '') {
                    $caseEvent->fill([
                        "event_title" => $request->event_name,
                        "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                        "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                        "event_type_id" => $request->event_type ?? NULL,
                        "start_date" => $start_date,
                        "end_date" => $end_date,
                        "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                        "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                        "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                        "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                        "event_description" => $request->description,
                        "is_recurring" => "yes",
                        "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                        "event_recurring_type" => $request->event_frequency,
                        "event_interval_day" => $request->event_interval_day,
                        "event_interval_month" => $request->event_interval_month,
                        "event_interval_year" => $request->event_interval_year,
                        "monthly_frequency" => $request->monthly_frequency,
                        "yearly_frequency" => $request->yearly_frequency,
                        "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                        "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                        "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                        "firm_id" => $authUser->firm_name,
                        "updated_by" => $authUser->id,
                    ])->save();
                } else {
                    $caseEvent = Event::create([
                        "event_title" => $request->event_name,
                        "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                        "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                        "event_type_id" => $request->event_type ?? NULL,
                        "parent_event_id" => $request->event_id ?? NULL,
                        "start_date" => $start_date,
                        "end_date" => $end_date,
                        "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                        "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                        "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                        "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                        "event_description" => $request->description,
                        "is_recurring" => "yes",
                        "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                        "event_recurring_type" => $request->event_frequency,
                        "event_interval_day" => $request->event_interval_day,
                        "event_interval_month" => $request->event_interval_month,
                        "event_interval_year" => $request->event_interval_year,
                        "monthly_frequency" => $request->monthly_frequency,
                        "yearly_frequency" => $request->yearly_frequency,
                        "edit_recurring_pattern" => "single event",
                        "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                        "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                        "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                        "firm_id" => $authUser->firm_name,
                        "created_by" => $caseEvent->created_by,
                        "created_at" => $caseEvent->created_at,
                        "updated_by" => $authUser->id,
                        "updated_at" => Carbon::now()
                    ]);
                }
                $recurringEvent = EventRecurring::whereId($request->event_recurring_id)->first();
                $recurringEvent->fill([
                    'event_id' => $caseEvent->id, 
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    // 'event_reminders' => $this->getEventReminderJson($caseEvent, $request),
                    'event_linked_staff' => $this->getEventLinkedStaffJson($caseEvent, $request),
                    'event_linked_contact_lead' => $this->getEventLinkedContactLeadJson($caseEvent, $request),
                    'event_comments' => $this->getEditEventHistoryJson($caseEvent->id, $recurringEvent),
                ])->save();

                // Update user's event reminders
                if($request->is_reminder_updated == 'yes') {
                    $eventUserReminders = EventUserReminder::where('event_recurring_id', $request->event_recurring_id)->get();
                    Log::info("event reminders: ".$eventUserReminders);
                    foreach($eventUserReminders as $rkey => $ritem) {
                        if($ritem->user_id == auth()->id()) {
                            $this->updateEventUserReminder($caseEvent, $recurringEvent, $request);
                        } else {
                            $ritem->fill(['event_id' => $caseEvent->id])->save();
                        }
                    }
                }
                    
                $this->saveEventRecentActivity($request, $caseEvent->id, @$recurringEvent->id);

            } else if($caseEvent && $caseEvent->is_recurring == 'no' && isset($request->recuring_event)) {
                $caseEvent->fill([
                    "event_title" => $request->event_name,
                    "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                    "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                    "event_type_id" => $request->event_type ?? NULL,
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                    "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                    "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                    "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                    "event_description" => $request->description,
                    "is_recurring" => "yes",
                    "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                    "event_recurring_type" => $request->event_frequency,
                    "event_interval_day" => $request->event_interval_day,
                    "event_interval_month" => $request->event_interval_month,
                    "event_interval_year" => $request->event_interval_year,
                    "monthly_frequency" => $request->monthly_frequency,
                    "yearly_frequency" => $request->yearly_frequency,
                    "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                    "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                    "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                    "firm_id" => $authUser->firm_name,
                    "updated_by" => $authUser->id,
                ])->save();

                EventRecurring::where("event_id", $caseEvent->id)->forceDelete();
                if($request->event_frequency=='DAILY') {
                    $recurringEvent = $this->saveDailyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
                } else if($request->event_frequency == "EVERY_BUSINESS_DAY") {
                    $recurringEvent = $this->saveBusinessDayRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
                } else if($request->event_frequency == "WEEKLY") {
                    $recurringEvent = $this->saveWeeklyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
                } else if($request->event_frequency == "CUSTOM") {
                    $recurringEvent = $this->saveCustomRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
                } else if($request->event_frequency == "MONTHLY") {
                    $recurringEvent = $this->saveMonthlyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
                }
                // Commented. As per client's requirement
                /* else if($request->event_frequency == "YEARLY") {
                    $recurringEvent = $this->saveYearlyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
                } */
                $this->saveEventRecentActivity($request, $caseEvent->id, @$recurringEvent->id);
            }
        } elseif($request->delete_event_type=='THIS_AND_FOLLOWING_EVENTS') {
            $start_date = convertDateToUTCzone(date("Y-m-d", $startDate), $authUser->user_timezone);
            $end_date = convertDateToUTCzone(date("Y-m-d", $endDate), $authUser->user_timezone);
            $caseEvent = Event::find($request->event_id);
            if($caseEvent->event_recurring_type != $request->event_frequency) {
                $allEventIds = Event::where("parent_event_id", $request->event_id)->orWhere("id", $request->event_id)->pluck("id")->toArray();
                $belowRecurringEvent = EventRecurring::where("id", "<", $request->event_recurring_id)->whereIn("event_id", $allEventIds)->get();
                $remainEventIds = $belowRecurringEvent->pluck('event_id')->toArray();
                $events = Event::where("parent_event_id", $request->event_id)->whereNotIn("id", $remainEventIds);
                $lastRecurringEvent = EventRecurring::where("id", "<", $request->event_recurring_id)->whereIn("event_id", $remainEventIds)->orderBy("id", 'desc')->first();
                $lastEvent = Event::whereIn('id', $remainEventIds)/* ->whereNotIn('edit_recurring_pattern', ['single event']) */->orderBy("id", "desc")->first();
                $lastEvent->fill([
                    'end_on' => $lastRecurringEvent->start_date,
                    'is_no_end_date' => 'no',
                    'recurring_event_end_date' => $lastRecurringEvent->start_date,
                ])->save();
                EventUserReminder::whereIn("event_id", $events->pluck("id")->toArray())->orWhere("event_id", $request->event_id)->where("event_recurring_id", ">=", $request->event_recurring_id)->forceDelete();
                EventRecurring::whereIn("event_id", $events->pluck("id")->toArray())->orWhere("event_id", $request->event_id)->where("id", ">=", $request->event_recurring_id)->forceDelete();
                $events->forceDelete();

                // Create new events for new frequency
                $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $recurringEndDate, $locationID);
            } else {
                if($request->event_frequency == 'DAILY') { 
                    $oldEvent = Event::find($request->event_id);
                    $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
                    if($oldEvent->start_date != $eventRecurring->start_date) {
                        if($oldEvent) {
                            $oldEvent->fill([
                                'is_no_end_date' => 'no',
                                'end_on' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d'),
                                'event_recurring_end_date' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d')
                            ])->save();
                        }
                        $caseEvent = Event::create([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type ?? NULL,
                            "start_date" => $start_date,
                            "end_date" => $end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "parent_event_id" => $oldEvent->id,
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_day" => $request->event_interval_day,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "edit_recurring_pattern" => "following event",
                            "firm_id" => $authUser->firm_name,
                            "created_by" => $authUser->id,
                        ]);

                        // $eventReminders = $this->getEventReminderJson($caseEvent, $request);
                        $eventLinkedStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
                        $eventLinkedClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
                        if($oldEvent->event_interval_day != $request->event_interval_day) {
                            EventUserReminder::where("event_id", $oldEvent->id)->where("event_recurring_id", ">=", $request->event_recurring_id)->forceDelete();
                            EventRecurring::where("event_id", $oldEvent->id)->where("id", ">=", $request->event_recurring_id)->forceDelete();
                            $eventRecurring = $this->saveDailyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
                        } else {
                            $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_day.' days', date("Y-m-d", $recurringEndDate));
                            $days = $this->getDatesDiffDays($request);
                            foreach($period as $date) {
                                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date)->first();
                                if($eventRecurring) {
                                    $eventRecurring->fill([
                                        "event_id" => $caseEvent->id,
                                        "start_date" => $date,
                                        "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date,
                                        // "event_reminders" => $eventReminders,
                                        "event_linked_staff" => $eventLinkedStaff,
                                        "event_linked_contact_lead" => $eventLinkedClient,
                                        'event_comments' => $this->getEditEventHistoryJson($caseEvent->id, $eventRecurring),
                                    ])->save();

                                    // Update user's event reminders
                                    if($request->is_reminder_updated == 'yes') {
                                        $this->updateEventUserReminder($caseEvent, $eventRecurring, $request);
                                    }
                                }
                            }
                        }                    
                    } else {
                        $oldEvent->fill([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type ?? NULL,
                            "start_date" => (isset($request->updated_start_date)) ? $start_date : $oldEvent->start_date,
                            "end_date" => (isset($request->updated_end_date)) ? $end_date : $oldEvent->end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "parent_event_id" => $oldEvent->id,
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_day" => $request->event_interval_day,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "edit_recurring_pattern" => "following event",
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();
                        
                        if($oldEvent->event_interval_day != $request->event_interval_day) {
                            EventUserReminder::where("event_id", $oldEvent->id)->where("event_recurring_id", ">=", $request->event_recurring_id)->forceDelete();
                            EventRecurring::where("event_id", $oldEvent->id)->where("id", ">=", $request->event_recurring_id)->forceDelete();
                            $eventRecurring = $this->saveDailyRecurringEvent($oldEvent, $start_date, $request, $recurringEndDate);
                        } else {
                            // $eventReminders = $this->getEventReminderJson($oldEvent, $request);
                            $eventLinkedStaff = $this->getEventLinkedStaffJson($oldEvent, $request);
                            $eventLinkedClient = $this->getEventLinkedContactLeadJson($oldEvent, $request);
                            $period = \Carbon\CarbonPeriod::create($start_date, date("Y-m-d", $recurringEndDate));
                            $days = $this->getDatesDiffDays($request);
                            foreach($period as $date) {
                                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date)->first();
                                if($eventRecurring) {
                                    $eventRecurring->fill([
                                        "event_id" => $oldEvent->id,
                                        "start_date" => $date,
                                        "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date,
                                        // "event_reminders" => $eventReminders,
                                        "event_linked_staff" => $eventLinkedStaff,
                                        "event_linked_contact_lead" => $eventLinkedClient,
                                        'event_comments' => $this->getEditEventHistoryJson($oldEvent->id, $eventRecurring),
                                    ])->save();

                                    // Update user's event reminders
                                    if($request->is_reminder_updated == 'yes') {
                                        $this->updateEventUserReminder($oldEvent, $eventRecurring, $request);
                                    }
                                }
                            }
                        }
                    }
                    $this->saveEventRecentActivity($request, $oldEvent->id, @$eventRecurring->id);
                } else if($request->event_frequency == 'EVERY_BUSINESS_DAY') { 
                    $oldEvent = Event::find($request->event_id);
                    $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
                    if($oldEvent->start_date != $eventRecurring->start_date) {
                        if($oldEvent) {
                            $oldEvent->fill([
                                'is_no_end_date' => 'no',
                                'end_on' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d'),
                                'event_recurring_end_date' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d')
                            ])->save();
                        }
                        $caseEvent = Event::create([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type ?? NULL,
                            "start_date" => $start_date,
                            "end_date" => $end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "parent_event_id" => $oldEvent->id,
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_day" => $request->event_interval_day,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "edit_recurring_pattern" => "following event",
                            "firm_id" => $authUser->firm_name,
                            "created_by" => $authUser->id,
                        ]);

                        // $eventReminders = $this->getEventReminderJson($caseEvent, $request);
                        $eventLinkedStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
                        $eventLinkedClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
                        $period = \Carbon\CarbonPeriod::create($start_date, date("Y-m-d", $recurringEndDate));
                        $days = $this->getDatesDiffDays($request);
                        foreach($period as $date) {       
                            if (!in_array($date->format('l'), ["Saturday","Sunday"])) {
                                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date)->first();
                                if($eventRecurring) {
                                    $eventRecurring->fill([
                                        "event_id" => $caseEvent->id,
                                        "start_date" => $date,
                                        "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date,
                                        // "event_reminders" => $eventReminders,
                                        "event_linked_staff" => $eventLinkedStaff,
                                        "event_linked_contact_lead" => $eventLinkedClient,
                                        'event_comments' => $this->getEditEventHistoryJson($caseEvent->id, $eventRecurring),
                                    ])->save();
                                    // Update user's event reminders
                                    if($request->is_reminder_updated == 'yes') {
                                        $this->updateEventUserReminder($caseEvent, $eventRecurring, $request);
                                    }
                                }
                            }
                        }
                    } else {
                        $oldEvent->fill([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type ?? NULL,
                            "start_date" => (isset($request->updated_start_date)) ? $start_date : $oldEvent->start_date,
                            "end_date" => (isset($request->updated_end_date)) ? $end_date : $oldEvent->end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "parent_event_id" => $oldEvent->id,
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_day" => $request->event_interval_day,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "edit_recurring_pattern" => "following event",
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();
                        
                        // $eventReminders = $this->getEventReminderJson($oldEvent, $request);
                        $eventLinkedStaff = $this->getEventLinkedStaffJson($oldEvent, $request);
                        $eventLinkedClient = $this->getEventLinkedContactLeadJson($oldEvent, $request);
                        $period = \Carbon\CarbonPeriod::create($start_date, date("Y-m-d", $recurringEndDate));
                        $days = $this->getDatesDiffDays($request);
                        foreach($period as $date) {       
                            if (!in_array($date->format('l'), ["Saturday","Sunday"])) {
                                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date)->first();
                                if($eventRecurring) {
                                    $eventRecurring->fill([
                                        "event_id" => $oldEvent->id,
                                        "start_date" => $date,
                                        "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date,
                                        // "event_reminders" => $eventReminders,
                                        "event_linked_staff" => $eventLinkedStaff,
                                        "event_linked_contact_lead" => $eventLinkedClient,
                                        'event_comments' => $this->getEditEventHistoryJson($oldEvent->id, $eventRecurring),
                                    ])->save();

                                    // Update user's event reminders
                                    if($request->is_reminder_updated == 'yes') {
                                        $this->updateEventUserReminder($oldEvent, $eventRecurring, $request);
                                    }
                                }
                            }
                        }
                    }
                    $this->saveEventRecentActivity($request, $oldEvent->id, @$eventRecurring->id);
                } else if($request->event_frequency == 'CUSTOM') {
                    $oldEvent = Event::find($request->event_id);
                    $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
                    if($oldEvent->start_date != $eventRecurring->start_date) {
                        if($oldEvent) {
                            $oldEvent->fill([
                                'is_no_end_date' => 'no',
                                'end_on' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d'),
                                'event_recurring_end_date' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d')
                            ])->save();
                        }
                        $caseEvent = Event::create([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type ?? NULL,
                            "start_date" => $start_date,
                            "end_date" => $end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "parent_event_id" => $oldEvent->id,
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "custom_event_weekdays" => $request->custom,
                            "event_interval_week" => $request->daily_weekname,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "edit_recurring_pattern" => "following event",
                            "firm_id" => $authUser->firm_name,
                            "created_by" => $authUser->id,
                        ]);

                        if(array_diff( $request->custom, $oldEvent->custom_event_weekdays ) ) {
                            EventUserReminder::where("event_id", $oldEvent->id)->where("event_recurring_id", ">=", $request->event_recurring_id)->forceDelete();
                            EventRecurring::where("event_id", $oldEvent->id)->where("id", ">=", $request->event_recurring_id)->forceDelete();
                            $eventRecurring = $this->saveCustomRecurringEvent($caseEvent, $caseEvent->start_date, $request, $recurringEndDate);
                        } else {
                            // $eventReminders = $this->getEventReminderJson($caseEvent, $request);
                            $eventLinkedStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
                            $eventLinkedClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
                            $days = $this->getDatesDiffDays($request);

                            $start = new DateTime($start_date);
                            $startClone = new DateTime($start_date);
                            if(isset($request->end_on)) {
                                $recurringEndDate=new DateTime($request->end_on);
                            }else{
                                $recurringEndDate=$startClone->add(new DateInterval('P365D'));
                            }
                            $interval = new DateInterval('P1D');
                            $period = new DatePeriod($start, $interval, $recurringEndDate);
                            $weekInterval = $request->daily_weekname;
                            $fakeWeek = 0;
                            $currentWeek = $start->format('W');
                            
                            foreach ($period as $date) {
                                if ($date->format('W') !== $currentWeek) {
                                    $currentWeek = $date->format('W');
                                    $fakeWeek++;
                                }
                                if ($fakeWeek % $weekInterval !== 0) {
                                    continue;
                                }
                                $dayOfWeek = $date->format('l');
                                if(in_array($dayOfWeek, $request->custom)) {   
                                    $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date)->first();
                                    if($eventRecurring) {
                                        $eventRecurring->fill([
                                            "event_id" => $caseEvent->id,
                                            "start_date" => $date->format('Y-m-d'),
                                            "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date->format('Y-m-d'),
                                            // "event_reminders" => $eventReminders,
                                            "event_linked_staff" => $eventLinkedStaff,
                                            "event_linked_contact_lead" => $eventLinkedClient,
                                            'event_comments' => $this->getEditEventHistoryJson($caseEvent->id, $eventRecurring),
                                        ])->save();
                                        // Update user's event reminders
                                        if($request->is_reminder_updated == 'yes') {
                                            $this->updateEventUserReminder($caseEvent, $eventRecurring, $request);
                                        }
                                    }
                                }
                            }
                        }  
                    } else {
                        $oldEvent->fill([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type,
                            "start_date" => (isset($request->updated_start_date)) ? $start_date : $oldEvent->start_date,
                            "end_date" => (isset($request->updated_end_date)) ? $end_date : $oldEvent->end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : Null,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : Null,
                            "recurring_event_end_date" => ($request->end_on) ? convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone) : $eitem->recurring_event_end_date,
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "custom_event_weekdays" => $request->custom,
                            "event_interval_week" => $request->daily_weekname,
                            "edit_recurring_pattern" => "all events",
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : Null,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();

                        if(array_diff( $request->custom, $oldEvent->custom_event_weekdays ) ) {
                            EventUserReminder::where("event_id", $oldEvent->id)->where("event_recurring_id", ">=", $request->event_recurring_id)->forceDelete();
                            EventRecurring::where("event_id", $oldEvent->id)->where("id", ">=", $request->event_recurring_id)->forceDelete();
                            $eventRecurring = $this->saveCustomRecurringEvent($oldEvent, $oldEvent->start_date, $request, $recurringEndDate);
                        } else {
                            // $eventReminders = $this->getEventReminderJson($oldEvent, $request);
                            $eventLinkedStaff = $this->getEventLinkedStaffJson($oldEvent, $request);
                            $eventLinkedClient = $this->getEventLinkedContactLeadJson($oldEvent, $request);
                            $days = $this->getDatesDiffDays($request);

                            $start = new DateTime($start_date);
                            $startClone = new DateTime($start_date);
                            if(isset($request->end_on)) {
                                $recurringEndDate=new DateTime($request->end_on);
                            }else{
                                $recurringEndDate=$startClone->add(new DateInterval('P365D'));
                            }
                            $interval = new DateInterval('P1D');
                            $period = new DatePeriod($start, $interval, $recurringEndDate);
                            $weekInterval = $request->daily_weekname;
                            $fakeWeek = 0;
                            $currentWeek = $start->format('W');
                            
                            foreach ($period as $date) {
                                if ($date->format('W') !== $currentWeek) {
                                    $currentWeek = $date->format('W');
                                    $fakeWeek++;
                                }
                                if ($fakeWeek % $weekInterval !== 0) {
                                    continue;
                                }
                                $dayOfWeek = $date->format('l');
                                if(in_array($dayOfWeek, $request->custom)) {   
                                    $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date)->first();
                                    if($eventRecurring) {
                                        $eventRecurring->fill([
                                            "event_id" => $oldEvent->id,
                                            "start_date" => $date->format('Y-m-d'),
                                            "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date->format('Y-m-d'),
                                            // "event_reminders" => $eventReminders,
                                            "event_linked_staff" => $eventLinkedStaff,
                                            "event_linked_contact_lead" => $eventLinkedClient,
                                            'event_comments' => $this->getEditEventHistoryJson($oldEvent->id, $eventRecurring),
                                        ])->save();

                                        // Update user's event reminders
                                        if($request->is_reminder_updated == 'yes') {
                                            $this->updateEventUserReminder($oldEvent, $eventRecurring, $request);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $this->saveEventRecentActivity($request, $oldEvent->id, @$eventRecurring->id);
                } else if($request->event_frequency == 'WEEKLY') { 
                    $oldEvent = Event::find($request->event_id);
                    $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
                    if($oldEvent->start_date != $eventRecurring->start_date) {
                        if($oldEvent) {
                            $oldEvent->fill([
                                'is_no_end_date' => 'no',
                                'end_on' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d'),
                                'event_recurring_end_date' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d')
                            ])->save();
                        }
                        $caseEvent = Event::create([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type ?? NULL,
                            "start_date" => $start_date,
                            "end_date" => $end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "parent_event_id" => $oldEvent->id,
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "edit_recurring_pattern" => "following event",
                            "firm_id" => $authUser->firm_name,
                            "created_by" => $authUser->id,
                        ]);

                        if(isset($request->updated_start_date)) {
                            EventUserReminder::where("event_id", $oldEvent->id)->where("event_recurring_id", ">=", $request->event_recurring_id)->forceDelete();
                            EventRecurring::where("event_id", $oldEvent->id)->where("id", ">=", $request->event_recurring_id)->forceDelete();
                            $eventRecurring = $this->saveWeeklyRecurringEvent($caseEvent, $caseEvent->start_date, $request, $recurringEndDate);
                        } else {
                            // $eventReminders = $this->getEventReminderJson($caseEvent, $request);
                            $eventLinkedStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
                            $eventLinkedClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
                            $period = \Carbon\CarbonPeriod::create($start_date, '7 days', date("Y-m-d", $recurringEndDate));
                            $days = $this->getDatesDiffDays($request);
                            foreach($period as $date) {   
                                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date)->first();
                                if($eventRecurring) {
                                    $eventRecurring->fill([
                                        "event_id" => $caseEvent->id,
                                        "start_date" => $date,
                                        "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date,
                                        // "event_reminders" => $eventReminders,
                                        "event_linked_staff" => $eventLinkedStaff,
                                        "event_linked_contact_lead" => $eventLinkedClient,
                                        'event_comments' => $this->getEditEventHistoryJson($caseEvent->id, $eventRecurring),
                                    ])->save();

                                    // Update user's event reminders
                                    if($request->is_reminder_updated == 'yes') {
                                        $this->updateEventUserReminder($caseEvent, $eventRecurring, $request);
                                    }
                                }
                            }
                        }
                    } else {
                        $oldEvent->fill([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type ?? NULL,
                            "start_date" => (isset($request->updated_start_date)) ? $start_date : $oldEvent->start_date,
                            "end_date" => (isset($request->updated_end_date)) ? $end_date : $oldEvent->end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "parent_event_id" => $oldEvent->id,
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "edit_recurring_pattern" => "following event",
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();
                        if(isset($request->updated_start_date)) {
                            EventUserReminder::where("event_id", $oldEvent->id)->where("event_rcurrieng_id", ">=", $request->event_recurring_id)->forceDelete();
                            EventRecurring::where("event_id", $oldEvent->id)->where("id", ">=", $request->event_recurring_id)->forceDelete();
                            $eventRecurring = $this->saveWeeklyRecurringEvent($oldEvent, $oldEvent->start_date, $request, $recurringEndDate);
                        } else {
                            // $eventReminders = $this->getEventReminderJson($oldEvent, $request);
                            $eventLinkedStaff = $this->getEventLinkedStaffJson($oldEvent, $request);
                            $eventLinkedClient = $this->getEventLinkedContactLeadJson($oldEvent, $request);
                            $period = \Carbon\CarbonPeriod::create($start_date, '7 days', date("Y-m-d", $recurringEndDate));
                            $days = $this->getDatesDiffDays($request);
                            foreach($period as $date) {       
                                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date)->first();
                                if($eventRecurring) {
                                    $eventRecurring->fill([
                                        "event_id" => $oldEvent->id,
                                        "start_date" => $date,
                                        "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date,
                                        // "event_reminders" => $eventReminders,
                                        "event_linked_staff" => $eventLinkedStaff,
                                        "event_linked_contact_lead" => $eventLinkedClient,
                                        'event_comments' => $this->getEditEventHistoryJson($oldEvent->id, $eventRecurring),
                                    ])->save();

                                    // Update user's event reminders
                                    if($request->is_reminder_updated == 'yes') {
                                        $this->updateEventUserReminder($oldEvent, $eventRecurring, $request);
                                    }
                                }
                            }
                        }
                    }
                    $this->saveEventRecentActivity($request, $oldEvent->id, @$eventRecurring->id);
                } else if($request->event_frequency == 'MONTHLY') {
                    $oldEvent = Event::find($request->event_id);
                    $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
                    if($oldEvent->start_date != $eventRecurring->start_date) {
                        if($oldEvent) {
                            $oldEvent->fill([
                                'is_no_end_date' => 'no',
                                'end_on' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d'),
                                'event_recurring_end_date' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d')
                            ])->save();
                        }
                        $caseEvent = Event::create([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type ?? NULL,
                            "start_date" => $start_date,
                            "end_date" => $end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "parent_event_id" => $oldEvent->id,
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_month" => $request->event_interval_month,
                            "monthly_frequency" => $request->monthly_frequency,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "edit_recurring_pattern" => "following event",
                            "firm_id" => $authUser->firm_name,
                            "created_by" => $authUser->id,
                        ]);

                        if($oldEvent->event_interval_month != $request->event_interval_month || $request->monthly_frequency != $oldEvent->monthly_frequency) {
                            EventRecurring::where("event_id", $oldEvent->id)->where("id", ">=", $request->event_recurring_id)->forceDelete();
                            $eventRecurring = $this->saveMonthlyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
                        } else {    
                            // $eventReminders = $this->getEventReminderJson($caseEvent, $request);
                            $eventLinkedStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
                            $eventLinkedClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
                            $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_month.' months', date("Y-m-d", $recurringEndDate));
                            $days = $this->getDatesDiffDays($request);
                            foreach($period as $date) {       
                                $currentWeekDay = strtolower(date('l', strtotime($request->start_date))); 
                                if($request->monthly_frequency == 'MONTHLY_ON_DAY'){
                                    $date1 = strtotime($date);
                                } else if($request->monthly_frequency == 'MONTHLY_ON_THE') {
                                    $nthDay = ceil(date('j', strtotime($request->start_date)) / 7);
                                    $nthText = getWeekNthDay($nthDay);
                                    $date1 = strtotime($nthText." ". $currentWeekDay ." of this month", strtotime($date));
                                }else if($request->monthly_frequency=='MONTHLY_ON_THE_LAST'){
                                    $date1 = strtotime("last ". $currentWeekDay ." of this month", strtotime($date));
                                } else { 
                                    $date1 = strtotime($date);
                                }
                                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date1)->first();
                                if($eventRecurring) {
                                    $eventRecurring->fill([
                                        "event_id" => $caseEvent->id,
                                        "start_date" => date('Y-m-d', $date1),
                                        "end_date" => ($days > 0) ? Carbon::parse($date1)->addDays($days)->format('Y-m-d') : date('Y-m-d', $date1),
                                        // "event_reminders" => $eventReminders,
                                        "event_linked_staff" => $eventLinkedStaff,
                                        "event_linked_contact_lead" => $eventLinkedClient,
                                        'event_comments' => $this->getEditEventHistoryJson($caseEvent->id, $eventRecurring),
                                    ])->save();
                                    // Update user's event reminders
                                    if($request->is_reminder_updated == 'yes') {
                                        $this->updateEventUserReminder($caseEvent, $eventRecurring, $request);
                                    }
                                }
                            }
                        }                    
                    } else {
                        $oldEvent->fill([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type ?? NULL,
                            "start_date" => (isset($request->updated_start_date)) ? $start_date : $oldEvent->start_date,
                            "end_date" => (isset($request->updated_end_date)) ? $end_date : $oldEvent->end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "parent_event_id" => $oldEvent->id,
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_month" => $request->event_interval_month,
                            "monthly_frequency" => $request->monthly_frequency,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "edit_recurring_pattern" => "following event",
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();
                        
                        if($oldEvent->event_interval_month != $request->event_interval_month) {
                            EventUserReminder::where("event_id", $oldEvent->id)->where("event_recurring_id", ">=", $request->event_recurring_id)->forceDelete();
                            EventRecurring::where("event_id", $oldEvent->id)->where("id", ">=", $request->event_recurring_id)->forceDelete();
                            $eventRecurring = $this->saveMonthlyRecurringEvent($oldEvent, $start_date, $request, $recurringEndDate);
                        } else {
                            // $eventReminders = $this->getEventReminderJson($oldEvent, $request);
                            $eventLinkedStaff = $this->getEventLinkedStaffJson($oldEvent, $request);
                            $eventLinkedClient = $this->getEventLinkedContactLeadJson($oldEvent, $request);
                            $period = \Carbon\CarbonPeriod::create($start_date, date("Y-m-d", $recurringEndDate));
                            $days = $this->getDatesDiffDays($request);
                            foreach($period as $date) {
                                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date)->first();
                                if($eventRecurring) {
                                    $eventRecurring->fill([
                                        "event_id" => $oldEvent->id,
                                        "start_date" => $date,
                                        "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date,
                                        // "event_reminders" => $eventReminders,
                                        "event_linked_staff" => $eventLinkedStaff,
                                        "event_linked_contact_lead" => $eventLinkedClient,
                                        'event_comments' => $this->getEditEventHistoryJson($oldEvent->id, $eventRecurring),
                                    ])->save();
                                    // Update user's event reminders
                                    if($request->is_reminder_updated == 'yes') {
                                        $this->updateEventUserReminder($oldEvent, $eventRecurring, $request);
                                    }
                                }
                            }
                        }
                    }
                    $this->saveEventRecentActivity($request, $oldEvent->id, @$eventRecurring->id);
                } 
                // Commented. As per client's requirement
                /* else if($request->event_frequency == 'YEARLY') {
                    $oldEvent = Event::find($request->event_id);
                    $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
                    if($oldEvent->start_date != $eventRecurring->start_date) {
                        if($oldEvent) {
                            $oldEvent->fill([
                                'is_no_end_date' => 'no',
                                'end_on' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d'),
                                'event_recurring_end_date' => Carbon::parse($start_date)->subDays(1)->format('Y-m-d')
                            ])->save();
                        }
                        $caseEvent = Event::create([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type ?? NULL,
                            "start_date" => $start_date,
                            "end_date" => $end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "parent_event_id" => $oldEvent->id,
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_year" => $request->event_interval_year,
                            "yearly_frequency" => $request->yearly_frequency,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "edit_recurring_pattern" => "following event",
                            "firm_id" => $authUser->firm_name,
                            "created_by" => $authUser->id,
                        ]);

                        if($oldEvent->event_interval_year != $request->event_interval_year || $request->yearly_frequency != $oldEvent->yearly_frequency) {
                            EventRecurring::where("event_id", $oldEvent->id)->where("id", ">=", $request->event_recurring_id)->forceDelete();
                            $eventRecurring = $this->saveYearlyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
                        } else {    
                            $eventReminders = $this->getEventReminderJson($caseEvent, $request);
                            $eventLinkedStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
                            $eventLinkedClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
                            $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_year.' years', date("Y-m-d", $recurringEndDate));
                            $days = $this->getDatesDiffDays($request);
                            foreach($period as $date) {       
                                $currentWeekDay = strtolower(date('l', strtotime($request->start_date))); 
                                if($request->monthly_frequency == 'YEARLY_ON_DAY') {
                                    $date1 = strtotime($date);
                                } else if($request->yearly_frequency == 'YEARLY_ON_THE') {
                                    $nthDay = ceil(date('j', strtotime($date)) / 7);
                                    $nthText = getWeekNthDay($nthDay);
                                    $date1 = strtotime($nthText." ". $currentWeekDay ." of this month", strtotime($date));
                                } else if($request->yearly_frequency == 'YEARLY_ON_THE_LAST') {
                                    $date1 = strtotime("last ". $currentWeekDay ." of this month", strtotime($date));
                                } else { 
                                    $date1 = strtotime($date);
                                }
                                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date1)->first();
                                if($eventRecurring) {
                                    $eventRecurring->fill([
                                        "event_id" => $caseEvent->id,
                                        "start_date" => date('Y-m-d', $date1),
                                        "end_date" => ($days > 0) ? Carbon::parse($date1)->addDays($days)->format('Y-m-d') : date('Y-m-d', $date1),
                                        "event_reminders" => $eventReminders,
                                        "event_linked_staff" => $eventLinkedStaff,
                                        "event_linked_contact_lead" => $eventLinkedClient,
                                        'event_comments' => $this->getEditEventHistoryJson($caseEvent->id, $eventRecurring),
                                    ])->save();
                                }
                            }
                        }                    
                    } else {
                        $oldEvent->fill([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type ?? NULL,
                            "start_date" => (isset($request->updated_start_date)) ? $start_date : $oldEvent->start_date,
                            "end_date" => (isset($request->updated_end_date)) ? $end_date : $oldEvent->end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "parent_event_id" => $oldEvent->id,
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_year" => $request->event_interval_year,
                            "yearly_frequency" => $request->yearly_frequency,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "edit_recurring_pattern" => "following event",
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();
                        
                        if($oldEvent->event_interval_year != $request->event_interval_year) {
                            EventRecurring::where("event_id", $oldEvent->id)->where("id", ">=", $request->event_recurring_id)->forceDelete();
                            $eventRecurring = $this->saveYearlyRecurringEvent($oldEvent, $start_date, $request, $recurringEndDate);
                        } else {
                            $eventReminders = $this->getEventReminderJson($oldEvent, $request);
                            $eventLinkedStaff = $this->getEventLinkedStaffJson($oldEvent, $request);
                            $eventLinkedClient = $this->getEventLinkedContactLeadJson($oldEvent, $request);
                            $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_year.' years', date("Y-m-d", $recurringEndDate));
                            $days = $this->getDatesDiffDays($request);
                            foreach($period as $date) {       
                                $currentWeekDay = strtolower(date('l', strtotime($request->start_date))); 
                                if($request->monthly_frequency == 'YEARLY_ON_DAY') {
                                    $date1 = strtotime($date);
                                } else if($request->yearly_frequency == 'YEARLY_ON_THE') {
                                    $nthDay = ceil(date('j', strtotime($date)) / 7);
                                    $nthText = getWeekNthDay($nthDay);
                                    $date1 = strtotime($nthText." ". $currentWeekDay ." of this month", strtotime($date));
                                } else if($request->yearly_frequency == 'YEARLY_ON_THE_LAST') {
                                    $date1 = strtotime("last ". $currentWeekDay ." of this month", strtotime($date));
                                } else { 
                                    $date1 = strtotime($date);
                                }
                                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $date1)->first();
                                if($eventRecurring) {
                                    $eventRecurring->fill([
                                        "event_id" => $oldEvent->id,
                                        "start_date" => date('Y-m-d', $date1),
                                        "end_date" => ($days > 0) ? Carbon::parse($date1)->addDays($days)->format('Y-m-d') : date('Y-m-d', $date1),
                                        "event_reminders" => $eventReminders,
                                        "event_linked_staff" => $eventLinkedStaff,
                                        "event_linked_contact_lead" => $eventLinkedClient,
                                        'event_comments' => $this->getEditEventHistoryJson($oldEvent->id, $eventRecurring),
                                    ])->save();
                                }
                            }
                        }
                    }
                    $this->saveEventRecentActivity($request, $oldEvent->id, @$eventRecurring->id);
                } */
            }
        } elseif($request->delete_event_type=='ALL_EVENTS') {
            $start_date = convertDateToUTCzone(date("Y-m-d", $startDate), $authUser->user_timezone);
            $end_date = convertDateToUTCzone(date("Y-m-d", $endDate), $authUser->user_timezone);

            $caseEvent = Event::find($request->event_id);
            if($caseEvent->event_recurring_type != $request->event_frequency) {
                $oldEventIds = Event::where("parent_event_id", $caseEvent->id)->orWhere("id", $caseEvent->id)->pluck('id')->toArray();
                EventUserReminder::whereIn('event_id', $oldEventIds)->forceDelete();
                EventRecurring::whereIn("event_id", $oldEventIds)->forceDelete();
                Event::whereIn("id", $oldEventIds)->forceDelete();

                // Create new events for new frequency
                $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $recurringEndDate, $locationID);
            } else {
                $isNoEndDate = (isset($request->no_end_date_checkbox)) ? "yes" : "no";
                if($request->event_frequency=='DAILY') {
                    $oldEvents = Event::whereId($request->event_id)->orWhere("parent_event_id", $request->event_id)->where("edit_recurring_pattern", "!=", "single event")->get();
                    foreach($oldEvents as $ekey => $eitem) {
                        if($eitem->event_interval_day != $request->event_interval_day || $eitem->is_no_end_date != $isNoEndDate || isset($request->updated_start_date)) {
                            EventUserReminder::where('event_id', $eitem->id)->forceDelete();
                            EventRecurring::where("event_id", $eitem->id)->forceDelete();
                            $eventRecurring = $this->saveDailyRecurringEvent($eitem, $eitem->start_date, $request, $recurringEndDate);
                        } else {
                            // $eventReminders = $this->getEventReminderJson($eitem, $request);
                            $eventLinkStaff = $this->getEventLinkedStaffJson($eitem, $request);
                            $eventLinkClient = $this->getEventLinkedContactLeadJson($eitem, $request);
                            $recurringEvents = EventRecurring::where("event_id", $eitem->id)->get();
                            $days = $this->getDatesDiffDays($request);
                            foreach($recurringEvents as $rkey => $ritem) {
                                $ritem->fill([
                                    "end_date" => ($days > 0) ? Carbon::parse($ritem->start_date)->addDays($days)->format('Y-m-d') : $ritem->end_date,
                                    // 'event_reminders' => $eventReminders,
                                    'event_linked_staff' => $eventLinkStaff,
                                    'event_linked_contact_lead' => $eventLinkClient,
                                    'event_comments' => $this->getEditEventHistoryJson($eitem->id, $ritem),
                                ])->save();

                                // Update user's event reminders
                                if($request->is_reminder_updated == 'yes') {
                                    $this->updateEventUserReminder($eitem, $ritem, $request);
                                }
                            }
                        }

                        $eitem->fill([
                            "event_title" => ($request->updated_event_name) ? $request->updated_event_name : $eitem->event_title,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : $eitem->case_id,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : $eitem->lead_id,
                            "event_type_id" => ($request->updated_event_type) ? $request->updated_event_type : $eitem->event_type_id,
                            "start_date" => ($request->updated_start_date) ? $start_date : $eitem->start_date,
                            "end_date" => ($request->updated_end_date) ? $end_date : $eitem->end_date,
                            "start_time" => ($request->updated_start_time && !isset($request->all_day)) ? $start_time : $eitem->start_time,
                            "end_time" => ($request->updated_end_time && !isset($request->all_day)) ? $end_time : $eitem->end_time,
                            "recurring_event_end_date" => ($request->updated_end_on) ? convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone) : $eitem->recurring_event_end_date,
                            "is_full_day" => (isset($request->updated_all_day)) ? "yes" : $eitem->is_full_day,
                            "event_description" => ($eitem->description != '') ? $eitem->description : $request->description,
                            "is_recurring" => "yes",
                            "event_location_id" => ($request->updated_case_location_list) ? $request->updated_case_location_list : $eitem->event_location_id,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_day" => ($request->updated_event_interval_day) ? $request->updated_event_interval_day : $eitem->event_interval_day,
                            "is_no_end_date" => (isset($request->updated_no_end_date_checkbox)) ? "yes" : $eitem->is_no_end_date,
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->updated_end_on) ? date("Y-m-d",strtotime($request->updated_end_on)) : $eitem->end_on,
                            "is_event_private" => (isset($request->updated_is_event_private)) ? 'yes' : $eitem->is_event_private,
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();
                    }
                    $this->saveEventRecentActivity($request, $request->event_id, $eventRecurring->id ?? $request->event_recurring_id);

                } else if($request->event_frequency == 'EVERY_BUSINESS_DAY') {
                    $oldEvents = Event::whereId($request->event_id)->orWhere("parent_event_id", $request->event_id)->where("edit_recurring_pattern", "!=", "single event")->get();
                    foreach($oldEvents as $ekey => $eitem) {
                        if($eitem->is_no_end_date != $isNoEndDate || isset($request->updated_start_date)) {
                            EventUserReminder::where('event_id', $eitem->id)->forceDelete();
                            EventRecurring::where("event_id", $eitem->id)->forceDelete();
                            $eventRecurring = $this->saveBusinessDayRecurringEvent($eitem, $eitem->start_date, $request, $recurringEndDate);
                        } else {
                            $recurringEvents = EventRecurring::where("event_id", $eitem->id)->get();
                            // $eventReminders = $this->getEventReminderJson($eitem, $request);
                            $eventLinkStaff = $this->geteventLinkedStaffJson($eitem, $request);
                            $eventLinkClient = $this->getEventLinkedContactLeadJson($eitem, $request);
                            $days = $this->getDatesDiffDays($request);
                            foreach($recurringEvents as $rkey => $ritem) {
                                $ritem->fill([
                                    "end_date" => ($days > 0) ? Carbon::parse($ritem->start_date)->addDays($days)->format('Y-m-d') : $ritem->end_date,
                                    // 'event_reminders' => $eventReminders,
                                    'event_linked_staff' => $eventLinkStaff,
                                    'event_linked_contact_lead' => $eventLinkClient,
                                    'event_comments' => $this->getEditEventHistoryJson($eitem->id, $ritem),
                                ])->save();

                                // Update user's event reminders
                                if($request->is_reminder_updated == 'yes') {
                                    $this->updateEventUserReminder($eitem, $ritem, $request);
                                }
                            }
                        }

                        $eitem->fill([
                            "event_title" => ($request->updated_event_name) ? $request->event_name : $eitem->event_title,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : $eitem->case_id,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : $eitem->lead_id,
                            "event_type_id" => ($request->event_type) ? $request->event_type : $eitem->event_type_id,
                            "start_date" => ($request->start_date) ? $start_date : $eitem->start_date,
                            "end_date" => ($request->end_date) ? $end_date : $eitem->end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : $eitem->start_time,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : $eitem->end_time,
                            "recurring_event_end_date" => ($request->end_on) ? convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone) : $eitem->recurring_event_end_date,
                            "is_full_day" => (isset($request->all_day)) ? "yes" : $eitem->is_full_day,
                            "event_description" => ($request->description) ? $request->description : $eitem->description,
                            "is_recurring" => "yes",
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_day" => ($request->event_interval_day) ? $request->event_interval_day : $eitem->event_interval_day,
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : $eitem->end_on,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : $eitem->is_event_private,
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();
                    }
                    $this->saveEventRecentActivity($request, $request->event_id, $request->event_recurring_id);
                } else if($request->event_frequency == 'CUSTOM') {
                    $oldEvents = Event::whereId($request->event_id)->orWhere("parent_event_id", $request->event_id)->where("edit_recurring_pattern", "!=", "single event")->get();
                    foreach($oldEvents as $ekey => $eitem) {
                        if(array_diff( $request->custom, $eitem->custom_event_weekdays )  || $eitem->is_no_end_date != $isNoEndDate ) {
                            EventUserReminder::where('event_id', $eitem->id)->forceDelete();
                            EventRecurring::where("event_id", $eitem->id)->forceDelete();
                            $eventRecurring = $this->saveCustomRecurringEvent($eitem, $eitem->start_date, $request, $recurringEndDate);
                        } else {
                            // $eventReminders = $this->getEventReminderJson($eitem, $request);
                            $eventLinkStaff = $this->getEventLinkedStaffJson($eitem, $request);
                            $eventLinkClient = $this->getEventLinkedContactLeadJson($eitem, $request);
                            $recurringEvents = EventRecurring::where("event_id", $eitem->id)->get();
                            foreach($recurringEvents as $rkey => $ritem) {
                                $ritem->fill([
                                    // "event_reminders" => $eventReminders,
                                    "event_linked_staff" => $eventLinkStaff,
                                    "event_linked_contact_lead" => $eventLinkClient,
                                    'event_comments' => $this->getEditEventHistoryJson($eitem->id, $ritem),
                                ])->save();

                                // Update user's event reminders
                                if($request->is_reminder_updated == 'yes') {
                                    $this->updateEventUserReminder($eitem, $ritem, $request);
                                }
                            }
                        }
                        $eitem->fill([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type,
                            "start_date" => (isset($request->updated_start_date)) ? $start_date : $eitem->start_date,
                            "end_date" => (isset($request->updated_start_date)) ? $end_date : $eitem->end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : Null,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : Null,
                            "recurring_event_end_date" => ($request->end_on) ? convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone) : $eitem->recurring_event_end_date,
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "custom_event_weekdays" => $request->custom,
                            "event_interval_week" => $request->daily_weekname,
                            "edit_recurring_pattern" => "all events",
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : Null,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();
                    }             
                    $this->saveEventRecentActivity($request, $request->event_id, $eventRecurring->id ?? $request->event_recurring_id);
                } else if($request->event_frequency == 'WEEKLY') {
                    $oldEvents = Event::whereId($request->event_id)->orWhere("parent_event_id", $request->event_id)->where("edit_recurring_pattern", "!=", "single event")->get();
                    foreach($oldEvents as $ekey => $eitem) {
                        if(isset($request->updated_start_date) || $eitem->is_no_end_date != $isNoEndDate) {
                            EventUserReminder::where('event_id', $eitem->id)->forceDelete();
                            EventRecurring::where("event_id", $eitem->id)->forcedelete();
                            $eventRecurring = $this->saveWeeklyRecurringEvent($eitem, $eitem->start_date, $request, $recurringEndDate);
                        } else {
                            // $eventReminders = $this->getEventReminderJson($eitem, $request);
                            $eventLinkStaff = $this->getEventLinkedStaffJson($eitem, $request);
                            $eventLinkClient = $this->getEventLinkedContactLeadJson($eitem, $request);
                            $recurringEvents = EventRecurring::where("event_id", $eitem->id)->get();
                            $days = $this->getDatesDiffDays($request);
                            foreach($recurringEvents as $rkey => $ritem) {
                                $ritem->fill([
                                    "end_date" => ($days > 0) ? Carbon::parse($ritem->start_date)->addDays($days)->format('Y-m-d') : $ritem->end_date,
                                    // "event_reminders" => $eventReminders,
                                    "event_linked_staff" => $eventLinkStaff,
                                    "event_linked_contact_lead" => $eventLinkClient,
                                    'event_comments' => $this->getEditEventHistoryJson($eitem->id, $ritem),
                                ])->save();

                                // Update user's event reminders
                                if($request->is_reminder_updated == 'yes') {
                                    $this->updateEventUserReminder($eitem, $ritem, $request);
                                }
                            }
                        }
                        $eitem->fill([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type,
                            "start_date" => (isset($request->updated_start_date)) ? $start_date : $eitem->start_date,
                            "end_date" => (isset($request->updated_start_date)) ? $end_date : $eitem->end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : Null,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : Null,
                            "recurring_event_end_date" => ($request->end_on) ? convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone) : $eitem->recurring_event_end_date,
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "edit_recurring_pattern" => "all events",
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : Null,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();
                    }
                    $this->saveEventRecentActivity($request, $request->event_id, $eventRecurring->id ?? $request->event_recurring_id);
                } else if($request->event_frequency == 'MONTHLY') {
                    $oldEvents = Event::whereId($request->event_id)->orWhere("parent_event_id", $request->event_id)->where("edit_recurring_pattern", "!=", "single event")->get();
                    foreach($oldEvents as $ekey => $eitem) {
                        // $eventReminders = $this->getEventReminderJson($eitem, $request);
                        $eventLinkStaff = $this->getEventLinkedStaffJson($eitem, $request);
                        $eventLinkClient = $this->getEventLinkedContactLeadJson($eitem, $request);
                        if($request->monthly_frequency != $eitem->monthly_frequency || $request->event_interval_month != $eitem->event_interval_month || isset($request->updated_start_date) || $eitem->is_no_end_date != $isNoEndDate) {
                            EventUserReminder::where('event_id', $eitem->id)->forceDelete();
                            EventRecurring::where("event_id", $eitem->id)->forceDelete();
                            $eventRecurring = $this->saveMonthlyRecurringEvent($eitem, $eitem->start_date, $request, $recurringEndDate);
                        } else {
                            $recurringEvents = EventRecurring::where("event_id", $eitem->id)->get();
                            $days = $this->getDatesDiffDays($request);
                            foreach($recurringEvents as $rkey => $ritem) {
                                $ritem->fill([
                                    "end_date" => ($days > 0) ? Carbon::parse($ritem->start_date)->addDays($days)->format('Y-m-d') : $ritem->end_date,
                                    // "event_reminders" => $eventReminders,
                                    "event_linked_staff" => $eventLinkStaff,
                                    "event_linked_contact_lead" => $eventLinkClient,
                                    'event_comments' => $this->getEditEventHistoryJson($eitem->id, $ritem),
                                ])->save();

                                // Update user's event reminders
                                if($request->is_reminder_updated == 'yes') {
                                    $this->updateEventUserReminder($eitem, $ritem, $request);
                                }
                            }
                        }

                        $eitem->fill([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type,
                            "start_date" => $eitem->start_date,
                            "end_date" => $eitem->end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : $eitem->start_time,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : $eitem->end_time,
                            "recurring_event_end_date" => ($request->end_on) ? convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone) : $eitem->recurring_event_end_date,
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_month" => ($request->event_interval_month) ? $request->event_interval_month : $eitem->event_interval_month,
                            "monthly_frequency" => ($request->monthly_frequency) ? $request->monthly_frequency : $eitem->monthly_frequency,
                            "edit_recurring_pattern" => "all events",
                            "is_no_end_date" => $isNoEndDate,
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : $eitem->end_on,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : $eitem->is_event_private,
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();
                    }
                    $this->saveEventRecentActivity($request, $request->event_id, $eventRecurring->id ?? $request->event_recurring_id);
                }
                // Commented. As per client's requirement
                /* else if($request->event_frequency == 'YEARLY') {
                    $oldEvents = Event::whereId($request->event_id)->orWhere("parent_event_id", $request->event_id)->where("edit_recurring_pattern", "!=", "single event")->get();
                    foreach($oldEvents as $ekey => $eitem) {
                        if($request->yearly_frequency != $eitem->yearly_frequency || $request->event_interval_year != $eitem->event_interval_year || isset($request->updated_start_date)) {
                            $recurringEvents = EventRecurring::where("event_id", $eitem->id)->forcedelete();
                            $eventRecurring = $this->saveYearlyRecurringEvent($eitem, $eitem->start_date, $request, $recurringEndDate);
                        } else {    
                            $eventReminders = $this->getEventReminderJson($eitem, $request);
                            $eventLinkStaff = $this->getEventLinkedStaffJson($eitem, $request);
                            $eventLinkClient = $this->getEventLinkedContactLeadJson($eitem, $request);
                            $recurringEvents = EventRecurring::where("event_id", $eitem->id)->get();
                            $days = $this->getDatesDiffDays($request);
                            foreach($recurringEvents as $rkey => $ritem) {
                                $ritem->fill([
                                    "end_date" => ($days > 0) ? Carbon::parse($ritem->start_date)->addDays($days)->format('Y-m-d') : $ritem->end_date,
                                    "event_reminders" => $eventReminders,
                                    "event_linked_staff" => $eventLinkStaff,
                                    "event_linked_contact_lead" => $eventLinkClient,
                                    'event_comments' => $this->getEditEventHistoryJson($eitem->id, $ritem),
                                ])->save();
                            }
                        }

                        $eitem->fill([
                            "event_title" => $request->event_name,
                            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                            "event_type_id" => $request->event_type,
                            "start_date" => $start_date,
                            "end_date" => $end_date,
                            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : Null,
                            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : Null,
                            "recurring_event_end_date" => ($request->end_on) ? convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone) : $eitem->recurring_event_end_date,
                            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
                            "event_description" => $request->description,
                            "is_recurring" => "yes",
                            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                            "event_recurring_type" => $request->event_frequency,
                            "event_interval_year" => $request->event_interval_year,
                            "yearly_frequency" => $request->yearly_frequency,
                            "edit_recurring_pattern" => "all events",
                            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : Null,
                            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                            "firm_id" => $authUser->firm_name,
                            "updated_by" => $authUser->id,
                        ])->save();
                    }
                    $this->saveEventRecentActivity($request, $request->event_id, $eventRecurring->id ?? $request->event_recurring_id);
                } */
            }
        }
        return response()->json(['errors'=>'']);
        exit;
    }

    public function loadEditEventPage(Request $request)
      {

            $evnt_id=$request->evnt_id;

            $authUser = auth()->user();
            $evetData=Event::find($evnt_id);

            $case_id=$evetData->case_id;
            // $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
            $CaseMasterData = CaseMaster::where("firm_id", $authUser->firm_name)->where('is_entry_done',"1")->get();

            $country = Countries::get();
            $eventLocation = CaseEventLocation::where("location_future_use","yes")->get();
            $currentDateTime=$this->getCurrentDateAndTime();
        
            //Get event type 
            $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',$authUser->firm_name)->orderBy("status_order","ASC")->get();

            //Event created By user name
            $userData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->created_by)->first();
        
            $updatedEvenByUserData='';
            if($evetData->updated_by!=NULL){
                //Event updated By user name
                $updatedEvenByUserData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->updated_by)->first();
            }
            $eventLocationAdded=[];
            if($evetData->event_location_id!=""){
                $eventLocationAdded = CaseEventLocation::where("id",$evetData->event_location_id)->first();
            }
        
            $getEventColorCode = EventType::select("color_code","id")->where('id',$evetData->event_type_id)->where('firm_id',$authUser->firm_name)->orderBy("status_order","ASC")->pluck('color_code');

            $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")
                ->where("users.user_type","5")->where("users.user_level","5")->where("firm_id", $authUser->firm_name)
                ->where("lead_additional_info.is_converted","no")->get();
            $eventRecurring = EventRecurring::where("id", $request->event_recurring_id)->first();
            $eventUserReminder = EventUserReminder::where("event_id", $request->evnt_id)->where("event_recurring_id", $request->event_recurring_id)->where("user_id", auth()->id())->first();
            $eventReminderData = ($eventUserReminder) ? encodeDecodeJson($eventUserReminder->event_reminders) : [];
            $fromPageRoute = $request->from_page_route ?? Null;
            return view('case.event.loadEditEvent',compact(/* 'CaseMasterClient', */'CaseMasterData','country','currentDateTime','eventLocation','allEventType','evetData','case_id','eventReminderData','userData','updatedEvenByUserData','getEventColorCode','eventLocationAdded','caseLeadList','eventRecurring', 'fromPageRoute'));          
    }
    // Made common code. This code is not in use
     /* public function loadSingleEditEventPage(Request $request)
     {

           $evnt_id=$request->evnt_id;
           $evetData=CaseEvent::find($evnt_id);
           $eventReminderData=CaseEventReminder::where('event_id',$evnt_id)->get();

           $case_id=$evetData->case_id;
           $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        //    $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
        }

        $country = Countries::get();
           $eventLocation = CaseEventLocation::get();
           $currentDateTime=$this->getCurrentDateAndTime();
       
           //Get event type 
           $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->get();

           //Event created By user name
           $userData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->created_by)->first();
       
           $updatedEvenByUserData='';
           if($evetData->updated_by!=NULL){
               //Event updated By user name
               $updatedEvenByUserData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->updated_by)->first();
           }
       
           $getEventColorCode = EventType::select("color_code","id")->where('id',$evetData->event_type)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->pluck('color_code');
           $eventLocationAdded=[];
           if($evetData->event_location_id!=""){
              
               $eventLocationAdded = CaseEventLocation::where("id",$evetData->event_location_id)->first();
             
           }


           $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
            
           $CaseEventLinkedContactLead=CaseEventLinkedContactLead::where("event_id",$evnt_id)->get();

           return view('case.event.loadSingleEditEvent',compact('CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType','evetData','case_id','eventReminderData','userData','updatedEvenByUserData','getEventColorCode','eventLocationAdded','caseLeadList','CaseEventLinkedContactLead'));          
    } */
    public function loadCaseClientAndLeads(Request $request)
    {
        $case_id=$request->case_id;
        $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","users.id as user_id","users_additional_info.client_portal_enable")->where("case_client_selection.case_id",$case_id)->get();
        
        return view('case.event.caseClientLeadSection',compact('caseCllientSelection'));     
        exit;    
   }

    /**
     * Code updated. For new code, check loadEventCommentPopup function
     */
       /* public function loadCommentPopup(Request $request)
       {
        $CaseMasterData=[];
        $evnt_id=$request->evnt_id;
        $evetData = CaseEvent::whereId($evnt_id)->with('eventType', 'eventLocation')->first();
        $eventReminderData=CaseEventReminder::where('event_id',$evnt_id)->get();
        $eventLocation='';
        if($evetData->event_location_id!="0"){
            $eventLocation = CaseEventLocation::leftJoin('countries','countries.id','=','case_event_location.country')->where('case_event_location.id',$evetData->event_location_id)->first();
        }
        if($evetData->case_id!=NULL){
            $case_id=$evetData->case_id;
            $CaseMasterData = CaseMaster::where('id',$case_id)->first();
        }
        $caseLinkedStaffList = CaseEventLinkedStaff::join('users','users.id','=','case_event_linked_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.user_type","case_event_linked_staff.attending")->where("case_event_linked_staff.event_id",$evnt_id)->get();

        //Event created By user name
        $eventCreatedBy = User::select("first_name","last_name","id","user_level","user_type")->where("id",$evetData->created_by)->first();
       
        $updatedEvenByUserData='';
        if($evetData->updated_by!=NULL){
            //Event updated By user name
            $updatedEvenByUserData = User::select("first_name","last_name","id","user_level","user_type")->where("id",$evetData->updated_by)->first();
        }
        $country = Countries::get();

        $CaseEventLinkedContactLead = CaseEventLinkedContactLead::join('users','users.id','=','case_event_linked_contact_lead.contact_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.user_type","contact_id","attending","invite")->where("case_event_linked_contact_lead.event_id",$evnt_id)->get();

        return view('case.event.loadEventCommentPopup',compact('evetData','eventLocation','country','CaseMasterData','caseLinkedStaffList','eventCreatedBy','updatedEvenByUserData','CaseEventLinkedContactLead'));     
        exit;    
      } */
    
    /**
     * Load event detail/comment popup
     */
    public function loadEventCommentPopup(Request $request)
    {
        $event = Event::whereId($request->event_id)->with('eventType', 'eventLocation', 'case')->first();
        $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->where("event_id", $request->event_id)->first();
        $linkedStaff = encodeDecodeJson($eventRecurring->event_linked_staff);
        $linkedUser = [];
        if(count($linkedStaff)) {
            foreach($linkedStaff as $key => $item) {
                $user = getUserDetail($item->user_id);
                $linkedUser[] = (object)[
                    'user_id' => $item->user_id,
                    'full_name' => $user->full_name,
                    'user_type' => $user->user_type_text,
                    'attending' => $item->attending,
                    'utype' => 'staff',
                ];
            }
        }
        $linkedContact = encodeDecodeJson($eventRecurring->event_linked_contact_lead);
        if(count($linkedContact)) {
            foreach($linkedContact as $key => $item) {
                $user = getUserDetail(($item->user_type == 'lead') ? $item->lead_id : $item->contact_id);
                $linkedUser[] = (object)[
                    'user_id' => ($item->user_type == 'lead') ? $item->lead_id : $item->contact_id,
                    'full_name' => $user->full_name,
                    'user_type' => $user->user_type_text,
                    'attending' => $item->attending,
                    'utype' => $item->user_type,
                ];
            }
        }
        $fromPageRoute = $request->from_page_route ?? Null;

        return view('case.event.loadEventCommentPopup',compact('event', 'eventRecurring', 'linkedUser', 'fromPageRoute'));     
        exit;    
    }

    public function loadCommentHistory(Request $request)
    {
        /* $evnt_id=$request->event_id;
        $evetData=CaseEvent::find($evnt_id);
        
        //Event created By user name
        $eventCreatedBy = '';
        if(!empty($evetData) && $evetData->created_by != NULL){
            $eventCreatedBy = User::select("first_name","last_name","id","user_level","user_type")->where("id",$evetData->created_by)->first();
        }       
        if(!empty($evetData)){
            $linkStaffPivot = $evetData->eventLinkedStaff()->wherePivot('user_id', Auth::User()->id)->first();
            
            if($linkStaffPivot) {
                $linkStaffPivot->pivot->comment_read_at = Carbon::now();
                $linkStaffPivot->pivot->save();
            }
        }
            
        $commentData = CaseEventComment::where("event_id", $evnt_id)->orderBy('created_at', 'desc')->with("createdByUser")->get();
            
        return view('case.event.loadEventCommentHistory',compact('evetData','eventCreatedBy','commentData'));     
        exit; */
        
        $eventData = Event::whereId($request->event_id)->with('eventCreatedByUser')->first();
        $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
        if($eventRecurring) {
            $linkStaffPivot = encodeDecodeJson($eventRecurring->event_linked_staff);
            if(count($linkStaffPivot)) {
                $newArray = [];
                foreach($linkStaffPivot as $skey => $sitem) {
                    if($sitem->user_id == auth()->id()) {
                        $sitem->comment_read_at = Carbon::now();
                    }
                    $newArray[] = $sitem;
                }
                $eventRecurring->fill(['event_linked_staff' => encodeDecodeJson($newArray, 'encode')])->save();

            }
            $eventCreatedBy = ($eventData) ? $eventData->eventCreatedByUser : '';
            $commentData = encodeDecodeJson($eventRecurring->event_comments)->sortByDesc('created_at');
            return view('case.event.loadEventHistory',compact('eventData','eventCreatedBy','commentData')); 
        }

    }
      public function loadCaseLinkedStaff(Request $request)
      {
          $from=$request->from;
          $case_id=$request->case_id;
          $caseLinkedStaffList = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get();
        
          $caseLinkeSaved=array();
          $caseLinkeSavedAttending=array();
          if(isset($request->event_id) && $request->event_id!=''){
            $caseLinkeSaved = CaseEventLinkedStaff::select("case_event_linked_staff.user_id")->where("case_event_linked_staff.event_id",$request->event_id)->where('is_linked','yes')->get()->pluck('user_id');
            $caseLinkeSaved= $caseLinkeSaved->toArray();

            $caseLinkeSavedAttending = CaseEventLinkedStaff::select("case_event_linked_staff.user_id")->where("case_event_linked_staff.event_id",$request->event_id)->where('attending','yes')->get()->pluck('user_id');
            $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();
            
          }
          return view('case.event.caseLinkedStaff',compact('caseLinkedStaffList','caseLinkeSaved','from','caseLinkeSavedAttending'));     
          exit;    
     }
     public function loadCaseNoneLinkedStaff(Request $request)
      {
          $nonLinkedSaved=[];
          $case_id=$request->case_id;
          $caseLinkedStaffList = CaseStaff::select("case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get()->pluck('case_staff_user_id');

          $loadFirmUser = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_status","1")->where("user_level","3")->whereNotIn('id',$caseLinkedStaffList)->get();
            if(isset($request->event_id)){
                $nonLinkedSaved = CaseEventLinkedStaff::select("case_event_linked_staff.user_id")->where("case_event_linked_staff.event_id",$request->event_id)->where('is_linked','no')->get()->pluck('user_id');
                $nonLinkedSaved= $nonLinkedSaved->toArray();
            }
          
          return view('case.event.caseNoneLinkedStaff',compact('loadFirmUser','nonLinkedSaved'));     
          exit;    
     }
    
    // Commented, code updated as per new logic
     /* public function saveEventReminder($request,$event_id)
     {
        CaseEventReminder::where("event_id", $event_id)->where("created_by", Auth::user()->id)->forceDelete();

        for($i=0;$i<count($request['reminder_user_type'])-1;$i++){
            $CaseEventReminder = new CaseEventReminder;
            $CaseEventReminder->event_id=$event_id; 
            $CaseEventReminder->reminder_type=$request['reminder_type'][$i];
            $CaseEventReminder->reminer_number=$request['reminder_number'][$i];
            $CaseEventReminder->reminder_frequncy=$request['reminder_time_unit'][$i];
            $CaseEventReminder->reminder_user_type=$request['reminder_user_type'][$i];
            $CaseEventReminder->created_by=Auth::user()->id; 
            $CaseEventReminder->remind_at=Carbon::now(); 

            $CaseEventReminder->save();
        }
    } */

    // Commented, code updated as per new logic
    /* public function saveLinkedStaffToEvent($request,$event_id)
    {
        $oldRecord = CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by", Auth::user()->id)->where("is_linked","yes")->first();
        $lastCommentReadAt = $oldRecord->comment_read_at ?? Carbon::now();
        CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by", Auth::user()->id)->where("is_linked","yes")->forceDelete();
        if(isset($request['linked_staff_checked_share'])){
            $alreadyAdded=[];
            for($i=0;$i<count($request['linked_staff_checked_share']);$i++){
                Log::info("saveLinkedStaffToEvent > user_id = ".$request['linked_staff_checked_share'][$i]);
                $CaseEventLinkedStaff = new CaseEventLinkedStaff;
                $CaseEventLinkedStaff->event_id=$event_id; 
                $CaseEventLinkedStaff->user_id=$request['linked_staff_checked_share'][$i];
                $attend = "no";
                if(isset($request['linked_staff_checked_attend']) && in_array($request['linked_staff_checked_share'][$i], $request['linked_staff_checked_attend'])){
                    $attend = "yes";
                }
                $CaseEventLinkedStaff->is_linked='yes';
                $CaseEventLinkedStaff->attending=$attend;
                $CaseEventLinkedStaff->comment_read_at = $lastCommentReadAt;
                $CaseEventLinkedStaff->created_by=Auth::user()->id; 
                if(!in_array($request['linked_staff_checked_share'][$i],$alreadyAdded)){
                    $CaseEventLinkedStaff->save();
                }
                $alreadyAdded[]=$request['linked_staff_checked_share'][$i];
            }
        }
   } */

    // Commented, code updated as per new logic
   /* public function saveNonLinkedStaffToEvent($request,$event_id)
    {
    //    print_r($request);
        CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by", Auth::user()->id)->where("is_linked","no")->forceDelete();
        if(isset($request['share_checkbox_nonlinked'])){
            $alreadyAdded = $attend_checkbox_nonlinked = [];
            if(isset($request['attend_checkbox_nonlinked'])){
                for($i=0;$i<count(array_unique($request['attend_checkbox_nonlinked']));$i++){                
                    array_push($attend_checkbox_nonlinked, $request['attend_checkbox_nonlinked'][$i]);
                }            
            }
            for($i=0;$i<count(array_unique($request['share_checkbox_nonlinked']));$i++){                
                $CaseEventLinkedStaff = new CaseEventLinkedStaff;
                $CaseEventLinkedStaff->event_id=$event_id; 
                $CaseEventLinkedStaff->user_id=$request['share_checkbox_nonlinked'][$i];
                $attend="no";
                if(isset($request['share_checkbox_nonlinked'][$i])){
                    if(in_array($request['share_checkbox_nonlinked'][$i], $attend_checkbox_nonlinked)){
                        $attend="yes";
                    }
                }                
                $CaseEventLinkedStaff->is_linked='no';
                $CaseEventLinkedStaff->attending=$attend;
                $CaseEventLinkedStaff->created_by=Auth::user()->id; 
                if(!in_array($request['share_checkbox_nonlinked'][$i],$alreadyAdded)){
                    $CaseEventLinkedStaff->save();
                }
                $alreadyAdded[]=$request['share_checkbox_nonlinked'][$i];
            }
        }
   } */

    // Commented, code updated as per new logic
   /* public function saveContactLeadData($request,$event_id)
   {
    //   print_r($reques[t);exit;
       CaseEventLinkedContactLead::where("event_id", $event_id)->where("created_by", Auth::user()->id)->forceDelete();
       if(isset($request['LeadInviteClientCheckbox'])){
            $alreadyAdded=$attend_checkbox_nonlinked = [];
            if(isset($request['LeadAttendClientCheckbox'])){
                for($i=0;$i<count(array_unique($request['LeadAttendClientCheckbox']));$i++){                
                    array_push($attend_checkbox_nonlinked, $request['LeadAttendClientCheckbox'][$i]);
                } 
            }
            for($i=0;$i<count(array_unique($request['LeadInviteClientCheckbox']));$i++){
                $CaseEventLinkedContactLead = new CaseEventLinkedContactLead;
                $CaseEventLinkedContactLead->event_id=$event_id; 
                $CaseEventLinkedContactLead->user_type='lead'; 
                $CaseEventLinkedContactLead->lead_id=$request['LeadInviteClientCheckbox'][$i];
                $attend="no";
                if(isset($request['LeadInviteClientCheckbox'][$i])){
                    if(in_array($request['LeadInviteClientCheckbox'][$i], $attend_checkbox_nonlinked)){
                        $attend="yes";
                    }
                } 
                $CaseEventLinkedContactLead->attending=$attend;
                $CaseEventLinkedContactLead->invite="yes";
                $CaseEventLinkedContactLead->created_by=Auth::user()->id; 
                if(!in_array($request['LeadInviteClientCheckbox'][$i],$alreadyAdded)){
                    $CaseEventLinkedContactLead->save();
                }
            //    print_r(CaseEventLinkedContactLead);
               $alreadyAdded[]=$request['LeadInviteClientCheckbox'][$i];
            }
       }else if(isset($request['ContactInviteClientCheckbox'])){
        $alreadyAdded=$attend_checkbox_nonlinked = [];
        if(isset($request['ContactAttendClientCheckbox'])){
            for($i=0;$i<count(array_unique($request['ContactAttendClientCheckbox']));$i++){                
                array_push($attend_checkbox_nonlinked, $request['ContactAttendClientCheckbox'][$i]);
            } 
        }
        for($i=0;$i<count(array_unique($request['ContactInviteClientCheckbox']));$i++){
            $CaseEventLinkedContactLead = new CaseEventLinkedContactLead;
            $CaseEventLinkedContactLead->event_id=$event_id; 
            $CaseEventLinkedContactLead->user_type='contact'; 
            $CaseEventLinkedContactLead->contact_id=$request['ContactInviteClientCheckbox'][$i];
            $attend="no";
            if(isset($request['ContactInviteClientCheckbox'][$i])){
                if(in_array($request['ContactInviteClientCheckbox'][$i], $attend_checkbox_nonlinked)){
                    $attend="yes";
                }
            } 
            $CaseEventLinkedContactLead->attending=$attend;
            $CaseEventLinkedContactLead->invite="yes";
            $CaseEventLinkedContactLead->created_by=Auth::user()->id; 
            if(!in_array($request['ContactInviteClientCheckbox'][$i],$alreadyAdded)){
                $CaseEventLinkedContactLead->save();
            }
            $alreadyAdded[]=$request['ContactInviteClientCheckbox'][$i];
        }
    }

  } */
   public function deleteEventPopup(Request $request)
   {
       $event_id=$request->event_id;
       $event = Event::find($event_id);
       $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
        $fromPageRoute = $request->from_page_route ?? Null;
       return view('case.event.deleteEvent',compact('event_id','event', 'eventRecurring', 'fromPageRoute'));     
       exit;    
   }    
   
   public function deleteEventFromCommentPopup(Request $request)
   {
       $event_id=$request->event_id;
       $CaseEvent = CaseEvent::find($event_id);

       return view('case.event.deleteEventFromComment',compact('event_id','CaseEvent'));     
       exit;    
   }

    
    public function deleteEvent(Request $request)
    {
        // return $request->all();
        $eventId=$request->event_id;
        /* $CaseEvent = CaseEvent::find($eventId);

        if($request->delete_event_type=='SINGLE_EVENT'){
            // $oldEvents = CaseEvent::where('parent_evnt_id',$CaseEvent->parent_evnt_id);
            $oldEvents = CaseEvent::where("id", $eventId);
            $CaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
            // $oldEvents->forceDelete();
            $oldEvents->delete();

        }else if($request->delete_event_type=='THIS_AND_FOLLOWING_EVENTS'){
            // CaseEvent::where("parent_evnt_id", $CaseEvent->parent_evnt_id)->whereDate('start_date',">=",$CaseEvent->start_date)->delete();
            $oldEvents = CaseEvent::where('parent_evnt_id',$CaseEvent->parent_evnt_id)->where('id',">=",$CaseEvent->id);
            $CaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
            // $oldEvents->forceDelete();
            $oldEvents->delete();
        
        }else if($request->delete_event_type=='ALL_EVENTS'){
            // CaseEvent::where("parent_evnt_id", $CaseEvent->parent_evnt_id)->delete();
            $oldEvents = CaseEvent::where('parent_evnt_id',$CaseEvent->parent_evnt_id);
            $CaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
            // $oldEvents->forceDelete();
            $oldEvents->delete();
        } */
        $event = Event::whereId($request->event_id)->first();
        $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
        //Master Event History
        $data=[];
        $data['event_for_case'] = $event->case_id;
        $data['event_for_lead']=$event->lead_id;                        
        $data['client_id']=$event->lead_id;
        $data['event_id']=$event->id;
        $data['event_recurring_id']=$request->event_recurring_id;
        $data['event_name']=$event->event_title;
        $data['user_id']=auth()->id();
        $data['activity']='deleted event';
        $data['type']='event';
        $data['action']='delete';
        $CommonController= new CommonController();
        if($request->delete_event_type == 'SINGLE_EVENT') {
            if($event->is_recurring == 'no') {
                $event->delete();
                $eventRecurring->delete();
                $CommonController->addMultipleHistory($data);
            } else if($event->is_recurring == 'yes' && $event->edit_recurring_pattern == "single event") {
                $event->delete();
                $eventRecurring->delete();
                $CommonController->addMultipleHistory($data);
            } else {
                $eventRecurring->delete();
            }
        } else if($request->delete_event_type == 'THIS_AND_FOLLOWING_EVENTS') {
            $allEventIds = Event::where("parent_event_id", $request->event_id)->orWhere("id", $request->event_id)->pluck("id")->toArray();
            $belowRecurringEvent = EventRecurring::where("id", "<", $request->event_recurring_id)->whereIn("event_id", $allEventIds)->get();
            $remainEventIds = $belowRecurringEvent->pluck('event_id')->toArray();
            $events = Event::where("parent_event_id", $request->event_id)->whereNotIn("id", $remainEventIds);
            $lastRecurringEvent = EventRecurring::where("id", "<", $request->event_recurring_id)->whereIn("event_id", $remainEventIds)->orderBy("id", 'desc')->first();
            $lastEvent = Event::whereIn('id', $remainEventIds)/* ->whereNotIn('edit_recurring_pattern', ['single event']) */->orderBy("id", "desc")->first();
            if($lastEvent) {
                $lastEvent->fill([
                    'end_on' => $lastRecurringEvent->start_date,
                    'is_no_end_date' => 'no',
                    'recurring_event_end_date' => $lastRecurringEvent->start_date,
                ])->save();
            }
            EventRecurring::whereIn("event_id", $events->pluck("id")->toArray())->orWhere("event_id", $request->event_id)->where("id", ">=", $request->event_recurring_id)->delete();
            $events->delete();
        
        } else if($request->delete_event_type=='ALL_EVENTS') {
            $events = Event::where("parent_event_id", $request->event_id)->orWhere("id", $request->event_id);
            EventRecurring::whereIn("event_id", $events->pluck("id")->toArray())->delete();
            $events->delete();
            $CommonController->addMultipleHistory($data);
        }
        //Master Event History
        session(['popup_success' => 'Event was deleted.']);
        return response()->json(['errors'=>''   ]);
        exit;   
    }
    public function confirmBeforeEditEventPopup(Request $request)
    {
        
        $event_id=$request->event_id;
        $CaseEvent = CaseEvent::find($event_id);
        return view('case.event.ConfirmBeforeEditEvent',compact('event_id','CaseEvent'));     
        exit;    
    }

    public function saveEventComment(Request $request)
    {
       
        /* $event_id=$request->event_id;
        $CaseEventComment =new CaseEventComment;
        $CaseEventComment->event_id=$request->event_id;
        $CaseEventComment->comment=$request->delta;
        $CaseEventComment->action_type="0";
        $CaseEventComment->created_by=Auth::user()->id; 
        $CaseEventComment->save();

        $eventId=$request->event_id;
        $CaseEvent = CaseEvent::whereId($eventId)->with('eventLinkedContact')->first(); 
        $data=[];
        $data['event_for_case']=$CaseEvent->case_id;
        $data['event_id']=$eventId;
        $data['event_name']=$CaseEvent->event_title;
        $data['user_id']=Auth::User()->id;
        $data['activity']='commented on event';
        $data['type']='event';
        $data['action']='comment';
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);

        // For client recent activity
        if($CaseEvent->eventLinkedContact) {
            foreach($CaseEvent->eventLinkedContact as $key => $item) {
                $data['user_id'] = $item->id;
                $data['client_id'] = $item->id;
                $data['activity']='commented event';
                $data['is_for_client'] = 'yes';
                $CommonController->addMultipleHistory($data);
            }
        }

        Log::info("comment email job dispatched");
        dispatch(new EventCommentEmailJob($request->event_id, Auth::User()->firm_name, $CaseEventComment->id, Auth::User()->id)); */
        $authUser = auth()->user();
        $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
        if($eventRecurring) {
            $eventComment = [
                'event_id' => $request->event_id,
                'comment' => $request->delta,
                'action_type' => "0",
                'created_by' => $authUser->id,
                'created_at' => Carbon::now(),
            ];
            $decodeJson = encodeDecodeJson($eventRecurring->event_comments);
            $decodeJson->push($eventComment);
            $eventRecurring->fill(['event_comments' => encodeDecodeJson($decodeJson, 'encode')])->save();

            $event = Event::whereId($request->event_id)->first(); 
            $data=[];
            $data['event_for_case'] = $event->case_id;
            $data['event_id'] = $eventRecurring->id;
            $data['event_name'] = $event->event_title;
            $data['user_id'] = $authUser->id;
            $data['activity']='commented on event';
            $data['type']='event';
            $data['action']='comment';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            // For client recent activity
            if($eventRecurring->event_linked_contact_lead && $event->case_id) {
                $decodeContacts = encodeDecodeJson($eventRecurring->event_linked_contact_lead);
                foreach($decodeContacts as $key => $item) {
                    $data['user_id'] = $item->contact_id;
                    $data['client_id'] = $item->contact_id;
                    $data['activity']='commented event';
                    $data['is_for_client'] = 'yes';
                    $CommonController->addMultipleHistory($data);
                }
            }
            if($event->case_id) {
                Log::info("comment email job dispatched");
                dispatch(new EventCommentEmailJob($request->event_id, $authUser->firm_name, $eventComment, $authUser->id, $request->event_recurring_id));
            }
        }
        return response()->json(['errors'=>'']);
        exit;    
    }

    // Commented, create new code as per new logic
    /* public function saveEventHistory($request)
    {
        $CaseEventComment =new CaseEventComment;
        $CaseEventComment->event_id=$request;
        $CaseEventComment->comment=NULL;
        $CaseEventComment->created_by=Auth::user()->id; 
        $CaseEventComment->action_type="1";
        $CaseEventComment->save();

    } */

    /**
     * Made common code so commented
     */
    /* public function loadReminderPopup(Request $request)
    {
        
        $event_id=$request->evnt_id;
        $eventReminderData = CaseEventReminder::where("event_id",$event_id)->get();
        return view('case.event.loadReminderPopup',compact('event_id','eventReminderData'));     
        exit;    
    } */

    /**
     * Load event reminder popup
     */
    public function loadEventReminderPopup(Request $request)
    {
        $event_id = $request->event_id;
        $event_recurring_id = $request->event_recurring_id;
        $eventRecurring = EventRecurring::where("id", $event_recurring_id)->where("event_id", $event_id)->first();
        if($eventRecurring) {
            $eventUserReminder = EventUserReminder::where('event_id', $event_id)->where('event_recurring_id', $event_recurring_id)->where('user_id', auth()->id())->first();
            $eventReminder = encodeDecodeJson(@$eventUserReminder->event_reminders);
            return view('case.event.loadReminderPopupIndex',compact('event_id', 'event_recurring_id', 'eventReminder'));     
        }
        return response()->json(["errors" => "Record not found"]);
    }

    /**
     * Update code, for new code check saveEventReminderPopup function
     */
    /* public function saveReminderPopup(Request $request)
    {
        // return $request->all();
        $event_id=$request->event_id;
        // $this->saveEventReminder($request,$event_id);
        if(isset($request->reminder['user_type'])){
            foreach($request->reminder['user_type'] as $key => $item) {
                CaseEventReminder::updateOrCreate([
                    'id' => @$request->reminder['id'][$key],
                    'event_id' => $event_id
                ], [
                    'reminder_type' => $request->reminder['type'][$key],
                    'reminer_number' => $request->reminder['number'][$key],
                    'reminder_frequncy' => $request->reminder['time_unit'][$key],
                    'reminder_user_type' => $item,
                    'created_by' => Auth::user()->id,
                    'remind_at' => Carbon::now(),
                ]);
            }

        
            // Delete deleted reminders
            $reminderIds = explode(",", $request->deleted_reminder_id);
            $reminders = CaseEventReminder::whereIn("id", $reminderIds)->get();
            if($reminders) {
                foreach($reminders as $key => $item) {
                    $item->delete();
                }
            }
        }else{
            // for fix save reminder on event edit from calender view
            $this->saveEventReminder($request,$event_id);
        }
        return response()->json(['errors'=>'','msg'=>'Reminders successfully updated']);
        exit;    
    } */

    /**
     * Save event reminders
     */
    public function saveEventReminderPopup(Request $request)
    {
        // return $request->all();
        $authUserId = auth()->id();
        $eventReminders = [];
        $eventRecurring = EventRecurring::where("id", $request->event_recurring_id)->where("event_id", $request->event_id)->first();
        if(isset($request->reminder['user_type']) && $eventRecurring) {
            $request->start_date = $eventRecurring->start_date;
            foreach($request->reminder['user_type'] as $key => $item) {
                $eventReminders[] = [
                    'event_id' => $request->event_id,
                    'reminder_type' => $request->reminder['type'][$key],
                    'reminer_number' => $request->reminder['number'][$key],
                    'reminder_frequncy' => $request->reminder['time_unit'][$key],
                    'reminder_user_type' => $item,
                    'created_by' => $authUserId,
                    'remind_at' => $this->getRemindAtAttribute($request, $request->reminder['time_unit'][$key], $request->reminder['number'][$key]),
                    'snooze_time' => null,
                    'snooze_type' => null,
                    'snoozed_at' => null,
                    'snooze_remind_at' => null,
                    'is_dismiss' => 'no',
                    'reminded_at' => null
                ];
            }
            $allRecurringEvent = EventRecurring::where('event_id', $request->event_id)->get();
            if($allRecurringEvent) {
                foreach($allRecurringEvent as $key => $item) {
                    EventUserReminder::updateOrCreate([
                        'event_id' => $request->event_id,
                        'event_recurring_id' => $item->id,
                        'user_id' => $authUserId,
                    ], [
                        'event_reminders' => encodeDecodeJson($eventReminders, 'encode')
                    ]);
                }
            }
            return response()->json(['errors'=>'','msg'=>'Reminders successfully updated']);
        } else {
            return response()->json(['errors'=>'Record not found']);
        }
    }

    /**
     * Load event reminder list
     */
    public function loadReminderHistory(Request $request)
    {
      /* $evnt_id=$request->event_id;
      $evetData=CaseEvent::find($evnt_id);
      $eventReminderData=CaseEventReminder::where('event_id',$evnt_id)->get();
      
        return view('case.event.loadReminderHistory',compact('evetData','eventReminderData'));     
        exit; */
        $eventRecurring = EventRecurring::where("id", $request->event_recurring_id)->where("event_id", $request->event_id)->first();
        if($eventRecurring) {
            $eventUserReminder = EventUserReminder::where('event_id', $request->event_id)->where('event_recurring_id', $request->event_recurring_id)->where('user_id', auth()->id())->first();
            if($eventUserReminder) {
                $eventReminder = encodeDecodeJson($eventUserReminder->event_reminders);
                return view('case.event.loadReminderHistory', compact( 'eventReminder'));  
            }
        }
        return response()->json(["errors" => "Record not found"]);
    }

   /**
    * Code updated, for new code, check loadEventReminderPopup function
    */
   /* public function loadReminderPopupIndex(Request $request)
    {
        
        $event_id=$request->evnt_id;
        $eventReminderData = CaseEventReminder::where("event_id",$event_id)->get();
        return view('case.event.loadReminderPopupIndex',compact('event_id','eventReminderData'));     
        exit;    
    } */

    public function saveSOLEventIntoCalender($case_id){
        $authUser = auth()->user();
        $CaseData=CaseMaster::find($case_id);
        $solEvent = Event::updateOrCreate(
            [
                'case_id' => $case_id,
                'is_SOL' => "yes"
            ], [
            'event_title'=>$CaseData->case_title,
            'case_id'=>$case_id,
            'event_type'=>NULL,
            'start_date'=>$CaseData->case_statute_date,
            'end_date'=>$CaseData->case_statute_date,
            'is_full_day'=>"yes",
            'is_SOL'=>"yes",
            'event_description'=>"",
            'is_recurring'=>"no",
            'event_location_id'=>'0',
            'is_event_private'=>'no',
            'firm_id' => $authUser->firm_name,
            'created_by'=> $authUser->id,
        ]);

        EventRecurring::updateOrCreate([
                'event_id' => $solEvent->id,
            ], [
                'event_id' => $solEvent->id,
                'start_date' => $CaseData->case_statute_date,
                'end_date' => $CaseData->case_statute_date,
            ]);

        // $CaseEvent = new CaseEvent;
        // $CaseEvent->event_title=$CaseData->case_title;  
        // $CaseEvent->case_id=$case_id;
        // $CaseEvent->event_type=NULL;
        // $CaseEvent->start_date=$CaseData->case_statute_date; 
        // $CaseEvent->end_date=$CaseData->case_statute_date; 
        // $CaseEvent->all_day="yes";
        // $CaseEvent->is_SOL="yes";
        // $CaseEvent->event_description="";
        // $CaseEvent->recuring_event="no"; 
        // $CaseEvent->event_location_id ='0';
        // $CaseEvent->is_event_private ='no';
        // $CaseEvent->parent_evnt_id ='0';
        // $CaseEvent->created_by=Auth::user()->id; 
        // $CaseEvent->save();
    }
    public function saveLocationOnce($locationdata){
      
        $CaseEventLocation = new CaseEventLocation;
        $CaseEventLocation->location_name=$locationdata['location_name'];
        $CaseEventLocation->address1=$locationdata['address'];
        $CaseEventLocation->address2=$locationdata['address2'];
        $CaseEventLocation->city=$locationdata['city'];
        $CaseEventLocation->state=$locationdata['state'];
        $CaseEventLocation->postal_code=$locationdata['postal_code'];
        $CaseEventLocation->country=$locationdata['country'];
        $CaseEventLocation->location_future_use=($locationdata['location_future_use'])?'yes':'no';
        $CaseEventLocation->created_by=Auth::user()->id; 
        $CaseEventLocation->save();
        return $CaseEventLocation->id;
    }


    public function hideAddEventGuide(Request $request)
    {
        $userMaster = User::find(Auth::User()->id);
        
        if($request->type=="1"){
            $userMaster->add_event_guide="1";
        }else{
            $userMaster->add_event_guide2="1";

        }
        $userMaster->save();        
    } 
   

    public function loadEventRightSection(Request $request)
    {       
        $caseLinkeSaved=$caseNonLinkeSaved=array();
        $caseLinkeSavedAttending=$caseNonLinkeSavedAttending=array();
        $case_id=$request->case_id;
        $event_id=$request->event_id;
        $nonLinkedSaved=[];
        $companyLinkedUsers=[];
        $caseLinkeSavedAttendingContact=$caseLinkeSavedInviteContact=[];
        $from='';
        //Client List
        $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
                ->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')
                ->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","users.id as user_id","users_additional_info.client_portal_enable")->where("case_client_selection.case_id",$case_id)->get();
        $newArray = []; $companyClientIds = [];
        foreach($caseCllientSelection as $k=>$v) {
            if($v->user_level == '4') {
                $newArray[] = $v;
                $companyContacts = $v->companyContactList($v->user_id, $v->case_id);
                if($companyContacts) {
                    foreach($companyContacts as $ck => $cv) {
                        $contact = $caseCllientSelection->where('id', $cv->cid)->first();
                        $contact['is_company_contact'] = "yes";
                        $newArray[] = $contact;
                        array_push($companyClientIds, $cv->cid);
                    }  
                }
                array_push($companyClientIds, $v->user_id);
            } else {   
                if(!in_array($v->user_id, $companyClientIds)) {
                    $v['is_company_contact']="no";
                    $newArray[] = $v;
                }
            }
        }
        $caseCllientSelection = collect($newArray);
        //Non linked staff List
        $caseNoneLinkedStaffList = CaseStaff::select("case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get()->pluck('case_staff_user_id');
        $loadFirmUser = User::select("first_name","last_name","id","parent_user")->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->whereIn("user_status",["1",'2'])->whereNotIn('id',$caseNoneLinkedStaffList)->get();
        
        //Linked Staff List
        $caseLinkedStaffList = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get();
      
        /* if(isset($request->event_id) && $request->event_id!=''){
            $caseLinkeSaved = CaseEventLinkedStaff::select("user_id")->where("event_id",$request->event_id)->where("is_linked","yes")->get()->pluck('user_id');
            $caseLinkeSaved= $caseLinkeSaved->toArray();
            
            $caseLinkeSavedAttending = CaseEventLinkedStaff::select("user_id")->where("event_id",$request->event_id)->where("is_linked","yes")->where('attending','yes')->get()->pluck('user_id');
            $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();

            $caseNonLinkeSaved = CaseEventLinkedStaff::select("user_id")->where("event_id",$request->event_id)->where("is_linked","no")->get()->pluck('user_id');
            $caseNonLinkeSaved= $caseNonLinkeSaved->toArray();

            $caseNonLinkeSavedAttending = CaseEventLinkedStaff::select("user_id")->where("event_id",$request->event_id)->where("is_linked","no")->where('attending','yes')->get()->pluck('user_id');
            $caseNonLinkeSavedAttending= $caseNonLinkeSavedAttending->toArray();

            $caseLinkeSavedAttendingContact = CaseEventLinkedContactLead::select("case_event_linked_contact_lead.contact_id")->where("case_event_linked_contact_lead.event_id",$request->event_id)->where('attending','yes')->get()->pluck('contact_id');
            $caseLinkeSavedAttendingContact= $caseLinkeSavedAttendingContact->toArray();

            $caseLinkeSavedInviteContact = CaseEventLinkedContactLead::select("case_event_linked_contact_lead.contact_id")->where("case_event_linked_contact_lead.event_id",$request->event_id)->where('invite','yes')->get()->pluck('contact_id');
            $caseLinkeSavedInviteContact= $caseLinkeSavedInviteContact->toArray();           
            $from="edit";
        } */

        $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
        if($eventRecurring) {
            $linkedStaff = encodeDecodeJson($eventRecurring->event_linked_staff);
            $caseLinkeSaved = $linkedStaff->where('is_linked', 'yes')->pluck('user_id')->toArray();
            $caseLinkeSavedAttending = $linkedStaff->where("is_linked","yes")->where('attending','yes')->pluck('user_id')->toArray();
            $caseNonLinkeSaved = $linkedStaff->where("is_linked","no")->pluck('user_id')->toArray();
            $caseNonLinkeSavedAttending = $linkedStaff->where("is_linked","no")->where('attending','yes')->pluck('user_id')->toArray();

            $linkedContact = encodeDecodeJson($eventRecurring->event_linked_contact_lead);
            $caseLinkeSavedAttendingContact = $linkedContact->where('attending','yes')->pluck('contact_id')->toArray();
            $caseLinkeSavedInviteContact = $linkedContact->where('invite','yes')->pluck('contact_id')->toArray();           
            $from="edit";
        }
       
        return view('case.event.loadEventRightSection',compact('caseCllientSelection','loadFirmUser','case_id','caseLinkedStaffList','caseLinkeSaved','caseLinkeSavedAttending','from','caseLinkeSavedAttendingContact','caseLinkeSavedInviteContact','caseNonLinkeSavedAttending','caseNonLinkeSaved','companyLinkedUsers'));     
        exit;    
   }
   public function loadLeadRightSection(Request $request)
    {       
        $caseLinkeSaved=array();
        $caseLinkeSavedAttending=array();
        $case_id=$request->case_id;
        $event_id=$request->event_id;
        $nonLinkedSaved=[];
        $caseLinkeSavedAttendingLead=$caseLinkeSavedInviteLead=[];
        $from='';


        //Client List
        $caseCllientSelection = User::select("first_name","last_name","id","parent_user","user_level")->where("id",$request->lead_id)->get();
        // $caseCllientSelection = User::leftJoin('lead_additional_info','lead_additional_info.user_id','=','users.id')->select("users.id","users.first_name","users.last_name","users.user_level","users.parent_user","lead_additional_info.client_portal_enable")->where("users.id",$request->lead_id)->get();

        //Load all staff
        $loadFirmUser = User::select("first_name","last_name","id","parent_user")->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->where("user_status","1")->get();
        
        /* if(isset($request->event_id) && $request->event_id!=''){
            $caseLinkeSaved = CaseEventLinkedStaff::select("case_event_linked_staff.user_id")->where("case_event_linked_staff.event_id",$request->event_id)->get()->pluck('user_id');
            $caseLinkeSaved= $caseLinkeSaved->toArray();

            $caseLinkeSavedAttending = CaseEventLinkedStaff::select("case_event_linked_staff.user_id")->where("case_event_linked_staff.event_id",$request->event_id)->where('attending','yes')->get()->pluck('user_id');
            $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();

            $caseLinkeSavedAttendingLead = CaseEventLinkedContactLead::select("case_event_linked_contact_lead.lead_id")->where("case_event_linked_contact_lead.event_id",$request->event_id)->where('attending','yes')->get()->pluck('lead_id');
            $caseLinkeSavedAttendingLead= $caseLinkeSavedAttendingLead->toArray();

            $caseLinkeSavedInviteLead = CaseEventLinkedContactLead::select("case_event_linked_contact_lead.lead_id")->where("case_event_linked_contact_lead.event_id",$request->event_id)->where('invite','yes')->get()->pluck('lead_id');
            $caseLinkeSavedInviteLead= $caseLinkeSavedInviteLead->toArray();
            
            $from="edit";
        } */
          
        $eventRecurring = EventRecurring::whereId($request->event_recurring_id)->first();
        if($eventRecurring) {
            $linkedStaff = encodeDecodeJson($eventRecurring->event_linked_staff);
            $caseLinkeSaved = $linkedStaff->where('is_linked', 'yes')->pluck('user_id')->toArray();
            $caseLinkeSavedAttending = $linkedStaff->where("is_linked","yes")->where('attending','yes')->pluck('user_id')->toArray();
            // $caseNonLinkeSaved = $linkedStaff->where("is_linked","no")->pluck('user_id')->toArray();
            // $caseNonLinkeSavedAttending = $linkedStaff->where("is_linked","no")->where('attending','yes')->pluck('user_id')->toArray();

            $linkedContact = encodeDecodeJson($eventRecurring->event_linked_contact_lead);
            $caseLinkeSavedAttendingLead = $linkedContact->where('attending','yes')->pluck('lead_id')->toArray();
            $caseLinkeSavedInviteLead = $linkedContact->where('invite','yes')->pluck('lead_id')->toArray();           
            $from="edit";
        }
       
        return view('case.event.loadLeadRightSection',compact('caseCllientSelection','loadFirmUser','case_id'/* ,'caseLinkedStaffList' */,'caseLinkeSaved','caseLinkeSavedAttending','from','caseLinkeSavedAttendingLead','caseLinkeSavedInviteLead'));     
        exit;    
   } 
   public function closeCase(Request $request)
   {
       $case_id=$request->case_id;
       $caseCllientSelection = $this->getAllLinkedClients($case_id);

       $caseStat = DB::table('view_case_state')->select("*")->where("id",$case_id)->first();

       return view('case.closeCase',compact('caseCllientSelection','case_id','caseStat'));
   }
   public function ProcessCloseCase(Request $request)
   {
        $validator = \Validator::make($request->all(), [
            'case_id' => 'required|numeric'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{    
            $caseMaster=CaseMaster::find($request->case_id);
            if(isset($request->case_close_date)){
               $cd=date('Y-m-d h:i:s',strtotime($request->case_close_date));
            }else{
                $cd=date('Y-m-d h:i:s');
            }
            $caseMaster->case_close_date=$cd;
            $caseMaster->save();

            //Delete linked events
            if(isset($request->archive_appts) && $request->archive_appts=="on"){
                $caseEVentData=CaseEvent::where("case_id", $request->case_id)->first();
                if(!empty( $caseEVentData)){
                    CaseEvent::where("case_id", $request->case_id)->delete();
                    //Master Event History
                    $data=[];
                    $data['event_for_case']=$request->case_id;
                    $data['event_id']=$CaseEvent->id;
                    $data['event_name']=$CaseEvent->event_title;
                    $data['user_id']=Auth::User()->id;
                    $data['activity']='deleted event';
                    $data['type']='event';
                    $data['action']='delete';
                    $CommonController= new CommonController();
                    $CommonController->addMultipleHistory($data);
                }
            }

            //Archive the client/contact 
            if(isset($request->clientRow) && !empty($request->clientRow)){
                foreach($request->clientRow as $k=>$v){
                    $user=User::find($v);
                    $user->user_status=4;  
                    $user->save();
                }
            }

            $data=[];
            $data['case_id']=$caseMaster->id;
            $data['activity']='closed case';
            $data['type']='case';
            $data['action']='close';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            session(['popup_success' => 'Case has been updated.']);

            return response()->json(['errors'=>'','id'=>'']);
            exit;
        }
   }

   public function ReopenClosedCase(Request $request)
   {
        $validator = \Validator::make($request->all(), [
            'case_id' => 'required|numeric'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{    
            $caseMaster=CaseMaster::find($request->case_id);
            $caseMaster->case_close_date=NULL;
            $caseMaster->save();

            $data=[];
            $data['case_id']=$caseMaster->id;
            $data['activity']='reopened case';
            $data['type']='case';
            $data['action']='reopen';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            session(['popup_success' => 'Case has been updated.']);
            return response()->json(['errors'=>'','id'=>'', 'url' => route('court_cases') ]);
            exit;
        }
   }

   public function DeleteClosedCase(Request $request)
   {
        $validator = \Validator::make($request->all(), [
            'case_id' => 'required|numeric'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{   
            $case_id=$request->case_id;
            Task::where("case_id", $case_id)->delete();
            TaskTimeEntry::where("case_id", $case_id)->delete();
            ExpenseEntry::where("case_id", $case_id)->delete();
            CaseNotes::where("case_id", $case_id)->delete();
            Invoices::where("case_id", $case_id)->delete();
            CaseEvent::where("case_id", $case_id)->delete();
            Messages::where("case_id", $case_id)->delete();
            CaseMaster::where("id", $case_id)->delete();


            //if current zero case available then popup field enabled :: Start
            $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
            $getChildUsers=$this->getParentAndChildUserIds();
            $case = $case->whereIn("case_master.created_by",$getChildUsers);
            $case=$case->count();
            if($case<=0){
                $userMaster = User::find(Auth::User()->id);
                $userMaster->popup_after_first_case="yes";
                $userMaster->save();       
            }
            //if current zero case available then popup field enabled :: End

            $data=[];
            $data['case_id']=$request->case_id;
            $data['activity']='deleted case';
            $data['type']='case';
            $data['action']='delete';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            session(['popup_success' => 'Case has been deleted.']);
            return response()->json(['errors'=>'', 'url' => route('court_cases')]);
            exit;
        }
   }

   public function addNotes(Request $request)
   {
       $case_id=$request->case_id;
       DB::table("client_notes")->where("note_date",NULL)->where("note_subject",NULL)->where("notes",NULL)->delete();
       $caseMaster=CaseMaster::find($case_id);
       $LeadNotes = new ClientNotes; 
       $LeadNotes->case_id=$case_id;
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
       return view('case.addNote',compact('caseMaster','case_id','note_id'));
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
               $data['case_id']=NULL;
               $data['notes_for_case']=$request->case_id;
               $data['user_id']=Auth::User()->id;
               $data['activity']='added a note';
               $data['type']='notes';
               $data['action']='add';
               
               $CommonController= new CommonController();
               $CommonController->addMultipleHistory($data);

               //Activity tab
               $data=[];
               $data['activity_title']='added note';
               $data['case_id']=$request->case_id;
               $data['activity_type']='';
               $this->caseActivity($data);

               session(['popup_success' => 'Your note has been created']);
           }else{
               session(['popup_success' => 'Your draft has been autosaved']);
           }
           return response()->json(['errors'=>'','id'=>$request->case_id,'note_id'=>$LeadNotes->id]);
           exit;
       }
       
   }
   public function loadTaskPortion(Request $request)
   {
        $case_id=$request->case_id;
        $task = Task::join("users","task.created_by","=","users.id")
        ->leftjoin("case_master","task.case_id","=","case_master.id")
        ->select('task.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","user_type");
        if($case_id!=""){
            $task = $task->where("task.case_id",$case_id);
        }
        if(isset($request->status) && $request->status=="incomplete"){
            $task = $task->where("status","0");
        }else if(isset($request->status) && $request->status=="complete"){
            $task = $task->where("status","1");
        }else if(isset($request->print_task_range_from)){
            $task = $task->orWhereBetween("task_due_on",[date('Y-m-d',strtotime($request->print_task_range_from)),date('Y-m-d',strtotime($request->print_task_range_to))]);
            if(isset($request->include_without_due_date)){
                $task = $task->Where("task_due_on",'9999-12-30');
            }     
        }        
        $task = $task->where("task.created_by",Auth::user()->id);
        $task = $task->orderBy('task_due_on', 'ASC');
        $task = $task->paginate(10);
        
        return view('case.view.taskDynamic',compact('task','request'));     
        exit;    
    }

    public function loadRate(Request $request)
   {
        $case_id=$request->case_id;
        $staff_id=$request->staff_id;
        $rateUsers = CaseStaff::select("*")->where("case_id",$case_id)->where("user_id",$staff_id)->first();
        $rateType='';
        // if(!empty($rateUsers) && $rateUsers['rate_type']=="0"){
        //     $defaultRate = User::select("*")->where("id",$rateUsers['user_id'])->first();
        //     $default_rate=($defaultRate['default_rate'])??0.00;
        //     $rateType="default";
        // }else{
        //     $default_rate=($rateUsers['rate_amount'])??0.00;
        //     $rateType="case";
        // }
        $default_rate=($rateUsers['rate_amount'])??0.00;
        $rateType="case";
        return view('case.view.loadRateBox',compact('default_rate','rateType','case_id','staff_id'));     
        exit;    
    }

    public function saveCaseRate(Request $request)
   {
       $validator = \Validator::make($request->all(), [
           'staff_id' => 'required',
           'case_id' => 'required',
           'default_rate' => 'required'
       ]);
       if($validator->fails())
       {
           return response()->json(['errors'=>$validator->errors()->all()]);
       }else{
            $rate_type= ($request->rate_type == 'case') ? "1" : "0";
            CaseStaff::where('user_id',$request->staff_id)->where('case_id',$request->case_id)
                ->update(['rate_amount'=>str_replace(",","",$request->default_rate), 'rate_type' => $rate_type]);
            // if($rate_type=="case"){
            //     CaseStaff::where('user_id',$request->staff_id)->where('case_id',$request->case_id)
            //     ->update(['rate_amount'=>str_replace(",","",$request->default_rate)]);
            // }else{
            //     $defaultRate = User::find($request->staff_id);
            //     $defaultRate->default_rate=str_replace(",","",$request->default_rate);
            //     $defaultRate->save();
            // }
           return response()->json(['errors'=>'']);
           exit;
       }
       
   }

   public function loadCaseTimeline(Request $request)
   {   
       $case_id=$request->case_id;
       $CaseMaster=CaseMaster::find($case_id);

       $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
       $getChildUsers[]=Auth::user()->id;
       
       $caseStageList = CaseStage::select("*")->where("status","1")->whereIn("created_by",$getChildUsers)->orderBy('stage_order','ASC')->get();

       $CaseStageHistory=[];
       if($CaseMaster->case_status!=0){
        $CaseStageHistory=CaseStageUpdate::select("*")->where("case_id",$case_id)->where("stage_id",$CaseMaster->case_status)->first();
       }       

       $AllCaseStageHistory=CaseStageUpdate::select("*")->where("case_id",$case_id)->orderBy('start_date','ASC')->get()->toArray();
       
       return view('case.loadCaseTimeline',compact('CaseMaster','caseStageList','CaseStageHistory','AllCaseStageHistory','case_id'));
   }

   public function saveCaseHistory(Request $request)
   {   
    //    dd($request->all());
        $validator = \Validator::make($request->all(), [
            'start_date.*' => 'required',
            'case_id' => 'required',
            'end_date.*' => 'required'
        ],['start_date.*.required'=>"Start date is required field.",'end_date.*.required'=>"End date is required field."]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $errors = $errorIndex = [];          
            if(isset($request->state_id) && !empty($request->state_id)){
                foreach($request->state_id as $k=>$v){
                    // echo $k."- [5782] >  start_date -> ".$request->start_date[$k]."end_date -> ".$request->end_date[$k];
                    // echo "<br>";
                    if(strtotime($request->start_date[$k]) > strtotime($request->end_date[$k])) 
                    {
                        $errors[$k] = $request->end_date[$k].' End date must be come after start date '.$request->start_date[$k];                    
                    }      
                   
                    if(isset($request->start_date[$k+1])){
                        // echo ($k+1)." - [5789] > start_date -> ".$request->start_date[$k+1]."end_date -> ".$request->end_date[$k];
                        // echo "<br>";
                        if(strtotime($request->end_date[$k]) > strtotime($request->start_date[$k+1])){
                            array_push($errorIndex, ($k+1));
                            array_push($errorIndex, ($k+2));
                            // echo 'Wrong Date selection of next to index of '.($k+1);  
                            // echo "<br>";
                            // $errors[$k] = 'Row number '.($k+1).' have conflicting start/end dates';
                        }
                    }
                }
                if(count($errorIndex) > 0){
                    $errors['99'] = 'Row number '.implode(' and ', $errorIndex).' have conflicting start/end dates';
                }
            }
            // if(isset($request->case_status) && !empty($request->case_status)){
            //     $CaseMaster=CaseMaster::find($request->case_id);
            //     if($CaseMaster->case_status > 0){
            //         foreach($request->case_status as $k=>$v){
            //             if($CaseMaster->case_status == $request->case_status[$k]){ 
            //                 $errors[$k] = 'The Current Stage is not used again in index of '.($k+1);                    
            //             }
            //         }
            //     }
            // }       
            // if(isset($request->state_id) && !empty($request->state_id)){    
            //     $arrayCount = max($request->state_id);
            //     for ($i=0; $i <= $arrayCount; $i++) { 

            //         if(isset($request->state_id[$i])){
            //             $errors[$i] = 'Start Date:'. $request->start_date[$i] .'- End Date:'.$request->end_date[$i];
            //         }else{                        
            //             if($i > 0){
            //                 $errors[$i] = 'nostage > Start Date:'. $request->end_date[$i-1] .'- End Date:'.$request->start_date[$i+1];
            //             }
            //         }
            //     }          
            // }
            // $errors['2'] = max($request->state_id);
            if(empty($errors)){
                $CaseStageUpdate=CaseStageUpdate::where("case_id",$request->case_id)->forceDelete();
                $new_array = array();
                if(isset($request->state_id) && !empty($request->state_id)){    
                    $arrayCount = max($request->state_id);
                    for ($i=0; $i <= $arrayCount; $i++) { 
                        if(isset($request->state_id[$i])){
                            $start = strtotime($request->start_date[$i]);
                            $end = strtotime($request->end_date[$i]);
                            $days_between = abs($end - $start) / 86400;

                            $caseStageHistory = new CaseStageUpdate;
                            $caseStageHistory->stage_id=$request->case_status[$i];
                            $caseStageHistory->case_id=$request->case_id;
                            $caseStageHistory->created_by=Auth::user()->id; 
                            $caseStageHistory->start_date = convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date[$i])))), auth()->user()->user_timezone ?? 'UTC'); 
                            $caseStageHistory->end_date = convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->end_date[$i])))), auth()->user()->user_timezone ?? 'UTC'); 
                            $caseStageHistory->days = ($days_between>0.99) ? ceil($days_between) : 0.5;
                            $caseStageHistory->save();
                        
                            if(isset($request->start_date[$i+1])){
                                if($request->end_date[$i] != $request->start_date[$i+1]){
                                    
                                    $start = strtotime($request->end_date[$i]);
                                    $end = strtotime($request->start_date[$i+1]);
                                    $days_between = abs($end - $start) / 86400;

                                    $caseStageHistory = new CaseStageUpdate;
                                    $caseStageHistory->stage_id=0;
                                    $caseStageHistory->case_id=$request->case_id;
                                    $caseStageHistory->created_by=Auth::user()->id; 
                                    $caseStageHistory->start_date = convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->end_date[$i])))), auth()->user()->user_timezone ?? 'UTC'); 
                                    $caseStageHistory->end_date = convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date[$i+1])))), auth()->user()->user_timezone ?? 'UTC'); 
                                    $caseStageHistory->days = ($days_between>0.99) ? ceil($days_between) : 0.5;
                                    $caseStageHistory->save();
                                }
                            }     
                                                
                        }else{
                            if($i > 0){
                                $start = strtotime($request->end_date[$i-1]);
                                $end = strtotime($request->start_date[$i+1]);
                                $days_between = abs($end - $start) / 86400;

                                $caseStageHistory = new CaseStageUpdate;
                                $caseStageHistory->stage_id=0;
                                $caseStageHistory->case_id=$request->case_id;
                                $caseStageHistory->created_by=Auth::user()->id; 
                                $caseStageHistory->start_date = convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->end_date[$i-1])))), auth()->user()->user_timezone ?? 'UTC'); 
                                $caseStageHistory->end_date = convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date[$i+1])))), auth()->user()->user_timezone ?? 'UTC'); 
                                $caseStageHistory->days = ($days_between>0.99) ? ceil($days_between) : 0.5;
                                $caseStageHistory->save();
                            }
                        }
                    }
                }           
                
                session(['popup_success' => 'Case stage timeline history has been successfully saved.']);
                return response()->json(['errors'=>'']);
                exit;
            }else{
                return response()->json(['errors'=>$errors]);
                exit;
            }        
        }
    }

    //Hide the popup when page reload or second time it will open
   public function dismissCaseModal(Request $request)
   {
       $userMaster = User::find(Auth::User()->id);
       $userMaster->popup_after_first_case="no";
       $userMaster->save();        
   } 

   public function addIntakeForm(Request $request)
    {
        $case_id=$request->case_id;
        $firmData=Firm::find(Auth::User()->firm_name);
        $IntakeForm=IntakeForm::where("firm_name",Auth::User()->firm_name)->where("form_type","0")->get();
        $clientList= CaseClientSelection::leftJoin('users','users.id','=','case_client_selection.selected_user')->where("case_id",$case_id)->get();

        return view('case.view.addIntakeForm',compact('IntakeForm','firmData','clientList','case_id'));

    }
    public function saveIntakeForm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'intake_form' => 'required',
            'email_address' => 'required|email'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            
            $CaseIntakeForm = new CaseIntakeForm;
            $CaseIntakeForm->intake_form_id=$request->intake_form; 
            $CaseIntakeForm->form_unique_id=md5(time());
            if($request->current_submit=="savenow"){
                $CaseIntakeForm->status="0";
            }else{
                $CaseIntakeForm->status="3";
            }
          
            $CaseIntakeForm->submited_at=date('Y-m-d h:i:s');
            $CaseIntakeForm->firm_id=Auth::user()->firm_name;
            $CaseIntakeForm->submited_to=$request->email_address;
            $CaseIntakeForm->lead_id=NULL;
            $CaseIntakeForm->case_id=$request->case_id;
            $CaseIntakeForm->client_id=$request->contact;
            $CaseIntakeForm->unique_token=$this->generateUniqueToken();
            $CaseIntakeForm->created_by=Auth::user()->id; 
            $CaseIntakeForm->save();

            $form_id=$request->intake_form;
            $intakeForm=IntakeForm::where("id",$form_id)->first();
            
            $getTemplateData = EmailTemplate::find(7);
            $email=$request->email;
            $token=url('cform', $CaseIntakeForm->form_unique_id);
            $mail_body = $getTemplateData->content;
            $mail_body = str_replace('{message}', $request->email_message, $mail_body);
            $mail_body = str_replace('{email}', $email,$mail_body);
            $mail_body = str_replace('{token}', $token,$mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
            $mail_body = str_replace('{regards}', REGARDS, $mail_body);
            $mail_body = str_replace('{year}', date('Y'), $mail_body);        

            $user = [
                "from" => FROM_EMAIL,
                "from_title" => FROM_EMAIL_TITLE,
                "subject" => $request->email_subject,
                "to" => $request->email_address,
                "full_name" => "",
                "mail_body" => $mail_body
            ];
            if($request->current_submit=="savenow"){
                $sendEmail = $this->sendMail($user);
            }
            session(['popup_success' => 'Added intake form.']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }   

    public function loadIntakeForms()
    {   
        $requestData= $_REQUEST;
        $allForms = CaseIntakeForm::leftJoin('intake_form','intake_form.id','=','case_intake_form.intake_form_id');
        $allForms = $allForms->select("intake_form.id as intake_form_id","case_intake_form.created_at as case_intake_form_created_at","intake_form.*","case_intake_form.*","intake_form.deleted_at as intake_form_deleted");      
        $allForms = $allForms->where("case_id",$requestData['case_id']);  
        $totalData=$allForms->count();
        $totalFiltered = $totalData; 
        
        $allForms = $allForms->offset($requestData['start'])->limit($requestData['length']);
        $allForms = $allForms->orderBy('case_intake_form.created_at','DESC');
        $allForms = $allForms->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $allForms 
        );
        echo json_encode($json_data);  
    }

    public function popupOpenSendEmailIntakeFormFromList(Request $request)
    {
        $formId=$request->form_id;
        $caseIntakeForm=CaseIntakeForm::where("intake_form_id",$formId)->where("case_id",$request->case_id)->first();
        $intakeForm=IntakeForm::where("id",$formId)->first();
        $firmData=Firm::find(Auth::User()->firm_name);
        return view('case.view.emailIntakeForm',compact('intakeForm','formId','firmData','caseIntakeForm'));

    }

    public function sendEmailIntakeFormCase(Request $request)
    {
        
        $validator = \Validator::make($request->all(), [
            'email_address' => 'required|email'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                
            $form_id=$request->form_id;
            $CaseIntakeForm =CaseIntakeForm::find($form_id);
            $CaseIntakeForm->status="0";
            $CaseIntakeForm->save();
            $CaseIntakeForm=CaseIntakeForm::where("id",$form_id)->first();

            $getTemplateData = EmailTemplate::find(7);
            $fullName=$request->first_name. ' ' .$request->last_name;
            $email=$request->email;
            $token=url('cform', $CaseIntakeForm->form_unique_id);
            $mail_body = $getTemplateData->content;
            $mail_body = str_replace('{message}', $request->email_message, $mail_body);
            $mail_body = str_replace('{email}', $email,$mail_body);
            $mail_body = str_replace('{token}', $token,$mail_body);
            $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
            $mail_body = str_replace('{regards}', REGARDS, $mail_body);
            $mail_body = str_replace('{year}', date('Y'), $mail_body);        

            $user = [
                "from" => FROM_EMAIL,
                "from_title" => FROM_EMAIL_TITLE,
                "subject" => $request->email_suubject,
                "to" => $request->email_address,
                "full_name" => "",
                "mail_body" => $mail_body
            ];
            $sendEmail = $this->sendMail($user);
            session(['popup_success' => 'Email sent successfully.']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    }
    //Open popup for Remove intake form from the list 
    public function deleteIntakeFormFromList(Request $request)
    {
        $intakeForm=IntakeForm::where("id",$request->id)->first();
        $primary_id=$request->primary_id;
        return view('case.view.deleteIntakeform',compact('intakeForm','primary_id'));
    } 

    //Remove intake form from the list 
    public function saveDeleteIntakeFormFromList(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
                
            $id=$request->id;
            CaseIntakeForm::where("id", $id)->delete();
            session(['popup_success' => 'Successfully deleted intake form.']);
            return response()->json(['errors'=>'']);
            exit;   
        }
    } 
    
    //Download intake form pdf from url EG: http://bne.poderjudicialvirtual.com/court_cases/609E6ED496F02/intake_forms
    public function downloadIntakeForm(Request $request)
    {
        $id=$request->id;
        $caseIntakeForm=CaseIntakeForm::where("id",$id)->first();
        $intakeForm=IntakeForm::where("id",$caseIntakeForm['intake_form_id'])->first();
        $intakeFormFields=IntakeFormFields::where("intake_form_id",$caseIntakeForm['intake_form_id'])->orderBy("sort_order","ASC")->get();
        $firmData=Firm::find(Auth::User()->firm_name);
        $country = Countries::get();
        $alreadyFilldedData=CaseIntakeFormFieldsData::where("intake_form_id",$intakeForm->id)->first();

        $search=array(' ',':');
        $filename=str_replace($search,"_",$intakeForm['form_name'])."_".time().'.pdf';
        $PDFData=view('case.view.intakeFormPDF',compact('intakeForm','country','firmData','alreadyFilldedData','intakeFormFields'));
        $pdf = new Pdf;
        if($_SERVER['SERVER_NAME']=='localhost'){
            $pdf->binary = EXE_PATH;
        }
        $pdf->addPage($PDFData);
        $pdf->setOptions(['javascript-delay' => 5000]);
        $pdf->setOptions(["footer-right"=> "Page [page] from [topage]"]);
        $pdf->setOptions(["footer-left"=> "Completed on ". date('m/d/Y',strtotime($caseIntakeForm['submited_at']))]);
        $pdf->saveAs(public_path("download_intakeform/pdf/".$filename));
        $path = public_path("download_intakeform/pdf/".$filename);
        // return response()->download($path);
        // exit;

        return response()->json([ 'success' => true, "url"=>url('public/download_intakeform/pdf/'.$filename),"file_name"=>$filename], 200);
        exit;
    }


    
  public function addCaseReminderPopup(Request $request)
  {
      $case_id=$request->case_id;
      $CaseSolReminder = CaseSolReminder::where("case_id",$case_id)->get();
      return view('case.loadSolReminderPopup',compact('case_id','CaseSolReminder'));     
      exit;    
  }

  public function saveCaseReminderPopup(Request $request)
  {
        $request=$request->all();
    //   print_r($request);exit;
        $case_id=$request['case_id'];
        $CaseMaster = CaseMaster::find($case_id);
        CaseSolReminder::where("case_id", $case_id)->delete();
        for($i=0;$i<count($request['reminder_type'])-1;$i++){
            $CaseSolReminder = new CaseSolReminder;
            $CaseSolReminder->case_id=$case_id; 
            $CaseSolReminder->reminder_type=$request['reminder_type'][$i];
            $CaseSolReminder->reminer_number=$request['reminder_days'][$i];
            $CaseSolReminder->created_by=Auth::user()->id;                  
            $reminderDate = \Carbon\Carbon::createFromFormat('Y-m-d', $CaseMaster->case_statute_date)->subDay($request['reminder_days'][$i])->format('Y-m-d'); // Subtracts reminder date day for case_statute_date 
            $CaseSolReminder->remind_at=$reminderDate;   
            $CaseSolReminder->save();
        }
        return response()->json(['errors'=>'']);
        exit;
    }

    public function saveSolStatus(Request $request)
    {
        
        $caseMaster=CaseMaster::find($request->case_id);
        $caseMaster->sol_satisfied=$request->type;
        $caseMaster->save();
        return response()->json(['errors'=>'']);
        exit;
      }

    public function saveTypeOfCase(Request $request)
    {

        // print_r($request->all());exit;
        // echo $request->Newtitle;
        if(isset($request['Newtitle']) && $request['Newtitle']==""){
            $validator = \Validator::make($request->all(), [
                'Newtitle' => 'required'
            ],["Newtitle.required"=>"1Event type name cannot be empty"]);
        }else{
            $validator = \Validator::make($request->all(), [
                'title.*' => 'required'
            ],["title.*.required"=>"Event type name cannot be empty"]);
        }
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $sr=1;
            foreach($request->title as $k=>$v){
              
                $EventType=EventType::find($k);
                $EventType->color_code=$request->Ccode[$k];
                $EventType->title=$v;
                $EventType->status_order=$sr;
                $EventType->save();
                $sr++;
            }

            if(isset($request['Newtitle']) && $request['Newtitle']!=""){
                $EventType=new EventType;
                $EventType->color_code=$request->NewCcode;
                $EventType->title=$request->Newtitle;
                $EventType->firm_id=Auth::user()->firm_name; 
                $EventType->status_order=EventType::where('firm_id',Auth::User()->firm_name)->max('status_order') + 1;
                $EventType->created_by=Auth::user()->id; 
                $EventType->save();
            }

            if(isset($request['del']) && $request['del']!=""){
                $ids=explode(",",$request['del']);
                EventType::whereIn("id",$ids)->delete();
                CaseEvent::whereIn("event_type",$ids)
                ->update([ 'event_type'=>NULL]);

            }
            return response()->json(['errors'=>'']);
            exit;   
        }
    } 
    public function saveEventPrefernace(Request $request)
    {
        $user=User::find(Auth::User()->id);
        $user->event_type_preferance=$request->prefrance;
        $user->save();
        return response()->json(['errors'=>'','message'=>"Preference set."]);
        exit;
    }

    public function checkCaseNameExists(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'case_name' => 'required|unique:case_master,case_title,NULL,id,firm_id,'.Auth::User()->firm_name
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            return response()->json(['errors'=>'']);
        }
    }

    public function loadMessagesEntry(Request $request){
        $columns = array('id', 'sender_name', 'subject', 'updated_at');
        $requestData= $_REQUEST;
        
        $messages = Messages::leftJoin("users","users.id","=","messages.created_by")
        ->leftJoin("case_master","case_master.id","=","messages.case_id")
        ->select('messages.*', DB::raw('CONCAT_WS("- ",messages.subject,messages.message) as subject'), "messages.updated_at as last_post", DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as sender_name'),"case_master.case_title");
        if(isset($requestData['case_id']) && $requestData['case_id']!=''){
            $ContractUser = User::where("id",Auth::user()->id)->first();
            $userPermissions = $ContractUser->getPermissionNames()->toArray();
            // dd($userPermissions);
            if((in_array('access_all_messages', $userPermissions))){
                $messages = $messages->where(function($messages){
                    $messages = $messages->orWhere("messages.created_by",Auth::user()->parent_user);
                    $messages = $messages->orWhere("messages.user_id",'like', '%'.Auth::user()->id.'%');
                    $messages = $messages->orWhere("messages.created_by",Auth::user()->id);
                });
            }else{
                $messages = $messages->where(function($messages){
                    $messages = $messages->orWhere("messages.user_id",'like', '%'.Auth::user()->id.'%');
                    $messages = $messages->orWhere("messages.created_by",Auth::user()->id);
                });                
            }
            $messages = $messages->where("messages.case_id",$requestData['case_id']);
        }
        if(isset($requestData['user_id']) && $requestData['user_id']!=''){
            // $messages = $messages->where("messages.user_id",'like', '%'.$requestData['user_id'].'%');
            $messages = $messages->where(function($messages) use ($requestData){
                $messages = $messages->orWhere("messages.user_id",'like', '%'.$requestData['user_id'].'%');
                $messages = $messages->orWhere("messages.created_by",$requestData['user_id']);
            });
        }else{
            $messages = $messages->where("messages.is_draft",0);
        }
        $messages = $messages->where("messages.firm_id",Auth::User()->firm_name);
        $totalData=$messages->count();
        $totalFiltered = $totalData; 

        $messages = $messages->offset($requestData['start'])->limit($requestData['length']);
        // if(!isset($requestData['order'][0]['dir'])){
        //     $requestData['order'][0]['dir']="DESC";
        // }
        // $messages = $messages->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $messages = $messages->orderBy("messages.updated_at", 'desc');
        $messages = $messages->get();

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $messages 
        );
        echo json_encode($json_data);  
    }

    /**
     * load default firm reminders for client
     */
    public function loadFirmDefaultReminder()
    {
        $defaultReminder = FirmEventReminder::where("firm_id", auth()->user()->firm_name)->get();
        return response()->json(['default_reminder' => $defaultReminder]);
    }

    /**
     * load default event reminders for login user
     */
    public function loadDefaultEventReminder(Request $request){
        $UserPreferanceReminder = UserPreferanceReminder::where("user_id",Auth::User()->id)->where("type","event")->get();
        return view('case.event.loadDefaultEventReminder',compact('UserPreferanceReminder'));
        exit; 
    }

}
  
