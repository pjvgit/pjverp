<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Validator, Response, Mail,Storage,DB;
use App\User,App\CaseActivity,App\AccountActivity;
use App\CaseClientSelection,App\ClientActivity,App\UsersAdditionalInfo;
use App\Rules\UniqueEmail;
use App\TaskTimeEntry,App\ExpenseEntry,App\ViewCaseState,App\CaseStaff,App\FlatFeeEntry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class BaseController extends Controller
{   
    public function __construct(){
    }
    
    public function sendMail($user){
        try{
             Mail::send('emails.reminder', ['user' => $user], function ($m) use ($user) {
                $m->from($user['from'], $user['from_title']);
                if(isset($user['replyto'])){
                    $m->replyTo($user['replyto'], $user['replyto_title']);
                }
                
                $m->to($user['to'],$user['full_name'])->subject($user['subject']);
            });
            
            if( count(Mail::failures()) > 0 ) {
                foreach(Mail::failures() as $email_address) {
                    \Log::info("failed email:". $email_address);
                    return 0;
                }
            } else {
                return 1;
                Log::info("email sent");
            }
        }
        catch(\Exception $e){
            \Log::info("mail sent failed:". $e->getMessage());
            return 0;
        }
    }
    public function sendMailWithAttachment($user,$files){
        Mail::send('emails.reminder', ['user' => $user], function ($m) use ($user,$files) {
            $m->from($user['from'], $user['from_title']);

            $m->to($user['to'],$user['full_name'])->subject($user['subject']);
            foreach ($files as $file){
                $m->attach($file);
            }
        });
        
        if( count(Mail::failures()) > 0 ) {
           foreach(Mail::failures() as $email_address) {
               return 0;
            }
        } else {
            return 1;
        }
    }
    public function uploadImage($imageName, $image , $permission = 'public')
    {
        Storage::disk('s3')->put($imageName, file_get_contents($image), $permission);

        $ursll= Storage::disk('s3')->url($imageName);
        return $ursll;
    }

    public function removeImage($imageName)
    {
        if($this->fileExists($imageName)) {
            $response = Storage::disk('s3')->delete($imageName);
        }
        return TRUE;
    }

    public function fileExists($imageName)
    {
        if(Storage::disk('s3')->exists($imageName)){
            return TRUE;
        }
        return FALSE;
    }

    
    public function copyimages($imageName,$destifile)
    {
        if(Storage::disk('s3')->exists($imageName)){
            Storage::disk('s3')->copy($imageName, $destifile);
            return TRUE;
        }
        return FALSE;
    }
    public function validate_email(Request $request) {
        $request->request->add(['user_id' => $request->id]);
        if ($request->input('email') !== '') {
            if ($request->input('email')) {
                $rule = array(
                    'email' => 'Required|email'/* |unique:users,email,'.$request->input('id').',id,deleted_at,NULL */,
                    'email' => [new UniqueEmail()],
                );
                $validator = \Validator::make($request->all(), $rule);
            }
            if (!$validator->fails()) {
                die('true');
            }
        }
        die('false');
    }

    public function caseActivity($data){
        $CaseActivity = new CaseActivity;
        $CaseActivity->case_id=$data['case_id'];
        $CaseActivity->activity_title=$data['activity_title']??NULL;
        $CaseActivity->activity_type=($data['activity_type'])??NULL;
        $CaseActivity->extra_notes=($data['extra_notes'])??NULL;
        $CaseActivity->staff_id=($data['staff_id'])??NULL;
        $CaseActivity->created_by=Auth::user()->id; 
        $CaseActivity->save();
        return true;
    }

    public function getCurrentDateAndTime(){
        $CommonController= new CommonController();
        $timezone=Auth::User()->user_timezone;
       return  $convertedDate=$CommonController->convertUTCToUserTime(date('Y-m-d H:i:s'),$timezone);
    }

    //return parent and its child user ids.
    public function getParentAndChildUserIds(){
        $getChildUsers = DB::table('users')->select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
        $getChildUsers[]=Auth::user()->id;  
        return $getChildUsers;
    }

    //Return firm user list
    public function getAllFirmUser(){
        $caseStaff = User::select("first_name","last_name","id","user_level","user_title","default_rate")->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->get();
        return $caseStaff;
    }
    //Return firm user list
    public function getClientWiseCaseList($client_id){
        $CaseClientSelection = CaseClientSelection::select("case_id")->where("selected_user",$client_id)->get()->pluck('case_id');
        return $CaseClientSelection;
    }
     //Return case list wich is align with staff member
     public function getStaffWiseCaseList($staff_id){
        $getStaffWiseCaseList = CaseStaff::select("case_id")->where("user_id",$staff_id)->get()->pluck('case_id');
        return $getStaffWiseCaseList;
    }
    //Return client list
    public function getCompanyWiseCaseList($company_id){
        $CaseClientSelection = UsersAdditionalInfo::select("user_id")->whereRaw("find_in_set($company_id,`multiple_compnay_id`)")->get()->pluck('user_id');
        return $CaseClientSelection;
    }
    public function saveClientActivity($historyData)
    {
        $ClientActivity = new ClientActivity; 
        $ClientActivity->acrtivity_title=$historyData['acrtivity_title'];
        $ClientActivity->activity_by=$historyData['activity_by'];
        $ClientActivity->activity_for =$historyData['activity_for'];
        $ClientActivity->type =$historyData['type'];
        $ClientActivity->task_id =$historyData['task_id'];
        $ClientActivity->case_id =$historyData['case_id'];
        $ClientActivity->created_by=Auth::User()->id;
        $ClientActivity->created_at=date('Y-m-d H:i:s');
        $ClientActivity->save();
    }
    /* public function saveAccountActivity($historyData)
    {
        $AccountActivity = new AccountActivity; 
        $AccountActivity->user_id=$historyData['user_id'];
        $AccountActivity->related_to=$historyData['related_to'];
        $AccountActivity->case_id =$historyData['case_id'];
        $AccountActivity->credit_amount =$historyData['credit_amount'];
        $AccountActivity->debit_amount =$historyData['debit_amount'];
        $AccountActivity->total_amount =$historyData['total_amount'];
        $AccountActivity->entry_date =$historyData['entry_date'];
        $AccountActivity->status =$historyData['status'];
        $AccountActivity->notes =$historyData['notes'];
        $AccountActivity->pay_type =$historyData['pay_type'];
        $AccountActivity->firm_id =$historyData['firm_id'];
        $AccountActivity->section =$historyData['section'];
        if(isset($historyData['from_pay'])){
            $AccountActivity->from_pay =$historyData['from_pay'];
        }
        $AccountActivity->created_by=Auth::User()->id;
        $AccountActivity->created_at=date('Y-m-d H:i:s');
        $AccountActivity->save();
        return true;
    } */
    public function generateUniqueToken()
    {
        return Str::random(250)."-".time(); 
    }
    public function getFlatfeeEntryTotalByCase($case_id){
        $timeTotalBillable=$timeTotalNonBillable=0;
        $flatFeeData = FlatFeeEntry::select("*")->where('case_id', $case_id)->where("time_entry_billable","yes")->get();
        foreach($flatFeeData as $TK=>$TE){
            if($TE->status == 'paid'){
                $timeTotalBillable+=str_replace(",","",number_format($TE['cost'], 2));
            }
        }
        $FlatFeeEntry['case_id']=$case_id;
        $FlatFeeEntry['billable_entry']=$timeTotalBillable;
        $FlatFeeEntry['non_billable_entry']=$timeTotalNonBillable;
        return $FlatFeeEntry;
    }
    public function getTimeEntryTotalByCase($case_id){
        $timeTotalBillable=$timeTotalNonBillable=0;
        $TimeEntry=TaskTimeEntry::select("*")->where("case_id",$case_id)->where('status','unpaid')->get();
        foreach($TimeEntry as $TK=>$TE){
            if($TE['rate_type']=="flat"){
                if($TE['time_entry_billable']=="yes"){
                        $timeTotalBillable+=str_replace(",","",number_format($TE['entry_rate'], 2));
                }else{
                        $timeTotalNonBillable+=str_replace(",","",number_format($TE['entry_rate'], 2));
                }
            }else{
                if($TE['time_entry_billable']=="yes"){
                    $timeTotalBillable+=(str_replace(",","",number_format($TE['entry_rate'], 2)) * str_replace(",","",number_format($TE['duration'], 2)));
                }else{
                    $timeTotalNonBillable+=(str_replace(",","",number_format($TE['entry_rate'], 2)) * str_replace(",","",number_format($TE['duration'], 2)));
                }
            }
        }
        $TimeEntry['case_id']=$case_id;
        $TimeEntry['billable_entry']=$timeTotalBillable;
        $TimeEntry['non_billable_entry']=$timeTotalNonBillable;
        return $TimeEntry;
    }
    public function getExpenseEntryTotalByCase($case_id){
        $expenseTotalBillable=$expenseTotalNonBillable=0;
        $ExpenseEntry=ExpenseEntry::select("*")->where("case_id",$case_id)->where('status','unpaid')->get();
        foreach($ExpenseEntry as $kE=>$vE){
            if($vE['time_entry_billable']=="yes"){
                $expenseTotalBillable+=(str_replace(",","",number_format($vE->cost, 2)) * str_replace(",","",number_format($vE->duration, 2)));
            }else{
                $expenseTotalNonBillable+=(str_replace(",","",number_format($vE->cost, 2)) * str_replace(",","",number_format($vE->duration, 2)));
            }
        }
        $TimeEntry['case_id']=$case_id;
        $TimeEntry['billable_entry']=$expenseTotalBillable;
        $TimeEntry['non_billable_entry']=$expenseTotalNonBillable;
        return $TimeEntry;
    }
    /* public function getTrustBalance($case_id){
        $masterUser=[];
        $totalTrustSum=0;
        $clients=CaseClientSelection::where("case_id", $case_id)->get();
        foreach($clients as $k=>$v){
            $userData = UsersAdditionalInfo::select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"trust_account_balance","users.id as uid","users.user_level")->join('users','users_additional_info.user_id','=','users.id')->where("users.id",$v->selected_user)->first();
            $masterUser[]=$userData;

            $totalTrustSum+=($userData['trust_account_balance'])??0;
        }
        $masterUser['totalTrustSum']=$totalTrustSum;
        return $masterUser;
        
    } */

    
   public function getAllLinkedClients($case_id)
   {
       $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')
       ->leftJoin('users_additional_info','users_additional_info.user_id','=','users.id')
       ->leftJoin('view_client_linked_state','view_client_linked_state.user_id','=','users.id')
       ->select(DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as user_name'),"users.id","users.id as uid","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","case_client_selection.case_id as case_id","case_client_selection.user_role as user_role","contact_group_id","users.user_level","users.user_type","is_billing_contact","client_linked_with_case_counter")->where("case_client_selection.case_id",$case_id)->get();
       
     
       return $caseCllientSelection;
   }
}