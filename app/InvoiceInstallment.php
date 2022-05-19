<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class InvoiceInstallment extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "invoice_installment";
    public $primaryKey = 'id';

    protected $fillable = ['invoice_id', 'installment_amount', 'due_date', 'firm_id', 'status', 'paid_date', 'pay_type', 'adjustment', 'created_by', 'updated_by', 'online_payment_status'];

    protected $appends  = ['decode_id','invoice_decode_id',"invoice_number",'total_paid','total_paid_display','total_amt','total_amt_display','total_due','total_due_display','completed','completed_display','next_payment_on','next_payment_on_display','next_payment_amount','next_payment_amount_display',"final_date",'final_date_display'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  
    public function getInvoiceNumberAttribute(){
        if(isset($this->invoice_id)){
            // return sprintf("%05d", $this->invoice_id);
            return sprintf("%05d", $this->invoice->unique_invoice_number);
        }else{
            return "";
        }
    }
    public function getInvoiceDecodeIdAttribute(){
        if(isset($this->invoice_id))
        {
            return base64_encode($this->invoice_id);
        }else{
            return "";
        }

    }  

    public function getTotalPaidAttribute(){
        if(isset($this->invoice_id)){
            $InvoiceInstallment=InvoiceInstallment::where("invoice_id",$this->invoice_id)->sum('adjustment');
            return $InvoiceInstallment;
        }else{
            return 0;
        }
    }

    public function getTotalPaidDisplayAttribute(){
        if(isset($this->invoice_id)){
            $InvoiceInstallment=InvoiceInstallment::where("invoice_id",$this->invoice_id)->sum('adjustment');
            return "$".number_format($InvoiceInstallment,2);
        }else{
            return 0;
        }
    }

    public function getTotalAmtAttribute(){
        if(isset($this->invoice_id)){
            $InvoiceInstallment=InvoiceInstallment::where("invoice_id",$this->invoice_id)->sum('installment_amount');
            return $InvoiceInstallment;
        }else{
            return 0;
        }
    }
    public function getTotalAmtDisplayAttribute(){
        if(isset($this->invoice_id)){
            $InvoiceInstallment=InvoiceInstallment::where("invoice_id",$this->invoice_id)->sum('installment_amount');
            return "$".number_format($InvoiceInstallment,2);
        }else{
            return 0;
        }
    }
    public function getTotalDueAttribute(){
        if(isset($this->invoice_id)){
           $InvoiceInstallmentTotal=InvoiceInstallment::where("invoice_id",$this->invoice_id)->sum('installment_amount');
           $InvoiceInstallmentPaid=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where('status','paid')->sum('installment_amount');
            $FinalAmt= $InvoiceInstallmentTotal-$InvoiceInstallmentPaid;
            return $FinalAmt;
        }else{
            return 0;
        }
    }
    public function getTotalDueDisplayAttribute(){
        if(isset($this->invoice_id)){
           $InvoiceInstallmentTotal=InvoiceInstallment::where("invoice_id",$this->invoice_id)->sum('installment_amount');
           $InvoiceInstallmentPaid=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where('status','paid')->sum('installment_amount');
            $FinalAmt= $InvoiceInstallmentTotal-$InvoiceInstallmentPaid;
            return "$".number_format($FinalAmt,2);
        }else{
            return 0;
        }
    }
    public function getCompletedAttribute(){
        if(isset($this->invoice_id)){
            $InvoiceInstallmentTotal=InvoiceInstallment::where("invoice_id",$this->invoice_id)->sum('installment_amount');
            $InvoiceInstallmentPaid=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where('status','paid')->sum('installment_amount');

            $InvoiceInstallmentTotalCount=InvoiceInstallment::where("invoice_id",$this->invoice_id)->count('id');
            $InvoiceInstallmentPaidCount=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where('status','paid')->count("id");
             $FinalAmt= $InvoiceInstallmentPaid/$InvoiceInstallmentTotal*100;
            //  return '<div><div class="row">'.number_format($FinalAmt,2).'%</div><div class="row text-muted">'.$InvoiceInstallmentPaidCount.' of '.$InvoiceInstallmentTotalCount.' payments</div></div>';
            return $FinalAmt;
          
        }else{
             return 0;
         }
       
    } 

    public function getCompletedDisplayAttribute(){
        if(isset($this->invoice_id)){
            $InvoiceInstallmentTotal=InvoiceInstallment::where("invoice_id",$this->invoice_id)->sum('installment_amount');
            $InvoiceInstallmentPaid=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where('status','paid')->sum('installment_amount');

            $InvoiceInstallmentTotalCount=InvoiceInstallment::where("invoice_id",$this->invoice_id)->count('id');
            $InvoiceInstallmentPaidCount=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where('status','paid')->count("id");
             $FinalAmt= $InvoiceInstallmentPaid/$InvoiceInstallmentTotal*100;
             return '<div><div class="row">'.number_format($FinalAmt,2).'%</div><div class="row text-muted">'.$InvoiceInstallmentPaidCount.' of '.$InvoiceInstallmentTotalCount.' payments</div></div>';
            
        }else{
             return 0;
         }
       
    } 
    public function getNextPaymentOnAttribute(){
        if(isset($this->invoice_id)){
            $NextInvoiceInstallment=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where("due_date",">=",date('Y-m-d'))->where("status","unpaid")->orderBy("due_date","ASC")->first();
            if(!empty($NextInvoiceInstallment)){
                $TypeOFPayment=InvoicePaymentPlan::select('repeat_by')->where("invoice_id",$this->invoice_id)->first();
                // return '<div><div class="row">'.date('M d,Y',strtotime($NextInvoiceInstallment['due_date'])).'</div><div class="row text-muted">'.ucfirst($TypeOFPayment['repeat_by']).' </div></div>';
                return date('Y-m-d',strtotime($NextInvoiceInstallment['due_date']));
            }else{
                return "";
            }
         }else{
             return "";
         }
       
    }  
    public function getNextPaymentOnDisplayAttribute(){
        if(isset($this->invoice_id)){
            $NextInvoiceInstallment=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where("due_date",">=",date('Y-m-d'))->where("status","unpaid")->orderBy("due_date","ASC")->first();
            if(!empty($NextInvoiceInstallment)){
                $TypeOFPayment=InvoicePaymentPlan::select('repeat_by')->where("invoice_id",$this->invoice_id)->first();
                return '<div><div class="row">'.date('M d,Y',strtotime($NextInvoiceInstallment['due_date'])).'</div><div class="row text-muted">'.ucfirst($TypeOFPayment['repeat_by']).' </div></div>';
            }else{
                return "";
            }
         }else{
             return "";
         }
       
    } 


    public function getNextPaymentAmountAttribute(){
        if(isset($this->invoice_id)){
            $NextInvoiceInstallment=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where("due_date",">=",date('Y-m-d'))->where("status","unpaid")->orderBy("due_date","ASC")->first();
            if(!empty($NextInvoiceInstallment)){
                // return '<div><div class="row">$'.number_format($NextInvoiceInstallment['installment_amount'],2).'</div>';
                return $NextInvoiceInstallment['installment_amount'];

            }else{
                return 0;
            }
         }else{
             return 0;
         }
       
    } 
    public function getNextPaymentAmountDisplayAttribute(){
        if(isset($this->invoice_id)){
            $NextInvoiceInstallment=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where("due_date",">=",date('Y-m-d'))->where("status","unpaid")->orderBy("due_date","ASC")->first();
            if(!empty($NextInvoiceInstallment)){
                return '<div><div class="row">$'.number_format($NextInvoiceInstallment['installment_amount'],2).'</div>';

            }else{
                return "";
            }
         }else{
             return "";
         }
       
    } 
    
    public function getFinalDateAttribute(){
        if(isset($this->invoice_id)){
            $NextInvoiceInstallment=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where("status","unpaid")->orderBy("due_date","DESC")->first();
            if(!empty($NextInvoiceInstallment)){
                // return '<div><div class="row">'.date('M d,Y',strtotime($NextInvoiceInstallment['due_date'])).'</div>';
                return date('Y-m-d',strtotime($NextInvoiceInstallment['due_date']));

            }else{
                return "";
            }
         }else{
             return "";
         }
       
    } 
    public function getFinalDateDisplayAttribute(){
        if(isset($this->invoice_id)){
            $NextInvoiceInstallment=InvoiceInstallment::where("invoice_id",$this->invoice_id)->where("status","unpaid")->orderBy("due_date","DESC")->first();
            if(!empty($NextInvoiceInstallment)){
                return '<div><div class="row">'.date('M d,Y',strtotime($NextInvoiceInstallment['due_date'])).'</div>';

            }else{
                return "";
            }
         }else{
             return "";
         }
       
    } 

    /**
     * Get the invoice that owns the InvoiceInstallment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoice_id');
    }
}
