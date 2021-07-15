<?php

use App\CaseMaster;
use App\CasePracticeArea;
use App\Firm;
use App\FirmAddress;
use App\LeadAdditionalInfo;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Get lead list for add task
 */
function userLeadList()
{
    $authUser = auth()->user();
    $leades = User::where("firm_name", $authUser->firm_name)->where("user_type", 5)->where("user_level","5")->whereHas("userLeadAdditionalInfo", function($query) {
        $query->where("is_converted", "no")->where("user_status", 1);
    });
    if($authUser->parent_user != 0) {
        $leades = $leades->where("parent_user", $authUser->id);
    }
    return $leades->get()->pluck("full_name", "id");
    // LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->where("lead_additional_info.user_status", 1)->get();
}

/**
 * Get firm user/owner clients
 */
function userClientList()
{
    $authUser = auth()->user();
    $clients = User::select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where("firm_name", $authUser->firm_name)
                ->where('user_level', 2)->whereIn("user_status", [1,2]);
    if($authUser->parent_user != 0) {
        $clients = $clients->where("parent_user", $authUser->id);
    }
    return $clients->pluck("name", "id");
    // User::select("email","first_name","last_name","id","user_level",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where('user_level',2)->whereIn("user_status",[1,2])->where("parent_user",Auth::user()->id)->get();
}

/**
 * Get firm user/owner company
 */
function userCompanyList()
{
    $authUser = auth()->user();
    $company = User::where("firm_name", $authUser->firm_name)->whereIn("user_status", [1,2])->where('user_level', 4);
    if($authUser->parent_user != 0) {
        $company = $company->where("parent_user", $authUser->id);
    }
    return $company->get()->pluck("full_name", "id");
    // User::select("email","first_name","last_name","id","user_level")->whereIn("user_status",[1,2])->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
}

/**
 * Get firm user list
 */
function firmUserList()
{
    return User::select("first_name","last_name","id","user_level","user_title","default_rate","default_color")->where("firm_name", auth()->user()->firm_name)
                ->where("user_level","3")->doesntHave('deactivateUserDetail')->get();
    /* $loadFirmUser = User::select("first_name","last_name","id","user_level","user_title","default_rate");
    $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
    $getChildUsers[]=Auth::user()->id;
    $getChildUsers[]="0"; //This 0 mean default category need to load in each user
    $loadFirmUser= $loadFirmUser->whereIn("id",$getChildUsers)->where("user_level","3")->get(); */
}

/*
* Begine Transaction.                  
*/
function dbStart()
{
    return DB::beginTransaction();
}

/*
* Commit Transaction.     
*/
function dbCommit()
{
    return DB::commit();
}

/**
 * RollBack Transaction.                    
 */
function dbEnd()
{
    return DB::rollback();
}

/**
 * Get practice area list of firm
 */
function casePracticeAreaList()
{
    return CasePracticeArea::where("status", "1")->where("firm_id", auth()->user()->firm_name)->pluck("title", "id")->toArray();  
}

/**
 * Get firm office (addresses) list
 */
function firmOfficeList()
{
    return FirmAddress::where('firm_id', auth()->user()->firm_name)->pluck("office_name", "id")->toArray();
}

//TO UTC
function convertTimeToUTCzone($str,$timezone){
    $timestamp = $str;
    $date = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $timezone);
    $date->setTimezone('UTC');
    return $NewDate= $date->format("Y-m-d H:i:s");
}

//TO USER
function convertUTCToUserTime($str, $timezone){
    $timestamp = $str;
    $date = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, "UTC");
    $date->setTimezone($timezone);
    return $NewDate= $date->format("Y-m-d H:i:s");
}

/**
 * Reminder user type
 */
function reminderUserType()
{
    return [
        "me" => "Me",
        "attorney" => "Attorneys",
        "paralegal" => "Paralegals",
        "staff" => "Staff",
        "client-lead" => "Clients/Leads",
    ];
}

/**
 * Fee structure list
 */
function feeStructureList()
{
    return [
        "hourly" => "Hourly",
        "contingency" => "Contingency",
        "flat" => "Flat",
        "mixed" => "Mixed",
        "pro_bono" => "Pro Bono",
    ];
}

/**
 * Get firm detail
 */
function firmDetail($firmId)
{
    return $firm = Firm::where("id", $firmId)->first();
}

/**
 * Get user case list
 */
function userCaseList()
{
    $authUser = auth()->user();
    $cases = CaseMaster::where("firm_id", $authUser->firm_name)->where('is_entry_done',"1");
    if($authUser->parent_user != 0) {
        $cases = $cases->where('created_by', $authUser->id);
    }
    return $cases->select('id', 'case_title', 'case_number', 'case_close_date')->get();
}

/**
 * Convert date UTC to user timezone
 */
function convertUTCToUserDate($str, $timezone){
    $timestamp = $str;
    $date = Carbon::createFromFormat('Y-m-d', $timestamp, "UTC");
    $date->setTimezone($timezone);
    return $NewDate= $date->format("Y-m-d");
}

//TO UTC
function convertDateToUTCzone($str,$timezone){
    $timestamp = $str;
    $date = Carbon::createFromFormat('Y-m-d', $timestamp, $timezone);
    $date->setTimezone('UTC');
    return $NewDate= $date->format("Y-m-d");
}

/**
 * Get user type list
 */
function userTypeList()
{
    return [
        "1"=>"Attorney","2"=>"Paralegal","3"=>"Staff"
    ];
}

/**
 * Get user Level list
 */
function userLevelList()
{
    return [
        "1"=>"Admin","2"=>"Client","3"=>"User", "4" => "Company", "5" => "Lead"
    ];
}

/**
 * Show this list in invoice setting
 */
function trustCreditDisplayList()
{
    return [
        'dont show' => "Don't Show",
        'show account summary' => "Show Account Summary",
        'show account history' => "Show Account History",
    ];
}

/**
 * invoice payment term list
 */
function invoicePaymentTermList()
{
    return [
        "" => "",
        "due date" => "Due Date",
        "due on receipt" => "Due on Receipt",
        "net 15" => "Net 15",
        "net 30" => "Net 30",
        "net 60" => "Net 60",
    ];
}