<?php

namespace App\Http\Controllers;

use App\InvoiceCustomizationSetting;
use App\InvoiceSetting;
use App\InvoiceSettingReminderSchedule;
use Illuminate\Http\Request;

class BillingSettingController extends BaseController
{
    public function index()
    {
        $invSetting = InvoiceSetting::where('firm_id', auth()->user()->firm_name)->with('reminderSchedule')->first();
        $customize = InvoiceCustomizationSetting::where('firm_id', auth()->user()->firm_name)->first();
        return view("billing_setting.index", compact('invSetting', 'customize'));
    }

    /**
     * Edit invoice default preferences
     */
    public function editPreferences(Request $request)
    {
        $invSetting = InvoiceSetting::whereId($request->setting_id)->with('reminderSchedule')->first();
        return view("billing_setting.partial.edit_invoice_preferences", compact('invSetting'))->render();
    }

    /**
     * Update invoice default preferences
     */
    public function updatePreferences(Request $request)
    {
        // return $request->all();
        $authUser = auth()->user();
        $invSetting = InvoiceSetting::updateOrCreate(
            [
                'id' => $request->setting_id,
            ], [
                'firm_id' => $authUser->firm_name,
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

        if($request->reminder_type && $request->days) {
            InvoiceSettingReminderSchedule::where('inv_setting_id', $invSetting->id)->forceDelete();
            foreach($request->reminder_type as $key => $item) {
                InvoiceSettingReminderSchedule::create([
                    'inv_setting_id' => $invSetting->id,
                    'firm_id' => $authUser->firm_name,
                    'remind_type' => $item,
                    'days' => @$request->days[$key] ?? 0,
                    'created_by' => $authUser->id,
                ]);
            }
        }

        return view("billing_setting.partial.view_invoice_preferences", compact('invSetting'));
    }

    
    /**
     * View invoice default Customization
     */
    public function viewCustomization(Request $request)
    {
        $customize = InvoiceCustomizationSetting::whereId($request->customize_id)->first();
        return view("billing_setting.partial.view_invoice_customization", compact('customize'))->render();
    }

    /**
     * Edit invoice default Customization
     */
    public function editCustomization(Request $request)
    {
        $customize = InvoiceCustomizationSetting::whereId($request->customize_id)->first();
        return view("billing_setting.partial.edit_invoice_customization", compact('customize'))->render();
    }

    /**
     * Update invoice default Customization
     */
    public function updateCustomization(Request $request)
    {
        // return $request->all();
        $authUser = auth()->user();
        $invSetting = InvoiceSetting::updateOrCreate(
            [
                'id' => $request->setting_id,
            ], [
                'firm_id' => $authUser->firm_name,
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

        if($request->reminder_type && $request->days) {
            InvoiceSettingReminderSchedule::where('inv_setting_id', $invSetting->id)->forceDelete();
            foreach($request->reminder_type as $key => $item) {
                InvoiceSettingReminderSchedule::create([
                    'inv_setting_id' => $invSetting->id,
                    'firm_id' => $authUser->firm_name,
                    'remind_type' => $item,
                    'days' => @$request->days[$key] ?? 0,
                    'created_by' => $authUser->id,
                ]);
            }
        }

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