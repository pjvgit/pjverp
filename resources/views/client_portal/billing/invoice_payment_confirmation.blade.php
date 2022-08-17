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
                                    <p>@lang('billing.confirm_note_2') {{ ($payableType == 'fundrequest') ? 'Request #'.$paymentDetail->fund_request_id : 'Invoice #'.$invoice->invoice_id}}</p>
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
                                        @php
                                            $userTime = convertToUserTimezone($paymentDetail->conekta_reference_expires_at, auth()->user()->user_timezone ?? 'UTC');
                                        @endphp
                                        <p>La referencia es válida por un periodo de <strong>7 días</strong> y expirará el día <strong>{{ $userTime->format('d-m-Y') }}</strong> a las <strong>{{ $userTime->format('H:i') }}</strong> hrs. </p>
                                        <div class="opps-footnote">Al completar estos pasos recibirás un correo de confirmación de tu pago.<br></div>
                                        <hr>
                                    </div>
                                </div>
                                <h4 class="text-center">CONCEPTO DE SU PAGO<br></h4>
                                <div class="text-center">{{ ($payableType == 'fundrequest') ? 'Request #'.$paymentDetail->fund_request_id : 'Invoice #'.$invoice->invoice_id}}<br>
                                    <button type="button" class="btn btn-info" onclick="printDiv('cash-pay')">Imprimir Comprobante</button>
                                </div>
                            </div>
                        @elseif($paymentDetail->payment_method == 'bank transfer')
                            <div class="mt-2 app-container__content">
                                <div class="bank-pay row">
                                    <div class="col-md-12 text-center">
                                        <h3 class="card-title mb-3">Transferencia Interbancaria (SPEI o TEF) con CLABE</h3>
                                    </div>
                                    <div class="col-md-8 offset-2" style="font-size: 14px !important;">
                                        <p>Para finalizar la compra haga el pago SPEI o TEF utilizando los siguientes datos:</p>
                                        <p>Banco: <strong>{{ $paymentDetail->conekta_order_object['charges']['data'][0]['payment_method']['bank'] }}</strong></p>
                                        <p>Beneficiario: <strong>{{ @$paymentDetail->firmDetail->firm_name }}</strong></p>
                                        <p>CLABE Interbancaria: <strong>{{ $paymentDetail->conekta_payment_reference_id }}</strong></p>
                                        <p>Monto: <strong>{{ $paymentDetail->amount }} MXN</strong></p>
                                        <h3>¡TEN EN CUENTA!</h3>
                                        <ol>
                                            <li>El presente comprobante solo es válido para el pago que estás efectuando. Si mandas un SPEI el pago se verá reflejado inmediatamente y si mandas un TEF el pago se verá reflejado el día programado a las 10 a.m.</li>
                                            <li>Si tienes dudas sobre tu compra escríbenos a <a href="mailto:{{ ($payableType == 'fundrequest') ? @$fundRequest->createdByUser->email : @$invoice->createdByUser->email }}" >{{ ($payableType == 'fundrequest') ? @$fundRequest->createdByUser->email : @$invoice->createdByUser->email }}</a></li>
                                            <li>Pague antes de <strong>{{ $paymentDetail->expires_date }}</strong>, de lo contrario el comprobante ya no será válido y tendrá que generar uno nuevo.</li>
                                            <li>La CLABE que le hemos proporcionado es dinámica y de un solo uso. Si en el futuro quiere hacer otro SPEI o TEF, deberá generar otro comprobante igual a este para que reciba otra CLABE nueva.</li>
                                        </ol>                                                                
                                        <div class="text-center">
                                            <h2>Concepto de su Pago</h2>{{ ($payableType == 'fundrequest') ? 'Request #'.$paymentDetail->fund_request_id : 'Invoice #'.$invoice->invoice_id}}<br>
                                            <br>
                                            <img src="{{ asset('images/payment/spei.jpg') }}" alt="SPEI">
                                            <br><br> 
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="button" class="btn btn-info" onclick="printDiv('bank-pay')">Imprimir Comprobante</button>
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

function printDiv(className) {
    var printContents = $("."+className).html();
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