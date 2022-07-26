<?php

use App\CaseMaster;
use App\CasePracticeArea;
use App\Firm;
use App\FirmAddress;
use App\FirmSolReminder;
use App\InvoiceCustomizationSetting;
use App\InvoiceCustomizationSettingColumn;
use App\FirmOnlinePaymentSetting;
use App\InvoiceSetting;
use App\LeadAdditionalInfo;
use App\User;
use App\CaseStage;
use App\Event;
use App\EventRecurring;
use App\Invoices;
use App\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\UsersAdditionalInfo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Get lead list for add task
 */
function userLeadList($lead_id = null)
{
    $authUser = auth()->user();
    $leades = User::where("firm_name", $authUser->firm_name)->where("user_type", 5)->where("user_level","5")->whereHas("userLeadAdditionalInfo", function($query) use ($lead_id){
        $query->where("is_converted", "no")->where("user_status", 1);
        if($lead_id != null) {
            $query->orWhere("user_id", $lead_id);
        }
    });
    // As per user permission all user can access all lead
    /* if($authUser->parent_user != 0) {
        $leades = $leades->where("parent_user", $authUser->id);
    } */
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
    // As per user permission, user can view all client/company
    /* if($authUser->parent_user != 0) {
        $clients = $clients->where("parent_user", $authUser->id);
    } */
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
    // As per user permission, user can view all client/company
    /* if($authUser->parent_user != 0) {
        $company = $company->where("parent_user", $authUser->id);
    } */
    return $company->get();
    // User::select("email","first_name","last_name","id","user_level")->whereIn("user_status",[1,2])->where('user_level',4)->where("parent_user",Auth::user()->id)->get();
}

/**
 * Get firm user list
 */
function firmUserList()
{
    // return User::select("first_name","last_name","id","user_level","user_title","default_rate","default_color")->where("firm_name", auth()->user()->firm_name)
                // ->where("user_level","3")
                // ->doesntHave('deactivateUserDetail')->get();
    // return  User::select("first_name","last_name","id","user_level","user_title","default_rate","default_color")
                // ->where("firm_name",Auth::user()->firm_name)
                // ->where("user_level","3")
                // ->where("user_status","1")
                // ->orWhere("id",Auth::user()->id)
                // ->orderBy('first_name','asc')->get();

    return  DB::table('users')->select("first_name","last_name","id","user_level","user_title","default_rate","default_color",DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as full_name'))
                ->where("firm_name",Auth::user()->firm_name)
                ->where("user_level","3")
                ->where("user_status","1")
                ->orWhere("id",Auth::user()->id)
                ->orderBy('first_name','asc')->get();
    
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
 * return parent and its child user ids.
 */
function getParentAndChildUserIds(){
    $getChildUsers = DB::table('users')->select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
    $getChildUsers[]=Auth::user()->id;  
    return $getChildUsers;
}

/**
 * Get case stage list of firm
 */
function caseStageList()
{
    $getChildUsers=getParentAndChildUserIds();
    $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();          
    return $caseStageList;    
}

/**
 * Get firm office (addresses) list
 */
function firmOfficeList()
{
    return FirmAddress::where('firm_id', auth()->user()->firm_name)->pluck("office_name", "id")->toArray();
}

/**
 * Get firm office (addresses) list with countries name
 */
function firmAddressList()
{
    return FirmAddress::select("firm_address.id","countries.name as countryname")
        ->leftJoin('countries','firm_address.country',"=","countries.id")
        ->where("firm_address.firm_id",Auth::User()->firm_name)
        ->orderBy('firm_address.is_primary','ASC')->get();
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
    $str = date('Y-m-d H:i:s', strtotime($str));
    $date = Carbon::createFromFormat('Y-m-d H:i:s', $str, "UTC");
    $date->setTimezone($timezone ?? 'UTC');
    return $date->format("Y-m-d H:i:s");
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
    // if($authUser->parent_user != 0) {
    if($authUser->hasPermissionTo('access_only_linked_cases')) {
        $cases = $cases/* ->where('created_by', $authUser->id) */->whereHas('caseStaffAll', function($query) use($authUser) {
            $query->where('user_id', $authUser->id);
        });
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
function getColumnsIfYes($array, $is_for_view = 'no')
{
    $type = $array['billing_type'];
    $result = array_map(function ($ind, $val) use($type, $is_for_view) {
        if($val == "yes") {
            if($is_for_view == 'yes') {
                if($ind == 'notes') {
                    return $type.' '.$ind;
                } else if($ind == 'line_total') {
                    return 'line total';
                } else if($ind == 'amount' && $type == 'time entry') {
                    return 'rate';
                } else if($ind == 'amount' && $type == 'expense') {
                    return 'cost';
                } else {
                    return $ind;
                }
            } else {
                return $ind;
            }
        }
    }, array_keys($array), $array);
    $finalArr = array_filter($result, function($v) { return !is_null($v); });
    if($is_for_view == 'yes' && $type != 'flat fee') {
        $orderArr = ($type == "time entry") ? timeEntryColumnOrder() : expenseColumnOrder();
        return array_intersect(array_flip($orderArr), $finalArr);
    } else {
        return $finalArr;
    }
}

/**
 * For time entry column display order
 */
function timeEntryColumnOrder()
{
    return [
        'date' => 'date',
        'employee' => 'employee',
        'activity' => 'activity',
        'time entry notes' => 'time entry notes',
        'rate' => 'rate',
        'hour' => 'hour',
        'line total' => 'line total',
    ];
}

/**
 * For expense column display order
 */
function expenseColumnOrder()
{
    return [
        'date' => 'date',
        'employee' => 'employee',
        'expense' => 'expense',
        'expense notes' => 'expense notes',
        'cost' => 'cost',
        'quantity' => 'quantity',
        'line total' => 'line total',
    ];
}

/**
 * get invoice setting
 */
function getInvoiceSetting($firmId = null)
{
    return InvoiceSetting::where("firm_id", auth()->user()->firm_name ?? $firmId)->first();
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
                ->where('user_level', 2)->whereIn("user_status", [1,2])->get()->makeHidden(['caselist']);
}

/**
 * Get firm all company
 */
function firmCompanyList()
{
    $authUser = auth()->user();
    return User::select("id", DB::raw('CONCAT_WS(" ",first_name,middle_name,last_name) as name'), 'user_level', 'email')->where("firm_name", $authUser->firm_name)
            ->whereIn("user_status", [1,2])->where('user_level', 4)->get()->makeHidden(['caselist']);
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
    UsersAdditionalInfo::updateOrCreate(['user_id' => $user_id], ['user_id' => $user_id,'created_by' => Auth::User()->id]);
}

/**
 * check case SOL setting enabled or not
 */
function IsCaseSolEnabled()
{
    $firmData = \App\Firm::find(Auth::User()->firm_name);
    return $firmData->sol;
}

/**
 * Get event reminder 
 */
function getEventReminderTpe()
{
    return [
        "popup" => "Popup",
        "email" => "Email"
    ];
}

/**
 * Get duein/overdue in days text
 */
function getDueText($dueDate)
{
    if(!$dueDate || $dueDate == '9999-12-30') {
        $dueText = "No Due Date";
    } else {
        $dueDate = \Carbon\Carbon::parse($dueDate);
        $currentDate = \Carbon\Carbon::now();
        $difference = $currentDate->diff($dueDate)->days;
        if($dueDate->isToday()) {
            $dueText = "DUE TODAY";
        } else if($dueDate->isTomorrow()) {
            $dueText = "DUE TOMORROW";
        } else if(/* $difference > 1 */$dueDate->gt($currentDate)) {
            $dueText = "DUE IN ".$difference." DAYS";
        } else if($dueDate->lt($currentDate)) {
            $dueText = "OVERDUE";
        } else {
            $dueText = "";
        }
    }
    return $dueText;
}

/**
 * Get encoded/decoded ids
 */
function encodeDecodeId($id, $type)
{
    if($type == "encode")
        return Crypt::encrypt($id);
    else
        return Crypt::decrypt($id);
}

/**
 * Calculate monthly payable amount
 */
function invoiceMonthlyPaymentAmount($amount, $month)
{
    return ceil($amount / $month);
}

/**
 * Get invoice/request online payment settings
 */
function getFirmOnlinePaymentSetting()
{
    return FirmOnlinePaymentSetting::where('firm_id', auth()->user()->firm_name)->first();
}

/**
 * Get call log status for firm setting
 */
function callLogStatus()
{
    $firmData = \App\Firm::find(Auth::User()->firm_name);
    return $firmData->call_log_status;
}

/**
 * List online payment methods
 */
function onlinePaymentMethod()
{
    return [
        'credit-card' => "Credit Card",
        'cash' => "Cash",
        'bank-transfer' => "Bank Transfer",
    ];
}

/**
 * Encode/Decode json array and convert into collection
 */
function encodeDecodeJson($array, $type = "decode")
{
    if($type == 'encode') {
        return json_encode($array);
    } else {
        $array  = json_decode($array);
        return collect($array);
    }
}

/**
 * Get user detail
 */
function getUserDetail($id)
{
    return User::select("first_name","last_name","id","user_level","user_type","user_title","email","default_color","user_timezone")->where("id", $id)->first();
}

/**
 * Get week's nth day
 */
function getWeekNthDay($nthDay)
{
    $array = array(1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth', 5 => 'fifth', 6 => 'sixth', 7 => 'seventh');
    return $array[$nthDay];
}

/**
 * Get invoice unique number using invoice id
 */
function getInvoiceUniqueNumber($invoiceId)
{
    $invoice = Invoices::whereId($invoiceId)->select('unique_invoice_number')->first();
    return $invoice->unique_invoice_number ?? 0;
}

/**
 * Get current logged in user's unread task count
 */
function getUnreadTaskCount()
{
    $authUser = auth()->user();
    $taskCount = Task::where("firm_id", $authUser->firm_name)->whereHas("taskLinkedStaff", function($query) use($authUser) {
                    $query->where("users.id", $authUser->id)->where('task_linked_staff.is_read', 'no');
                })->count();
    return $taskCount ?? 0;
}

/**
 * COnvert UTC timestamp to user timezone
 */
function convertToUserTimezone($str, $timezone){
    $str = date('Y-m-d H:i:s', strtotime($str));
    $date = Carbon::createFromFormat('Y-m-d H:i:s', $str, "UTC");
    $date->setTimezone($timezone ?? 'UTC');
    return $date;
}

/**
 * Get current logged in user's unread event count
 */
function getUnreadEventCount()
{
    $authUser = auth()->user();
    $authUserId = (string) $authUser->id;
    $eventCount = Event::where("firm_id", $authUser->firm_name)->whereHas("eventRecurring", function($query) use($authUserId) {
                    $query->whereJsonContains('event_linked_staff', ["user_id" => $authUserId, "is_read" => 'no']);
                })->count();
    return $eventCount ?? 0;
}