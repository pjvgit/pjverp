<form class="edit_firm" id="billing_defaults_form" action="{{ route('billing/settings/update/preferences') }}" method="post">
    @csrf
    <input type="hidden" name="setting_id" value="{{ $invSetting->id }}">
    <div class="preference-section-title">Invoice Preferences</div>
    <div class="form-group row">
        <div class="col-3 col-form-label"> Time Entry Hours </div>
        <div class="col-9">
            <div class="mt-1">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" value="1" name="time_entry_hours_decimal_point" id="firm_time_entry_digits_1" @if(isset($invSetting) && $invSetting->time_entry_hours_decimal_point == 1) checked @endif> Display 1 number after the decimal point </label>
                </div>
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" value="2" name="time_entry_hours_decimal_point" id="firm_time_entry_digits_2"  @if(isset($invSetting) && $invSetting->time_entry_hours_decimal_point == 2) checked @endif> Display 2 numbers after the decimal point </label>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-3 col-form-label"> Default Invoice Payment Terms </div>
        <div class="col-9 form-control-plaintext">
            <select class="form-control" name="default_invoice_payment_terms" id="firm_default_payment_terms">
                @forelse (invoicePaymentTermList() as $key => $item)
                    <option value="{{ $key }}" @if(isset($invSetting) && $invSetting->default_invoice_payment_terms == $key) selected @endif>{{ $item }}</option>
                @empty
                @endforelse
            </select>
        </div>
    </div>
    <div id="customized-automated-reminders">
        <div class="row pb-3">
            <div class="col-3"><span>Invoice Reminder Schedule</span></div>
            <div class="col-9">
                <div class="pb-1 reminder-schedule-0">
                    <div class="d-flex col-12 pl-0 align-items-center">
                        <div class="pl-0 col-4">
                            <div>
                                <div class="">
                                    <select id="reminder_type" name="reminder_type" class="form-control custom-select  ">
                                        <option value="-1">Due in</option>
                                        <option value="0">On the Due Date</option>
                                        <option value="1">Overdue by</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="text" class="form-control col-2 reminder-option-days" value="8"><span class="ml-1 col-1 pl-1 pr-0">day(s)</span>
                        <button class="btn btn-link col-2 pl-0 reminder-option-delete" type="button"><i class="delete-icon delete_link text-danger" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div class="pb-1 reminder-schedule-1">
                    <div class="d-flex col-12 pl-0 align-items-center">
                        <div class="pl-0 col-4">
                            <div>
                                <div class="">
                                    <select id="reminder_type" name="reminder_type" class="form-control custom-select  ">
                                        <option value="-1">Due in</option>
                                        <option value="0">On the Due Date</option>
                                        <option value="1">Overdue by</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="text" class="form-control col-2 reminder-option-days" disabled="" value=""><span class="ml-1 col-1 pl-1 pr-0">day(s)</span>
                        <button class="btn btn-link col-2 pl-0 reminder-option-delete" type="button"><i class="delete-icon delete_link text-danger" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div class="pb-1 reminder-schedule-2">
                    <div class="d-flex col-12 pl-0 align-items-center">
                        <div class="pl-0 col-4">
                            <div>
                                <div class="">
                                    <select id="reminder_type" name="reminder_type" class="form-control custom-select  ">
                                        <option value="-1">Due in</option>
                                        <option value="0">On the Due Date</option>
                                        <option value="1">Overdue by</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="text" class="form-control col-2 reminder-option-days" value="7"><span class="ml-1 col-1 pl-1 pr-0">day(s)</span>
                        <button class="btn btn-link col-2 pl-0 reminder-option-delete" type="button"><i class="delete-icon delete_link text-danger" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-link p-0">Add a reminder</button>
                </div>
                <br>
                <p><strong>Note:</strong> Automated reminders will be sent based on the next installment date. If Automatic Payment is On, reminders will show automatic payment status. We recommend keeping a “Due In” reminder to inform your client of pending automatic payment.</p>
            </div>
        </div>
    </div>
    <input id="reminders-schedule" name="reminders_schedule" type="hidden" value="[-8,0,7]">
    <input id="reminders-schedule-changed" name="reminders_schedule_changed" type="hidden" value="false">
    <div class="form-group row">
        <div class="col-3 col-form-label"> Default Trust and Credit Display on New Invoices </div>
        <div id="default-show-trust-credit" class="col-9 form-control-plaintext">
            <select class="form-control" name="default_trust_and_credit_display_on_new_invoices" id="firm_default_bill_history">
                @forelse (trustCreditDisplayList() as $key => $item)
                    <option value="{{ $key }}" @if(isset($invSetting) && $invSetting->default_trust_and_credit_display_on_new_invoices == $key) selected @endif>{{ $item }}</option>
                @empty
                @endforelse
            </select>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-3 col-form-label">
            <label for="firm_default_terms_and_conditions">Default Invoice Terms and Conditions</label>
        </div>
        <div class="col-9 form-control-plaintext">
            <textarea rows="5" class="form-control" name="default_terms_conditions" id="firm_default_terms_and_conditions">{{ @$invSetting->default_terms_conditions }}</textarea>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-3">Non-Trust Retainers and Credit Accounts</label>
        <div class="col-9">
            <input name="is_non_trust_retainers_credit_account" type="hidden" value="no">
            <input type="checkbox" value="yes" name="is_non_trust_retainers_credit_account" id="firm_credit_enabled" @if(isset($invSetting) && $invSetting->is_non_trust_retainers_credit_account == 'yes') checked @endif>
            <label for="firm_credit_enabled">Enabled</label>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-3">Payment History on Bills</label>
        <div class="col-9">
            <input name="is_payment_history_on_bills" type="hidden" value="no">
            <input type="checkbox" value="yes" checked="checked" name="is_payment_history_on_bills" id="firm_bill_payment_history_enabled" @if(isset($invSetting) && $invSetting->is_payment_history_on_bills == 'yes') checked @endif>
            <label for="firm_bill_payment_history_enabled">Enabled</label>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-3">LEDES Billing</label>
        <div class="col-9">
            <input name="is_ledes_billing" type="hidden" value="no">
            <input type="checkbox" value="yes" name="is_ledes_billing" id="firm_ledes_billing_enabled" @if(isset($invSetting) && $invSetting->is_ledes_billing == 'yes') checked @endif>
            <label for="firm_ledes_billing_enabled">Enabled</label>
        </div>
    </div>
    <div class="preference-section-title section-title-distance">Request Funds Preferences</div>
    <div class="form-group row">
        <div class="col-3 col-form-label">
            <label for="firm_default_message">Default Message</label>
            <p class="text-muted default-msg-disclaimer"> Any changes will only be reflected on new requests created after saving </p>
        </div>
        <div class="col-9 form-control-plaintext">
            <textarea rows="5" class="form-control" maxlength="160" name="request_funds_preferences_default_msg" id="firm_default_message">{{ @$invSetting->request_funds_preferences_default_msg }}</textarea>
            <div id="retainer-request-character-counter-container" class="helper-text mt-1 text-right text-muted text-right">Character count: 0/160</div>
        </div>
    </div>
</form>
<div>
    <div id="link_button" class="text-right">
        <button id="cancel_edit_billing_settings" class="btn btn-link" data-url="{{ route('billing/settings/view/preferences') }}" data-setting-id="{{ @$invSetting->id }}">Cancel Without Saving</button>
        <button id="save_billing_settings" class="btn btn-cta-primary">Save Preferences</button>
    </div>
    <div id="adding_box" style="display: none;"> <img style="vertical-align: middle;" class="retina" src="https://assets.mycase.com/packs/retina/ajax_arrows-0ba8e6a4d4.gif" width="16" height="16"> Saving… </div>
</div>