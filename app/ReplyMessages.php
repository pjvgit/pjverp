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
    protected $appends  = ['decode_id'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  
}
