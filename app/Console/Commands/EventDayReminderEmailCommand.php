<?php

namespace App\Console\Commands;

use App\EventRecurring;
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
        $result = EventRecurring::whereJsonContains('event_reminders', ['reminder_type' => 'email'])
                    // ->whereJsonContains('event_reminders', ['remind_at' => "2022-03-30"])
                    ->whereJsonContains('event_reminders', ['reminded_at' => null])
                    ->whereHas("event", function($query) {
                        $query->where("is_SOL", "no");
                    })
                    ->with('event', 'event.case', 'event.eventLocation', 'event.case.caseStaffAll', 'event.eventLinkedContact', 'event.eventLinkedLead')
                    ->get();        
        if($result) {
            foreach($result as $key => $item) {
                Log::info("Event id :". $item->id);
                // return $firmDetail = firmDetail($item->event)
                Log::info("reminder_id > ".$item->id);
                $users = $attendEvent = [];
                $remindTime = '';
                $itemEventReminders = encodeDecodeJson($item->event_reminders)->where('reminder_type' , 'email');

                $eventStartDate = Carbon::parse($item->start_date);
                foreach($itemEventReminders as $er => $ev){                    
                    if($ev->reminder_frequncy == "week") {
                        $remindTime = $eventStartDate->subWeeks($ev->reminer_number)->format('Y-m-d');
                    } else {
                        $remindTime = $eventStartDate->subDays($ev->reminer_number)->format('Y-m-d');
                    }
                    $response = $this->getEventLinkedUserPopup($ev, "email", $item->event, $item);
                    if($response["users"]){
                        foreach ($response["users"] as $k =>$v){
                            $users[] = $v;        
                        }
                    }
                    if($response["attendEvent"]){
                        foreach ($response["attendEvent"] as $k =>$v){
                            $attendEvent[] = $v;        
                        }
                    }
                }
                // Log::info("user found:".$users);
                if(count($users)) {
                    foreach($users as $userkey => $useritem) {
                        Log::info($useritem);
                        $evntdate = Carbon::now($useritem->user_timezone ?? 'UTC'); // Carbon::now('Europe/Moscow'), Carbon::now('Europe/Amsterdam') etc..
                        // Log::info($useritem->user_timezone."=".$date);
                        // if ($date->hour === 05) { 
                        //     Log::info("EventDayReminderEmailCommand > day time true");
                        //     dispatch(new EventReminderEmailJob($item, $useritem, $attendEvent, "day"));
                        // }

                        // new logic
                        
                        $date = date("Y-m-d", strtotime($remindTime));
                        $timestamp = $date.' 05:00:00';
                        Log::info("dispatchDate > ". $timestamp);
                        Log::info("user_timezone > " . $useritem->user_timezone);
                        $dispatchDate = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $useritem->user_timezone ?? 'UTC');
                        $dispatchDate->setTimezone('UTC');
                        Log::info("dispatchDate > ". $dispatchDate);
                        dispatch(new EventReminderEmailJob($item, $useritem, $attendEvent, "day"))->delay($dispatchDate);
                    }
                } else {
                    Log::info("EventDayReminderEmailCommand > user not found");
                }
            }
        }
    }
    
    public function handle_old()
    {
        $result = CaseEventReminder::where("reminder_type", "email")->whereIn("reminder_frequncy", ["day", "week"])/* ->where("event_id", "38439") */
                    ->whereDate("remind_at", Carbon::now())
                    ->whereNull("reminded_at")
                    ->whereHas("event", function($query) {
                        $query->where("is_SOL", "no");
                    })
                    ->with('event', 'event.eventLinkedStaff', 'event.case', 'event.eventLocation', 'event.case.caseStaffAll', 'event.eventLinkedContact', 'event.eventLinkedLead')
                    ->get();
        if($result) {
            foreach($result as $key => $item) {
                Log::info("Event id :". $item->id);
                // return $firmDetail = firmDetail($item->event
                $response = $this->getEventLinkedUser($item, "email");
                $users = $response["users"] ?? [];
                $attendEvent = $response["attendEvent"] ?? [];
                if(count($users)) {
                    // Log::info("user found:".$users);
                    foreach($users as $userkey => $useritem) {
                        $date = Carbon::now($useritem->user_timezone ?? 'UTC'); // Carbon::now('Europe/Moscow'), Carbon::now('Europe/Amsterdam') etc..
                        Log::info($useritem->user_timezone."=".$date);
                        if ($date->hour === 05) { 
                            Log::info("EventDayReminderEmailCommand : day time true");
                            dispatch(new EventReminderEmailJob($item, $useritem, $attendEvent, "day"));
                        }
                    }
                } else {
                    Log::info("EventDayReminderEmailCommand : user not found");
                }
            }
        }
    }
}
