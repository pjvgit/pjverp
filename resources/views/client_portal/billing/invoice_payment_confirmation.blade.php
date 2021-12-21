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
                <div id="smartwizard">
                    <ul>
                        <li><a href="#step-1" disabled>@lang('billing.step_1')<br /><small>@lang('billing.step_1_text')</small></a></li>
                        <li><a href="#step-2">@lang('billing.step_2')<br /><small>@lang('billing.step_2_text')</small></a></li>
                    </ul>
                    <div>
                        <div id="step-1" class="p-3"></div>
                        <div id="step-2" class="mb-2">
                        @if($paymentDetail->payment_method == 'card')
                            <div class="text-center mt-2 mb-2">
                                <img src="{{ asset('images/check.png') }}" />
                            </div>
                            <h3 class="text-center pb-2">@lang('billing.confirm_title')</h3>
                            <div class="row text-center o-hidden">
                                <div class="col-md-12">
                                    <h4>@lang('billing.confirm_note_1', ['amount' => number_format($paymentDetail->amount ?? 0, 2)])</h4>
                                    <p>@lang('billing.confirm_note_2') Invoice #{{ $paymentDetail->invoice_id }}</p>
                                    <p>@lang('billing.confirm_note_3')</p>
                                    <p>@lang('billing.confirm_note_4')</p>  
                                </div>
                            </div>
                        @elseif($paymentDetail->payment_method == 'cash')
                            <div class="mt-2 app-container__content">
                                <div class="cash-pay row">
                                    <div class="col-md-12 text-center">
                                        <h4 class="card-title mb-3">FICHA DIGITAL. NO ES NECESARIO IMPRIMIR.</h4>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <img src="{{ asset('images/payment/oxxopay_brand.png') }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <h4>MONTO A PAGAR</h4>
                                        <h3>$ {{ $paymentDetail->amount }} MXN</h3>
                                        <p>OXXO cobrará una comisión adicional al momento de realizar el pago.</p>
                                    </div>
                                    <div class="col-md-6 offset-3">
                                        <label>REFERENCIA</label>
                                        <h1>{{ $paymentDetail->conekta_payment_reference_id }}</h1>
                                    </div>
                                    <div class="col-md-8 offset-2" style="font-size: 14px !important;">
                                        <h3>Instrucciones</h3>
                                        <ol>
                                            <li>Acude a la tienda OXXO más cercana. <a href="https://www.google.com.mx/maps/search/oxxo/" target="_blank">Encuéntrala aquí</a>.</li>
                                            <li>Indica en caja que quieres ralizar un pago de <strong>OXXOPay</strong>.</li>
                                            <li>Dicta al cajero el número de referencia en esta ficha para que tecleé directamete en la pantalla de venta.</li>
                                            <li>Realiza el pago correspondiente con dinero en efectivo.</li>
                                            <li>Al confirmar tu pago, el cajero te entregará un comprobante impreso. <strong>En el podrás verificar que se haya realizado correctamente.</strong> Conserva este comprobante de pago.</li>
                                        </ol>                                                                
                                        <h4>NOTA IMPORTANTE:</h4>
                                        <p>La referencia es válida por un periodo de <strong>7 días</strong> y expirará el día <strong>{{ $paymentDetail->expires_date }}</strong> a las <strong>{{ $paymentDetail->expires_time }}</strong> hrs. </p>
                                        <div class="opps-footnote">Al completar estos pasos recibirás un correo de confirmación de tu pago.<br></div>
                                        <hr>
                                    </div>
                                </div>
                                <h4 class="text-center">CONCEPTO DE SU PAGO<br></h4>
                                <div class="text-center">Invoice #{{ $paymentDetail->invoice_id }}<br>
                                    <button type="button" class="btn btn-info" onclick="printDiv()">Imprimir Comprobante</button>
                                </div>
                            </div>
                        @endif
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
@endsection

@section('bottom-js')
<script>
$(document).ready(function () {
	// Smart Wizard
	$('#smartwizard').smartWizard({
		selected: 1,
		theme: 'arrows',
		transitionEffect: 'fade',
		showStepURLhash: true,
		toolbarSettings: {
			toolbarPosition: 'none', // none, top, bottom, both
            // toolbarButtonPosition: 'right', // left, right, center
            showNextButton: false, // show/hide a Next button
            showPreviousButton: false, // show/hide a Previous button
		},
        disabledSteps: [0], // Array Steps disabled
	});
});

function printDiv() {
    var printContents = $(".cash-pay").html();
    var mywindow = window.open();
    mywindow.document.head.innerHTML = '<title></title>'; 
    mywindow.document.body.innerHTML = '<body>' + printContents + '</body>'; 
    mywindow.document.close();
    mywindow.focus(); // necessary for IE >= 10
    mywindow.print();
    mywindow.close();

    return true;
}

</script>
@endsection