<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EventRecurring extends Model
{
    protected $fillable = [
        'id', 'event_id', 'start_date', 'end_date'
    ];    
    protected $appends  = ['start_time_user','end_time_user','st','et','start_date_time','end_date_time', 'user_start_date', 'user_end_date']; //colorcode
    
    public function getStartTimeUserAttribute(){
        // $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone ?? 'UTC';
        if($this->start_time!=''){
            $tm=$this->start_date . $this->start_time;
            // $currentConvertedDate= $CommonController->convertUTCToUserTime($tm,$timezone);
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('h:ia',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getEndTimeUserAttribute(){
        // $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone ?? 'UTC';
        if($this->end_time!=''){
            $tm=$this->start_date . $this->end_time;
            // $currentConvertedDate= $CommonController->convertUTCToUserTime($tm,$timezone);
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('h:ia',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getStAttribute(){
        // $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone ?? 'UTC';
        if($this->start_time!=''){
            $tm=$this->start_date . $this->start_time;
            // $currentConvertedDate= $CommonController->convertUTCToUserTime($tm,$timezone);
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('H:i:s',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getEtAttribute(){
        // $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone ?? 'UTC';
        if($this->end_time!=''){
            $tm=$this->end_date . $this->end_time;
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('H:i:s',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }

    public function getStartDateTimeAttribute(){
        if($this->start_time!=''){
            $tm=$this->start_date.' '.$this->start_time;
            return $currentConvertedDate= convertUTCToUserTime($tm, auth()->user()->user_timezone ?? 'UTC');
            // return date('Y-m-d H:i:s',strtotime($currentConvertedDate));
        }
    }
    public function getEndDateTimeAttribute(){
        if($this->end_time!=''){
            $tm=$this->end_date.' '.$this->end_time;
            return $currentConvertedDate= convertUTCToUserTime($tm,auth()->user()->user_timezone ?? 'UTC');
            // return date('Y-M-d H:i:s',strtotime($currentConvertedDate));
        }
    }

    /**
     * Delete event child tables record
     */
    public function deleteChildTableRecords($eventIds)
    {
        CaseEventLinkedStaff::whereIn("event_id", $eventIds)->forceDelete();
        CaseEventLinkedContactLead::whereIn("event_id", $eventIds)->forceDelete();
        CaseEventReminder::whereIn("event_id", $eventIds)->forceDelete();
        CaseEventComment::whereIn("event_id", $eventIds)->forceDelete();
    }

    /**
     * Get start date in user timezone
     */
    public function getUserStartDateAttribute(){
        return convertUTCToUserDate($this->start_date, auth()->user()->user_timezone ?? 'UTC');
    }

     /**
     * Get end date in user timezone
     */
    public function getUserEndDateAttribute(){
        return convertUTCToUserDate($this->end_date, auth()->user()->user_timezone ?? 'UTC');
    }

    /**
     * Get the event that owns the EventRecurring
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
