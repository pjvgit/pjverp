<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class ReplyMessages extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "reply_messages";
    public $primaryKey = 'id';

    protected $fillable = ['message_id', 'reply_message'];   

    protected $appends  = ['decode_id','created_date_new'];
    
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  
    
    public function getCreatedDateNewAttribute(){
        if($this->created_at!=NULL){
            $userTime = convertUTCToUserTime($this->created_at, auth()->user()->user_timezone ?? 'UTC');
            return date('M d, h:ia',strtotime($userTime));
        }else{
            return '--';
        }
    }

    /**
     * Get the createdByUser that owns the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
