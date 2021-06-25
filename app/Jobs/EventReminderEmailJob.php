<?php

namespace App\Jobs;

use App\Mail\EventReminderEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EventReminderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $event, $user, $attendEventUser;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($event, $user, $attendEventUser)
    {
        $this->event = $event;
        $this->user = $user;
        $this->attendEventUser = $attendEventUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $firmDetail = firmDetail($this->event->case->firm_id);
        if(!empty($this->user)) {
            foreach($this->user as $key => $item) {
                $attendEvent = (isset($this->attendEventUser) && array_key_exists($item->id, $this->attendEventUser)) ? ucfirst($this->attendEventUser[$item->id]) : "";
                // \Log::info("Attend event:". $attendEvent);
                Mail::to($item->email)->send((new EventReminderEmail($this->event, $firmDetail, $item, $attendEvent)));
                /* Mail::send('emails.event_reminder_email', ['event' => $this->event, 'firm' => $firmDetail, 'user' => $item], function ($m) use($item){
                    $m->to($item->email, $item->full_name)->subject("Reminder: Upcoming Event");
                }); */
            }
        }
    }
}
