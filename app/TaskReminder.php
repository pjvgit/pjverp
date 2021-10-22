<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class TaskReminder extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "task_reminder";
    public $primaryKey = 'id';

    protected $fillable = [
        'task_id', 'reminder_type', 'reminer_number', 'reminder_frequncy', 'snooze_time', 'snooze_type', 'snoozed_at', 'is_dismiss' , 'remind_at', 'snooze_remind_at', 'reminded_at'
    ];    
    protected $appends  = ['decode_id'];
    public function getDecodeIdAttribute(){
         
        return base64_encode($this->id);
    }  

    /**
     * Get the task that owns the TaskReminder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Set event remind at attribute
     */
    public function setRemindAtAttribute($value)
    {
        $taskDueDate = Carbon::parse($this->task->task_due_on);
        if($this->reminder_frequncy == "week") {
            $remindTime = $taskDueDate->subWeeks($this->reminer_number)->format('Y-m-d');
        } else if($this->reminder_frequncy == "day") {
            if($this->reminer_number == 0)
                $remindTime = $taskDueDate->format('Y-m-d');
            else
                $remindTime = $taskDueDate->subDays($this->reminer_number)->format('Y-m-d');
        } else {
            $remindTime = $value;
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
