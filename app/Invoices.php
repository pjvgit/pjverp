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
    
    
}
