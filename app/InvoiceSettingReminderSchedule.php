<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceSettingReminderSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inv_setting_id', 'firm_id', 'remind_type', 'days', 'created_by', 'updated_by'
    ];

    protected $appends = [];
    
}
