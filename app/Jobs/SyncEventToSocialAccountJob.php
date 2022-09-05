<?php

namespace App\Jobs;

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

class SyncEventToSocialAccountJob implements ShouldQueue
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
        $syncAccount = UserSyncSocialAccount::where('user_id', $this->authUser->id)->whereNotNull('access_token')->first();

        if($syncAccount && $syncAccount->social_type == 'google') {
            $this->addEventToGoogleCalendar($syncAccount, $this->event, $this->eventRecurring, $this->authUser);
        } else if($syncAccount && $syncAccount->social_type == 'outlook') {
            $this->addEventToOutlookCalendar($syncAccount, $this->event, $this->eventRecurring, $this->authUser);
        } else {

        }
    }

    /**
     * Add event to google calendar
     */
    public function addEventToGoogleCalendar($syncAccount, $event, $eventRecurring, $authUser)
    {
        $google = new GoogleService;
        $google->connectUsing($syncAccount);
        $service = $google->service('Calendar');
        $timezone = $authUser->user_timezone;

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
                $rRule .= 'INTERVAL='.$event->event_interval_month;
                
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
        $isAuthUserLinked = $linkedStaff->where('user_id', $authUser->id)->first();
        $overrides = [];
        if($isAuthUserLinked) {
            $decodeReminder = encodeDecodeJson($eventRecurring->event_reminders)->where('created_by', $authUser->id);
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
                if($minutes <= 40320 && count($overrides) <= 5) {
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

        $calendarId = $syncAccount->calendar_id;
        $socialEvent = $service->events->insert($calendarId, $newEvent);
        Log::info('Google Event created: %s\n'. $socialEvent->htmlLink);

        EventSyncToUserSocialAccount::create([
            'user_id' => $authUser->id,
            'user_sync_sa_id' => $syncAccount->id,
            'social_type' => $syncAccount->social_type,
            'event_id' => $event->id,
            'event_recurring_id' => $eventRecurring->id,
            'social_event_id' => $socialEvent->id,
            'social_event_url' => $socialEvent->htmlLink,
            'created_by' => $authUser->id,
        ]);
    }

    /**
     * Add event to outlook calendar
     */
    public function addEventToOutlookCalendar($syncAccount, $event, $eventRecurring, $authUser)
    {
        $timezone = $authUser->user_timezone;

        if($event->is_full_day == 'no') {
            $startDate = convertUTCToUserTime($event->start_date.' '.$event->start_time, $timezone);
            $start = [
                'dateTime' => Carbon::parse("$startDate $timezone")->format('Y-m-d\TH:i:s'),
                'timeZone' => $timezone,
            ];
            $endDate = convertUTCToUserTime($event->end_date.' '.$event->end_time, $timezone);
            $end = [
                'dateTime' => Carbon::parse("$endDate $timezone")->format('Y-m-d\TH:i:s'),
                'timeZone' => $timezone,
            ];
            $isAllDay = false;
        } else {
            $start = [
                'dateTime' => $eventRecurring->user_start_date->format('Y-m-d\T').'00:00:00',
                'timeZone' => $timezone,
            ];
            $end = [
                'dateTime' => Carbon::parse($eventRecurring->user_end_date->format('Y-m-d'))->addDay()->format('Y-m-d\T').'00:00:00',
                'timeZone' => $timezone,
            ];
            $isAllDay = true;
        }

        // Attendees array
        $attendees = [];
        $linkedStaff = encodeDecodeJson($eventRecurring->event_linked_staff);
        if(count($linkedStaff)) {
            foreach($linkedStaff as $key => $item) {
                $user = getUserDetail($item->user_id);
                if($user->email) {
                    $attendees[] = [
                        "emailAddress"=> [
                            "address"=>$user->email ?? '',
                            "name"=> $user->full_name
                        ],
                        "type"=> "required"
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
                        "emailAddress"=> [
                            "address"=>$user->email ?? '',
                            "name"=> $user->full_name
                        ],
                        "type"=> "required"
                    ];
                }
            }
        }

        $eventJson = [
            "subject"=> $event->event_title ?? "<No Title>",
            "isAllDay"=> $isAllDay,
            "start"=> $start,
            "end"=> $end,
            "attendees"=> $attendees,
            "allowNewTimeProposals"=> false,
            "body"=> [
                "contentType"=> "text",
                "content"=> $event->event_description ?? '',
            ],
        ];

        // For event location
        $location = [];
        if($event->event_location_id && $event->eventLocation) {
            $locationObj = $event->eventLocation;
            $location = [
                "displayName" => $locationObj->location_name,
                'address' => [
                    'street'          => $locationObj->address1.', '.$locationObj->address2,
                    'city'            => $locationObj->city,
                    'state'           => $locationObj->state,
                    'countryOrRegion' => @$locationObj->countryDetail->name ?? '',
                    'postalCode'      => $locationObj->postal_code,
                ],
            ];
            $eventJson['location'] = $location;
        }

        // For recurring event
        $recurrence = [];
        if($event->is_recurring == 'yes') {
            if($event->event_recurring_type == 'DAILY') {
                $pattern = [
                    "type" => "daily",
                    "interval" => $event->event_interval_day                    
                ];
            } else if($event->event_recurring_type == 'EVERY_BUSINESS_DAY') {
                $pattern = [
                    "type" => "weekly",
                    "interval" => 1,
                    "daysOfWeek" => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    "firstDayOfWeek" => 'monday'                  
                ];            
            } else if($event->event_recurring_type == 'CUSTOM') {
                $pattern = [
                    "type" => "weekly",
                    "interval" => $event->event_interval_week,
                    "daysOfWeek" => $event->custom_event_weekdays,
                    "firstDayOfWeek" => 'monday'                  
                ];
            } else if($event->event_recurring_type == 'WEEKLY') {
                $pattern = [
                    "type" => "weekly",
                    "interval" => $event->event_interval_week,
                    "daysOfWeek" => [$event->user_start_date->format('l')],
                    "firstDayOfWeek" => 'monday'                  
                ];
            } else if($event->event_recurring_type == 'MONTHLY') {
                if($event->monthly_frequency == 'MONTHLY_ON_DAY') {
                    $pattern = [
                        "type" => 'absoluteMonthly',
                        "interval" => $event->event_interval_month,
                        "dayOfMonth" => $event->user_start_date->format('d'),
                    ];
                } else {
                    $pattern = [
                        "type" => 'relativeMonthly',
                        "interval" => $event->event_interval_month,
                        "daysOfWeek" => $event->user_start_date->format('l'),
                    ];
                }
            }
            $eventJson['Recurrence'] = [
                "pattern" => $pattern,
                "Range" => [
                    "Type" => "EndDate",
                    "EndDate" => convertUTCToUserDate($event->recurring_event_end_date, $timezone)->format('Y-m-d'),
                    "StartDate" => $event->user_start_date->format('Y-m-d'),
                    "RecurrenceTimeZone" => $timezone
                ]
            ];
        }

        $guzzle = new \GuzzleHttp\Client(['base_uri' => 'https://graph.microsoft.com']);
        $newresponse = $guzzle->request('POST', '/v1.0/me/calendars/'.$syncAccount->calendar_id.'/events', [
            'headers' => [
                'Authorization' => "Bearer ".$syncAccount->access_token,
                'content-type' => "application/json",
            ],
            'json' => $eventJson
        ])->getBody()->getContents();
        $socialEvent = json_decode($newresponse);
        Log::info('Outlook Event created: %s\n'. $socialEvent->webLink);

        EventSyncToUserSocialAccount::create([
            'user_id' => $authUser->id,
            'user_sync_sa_id' => $syncAccount->id,
            'social_type' => $syncAccount->social_type,
            'event_id' => $event->id,
            'event_recurring_id' => $eventRecurring->id,
            'social_event_id' => $socialEvent->id,
            'social_event_url' => $socialEvent->webLink,
            'created_by' => $authUser->id,
        ]);
    }
}