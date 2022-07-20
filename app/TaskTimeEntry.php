<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\SoftDeletes;
class TaskTimeEntry extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "task_time_entry";
    public $primaryKey = 'id';

    protected $fillable = [
        'task_id', 'case_id', 'user_id', 'activity_id', 'time_entry_billable', 'description', 'entry_date', 'entry_rate', 'rate_type', 'duration', 'token_id', 'firm_id'
    ];    
    protected $appends  = ['decode_id','decode_invoice_id','date_format_new','calculated_amt','lead_data'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->uid);
    }  
    public function getDecodeInvoiceIdAttribute(){
        return base64_encode($this->invoice_link);
    } 
    public function getDateFormatNewAttribute(){
        return date('M d, Y',strtotime($this->attributes['entry_date']));
        // return date('M d, Y',strtotime($this->entry_date));
    }
    public function getCalculatedAmtAttribute(){
        if($this->rate_type=="flat"){
            return str_replace(",","",number_format($this->entry_rate,2));
        }else{
            return str_replace(",","",number_format($this->duration * $this->entry_rate,2));
        }
    }

    public function getEntryDateAttribute()
    {
        $userTime = convertUTCToUserDate($this->attributes['entry_date'], auth()->user()->user_timezone ?? 'UTC');            
        return date('Y-m-d', strtotime($userTime));                 
    }
    
    // public function getEntryDateAttribute()
    // {
    //     $userTime = convertUTCToUserDate($this->entry_date, auth()->user()->user_timezone ?? 'UTC');            
    //     echo date('Y-m-d', strtotime($userTime));            
    // }
    
    /**
     * Get duration decimal point as per settings
     */
    public function getDurationAttribute()
    {
        $setting = getInvoiceSetting(@$this->user->firm_name);
        $decimalPoint = 1;
        if($setting) {
            $decimalPoint = $setting->time_entry_hours_decimal_point;
        }        
        return str_replace(",","",number_format($this->attributes['duration'], $decimalPoint));
    }

    public function getEntryRateAttribute()
    {
        $setting = getInvoiceSetting(@$this->user->firm_name);
        $decimalPoint = 1;
        if($setting) {
            $decimalPoint = $setting->time_entry_hours_decimal_point;
        }        
        return str_replace(",","",number_format($this->attributes['entry_rate'], $decimalPoint));
    }

    public function getLeadDataAttribute(){
        $userData = $this->user;
        if(isset($userData) && $userData->user_level == 5){
            return '<a class="name" href="'.route('case_details/info',$this->user_id).'">Potential Case: '.$userData->full_name.'</a>';
        }
    }    

    /**
     * Get the taskActivity that owns the TaskTimeEntry
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taskActivity()
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
    
    /**
     * Get the invoice that owns the TaskTimeEntry
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoice_link');
    }

    /**
     * Set entry date attribute
     */
    public function setEntryDateAttribute($value)
    {
        if($value) {
            $this->attributes['entry_date'] = convertDateToUTCzone($value, auth()->user()->user_timezone);
        } else {
            $this->attributes['entry_date'] = $value;
        }
    }
}
