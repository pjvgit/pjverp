<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
class AccountActivity extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "account_activity";
    public $primaryKey = 'id';

    protected $fillable = ['user_id', 'related_to', 'case_id', 'credit_amount', 'debit_amount', 'total_amount', 'entry_date', 'status', 'notes', 'pay_type', 'firm_id', 
        'section', 'from_pay', 'payment_method', 'payment_type', 'is_refunded', 'refund_ref_id', 'payment_status', 'invoice_history_id', 'trust_history_id', 'is_lead_invoice', 
        'created_by', 'updated_by'];

    protected $appends  = ['added_date','case','decode_id','contact','refund_title','related','enter_by','enter_by_user_level','c_amt','d_amt','t_amt', 'payment_note'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->related_to);
    }  
    public function getRelatedAttribute(){
        
        if($this->section=="invoice"){
           return sprintf('%06d', $this->related_to);
        }else if($this->section=="request"){
            return sprintf('%05d', $this->related_to);
        }else{
            return '';
        }

    }
    public function getAddedDateAttribute(){
        return date('M j, Y',strtotime(convertUTCToUserDate(date("Y-m-d", strtotime($this->entry_date)), auth()->user()->user_timezone ?? 'UTC')));
        // $userTime = convertUTCToUserTime($this->entry_date, auth()->user()->user_timezone ?? 'UTC');
        // return date('M j, Y',strtotime($userTime));
    }

    public function getCaseAttribute(){
        if(isset($this->case_id)){
            return json_encode(CaseMaster::select("*")->where("id",$this->case_id)->first());
        }else{
            return NULL;
        }

        // if(isset($this->invoice_id)){
        //     $caseId=Invoices::find($this->invoice_id); 
        //     return json_encode(CaseMaster::select("*")->where("id",$caseId['case_id'])->first());
        // }else{
        //     return NULL;
        // }
       
     }
     public function getContactAttribute(){
        if(isset($this->user_id)){
            $caseCllientSelection = User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.last_name)  as name'),"users.id")->where("id",$this->user_id)->first();
            return json_encode($caseCllientSelection);
        }else{
            return NULL;
        }

        // if(isset($this->invoice_id)){
        //     $caseId=Invoices::find($this->invoice_id); 
        //     $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT(first_name, " ",last_name) as name'),"users.id")->where("case_client_selection.case_id",$caseId['case_id'])->where("is_billing_contact","yes")->first();
        //     return json_encode($caseCllientSelection);
        // }else{
        //     return NULL;
        // }
       
     }
     public function getRefundTitleAttribute(){
        return NULL;

        // if(isset($this->refund_ref_id)){
        //     $RefundMasterData=InvoicePayment::find($this->refund_ref_id); 
        //     $stringText="Refund of ".$RefundMasterData['payment_method']. " on ".date("m/d/Y",strtotime($RefundMasterData['payment_date']))." (original amount: $".number_format($RefundMasterData['amount_paid'],2).")";
        //     return $stringText;
        // }else{
        //     return NULL;
        // }
       
     }


     public function getEnterByAttribute(){
        if(isset($this->user_id)){
            $caseCllientSelection = User::select(DB::raw('CONCAT_WS(" ",users.first_name,users.last_name)  as name'),"users.id")->where("id",$this->user_id)->first();
            return $caseCllientSelection['name'] ?? '';
        }else{
            return NULL;
        }
     }
     public function getEnterByUserLevelAttribute(){
        if(isset($this->user_id)){
            $caseCllientSelection = User::select("users.user_level")->where("id",$this->user_id)->first();
            return $caseCllientSelection['user_level'] ?? '';
        }else{
            return NULL;
        }
     }

     public function getCAmtAttribute(){
        return $this->credit_amount;
     }
     public function getDAmtAttribute(){
        return $this->debit_amount;
     }
     public function getTAmtAttribute(){
        return $this->total_amount;
     }

    public function getPaymentNoteAttribute()
    {
        $paymentType = $this->payment_type;
        $isRefund = ($this->is_refunded == "yes") ? "(Refunded)" : "";
        $isInvoiceCancelled = ($this->invoice_history_id && $this->invoiceHistory->is_invoice_cancelled == 'yes') ? "(Invoice cancelled. Fund moved to trust account.)" : "";
        if($paymentType=="withdraw" && $this->pay_type == "trust"){
            $ftype="Withdraw from Trust Account";
        } 
        else if($paymentType=="withdraw" && $this->pay_type == "client"){
            $ftype="Withdraw from Trust Account to Operating Account";
        }
        else if($paymentType=="refund_withdraw"){
            $ftype="Refund Withdraw from Trust Account";
        }
        else if($paymentType=="refund_deposit"){
            $ftype="Refund Deposit into Trust Account";
        }
        else if($paymentType=="refund deposit"){
            $ftype="Refund Payment into Credit Account";
        }
        else if($paymentType=="payment" && $this->is_lead_invoice == "yes" && $this->pay_type == "client" && in_array($this->from_pay, ["normal","online"])){
            $ftype = "Payment into Operating Account";
        }
        else if($paymentType=="payment" && $this->is_lead_invoice == "yes" && $this->pay_type == "client" && $this->from_pay == "trust"){
            $ftype = "Payment from Trust Account to Operating Account";
        }
        else if($paymentType=="payment" && $this->is_lead_invoice == "no" && $this->pay_type == "client" && $this->from_pay == "online"){
            $ftype = "Payment into Operating Account";
        }
        else if($paymentType=="payment" && $this->is_lead_invoice == "no"){
            $ftype = "Payment from Trust Account to Operating Account";
        }
        else if($paymentType=="payment deposit"){
            $ftype = "Payment into Trust Account";
        }
        else if($paymentType=="refund payment deposit"){
            $ftype = "Refund Payment into Trust Account";
        }
        else if($paymentType=="refund payment" && $this->is_lead_invoice == "yes" && $this->pay_type == "client" && in_array($this->from_pay, ["normal","online"])){
            $ftype = "Refund Payment into Operating Account";
        }
        else if($paymentType=="refund payment" && $this->is_lead_invoice == "yes" && $this->pay_type == "client" && $this->from_pay == "trust"){
            $ftype = "Refund Payment from Trust Account to Operating Account";
        }
        else if($paymentType=="refund payment" && $this->is_lead_invoice == "no" && $this->pay_type == "client" && $this->from_pay == "online"){
            $ftype = "Refund Payment into Operating Account";
        }
        else if($paymentType=="refund payment" && $this->is_lead_invoice == "no"){
            $ftype = "Refund Payment from Trust Account to Operating Account";
        }
        else if($paymentType=="deposit" && $this->pay_type == "client"){
            $ftype="Payment into Credit Account";
        }
        else{
            $ftype="Deposit into Trust Account";
        }
        return $ftype.' '.$isRefund.' '.$isInvoiceCancelled;
    }

    /**
     * Get the leadAdditionalInfo that owns the AccountActivity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leadAdditionalInfo()
    {
        return $this->hasOne(LeadAdditionalInfo::class, 'user_id', 'user_id');
    }

    /**
     * Get the invoiceHistory that owns the AccountActivity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoiceHistory()
    {
        return $this->belongsTo(InvoiceHistory::class, 'invoice_history_id');
    }
}
