<?php

use App\CaseMaster;
use App\CasePracticeArea;
use App\Firm;
use App\FirmAddress;
use App\FirmSolReminder;
use App\InvoiceCustomizationSetting;
use App\InvoiceCustomizationSettingColumn;
use App\InvoiceSetting;
use App\LeadAdditionalInfo;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\UsersAdditionalInfo;

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
    $clients = User::select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'user_level')->where("firm_name", $authUser->firm_name)
                ->where('user_level', 2)->whereIn("user_status", [1,2]);
    if($authUser->parent_user != 0) {
        $clients = $clients->where("parent_user", $authUser->id);
    }
    // return $clients->pluck("name", "id");
    return $clients->get();
    // User::select("email","first_name","last_name","id","user_level",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'))->where('user_level',2)->whereIn("user_status",[1,2])->where("parent_user",Auth::user()->id)->get();
}

/**
 * Get firm user/owner company
 */
function userCompanyList()
{
    $authUser = auth()->user();
    $company = User::select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'user_level')->where("firm_name", $authUser->firm_name)->whereIn("user_status", [1,2])->where('user_level', 4);
    if($authUser->parent_user != 0) {
        $company = $company->where("parent_user", $authUser->id);
    }
    // return $company->get()->pluck("full_name", "id");
    return $company->get();
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

function convertUTCToUserTimeZone($type){
    if ($type == 'dateOnly'){
        $returnDate = \Carbon\Carbon::now((!(empty(Auth::User()->user_timezone))) ? Auth::User()->user_timezone : 'UTC')->format('m/d/Y');
    }else if ($type == 'timeOnly'){
        $returnDate = \Carbon\Carbon::now((!(empty(Auth::User()->user_timezone))) ? Auth::User()->user_timezone : 'UTC')->format('H:i');
    }else{
        $returnDate = \Carbon\Carbon::now((!(empty(Auth::User()->user_timezone))) ? Auth::User()->user_timezone : 'UTC')->format('m/d/Y H:i:s');
    }
    return $returnDate; 
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
function convertUTCToUserDate($timestamp, $timezone){
    $date = Carbon::createFromFormat('Y-m-d', $timestamp, "UTC");
    $date->setTimezone($timezone);
    // return $date->format("Y-m-d");
    return $date;
}

//TO UTC
function convertDateToUTCzone($timestamp,$timezone){
    $date = Carbon::createFromFormat('Y-m-d', $timestamp, $timezone);
    $date->setTimezone('UTC');
    return $date->format("Y-m-d");
}

/**
 * Get user type list
 */
function userTypeList()
{
    return [
        "1" => "Attorney", "2" => "Paralegal", "3" => "Staff", "4" => "None", "5" => "Lead"
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
        "5" => "",
        "0" => "Due Date",
        "1" => "Due on Receipt",
        "2" => "Net 15",
        "3" => "Net 30",
        "4" => "Net 60",
    ];
}

/**
 * Invoice reminder schedule type list
 */
function reminderScheduleTypeList()
{
    return [
        "due in" => "Due in",
        "on the due date" => "On the Due Date",
        "overdue by" => "Overdue by",
    ];
}

/**
 * Check and get table column name if value is yes and remove null value from array
 */
function getColumnsIfYes($array)
{
    $result = array_map(function ($ind, $val) {
        if($val == "yes") {
            return $ind;
        }
    }, array_keys($array), $array);
    return array_filter($result, function($v) { return !is_null($v); });
}

/**
 * get invoice setting
 */
function getInvoiceSetting()
{
    return InvoiceSetting::where("firm_id", auth()->user()->firm_name)->first();
}

/**
 * get customize setting
 */
function getCustomizeSetting()
{
    return InvoiceCustomizationSetting::where("firm_id", auth()->user()->firm_name)->with('flatFeeColumn', 'timeEntryColumn', 'expenseColumn')->first();
}

/**
 * Get flat fee column array
 */
function getFlatFeeColumnArray()
{
    $columns = InvoiceCustomizationSettingColumn::where("firm_id", auth()->user()->firm_name)->where("billing_type", 'flat fee')->first();
    if($columns) {
        $columns = getColumnsIfYes($columns->toArray());
    } else {
        $columns = [];
    }
    return $columns;
}

/**
 * Get time entry column array
 */
function getTimeEntryColumnArray()
{
    $columns = InvoiceCustomizationSettingColumn::where("firm_id", auth()->user()->firm_name)->where("billing_type", 'time entry')->first();
    if($columns) {
        $columns = getColumnsIfYes($columns->toArray());
    } else {
        $columns = [];
    }
    return $columns;
}

/**
 * Get time entry column array
 */
function getExpenseColumnArray()
{
    $columns = InvoiceCustomizationSettingColumn::where("firm_id", auth()->user()->firm_name)->where("billing_type", 'expense')->first();
    if($columns) {
        $columns = getColumnsIfYes($columns->toArray());
    } else {
        $columns = [];
    }
    return $columns;
}

/**
 * Get trust account history list to show on invoice
 */
function trustAccountHistoryList()
{
    return [
        "dont show" => "Don't show on invoice",
        "trust account summary" => "Show Trust Account Summary",
        "trust account history" => "Show Trust Account History",
    ];
}

/**
 * Get credit account history list to show on invoice
 */
function creditAccountHistoryList()
{
    return [
        "dont show" => "Don't show on invoice",
        "credit account summary" => "Show Credit Account Summary",
        "credit account history" => "Show Credit Account History",
    ];
}

/**
 * Get user type text using user level
 */
function getUserTypeText() {
    return [
        "1" => "Admin",
        "2" => "Client",
        "3" => "User",
        "4" => "Company",
        "5" => "Lead",
    ];
}

/**
 * Get firm all clients
 */
function firmClientList()
{
    $authUser = auth()->user();
    return User::select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'user_level', 'email')->where("firm_name", $authUser->firm_name)
                ->where('user_level', 2)->whereIn("user_status", [1,2])->get();
}

/**
 * Get firm all company
 */
function firmCompanyList()
{
    $authUser = auth()->user();
    return User::select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'user_level', 'email')->where("firm_name", $authUser->firm_name)->whereIn("user_status", [1,2])->where('user_level', 4)->get();
}

/**
 * Get firm all lead
 */
function firmLeadList()
{
    $authUser = auth()->user();
    return User::leftJoin('lead_additional_info','lead_additional_info.user_id','=','users.id')->select("users.id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'users.user_level', 'users.email')
    ->where("users.firm_name", $authUser->firm_name)
    ->where('users.user_type', 5)
    ->where('users.user_level', 5)
    ->where("lead_additional_info.is_converted","no")
    ->where("lead_additional_info.user_status","1")->get();    
}

function getTimezoneList() {
    /* * get dynamically timezone data* */
    $timezones = [];

    foreach (timezone_identifiers_list() as $timezone) {
        $datetime = new \DateTime('now', new DateTimeZone($timezone));
        $timezones[] = [
            'sort' => str_replace(':', '', $datetime->format('P')),
            'offset' => $datetime->format('P'),
            'name' => str_replace('_', ' ', implode(', ', explode('/', $timezone))),
            'timezone' => $timezone,
        ];
    }

    usort($timezones, function($a, $b) {
        return $a['sort'] - $b['sort'] ?: strcmp($a['name'], $b['name']);
    });

    $timezoneData = [];
    foreach ($timezones as $key => $timezone) {
        $timezoneData['(GMT ' . $timezone['offset'] . ') - ' . $timezone['timezone']] = $timezone['timezone'];
    }

    return serialize($timezoneData);
}

/**
 * Get firm SOL default reminder
 */
function firmSolReminders()
{
    return FirmSolReminder::where("firm_id", auth()->user()->firm_name)->get();
}
// added trust history in user_additional_info
function checkLeadInfoExists($user_id){
    $UsersAdditionalInfo= UsersAdditionalInfo::firstOrNew(array('id' => $user_id));
    $UsersAdditionalInfo->user_id=$user_id; 
    $UsersAdditionalInfo->created_by =Auth::User()->id;
    $UsersAdditionalInfo->save();
}