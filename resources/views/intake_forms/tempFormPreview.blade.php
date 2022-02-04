<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="robots" content="noindex">
    <title>
        {{$firmData->firm_name}} - {{$request->form_name}} - {{config('app.name')}}
    </title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="{{asset('assets/styles/css/preview/bootstrap-style-preview.css')}}">
    <link rel="stylesheet" media="screen" href="{{asset('assets/styles/css/preview/spacing.css')}}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="{{asset('assets/styles/css/bootstrap-datepicker3.min.css')}}">
    <script src="{{asset('assets/js/bootstrap-datepicker.js')}}" type="text/javascript"></script>
    <style>
        .logoView {
            width: 50px;
        }

        .error {
            color: red;
        }

        .form-field-container {
            color: {{$request->form_font_color}};
        }

    </style>
</head>

<body class="">
    <div class="main-content">
        <div id="form-preview-root" class="form-container">
            <div class="form p-4 p-md-5 mx-auto shadow" data-testid="form-container"
                style="background-color: {{$request->background_color}}; color: rgb(0, 0, 0); font-family: {{$request->form_font}}">
                <div class="alert alert-primary preview-banner" role="alert">This preview allows you to see what clients
                    will see. No data will be collected or recorded.</div>
                <div id="form-info">
                    <h3 class="heading mb-3 test-firm-name">{{$firmData->firm_name}}</h3>
                    <h4 class="font-weight-bold heading mb-3 test-form-name">{{$request->form_name}}</h4>

                    <p class=" heading mb-3 test-form-name">{{$request->form_introduction}}</p>
                </div>
                <hr class="mt-4">
                <div id="form-body">
                    <div id="form-footer" class="border-top pt-3 mt-3 d-print-none">
                        <button type="button" id="submit-button" disabled="" class="btn btn-cta-primary disabled"
                            style="background-color:#{{$request->button_color}}; color:#{{$request->button_font_color}}; font-family: {{$request->button_font}}; border-color:#{{$request->button_color}};">Submit
                            Form</button>
                        <button type="button" id="save-progress-button" disabled=""
                            class="ml-2 btn btn-secondary disabled"
                            style="background-color:#{{$request->button_color}}; color:#{{$request->button_font_color}}; font-family: {{$request->button_font}}; border-color:#{{$request->button_color}};">Save
                            Progress</button>
                        <div class="py-3 py-md-0 d-md-inline-block ml-md-3"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mycase-watermark text-muted text-center">
            <span class="mr-1">Powered by</span>
            <img class="logoView" src="{{BASE_URL}}assets/images/logo.png">
        </div>
    </div>
</body>

<script type="text/javascript">
    $(document).ready(function () {
        <?php if($v->form_field=="birthday"){ ?>
        $('#birthday').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            endDate: '+0d',
            'todayHighlight': true
        });
        <?php } 
        if($v->form_field=="date"){ ?>
        $('#datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        <?php } ?>
    });
</script>
</html>
