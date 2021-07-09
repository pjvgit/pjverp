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
            'notes', 'payment_plan_enabled', 'created_by', 'updated_by', 'invoice_unique_token', 'invoice_token', 'firm_id'];
    
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
        return sprintf("%05d", $this->id);
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
}
