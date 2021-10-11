<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Auth;
use DB;
class LeadAdditionalInfo extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "lead_additional_info";
    public $primaryKey = 'id';

    protected $fillable = [
         'user_id', 'address2', 'dob', 'driver_license', 'license_state', 'referal_source', 'refered_by', 'lead_detail', 'date_added', 'practice_area', 
         'potential_case_value', 'assigned_to', 'office', 'potential_case_description', 'user_status', 'conflict_check', 'notes' ,'potential_case_title',
         'do_not_hire_reason', "allocated_trust_balance"  
    ];
    protected $appends  = ['decode_id','added_date','added_date_sort','assign_to','assign_to_title','refered_by_name','refered_by_name_title','donthire_date','conveted_date'];

    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    } 

    public function getAddedDateAttribute(){
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
    public function getAddedDateSortAttribute(){
      $CommonController= new CommonController();
      if(isset(Auth::User()->user_timezone) && $this->created_at!=null) 
      {
          $timezone=Auth::User()->user_timezone;
          $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->created_at)),$timezone);
          return date('M j, Y',strtotime($convertedDate));

      }else{
          return null;
      }
  }
    public function getDonthireDateAttribute(){
      $CommonController= new CommonController();
      if(isset(Auth::User()->user_timezone) && $this->do_not_hire_on!=null) 
      {
          $timezone=Auth::User()->user_timezone;
          $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->do_not_hire_on)),$timezone);
          return date('M j, Y',strtotime($convertedDate));

      }else{
          return '';
      }
  }
    public function getAssignToAttribute(){
      if($this->assigned_to!=NULL){
        $creatdByData =  User::select("users.id as uid",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'))
        ->where('users.id',$this->assigned_to)
        ->first();
        return $creatdByData->created_by_name;
      }else{
          return "";
      }
    }

    public function getAssignToTitleAttribute(){
      if($this->assigned_to!=NULL){
        $creatdByData =  User::select("users.id as uid","users.user_title")
        ->where('users.id',$this->assigned_to)
        ->first();
        return $creatdByData->user_title;
      }else{
          return "";
      }
    }

    public function getReferedByNameAttribute(){
        if($this->refered_by!=NULL){
          $creatdByData =  User::select("users.id as uid",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as created_by_name'))
          ->where('users.id',$this->refered_by)
          ->first();
          return $creatdByData ? $creatdByData->created_by_name : '';
        }else{
            return "";
        }
      }

      public function getReferedByNameTitleAttribute(){
        if($this->refered_by!=NULL){
          $creatdByData =  User::select("users.id as uid","users.user_title")
          ->where('users.id',$this->refered_by)
          ->first();
          return $creatdByData ? $creatdByData->user_title : '';
        }else{
            return "";
        }
      }
      public function getConvetedDateAttribute(){
        $CommonController= new CommonController();
        if(isset(Auth::User()->user_timezone) && $this->converted_date!=null) 
        {
            $timezone=Auth::User()->user_timezone;
            $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->converted_date)),$timezone);
            return date('M j, Y',strtotime($convertedDate));
  
        }else{
            return '';
        }
    }

	/**
	 * Get the user that owns the LeadAdditionalInfo
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

}
