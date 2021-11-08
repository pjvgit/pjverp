<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use File, DB;
use App\AllHistory;
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
            ->whereDate("all_history.created_at", date("Y-m-d"))
            ->where("users.user_level", "3")
            ->select("all_history.*","users.*")
            ->with('caseFirm')
            ->get();
        
        
        $arrData = [];
        foreach($commentData as $key=>$val){
            $preparedFor = substr($val->first_name,0,100).' '.substr($val->last_name,0,100).'|'.$val->email;
            $arrData[$preparedFor][$key] = $val;
            $arrData[$preparedFor][$key]['logo_url'] = $val->caseFirm->firm_logo_url;
        }
        
        // $notificationSetting = NotificationSetting::all();
        // $userNotificationSetting = DB::table('user_notification_settings')->where('user_id',auth()->id())->get();
        // $UsersAdditionalInfo = DB::table('user_notification_interval')->where('user_id',auth()->id())->first();
        foreach($arrData as $key => $item) {
            $firmDetail = $item[0]->logo_url;
            echo $key;echo PHP_EOL;
            $explodeKey = explode('|', $key);            
            echo $preparedFor = $explodeKey[0];echo PHP_EOL;
            echo $preparedEmail = $explodeKey[1];echo PHP_EOL;
            echo count($item);echo PHP_EOL;
            \Mail::to('jignesh.prajapati@plutustec.com')->send(new NotificationActivityMail($item, $firmDetail, $preparedFor, $preparedEmail));
        }
        
        
        Log::info("Activity notification reminder Command End : ". date('Y-m-d H:i:s'));
    }
}
//sudo php artisan notification:email