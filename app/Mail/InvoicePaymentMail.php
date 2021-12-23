<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InvoicePaymentMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $invoice, $firm, $user, $template, $userType, $onlinePayment;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice, $firm, $user = null, $template, $userType, $onlinePayment)
    {
        $this->invoice = $invoice;
        $this->firm = $firm;
        $this->user = $user;
        $this->template = $template;
        $this->userType = $userType;
        $this->onlinePayment = $onlinePayment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        switch ($this->userType) {
            case 'client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.invoice_online_payment_confirm_email_client', ['firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment]);
                break;
            case 'user':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.invoice_online_payment_confirm_email_user', ['invoice' => $this->invoice, 'user' => $this->user, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment]);
                break;
            case 'cash_reference_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.invoice_online_payment_reference_email_client', ['firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment]);
                break;
            case 'cash_confirm_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.invoice_online_payment_confirm_email_client', ['firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment]);
                break;
            case 'cash_confirm_user':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.invoice_online_payment_confirm_email_user', ['invoice' => $this->invoice, 'user' => $this->user, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment]);
                break;
            case 'bank_reference_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.invoice_online_payment_reference_email_client', ['invoice' => $this->invoice, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment]);
                break;
            case 'bank_confirm_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.invoice_online_payment_confirm_email_client', ['firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment]);
                break;
            case 'bank_confirm_user':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.invoice_online_payment_confirm_email_user', ['invoice' => $this->invoice, 'user' => $this->user, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment]);
                break;
            default:
                # code...
                break;
        }
    }
}
