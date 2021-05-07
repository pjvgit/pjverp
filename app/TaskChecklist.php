<?php
namespace App;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
class TaskChecklist extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "task_checklist";
    public $primaryKey = 'id';

    protected $fillable = ['task_id', 'title', 'checklist_order', 'status'];    
    protected $appends  = ['checklist_counter','etext','decode_id'];


    public function getEventTyspeTexttAttribute(){
      
    }
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  
   
    public function getChecklistCounterAttribute(){
        $ContractUserCase =  CaseEventLinkedStaff::join('users','users.id','=','case_event_linked_staff.user_id')->select("users.id","users.first_name","users.last_name","users.id as user_id")
        ->where('case_event_linked_staff.event_id',$this->id)  
        ->get();
        if(!$ContractUserCase->isEmpty()){
            foreach($ContractUserCase as $key=>$val){
             $val->decode_user_id=base64_encode($val->user_id);
            }
        }
        return $ContractUserCase; 
    }
}
