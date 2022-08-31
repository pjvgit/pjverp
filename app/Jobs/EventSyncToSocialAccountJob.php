<?php

namespace App\Jobs;

use App\Services\GoogleService;
use App\UserSyncSocialAccount;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EventSyncToSocialAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $authUser, $event, $eventRecurring;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($authUser, $event, $eventRecurring)
    {
        $this->authUser = $authUser;
        $this->event = $event;
        $this->eventRecurring = $eventRecurring;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        $google = new GoogleService;
        $googleAccount = UserSyncSocialAccount::where('user_id', $this->authUser->id)->whereNotNull('access_token')->first();
        $google->connectUsing($googleAccount);
        $service = $google->service('Calendar');
        if(empty($googleAccount->calendar_id)) {
            $calendar = new \Google\Service\Calendar\Calendar();
            $calendar->setSummary('LegalCase');
            $calendar->setTimeZone($this->authUser->user_timezone);
            $createdCalendar = $service->calendars->insert($calendar);
            $googleAccount->update([
                'calendar_id' => $createdCalendar->getId(), 
                'calendar_name' => $calendar->getSummary(), 
                'calendar_timezone' => $this->authUser->user_timezone
            ]);
        }
        $googleAccount->refresh();
        $event = $this->event;
        $eventRecurring = $this->eventRecurring;
        $timezone = $this->authUser->user_timezone;
        $startDate = ($event->is_full_day == 'no') ? convertUTCToUserTime($eventRecurring->start_date.' '.$event->start_time, $timezone) : $eventRecurring->user_start_date->format('Y-m-d');
        $endDate = ($event->is_full_day == 'no') ? convertUTCToUserTime($eventRecurring->end_date.' '.$event->end_time, $timezone) : $eventRecurring->user_end_date->format('Y-m-d');

        // Attendees array
        $attendees = [];
        $linkedStaff = encodeDecodeJson($eventRecurring->event_linked_staff);
        if(count($linkedStaff)) {
            foreach($linkedStaff as $key => $item) {
                $user = getUserDetail($item->user_id);
                if($user->email) {
                    $attendees[] = [
                        'email' => $user->email ?? '',
                        'displayName' => $user->full_name,
                    ];
                }
            }
        }
        $linkedContact = encodeDecodeJson($eventRecurring->event_linked_contact_lead);
        if(count($linkedContact)) {
            foreach($linkedContact as $key => $item) {
                $user = getUserDetail(($item->user_type == 'lead') ? $item->lead_id : $item->contact_id);
                if($user->email) {
                    $attendees[] = [
                        'email' => $user->email ?? '',
                        'displayName' => $user->full_name,
                    ];
                }
            }
        }

        // For reminders 
        $isAuthUserLinked = $linkedStaff->where('user_id', $this->authUser->id)->first();
        $overrides = [];
        if($isAuthUserLinked) {
            $decodeReminder = encodeDecodeJson($eventRecurring->event_reminders)->where('created_by', $this->authUser->id);
            foreach($decodeReminder as $ritem) {
                if($ritem->reminder_frequncy == 'week') {
                    $days = $ritem->reminer_number * 7;
                    $hours = $days * 24;
                    $minutes = $hours * 60;
                } else if($ritem->reminder_frequncy == 'day') {
                    $hours = $ritem->reminer_number * 24;
                    $minutes = $hours * 60;
                } else if($ritem->reminder_frequncy == 'hour') { 
                    $hours = $ritem->reminer_number;
                    $minutes = $hours * 60;
                } else {
                    $minutes = $ritem->reminer_number;
                }
                if($minutes <= 40320) {
                    $overrides[] = [ 'method' => $ritem->reminder_type, 'minutes' => $minutes];
                }
            }
        }

        $event = new \Google\Service\Calendar\Event(array(
            'summary' => $event->event_title ?? "<No Title>",
            'location' => ($event->event_location_id) ? $event->eventLocation->full_address : '',
            'description' => $event->event_description,
            'start' => array(
              'dateTime' => Carbon::parse("$startDate $timezone")->format('Y-m-d\TH:i:sP'),
              'timeZone' => $timezone,
            ),
            'end' => array(
              'dateTime' => Carbon::parse("$endDate $timezone")->format('Y-m-d\TH:i:sP'),
              'timeZone' => $timezone,
            ),
            /* 'recurrence' => array(
              'RRULE:FREQ=DAILY;COUNT=2'
            ), */
            'attendees' => $attendees,
            'reminders' => array(
              'useDefault' => FALSE,
              'overrides' => $overrides,
            ),
        ));
        // $cal = new \Google\Service\Calendar($google);
        $calendarId = $googleAccount->calendar_id;
        $event = $service->events->insert($calendarId, $event);
        Log::info('Event created: %s\n'. $event->htmlLink);
    }
}