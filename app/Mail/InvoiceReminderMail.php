<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $invoice, $firm, $user, $template;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice, $firm, $user, $template)
    {
        $this->invoice = $invoice;
        $this->firm = $firm;
        $this->user = $user;
        $this->template = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = str_replace('[INVOICE_NUMBER]', "#".sprintf('%06d', $this->invoice->id), $this->template->subject);
        $subject = str_replace('[FIRM_NAME]', @$this->firm->firm_name, $subject);
        if($this->template->id == 24) {
            $date = ($this->invoice->invoiceFirstInstallment) ? Carbon::parse($this->invoice->invoiceFirstInstallment->due_date) : Carbon::parse($this->invoice->due_date);
            $txt = $date->isTomorrow() ? "tomorrow" : "soon";
            $subject = str_replace('[TOMO_SOON]', $txt, $subject);
        }
        // Log::info("Invoice subject". $subject);
        return $this
            ->subject($subject)
            ->markdown('emails.invoice_reminder_email', ['invoice' => $this->invoice, 'firm' => $this->firm, 'user' => $this->user, 'template' => $this->template]);
    }
}
