<?php

namespace App\Console\Commands;

use App\Invoices;
use App\Jobs\InvoiceReminderEmailJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InvoiceReminderEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:reminderemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send invoice reminder email before 7 days/on due date/after 7 days of due date to client';

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
        $result = Invoices::where("automated_reminder", "yes")->whereNotNull("due_date")
                    ->whereRaw("due_date = '".Carbon::now()->subDays(7)->format("Y-m-d")."' OR due_date = '".Carbon::now()->format("Y-m-d")."' OR due_date = '".Carbon::now()->addDays(7)->format("Y-m-d")."'")
                    ->with("case", "case.caseBillingClient")
                    ->get();
        if($result) {
            foreach($result as $key => $item) {
                if(count($item->case->caseBillingClient)) {
                    foreach($item->case->caseBillingClient as $userkey => $useritem) {
                        $date = Carbon::now($useritem->user_timezone); // Carbon::now('Europe/Moscow'), Carbon::now('Europe/Amsterdam') etc..
                        Log::info($useritem->user_timezone."=".$date);
                        if ($date->hour === 14) { 
                            Log::info("invoice day time true");
                            dispatch(new InvoiceReminderEmailJob($item, $useritem))->onConnection('database');
                        }
                    }
                }
            }
        }
    }
}
