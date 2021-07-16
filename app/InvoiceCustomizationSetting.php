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
    public function billingTypeColumn()
    {
        return $this->hasMany(InvoiceCustomizationSettingColumn::class, 'inv_customiz_setting_id');
    }

    /**
     * Get the flatFeeColumn associated with the InvoiceCustomizationSetting
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function flatFeeColumn()
    {
        return $this->hasOne(InvoiceCustomizationSettingColumn::class, 'inv_customiz_setting_id')->where("billing_type", 'flat fee');
    }

    /**
     * Get the timeEntryColumn associated with the InvoiceCustomizationSetting
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function timeEntryColumn()
    {
        return $this->hasOne(InvoiceCustomizationSettingColumn::class, 'inv_customiz_setting_id')->where("billing_type", 'time entry');
    }

    /**
     * Get the expenseColumn associated with the InvoiceCustomizationSetting
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function expenseColumn()
    {
        return $this->hasOne(InvoiceCustomizationSettingColumn::class, 'inv_customiz_setting_id')->where("billing_type", 'expense');
    }
}
