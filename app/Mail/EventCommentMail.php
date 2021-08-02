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
    protected $event, $firm, $user, $template, $commentAddedUser;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($event, $firm, $user, $template, $commentAddedUser)
    {
        $this->event = $event;
        $this->firm = $firm;
        $this->user = $user;
        $this->template = $template;
        $this->commentAddedUser = $commentAddedUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info("comment enail");
        return $this
            // ->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'))
            ->subject($this->template->subject)
            ->markdown('emails.event_comment', ['event' => $this->event, 'firm' => $this->firm, 'user' => $this->user, 'template' => $this->template, 'commentAddedUser' => $this->commentAddedUser]);
    }
}
