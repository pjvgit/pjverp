<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class Messages extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "messages";
    public $primaryKey = 'id';

    protected $fillable = ['status', 'title', 'users_json'];    
    protected $appends  = ['decode_id','last_post', 'client_last_post', 'is_read_msg'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  
    public function getLastPostAttribute(){
        if($this->updated_at!=NULL){
            $userTime = convertUTCToUserTime($this->updated_at, auth()->user()->user_timezone ?? 'UTC');
            return date('d F h:i A',strtotime($userTime));
        }
    }
    public function getclientLastPostAttribute(){
        if($this->updated_at!=NULL){
            $userTime = convertUTCToUserTime($this->updated_at, auth()->user()->user_timezone ?? 'UTC');
            return date('M d, Y',strtotime($userTime));
        }
    }

    /**
     * Check messages is read by firm user/staff attribute
     */
    public function getIsReadMsgAttribute()
    {
        $authUserId = (string) auth()->id();
        $decodeJson = encodeDecodeJson($this->users_json)->where("user_id", $authUserId)->first();
        return $decodeJson->is_read ?? "no";
    }
}
