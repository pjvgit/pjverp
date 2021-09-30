<?php
 
namespace App\Traits;

use App\CaseClientSelection;
use App\CaseMaster;
use App\InvoiceHistory;
use App\InvoicePayment;
use App\TrustHistory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

trait TrustAccountTrait {
    /**
     * Update allocated trust balance when trust deposit refund
     */
    public function refundAllocateTrustBalance($trustHistory)
    {
        CaseMaster::where('id', $trustHistory->allocated_to_case_id)->decrement('total_allocated_trust_balance', $trustHistory->refund_amount);
        CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->decrement('allocated_trust_balance', $trustHistory->refund_amount);
    }
 
    /**
     * Update allocated trust balance when trust deposit refund delete/withdraw refund
     */
    public function deleteRefundedAllocateTrustBalance($trustHistory)
    {
        CaseMaster::where('id', $trustHistory->allocated_to_case_id)->increment('total_allocated_trust_balance', $trustHistory->refund_amount);
        CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->increment('allocated_trust_balance', $trustHistory->refund_amount);
    }

    /**
     * Update allocated trust balance when trust deposit delete
     */
    public function deleteAllocateTrustBalance($trustHistory)
    {
        CaseMaster::where('id', $trustHistory->allocated_to_case_id)->decrement('total_allocated_trust_balance', $trustHistory->amount_paid);
        CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->decrement('allocated_trust_balance', $trustHistory->amount_paid);
    }

    /**
     * Update allocated trust balance when trust deposit withdraw
     */
    public function withdrawAllocateTrustBalance($trustHistory)
    {
        CaseMaster::where('id', $trustHistory->allocated_to_case_id)->decrement('total_allocated_trust_balance', $trustHistory->withdraw_amount);
        CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->decrement('allocated_trust_balance', $trustHistory->withdraw_amount);
    }

    /**
     * Update invoice payment entry and history after credit refund
     */
    public function updateInvoicePaymentAfterTrustRefund($trustId, $request)
    {
        $trustHistory = TrustHistory::whereId($trustId)->first();
        $invPayment = InvoicePayment::whereId($trustHistory->related_to_invoice_payment_id)->first();
        $authUser = auth()->user();
        $currentBalance = InvoicePayment::where("firm_id", $authUser->firm_name)->where("deposit_into","Trust Account")->orderBy("created_at","DESC")->first();
        if($currentBalance['total'] - $request->amount <= 0){
            $finalAmt=0;
        }else{
            $finalAmt = $currentBalance['total'] - $request->amount;
        }
        $invoiceId = $trustHistory->related_to_invoice_id;
        $newInvPayment = InvoicePayment::create([
            'invoice_id' => $invoiceId,
            'payment_from' => 'trust',
            'amount_refund' => $request->amount,
            'amount_paid' => 0.00,
            'payment_method' => "Refund",
            'deposit_into' => NULL,
            'notes' => $request->notes,
            'refund_ref_id' => $invPayment->id,
            'payment_date' => date('Y-m-d',strtotime($request->payment_date)),
            'notes' => $request->notes,
            'status' => "1",
            'entry_type' => "1",
            'total' => $finalAmt,
            'firm_id' => $authUser->firm_name,
            'ip_unique_id' => Hash::make(time().rand(1,20000)),
            'created_by'=>$authUser->id 
        ]);

        // Update old record
        $invPayment->fill([
            "status" => 1,
        ])->save();

        // For update invoice status and due/paid amount
        $this->updateInvoiceAmount($invoiceId);

        $invoiceHistory = InvoiceHistory::where("invoice_payment_id", $trustHistory->related_to_invoice_payment_id)->first();
        if($invoiceHistory) {
            $invoiceHistory->fill([
                "status" => ($request->amount == $invoiceHistory->amount) ? 2 : 3,
            ])->save();
        }

        InvoiceHistory::create([
            'invoice_id'  => $invoiceId,
            'acrtivity_title' => 'Payment Refund',
            'pay_method' => "Refund",
            'amount' => $request->amount,
            'responsible_user' => $authUser->id,
            'deposit_into' => NULL,
            'notes' => $request->notes,
            'status' => "4",
            'refund_ref_id' => $invoiceHistory->id,
            'invoice_payment_id' => $newInvPayment->id,
            'created_by' => $authUser->id,
        ]);        

        return $newInvPayment->id;
    }

    /**
     * Delete invoice payment and invoice history using trust history
     */
    public function deleteInvoicePaymentHistoryTrust($trustId)
    {
        $trustHistory = TrustHistory::whereId($trustId)->first();
        $invPayment = InvoicePayment::whereId($trustHistory->related_to_invoice_payment_id)->first();

        $invHistory = InvoiceHistory::where("invoice_payment_id", $invPayment->id)->first();
        $invRefHistory = InvoiceHistory::whereId($invHistory->refund_ref_id)->first();
        if($invRefHistory) {
            $invRefHistory->fill(["status" => "1"])->save();
            InvoicePayment::whereId($invRefHistory->invoice_payment_id)->update(["status" => 0]);
        }
        $invPayment->delete();
        $invHistory->delete();
        $this->updateInvoiceAmount($trustHistory->related_to_invoice_id);
    }
}
 