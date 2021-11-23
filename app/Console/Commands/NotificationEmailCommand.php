<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use File, DB;
use App\AllHistory,App\User;
use App\Mail\NotificationActivityMail;
use Carbon\Carbon;

class NotificationEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send activity notification on mid-night.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Send activity notification on mid-night.
        // 0 5 * * * cd /var/www/html && php artisan notification:email >> /var/www/html/storage/logs/cron-job.log 2>&1
        // * * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1

        Log::info("Activity notification reminder Command Fired : ". date('Y-m-d H:i:s'));
        $commentData = AllHistory::leftJoin('users','users.id','=','all_history.created_by')
            ->leftJoin('users as u1','u1.id','=','all_history.client_id')
            ->leftJoin('task_activity','task_activity.id','=','all_history.activity_for')
            ->leftJoin('case_master','case_master.id','=','all_history.case_id')
            ->leftJoin('case_events','case_events.id','=','all_history.event_id')
            ->leftJoin('expense_entry','expense_entry.id','=','all_history.activity_for')
            ->leftJoin('task_time_entry','task_time_entry.id','=','all_history.time_entry_id')
            ->leftJoin('task','task.id','=','all_history.task_id')
            ->select("all_history.id as historyID","all_history.case_id as caseId","all_history.created_by as createdBy",
                    "task_time_entry.deleted_at as timeEntry","expense_entry.id as ExpenseEntry","case_events.id as eventID", 
                    "users.*","all_history.*","u1.user_level as ulevel","u1.user_title as utitle",
                    DB::raw('CONCAT_WS(" ",u1.first_name,u1.last_name) as fullname'),
                    "case_master.case_title","case_master.id","task_activity.title",
                    "all_history.created_at as all_history_created_at",
                    "case_master.case_unique_number", "case_events.event_title as eventTitle", 
                    "case_events.deleted_at as deleteEvents", "task.deleted_at as deleteTasks",
                    'task.task_title as taskTitle', "case_master.deleted_at as deleteCase","u1.deleted_at as deleteContact")
            ->whereDate("all_history.created_at", date('Y-m-d', strtotime(date('Y-m-d').' - 1 day')))
            // ->whereDate("all_history.created_at", date('Y-m-d'))
            ->where('all_history.is_for_client','no')
            ->with('caseFirm')
            ->get();
        echo "History data for ". date('Y-m-d').' : '. count($commentData);echo PHP_EOL;
        Log::info("History data for ". date('Y-m-d').' : '. count($commentData));
        $firmData = [];
        $staffData = [];
        foreach($commentData as $key=>$val){            
            // echo $val->historyID;echo PHP_EOL;
            // echo $val->createdBy;echo PHP_EOL;
            // echo $val->caseFirm->parent_user_id;echo PHP_EOL;
            // echo "--------------------";echo PHP_EOL;
            $firmDetail = User::find($val->caseFirm->parent_user_id);
            // echo $val->user_level;echo PHP_EOL;
            if($val->user_level == 3 && ($val->caseFirm->parent_user_id != $val->createdBy)){
                // echo $val->historyID;echo PHP_EOL;
                // echo "*******************";echo PHP_EOL;
                $date = Carbon::now($firmDetail->user_timezone ?? 'UTC');
                $utcDate = Carbon::now('UTC');
                if(date("Y-m-d",strtotime($utcDate)) === date("Y-m-d",strtotime($date))){
                    Log::info("Firm Notification Email sent to : ". $firmDetail->email. ' for time zone : '.$firmDetail->user_timezone." at ".$date);
                    if ($date->hour === 05) {
                        $preparedFor = substr($firmDetail->first_name,0,100).' '.substr($firmDetail->last_name,0,100).'|'.$firmDetail->email;
                        $firmData[$preparedFor][$key] = $val;
                        $firmData[$preparedFor][$key]['logo_url'] = $val->caseFirm->firm_logo_url; 
                    } 
                }               
            }
            if($val->caseFirm->parent_user_id == $val->createdBy){
                $firmUserDetails = User::select("first_name","last_name","email","id","user_level","user_title","default_rate","default_color")
                        ->where("firm_name",$val->caseFirm->id)
                        ->where("user_level","3")
                        ->where("user_status","1")
                        ->orWhere("id",$val->caseFirm->parent_user_id)
                        ->orderBy('first_name','asc')->get();
                foreach($firmUserDetails as $k => $staff) {
                    $date = Carbon::now($staff->user_timezone ?? 'UTC');
                    $utcDate = Carbon::now('UTC');
                    if(date("Y-m-d",strtotime($utcDate)) === date("Y-m-d",strtotime($date))){
                        Log::info("Staff notification Email sent to : ". $staff->email. ' for time zone : '.$staff->user_timezone." at ".$date);
                        if ($date->hour === 05) {
                            $preparedFor = substr($staff->first_name,0,100).' '.substr($staff->last_name,0,100).'|'.$staff->email.'|'.$staff->id;
                            $staffData[$preparedFor][$key] = $val;
                            $staffData[$preparedFor][$key]['logo_url'] = $val->caseFirm->firm_logo_url;
                        }
                    }
                }
            }
        }
        // $userNotificationSetting =  DB::select('SELECT uns.notification_id, uns.for_email, ns.topic, ns.type, ns.action, ns.sub_type
        // FROM user_notification_settings uns
        // left join notification_settings ns on ns.id = uns.notification_id 
        // left join user_notification_interval uni on uni.user_id = uns.user_id 
        // where uns.user_id =11133 and uns.for_email = "yes" and uni.notification_email_interval = "1440" ');
        
        // for firm User
        $firmData = array_map('array_values', $firmData);
        $caseData = [];
        $itemData = [];        
        foreach($firmData as $key => $item) {
            if(count($item) > 0) {
                $firmId = $item[0]->caseFirm->parent_user_id;
                echo $firmId ."-->"; echo PHP_EOL;  
                $userNotificationSetting =  DB::select('SELECT uns.notification_id, uns.for_email, ns.topic, ns.type, ns.action, ns.sub_type
                FROM user_notification_settings uns
                left join notification_settings ns on ns.id = uns.notification_id 
                left join user_notification_interval uni on uni.user_id = uns.user_id 
                where uns.user_id = "'.$firmId.'" and uns.for_email = "yes" and uni.notification_email_interval = "1440" ');   
            
                echo "Firm userNotificationSetting data > ". count($userNotificationSetting); echo PHP_EOL; 
                Log::info("Firm userNotificationSetting data > ". count($userNotificationSetting)); 
                
                foreach($item as $k => $v){                    
                    $viewInMail = 0;
                    // echo $v->type ."-->". $v->action; echo PHP_EOL;
                    foreach($userNotificationSetting as $n => $setting){                        
                        if($setting->sub_type == $v->type && $setting->action == $v->action){
                            // echo $v->type ."-->". $v->action."--> 121 -->".$setting->sub_type.'--->'.$setting->action; echo PHP_EOL;
                            $viewInMail = 1;
                        }
                        if($setting->sub_type == 'notes' && $v->type == 'notes'){
                            $viewInMail = 1;
                        }
                        if($setting->sub_type == 'time_entry' && $v->type == 'expenses'){
                            $viewInMail = 1;
                        }
                    }                    
                    if($viewInMail == 1){
                        echo $v->type ."-->". $v->action; echo PHP_EOL;
                        if($v->case_id !=NULL){
                            $caseData[$v->case_title][] = $v;
                        }else{
                            $itemData[$k] = $v;
                        }                        
                    }
                    $firmDetail = @$v->logo_url;
                }
                
                echo $key;echo PHP_EOL;
                $explodeKey = explode('|', $key);            
                $preparedFor = $explodeKey[0];
                $preparedEmail = $explodeKey[1];
                // dd($item);
                if(count($itemData) > 0 || count($caseData) > 0){
                    echo"Firm user email send to > ". $preparedEmail. " & firm id : ".$firmId;echo PHP_EOL;
                    Log::info("Firm user email send to > ". $preparedEmail. " & firm id : ".$firmId);
                    \Mail::to($preparedEmail)->send(new NotificationActivityMail($itemData, $firmDetail, $preparedFor, $preparedEmail, $caseData, "yes"));
                    // \Mail::to('jignesh.prajapati@plutustec.com')->send(new NotificationActivityMail($itemData, $firmDetail, $preparedFor, $preparedEmail, $caseData, "yes"));
                }
            }
        }

        // for staff
        $caseStaffData = [];
        $itemStaffData = [];
        foreach($staffData as $key => $item) {
            if(count($item) > 0) {                
                echo $key;echo PHP_EOL;
                $explodeKey = explode('|', $key);            
                $preparedFor = $explodeKey[0];
                $preparedEmail = $explodeKey[1];
                $staffID = $explodeKey[2];

                $userNotificationSetting =  DB::select('SELECT uns.notification_id, uns.for_email, ns.topic, ns.type, ns.action, ns.sub_type
                FROM user_notification_settings uns
                left join notification_settings ns on ns.id = uns.notification_id 
                left join user_notification_interval uni on uni.user_id = uns.user_id 
                where uns.user_id = "'.$staffID.'" and uns.for_email = "yes" and uni.notification_email_interval = "1440" ');   
                
                echo "Staff userNotificationSetting data > ". count($userNotificationSetting); echo PHP_EOL;  
                // Log::info("Staff userNotificationSetting data > ". count($userNotificationSetting)); 
                foreach($item as $k => $v){ 
                    // echo $v->staff_id ."-->"; echo PHP_EOL;                   
                    $viewInMail = 0;
                    // echo $v->type ."-->". $v->action; echo PHP_EOL;
                    foreach($userNotificationSetting as $n => $setting){
                        if($setting->sub_type == $v->type && $setting->action == $v->action){
                            // echo $v->type ."-->". $v->action."--> 121 -->".$setting->sub_type.'--->'.$setting->action; echo PHP_EOL;
                            $viewInMail = 1;
                        }
                        if($setting->sub_type == 'notes' && $v->type == 'notes'){
                            $viewInMail = 1;
                        }
                        if($setting->sub_type == 'time_entry' && $v->type == 'expenses'){
                            $viewInMail = 1;
                        }
                    }                    
                    if($viewInMail == 1){
                        echo $v->type ."-->". $v->action; echo PHP_EOL;
                        if($v->case_id !=NULL){
                            $caseStaffData[$v->case_title][] = $v;                            
                        }
                        $itemStaffData[$k] = $v;
                    }
                    $firmDetail = @$v->logo_url;
                }
                
                // dd($item);
                if(count($itemStaffData) > 0 || count($caseStaffData) > 0){
                    echo "Staff email send to > ". $preparedEmail. " & staff id : ".$staffID;echo PHP_EOL;
                    Log::info("Staff email send to > ". $preparedEmail. " & staff id : ".$staffID);
                    \Mail::to($preparedEmail)->send(new NotificationActivityMail($itemStaffData, $firmDetail, $preparedFor, $preparedEmail, $caseStaffData, "yes"));
                    // \Mail::to('jignesh.prajapati@plutustec.com')->send(new NotificationActivityMail($itemStaffData, $firmDetail, $preparedFor, $preparedEmail, $caseStaffData, "yes"));
                }
            }
        }
        Log::info("Activity notification reminder Command End : ". date('Y-m-d H:i:s'));
    }
}
//sudo php artisan notification:email