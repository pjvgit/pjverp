<form class="edit_firm" id="billing_payment_form" action="{{ route('billing/settings/update/payment/preferences') }}" method="post">
    @csrf
    <input type="hidden" name="setting_id" value="{{ @$paymentSetting->id }}">
    <div class="preference-section-title">Online Payments</div>
    <div class="form-group row">
        <div class="col-3 col-form-label">Accept Online Payments</div>
        <div class="col-9">
            <input name="is_accept_online_payment" type="hidden" value="no">
            <input type="checkbox" value="yes" name="is_accept_online_payment" id="is_accept_online_payment" @if(isset($paymentSetting) && $paymentSetting->is_accept_online_payment == 'yes') checked @endif>
            <label for="is_accept_online_payment">Enabled</label>
        </div>
    </div>
    <div id="key_div" style="display: {{ (isset($paymentSetting) && $paymentSetting->is_accept_online_payment == 'yes') ? 'block' : 'none' }}">
        <div class="form-group row">
            <div class="col-3 col-form-label"></div>
            <div class="col-9 form-control-plaintext">
                <p> @lang('billing.online_payment_note1') <a href="https://www.conekta.com/">www.conekta.com</a> @lang('billing.online_payment_note2')</p>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-3 col-form-label"> @lang('billing.public_key') </div>
            <div class="col-9 form-control-plaintext">
                <input type="text" class="form-control" name="public_key" id="public_key" value="{{ $paymentSetting->public_key ?? '' }}">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-3 col-form-label"> @lang('billing.private_key') </div>
            <div class="col-9 form-control-plaintext">
                <input type="text" class="form-control" name="private_key" id="private_key" value="{{ $paymentSetting->private_key ?? '' }}">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-3 col-form-label">Accept interest free Monthly Payments</div>
            <div class="col-9">
                <input name="is_accept_interest_free_monthly_payment" type="hidden" value="no">
                <input type="checkbox" value="yes" name="is_accept_interest_free_monthly_payment" id="is_accept_interest_free_monthly_payment" @if(isset($paymentSetting) && $paymentSetting->is_accept_interest_free_monthly_payment == 'yes') checked @endif>
                <label for="is_accept_interest_free_monthly_payment">Enabled</label>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-3 col-form-label"> Paypal Public Key </div>
            <div class="col-9 form-control-plaintext">
                <input type="text" class="form-control" name="paypal_public_key" id="paypal_public_key" value="{{ $paymentSetting->paypal_public_key ?? '' }}">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-3 col-form-label"> Paypal Private Key </div>
            <div class="col-9 form-control-plaintext">
                <input type="text" class="form-control" name="paypal_private_key" id="paypal_private_key" value="{{ $paymentSetting->paypal_private_key ?? '' }}">
            </div>
        </div>
    </div>
</form>
<div>
    <div id="link_button" class="text-right">
        <button id="save_payment_settings" class="btn btn-primary">Save Preferences</button>
    </div>
    <div id="adding_box" style="display: none;"> <img style="vertical-align: middle;" class="retina" src="{{ asset("images/ajax_arrows.gif") }}" width="16" height="16"> Saving… </div>
</div>

<script type="text/javascript">


</script>