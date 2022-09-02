<?php

namespace App\Jobs;

use App\Event;
use App\EventRecurring;
use App\EventSyncToUserSocialAccount;
use App\Services\GoogleService;
use App\UserSyncSocialAccount;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAllEventToSocialAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $authUser;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($authUser)
    {
        $this->authUser = $authUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        $googleAccount = UserSyncSocialAccount::where('user_id', $this->authUser->id)->whereNotNull('access_token')->first();

        $google = new GoogleService;
        $google->connectUsing($googleAccount);
        $service = $google->service('Calendar');

        $dateS = Carbon::now()->startOfMonth()->subMonth(2);
        $dateE = Carbon::now();
        $eventIds = EventRecurring::whereDate("start_date", ">=", $dateS)->whereDate("start_date", "<=", $dateE)
                    ->whereJsonContains('event_linked_staff', ["user_id" => (string)$this->authUser->id])->pluck('event_id')->toArray();
        $allEvents = Event::whereIn('id', $eventIds)->whereDate('start_date', ">=", $dateS)->whereDate("start_date", "<=", $dateE)->with('eventLocation')->get();
        foreach($allEvents as $item) {
            $event = $item;
            $checkIfExist = EventSyncToUserSocialAccount::where('user_id', $this->authUser->id)->where('event_id', $event->id)->first();
            if(empty($checkIfExist)) {
                $eventRecurring = EventRecurring::where('event_id', $event->id)->first();
                $timezone = $this->authUser->user_timezone;
                if($event->is_full_day == 'no') {
                    $startDate = convertUTCToUserTime($event->start_date.' '.$event->start_time, $timezone);
                    $start = [
                        'dateTime' => Carbon::parse("$startDate $timezone")->format('Y-m-d\TH:i:sP'),
                        'timeZone' => $timezone,
                    ];
                    $endDate = convertUTCToUserTime($event->end_date.' '.$event->end_time, $timezone);
                    $end = [
                        'dateTime' => Carbon::parse("$endDate $timezone")->format('Y-m-d\TH:i:sP'),
                        'timeZone' => $timezone,
                    ];
                } else {
                    $start = [
                        'date' => $eventRecurring->user_start_date->format('Y-m-d'),
                        'timeZone' => $timezone,
                    ];
                    $end = [
                        'date' => $eventRecurring->user_end_date->format('Y-m-d'),
                        'timeZone' => $timezone,
                    ];
                }
                // Recurrence array
                $recurrence = [];
                if($event->is_recurring == 'yes') {
                    if($event->event_recurring_type == 'DAILY') {
                        $rRule = 'RRULE:FREQ=DAILY;';
                        if($event->is_no_end_date == 'no' && $event->end_on) {
                            $rRule .= 'UNTIL='.convertUTCToUserDate($event->end_on, $timezone)->format('Ymd').';';
                        }
                        $rRule .= 'INTERVAL='.$event->event_interval_day;
                    } else if($event->event_recurring_type == 'EVERY_BUSINESS_DAY') {
                        $rRule = 'RRULE:FREQ=WEEKLY;';
                        if($event->is_no_end_date == 'no' && $event->end_on) {
                            $rRule .= 'UNTIL='.convertUTCToUserDate($event->end_on, $timezone)->format('Ymd').';';
                        }
                        $rRule .= 'BYDAY='.'MO,TU,WE,TH,FR';
                                        
                    } else if($event->event_recurring_type == 'CUSTOM') {
                        $rRule = 'RRULE:FREQ=WEEKLY;';
                        if($event->is_no_end_date == 'no' && $event->end_on) {
                            $rRule .= 'UNTIL='.convertUTCToUserDate($event->end_on, $timezone)->format('Ymd').';';
                        }
                        $weekDays = '';
                        foreach($event->custom_event_weekdays as $wday) {
                            $weekDays .= strtoupper(substr($wday, 0, 2)).',';
                        }
                        $rRule .= 'BYDAY='.$weekDays.';';
                        $rRule .= 'INTERVAL='.$event->event_interval_week;
                        
                    } else if($event->event_recurring_type == 'WEEKLY') {
                        $rRule = 'RRULE:FREQ=WEEKLY;';
                        if($event->is_no_end_date == 'no' && $event->end_on) {
                            $rRule .= 'UNTIL='.convertUTCToUserDate($event->end_on, $timezone)->format('Ymd').';';
                        }
                        $rRule .= 'INTERVAL='.$event->event_interval_week;
                        
                    } else if($event->event_recurring_type == 'MONTHLY') {
                        $rRule = 'RRULE:FREQ=MONTHLY;';
                        if($event->is_no_end_date == 'no' && $event->end_on) {
                            $rRule .= 'UNTIL='.convertUTCToUserDate($event->end_on, $timezone)->format('Ymd').';';
                        }
                        $rRule .= 'INTERVAL='.$event->event_interval_day;
                        
                    }
                    $recurrence = array($rRule);
                }

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
                        if($minutes <= 40320 && count($overrides) < 5) {
                            $overrides[] = [ 'method' => $ritem->reminder_type, 'minutes' => $minutes];
                        }
                    }
                }

                $newEvent = new \Google\Service\Calendar\Event(array(
                    'summary' => $event->event_title ?? "<No Title>",
                    'location' => ($event->event_location_id && $event->eventLocation) ? $event->eventLocation->full_address : '',
                    'description' => $event->event_description,
                    'start' => $start,
                    'end' => $end,
                    'recurrence' => $recurrence,
                    'attendees' => $attendees,
                    'reminders' => array(
                    'useDefault' => FALSE,
                    'overrides' => $overrides,
                    ),
                ));
                $calendarId = $googleAccount->calendar_id;
                $socialEvent = $service->events->insert($calendarId, $newEvent);
                Log::info('all Event created: '. $socialEvent->htmlLink);

                EventSyncToUserSocialAccount::create([
                    'user_id' => $this->authUser->id,
                    'user_sync_sa_id' => $googleAccount->id,
                    'event_id' => $event->id,
                    'event_recurring_id' => $eventRecurring->id,
                    'social_event_id' => $socialEvent->id,
                    'social_event_url' => $socialEvent->htmlLink,
                    'created_by' => $this->authUser->id,
                ]);
            }
        }
    }
}