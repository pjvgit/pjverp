<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Http\Controllers\CommonController;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'case_id', 'no_case_link', 'task_title', 'task_due_on', 'description', 'task_priority', 'task_assign_to', 'time_tracking_enabled', 'firm_id', 'status', 
        'task_completed_by', 'task_completed_date', 'is_need_review'];    
    protected $appends  = ['task_user','task_completed','checklist_counter','decode_id','case_name','lead_name','task_due_date','assign_to', 'priority_text'];


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
        $tasklinkedstaff =  CaseTaskLinkedStaff::join('users','users.id','=','task_linked_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_type","users.user_level")
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
        if(isset(Auth::User()->user_timezone) && $this->task_due_on!=null && $this->task_due_on!='9999-12-30') 
        {
            $userTime = convertUTCToUserDate($this->attributes['task_due_on'], auth()->user()->user_timezone ?? 'UTC');            
            return date('M j, Y',strtotime($userTime));

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
    // public function setTaskDueOnAttribute($value)
    // {
    //     $this->attributes['task_due_on'] =  \Carbon\Carbon::parse($value, auth()->user()->user_timezone ?? 'UTC')->setTimezone(config('app.timezone'))->format('Y-m-d');
    // }
    public function getTaskDueOnAttribute()
    {
        if($this->attributes['task_due_on'] != '9999-12-30'){
            $userTime = convertUTCToUserDate($this->attributes['task_due_on'], auth()->user()->user_timezone ?? 'UTC');            
            return  date('Y-m-d', strtotime($userTime));            
        }else{
            return $this->attributes['task_due_on'];
        }
    }

    /**
     * The taskLinkedStaff that belong to the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function taskLinkedStaff()
    {
        return $this->belongsToMany(User::class, 'task_linked_staff', 'task_id', 'user_id')->wherePivot("is_contact", "no")->whereNull("task_linked_staff.deleted_at")
                ->withPivot('is_read');
    }

    /**
     * Get the case that owns the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function case()
    {
        return $this->belongsTo(CaseMaster::class, 'case_id');
    }

    /**
     * Get the lead that owns the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lead()
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    /**
     * Get the firmDetail that owns the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firm()
    {
        return $this->belongsTo(Firm::class, 'firm_id');
    }

    /**
     * Get the taskCreatedByUser that owns the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taskCreatedByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPriorityTextAttribute()
    {
        if($this->task_priority == 3)
            $text = "High";
        else if($this->task_priority == 2)
            $text = "Medium";
        else
            $text = "Low";
        return $text;
    }

    /**
     * Get the leadAdditionalInfo that owns the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leadAdditionalInfo()
    {
        return $this->belongsTo(LeadAdditionalInfo::class, 'lead_id', 'user_id');
    }

    public static $withoutAppends = false;

    public function scopeWithoutAppends($query)
    {
        self::$withoutAppends = true;

        return $query;
    }

    protected function getArrayableAppends()
    {
        if (self::$withoutAppends){
            return [];
        }

        return parent::getArrayableAppends();
    }

    /**
     * The taskLinkedContact that belong to the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function taskLinkedContact()
    {
        return $this->belongsToMany(User::class, 'task_linked_staff', 'task_id', 'user_id')->wherePivot("is_contact", "yes")->whereNull("task_linked_staff.deleted_at");
    }

    /**
     * Get all of the taskCheckList for the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taskCheckList()
    {
        return $this->hasMany(TaskChecklist::class, 'task_id')->orderBy("checklist_order");
    }

    
}
