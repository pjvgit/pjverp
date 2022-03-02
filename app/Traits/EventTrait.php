<?php
 
namespace App\Traits;

use App\Event;
use App\EventRecurring;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
                        'user_id' => $request['linked_staff_checked_share'][$i],
                        'is_linked' => 'no',
                        'attending' => $attend,
                        'comment_read_at' => Carbon::now(),
                        'created_by' => $authUserId,
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
                        'created_by' => $authUserId,
                    ];
                }
                $alreadyAdded[]=$request['ContactInviteClientCheckbox'][$i];
            }
        }
        return encodeDecodeJson($eventLinkedClient, 'encode');
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
            "is_no_end_date" => (isset($request->no_end_date_checkbox) && $request->end_on) ? "yes" : "no",
            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
            "firm_id" => $authUser->firm_name,
            "created_by" => $authUser->id,
        ]);

        $eventReminders = $this->getEventReminderJson($caseEvent, $request);
        $eventLinkStaff = $this->getEventLinkedStaffJson($caseEvent, $request);
        $eventLinkClient = $this->getEventLinkedContactLeadJson($caseEvent, $request);
        if($request->event_frequency =='DAILY') {
            $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_day.' days', date("Y-m-d", $recurringEndDate));
            foreach($period as $date) {
                EventRecurring::create([
                    "event_id" => $caseEvent->id,
                    "start_date" => $date,
                    "end_date" => $date,
                    "event_reminders" => $eventReminders,
                    "event_linked_staff" => $eventLinkStaff,
                    "event_linked_contact_lead" => $eventLinkClient,
                ]);
            }
        } else if($request->event_frequency == "EVERY_BUSINESS_DAY") {
            $period = \Carbon\CarbonPeriod::create($start_date, '1 days', date("Y-m-d", $recurringEndDate));
            foreach($period as $date) {          
                if (!in_array($date->format('l'), ["Saturday","Sunday"])) {
                    EventRecurring::insert([
                        "event_id" => $caseEvent->id,
                        "start_date" => $date,
                        "end_date" => $date,
                        "event_reminders" => $eventReminders,
                        "event_linked_staff" => $eventLinkStaff,
                        "event_linked_contact_lead" => $eventLinkClient,
                    ]);
                }
            }
        } else if($request->event_frequency == "WEEKLY") {
            $period = \Carbon\CarbonPeriod::create($start_date, '7 days', date("Y-m-d", $recurringEndDate));
            foreach($period as $date) {          
                EventRecurring::insert([
                    "event_id" => $caseEvent->id,
                    "start_date" => $date,
                    "end_date" => $date,
                    "event_reminders" => $eventReminders,
                    "event_linked_staff" => $eventLinkStaff,
                    "event_linked_contact_lead" => $eventLinkClient,
                ]);
            }
        } else if($request->event_frequency == "CUSTOM") {
            $period = \Carbon\CarbonPeriod::create($start_date, '7 days', date("Y-m-d", $recurringEndDate));
            foreach($period as $date) {          
                EventRecurring::insert([
                    "event_id" => $caseEvent->id,
                    "start_date" => $date,
                    "end_date" => $date,
                    "event_reminders" => $eventReminders,
                    "event_linked_staff" => $eventLinkStaff,
                    "event_linked_contact_lead" => $eventLinkClient,
                ]);
            }
        } else if($request->event_frequency == "MONTHLY") {
            $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_month.' months', date("Y-m-d", $recurringEndDate));
            foreach($period as $date) {       
                $currentWeekDay = strtolower($date->format('l')); 
                if($request->monthly_frequency == 'MONTHLY_ON_DAY'){
                    $date = strtotime($date);
                } else if($request->monthly_frequency == 'MONTHLY_ON_THE') {
                    $date = strtotime("fourth ". $currentWeekDay ." of this month", strtotime($date));
                }else if($request->monthly_frequency=='MONTHLY_ON_THE_LAST'){
                    $date = strtotime("last ". $currentWeekDay ." of this month", strtotime($date));
                } else { 
                    $date = strtotime($date);
                }
                EventRecurring::insert([
                    "event_id" => $caseEvent->id,
                    "start_date" => date('Y-m-d', $date),
                    "end_date" => date('Y-m-d', $date),
                    "event_reminders" => $eventReminders,
                    "event_linked_staff" => $eventLinkStaff,
                    "event_linked_contact_lead" => $eventLinkClient,
                ]);
            }
        } else if($request->event_frequency == "YEARLY") {
            $period = \Carbon\CarbonPeriod::create($start_date, $request->event_interval_year.' years', date("Y-m-d", $recurringEndDate));
            foreach($period as $date) {       
                $currentWeekDay = strtolower($date->format('l'));
                if($request->yearly_frequency == 'YEARLY_ON_THE'){
                    $date = strtotime("fourth ". $currentWeekDay ." of this month", strtotime($date));
                }else if($request->yearly_frequency == 'YEARLY_ON_THE_LAST'){
                    $date = strtotime("last ". $currentWeekDay ." of this month", strtotime($date));
                } else { 
                    $date = strtotime($date);
                }
                EventRecurring::insert([
                    "event_id" => $caseEvent->id,
                    "start_date" => date('Y-m-d', $date),
                    "end_date" => date('Y-m-d', $date),
                    "event_reminders" => $eventReminders,
                    "event_linked_staff" => $eventLinkStaff,
                    "event_linked_contact_lead" => $eventLinkClient,
                ]);
            }
        }
    }
}
 