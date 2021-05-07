<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
class ClientNotes extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "client_notes";
    public $primaryKey = 'id';

    protected $fillable = [
        'client_id', 'note_date', 'not_activity', 'note_subject', 'notes', 'status'
    ];

    protected $appends  = ['created_date_new','updated_date_new'];

    public function getCreatedDateNewAttribute(){
        $CommonController= new CommonController();
        if(isset(Auth::User()->user_timezone) && $this->created_at!=null) 
        {
            $timezone=Auth::User()->user_timezone;
            $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->created_at)),$timezone);
            return date('M j, Y h:i A',strtotime($convertedDate));

        }else{
            return null;
        }
    }
    public function getUpdatedDateNewAttribute(){
        $CommonController= new CommonController();
        if(isset(Auth::User()->user_timezone) && $this->updated_at!=null) 
        {
            $timezone=Auth::User()->user_timezone;
            $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->updated_at)),$timezone);
            return date('M j, Y h:i A',strtotime($convertedDate));

        }else{
            return null;
        }
    }
}
