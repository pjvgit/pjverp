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

class CaseFollowingEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use CaseEventTrait;
    protected $requestData, $startDate, $endDate, $start_time, $end_time, $authUser;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $requestData, $startDate, $endDate, $start_time, $end_time, $authUser)
    {
        $this->requestData = $requestData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->authUser = $authUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = (object) $this->requestData;
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $start_time = $this->start_time;
        $end_time = $this->end_time;
        $authUser = $this->authUser;
        if($request->event_frequency=='DAILY')
        {
            $i=0;
            $event_interval_day = $request->event_interval_day;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                $this->saveDailyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser);                        
            } else {
                $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                $endDate =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                if(isset($request->end_on)) {
                    $endDate =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                // Update previous record's (from current event) end date
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
                    "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
                    "no_end_date_checkbox" => "no"
                ]);
                if($startDate == strtotime($OldCaseEvent->start_date)) {
                    Log::info("Daily date matched");
                    do {
                        $start_date = date("Y-m-d", $startDate);
                        $end_date = date("Y-m-d", $startDate);
                        $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent);

                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser);   
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser);
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        Log::info("Job Auth user: ".@$authUser);
                        $this->saveEventHistory($CaseEvent->id, $authUser);

                        $startDate = strtotime('+'.$event_interval_day.' day',$startDate); 
                        $i++;
                    } while ($startDate <= $endDate);
                } else {
                    Log::info("daily date not matched");
                    $this->saveDailyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser);
                }
            }
            
        } else if($request->event_frequency=='EVERY_BUSINESS_DAY') { 
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);

            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                $this->saveBusinessDayEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser);
            } else {
                // Update existing event with trashed and same event frequency
                $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                $endDate =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                if(isset($request->end_on)) {
                    $endDate =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                // Update previous record's (from current event) end date
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
                    "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
                    "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
                ]);
                if($startDate == strtotime($OldCaseEvent->start_date)) {
                    do {
                        $timestamp = $startDate;
                        $weekday= date("l", $timestamp );            
                        if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                        }else {
                            $start_date = date("Y-m-d", $startDate);
                            $end_date = date("Y-m-d", $startDate);
                            $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent);
                        
                            $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveEventHistory($CaseEvent->id, $authUser);
                        }
                        $i++;
                        $startDate = strtotime('+1 day',$startDate); 
                    } while ($startDate <= $endDate);
                } else {
                    $this->saveBusinessDayEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser);
                }
            }
                
        } else if($request->event_frequency=='WEEKLY') {
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                $this->saveWeeklyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser);
            } else {
                $Edate=CaseEvent::where('parent_evnt_id', $OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                $endDate =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                if(isset($request->end_on)) {
                    $endDate =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                // Update previous record's (from current event) end date
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
                    "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
                    "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
                ]);
                if($startDate == strtotime($OldCaseEvent->start_date)) {
                    do {
                        $start_date = date("Y-m-d", $startDate);
                        $end_date = date("Y-m-d", $startDate);
                        $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent);
                        
                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveEventHistory($CaseEvent->id, $authUser);
                        $i++;
                        $startDate = strtotime('+7 day',$startDate); 
                    } while ($startDate <= $endDate);
                } else {
                    $this->saveWeeklyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser);
                }
            }
        }else if($request->event_frequency=='CUSTOM') { 
            $start = new DateTime(date("Y-m-d", $startDate));
            $startClone = new DateTime(date("Y-m-d", $startDate));
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                $this->saveCustomEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $start, $startClone);
            } else {
                $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                if($request->end_on!=''){
                    $end = new DateTime($request->end_on);
                }else{
                    $end =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                }
                // Update previous record's (from current event) end date
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
                    "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
                    "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
                ]);
                if($startDate == strtotime($OldCaseEvent->start_date)) {
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
                            $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent);
                        
                            $i++;
                            $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveEventHistory($CaseEvent->id, $authUser);
                        }
                    }
                } else {
                    $this->saveCustomEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $start, $startClone);
                }
            }
        } else if($request->event_frequency=='MONTHLY') { 
            $Currentweekday= date("l", $startDate ); 
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                $this->saveMonthlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday);
            } else {
                $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                $endDate =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                if(isset($request->end_on)) {
                    $endDate =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                // Update previous record's (from current event) end date
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
                    "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
                    "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
                ]);
                if($startDate == strtotime($OldCaseEvent->start_date)) {
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
                        $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent);
                        
                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser);
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveEventHistory($CaseEvent->id, $authUser);

                        $startDate = strtotime('+'.$event_interval_month.' months',$startDate);
                        $i++;
                    } while ($startDate < $endDate);
                } else {
                    $this->saveMonthlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday);
                }
            }
        } else if($request->event_frequency=='YEARLY') { 
            $yearly_frequency=$request->yearly_frequency;
            $Currentweekday= date("l", $startDate ); 
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                $this->saveYearlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday);             
            } else {
                $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                $endDate =  strtotime(date('Y-m-d',strtotime($Edate['end_date'])));
                if(isset($request->end_on)) {
                    $endDate =  strtotime(date('Y-m-d',strtotime($request->end_on)));
                }
                // Update previous record's (from current event) end date
                CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',"<=",$OldCaseEvent->id)->update([
                    "end_on" => Carbon::parse($startDate)->subDay()->format("Y-m-d"),
                    "no_end_date_checkbox" => $OldCaseEvent->no_end_date_checkbox
                ]);
                if($startDate == strtotime($OldCaseEvent->start_date)) {
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
                        $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent);
                    
                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveEventHistory($CaseEvent->id, $authUser);

                        $startDate = strtotime('+'.$event_interval_year.' years',$startDate);
                        $i++;
                    } while ($startDate < $endDate);
                } else {
                    $this->saveYearlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday);
                }
            }
        }
    }
}
