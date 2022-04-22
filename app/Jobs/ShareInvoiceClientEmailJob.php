<?php

namespace App\Jobs;

use App\Mail\ShareInvoiceClientMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ShareInvoiceClientEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $invoice, $user, $emailTemplate;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($invoice, $user, $emailTemplate)
    {
        $this->invoice = $invoice;
        $this->user = $user;
        $this->emailTemplate = $emailTemplate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user->email)->send((new ShareInvoiceClientMail($this->invoice, @$this->invoice->firmDetail, $this->user, $this->emailTemplate)));
    }
}
