<?php

namespace App\Jobs;

use App\Mail\TaskReminderMail;
use App\TaskReminder;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TaskReminderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $taskReminder, $user, $emailTemplate;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($taskReminder, $user, $emailTemplate)
    {
        $this->taskReminder = $taskReminder;
        $this->user = $user;
        $this->emailTemplate = $emailTemplate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!empty($this->user)) {
            Log::info("task mail job");
            Mail::to($this->user->email)->send((new TaskReminderMail($this->taskReminder->task, $this->taskReminder->task->firm, $this->user, $this->emailTemplate)));
            TaskReminder::where("id", $this->taskReminder->id)->update(["reminded_at" => Carbon::now()]);
        } else {
            Log::info("task job user not found");
        }
    }
}
