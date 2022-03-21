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
    protected $appends  = ['decode_id', 'user_start_date', 'user_end_date', 'is_view', 'is_read'];

    // protected $casts = ['event_reminders' => 'array', 'event_comments' => 'array',];

    /**
     * Get decoded id of event
     */
    public function getDecodeIdAttribute(){    
        return base64_encode($this->id);
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

    /**
     * Check event is viewed by client attribute
     */
    public function getIsViewAttribute()
    {
        $authUserId = auth()->id();
        $decodeJson = encodeDecodeJson($this->event_linked_contact_lead)->where("contact_id", $authUserId)->first();
        return $decodeJson->is_view ?? "no";
    }

    /**
     * Check event is read by firm user/staff attribute
     */
    public function getIsReadAttribute()
    {
        $authUserId = (string) auth()->id();
        $decodeJson = encodeDecodeJson($this->event_linked_staff)->where("user_id", $authUserId)->first();
        return $decodeJson->is_read ?? "no";
    }
}
