<?php
 
namespace App\Traits;

use App\Event;
use App\EventRecurring;
use App\Http\Controllers\CommonController;
use App\Jobs\SyncEventToSocialAccountJob;
use App\Jobs\LeadEventInvitationEmailJob;
use App\User;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Environment\Console;

trait EventTrait {
    /**
     * Get event reminders json
     */
    public function getEventReminderJson($caseEvent, $request, $oldDecodeReminders = null)
    {
        $eventReminders = [];
        $authUserId = auth()->id();
        $lastReminder = (isset($oldDecodeReminders)) ? $oldDecodeReminders->last() : null;
        $reminderNo = (isset($lastReminder)) ? $lastReminder->reminder_id : 0;
        if($request->reminder_user_type && count($request['reminder_user_type']) > 1) {
            for($i=0; $i < count($request['reminder_user_type'])-1; $i++) {
                $isExist = ($oldDecodeReminders) ? $oldDecodeReminders->where('reminder_id', @$request['reminder_id'][$i])->where('created_by', $authUserId)->first() : '';
                if($isExist) {
                    Log::info("updated start date: ". $request->updated_start_date);
                    if($isExist->reminder_type != $request['reminder_type'][$i] || $isExist->reminer_number != $request['reminder_number'][$i] || $isExist->reminder_frequncy != $request['reminder_time_unit'][$i] || $isExist->reminder_user_type != $request['reminder_user_type'][$i] || isset($request->updated_start_date) || isset($request->updated_start_time)) {
                        $isExist->reminder_type = $request['reminder_type'][$i];
                        $isExist->reminer_number = $request['reminder_number'][$i];
                        $isExist->reminder_frequncy = $request['reminder_time_unit'][$i];
                        $isExist->reminder_user_type = $request['reminder_user_type'][$i];
                        $isExist->remind_at = $this->getRemindAtAttribute($request, $request['reminder_time_unit'][$i], $request['reminder_number'][$i]);
                        $isExist->popup_remind_time = $this->getRemindAtAttribute($request, $request['reminder_time_unit'][$i], $request['reminder_number'][$i], 'time');
                        $isExist->is_dismiss = 'no';
                        $isExist->snooze_remind_at = null;
                        $isExist->dispatched_at = null;
                        $isExist->reminded_at = null;
                    }
                    $eventReminders[] = $isExist;
                } else {
                    $reminderNo++;
                    $eventReminders[] = [
                        'reminder_id' => $reminderNo,
                        'event_id' => $caseEvent->id,
                        'user_id' => $authUserId,
                        'reminder_type' => $request['reminder_type'][$i],
                        'reminer_number' => $request['reminder_number'][$i],
                        'reminder_frequncy' => $request['reminder_time_unit'][$i],
                        'reminder_user_type' => $request['reminder_user_type'][$i],
                        'created_by' => $authUserId,
                        'remind_at' => $this->getRemindAtAttribute($request, $request['reminder_time_unit'][$i], $request['reminder_number'][$i]),
                        'popup_remind_time' => $this->getRemindAtAttribute($request, $request['reminder_time_unit'][$i], $request['reminder_number'][$i], 'time'),
                        'snooze_time' => null,
                        'snooze_type' => null,
                        'snoozed_at' => null,
                        'snooze_remind_at' => null,
                        'is_dismiss' => 'no',
                        'reminded_at' => null,
                        'dispatched_at' => null,
                    ];
                }
            }
        }
        return $eventReminders;
    }

    /**
     * Get event linked/non-linked staff json
     */
    public function getEventLinkedStaffJson($caseEvent, $request)
    {
        $eventLinkedStaff = [];
        $authUserId = auth()->id();
        if(isset($request['linked_staff_checked_share']) && count($request['linked_staff_checked_share'])) {
            $alreadyAdded = [];
            for($i=0; $i < count($request['linked_staff_checked_share']); $i++) {
                if(!in_array($request['linked_staff_checked_share'][$i],$alreadyAdded)) {
                    $eventLinkedStaff[] = [
                        'event_id' => $caseEvent->id,
                        'user_id' => $request['linked_staff_checked_share'][$i],
                        'is_linked' => 'yes',
                        'attending' => (isset($request['linked_staff_checked_attend']) && in_array($request['linked_staff_checked_share'][$i], $request['linked_staff_checked_attend'])) ? "yes" : "no",
                        'comment_read_at' => Carbon::now(),
                        'created_by' => $authUserId,
                        'is_read' => ($authUserId == $request['linked_staff_checked_share'][$i]) ? 'yes' : 'no',
                    ];
                }
                $alreadyAdded[]=$request['linked_staff_checked_share'][$i];
            }
        }

        if(isset($request['share_checkbox_nonlinked']) && count($request['share_checkbox_nonlinked'])) {
            $alreadyAdded = $attend_checkbox_nonlinked = [];
            if(isset($request['attend_checkbox_nonlinked'])){
                for($i=0;$i<count(array_unique($request['attend_checkbox_nonlinked']));$i++){                
                    array_push($attend_checkbox_nonlinked, $request['attend_checkbox_nonlinked'][$i]);
                }            
            }
            for($i=0;$i<count(array_unique($request['share_checkbox_nonlinked']));$i++){
                $attend="no";
                if(isset($request['share_checkbox_nonlinked'][$i])){
                    if(in_array($request['share_checkbox_nonlinked'][$i], $attend_checkbox_nonlinked)){
                        $attend="yes";
                    }
                }
                if(!in_array($request['share_checkbox_nonlinked'][$i],$alreadyAdded)){
                    $eventLinkedStaff[] = [
                        'event_id' => $caseEvent->id,
                        'user_id' => $request['share_checkbox_nonlinked'][$i],
                        'is_linked' => 'no',
                        'attending' => $attend,
                        'comment_read_at' => Carbon::now(),
                        'created_by' => $authUserId,
                        'is_read' => ($authUserId == $request['share_checkbox_nonlinked'][$i]) ? 'yes' : 'no',
                    ];
                }
                $alreadyAdded[]=$request['share_checkbox_nonlinked'][$i];
            }
        }

        return encodeDecodeJson($eventLinkedStaff, 'encode');
    }

    /**
     * Get event linked contact/lead user json
     */
    public function getEventLinkedContactLeadJson($caseEvent, $request)
    {
        $authUserId = auth()->id();
        $eventLinkedClient = [];
        if(isset($request['LeadInviteClientCheckbox']) && count($request['LeadInviteClientCheckbox'])){
            $alreadyAdded=$attend_checkbox_nonlinked = [];
            if(isset($request['LeadAttendClientCheckbox'])) {
                for($i=0;$i<count(array_unique($request['LeadAttendClientCheckbox']));$i++) {                
                    array_push($attend_checkbox_nonlinked, $request['LeadAttendClientCheckbox'][$i]);
                } 
            }                
            for($i=0;$i<count(array_unique($request['LeadInviteClientCheckbox']));$i++) {
                $attend="no";
                if(isset($request['LeadInviteClientCheckbox'][$i])) {
                    if(in_array($request['LeadInviteClientCheckbox'][$i], $attend_checkbox_nonlinked)) {
                        $attend="yes";
                    }
                }
                if(!in_array($request['LeadInviteClientCheckbox'][$i], $alreadyAdded)) {
                    $eventLinkedClient[] = [
                        'event_id' => $caseEvent->id,
                        'user_type' => 'lead',
                        'lead_id' => $request['LeadInviteClientCheckbox'][$i],
                        'attending' => $attend,
                        'invite' => 'yes',
                        'is_view' => 'no',
                        'created_by' => $authUserId,
                        'contact_id' => '',
                    ];
                }
                $alreadyAdded[]=$request['LeadInviteClientCheckbox'][$i];

                
            }
        }else if(isset($request['ContactInviteClientCheckbox']) && count($request['ContactInviteClientCheckbox'])){
            $alreadyAdded=$attend_checkbox_nonlinked = [];
            if(isset($request['ContactAttendClientCheckbox'])) {
                for($i=0;$i<count(array_unique($request['ContactAttendClientCheckbox']));$i++) {                
                    array_push($attend_checkbox_nonlinked, $request['ContactAttendClientCheckbox'][$i]);
                } 
            }
            for($i=0;$i<count(array_unique($request['ContactInviteClientCheckbox']));$i++) {
                $attend = "no";
                if(isset($request['ContactInviteClientCheckbox'][$i])){
                    if(in_array($request['ContactInviteClientCheckbox'][$i], $attend_checkbox_nonlinked)){
                        $attend="yes";
                    }
                }
                if(!in_array($request['ContactInviteClientCheckbox'][$i],$alreadyAdded)){
                    $eventLinkedClient[] = [
                        'event_id' => $caseEvent->id,
                        'user_type' => 'contact',
                        'contact_id' => $request['ContactInviteClientCheckbox'][$i],
                        'attending' => $attend,
                        'invite' => 'yes',
                        'is_view' => 'no',
                        'created_by' => $authUserId,
                        'lead_id' => '',
                    ];
                }
                $alreadyAdded[]=$request['ContactInviteClientCheckbox'][$i];
            }
        }
        return encodeDecodeJson($eventLinkedClient, 'encode');
    }

    /**
     * Save add event history like created, updated etc
     */
    public function getAddEventHistoryJson($eventId)
    {
        $history = [];
        $history[] = [
            'event_id' => $eventId,
            'comment' => "",
            'created_by' => auth()->id(),
            'updated_by' => "",
            'action_type' => "1",
            'created_at' => Carbon::now(),
        ];
        return encodeDecodeJson($history, 'encode');
    }

    /**
     * Save edit event history like created, updated etc
     */
    public function getEditEventHistoryJson($eventId, $eventRecurring)
    {
        $eventHistory = [
            'event_id' => $eventId,
            'comment' => "",
            'created_by' => "",
            'updated_by' => auth()->id(),
            'action_type' => "1",
            'created_at' => Carbon::now(),
        ];
        $decodeJson = encodeDecodeJson($eventRecurring->event_comments);
        $decodeJson->push($eventHistory);
        return encodeDecodeJson($decodeJson, 'encode');
    }

    /**
     * Save recurring events
     */
    public function saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $recurringEndDate, $locationID)
    {
        $authUser = auth()->user();
        if(isset($request->all_day)) {
            $utc_start_date = $this->eventConvertTimestampToUtc($start_date, $request->start_time, $authUser->user_timezone, 'onlyDate');
            $utc_end_date = $this->eventConvertTimestampToUtc($end_date, $request->end_time, $authUser->user_timezone, 'onlyDate');
            $utc_recurring_end_date = $this->eventConvertTimestampToUtc($recurringEndDate, $request->start_time, $authUser->user_timezone, 'onlyDate');
        } else {
            $utc_start_date = $this->eventConvertTimestampToUtc($start_date, $request->start_time, $authUser->user_timezone, 'dateFromTime');
            $utc_end_date = $this->eventConvertTimestampToUtc($end_date, $request->end_time, $authUser->user_timezone, 'dateFromTime');
            $utc_recurring_end_date = $this->eventConvertTimestampToUtc($recurringEndDate, $request->start_time, $authUser->user_timezone, 'dateFromTime');
        }


        $caseEvent = Event::create([
            "event_title" => $request->event_name,
            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
            "event_type_id" => $request->event_type ?? NULL,
            "start_date" => $utc_start_date,
            "end_date" => $utc_end_date,
            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
            "recurring_event_end_date" => $utc_recurring_end_date,
            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
            "event_description" => $request->description,
            "is_recurring" => "yes",
            "event_location_id" => $locationID ?? NULL,
            "event_recurring_type" => $request->event_frequency,
            "event_interval_day" => $request->event_interval_day,
            "event_interval_month" => $request->event_interval_month,
            "event_interval_year" => $request->event_interval_year,
            "monthly_frequency" => $request->monthly_frequency,
            "yearly_frequency" => $request->yearly_frequency,
            "custom_event_weekdays" => $request->custom,
            "event_interval_week" => $request->daily_weekname,
            "is_no_end_date" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
            "end_on" => (!isset($request->no_end_date_checkbox)) ? $utc_recurring_end_date : NULL,
            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
            "firm_id" => $authUser->firm_name,
            "created_by" => $authUser->id,
        ]);

        if($request->event_frequency =='DAILY') {
            $eventRecurring = $this->saveDailyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
        } else if($request->event_frequency == "EVERY_BUSINESS_DAY") {
            $eventRecurring = $this->saveBusinessDayRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
        } else if($request->event_frequency == "WEEKLY") {
            $eventRecurring = $this->saveWeeklyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
        } else if($request->event_frequency == "CUSTOM") {
            $eventRecurring = $this->saveCustomRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
        } else if($request->event_frequency == "MONTHLY") {
            $eventRecurring = $this->saveMonthlyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
        }
        // Commented. As per client's requirement
        /* else if($request->event_frequency == "YEARLY") {
            $eventRecurring = $this->saveYearlyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
        } */

        if(getAuthUserSocialAccount()) {
            $this->dispatch(new SyncEventToSocialAccountJob($authUser, $caseEvent, $eventRecurring));
        }

        $this->saveEventRecentActivity($request, $caseEvent->id, @$eventRecurring->id, 'add');
        if(!isset($request->no_case_link) && $request->text_lead_id!='') {
            $this->sendLeadUserInviteEmail($request->text_lead_id, $eventRecurring, $caseEvent, 'add');
        }
    }

    /**
     * Get diff between 2 dates in days
     */
    public function getDatesDiffDays($request)
    {
        $authUser = auth()->user();
        if(isset($request->all_day)) {
            $utc_start_date = $this->eventConvertTimestampToUtc(date('Y-m-d', strtotime($request->start_date)), $authUser->user_timezone, 'onlyDate');
            $utc_end_date = $this->eventConvertTimestampToUtc(date('Y-m-d', strtotime($request->end_date)), $authUser->user_timezone, 'onlyDate');
        } else {
            $utc_start_date = $this->eventConvertTimestampToUtc(date('Y-m-d', strtotime($request->start_date)), $request->start_time, $authUser->user_timezone, 'dateFromTime');
            $utc_end_date = $this->eventConvertTimestampToUtc(date('Y-m-d', strtotime($request->end_date)), $request->end_time, $authUser->user_timezone, 'dateFromTime');
        }
        // return strtotime($utc_end_date);
        $days = 0;
        /* if((strtotime($request->start_date) != strtotime($request->end_date))) {
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $days = $end->diffInDays($start);
        } */
        if((strtotime($utc_start_date) != strtotime($utc_end_date))) {
            $start = Carbon::parse($utc_start_date);
            $end = Carbon::parse($utc_end_date);
            $days = $end->diffInDays($start);
        }
        return $days;
    }

    /**
     * Save daily recurring event
     */
    public function saveDailyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate)
    {
        $authUser = auth()->user();
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_day.' days', $recurringEndDate);
        $days = $this->getDatesDiffDays($request);
        foreach($period as $date) {
            if(isset($request->all_day)) {
                $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'onlyDate');
            } else {
                $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'dateFromTime');
            }
            $request->start_date = $date->format('Y-m-d');
            $eventRecurring = EventRecurring::create([
                "event_id" => $caseEvent->id,
                "start_date" => $utc_start_date,
                "end_date" => ($days > 0) ? Carbon::parse($utc_start_date)->addDays($days)->format('Y-m-d') : $utc_start_date,
                "event_reminders" => $this->getUpdateEventReminderJson($caseEvent, $request),
                "event_linked_staff" => $eventLinkStaff,
                "event_linked_contact_lead" => $eventLinkClient,
                "event_comments" => $eventHistory
            ]);
        }
        return $eventRecurring ?? Null;
    }

    /**
     * Save every businessday recurring event
     */
    public function saveBusinessDayRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate)
    {
        $authUser = auth()->user();
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $period = \Carbon\CarbonPeriod::create($start_date, '1 days', $recurringEndDate);
        $days = $this->getDatesDiffDays($request);
        foreach($period as $date) {    
            if (!in_array($date->format('l'), ["Saturday","Sunday"])) {
                if(isset($request->all_day)) {
                    $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'onlyDate');
                } else {
                    $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'dateFromTime');
                }
                // $timestamp = date('Y-m-d H:i:s',strtotime($date->format('Y-m-d').' '.$request->start_time));
                // $date = Carbon::parse($timestamp)->subHours('6');
                /* $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $authUser->user_timezone);
                $date1->setTimezone('UTC');
                $utcDateOffset = Carbon::parse($date1->format('Y-m-d H:i:s').' America/Mexico_City')->format('P');
                $first_character = substr($str, 0, 1); */
                
                // $utc_start_date = Carbon::parse($date->format('Y-m-d').' '. $request->start_time)->subHours($offset)->format('Y-m-d');
                $request->start_date = $date->format('Y-m-d');
                $eventRecurring = EventRecurring::create([
                    "event_id" => $caseEvent->id,
                    "start_date" => $utc_start_date,
                    "end_date" => ($days > 0) ? Carbon::parse($utc_start_date)->addDays($days)->format('Y-m-d') : $utc_start_date,
                    "event_reminders" => $this->getUpdateEventReminderJson($caseEvent, $request),
                    "event_linked_staff" => $eventLinkStaff,
                    "event_linked_contact_lead" => $eventLinkClient,
                    "event_comments" => $eventHistory
                ]);
            }
        }
        return $eventRecurring ?? Null;
    }

    /**
     * SAve custom(weekly) recurring events
     */
    public function saveCustomRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate)
    {
        $authUser = auth()->user();
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $days = $this->getDatesDiffDays($request);
        $start = new DateTime($start_date);
        $startClone = new DateTime($start_date);
        if(isset($request->end_on)) {
            $recurringEndDate=new DateTime($recurringEndDate);
        }else{
            $recurringEndDate=$startClone->add(new DateInterval('P365D'));
        }
        $recurringEndDate = $recurringEndDate->modify( '+1 day' );
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $recurringEndDate);
        $weekInterval = $request->daily_weekname;
        $fakeWeek = 0;
        $currentWeek = $start->format('W');
        
        foreach ($period as $date) {
            if ($date->format('W') !== $currentWeek) {
                $currentWeek = $date->format('W');
                $fakeWeek++;
            }
            if ($fakeWeek % $weekInterval !== 0) {
                continue;
            }
            $dayOfWeek = $date->format('l');
            if(in_array($dayOfWeek, $request->custom)) {    
                if(isset($request->all_day)) {
                    $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'onlyDate');
                } else {
                    $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'dateFromTime');
                }
                $request->start_date = $date->format('Y-m-d');   
                $eventRecurring = EventRecurring::create([
                    "event_id" => $caseEvent->id,
                    "start_date" => $utc_start_date,
                    "end_date" => ($days > 0) ? Carbon::parse($utc_start_date)->addDays($days)->format('Y-m-d') : $utc_start_date,
                    "event_reminders" => $this->getUpdateEventReminderJson($caseEvent, $request),
                    "event_linked_staff" => $eventLinkStaff,
                    "event_linked_contact_lead" => $eventLinkClient,
                    "event_comments" => $eventHistory
                ]);
            }
        }
        return $eventRecurring ?? Null;
    }

    /**
     * Save weekly recurring event
     */
    public function saveWeeklyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate)
    {
        $authUser = auth()->user();
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $period = \Carbon\CarbonPeriod::create($start_date, '7 days', $recurringEndDate);
        $days = $this->getDatesDiffDays($request);
        foreach($period as $date) {        
            if(isset($request->all_day)) {
                $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'onlyDate');
            } else {
                $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'dateFromTime');
            }      
            $request->start_date = $date->format('Y-m-d');
            $eventRecurring = EventRecurring::create([
                "event_id" => $caseEvent->id,
                "start_date" => $utc_start_date,
                "end_date" => ($days > 0) ? Carbon::parse($utc_start_date)->addDays($days)->format('Y-m-d') : $utc_start_date,
                "event_reminders" => $this->getUpdateEventReminderJson($caseEvent, $request),
                "event_linked_staff" => $eventLinkStaff,
                "event_linked_contact_lead" => $eventLinkClient,
                "event_comments" => $eventHistory
            ]);
        }
        return $eventRecurring ?? Null;
    }

    /**
     * Save monthly recurring event
     */
    public function saveMonthlyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate)
    {
        $authUser = auth()->user();
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_month.' months', $recurringEndDate);
        $days = $this->getDatesDiffDays($request);
        foreach($period as $date) {       
            $currentWeekDay = strtolower(date('l', strtotime($request->start_date))); 
            if($request->monthly_frequency == 'MONTHLY_ON_DAY'){
                $date1 = strtotime($date);
            } else if($request->monthly_frequency == 'MONTHLY_ON_THE') {
                $nthDay = ceil(date('j', strtotime($request->start_date)) / 7);
                $nthText = getWeekNthDay($nthDay);
                $date1 = strtotime($nthText." ". $currentWeekDay ." of this month", strtotime($date));
            }else if($request->monthly_frequency=='MONTHLY_ON_THE_LAST'){
                $date1 = strtotime("last ". $currentWeekDay ." of this month", strtotime($date));
            } else { 
                $date1 = strtotime($date);
            }            
            if(isset($request->all_day)) {
                $utc_start_date = $this->eventConvertTimestampToUtc(date('Y-m-d', $date1), $request->start_time, $authUser->user_timezone, 'onlyDate');
            } else {
                $utc_start_date = $this->eventConvertTimestampToUtc(date('Y-m-d', $date1), $request->start_time, $authUser->user_timezone, 'dateFromTime');
            }
            $request->start_date = date('Y-m-d', $date1);
            $eventRecurring = EventRecurring::create([
                "event_id" => $caseEvent->id,
                "start_date" => $utc_start_date,
                "end_date" => ($days > 0) ? Carbon::parse($date1)->addDays($days)->format('Y-m-d') : $utc_start_date,
                "event_reminders" => $this->getUpdateEventReminderJson($caseEvent, $request),
                "event_linked_staff" => $eventLinkStaff,
                "event_linked_contact_lead" => $eventLinkClient,
                "event_comments" => $eventHistory
            ]);
        }
        return $eventRecurring ?? Null;
    }
    // Yearly event option comment, as per client's requirement
    /**
     * Save yearly recurring event
     */
    /* public function saveYearlyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate)
    {
        // $eventReminders = $this->getEventReminderJson($caseEvent, $request);
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_year.' years', $recurringEndDate);
        $days = $this->getDatesDiffDays($request);
        foreach($period as $date) {       
            $currentWeekDay = strtolower(date('l', strtotime($request->start_date))); 
            if($request->monthly_frequency == 'YEARLY_ON_DAY') {
                $date1 = strtotime($date);
            } else if($request->yearly_frequency == 'YEARLY_ON_THE') {
                $nthDay = ceil(date('j', strtotime($date)) / 7);
                $nthText = getWeekNthDay($nthDay);
                $date1 = strtotime($nthText." ". $currentWeekDay ." of this month", strtotime($date));
                // $date = strtotime("fourth ". $currentWeekDay ." of this month", strtotime($date));
            } else if($request->yearly_frequency == 'YEARLY_ON_THE_LAST') {
                $date1 = strtotime("last ". $currentWeekDay ." of this month", strtotime($date));
            } else { 
                $date1 = strtotime($date);
            }
            $eventRecurring = EventRecurring::create([
                "event_id" => $caseEvent->id,
                "start_date" => date('Y-m-d', $date1),
                "end_date" => ($days > 0) ? Carbon::parse($date1)->addDays($days)->format('Y-m-d') : date('Y-m-d', $date1),
                // "event_reminders" => $eventReminders,
                "event_linked_staff" => $eventLinkStaff,
                "event_linked_contact_lead" => $eventLinkClient,
                "event_comments" => $eventHistory
            ]);

            if($request->reminder_user_type && count($request['reminder_user_type']) > 1) {
                $this->saveEventUserReminder($caseEvent, $eventRecurring, $request);
            }
        }
        return $eventRecurring ?? Null;
    } */

    /**
     * Save events recent activity like created, updated, deleted etc
     */
    public function saveEventRecentActivity($request, $caseEventId, $eventRecurringId = Null, $activityType = "edit")
    {
        $data=[];
        if(!isset($request->no_case_link)){
            if(isset($request->case_or_lead)) { 
                if($request->text_case_id!=''){
                    $data['event_for_case']=$request->text_case_id;
                }    
                if($request->text_lead_id!=''){
                    $data['event_for_lead']=$request->text_lead_id;                                            
                    $data['client_id']=$request->text_lead_id;
                }    
            } 
        }
        $data['event_id']=$caseEventId;
        $data['event_recurring_id']=$eventRecurringId;
        $data['event_name']=$request->event_name;
        $data['user_id'] = auth()->id();
        $data['activity'] = ($activityType == 'edit') ? 'updated event' : 'added event';
        $data['type'] = 'event';
        $data['action'] = ($activityType == 'edit') ? 'update' : 'add';
        
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);
    }

    /**
     * Get event remind at attribute
     */
    public function getRemindAtAttribute($request, $reminder_frequncy, $reminder_number, $responseType = 'date')
    {
        $timezone = auth()->user()->user_timezone;
        $eventStartTime = Carbon::parse($request->start_date.' '.$request->start_time);
        if($reminder_frequncy == "week" || $reminder_frequncy == "day") {
            if($reminder_frequncy == "week") {
                $remindDate = $eventStartTime->copy()->subWeeks($reminder_number)->format('Y-m-d');
                $remindTime = $eventStartTime->copy()->subWeeks($reminder_number)->format('Y-m-d H:i:s');
            } else {
                $remindDate = $eventStartTime->copy()->subDays($reminder_number)->format('Y-m-d');
                $remindTime = $eventStartTime->copy()->subDays($reminder_number)->format('Y-m-d H:i:s');
            }
        } else if($reminder_frequncy == "hour") {
            $remindTime = $eventStartTime->copy()->subHours($reminder_number)->format('Y-m-d H:i:s');
            $remindDate = $eventStartTime->copy()->subHours($reminder_number)->format('Y-m-d');
        } else if($reminder_frequncy == "minute") {
            $remindTime = $eventStartTime->copy()->subMinutes($reminder_number)->format('Y-m-d H:i:s');
            $remindDate = $eventStartTime->copy()->subMinutes($reminder_number)->format('Y-m-d');
        } else {
            $remindDate = Carbon::now()->format('Y-m-d');
            $remindTime = Carbon::now()->format('Y-m-d H:i:s');
        }
        return ($responseType == 'time') ? convertTimeToUTCzone($remindTime, $timezone) : convertDateToUTCzone($remindDate,$timezone);
    }

    /**
     * Update event user reminders json
     */
    public function getUpdateEventReminderJson($caseEvent, $request, $eventRecurring = null)
    {
        $authUserId = auth()->id();
        $newArray = [];
        if($eventRecurring) {
            if(!isset($request->updated_start_date) ) {
                $request->start_date = $eventRecurring->user_start_date->format('Y-m-d');
            }
            $decodeReminder = encodeDecodeJson($eventRecurring->event_reminders);
            $eventReminders = $this->getEventReminderJson($caseEvent, $request, $decodeReminder);
            if(count($decodeReminder)) {
                $newArray = $decodeReminder->filter(function($item) use($authUserId) {
                    return $item->created_by != $authUserId;
                })->values()->toArray();
            }
            foreach($eventReminders as $ritem) {
                array_push($newArray, $ritem);
            }
        } else {
            $eventReminders = $this->getEventReminderJson($caseEvent, $request);
            $newArray = $eventReminders;
        }
        return encodeDecodeJson($newArray, 'encode');
    }

    /**
     * Get event reminder snooze remind at attribute
     */
    public function getSnoozeRemindAtAttribute($request)
    {
        $snoozedTime = Carbon::now();
        if($request->snooze_type == "hour")
            $remindTime = Carbon::parse($snoozedTime)->addHours($request->snooze_time)->format('Y-m-d H:i');
        else if($request->snooze_type == "day")
            $remindTime = Carbon::parse($snoozedTime)->addDays($request->snooze_time)->format('Y-m-d');
        else if($request->snooze_type == "week")
            $remindTime = Carbon::parse($snoozedTime)->addDays($request->snooze_time)->format('Y-m-d H:i');
        else
            $remindTime = Carbon::parse($snoozedTime)->addMinutes($request->snooze_time)->format('Y-m-d H:i');
        return $remindTime;
    }

    /**
     * Send invitation email to lead user for event
     */
    public function sendLeadUserInviteEmail($leadId, $eventRecurring, $caseEvent, $eventAction = 'edit')
    {
        $leadUser = User::whereId($leadId)->first();
        $isUserLinked = encodeDecodeJson($eventRecurring->event_linked_contact_lead)->where('lead_id', $leadId)->first();
        if($isUserLinked && $leadUser && $leadUser->email) {
            dispatch(new LeadEventInvitationEmailJob($eventRecurring, $caseEvent, $leadUser, $eventAction));
        }
    }

    /**
     * Convert event start/end date/time to utc zone
     */
    public function eventConvertTimestampToUtc($date, $time, $authUserTimezone, $responseType = null)
    {
        if($responseType == 'time' || $responseType == 'dateFromTime') {
            $offset = Carbon::now($authUserTimezone)->offsetHours;
            $timestamp = date('Y-m-d H:i:s',strtotime($date.' '.$time));
            if($responseType == 'dateFromTime') {
                // $date = Carbon::parse($timestamp)->subHours('6');
                // $date = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $authUserTimezone);
                // $date->setTimezone('UTC');
                $first_character = substr($offset, 0, 1);
                $hour = substr($offset, 1);
                $date = ($first_character == '-') ? Carbon::parse($date.' '.$time)->addHours($hour)->format('Y-m-d') : Carbon::parse($date.' '.$time)->subHours($hour)->format('Y-m-d');
                return $date;
            } else {
                $date = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $authUserTimezone);
                $date->setTimezone('UTC');
                return $date->format("H:i:s");
            }
        } elseif($responseType == 'onlyDate') {
            $date = Carbon::createFromFormat('Y-m-d', date('Y-m-d',strtotime($date)), $authUserTimezone);
            $date->setTimezone('UTC');
            return $date->format("Y-m-d");
        }
        return $date;
    }

    /**
     * Update weekly recurring event
     */
    public function updateRecurringEvent($event, $request)
    {
        $authUser = auth()->user();
        $days = $this->getDatesDiffDays($request);

        $eventLinkStaff = $this->getEventLinkedStaffJson($event, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($event, $request);
        $recurringEvents = EventRecurring::where("event_id", $event->id)->get();
        foreach($recurringEvents as $rkey => $ritem) {
            $userStartDate = $ritem->user_start_date;
            $userEndDate = $ritem->user_end_date;
            if($event->is_full_day == 'no') {
                $userStartDate = convertToUserTimezone($ritem->start_date.' '.$event->start_time, $authUser->user_timezone);
                $userEndDate = convertToUserTimezone($ritem->end_date.' '.$event->end_time, $authUser->user_timezone);
            }
            if(isset($request->all_day)) {
                $utc_start_date = $this->eventConvertTimestampToUtc($userStartDate->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'onlyDate');
                $utc_end_date = $this->eventConvertTimestampToUtc($userEndDate->format('Y-m-d'), $request->end_time, $authUser->user_timezone, 'onlyDate');
            } else {
                $utc_start_date = $this->eventConvertTimestampToUtc($userStartDate->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'dateFromTime');
                $utc_end_date = $this->eventConvertTimestampToUtc($userEndDate->format('Y-m-d'), $request->end_time, $authUser->user_timezone, 'dateFromTime');
            }      
            $ritem->fill([
                "start_date" => $utc_start_date,
                "end_date" => ($days > 0) ? Carbon::parse($utc_start_date)->addDays($days)->format('Y-m-d') : $utc_end_date,
                "event_reminders" => ($request->is_reminder_updated == 'yes') ? $this->getUpdateEventReminderJson($event, $request, $ritem) : $ritem->event_reminders,
                "event_linked_staff" => $eventLinkStaff,
                "event_linked_contact_lead" => $eventLinkClient,
                'event_comments' => $this->getEditEventHistoryJson($event->id, $ritem),
            ])->save();
            $eventRecurring = $ritem;
        }
        return $eventRecurring ?? Null;
    }

    /**
     * Update following recurring events of all type
     */
    public function updateFollowingRecurringEvent($request, $recurringType, $start_date, $recurring_end_date, $oldEvent, $caseEvent, $days)
    {
        $authUser = auth()->user();
        $eventLinkedStaff = $this->getEventLinkedStaffJson($caseEvent ?? $oldEvent, $request);
        $eventLinkedClient = $this->getEventLinkedContactLeadJson($caseEvent ?? $oldEvent, $request);

        if($recurringType == "daily") {
            $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_day.' days', $recurring_end_date);
            foreach($period as $date) {
                if($oldEvent->is_full_day == 'no') {
                    $oldStartDate = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $oldEvent->user_start_time, $authUser->user_timezone, 'dateFromTime');
                } else {
                    $oldStartDate = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->user_start_time, $authUser->user_timezone, 'onlyDate');
                }
                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $oldStartDate)->first();
                if($eventRecurring) {
                    if(isset($request->all_day)) {
                        $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'onlyDate');
                        $utc_end_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->end_time, $authUser->user_timezone, 'onlyDate');
                    } else {
                        $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'dateFromTime');
                        $utc_end_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->end_time, $authUser->user_timezone, 'dateFromTime');
                    }
                    $request->start_date = $date->format('Y-m-d');
                    $eventRecurring->fill([
                        "event_id" => $caseEvent->id ?? $oldEvent->id,
                        "start_date" => $utc_start_date,
                        "end_date" => ($days > 0) ? Carbon::parse($utc_start_date)->addDays($days)->format('Y-m-d') : $utc_end_date,
                        "event_reminders" => ($request->is_reminder_updated == 'yes') ? $this->getUpdateEventReminderJson($caseEvent ?? $oldEvent, $request, $eventRecurring) : $eventRecurring->event_reminders,
                        "event_linked_staff" => $eventLinkedStaff,
                        "event_linked_contact_lead" => $eventLinkedClient,
                        'event_comments' => $this->getEditEventHistoryJson($caseEvent->id ?? $oldEvent->id, $eventRecurring),
                    ])->save();
                }
            }
        } else if($recurringType == "business_day") {
            $period = \Carbon\CarbonPeriod::create($start_date, $recurring_end_date);
            $days = $this->getDatesDiffDays($request);
            foreach($period as $date) {  
                if($oldEvent->is_full_day == 'no') {
                    $oldStartDate = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $oldEvent->user_start_time, $authUser->user_timezone, 'dateFromTime');
                } else {
                    $oldStartDate = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->user_start_time, $authUser->user_timezone, 'onlyDate');
                } 
                if (!in_array($date->format('l'), ["Saturday","Sunday"])) {
                    $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $oldStartDate)->first();
                    if($eventRecurring) {    
                        if(isset($request->all_day)) {
                            $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'onlyDate');
                            $utc_end_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->end_time, $authUser->user_timezone, 'onlyDate');
                        } else {
                            $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'dateFromTime');
                            $utc_end_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->end_time, $authUser->user_timezone, 'dateFromTime');
                        }
                        $request->start_date = $date->format('Y-m-d');
                        $eventRecurring->fill([
                            "event_id" => $caseEvent->id ?? $oldEvent->id,
                            "start_date" => $utc_start_date,
                            "end_date" => ($days > 0) ? Carbon::parse($utc_start_date)->addDays($days)->format('Y-m-d') : $utc_end_date,
                            "event_reminders" => ($request->is_reminder_updated == 'yes') ? $this->getUpdateEventReminderJson($caseEvent ?? $oldEvent, $request, $eventRecurring) : $eventRecurring->event_reminders,
                            "event_linked_staff" => $eventLinkedStaff,
                            "event_linked_contact_lead" => $eventLinkedClient,
                            'event_comments' => $this->getEditEventHistoryJson($caseEvent->id ?? $oldEvent->id, $eventRecurring),
                        ])->save();
                    }
                }
            }
        } else if($recurringType == "custom") {
            $start = new DateTime($start_date);
            $startClone = new DateTime($start_date);
            if(isset($request->end_on)) {
                $recurringEndDate=new DateTime($request->end_on);
            }else{
                $recurringEndDate=$startClone->add(new DateInterval('P365D'));
            }
            $recurringEndDate = $recurringEndDate->modify( '+1 day' );
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($start, $interval, $recurringEndDate);
            $weekInterval = $request->daily_weekname;
            $fakeWeek = 0;
            $currentWeek = $start->format('W');
            
            foreach ($period as $date) {
                if ($date->format('W') !== $currentWeek) {
                    $currentWeek = $date->format('W');
                    $fakeWeek++;
                }
                if ($fakeWeek % $weekInterval !== 0) {
                    continue;
                }
                if($oldEvent->is_full_day == 'no') {
                    $oldStartDate = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $oldEvent->user_start_time, $authUser->user_timezone, 'dateFromTime');
                } else {
                    $oldStartDate = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->user_start_time, $authUser->user_timezone, 'onlyDate');
                } 
                $dayOfWeek = $date->format('l');
                if(in_array($dayOfWeek, $request->custom)) {   
                    $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $oldStartDate)->first();
                    if($eventRecurring) {
                        if(isset($request->all_day)) {
                            $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'onlyDate');
                            $utc_end_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->end_time, $authUser->user_timezone, 'onlyDate');
                        } else {
                            $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'dateFromTime');
                            $utc_end_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->end_time, $authUser->user_timezone, 'dateFromTime');
                        }
                        $request->start_date = $date->format('Y-m-d');
                        $eventRecurring->fill([
                            "event_id" => $caseEvent->id ?? $oldEvent->id,
                            "start_date" => $utc_start_date,
                            "end_date" => ($days > 0) ? Carbon::parse($utc_start_date)->addDays($days)->format('Y-m-d') : $utc_end_date,
                            "event_reminders" => ($request->is_reminder_updated == 'yes') ? $this->getUpdateEventReminderJson($caseEvent ?? $oldEvent, $request, $eventRecurring) : $eventRecurring->event_reminders,
                            "event_linked_staff" => $eventLinkedStaff,
                            "event_linked_contact_lead" => $eventLinkedClient,
                            'event_comments' => $this->getEditEventHistoryJson($caseEvent->id ?? $oldEvent->id, $eventRecurring),
                        ])->save();
                    }
                }
            }
        } else if($recurringType == "weekly") {
            $period = \Carbon\CarbonPeriod::create($start_date, '7 days', $recurring_end_date);
            foreach($period as $date) {   
                if($oldEvent->is_full_day == 'no') {
                    $oldStartDate = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $oldEvent->user_start_time, $authUser->user_timezone, 'dateFromTime');
                } else {
                    $oldStartDate = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->user_start_time, $authUser->user_timezone, 'onlyDate');
                }
                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $oldStartDate)->first();
                if($eventRecurring) {
                    if(isset($request->all_day)) {
                        $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'onlyDate');
                        $utc_end_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->end_time, $authUser->user_timezone, 'onlyDate');
                    } else {
                        $utc_start_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->start_time, $authUser->user_timezone, 'dateFromTime');
                        $utc_end_date = $this->eventConvertTimestampToUtc($date->format('Y-m-d'), $request->end_time, $authUser->user_timezone, 'dateFromTime');
                    }
                    $request->start_date = $date->format('Y-m-d');
                    $eventRecurring->fill([
                        "event_id" => $caseEvent->id ?? $oldEvent->id,
                        "start_date" => $utc_start_date,
                        "end_date" => ($days > 0) ? Carbon::parse($utc_start_date)->addDays($days)->format('Y-m-d') : $utc_end_date,
                        "event_reminders" => ($request->is_reminder_updated == 'yes') ? $this->getUpdateEventReminderJson($caseEvent ?? $oldEvent, $request, $eventRecurring) : $eventRecurring->event_reminders,
                        "event_linked_staff" => $eventLinkedStaff,
                        "event_linked_contact_lead" => $eventLinkedClient,
                        'event_comments' => $this->getEditEventHistoryJson($caseEvent->id ?? $oldEvent->id, $eventRecurring),
                    ])->save();
                }
            }
        } else if($recurringType == 'monthly') {
            $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_month.' months', $recurring_end_date);
            foreach($period as $date) {       
                $currentWeekDay = strtolower(date('l', strtotime($request->start_date))); 
                if($request->monthly_frequency == 'MONTHLY_ON_DAY'){
                    $date1 = strtotime($date);
                } else if($request->monthly_frequency == 'MONTHLY_ON_THE') {
                    $nthDay = ceil(date('j', strtotime($request->start_date)) / 7);
                    $nthText = getWeekNthDay($nthDay);
                    $date1 = strtotime($nthText." ". $currentWeekDay ." of this month", strtotime($date));
                }else if($request->monthly_frequency=='MONTHLY_ON_THE_LAST'){
                    $date1 = strtotime("last ". $currentWeekDay ." of this month", strtotime($date));
                } else { 
                    $date1 = strtotime($date);
                }
                if($oldEvent->is_full_day == 'no') {
                    $oldStartDate = $this->eventConvertTimestampToUtc(date('Y-m-d', $date1), $oldEvent->user_start_time, $authUser->user_timezone, 'dateFromTime');
                } else {
                    $oldStartDate = $this->eventConvertTimestampToUtc(date('Y-m-d', $date1), $request->user_start_time, $authUser->user_timezone, 'onlyDate');
                }
                $eventRecurring = EventRecurring::where("event_id", $oldEvent->id)->whereDate("start_date", $oldStartDate)->first();
                if($eventRecurring) {
                    if(isset($request->all_day)) {
                        $utc_start_date = $this->eventConvertTimestampToUtc(date('Y-m-d', $date1), $request->start_time, $authUser->user_timezone, 'onlyDate');
                        $utc_end_date = $this->eventConvertTimestampToUtc(date('Y-m-d', $date1), $request->end_time, $authUser->user_timezone, 'onlyDate');
                    } else {
                        $utc_start_date = $this->eventConvertTimestampToUtc(date('Y-m-d', $date1), $request->start_time, $authUser->user_timezone, 'dateFromTime');
                        $utc_end_date = $this->eventConvertTimestampToUtc(date('Y-m-d', $date1), $request->end_time, $authUser->user_timezone, 'dateFromTime');
                    }
                    $request->start_date = date('Y-m-d', $date1);
                    $eventRecurring->fill([
                        "event_id" => $caseEvent->id ?? $oldEvent->id,
                        "start_date" => $utc_start_date,
                        "end_date" => ($days > 0) ? Carbon::parse($utc_start_date)->addDays($days)->format('Y-m-d') : $utc_end_date,
                        "event_reminders" => ($request->is_reminder_updated == 'yes') ? $this->getUpdateEventReminderJson($caseEvent ?? $oldEvent, $request, $eventRecurring) : $eventRecurring->event_reminders,
                        "event_linked_staff" => $eventLinkedStaff,
                        "event_linked_contact_lead" => $eventLinkedClient,
                        'event_comments' => $this->getEditEventHistoryJson($caseEvent->id ?? $oldEvent->id, $eventRecurring),
                    ])->save();
                }
            }
            if(!isset($request->no_case_link) && $request->text_lead_id!='') {
                $this->sendLeadUserInviteEmail($request->text_lead_id, @$eventRecurring, $caseEvent);
            }
        }
    }
}
 