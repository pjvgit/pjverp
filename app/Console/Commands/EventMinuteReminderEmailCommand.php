<?php

namespace App\Console\Commands;

use App\EventRecurring;
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
        Log::info("Minute Event Reminder Email Command Started :". date('Y-m-d H:i:s'));
        $result = EventRecurring::whereJsonContains('event_reminders', ['reminder_type' => 'email'])
                    ->whereJsonContains('event_reminders', ['reminder_frequncy' => "minute"])
                    ->whereJsonContains('event_reminders', ['remind_at' => date("Y-m-d")])
                    ->whereJsonContains('event_reminders', ['reminded_at' => null])
                    ->whereJsonContains('event_reminders', ['dispatched_at' => null])
                    ->whereHas("event", function($query) {
                        $query->where("is_SOL", "no");
                    })
                    // ->whereId(59940)
                    ->with('event', 'event.case', 'event.eventLocation', 'event.case.caseStaffAll')
                    ->get();        
        if($result) {
            foreach($result as $key => $item) {
                Log::info("Event recurring id :". $item->id);
                $attendEvent = []; $newArray = [];
                $decodeReminders = encodeDecodeJson($item->event_reminders)->where('reminder_type' , 'email')->where('remind_at', date('Y-m-d'))->where('reminder_frequncy', "minute")->whereNull('dispatched_at')/* ->whereNull('reminded_at') */;
                foreach($decodeReminders as $rkey => $ritem) {
                    $users = [];
                    $response = $this->getEventLinkedUser($ritem, "email", $item->event, $item);
                    Log::info("event minute reminder users: ". $response["users"]);
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
                    if(count($users)) {
                        $eventStartTime = Carbon::parse($item->start_date.' '.$item->event->start_time);
                        Log::info("minute event start time: ". $eventStartTime);
                        $remindTime = Carbon::parse($eventStartTime)->subMinutes($ritem->reminer_number)->format('Y-m-d H:i');
                        $dispatchDate = Carbon::createFromFormat('Y-m-d H:i', $remindTime);
                        Log::info("minute event remind time: ". $dispatchDate);
                        Log::info("EventMinuteReminderEmailCommand : minute time true");
                        dispatch(new EventReminderEmailJob($item, $users, $attendEvent, 'minute'))->delay($dispatchDate);
                    }
                    // $ritem->dispatched_at = Carbon::now();
                    // $newArray[] = $ritem;
                }
                $decodeReminders = encodeDecodeJson($item->event_reminders);
                if($decodeReminders) {
                    foreach($decodeReminders as $ritem) {
                        if($ritem->reminder_type == 'email' && $ritem->remind_at == date('Y-m-d') && $ritem->reminder_frequncy == 'minute') {
                            $ritem->dispatched_at = Carbon::now();
                        }
                        $newArray[] = $ritem;
                    }
                }
                EventRecurring::whereId($item->id)->update(["event_reminders" => encodeDecodeJson($newArray, 'encode')]);
            }
            Log::info("Minute Event Reminder Email Command Ended :". date('Y-m-d H:i:s'));
        }
    }

    public function handle_old()
    {
        $result = CaseEventReminder::where("reminder_type", "email")
                    ->where("reminder_frequncy", "minute")/* ->where("event_id", "38439") */
                    ->whereDate("remind_at", Carbon::now())
                    ->whereNull("reminded_at")
                    ->whereHas("event", function($query) {
                        $query->where("is_SOL", "no");
                    })
                    // ->whereEventId(58119)
                    ->with('event', 'event.eventLinkedStaff', 'event.case', 'event.eventLocation', 'event.case.caseStaffAll', 'event.eventLinkedContact', 'event.eventLinkedLead')
                    ->get();
        if($result) {
            // Log::info("Minute Event Reminder Email Command Started :". date('Y-m-d H:i:s'));
            // Log::info("Minute Event Reminder total records :". count($result));
            foreach($result as $key => $item) {
                Log::info("Event id :". $item->id);
                $response = $this->getEventLinkedUser($item, "email");
                $users = $response["users"] ?? [];
                $attendEvent = $response["attendEvent"] ?? [];
                if(count($users)) {
                    // Log::info("user found:".$users);
                    $currentTime = Carbon::now()->format('Y-m-d H:i');
                    $date1 = Carbon::createFromFormat('Y-m-d H:i', $currentTime);
                    // Log::info("carbon now:". $date1);
                    $date2 = Carbon::createFromFormat('Y-m-d H:i', Carbon::parse($item->remind_at)->format('Y-m-d H:i'));
                    // Log::info("remind at:". $date2);
                    if($date1->eq($date2)) {
                        Log::info("EventMinuteReminderEmailCommand : minute time true");
                        dispatch(new EventReminderEmailJob($item, $users, $attendEvent));
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
// sudo php artisan eventminute:reminderemail