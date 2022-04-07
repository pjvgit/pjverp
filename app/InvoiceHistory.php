<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
use Carbon\Carbon;

class InvoiceHistory extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "invoice_history";
    public $primaryKey = 'id';

    protected $fillable = ["invoice_id", "lead_invoice_id", "lead_id", "acrtivity_title", "pay_method", "amount", "responsible_user", "deposit_into", 
        "deposit_into_id", "invoice_payment_id", "status", "notes", "refund_ref_id", "created_by", "updated_by", "payment_from", "online_payment_status", "is_invoice_cancelled"];

    protected $appends  = ['added_date','responsible','refund_amount'];

    public function getCreatedatnewformateAttribute(){
        return date('M j, Y h:i A',strtotime($this->created_at));
    }
    public function getAddedDateAttribute(){
        if(isset(auth()->user()->user_timezone) && $this->created_at!=null) 
        {
            $pDate = Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at, "UTC");
            $pDate->setTimezone(auth()->user()->user_timezone ?? 'UTC');
            return $pDate->format("M d, Y");
        }else{
            return null;
        }
    }

    public function getNewduedateAttribute(){
        $CommonController= new CommonController();
        if(isset(Auth::User()->user_timezone) && $this->due_date!=null) 
        {
            $timezone=Auth::User()->user_timezone;
            $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->due_date)),$timezone);
            return date('M j, Y',strtotime($convertedDate));

        }else{
            return null;
        }
    }
    public function getResponsibleAttribute(){
        //    return User::select("*")->find($this->created_by);
    }
    public function getInvoicePaidAmtAttribute(){
        return number_format($this->amount_paid,2);
    }
    public function getIsOverdueAttribute(){
       if(strtotime(date('Y-m-d')) > strtotime($this->due_date)){
            return "overdue";
       }else{
            return "";
       }
        
    }
    public function getTrustBalanceAttribute(){
       if($this->current_trust_balance!='0.00'){
           return number_format($this->current_trust_balance,2);
        }else{
            return "0.00";
        }
         
     }
     public function getPaidAttribute(){
        if($this->amount_paid!=NULL){
            return number_format($this->amount_paid,2);
         }else{
             return "0.00";
         }
          
      }

      public function getWithdrawAttribute(){
        if($this->withdraw_amount!=NULL){
            return number_format($this->withdraw_amount,2);
         }else{
             return "0.00";
         }
          
      }
      public function getRefundAttribute(){
        if($this->refund_amount!="0.00"){
            return number_format($this->refund_amount,2);
         }else{
             return "0.00";
         }
          
      }

      public function getRefundAmountAttribute(){
       return $isRefunder=InvoiceHistory::select("*")->where("refund_ref_id",$this->id)->where("status","4")->sum('amount');
        if($isRefunder>=0){
            return $this->amount-$isRefunder;
        }else{
            return NULL;
        }
     }
    
    /**
     * Set pay menthod attribute
     */
    public function setPayMethodAttribute($value)
    {
        if($value == "Credit") {
            $this->attributes['pay_method'] = 'Credit Account';
        } else {
            $this->attributes['pay_method'] = $value;
        }
    }

    /**
     * Get the invoice that owns the InvoiceHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoice_id');
    }

    /**
     * Get the createdByUser that owns the Invoices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Get the invoicePayment that owns the InvoiceHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoicePayment()
    {
        return $this->belongsTo(InvoicePayment::class, 'invoice_payment_id');
    }
}
