<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\SoftDeletes;
class CaseIntakeForm extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_intake_form";
    public $primaryKey = 'id';

    protected $fillable = [
        
    ];
    protected $appends  = ['added_date','submitted_date'];
    public function getAddedDateAttribute(){
        $CommonController= new CommonController();
        if(isset(Auth::User()->user_timezone) && $this->case_intake_form_created_at!=null) 
        {
            $timezone=Auth::User()->user_timezone;
            $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->case_intake_form_created_at)),$timezone);
            return date('M j, Y h:i A',strtotime($convertedDate));

        }else{
            return null;
        }
    }
    public function getSubmittedDateAttribute(){
        $CommonController= new CommonController();
        if(isset(Auth::User()->user_timezone) && $this->submited_at!=null) 
        {
            $timezone=Auth::User()->user_timezone;
            $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->submited_at)),$timezone);
            return date('M j, Y h:i A',strtotime($convertedDate));

        }else{
            return null;
        }
    }
}
