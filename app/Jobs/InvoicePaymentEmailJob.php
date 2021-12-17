<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Firm,App\EmailTemplate;
use App\InvoiceOnlinePayment;
use App\Mail\InvoicePaymentMail;
use App\Mail\TaskCommentMail;
use App\Task;
use App\TaskComment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoicePaymentEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice,$user,$online_payment_id,$emailTemplateId,$userType;

    /**
     * Create a new job instance.
     *
     * @param  Podcast  $podcast
     * @return void
     */
    public function __construct($invoice = null,$user,$emailTemplateId,$online_payment_id,$userType)
    {
        $this->invoice = $invoice;
        $this->user = $user;
        $this->online_payment_id = $online_payment_id;   
        $this->emailTemplateId = $emailTemplateId;   
        $this->userType = $userType;   
    }

    /**
     * Execute the job.
     *
     * @param  AudioProcessor  $processor
     * @return void
     */
    public function handle()
    {
        Log::info("invoice payment email job handle");
        $onlinePayment = InvoiceOnlinePayment::whereId($this->online_payment_id)->first();
        $firmData = Firm::find($this->invoice->firm_id); 
        $getTemplateData = EmailTemplate::find($this->emailTemplateId);
        Mail::to($this->user->email)->send((new InvoicePaymentMail($this->invoice, $firmData, $this->user, $getTemplateData, $this->userType, $onlinePayment)));
    }
}