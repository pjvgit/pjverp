<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use File;
use App\CaseSolReminder,App\CaseMaster,App\CaseStaff,App\User;
use App\Mail\SolReminderMail;
use App\Jobs\SolReminderEmailJob;
use Carbon\Carbon;

class SolReminderEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sol:reminderemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send sol reminder on every hourly.';

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
        // Send sol reminder at mid night on every day. 
        // 0 5 * * * cd /var/www/html && php artisan sol:reminderemail >> /var/www/html/storage/logs/cron-job.log 2>&1
        // * * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1

        Log::info("SOL reminder Command Fired : ". date('Y-m-d H:i:s'));

        $result = CaseSolReminder::where("reminder_type", "email")
                    // ->whereDate("remind_at", \Carbon\Carbon::now())
                    ->whereNull("reminded_at")
                    ->with('case','case.caseFirm','case.caseStaffDetails')
                    ->get();
        // print_r(count($result));echo PHP_EOL;
        if($result) {            
            foreach($result as $key => $item) {
                $caseDetails = $item->case;
                $firmDetail = $caseDetails->caseFirm;
                // print_r($firmDetail);echo PHP_EOL;
                //  Reminders will be sent to all firm users linked to the case.
                
                foreach($caseDetails->caseStaffDetails as $caseStaff => $staff) {
                    $date = Carbon::now($staff->user_timezone ?? 'UTC'); // Carbon::now('Europe/Moscow'), Carbon::now('Europe/Amsterdam') etc..
                    $utcDate = Carbon::now('UTC');
                    if(date("Y-m-d",strtotime($utcDate)) === date("Y-m-d",strtotime($date))){
                        Log::info("Staff > ". $staff->id. ' > '. $staff->email. ' for time zone : '.$staff->user_timezone." at ".$date);echo PHP_EOL;
                        // if ($date->hour === 05) { 
                        //     echo "Mail send";echo PHP_EOL;
                        //     // $mailSend = \Mail::to($staff->email)->send(new SolReminderMail($caseDetails, $firmDetail, $staff));
                        //     Log::info("SOL reminder Email sent to : ". $staff->email. ' for time zone : '.$staff->user_timezone." at ".$date );
                        // }
                        $timestamp = $date->format('Y-m-d').' 05:00:00';
                        $dispatchDate = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $staff->user_timezone ?? 'UTC');
                        $dispatchDate->setTimezone('UTC');
                        Log::info("dispatchDate > ". $dispatchDate);
                        dispatch(new SolReminderEmailJob($item, $staff, $caseDetails, $firmDetail))->delay($dispatchDate);
                    }
                } 
                
                sleep(3);
                CaseSolReminder::where('id',$item->id)->update(['reminded_at' => date('Y-m-d H:i:s')]);
                Log::info("SOL reminder Update Item Id : ". $item->id." for case of :".$caseDetails->case_title."(".$caseDetails->id.")");
            }
        }
        Log::info("SOL reminder Command End : ". date('Y-m-d H:i:s'));
    }
}
//sudo php artisan sol:reminderemail