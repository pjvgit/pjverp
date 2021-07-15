<?php

namespace App\Traits;

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
                'days' => 0,
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
    }
}