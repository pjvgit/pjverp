<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class ContractUser extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "users";
    public $primaryKey = 'id';

    protected $fillable = [
        'id', 'first_name', 'middle_name', 'last_name', 'email', 'password', 'user_type', 'user_level', 'user_title', 'default_rate', 'mobile_number', 'token', 'user_timezone', 'user_status', 'verified', 'firm_name', 'street', 'apt_unit', 'city', 'state', 'postal_code', 'country', 'work_phone', 'home_phone', 'link_user_to', 'sharing_setting_1', 'sharing_setting_2', 'sharing_setting_3', 'case_rate', 'rate_amount', 'default_color', 'last_login', 'is_sent_welcome_email', 'profile_image', 'remember_token', 'employee_no', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at'
    ];
    protected $appends  = ['last_login_date'];

    public function getLastLoginDateAttribute(){
        return date('m-d-Y h:i A',strtotime(convertUTCToUserDate($this->last_login, auth()->user()->user_timezone)));
    }  
   
}
