<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
class InvoiceHistory extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "invoice_history";
    public $primaryKey = 'id';

    protected $fillable = ["invoice_id", "lead_invoice_id", "lead_id", "acrtivity_title", "pay_method", "amount", "responsible_user", "deposit_into", 
        "deposit_into_id", "invoice_payment_id", "status", "notes", "refund_ref_id", "created_by", "updated_by"];

    protected $appends  = ['added_date','responsible','refund_amount'];
    public function getCreatedatnewformateAttribute(){
        return date('M j, Y h:i A',strtotime($this->created_at));
    }
    public function getAddedDateAttribute(){
        if(isset(auth()->user()->user_timezone) && $this->created_at!=null) 
        {
            $userTime = convertUTCToUserTime($this->created_at, auth()->user()->user_timezone ?? 'UTC');
            return date('M j, Y h:i a',strtotime($userTime));
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
       return User::select("*",DB::raw('CONCAT_WS(" ",users.first_name,users.middle_name,users.last_name) as cname'))->find($this->created_by);
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
            $this->attributes['pay_method'] = 'Non-Trust Credit Account';
        } else {
            $this->attributes['pay_method'] = $value;
        }
    }
}
