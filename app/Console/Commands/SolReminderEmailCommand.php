<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use File;
use App\CaseSolReminder,App\CaseMaster;

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
    protected $description = 'Send sol reminder at mid night on every day.';

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
                $CaseSolReminderData = CaseSolReminder::where('case_id',$v->id)->where('reminder_type','email')->get();
                if(count($CaseSolReminderData) > 0) {
                    foreach($CaseSolReminderData as $i => $j){
                        //  echo $j->reminder_number.'- days'.\r\n;   
                    }
                }
            }
        }
        Log::info("SOL reminder Command End : ". date('Y-m-d H:i:s'));
    }
}
//sudo php artisan sol:reminderemail