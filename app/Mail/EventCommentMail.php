<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EventCommentMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $event, $firm, $user, $template, $commentAddedUser, $userType, $eventRecurring;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($event, $firm, $user, $template, $commentAddedUser, $userType, $eventRecurring)
    {
        $this->event = $event;
        $this->firm = $firm;
        $this->user = $user;
        $this->template = $template;
        $this->commentAddedUser = $commentAddedUser;
        $this->userType = $userType;
        $this->eventRecurring = $eventRecurring;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->userType == "staff") {
            return $this
            ->subject($this->template->subject)
            ->markdown('emails.event_comment_staff', ['event' => $this->event, 'firm' => $this->firm, 'user' => $this->user, 'template' => $this->template, 'commentAddedUser' => $this->commentAddedUser]);
        } else {
            return $this
            ->subject($this->template->subject)
            ->markdown('emails.event_comment_client', ['event' => $this->event, 'firm' => $this->firm, 'user' => $this->user, 'template' => $this->template, 'commentAddedUser' => $this->commentAddedUser, 'eventRecurring' => $this->eventRecurring]);
        }
    }
}
