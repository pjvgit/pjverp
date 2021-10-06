<?php
 
namespace App\Traits;

use App\AccountActivity;
use Illuminate\Support\Facades\Log;

trait TrustAccountActivityTrait {
    /**
     * Save account activity
     */
    public function saveAccountActivity($historyData)
    {
        $AccountActivity = new AccountActivity(); 
        $AccountActivity->user_id=$historyData['user_id'];
        $AccountActivity->related_to=$historyData['related_to'];
        $AccountActivity->case_id =$historyData['case_id'];
        $AccountActivity->credit_amount =$historyData['credit_amount'];
        $AccountActivity->debit_amount =$historyData['debit_amount'];
        $AccountActivity->total_amount =$historyData['total_amount'];
        $AccountActivity->entry_date =$historyData['entry_date'];
        $AccountActivity->status =$historyData['status'];
        $AccountActivity->payment_method=$historyData['payment_method'];
        $AccountActivity->payment_type=$historyData['payment_type'];
        // $AccountActivity->notes =$historyData['notes'];
        $AccountActivity->notes = $this->getPaymentNote($historyData['payment_type']);
        $AccountActivity->pay_type =$historyData['pay_type'];
        $AccountActivity->firm_id =$historyData['firm_id'];
        $AccountActivity->section =$historyData['section'];
        if(isset($historyData['from_pay'])){
            $AccountActivity->from_pay =$historyData['from_pay'];
        }
        $AccountActivity->created_by= auth()->id();
        $AccountActivity->created_at=date('Y-m-d H:i:s');
        $AccountActivity->save();
        return true;
    }

    public function updateTrustAccountActivity($request, $amtAction = null, $InvoiceData = null, $isDebit = "no")
    {
        $authUser = auth()->user();
        $AccountActivityData=AccountActivity::select("*")->where("firm_id",$authUser->firm_name)->where("pay_type","trust")->orderBy("id","DESC")->first();
        $activityHistory=[];
        $activityHistory['user_id']=$request->trust_account;
        $activityHistory['related_to']=($InvoiceData) ? $InvoiceData['id'] : NULL;
        $activityHistory['case_id']= ($InvoiceData) ? $InvoiceData['case_id'] : NULL;
        $activityHistory['debit_amount']= ($isDebit == "yes") ? $request->amount : 0.00;
        $activityHistory['credit_amount']=($isDebit == "no") ? $request->amount : 0.00;
        $activityHistory['total_amount']= ($AccountActivityData) ? $AccountActivityData['total_amount'] + $request->amount : $request->amount;
        if(!empty($AccountActivityData)){
            if($amtAction == "sub")
                $activityHistory['total_amount'] = $AccountActivityData['total_amount'] - $request->amount;
            else
                $activityHistory['total_amount'] = $AccountActivityData['total_amount'] + $request->amount;
        }else{
            $activityHistory['total_amount'] = $request->amount;
        }
        $activityHistory['entry_date']=date('Y-m-d');
        $activityHistory['payment_method']=$request->payment_method;
        $activityHistory['payment_type']=$request->payment_type;
        $activityHistory['notes']=$request->notes;
        $activityHistory['status']="unsent";
        $activityHistory['pay_type']="trust";
        $activityHistory['firm_id']=$authUser->firm_name;
        if(isset($request->applied_to) && $request->applied_to!=0){
        $activityHistory['section']="request";
        $activityHistory['related_to']=$request->applied_to;
        }else{
        $activityHistory['section']="other";
        $activityHistory['related_to']=NULL;
        }
        $activityHistory['created_by']=$authUser->id;
        $activityHistory['created_at']=date('Y-m-d H:i:s');
        $this->saveAccountActivity($activityHistory);
    }

    public function updateClientPaymentActivity($request, $InvoiceData, $isDebit = "no")
    {
        $authUser = auth()->user();
        $AccountActivityData=AccountActivity::select("*")->where("firm_id",$authUser->firm_name)->where("pay_type","client")->orderBy("id","DESC")->first();
        $activityHistory=[];
        $activityHistory['user_id']=$request->contact_id;
        $activityHistory['related_to']=$InvoiceData['id'];
        $activityHistory['case_id']=$InvoiceData['case_id'];
        $activityHistory['debit_amount']= ($isDebit == "yes") ? $request->amount : 0.00;
        $activityHistory['credit_amount']=($isDebit == "no") ? $request->amount : 0.00;
        if(!empty($AccountActivityData)){
            $activityHistory['total_amount']=$AccountActivityData['total_amount']+$request->amount;

        }else{
            $activityHistory['total_amount']=$request->amount;
        }

        // $activityHistory['total_amount']=$AccountActivityData['total_amount']+$request->amount;
        $activityHistory['entry_date']=date('Y-m-d');
        $activityHistory['payment_method']=$request->payment_method;
        $activityHistory['payment_type']=$request->payment_type;
        $activityHistory['notes']=$request->notes;
        $activityHistory['status']="unsent";
        $activityHistory['pay_type']="client";
        $activityHistory['from_pay']="trust";
        $activityHistory['firm_id']=$authUser->firm_name;
        $activityHistory['section']="invoice";
        $activityHistory['created_by']=$authUser->id;
        $activityHistory['created_at']=date('Y-m-d H:i:s');
        $this->saveAccountActivity($activityHistory);
    }

    public function getPaymentNote($paymentType, $is_refunded = "no")
    {
        $isRefund = ($is_refunded == "yes") ? "(Refunded)" : "";
        if($paymentType=="withdraw"){
            $ftype="Withdraw from Trust (Trust Account)";
        }else if($paymentType=="refund_withdraw"){
            $ftype="Refund Withdraw from Trust (Trust Account)";
        }else if($paymentType=="refund_deposit"){
            $ftype="Refund Deposit into Trust (Trust Account)";
        }else if($paymentType=="payment"){
            $ftype = "Payment from Trust (Trust Account) to Operating (Operating Account)";
        }else if($paymentType=="payment deposit"){
            $ftype = "Payment into Trust (Trust Account)";
        }else if($paymentType=="refund payment deposit"){
            $ftype = "Refund Payment into Trust (Trust Account)";
        }else if($paymentType=="refund payment"){
            $ftype = "Refund Payment from Trust (Trust Account) to Operating (Operating Account)";
        }else{
            $ftype="Deposit into Trust (Trust Account)";
        }
        return $ftype.' '.$isRefund;
    }
}
 