<?php

namespace App\Jobs;

use App\Mail\TaskReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class TaskReminderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $task, $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($task, $user)
    {
        $this->task = $task;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!empty($this->user)) {
            // foreach($this->user as $key => $item) {
                Mail::to($this->user->email)->send((new TaskReminderMail($this->task, $this->task->firm, $this->user)));
                /* Mail::send('emails.event_reminder_email', ['event' => $this->event, 'firm' => $firmDetail, 'user' => $item], function ($m) use($item){
                    $m->to($item->email, $item->full_name)->subject("Reminder: Upcoming Event");
                }); */
            // }
        }
    }
}
