<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use File;
use App\CaseSolReminder,App\CaseMaster,App\CaseStaff;
use App\Mail\SolReminderMail;
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
        $caseMasterData = CaseMaster::where('case_statute_date','!=', null)->where('case_statute_date','>', date('Y-m-d'))->get();
        if(count($caseMasterData) > 0) {
            foreach($caseMasterData as $k =>$v){
                // print_r('case-id : '.$v->id);echo PHP_EOL;
                
                //Client List
                $CaseStaff = CaseStaff::join('users','users.id','=','case_staff.user_id')
                ->select("users.id","users.first_name","users.last_name","users.email","users.user_timezone","users.firm_name")
                ->where("case_staff.case_id",$v->id)->where("users.user_status","1")->get();
                if(count($CaseStaff) > 0){
                    $CaseSolReminderUpdateId = 0;
                    foreach($CaseStaff as $key =>$staff){
                        $firmDetail = firmDetail($staff->firm_name);
                        // print_r('--staff-id : '.$staff->id.'--staff-user_timezone : '.$staff->user_timezone);echo PHP_EOL;

                        $CaseSolReminderData = CaseSolReminder::where('case_id',$v->id)->where('reminder_type','email')->where("reminded_at","=",NULL)->get();
                        if(count($CaseSolReminderData) > 0) {
                            foreach($CaseSolReminderData as $i => $j){

                                // print_r('---days : '. $j->reminer_number);echo PHP_EOL;
                                // $v->case_statute_date
                                // print_r('-----case_statute_date : '. $v->case_statute_date);echo PHP_EOL;

                                $todayStaffDate = date('Y-m-d',strtotime(convertUTCToUserDate(date('Y-m-d',strtotime($v->case_statute_date)), $staff->user_timezone ?? 'UTC')));
                                // $todayStaffDate = convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($v->case_statute_date)))), $staff->user_timezone ?? 'UTC');
                                // print_r('  --    ---  todayStaffDate : '. $todayStaffDate);echo PHP_EOL;

                                $todayUserDate = \Carbon\Carbon::now((!(empty($staff->user_timezone))) ? $staff->user_timezone : 'UTC')->format('Y-m-d');
                                // print_r('  --    ---  todayUserDate : '. $todayUserDate);echo PHP_EOL;
                                
                                $reminderDate = \Carbon\Carbon::createFromFormat('Y-m-d', $todayStaffDate)->subDay($j->reminer_number)->format('Y-m-d'); // Subtracts reminder date day for case_statute_date 
                                // print_r('send mail date : '. $reminderDate);echo PHP_EOL;
                                
                                if($reminderDate == $todayUserDate){
                                    // print_r("Send mail to staff at - ".$staff->email);echo PHP_EOL;
                                    Log::info("SOL reminder : Send mail to staff at - ".$staff->email);
                                    $mailSend = \Mail::to($staff->email)->send(new SolReminderMail($v, $firmDetail, $staff));
                                    // print_r("Send mail - ". print($mailSend));echo PHP_EOL;
                                    // print_r("Casesolremiceder update at - ".$j->id);echo PHP_EOL;
                                    $CaseSolReminderUpdateId = $j->id;
                                }
                                // print_r('---------------------------------------------------------');echo PHP_EOL;
                            }
                        }else{
                            Log::info("No SOL Reminder found");
                        }
                    }
                    CaseSolReminder::where('id',$CaseSolReminderUpdateId)->update(['reminded_at' => date('Y-m-d')]);
                }else{
                    Log::info("SOL reminder : No staff found");
                }
            }
        }
        Log::info("SOL reminder Command End : ". date('Y-m-d H:i:s'));
    }
}
//sudo php artisan sol:reminderemail