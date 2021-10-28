<?php

namespace App\Console\Commands;

use App\CaseEventReminder;
use App\Jobs\EventReminderEmailJob;
use App\Traits\EventReminderTrait;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EventMinuteReminderEmailCommand extends Command
{
    use EventReminderTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eventminute:reminderemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send evant reminder email before minutes to firmuser/client/company';

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
        $result = CaseEventReminder::where("reminder_type", "email")
                    ->where("reminder_frequncy", "minute")/* ->where("event_id", "38439") */
                    ->whereDate("remind_at", Carbon::now())
                    ->whereNull("reminded_at")
                    ->whereHas("event", function($query) {
                        $query->where("is_SOL", "no");
                    })
                    ->with('event', 'event.eventLinkedStaff', 'event.case', 'event.eventLocation', 'event.case.caseStaffAll', 'event.eventLinkedContact', 'event.eventLinkedLead')
                    ->get();
        if($result) {
            Log::info("Minute Event Reminder Email Command Started :". date('Y-m-d H:i:s'));
            Log::info("Minute Event Reminder total records :". count($result));
            foreach($result as $key => $item) {
                Log::info("Event id :". $item->id);
                $response = $this->getEventLinkedUser($item, "email");
                $users = $response["users"] ?? [];
                $attendEvent = $response["attendEvent"] ?? [];
                if(count($users)) {
                    Log::info("user found:".$users);
                    $currentTime = Carbon::now()->format('Y-m-d H:i');
                    $date1 = Carbon::createFromFormat('Y-m-d H:i', $currentTime);
                    Log::info("carbon now:". $date1);
                    $date2 = Carbon::createFromFormat('Y-m-d H:i', Carbon::parse($item->remind_at)->format('Y-m-d H:i'));
                    Log::info("remind at:". $date2);
                    if($date1->eq($date2)) {
                        Log::info("EventMinuteReminderEmailCommand : minute time true");
                        dispatch(new EventReminderEmailJob($item, $users, $attendEvent))->onConnection('database');
                    } else {
                        Log::info("EventMinuteReminderEmailCommand : event minute time not match");
                    }
                } else {
                    Log::info("EventMinuteReminderEmailCommand : user not found");
                }
            }
            Log::info("Minute Event Reminder Email Command Ended :". date('Y-m-d H:i:s'));
        }
    }
}
