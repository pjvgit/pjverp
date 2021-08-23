<?php

namespace App\Jobs;

use App\CaseEventLinkedContactLead;
use App\CaseEventLinkedStaff;
use App\CaseEventReminder;
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

class CaseSingleEventJob implements ShouldQueue
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
        // dd($request->event_id);
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $start_time = $this->start_time;
        $end_time = $this->end_time;
        $authUser = $this->authUser;
        if($request->event_frequency=='DAILY')
        {
            dbStart();
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
                $this->saveEventReminder((array)$request, $CaseEvent->id, $authUser); 
                $this->saveLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser); 
                $this->saveNonLinkedStaffToEvent((array)$request, $CaseEvent->id, $authUser);
                $this->saveContactLeadData((array)$request, $CaseEvent->id, $authUser); 
    
                // $this->saveEventHistory($CaseEvent->id);
                
                $startDate = strtotime('+'.$event_interval_day.' day',$startDate); 
                $i++;
            } while ($startDate <= $endDate);
            dbCommit();
        }
        else if($request->event_frequency=='EVERY_BUSINESS_DAY')
        { 
            $i=0;
            do {
                $timestamp = $startDate;
                $weekday= date("l", $timestamp );            
                if ($weekday =="Saturday" OR $weekday =="Sunday") { 
                }else {
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

                    // $this->saveEventHistory($CaseEvent->id);
                }
                $i++;
                $startDate = strtotime('+1 day',$startDate); 
                } while ($startDate < $endDate);
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
                $this->saveEventReminder($request->all(),$CaseEvent->id); 
                $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                // $this->saveEventHistory($CaseEvent->id);

                $i++;
                $startDate = strtotime('+7 day',$startDate); 
            } while ($startDate < $endDate);
        }
        else if($request->event_frequency=='CUSTOM')
        { 
            $i=0;
            $weekFirstDay=date("Y-m-d", strtotime('monday this week'));
            $start = new DateTime($weekFirstDay);
            $startClone = new DateTime($weekFirstDay);
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
                    $this->saveEventReminder($request->all(),$CaseEvent->id); 
                    $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                    $this->saveContactLeadData($request->all(),$CaseEvent->id); 

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
                $this->saveEventReminder($request->all(),$CaseEvent->id); 
                $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveContactLeadData($request->all(),$CaseEvent->id); 

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
                $this->saveEventReminder($request->all(),$CaseEvent->id); 
                $this->saveLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveNonLinkedStaffToEvent($request->all(),$CaseEvent->id); 
                $this->saveContactLeadData($request->all(),$CaseEvent->id); 

                // $this->saveEventHistory($CaseEvent->id);

                
                $startDate = strtotime('+'.$event_interval_year.' years',$startDate);
                $i++;
                } while ($startDate < $endDate);
        }
        
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
                    if(isset($request->linked_staff_checked_attend) && in_array($request['linked_staff_checked_share'][$i], $request->linked_staff_checked_attend)){
                        $attend = "yes";
                    }
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
                $alreadyAdded=[];
                for($i=0;$i<count(array_unique($request['share_checkbox_nonlinked']));$i++){
                    $CaseEventLinkedStaff = new CaseEventLinkedStaff;
                    $CaseEventLinkedStaff->event_id=$event_id; 
                    $CaseEventLinkedStaff->user_id=$request['share_checkbox_nonlinked'][$i];
                    if(isset($request['attend_checkbox_nonlinked'][$i])){
                        $attend="yes";
                    }else{
                        $attend="no";
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
                $alreadyAdded=[];
                for($i=0;$i<count(array_unique($request['LeadInviteClientCheckbox']));$i++){
                    $CaseEventLinkedContactLead = new CaseEventLinkedContactLead;
                    $CaseEventLinkedContactLead->event_id=$event_id; 
                    $CaseEventLinkedContactLead->user_type='lead'; 
                    $CaseEventLinkedContactLead->lead_id=$request['LeadInviteClientCheckbox'][$i];
                    if(isset($request['LeadAttendClientCheckbox'][$i])){
                        $attend="yes";
                    }else{
                        $attend="no";
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
                $alreadyAdded=[];
                for($i=0;$i<count(array_unique($request['ContactInviteClientCheckbox']));$i++){
                    $CaseEventLinkedContactLead = new CaseEventLinkedContactLead;
                    $CaseEventLinkedContactLead->event_id=$event_id; 
                    $CaseEventLinkedContactLead->user_type='contact'; 
                    $CaseEventLinkedContactLead->contact_id=$request['ContactInviteClientCheckbox'][$i];
                    if(isset($request['ContactAttendClientCheckbox'][$i])){
                        $attend="yes";
                    }else{
                        $attend="no";
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
}
