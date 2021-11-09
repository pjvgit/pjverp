<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use File, DB;
use App\AllHistory,App\User;
use App\Mail\NotificationActivityMail;

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
            ->select("all_history.id as historyID","all_history.case_id as caseId","all_history.created_by as createdBy","task_time_entry.deleted_at as timeEntry","expense_entry.id as ExpenseEntry","case_events.id as eventID", "all_history.*","users.*","u1.user_level as ulevel",DB::raw('CONCAT_WS(" ",u1.first_name,u1.last_name) as fullname'),"case_master.case_title","case_master.id","task_activity.title","all_history.created_at as all_history_created_at","case_master.case_unique_number")
            ->whereDate("all_history.created_at", date("Y-m-d"))
            ->with('caseFirm')
            ->get();
        // dd($commentData);
        Log::info("History data for ". date('Y-m-d').' : '. count($commentData));
        
        $arrData = [];
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
                $preparedFor = substr($firmDetail->first_name,0,100).' '.substr($firmDetail->last_name,0,100).'|'.$firmDetail->email;
                $arrData[$preparedFor][$key] = $val;
                $arrData[$preparedFor][$key]['logo_url'] = $val->caseFirm->firm_logo_url;
            }
            
        }
        // dd($arrData);
        // $notificationSetting = NotificationSetting::all();
        // $userNotificationSetting = DB::table('user_notification_settings')->where('user_id',auth()->id())->get();
        // $UsersAdditionalInfo = DB::table('user_notification_interval')->where('user_id',auth()->id())->first();

        $arrData = array_map('array_values', $arrData);
        $caseData = [];
        foreach($arrData as $key => $item) {
            foreach($item as $k => $v){
                if($v->case_id !=NULL){
                    $caseData[$v->case_title][] = $v;
                }
                $firmDetail = @$v->logo_url;
            }
            echo $key;echo PHP_EOL;
            $explodeKey = explode('|', $key);            
            echo $preparedFor = $explodeKey[0];echo PHP_EOL;
            echo $preparedEmail = $explodeKey[1];echo PHP_EOL;
            // dd($item);
            Log::info("Email send to >". $preparedEmail);
            \Mail::to($preparedEmail)->send(new NotificationActivityMail($item, $firmDetail, $preparedFor, $preparedEmail, $caseData));
            // \Mail::to('jignesh.prajapati@plutustec.com')->send(new NotificationActivityMail($item, $firmDetail, $preparedFor, $preparedEmail, $caseData));
        }
        
        
        Log::info("Activity notification reminder Command End : ". date('Y-m-d H:i:s'));
    }
}
//sudo php artisan notification:email