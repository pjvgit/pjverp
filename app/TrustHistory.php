<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
class TrustHistory extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "trust_history";
    public $primaryKey = 'id';

    protected $appends  = ['createdatnewformate','added_date','newduedate','invoice_amt','invoice_paid_amt','is_overdue','trust_balance','paid','withdraw','refund'];
    public function getCreatedatnewformateAttribute(){
        return date('M j, Y h:i A',strtotime($this->created_at));
    }
    public function getAddedDateAttribute(){
        if(isset(Auth::User()->user_timezone) && $this->payment_date!=null) 
        {
            $timezone=Auth::User()->user_timezone;
            $convertedDate= convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->payment_date)),$timezone);
            return date('M j, Y',strtotime($convertedDate));
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
      
}
