<?php

namespace App\Traits;

use App\InvoiceCustomizationSetting;
use App\InvoiceCustomizationSettingColumn;
use App\InvoiceSetting;
use App\InvoiceSettingReminderSchedule;

trait InvoiceSettingTrait {

    public function saveDefaultInvoicePreferences($firmId, $userId)
    {
        $setting = InvoiceSetting::create([
            'firm_id' => $firmId,
            'created_by' => $userId,
        ]);

        InvoiceSettingReminderSchedule::insert([
            [
                'inv_setting_id' => $setting->id,
                'firm_id' => $firmId,
                'remind_type' => 'due in',
                'days' => 7,
                'created_by' => $userId
            ],
            [
                'inv_setting_id' => $setting->id,
                'firm_id' => $firmId,
                'remind_type' => 'on the due date',
                'days' => "",
                'created_by' => $userId
            ],
            [
                'inv_setting_id' => $setting->id,
                'firm_id' => $firmId,
                'remind_type' => 'overdue by',
                'days' => 7,
                'created_by' => $userId
            ],
        ]);

        $customizeSetting = InvoiceCustomizationSetting::create([
            'firm_id' => $firmId,
            'created_by' => $userId,
        ]);

        InvoiceCustomizationSettingColumn::insert([
            [
                'inv_customiz_setting_id' => $customizeSetting->id,
                'firm_id' => $firmId,
                'billing_type' => 'flat fee',
                'date' => 'yes',
                'employee' => 'no',
                'item' => 'yes',
                'notes' => 'yes',
                'amount' => 'yes',
                'created_by' => $userId
            ],
            [
                'inv_customiz_setting_id' => $customizeSetting->id,
                'firm_id' => $firmId,
                'billing_type' => 'time entry',
                'date' => 'yes',
                'employee' => 'yes',
                'activity' => 'yes',
                'notes' => 'yes',
                'amount' => 'yes',
                'hour' => 'yes',
                'line_total' => 'yes',
                'created_by' => $userId
            ],
            [
                'inv_customiz_setting_id' => $customizeSetting->id,
                'firm_id' => $firmId,
                'billing_type' => 'expense',
                'date' => 'yes',
                'employee' => 'yes',
                'expense' => 'yes',
                'notes' => 'yes',
                'amount' => 'yes',
                'quantity' => 'yes',
                'line_total' => 'yes',
                'created_by' => $userId
            ],
        ]);
    }
}