<?php

namespace App\Jobs;

use App\CaseEventReminder;
use App\EventRecurring;
use App\Mail\EventReminderMail;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EventReminderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $eventRecurring, $user, $attendEventUser, $reminderFrequency;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($eventRecurring, $user, $attendEventUser, $reminderFrequency = null)
    {
        $this->eventRecurring = $eventRecurring;
        $this->user = $user;
        $this->attendEventUser = $attendEventUser;
        $this->reminderFrequency = $reminderFrequency;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        Log::info("Event Reminder Email Job Started :". date('Y-m-d H:i:s'));
        $event = $this->eventRecurring->event;
        $firmDetail = firmDetail($event->firm_id);
        if(!empty($this->user)) {
            if($this->reminderFrequency == "day") {
                // Log::info("user not empty".$this->user);
                $attendEvent = (isset($this->attendEventUser) && array_key_exists($this->user->id, $this->attendEventUser)) ? ucfirst($this->attendEventUser[$this->user->id]) : "";
                Mail::to($this->user->email)->send((new EventReminderMail($event, $firmDetail, $this->user, $attendEvent, $this->eventRecurring)));
            } else {
                // Log::info("job else".$this->user);
                foreach($this->user as $key => $item) {
                    $attendEvent = (isset($this->attendEventUser) && array_key_exists($item->id, $this->attendEventUser)) ? ucfirst($this->attendEventUser[$item->id]) : "";
                    Mail::to($item->email)->send((new EventReminderMail($event, $firmDetail, $item, $attendEvent, $this->eventRecurring)));
                }
            }
            $decodeReminders = encodeDecodeJson($this->eventRecurring->event_reminders)->where('reminder_type', 'email')->where('remind_at', date('Y-m-d'));
            if($decodeReminders) {
                $newArray = [];
                foreach($decodeReminders as $ritem) {
                    if($ritem->reminder_type == 'email' && $ritem->remind_at == date('Y-m-d')) {
                        $ritem->reminded_at = Carbon::now();
                    }
                    $newArray[] = $ritem;
                }
            }
            EventRecurring::whereId($this->eventRecurring->id)->update(["event_reminders" => encodeDecodeJson($newArray, 'encode')]);
        }
        Log::info("Event Reminder Email Job Endned :". date('Y-m-d H:i:s'));
    }
}
