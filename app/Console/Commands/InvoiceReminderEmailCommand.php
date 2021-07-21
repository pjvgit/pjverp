<?php

namespace App\Console\Commands;

use App\EmailTemplate;
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
        $result = Invoices::where("automated_reminder", "yes")->whereNotNull("due_date")->has("invoiceShared")
                    ->where(function($query) {
                        $query
                        ->whereRaw("due_date = '".Carbon::now()->subDays(7)->format("Y-m-d")."' OR due_date = '".Carbon::now()->format("Y-m-d")."' OR due_date = '".Carbon::now()->addDays(7)->format("Y-m-d")."'")
                        ->orWhereHas("invoiceFirstInstallment", function($q) {
                            $q->whereRaw("due_date = '".Carbon::now()->subDays(7)->format("Y-m-d")."' OR due_date = '".Carbon::now()->format("Y-m-d")."' OR due_date = '".Carbon::now()->addDays(7)->format("Y-m-d")."'");
                        });
                    })
                    ->with("case", "case.caseBillingClient", "invoiceFirstInstallment", "firmDetail")
                    ->get();
        if($result) {
            foreach($result as $key => $item) {
                $currentDate = \Carbon\Carbon::now()->format('Y-m-d');
                if (!empty($item->invoiceFirstInstallment)) {
                    $dueDate = $item->invoiceFirstInstallment->due_date;
                } else if($item->due_date) {
                    $dueDate = $item->due_date;
                } else {
                    $dueDate = "";
                }
                if($dueDate) {
                    $currentDate = \Carbon\Carbon::createFromFormat('Y-m-d', $currentDate);
                    $dueDate = \Carbon\Carbon::createFromFormat('Y-m-d', $dueDate);
                    if($dueDate->eq($currentDate)) { // For present
                        $remindDate = $currentDate;
                        $emailTemplateId = 23;
                    } else if($dueDate->gt($currentDate)) { // For future
                        $remindDate = $dueDate->subDays(7);
                        $emailTemplateId = 24;
                    } else if($dueDate->lt($currentDate)) { // For past
                        $remindDate = $dueDate->addDays(7);
                        $emailTemplateId = 22;
                    } else {
                        $remindDate = "";
                    }
                }
                $emailTemplate = EmailTemplate::whereId($emailTemplateId)->first();
                if(count($item->case->caseBillingClient)) {
                    foreach($item->case->caseBillingClient as $userkey => $useritem) {
                        $date = Carbon::now($useritem->user_timezone); // Carbon::now('Europe/Moscow'), Carbon::now('Europe/Amsterdam') etc..
                        Log::info($useritem->user_timezone."=".$date);
                        if ($date->hour === 05) { 
                            if($emailTemplate) {
                                Log::info("invoice day time true");
                                dispatch(new InvoiceReminderEmailJob($item, $useritem, $emailTemplate));
                            }
                        }
                    }
                }
            }
        }
    }
}
