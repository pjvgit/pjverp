<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
class PotentialCaseInvoice extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "potential_case_invoice";
    public $primaryKey = 'id';

    protected $appends  = ['invoice_num','decoded_id','createdatnewformate','added_date','newduedate','invoice_amt','invoice_paid_amt','is_overdue','is_pay'];
    
    public function getDecodedIdAttribute(){
        return base64_encode($this->id);
    }
    public function getInvoiceNumAttribute(){
        return sprintf('%06d', $this->id);
    }
    public function getCreatedatnewformateAttribute(){
        return date('M j, Y h:i A',strtotime(convertUTCToUserDate($this->created_at, auth()->user()->user_timezone)));
    }
    public function getAddedDateAttribute(){
        $CommonController= new CommonController();
        if(isset(Auth::User()->user_timezone) && $this->created_at!=null) 
        {
            $timezone=Auth::User()->user_timezone;
            $convertedDate= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($this->created_at)),$timezone);
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
    public function getIsPayAttribute(){
       if($this->amount_paid==$this->invoice_amount){
           return "Paid";
       }else if($this->amount_paid > "0.00" && $this->amount_paid < $this->invoice_amount){
            return "Partial";
        }else{
            return "";
        }
         
     }
}
