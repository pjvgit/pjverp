<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LeadEventInvitationMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $event, $firm, $user, $attendEvent, $eventRecurring, $eventAction;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($event, $firm, $user, $attendEvent, $eventRecurring, $eventAction)
    {
        $this->event = $event;
        $this->firm = $firm;
        $this->user = $user;
        $this->attendEvent = $attendEvent;
        $this->eventRecurring = $eventRecurring;
        $this->eventAction = $eventAction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = ($this->eventAction == "add") ? "You have an upcoming event" : "Your event has been updated";
        return $this
            // ->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'))
            ->subject($subject)
            ->markdown('emails.lead_event_invitation_email', ['event' => $this->event, 'firm' => $this->firm, 'user' => $this->user, 'attendEvent' => $this->attendEvent, 'eventRecurring' => $this->eventRecurring]);
    }
}
