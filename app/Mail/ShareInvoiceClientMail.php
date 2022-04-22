<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShareInvoiceClientMail extends Mailable
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
        return $this
            ->subject(@$this->firm->firm_name. " has sent you an invoice")
            ->markdown('emails.share_invoice_client_email', ['invoice' => $this->invoice, 'firm' => $this->firm, 'user' => $this->user, 'template' => $this->template]);
    }
}
