<?php

namespace App\Console\Commands;

use App\CaseEventReminder;
use App\Jobs\EventReminderEmailJob;
use App\Traits\EventReminderTrait;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EventDayReminderEmailCommand extends Command
{
    use EventReminderTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eventday:reminderemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send evant reminder email before days/weeks to firmuser/client/company';

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
        $result = CaseEventReminder::where("reminder_type", "email")->whereIn("reminder_frequncy", ["day", "week"])/* ->where("event_id", "38439") */
                    ->whereDate("remind_at", Carbon::now())
                    ->with('event', 'event.eventLinkedStaff', 'event.case', 'event.eventLocation', 'event.case.caseStaffAll', 'event.eventLinkedContact', 'event.eventLinkedLead')
                    ->get();
        if($result) {
            foreach($result as $key => $item) {
                // return $firmDetail = firmDetail($item->event
                $response = $this->getEventLinkedUser($item, "email");
                $users = $response["users"] ?? [];
                $attendEvent = $response["attendEvent"] ?? [];
                if(count($users)) {
                    foreach($users as $userkey => $useritem) {
                        $date = Carbon::now($useritem->user_timezone); // Carbon::now('Europe/Moscow'), Carbon::now('Europe/Amsterdam') etc..
                        Log::info($useritem->user_timezone."=".$date);
                        if ($date->hour === 00) { 
                            Log::info("day time true");
                            dispatch(new EventReminderEmailJob($item->event, $useritem, $attendEvent, "day"));
                        }
                    }
                }
            }
        }
    }
}
