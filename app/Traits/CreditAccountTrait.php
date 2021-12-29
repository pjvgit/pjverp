<?php
 
namespace App\Traits;

use App\DepositIntoCreditHistory;
use App\InvoiceHistory;
use App\InvoiceInstallment;
use App\InvoicePayment;
use App\Invoices;
use App\User;
use App\UsersAdditionalInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
                "status" => ($request->amount == $invoiceHistory->amount) ? '2' : '3',
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
     * Update invoice draft status to other status if it invoices in forced set
     */
    public function updateInvoiceDraftStatus($invoiceId){
        $invoice = Invoices::whereId($invoiceId)->first();
        $invoice->fill([
            'is_force_status'=>0
        ])->save();
    }

    /**
     * Update invoice paid/due amount and status
     */
    public function updateInvoiceAmount($invoiceId)
    {
        $invoice = Invoices::whereId($invoiceId)->with('invoiceFirstInstallment')->first();
        $allPayment = InvoicePayment::where("invoice_id", $invoiceId)->where('status', '!=', '2')->get();
        $totalPaid = $allPayment->sum("amount_paid");
        $totalRefund = $allPayment->sum("amount_refund");
        $remainPaidAmt = ($totalPaid - $totalRefund);
        $dueDate = ($invoice->invoiceFirstInstallment) ? $invoice->invoiceFirstInstallment->due_date : $invoice->due_date;
        if($invoice->is_force_status == 0){
            if($remainPaidAmt == 0 && !isset($dueDate)) {
                $status="Unsent";
                if($invoice->is_sent  == "yes") {
                    $status = "Sent";    
                }else{
                    if($invoice->status == "forwarded"){
                        if(isset($dueDate) && strtotime($dueDate) < strtotime(date('Y-m-d'))) {
                            $status="Overdue";
                        }
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
        }else{
            $status = "Draft";
        }

        $invoice->fill([
            'paid_amount'=> $remainPaidAmt,
            'due_amount'=> ($invoice->total_amount - $remainPaidAmt),
            'status'=>$status,
        ])->save();
    }

    /**
     * Update invoice installment status and paid amount
     */
    public function installmentManagement($paidAmt, $invoice_id, $onlinePaymentStatus = null) {
        $invoice_installment=InvoiceInstallment::where("invoice_id",$invoice_id)->where("status","unpaid")->orderBy("due_date","ASC")->get();
        $arrayGrid=array();
        foreach($invoice_installment as $k=>$v){
            $arrayGrid[$k]['id']=$v->id;
            $arrayGrid[$k]['installment_amt']=$v->installment_amount;
            $arrayGrid[$k]['total_paid_amt']=$v->adjustment;
            $arrayGrid[$k]['now_pay']=$arrayGrid[$k]['installment_amt']-$arrayGrid[$k]['total_paid_amt'];
            if($arrayGrid[$k]['now_pay']>=$paidAmt){
                $arrayGrid[$k]['actual_pay_amt']=$paidAmt;
            }else{
                $arrayGrid[$k]['actual_pay_amt']=$arrayGrid[$k]['now_pay'];
            }
            $arrayGrid[$k]['available_bal']=$paidAmt-$arrayGrid[$k]['now_pay'];
            $paidAmt-=$arrayGrid[$k]['now_pay'];
        }
        foreach($arrayGrid as $G=>$H){
            if($H['actual_pay_amt']>=0){
                DB::table('invoice_installment')->where("id",$H['id'])->update([
                    'paid_date'=>date('Y-m-d h:i:s'),
                    'adjustment'=>DB::raw('adjustment + ' . $H['actual_pay_amt']),
                    'online_payment_status' => $onlinePaymentStatus ?? 'paid',
                ]);  
                if($onlinePaymentStatus == null || $onlinePaymentStatus == 'paid') {
                    $invoice_installment=InvoiceInstallment::find($H['id']);
                    if($invoice_installment['installment_amount']==$invoice_installment['adjustment']){
                        $invoice_installment->status="paid";   
                    }
                    $invoice_installment->save();
                }
            }
        }
    }
}
 