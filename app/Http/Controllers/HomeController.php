<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Firm;
use App\ReferalResource,App\LeadStatus,App\NotHireReasons;
use App\TaskActivity,App\UserRole,App\CaseIntakeForm;
use App\User;
use App\ContractUserCase,App\CaseMaster,App\ContractUserPermission,App\ContractAccessPermission;
use App\DeactivatedUser,App\TempUserSelection,App\CasePracticeArea,App\CaseStage,App\CaseClientSelection;
use App\CaseStaff,App\CaseUpdate,App\CaseStageUpdate,App\CaseActivity;
use App\CaseEvent,App\CaseEventLocation,App\EventType;
use Carbon\Carbon,App\CaseEventReminder,App\CaseEventLinkedStaff;
use App\Http\Controllers\CommonController,App\CaseSolReminder;
use DateInterval,DatePeriod,App\CaseEventComment;
use App\Task,App\LeadAdditionalInfo,App\UsersAdditionalInfo,App\AllHistory;
use App\Invoices,App\EmailTemplate;
use App\Http\Requests\MultiuserRequest;
use Illuminate\Support\Str;
class HomeController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        // DB::delete('DELETE t1 FROM case_client_selection t1 INNER JOIN case_client_selection t2 WHERE t1.id < t2.id AND t1.selected_user = t2.selected_user AND t1.case_id = t2.case_id');
        // DB::delete('DELETE t1 FROM case_event_linked_staff t1 INNER JOIN case_event_linked_staff t2 WHERE t1.id < t2.id AND t1.event_id = t2.event_id AND t1.user_id = t2.user_id');

        $ReferalResource=ReferalResource::select('id')->where('firm_id',Auth::User()->firm_name)->count();
        if( $ReferalResource<=0){
            $referal_resource = array(
                array('title' => 'Advertisement','status' => '1','stage_order' => '1','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
                array('title' => 'Avvo','status' => '1','stage_order' => '2','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
                array('title' => 'Client Referral','status' => '1','stage_order' => Auth::User()->firm_name,'firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
                array('title' => 'Facebook','status' => '1','stage_order' => '4','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
                array('title' => 'LinkedIn','status' => '1','stage_order' => '5','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
                array('title' => 'Networking Event','status' => '1','stage_order' => '6','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
                array('title' => 'Professional Referral','status' => '1','stage_order' => '7','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
                array('title' => 'Search','status' => '1','stage_order' => '8','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
                array('title' => 'Twitter','status' => '1','stage_order' => '9','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
                array('title' => 'Website','status' => '1','stage_order' => '10','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
                array('title' => 'Yelp','status' => '1','stage_order' => '11','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
                array('title' => 'Other','status' => '1','stage_order' => '12','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id)
                );
            ReferalResource::insert($referal_resource);
        }
        $LeadStatus=LeadStatus::select('id')->where('firm_id',Auth::User()->firm_name)->count();
        if( $LeadStatus<=0){
        $leadStatus = array(
            array('title' => 'NEW','status' => '1','status_order' => '1','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
            array('title' => 'Pending','status' => '1','status_order' => '2','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
            array('title' => 'Consult Scheduled','status' => '1','status_order' => '3','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
            array('title' => 'Contacted','status' => '1','status_order' => '4','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
           );
            LeadStatus::insert($leadStatus);
        }
        $NotHireReasons=NotHireReasons::select('id')->where('firm_id',Auth::User()->firm_name)->count();
        if( $NotHireReasons<=0){
        $NotHireReasons = array(
            array('title' => 'Conflict','status' => '1','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
            array('title' => 'Duplicate lead','status' => '1','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
            array('title' => 'No case','status' => '1','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
            array('title' => 'Not a fit for the firm','status' => '1','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
            array('title' => 'Timing','status' => '1','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
            array('title' => 'Unresponsive','status' => '1','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
            array('title' => 'Went with another firm','status' => '1','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),
            array('title' => 'Other','status' => '1','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id),

           );
           NotHireReasons::insert($NotHireReasons);
        }
        
        $TaskActivity=TaskActivity::select('id')->where('firm_id',Auth::User()->firm_name)->count();
        if( $TaskActivity<=0){
        $TaskActivity = array(
            array('title' => 'Document Preparation','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
            array('title' => 'Postage','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
           );
           TaskActivity::insert($TaskActivity);
        }


        $UserRole=UserRole::select('id')->where('firm_id',Auth::User()->firm_name)->count();
        if( $UserRole<=0){
            $UserRole = array(
                array('role_name'=>'Accountant','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Actuarialist','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Adverse Party','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Appraiser','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Arbitrator','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Assessor','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Auditor','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Banker','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Clerk of Court','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Court Administrator','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Defendant','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Financial Planner','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'In-House Professionals','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Insurance Adjuster','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Insurance Agent','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Judge','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Law Clerk','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Mediator','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Personal Representative','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Power of Attorney','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Recorder','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('role_name'=>'Spouse','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                );
                UserRole::insert($UserRole);
        }
        // $caseEventData=CaseEvent::select("*")->where("recuring_event","yes")->where('created_by',Auth::User()->id)->get();
        // print_r($caseEventData);
        

        $event_type=EventType::select('id')->where('firm_id',Auth::User()->firm_name)->count();
        if( $event_type<=0){
        $eventTypeArray = array(
            array('title' => 'Court','color_code'=>'#6edcff','status' => '1','status_order' => '1','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id,'created_at' => date('Y-m-d h:i:s')),
            array('title' => 'Client Meeting','color_code'=>'#6dd507','status' => '1','status_order' => '2','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id,'created_at' => date('Y-m-d h:i:s')),
            array('title' => 'Consult','color_code'=>'#ceaff2','status' => '1','status_order' => '3','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id,'created_at' => date('Y-m-d h:i:s')),
            array('title' => 'Travel','color_code'=>'#ff8c00','status' => '1','status_order' => '4','firm_id' => Auth::User()->firm_name,'created_by' => Auth::User()->id,'created_at' => date('Y-m-d h:i:s')),
           );
            EventType::insert($eventTypeArray);
        }

        $CasePracticeArea=CasePracticeArea::select('id')->where('firm_id',Auth::User()->firm_name)->count();
        if( $CasePracticeArea<=0){
            $CasePracticeArea = array(
                array('title'=>'Bankruptcy','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Business','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Civil Party','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Criminal Defense','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Divorce/Separation','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'DUI/DWI','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Employment','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Estate Planning','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Family','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Foreclosure','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Immigration','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Landlord/Tenant','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Personal Injury','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Real Estate','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                array('title'=>'Tax','status' => '1','firm_id' => Auth::User()->firm_name,'created_at' => date('Y-m-d h:i:s'),'created_by' => Auth::User()->id),
                );
                CasePracticeArea::insert($CasePracticeArea);
        }


        //Set unique token
        $CaseIntakeForm=CaseIntakeForm::get();
        foreach($CaseIntakeForm as $k=>$v){
            if($v->unique_token==NULL){
                $CaseIntakeForm=CaseIntakeForm::find($v->id);
                $CaseIntakeForm->unique_token=$this->generateUniqueToken();
                $CaseIntakeForm->save();
            }
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
   
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();          
        
        $CaseLeadAttorney = CaseStaff::join('users','users.id','=','case_staff.lead_attorney')->select("users.id","users.first_name","users.last_name",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'))->groupBy('case_staff.lead_attorney')->get();


        //Get 15 upcoming events for dashboard
        // $upcomingTenEvents=CaseEvent::leftJoin('case_master','case_master.id','=','case_events.case_id')->leftJoin('users','users.id','=','case_events.lead_id')->select("case_master.id","case_master.case_title","case_master.case_unique_number","users.first_name","users.middle_name","users.last_name","case_events.*")->where('start_date','>=',date('Y-m-d'))->where('case_events.created_by',Auth::User()->id)->orderBy("start_date", "ASC")->limit(15)->get();
        $upcomingTenEvents=CaseEvent::where('start_date','>=',date('Y-m-d'))->where('case_events.created_by',Auth::User()->id)->orderBy("start_date", "ASC")->with("case", "leadUser", 'eventLinkedStaff')->limit(15)->get();

        //Get 15 upcoming task for dashboard
        $upcomingTask=Task::leftJoin('case_master','case_master.id','=','task.case_id')->leftJoin('users','users.id','=','task.lead_id')->select("case_master.id","case_master.case_title","case_master.case_unique_number","users.first_name","users.middle_name","users.last_name","task.*")->where("task_due_on","!=","9999-12-30")->where('task.created_by',Auth::User()->id)->orderBy("task_due_on","ASC")->where('status','0')->limit(15)->get();
        
        //For Alter widget (Overdue invoice)
        $InvoicesOverdue=Invoices::leftJoin('case_master','case_master.id','=','invoices.case_id')->select("invoices.*","case_master.case_title","case_master.case_unique_number")->where("invoices.created_by",Auth::User()->id)->where('due_date',"!=",NULL)->where("invoices.status",'Overdue');
        $totalEvetdueInvoiceCount=$InvoicesOverdue->count();
        $InvoicesOverdue=$InvoicesOverdue->orderBy('due_date',"ASC")->limit(10)->get();


        //Low trust balance notification
        $clientList = UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
        ->select("*")->whereIn("users.user_level",["2","4"])->where("firm_name",Auth::user()->firm_name)->whereRaw('users_additional_info.minimum_trust_balance > users_additional_info.trust_account_balance')->get();

        return view('dashboard.homepage',compact('practiceAreaList','caseStageList','CaseLeadAttorney','CaseMasterClient','CaseMasterCompany','practiceAreaList','caseStageList','selectdUSerList','loadFirmUser','upcomingTenEvents','upcomingTask','InvoicesOverdue','totalEvetdueInvoiceCount','clientList'));
    }
    public function dismissWidget(Request $request)
    {
        $user =User::find(Auth::User()->id);
        $user->welcome_page_widget_is_display ="no";
        $user->save();
        return response()->json(['errors'=>'','user_id'=>$user->id]);
        exit;
    }
    public function addBulkUserPopup(Request $request)
    {
        return view('dashboard.addBulkUserPopup');
        exit;  
            
    }
    public function saveBulkUserPopup(Request $request)
    {
        
        $rules = $messages= [];

        $verifyData=[];
        for($i=1;$i<=count($request->first_name);$i++){
            if($request->first_name[$i]!="" || $request->last_name[$i]!="" || $request->email[$i]!=""){
                $verifyData[$i]=$i;
            }
        }
        foreach($verifyData as $key => $val)
        {
            if($val!=""){
                $rules['first_name.'.$key] = 'required|min:1';
                $rules['last_name.'.$key] = 'required|min:1';
                $rules['email.'.$key] = 'required|email|unique:users,email,NULL,id,firm_name,'.Auth::User()->firm_name;
                $messages['first_name.'.$key.'.required'] = 'The Row '.$key.' first name  can\'t be blank.';
                $messages['last_name.'.$key.'.required'] = 'The Row '.$key.' last name  can\'t be blank.';
                $messages['email.'.$key.'.email'] = 'The Row '.$key.' email  is not formatted correctly';
                $messages['email.'.$key.'.required'] = 'The Row '.$key.' email can\'t be blank';
                $messages['email.'.$key.'.unique'] = 'The Row '.$key.' email  is already exist.';
            }
        }
        $validator = \Validator::make($request->all(),$rules,$messages);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $totalUser=[];
            foreach($request->first_name as $key=>$val)
            {
                if($request->first_name[$key]!=""){
                    
                    
                    $user = new User;
                    $user->first_name=$request->first_name[$key];
                    $user->last_name=$request->last_name[$key];
                    $user->email=$request->email[$key];
                    $user->middle_name=(trim($request->middle_name[$key]))??NULL; 
                    $user->user_type=$request->accessLevel[$key];
                    if($request->accessLevel[$key]=="1") { 
                        $user->user_title='Attorney';
                    }else if($request->accessLevel[$key]=="2") 
                    { 
                        $user->user_title='Paralegal'; 
                    }else{
                        $user->user_title='Staff';
                    }
                    $user->firm_name=Auth::User()->firm_name;
                    $user->token  = Str::random(40);
                    $user->parent_user =Auth::User()->id;
                    $user->user_status  = "2";  // Default status is inactive once verified account it will activated.
                    $user->created_by =Auth::User()->id;
                    // print_r($user);
                    $user->save();

                    $totalUser[]=$user->id;

                    if(isset($request->portal_access[$key]) &&  $request->portal_access[$key]=="on"){
                        $getTemplateData = EmailTemplate::find(6);
                        $userData=User::find($user->id);
                        $fullName=$userData['first_name']. ' ' .$userData['last_name'];
                        $email=$userData['email'];
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
                            "to" => $userData['email'],
                            "full_name" => $fullName,
                            "mail_body" => $mail_body
                        ];
                        $sendEmail = $this->sendMail($userEmail);
                    }
                }
            }
            return response()->json(['errors'=>'','totalUser'=>count($totalUser)]);
            exit;
        }
    } 
    public function loadAllHistoryForDashboard(Request $request)
    {
  
        $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
        ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
        ->leftJoin('case_master','case_master.id','=','all_history.case_id')
        ->leftJoin('document_master','document_master.id','=','all_history.document_id')
        ->select("users.*","all_history.*","document_master.*","case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number")
        ->where("all_history.firm_id",Auth::User()->firm_name)
        ->orderBy('all_history.id','DESC')
        ->limit(20)
        ->get();
        return view('dashboard.AllHistory',compact('commentData'));
        exit;  
            
    }
    public function loadEventHistoryForDashboard(Request $request)
    {
  
        $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
        ->leftJoin('case_master','case_master.id','=','all_history.case_id')
        ->select("users.*","all_history.*","case_master.case_title","case_master.id","all_history.created_at as all_history_created_at")
        ->where("all_history.firm_id",Auth::User()->firm_name)
        ->where("all_history.type","event")
        ->orderBy('all_history.id','DESC')
        ->limit(20)
        ->get();
        // print_r($commentData);exit;
        return view('dashboard.EventHistory',compact('commentData'));
        exit;  
            
    }
    public function loadTaskHistoryForDashboard(Request $request)
    {
  
        $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
        ->leftJoin('case_master','case_master.id','=','all_history.case_id')
        ->select("users.*","all_history.*","case_master.case_title","case_master.id","all_history.created_at as all_history_created_at","case_master.case_unique_number")
        ->where("all_history.firm_id",Auth::User()->firm_name)
        ->where("all_history.type","task")
        ->orderBy('all_history.id','DESC')
        ->limit(20)
        ->get();
        return view('dashboard.TaskHistory',compact('commentData'));
        exit;  
            
    }
    public function loadDocumentHistoryForDashboard(Request $request)
    {
  
        $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
        ->leftJoin('users as u1','u1.id','=','all_history.deposit_for')
        ->leftJoin('case_master','case_master.id','=','all_history.case_id')
        ->leftJoin('document_master','document_master.id','=','all_history.document_id')
        ->select("users.*","all_history.*","document_master.*","case_master.case_title","case_master.case_unique_number","case_master.id","u1.user_level as ulevel",DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as fullname'),DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as creator_name'),"all_history.created_at as all_history_created_at")
        ->where("all_history.firm_id",Auth::User()->firm_name)
        ->where("all_history.type","document")
        ->orderBy('all_history.id','DESC')
        ->limit(20)
        ->get();
        // print_r($commentData);exit;
        return view('dashboard.DocumentHistory',compact('commentData'));
        exit;  
            
    }
    public function notification(Request $request)
    {
        return view('notifications.notifications');
        exit;  
    }

    public function loadAllNotification(Request $request)
    {
        if($request->ajax())
        {
            $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
            ->leftJoin('users as u1','u1.id','=','all_history.client_id')
            ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
            ->leftJoin('case_master','case_master.id','=','all_history.case_id')
            ->select("users.*","all_history.*","u1.user_level as ulevel",DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as fullname'),"case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number")
            ->where("all_history.firm_id",Auth::User()->firm_name);
            if(isset($request->user_id)){
                $commentData=$commentData->where("user_id",$request->user_id);
            }
            $commentData=$commentData->orderBy('all_history.id','DESC');
            if(isset($request->per_page)){
                $commentData=$commentData->paginate($request->per_page);
            }else{
                $commentData=$commentData->paginate(10);
            }
        return view('notifications.loadAllNotifications', compact('commentData'))->render();
        }
    }
    public function loadInvoiceNotification(Request $request)
    {
        if($request->ajax())
        {
            $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
            ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
            ->leftJoin('case_master','case_master.id','=','all_history.case_id')
            ->select("users.*","all_history.*","case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number")
            ->where("all_history.firm_id",Auth::User()->firm_name)
            ->where("all_history.type","invoices")
            ->orderBy('all_history.id','DESC');
            if(isset($request->per_page)){
                $commentData=$commentData->paginate($request->per_page);
            }else{
                $commentData=$commentData->paginate(10);
            }
            return view('notifications.loadInvoiceNotifications', compact('commentData'))->render();
        }
    }
    public function loadTimeEntryNotification(Request $request)
    {
        if($request->ajax())
        {
            $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
            ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
            ->leftJoin('case_master','case_master.id','=','all_history.case_id')
            ->select("users.*","all_history.*","case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number")
            ->where("all_history.firm_id",Auth::User()->firm_name)
            ->where("all_history.type","time_entry")
            ->orderBy('all_history.id','DESC');
            if(isset($request->per_page)){
                $commentData=$commentData->paginate($request->per_page);
            }else{
                $commentData=$commentData->paginate(10);
            }
            return view('notifications.loadTimeEntryNotifications', compact('commentData'))->render();
        }
    }
    public function loadExpensesNotification(Request $request)
    {
        if($request->ajax())
        {
            $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
            ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
            ->leftJoin('case_master','case_master.id','=','all_history.case_id')
            ->select("users.*","all_history.*","case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number")
            ->where("all_history.firm_id",Auth::User()->firm_name)
            ->where("all_history.type","expenses")
            ->orderBy('all_history.id','DESC');
            if(isset($request->per_page)){
                $commentData=$commentData->paginate($request->per_page);
            }else{
                $commentData=$commentData->paginate(10);
            }
            return view('notifications.loadExpensesNotifications', compact('commentData'))->render();
        }
    }
    public function loadEventsNotification(Request $request)
    {
        if($request->ajax())
        {
            $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
            ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
            ->leftJoin('case_master','case_master.id','=','all_history.case_id')
            ->select("users.*","all_history.*","case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number")
            ->where("all_history.firm_id",Auth::User()->firm_name)
            ->where("all_history.type","event")
            ->orderBy('all_history.id','DESC');
            if(isset($request->per_page)){
                $commentData=$commentData->paginate($request->per_page);
            }else{
                $commentData=$commentData->paginate(10);
            }
            return view('notifications.loadEventsNotifications', compact('commentData'))->render();
        }
    }
    public function loadTasksNotification(Request $request)
    {
        if($request->ajax())
        {
            $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
            ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
            ->leftJoin('case_master','case_master.id','=','all_history.case_id')
            ->select("users.*","all_history.*","case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number")
            ->where("all_history.firm_id",Auth::User()->firm_name)
            ->where("all_history.type","task")
            ->orderBy('all_history.id','DESC');
            if(isset($request->per_page)){
                $commentData=$commentData->paginate($request->per_page);
            }else{
                $commentData=$commentData->paginate(10);
            }
            return view('notifications.loadTasksNotifications', compact('commentData'))->render();
        }
    }
    public function loadDepositRequestsNotification(Request $request)
    {
        if($request->ajax())
        {
            $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
            ->leftJoin('users as u1','u1.id','=','all_history.deposit_for')
            ->select("users.*","all_history.*","u1.user_level as ulevel",DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as fullname'),"all_history.created_at as all_history_created_at")
            ->where("all_history.firm_id",Auth::User()->firm_name)
            ->where("all_history.type","deposit")
            ->orderBy('all_history.id','DESC');
            if(isset($request->per_page)){
                $commentData=$commentData->paginate($request->per_page);
            }else{
                $commentData=$commentData->paginate(10);
            }
            return view('notifications.loadDepositNotifications', compact('commentData'))->render();
        }
    }

    public function loadDocumentNotification(Request $request)
    {
        if($request->ajax())
        {
            $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
            ->leftJoin('users as u1','u1.id','=','all_history.deposit_for')
            ->leftJoin('case_master','case_master.id','=','all_history.case_id')
            ->leftJoin('document_master','document_master.id','=','all_history.document_id')
            ->select("users.*","all_history.*","document_master.*","case_master.case_title","case_master.case_unique_number","case_master.id","u1.user_level as ulevel",DB::raw('CONCAT_WS(" ",u1.first_name,u1.middle_name,u1.last_name) as fullname'),DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as creator_name'),"all_history.created_at as all_history_created_at")
            ->where("all_history.firm_id",Auth::User()->firm_name)
            ->where("all_history.type","document")
            ->orderBy('all_history.id','DESC');
            if(isset($request->per_page)){
                $commentData=$commentData->paginate($request->per_page);
            }else{
                $commentData=$commentData->paginate(10);
            }
            return view('notifications.loadDocumentNotifications', compact('commentData'))->render();
        }
    }
}
