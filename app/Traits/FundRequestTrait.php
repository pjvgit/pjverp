<?php
 
namespace App\Traits;

use App\DepositIntoCreditHistory;
use App\RequestedFund;
use App\TrustHistory;
use Illuminate\Support\Facades\Log;

trait FundRequestTrait {
    /**
     * Update trust fund request when trust deposte refund
     */
    public function refundTrustRequest($trustId)
    {
        $trustHistory = TrustHistory::whereId($trustId)->first();
        if($trustHistory->related_to_fund_request_id) {
            $fundRequest = RequestedFund::whereId($trustHistory->related_to_fund_request_id)->first();
            if($fundRequest) {
                $fundRequest->fill([
                    'status' => 'partial', // status set attribute in model for diff status
                    'amount_due' => $fundRequest->amount_due + $trustHistory->refund_amount,
                    'amount_paid' => $fundRequest->amount_paid - $trustHistory->refund_amount,
                    'updated_by' => auth()->id(),
                ])->save();
            }
        }
    }
 
    /**
     * Update trust fund request when delete trust deposite/refund request
     */
    public function deletePaymentTrustRequest($trustId)
    {
        $trustHistory = TrustHistory::whereId($trustId)->first();
        if($trustHistory->related_to_fund_request_id) {
            $fundRequest = RequestedFund::whereId($trustHistory->related_to_fund_request_id)->first();
            if($fundRequest) {
                if($trustHistory->fund_type == 'refund_deposit') {
                    $dueAmt = $fundRequest->amount_due - $trustHistory->refund_amount;
                    $paidAmt = $fundRequest->amount_paid + $trustHistory->refund_amount;
                } else if($trustHistory->fund_type == 'diposit'){
                    $dueAmt = $fundRequest->amount_due + $trustHistory->amount_paid;
                    $paidAmt = $fundRequest->amount_paid - $trustHistory->amount_paid;
                } else {
                    $dueAmt = $fundRequest->amount_due;
                    $paidAmt = $fundRequest->amount_paid;
                }
                $fundRequest->fill([
                    'status' => 'partial', // status set attribute in model for diff status
                    'amount_due' => $dueAmt,
                    'amount_paid' => $paidAmt,
                    'updated_by' => auth()->id(),
                ])->save();
            }
        }
    }

    /**
     * Update credit fund request when trust deposte refund
     */
    public function refundCreditRequest($creditId)
    {
        $creditHistory = DepositIntoCreditHistory::whereId($creditId)->first();
        if($creditHistory->related_to_fund_request_id) {
            $fundRequest = RequestedFund::whereId($creditHistory->related_to_fund_request_id)->first();
            if($fundRequest) {
                $fundRequest->fill([
                    'status' => 'partial', // status set attribute in model for diff status
                    'amount_due' => $fundRequest->amount_due + $creditHistory->deposit_amount,
                    'amount_paid' => $fundRequest->amount_paid - $creditHistory->deposit_amount,
                    'updated_by' => auth()->id(),
                ])->save();
            }
        }
    }

    /**
     * Update credit fund request when delete trust deposite/refund request
     */
    public function deletePaymentCreditRequest($trustId)
    {
        $creditHistory = DepositIntoCreditHistory::whereId($trustId)->first();
        if($creditHistory->related_to_fund_request_id) {
            $fundRequest = RequestedFund::whereId($creditHistory->related_to_fund_request_id)->first();
            if($fundRequest) {
                if($creditHistory->fund_type == 'refund_deposit') {
                    $dueAmt = $fundRequest->amount_due - $creditHistory->deposit_amount;
                    $paidAmt = $fundRequest->amount_paid + $creditHistory->deposit_amount;
                } else if($creditHistory->fund_type == 'diposit'){
                    $dueAmt = $fundRequest->amount_due + $creditHistory->deposit_amount;
                    $paidAmt = $fundRequest->deposit_amount - $creditHistory->amount_paid;
                } else {
                    $dueAmt = $fundRequest->amount_due;
                    $paidAmt = $fundRequest->amount_paid;
                }
                $fundRequest->fill([
                    'status' => 'partial', // status set attribute in model for diff status
                    'amount_due' => $dueAmt,
                    'amount_paid' => $paidAmt,
                    'updated_by' => auth()->id(),
                ])->save();
            }
        }
    }
}
 