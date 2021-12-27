<?php

namespace App\Http\Controllers;

use App\InvoiceCustomizationSetting;
use App\InvoiceCustomizationSettingColumn;
use App\FirmOnlinePaymentSetting;
use App\InvoiceSetting;
use App\InvoiceSettingReminderSchedule;
use Illuminate\Http\Request;

class BillingSettingController extends BaseController
{
    public function index()
    {
        $invSetting = InvoiceSetting::where('firm_id', auth()->user()->firm_name)->with('reminderSchedule')->first();
        $customize = InvoiceCustomizationSetting::where('firm_id', auth()->user()->firm_name)->with('flatFeeColumn', 'timeEntryColumn', 'expenseColumn')->first();        
        $paymentSetting = FirmOnlinePaymentSetting::where('firm_id', auth()->user()->firm_name)->first();
        return view("billing_setting.index", compact('invSetting', 'customize', 'paymentSetting'));
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
                // 'is_ledes_billing' => $request->is_ledes_billing,
                'request_funds_preferences_default_msg' => $request->request_funds_preferences_default_msg,
                'updated_by' => $authUser->id,
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
        $customize = InvoiceCustomizationSetting::whereId($request->customize_id)->with('flatFeeColumn', 'timeEntryColumn', 'expenseColumn')->first();
        return view("billing_setting.partial.view_invoice_customization", compact('customize'))->render();
    }

    /**
     * Edit invoice default Customization
     */
    public function editCustomization(Request $request)
    {
        $customize = InvoiceCustomizationSetting::whereId($request->customize_id)->with('flatFeeColumn', 'timeEntryColumn', 'expenseColumn')->first();
        $flatFeeColumn = (!empty($customize) && $customize->flatFeeColumn) ? getColumnsIfYes($customize->flatFeeColumn->toArray()) : [];
        $timeEntryColumn = (!empty($customize) && $customize->timeEntryColumn) ? getColumnsIfYes($customize->timeEntryColumn->toArray()) : [];
        $expenseColumn = (!empty($customize) && $customize->expenseColumn) ? getColumnsIfYes($customize->expenseColumn->toArray()) : [];
        return view("billing_setting.partial.edit_invoice_customization", compact('customize', 'flatFeeColumn', 'timeEntryColumn', 'expenseColumn'))->render();
    }

    /**
     * Update invoice default Customization
     */
    public function updateCustomization(Request $request)
    {
        // return $request->all();
        $authUser = auth()->user();
        $customize = InvoiceCustomizationSetting::updateOrCreate(
            [
                'id' => $request->id,
            ],[
                'firm_id' => $authUser->firm_name,
                'invoice_theme' => $request->invoice_theme,
                'show_case_no_after_case_name' => ($request->show_case_no_after_case_name) ? "yes" : "no",
                'non_billable_time_entries_and_expenses' => ($request->non_billable_time_entries_and_expenses) ? "yes" : "no",
                'updated_by' => $authUser->id,
        ]);

        if($request->column) {
            InvoiceCustomizationSettingColumn::where('firm_id', $authUser->firm_name)->forceDelete();
            foreach($request->column as $keys => $items) {
                foreach($items as $key => $item) {
                    InvoiceCustomizationSettingColumn::create([
                        'inv_customiz_setting_id' => $customize->id,
                        'firm_id' => $authUser->firm_name,
                        'billing_type' => $keys,
                        'date' => array_key_exists('date', $item) ? "yes" : "no",
                        'employee' => array_key_exists('employee', $item) ? "yes" : "no",
                        'item' => array_key_exists('item', $item) ? "yes" : "no",
                        'notes' => array_key_exists('notes', $item) ? "yes" : "no",
                        'amount' => array_key_exists('amount', $item) ? "yes" : "no",
                        'activity' => array_key_exists('activity', $item) ? "yes" : "no",
                        'hour' => array_key_exists('hour', $item) ? "yes" : "no",
                        'line_total' => array_key_exists('line_total', $item) ? "yes" : "no",
                        'expense' => array_key_exists('expense', $item) ? "yes" : "no",
                        'quantity' => array_key_exists('quantity', $item) ? "yes" : "no",
                        'hour' => array_key_exists('hour', $item) ? "yes" : "no",
                        'created_by' => $authUser->id,
                    ]);
                }
            }
        }       

        return view("billing_setting.partial.view_invoice_customization", compact('customize'));
    }

    
    /**
     * View invoice default preferences
     */
    public function viewPreferences(Request $request)
    {
        $invSetting = InvoiceSetting::whereId($request->setting_id)->first();
        return view("billing_setting.partial.view_invoice_preferences", compact('invSetting'))->render();
    }

    /**
     * Edit invoice default preferences
     */
    public function editPaymentPreferences(Request $request)
    {
        $paymentSetting = FirmOnlinePaymentSetting::whereId($request->setting_id)->first();
        return view("billing_setting.partial.edit_payment_preferences", compact('paymentSetting'))->render();
    }

    /**
     * Update invoice default preferences
     */
    public function updatePaymentPreferences(Request $request)
    {
        // return $request->all();
        $authUser = auth()->user();
        $data['firm_id'] = $authUser->firm_name;
        $data['is_accept_online_payment'] = $request->is_accept_online_payment;
        $data['updated_by'] = $authUser->id;
        if($request->is_accept_online_payment == 'yes') {
            $data['public_key'] = $request->public_key;
            $data['private_key'] = $request->private_key;
        }
        $paymentSetting = FirmOnlinePaymentSetting::updateOrCreate(
            [
                'id' => $request->setting_id,
            ], $data);

        return view("billing_setting.partial.edit_payment_preferences", compact('paymentSetting'));
    }
}