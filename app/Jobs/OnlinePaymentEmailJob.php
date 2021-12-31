<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Firm,App\EmailTemplate;
use App\Mail\OnlinePaymentMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OnlinePaymentEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payableRecord,$user,$onlinePayment,$emailTemplateId,$userType, $payableType;

    /**
     * Create a new job instance.
     *
     * @param  Podcast  $podcast
     * @return void
     */
    public function __construct($payableRecord = null,$user,$emailTemplateId,$onlinePayment,$userType, $payableType)
    {
        $this->payableRecord = $payableRecord;
        $this->user = $user;
        $this->onlinePayment = $onlinePayment;   
        $this->emailTemplateId = $emailTemplateId;   
        $this->userType = $userType;   
        $this->payableType = $payableType;   
    }

    /**
     * Execute the job.
     *
     * @param  AudioProcessor  $processor
     * @return void
     */
    public function handle()
    {
        Log::info("online payment job enter: ". $this->userType);
        $firmData = Firm::find($this->onlinePayment->firm_id); 
        $getTemplateData = EmailTemplate::find($this->emailTemplateId);
        Mail::to($this->user->email)->send((new OnlinePaymentMail($this->payableRecord, $firmData, $this->user, $getTemplateData, $this->userType, $this->onlinePayment, $this->payableType)));
    }
}