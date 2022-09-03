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
                'Authorization' => "Bearer EwBoA8l6BAAUkj1NuJYtTVha+Mogk+HEiPbQo04AAYGSsHvGW7bxiilBlXmZIUB7Kgdc2OirkpKQidp2bgGIdHemwvPLNA4ARZBIu4jpfWa2obzx0G/F35f6NOqkMUHjN7cWK6HXGzSeXdy3lcDCirNC0iaAF8PfvzEsuBrBLVIrOa4qWrVXRemn2AnXRiIi7yNfhRklk9CmsMydzk8FuEQH5EQOb9Dkd9roz33X6IJSw02jq/gHvb7C7QYRbvSUopTY1IXoNJkwPDUYTiV+hBSQiCZ5dXBZancyT8tzvGggI/mV3On1Tt2PPtpuWS8LmcXl3JuIhWM2fwe7aoKK5fW+FcgHVAYxJ9SGyBEEtDjkO9Uezw+ny0h3perX1X0DZgAACLRnbyqT8P2HOAIFnYGRccHMbi5TCBmifxlIEwERS8QI4KX4dvH7OxS41r9NOqUYXw4nE1VZ0/thzcNXXwQOyO96H4iMbGzsD3eZH6ogQkwtthLD1LjO2Qy2/975+AavS9V5F0WzdN9HIHiHOi8ACdEMlC5Opk1h8olB9IhNh1EvK+5DQ/4NvBh/QpTeh9tOg3KbBRwoWGGgnow8pGxIXfZkNUFRSiN7UzK8m46MwFW3PDw8EKHdV6ycrKjCJin+kE4G7SdpJK2sEWaG/JX3Ph9yC8yS+YO8+Lvv5Q0/wEcJgDZx4+2fCt9c1fZYLxuiJvHPmLCkNQqQ9nW0gRARrA0SUSAd5jt142jWKk31006LVWcwzBd+ZFi8SygWFpxb7btxHQIQUlDPUq2qVt0OeWnjn1Sh+KnYviL3ylzfmb3WEvxmgOEuN7bEndyWHYd0cNwIV7Ywm8r6uKPn92Jydv19pSac7G9OwJKnvF7kGpaN8v7Tuuwjyr/iJArAPkj4g9YVwTL8q+M+CGoj8JRoYyB/yaucJs2A7/9936djuVmECWBeEsP/baiQaU2g7jV+WMiQDNJSfdp313/GMV7tpxJ4Fty7F9MBrc2NUR3vZu1brBL5ICMpJptyvMKusHUm60lW/kKBLrcax1q4ALZdMjtpnu6cPLLgcOU48HZFCs4EsFyKLU8HFfOV6DH9LEaatPQVofyZPh9FKbk4bCG2snbW9Fd7epCBZ0OmLBhqasvv9J5s1vDaYi+8Q8u21FRjqmAogQI=",
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