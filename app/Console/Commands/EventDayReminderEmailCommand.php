<?php

namespace App\Console\Commands;

use App\EventRecurring;
use App\Jobs\EventReminderEmailJob;
use App\Traits\EventReminderTrait;
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
                    ->where(function($query) {
                        $query->whereJsonContains('event_reminders', ['reminder_frequncy' => 'day'])
                            ->orWhereJsonContains('event_reminders', ['reminder_frequncy' => 'week']);
                    })
                    ->whereJsonContains('event_reminders', ['remind_at' => date('Y-m-d')])
                    ->whereJsonContains('event_reminders', ['reminded_at' => null])
                    ->whereJsonContains('event_reminders', ['dispatched_at' => null])
                    ->whereHas("event", function($query) {
                        $query->where("is_SOL", "no");
                    })
                    // ->whereId(59939)
                    ->with('event', 'event.case', 'event.eventLocation', 'event.case.caseStaffAll')
                    ->get();   
        Log::info("day reminder total found: ". count($result));
        if($result) {
            foreach($result as $key => $item) {
                Log::info("Event recurring id :". $item->id);
                $users = $attendEvent = [];
                $decodeReminders = encodeDecodeJson($item->event_reminders)->where('reminder_type', 'email')->where('remind_at', date('Y-m-d'))->whereIn('reminder_frequncy', ['day', 'week'])->whereNull('dispatched_at')->whereNull('reminded_at');
                foreach($decodeReminders as $rkey => $ritem){
                    $response = $this->getEventLinkedUser($ritem, "email", $item->event, $item);
                    Log::info("event day reminder users: ". $response["users"]);
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
                if(count($users)) {
                    foreach($users as $userkey => $useritem) {
                        Log::info($useritem);
                        Log::info("user_timezone > " . $useritem->user_timezone);
                        $remindDate = Carbon::now($useritem->user_timezone ?? 'UTC'); // Carbon::now('Europe/Moscow'), Carbon::now('Europe/Amsterdam') etc..
                        Log::info("event day remind date:" . $remindDate);
                        $timestamp = $remindDate->format('Y-m-d').' 05:00';
                        Log::info("day remind time stamp: ". $timestamp);
                        $dispatchDate = Carbon::createFromFormat('Y-m-d H:i', $timestamp, $useritem->user_timezone ?? 'UTC');
                        $dispatchDate->setTimezone('UTC');
                        Log::info("day dispatchDate > ". $dispatchDate);
                        dispatch(new EventReminderEmailJob($item, $useritem, $attendEvent, "day"))->delay($dispatchDate);
                    }
                    
                    $decodeReminders = encodeDecodeJson($item->event_reminders);
                    if($decodeReminders) {
                        $newArray = [];
                        foreach($decodeReminders as $ritem) {
                            if($ritem->reminder_type == 'email' && $ritem->remind_at == date('Y-m-d') && in_array($ritem->reminder_frequncy, ['day','week'])) {
                                $ritem->dispatched_at = Carbon::now();
                            }
                            $newArray[] = $ritem;
                        }
                        EventRecurring::whereId($item->id)->update(["event_reminders" => encodeDecodeJson($newArray, 'encode')]);
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
//sudo php artisan eventday:reminderemail