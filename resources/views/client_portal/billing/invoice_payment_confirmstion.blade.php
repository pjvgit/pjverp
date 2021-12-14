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
                        <div id="step-1" class="p-3">
                        </div>
                        <div id="step-2" class="text-center mb-2">
                            <div class="mt-2 mb-2">
                                <img src="{{ asset('images/check.png') }}" />
                            </div>
                            <h3 class="pb-2">@lang('billing.confirm_title')</h3>
                            <div class="row o-hidden">
                                <div class="col-md-12">
                                    <h4>@lang('billing.confirm_note_1', ['amount' => number_format($paymentDetail->amount ?? 0, 2)])</h4>
                                    <p>@lang('billing.confirm_note_2') Invoice #{{ $invoice->id }}</p>
                                    <p>@lang('billing.confirm_note_3')</p>
                                    <p>@lang('billing.confirm_note_4')</p>  
                                </div>
                            </div>
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
</script>
@endsection