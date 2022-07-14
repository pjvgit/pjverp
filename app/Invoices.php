<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\UsersAdditionalInfo;
class Invoices extends Model
{
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "invoices";
    public $primaryKey = 'id';

    protected $fillable = ['id', 'user_id', 'case_id', 'invoice_date', 'total_amount', 'paid_amount', 'due_amount', 'due_date', 'is_viewed', 'is_sent', 
            'reminder_sent_counter', 'reminder_viewed_on', 'last_reminder_sent_on', 'status', 'payment_term', 'automated_reminder', 'terms_condition', 
            'notes', 'payment_plan_enabled', 'created_by', 'updated_by', 'invoice_unique_token', 'invoice_token', 'firm_id', 'invoice_setting',
            'bill_sent_status','is_lead_invoice', 'online_payment_status','is_force_status', 'invoice_reminders', 'unique_invoice_number'];

    protected $casts = [
        'invoice_setting' => 'array',
    ];
    
    protected $appends  = ['decode_id','total_amount_new','paid_amount_new','due_amount_new','due_date_new','created_date_new',"current_status",/*"check_portal_access",*/"invoice_id", "days_aging"];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    } 

    public function getDaysAgingAttribute(){
        if($this->due_date != null){
            $date = \Carbon\Carbon::parse($this->due_date);
            $now = \Carbon\Carbon::now();
            return $date->diffInDays($now);
        }else{
            return '--';
        }
    }

    public function getTotalAmountNewAttribute(){
        return number_format($this->total_amount,2);
    }
    public function getPaidAmountNewAttribute(){
        return number_format($this->paid_amount,2);
    }
    public function getDueAmountAttribute(){
        $due_amount = str_replace(",","",number_format($this->total_amount,2)) - str_replace(",","",number_format($this->paid_amount,2));
        return str_replace(",","",number_format($due_amount,2));
    }
    public function getDueAmountNewAttribute(){
        $due_amount = str_replace(",","",number_format($this->total_amount,2)) - str_replace(",","",number_format($this->paid_amount,2));
        // return str_replace(",","",number_format($due_amount,2));
        return number_format($this->due_amount,2);
    }
    public function getDueDateNewAttribute(){
        if($this->due_date!=NULL){
            // return date('M j, Y',strtotime($this->due_date));
            return convertUTCToUserDate($this->due_date, auth()->user()->user_timezone)->format('M j, Y');
        }else{
            return '--';
        }
    }
    public function getCreatedDateNewAttribute(){
        if($this->created_at!=NULL){
            $userTime = convertUTCToUserTime($this->created_at, auth()->user()->user_timezone ?? 'UTC');
            return date('M j, Y',strtotime($userTime));
        }else{
            return '--';
        }
    }

    public function getInvoiceDateAttribute()
    {
        $userTime = convertUTCToUserDate($this->attributes['invoice_date'], auth()->user()->user_timezone  ?? 'UTC');            
        return date('Y-m-d', strtotime($userTime));            
    } 
    
    public function getCurrentStatusAttribute(){
        $due_amount =  str_replace(",","",number_format($this->total_amount,2)) - str_replace(",","",number_format($this->paid_amount,2));
        if($this->is_sent=="yes"){
            if($due_amount =="0.00"){
                return "Paid";
            }else if($due_amount!="0.00" && $this->paid_amount!="0.00" ){
                 return "Partial";
            }else if($this->due_date!=NULL && strtotime($this->due_date) < strtotime(date('Y-m-d')) && $this->paid_amount=="0.00" ){
                return "Overdue";
            }else{
                return "Sent";
            }
        }else{
            if($due_amount =="0.00"){
                return "Paid";
            }else if($due_amount!="0.00" && $this->paid_amount!="0.00" ){
                 return "Partial";
            }else if($this->due_date!=NULL && strtotime($this->due_date) < strtotime(date('Y-m-d')) && $this->paid_amount=="0.00" ){
                return "Overdue";
            }else{
                return "Unsent";
            }
        }
        
    }
    /**
     * Do not add this attribute to append array, if required, set append dynamically
     */
    public function getCheckPortalAccessAttribute(){
        $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$this->client_id)->select("client_portal_enable")->first();
        if(!empty($UsersAdditionalInfo) && $UsersAdditionalInfo['client_portal_enable']=="1"){
            return "yes";
        }else{
            return "no";
        }
     } 

     public function getInvoiceIdAttribute(){
        // return sprintf("%06d", $this->id);
        return sprintf("%06d", $this->unique_invoice_number);
    }
    
    /**
     * Get the case that owns the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function case()
    {
        return $this->belongsTo(CaseMaster::class, 'case_id');
    }

    /**
     * Get all of the invoiceInstallment for the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoiceInstallment()
    {
        return $this->hasMany(InvoiceInstallment::class, 'invoice_id');
    }

    /**
     * Get the invoiceFirstInstallment associated with the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function invoiceFirstInstallment()
    {
        return $this->hasOne(InvoiceInstallment::class, 'invoice_id')->orderBy("due_date", "asc")->whereStatus("unpaid");
    }

    /**
     * Get the firmDetail that owns the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firmDetail()
    {
        return $this->belongsTo(Firm::class, 'firm_id');
    }

    /**
     * Get all of the invoiceShared for the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoiceShared()
    {
        return $this->hasMany(SharedInvoice::class, 'invoice_id');
    }

    /**
     * The forwardedInvoices that belong to the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function forwardedInvoices()
    {
        return $this->belongsToMany(Invoices::class, 'invoice_forwarded_invoices', 'invoice_id', 'forwarded_invoice_id');
    }

    /**
     * Get invoice Forwarded To Invoice that belong to the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoiceForwardedToInvoice()
    {
        return $this->belongsToMany(Invoices::class, 'invoice_forwarded_invoices', 'forwarded_invoice_id', 'invoice_id');
    }

    /**
     * Get the portalAccessUserAdditionalInfo that owns the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function portalAccessUserAdditionalInfo()
    {
        return $this->belongsTo(UsersAdditionalInfo::class, 'user_id', 'user_id');
    }

     /**
     * Get default reminder schedule message
     */
    public function getReminderMessage()
    {
        $msg = "";
        if($this->invoice_setting['reminder']) {
            $data = collect($this->invoice_setting['reminder'])->sortBy("remind_type");
            foreach($data as $key => $item) {
                if($item['remind_type'] == "due in") {
                    $msg .= $item['days']." days before the due date, ";
                } else if($item['remind_type'] == "on the due date") {
                    $msg .= "on the due date,";
                } else if($item['remind_type'] == "overdue by") {
                    $msg .= " and ".$item['days']." days after the due date";
                } else {

                }
            }
        }

        return "When a due date is entered and there is a balance due, all shared contacts will be sent automated reminders ".$msg;
    }

    /**
     * Get the applyTrustCreditFund associated with the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function applyTrustCreditFund()
    {
        return $this->hasOne(InvoiceApplyTrustCreditFund::class, 'invoice_id');
    }

    /**
     * Get the applyTrustFund associated with the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function applyTrustFund()
    {
        return $this->hasMany(InvoiceApplyTrustCreditFund::class, 'invoice_id')->where("account_type", "trust")->withTrashed();
    }

    /**
     * Get the applyCreditFund associated with the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function applyCreditFund()
    {
        return $this->hasMany(InvoiceApplyTrustCreditFund::class, 'invoice_id')->where("account_type", "credit")->withTrashed();
    }

    /**
     * The invoiceTimeEntry that belong to the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoiceTimeEntry()
    {
        return $this->belongsToMany(TaskTimeEntry::class, 'time_entry_for_invoice', 'invoice_id', 'time_entry_id')->wherePivotNull("deleted_at");
    }

    /**
     * The invoiceExpenseEntry that belong to the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoiceExpenseEntry()
    {
        return $this->belongsToMany(ExpenseEntry::class, 'expense_for_invoice', 'invoice_id', 'expense_entry_id')->wherePivotNull("deleted_at");
    }

    /**
     * The invoiceFlatFeeEntry that belong to the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoiceFlatFeeEntry()
    {
        return $this->belongsToMany(FlatFeeEntry::class, 'flat_fee_entry_for_invoice', 'invoice_id', 'flat_fee_entry_id')->wherePivotNull("deleted_at");
    }

    /**
     * The invoiceAdjustmentEntry that belong to the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoiceAdjustmentEntry()
    {
        return $this->hasMany(InvoiceAdjustment::class, 'invoice_id');
    }

    /**
     * Get all of the invoicePayment for the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoicePaymentHistory()
    {
        return $this->hasMany(InvoiceHistory::class, 'invoice_id')->whereIn("acrtivity_title",["Payment Received","Payment Refund","Payment Pending","Awaiting Online Payment"])->orderBy("id","DESC");
    }

    /**
     * Get the invoiceLastPayment associated with the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function invoiceLastPayment()
    {
        return $this->hasOne(InvoicePayment::class, 'invoice_id');
    }

    /**
     * Get the leadAdditionalInfo that owns the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leadAdditionalInfo()
    {
        return $this->belongsTo(LeadAdditionalInfo::class, 'user_id', 'user_id');
    }

    /**
     * The invoiceSharedUser that belong to the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoiceSharedUser()
    {
        return $this->belongsToMany(User::class, 'shared_invoice', 'invoice_id', 'user_id');
    }

    /**
     * Get the client that owns the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the createdByUser that owns the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get due amount of installments and normal invoice amounts
     */
    public function getInstallmentDueAmount()
    {
        $installments = $this->invoiceInstallment;
        if(count($installments)) {
            $dueAmount = $installments->where("status", "unpaid")->sortBy("due_date")->where('due_date', '<', date('Y-m-d'))->sum('installment_amount');
            $adjustment = $installments->where("status", "unpaid")->sortBy("due_date")->where('due_date', '<', date('Y-m-d'))->sum('adjustment');
            $nextInstallment = $installments->where("status", "unpaid")->sortBy("due_date")->where('due_date', '>=', date('Y-m-d'))->first();
            if($nextInstallment) {
                $dueAmount += $nextInstallment->installment_amount;
                $adjustment += $nextInstallment->adjustment;
            }
            $pendingAmount = number_format(($dueAmount - $adjustment), 2);
        } else {
            $pendingAmount = $this->due_amount_new;
        }
        return $pendingAmount;
    }

    /**
     * Set due date attribute
     */
    public function setDueDateAttribute($value)
    {
        if($value) {
            $this->attributes['due_date'] = convertDateToUTCzone($value, auth()->user()->user_timezone);
        } else {
            $this->attributes['due_date'] = $value;
        }
    }
}
