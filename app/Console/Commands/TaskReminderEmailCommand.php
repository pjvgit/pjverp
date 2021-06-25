<?php

namespace App\Console\Commands;

use App\Jobs\TaskReminderEmailJob;
use App\TaskReminder;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TaskReminderEmailCommand extends Command
{
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
                    ->where("task_id", 90)
                    ->with('task', 'task.taskLinkedStaff', 'task.case', 'task.lead', 'task.case.caseStaffAll', 'task.firm', 'task.lead.userLeadAdditionalInfo')
                    ->get();
        if($result) {
            foreach($result as $key => $item) {
                $taskLinkedUser = $item->task->taskLinkedStaff->pluck('id');
                if($item->task->case_id != "") {
                    $caseLinkedUser = $item->task->case->caseStaffAll->pluck('user_id');
                } else if($item->task->lead_id != "") {
                    $caseLinkedUser = [@$item->task->lead->userLeadAdditionalInfo->assigned_to];
                } else {
                    $caseLinkedUser = [];
                }
                $users = User::whereIn("id", $taskLinkedUser)->orWhereIn("id", $caseLinkedUser);
                if($item->reminder_user_type == "attorney") {
                    $users = $users->where("user_type", "1");
                } else if($item->reminder_user_type == "staff") {
                    $users = $users->where("user_type", "3");
                } else if($item->reminder_user_type == "paralegal") {
                    $users = $users->where("user_type", "2");
                } else {
                    $users = User::whereId($item->created_by);
                }
                $users = $users->get();
                $taskDueOn = Carbon::parse($item->task->task_due_on)->format('Y-m-d');
                if($item->reminder_frequncy == "week") {
                    $remindDate = Carbon::now()->addWeeks($item->reminer_number)->format('Y-m-d');
                } else {
                    $remindDate = Carbon::now()->addDays($item->reminer_number)->format('Y-m-d');
                }
                if(Carbon::parse($taskDueOn)->eq(Carbon::parse($remindDate))) {
                    Log::info("task day time true");
                    dispatch(new TaskReminderEmailJob($item->task, $users));
                }
            }
        }
    }
}
