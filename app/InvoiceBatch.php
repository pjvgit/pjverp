<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
class InvoiceBatch extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "invoice_batch";
    public $primaryKey = 'id';

    protected $appends  = ['decode_id','decode_type'];

    public function getDecodeIdAttribute(){
        return base64_encode($this->invoice_batch_id);
    } 
    public function getDecodeTypeAttribute(){
        return base64_encode('batches');
    } 
}
