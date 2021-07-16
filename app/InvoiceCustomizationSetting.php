<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceCustomizationSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'firm_id', 'invoice_theme', 'show_case_no_after_case_name', 'non_billable_time_entries_and_expenses', 'created_by', 'updated_by'
    ];

    protected $appends = [];
    
    /**
     * Get all of the reminderSchedule for the InvoiceSetting
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reminderSchedule()
    {
        return $this->hasMany(InvoiceSettingReminderSchedule::class, 'inv_setting_id');
    }
}
