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
                    /* ->whereHas("event", function($query) {
                        $query->whereDate("start_date", Carbon::now());
                    }) */
                    ->where("reminder_frequncy", "hour")/* ->where("event_id", "38439") */
                    ->whereDate("remind_at", Carbon::now())
                    ->with('event', 'event.eventLinkedStaff', 'event.case', 'event.eventLocation', 'event.case.caseStaffAll', 'event.eventLinkedContact', 'event.eventLinkedLead')
                    ->get();
        if($result) {
            foreach($result as $key => $item) {
                $response = $this->getEventLinkedUser($item, "email");
                $users = $response["users"] ?? [];
                $attendEvent = $response["attendEvent"] ?? [];
                $users = $this->getEventLinkedUser($item, "email");
                if(count($users)) {
                    $eventStartTime = Carbon::parse($item->event->start_date.' '.$item->event->start_time);
                    // $remindTime = Carbon::parse($item->event->start_date.' '.$item->event->start_time)->subHours($item->reminer_number)->format('Y-m-d H:i');
                    $now = Carbon::now()->format('Y-m-d H:i');
                    if(Carbon::parse($now)->gte(Carbon::parse($item->remind_at)) && $eventStartTime->gt(Carbon::parse($now))) {
                        Log::info("hour time true");
                        dispatch(new EventReminderEmailJob($item->event, $users, $attendEvent))->onConnection('database');
                    }
                }
            }
        }
    }
}
