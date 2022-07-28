<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\CaseEvent,App\Firm,App\EmailTemplate,App\CaseEventComment;
use App\Event;
use App\EventRecurring;
use App\Mail\EventCommentMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EventCommentEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $event_id,$firm_id,$eventComment,$fromUser, $event_recurring_id;

    /**
     * Create a new job instance.
     *
     * @param  Podcast  $podcast
     * @return void
     */
    public function __construct($event_id, $firm_id, $eventComment, $fromUser, $event_recurring_id)
    {
        $this->event_id = $event_id;
        $this->firm_id = $firm_id;
        $this->eventComment = $eventComment;   
        $this->fromUser = $fromUser;
        $this->event_recurring_id = $event_recurring_id;
    }

    /**
     * Execute the job.
     *
     * @param  AudioProcessor  $processor
     * @return void
     */
    public function handle()
    {
        Log::info("comment handle");
        $eventComment = collect($this->eventComment);
        $commentAddedByUser = getUserDetail($eventComment['created_by']);
        $eventData = Event::whereId($this->event_id)->first();
        $firmData = Firm::whereId($this->firm_id)->first();
        $eventRecurring = EventRecurring::whereId($this->event_recurring_id)->first();
        if($eventData && $eventRecurring) {
            if($eventRecurring->event_linked_staff) {
                $linkedStaff = encodeDecodeJson($eventRecurring->event_linked_staff)->where("user_id", "!=", $eventComment['created_by']);
                Log::info("event linked staff");
                $getTemplateData = EmailTemplate::find(26);
                foreach($linkedStaff as $key => $item) {
                    $user = getUserDetail($item->user_id);
                    Log::info("event linked staff > email > " . $user->email);
                    Mail::to($user->email)->send((new EventCommentMail($eventData, $firmData, $user, $getTemplateData, $commentAddedByUser, 'staff', $eventRecurring)));        
                }
            }
            $getTemplateData = EmailTemplate::find(25);
            if($eventRecurring->event_linked_contact_lead) {
                $linkedContact = encodeDecodeJson($eventRecurring->event_linked_contact_lead)->where("contact_id", "!=", $eventComment['created_by'])->where("user_type", "contact");
                Log::info("event contact staff");
                if(count($linkedContact)) {
                    foreach($linkedContact as $key => $item) {
                        $user = getUserDetail($item->contact_id);
                        Log::info("event contact > email > " . $user->email);
                        Mail::to($user->email)->send((new EventCommentMail($eventData, $firmData, $user, $getTemplateData, $commentAddedByUser, 'client', $eventRecurring)));        
                    }
                }
                Log::info("event linked lead");
                $linkedLead = encodeDecodeJson($eventRecurring->event_linked_contact_lead)->where("lead_id", "!=", $eventComment['created_by'])->where("user_type", "lead");
                if($linkedLead) {
                    foreach($linkedLead as $key => $item) {
                        $user = getUserDetail($user->lead_id);
                        Log::info("event contact staff > email > " . $user->email);
                        Mail::to($user->email)->send((new EventCommentMail($eventData, $firmData, $user, $getTemplateData, $commentAddedByUser, 'client', $eventRecurring)));        
                    }
                }
            }
        }
    }
}