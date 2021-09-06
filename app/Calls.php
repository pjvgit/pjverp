<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth;
class Calls extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "calls";
    public $primaryKey = 'id';

    protected $appends  = ['utc_time','created_by_decode_id','call_for_decode_id'];

    public function getCreatedByDecodeIdAttribute(){
        return base64_encode($this->created_by);
    }  
    public function getCallForDecodeIdAttribute(){
        return base64_encode($this->call_for);
    } 
    public function getUtcTimeAttribute(){
        if($this->call_time!=''){
            $tm=$this->call_date . $this->call_time;
            $userTime = convertUTCToUserTime($tm,  auth()->user()->user_timezone ?? 'UTC');
            return date('M j, Y h:i a',strtotime($userTime));
        }else{
            return "";
        }
    }
}
