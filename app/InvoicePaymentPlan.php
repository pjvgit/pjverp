<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class InvoicePaymentPlan extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "invoice_payment_plan";
    public $primaryKey = 'id';

    protected $appends  = ['decode_id'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  
   
}
