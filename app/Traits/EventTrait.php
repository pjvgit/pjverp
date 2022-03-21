<?php
 
namespace App\Traits;

use App\Event;
use App\EventRecurring;
use App\Http\Controllers\CommonController;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Null_;

trait EventTrait {
    /**
     * Get event reminders json
     */
    public function getEventReminderJson($caseEvent, $request)
    {
        $eventReminders = [];
        $authUserId = auth()->id();
        if($request->reminder_user_type && count($request['reminder_user_type']) > 1) {
            for($i=0; $i < count($request['reminder_user_type'])-1; $i++) {
                $eventReminders[] = [
                    'event_id' => $caseEvent->id,
                    'reminder_type' => $request['reminder_type'][$i],
                    'reminer_number' => $request['reminder_number'][$i],
                    'reminder_frequncy' => $request['reminder_time_unit'][$i],
                    'reminder_user_type' => $request['reminder_user_type'][$i],
                    'created_by' => $authUserId,
                    'remind_at' => Carbon::now(),
                ];
            }
        }
        return encodeDecodeJson($eventReminders, 'encode');
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
                        'is_read' => 'no',
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
                        'is_read' => 'no',
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
        $caseEvent = Event::create([
            "event_title" => $request->event_name,
            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
            "event_type_id" => $request->event_type ?? NULL,
            "start_date" => $start_date,
            "end_date" => $end_date,
            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
            "recurring_event_end_date" => convertDateToUTCzone(date("Y-m-d", $recurringEndDate), $authUser->user_timezone),
            "is_full_day" => (isset($request->all_day)) ? "yes" : "no",
            "event_description" => $request->description,
            "is_recurring" => "yes",
            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
            "event_recurring_type" => $request->event_frequency,
            "event_interval_day" => $request->event_interval_day,
            "event_interval_month" => $request->event_interval_month,
            "event_interval_year" => $request->event_interval_year,
            "monthly_frequency" => $request->monthly_frequency,
            "yearly_frequency" => $request->yearly_frequency,
            "custom_event_weekdays" => $request->custom,
            "event_interval_week" => $request->daily_weekname,
            "is_no_end_date" => (isset($request->no_end_date_checkbox) && $request->end_on) ? "yes" : "no",
            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
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
        } else if($request->event_frequency == "YEARLY") {
            $eventRecurring = $this->saveYearlyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate);
        }

        $this->saveEventRecentActivity($request, $caseEvent->id, @$eventRecurring->id, 'add');
    }

    /**
     * Get diff between 2 dates in days
     */
    public function getDatesDiffDays($request)
    {
        $days = 0;
        if((strtotime($request->start_date) != strtotime($request->end_date))) {
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $days = $end->diffInDays($start);
        }
        return $days;
    }

    /**
     * Save daily recurring event
     */
    public function saveDailyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate)
    {
        $eventReminders = $this->getEventReminderJson($caseEvent, $request);
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_day.' days', date("Y-m-d", $recurringEndDate));
        $days = $this->getDatesDiffDays($request);
        foreach($period as $date) {
            $eventRecurring = EventRecurring::create([
                "event_id" => $caseEvent->id,
                "start_date" => $date,
                "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date,
                "event_reminders" => $eventReminders,
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
        $eventReminders = $this->getEventReminderJson($caseEvent, $request);
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $period = \Carbon\CarbonPeriod::create($start_date, '1 days', date("Y-m-d", $recurringEndDate));
        $days = $this->getDatesDiffDays($request);
        foreach($period as $date) {          
            if (!in_array($date->format('l'), ["Saturday","Sunday"])) {
                $eventRecurring = EventRecurring::insert([
                    "event_id" => $caseEvent->id,
                    "start_date" => $date,
                    "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date,
                    "event_reminders" => $eventReminders,
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
        $eventReminders = $this->getEventReminderJson($caseEvent, $request);
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $days = $this->getDatesDiffDays($request);
        $start = new DateTime($start_date);
        $startClone = new DateTime($start_date);
        if(isset($request->end_on)) {
            $recurringEndDate=new DateTime($request->end_on);
        }else{
            $recurringEndDate=$startClone->add(new DateInterval('P365D'));
        }

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
                $eventRecurring = EventRecurring::create([
                    "event_id" => $caseEvent->id,
                    "start_date" => $date->format('Y-m-d'),
                    "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date->format('Y-m-d'),
                    "event_reminders" => $eventReminders,
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
        $eventReminders = $this->getEventReminderJson($caseEvent, $request);
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $period = \Carbon\CarbonPeriod::create($start_date, '7 days', date("Y-m-d", $recurringEndDate));
        $days = $this->getDatesDiffDays($request);
        foreach($period as $date) {          
            $eventRecurring = EventRecurring::create([
                "event_id" => $caseEvent->id,
                "start_date" => $date,
                "end_date" => ($days > 0) ? Carbon::parse($date)->addDays($days)->format('Y-m-d') : $date,
                "event_reminders" => $eventReminders,
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
        $eventReminders = $this->getEventReminderJson($caseEvent, $request);
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_month.' months', date("Y-m-d", $recurringEndDate));
        $days = $this->getDatesDiffDays($request);
        foreach($period as $date) {       
            $currentWeekDay = strtolower(date('l', strtotime($request->start_date))); 
            if($request->monthly_frequency == 'MONTHLY_ON_DAY'){
                $date1 = strtotime($date);
            } else if($request->monthly_frequency == 'MONTHLY_ON_THE') {
                $nthDay = ceil(date('j', strtotime($request->start_date)) / 7);
                $nthText = getWeekNthDay($nthDay);
                $date1 = strtotime($nthText." ". $currentWeekDay ." of this month", strtotime($date));
                // $date = strtotime("fourth ". $currentWeekDay ." of this month", strtotime($date));
            }else if($request->monthly_frequency=='MONTHLY_ON_THE_LAST'){
                $date1 = strtotime("last ". $currentWeekDay ." of this month", strtotime($date));
            } else { 
                $date1 = strtotime($date);
            }
            $eventRecurring = EventRecurring::create([
                "event_id" => $caseEvent->id,
                "start_date" => date('Y-m-d', $date1),
                "end_date" => ($days > 0) ? Carbon::parse($date1)->addDays($days)->format('Y-m-d') : date('Y-m-d', $date1),
                "event_reminders" => $eventReminders,
                "event_linked_staff" => $eventLinkStaff,
                "event_linked_contact_lead" => $eventLinkClient,
                "event_comments" => $eventHistory
            ]);
        }
        return $eventRecurring ?? Null;
    }

    /**
     * Save yearly recurring event
     */
    public function saveYearlyRecurringEvent($caseEvent, $start_date, $request, $recurringEndDate)
    {
        $eventReminders = $this->getEventReminderJson($caseEvent, $request);
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        $eventHistory = $this->getAddEventHistoryJson($caseEvent->id);
        $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_year.' years', date("Y-m-d", $recurringEndDate));
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
                "event_reminders" => $eventReminders,
                "event_linked_staff" => $eventLinkStaff,
                "event_linked_contact_lead" => $eventLinkClient,
                "event_comments" => $eventHistory
            ]);
        }
        return $eventRecurring ?? Null;
    }

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
}
 