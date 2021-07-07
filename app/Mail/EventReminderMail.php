<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $event, $firm, $user, $attendEvent;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($event, $firm, $user, $attendEvent)
    {
        $this->event = $event;
        $this->firm = $firm;
        $this->user = $user;
        $this->attendEvent = $attendEvent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info("email markdown");
        // return $this->view('view.name');
        return $this
            // ->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'))
            ->subject("Reminder: Upcoming Event")
            ->markdown('emails.event_reminder_email', ['event' => $this->event, 'firm' => $this->firm, 'user' => $this->user, 'attendEvent' => $this->attendEvent]);
    }
}
