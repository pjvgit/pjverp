<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
class Task extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "task";
    public $primaryKey = 'id';

    protected $fillable = [
        'case_id', 'no_case_link', 'task_title', 'task_due_on', 'description', 'task_priority', 'task_assign_to', 'time_tracking_enabled'];    
    protected $appends  = ['task_user','task_completed','checklist_counter','decode_id','case_name','lead_name','task_due_date','assign_to'];


    public function getEventTyspeTexttAttribute(){
      
    }
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  
   
    public function getChecklistCounterAttribute(){
        $TaskChecklistAll =  TaskChecklist::where("task_id",$this->id)->count();
        if($TaskChecklistAll!="0"){
            $TaskChecklistDone =  TaskChecklist::where("task_id",$this->id)->where("status","1")->count();
            return  $TaskChecklistDone."/".$TaskChecklistAll; 
        }
    }
    public function getCaseNameAttribute(){
        $caseName =  CaseMaster::select("case_title","id","case_unique_number")->where("id",$this->case_id)->first();
        return $caseName;
    }
    public function getLeadNameAttribute(){
        $leadName =  User::select("first_name","id","last_name")->where("id",$this->lead_id)->first();
        return $leadName;
    }

    public function getTaskUserAttribute(){
        $tasklinkedstaff =  CaseTaskLinkedStaff::join('users','users.id','=','task_linked_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_type")
        ->where('task_id',$this->id)  
        ->get();
        return $tasklinkedstaff; 
    }
    public function getTaskCompletedAttribute(){
        $taskCompletedby =  User::select("users.id","users.first_name","users.last_name")
        ->where('id',$this->task_completed_by)  
        ->first();
        return $taskCompletedby; 
    }
    public function getTaskDueDateAttribute(){
        $CommonController= new CommonController();
        if(isset(Auth::User()->user_timezone) && $this->task_due_on!=null && $this->task_due_on!='9999-12-30') 
        {
            $timezone=Auth::User()->user_timezone;
            $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->task_due_on)),$timezone);
            return date('M j, Y',strtotime($convertedDate));

        }else{
            return "N/A";
        }
    }

    public function getAssignToAttribute(){
        $assignToUser =  CaseTaskLinkedStaff::join('users','users.id','=','task_linked_staff.user_id')->select("users.id","users.first_name","users.last_name","task_linked_staff.user_id")
        ->where('task_id',$this->id)  
        ->get();
        if(!$assignToUser->isEmpty()){
            foreach($assignToUser as $k=>$v){
                $v->decode_user_id=base64_encode($v->id);
            }
        }
        return json_encode($assignToUser); 
    }

}
