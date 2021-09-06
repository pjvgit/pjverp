<?php

namespace App\Jobs;

use App\CaseEvent;
use App\Traits\CaseEventTrait;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CaseAllEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use CaseEventTrait;
    protected $requestData, $startDate, $endDate, $start_time, $end_time, $authUser, $locationID;
    public $tries = 5;
    public $timeout = 240;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $requestData, $startDate, $endDate, $start_time, $end_time, $authUser, $locationID)
    {
        $this->requestData = $requestData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->authUser = $authUser;
        $this->locationID = $locationID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Case ALL Event Job Started :". date('Y-m-d H:i:s'));
        $request = (object) $this->requestData;
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        // $startDate = strtotime(convertDateToUTCzone(date("Y-m-d", $this->startDate), $this->authUser->user_timezone));
        // $endDate = strtotime(convertDateToUTCzone(date("Y-m-d", $this->endDate), $this->authUser->user_timezone));
        $start_time = $this->start_time;
        $end_time = $this->end_time;
        $authUser = $this->authUser;
        $locationID = $this->locationID;
        if($request->event_frequency=='DAILY')
        {
            $OldCaseEvent=CaseEvent::find($request->event_id);
            $oldFirstEvent = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('start_date','asc')->first();
            $startDate = strtotime($oldFirstEvent->start_date);
            $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
            $endDate =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
            if(isset($request->end_on)) {
                $endDate = strtotime($request->end_on);
            }
            $i=0;
            $event_interval_day=$request->event_interval_day;
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                if($OldCaseEvent->event_frequency == 'YEARLY') {
                    $endDateNew =  strtotime('+ 1 year', $startDate);
                    if($endDate < $endDateNew) {
                        $endDate = $endDateNew;
                    }
                    if(isset($request->end_on)) {
                        $endDate = strtotime($request->end_on);
                    }
                }
                // Delete old records
                $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
                $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                $oldEvents->forceDelete();
                // Create new events for new frequency
                $event_interval_day=$request->event_interval_day;
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
                    $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser);
                    $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
        
                    // $this->saveEventHistory($CaseEvent->id, $authUser);
                    
                    $startDate = strtotime('+'.$event_interval_day.' day',$startDate); 
                    $i++;
                } while ($startDate < $endDate);
            } else {
                do {
                    $start_date = date("Y-m-d",$startDate);
                    $end_date = $start_date;
                    $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, null, $locationID);

                    $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser);                               
                    $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser);
                    $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                    // $this->saveEventHistory($CaseEvent->id, $authUser);
                    $startDate = strtotime('+'.$event_interval_day.' day',$startDate); 
                    $i++;
                } while ($startDate <= $endDate);
            }
        }else if($request->event_frequency=='EVERY_BUSINESS_DAY')
        { 
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            $oldFirstEvent = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('start_date','asc')->first();
            $startDate = strtotime($oldFirstEvent->start_date);
            $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
            $endDate =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
            if(isset($request->end_on)) {
                $endDate = strtotime($request->end_on);
            }
            
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                if($OldCaseEvent->event_frequency == 'YEARLY') {
                    $endDateNew =  strtotime('+ 1 year', $startDate);
                    if($endDate < $endDateNew) {
                        $endDate = $endDateNew;
                    }
                    if(isset($request->end_on)) {
                        $endDate = strtotime($request->end_on);
                    }
                }
                // Delete old records
                $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
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
                        $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $locationID);
                        if($i==$currentI) { 
                            $parentCaseID=$CaseEvent->id;
                            $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                            $CaseEvent->save();
                        }else{
                            $CaseEvent->parent_evnt_id =  $parentCaseID;
                            $CaseEvent->save();
                        }
                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 

                        // $this->saveEventHistory($CaseEvent->id, $authUser);
                    }
                    $i++;
                    $startDate = strtotime('+1 day',$startDate); 
                } while ($startDate < $endDate);

            } else {    
                do {
                    $timestamp = $startDate;
                    $weekday= date("l", $timestamp );    
                    if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                    }else {
                        $start_date = date("Y-m-d", $startDate);
                        $end_date = date("Y-m-d", $startDate);
                        $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, null, $locationID);

                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        // $this->saveEventHistory($CaseEvent->id, $authUser);

                    }
                    $i++;
                    $startDate = strtotime('+1 day',$startDate); 
                } while ($startDate <= $endDate);
            }                
        } else if($request->event_frequency=='WEEKLY') {
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            $oldFirstEvent = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('start_date','asc')->first();
            $startDate = strtotime($oldFirstEvent->start_date);
            $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
            $endDate =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
            if(isset($request->end_on)) {
                $endDate = strtotime($request->end_on);
            }                    
            
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                if($OldCaseEvent->event_frequency == 'YEARLY') {
                    $endDateNew =  strtotime('+ 1 year', $startDate);
                    if($endDate < $endDateNew) {
                        $endDate = $endDateNew;
                    }
                    if(isset($request->end_on)) {
                        $endDate = strtotime($request->end_on);
                    }
                }
                // Delete next records from current event                        
                $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
                $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                $oldEvents->forceDelete();
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
                    $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 

                    $i++;
                    $startDate = strtotime('+7 day',$startDate); 
                } while ($startDate <= $endDate);
            } else {
                do {
                    $start_date = date("Y-m-d", $startDate);
                    $end_date = date("Y-m-d", $startDate);
                    $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, null, $locationID);

                    $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                    // $this->saveEventHistory($CaseEvent->id, $authUser);

                    $startDate = strtotime('+7 day',$startDate); 
                    $i++;
                } while ($startDate <= $endDate);
            }
        }else if($request->event_frequency=='CUSTOM')
        {
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            $oldFirstEvent = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('start_date','asc')->first();
            $startDate = strtotime($oldFirstEvent->start_date);
            $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
            $endDate =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
            $start = new DateTime(date("Y-m-d", $startDate));
            $startClone = new DateTime(date("Y-m-d", $startDate));
            if(isset($request->end_on)) {
                $end=new DateTime($request->end_on);
            }else{
                $end=$startClone->add(new DateInterval('P365D'));
            }                
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                if($OldCaseEvent->event_frequency == 'YEARLY') {
                    $endDateNew =  strtotime('+ 1 year', $startDate);
                    if($endDate < $endDateNew) {
                        $end = new DateTime($endDateNew);
                    }
                    if(isset($request->end_on)) {
                        $end=new DateTime($request->end_on);
                    }
                }
                // Delete next records from current event  
                $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
                $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                $oldEvents->forceDelete();

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
                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        // $this->saveEventHistory($CaseEvent->id, $authUser);
                    }
                }
            } else {
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
                        $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, null, $locationID);
                        $i++;
                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        // $this->saveEventHistory($CaseEvent->id, $authUser);
                    }
                }
            }
        }else if($request->event_frequency=='MONTHLY')
        { 
            $Currentweekday= date("l", $startDate ); 
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
            $endDate =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
            if(isset($request->end_on)) {
                $endDate = strtotime($request->end_on);
            }

            
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                if($OldCaseEvent->event_frequency == 'YEARLY') {
                    $endDateNew =  strtotime('+ 1 year', $startDate);
                    if($endDate < $endDateNew) {
                        $endDate = $endDateNew;
                    }
                    if(isset($request->end_on)) {
                        $endDate = strtotime($request->end_on);
                    }
                }
                // Delete next records from current event                        
                $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
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
                    $CaseEvent = $this->saveRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $locationID);
                    if($i==0) { 
                        $parentCaseID=$CaseEvent->id;
                        $CaseEvent->parent_evnt_id =  $CaseEvent->id; 
                        $CaseEvent->save();
                    }else{
                        $CaseEvent->parent_evnt_id =  $parentCaseID;
                        $CaseEvent->save();
                    }
                    $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 

                    //  $this->saveEventHistory($CaseEvent->id, $authUser);
                    $startDate = strtotime('+'.$event_interval_month.' months',$startDate);
                    $i++;
                } while ($startDate < $endDate);
            } else {
                $oldFirstEvent = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('start_date','asc')->first();
                $startDate = strtotime($oldFirstEvent->start_date);
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
                    $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, null, $locationID);

                    $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser);
                    $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                    // $this->saveEventHistory($CaseEvent->id, $authUser);

                    
                    $startDate = strtotime('+'.$event_interval_month.' months',$startDate);
                    $i++;
                } while ($startDate < $endDate);
            }
        }else if($request->event_frequency=='YEARLY') { 
            // $endDate =  strtotime(date('Y-m-d',strtotime('+25 years')));
            $yearly_frequency=$request->yearly_frequency;
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            $Currentweekday= date("l", $startDate ); 

            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                // Delete next records from current event                        
                $oldEvents = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id);
                $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                $oldEvents->forceDelete();

                // Create new events for new frequency
                if($yearly_frequency=='YEARLY_ON_DAY'){
                    $startDate=$startDate;
                }else if($yearly_frequency=='YEARLY_ON_THE'){
                $startDate = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startDate);
                }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                    $startDate = strtotime("last ".strtolower($Currentweekday)." of this month",$startDate);
                }
                $endDate =  strtotime('+ 1 year', $startDate);
                if(isset($request->end_on)) {
                    $endDate = strtotime($request->end_on);
                }
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
                    $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 

                    // $this->saveEventHistory($CaseEvent->id, $authUser);

                    $startDate = strtotime('+'.$request->event_interval_year.' years',$startDate);
                    $i++;
                } while ($startDate <= $endDate);

            } else {
                $oldFirstEvent = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('start_date','asc')->first();
                $startDate = strtotime($oldFirstEvent->start_date);
                $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                $endDate =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                if(isset($request->end_on)) {
                    $endDate = strtotime($request->end_on);
                }

                if($yearly_frequency=='YEARLY_ON_DAY'){
                    $startDate=$startDate;
                }else if($yearly_frequency=='YEARLY_ON_THE'){
                    $startDate = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startDate);
                }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                    $startDate = strtotime("last ".strtolower($Currentweekday)." of this month",$startDate);
                }
                do {
                    $start_date = date("Y-m-d", $startDate);
                    $end_date = date("Y-m-d", $startDate);
                    $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, null, $locationID);

                    $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                    $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                    // $this->saveEventHistory($CaseEvent->id, $authUser);
                    
                    $startDate = strtotime('+'.$request->event_interval_year.' years',$startDate);
                    $i++;
                } while ($startDate <= $endDate);
            }
        }

        Log::info("Case ALL Event Job Ended :". date('Y-m-d H:i:s'));
    }

    /**
     * Update recurring event
     */
    public function updateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $oldCaseEvent)
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
            "updated_by" => $authUser->id,
            "created_by" => $oldCaseEvent->created_by,
            "created_at" => $oldCaseEvent->created_at,
            "parent_evnt_id" => $oldCaseEvent->parent_evnt_id,
        ]);
        return $caseEvent;
    }
}


