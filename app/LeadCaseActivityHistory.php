<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
class LeadCaseActivityHistory extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "lead_case_activity_history";
    public $primaryKey = 'id';

    protected $fillable = [
        'id', 'acrtivity_title', 'activity_by', 'for_lead', 'type', 'task_id', 'case_id'
    ];
    protected $appends  = ['decode_id','lead_name','time_ago','case_name','task_name'];

    public function getDecodeIdAttribute(){
        return base64_encode($this->for_lead);
    } 

    public function getLeadNameAttribute(){
        if($this->for_lead!=NULL){
            $uData=User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'),"users.user_title")
            ->where("id",$this->for_lead)
            ->first();
            return $uData->created_by_name;
        }else{
            return "";
        }
        
    } 

    public function getCaseNameAttribute(){
        if($this->case_id!=NULL){
            $uData=LeadAdditionalInfo::select("potential_case_title")
            ->where("id",$this->case_id)
            ->first();
            return $uData->potential_case_title;
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
