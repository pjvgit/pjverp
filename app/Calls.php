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
        $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        if($this->call_time!=''){
            $tm=$this->call_date . $this->call_time;
            $currentConvertedDate= $CommonController->convertUTCToUserTime($tm,$timezone);
            return date('M d Y,h:i A',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
}
