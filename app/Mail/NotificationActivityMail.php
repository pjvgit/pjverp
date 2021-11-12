<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationActivityMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $history, $preparedFor, $preparedEmail, $firm, $cases, $forFirm;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($history, $firm, $preparedFor, $preparedEmail, $cases, $forFirm)
    {
        $this->history = $history;
        $this->firm = $firm;
        $this->preparedFor = $preparedFor;
        $this->preparedEmail = $preparedEmail;
        $this->cases = $cases;
        $this->forFirm = $forFirm;
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
            ->subject("Recent activity in ".env('APP_NAME'))
            ->markdown('emails.recent_notification_email', ['history' => $this->history, 'firm' => $this->firm, 'preparedFor' => $this->preparedFor, 'preparedEmail' => $this->preparedEmail, 'cases' => $this->cases, 'forFirm' => $this->forFirm]);
    }
}
