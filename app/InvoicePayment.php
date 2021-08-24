<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\CommonController;
use DB;
class InvoicePayment extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "invoice_payment";
    public $primaryKey = 'id';

    protected $fillable = ['invoice_id', 'payment_method', 'amount_paid', 'amount_refund', 'payment_date', 'deposit_into', 'deposit_into_id', 'payment_from_id', 
            'notes', 'payment_from', 'status', 'total', 'firm_id', 'ip_unique_id', 'refund_ref_id', 'entry_type', 'created_by', 'updated_by'];

    protected $appends  = ['added_date','case','decode_id','contact','refund_title'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->invoice_id);
    }  

    public function getAddedDateAttribute(){
        return date('M j, Y',strtotime($this->created_at));
    }

    public function getCaseAttribute(){
        if(isset($this->invoice_id)){
            $caseId=Invoices::find($this->invoice_id); 
            if(!empty($caseId)){
            return json_encode(CaseMaster::select("*")->where("id",$caseId['case_id'])->first());
            }else{
                return NULL;
            }
        }else{
            return NULL;
        }
       
     }
     public function getContactAttribute(){
        if(isset($this->invoice_id)){
            $caseId=Invoices::find($this->invoice_id); 
            if(!empty($caseId)){
                $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->select(DB::raw('CONCAT(first_name, " ",last_name) as name'),"users.id")->where("case_client_selection.case_id",$caseId['case_id'])->where("is_billing_contact","yes")->first();
                return json_encode($caseCllientSelection);
            }else{
                return NULL;
            }
        }else{
            return NULL;
        }
       
     }
     public function getRefundTitleAttribute(){
        if(isset($this->refund_ref_id) && $this->refund_ref_id != NULL){
            $stringText= '';
            $RefundMasterData=InvoicePayment::find($this->refund_ref_id); 
            if(!empty($RefundMasterData)){
                $stringText.="Refund of ".$RefundMasterData['payment_method']. " on ".date("m/d/Y",strtotime($RefundMasterData['payment_date']))." (original amount: $".number_format($RefundMasterData['amount_paid'],2).")";
            }
            return $stringText;
        }else{
            return NULL;
        }
       
     }
}
