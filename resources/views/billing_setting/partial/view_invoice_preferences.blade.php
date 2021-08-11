<div class="preference-section-title">Invoice Preferences</div>
<div class="form-group row">
    <div class="col-3 col-form-label"> Time Entry Hours </div>
    <div class="col-9 form-control-plaintext"> Display {{ $invSetting->time_entry_hours_decimal_point ?? 1}} numbers after the decimal point
        <br> <em class="text-muted">Time entries are accurate to the nearest minute</em> </div>
</div>
<div class="form-group row">
    <div class="col-3 col-form-label"> Default Invoice Payment Terms </div>
    <div id="default-payment-terms" class="col-9 form-control-plaintext"> {{ invoicePaymentTermList()[$invSetting->default_invoice_payment_terms ?? "5"] }} </div>
</div>
<div class="form-group row">
    <div class="col-3 col-form-label"> Invoice Reminder Schedule </div>
    <div id="reminders-schedule" class="col-9 form-control-plaintext">
        @if (isset($invSetting) && $invSetting->reminderSchedule)
            @forelse($invSetting->reminderSchedule as $key => $item)
                @if($item->remind_type == "on the due date")
                <div class="py-1">On the Due Date</div>
                @else
                <div class="py-1">{{ ucfirst($item->remind_type) }} {{ $item->days }} days</div>
                @endif
            @empty
            @endforelse
        @endif
    </div>
</div>
<div class="form-group row">
    <div class="col-3 col-form-label"> Default Trust and Credit Display on New Invoices </div>
    <div id="default-show-trust-credit" class="col-9 form-control-plaintext"> {{ trustCreditDisplayList()[$invSetting->default_trust_and_credit_display_on_new_invoices ?? "dont show"] }} </div>
</div>
<div class="form-group row">
    <div class="col-3 col-form-label"> Default Invoice Terms and Conditions </div>
    <div id="default-terms-conditions" class="col-9 form-control-plaintext">
        {{ @$invSetting->default_terms_conditions }}
    </div>
</div>
<div class="form-group row">
    <div class="col-3 col-form-label"> Non-Trust Retainers and Credit Accounts </div>
    <div class="col-9 form-control-plaintext"> {{ (@$invSetting->is_non_trust_retainers_credit_account == 'yes') ? 'Enabled' : 'Disabled' }} </div>
</div>
<div class="form-group row">
    <div class="col-3 col-form-label"> Payment History on Bills </div>
    <div id="payment-history" class="col-9 form-control-plaintext"> {{ (@$invSetting->is_payment_history_on_bills == 'no') ? 'Disabled' : 'Enabled' }} </div>
</div>
<div class="preference-section-title section-title-distance">Request Funds Preferences </div>
<div class="form-group row">
    <div class="col-3 col-form-label"> Default Message
        <p class="text-muted default-msg-disclaimer"> Any changes will only be reflected on new requests created after saving </p>
    </div>
    <div id="default-message" class="col-9 form-control-plaintext">
        {{ @$invSetting->request_funds_preferences_default_msg }}
    </div>
</div>
<div class="text-right">
    <button class="btn btn-primary edit-billing-defaults" data-url="{{ route("billing/settings/edit/preferences") }}" data-setting-id="{{ @$invSetting->id }}">Edit Preferences</button>
</div>