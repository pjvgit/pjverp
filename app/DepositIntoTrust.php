<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
class DepositIntoTrust extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "deposit_into_trust";
    public $primaryKey = 'id';

    // protected $appends  = ['padding_id','amt_requested','amt_paid','amt_due','due_date_format','send_date_format','is_due','last_send','current_status'];
    // public function getPaddingIdAttribute(){
    //    return "#R-".sprintf('%06d', $this->id);

    // }
    // public function getAmtRequestedAttribute(){
    //     if($this->amount_requested!="0.00"){
    //     return number_format($this->amount_requested,2);
    //     }else{
    //         return "0.00";
    //     }
        
    // }
    // public function getAmtPaidAttribute(){
    //     if($this->amount_paid!="0.00"){
    //     return number_format($this-> amount_paid,2);
    //     }else{
    //         return "0.00";
    //     }
        
    // }
    // public function getAmtDueAttribute(){
    //     if($this->amount_due!="0.00"){
    //     return number_format($this->amount_due,2);
    //     }else{
    //         return "0.00";
    //     }
        
    // }
    // public function getDueDateFormatAttribute(){
    //     if($this->due_date!=NULL){
    //         return date("M d,Y",strtotime($this->due_date));
    //     }else{
    //         return NULL;
    //     }
        
    // }  
    // public function getSendDateFormatAttribute(){
    //     if($this->created_at!=NULL){
    //         return date("M d,Y",strtotime($this->created_at));
    //     }else{
    //         return NULL;
    //     }
        
    // }
    // public function getisDueAttribute(){
    //     if(strtotime($this->due_date) < strtotime(date('Y-m-d'))){
    //         return "Overdue";
    //     }else{
    //         return NULL;
    //     }
        
    // }

    // public function getCurrentStatusAttribute(){
    //     if($this->amount_due =="0.00"){
    //         return "Paid";
    //     }else if($this->amount_due!="0.00" && $this->amount_paid!="0.00" ){
    //          return "Partial";
    //     }else if(strtotime($this->due_date) < strtotime(date('Y-m-d')) && $this->amount_paid=="0.00" ){
    //         return "Overdue";
    //     }else{
    //         return "Sent";
    //     }
        
    // }
    // public function getLastSendAttribute(){
    //     if($this->last_reminder_sent_on!=NULL){
    //         return date("M d,Y",strtotime($this->last_reminder_sent_on));
    //     }else{
    //         return date("M d,Y",strtotime($this->created_at));
    //     }
        
    // }
}
