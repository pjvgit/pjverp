<?php

namespace App\Jobs;

use App\EventRecurring;
use App\Mail\EventReminderMail;
use App\Mail\LeadEventInvitationMail;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeadEventInvitationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $eventRecurring, $event, $leadUser, $eventAction;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($eventRecurring, $event, $leadUser, $eventAction)
    {
        $this->eventRecurring = $eventRecurring;
        $this->event = $event;
        $this->leadUser = $leadUser;
        $this->eventAction = $eventAction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        Log::info("Lead Event invitation Email Job Started :". date('Y-m-d H:i:s'));
        $event = $this->event;
        $firmDetail = firmDetail($event->firm_id);
        if(!empty($this->leadUser)) {
            $attendEventUser = encodeDecodeJson($this->eventRecurring->event_linked_contact_lead)->where('lead_id', $this->leadUser->id)->first();
            $attendEvent = (isset($attendEventUser)) ? ucfirst($attendEventUser->attending) : "";
            Mail::to($this->leadUser->email)->send((new LeadEventInvitationMail($event, $firmDetail, $this->leadUser, $attendEvent, $this->eventRecurring, $this->eventAction)));
        }
        Log::info("Lead Event invitation Email Job Endned :". date('Y-m-d H:i:s'));
    }
}
