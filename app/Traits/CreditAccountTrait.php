<?php
 
namespace App\Traits;

use App\DepositIntoCreditHistory;
use App\InvoiceHistory;
use App\InvoicePayment;
use App\Invoices;
use App\User;
use App\UsersAdditionalInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

trait CreditAccountTrait {
 
    public function updateNextPreviousCreditBalance($userId) {
        $creditHistory = DepositIntoCreditHistory::where("user_id", $userId)->orderBy("payment_date", "asc")->orderBy("created_at", "asc")->get();
        foreach($creditHistory as $key => $item) {
            $previous = $creditHistory->get(--$key);  
            $currentBal = 0;
            if($previous) {
                $currentBal = $previous->total_balance;
            }
            if($item->payment_type == "deposit") {
                $currentBal = $currentBal + $item->deposit_amount;
            } else if($item->payment_type == "refund deposit") {
                $currentBal = $currentBal - $item->deposit_amount;
            } else if($item->payment_type == "withdraw") {
                $currentBal = $currentBal - $item->deposit_amount;
            } else if($item->payment_type == "refund withdraw") {
                $currentBal = $currentBal + $item->deposit_amount;
            } else if($item->payment_type == "payment") {
                $currentBal = $currentBal - $item->deposit_amount;
            } else if($item->payment_type == "refund payment") {
                $currentBal = $currentBal + $item->deposit_amount;
            } else if($item->payment_type == "payment deposit") {
                $currentBal = $currentBal + $item->deposit_amount;
            } else if($item->payment_type == "refund payment deposit") {
                $currentBal = $currentBal - $item->deposit_amount;
            }
            $item->total_balance = $currentBal;
            $item->save();
        }
    }
 
    /**
     * Update invoice payment entry and history after credit refund
     */
    public function updateInvoicePaymentAfterRefund($creditId, $request)
    {
        $creditHistory = DepositIntoCreditHistory::whereId($creditId)->first();
        $invPayment = InvoicePayment::whereId($creditHistory->related_to_invoice_payment_id)->first();
        $authUser = auth()->user();
        $currentBalance = InvoicePayment::where("firm_id", $authUser->firm_name)->where("deposit_into","Operating Account")->orderBy("created_at","DESC")->first();
        if($currentBalance['total'] - $request->amount <= 0){
            $finalAmt=0;
        }else{
            $finalAmt = $currentBalance['total'] - $request->amount;
        }
        $invoiceId = $creditHistory->related_to_invoice_id;
        $newInvPayment = InvoicePayment::create([
            'invoice_id' => $invoiceId,
            'payment_from' => 'credit',
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

        $invoiceHistory = InvoiceHistory::where("invoice_payment_id", $creditHistory->related_to_invoice_payment_id)->first();
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
            'payment_from' => $invoiceHistory->payment_from,
            'deposit_into' => $invoiceHistory->deposit_into,
            'deposit_into_id' => @$invoiceHistory->deposit_into_id,
            'notes' => $request->notes,
            'status' => "4",
            'refund_ref_id' => $invoiceHistory->id,
            'invoice_payment_id' => $newInvPayment->id,
            'created_by' => $authUser->id,
        ]);        

        return $newInvPayment->id;
    }

    /**
     * Delete invoice payment and invoice history using credit history
     */
    public function deleteInvoicePaymentHistory($creditId)
    {
        $creditHistory = DepositIntoCreditHistory::whereId($creditId)->first();
        $invPayment = InvoicePayment::whereId($creditHistory->related_to_invoice_payment_id)->first();
        if($invPayment) {
            $invHistory = InvoiceHistory::where("invoice_payment_id", $invPayment->id)->first();
            $invRefHistory = InvoiceHistory::whereId($invHistory->refund_ref_id)->first();
            if($invRefHistory) {
                $invRefHistory->fill(["status" => "1"])->save();
                InvoicePayment::whereId($invRefHistory->invoice_payment_id)->update(["status" => 0]);
            }
            $invPayment->delete();
            $invHistory->delete();
            $this->updateInvoiceAmount($creditHistory->related_to_invoice_id);
        }
    }

    /**
     * Update invoice paid/due amount and status
     */
    public function updateInvoiceAmount($invoiceId)
    {
        $invoice = Invoices::whereId($invoiceId)->with('invoiceFirstInstallment')->first();
        $allPayment = InvoicePayment::where("invoice_id", $invoiceId)->get();
        $totalPaid = $allPayment->sum("amount_paid");
        $totalRefund = $allPayment->sum("amount_refund");
        $remainPaidAmt = ($totalPaid - $totalRefund);
        $dueDate = ($invoice->invoiceFirstInstallment) ? $invoice->invoiceFirstInstallment->due_date : $invoice->due_date;
        if($remainPaidAmt == 0) {
            if($invoice->is_sent  == "yes") {
                $status = "Sent";    
            }else{
                if($invoice->status == "forwarded"){
                    if(isset($dueDate) && strtotime($dueDate) < strtotime(date('Y-m-d'))) {
                        $status="Overdue";
                    } else{
                        $status="Unsent";
                    }
                }else{
                    $status = $invoice->status;
                }
            }
        } elseif($invoice->total_amount == $remainPaidAmt) {
            $status = "Paid";
        } else if($remainPaidAmt > 0 && $remainPaidAmt < $invoice->total_amount && (!isset($dueDate) || strtotime($dueDate) >= strtotime(date('Y-m-d')))) {
            $status="Partial";
        } else if(isset($dueDate) && strtotime($dueDate) < strtotime(date('Y-m-d'))) {
            $status="Overdue";
        } else if($invoice->is_sent  == "yes") {
            $status = "Sent";
        } else {
            $status = 'Unsent';
        }
        $invoice->fill([
            'paid_amount'=> $remainPaidAmt,
            'due_amount'=> ($invoice->total_amount - $remainPaidAmt),
            'status'=>$status,
        ])->save();
    }
}
 