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
        'id', 'case_id', 'lead_id', 'parent_evnt_id', 'event_title', 'is_SOL', 'event_type', 'all_day', 'start_date', 'start_time', 
        'end_date', 'end_time', 'event_location_id', 'event_description', 'is_event_private', 'recuring_event', 'event_frequency', 'event_interval_day', 
        'daily_weekname', 'no_end_date_checkbox', 'event_interval_month', 'event_interval_year', 'monthly_frequency', 'yearly_frequency', 'end_on', 'event_read', 
        'firm_id', 'created_by', 'updated_by',
    ];    
    protected $appends  = [/* 'caseuser', *//* 'etext', */'decode_id','start_time_user','st','et','start_date_time','end_date_time', 'user_start_date']; //colorcode


    public function getEventTyspeTexttAttribute(){
      
    }
    public function getDecodeIdAttribute(){
         
        return base64_encode($this->id);
    }  
    /* public function getEtextAttribute(){
        // return "";
        if($this->event_type!=''){
            $typeEventText =  EventType::select('title','color_code');
            $typeEventText=$typeEventText->where('status',"1");
            $typeEventText=$typeEventText->where('id',$this->event_type);
            $typeEventText=$typeEventText->first();
            return $typeEventText;
        }else{
            return "";
        }
    }  */
    // public function getCaseuserAttribute(){
     
        /* $ContractUserCase =  CaseEventLinkedStaff::join('users','users.id','=','case_event_linked_staff.user_id')->select("users.id","users.first_name","users.last_name","users.id as user_id","users.user_type")
        ->where('case_event_linked_staff.event_id',$this->id)  
        ->get();
        if(!$ContractUserCase->isEmpty()){
            foreach($ContractUserCase as $key=>$val){
             $val->decode_user_id=base64_encode($val->user_id);
            }
        }
        return $ContractUserCase; */
        // return $this->eventLinkedStaff()->take(1)->get(); 
    // }
    public function getStartTimeUserAttribute(){
        // $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        if($this->start_time!=''){
            $tm=$this->start_date . $this->start_time;
            // $currentConvertedDate= $CommonController->convertUTCToUserTime($tm,$timezone);
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('h:ia',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getStAttribute(){
        // $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        if($this->start_time!=''){
            $tm=$this->start_date . $this->start_time;
            // $currentConvertedDate= $CommonController->convertUTCToUserTime($tm,$timezone);
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('H:i:s',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }
    public function getEtAttribute(){
        // $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
        if($this->end_time!=''){
            $tm=$this->end_date . $this->end_time;
            $currentConvertedDate= convertUTCToUserTime($tm,$timezone);
            return date('H:i:s',strtotime($currentConvertedDate));
        }else{
            return "";
        }
    }

    public function getStartDateTimeAttribute(){
        if($this->start_time!=''){
            $tm=$this->start_date.' '.$this->start_time;
            return $currentConvertedDate= convertUTCToUserTime($tm, auth()->user()->user_timezone ?? 'UTC');
            // return date('Y-m-d H:i:s',strtotime($currentConvertedDate));
        }
    }
    public function getEndDateTimeAttribute(){
        if($this->end_time!=''){
            $tm=$this->end_date.' '.$this->end_time;
            return $currentConvertedDate= convertUTCToUserTime($tm,auth()->user()->user_timezone ?? 'UTC');
            // return date('Y-M-d H:i:s',strtotime($currentConvertedDate));
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
 
    /**
     * Get the eventType associated with the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'event_type')->where("status", 1);
    }

    /**
     * The eventLinkedStaff that belong to the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function eventLinkedStaff()
    {
        return $this->belongsToMany(User::class, 'case_event_linked_staff', 'event_id', 'user_id')->withPivot("attending", "comment_read_at")->wherePivot("deleted_at", Null);
    }

    /**
     * Delete event child tables record
     */
    public function deleteChildTableRecords($eventIds)
    {
        CaseEventLinkedStaff::whereIn("event_id", $eventIds)->forceDelete();
        CaseEventLinkedContactLead::whereIn("event_id", $eventIds)->forceDelete();
        CaseEventReminder::whereIn("event_id", $eventIds)->forceDelete();
        CaseEventComment::whereIn("event_id", $eventIds)->forceDelete();
    }

    /**
     * Get the case that owns the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function case()
    {
        return $this->belongsTo(CaseMaster::class, 'case_id');
    }

    /**
     * Get the leadUser that owns the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leadUser()
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    /**
     * Get the eventLocation that owns the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eventLocation()
    {
        return $this->belongsTo(CaseEventLocation::class, 'event_location_id');
    }

    /**
     * Get the eventCreatedByUser that owns the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eventCreatedByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the eventUpdatedByUser that owns the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eventUpdatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The eventContact linked that belong to the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function eventLinkedContact()
    {
        return $this->belongsToMany(User::class, 'case_event_linked_contact_lead', 'event_id', 'contact_id');
    }

    /**
     * The eventLinkedLead that belong to the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function eventLinkedLead()
    {
        return $this->belongsToMany(User::class, 'case_event_linked_contact_lead', 'event_id', 'lead_id');
    }

    /**
     * Get all of the clientReminder for the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientReminder()
    {
        return $this->hasMany(CaseEventReminder::class, 'event_id')->where('reminder_user_type', 'client-lead');
    }

    /**
     * Get all of the eventComments for the CaseEvent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventComments()
    {
        return $this->hasMany(CaseEventComment::class, 'event_id')->where("action_type", 0);
    }

    /**
     * Get start date in user timezone
     */
    public function getUserStartDateAttribute(){
        return convertUTCToUserDate($this->start_date, auth()->user()->user_timezone);
    }
}
