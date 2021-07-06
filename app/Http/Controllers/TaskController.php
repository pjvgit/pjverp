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
use App\Task,App\CaseTaskLinkedStaff,App\TaskChecklist;
use App\TaskReminder,App\TaskActivity,App\TaskTimeEntry,App\TaskComment;
use App\TaskHistory,App\LeadAdditionalInfo;
use App\FirmAddress;
class TaskController extends BaseController
{
    public function __construct()
    {
       
    }
    public function index()
    {
        $CaseMaster = Task::latest()->get();
        $country = Countries::get();
        $getChildUsers=$this->getParentAndChildUserIds();
        $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
      
        $getChildUsers=$this->getParentAndChildUserIds();
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();          

        $CaseLeadAttorney = CaseStaff::join('users','users.id','=','case_staff.lead_attorney')->select("users.id","users.first_name","users.last_name",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'))->groupBy('case_staff.lead_attorney')->get();
       
        
        $columns = array('id');
        $requestData= $_REQUEST;
        
        $task = Task::join("users","task.created_by","=","users.id")
        ->leftjoin("case_master","task.case_id","=","case_master.id")
        ->select('task.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
        
        $totalData=$task->count();
        $totalFiltered = $totalData; 
        if( !empty($requestData['search']['value']) ) {   
            $task = $task->where( function($q) use ($requestData){
                $q->where( function($select) use ($requestData){
                    $select->orWhere( DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%".$requestData['search']['value']."%");
                    $select->orWhere('email', 'like', "%".$requestData['search']['value']."%" );
                });
            });
        }
        if( !empty($requestData['search']['value']) ) { 
            $totalFiltered = $task->count(); 
        }

         //Filter applied for task status
         if(isset($_GET['ts']) && $_GET['ts']!='all'){
            $task = $task->where("status",$_GET['ts']);
        }
       
         //Filter applied on user assign column
         if(isset($_GET['at']) && $_GET['at']!=''){
            if($_GET['at']=="me"){
                $taskAssigneME=CaseTaskLinkedStaff::select('task_id')->where("user_id",Auth::User()->id)->get()->pluck('task_id');
                $task = $task->whereIn("task.id",$taskAssigneME);
            }elseif($_GET['at']=="everyoneelse"){
                $taskAssigneOther=CaseTaskLinkedStaff::select('task_id')->where("user_id","!=",Auth::User()->id)->get()->pluck('task_id');
                $task = $task->whereIn("task.id",$taskAssigneOther);
            }elseif($_GET['at']=="allfirmuser"){

            }else{
                $taskAssigneTouser=CaseTaskLinkedStaff::select('task_id')->where("user_id",$_GET['at'])->get()->pluck('task_id');
                $task = $task->whereIn("task.id",$taskAssigneTouser);
            }
        }

        //Filter applied on case/lead column
        if(isset($_GET['cl']) && $_GET['cl']!=''){
            $task = $task->where("task.case_id",$_GET['cl']);
        }
        
        
        //Filter applied on due on column
        if(isset($_GET['daterange']) && $_GET['daterange']!='' && isset($_GET['filter_type']) && $_GET['filter_type']!='All Days' ){
            $t=explode("-",$_GET['daterange']);
            $startDate=date('Y-m-d',strtotime($t[0]));
            $endDate=date('Y-m-d',strtotime($t[1]));
            $task = $task->whereBetween('task_due_on', [$startDate, $endDate]);
        }
        
         //If Parent user logged in then show all child case to parent
         if(Auth::user()->parent_user=="0"){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $task = $task->whereIn("task.created_by",$getChildUsers);
        }else{
            $task = $task->where("task.created_by",Auth::user()->id);
        }
 
        if(isset($_GET['sort'])){
            $task = $task->orderBy($_GET['sort_on'], $_GET['sort']);
        }else{
            $task = $task->orderBy('task_due_on', 'desc');
        }

        //Filter applied on read task only checkbox
        if(isset($_GET['task_read']) && $_GET['task_read']!=''){
            $task = $task->where("task.task_read",'yes');
        }
        $task = $task->paginate(25);
        // $task = $task->orderBy('task_due_on', 'ASC')->groupBy('task_due_on')->get();
     
        $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->where("id","!=",Auth::user()->id)->get();
        $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        

        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
        $getChildUsers=$this->getParentAndChildUserIds();
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();  
        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
        $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
        $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        $getChildUsers[]=Auth::user()->id;
        $getChildUsers[]="0"; //This 0 mean default category need to load in each user
        $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_level","3")->get();
        // return view('case.loadStep1',compact('CaseMasterClient','CaseMasterCompany','user_id','practiceAreaList','caseStageList','selectdUSerList','loadFirmUser'));
        $firmAddress = FirmAddress::select("firm_address.*","countries.name as countryname")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->orderBy('firm_address.is_primary','ASC')->get();
    

        return view('task.index',compact('task','CaseMaster','country','practiceAreaList','caseStageList','CaseLeadAttorney','loadFirmStaff','CaseMasterData','CaseMasterClient','CaseMasterCompany',/* 'user_id', */'practiceAreaList','caseStageList','selectdUSerList','loadFirmUser','firmAddress'));
    }

    public function loadTask()
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
        }
        //Load only own created case
        if(isset($requestData['mc']) && $requestData['mc']!=''){
            $case = $case->where("case_master.created_by",Auth::user()->id); 
        }else{
            //If Parent user logged in then show all child case to parent
            if(Auth::user()->parent_user==0){
                $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
                $getChildUsers[]=Auth::user()->id;
                $case = $case->whereIn("case_master.created_by",$getChildUsers);
            }else{
                $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
                $case = $case->whereIn("case_master.id",$childUSersCase);
                
                
            }

        }


        //    $case = $case->where("case_master.created_by",Auth::user()->id);
     
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
        $case = $case->offset($requestData['start'])->limit($requestData['length']);
        $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $case = $case->get()->paginate(5);
        // $json_data = array(
        //     "draw"            => intval( $requestData['draw'] ),   
        //     "recordsTotal"    => intval( $totalData ),  
        //     "recordsFiltered" => intval( $totalFiltered ), 
        //     "data"            => $case 
        // );
        // echo json_encode($json_data);  
    }

    public function loadAddTaskPopup(Request $request)
    {
        $case_id=$request->case_id;
        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
        }

        /* $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')
                            ->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")
                            ->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")
                            ->where("lead_additional_info.user_status", 1)->get(); */
        $caseLeadList = userLeadList();

        $country = Countries::get();
        $eventLocation = CaseEventLocation::get();
        $currentDateTime=$this->getCurrentDateAndTime();
         //Get event type 
         $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
         return view('task.loadAddTaskPopup',compact('CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType','case_id','caseLeadList'));          
    }
    public function loadCaseLinkedStaffForTask(Request $request)
      {
          $from=$request->from;
          $case_id=$request->case_id;
          $caseLinkedStaffList = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get();
        
          $caseLinkeSaved=array();
          $caseLinkeSavedAttending=array();
          if(isset($request->task_id) && $request->task_id!=''){
            $caseLinkeSaved = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","yes")->where("task_linked_staff.task_id",$request->task_id)->get()->pluck('user_id');
            $caseLinkeSaved= $caseLinkeSaved->toArray();

            $caseLinkeSavedAttending = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","yes")->where("task_linked_staff.task_id",$request->task_id)->get()->pluck('user_id');
            $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();
          }
          return view('task.caseLinkedStaff',compact('caseLinkedStaffList','caseLinkeSaved','from','caseLinkeSavedAttending'));     
          exit;    
     }
     public function loadCaseNoneLinkedStaffForTask(Request $request)
      {
            $from=$request->from;
          $case_id=$request->case_id;
          $caseLinkedStaffList = CaseStaff::select("case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get()->pluck('case_staff_user_id');

          $loadFirmUser = User::select("first_name","last_name","id","parent_user")->whereIn("parent_user",[Auth::user()->id,"0"])->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->whereNotIn('id',$caseLinkedStaffList)->get();
       
          $caseLinkeSaved=array();
          $caseLinkeSavedAttending=array();
          if(isset($request->task_id) && $request->task_id!=''){
            $caseLinkeSavedAttending = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","no")->where("task_linked_staff.task_id",$request->task_id)->get()->pluck('user_id');
            $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();
          }
          $task_id=$request->task_id;

          return view('task.caseNoneLinkedStaff',compact('loadFirmUser','caseLinkeSavedAttending','from','task_id'));     
          exit;    
     }

     public function loadCaseClientAndLeadsForTask(Request $request)
     {
         $case_id=$request->case_id;
         $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id")->where("case_client_selection.case_id",$case_id)->get();
         
         return view('task.caseClientLeadSection',compact('caseCllientSelection'));     
         exit;    
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

    public function remomeSelectedUser(Request $request)
    {
        $firstCheck=TempUserSelection::where("selected_user",$request->selectdValue)->where("user_id",Auth::user()->id)->delete();
        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
        return view('case.showSelectdUser',compact('selectdUSerList'));
    }
    public function saveAddTaskPopup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'task_name' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $TaskMaster = new Task;
           
            // if(!isset($request->no_case_link)){
            //     if(isset($request->case_or_lead)) { $TaskMaster->case_id=$request->case_or_lead; } 
            //     $TaskMaster->no_case_link="yes";
            // }else{
            //     $TaskMaster->no_case_link="no";
            // }

            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { 
                    if($request->text_case_id!=''){
                        $TaskMaster->case_id=$request->text_case_id; 
                    }    
                    if($request->text_lead_id!=''){
                        $TaskMaster->lead_id=$request->text_lead_id; 
                    }    
                } 
                $TaskMaster->no_case_link="yes";
            }else{
                $TaskMaster->no_case_link="no";
            }
            if(isset($request->task_name)) { $TaskMaster->task_title=$request->task_name; }else{ $TaskMaster->task_title=NULL; }
            if(isset($request->due_date) && $request->due_date!="") { $TaskMaster->task_due_on=date('Y-m-d',strtotime($request->due_date)); }else { $TaskMaster->task_due_on= "9999-12-30";}
            if(isset($request->status)) { $TaskMaster->case_status=$request->case_status; }
            if(isset($request->event_frequency)) { $TaskMaster->task_priority=$request->event_frequency; }else{$TaskMaster->task_priority=NULL;}
            if(isset($request->description)) { $TaskMaster->description=$request->description; }else{ $TaskMaster->description=NULL; }
            if(isset($request->time_tracking_enabled)) { $TaskMaster->time_tracking_enabled='yes'; }else{ $TaskMaster->time_tracking_enabled='no'; }
            $TaskMaster->created_by=Auth::User()->id; 
            $TaskMaster->firm_id = auth()->user()->firm_name; 
            $TaskMaster->save();

            $this->saveTaskReminder($request->all(),$TaskMaster->id); 
            $this->saveLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveNonLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveTaskChecklist($request->all(),$TaskMaster->id); 

            $taskHistory=[];
            $taskHistory['task_id']=$TaskMaster->id;
            $taskHistory['task_action']='Created task';
            $taskHistory['created_by']=Auth::User()->id;
            $taskHistory['created_at']=date('Y-m-d H:i:s');
            $this->taskHistory($taskHistory);
            

            //Master history
            $data=[];
            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { 
                    if($request->text_case_id!=''){
                        $data['task_for_case']=$request->text_case_id;
                    }    
                    if($request->text_lead_id!=''){
                        $data['task_for_lead']=$request->text_lead_id; ;
                    }    
                } 
            }
            $data['task_id']=$TaskMaster->id;
            $data['task_name']=$TaskMaster->task_title;
            $data['user_id']=Auth::User()->id;
            $data['activity']='added a task';
            $data['type']='task';
            $data['action']='add';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
            
            return response()->json(['errors'=>'','user_id'=>$request->user_id]);
          exit;
        }
    }

    public function saveTaskReminder($request,$task_id)
    {
        TaskReminder::where("task_id", $task_id)->where("created_by", Auth::user()->id)->delete();

       for($i=0;$i<count($request['reminder_user_type'])-1;$i++){
           $CaseTaskReminder = new TaskReminder;
           $CaseTaskReminder->task_id=$task_id; 
           $CaseTaskReminder->reminder_type=$request['reminder_type'][$i];
           $CaseTaskReminder->reminer_number=$request['reminder_number'][$i];
           $CaseTaskReminder->reminder_frequncy=$request['reminder_time_unit'][$i];
           $CaseTaskReminder->reminder_user_type=$request['reminder_user_type'][$i];
           $CaseTaskReminder->created_by=Auth::user()->id; 
           $CaseTaskReminder->remind_at=Carbon::now(); 
           $CaseTaskReminder->save();
       }
   }

   public function saveNonLinkedStaffToTask($request,$task_id)
   {
       if(isset($request['share_checkbox_nonlinked'])){
        for($i=0;$i<count($request['share_checkbox_nonlinked']);$i++){
                $CaseTaskLinkedStaff = new CaseTaskLinkedStaff;
                $CaseTaskLinkedStaff->task_id=$task_id; 
                $CaseTaskLinkedStaff->user_id=$request['share_checkbox_nonlinked'][$i];
                if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                    $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$request['share_checkbox_nonlinked'][$i]];
                }else{
                    $CaseTaskLinkedStaff->time_estimate_total="0";
                }

                $CaseTaskLinkedStaff->linked_or_not_with_case="no";
                $CaseTaskLinkedStaff->created_by=Auth::user()->id; 
                $CaseTaskLinkedStaff->save();
            }
        }
  }
   public function saveLinkedStaffToTask($request,$task_id)
   {
       CaseTaskLinkedStaff::where("task_id", $task_id)->where("created_by", Auth::user()->id)->delete();
       if(isset($request['linked_staff_checked_attend'])){
        for($i=0;$i<count($request['linked_staff_checked_attend']);$i++){
                $CaseTaskLinkedStaff = new CaseTaskLinkedStaff;
                $CaseTaskLinkedStaff->task_id=$task_id; 
                $CaseTaskLinkedStaff->user_id=$request['linked_staff_checked_attend'][$i];
                // $CaseTaskLinkedStaff->time_estimate_total="0";
                if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                    $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$request['linked_staff_checked_attend'][$i]];
                }else{
                    $CaseTaskLinkedStaff->time_estimate_total="0";
                }
                $CaseTaskLinkedStaff->linked_or_not_with_case="yes";
            
                $CaseTaskLinkedStaff->created_by=Auth::user()->id; 
                $CaseTaskLinkedStaff->save();
            }
        }
  }

  public function saveTaskChecklist($request,$task_id)
  {
        TaskChecklist::where("task_id", $task_id)->where("created_by", Auth::user()->id)->delete();
        $orderValue=1;
        if(isset($request['checklist-item-name'])){
                for($i=0;$i<count($request['checklist-item-name'])-1;$i++){
                $TaskChecklist = new TaskChecklist;
                $TaskChecklist->task_id=$task_id; 
                $TaskChecklist->checklist_order=$orderValue; 
                $TaskChecklist->status="0"; 
                $TaskChecklist->title=$request['checklist-item-name'][$i];
                $TaskChecklist->created_by=Auth::user()->id; 
                if($request['checklist-item-name'][$i]!=''){
                    $TaskChecklist->save(); //Could not store empty checklist
                }
                $orderValue++;
            }
        }
 }
    public function hideTaskGuide(Request $request)
    {
        $userMaster = User::find(Auth::User()->id);
        $userMaster->add_task_guide="1";
        $userMaster->save();        
    }

    public function loadAllStaffMember(Request $request)
    {

          $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy("id","ASC")->get();
            $task_id = $request->task_id;
          $SavedStaff=$from='';
            if(isset($request->edit)){
            $SavedStaff=CaseTaskLinkedStaff::select('user_id')->where("task_id", $request->task_id)->orderBy("user_id","ASC")->get()->pluck('user_id')->toArray();
            $from='edit';  
            }
          return view('task.firmStaff',compact('loadFirmStaff','SavedStaff','from','task_id'));     
          exit;    
     }
     public function loadTimeEstimationUsersList(Request $request)
      {
            $fillsedHours=[];
            $userList=json_decode($request->userList, TRUE);
            $userListWithHours=json_decode($request->arrayList, TRUE);

            if(isset($userList)){
                $loadFirmStaff = User::select("first_name","last_name","id")->whereIn("id",$userList)->orderBy("id","ASC")->get();                
                if($request->edit=="edit"){
                    $fillsedHours=CaseTaskLinkedStaff::select('time_estimate_total',"user_id")->where("task_id", $request->task_id)->orderBy("user_id","ASC")->get();
                    foreach($fillsedHours as $k=>$v){
                        $fillsedHours[$v->user_id]=$v->time_estimate_total;
                    }
                    if(isset($userListWithHours) && !empty($userListWithHours)){
                        foreach($userListWithHours as $k=>$v){
                            $fillsedHours[$v['id']]=$v['hour'];
                        } 
                    }
                }else{
                    //This code is for show old added hours    
                    if(isset($userListWithHours) && !empty($userListWithHours)){
                        foreach($userListWithHours as $k=>$v){
                            $fillsedHours[$v['id']]=$v['hour'];
                        } 
                    }
                }
            }else{
                $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy("id","ASC")->get();
            }

            return view('task.loadTimeEstimationUsersList',compact('loadFirmStaff','fillsedHours'));     
            exit;    
     }
     public function loadTimeEstimationCaseWiseUsersList(Request $request)
     {
         if(isset($request->userList)){
            $userList=array_unique(json_decode($request->userList, TRUE));

            // $loadFirmStaff = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$request->case_id)->whereIn("users.id",$userList)->get();
            $loadFirmStaff = User::select("users.*")->whereIn("users.id",$userList)->orderBy("id","ASC")->get();
         }else{
            // $loadFirmStaff = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$request->case_id)->get();
            $loadFirmStaff = CaseTaskLinkedStaff::join('users','users.id','=','task_linked_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title")->select("users.*")->where("task_linked_staff.task_id",$request->task_id)->orderBy("user_id","ASC")->get();

        }

        $fillsedHours='';
        if($request->edit=="edit"){
           $fillsedHours=CaseTaskLinkedStaff::select('time_estimate_total',"user_id")->where("task_id", $request->task_id)->get();
            foreach($fillsedHours as $k=>$v){
                $fillsedHours[$v->user_id]=$v->time_estimate_total;
            }
        }
         return view('task.loadTimeEstimationUsersList',compact('loadFirmStaff','fillsedHours'));     
         exit;    
    }

    public function deleteTask(Request $request)
    {
        $id=$request->task_id;
        $taskData=Task::find($id);
        //Master history
        $data=[];
        if($taskData['case_id']!=NULL) { 
            $data['task_for_case']=$taskData['case_id'];  
        }   
        if($taskData['lead_id']!=NULL) { 
            $data['task_for_lead']=$taskData['lead_id'];  
        } 
        $data['task_id']=$taskData['id'];
        $data['task_name']=$taskData['task_title'];
        $data['user_id']=Auth::User()->id;
        $data['activity']='deleted a task';
        $data['type']='task';
        $data['action']='delete';
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);

        Task::where("id", $id)->delete();
        session(['popup_success' => 'Task deleted successfully.']);

        return response()->json(['errors'=>'','id'=>$id]);
        exit;    
    }

    public function taskStatus(Request $request)
    {      
        $data=[];
        $taskHistory=[];
        $Task = Task::find($request->task_id);
        if($request->status=="0"){
            $Task->status="1";
            $taskHistory['task_id']=$Task->id;
            $taskHistory['task_action']='Completed task';

            if($Task['case_id']!=NULL) { 
                $data['task_for_case']=$Task['case_id'];  
            }   
            if($Task['lead_id']!=NULL) { 
                $data['task_for_lead']=$Task['lead_id'];  
            } 
            $data['task_id']=$Task['id'];
            $data['task_name']=$Task['task_title'];
            $data['user_id']=Auth::User()->id;
            $data['activity']='completed task';
            $data['type']='task';
            $data['action']='complete';
        }else{
            $Task->status="0";
            $taskHistory['task_id']=$Task->id;
            $taskHistory['task_action']='Marked task as incomplete';

            if($Task['case_id']!=NULL) { 
                $data['task_for_case']=$Task['case_id'];  
            }   
            if($Task['lead_id']!=NULL) { 
                $data['task_for_lead']=$Task['lead_id'];  
            } 
            $data['task_id']=$Task['id'];
            $data['task_name']=$Task['task_title'];
            $data['user_id']=Auth::User()->id;
            $data['activity']='marked as incomplete task';
            $data['type']='task';
            $data['action']='incomplete';
        }
        $Task->task_completed_by=Auth::User()->id;
        $Task->task_completed_date=date('Y-m-d h:i:s');
        $Task->save();

        
        $taskHistory['created_by']=Auth::User()->id;
        $taskHistory['created_at']=$Task->task_completed_date;
        $this->taskHistory($taskHistory);
        
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);


        return response()->json(['errors'=>'','id'=>$Task->id]);
        exit;    
    }


    public function loadEditTaskPopup(Request $request)
    {
        $task_id=$request->task_id;
        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        // $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $CaseMasterData = CaseMaster::whereIn("case_master.created_by",$getChildUsers)->where('is_entry_done',"1")->get();
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $CaseMasterData = CaseMaster::whereIn("case_master.id",$childUSersCase)->where('is_entry_done',"1")->get();
        }
        $Task = Task::find($request->task_id);
        $TaskChecklist = TaskChecklist::select("*")->where("task_id",$task_id)->orderBy('checklist_order','ASC')->get();
        $taskReminderData = TaskReminder::select("*")->where("task_id",$task_id)->get();
        $from_view="no";
        if(isset($request->from_view) && $request->from_view=='yes'){
            $from_view="yes";
        }
        // $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->where("lead_additional_info.user_status", 1)->get();
        $caseLeadList = userLeadList();

         return view('task.loadEditTaskPopup',compact('caseLeadList','CaseMasterClient','CaseMasterData','task_id','Task','TaskChecklist','taskReminderData','from_view'));          
    }
    public function loadStatus(Request $request)
    {        
      $getChildUsers=$this->getParentAndChildUserIds();
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();          

        $CaseMaster = CaseMaster::where("id",$request->case_id)->get();
        return view('case.changeStatus',compact('CaseMaster','caseStageList'));
    }

    public function saveEditTaskPopup(Request $request)
    {
     
    //    print_r($request->all());exit;
        $validator = \Validator::make($request->all(), [
            'task_name' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $TaskMaster =Task::find($request->task_id);
           
            // if(!isset($request->no_case_link)){
            //     if(isset($request->case_or_lead)) { $TaskMaster->case_id=$request->case_or_lead; } 
            //     $TaskMaster->no_case_link="yes";
            // }else{
            //     $TaskMaster->no_case_link="no";
            // }
            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { 
                    if($request->text_case_id!=''){
                        $TaskMaster->case_id=$request->text_case_id; 
                        $TaskMaster->lead_id=NULL; 
                    }else if($request->text_lead_id!=''){
                        $TaskMaster->case_id=NULL; 
                        $TaskMaster->lead_id=$request->text_lead_id; 
                    }    
                } 
                $TaskMaster->no_case_link="yes";
            }else{
                $TaskMaster->no_case_link="no";
            }
                
            if(isset($request->task_name)) { $TaskMaster->task_title=$request->task_name; }else{ $TaskMaster->task_title=NULL; }
            if(isset($request->due_date) && $request->due_date!="") { $TaskMaster->task_due_on=date('Y-m-d',strtotime($request->due_date)); }else { $TaskMaster->task_due_on= "9999-12-30";}
            if(isset($request->status)) { $TaskMaster->case_status=$request->case_status; }
            if(isset($request->event_frequency)) { $TaskMaster->task_priority=$request->event_frequency; }else{$TaskMaster->task_priority=NULL;}
            if(isset($request->description)) { $TaskMaster->description=$request->description; }else{ $TaskMaster->description=NULL; }
            if(isset($request->time_tracking_enabled) && $request->time_tracking_enabled=="on") { $TaskMaster->time_tracking_enabled='yes'; }else{ $TaskMaster->time_tracking_enabled='no'; }
            $TaskMaster->updated_by=Auth::User()->id; 
            $TaskMaster->firm_id = auth()->user()->firm_name; 
            $TaskMaster->save();

            $taskHistory=[];
            $taskHistory['task_id']=$TaskMaster->id;
            $taskHistory['task_action']='Updated task';
            $taskHistory['created_by']=Auth::User()->id;
            $taskHistory['created_at']=date('Y-m-d H:i:s');
            $this->taskHistory($taskHistory);

            $data=[];
            if($TaskMaster->case_id!=NULL) { 
                $data['task_for_case']=$TaskMaster->case_id;  
            }   
            if($TaskMaster->lead_id!=NULL) { 
                $data['task_for_lead']=$TaskMaster->lead_id;  
            } 
            $data['task_id']=$TaskMaster->id;
            $data['task_name']=$TaskMaster->task_title;
            $data['user_id']=Auth::User()->id;
            $data['activity']='updated a task';
            $data['type']='task';
            $data['action']='update';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            $this->saveEditTaskReminder($request->all(),$TaskMaster->id); 
            $this->saveEditLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveNonLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveEditTaskChecklist($request->all(),$TaskMaster->id); 
            if($request->from_view=="yes"){
                Session::put('task_id', $request->task_id);
            }
            
            return response()->json(['errors'=>'','user_id'=>$request->user_id]);
          exit;
        }
    }

    public function saveEditTaskChecklist($request,$task_id)
    {
          $orderValue=1;
          $finalDataList=array();
          if(isset($request['checklist-item-name'])){
                foreach($request['checklist-item-name'] as $k=>$v){
                $TaskChecklist =TaskChecklist::where("id",$k)->where("task_id", $task_id)->count();
                if($TaskChecklist=="0"){
                    $TaskChecklist = new TaskChecklist;
                    $TaskChecklist->task_id=$task_id; 
                    $TaskChecklist->checklist_order=$orderValue; 
                    $TaskChecklist->status="0"; 
                    $TaskChecklist->title=$request['checklist-item-name'][$k];
                    $TaskChecklist->created_by=Auth::user()->id; 
                    if($request['checklist-item-name'][$k]!=''){
                        $TaskChecklist->save(); //Could not store empty checklist
                        $finalDataList[]=$TaskChecklist->id;    
                    }
                }else{
                    $TaskChecklist = TaskChecklist::find($k);
                    $TaskChecklist->checklist_order=$orderValue; 
                    $TaskChecklist->title=$request['checklist-item-name'][$k];
                    $TaskChecklist->updated_by=Auth::user()->id; 
                    if($request['checklist-item-name'][$k]!=''){
                        $TaskChecklist->save(); //Could not store empty checklist
                        $finalDataList[]=$TaskChecklist->id;    
                    }  
                }
                $orderValue++;
            }
            $ids=TaskChecklist::select("*")->whereIn("id",$finalDataList)->get()->pluck('id');
            TaskChecklist::where("task_id", $task_id)->whereNotIn("id",$ids)->delete();
        }
   }
    public function saveEditTaskReminder($request,$task_id)
    {
      
        TaskReminder::where("task_id", $task_id)->where("created_by", Auth::user()->id)->forceDelete();
        for($i=0;$i<count($request['reminder_user_type'])-1;$i++){
           $CaseTaskReminder = new TaskReminder;
           $CaseTaskReminder->task_id=$task_id; 
           $CaseTaskReminder->reminder_type=$request['reminder_type'][$i];
           $CaseTaskReminder->reminer_number=$request['reminder_number'][$i];
           $CaseTaskReminder->reminder_frequncy=$request['reminder_time_unit'][$i];
           $CaseTaskReminder->reminder_user_type=$request['reminder_user_type'][$i];
           $CaseTaskReminder->created_by=Auth::user()->id; 
           $CaseTaskReminder->remind_at=Carbon::now(); 
           $CaseTaskReminder->save();
       }
   }
   public function saveEditLinkedStaffToTask($request,$task_id)
   {
        $orderValue=1;
        $finalDataList=array();
        if(isset($request['linked_staff_checked_attend'])){
            foreach($request['linked_staff_checked_attend'] as $k=>$v){
                $CaseTaskLinkedStaff =CaseTaskLinkedStaff::where("user_id",$v)->where("task_id", $task_id)->count();
                if($CaseTaskLinkedStaff=="0"){
                    $CaseTaskLinkedStaff = new CaseTaskLinkedStaff;
                    $CaseTaskLinkedStaff->task_id=$task_id; 
                    $CaseTaskLinkedStaff->user_id=$v; 
                    if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                        $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$v];
                    }else{
                        $CaseTaskLinkedStaff->time_estimate_total="0";
                    }
                    $CaseTaskLinkedStaff->linked_or_not_with_case="yes";
                    $CaseTaskLinkedStaff->created_by=Auth::user()->id; 
                    $CaseTaskLinkedStaff->save();
                    $finalDataList[]=$CaseTaskLinkedStaff->id;
                }else{
                    $CaseTaskLinkedStaffCheck =CaseTaskLinkedStaff::select("*")->where("user_id",$v)->where("task_id", $task_id)->first();
                    if(!empty($CaseTaskLinkedStaffCheck)){
                        $CaseTaskLinkedStaff = CaseTaskLinkedStaff::find($CaseTaskLinkedStaffCheck->id);
                        $CaseTaskLinkedStaff->task_id=$task_id; 
                        $CaseTaskLinkedStaff->user_id=$v;
                        if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                            $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$v];
                        }else{
                            $CaseTaskLinkedStaff->time_estimate_total="0";
                        }
                        $CaseTaskLinkedStaff->linked_or_not_with_case="yes";
                        $CaseTaskLinkedStaff->updated_by=Auth::user()->id; 
                        $CaseTaskLinkedStaff->save();
                        $finalDataList[]=$CaseTaskLinkedStaffCheck->id;
                    }
                  
                }
            }
        }
        $pluckIds =CaseTaskLinkedStaff::select("*")->where("task_id", $task_id)->whereIn("id",$finalDataList)->get()->pluck("id");
        CaseTaskLinkedStaff::where("task_id", $task_id)->whereNotIn("id",$pluckIds)->delete();
   }


  public function loadTaskReminderPopupIndex(Request $request)
  {
      $task_id=$request->task_id;
      $TaskReminder = TaskReminder::where("task_id",$task_id)->get();
      $from_view="no";
      if(isset($request->from_view) && $request->from_view=='yes'){
          $from_view="yes";
      }
      return view('task.loadReminderPopupIndex',compact('task_id','TaskReminder','from_view'));     
      exit;    
  }
  public function loadReminderPopupIndexDontRefresh(Request $request)
  {
      $task_id=$request->task_id;
      $TaskReminder = TaskReminder::where("task_id",$task_id)->get();
      $from_view="no";
      if(isset($request->from_view) && $request->from_view=='yes'){
          $from_view="yes";
      }
      return view('task.loadReminderPopupIndexDontRefresh',compact('task_id','TaskReminder','from_view'));     
      exit;    
  }
  public function loadReminderArea(Request $request)
  {
      $task_id=$request->task_id;
      $TaskReminders=TaskReminder::leftJoin("users","task_reminder.created_by","=","users.id")
      ->select("task_reminder.*",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'))
      ->where("task_id", $task_id)->get();
      return view('task.loadReminderArea',compact('task_id','TaskReminders'));     
      exit;    
  }
  public function saveTaskReminderPopup(Request $request)
  {
        $ses='';
        $task_id=$request->task_id;
        $this->saveEditTaskReminder($request,$task_id);
        if($request->from_view=="yes"){
            $ses=Session::put('task_id', $request->task_id);
        }
        return response()->json(['errors'=>'','msg'=>'Reminders successfully updated','setSession'=>$ses]);
        exit;    
    }

  
  public function loadTimeEntryPopup(Request $request)
  {
        $task_id=$request->task_id;
        $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->orderBy('first_name','DESC')->get();

        $case_id="";

        $TaskActivity=TaskActivity::where('status','1')->get();
        $TaskData=Task::find($task_id);
        $from_view="no";
        if(isset($request->from_view) && $request->from_view=='yes'){
            $from_view="yes";
        }

        $assignedTaskUsersList=CaseTaskLinkedStaff::where("task_id", $task_id)->pluck("user_id")->toArray();
        $selectedId='';
        $defaultRate='';
        $assignedTaskUsersList=CaseTaskLinkedStaff::where("task_id", $task_id)->pluck("user_id")->toArray();
        if(!empty($assignedTaskUsersList)){
            if(!empty($assignedTaskUsersList) && in_array(Auth::User()->id,$assignedTaskUsersList)){
                $selectedId=Auth::User()->id;
                $defaultRate=Auth::User()->default_rate;
            }else{
                $selectedId=$assignedTaskUsersList[0];
                $loadDefaultRateForSelectedUser = User::select("default_rate")->where("id",$selectedId)->first();
                $defaultRate=$loadDefaultRateForSelectedUser->default_rate;
            }
        }
        return view('task.loadTimeEntryPopup',compact('task_id','CaseMasterData','case_id','loadFirmStaff','TaskActivity','TaskData','from_view','assignedTaskUsersList','selectedId','defaultRate'));     
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
        
        $TaskTimeEntry->task_id=$request->task_id;
        $TaskTimeEntry->case_id =$request->case_or_lead;
        $TaskTimeEntry->user_id =$request->staff_user;
        if(isset($request->activity_text)){
            $TaskAvtivity = new TaskActivity;
            $TaskAvtivity->title=$request->activity_text;
            $TaskAvtivity->status="1";
            $TaskAvtivity->created_by=Auth::User()->id; 
            $TaskAvtivity->firm_id=Auth::User()->firm_name;
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
        ; 
        $TaskTimeEntry->save();
        if($request->from_view=="yes"){
            Session::put('task_id', $request->task_id);
        }
       
        
        //Add time entory history
        $data=[];
        $data['case_id']=$TaskTimeEntry->case_id;
        $data['user_id']=$TaskTimeEntry->user_id;
        $data['activity']='added an time entry';
        $data['activity_for']=$TaskTimeEntry->activity_id;
        $data['time_entry_id']=$TaskTimeEntry->id;
        $data['type']='time_entry';
        $data['action']='add';
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);

        if(isset($request->from)){
            $from="timesheet";
        }else{
            $from="";
        }

        return response()->json(['errors'=>'','id'=>$TaskTimeEntry->id,'from'=>$from]);
      exit;
    }
  } 

  public function savebulkTimeEntry(Request $request)
  {
    // return $request->all();
    $validator = \Validator::make($request->all(), [
        'case_or_lead' => 'required',
        'staff_user' => 'required',
    ]);
    if ($validator->fails())
    {
        return response()->json(['errors'=>$validator->errors()->all()]);
    }else{
        
        foreach($request->case_or_lead as $i=>$v){
            if($request->case_or_lead[$i]!='' && $request->activity[$i]!='' && $request->duration[$i]!=''){
                $TaskTimeEntry = new TaskTimeEntry; 
                $TaskTimeEntry->task_id=$request->task_id;
                $TaskTimeEntry->case_id =$request->case_or_lead[$i];
                $TaskTimeEntry->user_id =$request->staff_user;
                $TaskTimeEntry->activity_id=$request->activity[$i];
                if(isset($request->billable[$i]) && $request->billable[$i]=="on"){
                    $TaskTimeEntry->time_entry_billable="yes";
                }else{
                    $TaskTimeEntry->time_entry_billable="no";
                }
                $TaskTimeEntry->description=$request->description[$i];
                $TaskTimeEntry->entry_date=date('Y-m-d',strtotime($request->start_date));

                $rateUsers = CaseStaff::select("*")->where("case_id",$request->case_or_lead[$i])->whereRaw('case_staff.user_id = case_staff.lead_attorney')->first();
                if(!empty($rateUsers) && $rateUsers['rate_type']=="0"){
                    $defaultRate = User::select("*")->where("id",$rateUsers['user_id'])->first();
                    $default_rate=($defaultRate['default_rate'])??0.00;
                }else{
                    $default_rate=($rateUsers['rate_amount'])??0.00;
                }

                $TaskTimeEntry->entry_rate=$default_rate;
                $TaskTimeEntry->rate_type='hr';
                $TaskTimeEntry->duration =$request->duration[$i];
                $TaskTimeEntry->created_by=Auth::User()->id; 
                $TaskTimeEntry->save();
                    
                //Add time entory history
                $data=[];
                $data['case_id']=$TaskTimeEntry->case_id;
                $data['user_id']=$TaskTimeEntry->user_id;
                $data['activity']='added an time entry';
                $data['activity_for']=$TaskTimeEntry->activity_id;
                $data['time_entry_id']=$TaskTimeEntry->id;

                $data['type']='time_entry';
                $data['action']='add';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);
            }
                
        }
        if($request->from_view=="yes"){
            Session::put('task_id', $request->task_id);
        }

        if(isset($request->from)){
            $from="timesheet";
        }else{
            $from="";
        }

        return response()->json(['errors'=>'','id'=>$TaskTimeEntry->id,'from'=>$from]);
      exit;
    }
  } 
  public function markasread()
  {
        Task::where('created_by',Auth::User()->id)
        ->update(['task_read'=>'yes']);
        return redirect('tasks');

      exit;    
  }
  public function bulkMarkAsRead(Request $request)
  {
        $data = json_decode(stripslashes($request->task_id));
        foreach($data as $k=>$v){
            Task::where('id',$v)->update(['task_read'=>'yes']);
        }
        return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
        exit;    
  } 
  public function taskAllReadFromCalender(Request $request)
  {
        CaseEvent::where('created_by',Auth::User()->id)->update(['event_read'=>'yes']);
        session(['popup_success' => 'We\'ve received your request to mark all events as read. It may take a minute to finish processing.']);

        return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
        exit;    
  }   
  public function markAsCompleted(Request $request)
  {
        $data = json_decode(stripslashes($request->task_id));
        foreach($data as $k=>$v){
            Task::where('id',$v)->update(['status'=>'1','task_completed_date'=>date('Y-m-d h:i:s'),'task_completed_by'=>Auth::User()->id]);
        }
        return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
        exit;    
  }
  public function changeDueDate(Request $request)
  {
        $data = json_decode(stripslashes($request->task_id));
        foreach($data as $k=>$v){
            Task::where('id',$v)->update(['task_due_on'=>date('Y-m-d',strtotime($request->duedate))]);
        }
        return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
        exit;    
  }
  public function loadTaskActivity(Request $request)
  {        $TaskActivity=TaskActivity::where('status','1')->get();

    return view('task.taskActivity',compact('TaskActivity'));     
    exit;   
  }
  
  public function getAndCheckDefaultCaseRate(Request $request)
  {
      $case_id=$request->case_id;
      $rateUsers = CaseStaff::select("*")->where("case_id",$case_id)->whereRaw('case_staff.user_id = case_staff.lead_attorney')->first();
      if(!empty($rateUsers) && $rateUsers['rate_type']=="0"){
          $defaultRate = User::select("*")->where("id",$rateUsers['user_id'])->first();
          $default_rate=($defaultRate['default_rate'])??0.00;
          $rate_type=0;
          $drate="user_default";
      }else{
          $default_rate=($rateUsers['rate_amount'])??0.00;
          $rate_type=$rateUsers['rate_type'];
          $drate="case_default";
      }

    return response()->json(['errors'=>'','msg'=>'Records successfully found','data'=>$default_rate]);
    exit;    
  }

  public function loadTaskDetailPage(Request $request)
  {        
    $TaskActivity=TaskActivity::where('status','1')->get();
    $TaskData=Task::find($request->task_id);

    $TaskCreatedBy = Task::join("users","task.created_by","=","users.id")
        ->select('task.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_title")->where('task.id',$request->task_id)->first();

    $TaskAssignedTo = CaseTaskLinkedStaff::join("users","task_linked_staff.user_id","=","users.id")
        ->select(DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_title","task_linked_staff.time_estimate_total")->where('task_linked_staff.task_id',$request->task_id)
       // ->where('task_linked_staff.linked_or_not_with_case','yes')
        ->get();
        $CaseMasterData='';
    if($TaskData->case_id!=''){
        // $CaseMasterData = CaseMaster::find($TaskData->case_id);
    }

    $TaskReminders=TaskReminder::leftJoin("users","task_reminder.created_by","=","users.id")
    ->select("task_reminder.*",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'))
    ->where("task_id", $request->task_id)->get();

    $TaskChecklist = TaskChecklist::select("*")->where("task_id", $request->task_id)->orderBy('checklist_order','ASC')->get();
    $TaskChecklistCompleted = TaskChecklist::select("*")->where("task_id", $request->task_id)->where('status','1')->count();


    $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        $CaseMasterCompany = User::select("first_name","last_name","id","user_level")->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
        $practiceAreaList = CasePracticeArea::where("status","1")->where("firm_id",Auth::User()->firm_name)->get();  
        $getChildUsers=$this->getParentAndChildUserIds();
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();  
        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
        $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
        $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        $getChildUsers[]=Auth::user()->id;
        $getChildUsers[]="0"; //This 0 mean default category need to load in each user
        $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_level","3")->get();
        // return view('case.loadStep1',compact('CaseMasterClient','CaseMasterCompany','user_id','practiceAreaList','caseStageList','selectdUSerList','loadFirmUser'));
        $firmAddress = FirmAddress::select("firm_address.*","countries.name as countryname")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->orderBy('firm_address.is_primary','ASC')->get();
    
    return view('task.taskView',compact('TaskData','CaseMasterData','TaskCreatedBy','TaskAssignedTo','TaskReminders','TaskChecklist','TaskChecklistCompleted','CaseMasterClient','CaseMasterCompany',/* 'user_id', */'practiceAreaList','caseStageList','selectdUSerList','loadFirmUser','firmAddress'));     
    exit;   
  }

  public function loadTaskViewPage(Request $request)
  {        
    $task_id=$request->task_id;
    $TaskActivity=TaskActivity::where('status','1')->get();
    $TaskData=Task::find($request->task_id);

    $TaskCompletedBy = Task::leftJoin("users","task.task_completed_by","=","users.id")
        ->select('task.*',"users.id as uid","users.user_title","users.user_type",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as completed_by_name'))->where('task.id',$request->task_id)->first();
// print_r($TaskCompletedBy);
    $TaskCreatedBy = Task::join("users","task.created_by","=","users.id")
        ->select('task.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_title")->where('task.id',$request->task_id)->first();

    $TaskAssignedTo = CaseTaskLinkedStaff::join("users","task_linked_staff.user_id","=","users.id")
        ->select(DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_title","task_linked_staff.time_estimate_total")->where('task_linked_staff.task_id',$request->task_id)
       // ->where('task_linked_staff.linked_or_not_with_case','yes')
        ->get();
        $CaseMasterData='';
    if($TaskData->case_id!=''){
        $CaseMasterData = CaseMaster::find($TaskData->case_id);
    }

    $TaskReminders=TaskReminder::leftJoin("users","task_reminder.created_by","=","users.id")
    ->select("task_reminder.*",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'))
    ->where("task_id", $request->task_id)->get();

    $TaskChecklist = TaskChecklist::leftJoin('users','users.id','=','task_checklist.updated_by')->select("task_checklist.*","users.first_name","users.last_name","users.id as uid","users.user_type")->where("task_id", $request->task_id)->orderBy('checklist_order','ASC')->get();
    $TaskChecklistCompleted = TaskChecklist::leftJoin('users','users.id','=','task_checklist.updated_by')->select("task_checklist.*","users.first_name","users.last_name","users.id as uid","users.user_type")->where("task_id", $request->task_id)->where('status','1')->count();

    return view('case.taskView',compact('TaskData','CaseMasterData','TaskCreatedBy','TaskAssignedTo','TaskReminders','TaskChecklist','TaskChecklistCompleted','CaseMasterData','TaskCompletedBy','task_id'));     
    exit;   
  }

  public function reloadTaskCounter(Request $request)
  {
        $task_id=$request->task_id;
        $TaskChecklistAll =  TaskChecklist::where("task_id",$task_id)->count();
        $TaskChecklistDone =  TaskChecklist::where("task_id",$task_id)->where("status","1")->count();
        return view('task.reloadTaskCounter',compact('TaskChecklistDone','TaskChecklistAll'));     
        exit;    
  } 
  public function saveTaskComment(Request $request)
  {
        $TaskComment = new TaskComment; 
        $TaskComment->task_id=$request->task_id;
        $TaskComment->title =$request->delta;
        $TaskComment->created_by=Auth::User()->id; 
        $TaskComment->save();
        return response()->json(['errors'=>'','id'=>$TaskComment->id]);
        exit;
  } 
  public function loadTaskComment(Request $request)
  {
        $task_id=$request->task_id;
        $TaskCommentData=TaskComment::leftJoin("users","task_comment.created_by","=","users.id")
        ->select("task_comment.*","users.first_name","users.last_name")
        ->where('task_id',$task_id)->get();
        return view('task.loadTaskComment',compact('TaskCommentData'));     
        exit;    
  } 

  public function loadTaskCommentUpdatedView(Request $request)
  {
        $task_id=$request->task_id;
        $TaskCommentData=TaskComment::leftJoin("users","task_comment.created_by","=","users.id")
        ->select("task_comment.*","users.first_name","users.last_name","users.user_type","users.id as uid")
        ->where('task_id',$task_id)->get();
        return view('task.loadTaskCommentUpdatedView',compact('TaskCommentData'));     
        exit;    
  } 

  public function taskHistory($historyData)
  {
        $TaskHistory = new TaskHistory; 
        $TaskHistory->task_id=$historyData['task_id'];
        $TaskHistory->task_action= $historyData['task_action'];
        $TaskHistory->created_by=$historyData['created_by'];
        $TaskHistory->created_at=$historyData['created_at'];
        $TaskHistory->save();
        return true;
  }
  
  public function loadTaskHistory(Request $request)
  {
        $task_id=$request->task_id;
        $taskHistoryData=TaskHistory::leftJoin("users","task_history.created_by","=","users.id")
        ->select("task_history.*",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.first_name","users.last_name","users.user_type")
        ->where('task_id',$task_id)
        ->orderBy('task_history.id','DESC')->get();
        return view('task.loadTaskHistory',compact('taskHistoryData'));     
        exit;    
  } 

  public function updateCheckList(Request $request)
  {        
      $TaskChecklist = TaskChecklist::find($request->id);
      if($request->status=="0"){
          $TaskChecklist->status="1";
      }else{
          $TaskChecklist->status="0";
      }
      $TaskChecklist->updated_by=Auth::User()->id;
      $TaskChecklist->save();
      return response()->json(['errors'=>'','id'=>$TaskChecklist->id]);
      exit;    
  }

  public function singleTaskMarkAsComplete(Request $request)
  {        
      $TaskChecklist = Task::find($request->task_id);
      if($TaskChecklist->status=="0"){
          $TaskChecklist->status="1";
          $TaskChecklist->task_completed_by=Auth::User()->id;
          $TaskChecklist->task_completed_date=date('Y-m-d');
      }else{
          $TaskChecklist->status="0";
          $TaskChecklist->task_completed_by=NULL;
          $TaskChecklist->task_completed_date=NULL;
      }
      
   
      $TaskChecklist->updated_by=Auth::User()->id;
      $TaskChecklist->save();
      return response()->json(['errors'=>'','id'=>$TaskChecklist->id]);
      exit;    
  }
  public function loadCheckListView(Request $request)
  {
        $task_id=$request->task_id;
        $TaskChecklist = TaskChecklist::select("*")->where("task_id", $task_id)->orderBy('checklist_order','ASC')->get();
        $TaskChecklistCompleted = TaskChecklist::select("*")->where("task_id", $task_id)->where('status','1')->count();
        return view('task.loadCheckListView',compact('TaskChecklist','TaskChecklistCompleted'));     
        exit;    
  } 
  public function loadCheckListViewForTask(Request $request)
  {
        $task_id=$request->task_id;
        $TaskChecklist = TaskChecklist::leftJoin('users','users.id','=','task_checklist.updated_by')->select("task_checklist.*","users.first_name","users.last_name","users.id as uid","users.user_type")->where("task_id", $task_id)->orderBy('checklist_order','ASC')->get();

        $TaskChecklistCompleted = TaskChecklist::leftJoin('users','users.id','=','task_checklist.updated_by')->select("task_checklist.*","users.first_name","users.last_name","users.id as uid","users.user_type")->where("task_id", $task_id)->where('status','1')->count();

        $TaskCompletedBy = Task::leftJoin("users","task.task_completed_by","=","users.id")
        ->select('task.*',"users.id as uid","users.user_title","users.user_type",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as completed_by_name'))->where('task.id',$task_id)->first();

        // print_r($TaskCompletedBy);
        $TaskData=Task::find($task_id);

        $fromList=$request->list;
        if($request->forList=="yes"){
            return view('task.loadCheckListViewForTaskWithoutCheckbox',compact('TaskChecklist','TaskChecklistCompleted','TaskData','TaskCompletedBy','task_id','fromList'));     
        
        }else{
            return view('task.loadCheckListViewForTask',compact('TaskChecklist','TaskChecklistCompleted','TaskData','TaskCompletedBy','task_id','fromList'));     
        }
        exit;    
  } 

  public function loadTaskRightSection(Request $request)
  {       
      $caseLinkeSaved=array();
      $caseLinkedSavedAssigned=array();
      $case_id=$request->case_id;
      $task_id=$request->task_id;
      $caseNonLinkedAssigned=[];
      $from=$request->from;

      //Load Lead And Client
      $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id")->where("case_client_selection.case_id",$case_id)->orderBy('case_client_selection.selected_user',"ASC")->get();

      //Load Non link staff list
      $caseNoneLinkedStaffList = CaseStaff::select("case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get()->pluck('case_staff_user_id');
      $loadFirmUser = User::select("first_name","last_name","id","parent_user")->whereIn("parent_user",[Auth::user()->id,"0"])->where("firm_name",Auth::user()->firm_name)->orderBy('id',"ASC")->groupBy('id')->where("user_level","3")->whereNotIn('id',$caseNoneLinkedStaffList)->get();

     //Load Linked staff
      $caseLinkedStaffList = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->orderBy('case_staff.user_id',"ASC")->groupBy('case_staff.user_id')->get();
    
  
      if(isset($task_id) && $task_id!=''){
     
        $caseLinkedSavedAssigned = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","yes")->where("task_linked_staff.task_id",$request->task_id)->get()->pluck('user_id');
       $caseLinkedSavedAssigned= $caseLinkedSavedAssigned->toArray();
  
       $caseNonLinkedAssigned = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","no")->where("task_linked_staff.task_id",$task_id)->get()->pluck('user_id');
        $caseNonLinkedAssigned= $caseNonLinkedAssigned->toArray();
        $from="edit";

      }
     
      return view('task.loadTaskRightSection',compact('caseCllientSelection','loadFirmUser','from','task_id','caseLinkedStaffList','caseNonLinkedAssigned','caseLinkedSavedAssigned'));     
      exit;    
 }
   

}
  
