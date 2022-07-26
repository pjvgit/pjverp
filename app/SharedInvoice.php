<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class SharedInvoice extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "shared_invoice";
    public $primaryKey = 'id';

    protected $fillable = ["last_reminder_sent_on", "reminder_sent_counter", "last_viewed_at", 'is_viewed', 'is_shared', 'user_id', 'invoice_id', 'created_by', 'updated_by'];

    protected $appends  = ['decode_id', 'viewed_date'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  
   
    public function getViewedDateAttribute(){
        if($this->last_viewed_at!=NULL){
            $userTime = convertUTCToUserTime($this->last_viewed_at, auth()->user()->user_timezone ?? 'UTC');
            return date('M j, Y',strtotime($userTime));
        }else{
            return '';
        }
    }
}
