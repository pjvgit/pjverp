<?php
 
namespace App\Traits;

use App\CaseEvent;
use App\CaseEventComment;
use App\CaseEventLinkedContactLead;
use App\CaseEventLinkedStaff;
use App\CaseEventReminder;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\CommonController;
use App\AllHistory;

trait CaseEventTrait {
    /**
     * Save recurring event data
     */
    public function saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $locationID)
    {
        $caseEvent = CaseEvent::create([
            "event_title" => $request->event_name,
            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
            "event_type" => $request->event_type ?? NULL,
            // "start_date" => $start_date,
            // "end_date" => $end_date,
            "start_date" => convertDateToUTCzone($start_date, $authUser->user_timezone),
            "end_date" => convertDateToUTCzone($end_date, $authUser->user_timezone),
            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
            "all_day" => (isset($request->all_day)) ? "yes" : "no",
            "event_description" => $request->description,
            "recuring_event" => "yes",
            "event_frequency" => $request->event_frequency,
            "event_interval_day" => $request->event_interval_day,
            "daily_weekname" => $request->daily_weekname,
            "event_interval_month" => $request->event_interval_month,
            "monthly_frequency" => $request->monthly_frequency,
            "event_interval_year" => $request->event_interval_year,
            "yearly_frequency" => $request->yearly_frequency,
            "no_end_date_checkbox" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
            "firm_id" => $authUser->firm_name,
            "created_by" => $authUser->id,
        ]);
        return $caseEvent;
    }

    /**
     * Save daily event data
     */
    public function saveDailyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $locationID)
    {
        $i = 0;
        /* // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => "no"
        ]); */
        Log::info("update old event end on date");
        // Delete next records from current event                        
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();
        // Create new events for new frequency
        $event_interval_day=$request->event_interval_day;
        $CaseEvent = '';
        do {
            $start_date = date("Y-m-d", $startDate);
            $end_date = date("Y-m-d", $startDate);
            $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $locationID);
            if($i==0) { 
                $parentCaseID=$CaseEvent->id;
                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                $CaseEvent->save();
            }else{
                $CaseEvent->parent_evnt_id =  $parentCaseID;
                $CaseEvent->save();
            }
            $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
            $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
            $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser);
            $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser); 
            
            $startDate = strtotime('+'.$event_interval_day.' day',$startDate); 
            $i++;
        } while ($startDate <= $endDate);
        return $CaseEvent;
    }

    /**
     * Save business day events
     */
    public function saveBusinessDayEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $locationID)
    {
        $i = 0;
        /* // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
        ]); */
        // Delete next records from current event
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();
        // Create new events for new frequency
        $elseFist = true; $currentI = 0;  
        $CaseEvent = '';
        do {
            $timestamp = $startDate;
            $weekday= date("l", $timestamp );            
            if ($weekday =="Saturday" OR $weekday =="Sunday") { 
            }else {
                if($elseFist) {
                    $elseFist = false;
                    $currentI = $i;
                }
                $start_date = date("Y-m-d", $startDate);
                $end_date = date("Y-m-d", $startDate);
                $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $locationID);
                if($i==$currentI) { 
                    $parentCaseID=$CaseEvent->id;
                    $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                    $CaseEvent->save();
                }else{
                    $CaseEvent->parent_evnt_id =  $parentCaseID;
                    $CaseEvent->save();
                }
                $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
                $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser); 

                // $this->saveEventHistory($CaseEvent->id);
            }
            $i++;
            $startDate = strtotime('+1 day',$startDate); 
        } while ($startDate <= $endDate);
        return $CaseEvent;
    }

    /**
     * Save weekly events
     */
    public function saveWeeklyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $locationID)
    {
        $i = 0;
        /* // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
        ]); */
        // Delete next records from current event                        
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();
        $CaseEvent = '';
        // Create new events for new frequency
        do {
            $start_date = date("Y-m-d", $startDate);
            $end_date = date("Y-m-d", $startDate);
            $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $locationID);
            if($i==0) { 
                $parentCaseID=$CaseEvent->id;
                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                $CaseEvent->save();
            }else{
                $CaseEvent->parent_evnt_id =  $parentCaseID;
                $CaseEvent->save();
            }
            $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
            $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
            $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
            $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser); 

            $i++;
            $startDate = strtotime('+7 day',$startDate); 
        } while ($startDate <= $endDate);
        return $CaseEvent;
    }

    /**
     * Save custom event
     */
    public function saveCustomEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $start, $startClone, $locationID)
    {
        $i = 0;
        /* // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
        ]); */
        // Delete next records from current event  
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();
        $CaseEvent = '';
        if(isset($request->end_on)) {
            $end=new DateTime($request->end_on);
        }else{
            $end=$startClone->add(new DateInterval('P365D'));
        }

        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);
        
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
            if(in_array($dayOfWeek,$request->custom)){

                $start_date = $date->format('Y-m-d');
                $end_date =$date->format('Y-m-d');
                $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $locationID);
                if($i==0) { 
                    $parentCaseID=$CaseEvent->id;
                    $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                    $CaseEvent->save();
                }else{
                    $CaseEvent->parent_evnt_id =  $parentCaseID;
                    $CaseEvent->save();
                }
                $i++;
                $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
                $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser); 
            }
        }
        return $CaseEvent;
    }

    /**
     * Save monthly event
     */
    public function saveMonthlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday, $locationID)
    {
        $i = 0;
        /* // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
        ]); */
        // Delete next records from current event                        
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();
        $CaseEvent = '';
        // Create new events for new frequency
        do {
            $monthly_frequency=$request->monthly_frequency;
            $event_interval_month=$request->event_interval_month;
            if($monthly_frequency=='MONTHLY_ON_DAY'){
                $startDate=$startDate;
            }else if($monthly_frequency=='MONTHLY_ON_THE'){
                $startDate = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startDate);
            }else if($monthly_frequency=='MONTHLY_ON_THE_LAST'){
                $startDate = strtotime("last ".strtolower($Currentweekday)." of this month",$startDate);
            }
            $start_date = date("Y-m-d", $startDate);
            $end_date = date("Y-m-d", $startDate);
            $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $locationID);
            if($i==0) { 
                $parentCaseID=$CaseEvent->id;
                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                $CaseEvent->save();
            }else{
                $CaseEvent->parent_evnt_id =  $parentCaseID;
                $CaseEvent->save();
            }
            $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
            $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
            $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
            $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser); 

            //  $this->saveEventHistory($CaseEvent->id);
            $startDate = strtotime('+'.$event_interval_month.' months',$startDate);
            $i++;
        } while ($startDate < $endDate);
        return $CaseEvent;
    }

    /**
     * Save yearly events
     */
    public function saveYearlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday, $locationID)
    {
        $i = 0;
        $yearly_frequency = $request->yearly_frequency;
        /* // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
        ]); */
        // Delete next records from current event                        
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();
        $CaseEvent = '';
        // Create new events for new frequency
        // $endDate =  strtotime(date('Y-m-d',strtotime('+25 years')));
        $endDate =  strtotime(date("Y-m-d", strtotime($startDate)) . " + 1 year");
        if($request->end_on!=''){
            $endDate =  strtotime(date('Y-m-d',strtotime($request->end_on)));
        }
        do {
            $event_interval_year=$request->event_interval_year;
            if($yearly_frequency=='YEARLY_ON_DAY'){
                $startDate=$startDate;
            }else if($yearly_frequency=='YEARLY_ON_THE'){
            $startDate = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startDate);
            }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                $startDate = strtotime("last ".strtolower($Currentweekday)." of this month",$startDate);
            }
            $start_date = date("Y-m-d", $startDate);
            $end_date = date("Y-m-d", $startDate);
            $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $locationID);
            if($i==0) { 
                $parentCaseID=$CaseEvent->id;
                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                $CaseEvent->save();
            }else{
                $CaseEvent->parent_evnt_id =  $parentCaseID;
                $CaseEvent->save();
            }
            $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
            $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
            $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
            $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser);
            
            $startDate = strtotime('+'.$event_interval_year.' years',$startDate);
            $i++;
        } while ($startDate < $endDate);
        return $CaseEvent;
    }

    /**
     * Save event reminders
     */
    public function saveEventReminder($request, $event_id, $authUser)
    {
        $reminders = CaseEventReminder::where("event_id", $event_id)->where("created_by", $authUser->id);
        if($reminders->get()) {
            // CaseEventReminder::where("event_id", $event_id)->where("created_by", auth()->id())->forceDelete();
            $reminders->forceDelete();
            if(count($request['reminder_user_type'])) {
                for($i=0;$i<count($request['reminder_user_type'])-1;$i++){
                    $CaseEventReminder = new CaseEventReminder();
                    $CaseEventReminder->event_id=$event_id; 
                    $CaseEventReminder->reminder_type=$request['reminder_type'][$i];
                    $CaseEventReminder->reminer_number=$request['reminder_number'][$i];
                    $CaseEventReminder->reminder_frequncy=$request['reminder_time_unit'][$i];
                    $CaseEventReminder->reminder_user_type=$request['reminder_user_type'][$i];
                    $CaseEventReminder->created_by = $authUser->id; 
                    $CaseEventReminder->remind_at=Carbon::now(); 
                    $CaseEventReminder->save();
                }
            }
        }
    }

    /**
     * Save linked staff to event
     */
    public function saveLinkedStaffToEvent($request, $event_id, $authUser)
    {
        $linkedUser = CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by",  $authUser->id)->where("is_linked","yes");
        if($linkedUser->get()) {
            $oldRecord = CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by",  $authUser->id)->where("is_linked","yes")->first();
            $lastCommentReadAt = $oldRecord->comment_read_at ?? Carbon::now();
            // CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by",  auth()->id())->where("is_linked","yes")->forceDelete();
            $linkedUser->forceDelete();
            if(isset($request['linked_staff_checked_share']) && count($request['linked_staff_checked_share'])) {
                $alreadyAdded=[];
                for($i=0;$i<count($request['linked_staff_checked_share']);$i++) {
                    $CaseEventLinkedStaff = new CaseEventLinkedStaff;
                    $CaseEventLinkedStaff->event_id=$event_id; 
                    $CaseEventLinkedStaff->user_id=$request['linked_staff_checked_share'][$i];
                    $attend = "no";
                    if(isset($request['linked_staff_checked_attend']) && in_array($request['linked_staff_checked_share'][$i], $request['linked_staff_checked_attend'])){
                        $attend = "yes";
                    }
                    // dd($attend);
                    $CaseEventLinkedStaff->is_linked='yes';
                    $CaseEventLinkedStaff->attending=$attend;
                    $CaseEventLinkedStaff->comment_read_at = $lastCommentReadAt;
                    $CaseEventLinkedStaff->created_by= $authUser->id; 
                    if(!in_array($request['linked_staff_checked_share'][$i],$alreadyAdded)){
                        $CaseEventLinkedStaff->save();
                    }
                    $alreadyAdded[]=$request['linked_staff_checked_share'][$i];
                }
            }
        }
    }

    /**
     * Save non linked staff to event
     */
    public function saveNonLinkedStaffToEvent($request,$event_id, $authUser)
    {
        $linkedUser = CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by", $authUser->id)->where("is_linked","no");
        if($linkedUser->get()) {
            // CaseEventLinkedStaff::where("event_id", $event_id)->where("created_by", $authUser->id)->where("is_linked","no")->forceDelete();
            $linkedUser->forceDelete();
            if(isset($request['share_checkbox_nonlinked']) && count($request['share_checkbox_nonlinked'])) {
                $alreadyAdded = $attend_checkbox_nonlinked = [];
                if(isset($request['attend_checkbox_nonlinked'])){
                    for($i=0;$i<count(array_unique($request['attend_checkbox_nonlinked']));$i++){                
                        array_push($attend_checkbox_nonlinked, $request['attend_checkbox_nonlinked'][$i]);
                    }            
                }
                for($i=0;$i<count(array_unique($request['share_checkbox_nonlinked']));$i++){
                    $CaseEventLinkedStaff = new CaseEventLinkedStaff;
                    $CaseEventLinkedStaff->event_id=$event_id; 
                    $CaseEventLinkedStaff->user_id=$request['share_checkbox_nonlinked'][$i];
                    $attend="no";
                    if(isset($request['share_checkbox_nonlinked'][$i])){
                        if(in_array($request['share_checkbox_nonlinked'][$i], $attend_checkbox_nonlinked)){
                            $attend="yes";
                        }
                    }                     
                    $CaseEventLinkedStaff->is_linked='no';
                    $CaseEventLinkedStaff->attending=$attend;
                    $CaseEventLinkedStaff->created_by = $authUser->id; 
                    if(!in_array($request['share_checkbox_nonlinked'][$i],$alreadyAdded)){
                        $CaseEventLinkedStaff->save();
                    }
                    $alreadyAdded[]=$request['share_checkbox_nonlinked'][$i];
                }
            }
        }
    }

    /**
     * Save linked contact/lead to event
     */
    public function saveContactLeadData($request, $event_id, $authUser)
    {
        $linkedUser = CaseEventLinkedContactLead::where("event_id", $event_id)->where("created_by",  $authUser->id);
        if($linkedUser->get()) {
            // CaseEventLinkedContactLead::where("event_id", $event_id)->where("created_by",  $authUser->id)->forceDelete();
            $linkedUser->forceDelete();
            if(isset($request['LeadInviteClientCheckbox']) && count($request['LeadInviteClientCheckbox'])){
                $alreadyAdded=$attend_checkbox_nonlinked = [];
                if(isset($request['LeadAttendClientCheckbox'])){
                    for($i=0;$i<count(array_unique($request['LeadAttendClientCheckbox']));$i++){                
                        array_push($attend_checkbox_nonlinked, $request['LeadAttendClientCheckbox'][$i]);
                    } 
                }                
                for($i=0;$i<count(array_unique($request['LeadInviteClientCheckbox']));$i++){
                    $CaseEventLinkedContactLead = new CaseEventLinkedContactLead;
                    $CaseEventLinkedContactLead->event_id=$event_id; 
                    $CaseEventLinkedContactLead->user_type='lead'; 
                    $CaseEventLinkedContactLead->lead_id=$request['LeadInviteClientCheckbox'][$i];
                    $attend="no";
                    if(isset($request['LeadInviteClientCheckbox'][$i])){
                        if(in_array($request['LeadInviteClientCheckbox'][$i], $attend_checkbox_nonlinked)){
                            $attend="yes";
                        }
                    }                    
                    $CaseEventLinkedContactLead->attending=$attend;
                    $CaseEventLinkedContactLead->invite="yes";
                    $CaseEventLinkedContactLead->created_by= $authUser->id; 
                    if(!in_array($request['LeadInviteClientCheckbox'][$i],$alreadyAdded)){
                        $CaseEventLinkedContactLead->save();
                    }
                    //    print_r(CaseEventLinkedContactLead);
                    $alreadyAdded[]=$request['LeadInviteClientCheckbox'][$i];
                }
            }else if(isset($request['ContactInviteClientCheckbox']) && count($request['ContactInviteClientCheckbox'])){
                $alreadyAdded=$attend_checkbox_nonlinked = [];
                if(isset($request['ContactAttendClientCheckbox'])){
                    for($i=0;$i<count(array_unique($request['ContactAttendClientCheckbox']));$i++){                
                        array_push($attend_checkbox_nonlinked, $request['ContactAttendClientCheckbox'][$i]);
                    } 
                }
                for($i=0;$i<count(array_unique($request['ContactInviteClientCheckbox']));$i++){
                    $CaseEventLinkedContactLead = new CaseEventLinkedContactLead;
                    $CaseEventLinkedContactLead->event_id=$event_id; 
                    $CaseEventLinkedContactLead->user_type='contact'; 
                    $CaseEventLinkedContactLead->contact_id=$request['ContactInviteClientCheckbox'][$i];
                    $attend="no";
                    if(isset($request['ContactInviteClientCheckbox'][$i])){
                        if(in_array($request['ContactInviteClientCheckbox'][$i], $attend_checkbox_nonlinked)){
                            $attend="yes";
                        }
                    }
                    $CaseEventLinkedContactLead->attending=$attend;
                    $CaseEventLinkedContactLead->invite="yes";
                    $CaseEventLinkedContactLead->created_by= $authUser->id; 
                    if(!in_array($request['ContactInviteClientCheckbox'][$i],$alreadyAdded)){
                        $CaseEventLinkedContactLead->save();
                    }
                    $alreadyAdded[]=$request['ContactInviteClientCheckbox'][$i];
                }
            }
        }
    }

    /**
     * Save event history
     */
    public function saveEventHistory($request, $authUser)
    {
        Log::info("Trait Auth user: ".@$authUser);
        $CaseEventComment =new CaseEventComment();
        $CaseEventComment->event_id=$request;
        $CaseEventComment->comment=NULL;
        $CaseEventComment->created_by = @$authUser->id; 
        $CaseEventComment->action_type="1";
        $CaseEventComment->save();
    }

    /**
     * Update/create recurring event
     */
    public function updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $oldCaseEvent, $currentEventId = null, $locationID)
    {
        $caseEvent = CaseEvent::withTrashed()->updateOrCreate([
            "start_date" => convertDateToUTCzone($start_date, $authUser->user_timezone),
            "parent_evnt_id" => $oldCaseEvent->parent_evnt_id,
        ], [
            "event_title" => $request->event_name,
            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
            "event_type" => $request->event_type ?? NULL,
            // "start_date" => $start_date,
            // "end_date" => $end_date,
            "start_date" => convertDateToUTCzone($start_date, $authUser->user_timezone),
            "end_date" => convertDateToUTCzone($end_date, $authUser->user_timezone),
            "start_time" => (isset($request->start_time) && !isset($request->all_day)) ? $start_time : NULL,
            "end_time" => (isset($request->end_time) && !isset($request->all_day)) ? $end_time : NULL,
            "all_day" => (isset($request->all_day)) ? "yes" : "no",
            "event_description" => $request->description,
            "recuring_event" => "yes",
            "event_frequency" => $request->event_frequency,
            "event_interval_day" => $request->event_interval_day,
            "daily_weekname" => $request->daily_weekname,
            "event_interval_month" => $request->event_interval_month,
            "monthly_frequency" => $request->monthly_frequency,
            "event_interval_year" => $request->event_interval_year,
            "yearly_frequency" => $request->yearly_frequency,
            "no_end_date_checkbox" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
            "firm_id" => $authUser->firm_name,
            "updated_by" => $authUser->id,
            "created_by" => $oldCaseEvent->created_by,
            "created_at" => $oldCaseEvent->created_at,
            "parent_evnt_id" => $oldCaseEvent->parent_evnt_id,
        ]);
        return $caseEvent;
    }

    public function addCaseEventActivity($request, $CaseEvent, $authUser, $activity = 'added'){
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
        $data['event_id']=$CaseEvent->id;
        $data['event_name']=$CaseEvent->event_title;
        $data['user_id']=$authUser->id;
        $data['activity'] = ($activity == 'updated') ? 'updated event' : 'added event';
        $data['type']='event';
        $data['action']='add';        

        $this->addEventHistory($data, $authUser);

        // For client recent activity
        if($CaseEvent->eventLinkedContact) {
            foreach($CaseEvent->eventLinkedContact as $key => $item) {
                $data['user_id'] = $item->id;
                $data['client_id'] = $item->id;
                $data['is_for_client'] = 'yes';
                $this->addEventHistory($data, $authUser);
            }
        }
    }

    /**
     * Add event history to all history table
     */
    public function addEventHistory($data, $authUser)
    {
        $AllHistory=new AllHistory;
        $AllHistory->case_id=($data['case_id'])??NULL;
        $AllHistory->user_id=($data['user_id'])??NULL;
        $AllHistory->expense_id=($data['expense_id'])??NULL;
        $AllHistory->time_entry_id=($data['time_entry_id'])??NULL;
        $AllHistory->activity=($data['activity'])??NULL;
        $AllHistory->activity_for=($data['activity_for'])??NULL;
        $AllHistory->notes_for_client=($data['notes_for_client'])??NULL;
        $AllHistory->notes_for_company=($data['notes_for_company'])??NULL;
        $AllHistory->notes_for_case=($data['notes_for_case'])??NULL;
        $AllHistory->event_for_case=($data['event_for_case'])??NULL;
        $AllHistory->event_for_lead=($data['event_for_lead'])??NULL;
        $AllHistory->event_id=($data['event_id'])??NULL;
        $AllHistory->event_name=($data['event_name'])??NULL;
        $AllHistory->task_for_case=($data['task_for_case'])??NULL;
        $AllHistory->task_for_lead=($data['task_for_lead'])??NULL;
        $AllHistory->task_id=($data['task_id'])??NULL;
        $AllHistory->task_name=($data['task_name'])??NULL;
        $AllHistory->deposit_id=($data['deposit_id'])??NULL;
        $AllHistory->deposit_for=($data['deposit_for'])??NULL;
        $AllHistory->type=($data['type'])??NULL;
        $AllHistory->action=($data['action'])??NULL;
        $AllHistory->client_id=($data['client_id'])??NULL;
        $AllHistory->is_for_client=($data['is_for_client'])??'no';
        $AllHistory->firm_id=$authUser->firm_name;
        $AllHistory->created_by=$authUser->id;
        $AllHistory->created_at=date('Y-m-d H:i:s');  
        $AllHistory->save();
    }

    
    /**
     * Update monthly/yearly recurring event with diff frequency
     */
    public function updateMonthlyYearlyRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $oldCaseEvent, $locationID, $oldCaseEventId)
    {
        $caseEvent = CaseEvent::withTrashed()->updateOrCreate([
            "id" => $oldCaseEventId,
        ], [
            "event_title" => $request->event_name,
            "case_id" => (!isset($request->no_case_link) && $request->text_case_id!='') ? $request->text_case_id : NULL,
            "lead_id" => (!isset($request->no_case_link) && $request->text_lead_id!='') ? $request->text_lead_id : NULL,
            "event_type" => $request->event_type ?? NULL,
            "start_date" => $start_date,
            "end_date" => $end_date,
            "start_time" => ($request->start_time && !isset($request->all_day)) ? $start_time : NULL,
            "end_time" => ($request->end_time && !isset($request->all_day)) ? $end_time : NULL,
            "all_day" => (isset($request->all_day)) ? "yes" : "no",
            "event_description" => $request->description,
            "recuring_event" => "yes",
            "event_frequency" => $request->event_frequency,
            "event_interval_day" => $request->event_interval_day,
            "daily_weekname" => $request->daily_weekname,
            "event_interval_month" => $request->event_interval_month,
            "monthly_frequency" => $request->monthly_frequency,
            "event_interval_year" => $request->event_interval_year,
            "yearly_frequency" => $request->yearly_frequency,
            "no_end_date_checkbox" => (isset($request->no_end_date_checkbox)) ? "yes" : "no",
            "end_on" => (!isset($request->no_end_date_checkbox) && $request->end_on) ? date("Y-m-d",strtotime($request->end_on)) : NULL,
            "event_location_id" => ($request->case_location_list) ? $request->case_location_list : $locationID ?? NULL,
            "is_event_private" => (isset($request->is_event_private)) ? 'yes' : 'no',
            "firm_id" => $authUser->firm_name,
            "updated_by" => $authUser->id,
            "created_by" => $oldCaseEvent->created_by,
            "created_at" => $oldCaseEvent->created_at,
            "parent_evnt_id" => $oldCaseEvent->parent_evnt_id,
        ]);
        return $caseEvent;
    }
}
 