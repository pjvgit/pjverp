<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class ExpenseEntry extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "expense_entry";
    public $primaryKey = 'id';

    protected $fillable = [
        'case_id', 'user_id', 'activity_id', 'time_entry_billable', 'description', 'entry_date', 'entry_rate', 'rate_type', 'duration'
    ];    
    protected $appends  = ['decode_id','decode_invoice_id','date_format_new','qty','cost_value','calulated_cost'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->uid);
    }  
    public function getDecodeInvoiceIdAttribute(){
        if($this->invoice_link!=NULL){
            return base64_encode($this->invoice_link);
        }else{
            return "";
        }
    }  

    public function getDateFormatNewAttribute(){
        return date('M j, Y',strtotime($this->entry_date));
    }
    public function getCostValueAttribute(){
        return str_replace(",","",number_format($this->cost,2));
    }
    public function getQtyAttribute(){
        return str_replace(",","",number_format($this->duration,2));
    }
    public function getCalulatedCostAttribute(){
        return  str_replace(",","",number_format($this->duration * $this->cost,2));
    }

    public function getDurationAttribute()
    {
        $setting = getInvoiceSetting();
        $decimalPoint = 1;
        if($setting) {
            $decimalPoint = $setting->time_entry_hours_decimal_point;
        }        
        return str_replace(",","",number_format($this->attributes['duration'], $decimalPoint));
    }

    public function getCostAttribute()
    {
        $setting = getInvoiceSetting();
        $decimalPoint = 1;
        if($setting) {
            $decimalPoint = $setting->time_entry_hours_decimal_point;
        }        
        return str_replace(",","",number_format($this->attributes['cost'], $decimalPoint));
    }

    public function getEntryDateAttribute()
    {
        $userTime = convertUTCToUserDate($this->attributes['entry_date'], auth()->user()->user_timezone ?? 'UTC');            
        return date('Y-m-d', strtotime($userTime));                 
    }

    /**
     * Get the taskActivity that owns the TaskTimeEntry
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expenseActivity()
    {
        return $this->belongsTo(TaskActivity::class, 'activity_id');
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
