<?php
 
namespace App\Traits;

use App\AccountActivity;
use Exception;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Null_;

trait TrustAccountActivityTrait {
    /**
     * Save account activity
     */
    public function saveAccountActivity($historyData)
    {
        $AccountActivity = new AccountActivity(); 
        $AccountActivity->user_id=$historyData['user_id'];
        $AccountActivity->related_to=$historyData['related_to'];
        $AccountActivity->case_id =$historyData['case_id'] ?? NULL;
        $AccountActivity->credit_amount =$historyData['credit_amount'];
        $AccountActivity->debit_amount =$historyData['debit_amount'];
        $AccountActivity->total_amount =$historyData['total_amount'];
        $AccountActivity->entry_date =$historyData['entry_date'];
        $AccountActivity->status =$historyData['status'];
        $AccountActivity->payment_method=$historyData['payment_method'];
        $AccountActivity->payment_type=$historyData['payment_type'];
        $AccountActivity->payment_status=$historyData['payment_status'] ?? NULL;
        $AccountActivity->invoice_history_id=$historyData['invoice_history_id'] ?? NULL;
        $AccountActivity->trust_history_id=$historyData['trust_history_id'] ?? NULL;
        $AccountActivity->notes =$historyData['notes'];
        $AccountActivity->is_lead_invoice = $historyData['is_lead_invoice'] ?? 'no';
        $AccountActivity->pay_type =$historyData['pay_type'];
        $AccountActivity->firm_id =$historyData['firm_id'];
        $AccountActivity->section =$historyData['section'];
        $AccountActivity->refund_ref_id =@$historyData['refund_ref_id'];
        if(isset($historyData['from_pay'])){
            $AccountActivity->from_pay =$historyData['from_pay'];
        }
        $AccountActivity->created_by= auth()->id();
        $AccountActivity->created_at=date('Y-m-d H:i:s');
        $AccountActivity->save();
        return true;
    }

    public function updateTrustAccountActivity($request, $amtAction = "add", $InvoiceData = null, $isDebit = "no")
    {
        // try {
        // dbStart();
        $authUser = auth()->user();
        $AccountActivityData=AccountActivity::select("*")->where("firm_id",$authUser->firm_name)->where("pay_type","trust")->orderBy("id","DESC")->first();
        $activityHistory=[];
        $activityHistory['user_id']=$request->trust_account;
        $activityHistory['related_to']=$InvoiceData['id'] ?? NULL;
        $activityHistory['case_id']= $InvoiceData['case_id'] ?? $request->case_id;
        if($request->allocated_case_id && $InvoiceData['case_id'] == 0) {
            $activityHistory['case_id']= $request->allocated_case_id;
        }
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
        $activityHistory['entry_date']= ($request->payment_date) ? convertDateToUTCzone(date("Y-m-d", strtotime($request->payment_date)), $authUser->user_timezone) : date('Y-m-d');
        $activityHistory['payment_method']=($request->payment_type == "refund payment deposit" || $request->payment_type == "refund payment") ? "Refund" : $request->payment_method ?? 'Trust';
        $activityHistory['payment_type']=$request->payment_type;
        $activityHistory['invoice_history_id']=$request->invoice_history_id ?? NULL;
        $activityHistory['notes']=$request->notes;
        $activityHistory['status']="unsent";
        $activityHistory['pay_type']="trust";
        $activityHistory['from_pay']= $request->from_pay ?? "trust";
        $activityHistory['trust_history_id']=$request->trust_history_id ?? Null;
        $activityHistory['firm_id']=$authUser->firm_name;
        if(isset($request->applied_to) && $request->applied_to!=0){
            $activityHistory['section']="request";
            $activityHistory['related_to']=$request->applied_to;
        } else if(isset($InvoiceData) && $InvoiceData != null){
            $activityHistory['section']="invoice";
            $activityHistory['related_to']=$InvoiceData['id'];
            $activityHistory['is_lead_invoice']=$InvoiceData['is_lead_invoice'];
        }else{
            $activityHistory['section']="other";
        }
        $activityHistory['created_by']=$authUser->id;
        $activityHistory['created_at']=date('Y-m-d H:i:s');

        // Update refund reference record
        if($request->payment_type == "refund payment deposit" || $request->payment_type == "refund payment") {
            $refundRef = AccountActivity::where("invoice_history_id", $request->transaction_id)->where("pay_type","trust")->first();
            if($refundRef) {
                $refundRef->is_refunded = "yes";
                $refundRef->payment_status = ($request->amount == $refundRef->credit_amount) ? "full refund" : "partial refund";
                $refundRef->save();
            }
            $activityHistory['refund_ref_id'] = @$refundRef->id;
            $activityHistory['payment_status'] = "refund entry";
        }

        // Update deposit refund reference record
        if($request->payment_type == "refund_deposit") {
            $refundRef = AccountActivity::where("trust_history_id", $request->transaction_id)->where("pay_type","trust")->first();
            if($refundRef) {
                $refundRef->is_refunded = "yes";
                $refundRef->payment_status = ($request->amount == $refundRef->credit_amount) ? "full refund" : "partial refund";
                $refundRef->save();
            }
            $activityHistory['refund_ref_id'] = @$refundRef->id;
            $activityHistory['payment_status'] = "refund entry";
        }
        $this->saveAccountActivity($activityHistory);
        /* dbCommit();
        } catch (Exception $e) {
            dbEnd();
        } */
    }

    public function updateClientPaymentActivity($request, $InvoiceData = null, $isDebit = "no", $amtAction = "add")
    {
        $authUser = auth()->user();
        $AccountActivityData=AccountActivity::select("*")->where("firm_id",$authUser->firm_name)->where("pay_type","client")->orderBy("id","DESC")->first();
        $activityHistory=[];
        $activityHistory['user_id']=$request->contact_id;
        $activityHistory['related_to']=$InvoiceData['id'] ?? NULL;
        $activityHistory['case_id']=$InvoiceData['case_id'] ?? $request->case_id;
        $activityHistory['debit_amount']= ($isDebit == "yes") ? $request->amount : 0.00;
        $activityHistory['credit_amount']=($isDebit == "no") ? $request->amount : 0.00;
        if(!empty($AccountActivityData)){
            if($amtAction == "sub")
                $activityHistory['total_amount'] = $AccountActivityData['total_amount'] - $request->amount;
            else
                $activityHistory['total_amount'] = $AccountActivityData['total_amount'] + $request->amount;
        }else{
            $activityHistory['total_amount']=$request->amount;
        }
        $activityHistory['entry_date']= ($request->payment_date) ? convertDateToUTCzone(date("Y-m-d", strtotime($request->payment_date)), $authUser->user_timezone) : date('Y-m-d');
        $activityHistory['payment_method']=($request->payment_type == "refund payment deposit" || $request->payment_type == "refund payment" || $request->payment_type == "refund deposit") ? "Refund" : $request->payment_method ?? 'Trust';
        $activityHistory['payment_type']=$request->payment_type;
        $activityHistory['invoice_history_id']=$request->invoice_history_id ?? NULL;
        $activityHistory['trust_history_id']=$request->trust_history_id ?? Null;
        $activityHistory['notes']=$request->notes;
        $activityHistory['status']="unsent";
        $activityHistory['pay_type']="client";
        $activityHistory['from_pay']= $request->from_pay ?? "credit";
        $activityHistory['firm_id']=$authUser->firm_name;
        if(isset($request->applied_to) && $request->applied_to!=0){
            $activityHistory['section']="request";
            $activityHistory['related_to']=$request->applied_to;
            $activityHistory['from_pay']= $request->from_pay ?? "credit";
        } else if(isset($InvoiceData) && $InvoiceData != null){
            $activityHistory['section']="invoice";
            $activityHistory['related_to']=$InvoiceData['id'];
            $activityHistory['is_lead_invoice']=$InvoiceData['is_lead_invoice'];
        }else{
            $activityHistory['section']="other";
        }
        $activityHistory['created_by']=$authUser->id;
        $activityHistory['created_at']=date('Y-m-d H:i:s');

        // Update invoice refund reference record
        if($request->payment_type == "refund payment deposit" || $request->payment_type == "refund payment") {
            $refundRef = AccountActivity::where("invoice_history_id", $request->transaction_id)->where("pay_type","client")->first();
            if($refundRef) {
                $refundRef->is_refunded = "yes";
                $refundRef->payment_status = ($request->amount == $refundRef->credit_amount) ? "full refund" : "partial refund";
                $refundRef->save();
            }
            $activityHistory['refund_ref_id'] = @$refundRef->id;
            $activityHistory['payment_status'] = "refund entry";
        }

        // Update deposit refund reference record
        if($request->payment_type == "refund deposit") {
            $refundRef = AccountActivity::where("trust_history_id", $request->transaction_id)->where("pay_type","client")->first();
            if($refundRef) {
                $refundRef->is_refunded = "yes";
                $refundRef->payment_status = ($request->amount == $refundRef->credit_amount) ? "full refund" : "partial refund";
                $refundRef->save();
            }
            $activityHistory['refund_ref_id'] = @$refundRef->id;
            $activityHistory['payment_status'] = "refund entry";
        }

        $this->saveAccountActivity($activityHistory);
    }

    /**
     * Delete trust account activity
     */
    public function deleteTrustAccountActivity($invoiceHistoryId = null, $trustHistoryId = null)
    {
        if($trustHistoryId && $invoiceHistoryId == null) {
            $activity = AccountActivity::where("trust_history_id", $trustHistoryId)->where("firm_id", auth()->user()->firm_name)->where("pay_type", "trust")->first();
            if($activity) {
                if($activity->payment_type == "refund payment" || $activity->payment_type == "refund_deposit") {
                    $this->updateRefundedAcitivity($activity->refund_ref_id);
                }
                $this->updateNextPreviousTotalBalance($activity->id, $activity->pay_type);
                $activity->delete();
            }
            $activity1 = AccountActivity::where("trust_history_id", $trustHistoryId)->where("firm_id", auth()->user()->firm_name)->where("pay_type", "client")->first();
            if($activity1) {
                if($activity1->payment_type == "refund payment" || $activity1->payment_type == "refund deposit") {
                    $this->updateRefundedAcitivity($activity1->refund_ref_id);
                }
                $this->updateNextPreviousTotalBalance($activity1->id, $activity1->pay_type);
                $activity1->delete();
            }
        } else if($invoiceHistoryId != null) {
            // Delete trust account activity
            $activity = AccountActivity::where("invoice_history_id", $invoiceHistoryId)->where("firm_id", auth()->user()->firm_name)->where("pay_type", "trust")->first();
            if($activity) {
                if($activity->payment_type == "refund payment") {
                    $this->updateRefundedAcitivity($activity->refund_ref_id);
                }
                $this->updateNextPreviousTotalBalance($activity->id, $activity->pay_type);
                $activity->delete();
            }
            
            // Delete payment history activity
            $activityPay = AccountActivity::where("invoice_history_id", $invoiceHistoryId)->where("firm_id", auth()->user()->firm_name)->where("pay_type", "client")->first();
            if($activityPay) {
                if($activityPay->payment_type == "refund payment" || $activityPay->payment_type == "refund deposit") {
                    $this->updateRefundedAcitivity($activityPay->refund_ref_id);
                }
                $this->updateNextPreviousTotalBalance($activityPay->id, $activityPay->pay_type);
                $activityPay->delete();
            }
        }
    }

    /**
     * update next/previous total balance of activity
     */
    public function updateNextPreviousTotalBalance($activityId, $payType) {
        $accountActivity = AccountActivity::where("id", '>', $activityId)->where("firm_id", auth()->user()->firm_name)
                        ->where("pay_type",$payType)->orderBy("created_at", "asc")->get();
        $previous = AccountActivity::where("id", '<', $activityId)->where("firm_id", auth()->user()->firm_name)->where("pay_type",$payType)->orderBy("id", "desc")->first();
        
        foreach($accountActivity as $key => $item) {
            if($key > 0) {
                $previous = $accountActivity->get(--$key);  
            }
            $currentBal = 0;
            if($previous) {
                $currentBal = $previous->total_amount;
            }
            Log::info("previous record: ".$previous);
            Log::info("current balance: ".$currentBal);
            if($item->payment_type == "payment" && $item->credit_amount > 0) {
                $currentBal = $currentBal + $item->credit_amount;
            } else if($item->payment_type == "payment" && $item->debit_amount > 0) {
                $currentBal = $currentBal - $item->debit_amount;
            } else if(($item->payment_type == "refund payment" || $item->payment_type == "refund_deposit") && $item->debit_amount > 0) {
                $currentBal = $currentBal - $item->debit_amount;
            } else if($item->payment_type == "refund payment" && $item->credit_amount > 0) {
                $currentBal = $currentBal + $item->credit_amount;
            } else if($item->payment_type == "payment deposit") {
                $currentBal = $currentBal + $item->credit_amount;
            } else if($item->payment_type == "refund payment deposit") {
                $currentBal = $currentBal - $item->debit_amount;
            } else if($item->payment_type == "withdraw" && $item->debit_amount > 0) {
                $currentBal = $currentBal - $item->debit_amount;
            } else if($item->payment_type == "withdraw" && $item->credit_amount > 0) {
                $currentBal = $currentBal + $item->credit_amount;
            } else {
                $currentBal = $currentBal + $item->credit_amount;
            }
            Log::info("updated current balance: ".$currentBal);
            Log::info("updated record id: ".$item->id);
            $item->total_amount = $currentBal;
            $item->save();
        }
    }

    /**
     * Update refunded record status
     */
    public function updateRefundedAcitivity($refundRefId)
    {
        $updateRedord= AccountActivity::find($refundRefId);
        if($updateRedord) {
            $updateRedord->is_refunded="no";
            $updateRedord->payment_status=NULL;
            $updateRedord->save();
        }
    }
}
 