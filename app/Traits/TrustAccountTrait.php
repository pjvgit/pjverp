<?php
 
namespace App\Traits;

use App\CaseClientSelection;
use App\CaseMaster;
use App\InvoiceHistory;
use App\InvoicePayment;
use App\Invoices;
use App\TrustHistory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

trait TrustAccountTrait {

    use TrustAccountActivityTrait;

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
     * Update allocated trust balance when delete withdraw
     */
    public function deleteWithdrawAllocateTrustBalance($trustHistory)
    {
        CaseMaster::where('id', $trustHistory->allocated_to_case_id)->increment('total_allocated_trust_balance', $trustHistory->withdraw_amount);
        CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->increment('allocated_trust_balance', $trustHistory->withdraw_amount);
    }

    /**
     * Update allocated trust balance when trust payment delete
     */
    public function deletePaymentTrustBalance($trustHistory)
    {
        CaseMaster::where('id', $trustHistory->allocated_to_case_id)->increment('total_allocated_trust_balance', $trustHistory->amount_paid);
        if($trustHistory->allocated_to_case_id) {
            CaseClientSelection::where('case_id', $trustHistory->allocated_to_case_id)->where('selected_user', $trustHistory->client_id)->increment('allocated_trust_balance', $trustHistory->amount_paid);
        }
    }

    /**
     * Update invoice payment entry and history after credit refund
     */
    public function updateInvoicePaymentAfterTrustRefund($trustId, $request, $newTrustHistory = null)
    {
        $trustHistory = TrustHistory::whereId($trustId)->first();
        $invPayment = InvoicePayment::whereId($trustHistory->related_to_invoice_payment_id)->first();
        $authUser = auth()->user();
        $currentBalance = InvoicePayment::where("firm_id", $authUser->firm_name)->where("deposit_into","Operating Account")->orderBy("created_at","DESC")->first();
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
            'payment_method' => "Trust Refund",
            'deposit_into' => "Trust Account",
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
        Log::info("Old transaction:". $trustHistory->related_to_invoice_payment_id.", invoice payment id:".$trustHistory->related_to_invoice_payment_id);
        $invoiceHistory = InvoiceHistory::where("invoice_payment_id", $trustHistory->related_to_invoice_payment_id)->first();
        if($invoiceHistory) {
            $invoiceHistory->fill([
                "status" => ($request->amount == $invoiceHistory->amount) ? '2' : '3',
            ])->save();
        }

        $newInvHistory = InvoiceHistory::create([
            'invoice_id'  => $invoiceId,
            'acrtivity_title' => 'Payment Refund',
            'pay_method' => "Trust Refund",
            'amount' => $request->amount,
            'responsible_user' => $authUser->id,
            'deposit_into' => "Trust Account",
            'notes' => $request->notes,
            'status' => "4",
            'refund_ref_id' => @$invoiceHistory->id,
            'invoice_payment_id' => $newInvPayment->id,
            'created_by' => $authUser->id,
        ]);        

        $request->request->add(['invoice_history_id' => $newInvHistory->id]);
        $request->request->add(["payment_type" => @$newTrustHistory->fund_type]);
        $request->request->add(["contact_id" => @$trustHistory->client_id]);
        $request->request->add(["trust_account" => @$trustHistory->client_id]);
        $request->request->add(["transaction_id" => @$invoiceHistory->id]);
        $findInvoice = Invoices::whereId($trustHistory->related_to_invoice_id)->first();
        if($invoiceHistory->payment_from == "offline" && !in_array($invoiceHistory->pay_method, ["Trust", "Non-Trust Credit Account"])) {
            if($invoiceHistory->deposit_into == "Credit" || $invoiceHistory->deposit_into == "Trust Account") {
                // For account activity
                $this->updateTrustAccountActivity($request, $amtAction = "sub", $findInvoice, $isDebit = "yes");
            } else {
                // For account activity > payment history
                $this->updateClientPaymentActivity($request, $findInvoice, $isDebit = "yes", $amtAction = "sub");
            }
        } else if($invoiceHistory->payment_from == "trust" && $invoiceHistory->pay_method == "Trust") {
            // For account activity
            $this->updateTrustAccountActivity($request, null, $findInvoice);

            // For account activity > payment history
            $this->updateClientPaymentActivity($request, $findInvoice, $isDebit = "yes", $amtAction = "sub");
        }

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

        // For account activity
        $this->deleteTrustAccountActivity($invHistory->id);

        $invPayment->delete();
        $invHistory->delete();
        $this->updateInvoiceAmount($trustHistory->related_to_invoice_id);
    }

    public function updateNextPreviousTrustBalance($userId) {
        $trustHistory = TrustHistory::where("client_id", $userId)->orderBy("payment_date", "asc")->orderBy("created_at", "asc")->whereNull("deleted_at")->get();
        foreach($trustHistory as $key => $item) {
            $previous = $trustHistory->get(--$key);  
            $currentBal = 0;
            if($previous) {
                $currentBal = $previous->current_trust_balance;
            }
            Log::info("trust previous record:". $previous);
            Log::info("trust current balance:". $currentBal);
            if($item->fund_type == "diposit") {
                $currentBal = $currentBal + $item->amount_paid;
            } else if($item->fund_type == "refund_deposit") {
                $currentBal = $currentBal - $item->refund_amount;
            } else if($item->fund_type == "withdraw") {
                $currentBal = $currentBal - $item->withdraw_amount;
            } else if($item->fund_type == "refund_withdraw") {
                $currentBal = $currentBal + $item->refund_amount;
            } else if($item->fund_type == "payment") {
                $currentBal = $currentBal - $item->amount_paid;
            } else if($item->fund_type == "refund payment") {
                $currentBal = $currentBal + $item->refund_amount;
            } else if($item->fund_type == "payment deposit") {
                $currentBal = $currentBal + $item->amount_paid;
            } else if($item->fund_type == "refund payment deposit") {
                $currentBal = $currentBal - $item->refund_amount;
            }
            Log::info("trust updated balance:". $currentBal);
            TrustHistory::whereId($item->id)->update(['current_trust_balance' => $currentBal]);
            $item->refresh();
            Log::info("updated record:". $item);
        }
    }
}
 