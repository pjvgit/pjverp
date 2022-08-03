<?php

namespace App\Console\Commands;

use App\InvoiceOnlinePayment;
use App\Jobs\InvoicePaymentEmailJob;
use App\Jobs\OnlinePaymentEmailJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InvoiceOnlinePaymentReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoiceonline:paymentreminderemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send invoice online cash/bank transfer payment reminder email before 1 day of expired date to client';

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
        $result = InvoiceOnlinePayment::where("conekta_payment_status", "pending_payment")->whereIn("payment_method", ["cash","bank transfer"])
                    ->whereHas("invoice", function($query) {
                        $query->where("online_payment_status", "pending")->whereNotIn("status", ["Paid", "Forwarded"]);
                    })
                    ->whereDate("conekta_reference_expires_at", Carbon::now()->addDays(1)->format("Y-m-d"))
                    // ->whereId(17)
                    ->with("invoice", "client")
                    ->get();

        if($result) {
            foreach($result as $key => $item) {
                Log::info("invoice online payment id: ". $item->id);
                $currentDate = Carbon::now();
                $userType = "cash_payment_reminder_client";
                $emailTemplateId = 39;
                if($item->payment_method == "bank transfer") {
                    $emailTemplateId = 41;
                    $userType = "bank_payment_reminder_client";
                }
                                
                if(!empty($item->client)) {
                    // Set job dispatch time
                    $timestamp = $currentDate->format('Y-m-d').' 05:00:00';
                    $dispatchDate = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $item->client->user_timezone ?? 'UTC');
                    $dispatchDate->setTimezone('UTC');
                    Log::info("client time to utc time: ". $dispatchDate);
                    dispatch(new OnlinePaymentEmailJob(null, $item->client, $emailTemplateId, $item, $userType, 'invoice'))->delay($dispatchDate);
                } else {
                    Log::info("no online payment client:". $item->id);
                }
            }
        }
    }
}
