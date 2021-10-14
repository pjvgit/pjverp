<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SolReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $case, $firm, $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($case, $firm, $user)
    {
        $this->case = $case;
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
            ->subject("Reminder: Upcoming SOL")
            ->markdown('emails.sol_reminder_email', ['case' => $this->case, 'firm' => $this->firm, 'user' => $this->user]);
    }
}
