<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrustHistory extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "trust_history";
    public $primaryKey = 'id';

    protected $fillable = ['client_id', 'payment_method', 'amount_paid', 'withdraw_amount', 'withdraw_from_account', 'payment_date', 'notes', 'fund_type', 
                'current_trust_balance', 'refund_ref_id', 'is_refunded', 'refund_amount', 'related_to_invoice_id', 'created_by', 'updated_by', 'related_to_fund_request_id',
                'allocated_to_case_id', 'related_to_invoice_payment_id', 'allocated_to_lead_case_id', 'online_payment_status', 'is_invoice_cancelled', 'is_invoice_fund_request_overpaid'];

    protected $appends  = ['createdatnewformate','added_date','newduedate','invoice_amt','invoice_paid_amt','is_overdue','trust_balance','paid','withdraw','refund','related_to'];
    public function getCreatedatnewformateAttribute(){
        return date('M j, Y h:i A',strtotime($this->created_at));
    }
    public function getAddedDateAttribute(){
        if(isset(Auth::User()->user_timezone) && $this->payment_date!=null) 
        {
            $pDate = Carbon::createFromFormat('Y-m-d', $this->payment_date, "UTC");
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
    public function getInvoiceAmtAttribute(){
        return number_format($this->invoice_amount,2);
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
       if($this->current_trust_balance != 0.00){
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

    /**
     * Get the invoice that owns the TrustHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'related_to_invoice_id');
    }

    /**
     * Get the fundRequest that owns the TrustHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fundRequest()
    {
        return $this->belongsTo(RequestedFund::class, 'related_to_fund_request_id');
    }
      
    /**
     * Get the allocateToCase that owns the TrustHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function allocateToCase()
    {
        return $this->belongsTo(CaseMaster::class, 'allocated_to_case_id');
    }

    /**
     * Get the user that owns the TrustHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the leadAdditionalInfo that owns the TrustHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leadAdditionalInfo()
    {
        return $this->belongsTo(LeadAdditionalInfo::class, 'allocated_to_lead_case_id', 'user_id');
    }

    /**
     * Get the createdByUser that owns the TrustHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRelatedToAttribute()
    {
        if($this->related_to_invoice_id) {
            return sprintf("%06d", $this->related_to_invoice_id);
        } else if($this->related_to_fund_request_id) {
            return "#R-".sprintf('%05d', $this->related_to_fund_request_id);
        } else {
            return "";
        }
    }
}
