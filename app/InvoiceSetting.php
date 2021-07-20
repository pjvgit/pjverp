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
        return $this->hasMany(InvoiceSettingReminderSchedule::class, 'inv_setting_id')->orderBy("remind_type", "asc");
    }

    /**
     * Get default reminder schedule message
     */
    public function getReminderMessage()
    {
        $msg = "";
        foreach($this->reminderSchedule as $key => $item) {
            if($item->remind_type == "due in") {
                $msg .= $item->days." days before the due date, ";
            } else if($item->remind_type == "on the due date ") {
                $msg .= "on the due date,";
            } else if($item->remind_type == "overdue by") {
                $msg .= "and ".$item->days." days after the due date";
            } else {

            }
        }
        return "When a due date is entered and there is a balance due, all shared contacts will be sent automated reminders ".$msg;
    }
}
