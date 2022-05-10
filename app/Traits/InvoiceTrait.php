<?php
 
namespace App\Traits;

use App\CaseClientSelection;
use App\CaseMaster;
use App\DepositIntoCreditHistory;
use App\Http\Controllers\CommonController;
use App\InvoiceHistory;
use App\InvoicePayment;
use App\LeadAdditionalInfo;
use App\TrustHistory;
use App\UsersAdditionalInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

trait InvoiceTrait {
    /**
     * Update allocated trust balance when trust deposit refund
     */
    public function invoiceApplyTrustFund($item, $request, $InvoiceSave, $trustFundType = null)
    {
        $authUser = auth()->user();
        // Store invoice payment
        $InvoicePayment = InvoicePayment::create([
            'invoice_id' => $InvoiceSave->id,
            'payment_from' => 'trust',
            'amount_paid' => @$item['applied_amount'] ?? 0,
            'payment_date' => date('Y-m-d'),
            'notes' => $request->notes,
            'status' => "0",
            'entry_type' => "0",
            'payment_from_id' => @$item['client_id'],
            'deposit_into' => "Operating Account",
            'deposit_into_id' => @$item['client_id'],
            'firm_id' => $authUser->firm_name,
            'created_by' => $authUser->id 
        ]);
        $InvoicePayment->fill(['ip_unique_id' => Hash::make($InvoicePayment->id)])->save();

        // Deduct amount from trust account after payment.
        $userAddInfo = UsersAdditionalInfo::where("user_id", @$item['client_id'])->first();
        if($userAddInfo) {
            $userAddInfo->fill([
                'trust_account_balance' => ($userAddInfo->trust_account_balance) ? $userAddInfo->trust_account_balance - @$item['applied_amount'] ?? 00 : $userAddInfo->trust_account_balance
            ])->save();
        }
            
        // Add trust history
        TrustHistory::create([
            "client_id" => $item['client_id'],
            "amount_paid" => @$item['applied_amount'] ?? 0,
            "current_trust_balance" => @$userAddInfo->trust_account_balance,
            "payment_date" => date('Y-m-d'),
            "payment_method" => "Trust",
            "fund_type" => 'payment',
            "related_to_invoice_id" => $InvoiceSave->id,
            "allocated_to_case_id" => ($trustFundType == "allocate") ? @$item['case_id'] : NULL,
            "created_by" => $authUser->id,
            "related_to_invoice_payment_id" => $InvoicePayment->id,
        ]);

        if(array_key_exists("allocate_applied_amount", (array) $item) && $item["allocate_applied_amount"] != "" && $trustFundType == "allocate") {
            $clientCaseSelect = CaseClientSelection::where("case_id", @$item['case_id'])->where("selected_user", $item['client_id'])->first();
            if($clientCaseSelect) {
                $clientCaseSelect->decrement('allocated_trust_balance', $item["allocate_applied_amount"] ?? 0);
                CaseMaster::where("id", @$item['case_id'])->decrement('total_allocated_trust_balance', $item["allocate_applied_amount"] ?? 0);
            }
        }

        $invoiceHistory=[];
        $invoiceHistory['invoice_id'] = $InvoiceSave->id;
        $invoiceHistory['acrtivity_title']='Payment Received';
        $invoiceHistory['pay_method']='Trust';
        $invoiceHistory['amount'] = @$item['applied_amount'] ?? 0;
        $invoiceHistory['responsible_user'] = $authUser->id;
        $invoiceHistory['deposit_into']='Operating Account';
        $invoiceHistory['payment_from'] = 'trust';
        $invoiceHistory['deposit_into_id'] = (@$item['client_id'])??NULL;
        $invoiceHistory['invoice_payment_id'] = $InvoicePayment->id;
        $invoiceHistory['notes']=$request->notes ?? NULL;
        $invoiceHistory['status']="1";
        $invoiceHistory['created_by'] = $authUser->id;
        $invoiceHistory['created_at']=date('Y-m-d H:i:s');
        $newHistoryId = $this->invoiceHistory($invoiceHistory);
        
        $request->request->add(["invoice_history_id" => $newHistoryId]);
        $request->request->add(['payment_type' => 'payment']);
        $request->request->add(['trust_account' => $item['client_id']]);
        $request->request->add(['contact_id' => $item['client_id']]);
        $request->request->add(['amount' => $item['applied_amount']]);

        $this->updateTrustAccountActivity($request, $amtAction = 'sub', $InvoiceSave, $isDebit = "yes");
        $this->updateClientPaymentActivity($request, $InvoiceSave);

        //Add Invoice history
        $data=[];
        $data['case_id'] = $InvoiceSave->case_id;
        $data['user_id'] = $InvoiceSave->user_id;
        $data['activity']='accepted a payment of $'.number_format(@$item['applied_amount'] ?? 0,2).' (Trust)';
        $data['activity_for'] = $InvoiceSave->id;
        $data['type']='invoices';
        $data['action']='pay';
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);

        sleep(3); // This is for trust history order
    }

    /**
     * Update invoice reminder settings
     */
    public function updateInvoiceSetting($InvoiceSave, $due_date)
    {
        // Update invoice settings
        if($InvoiceSave->invoice_setting) {
            $invoiceSetting = $InvoiceSave->invoice_setting;
            $emailReminders = [];
            $dueDate = ($due_date) ? date('Y-m-d',strtotime($due_date)) : NULL;
            Log::info("invoice due date: ". $dueDate);
            foreach($invoiceSetting['reminder'] as $key => $item) {
                /* $jsonData['reminder'][] = [
                    'remind_type' => $item['remind_type'],
                    'days' => $item['days'],
                    'is_reminded' => "no",
                ]; */

                $remindAt = null;
                if($dueDate) {
                    if($item['remind_type'] == 'due in') {
                        $remindAt = Carbon::parse($dueDate)->subDays($item['days'])->format('Y-m-d');
                    } else if($item['remind_type'] == 'overdue by') {
                        $remindAt = Carbon::parse($dueDate)->addDays($item['days'])->format('Y-m-d');
                    } else {
                        $remindAt = Carbon::parse($dueDate)->format('Y-m-d');
                    }
                }
                $emailReminders[] = [
                    'remind_type' => $item['remind_type'],
                    'days' => $item['days'],
                    'is_reminded' => 'no',
                    'dispatched_at' => null,
                    'remind_at' => $remindAt,
                ];
            }
            return encodeDecodeJson($emailReminders, 'encode');
        }
        return '';
    }

    /**
     * Transfer fund to allocated trust fund when delete invoice
     */
    public function deleteInvoiceFundTransfer($invoice)
    {
        $invoiceHistories = InvoiceHistory::where("invoice_id", $invoice->id)->get();
        $authUser = auth()->user();
        if($invoiceHistories) {
            foreach($invoiceHistories as $key => $item) {
                if(/* $item->payment_from != "online" && */ $item->refund_ref_id == '' && $item->status == '1' && $item->deposit_into_id != '') {
                    $caseId = ($invoice->case_id != 0 && $invoice->is_lead_invoice == 'no') ? $invoice->case_id : Null; 
                    $leadCaseId = ($invoice->case_id == Null || $invoice->is_lead_invoice == 'yes') ? $invoice->user_id : Null;
                    Log::info("lead case id: ". $leadCaseId);
                    if($item->payment_from == "trust") {
                        $trustHis = TrustHistory::where("related_to_invoice_payment_id", $item->invoice_payment_id)->first();
                        $trustHis->fill(["is_invoice_cancelled" => "yes"])->save();
                        // UsersAdditionalInfo::where("user_id", $trustHis->client_id)->decrement("trust_account_balance", $item->amount);
                        /* if($trustHis->allocated_to_case_id) {
                            CaseMaster::where('id', $trustHis->allocated_to_case_id)->decrement('total_allocated_trust_balance', $item->amount);
                            CaseClientSelection::where('case_id', $trustHis->allocated_to_case_id)->where('selected_user', $trustHis->client_id)->decrement('allocated_trust_balance', $item->amount);
                            // $caseId = $trustHis->allocated_to_case_id;
                        } */
                        /* if($trustHis->allocated_to_lead_case_id) {
                            LeadAdditionalInfo::where("user_id", $trustHis->allocated_to_lead_case_id)->decrement('allocated_trust_balance', $item->amount);
                            $leadCaseId = $trustHis->allocated_to_lead_case_id;
                        } */
                        // $trustHis->delete();
                        // $this->updateNextPreviousTrustBalance($item->deposit_into_id);
                    }
                    if($item->payment_from == "credit") {
                        DepositIntoCreditHistory::where("related_to_invoice_payment_id", $item->invoice_payment_id)->update(["is_invoice_cancelled" => "yes"]);
                    }
                    UsersAdditionalInfo::where("user_id", $item->deposit_into_id)->increment("trust_account_balance", $item->amount);
                    $userAddInfo = UsersAdditionalInfo::where("user_id", $item->deposit_into_id)->first();
                    TrustHistory::create([
                        "client_id" => $item->deposit_into_id,
                        "payment_method" => 'invoice cancelled deposit',
                        "amount_paid" => $item->amount,
                        "current_trust_balance" => $userAddInfo->trust_account_balance,
                        "payment_date" => date('Y-m-d'),
                        "fund_type" => 'payment deposit',
                        "related_to_invoice_id" => $invoice->id,
                        "allocated_to_case_id" => $caseId ?? Null,
                        "allocated_to_lead_case_id" => $leadCaseId ?? Null,
                        "created_by" => $authUser->id,
                        "is_invoice_cancelled" => "yes"
                    ]);
                    if(isset($caseId)) {
                        CaseMaster::where('id', $caseId)->increment('total_allocated_trust_balance', $item->amount);
                        CaseClientSelection::where('case_id', $caseId)->where('selected_user', $item->deposit_into_id)->increment('allocated_trust_balance', $item->amount);
                    }
                    if(isset($leadCaseId)) {
                        LeadAdditionalInfo::where("user_id", $leadCaseId)->increment('allocated_trust_balance', $item->amount);
                    }
                    $this->updateNextPreviousTrustBalance($item->deposit_into_id);
                    InvoicePayment::where("id", $item->invoice_payment_id)->update(["is_invoice_cancelled" => "yes"]);
                    sleep(2);
                }
                $item->fill(["is_invoice_cancelled" => "yes"])->save();
            }
        }
    }
}
 