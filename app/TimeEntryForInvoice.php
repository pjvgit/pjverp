<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class TimeEntryForInvoice extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "time_entry_for_invoice";
    public $primaryKey = 'id';

    protected $appends  = ['decode_id','entry_date'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  
    public function getEntryDateAttribute()
    {
        $userTime = convertUTCToUserDate($this->attributes['entry_date'], auth()->user()->user_timezone ?? 'UTC');            
        return date('Y-m-d', strtotime($userTime));            
    }
   
}
