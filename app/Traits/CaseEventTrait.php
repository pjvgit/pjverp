<?php
 
namespace App\Traits;

use App\CaseEvent;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;

trait CaseEventTrait {
    /**
     * Save recurring event data
     */
    public function saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser)
    {
        $caseEvent = CaseEvent::create([
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
            "created_by" => $authUser->id,
        ]);
        return $caseEvent;
    }

    /**
     * Save daily event data
     */
    public function saveDailyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser)
    {
        $i = 0;
        // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
        ]);
        // Delete next records from current event                        
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();
        // Create new events for new frequency
        $event_interval_day=$request->event_interval_day;
        do {
            $start_date = date("Y-m-d", $startDate);
            $end_date = date("Y-m-d", $startDate);
            $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
            if($i==0) { 
                $parentCaseID=$CaseEvent->id;
                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                $CaseEvent->save();
            }else{
                $CaseEvent->parent_evnt_id =  $parentCaseID;
                $CaseEvent->save();
            }
            $this->saveEventReminder($request->all(),$CaseEvent->id); 
            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id);
            $this->saveContactLeadData($request->all(),$CaseEvent->id); 
            
            $startDate = strtotime('+'.$event_interval_day.' day',$startDate); 
            $i++;
        } while ($startDate <= $endDate);
    }

    /**
     * Save business day events
     */
    public function saveBusinessDayEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser)
    {
        $i = 0;
        // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
        ]);
        // Delete next records from current event
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();
        // Create new events for new frequency
        $elseFist = true; $currentI = 0;  
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
                $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
                if($i==$currentI) { 
                    $parentCaseID=$CaseEvent->id;
                    $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                    $CaseEvent->save();
                }else{
                    $CaseEvent->parent_evnt_id =  $parentCaseID;
                    $CaseEvent->save();
                }
                $this->saveEventReminder($request->all(),$CaseEvent->id); 
                $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                // $this->saveEventHistory($CaseEvent->id);
            }
            $i++;
            $startDate = strtotime('+1 day',$startDate); 
        } while ($startDate <= $endDate);
    }

    /**
     * Save weekly events
     */
    public function saveWeeklyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser)
    {
        $i = 0;
        // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
        ]);
        // Delete next records from current event                        
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();
        // Create new events for new frequency
        do {
            $start_date = date("Y-m-d", $startDate);
            $end_date = date("Y-m-d", $startDate);
            $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
            if($i==0) { 
                $parentCaseID=$CaseEvent->id;
                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                $CaseEvent->save();
            }else{
                $CaseEvent->parent_evnt_id =  $parentCaseID;
                $CaseEvent->save();
            }
            $this->saveEventReminder($request->all(),$CaseEvent->id); 
            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveContactLeadData($request->all(),$CaseEvent->id); 

            $i++;
            $startDate = strtotime('+7 day',$startDate); 
        } while ($startDate <= $endDate);
    }

    /**
     * Save custom event
     */
    public function saveCustomEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $start, $startClone)
    {
        $i = 0;
        // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
        ]);
        // Delete next records from current event  
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();

        if($request->end_on!=''){
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
                $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
                if($i==0) { 
                    $parentCaseID=$CaseEvent->id;
                    $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                    $CaseEvent->save();
                }else{
                    $CaseEvent->parent_evnt_id =  $parentCaseID;
                    $CaseEvent->save();
                }
                $i++;
                $this->saveEventReminder($request->all(),$CaseEvent->id); 
                $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveContactLeadData($request->all(),$CaseEvent->id); 
            }
        }
    }

    /**
     * Save monthly event
     */
    public function saveMonthlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday)
    {
        $i = 0;
        // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
        ]);
        // Delete next records from current event                        
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();
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
            $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
            if($i==0) { 
                $parentCaseID=$CaseEvent->id;
                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                $CaseEvent->save();
            }else{
                $CaseEvent->parent_evnt_id =  $parentCaseID;
                $CaseEvent->save();
            }
            $this->saveEventReminder($request->all(),$CaseEvent->id); 
            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveContactLeadData($request->all(),$CaseEvent->id); 

            //  $this->saveEventHistory($CaseEvent->id);
            $startDate = strtotime('+'.$event_interval_month.' months',$startDate);
            $i++;
        } while ($startDate < $endDate);
    }

    /**
     * Save yearly events
     */
    public function saveYearlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday)
    {
        $i = 0;
        $yearly_frequency = $request->yearly_frequency;
        // Update previous record's (from current event) end date
        CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
            "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
            "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
        ]);
        // Delete next records from current event                        
        $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id);
        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
        $oldEvents->forceDelete();
        // Create new events for new frequency
        $endDate =  strtotime(date('Y-m-d',strtotime('+25 years')));
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
            $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser);
            if($i==0) { 
                $parentCaseID=$CaseEvent->id;
                $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                $CaseEvent->save();
            }else{
                $CaseEvent->parent_evnt_id =  $parentCaseID;
                $CaseEvent->save();
            }
            $this->saveEventReminder($request->all(),$CaseEvent->id); 
            $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
            $this->saveContactLeadData($request->all(),$CaseEvent->id);
            
            $startDate = strtotime('+'.$event_interval_year.' years',$startDate);
            $i++;
        } while ($startDate < $endDate);
    }
}
 