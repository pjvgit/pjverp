<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('images/fav.png')}}" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    @yield('before-css')
    {{-- theme css --}}
    <link id="gull-theme" rel="stylesheet" href="{{  asset('assets/styles/css/themes/lite-purple.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/styles/vendor/perfect-scrollbar.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome-free-5.10.1-web/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/styles/vendor/metisMenu.min.css') }}">
    <link rel="stylesheet" href="{{asset('assets/styles/css/plugins/toastr.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/styles/css/plugins/ladda-themeless.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/styles/css/plugins/datatables.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/styles/css/plugins/smart.wizard/smart_wizard.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/styles/css/plugins/smart.wizard/smart_wizard_theme_circles.min.css')}}" />
    <link rel="stylesheet" media="screen" type="text/css" href="{{asset('assets/styles/css/plugins/colorpicker.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/styles/css/plugins/sweetalert2.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/styles/css/jquery-ui.css')}}">
    <link rel="stylesheet" href="{{asset('assets/styles/css/jquery.timepicker.css')}}">
    <link rel="stylesheet" href="{{asset('assets/styles/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/styles/css/bootstrap-datepicker3.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/styles/css/daterangepicker.css')}}">
    <link rel="stylesheet" href="{{asset('assets\styles\css\client-portal\custom.css')}}" />
    {{-- page specific css --}}
    @yield('page-css')
    <script>
        var baseUrl = '<?php echo URL('/');?>';
        var loaderImage = '<?=LOADER?>';
    </script>
</head>

<body>
    @php
    $layout = session('layout');
    @endphp
  
<!-- Pre Loader Strat  -->
<div class="loadscreen preloader" id="preloader" style="display: block;">
    <div class="loader"><img class="logo mb-3" src="{{asset('images/logo.png')}}" style="display: none" alt="">
        <div class="loader-bubble loader-bubble-primary d-block"></div>
    </div>
</div>
<!-- Pre Loader end  -->

<div class="app-admin-wrap layout-horizontal-bar clearfix">
    @include('client_portal.layouts.header-nav')
            
    <div class="main-content-wrap  d-flex flex-column" style="margin-top: 0px !important;">
        <div class="main-content">
            @yield('main-content')
            @include('client_portal.layouts.commonPopup')
        </div>
    </div>
</div>
{{-- Login modal --}}
{{-- <div id="login_modal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Your session has expired</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form>
                            <p>Please log in again to continue using the portal</p>
                            <input type="hidden" name="login_session[email]" value="emma@mailinator.com">
                            <div class="form-input mb-2">
                                <div class="form-input__label">Email</div>
                                <div class="u-word-wrap-break-word">emma@mailinator.com</div>
                            </div>
                            <div class="form-input is-required">
                                <label class="form-input__label" for="login_session_password">Password</label>
                                <input id="login_session_password" name="login_session[password]" required="" type="password" class="form-input__field">
                            </div>
                            <div class="mt-4">
                                <button disabled="" type="submit" aria-label="Please fill out all required fields." class="form__submit tooltipped tooltipped-ne">Log In</button>
                                <button class="btn btn-link form__cancel" type="button">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --}}

{{-- common js --}}
<script src="{{  asset('assets/js/common-bundle-script.js')}}"></script>

{{-- page specific javascript --}}


<script type="text/javascript">
// "use strict";
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
        
$(document)
.ajaxStart(function () {
    $('.preloader').show();   //ajax request went so show the loading image
})
.ajaxStop(function () {
    $('.preloader').hide();   //got response so hide the loading image
});
</script>                                        
    

<script src="{{asset('assets/js/script.js')}}"></script>
<script src="{{asset('assets/js/sidebar-horizontal.script.js')}}"></script>
<script src="{{asset('assets/js/customizer.script.js')}}"></script>
<script src="{{asset('assets/js/plugins/toastr.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/spin.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/ladda.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/scripts/datatables.script.min.js')}}"></script>
<script src="{{asset('assets/js/vendor/jquery.smartWizard.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.validate.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/colorpicker.js')}}"></script>
<script src="{{asset('assets/js/plugins/sweetalert2.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/jquery.timepicker.js')}}"></script>
<script src="{{asset('assets/js/jquery-ui.js')}}"></script>
<script src="{{asset('assets/js/custome.js')}}"></script>
<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script src="{{asset('assets/js/daterangepicker.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
<script src="{{asset('assets/js/additional-methods.js')}}"></script>
<script src="{{asset('assets/js/plugins/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/js/main.min.js')}}"></script>
<script src="{{asset('assets/js/select2.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap-datepicker.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/js/timer.jquery.min.js')}}" type="text/javascript"></script> 

@yield('page-js')

@if ($message = session('popup_success'))
<script>
    $(window).on('load', function () {
        toastr.success('{{ $message }}', "", {
            progressBar: !0,
            positionClass: "toast-top-full-width",
            containerId: "toast-top-full-width"
        });
    });
</script>
{{session(['popup_success' => ''])}}
@endif
@if ($message = session('popup_error'))
<script>
    $(window).on('load', function () {
        toastr.error('{{ $message }}', "", {
            progressBar: !0,
            positionClass: "toast-top-full-width",
            containerId: "toast-top-full-width"
        })
    });
</script>
{{session(['popup_error' => ''])}}
@endif
@if ($message = Session::get('success'))
<script>
    toastr.success('{{ $message }}', "", {
        progressBar: !0,
        positionClass: "toast-top-full-width",
        containerId: "toast-top-full-width"
    })
</script>
@endif
@if ($message = Session::get('error'))
<script>
    toastr.error('{{ $message }}', "", {
        progressBar: !0,
        positionClass: "toast-top-full-width",
        containerId: "toast-top-full-width"
    })
</script>
@endif
@yield('bottom-js')
<script>   
    // Show idle timeout warning dialog.
    function IdleWarning() {
        $("#timeoutPopup").modal("show");
    }
    // Logout the user.
    function IdleTimeout() {
        window.location = baseUrl + '/autologout';
    }

    function ResetTimers(){
        $("#timeoutPopup").modal("hide");
    }

    @if(Auth::User()->auto_logout=="on")
    $(document).ready(function () {
        setTimeout(function(){
            IdleWarning();
        }, {{(Auth::User()->sessionTime * 60000) - 50000}}); //
        var counter = 50;
        $("#ReminingTimeForLogout").html(counter);
        setTimeout(function(){
            var interval = setInterval(function () {
                counter--;
                $("#ReminingTimeForLogout").html(counter);
                if (counter == 0) {
                    IdleTimeout();
                    clearInterval(interval);
                }
            }, 1000);
        }, {{(Auth::User()->sessionTime * 60000)-50000}}); //{{Auth::User()->sessionTime * 60000}}
        
        $('#timeoutPopup').on('hidden.bs.modal', function () {
            window.location.reload();
        });

        $('body').hover(function(){
            ResetTimers()
        });
    });
    @endif

// For Header menu dropdown
$("#header-dd").trigger("click");

</script>
<style>
.search-ui {background-color: #ffffff !important;}
</style>

</body>
</html>
