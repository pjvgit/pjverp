<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class FlatFeeEntryForInvoice extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "flat_fee_entry_for_invoice";
    public $primaryKey = 'id';

    protected $appends  = ['decode_id'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->id);
    }  
   
}
