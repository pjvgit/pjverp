<?php

namespace App\Jobs;

use App\CaseEventReminder;
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
    protected $eventReminder, $user, $attendEventUser, $reminderFrequency;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($eventReminder, $user, $attendEventUser, $reminderFrequency = null)
    {
        $this->eventReminder = $eventReminder;
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
        $firmDetail = firmDetail($this->eventReminder->event->case->firm_id);
        if(!empty($this->user)) {
            Log::info("user not empty".$this->user);
            if($this->reminderFrequency == "day") {
                $attendEvent = (isset($this->attendEventUser) && array_key_exists($this->user->id, $this->attendEventUser)) ? ucfirst($this->attendEventUser[$this->user->id]) : "";
                Mail::to($this->user->email)->send((new EventReminderMail($this->eventReminder->event, $firmDetail, $this->user, $attendEvent)));
            } else {
                Log::info("job else".$this->user);
                foreach($this->user as $key => $item) {
                    $attendEvent = (isset($this->attendEventUser) && array_key_exists($item->id, $this->attendEventUser)) ? ucfirst($this->attendEventUser[$item->id]) : "";
                    Mail::to($item->email)->send((new EventReminderMail($this->eventReminder->event, $firmDetail, $item, $attendEvent)));
                }
            }
            CaseEventReminder::where("id", $this->eventReminder->id)->update(["reminded_at" => Carbon::now()]);
        }
        Log::info("Event Reminder Email Job Endned :". date('Y-m-d H:i:s'));
    }
}
