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
        $result = CaseEventReminder::where("reminder_type", "email")->whereHas("event", function($query) {
                        $query->whereDate("start_date", Carbon::now());
                    })->where("reminder_frequncy", "minute")/* ->where("event_id", "38439") */
                    ->with('event', 'event.eventLinkedStaff', 'event.case', 'event.eventLocation', 'event.case.caseStaffAll', 'event.eventLinkedContact', 'event.eventLinkedLead')
                    ->get();
        if($result) {
            foreach($result as $key => $item) {
                // return $firmDetail = firmDetail($item->event->case->firm_id);
                /* if($item->reminder_user_type == "attorney" || $item->reminder_user_type == "staff" || $item->reminder_user_type == "paralegal") {
                    $eventLinkedUser = $item->event->eventLinkedStaff->pluck('id');
                    $caseLinkedUser = $item->event->case->caseStaffAll->pluck('user_id');
                    $userType = ($item->reminder_user_type == "attorney") ? 1 : (($item->reminder_user_type == "staff") ? 3 : 2);
                    $users = User::whereIn("id", $eventLinkedUser)->orWhereIn("id", $caseLinkedUser)->where("user_type", $userType)->get();
                    $attendEvent = $item->event->eventLinkedStaff->pluck("pivot.attending", 'id')->toArray();
                // } else if($item->reminder_user_type == "staff") {
                //     $eventLinkedUser = $item->event->eventLinkedStaff->pluck('id');
                //     $caseLinkedUser = $item->event->case->caseStaffAll->pluck('user_id');
                //     $users = User::whereIn("id", $eventLinkedUser)->orWhereIn("id", $caseLinkedUser)->where("user_type", "3")->get();
                //     $attendEvent = $item->event->eventLinkedStaff->pluck("pivot.attending", 'id')->toArray();
                // } else if($item->reminder_user_type == "paralegal") {
                //     $eventLinkedUser = $item->event->eventLinkedStaff->pluck('id');
                //     $caseLinkedUser = $item->event->case->caseStaffAll->pluck('user_id');
                //     $users = User::whereIn("id", $eventLinkedUser)->orWhereIn("id", $caseLinkedUser)->where("user_type", "2")->get();
                //     $attendEvent = $item->event->eventLinkedStaff->pluck("pivot.attending", 'id')->toArray();
                } else if($item->reminder_user_type == "client-lead") {
                    $eventLinkContactIds = $item->event->eventLinkedContact->pluck('id');
                    $eventLinkedLeadIds = $item->event->eventLinkedLead->pluck('user_id');
                    $users = User::whereIn("id", $eventLinkContactIds)->orWhereIn("id", $eventLinkedLeadIds)->get();
                    if(count($eventLinkContactIds)) {
                        $attendEvent = $item->event->eventLinkedContact->pluck("pivot.attending", 'id')->toArray();
                    } else {
                        $attendEvent = $item->event->eventLinkedLead->pluck("pivot.attending", 'id')->toArray();
                    }
                } else {
                    $users = User::whereId($item->created_by)->get();
                    $attendEvent = [$item->created_by => "yes"];
                } */
                // return $attendEvent;
                $users = $this->getEventLinkedUser($item, "email");
                if(count($users)) {
                    $eventStartTime = Carbon::parse($item->event->start_date.' '.$item->event->start_time)->format('Y-m-d H:i');
                    $now = Carbon::now()->addMinutes($item->reminer_number)->format('Y-m-d H:i');
                    if(Carbon::parse($now)->eq(Carbon::parse($eventStartTime))) {
                        Log::info("minute time true");
                        dispatch(new EventReminderEmailJob($item->event, $users, $attendEvent));
                    }
                }
            }
        }
    }
}
