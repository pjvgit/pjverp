<?php

namespace App\Console\Commands;

use App\EmailTemplate;
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
        Log::info("task command enter");
        $result = TaskReminder::where("reminder_type", "email")->whereIn("reminder_frequncy", ["day", "week"])
                    // ->where("task_id", 61)
                    ->whereDate("remind_at", Carbon::now())
                    ->whereNull("reminded_at")
                    ->with('task', 'task.taskLinkedStaff', 'task.case', 'task.leadAdditionalInfo', 'task.case.caseStaffAll', 'task.firm', 'task.taskLinkedContact')
                    ->get();
        Log::info("task current time: ".Carbon::now());
        Log::info("task result: ".$result);
        if($result) {
            $emailTemplate = EmailTemplate::whereId(27)->first();
            foreach($result as $key => $item) {
                Log::info("task id:". $item->task->id);
                $users = $this->getTaskLinkedUser($item, "email");
                // Log::info("task users:". $users);
                if(count($users) && $emailTemplate) {
                    foreach($users as $userkey => $useritem) {
                        $date = Carbon::now($useritem->user_timezone ?? "UTC"); // Carbon::now('Europe/Moscow'), Carbon::now('Europe/Amsterdam') etc..
                        Log::info($useritem->user_timezone."=".$date);
                        /* if ($date->hour === 05) { 
                            Log::info("task user email: ".$useritem->email.", task day time true");
                            dispatch(new TaskReminderEmailJob($item, $useritem, $emailTemplate));
                        } else {
                            Log::info("task user email: ".$useritem->email.", time not match");
                        } */
                        // Set task job dispatch time
                        $timestamp = Carbon::parse($item->remind_at)->format('Y-m-d').' 05:00:00';
                        $dispatchDate = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $useritem->user_timezone ?? "UTC");
                        $dispatchDate->setTimezone('UTC');
                        Log::info("task user time to utc time: ". $dispatchDate);
                        dispatch(new TaskReminderEmailJob($item, $useritem, $emailTemplate))->delay($dispatchDate);
                        TaskReminder::where("id", $item->id)->update(["reminded_at" => Carbon::now()]);
                    }
                } else {
                    Log::info("task no user found");
                }
            }
        } else {
            Log::info("no task found");
        }
    }
}
