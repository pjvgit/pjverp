<!DOCTYPE html>
<html lang="en" dir="">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Payment | {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet" />
    <link href="{{  asset('assets/styles/css/themes/lite-purple.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/styles/vendor/perfect-scrollbar.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/styles/css/plugins/fontawesome-5.css') }}" />
    {{-- <link rel="stylesheet" href="{{ asset('assets/styles/css/plugins/smart.wizard/smart_wizard.min.css') }}" /> --}}
    <link rel="stylesheet" href="{{ asset('assets/styles/css/plugins/smart.wizard/smart_wizard_theme_arrows.min.css') }}" />
    {{-- <link rel="stylesheet" href="{{ asset('assets/styles/css/plugins/smart.wizard/smart_wizard_theme_circles.min.css') }}" /> --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/styles/css/plugins/smart.wizard/smart_wizard_theme_dots.min.css') }}" /> --}}
</head>

<body class="text-left">
    <div class="app-admin-wrap">
        <div class="switch-overlay"></div>
        <div class="main-content-wrap mobile-menu-content bg-off-white m-0">
            <header class="main-header bg-white d-flex justify-content-between">
                <div class="header-toggle">
					<div class="logo">
						<img src="{{asset('assets/images/logo.png')}}" alt="">
					</div>
                </div>
            </header><!-- ============ Body content start ============= -->
            <div class="main-content pt-4">
                <div class="breadcrumb">
                    <h1>Invoice #{{ $invoice->id }}</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>
                <div class="row">
                    <div class="col-md-12">
                        <!--  SmartWizard html -->
                        <div id="smartwizard">
                            <ul>
                                <li><a href="#step-1">@lang('billing.step_1')<br /><small>@lang('billing.step_1_text')</small></a></li>
                                <li><a href="#step-2">@lang('billing.step_2')<br /><small>@lang('billing.step_2_text')</small></a></li>
                            </ul>
                            <div>
                                <div id="step-1" class="p-3">
                                    @if(isset($month) && $month != '')
                                        @include('client_portal.billing.partial.load_credit_card_form')
                                    @else
                                        <h3 class="border-bottom border-gray pb-2">Total: One payment of {{ $invoice->due_amount }}</h3>
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
                                            <div class="col-md-8">
                                                <div class="card text-left">
                                                    <div class="card-body">
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
                                                                    <input type="text" name="invoice_id" value="{{ encodeDecodeId($invoice->id, 'encode') }}" >
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
                                                                <form method="POST" action="{{ route('client/bills/payment/cash', ['invoice_id'=>encodeDecodeId($invoice->id, 'encode'), 'client_id'=>encodeDecodeId($clientId, 'encode')]) }}">
                                                                    @csrf
                                                                    <label class="radio radio-primary">
                                                                        <input type="radio" name="radio" value="0" checked>
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
                                        </div>
                                    @endif
                                </div>
                                <div id="step-2">
                                    <h3 class="border-bottom border-gray pb-2">Step 4 Content</h3>
                                    <div class="card o-hidden">
                                        <div class="card-header">My Details</div>
                                        <div class="card-block p-0">
                                            <table class="table">
                                                <tbody>
                                                    <tr>
                                                        <th>Name:</th>
                                                        <td>Tim Smith</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Email:</th>
                                                        <td>example@example.com</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
            <div class="sidebar-overlay open"></div><!-- Footer Start -->
            <div class="flex-grow-1"></div>
            <div class="app-footer">
                <div class="footer-bottom align-items-center">
                    <div class="d-flex align-items-center">
                        <img class="logo" src="{{asset('assets/images/logo.png')}}" alt="">
                        <div>
                            <p class="m-0">&copy; 2021 LegalCase</p>
                            <p class="m-0">All rights reserved</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- fotter end -->
        </div>
    </div>

<script src="{{ asset('assets/js/plugins/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/scripts/tooltip.script.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.mask.min.js') }}"></script>
<script src="{{asset('assets/js/jquery.validate.min.js')}}"></script>
{{-- <script src="{{ asset('assets/js/scripts/script.min.js') }}"></script> --}}
{{-- <script src="{{ asset('assets/js/scripts/script_2.min.js') }}"></script> --}}
{{-- <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script> --}}
<script src="{{ asset('assets/js/plugins/jquery.smartWizard.min.js') }}"></script>
{{-- <script src="{{ asset('assets/js/scripts/smart.wizard.script.min.js') }}"></script> --}}
<script src="{{ asset('assets\plugins\creditcardvalidator\jquery.creditCardValidator.js') }}"></script>
<script type="text/javascript" src="https://conektaapi.s3.amazonaws.com/v1.0.0/js/conekta.js"></script>
<script src="{{ asset('assets\client_portal\js\payment\payment.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script type="text/javascript">
    // Conekta Public Key
    Conekta.setPublicKey('key_G4QB4RszLMz8p11sNFxBn6A');
</script>
<script>
$(document).ready(function () {
	// Step show event
	$("#smartwizard").on("showStep", function (e, anchorObject, stepNumber, stepDirection, stepPosition) {
		//alert("You are on step "+stepNumber+" now");
		if (stepPosition === 'first') {
			$("#prev-btn").addClass('disabled');
		} else if (stepPosition === 'final') {
			$("#next-btn").addClass('disabled');
		} else {
			$("#prev-btn").removeClass('disabled');
			$("#next-btn").removeClass('disabled');
		}
	});

	// Toolbar extra buttons
	var btnFinish = $('<button></button>').text('Finish')
		.addClass('btn btn-info')
		.on('click', function () { alert('Finish Clicked'); });
	var btnCancel = $('<button></button>').text('Cancel')
		.addClass('btn btn-danger')
		.on('click', function () { $('#smartwizard').smartWizard("reset"); });


	// Smart Wizard
	$('#smartwizard').smartWizard({
		selected: 0,
		theme: 'arrows',
		transitionEffect: 'fade',
		showStepURLhash: true,
		toolbarSettings: {
			// toolbarPosition: 'both',
			toolbarButtonPosition: 'end',
			toolbarExtraButtons: [btnFinish, btnCancel]
		}
	});
});
jQuery(function ($) {
    $("#card_form").submit(function (event) {
        event.preventDefault();
        $("#error-alert").hide();

        if ($("#card_form").valid()) {
            var $form;
            $form = $(this);
            /* Previene hacer submit más de una vez */
            // $form.find("button").prop("disabled", true);
            // $form.find("button").addClass("gris").removeClass("rojo");
            // $("#loaderM").css("display", "inline-block");
            $(".card-errors").text("");
            Conekta.token.create($form, conektaSuccessResponseHandler, conektaErrorResponseHandler);
            /* Previene que la información de la forma sea enviada al servidor */
        }
        return false;
    });
});
var conektaSuccessResponseHandler;
conektaSuccessResponseHandler = function (token) {
    var $form;
    $form = $("#card_form");
    /* Inserta el token_id en la forma para que se envíe al servidor */
    $form.append($("<input type=\"text\" name=\"conektaTokenId\" />").val(token.id));
    /* and submit */
    // $form.get(0).submit();
};
var conektaErrorResponseHandler;
conektaErrorResponseHandler = function (response) {
    var $form;
    $form = $("#card_form");
    /* Conekta card erros */
    if (response.message === "The cardholder name is invalid.") {
        response.message = "Ingrese su nombre (utilice únicamente letras, guiones, espacios y comas)";
    } else if (response.message === "The card number is invalid.") {
        response.message = "Ingrese un número de tarjeta válido ";
    } else if (response.message === "The CVC (security code) of the card is invalid.") {
        response.message = "Introduzca un Código de seguridad de la tarjeta válido";
    } else if (response.message === "The card has expired.") {
        response.message = "Ingrese un fecha de vencimiento válida";
    } else if (response.message === "The expiration month is invalid.") {
        response.message = "Ingrese un fecha de vencimiento válida";
    } else if (response.message === "A plan cannot contain spaces or special characters, only dashes, underscores and alphanumeric characters are allowed.") {
        response.message = "Se les permite un plan no puede contener espacios o caracteres especiales, únicos guiones, guiones y caracteres alfanuméricos.";
    } else if (response.message === "The token has already been used.") {
        response.message = "El token ya se ha utilizado.";
    } else if (response.message === "A plan cannot contain spaces or special characters, only dashes, underscores and alphanumeric characters are allowed.") {
        response.message = "Un plan no puede contener espacios o caracteres especiales, solamente guiones, guiones y caracteres alfanuméricos son permitidos.";
    } else {
        //response.message = "";
    }
    $("#error-alert span").text(response.message);
    $("#error-alert").show();
    $("#loaderM").css("display", "none");
};
</script>
</body>

</html>