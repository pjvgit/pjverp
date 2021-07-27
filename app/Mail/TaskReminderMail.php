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
    protected $task, $firm, $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($task, $firm, $user)
    {
        $this->task = $task;
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
        Log::info("Task reminder logo url: ".$this->firm->firm_logo_url);
        return $this
            // ->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'))
            ->subject("Task reminders")
            ->markdown('emails.task_reminder_email', ['task' => $this->task, 'firm' => $this->firm, 'user' => $this->user]);
    }
}
