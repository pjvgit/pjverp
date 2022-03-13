<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id', 'case_id', 'lead_id', 'parent_event_id', 'event_title', 'is_SOL', 'event_type_id', 'is_full_day', 'start_date', 'start_time', 
        'end_date', 'end_time', 'event_location_id', 'event_description', 'is_event_private', 'is_recurring', 'event_recurring_type', 'event_interval_day', 
        'custom_event_weekdays', 'is_no_end_date', 'end_on', 'is_event_read', 'recurring_event_end_date', 'firm_id', 'created_by', 'updated_by', 
        'edit_recurring_pattern', 'event_interval_month', 'event_interval_year', 'monthly_frequency', 'yearly_frequency', 'event_interval_week'
    ];    
    protected $appends  = ['decode_id','user_start_time','user_end_time',/* 'start_date_time','end_date_time', */ 'user_start_date', 'user_end_date'];
    protected $casts = ["custom_event_weekdays" => 'array'];
    public function getDecodeIdAttribute(){
         
        return base64_encode($this->id);
    }  
    
    public function getUserStartTimeAttribute(){
        $timezone=Auth::User()->user_timezone ?? 'UTC';
        if($this->start_time!=''){
            $tm=$this->start_date . $this->start_time;
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('h:ia',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getUserEndTimeAttribute(){
        $timezone=Auth::User()->user_timezone ?? 'UTC';
        if($this->end_time!=''){
            $tm=$this->end_date . $this->end_time;
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('h:ia',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }

    /* public function getStartDateTimeAttribute(){
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
    } */
 
    /**
     * Get the eventType associated with the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'event_type_id')->where("status", 1);
    }

    /**
     * The eventLinkedStaff that belong to the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function eventLinkedStaff()
    {
        return $this->belongsToMany(User::class, 'case_event_linked_staff', 'event_id', 'user_id')->withPivot("attending", "comment_read_at")->wherePivot("deleted_at", Null);
    }

    /**
     * Delete event child tables record
     */
    public function deleteChildTableRecords($eventIds)
    {
        CaseEventLinkedStaff::whereIn("event_id", $eventIds)->forceDelete();
        CaseEventLinkedContactLead::whereIn("event_id", $eventIds)->forceDelete();
        // CaseEventReminder::whereIn("event_id", $eventIds)->forceDelete();
        CaseEventComment::whereIn("event_id", $eventIds)->forceDelete();
    }

    /**
     * Get the case that owns the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function case()
    {
        return $this->belongsTo(CaseMaster::class, 'case_id');
    }

    /**
     * Get the leadUser that owns the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leadUser()
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    /**
     * Get the eventLocation that owns the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eventLocation()
    {
        return $this->belongsTo(CaseEventLocation::class, 'event_location_id');
    }

    /**
     * Get the eventCreatedByUser that owns the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eventCreatedByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the eventUpdatedByUser that owns the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eventUpdatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The eventContact linked that belong to the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function eventLinkedContact()
    {
        return $this->belongsToMany(User::class, 'case_event_linked_contact_lead', 'event_id', 'contact_id')->withPivot(["attending", "is_view"]);
    }

    /**
     * The eventLinkedLead that belong to the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function eventLinkedLead()
    {
        return $this->belongsToMany(User::class, 'case_event_linked_contact_lead', 'event_id', 'lead_id')->withPivot("attending");
    }

    /**
     * Get all of the clientReminder for the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientReminder()
    {
        return $this->hasMany(CaseEventReminder::class, 'event_id')->where('reminder_user_type', 'client-lead');
    }

    /**
     * Get all of the eventComments for the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventComments()
    {
        return $this->hasMany(CaseEventComment::class, 'event_id')->where("action_type", 0);
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
     * Get all of the eventRecurring for the Event
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventRecurring()
    {
        return $this->hasMany(EventRecurring::class, 'event_id');
    }
}
