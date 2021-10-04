<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CommonController;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'first_name', 'middle_name', 'last_name', 'email', 'password', 'user_type', 'user_level', 'user_title', 'default_rate', 'mobile_number', 'token', 'user_timezone', 'user_status', 'verified', 'firm_name', 'street', 'apt_unit', 'city', 'state', 'postal_code', 'country', 'work_phone', 'home_phone', 'link_user_to', 'sharing_setting_1', 'sharing_setting_2', 'sharing_setting_3', 'case_rate', 'rate_amount', 'default_color', 'last_login', 'is_sent_welcome_email', 'profile_image', 'remember_token', 'employee_no', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $appends  = ['decode_id','created_date_new','additioninfo','createdby','lastloginnewformate','caselist',/* 'clientwise_caselist', */'contactlist','active_case_counter', 'full_name', 'full_address', 'user_type_text'];

    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    } 
    public function getAdditioninfoAttribute(){
        $ContractCaseUpdate =  UsersAdditionalInfo::join('client_group','users_additional_info.contact_group_id','=','client_group.id')
        ->select("client_group.group_name")
        ->where('client_group.status',"1")
        ->where('users_additional_info.user_id',$this->id)
        ->limit(1)
        ->first();
        if(!empty($ContractCaseUpdate)){
            $returnData=$ContractCaseUpdate->group_name;
        }else{
            $ContractCaseUpdate =  LeadAdditionalInfo::join('client_group','lead_additional_info.contact_group_id','=','client_group.id')
            ->select("client_group.group_name")
            ->where('client_group.status',"1")
            ->where('lead_additional_info.user_id',$this->id)
            ->limit(1)
            ->first();
            if(!empty($ContractCaseUpdate)){
                $returnData=$ContractCaseUpdate->group_name;
            }else{
                $returnData="-";
            }
           
        }
        return json_encode($returnData); 
    }
    public function getCreatedbyAttribute(){
        $CommonController= new CommonController();
        $creatdByData =  UsersAdditionalInfo::join("users","users_additional_info.created_by","=","users.id")
        ->select("users.id as uid",DB::raw('CONCAT(users.first_name, " ",users.last_name) as created_by_name'),'users_additional_info.created_at as cdt','users_additional_info.client_portal_enable')
        ->where('users_additional_info.user_id',$this->id)
        ->limit(1)
        ->first();
        
        if(!empty($creatdByData)){
            $timezone=(Auth::User()->user_timezone)??'UTC';
            $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($creatdByData->cdt)),$timezone);
            $creatdByData->newFormateCreatedAt=date('M j, Y h:i A',strtotime($convertedDate));

            $creatdByData->decode_user_id=base64_encode($creatdByData->uid);
        }else{
            $creatdByData =  LeadAdditionalInfo::join("users","lead_additional_info.created_by","=","users.id")
            ->select("users.id as uid",DB::raw('CONCAT(users.first_name, " ",users.last_name) as created_by_name'),'lead_additional_info.created_at as cdt')
            ->where('lead_additional_info.user_id',$this->id)
            ->limit(1)
            ->first();

            if(!empty($creatdByData)){
                 $timezone=(Auth::User()->user_timezone)??'UTC';
                $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($creatdByData->cdt)),$timezone);
                $creatdByData->newFormateCreatedAt=date('M j, Y h:i A',strtotime($convertedDate));

                $creatdByData->decode_user_id=base64_encode($creatdByData->uid);
            }
        }
        return json_encode($creatdByData); 
    }

    public function getLastloginnewformateAttribute(){
        $CommonController= new CommonController();
        if(isset(Auth::User()->user_timezone) && $this->last_login!=null) 
        {
            $timezone=Auth::User()->user_timezone;
            $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->last_login)),$timezone);
            return date('M j, Y h:i A',strtotime($convertedDate));

        }else{
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
    /* public function getClientWiseCaselistAttribute(){
        $ContractUserCase =  CaseMaster::join('case_client_selection','case_master.id','=','case_client_selection.case_id')
        ->select("case_master.case_title","case_master.id as cid","case_master.case_unique_number as case_unique_number")
        ->where('case_client_selection.selected_user',$this->id)
        ->where("case_master.is_entry_done","1")
        ->groupBy("case_master.id")  
        ->orderBy("case_master.id","DESC")  
        ->get();
        return json_encode($ContractUserCase); 
    } */
    public function getCaselistAttribute(){
        // $CommonController= new CommonController();
        // $getCompanyWiseClientList=$CommonController->getCompanyWiseCaseList($this->id);
        // $CaseClientSelection = CaseClientSelection::select("case_id")->whereIn("selected_user",$getCompanyWiseClientList)->get()->pluck('case_id');
        // $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
        // $case = $case->whereIn("case_master.id",$CaseClientSelection);
        // $case = $case->where("case_master.is_entry_done","1")->get(); 
        // return json_encode($case); 
        
        $CaseClientSelection = CaseClientSelection::select("case_id")->where("selected_user",$this->id)->get()->pluck('case_id');
        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
        $case = $case->whereIn("case_master.id",$CaseClientSelection);
        $case = $case->where("case_master.is_entry_done","1")->get(); 
        return json_encode($case); 
        
    }
    public function getContactlistAttribute(){
        $companyID = $this->id;
        if($companyID != null){
            $userCount = DB::table('users')->join('users_additional_info',"users_additional_info.user_id","=",'users.id')
                    ->select("users.id as cid","users.first_name","users.last_name",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as fullname'))
                    // ->where('users_additional_info.company_id',$this->id)
                    ->whereRaw("find_in_set($companyID,users_additional_info.multiple_compnay_id)")
                    // ->whereRaw("find_in_set(?,'multiple_compnay_id')", [$this->id])
                    ->get();
        }else{
            $userCount = DB::table('users')->join('users_additional_info',"users_additional_info.user_id","=",'users.id')
                    ->select("users.id as cid","users.first_name","users.last_name",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as fullname'))
                    ->where('users_additional_info.company_id',$companyID)
                    ->get();
        }
        
        if(!empty($userCount)){
            foreach($userCount as $k=>$v){
                $v->cid=base64_encode($v->cid);
            }
        }            
        return json_encode($userCount); 
    }

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


    public function getActiveCaseCounterAttribute(){
        $case = CaseStaff::leftJoin('case_master','case_master.id',"=","case_staff.case_id");
        $case = $case->where("case_staff.user_id",$this->id);
        $case = $case->where("case_master.is_entry_done","1");
        $case = $case->where("case_master.case_close_date",null);
        $totalData=$case->count();
        return $totalData;
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
        return $this->belongsToMany(CaseMaster::class, 'case_client_selection', 'selected_user', 'case_id')->where("is_entry_done", "1")->whereNull('case_client_selection.deleted_at');
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
     * Get user full address attribute
     */
    public function getFullAddressAttribute()
    {
        return $this->apt_unit.', '.$this->street.', '.$this->city.', '.$this->state.', '.$this->postal_code.', '.$this->country;
    }
}
