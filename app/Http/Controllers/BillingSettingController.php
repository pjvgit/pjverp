<?php

namespace App\Http\Controllers;

use App\InvoiceSetting;
use Illuminate\Http\Request;

class BillingSettingController extends BaseController
{
    public function index()
    {
        $invSetting = InvoiceSetting::where('firm_id', auth()->user()->firm_name)->with('reminderSchedule')->first();
        return view("billing_setting.index", compact('invSetting'));
    }

    /**
     * Edit invoice default preferences
     */
    public function editPreferences(Request $request)
    {
        $invSetting = InvoiceSetting::whereId($request->setting_id)->first();
        return view("billing_setting.partial.edit_invoice_preferences", compact('invSetting'))->render();
    }

    /**
     * Update invoice default preferences
     */
    public function updatePreferences(Request $request)
    {
        $invSetting = InvoiceSetting::updateOrCreate(
            [
                'id' => $request->setting_id,
            ], [
                'time_entry_hours_decimal_point' => $request->time_entry_hours_decimal_point,
                'default_invoice_payment_terms' => $request->default_invoice_payment_terms,
                'default_trust_and_credit_display_on_new_invoices' => $request->default_trust_and_credit_display_on_new_invoices,
                'default_terms_conditions' => $request->default_terms_conditions,
                'is_non_trust_retainers_credit_account' => $request->is_non_trust_retainers_credit_account,
                'is_payment_history_on_bills' => $request->is_payment_history_on_bills,
                'is_ledes_billing' => $request->is_ledes_billing,
                'request_funds_preferences_default_msg' => $request->request_funds_preferences_default_msg,
                'updated_by' => auth()->id(),
            ]);
        return view("billing_setting.partial.view_invoice_preferences", compact('invSetting'));
    }

    
    /**
     * View invoice default preferences
     */
    public function viewPreferences(Request $request)
    {
        $invSetting = InvoiceSetting::whereId($request->setting_id)->first();
        return view("billing_setting.partial.view_invoice_preferences", compact('invSetting'))->render();
    }
}