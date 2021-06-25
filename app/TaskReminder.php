<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class TaskReminder extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "task_reminder";
    public $primaryKey = 'id';

    protected $fillable = [
        'task_id', 'reminder_type', 'reminer_number', 'reminder_frequncy'   
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

    
}
