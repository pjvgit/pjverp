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
    protected $appends  = ['decode_id','additioninfo','createdby','lastloginnewformate','caselist','clientwise_caselist','contactlist','active_case_counter'];

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
    public function getClientWiseCaselistAttribute(){
        $ContractUserCase =  CaseMaster::join('case_client_selection','case_master.id','=','case_client_selection.case_id')
        ->select("case_master.case_title","case_master.id as cid","case_master.case_unique_number as case_unique_number")
        ->where('case_client_selection.selected_user',$this->id)
        ->where("case_master.is_entry_done","1")
        ->groupBy("case_master.id")  
        ->orderBy("case_master.id","DESC")  
        ->get();
        return json_encode($ContractUserCase); 
    }
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
        $userCount = DB::table('users')->join('users_additional_info',"users_additional_info.user_id","=",'users.id')
                    ->select("users.id as cid","users.first_name","users.last_name",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as fullname'))
                    // ->where('users_additional_info.company_id',$this->id)
                    ->whereRaw("find_in_set($this->id,`multiple_compnay_id`)")
                    ->get();
        
        if(!$userCount->isEmpty()){
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
        $g=CaseMaster::leftJoin('case_staff','case_master.id','=','case_staff.case_id')
        ->where("case_staff.user_id",$this->id)
        ->where("case_master.case_close_date",NULL)
        ->get();
        return count($g);
    }

}
