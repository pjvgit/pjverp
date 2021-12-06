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
                                <div id="step-1">
                                    <h3 class="border-bottom border-gray pb-2">Total: One payment of {{ $invoice->due_amount }}</h3>
									<p>Or if you chose interest free monthly payments you will pay as follows:</p>
									Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
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
    {{-- <script src="{{ asset('assets/js/scripts/script.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/js/scripts/script_2.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script> --}}
    <script src="{{ asset('assets/js/plugins/jquery.smartWizard.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/scripts/smart.wizard.script.min.js') }}"></script> --}}

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
</script>
</body>

</html>