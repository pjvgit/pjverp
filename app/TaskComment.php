<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class TaskComment extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "task_comment";
    public $primaryKey = 'id';

    protected $fillable = ['status', 'title', 'task_id', 'created_by', 'updated_by'];    
    protected $appends  = ['decode_id', 'comment_added_at'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  

    /**
     * Get the createdByUser that owns the TaskComment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get comment added date time
     */
    public function getCommentAddedAtAttribute() {
        return convertUTCToUserTime($this->created_at, auth()->user()->user_timezone ?? 'UTC');
    }
}
