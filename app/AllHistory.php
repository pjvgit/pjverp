<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
class AllHistory extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "all_history";
    public $primaryKey = 'id';

    protected $appends  = ['padding_id','time_ago','notes_for','events_for','task_for'];

    protected $dates = ['created_at', 'updated_at'];

    public function getPaddingIdAttribute(){
       return "#R-".sprintf('%05d', $this->id);

    }
   
    
    public function getTimeAgoAttribute(){
        return $GetLabel=$this->timeAgo($this->all_history_created_at);
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

     public function getNotesForAttribute(){
         if($this->notes_for_case!=NULL){
            return CaseMaster::find($this->notes_for_case);
         }
         if($this->notes_for_client!=NULL){
            return User::find($this->notes_for_client);
         }
         if($this->notes_for_company!=NULL){
            return User::find($this->notes_for_company);
         }
     } 
     public function getEventsForAttribute(){
        if($this->event_for_case!=NULL){
           return CaseMaster::find($this->event_for_case);
        }
        if($this->event_for_lead!=NULL){
           return User::find($this->event_for_lead);
        }
       
    } 
    public function getTaskForAttribute(){
        if($this->task_for_case!=NULL){
           return CaseMaster::find($this->task_for_case);
        }
        if($this->task_for_lead!=NULL){
           return User::find($this->task_for_lead);
        }
       
    } 

    /**
     * Get the depositForUser that owns the AllHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function depositForUser()
    {
        return $this->belongsTo(User::class, 'deposit_for');
    }

    /**
     * Get the createdByUser that owns the AllHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get formated created at date
     */
    public function getFormatedCreatedAtAttribute()
    {
        return convertUTCToUserTime($this->created_at, auth()->user()->user_timezone ?? 'UTC');
    }

    /**
     * Get the task that owns the AllHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    /**
     * Get the firm detials associated with the CaseMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function caseFirm()
    {
        return $this->hasOne(Firm::class, 'id', 'firm_name');
    }
    
    /**
     * Get the invoice that owns the AllHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'activity_for', 'id');
    }
}
