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
            'notes', 'payment_plan_enabled', 'created_by', 'updated_by', 'invoice_unique_token', 'invoice_token', 'firm_id', 'invoice_setting'];

    protected $casts = [
        'invoice_setting' => 'array',
    ];
    
    protected $appends  = ['decode_id','total_amount_new','paid_amount_new','due_amount_new','due_date_new','created_date_new',"current_status","check_portal_access","invoice_id"];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    } 

    public function getTotalAmountNewAttribute(){
        return number_format($this->total_amount,2);
    }
    public function getPaidAmountNewAttribute(){
        return number_format($this->paid_amount,2);
    }
    public function getDueAmountNewAttribute(){
        return number_format($this->due_amount,2);
    }
    public function getDueDateNewAttribute(){
        if($this->due_date!=NULL){
            return date('M j, Y',strtotime($this->due_date));
        }else{
            return '--';
        }
    }
    public function getCreatedDateNewAttribute(){
        if($this->created_at!=NULL){
            return date('M j, Y',strtotime($this->created_at));
        }else{
            return '--';
        }
    }
    // public function getCurrentStatusAttribute(){

    //     if($this->is_sent=="yes"){
    //         if($this->due_amount =="0.00"){
    //             return "Paid";
    //         }else if($this->due_amount!="0.00" && $this->paid_amount!="0.00" ){
    //              return "Partial";
    //         }else if($this->due_date!=NULL && strtotime($this->due_date) < strtotime(date('Y-m-d')) && $this->paid_amount=="0.00" ){
    //             return "Overdue";
    //         }else{
    //             return "Sent";
    //         }
    //     }else{
    //         if($this->due_amount =="0.00"){
    //             return "Paid";
    //         }else if($this->due_amount!="0.00" && $this->paid_amount!="0.00" ){
    //              return "Partial";
    //         }else if($this->due_date!=NULL && strtotime($this->due_date) < strtotime(date('Y-m-d')) && $this->paid_amount=="0.00" ){
    //             return "Overdue";
    //         }else{
    //             return "Unsent";
    //         }
    //     }
        
    // }
    public function getCurrentStatusAttribute(){

        if($this->is_sent=="yes"){
            if($this->due_amount =="0.00"){
                return "Paid";
            }else if($this->due_amount!="0.00" && $this->paid_amount!="0.00" ){
                 return "Partial";
            }else if($this->due_date!=NULL && strtotime($this->due_date) < strtotime(date('Y-m-d')) && $this->paid_amount=="0.00" ){
                return "Overdue";
            }else{
                return "Sent";
            }
        }else{
            if($this->due_amount =="0.00"){
                return "Paid";
            }else if($this->due_amount!="0.00" && $this->paid_amount!="0.00" ){
                 return "Partial";
            }else if($this->due_date!=NULL && strtotime($this->due_date) < strtotime(date('Y-m-d')) && $this->paid_amount=="0.00" ){
                return "Overdue";
            }else{
                return "Unsent";
            }
        }
        
    }
    public function getCheckPortalAccessAttribute(){
        $UsersAdditionalInfo=UsersAdditionalInfo::where("user_id",$this->client_id)->select("client_portal_enable")->first();
        if(!empty($UsersAdditionalInfo) && $UsersAdditionalInfo['client_portal_enable']=="1"){
            return "yes";
        }else{
            return "no";
        }
     } 

     public function getInvoiceIdAttribute(){
        return sprintf("%06d", $this->id);
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
        return $this->hasOne(InvoiceInstallment::class, 'invoice_id')->orderBy("created_by", "asc")->whereStatus("unpaid");
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
     * Get the applyTrustCreditFund associated with the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function applyTrustFund()
    {
        return $this->hasMany(InvoiceApplyTrustCreditFund::class, 'invoice_id')->where("account_type", "trust");
    }

    /**
     * Get the applyTrustCreditFund associated with the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function applyCreditFund()
    {
        return $this->hasMany(InvoiceApplyTrustCreditFund::class, 'invoice_id')->where("account_type", "credit");
    }
}
