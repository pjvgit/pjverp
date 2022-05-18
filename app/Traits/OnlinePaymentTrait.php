<?php
 
namespace App\Traits;

use App\UsersAdditionalInfo;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait OnlinePaymentTrait {

    /**
     * Get/check user is exist in conekta or not
     */
    public function checkUserExistOnConekta($client)
    {
        try {
            $customerId = '';
            if(count($client->onlinePaymentCustomerDetail)) {
                $allCustomer = \Conekta\Customer::all();
                if(count($allCustomer)) {
                    $pluckAllCustomerId = collect($allCustomer)->pluck('id')->toArray();
                    $existCustomerId = $client->onlinePaymentCustomerDetail->pluck('conekta_customer_id')->toArray();
                    $existId = array_intersect($pluckAllCustomerId, $existCustomerId);
                    $customerId = array_values($existId)[0] ?? '';
                }
            } 
            return $customerId;
        } catch (\Conekta\AuthenticationError $e){
            return 'error code';
        } catch (\Conekta\ApiError $e){
            return 'error code';
        } catch (\Conekta\ProcessingError $e){
            return 'error code';
        } catch (\Conekta\ParameterValidationError $e){
            return 'error code';
        } catch (\Conekta\Handler $e){
            return 'error code';
        } catch (Exception $e){
            return 'error code';
        }
    }

    public function savePaymentToTrustFund($paymentDetail, $invoice, $amount)
    {
        $caseId = ($invoice->case_id != 0 && $invoice->is_lead_invoice == 'no') ? $invoice->case_id : Null; 
        $leadCaseId = ($invoice->case_id == Null || $invoice->is_lead_invoice == 'yes') ? $invoice->user_id : Null;
        
        $userAdditionalInfo = UsersAdditionalInfo::select("trust_account_balance", "credit_account_balance")->where("user_id", $paymentDetail->user_id)->first();
        $paymentMethod = ($paymentDetail->payment_method == 'cash') ? 'Oxxo Cash' : (($paymentDetail->payment_method == 'bank transfer') ? 'SPEI' : '');
        DB::table('users_additional_info')->where("user_id", $paymentDetail->user_id)->increment('trust_account_balance', $amount);
        Log::info("oxxo cash move payment to trust account");
        $trustHistoryId = DB::table('trust_history')->insertGetId([
            'client_id' => $paymentDetail->user_id,
            'payment_method' => $paymentMethod,
            'amount_paid' => $amount,
            'current_trust_balance' => @$userAdditionalInfo->trust_account_balance,
            'payment_date' => date('Y-m-d'),
            'fund_type' => 'diposit',
            'online_payment_status' => 'paid',
            'related_to_invoice_id' => $invoice->id,
            "allocated_to_case_id" => $caseId ?? Null,
            "allocated_to_lead_case_id" => $leadCaseId ?? Null,
            'created_by' => $paymentDetail->user_id,
            'created_at' => Carbon::now(),
        ]);
        if(isset($caseId)) {
            DB::table('case_master')->where('id', $caseId)->increment("total_allocated_trust_balance", $amount);
            // CaseMaster::where('id', $caseId)->increment('total_allocated_trust_balance', $paymentDetail->amount);
            DB::table("case_client_selection")->where("case_id", $caseId)->where("selected_user", $paymentDetail->user_id)->increment('allocated_trust_balance', $amount);
            // CaseClientSelection::where('case_id', $caseId)->where('selected_user', $paymentDetail->user_id)->increment('allocated_trust_balance', $paymentDetail->amount);
        }
        if(isset($leadCaseId)) {
            DB::table("lead_additional_info")->where("user_id", $leadCaseId)->increment("allocated_trust_balance", $amount);
            // LeadAdditionalInfo::where("user_id", $leadCaseId)->increment('allocated_trust_balance', $paymentDetail->amount);
        }
        $paymentDetail->fill(['trust_history_id' => $trustHistoryId])->save();
        // For update next/previous trust balance
        $this->updateNextPreviousTrustBalance($paymentDetail->user_id);
    }
}