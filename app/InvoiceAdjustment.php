<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class InvoiceAdjustment extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "invoice_adjustment";
    public $primaryKey = 'id';
    
    protected $appends  = ['decode_id','date_format_new','qty','cost_value','calulated_cost'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->uid);
    }  
    public function getDateFormatNewAttribute(){
        return date('M j, Y',strtotime($this->entry_date));
    }
    public function getCostValueAttribute(){
        return str_replace(",","",number_format($this->cost,2));
    }
    public function getQtyAttribute(){
        return str_replace(",","",number_format($this->duration,1));
    }
    public function getCalulatedCostAttribute(){
        return str_replace(",","",number_format($this->duration * $this->cost,2));
    }
}
