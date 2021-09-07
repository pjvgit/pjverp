<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;
class TaskTimeEntry extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "task_time_entry";
    public $primaryKey = 'id';

    protected $fillable = [
        'task_id', 'case_id', 'user_id', 'activity_id', 'time_entry_billable', 'description', 'entry_date', 'entry_rate', 'rate_type', 'duration'
    ];    
    protected $appends  = ['decode_id','decode_invoice_id','date_format_new','calculated_amt'];
    public function getDecodeIdAttribute(){
        return base64_encode($this->uid);
    }  
    public function getDecodeInvoiceIdAttribute(){
        return base64_encode($this->invoice_link);
    } 
    public function getDateFormatNewAttribute(){
        return date('M d, Y',strtotime($this->entry_date));
    }
    public function getCalculatedAmtAttribute(){
        if($this->rate_type=="flat"){
            return number_format(str_replace(",","",$this->entry_rate),2);
        }else{
            return number_format(str_replace(",","",$this->duration) * str_replace(",","",$this->entry_rate),2);
        }
    }

    public function setEntryDateAttribute($value)
    {
        $this->attributes['entry_date'] =  \Carbon\Carbon::parse($value, auth()->user()->user_timezone ?? 'UTC')->setTimezone(config('app.timezone'))->format('Y-m-d');
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
        $setting = getInvoiceSetting();
        $decimalPoint = 1;
        if($setting) {
            $decimalPoint = $setting->time_entry_hours_decimal_point;
        }        
        return number_format($this->attributes['duration'], $decimalPoint);
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
}
