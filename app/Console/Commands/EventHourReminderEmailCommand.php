<?php

namespace App\Console\Commands;

use App\EventRecurring;
use App\Jobs\EventReminderEmailJob;
use App\Traits\EventReminderTrait;
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
        $result = EventRecurring::whereJsonContains('event_reminders', ['reminder_type' => 'email'])
                    ->whereJsonContains('event_reminders', ['reminder_frequncy' => "hour"])
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
                $users = $attendEvent = []; $newArray = [];
                $decodeReminders = encodeDecodeJson($item->event_reminders)->where('reminder_type' , 'email')->where('remind_at', date('Y-m-d'))->where('reminder_frequncy', "hour");
                foreach($decodeReminders as $rkey => $ritem) {
                    $response = $this->getEventLinkedUser($ritem, "email", $item->event, $item);
                    Log::info("event hour reminder users: ". $response["users"]);
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
                        Log::info("hour event start time: ". $eventStartTime);
                        $remindTime = Carbon::parse($eventStartTime)->subHours($ritem->reminer_number)->format('Y-m-d H:i');
                        $dispatchDate = Carbon::createFromFormat('Y-m-d H:i', $remindTime);
                        Log::info("hour event remind time: ". $dispatchDate);
                        Log::info("EventHourReminderEmailCommand : hour time true");
                        dispatch(new EventReminderEmailJob($item, $users, $attendEvent))->delay($dispatchDate);
                    }
                    $ritem->dispatched_at = Carbon::now();
                    $newArray[] = $ritem;
                }
                $decodeReminders = encodeDecodeJson($item->event_reminders);
                if($decodeReminders) {
                    $newArray = [];
                    foreach($decodeReminders as $ritem) {
                        if($ritem->reminder_type == 'email' && $ritem->remind_at == date('Y-m-d') && $ritem->reminder_frequncy == 'hour') {
                        } else {
                            $newArray[] = $ritem;
                        }
                    }
                }
                EventRecurring::whereId($item->id)->update(["event_reminders" => encodeDecodeJson($newArray, 'encode')]);
            }
        } 
    }

    public function handle_old()
    {
        $result = CaseEventReminder::where("reminder_type", "email")
                    ->where("reminder_frequncy", "hour")/* ->where("event_id", "38439") */
                    ->whereDate("remind_at", Carbon::now())
                    ->whereNull("reminded_at")
                    ->whereHas("event", function($query) {
                        $query->where("is_SOL", "no");
                    })
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
                        dispatch(new EventReminderEmailJob($item, $users, $attendEvent));
                    } else {
                        Log::info("EventHourReminderEmailCommand : time not match");
                    }
                }
            }
        } 
    }
}
//sudo php artisan eventhour:reminderemail