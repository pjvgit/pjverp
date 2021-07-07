<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $invoice, $firm, $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice, $firm, $user)
    {
        $this->invoice = $invoice;
        $this->firm = $firm;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->view('view.name');
        return $this
            // ->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'))
            ->subject("Payment reminders")
            ->markdown('emails.invoice_reminder_email', ['invoice' => $this->invoice, 'firm' => $this->firm, 'user' => $this->user]);
    }
}
