<?php

namespace App\Jobs;

use App\Mail\InvoiceReminderMail;
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
        // Log::info("invoice job handle");
        Mail::to($this->user->email)->send((new InvoiceReminderMail($this->invoice, @$this->invoice->firmDetail, $this->user, $this->emailTemplate)));
    }
}
