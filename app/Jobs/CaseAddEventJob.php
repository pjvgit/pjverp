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

class CaseAddEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use CaseEventTrait;
    protected $requestData, $startDate, $endDate, $start_time, $end_time, $authUser;
    public $tries = 5;
    public $timeout = 240;
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
                $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
                $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser);
                $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser); 
    
                // $this->saveEventHistory($CaseEvent->id);
                
                $startDate = strtotime('+'.$event_interval_day.' day',$startDate); 
                $i++;
            } while ($startDate <= $endDate);
        }
        else if($request->event_frequency=='EVERY_BUSINESS_DAY')
        { 
            $i=0; $elseFist = true; $currentI = 0;   
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
                    $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
                    $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                    $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                    $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser); 

                    // $this->saveEventHistory($CaseEvent->id);
                }
                $i++;
                $startDate = strtotime('+1 day',$startDate); 
                } while ($startDate <= $endDate);
        }
        else if($request->event_frequency=='WEEKLY')
        {
            $i=0;
            do {
                // $timestamp = $startDate;
                // $weekday= date("l", $timestamp ); 
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
                $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
                $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser); 

                // $this->saveEventHistory($CaseEvent->id);

                $i++;
                $startDate = strtotime('+7 day',$startDate); 
            } while ($startDate < $endDate);
        }
        else if($request->event_frequency=='CUSTOM')
        { 
            $i=0;
            // $weekFirstDay=date("Y-m-d", strtotime('monday this week'));
            // $start = new DateTime($weekFirstDay);
            // $startClone = new DateTime($weekFirstDay);
            $start = new DateTime(date("Y-m-d", $startDate));
            $startClone = new DateTime(date("Y-m-d", $startDate));
            if($request->end_on!=''){
                $end=new DateTime($request->end_on);
            }else{
                $end=$startClone->add(new DateInterval('P365D'));
            }
            //$end = new DateTime( '2021-09-28 23:59:59');
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
                    $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
                    $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                    $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                    $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser); 

                    // $this->saveEventHistory($CaseEvent->id);
                }
            }
            
        }
        else if($request->event_frequency=='MONTHLY')
        { 
            $Currentweekday= date("l", $startDate ); 
            $i=0;
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
                $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
                $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser); 

                //  $this->saveEventHistory($CaseEvent->id);
                $startDate = strtotime('+'.$event_interval_month.' months',$startDate);
                $i++;
                } while ($startDate < $endDate);
        }
        else if($request->event_frequency=='YEARLY')
        { 
            $endDate =  strtotime(date('Y-m-d',strtotime('+25 years')));
            if($request->end_on!=''){
                $endDate =  strtotime(date('Y-m-d',strtotime($request->end_on)));
            }
            $yearly_frequency=$request->yearly_frequency;
            $Currentweekday= date("l", $startDate ); 
            $i=0;
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
                $this->saveEventReminder((array)$request,$CaseEvent->id, $authUser); 
                $this->saveLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                $this->saveNonLinkedStaffToEvent((array)$request,$CaseEvent->id, $authUser); 
                $this->saveContactLeadData((array)$request,$CaseEvent->id, $authUser); 

                // $this->saveEventHistory($CaseEvent->id);

                
                $startDate = strtotime('+'.$event_interval_year.' years',$startDate);
                $i++;
            } while ($startDate < $endDate);
        }

        $data=[];
        if(!isset($request->no_case_link)){
            if(isset($request->case_or_lead)) { 
                if($request->text_case_id!=''){
                    $data['event_for_case']=$request->text_case_id;
                }    
                if($request->text_lead_id!=''){
                    $data['event_for_lead']=$request->text_lead_id; ;
                }    
            } 
        }
        $data['event_id']=$CaseEvent->id;
        $data['event_name']=$CaseEvent->event_title;
        $data['user_id']=Auth::User()->id;
        $data['activity']='added event';
        $data['type']='event';
        $data['action']='add';
        
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);
    }
}