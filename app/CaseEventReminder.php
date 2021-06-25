<?php
namespace App;

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
        'event_id', 'reminder_type', 'reminer_number', 'reminder_frequncy'   
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
}
