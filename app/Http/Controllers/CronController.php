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
use App\Task,App\CaseTaskReminder,App\CaseTaskLinkedStaff,App\TaskChecklist;
use App\TaskReminder,App\TaskActivity,App\TaskTimeEntry,App\TaskComment;
use App\TaskHistory,App\LeadAdditionalInfo,App\UsersAdditionalInfo,App\ClientGroup;
use App\Invoices,App\Firm;
class CronController extends BaseController
{
    public function __construct()
    {
       
    }
    //Clean the database with indexing
    public function index()
    {
        $UsersAdditionalInfo=DB::table('users_additional_info')->get();
        foreach($UsersAdditionalInfo as $k=>$v){
            $client_group=ClientGroup::find($v->contact_group_id);
            if(empty($client_group)){
                 UsersAdditionalInfo::where('id',$v->id)->update(['contact_group_id'=>"1"]);
            }
        }


        $CaseMaster=CaseMaster::select("*","created_by as created_bya")->get();
        foreach($CaseMaster as $val){
            $User=User::find($val->created_bya);
            $CaseMasterUpdate=CaseMaster::find($val->id);
            $CaseMasterUpdate->firm_id=$User['firm_name'];
            $CaseMasterUpdate->save();
        }

        $ClientGroup=ClientGroup::select("*","created_by as created_bya")->get();
        foreach($ClientGroup as $val){
            $User=User::find($val->created_bya);
            $CaseMasterUpdate=ClientGroup::find($val->id);
            $CaseMasterUpdate->firm_id=$User['firm_name'];
            $CaseMasterUpdate->save();
        }

        // DB::table('calls')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_activity')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_client_selection')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_events')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_event_comment')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_event_linked_staff')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_event_location')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_event_reminder')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_intake_form')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_intake_form_fields_data')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_master')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_notes')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_practice_area')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_sol_reminder')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_staff')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_stage')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_stage_history')->where("deleted_at","!=",NULL)->delete();
        // DB::table('case_update')->where("deleted_at","!=",NULL)->delete();
        // DB::table('client_activity')->where("deleted_at","!=",NULL)->delete();
        // DB::table('client_group')->where("deleted_at","!=",NULL)->delete();
        // DB::table('client_notes')->where("deleted_at","!=",NULL)->delete();
        // DB::table('contract_access_permission')->where("deleted_at","!=",NULL)->delete();
        // DB::table('contract_user_case')->where("deleted_at","!=",NULL)->delete();
        // DB::table('contract_user_permission')->where("deleted_at","!=",NULL)->delete();
        // // DB::table('countries')->where("deleted_at","!=",NULL)->delete();
        // DB::table('deactivated_user')->where("deleted_at","!=",NULL)->delete();
        // DB::table('email_template')->where("deleted_at","!=",NULL)->delete();
        // DB::table('event_type')->where("deleted_at","!=",NULL)->delete();
        // DB::table('expense_entry')->where("deleted_at","!=",NULL)->delete();
        // DB::table('expense_for_invoice')->where("deleted_at","!=",NULL)->delete();
        // DB::table('firm')->where("deleted_at","!=",NULL)->delete();
        // DB::table('firm_address')->where("deleted_at","!=",NULL)->delete();
        // DB::table('firm_event_reminder')->where("deleted_at","!=",NULL)->delete();
        // DB::table('firm_sol_reminder')->where("deleted_at","!=",NULL)->delete();
        // DB::table('intake_form')->where("deleted_at","!=",NULL)->delete();
        // DB::table('intake_form_fields')->where("deleted_at","!=",NULL)->delete();
        // DB::table('invoices')->where("deleted_at","!=",NULL)->delete();
        // DB::table('invoice_adjustment')->where("deleted_at","!=",NULL)->delete();
        // DB::table('invoice_installment')->where("deleted_at","!=",NULL)->delete();
        // DB::table('invoice_payment')->where("deleted_at","!=",NULL)->delete();
        // DB::table('invoice_payment_plan')->where("deleted_at","!=",NULL)->delete();
        // DB::table('lead_additional_info')->where("deleted_at","!=",NULL)->delete();
        // DB::table('lead_case_activity_history')->where("deleted_at","!=",NULL)->delete();
        // DB::table('lead_notes')->where("deleted_at","!=",NULL)->delete();
        // DB::table('lead_notes_activity')->where("deleted_at","!=",NULL)->delete();
        // DB::table('lead_notes_activity_history')->where("deleted_at","!=",NULL)->delete();
        // DB::table('lead_status')->where("deleted_at","!=",NULL)->delete();
        // DB::table('messages')->where("deleted_at","!=",NULL)->delete();
        // // DB::table('migrations')->where("deleted_at","!=",NULL)->delete();
        // DB::table('not_hire_reasons')->where("deleted_at","!=",NULL)->delete();
        // // DB::table('password_resets')->where("deleted_at","!=",NULL)->delete();
        // DB::table('plan_history')->where("deleted_at","!=",NULL)->delete();
        // DB::table('potential_case_invoice')->where("deleted_at","!=",NULL)->delete();
        // DB::table('potential_case_invoice_payment')->where("deleted_at","!=",NULL)->delete();
        // DB::table('potential_case_payment')->where("deleted_at","!=",NULL)->delete();
        // DB::table('referal_resource')->where("deleted_at","!=",NULL)->delete();
        // DB::table('requested_fund')->where("deleted_at","!=",NULL)->delete();
        // DB::table('shared_invoice')->where("deleted_at","!=",NULL)->delete();
        // DB::table('task')->where("deleted_at","!=",NULL)->delete();
        // DB::table('task_activity')->where("deleted_at","!=",NULL)->delete();
        // DB::table('task_checklist')->where("deleted_at","!=",NULL)->delete();
        // DB::table('task_comment')->where("deleted_at","!=",NULL)->delete();
        // DB::table('task_history')->where("deleted_at","!=",NULL)->delete();
        // DB::table('task_linked_staff')->where("deleted_at","!=",NULL)->delete();
        // DB::table('task_reminder')->where("deleted_at","!=",NULL)->delete();
        // DB::table('task_time_entry')->where("deleted_at","!=",NULL)->delete();
        // DB::table('temp_user_selection')->where("deleted_at","!=",NULL)->delete();
        // DB::table('time_entry_for_invoice')->where("deleted_at","!=",NULL)->delete();
        // DB::table('trust_history')->where("deleted_at","!=",NULL)->delete();
        // DB::table('users')->where("deleted_at","!=",NULL)->delete();
        // DB::table('usersold')->where("deleted_at","!=",NULL)->delete();
        // DB::table('users_additional_info')->where("deleted_at","!=",NULL)->delete();
        // DB::table('users_detail')->where("deleted_at","!=",NULL)->delete();

        $sqlQuery="OPTIMIZE TABLE `calls`, `case_activity`, `case_client_selection`, `case_events`, `case_event_comment`, `case_event_linked_staff`, `case_event_location`, `case_event_reminder`, `case_intake_form`, `case_intake_form_fields_data`, `case_master`, `case_notes`, `case_practice_area`, `case_sol_reminder`, `case_staff`, `case_stage`, `case_stage_history`, `case_update`, `client_activity`, `client_group`, `client_notes`, `contract_access_permission`, `contract_user_case`, `contract_user_permission`, `countries`, `deactivated_user`, `email_template`, `event_type`, `expense_entry`, `expense_for_invoice`, `firm`, `firm_address`, `firm_event_reminder`, `firm_sol_reminder`, `intake_form`, `intake_form_fields`, `invoices`, `invoice_adjustment`, `invoice_installment`, `invoice_payment`, `invoice_payment_plan`, `lead_additional_info`, `lead_case_activity_history`, `lead_notes`, `lead_notes_activity`, `lead_notes_activity_history`, `lead_status`, `messages`, `migrations`, `not_hire_reasons`, `password_resets`, `plan_history`, `potential_case_invoice`, `potential_case_invoice_payment`, `potential_case_payment`, `referal_resource`, `requested_fund`, `shared_invoice`, `task`, `task_activity`, `task_checklist`, `task_comment`, `task_history`, `task_linked_staff`, `task_reminder`, `task_time_entry`, `temp_user_selection`, `time_entry_for_invoice`, `trust_history`, `users`, `usersold`, `users_additional_info`, `users_detail`";

        $result = DB::select(DB::raw($sqlQuery));

       

        return response()->json(['errors'=>'']);
        
        exit;
   
    }
    public function removeDuplicateUser(){

        $DuplicateUSer=DB::table('users_additional_info')
        ->select('users_additional_info.user_id',DB::raw('COUNT(user_id) as count'))
        ->groupBy('user_id')
        ->orderBy('count')
        ->having('count',">",1)
        ->get();

        DB::statement("DELETE t1 FROM users_additional_info t1 INNER JOIN users_additional_info t2 WHERE t1.id < t2.id AND t1.user_id=t2.user_id");
        
        DB::statement("DELETE t1 FROM case_client_selection t1 INNER JOIN case_client_selection t2 WHERE t1.id < t2.id AND t1.`case_id`=t2.`case_id` AND t1.`selected_user`=t2.`selected_user`");

        DB::statement("DELETE t1 FROM case_staff t1 INNER JOIN case_staff t2 WHERE t1.id < t2.id AND t1.`case_id`=t2.`case_id` AND t1.`user_id`=t2.`user_id`");

    }

    public function addUser(){

        $USer=User::where("user_level","2")->get();
        foreach($USer as $k=>$user){
            $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$user->id)->count();
            if(empty($UsersAdditionalInfo)){
                $UsersAdditionalInfo= new UsersAdditionalInfo;
                $UsersAdditionalInfo->user_id=$user->id; 
                $UsersAdditionalInfo->created_by=Auth::User()->id;
                $UsersAdditionalInfo->created_at=date('Y-m-d H:i:s');                
                $UsersAdditionalInfo->save();
                echo "save";
            }
        }
    }
    public function deletepdf()
    {
        $a = glob("public/download/pdf/*"); 
        foreach($a as $file){
            unlink($file);
        }

        $b = glob("public/download_intakeform/pdf/*"); 
        foreach($b as $file){
            unlink($file);
        }

        // $c = glob("public/download/*"); 
        // foreach($c as $file){
        //     if(!is_dir($file)){
        //         unlink($file);
        //     }
        // }
        return response()->json(['errors'=>'']);
        exit;
    }

    function setBillingMethod(){
        $CaseMaster1=CaseMaster::select("*")->get();
        foreach($CaseMaster1 as $k=>$v){
            $CaseClientSelection=CaseClientSelection::select("*")->where("case_id",$v->id)->where("is_billing_contact","yes")->first();
            if(!empty($CaseClientSelection)){
                $CaseMaster=CaseMaster::find($v->id);
                $CaseMaster->billing_method=$CaseClientSelection['billing_method']; 
                $CaseMaster->billing_amount=$CaseClientSelection['billing_amount']; 
                $CaseMaster->save();
            }

        }

    }

    function setContactGroup(){
        $clientList = User::leftjoin('users_additional_info','users_additional_info.user_id','=','users.id')
        ->select("users.*","users_additional_info.*","users.id as uid")->whereIn("users.user_level",["2","4"])->where("contact_group_id",NULL)->get();
        print_r($clientList);
        foreach($clientList as $k=>$v){
            if($v->uid!="" &&$v->user_id==""){
                User::where("id",$v->uid)->delete();
            }else{
                UsersAdditionalInfo::where('user_id',$v->user_id)
                ->update(['contact_group_id'=>1]);
            }
         
        }

    }

    function sentInvoiceReminder(){
        $date=date('Y-m-d');
        $seven_day_before = date( 'Y-m-d', strtotime( $date . ' -7 day' ) );
        $seven_day_after = date( 'Y-m-d', strtotime( $date . ' +7 day' ) );
        $BeforeInvoices=Invoices::select("*")->whereNotIn("status",["Paid","Partial"])->whereDate("due_date","=",$seven_day_before)->get();
        $AfterInvoices=Invoices::select("*")->whereNotIn("status",["Paid","Partial"])->whereDate("due_date","=",$seven_day_after)->get();
        
    }

    function createEventType(){
        $firmData=Firm::get();
        foreach($firmData as $k=>$v){
            $event_type=EventType::select('id')->where('firm_id',$v->id)->count();
            if( $event_type<=0){
            $eventTypeArray = array(
                array('title' => 'Court','color_code'=>'#6edcff','status' => '1','status_order' => '1','firm_id' => $v->id,'created_by' => Auth::User()->id,'created_at' => date('Y-m-d h:i:s')),
                array('title' => 'Client Meeting','color_code'=>'#6dd507','status' => '1','status_order' => '2','firm_id' => $v->id,'created_by' => Auth::User()->id,'created_at' => date('Y-m-d h:i:s')),
                array('title' => 'Consult','color_code'=>'#ceaff2','status' => '1','status_order' => '3','firm_id' => $v->id,'created_by' => Auth::User()->id,'created_at' => date('Y-m-d h:i:s')),
                array('title' => 'Travel','color_code'=>'#ff8c00','status' => '1','status_order' => '4','firm_id' => $v->id,'created_by' => Auth::User()->id,'created_at' => date('Y-m-d h:i:s')),
            );
                EventType::insert($eventTypeArray);
            } 
        }

    }
}
  
