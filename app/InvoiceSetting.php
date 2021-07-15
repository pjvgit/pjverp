<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'firm_id', 'time_entry_hours_decimal_point', 'default_invoice_payment_terms', 'default_trust_and_credit_display_on_new_invoices', 
        'default_terms_conditions', 'is_non_trust_retainers_credit_account', 'is_payment_history_on_bills', 'is_ledes_billing', 
        'request_funds_preferences_default_msg', 'created_by', 'updated_by'
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
