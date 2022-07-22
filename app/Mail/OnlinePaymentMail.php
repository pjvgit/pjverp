<?php

namespace App\Mail;

use App\Invoices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OnlinePaymentMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $payableRecord, $firm, $user, $template, $userType, $onlinePayment, $payableType;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($payableRecord, $firm, $user = null, $template, $userType, $onlinePayment, $payableType)
    {
        $this->payableRecord = $payableRecord;
        $this->firm = $firm;
        $this->user = $user;
        $this->template = $template;
        $this->userType = $userType;
        $this->onlinePayment = $onlinePayment;
        $this->payableType = $payableType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->payableType == 'invoice' && $this->payableRecord == null) {
            $this->payableRecord = Invoices::whereId()->first();
        }
        \Log::info("unser confirm fundrequest: ". @$this->payableRecord->allocateToCase);
        switch ($this->userType) {
            case 'client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_confirm_email_client', ['payableRecord' => $this->payableRecord, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'user':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_confirm_email_user', ['payableRecord' => $this->payableRecord, 'user' => $this->user, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'cash_reference_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_reference_email_client', ['payableRecord' => $this->payableRecord, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'cash_confirm_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_confirm_email_client', ['payableRecord' => $this->payableRecord, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'cash_confirm_user':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_confirm_email_user', ['payableRecord' => $this->payableRecord, 'user' => $this->user, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'cash_payment_reminder_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_confirm_email_client', ['payableRecord' => $this->payableRecord, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'cash_reference_expired_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_confirm_email_client', ['payableRecord' => $this->payableRecord, 'payableRecord' => $this->payableRecord, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'bank_reference_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_reference_email_client', ['payableRecord' => $this->payableRecord, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'bank_confirm_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_confirm_email_client', ['payableRecord' => $this->payableRecord, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'bank_confirm_user':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_confirm_email_user', ['payableRecord' => $this->payableRecord, 'user' => $this->user, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'bank_reference_expired_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_confirm_email_client', ['payableRecord' => $this->payableRecord, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'bank_payment_reminder_client':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_confirm_email_client', ['payableRecord' => $this->payableRecord, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            case 'client_credit_card_refund':
                return $this
                ->subject($this->template->subject)
                ->markdown('emails.online_payment_confirm_email_client', ['payableRecord' => $this->payableRecord, 'firm' => $this->firm, 'template' => $this->template, 'onlinePayment' => $this->onlinePayment, 'payableType' => $this->payableType]);
                break;
            default:
                # code...
                break;
        }
    }
}
