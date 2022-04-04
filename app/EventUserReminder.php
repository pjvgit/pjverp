<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventUserReminder extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id', 'event_id', 'user_id', 'event_recurring_id', 'event_reminders', 'created_by', 'updated_by'
    ];    

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
