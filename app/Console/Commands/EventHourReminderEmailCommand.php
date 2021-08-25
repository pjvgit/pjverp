<?php

namespace App\Console\Commands;

use App\CaseEventReminder;
use App\Jobs\EventReminderEmailJob;
use App\Traits\EventReminderTrait;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EventHourReminderEmailCommand extends Command
{
    use EventReminderTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eventhour:reminderemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send evant reminder email before hours etc to firmuser/client/company';

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
                    ->where("reminder_frequncy", "hour")/* ->where("event_id", "38439") */
                    ->whereDate("remind_at", Carbon::now())
                    ->whereNull("reminded_at")
                    ->with('event', 'event.eventLinkedStaff', 'event.case', 'event.eventLocation', 'event.case.caseStaffAll', 'event.eventLinkedContact', 'event.eventLinkedLead')
                    ->get();
        if($result) {
            foreach($result as $key => $item) {
                $response = $this->getEventLinkedUser($item, "email");
                $users = $response["users"] ?? [];
                $attendEvent = $response["attendEvent"] ?? [];
                if(count($users)) {
                    $eventStartTime = Carbon::parse($item->event->start_date.' '.$item->event->start_time);
                    $currentTime = Carbon::now()->format('Y-m-d H:i');
                    $date1 = Carbon::createFromFormat('Y-m-d H:i', $currentTime);
                    $date2 = Carbon::createFromFormat('Y-m-d H:i', Carbon::parse($item->remind_at)->format('Y-m-d H:i'));
                    if($date1->gte($date2) && $eventStartTime->gt(Carbon::now())) {
                    // if($now->gte(Carbon::parse($item->remind_at)) && $eventStartTime->gt($now)) {
                        Log::info("EventHourReminderEmailCommand : hour time true");
                        dispatch(new EventReminderEmailJob($item, $users, $attendEvent))->onConnection('database');
                    } else {
                        Log::info("EventHourReminderEmailCommand : time not match");
                    }
                }
            }
        } 
    }
}
