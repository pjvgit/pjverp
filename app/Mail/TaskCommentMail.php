<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TaskCommentMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $task, $firm, $user, $template, $userType;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($task, $firm, $user, $template, $userType)
    {
        $this->task = $task;
        $this->firm = $firm;
        $this->user = $user;
        $this->template = $template;
        $this->userType = $userType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject($this->template->subject)
        ->markdown('emails.task_comment_email', ['task' => $this->task, 'firm' => $this->firm, 'user' => $this->user, 'template' => $this->template, 'userType' => $this->userType]);
    }
}
