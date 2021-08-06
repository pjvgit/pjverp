<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class CaseEventReminder extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_event_reminder";
    public $primaryKey = 'id';

    protected $fillable = [
        'event_id', 'reminder_type', 'reminer_number', 'reminder_frequncy', 'snooze_time', 'snooze_type', 'is_dismiss', 'snoozed_at', 'remind_at', 
        'snooze_remind_at', 'reminded_at', 'reminder_user_type', 'created_by'
    ];    
    protected $appends  = ['decode_id'];
    public function getDecodeIdAttribute(){
         
        return base64_encode($this->id);
    }  

    /**
     * Get the event that owns the CaseEventReminder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo(CaseEvent::class, 'event_id');
    }

    /**
     * Set event remind at attribute
     */
    public function setRemindAtAttribute($value)
    {
        if($this->reminder_frequncy == "week" || $this->reminder_frequncy == "day") {
            $eventStartDate = Carbon::parse($this->event->start_date);
            if($this->reminder_frequncy == "week") {
                $remindTime = $eventStartDate->subWeeks($this->reminer_number)->format('Y-m-d H:i:s');
            } else {
                $remindTime = $eventStartDate->subDays($this->reminer_number)->format('Y-m-d H:i:s');
            }
        } else if($this->reminder_frequncy == "hour") {
            $eventStartTime = @$this->event->start_date." ".@$this->event->start_time;
            $remindTime = Carbon::parse($eventStartTime)->subHours($this->reminer_number)->format('Y-m-d H:i:s');
        } else if($this->reminder_frequncy == "minute") {
            $eventStartTime = @$this->event->start_date." ".@$this->event->start_time;
            $remindTime = Carbon::parse($eventStartTime)->subMinutes($this->reminer_number)->format('Y-m-d H:i:s');
        } else {
            $remindTime = Carbon::parse($value)->format('Y-m-d H:i:s');
        }
        $this->attributes['remind_at'] = $remindTime;
    }

    /**
     * Set event snooze remind at attribute
     */
    public function setSnoozeRemindAtAttribute($value)
    {
        $snoozedTime = $this->snoozed_at;
        if($this->snooze_type == "hour")
            $remindTime = Carbon::parse($snoozedTime)->addHours($this->snooze_time)->format('Y-m-d H:i');
        else if($this->snooze_type == "day")
            $remindTime = Carbon::parse($snoozedTime)->addDays($this->snooze_time)->format('Y-m-d H:i');
        else if($this->snooze_type == "week")
            $remindTime = Carbon::parse($snoozedTime)->addDays($this->snooze_time)->format('Y-m-d H:i');
        else
            $remindTime = Carbon::parse($snoozedTime)->addMinutes($this->snooze_time)->format('Y-m-d H:i');
        $this->attributes['snooze_remind_at'] = $remindTime;
    }
}
