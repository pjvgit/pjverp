@extends('client_portal.layouts.master')

@section('page-css')
<link rel="stylesheet" href="{{ asset('assets/styles/css/plugins/smart.wizard/smart_wizard_theme_arrows.min.css') }}" />
@endsection

@section('main-content')
<div class="app-container__content">
	<section>
        <div class="row">
            <div class="col-md-12">
                <!--  SmartWizard html -->
                @component('client_portal.component.alert')@endcomponent
                <div id="smartwizard">
                    <ul>
                        <li><a href="#step-1">@lang('billing.step_1')<br /><small>@lang('billing.step_1_text')</small></a></li>
                        <li><a href="#step-2">@lang('billing.step_2')<br /><small>@lang('billing.step_2_text')</small></a></li>
                    </ul>
                    <div>
                        <div id="step-1" class="p-3">
                            <h1>Invoice #{{ $invoice->id }}</h1>
                            @if(isset($month) && $month != '')
                                @include('client_portal.billing.partial.load_credit_card_form')
                            @else
                                <h3 class="border-bottom border-gray pb-2">Total: One payment of {{ $payableAmount }} pesos</h3>
                                <p>Or if you chose interest free monthly payments you will pay as follows:</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        @if($invoice->due_amount >= 300)
                                        <p>3 payments of {{ invoiceMonthlyPaymentAmount($invoice->due_amount, 3) }}</p>
                                        @endif
                                        @if($invoice->due_amount >= 600)
                                        <p>6 payments of {{ invoiceMonthlyPaymentAmount($invoice->due_amount, 6) }}</p>
                                        @endif
                                        @if($invoice->due_amount >= 800)
                                        <p>9 payments of {{ invoiceMonthlyPaymentAmount($invoice->due_amount, 9) }}</p>
                                        @endif
                                        @if($invoice->due_amount >= 1200)
                                        <p>12 payments of {{ invoiceMonthlyPaymentAmount($invoice->due_amount, 12) }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="row pt-3">
                                    <div class="col-md-10">
                                        <div class="text-left">
                                            <h4 class="card-title mb-3">Please choose a payment method</h4>
                                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="credit-card-tab" data-toggle="tab" href="#credit_card_tab" role="tab" aria-controls="credit_card_tab" aria-selected="true">Credit Card
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="case-tab" data-toggle="tab" href="#cash_tab" role="tab" aria-controls="cash_tab" aria-selected="false"> Cash
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="bank-transfer-tab" data-toggle="tab" href="#bank_transfer_tab" role="tab" aria-controls="bank_transfer_tab" aria-selected="false"> Bank Transfer
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content" id="myTabContent">
                                                <div class="tab-pane fade show active" id="credit_card_tab" role="tabpanel" aria-labelledby="credit-card-tab">
                                                    <img src="{{ asset('images/payment/pago1.png') }}" />
                                                    <form id="card_pay_option_form" method="POST" action="{{ route('client/bills/payment/card/option', ['invoice_id'=>encodeDecodeId($invoice->id, 'encode'), 'client_id'=>encodeDecodeId($clientId, 'encode')]) }}">
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ encodeDecodeId($invoice->id, 'encode') }}" >
                                                        <ul class="list-group">
                                                            <li class="list-group-item border-0">
                                                                <label class="radio radio-primary">
                                                                    <input type="radio" class="payment-option" name="payment_option" value="0" checked>
                                                                    <span>Pay with Visa, MasterCard or American Express Credit or Debit Card</span><span class="checkmark"></span>
                                                                </label>
                                                            </li>
                                                            @if($invoice->due_amount >= 300)
                                                            <li class="list-group-item border-0">
                                                                <label class="radio radio-primary">
                                                                    <input type="radio" class="payment-option" name="payment_option" value="3">
                                                                    <span>3 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                                                                </label>
                                                            </li>
                                                            @endif
                                                            @if($invoice->due_amount >= 600)
                                                            <li class="list-group-item border-0">
                                                                <label class="radio radio-primary">
                                                                    <input type="radio" class="payment-option" name="payment_option" value="6">
                                                                    <span>6 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                                                                </label>
                                                            </li>
                                                            @endif
                                                            @if($invoice->due_amount >= 800)
                                                            <li class="list-group-item border-0">
                                                                <label class="radio radio-primary">
                                                                    <input type="radio" class="payment-option" name="payment_option" value="9">
                                                                    <span>9 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                                                                </label>
                                                            </li>
                                                            @endif
                                                            @if($invoice->due_amount >= 1200)
                                                            <li class="list-group-item border-0">
                                                                <label class="radio radio-primary">
                                                                    <input type="radio" class="payment-option" name="payment_option" value="12">
                                                                    <span>12 interest free Monthly Payments with credit card</span><span class="checkmark"></span>
                                                                </label>
                                                            </li>
                                                            @endif
                                                        </ul>
                                                        <button type="submit" class="btn btn-primary mt-2" id="credit_card_continue_btn">Continue</button>
                                                    </form>
                                                </div>
                                                <div class="tab-pane fade" id="cash_tab" role="tabpanel" aria-labelledby="case-tab">
                                                    <img src="{{ asset('images/payment/pago2.png') }}" />
                                                    <form method="POST" action="{{ route('client/bills/payment/cash') }}">
                                                        @csrf
                                                        <input type="text" name="invoice_id" value="{{ $invoice->id }}" >
                                                        <input type="text" name="payable_amount" value="{{ $payableAmount }}" >
                                                        <label class="radio radio-primary">
                                                            <input type="radio" name="radio" value="1" checked>
                                                            <span> @lang('billing.c_radio_text') </span><span class="checkmark"></span>
                                                        </label>
                                                        <button type="submit" class="btn btn-primary mt-2">Continue</button>
                                                    </form>
                                                </div>
                                                <div class="tab-pane fade" id="bank_transfer_tab" role="tabpanel" aria-labelledby="bank-transfer-tab">
                                                    <img src="{{ asset('images/payment/pago3.png') }}" />
                                                    <form method="POST" action="{{ route('client/bills/payment/bank', ['invoice_id'=>encodeDecodeId($invoice->id, 'encode'), 'client_id'=>encodeDecodeId($clientId, 'encode')]) }}">
                                                        @csrf
                                                        <label class="radio radio-primary">
                                                            <input type="radio" name="radio" value="0" checked>
                                                            <span> @lang('billing.bt_radio_text') </span><span class="checkmark"></span>
                                                        </label>
                                                        <button type="submit" class="btn btn-primary mt-2">Continue</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div id="step-2">
                            <h3 class="border-bottom border-gray pb-2">@lang('billing.confirm_title')</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('page-js')
<script src="{{ asset('assets/js/plugins/jquery.smartWizard.min.js') }}"></script>
<script type="text/javascript" src="https://conektaapi.s3.amazonaws.com/v1.0.0/js/conekta.js"></script>
<script src="{{ asset('assets\client_portal\js\payment\payment.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
@endsection

@section('bottom-js')
<script type="text/javascript">
    // Conekta Public Key
    Conekta.setPublicKey('key_G4QB4RszLMz8p11sNFxBn6A');
</script>
<script>
$(document).ready(function () {
	// Smart Wizard
	$('#smartwizard').smartWizard({
		selected: 0,
		theme: 'arrows',
		transitionEffect: 'fade',
		showStepURLhash: true,
		toolbarSettings: {
			toolbarPosition: 'none', // none, top, bottom, both
            // toolbarButtonPosition: 'right', // left, right, center
            showNextButton: false, // show/hide a Next button
            showPreviousButton: false, // show/hide a Previous button
		},
        disabledSteps: [1], // Array Steps disabled
	});
});

</script>

@endsection