<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class EventRecurring extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id', 'event_id', 'start_date', 'end_date', 'event_reminders', 'event_comments', 'event_linked_staff', 'event_linked_contact_lead'
    ];    
    protected $appends  = ['start_time_user','end_time_user','st','et','start_date_time','end_date_time', 'user_start_date', 'user_end_date']; //colorcode

    // protected $casts = ['event_reminders' => 'array', 'event_comments' => 'array',];
    
    public function getStartTimeUserAttribute(){
        $timezone=Auth::User()->user_timezone ?? 'UTC';
        if($this->start_time!=''){
            $tm=$this->start_date . $this->start_time;
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('h:ia',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getEndTimeUserAttribute(){
        $timezone=Auth::User()->user_timezone ?? 'UTC';
        if($this->end_time!=''){
            $tm=$this->start_date . $this->end_time;
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('h:ia',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getStAttribute(){
        $timezone=Auth::User()->user_timezone ?? 'UTC';
        if($this->start_time!=''){
            $tm=$this->start_date . $this->start_time;
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('H:i:s',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getEtAttribute(){
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
        }
    }
    public function getEndDateTimeAttribute(){
        if($this->end_time!=''){
            $tm=$this->end_date.' '.$this->end_time;
            return $currentConvertedDate= convertUTCToUserTime($tm,auth()->user()->user_timezone ?? 'UTC');
        }
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
