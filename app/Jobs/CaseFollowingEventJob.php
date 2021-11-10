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
        Log::info("Case Following Event Job Started :". date('Y-m-d H:i:s'));
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
            $i=0;
            $event_interval_day = $request->event_interval_day;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                Log::info("saveDailyEvent ");
                $this->saveDailyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $locationID);                        
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
                        Log::info("start_date : ".$startDate." <=  End date :".$endDate. " with request :". json_encode($request));
                        $start_date = date("Y-m-d", $startDate);
                        $end_date = date("Y-m-d", $startDate);
                        $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, null, $locationID);

                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser);   
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser);
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        // Log::info("Job Auth user: ".@$authUser);
                        // $this->saveEventHistory($CaseEvent->id, $authUser);

                        $startDate = strtotime('+'.$event_interval_day.' day',$startDate); 
                        $i++;
                    } while ($startDate <= $endDate);
                } else {
                    Log::info("daily date not matched");
                    $this->saveDailyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $locationID);
                }
            }
            
        } else if($request->event_frequency=='EVERY_BUSINESS_DAY') { 
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                $this->saveBusinessDayEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $locationID);
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
                            $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, null, $locationID);
                        
                            $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                            // $this->saveEventHistory($CaseEvent->id, $authUser);
                        }
                        $i++;
                        $startDate = strtotime('+1 day',$startDate); 
                        // dd(date('Y-m-d', $startDate));
                    } while ($startDate <= $endDate);
                } else {
                    // dd('date not match');
                    $this->saveBusinessDayEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $locationID);
                }
            }
                
        } else if($request->event_frequency=='WEEKLY') {
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                $this->saveWeeklyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $locationID);
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
                        $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, null, $locationID);
                        
                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        // $this->saveEventHistory($CaseEvent->id, $authUser);
                        $i++;
                        $startDate = strtotime('+7 day',$startDate); 
                    } while ($startDate <= $endDate);
                } else {
                    $this->saveWeeklyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $locationID);
                }
            }
        }else if($request->event_frequency=='CUSTOM') { 
            $start = new DateTime(date("Y-m-d", $startDate));
            $startClone = new DateTime(date("Y-m-d", $startDate));
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            // dd($request);
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                $this->saveCustomEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $start, $startClone, $locationID);
            } else {
                $Edate=CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->orderBy('end_date','desc')->first();
                if(isset($request->end_on)) {
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
                            $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, null, $locationID);
                        
                            $i++;
                            $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                            $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                            // $this->saveEventHistory($CaseEvent->id, $authUser);
                        }
                    }
                } else {
                    $this->saveCustomEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $start, $startClone, $locationID);
                }
            }
        } else if($request->event_frequency=='MONTHLY') { 
            $Currentweekday= date("l", $startDate ); 
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            $monthly_frequency=$request->monthly_frequency;
            
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                $this->saveMonthlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday, $locationID);
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
                } else if($monthly_frequency != $OldCaseEvent->monthly_frequency) {
                    $endDate = strtotime(date("Y-m-t", $endDate ));
                    $oldEventIds = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->orderBy('start_date','asc')->pluck('id')->toArray();
                    $editedId = [];
                    do {
                        if($monthly_frequency=='MONTHLY_ON_DAY'){
                            $startDate=$startDate;
                        }else if($monthly_frequency=='MONTHLY_ON_THE'){
                            $startDate = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startDate);
                        }else if($monthly_frequency=='MONTHLY_ON_THE_LAST'){
                            $startDate = strtotime("last ".strtolower($Currentweekday)." of this month",$startDate);
                        }

                        $start_date = date("Y-m-d", $startDate);
                        $end_date = date("Y-m-d", $startDate);
                        $CaseEvent = $this->updateMonthlyYearlyRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, $locationID, $oldEventIds[$i]);

                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        // $this->saveEventHistory($CaseEvent->id, $authUser);
                        array_push($editedId, $oldEventIds[$i]);
                        $startDate = strtotime('+'.$request->event_interval_month.' months',$startDate);
                        $i++;
                    } while ($startDate <= $endDate);
                    $remainEventIds = array_diff($oldEventIds, $editedId);
                    if(count($remainEventIds)) {
                        $oldEvents = CaseEvent::whereIn('id', $remainEventIds);
                        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                        $oldEvents->forceDelete();
                    }
                } else {
                    $this->saveMonthlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday, $locationID);
                }
            }
        } else if($request->event_frequency=='YEARLY') { 
            $yearly_frequency=$request->yearly_frequency;
            $Currentweekday= date("l", $startDate ); 
            $i=0;
            $OldCaseEvent=CaseEvent::find($request->event_id);
            if($OldCaseEvent->event_frequency != $request->event_frequency) {
                $this->saveYearlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday, $locationID);             
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
                        $CaseEvent = $this->updateCreateRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, null, $locationID);
                    
                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        // $this->saveEventHistory($CaseEvent->id, $authUser);

                        $startDate = strtotime('+'.$event_interval_year.' years',$startDate);
                        $i++;
                    } while ($startDate < $endDate);
                } else if($yearly_frequency != $OldCaseEvent->yearly_frequency) {
                    $oldEventIds = CaseEvent::where('parent_evnt_id',$OldCaseEvent->parent_evnt_id)->where('id',">=",$OldCaseEvent->id)->orderBy('start_date','asc')->pluck('id')->toArray();
                    $endDate = strtotime(date("Y-m-t", $endDate ));
                    $editedId = [];
                    do {
                        if($yearly_frequency=='YEARLY_ON_DAY'){
                            $startDate=$startDate;
                        }else if($yearly_frequency=='YEARLY_ON_THE'){
                            $startDate = strtotime("fourth ".strtolower($Currentweekday)." of this month",$startDate);
                        }else if($yearly_frequency=='YEARLY_ON_THE_LAST'){
                            $startDate = strtotime("last ".strtolower($Currentweekday)." of this month",$startDate);
                        }
                        $start_date = date("Y-m-d", $startDate);
                        $end_date = date("Y-m-d", $startDate);
                        $CaseEvent = $this->updateMonthlyYearlyRecurringEvent($request, $start_date, $end_date, $start_time, $end_time, $authUser, $OldCaseEvent, $locationID, $oldEventIds[$i]);

                        $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                        $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
                        // $this->saveEventHistory($CaseEvent->id, $authUser);
                        array_push($editedId, $oldEventIds[$i]);
                        $startDate = strtotime('+'.$request->event_interval_year.' years',$startDate);
                        $i++;
                    } while ($startDate <= $endDate);
                    $remainEventIds = array_diff($oldEventIds, $editedId);
                    if(count($remainEventIds)) {
                        $oldEvents = CaseEvent::whereIn('id', $remainEventIds);
                        $OldCaseEvent->deleteChildTableRecords($oldEvents->pluck("id")->toArray());
                        $oldEvents->forceDelete();
                    }
                } else {
                    $this->saveYearlyEvent($request, $OldCaseEvent, $startDate, $endDate, $start_time, $end_time, $authUser, $Currentweekday, $locationID);
                }
            }
        }
        $this->addCaseEventActivity((array)$request, $CaseEvent, $authUser, 'updated');
        Log::info("Case Following Event Job Ended :". date('Y-m-d H:i:s'));
    }
}
