<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TaskReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $task, $firm, $user, $template;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($task, $firm, $user, $template)
    {
        $this->task = $task;
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
        // return $this->view('view.name');
        $subject = str_replace('[TASK_TITLE]', $this->task->task_title, $this->template->subject);
        return $this
            // ->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'))
            ->subject($subject)
            ->markdown('emails.task_reminder_email', ['task' => $this->task, 'firm' => $this->firm, 'user' => $this->user, 'template' => $this->template]);
    }
}
