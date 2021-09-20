<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class CaseSolReminder extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_sol_reminder";
    public $primaryKey = 'id';

    protected $fillable = [
        'case_id', 'reminder_type', 'reminer_number', 'snooze_time', 'snooze_type', 'is_dismiss', 'snoozed_at', 'remind_at',  'snooze_remind_at', 'reminded_at',
    ];    
    protected $appends  = ['decode_id'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  

    /**
     * Get the case that owns the CaseSolReminder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function case()
    {
        return $this->belongsTo(CaseMaster::class, 'case_id');
    }

    /**
     * Set event remind at attribute
     */
    public function setRemindAtAttribute($value)
    {
        $caseStatuteDate = Carbon::parse($this->case->case_statute_date);
        $remindTime = $caseStatuteDate->subDays($this->reminer_number)->format('Y-m-d H:i:s');
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
