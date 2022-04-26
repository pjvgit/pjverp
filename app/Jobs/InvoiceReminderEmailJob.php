<?php

namespace App\Jobs;

use App\Invoices;
use App\Mail\InvoiceReminderMail;
use App\SharedInvoice;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceReminderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $invoice, $user, $emailTemplate, $remindType, $days;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($invoice, $user, $emailTemplate, $remindType = null, $days = null)
    {
        $this->invoice = $invoice;
        $this->user = $user;
        $this->emailTemplate = $emailTemplate;
        $this->remindType = $remindType;
        $this->days = $days;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("invoice job handle & dispatched at: ". Carbon::now());
        Log::info("invoice detail id: ". $this->invoice->id);
        Mail::to($this->user->email)->send((new InvoiceReminderMail($this->invoice, @$this->invoice->firmDetail, $this->user, $this->emailTemplate)));
        // Sent/shared invoice count
        $sharedInv = SharedInvoice::where("user_id", $this->user->id)->where("invoice_id", $this->invoice->id)->first();
        $sharedInv->fill([
            'last_reminder_sent_on' => date('Y-m-d h:i:s'),
            'reminder_sent_counter' => $sharedInv->reminder_sent_counter + 1,
        ])->save();

        // Update invoice settings
        if($this->invoice->invoice_setting && $this->remindType && $this->days >= 0) {
            /* $invoiceSetting = $this->invoice->invoice_setting;
            foreach($invoiceSetting['reminder'] as $key => $item) {
                $is_reminded = $item['is_reminded'] ?? "no";
                if($this->remindType == $item['remind_type'] && $this->days == $item['days']) {
                    $is_reminded = "yes";
                }
                $jsonData['reminder'][] = [
                    'remind_type' => $item['remind_type'],
                    'days' => $item['days'],
                    'is_reminded' => $is_reminded,
                ];
            } */
            $decodeReminders = encodeDecodeJson($this->invoice->invoice_reminders);
            if($decodeReminders) {
                $newArray = [];
                foreach($decodeReminders as $ritem) {
                    if($ritem->remind_at == date('Y-m-d')) {
                        $ritem->reminded_at = Carbon::now();
                    }
                    $newArray[] = $ritem;
                }
                Invoices::whereId($this->invoice->id)->update(["invoice_reminders" => encodeDecodeJson($newArray, 'encode')]);
            }
            Log::info("invoice setting updated");
            // $invoiceSetting['reminder'] = $jsonData['reminder'];
            // $this->invoice->fill(['invoice_setting' => $invoiceSetting])->save();
        }
    }
}
