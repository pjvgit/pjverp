<?php

namespace App\Console\Commands;

use App\Jobs\TaskReminderEmailJob;
use App\TaskReminder;
use App\Traits\TaskReminderTrait;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TaskReminderEmailCommand extends Command
{
    use TaskReminderTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taskday:reminderemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send task reminder email before days/weeks to firmuser';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $result = TaskReminder::where("reminder_type", "email")->whereIn("reminder_frequncy", ["day", "week"])
                    // ->where("task_id", 90)
                    ->whereDate("remind_at", Carbon::now()) 
                    ->whereNull("reminded_at")
                    ->with('task', 'task.taskLinkedStaff', 'task.case', 'task.lead', 'task.case.caseStaffAll', 'task.firm', 'task.lead.userLeadAdditionalInfo')
                    ->get();
        if($result) {
            foreach($result as $key => $item) {
                $users = $this->getTaskLinkedUser($item, "email");
                if(count($users)) {
                    foreach($users as $userkey => $useritem) {
                        $date = Carbon::now($useritem->user_timezone); // Carbon::now('Europe/Moscow'), Carbon::now('Europe/Amsterdam') etc..
                        Log::info($useritem->user_timezone."=".$date);
                        if ($date->hour === 00) { 
                            Log::info("task day time true");
                            dispatch(new TaskReminderEmailJob($item, $useritem))->onConnection('database');
                        }
                    }
                }
            }
        }
    }
}
