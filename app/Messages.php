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

    protected $fillable = ['status', 'title'];    
    protected $appends  = ['decode_id','last_post'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  
    public function getLastPostAttribute(){
        if($this->updated_at!=NULL){
            $userTime = convertUTCToUserTime($this->updated_at, auth()->user()->user_timezone ?? 'UTC');
            return date('d F H:i A',strtotime($userTime));
        }
    }
}
