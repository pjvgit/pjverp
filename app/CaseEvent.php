<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\SoftDeletes;
class CaseEvent extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "case_events";
    public $primaryKey = 'id';

    protected $fillable = [
        'id', 'case_id', 'event_title', 'event_type', 'start_date', 'start_time', 'end_date', 'end_time', 'event_location_id', 'event_description', 'is_event_private'
    ];    
    protected $appends  = ['caseuser','etext','decode_id','start_time_user','colorcode','st','et','sdt','edt'];


    public function getEventTyspeTexttAttribute(){
      
    }
    public function getDecodeIdAttribute(){
         
        return base64_encode($this->id);
    }  
    public function getEtextAttribute(){
        if($this->event_type!=''){
            $typeEventText =  EventType::select('title','color_code');
            $typeEventText=$typeEventText->where('status',"1");
            $typeEventText=$typeEventText->where('id',$this->event_type);
            $typeEventText=$typeEventText->first();
            return $typeEventText;
        }else{
            return "";
        }
    } 
    public function getCaseuserAttribute(){
        $ContractUserCase =  CaseEventLinkedStaff::join('users','users.id','=','case_event_linked_staff.user_id')->select("users.id","users.first_name","users.last_name","users.id as user_id","users.user_type")
        ->where('case_event_linked_staff.event_id',$this->id)  
        ->get();
        if(!$ContractUserCase->isEmpty()){
            foreach($ContractUserCase as $key=>$val){
             $val->decode_user_id=base64_encode($val->user_id);
            }
        }
        return $ContractUserCase; 
    }
    public function getStartTimeUserAttribute(){
        $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        if($this->start_time!=''){
            $tm=$this->start_date . $this->start_time;
            $currentConvertedDate= $CommonController->convertUTCToUserTime($tm,$timezone);
            return date('h:ia',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getStAttribute(){
        $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        if($this->start_time!=''){
            $tm=$this->start_date . $this->start_time;
            $currentConvertedDate= $CommonController->convertUTCToUserTime($tm,$timezone);
            return date('H:i:s',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getEtAttribute(){
        $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        if($this->end_time!=''){
            $tm=$this->end_date . $this->end_time;
            $currentConvertedDate= $CommonController->convertUTCToUserTime($tm,$timezone);
            return date('H:i:s',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }

    public function getSdtAttribute(){
        $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        if($this->start_time!=''){
            $tm=$this->start_date . $this->start_time;
            $currentConvertedDate= $CommonController->convertUTCToUserTime($tm,$timezone);
            return date('Y-m-d H:i:s',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getEdtAttribute(){
        $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        if($this->end_time!=''){
            $tm=$this->end_date . $this->end_time;
            $currentConvertedDate= $CommonController->convertUTCToUserTime($tm,$timezone);
            return date('Y-M-d H:i:s',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getColorcodeAttribute(){
        
        if(isset(request()->all()['byuser'])){
        $allUser=json_decode(request()->all()['byuser'], TRUE);
        if($this->event_type==''){
            $CheckUserLinked=CaseEventLinkedStaff::select("user_id")->where("event_id",$this->id)->pluck("user_id")->toArray();
            if(in_array(Auth::User()->id,$CheckUserLinked) && in_array(Auth::User()->id,$allUser)){
                return Auth::User()->default_color;
            }else{
                $allUser=json_decode(request()->all()['byuser'], TRUE);
                $staffData = User::select("default_color")->where("id","!=",Auth::User()->id)->whereIn("id",$allUser)->first();
                if(!empty($staffData)){
                return $staffData->default_color;
                }else{
                    return "";
                }
            }
        }
    }else{
        return "";
    }
       
    }
 
}
