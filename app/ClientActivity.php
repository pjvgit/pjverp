<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
class ClientActivity extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "client_activity";
    public $primaryKey = 'id';

    protected $fillable = [
        'id', 'acrtivity_title', 'activity_by', 'activity_for', 'type', 'task_id', 'case_id'
    ];
    protected $appends  = ['decode_id','case_decode_id','case_unique_id','lead_name','time_ago','case_name','task_name','client_name'];

    public function getDecodeIdAttribute(){
        return base64_encode($this->activity_for);
    } 
    public function getCaseDecodeIdAttribute(){
        return base64_encode($this->case_id);
    } 

    public function getLeadNameAttribute(){
        if($this->activity_by!=NULL){
            $uData=User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),"users.user_title")
            ->where("id",$this->activity_by)
            ->first();
            return $uData->created_by_name;
        }else{
            return "";
        }
        
    } 
    public function getClientNameAttribute(){
        if($this->activity_for!=NULL){
            $uData=User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as client_name'),"users.user_title")
            ->where("id",$this->activity_for)
            ->first();
            return $uData->client_name;
        }else{
            return "";
        }
        
    } 
    public function getCaseNameAttribute(){
        if($this->case_id!=NULL){
            $uData=CaseMaster::select("case_title")
            ->where("id",$this->case_id)
            ->first();
            return $uData->case_title;
        }else{
            return "";
        }
        
    } 
    public function getCaseUniqueIdAttribute(){
        if($this->case_id!=NULL){
            $uData=CaseMaster::select("case_unique_number")
            ->where("id",$this->case_id)
            ->first();
            return $uData->case_unique_number;
        }else{
            return "";
        }
        
    } 
    public function getTaskNameAttribute(){
        if($this->task_id!=NULL){
            $uData=Task::select("task_title")
            ->where("id",$this->task_id)
            ->first();
            return $uData->task_title;
        }else{
            return "";
        }
        
    } 


    public function getTimeAgoAttribute(){
       return $GetLabel=$this->timeAgo($this->note_created_at);
    } 

    public function timeAgo($time_ago)
    {
        $time_ago = strtotime($time_ago);
        $cur_time   = time();
        $time_elapsed   = $cur_time - $time_ago;
        $seconds    = $time_elapsed ;
        $minutes    = round($time_elapsed / 60 );
        $hours      = round($time_elapsed / 3600);
        $days       = round($time_elapsed / 86400 );
        $weeks      = round($time_elapsed / 604800);
        $months     = round($time_elapsed / 2600640 );
        $years      = round($time_elapsed / 31207680 );
        // Seconds
        if($seconds <= 60){
            return "just now";
        }
        //Minutes
        else if($minutes <=60){
            if($minutes==1){
                return "one minute ago";
            }
            else{
                return "$minutes minutes ago";
            }
        }
        //Hours
        else if($hours <=24){
            if($hours==1){
                return "an hour ago";
            }else{
                return "$hours hrs ago";
            }
        }
        //Days
        else if($days <= 7){
            if($days==1){
                return "yesterday";
            }else{
                return "$days days ago";
            }
        }
        //Weeks
        else if($weeks <= 4.3){
            if($weeks==1){
                return "a week ago";
            }else{
                return "$weeks weeks ago";
            }
        }
        //Months
        else if($months <=12){
            if($months==1){
                return "a month ago";
            }else{
                return "$months months ago";
            }
        }
        //Years
        else{
            if($years==1){
                return "one year ago";
            }else{
                return "$years years ago";
            }
        }
    }
}
