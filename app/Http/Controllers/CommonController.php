<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB, Exception;
use Illuminate\Support\Facades\Input;
use DateTime,DateTimeZone;
use Carbon\Carbon;
use Validator, Response, Mail,Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\AllHistory,App\InvoiceHistory;
use Illuminate\Support\Str;

class CommonController extends BaseController {
  
    //TO UTC
    public function convertTimeToUTCzone($str,$timezone){
        $timestamp = $str;
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $timezone);
        $date->setTimezone('UTC');
        return $NewDate= $date->format("Y-m-d H:i:s");
    }

    //TO USER
    public function convertUTCToUserTime($str, $timezone){
        $timestamp = $str;
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, "UTC");
        $date->setTimezone($timezone);
        return $NewDate= $date->format("Y-m-d H:i:s");
    }
    //Return remains days
    public function daysReturns($date1){
        $date1 = $date1;
        $date2 = date('Y-m-d');
        
        $diff = abs(strtotime($date2) - strtotime($date1));
        
        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
        $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
        
        if($years!="0"){
            return $years ." years";
        }else if($months!="0"){
            return $months." months";
        }else if($days!="0"){
            return $days. " days";
        }
        
    }
    public function getUserTypeText($user_level){
        $Title="";
        if($user_level==1){
            $Title="Admin";
        }else if($user_level==2){
            $Title="Client";
        }else if($user_level==3){
            $Title="User";
        }else if($user_level==4){
            $Title="Company";
        }else if($user_level==5){
            $Title="Lead";
        }
        return $Title;
    }
    public function getUserLevelText($user_level){
        $Title="";
        if($user_level==1){
            $Title="Attorney";
        }else if($user_level==2){
            $Title="Paralegal";
        }else if($user_level==3){
            $Title="Staff";
        }else if($user_level==4){
            $Title="Company";
        }else if($user_level==5){
            $Title="Lead";
        }
        return $Title;
    }
    public function addMultipleHistory($data){
        $authUserNotificationSetting = auth()->user()->userNotificationSetting()->where('user_notification_settings.for_feed', 'yes')->get();
        
        $AllHistory=new AllHistory;
        $AllHistory->case_id=($data['case_id'])??NULL;
        $AllHistory->user_id=($data['user_id'])??NULL;
        $AllHistory->expense_id=($data['expense_id'])??NULL;
        $AllHistory->time_entry_id=($data['time_entry_id'])??NULL;
        $AllHistory->activity=($data['activity'])??NULL;
        $AllHistory->activity_for=($data['activity_for'])??NULL;
        $AllHistory->notes_for_client=($data['notes_for_client'])??NULL;
        $AllHistory->notes_for_company=($data['notes_for_company'])??NULL;
        $AllHistory->notes_for_case=($data['notes_for_case'])??NULL;
        $AllHistory->event_for_case=($data['event_for_case'])??NULL;
        $AllHistory->event_for_lead=($data['event_for_lead'])??NULL;
        $AllHistory->event_id=($data['event_id'])??NULL;
        $AllHistory->event_name=($data['event_name'])??NULL;
        $AllHistory->task_for_case=($data['task_for_case'])??NULL;
        $AllHistory->task_for_lead=($data['task_for_lead'])??NULL;
        $AllHistory->task_id=($data['task_id'])??NULL;
        $AllHistory->task_name=($data['task_name'])??NULL;
        $AllHistory->deposit_id=($data['deposit_id'])??NULL;
        $AllHistory->deposit_for=($data['deposit_for'])??NULL;
        $AllHistory->type=($data['type'])??NULL;
        $AllHistory->action=($data['action'])??NULL;
        $AllHistory->client_id=($data['client_id'])??NULL;
        $AllHistory->is_for_client = $data['is_for_client'] ?? 'no';
        $AllHistory->firm_id=Auth::User()->firm_name;
        $AllHistory->created_by=Auth::User()->id;
        $AllHistory->created_at=date('Y-m-d H:i:s');  
        Log::info("History Type > ". $data['type'] ." and action > ".$data['action']);
        $viewInMail = 0;
        // echo $data['type'] ."-->". $data['action']; echo PHP_EOL;
        foreach($authUserNotificationSetting as $n => $setting){
            if($setting->sub_type == $data['type'] && $setting->action == $data['action']){
                // echo $data['type'] ."-->". $data['action']."--> 121 -->".$setting->sub_type.'--->'.$setting->action; echo PHP_EOL;
                $viewInMail = 1;
            }
            if($setting->sub_type == 'notes' && $data['type'] == 'notes'){
                $viewInMail = 1;
            }
            if($setting->sub_type == 'time_entry' && $data['type'] == 'expenses'){
                $viewInMail = 1;
            }
        }                    
        $ignoreTypes = ['fundrequest','credit','deposit'];
        $ignoreActions = ['pay_delete'];
        if($viewInMail == 1){
            $AllHistory->save();
        }else{
            if(in_array($data['type'],$ignoreTypes)){
                $AllHistory->save();
            }
            if(in_array($data['action'],$ignoreActions)){
                $AllHistory->save();
            }
        }        
        // $AllHistory->save();
        return true;
    }

    
    public function invoiceHistory($historyData)
    {
        $InvoiceHistory = new InvoiceHistory; 
        $InvoiceHistory->invoice_id=($historyData['invoice_id'])??NULL;
        $InvoiceHistory->lead_invoice_id=($historyData['lead_invoice_id'])??NULL;
        $InvoiceHistory->lead_id=($historyData['lead_id'])??NULL;
        $InvoiceHistory->lead_message=($historyData['lead_message'])??NULL;
        $InvoiceHistory->acrtivity_title= ($historyData['acrtivity_title'])??NULL;
        $InvoiceHistory->pay_method= ($historyData['pay_method'])??NULL;
        $InvoiceHistory->amount= (str_replace(",","",$historyData['amount']))??NULL;
        $InvoiceHistory->responsible_user= ($historyData['responsible_user'])??NULL;
        $InvoiceHistory->deposit_into= ($historyData['deposit_into'])??NULL;
        $InvoiceHistory->deposit_into_id= ($historyData['deposit_into_id'])??NULL;
        $InvoiceHistory->invoice_payment_id= ($historyData['invoice_payment_id'])??NULL;
        $InvoiceHistory->notes= ($historyData['notes'])??NULL;
        $InvoiceHistory->status= ($historyData['status'])??0;
        $InvoiceHistory->refund_ref_id= ($historyData['refund_ref_id'])??NULL;
        $InvoiceHistory->created_by=$historyData['created_by'];
        $InvoiceHistory->created_at=$historyData['created_at'];
        $InvoiceHistory->save();
        return true;
    }
    
    public static function getToken(){
        return substr(sha1(rand()), 0, 15);
    }
    public static function getUniqueToken(){
        return $var = Str::random(128)."-".time();
        // return substr(sha1(rand(100,200)), 0, 200).time();
    }
}
