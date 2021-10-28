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
                    ->whereNotIn("status", ["Paid", "Forwarded"])
                    /* ->where(function($query) {
                        $query
                        ->whereRaw("due_date = '".Carbon::now()->subDays(7)->format("Y-m-d")."' OR due_date = '".Carbon::now()->format("Y-m-d")."' OR due_date = '".Carbon::now()->addDays(7)->format("Y-m-d")."'")
                        ->orWhereHas("invoiceFirstInstallment", function($q) {
                            $q->whereRaw("due_date = '".Carbon::now()->subDays(7)->format("Y-m-d")."' OR due_date = '".Carbon::now()->format("Y-m-d")."' OR due_date = '".Carbon::now()->addDays(7)->format("Y-m-d")."'");
                        });
                    }) */
                    // ->whereId(176)
                    ->with("case", "invoiceSharedUser", "invoiceFirstInstallment", "firmDetail")
                    ->get();
        if($result) {
            foreach($result as $key => $item) {
                Log::info("invoice id: ". $item->id);
                $currentDate = \Carbon\Carbon::now()->format('Y-m-d');
                if (!empty($item->invoiceFirstInstallment)) {
                    $dueDate = $item->invoiceFirstInstallment->due_date;
                    Log::info("installment due date:". $dueDate);
                } else if($item->due_date) {
                    $dueDate = $item->due_date;
                } else {
                    $dueDate = "";
                }
                $emailTemplateId = ''; $remindDate = ""; $remindType = ""; $days = 0;
                if($dueDate) {
                    $currentDate = \Carbon\Carbon::createFromFormat('Y-m-d', $currentDate);
                    $dueDate = \Carbon\Carbon::createFromFormat('Y-m-d', $dueDate);
                    $remindSetting = collect($item->invoice_setting['reminder'] ?? []);

                    if($dueDate->eq($currentDate)) { // For present due date
                        $onDue = $remindSetting->where("remind_type", "on the due date")->where('is_reminded', 'no');
                        if($onDue->count()) {
                            $remindDate = $currentDate;
                            $emailTemplateId = 23;
                            $remindType = "on the due date";
                        }
                    } else if($dueDate->gt($currentDate)) { // For future due date
                        $dueIn = $remindSetting->where("remind_type", 'due in')->where('is_reminded', 'no');
                        Log::info("Due ins:". $dueIn);
                        if($dueIn->count()) {
                            $dueIn = $dueIn->sortByDesc('days')->first();
                            $days = $dueIn['days'];
                            Log::info("Due in days:". $days);
                            $remindDate = $dueDate->subDays($days);
                            Log::info("Remind date:". $remindDate);
                            $emailTemplateId = 24;
                            $remindType = "due in";
                        }
                    } else if($dueDate->lt($currentDate)) { // For past due date
                        $overDue = $remindSetting->where("remind_type", 'overdue by')->where('is_reminded', 'no');
                        if($overDue->count()) {
                            $overDue = $overDue->sortBy('days')->first();
                            $days = $overDue['days'];
                            $remindDate = $dueDate->addDays($days);
                            $emailTemplateId = 22;
                            $remindType = "overdue by";
                        }
                    } else {
                        $remindDate = "";
                    }
                }
                Log::info("remind date: ".$remindDate.', emailTemplateId: '.$emailTemplateId);
                // return $remindDate;
                $emailTemplate = EmailTemplate::whereId($emailTemplateId)->first();
                if($emailTemplate && $remindDate) {
                    $remindDate = \Carbon\Carbon::createFromFormat('Y-m-d', $remindDate->format('Y-m-d'));
                    if($remindDate->eq($currentDate)) {
                        if(count($item->invoiceSharedUser)) {
                            foreach($item->invoiceSharedUser as $userkey => $useritem) {
                                $date = Carbon::now($useritem->user_timezone ?? 'UTC'); // Carbon::now('Europe/Moscow'), Carbon::now('Europe/Amsterdam') etc..
                                Log::info($useritem->user_timezone."=".$date);
                                if ($date->hour === 05) { 
                                    if($emailTemplate) {
                                        Log::info("invoice day time true");
                                        dispatch(new InvoiceReminderEmailJob($item, $useritem, $emailTemplate, $remindType, $days));
                                    }
                                }
                            }
                        } else {
                            Log::info("no billing client:". $item->id);
                        }
                    } else {
                        Log::info("invoice remind date: ".$remindDate);
                    }
                } else {
                    Log::info("invoice email template: ".$emailTemplateId);
                }
            }
        }
    }
}
