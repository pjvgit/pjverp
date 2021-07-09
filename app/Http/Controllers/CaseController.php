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
use App\ViewCaseState,App\ClientNotes,App\CaseTaskLinkedStaff;
use App\ExpenseEntry,App\CaseNotes,App\Firm,App\IntakeForm,App\CaseIntakeForm;
use App\FlatFeeEntry;
use Illuminate\Support\Str;
use App\Jobs\CommentEmail;
use App\Jobs\EventReminderEmailJob;
use Exception;
use Illuminate\Support\Facades\Log;

class CaseController extends BaseController
{
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

        
        $CaseLeadAttorney = CaseStaff::join('users','users.id','=','case_staff.lead_attorney')->select("users.id","users.first_name","users.last_name",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'))->groupBy('case_staff.lead_attorney')->get();

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

        $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
        $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        $getChildUsers[]=Auth::user()->id;
        $getChildUsers[]="0"; //This 0 mean default category need to load in each user
        $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_level","3")->get();
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
        //Load only own created case
        // if(isset($requestData['mc']) && $requestData['mc']!=''){
        //     $case = $case->where("case_master.created_by",Auth::user()->id); 
        // }else{
        //     //If Parent user logged in then show all child case to parent
        //     if(Auth::user()->parent_user==0){
        //         $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        //         $getChildUsers[]=Auth::user()->id;
        //         $case = $case->whereIn("case_master.created_by",$getChildUsers);
        //     }else{
        //         $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
        //         $case = $case->whereIn("case_master.id",$childUSersCase);
        //     }
        // }

        ///Load case base on user type
        // If user type is parent then load all child and own case
        // If user type is staff then load onw case only. 
        if(Auth::user()->parent_user==0){
            $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
            $getChildUsers[]=Auth::user()->id;
            $case = $case->whereIn("case_master.created_by",$getChildUsers);
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
            $case = $case->whereIn("case_master.id",$childUSersCase);
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
        $case = $case->get();
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

        $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
        $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        $getChildUsers[]=Auth::user()->id;
        $getChildUsers[]="0"; //This 0 mean default category need to load in each user
        $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_level","3")->get();
        return view('case.loadStep1',compact('CaseMasterClient','CaseMasterCompany','user_id','practiceAreaList','caseStageList','selectdUSerList','loadFirmUser'));
    }  

    public function saveAllStep(Request $request)
    {

        // return response()->json(['errors'=>'','user_id'=>'','case_unique_number'=>'6045FB829C823']);
        
        if(isset($request->default_rate)) {$request['default_rate']=str_replace(",","",$request->default_rate); }
      
        $validator = \Validator::make($request->all(), [
            'case_name' => 'required|unique:case_master,case_title',
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
                $var = $request->case_open_date;
                $CaseMaster->case_open_date= date('Y-m-d', strtotime($var));
            }

            if(isset($request->case_office)) { $CaseMaster->case_office=$request->case_office; }
            if(isset($request->case_statute)) {
                $var = $request->case_statute;
                $CaseMaster->case_statute_date= date('Y-m-d', strtotime($var));
                $CaseMaster->sol_satisfied="yes";
            }
             if(isset($request->conflict_check)) { 
                $CaseMaster->conflict_check="1"; 
                if(isset($request->conflict_check_description)) { $CaseMaster->conflict_check_description=$request->conflict_check_description; }
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
                    $CaseSolReminder->save();
                }
            }

            //Activity tab
            $data=[];
            $data['activity_title']='added case';
            $data['case_id']=$CaseMaster->id;
            $data['activity_type']='';
            $this->caseActivity($data);

            
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
                    
                    //Activity tab
                    $datauser=[];
                    $datauser['activity_title']='linked client';
                    $datauser['case_id']=$CaseMaster->id;
                    $datauser['staff_id']=$val->selected_user;
                    $this->caseActivity($datauser);
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
    
                }
    
                $caseStatusChange=CaseMaster::find($CaseMaster->id);
                $caseStatusChange->is_entry_done="1";
                $caseStatusChange->save();
    
                $caseStageHistory = new CaseStageUpdate;
                $caseStageHistory->stage_id=($caseStatusChange->case_status)??NULL;
                $caseStageHistory->case_id=$caseStatusChange->id;
                $caseStageHistory->created_by=Auth::user()->id; 
                $caseStageHistory->created_at=$caseStatusChange->case_open_date; 
                $caseStageHistory->save();
    
                if($caseStatusChange->case_statute!=NULL){
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
                $var = $request->case_open_date;
                // $date = str_replace('/', '-', $var);
                $CaseMaster->case_open_date= date('Y-m-d', strtotime($var));
            }

            if(isset($request->case_office)) { $CaseMaster->case_office=$request->case_office; }
            if(isset($request->case_statute)) {
                $var = $request->case_statute;
                // $date = str_replace('/', '-', $var);
                $CaseMaster->case_statute_date= date('Y-m-d', strtotime($var));
            }
             if(isset($request->conflict_check)) { 
                $CaseMaster->conflict_check="1"; 
                if(isset($request->conflict_check_description)) { $CaseMaster->conflict_check_description=$request->conflict_check_description; }
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
                    $CaseSolReminder->save();
                }
            }

            //Activity tab
            $data=[];
            $data['activity_title']='added case';
            $data['case_id']=$CaseMaster->id;
            $data['activity_type']='';
            $this->caseActivity($data);


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
                foreach($selectdUSerList as $key=>$val){
                    $CaseClientSelection = new CaseClientSelection;
                    $CaseClientSelection->case_id=$request->case_id; 
                    $CaseClientSelection->selected_user=$val->selected_user; 
                    $CaseClientSelection->created_by=Auth::user()->id; 
                    if($val->selected_user == $request->billing_contact){
                        $CaseClientSelection->is_billing_contact='yes';
                        if(isset($request->billingMethod)) { $CaseClientSelection->billing_method=$request->billingMethod; }
                        if(isset($request->default_rate)) { $CaseClientSelection->billing_amount=$request->default_rate; }
                    }   
                    $CaseClientSelection->save();
                    
                    //Activity tab
                    $datauser=[];
                    $datauser['activity_title']='linked client';
                    $datauser['case_id']=$request->case_id;
                    $datauser['staff_id']=$val->selected_user;
                    $this->caseActivity($datauser);
                }
            }
            return response()->json(['errors'=>'','case_id'=>$request->case_id]);
            exit;
        }
    }
    
    public function loadStep4(Request $request)
    {
        $case_id=$request->case_id;

        $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
        $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        $getChildUsers[]=Auth::user()->id;
        $getChildUsers[]="0"; //This 0 mean default category need to load in each user
        $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_level","3")->get();
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

            }

            $caseStatusChange=CaseMaster::find($request->case_id);
            $caseStatusChange->is_entry_done="1";
            $caseStatusChange->save();

            $caseStageHistory = new CaseStageUpdate;
            $caseStageHistory->stage_id=($caseStatusChange->case_status)??NULL;
            $caseStageHistory->case_id=$caseStatusChange->id;
            $caseStageHistory->created_by=Auth::user()->id; 
            $caseStageHistory->created_at=$caseStatusChange->case_open_date; 
            $caseStageHistory->save();

            if($caseStatusChange->case_statute!=NULL){
                $this->saveSOLEventIntoCalender($request->case_id);
            }

            
            $s=Session::get('caseLinkToClient');
            if(isset($s))
            {
                $clientId=Session::get('clientId');
                $CaseClientSelection=new CaseClientSelection;
                $CaseClientSelection->case_id=$request->case_id;
                $CaseClientSelection->selected_user=$clientId;
                $CaseClientSelection->save();
           
                $ClientActivityHistory=[];
                $ClientActivityHistory['acrtivity_title']='linked contact';
                $ClientActivityHistory['activity_by']=Auth::User()->id;
                $ClientActivityHistory['activity_for']=($clientId)??NULL;
                $ClientActivityHistory['type']="2";
                $ClientActivityHistory['task_id']=NULL;
                $ClientActivityHistory['case_id']=$request->case_id;
                $ClientActivityHistory['created_by']=Auth::User()->id;
                $ClientActivityHistory['created_at']=date('Y-m-d H:i:s');
                $this->saveClientActivity($ClientActivityHistory);
                return response()->json(['errors'=>'','reload'=>'true']);
                exit;
            }
            $sCompany=Session::get('caseLinkToCompany');
            if(isset($sCompany))
            {
                $companyId=Session::get('companyId');
                $CaseClientSelection=new CaseClientSelection;
                $CaseClientSelection->case_id=$request->case_id;
                $CaseClientSelection->selected_user=$companyId;
                $CaseClientSelection->save();
           
                $ClientActivityHistory=[];
                $ClientActivityHistory['acrtivity_title']='linked contact';
                $ClientActivityHistory['activity_by']=Auth::User()->id;
                $ClientActivityHistory['activity_for']=($companyId)??NULL;
                $ClientActivityHistory['type']="2";
                $ClientActivityHistory['task_id']=NULL;
                $ClientActivityHistory['case_id']=$request->case_id;
                $ClientActivityHistory['created_by']=Auth::User()->id;
                $ClientActivityHistory['created_at']=date('Y-m-d H:i:s');
                $this->saveClientActivity($ClientActivityHistory);
                return response()->json(['errors'=>'','reload'=>'true']);
                exit;
            }
          


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
        $CaseMaster->case_status=$request->case_status;
        $CaseMaster->save();
        
        
        $caseStageHistory = CaseStageUpdate::firstOrNew(array('case_id' => $CaseMaster->id,'stage_id'=>$request->status));
        $caseStageHistory->stage_id=($CaseMaster->case_status)??0;
        $caseStageHistory->case_id=$CaseMaster->id;
        $caseStageHistory->created_by=Auth::user()->id; 
        $caseStageHistory->save();

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
            if(isset($request->case_open_date)) { $CaseMaster->case_open_date=date('Y-m-d',strtotime($request->case_open_date)); }

            if(isset($request->case_office)) { $CaseMaster->case_office=$request->case_office; }
            if(isset($request->case_statute)) { $CaseMaster->case_statute_date=date('Y-m-d',strtotime($request->case_statute)); }else{ $CaseMaster->case_statute_date=NULL;}
            if(isset($request->conflict_check)) { 
                $CaseMaster->conflict_check="1"; 
                if(isset($request->conflict_check_description)) { $CaseMaster->conflict_check_description=$request->conflict_check_description; }
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
                    $CaseSolReminder->save();
                }
            }
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

        $CaseMaster = CaseMaster::join('users','users.id','=','case_master.created_by')->select("case_master.*","case_master.id as case_id","users.id","users.first_name","users.last_name","users.user_level","users.email","case_master.created_at as case_created_date","case_master.created_by as case_created_by")->where("case_unique_number",$request->id)->with('caseOffice')->first();
        if(!empty($CaseMaster)){
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
               
                $caseStatusHistory=CaseStageUpdate::leftJoin('case_stage','case_stage.id','=','case_stage_history.stage_id')->select('case_stage_history.*','case_stage.stage_color')->where("case_stage_history.case_id",$case_id)->orderBy('case_stage_history.created_at','ASC')->get();
                if(!$caseStatusHistory->isEmpty()){
                    $caseStatusHistory=$caseStatusHistory->toArray();
                       
                    foreach($caseStatusHistory as $k=>$v){
                        $fdate = ($caseStatusHistory[$k+1]['created_at'])??date('Y-m-d');
                        $tdate = $caseStatusHistory[$k]['created_at'];
                        $datetime1 = new DateTime($fdate);
                        $datetime2 = new DateTime($tdate);
                        $interval = $datetime1->diff($datetime2);
                        $NoStageDays = $interval->format('%a');
                        if($NoStageDays=="0"){
                            $NoStageDays="1";
                        }
                        $caseStatusHistory[$k]['days']=$NoStageDays;
                        $caseStatusHistory[$k]['color']=$caseStatusHistory[$k]['stage_color'];
                        $caseStatusHistory[$k]['startDate']=$caseStatusHistory[$k]['created_at'];
                        if($k+1>count($caseStatusHistory)-1){   
                            $caseStatusHistory[$k]['endDate']=$caseStatusHistory[$k]['created_at'];
                        }else{   
                            $caseStatusHistory[$k]['endDate']=$caseStatusHistory[$k+1]['created_at'];
                        }
                     
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
                    $eventCountNextDays=CaseEvent::select('id')->where('case_id',$case_id)->where("start_date","<=",date("Y-m-d", strtotime("+365 days")))->count();

                     //Upcoming event list 
                     $upcomingEventList=CaseEvent::select('*')->where('case_id',$case_id)->where("start_date","<=",date("Y-m-d", strtotime("+365 days")))->where("start_date",">=",date("Y-m-d"))->orderBy("start_date","ASC")->limit("4")->get();

                     $startDate=date('Y-m-d');
                     $InvoicesOverdueCase=Invoices::where("invoices.case_id",$case_id)->where('invoices.due_date',"<",$startDate)->count();


            }
            if(\Route::current()->getName()=="tasks"){
            }
            if(\Route::current()->getName()=="status_updates"){
                $allStatus = CaseUpdate::join('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","case_update.update_status","case_update.created_at","case_update.id as case_update_id")->where("case_id",$case_id)->orderBy('created_at','DESC')->get();
               
            } 
            if(\Route::current()->getName()=="calendars"){
               
                //Load only upcoming events
                if(isset($_GET['upcoming_events'])){
                    $allStatus = CaseUpdate::join('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","case_update.update_status","case_update.created_at","case_update.id as case_update_id")->where("case_id",$case_id)->orderBy('created_at','DESC')->get();

                    //Get all event by 
                    $allEvents = CaseEvent::select("*")->where("case_id",$case_id)->where("start_date",">=",date('Y-m-d'))->orderBy('start_date','ASC')->orderBy('start_time','ASC')->get()
                    ->groupBy(function($val) {
                        return Carbon::parse($val->start_date)->format('Y');
                    });
                }else{
                    $allStatus = CaseUpdate::join('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","case_update.update_status","case_update.created_at","case_update.id as case_update_id")->where("case_id",$case_id)->orderBy('created_at','DESC')->get();

                    //Get all event by 
                    $allEvents = CaseEvent::select("*")->where("case_id",$case_id)->orderBy('start_date','ASC')->orderBy('start_time','ASC')
                    ->with("eventLinkedStaff", "eventType")
                    ->get()
                    ->groupBy(function($val) {
                        return Carbon::parse($val->start_date)->format('Y');
                    });
                    /* $allEvents = DB::table('case_events')->select("case_events.*", "et.color_code", "et.title", "users.first_name","users.last_name","users.id as user_id","users.user_type")->where("case_id",$case_id)->orderBy('start_date','ASC')->orderBy('start_time','ASC')
                    ->leftjoin("event_type as et", "case_events.event_type", "=", "et.id")
                    ->leftjoin("case_event_linked_staff as els", "case_events.id", "=", "els.event_id")
                    ->leftJoin('users','users.id','=','els.user_id')
                    ->whereNull('case_events.deleted_at')
                    ->get()
                    ->groupBy(function($val) {
                        return Carbon::parse($val->start_date)->format('Y');
                    }); */
                }
            } 
            if(\Route::current()->getName()=="recent_activity"){
                $mainArray=[];
                $allStatus = CaseActivity::where("case_id",$case_id)->orderBy('case_activity.created_at','DESC')->get();
                foreach($allStatus as $k=>$vv){
                    $caseData=$this->getCaseData($vv->case_id);
                    $createdUSer=$this->getCreatedByUserData($vv->created_by);

                    $mainArray[$k]['id']=$createdUSer->id;
                    $mainArray[$k]['title']=$vv->activity_title;
                    $mainArray[$k]['created_by']=$createdUSer->first_name;
                    $mainArray[$k]['case_name']=$caseData->case_title;

                    $CommonController= new CommonController();
                    $timezone=Auth::User()->user_timezone;
                    $convertedDate=$CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($vv->created_at)),$timezone);
                    $mainArray[$k]['created_at']=date('m-d-Y h:i A',strtotime($convertedDate));

                    $caseCreatedAt=$CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($CaseMaster->case_created_date)),$timezone);
                    $caseCreatedDate=date('m-d-Y h:i A',strtotime($caseCreatedAt));
                    
                }
                // print_r($mainArray);
                // exit;
            }

            
            if(\Route::current()->getName()=="case_link"){
                $allStatus = CaseUpdate::join('users','users.id','=','case_update.created_by')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","case_update.update_status","case_update.created_at","case_update.id as case_update_id","users.is_published")->where("case_id",$case_id)->orderBy('created_at','DESC')->get();
               
            } 
            $caseBiller=[];
            $timeEntryData=$expenseEntryData=$trustUSers=$InvoicesTotal=$InvoicesCollectedTotal=$InvoicesPendingTotal='';
            if(\Route::current()->getName()=="overview"){
                $timeEntryData=$this->getTimeEntryTotalByCase($case_id);    
                $expenseEntryData=$this->getExpenseEntryTotalByCase($case_id);    
                $trustUSers=$this->getTrustBalance($case_id);    
                $InvoicesTotal= Invoices::where("invoices.created_by",Auth::User()->id)->where("case_id",$case_id)->sum("total_amount");
                $InvoicesCollectedTotal= Invoices::where("invoices.created_by",Auth::User()->id)->where("case_id",$case_id)->sum("paid_amount");
                $InvoicesPendingTotal= Invoices::where("invoices.created_by",Auth::User()->id)->where("case_id",$case_id)->sum("due_amount");
                
                $caseBiller = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
                ->leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
                ->select("users.id","users.id as uid","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","case_client_selection.user_role as user_role","contact_group_id","case_client_selection.billing_method","case_client_selection.billing_amount")->where("case_client_selection.case_id",$case_id)->where("is_billing_contact","yes")->first();
            }
            $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
            ->leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
            ->select("users.id","users.id as uid","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","case_client_selection.is_billing_contact","contact_group_id","users.profile_image","users.is_published","multiple_compnay_id")->where("case_client_selection.case_id",$case_id)->get();

            $CompanyList=User::where("user_level","4")->where("parent_user",Auth::User()->id)->pluck("first_name","id");

            $linkedCompany=CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
           ->where("case_client_selection.case_id",$case_id)->where("user_level","4")->get()->pluck("first_name","selected_user");
            
            $totalCalls=$getAllFirmUser='';
            if(\Route::current()->getName()=="communications/calls"){

                $Calls = Calls::select("calls.*",DB::raw('CONCAT(u1.first_name, " ",u1.last_name) as created_name'),DB::raw('CONCAT(u2.first_name, " ",u2.last_name) as caller_full_name'),DB::raw('CONCAT(u3.first_name, " ",u3.last_name) as call_for_name'));
                $Calls = $Calls->leftJoin('users as u1','calls.created_by','=','u1.id');        
                $Calls = $Calls->leftJoin('users as u2','calls.caller_name','=','u2.id');        
                $Calls = $Calls->leftJoin('users as u3','calls.call_for','=','u3.id');        
                $totalCalls=$Calls->count();
                
                $getAllFirmUser=$this->getAllFirmUser();
                
                $getAllFirmUser =  Calls::select("calls.id as cid","u1.id","u1.first_name","u1.last_name","calls.call_for");
                $getAllFirmUser = $getAllFirmUser->leftJoin('users as u1','calls.call_for','=','u1.id')->where("case_id",$case_id)->groupBy("call_for")->get();
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

            //Get total number of case avaulable in system 
            $caseCount = CaseMaster::where("created_by",Auth::User()->id)->where('is_entry_done',"1")->count();
            return view('case.viewCase',compact("CaseMaster","caseCllientSelection","practiceAreaList","caseStageList","leadAttorney","originatingAttorney","staffList","lastStatusUpdate","caseStatusHistory","caseStageListArray","allStatus","mainArray","caseCreatedDate","allEvents","caseCount","taskCountNextDays","taskCompletedCounter","overdueTaskList","upcomingTaskList","eventCountNextDays","upcomingEventList",'timeEntryData','expenseEntryData','trustUSers','InvoicesTotal','InvoicesPendingTotal','InvoicesCollectedTotal','caseBiller','getAllFirmUser','totalCalls','caseStat','InvoicesOverdueCase','totalCaseIntakeForm','linkedCompany','CompanyList'));
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
        $caseMaster->billing_amount=($request->default_rate)??0.00;
        $caseMaster->save();

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
                    $updateBilling->updated_by=Auth::User()->id;
                    $updateBilling->save();
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
        $timeSlot=explode("-",$requestData['time_slot']);
        $from=$timeSlot[0];
        $to=$timeSlot[1];
        $timeTotalBillable=$timeTotalNonBillable=$timeTotalNonBillableHours=$timeTotalBillableHours=$invoiceEntry=$invoiceEntryHours=0;
        $TimeEntryLog=[];
        $TimeEntry=TaskTimeEntry::select("*")->where("case_id",$case_id)->whereBetween("task_time_entry.entry_date",[date('Y-m-d',strtotime($timeSlot[0])),date('Y-m-d',strtotime($timeSlot[1]))])->get();
        foreach($TimeEntry as $TK=>$TE){
            if($TE['rate_type']=="flat"){
                if($TE['time_entry_billable']=="yes"){
                        $timeTotalBillable+=$TE['entry_rate'];
                        $timeTotalBillableHours+=$TE['duration'];
                }else{
                        $timeTotalNonBillable+=$TE['entry_rate'];
                        $timeTotalNonBillableHours+=$TE['duration'];
                }
            }else{
                if($TE['time_entry_billable']=="yes"){
                    $timeTotalBillable+=($TE['entry_rate']*$TE['duration']);
                    $timeTotalBillableHours+=$TE['duration'];
                }else{
                    $timeTotalNonBillable+=($TE['entry_rate']*$TE['duration']);
                    $timeTotalNonBillableHours+=$TE['duration'];

                }
            }

            if($TE['status']=="paid"){
                if($TE['rate_type']=="flat"){
                    if($TE['time_entry_billable']=="yes"){
                            $invoiceEntry+=$TE['entry_rate'];
                            $invoiceEntryHours+=$TE['duration'];
                    }
                }else{
                    if($TE['time_entry_billable']=="yes"){
                        $invoiceEntry+=($TE['entry_rate']*$TE['duration']);
                        $invoiceEntryHours+=$TE['duration'];
                    }
                }
    
            }
        }
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

    public function loadPermissionModel(Request $request)
    {        
        $ContractUser = User::where("id",$request->user_id)->get();
        $ContractUserPermission = ContractUserPermission::where("user_id",$request->user_id)->get();
        $ContractAccessPermission = ContractAccessPermission::where("user_id",$request->user_id)->get();
        return view('contract.loadPermissionModel',compact('ContractUser','ContractUserPermission','ContractAccessPermission'));
    }
    public function savePermissionModel(Request $request)
    {
        
        $userPermission = ContractAccessPermission::firstOrNew(array('user_id' => $request->user_id));
        if(isset($request->user_id)) { $userPermission->user_id=$request->user_id; }
        if(isset($request->clientsPermission)) { $userPermission->clientsPermission=$request->clientsPermission; }
        if(isset($request->leadsPermission)) { $userPermission->leadsPermission=$request->leadsPermission; }
        if(isset($request->casesPermission)) { $userPermission->casesPermission=$request->casesPermission; }
        if(isset($request->eventsPermission)) { $userPermission->eventsPermission=$request->eventsPermission; }
        if(isset($request->documentsPermission)) { $userPermission->documentsPermission=$request->documentsPermission; }
        if(isset($request->commentingPermission)) { $userPermission->commentingPermission=$request->commentingPermission; }
        if(isset($request->textMessagingPermission)) { $userPermission->textMessagingPermission=$request->textMessagingPermission; }
        if(isset($request->messagesPermission)) { $userPermission->messagesPermission=$request->messagesPermission; }
        if(isset($request->billingPermission)) { $userPermission->billingPermission=$request->billingPermission; }
        if(isset($request->reportingPermission)) { $userPermission->reportingPermission=$request->reportingPermission; }
        if(isset($request->allMessagesFirmwide)) { $userPermission->allMessagesFirmwide="1"; }else { $userPermission->allMessagesFirmwide="0"; }
        if(isset($request->restrictBilling)) { $userPermission->restrictBilling="1"; }else { $userPermission->restrictBilling="0"; }
        if(isset($request->financialInsightsPermission)) { $userPermission->financialInsightsPermission="1"; }else { $userPermission->financialInsightsPermission="0"; }
        $userPermission->updated_by =Auth::User()->id;

        $userPermission->save();

        $CurrentUserPermission = ContractUserPermission::firstOrNew(array('user_id' => $request->user_id));
        if(isset($request->user_id)) { $CurrentUserPermission->user_id=$request->user_id; }
        if(isset($request->access_case)) { $CurrentUserPermission->access_case=$request->access_case; }
        if(isset($request->add_new)) { $CurrentUserPermission->add_new=$request->add_new; }
        if(isset($request->edit_permisssion)) { $CurrentUserPermission->edit_permisssion=$request->edit_permisssion; }
        if(isset($request->delete_item)) { $CurrentUserPermission->delete_item=$request->delete_item; }
        if(isset($request->import_export)) { $CurrentUserPermission->import_export=$request->import_export; }
        if(isset($request->custome_fields)) { $CurrentUserPermission->custome_fields=$request->custome_fields; }
        if(isset($request->manage_firm)) { $CurrentUserPermission->manage_firm=$request->manage_firm; }
        $CurrentUserPermission->updated_by =Auth::User()->id;
        $CurrentUserPermission->save();

        return response()->json(['errors'=>'','user_id'=>$request->user_id]);
        exit;
    }

    //Verify user once click on link shared by email.
    public function verifyUser($token)
    {
        $verifyUser = User::where('token', $token)->first();
        if(isset($verifyUser) ){
            if($verifyUser->user_status==1){
                return redirect('login')->with('warning', EMAIL_ALREADY_VERIFIED);
            }else{
                $status = EMAIL_VERIFIED;
                return redirect('setupuserpprofile/'.$token);
            }
        }else{
            return redirect('login')->with('warning', EMAIL_NOT_IDENTIFIED);
        }
    }

     //open set password popup when verify email
     public function setupuserpprofile($token)
     {
        $verifyUser = User::where('token', $token)->first();
        return view('contract.setupprofile',['verifyUser'=>$verifyUser]);

     }   
     
     //open set password popup when verify email
     public function setupusersave(Request $request)
     {
        $request->validate([
            'password' => 'required|min:6|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'required|min:6',
            'user_timezone' => 'required',
        ]);

        $verifyUser =  User::where(["token" => $request->utoken])->first();
    
        if(isset($verifyUser) ){
            $user = $verifyUser;
            User::where('id',$user->id)->update(['password'=>Hash::make(trim($request->password)),
            'user_timezone'=>$request->user_timezone,
            'verified'=>"1",
            'user_status'=>"1"
            ]);

             //Sent welcome email to user.
             $getTemplateData = EmailTemplate::find(4);
             $fullName = $user->first_name . ' ' . $user->last_name;

             $mail_body = $getTemplateData->content;
             $mail_body = str_replace('{name}', $fullName, $mail_body);
             $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
             $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
             $mail_body = str_replace('{regards}', REGARDS, $mail_body);
             $mail_body = str_replace('{year}', date('Y'), $mail_body);     
             $user = [
                 "from" => FROM_EMAIL,
                 "from_title" => FROM_EMAIL_TITLE,
                 "subject" => $getTemplateData->subject,
                 "to" => $user->email,
                 "full_name" => $fullName,
                 "mail_body" => $mail_body
             ];
             $sendEmail = $this->sendMail($user);   
            if (Auth::attempt(['email' => $verifyUser->email, 'password' => $request->password])) {
                $userStatus = Auth::User()->user_status;
                if($userStatus=='1') { 
                    session(['layout' => 'horizontal']);
                    return redirect()->intended('dashboard')->with('success','Login Successfully');
                }else{
                    Auth::logout();
                    Session::flush();
                    return redirect('login')->with('warning', INACTIVE_ACCOUNT);
                }
            }
        }else{
            Auth::logout();
            Session::flush();
            return redirect('login')->with('warning', INACTIVE_ACCOUNT);
        }
     }

     //Send welcome email to user as many times want to send.
     public function SendWelcomeEmail(Request $request)
     {
            $user =  User::where(["id" => $request->user_id])->first();
            $getTemplateData = EmailTemplate::find(6);
             $fullName=$user->first_name. ' ' .$user->last_name;
             $email=$user->email;
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
 
             $userEmail = [
                 "from" => FROM_EMAIL,
                 "from_title" => FROM_EMAIL_TITLE,
                 "subject" => $getTemplateData->subject,
                 "to" => $user->email,
                 "full_name" => $fullName,
                 "mail_body" => $mail_body
                 ];
           $sendEmail = $this->sendMail($userEmail);
           if($sendEmail=="1"){
             $user->is_sent_welcome_email  = "1";  // Welcome email sent to user.
             $user->save();
           }
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
         $case = $case->get();
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
            ->select(DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'),"users.id","user_level")->where("users.user_level","2");
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

            if(!empty($request->client_links)){
                foreach($request->client_links as $k=>$v ){
                    $CaseClientSelection = new CaseClientSelection;
                    $CaseClientSelection->case_id=$request->case_id; 
                    $CaseClientSelection->selected_user=$v; 
                    $CaseClientSelection->created_by=Auth::user()->id; 
                    $CaseClientSelection->save();
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
            CaseStaff::where('case_id',$case_id)->update(['lead_originating_id' => NULL]);
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
        CaseStaff::where("id", $id)->delete();
        session(['popup_success' => 'Unlink '.$request->username.' from case']);
        return response()->json(['errors'=>'','id'=>$id]);
        exit;
    }
   
    public function loadExistingStaff(Request $request)
    {
        $case_id=$request->case_id;
        $caseStaff = User::select("first_name","last_name","id","user_level","user_title","default_rate")->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->get();
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
        $CaseStage->stage_color='#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
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
        $CaseStage->save();
        // session(['popup_success' => 'Stage name has been updated.']);
        return response()->json(['errors'=>''   ]);
        exit;
    }

    public function reloadCaserStages(Request $request)
    {
        $caseStage = CaseStage::select("*")->where("status","1");
        $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        $getChildUsers[]=Auth::user()->id;
        $caseStage = $caseStage->whereIn("created_by",$getChildUsers);          
        $caseStage=$caseStage->orderBy('stage_order','ASC')->get();
        return view('case.reload_case_stages',compact('caseStage'));          
   }

    //Calender tab
      public function loadAddEventPage(Request $request)
      {
          
        $lead_id="";
        if(isset($request->lead_id)){
            $lead_id=$request->lead_id;
        }
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

        $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();


        $country = Countries::get();
        $eventLocation = CaseEventLocation::where("location_future_use","yes")->get();
        $currentDateTime=$this->getCurrentDateAndTime();
         //Get event type 
         $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
        return view('case.event.loadAddEvent',compact('CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType','case_id','caseLeadList','lead_id'));          
     }
      public function saveAddEventPageOld(Request $request)
      {
        
        // return $request->all();

            $CommonController= new CommonController();
            $validator = \Validator::make($request->all(), [
                'linked_staff_checked_share' => 'required'
            ]);
            if($validator->fails())
            {
                return response()->json(['errors'=>['You must share with at least one firm user<br>You must share with at least one user'],]);
            }

           
        //Single event
        if(!isset($request->recuring_event)){
            $startTime = strtotime(date("Y-m-d", strtotime($request->start_date)));
            $endTime = strtotime(date("Y-m-d",strtotime($request->end_date)));
        }else{
            //recurring event
            $startTime = strtotime(date("Y-m-d",  strtotime($request->start_date)));
             $endTime =  strtotime(date('Y-m-d',strtotime('+365 days')));
             if($request->end_on!=''){
                $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
            }
        }
        if(!isset($request->recuring_event)){
            //If new location is creating.
            if($request->location_name!=''){
                $locationID= $this->saveLocationOnce($request);
              }

            $start_date = date("Y-m-d", $startTime);
            $start_time = date("H:i:s", strtotime($CommonController->convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->start_date.' '.$request->start_time)),Auth::User()->user_timezone)));
            $end_date = date("Y-m-d", $endTime);
            $end_time = date("H:i:s", strtotime($CommonController->convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->end_date.' '.$request->end_time)),Auth::User()->user_timezone)));
            $CaseEvent = new CaseEvent;
            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { 
                    if($request->text_case_id!=''){
                        $CaseEvent->case_id=$request->text_case_id; 
                    }    
                    if($request->text_lead_id!=''){
                        $CaseEvent->lead_id=$request->text_lead_id; 
                    }    
                } 
                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
            }
            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
            if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
            if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
            $CaseEvent->recuring_event="no"; 
            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                $CaseEvent->event_location_id=$request->case_location_list; 
            }else{  
                $CaseEvent->event_location_id=($locationID)??NULL;
                
            }
            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
            $CaseEvent->parent_evnt_id ='0';

            $CaseEvent->created_by=Auth::user()->id; 
            $CaseEvent->firm_id = auth()->user()->firm_name;
            $CaseEvent->save();
            $this->saveEventReminder($request->all(),$CaseEvent->id); 
            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveContactLeadData($request->all(),$CaseEvent->id); 
            // $this->saveEventHistory($CaseEvent->id);
              
        }else{
            if($request->event_frequency=='DAILY')
            {
                $i=0;
                $event_interval_day=$request->event_interval_day;

               
                 //If new location is creating.
                 if($request->location_name!=''){
                   $locationID= $this->saveLocationOnce($request);
                 }

                do {
                    
                    $start_date = date("Y-m-d", $startTime);
                    $start_time = date("H:i:s", strtotime($request->start_time));
                    $end_date = date("Y-m-d", $startTime);
                    $end_time = date("H:i:s", strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                        // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    $CaseEvent->event_interval_day=$request->event_interval_day;
                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                        
                    } 
                    if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->created_by=Auth::user()->id; 
                    $CaseEvent->firm_id = auth()->user()->firm_name;
                    $CaseEvent->save();
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
                    $this->saveContactLeadData($request->all(),$CaseEvent->id); 
 
                    // $this->saveEventHistory($CaseEvent->id);
                    
                    $startTime = strtotime('+'.$event_interval_day.' day',$startTime); 
                    $i++;
                } while ($startTime < $endTime);
            }else if($request->event_frequency=='EVERY_BUSINESS_DAY')
            { 
                $i=0;
                //If new location is creating.
                if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                do {
                   
                    $timestamp = $startTime;
                    $weekday= date("l", $timestamp );            
                    if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                    }else {
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                      
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        } 
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->firm_id = auth()->user()->firm_name;
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                        // $this->saveEventHistory($CaseEvent->id);
                    }
                    $i++;
                    $startTime = strtotime('+1 day',$startTime); 
                    } while ($startTime < $endTime);
            }else if($request->event_frequency=='WEEKLY')
            {
                $i=0;
 
                //If new location is creating.
                if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                 do {
                
                    $timestamp = $startTime;
                    $weekday= date("l", $timestamp );       
                    if ($weekday==date("l")) { 
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;

                        $CaseEvent->daily_weekname=$request->daily_weekname;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->firm_id = auth()->user()->firm_name;
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                        // $this->saveEventHistory($CaseEvent->id);

                    }  $startTime = strtotime('+1 day',$startTime); 
                    $i++;
                    } while ($startTime < $endTime);
            }else if($request->event_frequency=='CUSTOM')
            { 
                $i=0;
                $weekFirstDay=date("Y-m-d", strtotime('monday this week'));
                $start = new DateTime($weekFirstDay);
                $startClone = new DateTime($weekFirstDay);

                
               
                if($request->end_on!=''){
                    $end=new DateTime($request->end_on);
                }else{
                    $end=$startClone->add(new DateInterval('P365D'));
                }
                //$end = new DateTime( '2021-09-28 23:59:59');
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start, $interval, $end);
                
                $weekInterval = $request->daily_weekname;
                $fakeWeek = 0;
                $currentWeek = $start->format('W');
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                foreach ($period as $date) {
                    if ($date->format('W') !== $currentWeek) {
                        $currentWeek = $date->format('W');
                        $fakeWeek++;
                    }
                
                    if ($fakeWeek % $weekInterval !== 0) {
                        continue;
                    }
                
                    $dayOfWeek = $date->format('l');
                    if(in_array($dayOfWeek,$request->custom)){

                        $start_date = $date->format('Y-m-d');
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date =$date->format('Y-m-d');
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;

                        $CaseEvent->daily_weekname=$request->daily_weekname;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->firm_id = auth()->user()->firm_name;
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $i++;
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                        // $this->saveEventHistory($CaseEvent->id);
                    }
                }
               
            }else if($request->event_frequency=='MONTHLY')
            { 
                $Currentweekday= date("l", $startTime ); 
                $i=0;
                 //If new location is creating.
                 if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                 do {
                
                    $monthly_frequency=$request->monthly_frequency;
                    $event_interval_month=$request->event_interval_month;
                    if($monthly_frequency=='MONTHLY_ON_DAY'){
                        $startTime=$startTime;
                        // echo date('Y-m-d', $startTime);
                    }else if($monthly_frequency=='MONTHLY_ON_THE'){
                    $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                        // $startTime=date('Y-m-d', $fourthDay);
                    }else if($monthly_frequency=='MONTHLY_ON_THE_LAST'){
                        $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                        // $startTime=date('Y-m-d', $lastDay);
                    }
                    $start_date = date("Y-m-d", $startTime);
                    $start_time = date("H:i:s", strtotime($request->start_time));
                    $end_date = date("Y-m-d", $startTime);
                    $end_time = date("H:i:s", strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                        // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    $CaseEvent->event_interval_month=$request->event_interval_month;
                    $CaseEvent->monthly_frequency=$request->monthly_frequency;
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                    }   
                    
                    if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->created_by=Auth::user()->id; 
                    $CaseEvent->firm_id = auth()->user()->firm_name;
                    $CaseEvent->save();
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                    //  $this->saveEventHistory($CaseEvent->id);
                    $startTime = strtotime('+'.$event_interval_month.' months',$startTime);
                    $i++;
                    } while ($startTime < $endTime);
            }else if($request->event_frequency=='YEARLY'){ 
                $endTime =  strtotime(date('Y-m-d',strtotime('+25 years')));
                if($request->end_on!=''){
                    $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                $yearly_frequency=$request->yearly_frequency;
                $Currentweekday= date("l", $startTime ); 
                $i=0;
                //If new location is creating.
                if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                }
                do {
                    $event_interval_year=$request->event_interval_year;
                    if($yearly_frequency=='YEARLY_ON_DAY'){
                        $startTime=$startTime;
                        // echo date('Y-m-d', $startTime);
                    }else if($yearly_frequency=='YEARLY_ON_THE'){
                    $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                    //    echo date('Y-m-d', $startTime);
                    }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                        $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                        // echo date('Y-m-d', $startTime);
                    }
                    $start_date = date("Y-m-d", $startTime);
                    $start_time = date("H:i:s", strtotime($request->start_time));
                    $end_date = date("Y-m-d", $startTime);
                    $end_time = date("H:i:s", strtotime($request->end_time));
                    $CaseEvent = new CaseEvent;
                    if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                    if(!isset($request->no_case_link)){
                        if(isset($request->case_or_lead)) { 
                            if($request->text_case_id!=''){
                                $CaseEvent->case_id=$request->text_case_id; 
                            }    
                            if($request->text_lead_id!=''){
                                $CaseEvent->lead_id=$request->text_lead_id; 
                            }    
                        } 
                        // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                    }
                    if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                    if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                    if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                    if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                    if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                    if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                    if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                    $CaseEvent->recuring_event="yes";
                    $CaseEvent->event_frequency=$request->event_frequency;
                    $CaseEvent->event_interval_year=$request->event_interval_year;
                    $CaseEvent->yearly_frequency=$request->yearly_frequency;

                    if(isset($request->no_end_date_checkbox)) { 
                        $CaseEvent->no_end_date_checkbox="yes"; 
                        $CaseEvent->end_on=NULL;
                    }else{ 
                        $CaseEvent->no_end_date_checkbox="no";
                        $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                    } 
                    if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                        $CaseEvent->event_location_id=$request->case_location_list; 
                    }else{  
                        $CaseEvent->event_location_id=($locationID)??NULL;
                    }   
                    
                    if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                    $CaseEvent->created_by=Auth::user()->id; 
                    $CaseEvent->firm_id = auth()->user()->firm_name;
                    $CaseEvent->save();
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                    // $this->saveEventHistory($CaseEvent->id);

                    
                    $startTime = strtotime('+'.$event_interval_year.' years',$startTime);
                    $i++;
                    } while ($startTime < $endTime);
            }
        }
        $data=[];
        if(!isset($request->no_case_link)){
            if(isset($request->case_or_lead)) { 
                if($request->text_case_id!=''){
                    $data['event_for_case']=$request->text_case_id;
                }    
                if($request->text_lead_id!=''){
                    $data['event_for_lead']=$request->text_lead_id; ;
                }    
            } 
        }
        $data['event_id']=$CaseEvent->id;
        $data['event_name']=$CaseEvent->event_title;
        $data['user_id']=Auth::User()->id;
        $data['activity']='added event';
        $data['type']='event';
        $data['action']='add';
        
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);
        session(['popup_success' => 'Event was added.']);
        return response()->json(['errors'=>''   ]);
        exit;
      }
    
    /**
     * Add new single/recurring event
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
        if($request->location_name!=''){
            $locationID= $this->saveLocationOnce($request);
        }

        //Single event
        if(!isset($request->recuring_event)){
            $startDate = strtotime(date("Y-m-d", strtotime($request->start_date)));
            $endDate = strtotime(date("Y-m-d",strtotime($request->end_date)));
        }else{
            //recurring event
            $startDate = strtotime(date("Y-m-d",  strtotime($request->start_date)));
            $endDate =  strtotime(date('Y-m-d',strtotime('+365 days')));
            if($request->end_on!=''){
                $endDate =  strtotime(date('Y-m-d',strtotime($request->end_on)));
            }
        }

        // Start-End time for all events convert into UTC
        $start_time = date("H:i:s", strtotime(convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->start_date.' '.$request->start_time)), $authUser ->user_timezone)));
        $end_time = date("H:i:s", strtotime(convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->end_date.' '.$request->end_time)), $authUser ->user_timezone)));

        if(!isset($request->recuring_event)){        
            $start_date = date("Y-m-d", $startDate);
            $end_date = date("Y-m-d", $endDate);

            $CaseEvent = CaseEvent::create([
                "event_title" => $request->event_name,
                "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                "event_type" => $request->event_type ?? NULL,
                "start_date" => $start_date,
                "end_date" => $end_date,
                "start_time" => $start_time,
                "end_time" => $end_time,
                "all_day" => (isset($request->all_day)) ? "yes" : "no",
                "event_description" => $request->description,
                "recuring_event" => "no",
                "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                "parent_evnt_id" => '0',
                "firm_id" => $authUser->firm_name,
                "created_by" => $authUser->id,
            ]);

            $this->saveEventReminder($request->all(),$CaseEvent->id); 
            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveContactLeadData($request->all(),$CaseEvent->id);                 
        } else {
            if($request->event_frequency=='DAILY')
            {
                $i=0;
                $event_interval_day=$request->event_interval_day;
                do {
                    $start_date = date("Y-m-d", $startDate);
                    $end_date = date("Y-m-d", $startDate);

                    /* $caseEvent = CaseEvent::create([
                        "event_title" => $request->event_name,
                        "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                        "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                        "event_type" => $request->event_type ?? NULL,
                        "start_date" => $start_date,
                        "end_date" => $end_date,
                        "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
                        "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
                        "all_day" => (isset($request->all_day)) ? "yes" : "no",
                        "event_description" => $request->description,
                        "recuring_event" => "yes",
                        "event_frequency" => $request->event_frequency,
                        "event_interval_day" => $request->event_interval_day,
                        "no_end_date_checkbox" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
                        "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
                        "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                        "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                        "firm_id" => $authUser->firm_name,
                        "created_by" => $authUser->id,
                    ]); */
                    $caseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
                    if($i==0) { 
                        $parentCaseID=$caseEvent->id;
                        $caseEvent->parent_evnt_id =  $caseEvent->id; 
                        $caseEvent->save();
                    }else{
                        $caseEvent->parent_evnt_id =  $parentCaseID;
                        $caseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$caseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$caseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$caseEvent->id);
                    $this->saveContactLeadData($request->all(),$caseEvent->id); 
        
                    // $this->saveEventHistory($CaseEvent->id);
                    
                    $startDate = strtotime('+'.$event_interval_day.' day',$startDate); 
                    $i++;
                } while ($startDate < $endDate);
            }
            else if($request->event_frequency=='EVERY_BUSINESS_DAY')
            { 
                $i=0;
                do {
                    $timestamp = $startDate;
                    $weekday= date("l", $timestamp );            
                    if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                    }else {
                        $start_date = date("Y-m-d", $startDate);
                        $end_date = date("Y-m-d", $startDate);
                        $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                        // $this->saveEventHistory($CaseEvent->id);
                    }
                    $i++;
                    $startDate = strtotime('+1 day',$startDate); 
                    } while ($startDate < $endDate);
            }
            else if($request->event_frequency=='WEEKLY')
            {
                $i=0;
                do {
                    // $timestamp = $startDate;
                    // $weekday= date("l", $timestamp ); 
                    $start_date = date("Y-m-d", $startDate);
                    $end_date = date("Y-m-d", $startDate);
                    $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                    // $this->saveEventHistory($CaseEvent->id);

                    $i++;
                    $startDate = strtotime('+7 day',$startDate); 
                } while ($startDate < $endDate);
            }
            else if($request->event_frequency=='CUSTOM')
            { 
                $i=0;
                $weekFirstDay=date("Y-m-d", strtotime('monday this week'));
                $start = new DateTime($weekFirstDay);
                $startClone = new DateTime($weekFirstDay);
                if($request->end_on!=''){
                    $end=new DateTime($request->end_on);
                }else{
                    $end=$startClone->add(new DateInterval('P365D'));
                }
                //$end = new DateTime( '2021-09-28 23:59:59');
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start, $interval, $end);
                
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
                    if(in_array($dayOfWeek,$request->custom)){

                        $start_date = $date->format('Y-m-d');
                        $end_date =$date->format('Y-m-d');
                        $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $i++;
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                        // $this->saveEventHistory($CaseEvent->id);
                    }
                }
               
            }
            else if($request->event_frequency=='MONTHLY')
            { 
                $Currentweekday= date("l", $startDate ); 
                $i=0;
                do {
                    $monthly_frequency=$request->monthly_frequency;
                    $event_interval_month=$request->event_interval_month;
                    if($monthly_frequency=='MONTHLY_ON_DAY'){
                        $startDate=$startDate;
                    }else if($monthly_frequency=='MONTHLY_ON_THE'){
                        $startDate = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startDate);
                    }else if($monthly_frequency=='MONTHLY_ON_THE_LAST'){
                        $startDate = strtotime("last ".strtolower($Currentweekday)." of this month",$startDate);
                    }
                    $start_date = date("Y-m-d", $startDate);
                    $end_date = date("Y-m-d", $startDate);
                    $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                    //  $this->saveEventHistory($CaseEvent->id);
                    $startDate = strtotime('+'.$event_interval_month.' months',$startDate);
                    $i++;
                    } while ($startDate < $endDate);
            }
            else if($request->event_frequency=='YEARLY')
            { 
                $endDate =  strtotime(date('Y-m-d',strtotime('+25 years')));
                if($request->end_on!=''){
                    $endDate =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                $yearly_frequency=$request->yearly_frequency;
                $Currentweekday= date("l", $startDate ); 
                $i=0;
                do {
                    $event_interval_year=$request->event_interval_year;
                    if($yearly_frequency=='YEARLY_ON_DAY'){
                        $startDate=$startDate;
                    }else if($yearly_frequency=='YEARLY_ON_THE'){
                    $startDate = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startDate);
                    }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                        $startDate = strtotime("last ".strtolower($Currentweekday)." of this month",$startDate);
                    }
                    $start_date = date("Y-m-d", $startDate);
                    $end_date = date("Y-m-d", $startDate);
                    $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                    // $this->saveEventHistory($CaseEvent->id);

                    
                    $startDate = strtotime('+'.$event_interval_year.' years',$startDate);
                    $i++;
                    } while ($startDate < $endDate);
            }
        }
        $data=[];
        if(!isset($request->no_case_link)){
            if(isset($request->case_or_lead)) { 
                if($request->text_case_id!=''){
                    $data['event_for_case']=$request->text_case_id;
                }    
                if($request->text_lead_id!=''){
                    $data['event_for_lead']=$request->text_lead_id; ;
                }    
            } 
        }
        $data['event_id']=$CaseEvent->id;
        $data['event_name']=$CaseEvent->event_title;
        $data['user_id']=Auth::User()->id;
        $data['activity']='added event';
        $data['type']='event';
        $data['action']='add';
        
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);
        session(['popup_success' => 'Event was added.']);
        return response()->json(['errors'=>''   ]);
    }

    /**
     * Save recurring event data
     */
    public function saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser)
    {
        $caseEvent = CaseEvent::create([
            "event_title" => $request->event_name,
            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
            "event_type" => $request->event_type ?? NULL,
            "start_date" => $start_date,
            "end_date" => $end_date,
            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
            "all_day" => (isset($request->all_day)) ? "yes" : "no",
            "event_description" => $request->description,
            "recuring_event" => "yes",
            "event_frequency" => $request->event_frequency,
            "event_interval_day" => $request->event_interval_day,
            "daily_weekname" => $request->daily_weekname,
            "event_interval_month" => $request->event_interval_month,
            "monthly_frequency" => $request->monthly_frequency,
            "event_interval_year" => $request->event_interval_year,
            "yearly_frequency" => $request->yearly_frequency,
            "no_end_date_checkbox" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
            "firm_id" => $authUser->firm_name,
            "created_by" => $authUser->id,
        ]);
        return $caseEvent;
    }
    
      public function saveEditEventPage(Request $request)
      {
        //   return $request->all();
          if(!isset($request->no_case_link)){
            $validator = \Validator::make($request->all(), [
                'linked_staff_checked_share' => 'required'
            ]);
            if($validator->fails())
            {
                return response()->json(['errors'=>['You must share with at least one firm user<br>You must share with at least one user'],]);
            }
        }
        
        if($request->delete_event_type=='SINGLE_EVENT'){
            $CaseEvent=CaseEvent::find($request->event_id);

            $start_date = date("Y-m-d",  strtotime($request->start_date));
            $start_time = date("H:i:s", strtotime($request->start_time));
            $end_date = date("Y-m-d",  strtotime($request->end_date));
            $end_time = date("H:i:s", strtotime($request->end_time));
            if(!isset($request->recuring_event)){
                if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                if(!isset($request->no_case_link)){
                    if(isset($request->case_or_lead)) { 
                        if($request->text_case_id!=''){
                            $CaseEvent->case_id=$request->text_case_id; 
                        }    
                        if($request->text_lead_id!=''){
                            $CaseEvent->lead_id=$request->text_lead_id; 
                        }    
                    } 
                    // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                }else{
                    $CaseEvent->case_id=NULL; 
                }
                if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
                if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
                if(isset($request->all_day)) { $CaseEvent->all_day="yes";
                    $CaseEvent->start_time=NULL;
                    $CaseEvent->end_time=NULL;
                }else{ $CaseEvent->all_day="no";} 
                if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                $CaseEvent->recuring_event="no"; 
                $CaseEvent->event_frequency=$request->event_frequency;
                $CaseEvent->event_interval_day=$request->event_interval_day;
                if(isset($request->no_end_date_checkbox)) { 
                    $CaseEvent->no_end_date_checkbox="yes"; 
                    $CaseEvent->end_on=NULL;
                }else{ 
                    $CaseEvent->no_end_date_checkbox="no";
                    $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                } 
                if(isset($request->case_location_list)) { $CaseEvent->event_location_id =$request->case_location_list; }

                //If new location is creating.
                if($request->location_name!=''){
                    $CaseEventLocation = new CaseEventLocation;
                    $CaseEventLocation->location_name=$request->location_name;
                    $CaseEventLocation->address1=$request->address;
                    $CaseEventLocation->address2=$request->address2;
                    $CaseEventLocation->city=$request->city;
                    $CaseEventLocation->state=$request->state;
                    $CaseEventLocation->postal_code=$request->postal_code;
                    $CaseEventLocation->country=$request->country;
                    $CaseEventLocation->location_future_use=($request->location_future_use)?'yes':'no';
                    $CaseEventLocation->created_by=Auth::user()->id; 
                    $CaseEventLocation->save();
                    $CaseEvent->event_location_id =$CaseEventLocation->id;
                }
                if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                $CaseEvent->parent_evnt_id ='0';
                $CaseEvent->updated_by=Auth::user()->id; 
                $CaseEvent->firm_id = auth()->user()->firm_name;
                $CaseEvent->save();
                $this->saveEventReminder($request->all(),$CaseEvent->id); 
                $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
                $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                $this->saveEventHistory($CaseEvent->id);
            } else if($CaseEvent->parent_evnt_id != 0 && $CaseEvent->recuring_event == "yes") {
                if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                if(!isset($request->no_case_link)){
                    if(isset($request->case_or_lead)) { 
                        if($request->text_case_id!=''){
                            $CaseEvent->case_id=$request->text_case_id; 
                        }    
                        if($request->text_lead_id!=''){
                            $CaseEvent->lead_id=$request->text_lead_id; 
                        }    
                    } 
                    // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                }else{
                    $CaseEvent->case_id=NULL; 
                }
                if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
                if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
                if(isset($request->all_day)) { $CaseEvent->all_day="yes";
                    $CaseEvent->start_time=NULL;
                    $CaseEvent->end_time=NULL;
                }else{ $CaseEvent->all_day="no";} 
                if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                $CaseEvent->recuring_event=$CaseEvent->recurring_event; 
                $CaseEvent->event_frequency=$request->event_frequency;
                $CaseEvent->event_interval_day=$request->event_interval_day;
                if(isset($request->no_end_date_checkbox)) { 
                    $CaseEvent->no_end_date_checkbox="yes"; 
                    $CaseEvent->end_on=NULL;
                }else{ 
                    $CaseEvent->no_end_date_checkbox="no";
                    $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                } 
                if(isset($request->case_location_list)) { $CaseEvent->event_location_id =$request->case_location_list; }

                //If new location is creating.
                if($request->location_name!=''){
                    $CaseEventLocation = new CaseEventLocation;
                    $CaseEventLocation->location_name=$request->location_name;
                    $CaseEventLocation->address1=$request->address;
                    $CaseEventLocation->address2=$request->address2;
                    $CaseEventLocation->city=$request->city;
                    $CaseEventLocation->state=$request->state;
                    $CaseEventLocation->postal_code=$request->postal_code;
                    $CaseEventLocation->country=$request->country;
                    $CaseEventLocation->location_future_use=($request->location_future_use)?'yes':'no';
                    $CaseEventLocation->created_by=Auth::user()->id; 
                    $CaseEventLocation->save();
                    $CaseEvent->event_location_id =$CaseEventLocation->id;
                }
                if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                $CaseEvent->parent_evnt_id = $CaseEvent->parent_evnt_id;
                $CaseEvent->updated_by=Auth::user()->id;
                $CaseEvent->firm_id = auth()->user()->firm_name; 
                $CaseEvent->save();
                $this->saveEventReminder($request->all(),$CaseEvent->id); 
                $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
                $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                $this->saveEventHistory($CaseEvent->id);
            } else {
                $startTime = strtotime($request->start_date);
                $endTime =  strtotime(date('Y-m-d',strtotime('+365 days')));
                if($request->end_on!=''){
                    $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                if($request->event_frequency=='DAILY')
                {
                    $i=0;
                    $event_interval_day=$request->event_interval_day;

                
                    //If new location is creating.
                    if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                    }

                    do {
                        
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        $CaseEvent->event_interval_day=$request->event_interval_day;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                            
                        } 
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->firm_id = auth()->user()->firm_name;
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 
    
                        // $this->saveEventHistory($CaseEvent->id);
                        
                        $startTime = strtotime('+'.$event_interval_day.' day',$startTime); 
                        $i++;
                    } while ($startTime < $endTime);
                } else if($request->event_frequency=='EVERY_BUSINESS_DAY')
                { 
                    $i=0;
                    //If new location is creating.
                    if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    do {
                       
                        $timestamp = $startTime;
                        $weekday= date("l", $timestamp );            
                        if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                        }else {
                            $start_date = date("Y-m-d", $startTime);
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date = date("Y-m-d", $startTime);
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            } 
                          
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            } 
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->created_by=Auth::user()->id; 
                            $CaseEvent->save();
                            if($i==0) { 
                                $parentCaseID=$CaseEvent->id;
                                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                                $CaseEvent->save();
                            }else{
                                $CaseEvent->parent_evnt_id =  $parentCaseID;
                                $CaseEvent->save();
                            }
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 
    
                            // $this->saveEventHistory($CaseEvent->id);
                        }
                        $i++;
                        $startTime = strtotime('+1 day',$startTime); 
                        } while ($startTime < $endTime);
                }else if($request->event_frequency=='WEEKLY')
                {
                    $i=0;
     
                    //If new location is creating.
                    if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                     do {
                    
                        $timestamp = $startTime;
                        $weekday= date("l", $timestamp );       
                        if ($weekday==date("l")) { 
                            $start_date = date("Y-m-d", $startTime);
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date = date("Y-m-d", $startTime);
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;
    
                            $CaseEvent->daily_weekname=$request->daily_weekname;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            } 
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            }   
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->created_by=Auth::user()->id; 
                            $CaseEvent->save();
                            if($i==0) { 
                                $parentCaseID=$CaseEvent->id;
                                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                                $CaseEvent->save();
                            }else{
                                $CaseEvent->parent_evnt_id =  $parentCaseID;
                                $CaseEvent->save();
                            }
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 
    
                            // $this->saveEventHistory($CaseEvent->id);
    
                        }  $startTime = strtotime('+1 day',$startTime); 
                        $i++;
                        } while ($startTime < $endTime);
                }else if($request->event_frequency=='CUSTOM')
                { 
                    $i=0;
                    $weekFirstDay=date("Y-m-d", strtotime('monday this week'));
                    $start = new DateTime($weekFirstDay);
                    $startClone = new DateTime($weekFirstDay);
    
                    
                   
                    if($request->end_on!=''){
                        $end=new DateTime($request->end_on);
                    }else{
                        $end=$startClone->add(new DateInterval('P365D'));
                    }
                    //$end = new DateTime( '2021-09-28 23:59:59');
                    $interval = new DateInterval('P1D');
                    $period = new DatePeriod($start, $interval, $end);
                    
                    $weekInterval = $request->daily_weekname;
                    $fakeWeek = 0;
                    $currentWeek = $start->format('W');
                     //If new location is creating.
                     if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    foreach ($period as $date) {
                        if ($date->format('W') !== $currentWeek) {
                            $currentWeek = $date->format('W');
                            $fakeWeek++;
                        }
                    
                        if ($fakeWeek % $weekInterval !== 0) {
                            continue;
                        }
                    
                        $dayOfWeek = $date->format('l');
                        if(in_array($dayOfWeek,$request->custom)){
    
                            $start_date = $date->format('Y-m-d');
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date =$date->format('Y-m-d');
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;
    
                            $CaseEvent->daily_weekname=$request->daily_weekname;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            } 
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            }   
                            
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->created_by=Auth::user()->id; 
                            $CaseEvent->save();
                            if($i==0) { 
                                $parentCaseID=$CaseEvent->id;
                                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                                $CaseEvent->save();
                            }else{
                                $CaseEvent->parent_evnt_id =  $parentCaseID;
                                $CaseEvent->save();
                            }
                            $i++;
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 
    
                            // $this->saveEventHistory($CaseEvent->id);
                        }
                    }
                   
                }else if($request->event_frequency=='MONTHLY')
                { 
                    $Currentweekday= date("l", $startTime ); 
                    $i=0;
                     //If new location is creating.
                     if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                     do {
                    
                        $monthly_frequency=$request->monthly_frequency;
                        $event_interval_month=$request->event_interval_month;
                        if($monthly_frequency=='MONTHLY_ON_DAY'){
                            $startTime=$startTime;
                            // echo date('Y-m-d', $startTime);
                        }else if($monthly_frequency=='MONTHLY_ON_THE'){
                        $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                            // $startTime=date('Y-m-d', $fourthDay);
                        }else if($monthly_frequency=='MONTHLY_ON_THE_LAST'){
                            $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                            // $startTime=date('Y-m-d', $lastDay);
                        }
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        $CaseEvent->event_interval_month=$request->event_interval_month;
                        $CaseEvent->monthly_frequency=$request->monthly_frequency;
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 
    
                        //  $this->saveEventHistory($CaseEvent->id);
                        $startTime = strtotime('+'.$event_interval_month.' months',$startTime);
                        $i++;
                        } while ($startTime < $endTime);
                }else if($request->event_frequency=='YEARLY'){ 
                    $endTime =  strtotime(date('Y-m-d',strtotime('+25 years')));
                    if($request->end_on!=''){
                        $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                    }
                    $yearly_frequency=$request->yearly_frequency;
                    $Currentweekday= date("l", $startTime ); 
                    $i=0;
                    //If new location is creating.
                    if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    do {
                        $event_interval_year=$request->event_interval_year;
                        if($yearly_frequency=='YEARLY_ON_DAY'){
                            $startTime=$startTime;
                            // echo date('Y-m-d', $startTime);
                        }else if($yearly_frequency=='YEARLY_ON_THE'){
                        $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                        //    echo date('Y-m-d', $startTime);
                        }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                            $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                            // echo date('Y-m-d', $startTime);
                        }
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        $CaseEvent->event_interval_year=$request->event_interval_year;
                        $CaseEvent->yearly_frequency=$request->yearly_frequency;
    
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 
    
                        // $this->saveEventHistory($CaseEvent->id);
    
                        
                        $startTime = strtotime('+'.$event_interval_year.' years',$startTime);
                        $i++;
                        } while ($startTime < $endTime);
                }

                // Delete old/current edit event
                // CaseEvent::whereId($request->event_id)->delete();
                $oldEvent = CaseEvent::whereId($request->event_id)->first();
                if($oldEvent) {
                    $oldEvent->deleteChildTableRecords([$request->event_id]);
                    $oldEvent->forceDelete();
                }
            }

        }elseif($request->delete_event_type=='THIS_AND_FOLLOWING_EVENTS'){
            $CaseEvent=CaseEvent::find($request->event_id);
           
            if(!isset($request->recuring_event)){
                CaseEvent::where('parent_evnt_id',$CaseEvent->parent_evnt_id)->where('id',"!=",$request->event_id)->delete();
                $start_date = date("Y-m-d", strtotime($request->start_date));
                $start_time = date("H:i:s", strtotime($request->start_time));
                $end_date = date("Y-m-d", strtotime($request->end_date));
                $end_time = date("H:i:s", strtotime($request->end_time));
                if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                if(!isset($request->no_case_link)){
                    if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                }
                if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
                if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
                if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                    $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                $CaseEvent->recuring_event="no"; 
                $CaseEvent->event_frequency=NULL; 
                $CaseEvent->event_interval_day=NULL; 
                $CaseEvent->daily_weekname=NULL; 
                $CaseEvent->end_on=NULL; 
                if(isset($request->case_location_list)) { $CaseEvent->event_location_id =$request->case_location_list; }

                //If new location is creating.
                if( $request->location_name!=''){ //$request->case_location_list=="0" &&
                    $CaseEventLocation = new CaseEventLocation;
                    $CaseEventLocation->location_name=$request->location_name;
                    $CaseEventLocation->address1=$request->address;
                    $CaseEventLocation->address2=$request->address2;
                    $CaseEventLocation->city=$request->city;
                    $CaseEventLocation->state=$request->state;
                    $CaseEventLocation->postal_code=$request->postal_code;
                    $CaseEventLocation->country=$request->country;
                    $CaseEventLocation->location_future_use=($request->location_future_use)?'yes':'no';
                    $CaseEventLocation->created_by=Auth::user()->id; 
                    $CaseEventLocation->save();
                    $CaseEvent->event_location_id =$CaseEventLocation->id;
                }
                if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                $CaseEvent->parent_evnt_id ='0';
                $CaseEvent->updated_by=Auth::user()->id; 
                $CaseEvent->save();
            }else{
                $startTime = strtotime($request->start_date);
                $endTime =  strtotime(date('Y-m-d',strtotime('+365 days')));
                if($request->end_on!=''){
                    $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                if($request->event_frequency=='DAILY')
                {
                    $OldCaseEvent=CaseEvent::find($request->event_id);
                    $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                    $endTime =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                    
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();
                  
                    $i=0;
                    $event_interval_day=$request->event_interval_day;
                    //If new location is creating.
                    if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    
                    do {
                        $start_date = date("Y-m-d",$startTime);
                        $start_time = date("H:i:s",strtotime($request->start_time));
                        $end_date = date("Y-m-d",$startTime);
                        $end_time = date("H:i:s",strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date;} 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        $CaseEvent->event_interval_day=$request->event_interval_day;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at; 
                        $CaseEvent->parent_evnt_id =  $OldCaseEvent->parent_evnt_id; 
                        $CaseEvent->save();
                       
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id);   
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                        $this->saveEventHistory($CaseEvent->id);

                        $startTime = strtotime('+'.$event_interval_day.' day',$startTime); 
                        $i++;
                    } while ($startTime <= $endTime);

                   
                }else if($request->event_frequency=='EVERY_BUSINESS_DAY')
                { 
                    $i=0;
                    $OldCaseEvent=CaseEvent::find($request->event_id);
                    $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                    $endTime =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                    
                    // CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->forceDelete();
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();

                   //If new location is creating.
                   if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                
                    do {
                        $timestamp = $startTime;
                        $weekday= date("l", $timestamp );            
                        if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                        }else {
                            $start_date = date("Y-m-d", $startTime);
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date = date("Y-m-d", $startTime);
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                                $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            } 
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            }   
                           
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->updated_by=Auth::user()->id; 
                            $CaseEvent->created_by=$OldCaseEvent->created_by;
                            $CaseEvent->created_at=$OldCaseEvent->created_at; 
                            $CaseEvent->parent_evnt_id =  $OldCaseEvent->id;                             
                            $CaseEvent->save();
                           
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                            $this->saveEventHistory($CaseEvent->id);

                        }
                        $i++;
                        $startTime = strtotime('+1 day',$startTime); 
                        } while ($startTime <= $endTime);
                       
                }else if($request->event_frequency=='WEEKLY')
                {
                   $i=0;
                   $OldCaseEvent=CaseEvent::find($request->event_id);
                   $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                   $endTime =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                //  CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->forceDelete();
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();

                        //If new location is creating.
                        if($request->location_name!=''){
                            $locationID= $this->saveLocationOnce($request);
                        }
                    
                    do {
                        $timestamp = $startTime;
                        $weekday= date("l", $timestamp );       
                        if ($weekday==date("l")) { 
                            $start_date = date("Y-m-d", $startTime);
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date = date("Y-m-d", $startTime);
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                                $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;

                            $CaseEvent->daily_weekname=$request->daily_weekname;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            } 
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            }   
                           
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->updated_by=Auth::user()->id; 
                            $CaseEvent->created_by=$OldCaseEvent->created_by;
                            $CaseEvent->created_at=$OldCaseEvent->created_at; 
                            $CaseEvent->parent_evnt_id = $OldCaseEvent->parent_evnt_id;
   
                            $CaseEvent->save();
                           
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                            $this->saveEventHistory($CaseEvent->id);

                        }  
                        $startTime = strtotime('+1 day',$startTime); 
                        $i++;
                    } while ($startTime < $endTime);
                }else if($request->event_frequency=='CUSTOM')
                { 
                    $i=0;
                    $OldCaseEvent=CaseEvent::find($request->event_id);
                    $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                   
                    // CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->forceDelete();
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();


                    $weekFirstDay=date("Y-m-d", strtotime('monday this week'));
                    $start = new DateTime($weekFirstDay);
                    $startClone = new DateTime($weekFirstDay);

                    
                
                    if($request->end_on!=''){
                        $end=new DateTime($request->end_on);
                    }else{
                        // $end=$startClone->add(new DateInterval('P365D'));
                        $end =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                    
                    }
                    //$end = new DateTime( '2021-09-28 23:59:59');
                    $interval = new DateInterval('P1D');
                    $period = new DatePeriod($start, $interval, $end);
                    
                    $weekInterval = $request->daily_weekname;
                    $fakeWeek = 0;
                    $currentWeek = $start->format('W');
                     //If new location is creating.
                     if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    
                    foreach ($period as $date) {
                        if ($date->format('W') !== $currentWeek) {
                            $currentWeek = $date->format('W');
                            $fakeWeek++;
                        }
                    
                        if ($fakeWeek % $weekInterval !== 0) {
                            continue;
                        }
                    
                        $dayOfWeek = $date->format('l');
                        if(in_array($dayOfWeek,$request->custom)){

                            $start_date = $date->format('Y-m-d');
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date =$date->format('Y-m-d');
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                                $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;

                            $CaseEvent->daily_weekname=$request->daily_weekname;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            } 
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            }   
                           
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->updated_by=Auth::user()->id; 
                            $CaseEvent->created_by=$OldCaseEvent->created_by;
                            $CaseEvent->created_at=$OldCaseEvent->created_at; 
                            $CaseEvent->parent_evnt_id=$OldCaseEvent->parent_evnt_id; 
                            $CaseEvent->save();
                           
                            $i++;
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                            $this->saveEventHistory($CaseEvent->id);


                        }
                    }
                
                }else if($request->event_frequency=='MONTHLY')
                { 
                    $Currentweekday= date("l", $startTime ); 
                    $i=0;
                    $OldCaseEvent=CaseEvent::find($request->event_id);
                    $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                    $endTime =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                    
                    // CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->forceDelete();
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();

                    //If new location is creating.
                    if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }

                    do {
                    
                        $monthly_frequency=$request->monthly_frequency;
                        $event_interval_month=$request->event_interval_month;
                        if($monthly_frequency=='MONTHLY_ON_DAY'){
                            $startTime=$startTime;
                            // echo date('Y-m-d', $startTime);
                        }else if($monthly_frequency=='MONTHLY_ON_THE'){
                        $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                            // $startTime=date('Y-m-d', $fourthDay);
                        }else if($monthly_frequency=='MONTHLY_ON_THE_LAST'){
                            $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                            // $startTime=date('Y-m-d', $lastDay);
                        }
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        $CaseEvent->event_interval_month=$request->event_interval_month;
                        $CaseEvent->monthly_frequency=$request->monthly_frequency;
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at; 
                        $CaseEvent->parent_evnt_id=$OldCaseEvent->parent_evnt_id; 
                        $CaseEvent->save();
                        
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                        $this->saveEventHistory($CaseEvent->id);

                        $startTime = strtotime('+'.$event_interval_month.' months',$startTime);
                        $i++;
                        } while ($startTime < $endTime);
                }else if($request->event_frequency=='YEARLY'){ 
                    $endTime =  strtotime(date('Y-m-d',strtotime('+25 years')));
                    if($request->end_on!=''){
                        $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                    }
                    $yearly_frequency=$request->yearly_frequency;
                    $Currentweekday= date("l", $startTime ); 
                    $i=0;
                    $OldCaseEvent=CaseEvent::find($request->event_id);
                    $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                    $endTime =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                    
                    // CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->forceDelete();
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();

                    //If new location is creating.
                    if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }

                    do {
                        $event_interval_year=$request->event_interval_year;
                        if($yearly_frequency=='YEARLY_ON_DAY'){
                            $startTime=$startTime;
                            // echo date('Y-m-d', $startTime);
                        }else if($yearly_frequency=='YEARLY_ON_THE'){
                        $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                        //    echo date('Y-m-d', $startTime);
                        }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                            $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                            // echo date('Y-m-d', $startTime);
                        }
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        $CaseEvent->event_interval_year=$request->event_interval_year;
                        $CaseEvent->yearly_frequency=$request->yearly_frequency;

                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at; 
                        $CaseEvent->parent_evnt_id=$OldCaseEvent->parent_evnt_id; 
                        $CaseEvent->save();
                       
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                        $this->saveEventHistory($CaseEvent->id);

                        
                        $startTime = strtotime('+'.$event_interval_year.' years',$startTime);
                        $i++;
                        } while ($startTime < $endTime);
                }
            }

        }elseif($request->delete_event_type=='ALL_EVENTS'){
            $CaseEvent=CaseEvent::find($request->event_id);
           
            if(!isset($request->recuring_event)){
                CaseEvent::where('parent_evnt_id',$CaseEvent->parent_evnt_id)->where('id',"!=",$request->event_id)->forceDelete();
                $start_date = date("Y-m-d", strtotime($request->start_date));
                $start_time = date("H:i:s", strtotime($request->start_time));
                $end_date = date("Y-m-d", strtotime($request->end_date));
                $end_time = date("H:i:s", strtotime($request->end_time));
                if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                if(!isset($request->no_case_link)){
                    if(isset($request->case_or_lead)) { 
                        if($request->text_case_id!=''){
                            $CaseEvent->case_id=$request->text_case_id; 
                        }    
                        if($request->text_lead_id!=''){
                            $CaseEvent->lead_id=$request->text_lead_id; 
                        }    
                    } 
                    // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                }
                if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
                if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
                if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                $CaseEvent->recuring_event="no"; 
                $CaseEvent->event_frequency=NULL; 
                $CaseEvent->event_interval_day=NULL; 
                $CaseEvent->daily_weekname=NULL; 
                $CaseEvent->end_on=NULL; 
                if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                    $CaseEvent->event_location_id=$request->case_location_list; 
                }else{  
                    $CaseEvent->event_location_id=($locationID)??NULL;
                }   
                if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                $CaseEvent->parent_evnt_id ='0';
                $CaseEvent->updated_by=Auth::user()->id; 
                $CaseEvent->save();
            }else{
                $startTime = strtotime(date('Y-m-d'));
                $endTime =  strtotime(date('Y-m-d',strtotime('+365 days')));
                if($request->end_on!=''){
                    $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                if($request->event_frequency=='DAILY')
                {
                    $OldCaseEvent=CaseEvent::find($request->event_id);
                    $OldCaseEventForDate=CaseEvent::where("id",$OldCaseEvent->parent_evnt_id)->first();
                    $startTime = strtotime($OldCaseEventForDate->start_date);
                    $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                    $endTime =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                    // CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->forceDelete();
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();
                   
                    $i=0;
                    $event_interval_day=$request->event_interval_day;
                     //If new location is creating.
                     if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    
                    do {
                        $start_date = date("Y-m-d",$startTime);
                        $start_time = date("H:i:s",strtotime($request->start_time));
                        $end_date = $start_date;
                        $end_time = date("H:i:s",strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date;} 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        $CaseEvent->event_interval_day=$request->event_interval_day;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at; 
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id);                               
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                         $this->saveEventHistory($CaseEvent->id);

                        $startTime = strtotime('+'.$event_interval_day.' day',$startTime); 
                        $i++;
                    } while ($startTime <= $endTime);
                }else if($request->event_frequency=='EVERY_BUSINESS_DAY')
                { 
                    $i=0;
                    $OldCaseEvent=CaseEvent::find($request->event_id);
                    $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                    $endTime =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                    // CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->forceDelete();
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();
                     //If new location is creating.
                     if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    
                    do {
                        $timestamp = $startTime;
                        $weekday= date("l", $timestamp );            
                        if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                        }else {
                            $start_date = date("Y-m-d", $startTime);
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date = date("Y-m-d", $startTime);
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                                $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            } 
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            }   
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->updated_by=Auth::user()->id; 
                            $CaseEvent->created_by=$OldCaseEvent->created_by;
                            $CaseEvent->created_at=$OldCaseEvent->created_at;                             
                            $CaseEvent->save();
                            if($i==0) { 
                                $parentCaseID=$CaseEvent->id;
                                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                                $CaseEvent->save();
                            }else{
                                $CaseEvent->parent_evnt_id =  $parentCaseID;
                                $CaseEvent->save();
                            }
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                            $this->saveEventHistory($CaseEvent->id);

                        }
                        $i++;
                        $startTime = strtotime('+1 day',$startTime); 
                        } while ($startTime <= $endTime);
                       
                }else if($request->event_frequency=='WEEKLY')
                {
                    $i=0;
                    $OldCaseEvent=CaseEvent::find($request->event_id);
                    $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                    $endTime =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                    // CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->forceDelete();
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();
                     //If new location is creating.
                     if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    
                    do {
                    
                        $timestamp = $startTime;
                        $weekday= date("l", $timestamp );       
                        if ($weekday==date("l")) { 
                            $start_date = date("Y-m-d", $startTime);
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date = date("Y-m-d", $startTime);
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                                $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;

                            $CaseEvent->daily_weekname=$request->daily_weekname;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            } 
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            }   
                           
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->updated_by=Auth::user()->id; 
                            $CaseEvent->created_by=$OldCaseEvent->created_by;
                            $CaseEvent->created_at=$OldCaseEvent->created_at;    
                            $CaseEvent->save();
                            if($i==0) { 
                                $parentCaseID=$CaseEvent->id;
                                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                                $CaseEvent->save();
                            }else{
                                $CaseEvent->parent_evnt_id =  $parentCaseID;
                                $CaseEvent->save();
                            }
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                            $this->saveEventHistory($CaseEvent->id);

                        }  $startTime = strtotime('+1 day',$startTime); 
                        $i++;
                        } while ($startTime < $endTime);
                }else if($request->event_frequency=='CUSTOM')
                { 
                    $i=0;
                    $OldCaseEvent=CaseEvent::find($request->event_id);
                    $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                   
                    // CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->forceDelete();
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();
                    $weekFirstDay=date("Y-m-d", strtotime('monday this week'));
                    $start = new DateTime($weekFirstDay);
                    $startClone = new DateTime($weekFirstDay);

                    if($request->end_on!=''){
                        $end=new DateTime($request->end_on);
                    }else{
                        // $end=$startClone->add(new DateInterval('P365D'));
                        $end =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                    }
                    //$end = new DateTime( '2021-09-28 23:59:59');
                    $interval = new DateInterval('P1D');
                    $period = new DatePeriod($start, $interval, $end);
                    
                    $weekInterval = $request->daily_weekname;
                    $fakeWeek = 0;
                    $currentWeek = $start->format('W');
                     //If new location is creating.
                     if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    
                    foreach ($period as $date) {
                        if ($date->format('W') !== $currentWeek) {
                            $currentWeek = $date->format('W');
                            $fakeWeek++;
                        }
                    
                        if ($fakeWeek % $weekInterval !== 0) {
                            continue;
                        }
                    
                        $dayOfWeek = $date->format('l');
                        if(in_array($dayOfWeek,$request->custom)){

                            $start_date = $date->format('Y-m-d');
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date =$date->format('Y-m-d');
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; $CaseEvent->start_time=NULL;
                                $CaseEvent->end_time=NULL; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;

                            $CaseEvent->daily_weekname=$request->daily_weekname;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            }
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            }   
                           
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->updated_by=Auth::user()->id; 
                            $CaseEvent->created_by=$OldCaseEvent->created_by;
                            $CaseEvent->created_at=$OldCaseEvent->created_at; 
                            $CaseEvent->save();
                            if($i==0) { 
                                $parentCaseID=$CaseEvent->id;
                                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                                $CaseEvent->save();
                            }else{
                                $CaseEvent->parent_evnt_id =  $parentCaseID;
                                $CaseEvent->save();
                            }
                            $i++;
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                            $this->saveEventHistory($CaseEvent->id);
                        }
                    }
                
                }else if($request->event_frequency=='MONTHLY')
                { 
                    $Currentweekday= date("l", $startTime ); 
                    $i=0;
                    $OldCaseEvent=CaseEvent::find($request->event_id);
                    $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                    $endTime =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                    // CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->forceDelete();
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();
                     //If new location is creating.
                     if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    
                    do {
                    
                        $monthly_frequency=$request->monthly_frequency;
                        $event_interval_month=$request->event_interval_month;
                        if($monthly_frequency=='MONTHLY_ON_DAY'){
                            $startTime=$startTime;
                            // echo date('Y-m-d', $startTime);
                        }else if($monthly_frequency=='MONTHLY_ON_THE'){
                        $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                            // $startTime=date('Y-m-d', $fourthDay);
                        }else if($monthly_frequency=='MONTHLY_ON_THE_LAST'){
                            $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                            // $startTime=date('Y-m-d', $lastDay);
                        }
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes";  $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL;}else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        $CaseEvent->event_interval_month=$request->event_interval_month;
                        $CaseEvent->monthly_frequency=$request->monthly_frequency;
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at; 
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                        $this->saveEventHistory($CaseEvent->id);

                        
                        $startTime = strtotime('+'.$event_interval_month.' months',$startTime);
                        $i++;
                        } while ($startTime < $endTime);
                }else if($request->event_frequency=='YEARLY'){ 
                    $endTime =  strtotime(date('Y-m-d',strtotime('+25 years')));
                    if($request->end_on!=''){
                        $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                    }
                    
                    $yearly_frequency=$request->yearly_frequency;
                    $Currentweekday= date("l", $startTime ); 
                    $i=0;
                    $OldCaseEvent=CaseEvent::find($request->event_id);
                    $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                    $endTime =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                    // CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->forceDelete();
                    $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
                    $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                    $oldEvents->forceDelete();
                     //If new location is creating.
                     if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    
                    do {
                        $event_interval_year=$request->event_interval_year;
                        if($yearly_frequency=='YEARLY_ON_DAY'){
                            $startTime=$startTime;
                            // echo date('Y-m-d', $startTime);
                        }else if($yearly_frequency=='YEARLY_ON_THE'){
                            $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                        //    echo date('Y-m-d', $startTime);
                        }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                            $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                            // echo date('Y-m-d', $startTime);
                        }
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes";  $CaseEvent->start_time=NULL;
                            $CaseEvent->end_time=NULL;}else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        $CaseEvent->event_interval_year=$request->event_interval_year;
                        $CaseEvent->yearly_frequency=$request->yearly_frequency;

                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                      if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->updated_by=Auth::user()->id; 
                        $CaseEvent->created_by=$OldCaseEvent->created_by;
                        $CaseEvent->created_at=$OldCaseEvent->created_at; 
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                        $this->saveEventHistory($CaseEvent->id);
                        
                        $startTime = strtotime('+'.$event_interval_year.' years',$startTime);
                        $i++;
                        } while ($startTime < $endTime);
                }
            }
        }

        $data=[];
        if(!isset($request->no_case_link)){
            if(isset($request->case_or_lead)) { 
                if($request->text_case_id!=''){
                    $data['event_for_case']=$request->text_case_id;
                }    
                if($request->text_lead_id!=''){
                    $data['event_for_lead']=$request->text_lead_id; ;
                }    
            } 
        }
        $data['event_id']=$CaseEvent->id;
        $data['event_name']=$CaseEvent->event_title;
        $data['user_id']=Auth::User()->id;
        $data['activity']='updated event';
        $data['type']='event';
        $data['action']='update';
        
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);

        session(['popup_success' => 'Event was updated.']);
        return response()->json(['errors'=>'']);
        exit;
      }

    public function saveEditEventPageNew(Request $request)
    {
        // return $request->all();
        if(!isset($request->no_case_link)){
            $validator = \Validator::make($request->all(), [
                'linked_staff_checked_share' => 'required'
            ]);
            if($validator->fails())
            {
                return response()->json(['errors'=>['You must share with at least one firm user<br>You must share with at least one user'],]);
            }
        }
        $authUser = auth()->user();

        //If new location is creating.
        if($request->location_name!=''){
            $locationID= $this->saveLocationOnce($request);
        }

        // Start-End time for all events convert into UTC
        $start_time = date("H:i:s", strtotime(convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->start_date.' '.$request->start_time)), $authUser ->user_timezone)));
        $end_time = date("H:i:s", strtotime(convertTimeToUTCzone(date('Y-m-d H:i:s',strtotime($request->end_date.' '.$request->end_time)), $authUser ->user_timezone)));

        if($request->delete_event_type=='SINGLE_EVENT') {
            $CaseEvent = CaseEvent::find($request->event_id);
            $start_date = date("Y-m-d",  strtotime($request->start_date));
            $end_date = date("Y-m-d",  strtotime($request->end_date));
            if(!isset($request->recuring_event)) {
                $CaseEvent->fill([
                    "event_title" => $request->event_name,
                    "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
                    "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
                    "event_type" => $request->event_type ?? NULL,
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    "start_time" => (!isset($request->all_day)) ? $start_time : NULL,
                    "end_time" => (!isset($request->all_day)) ? $end_time : NULL,
                    "all_day" => (isset($request->all_day)) ? "yes" : "no",
                    "event_description" => $request->description,
                    "recuring_event" => "no",
                    "event_frequency" => $request->event_frequency,
                    "event_interval_day" => $request->event_interval_day,
                    "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
                    "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
                    "parent_evnt_id" => '0',
                    "firm_id" => $authUser->firm_name,
                    "updated_by" => $authUser->id,
                ]);
                // if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                // if(!isset($request->no_case_link)){
                //     if(isset($request->case_or_lead)) { 
                //         if($request->text_case_id!=''){
                //             $CaseEvent->case_id=$request->text_case_id; 
                //         }    
                //         if($request->text_lead_id!=''){
                //             $CaseEvent->lead_id=$request->text_lead_id; 
                //         }    
                //     } 
                //     // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                // }else{
                //     $CaseEvent->case_id=NULL; 
                // }
                // if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                // if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                // if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
                // if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                // if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
                // if(isset($request->all_day)) { $CaseEvent->all_day="yes";
                    // $CaseEvent->start_time=NULL;
                    // $CaseEvent->end_time=NULL;
                // }else{ $CaseEvent->all_day="no";} 
                // if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                // $CaseEvent->recuring_event="no"; 
                // $CaseEvent->event_frequency=$request->event_frequency;
                // // $CaseEvent->event_interval_day=$request->event_interval_day;
                // if(isset($request->no_end_date_checkbox)) { 
                //     $CaseEvent->no_end_date_checkbox="yes"; 
                //     $CaseEvent->end_on=NULL;
                // }else{ 
                //     $CaseEvent->no_end_date_checkbox="no";
                //     $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                // } 
                // if(isset($request->case_location_list)) { $CaseEvent->event_location_id =$request->case_location_list; }
                    // $CaseEvent->event_location_id =$CaseEventLocation->id;
                // if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                // $CaseEvent->parent_evnt_id ='0';
                // $CaseEvent->updated_by=Auth::user()->id; 
                // $CaseEvent->firm_id = auth()->user()->firm_name;
                // $CaseEvent->save();
                $this->saveEventReminder($request->all(),$CaseEvent->id); 
                $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
                $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                $this->saveEventHistory($CaseEvent->id);
            } else if($CaseEvent->parent_evnt_id != 0 && $CaseEvent->recuring_event == "yes") {
                if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                if(!isset($request->no_case_link)){
                    if(isset($request->case_or_lead)) { 
                        if($request->text_case_id!=''){
                            $CaseEvent->case_id=$request->text_case_id; 
                        }    
                        if($request->text_lead_id!=''){
                            $CaseEvent->lead_id=$request->text_lead_id; 
                        }    
                    } 
                    // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                }else{
                    $CaseEvent->case_id=NULL; 
                }
                if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                if(isset($request->start_time)) { $CaseEvent->start_time=$start_time; } 
                if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                if(isset($request->end_time)) { $CaseEvent->end_time=$end_time; } 
                if(isset($request->all_day)) { $CaseEvent->all_day="yes";
                    $CaseEvent->start_time=NULL;
                    $CaseEvent->end_time=NULL;
                }else{ $CaseEvent->all_day="no";} 
                if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                $CaseEvent->recuring_event=$CaseEvent->recurring_event; 
                $CaseEvent->event_frequency=$request->event_frequency;
                $CaseEvent->event_interval_day=$request->event_interval_day;
                if(isset($request->no_end_date_checkbox)) { 
                    $CaseEvent->no_end_date_checkbox="yes"; 
                    $CaseEvent->end_on=NULL;
                }else{ 
                    $CaseEvent->no_end_date_checkbox="no";
                    $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                } 
                if(isset($request->case_location_list)) { $CaseEvent->event_location_id =$request->case_location_list; }

                //If new location is creating.
                if($request->location_name!=''){
                    $CaseEventLocation = new CaseEventLocation;
                    $CaseEventLocation->location_name=$request->location_name;
                    $CaseEventLocation->address1=$request->address;
                    $CaseEventLocation->address2=$request->address2;
                    $CaseEventLocation->city=$request->city;
                    $CaseEventLocation->state=$request->state;
                    $CaseEventLocation->postal_code=$request->postal_code;
                    $CaseEventLocation->country=$request->country;
                    $CaseEventLocation->location_future_use=($request->location_future_use)?'yes':'no';
                    $CaseEventLocation->created_by=Auth::user()->id; 
                    $CaseEventLocation->save();
                    $CaseEvent->event_location_id =$CaseEventLocation->id;
                }
                if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                $CaseEvent->parent_evnt_id = $CaseEvent->parent_evnt_id;
                $CaseEvent->updated_by=Auth::user()->id;
                $CaseEvent->firm_id = auth()->user()->firm_name; 
                $CaseEvent->save();
                $this->saveEventReminder($request->all(),$CaseEvent->id); 
                $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
                $this->saveContactLeadData($request->all(),$CaseEvent->id); 
                $this->saveEventHistory($CaseEvent->id);
            } else {
                $startTime = strtotime($request->start_date);
                $endTime =  strtotime(date('Y-m-d',strtotime('+365 days')));
                if($request->end_on!=''){
                    $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                if($request->event_frequency=='DAILY')
                {
                    $i=0;
                    $event_interval_day=$request->event_interval_day;

                
                    //If new location is creating.
                    if($request->location_name!=''){
                    $locationID= $this->saveLocationOnce($request);
                    }

                    do {
                        
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        $CaseEvent->event_interval_day=$request->event_interval_day;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                            
                        } 
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->firm_id = auth()->user()->firm_name;
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                        // $this->saveEventHistory($CaseEvent->id);
                        
                        $startTime = strtotime('+'.$event_interval_day.' day',$startTime); 
                        $i++;
                    } while ($startTime < $endTime);
                } else if($request->event_frequency=='EVERY_BUSINESS_DAY')
                { 
                    $i=0;
                    //If new location is creating.
                    if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    do {
                        
                        $timestamp = $startTime;
                        $weekday= date("l", $timestamp );            
                        if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                        }else {
                            $start_date = date("Y-m-d", $startTime);
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date = date("Y-m-d", $startTime);
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";} 
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            } 
                            
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            } 
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->created_by=Auth::user()->id; 
                            $CaseEvent->save();
                            if($i==0) { 
                                $parentCaseID=$CaseEvent->id;
                                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                                $CaseEvent->save();
                            }else{
                                $CaseEvent->parent_evnt_id =  $parentCaseID;
                                $CaseEvent->save();
                            }
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                            // $this->saveEventHistory($CaseEvent->id);
                        }
                        $i++;
                        $startTime = strtotime('+1 day',$startTime); 
                        } while ($startTime < $endTime);
                }else if($request->event_frequency=='WEEKLY')
                {
                    $i=0;
        
                    //If new location is creating.
                    if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                        do {
                    
                        $timestamp = $startTime;
                        $weekday= date("l", $timestamp );       
                        if ($weekday==date("l")) { 
                            $start_date = date("Y-m-d", $startTime);
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date = date("Y-m-d", $startTime);
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;

                            $CaseEvent->daily_weekname=$request->daily_weekname;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            } 
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            }   
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->created_by=Auth::user()->id; 
                            $CaseEvent->save();
                            if($i==0) { 
                                $parentCaseID=$CaseEvent->id;
                                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                                $CaseEvent->save();
                            }else{
                                $CaseEvent->parent_evnt_id =  $parentCaseID;
                                $CaseEvent->save();
                            }
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                            // $this->saveEventHistory($CaseEvent->id);

                        }  $startTime = strtotime('+1 day',$startTime); 
                        $i++;
                        } while ($startTime < $endTime);
                }else if($request->event_frequency=='CUSTOM')
                { 
                    $i=0;
                    $weekFirstDay=date("Y-m-d", strtotime('monday this week'));
                    $start = new DateTime($weekFirstDay);
                    $startClone = new DateTime($weekFirstDay);

                    
                    
                    if($request->end_on!=''){
                        $end=new DateTime($request->end_on);
                    }else{
                        $end=$startClone->add(new DateInterval('P365D'));
                    }
                    //$end = new DateTime( '2021-09-28 23:59:59');
                    $interval = new DateInterval('P1D');
                    $period = new DatePeriod($start, $interval, $end);
                    
                    $weekInterval = $request->daily_weekname;
                    $fakeWeek = 0;
                    $currentWeek = $start->format('W');
                        //If new location is creating.
                        if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    foreach ($period as $date) {
                        if ($date->format('W') !== $currentWeek) {
                            $currentWeek = $date->format('W');
                            $fakeWeek++;
                        }
                    
                        if ($fakeWeek % $weekInterval !== 0) {
                            continue;
                        }
                    
                        $dayOfWeek = $date->format('l');
                        if(in_array($dayOfWeek,$request->custom)){

                            $start_date = $date->format('Y-m-d');
                            $start_time = date("H:i:s", strtotime($request->start_time));
                            $end_date =$date->format('Y-m-d');
                            $end_time = date("H:i:s", strtotime($request->end_time));
                            $CaseEvent = new CaseEvent;
                            if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                            if(!isset($request->no_case_link)){
                                if(isset($request->case_or_lead)) { 
                                    if($request->text_case_id!=''){
                                        $CaseEvent->case_id=$request->text_case_id; 
                                    }    
                                    if($request->text_lead_id!=''){
                                        $CaseEvent->lead_id=$request->text_lead_id; 
                                    }    
                                } 
                                // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                            }
                            if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                            if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                            if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                            if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                            if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                            if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                            if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}                    
                            $CaseEvent->recuring_event="yes";
                            $CaseEvent->event_frequency=$request->event_frequency;

                            $CaseEvent->daily_weekname=$request->daily_weekname;
                            if(isset($request->no_end_date_checkbox)) { 
                                $CaseEvent->no_end_date_checkbox="yes"; 
                                $CaseEvent->end_on=NULL;
                            }else{ 
                                $CaseEvent->no_end_date_checkbox="no";
                                $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                            } 
                            if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                                $CaseEvent->event_location_id=$request->case_location_list; 
                            }else{  
                                $CaseEvent->event_location_id=($locationID)??NULL;
                            }   
                            
                            if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                            $CaseEvent->created_by=Auth::user()->id; 
                            $CaseEvent->save();
                            if($i==0) { 
                                $parentCaseID=$CaseEvent->id;
                                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                                $CaseEvent->save();
                            }else{
                                $CaseEvent->parent_evnt_id =  $parentCaseID;
                                $CaseEvent->save();
                            }
                            $i++;
                            $this->saveEventReminder($request->all(),$CaseEvent->id); 
                            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                            $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                            // $this->saveEventHistory($CaseEvent->id);
                        }
                    }
                    
                }else if($request->event_frequency=='MONTHLY')
                { 
                    $Currentweekday= date("l", $startTime ); 
                    $i=0;
                        //If new location is creating.
                        if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                        do {
                    
                        $monthly_frequency=$request->monthly_frequency;
                        $event_interval_month=$request->event_interval_month;
                        if($monthly_frequency=='MONTHLY_ON_DAY'){
                            $startTime=$startTime;
                            // echo date('Y-m-d', $startTime);
                        }else if($monthly_frequency=='MONTHLY_ON_THE'){
                        $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                            // $startTime=date('Y-m-d', $fourthDay);
                        }else if($monthly_frequency=='MONTHLY_ON_THE_LAST'){
                            $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                            // $startTime=date('Y-m-d', $lastDay);
                        }
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        $CaseEvent->event_interval_month=$request->event_interval_month;
                        $CaseEvent->monthly_frequency=$request->monthly_frequency;
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                        //  $this->saveEventHistory($CaseEvent->id);
                        $startTime = strtotime('+'.$event_interval_month.' months',$startTime);
                        $i++;
                        } while ($startTime < $endTime);
                }else if($request->event_frequency=='YEARLY'){ 
                    $endTime =  strtotime(date('Y-m-d',strtotime('+25 years')));
                    if($request->end_on!=''){
                        $endTime =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                    }
                    $yearly_frequency=$request->yearly_frequency;
                    $Currentweekday= date("l", $startTime ); 
                    $i=0;
                    //If new location is creating.
                    if($request->location_name!=''){
                        $locationID= $this->saveLocationOnce($request);
                    }
                    do {
                        $event_interval_year=$request->event_interval_year;
                        if($yearly_frequency=='YEARLY_ON_DAY'){
                            $startTime=$startTime;
                            // echo date('Y-m-d', $startTime);
                        }else if($yearly_frequency=='YEARLY_ON_THE'){
                        $startTime = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startTime);
                        //    echo date('Y-m-d', $startTime);
                        }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                            $startTime = strtotime("last ".strtolower($Currentweekday)." of this month",$startTime);
                            // echo date('Y-m-d', $startTime);
                        }
                        $start_date = date("Y-m-d", $startTime);
                        $start_time = date("H:i:s", strtotime($request->start_time));
                        $end_date = date("Y-m-d", $startTime);
                        $end_time = date("H:i:s", strtotime($request->end_time));
                        $CaseEvent = new CaseEvent;
                        if(isset($request->event_name)) { $CaseEvent->event_title=$request->event_name; } 
                        if(!isset($request->no_case_link)){
                            if(isset($request->case_or_lead)) { 
                                if($request->text_case_id!=''){
                                    $CaseEvent->case_id=$request->text_case_id; 
                                }    
                                if($request->text_lead_id!=''){
                                    $CaseEvent->lead_id=$request->text_lead_id; 
                                }    
                            } 
                            // if(isset($request->case_or_lead)) { $CaseEvent->case_id=$request->case_or_lead; } 
                        }
                        if(isset($request->event_type) && $request->event_type!=0) { $CaseEvent->event_type=$request->event_type; }else{ $CaseEvent->event_type=NULL;}
                        if(isset($request->start_date)) { $CaseEvent->start_date=$start_date; } 
                        if(isset($request->start_time) && !isset($request->all_day)) { $CaseEvent->start_time=$start_time; } 
                        if(isset($request->end_date)) { $CaseEvent->end_date=$end_date; } 
                        if(isset($request->end_time) && !isset($request->all_day)) { $CaseEvent->end_time=$end_time; } 
                        if(isset($request->all_day)) { $CaseEvent->all_day="yes"; }else{ $CaseEvent->all_day="no";} 
                        if(isset($request->description)) { $CaseEvent->event_description=$request->description; }else{ $CaseEvent->event_description="";}
                        $CaseEvent->recuring_event="yes";
                        $CaseEvent->event_frequency=$request->event_frequency;
                        $CaseEvent->event_interval_year=$request->event_interval_year;
                        $CaseEvent->yearly_frequency=$request->yearly_frequency;

                        if(isset($request->no_end_date_checkbox)) { 
                            $CaseEvent->no_end_date_checkbox="yes"; 
                            $CaseEvent->end_on=NULL;
                        }else{ 
                            $CaseEvent->no_end_date_checkbox="no";
                            $CaseEvent->end_on=date("Y-m-d",strtotime($request->end_on));
                        } 
                        if($request->case_location_list!="0" &&  isset($request->case_location_list)) { 
                            $CaseEvent->event_location_id=$request->case_location_list; 
                        }else{  
                            $CaseEvent->event_location_id=($locationID)??NULL;
                        }   
                        
                        if(isset($request->is_event_private)) { $CaseEvent->is_event_private ='yes'; }else{ $CaseEvent->is_event_private ='no'; }
                        $CaseEvent->created_by=Auth::user()->id; 
                        $CaseEvent->save();
                        if($i==0) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder($request->all(),$CaseEvent->id); 
                        $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                        $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                        // $this->saveEventHistory($CaseEvent->id);

                        
                        $startTime = strtotime('+'.$event_interval_year.' years',$startTime);
                        $i++;
                        } while ($startTime < $endTime);
                }

                // Delete old/current edit event
                // CaseEvent::whereId($request->event_id)->delete();
                $oldEvent = CaseEvent::whereId($request->event_id)->first();
                if($oldEvent) {
                    $oldEvent->deleteChildTableRecords([$request->event_id]);
                    $oldEvent->forceDelete();
                }
            }

        }

        /* $data=[];
        if(!isset($request->no_case_link)){
            if(isset($request->case_or_lead)) { 
                if($request->text_case_id!=''){
                    $data['event_for_case']=$request->text_case_id;
                }    
                if($request->text_lead_id!=''){
                    $data['event_for_lead']=$request->text_lead_id; ;
                }    
            } 
        }
        $data['event_id']=$CaseEvent->id;
        $data['event_name']=$CaseEvent->event_title;
        $data['user_id']=Auth::User()->id;
        $data['activity']='updated event';
        $data['type']='event';
        $data['action']='update';
        
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data); */

        session(['popup_success' => 'Event was updated.']);
        return response()->json(['errors'=>'']);
        exit;
    }
      public function loadEditEventPage(Request $request)
      {

            $evnt_id=$request->evnt_id;
            // $delete="DELETE t1 FROM case_event_linked_staff t1 INNER JOIN case_event_linked_staff t2 WHERE t1.id < t2.id AND t1.event_id =".$evnt_id." AND t1.user_id = t2.user_id";
            // DB::delete($delete); 

            $evetData=CaseEvent::find($evnt_id);
            $eventReminderData=CaseEventReminder::where('event_id',$evnt_id)->get();

            $case_id=$evetData->case_id;
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

            $country = Countries::get();
            $eventLocation = CaseEventLocation::where("location_future_use","yes")->get();
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
            $eventLocationAdded=[];
            if($evetData->event_location_id!=""){
                $eventLocationAdded = CaseEventLocation::where("id",$evetData->event_location_id)->first();
            }
        
            $getEventColorCode = EventType::select("color_code","id")->where('id',$evetData->event_type)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->pluck('color_code');

            $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();

            return view('case.event.loadEditEvent',compact('CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType','evetData','case_id','eventReminderData','userData','updatedEvenByUserData','getEventColorCode','eventLocationAdded','caseLeadList'));          
     }


     public function loadSingleEditEventPage(Request $request)
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
    }
    public function loadCaseClientAndLeads(Request $request)
    {
        $case_id=$request->case_id;
        $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","users.id as user_id","users_additional_info.client_portal_enable")->where("case_client_selection.case_id",$case_id)->get();
        
        return view('case.event.caseClientLeadSection',compact('caseCllientSelection'));     
        exit;    
   }
       public function loadCommentPopup(Request $request)
       {
        $CaseMasterData=[];
        $evnt_id=$request->evnt_id;
        $evetData=CaseEvent::find($evnt_id);
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
      }


      public function loadCommentHistory(Request $request)
      {
        $evnt_id=$request->event_id;
        $evetData=CaseEvent::find($evnt_id);

          //Event created By user name
        $eventCreatedBy = User::select("first_name","last_name","id","user_level","user_type")->where("id",$evetData->created_by)->first();
       
        $updatedEvenByUserData='';
        // if($evetData->updated_by!=NULL){
        //     //Event updated By user name
        //     $updatedEvenByUserData = User::select("first_name","last_name","id","user_level","user_type")->where("id",$evetData->updated_by)->first();
        // }

         //Event updated data
        //  $updatedEvenByUserData = CaseEventComment::join('users','users.id','=','case_event_comment.created_by')
        //  ->select("users.id","users.first_name","users.last_name","case_event_comment.comment","user_type","case_event_comment.created_at")
        //  ->where("case_event_comment.event_id",$evnt_id)->where("case_event_comment.action_type","1")->orderBy('case_event_comment.created_at','DESC')->get();
            
         

        //Event comment data
        $commentData = CaseEventComment::join('users','users.id','=','case_event_comment.created_by')
        ->select("users.id","users.first_name","users.last_name","case_event_comment.comment","user_type","case_event_comment.created_at","case_event_comment.action_type")->where("case_event_comment.event_id",$evnt_id)->orderBy('case_event_comment.created_at','DESC')->get();
            
        return view('case.event.loadEventHistory',compact('evetData','eventCreatedBy','updatedEvenByUserData','commentData'));     
        exit;    
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

          $loadFirmUser = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->whereNotIn('id',$caseLinkedStaffList)->get();
            if(isset($request->event_id)){
                $nonLinkedSaved = CaseEventLinkedStaff::select("case_event_linked_staff.user_id")->where("case_event_linked_staff.event_id",$request->event_id)->where('is_linked','no')->get()->pluck('user_id');
                $nonLinkedSaved= $nonLinkedSaved->toArray();
            }
          
          return view('case.event.caseNoneLinkedStaff',compact('loadFirmUser','nonLinkedSaved'));     
          exit;    
     }
     public function saveEventReminder($request,$event_id)
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
            /* $event = CaseEvent::whereId($event_id)->first();
            if($request->reminder_frequncy == "week" || $request->reminder_frequncy == "day") {
                $eventStartDate = Carbon::parse($event->start_date);
                if($request->reminder_frequncy == "week") {
                    $remindTime = $eventStartDate->subWeeks($request->reminer_number)->format('Y-m-d H:i:s');
                } else {
                    $remindTime = $eventStartDate->subDays($request->reminer_number)->format('Y-m-d H:i:s');
                }
            } else if($request->reminder_frequncy == "hour") {
                $eventStartTime = @$event->start_date." ".@$event->start_time;
                $remindTime = Carbon::parse($eventStartTime)->subHours($request->reminer_number)->format('Y-m-d H:i:s');
            } else if($request->reminder_frequncy == "minute") {
                $eventStartTime = @$event->start_date." ".@$event->start_time;
                $remindTime = Carbon::parse($eventStartTime)->subMinutes($request->reminer_number)->format('Y-m-d H:i:s');
            } else {
                $remindTime = Carbon::now()->format('Y-m-d H:i:s');
            }
            // return $remindTime;
            // if($remindTime != '') 
                return convertTimeToUTCzone($remindTime, auth()->user()->user_timezone); */

            $CaseEventReminder->save();
        }
    }
    public function saveLinkedStaffToEvent($request,$event_id)
    {
        CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by", Auth::user()->id)->where("is_linked","yes")->forceDelete();
        if(isset($request['linked_staff_checked_share'])){
            $alreadyAdded=[];
            for($i=0;$i<count($request['linked_staff_checked_share']);$i++){
                $CaseEventLinkedStaff = new CaseEventLinkedStaff;
                $CaseEventLinkedStaff->event_id=$event_id; 
                $CaseEventLinkedStaff->user_id=$request['linked_staff_checked_share'][$i];
                $attend = "no";
                if(isset($request->linked_staff_checked_attend) && in_array($request['linked_staff_checked_share'][$i], $request->linked_staff_checked_attend)){
                    $attend = "yes";
                }
                // if(isset($request['linked_staff_checked_attend'][$i])){
                //     $attend="yes";
                // }else{
                //     $attend="no";
                // }
                $CaseEventLinkedStaff->is_linked='yes';
                $CaseEventLinkedStaff->attending=$attend;
                $CaseEventLinkedStaff->created_by=Auth::user()->id; 
                if(!in_array($request['linked_staff_checked_share'][$i],$alreadyAdded)){
                    $CaseEventLinkedStaff->save();
                }
                $alreadyAdded[]=$request['linked_staff_checked_share'][$i];
            }
        }
   }
   public function saveNonLinkedStaffToEvent($request,$event_id)
    {
    //    print_r($request);
        CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by", Auth::user()->id)->where("is_linked","no")->forceDelete();
        if(isset($request['share_checkbox_nonlinked'])){
            $alreadyAdded=[];
            for($i=0;$i<count(array_unique($request['share_checkbox_nonlinked']));$i++){
                $CaseEventLinkedStaff = new CaseEventLinkedStaff;
                $CaseEventLinkedStaff->event_id=$event_id; 
                $CaseEventLinkedStaff->user_id=$request['share_checkbox_nonlinked'][$i];
                if(isset($request['attend_checkbox_nonlinked'][$i])){
                    $attend="yes";
                }else{
                    $attend="no";
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
   }
   public function saveContactLeadData($request,$event_id)
   {
    //   print_r($reques[t);exit;
       CaseEventLinkedContactLead::where("event_id", $event_id)->where("created_by", Auth::user()->id)->forceDelete();
       if(isset($request['LeadInviteClientCheckbox'])){
           $alreadyAdded=[];
           for($i=0;$i<count(array_unique($request['LeadInviteClientCheckbox']));$i++){
               $CaseEventLinkedContactLead = new CaseEventLinkedContactLead;
               $CaseEventLinkedContactLead->event_id=$event_id; 
               $CaseEventLinkedContactLead->user_type='lead'; 
               $CaseEventLinkedContactLead->lead_id=$request['LeadInviteClientCheckbox'][$i];
               if(isset($request['LeadAttendClientCheckbox'][$i])){
                   $attend="yes";
               }else{
                   $attend="no";
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
        $alreadyAdded=[];
        for($i=0;$i<count(array_unique($request['ContactInviteClientCheckbox']));$i++){
            $CaseEventLinkedContactLead = new CaseEventLinkedContactLead;
            $CaseEventLinkedContactLead->event_id=$event_id; 
            $CaseEventLinkedContactLead->user_type='contact'; 
            $CaseEventLinkedContactLead->contact_id=$request['ContactInviteClientCheckbox'][$i];
            if(isset($request['ContactAttendClientCheckbox'][$i])){
                $attend="yes";
            }else{
                $attend="no";
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

  }
   public function deleteEventPopup(Request $request)
   {
       $event_id=$request->event_id;
       $CaseEvent = CaseEvent::find($event_id);

       return view('case.event.deleteEvent',compact('event_id','CaseEvent'));     
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
        
        $eventId=$request->event_id;
        $CaseEvent = CaseEvent::find($eventId);

        if($request->delete_event_type=='SINGLE_EVENT'){
            // CaseEvent::where("id", $eventId)->delete();
            $oldEvents = CaseEvent::where('parent_evnt_id',$CaseEvent->parent_evnt_id);
            $CaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
            $oldEvents->forceDelete();

        }else if($request->delete_event_type=='THIS_AND_FOLLOWING_EVENTS'){
            // CaseEvent::where("parent_evnt_id", $CaseEvent->parent_evnt_id)->whereDate('start_date',">=",$CaseEvent->start_date)->delete();
            $oldEvents = CaseEvent::where('parent_evnt_id',$CaseEvent->parent_evnt_id)->where('id',">=",$CaseEvent->id);
            $CaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
            $oldEvents->forceDelete();
        
        }else if($request->delete_event_type=='ALL_EVENTS'){
            // CaseEvent::where("parent_evnt_id", $CaseEvent->parent_evnt_id)->delete();
            $oldEvents = CaseEvent::where('parent_evnt_id',$CaseEvent->parent_evnt_id);
            $CaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
            $oldEvents->forceDelete();
        }

        //Master Event History
        $data=[];
        if($CaseEvent->case_id!=NULL){
            $data['event_for_case']=$CaseEvent->case_id;
        }    
        if($CaseEvent->lead_id!=NULL){
            $data['event_for_lead']=$CaseEvent->lead_id;
        }    
        $data['event_id']=$CaseEvent->id;
        $data['event_name']=$CaseEvent->event_title;
        $data['user_id']=Auth::User()->id;
        $data['activity']='deleted event';
        $data['type']='event';
        $data['action']='delete';
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);
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
       
        $event_id=$request->event_id;
        $CaseEventComment =new CaseEventComment;
        $CaseEventComment->event_id=$request->event_id;
        $CaseEventComment->comment=$request->delta;
        $CaseEventComment->action_type="0";
        $CaseEventComment->created_by=Auth::user()->id; 
        $CaseEventComment->save();

        $eventData=CaseEvent::find($request->event_id);
        $CaseEventLinkedContactLead=CaseEventLinkedContactLead::where("event_id",$request->event_id)->get();
        if(!$CaseEventLinkedContactLead->isEmpty()){
            CommentEmail::dispatch($request->event_id,Auth::User()->firm_name,$CaseEventComment->id,Auth::User()->id);

            // CommentEmail::dispatch($request->event_id)->delay(now()->addMinutes(1));


            // $CommonController= new CommonController();
            // foreach($CaseEventLinkedContactLead as $k=>$v){
            //     $firmData=Firm::find(Auth::User()->firm_name); 
            //     if($v->lead_id!=NULL){
            //         $findUSer=User::find($v->lead_id);
            //     }else{
            //         $findUSer=User::find($v->contact_id);
            //     }   
            //     $getTemplateData = EmailTemplate::find(22);
            //     $email=$findUSer['email'];
            //     $fullName=$findUSer['first_name']." ".$findUSer['middle']." ".$findUSer['last_name'];
            //     $sender=Auth::User()->first_name." ".Auth::User()->last_name;
              
            //     $timezone=$findUSer->user_timezone;
            //     $convertedDate=$CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($eventData->start_date ." " .$eventData->start_time)),$timezone);
            //     $Edates=date('m-d-Y h:i A',strtotime($convertedDate));


            //     $mail_body = $getTemplateData->content;
            //     $mail_body = str_replace('{email}', $email,$mail_body);
            //     $mail_body = str_replace('{receiver}', $fullName,$mail_body);
            //     $mail_body = str_replace('{sender}', $sender,$mail_body);
            //     $mail_body = str_replace('{event_name}', $eventData->event_title,$mail_body);
            //     $mail_body = str_replace('{date_time}', $Edates ,$mail_body);
            //     $mail_body = str_replace('{comment}', $CaseEventComment->comment,$mail_body);
            //     $mail_body = str_replace('{EmailLogo1}', url('/images/logo.png'), $mail_body);
            //     $mail_body = str_replace('{support_email}', SUPPORT_EMAIL, $mail_body);
            //     $mail_body = str_replace('{regards}', $firmData['firm_name'], $mail_body);  
            //     $mail_body = str_replace('{site_title}', TITLE, $mail_body);  
            //     $mail_body = str_replace('{year}', date('Y'), $mail_body);        
            //     $mail_body = str_replace('{EmailLinkOnLogo}', BASE_LOGO_URL, $mail_body);
            //     $mail_body = str_replace('{url}', BASE_URL."login", $mail_body);  

            //     $userEmail = [
            //         "from" => FROM_EMAIL,
            //         "from_title" => $firmData['firm_name'],
            //         "subject" => $getTemplateData->subject,
            //         "to" => $email,
            //         "full_name" => $fullName,
            //         "mail_body" => $mail_body
            //         ];
            //     $sendEmail = $this->sendMail($userEmail);
            //}
        }
        return response()->json(['errors'=>'']);
        exit;    
    }
    public function saveEventHistory($request)
    {
        $CaseEventComment =new CaseEventComment;
        $CaseEventComment->event_id=$request;
        $CaseEventComment->comment=NULL;
        $CaseEventComment->created_by=Auth::user()->id; 
        $CaseEventComment->action_type="1";
        $CaseEventComment->save();
    }
    public function loadReminderPopup(Request $request)
    {
        
        $event_id=$request->evnt_id;
        $eventReminderData = CaseEventReminder::where("event_id",$event_id)->get();
        return view('case.event.loadReminderPopup',compact('event_id','eventReminderData'));     
        exit;    
    }

    public function saveReminderPopup(Request $request)
    {
        $event_id=$request->event_id;
        $this->saveEventReminder($request,$event_id);
        return response()->json(['errors'=>'','msg'=>'Reminders successfully updated']);
        exit;    
    }
    public function loadReminderHistory(Request $request)
    {
      $evnt_id=$request->event_id;
      $evetData=CaseEvent::find($evnt_id);
      $eventReminderData=CaseEventReminder::where('event_id',$evnt_id)->get();
      
        return view('case.event.loadReminderHistory',compact('evetData','eventReminderData'));     
        exit;    
   }

   public function loadReminderPopupIndex(Request $request)
    {
        
        $event_id=$request->evnt_id;
        $eventReminderData = CaseEventReminder::where("event_id",$event_id)->get();
        return view('case.event.loadReminderPopupIndex',compact('event_id','eventReminderData'));     
        exit;    
    }

    public function saveSOLEventIntoCalender($case_id){
        $CaseData=CaseMaster::find($case_id);    
        $CaseEvent = new CaseEvent;
            $CaseEvent->event_title=$CaseData->case_title;  
            $CaseEvent->case_id=$case_id;
            $CaseEvent->event_type=NULL;
            $CaseEvent->start_date=$CaseData->case_statute_date; 
            $CaseEvent->end_date=$CaseData->case_statute_date; 
            $CaseEvent->all_day="yes";
            $CaseEvent->is_SOL="yes";
            $CaseEvent->event_description="";
            $CaseEvent->recuring_event="no"; 
            $CaseEvent->event_location_id ='0';
            $CaseEvent->is_event_private ='no';
            $CaseEvent->parent_evnt_id ='0';
            $CaseEvent->created_by=Auth::user()->id; 
            $CaseEvent->save();
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
        $caseLinkeSaved=array();
        $caseLinkeSavedAttending=array();
        $case_id=$request->case_id;
        $event_id=$request->event_id;
        $nonLinkedSaved=[];
        $caseLinkeSavedAttendingContact=$caseLinkeSavedInviteContact=[];
        $from='';
        //Client List
        $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->leftJoin('users_additional_info','users_additional_info.user_id','=','case_client_selection.selected_user')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","users.id as user_id","users_additional_info.client_portal_enable")->where("case_client_selection.case_id",$case_id)->get();


        //Non linked staff List
        $caseNoneLinkedStaffList = CaseStaff::select("case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get()->pluck('case_staff_user_id');
        $loadFirmUser = User::select("first_name","last_name","id","parent_user")->whereIn("parent_user",[Auth::user()->id,"0"])->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->whereNotIn('id',$caseNoneLinkedStaffList)->get();
        
        //Linked Staff List
        $caseLinkedStaffList = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get();
      
        if(isset($request->event_id) && $request->event_id!=''){
            $caseLinkeSaved = CaseEventLinkedStaff::select("case_event_linked_staff.user_id")->where("case_event_linked_staff.event_id",$request->event_id)->get()->pluck('user_id');
            $caseLinkeSaved= $caseLinkeSaved->toArray();

            $caseLinkeSavedAttending = CaseEventLinkedStaff::select("case_event_linked_staff.user_id")->where("case_event_linked_staff.event_id",$request->event_id)->where('attending','yes')->get()->pluck('user_id');
            $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();

            $caseLinkeSavedAttendingContact = CaseEventLinkedContactLead::select("case_event_linked_contact_lead.contact_id")->where("case_event_linked_contact_lead.event_id",$request->event_id)->where('attending','yes')->get()->pluck('contact_id');
            $caseLinkeSavedAttendingContact= $caseLinkeSavedAttendingContact->toArray();

            $caseLinkeSavedInviteContact = CaseEventLinkedContactLead::select("case_event_linked_contact_lead.contact_id")->where("case_event_linked_contact_lead.event_id",$request->event_id)->where('invite','yes')->get()->pluck('contact_id');
            $caseLinkeSavedInviteContact= $caseLinkeSavedInviteContact->toArray();


           
            $from="edit";
        }
          
        
       
        return view('case.event.loadEventRightSection',compact('caseCllientSelection','loadFirmUser','case_id','caseLinkedStaffList','caseLinkeSaved','caseLinkeSavedAttending','from','caseLinkeSavedAttendingContact','caseLinkeSavedInviteContact'));     
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
        // $caseCllientSelection = User::select("first_name","last_name","id","parent_user","user_level")->where("id",$request->lead_id)->get();
        $caseCllientSelection = User::leftJoin('lead_additional_info','lead_additional_info.user_id','=','users.id')->select("users.id","users.first_name","users.last_name","users.user_level","users.parent_user","lead_additional_info.client_portal_enable")->where("users.id",$request->lead_id)->get();

        //Load all staff
        $loadFirmUser = User::select("first_name","last_name","id","parent_user")->whereIn("parent_user",[Auth::user()->id,"0"])->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->get();
        
        if(isset($request->event_id) && $request->event_id!=''){
            $caseLinkeSaved = CaseEventLinkedStaff::select("case_event_linked_staff.user_id")->where("case_event_linked_staff.event_id",$request->event_id)->get()->pluck('user_id');
            $caseLinkeSaved= $caseLinkeSaved->toArray();

            $caseLinkeSavedAttending = CaseEventLinkedStaff::select("case_event_linked_staff.user_id")->where("case_event_linked_staff.event_id",$request->event_id)->where('attending','yes')->get()->pluck('user_id');
            $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();

            $caseLinkeSavedAttendingLead = CaseEventLinkedContactLead::select("case_event_linked_contact_lead.lead_id")->where("case_event_linked_contact_lead.event_id",$request->event_id)->where('attending','yes')->get()->pluck('lead_id');
            $caseLinkeSavedAttendingLead= $caseLinkeSavedAttendingLead->toArray();

            $caseLinkeSavedInviteLead = CaseEventLinkedContactLead::select("case_event_linked_contact_lead.lead_id")->where("case_event_linked_contact_lead.event_id",$request->event_id)->where('invite','yes')->get()->pluck('lead_id');
            $caseLinkeSavedInviteLead= $caseLinkeSavedInviteLead->toArray();
            
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
            session(['popup_success' => 'Case has been updated.']);
            return response()->json(['errors'=>'','id'=>'']);
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
            // Task::where("case_id", $case_id)->delete();
            // TaskTimeEntry::where("case_id", $case_id)->delete();
            // ExpenseEntry::where("case_id", $case_id)->delete();
            // CaseNotes::where("case_id", $case_id)->delete();
            // Invoices::where("case_id", $case_id)->delete();
            // CaseEvent::where("case_id", $case_id)->delete();
            // CaseMaster::where("id", $case_id)->delete();


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

            session(['popup_success' => 'Case has been deleted.']);
            return response()->json(['errors'=>'']);
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
               $data['case_id']=NULL;
               $data['notes_for_case']=$request->case_id;
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
        }
        $task = $task->where("task.created_by",Auth::user()->id);
        $task = $task->orderBy('task_due_on', 'ASC');
        $task = $task->paginate(10);

        
        return view('case.view.taskDynamic',compact('task'));     
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

       $caseStageList = CaseStage::select("*")->where("status","1");
       $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
       $getChildUsers[]=Auth::user()->id;
       $caseStageList = $caseStageList->whereIn("created_by",$getChildUsers);          
       $caseStageList=$caseStageList->orderBy('stage_order','ASC')->get();
    
       $CaseStageHistory=[];
       if($CaseMaster->case_status!=0){
        $CaseStageHistory=CaseStageUpdate::select("*")->where("case_id",$case_id)->where("stage_id",$CaseMaster->case_status)->first();
       }

       $AllCaseStageHistory=CaseStageUpdate::select("*")->where("case_id",$case_id)->get()->toArray();
       
       return view('case.loadCaseTimeline',compact('CaseMaster','caseStageList','CaseStageHistory','AllCaseStageHistory','case_id'));
   }

   public function saveCaseHistory(Request $request)
   {
    //    print_r($request->all());
       $validator = \Validator::make($request->all(), [
           'start_date.*' => 'required',
           'case_id' => 'required',
           'end_date.*' => 'required'
       ],['start_date.*.required'=>"Start date is required field.",'end_date.*.required'=>"End date is required field."]);
       if($validator->fails())
       {
           return response()->json(['errors'=>$validator->errors()->all()]);
       }else{
            $ids=[];
            foreach($request->old_state_id as $k=>$v){
                $caseStageHistory = CaseStageUpdate::find($v);
                $caseStageHistory->created_at=date('Y-m-d',strtotime($request->old_start_date[$v]));
                $caseStageHistory->save();
                $ids[]=$v;  
            }
            if(!empty($ids)){
                $CaseStageUpdate=CaseStageUpdate::whereNotIn("id",$ids)->where("case_id",$request->case_id)->delete();
            }
            
           for($i=0; $i<count($request->case_status);$i++){
                $caseStageHistory = new CaseStageUpdate;
                $caseStageHistory->stage_id=$request->case_status[$i];
                $caseStageHistory->case_id=$request->case_id;
                $caseStageHistory->created_by=Auth::user()->id; 
                $caseStageHistory->created_at=date('Y-m-d',strtotime($request->start_date[$i]));
                $caseStageHistory->save();
           }
           return response()->json(['errors'=>'']);
           exit;
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
        $IntakeForm=IntakeForm::where("firm_name",Auth::User()->firm_name)->get();
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
        $allForms = $allForms->select("intake_form.id as intake_form_id","case_intake_form.created_at as case_intake_form_created_at","intake_form.*","case_intake_form.*");      
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
      return view('case.loadReminderPopup',compact('case_id','CaseSolReminder'));     
      exit;    
  }

  public function saveCaseReminderPopup(Request $request)
  {
      $request=$request->all();
    //   print_r($request);exit;
      $case_id=$request['case_id'];
        CaseSolReminder::where("case_id", $case_id)->delete();
        for($i=0;$i<count($request['reminder_type'])-1;$i++){
            $CaseSolReminder = new CaseSolReminder;
            $CaseSolReminder->case_id=$case_id; 
            $CaseSolReminder->reminder_type=$request['reminder_type'][$i];
            $CaseSolReminder->reminer_number=$request['reminder_days'][$i];
            $CaseSolReminder->created_by=Auth::user()->id; 
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
            'case_name' => 'required|unique:case_master,case_title',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            return response()->json(['errors'=>'']);
        }
    }
}
  
