<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class FlatFeeEntry extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "flat_fee_entry";
    public $primaryKey = 'id';

    protected $fillable = [
        'task_id', 'case_id', 'user_id', 'activity_id', 'time_entry_billable', 'description', 'entry_date', 'entry_rate', 'rate_type', 'duration', 'cost', 'created_by', 'temp_invoice_token'
    ];    
    protected $appends  = ['decode_id','decode_invoice_id','date_format_new','calculated_amt'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->uid);
    }  
    public function getDecodeInvoiceIdAttribute(){
        return base64_encode($this->invoice_link);
    } 
    public function getDateFormatNewAttribute(){
        return date('M d, Y',strtotime(convertUTCToUserDate($this->entry_date, auth()->user()->user_timezone)));
    }
    public function getCalculatedAmtAttribute(){
        if($this->rate_type=="flat"){
            return number_format((int)$this->entry_rate,2);
        }else{
            return number_format((int)$this->duration * (int)$this->entry_rate,2);
        }
    }
    
    /**
     * Get the user that owns the TaskTimeEntry
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
