<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    protected $guard = 'web';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'first_name', 'middle_name', 'last_name', 'email', 'password', 'user_type', 'user_level', 'user_title', 'default_rate', 'mobile_number', 'token', 
        'user_timezone', 'user_status', 'verified', 'firm_name', 'street', 'apt_unit', 'city', 'state', 'postal_code', 'country', 'work_phone', 'home_phone', 
        'link_user_to', 'sharing_setting_1', 'sharing_setting_2', 'sharing_setting_3', 'case_rate', 'rate_amount', 'default_color', 'last_login', 
        'is_sent_welcome_email', 'profile_image', 'remember_token', 'employee_no', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at',
        'sessionTime', 'auto_logout', 'is_primary_account', 'conekta_customer_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $appends  = ['decode_id', 'created_date_new', 'full_name', /* 'full_address', */ 'user_type_text','user_route_link'];

    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    } 
    
    /**
     * Get user's last login detail, Do not add this attribute to append list, if required please set append dynamically
     */
    public function getLastloginnewformateAttribute() {
        $authUser = auth()->user();
        if(isset($authUser->user_timezone) && $this->last_login!=null) {
            $convertedDate= convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->last_login)), $authUser->user_timezone);
            return date('M j, Y h:i A',strtotime($convertedDate));
        } else {
            return null;
        }
    }
     
    public function getCreatedDateNewAttribute(){
        if($this->created_at!=NULL){
            $userTime = convertUTCToUserTime($this->created_at, auth()->user()->user_timezone ?? 'UTC');
            return date('M j, Y',strtotime($userTime));
        }else{
            return '';
        }
    }
    
    /**
     * Do not add this attribute to append array, if required please set append dynamically
     */
    public function getContactlistAttribute(){
        $companyID = $this->id;
        if($companyID != null){
            $userCount = UsersAdditionalInfo::join('users','users_additional_info.user_id','=','users.id')
                    ->select("users.id as cid","users.first_name","users.last_name",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as fullname'))
                    ->where("users.user_level","2")
                    ->where("parent_user",auth()->user()->id)
                    ->whereIn("users.user_status",[1,2,3]) //1 Active
                    ->whereRaw("find_in_set($companyID,`multiple_compnay_id`)")
                    ->get();
        }else{
            $userCount = DB::table('users')->join('users_additional_info',"users_additional_info.user_id","=",'users.id')
                    ->select("users.id as cid","users.first_name","users.last_name",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as fullname'))
                    ->where('users_additional_info.company_id',$companyID)
                    ->where("users.user_level","2")
                    ->get();
        }      
        return json_encode($userCount); 
    }

    /**
     * Get user type text attribute
     */
    public function getUserTypeTextAttribute(){
        $Title="";
        if($this->user_level=="1"){
            $Title="Admin";
        }else if($this->user_level=="2"){
            $Title="Client";
        }else if($this->user_level=="3"){
            $Title="User";
        }else if($this->user_level=="4"){
            $Title="Company";
        }else if($this->user_level=="5"){
            $Title="Lead";
        }
        return $Title;

    }

    /**
     * The staffCases that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staffCases()
    {
        return $this->belongsToMany(CaseMaster::class, 'case_staff', 'user_id', 'case_id')->where("case_master.is_entry_done","1")
                ->whereNull("case_master.case_close_date")->whereNull("case_staff.deleted_at");
    }

    /**
     * Get active case count for the User, This relationship created on pivot table
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function caseStaff()
    {
        return $this->hasMany(CaseStaff::class, 'user_id')->whereNull("deleted_at");
    }

    /**
     * Get the userDeactivated associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function deactivateUserDetail()
    {
        return $this->hasOne("App\DeactivatedUser", 'user_id');
    }

    /**
     * Get all of the userLeadAdditionalInfo for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userLeadAdditionalInfo()
    {
        return $this->hasMany(LeadAdditionalInfo::class, 'user_id');
    }

    /**
     * Get user full name attribute
     */
    public function getFullNameAttribute()
    {
        return substr($this->first_name,0,100).' '.substr($this->last_name,0,100);
    }

    /**
     * Get all of the userLeadTask for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userLeadTask()
    {
        return $this->hasMany(Task::class, 'lead_id', 'id');
    }

    /**
     * The clientCases that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clientCases()
    {
        return $this->belongsToMany(CaseMaster::class, 'case_client_selection', 'selected_user', 'case_id')->where("is_entry_done", "1")->whereNull('case_client_selection.deleted_at')->where('case_client_selection.case_id', '!=', 0);
    }

    /**
     * Get the firm that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firmDetail()
    {
        return $this->belongsTo(Firm::class, 'firm_name');
    }

    /**
     * Get the userAdditionalInfo associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userAdditionalInfo()
    {
        return $this->hasOne(UsersAdditionalInfo::class, 'user_id');
    }

    /**
     * Get all of the userTrustAccountHistory for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userTrustAccountHistory()
    {
        return $this->hasMany(TrustHistory::class, 'client_id')->orderBy("created_at", "desc");
    }

    /**
     * Get all of the invoices for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        return $this->hasMany(Invoices::class, 'user_id');
    }

    /**
     * Get all of the userCreditAccountHistory for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userCreditAccountHistory()
    {
        return $this->hasMany(DepositIntoCreditHistory::class, 'user_id')->orderBy("payment_date", "desc")->orderBy("created_at", "desc");
    }

    /**
     * Get all of the companyContactList for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companyContactList()
    {
        return DB::table('users')->join('users_additional_info',"users_additional_info.user_id","=",'users.id')
                ->select("users.id as cid","users.first_name","users.last_name",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as fullname'))
                ->whereRaw("find_in_set(?,'multiple_compnay_id')", [$this->id])
                ->get();
    }

    // save notifications setting for email and feed
    public function userNotificationSetting()
    {
        return $this->belongsToMany(NotificationSetting::class, 'user_notification_settings', 'user_id', 'notification_id')->withPivot('for_feed');
    }

    /**
     * Get user title attribute
     */
    public function getUserTitleAttribute()
    {
        if(!$this->attributes['user_title']) {
            return ($this->attributes['user_level'] == 5) ? 'Lead' : (($this->attributes['user_level'] == 4) ? 'Company' : 'Client');
        } else {
            return $this->attributes['user_title'];
        }
    }

    /**
     * Get all of the user Firms for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getUserFirms()
    {
        $firmPortal = User::where('users.email', $this->email)->where('users.user_status', '1')->where('user_level', '3')->where('verified', '1')->count();
        $clientPortal = User::where('users.email', $this->email)->where('users.user_status', '1')->where('user_level', '2')->where('verified', '1')
                        ->whereHas("userAdditionalInfo", function($query) {
                            $query->where("client_portal_enable", '1');
                        })->count();
        return $firmPortal + $clientPortal;
    }

    /**
     * Set scope to not get appends into query result
     */
    public static $withoutAppends = false;
    public function scopeWithoutAppends($query)
    {
        self::$withoutAppends = true;

        return $query;
    }

    /**
     * Get the createdByUser that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')/* ->select(DB::raw('CONCAT(users.first_name, " ",users.last_name) as created_by_name')) */;
    }

    /**
     * Get user route link attribute
     */
    public function getUserRouteLinkAttribute(){
        $link="";
        if($this->user_level=="1"){
            $link="";
        }else if($this->user_level=="2"){
            $link=route('contacts/clients/view', $this->id);
        }else if($this->user_level=="3"){
            $link=route('contacts/attorneys/info', base64_encode($this->id));
        }else if($this->user_level=="4"){
            $link=route('contacts/companies/view', $this->id);
        }else if($this->user_level=="5"){
            $link=route('case_details/info', $this->id);
        }
        return $link;
    }

    /**
     * Get all of the clientCasesSelection for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientCasesSelection()
    {
        return $this->hasMany(CaseClientSelection::class, 'selected_user');
    }

    /**
     * Do not add this attribute to append array, if required please set append dynamically
     */
    public function getUserCasePermissionTextAttribute(){
        if($this->hasPermissionTo('access_only_linked_cases')) {
            return 'Only linked cases';
        } else {
            return 'All firm cases';
        }
    }

    /**
     * Get all of the onlinePaymentCustomerDetail for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function onlinePaymentCustomerDetail()
    {
        return $this->hasMany(UserOnlinePaymentCustomerDetail::class, 'client_id');
    }
}
