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
    <link rel="stylesheet" href="{{asset('assets/styles/css/custome.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/styles/css/jquery-ui.css')}}">
    <link rel="stylesheet" href="{{asset('assets/styles/css/jquery.timepicker.css')}}">
    <link href="{{asset('assets/styles/css/quill.snow.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/styles/vendor/calendar/fullcalendar.min.css')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@1.10.1/dist/scheduler.min.css">
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.3.1/main.min.css"> --}}
    <link rel="stylesheet" href="{{asset('assets/styles/css/plugins/hopscotch.css')}}" />
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" /> --}}
    <link rel="stylesheet" href="{{asset('assets/styles/css/select2.min.css')}}">
    {{-- <link rel="stylesheet" type="text/css" href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" /> --}}
    <link rel="stylesheet" href="{{asset('assets/styles/css/bootstrap-datepicker3.min.css')}}">
    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" /> --}}
    <link rel="stylesheet" href="{{asset('assets/styles/css/daterangepicker.css')}}">
    <link rel="stylesheet" href="{{asset('assets/styles/css/rcrop.min.css')}}">
    {{-- page specific css --}}
    @yield('page-css')
    <script>
        var baseUrl = '<?php echo URL('/');?>';
        var loaderImage = '<?=LOADER?>';
        var imgBaseUrl = "{{ asset('') }}";
    </script>
</head>

{{-- <body class="text-left" onload="StartTimers();" onmousemove="ResetTimers();"> --}}
<body class="text-left">
<input type="hidden" name="auth_login_user_id" id="auth_login_user_id" value="{{Auth::user()->id}}">
    @php
    $layout = session('layout');
    @endphp
  
    <!-- Pre Loader Strat  -->
    <div class="loadscreen" id="preloader" style="display: block;">
        <div class="loader"><img class="logo mb-3" src="{{asset('images/logo.png')}}" style="display: none"
                alt="">
            <div class="loader-bubble loader-bubble-primary d-block"></div>
        </div>
    </div>
    <!-- Pre Loader end  -->
    <!-- ============ Compact Layout start ============= -->
    @if($layout=="horizontal")
    <div class="app-admin-wrap layout-horizontal-bar clearfix">
        @include('layouts.header-menu')
        <!-- ============ end of header menu ============= -->
        @include('layouts.horizontal-bar')
        <!-- ============ end of left sidebar ============= -->
        <!-- ============ Body content start ============= -->
        <div class="main-content-wrap  d-flex flex-column">
            <div class="main-content">
                @yield('main-content')
                @include('commonPopup.popup_without_param_code')
            </div>
            @include('layouts.footer')
        </div>
        <!-- ============ Body content End ============= -->
    </div>
    <!--=============== End app-admin-wrap ================-->
    <!-- ============ Search UI Start ============= -->
    @include('layouts.search')
    <!-- ============ Search UI End ============= -->
    {{-- @include('layouts.horizontal-customizer') --}}
    <!-- ============ Horizontal Layout End ============= -->
    <!-- ============ Vetical SIdebar Layout start ============= -->
    @elseif($layout=="vertical")
    <div class="app-admin-wrap layout-sidebar-vertical sidebar-full">
        @include('layouts.vertical.sidebar')
        <div class="main-content-wrap  mobile-menu-content bg-off-white m-0">
            @include('layouts.vertical.header')
            <div class="main-content pt-4">
                @yield('main-content')
            </div>
        </div>
        <div class="sidebar-overlay open"></div>
    </div>
    <!-- ============ Vetical SIdebar Layout End ============= -->
    <!-- ============ Large SIdebar Layout start ============= -->
    @elseif($layout=="normal")
    <div class="app-admin-wrap layout-sidebar-large clearfix">
        @include('layouts.header-menu')
        <!-- ============ end of header menu ============= -->
        @include('layouts.sidebar')
        <!-- ============ end of left sidebar ============= -->
        <!-- ============ Body content start ============= -->
        <div class="main-content-wrap sidenav-open d-flex flex-column">
            <div class="main-content">
                @yield('main-content')
            </div>
            @include('layouts.footer')
        </div>
        <!-- ============ Body content End ============= -->
    </div>
    <!--=============== End app-admin-wrap ================-->
    <!-- ============ Search UI Start ============= -->
    @include('layouts.search')
    <!-- ============ Search UI End ============= -->
    <!-- ============ Large Sidebar Layout End ============= -->
    @else
    <!-- ============Deafult  Large SIdebar Layout start ============= -->
    <div class="app-admin-wrap layout-horizontal-bar clearfix">
        @include('layouts.header-menu')
        <!-- ============ end of header menu ============= -->
        @include('layouts.horizontal-bar')
        <!-- ============ end of left sidebar ============= -->
        <!-- ============ Body content start ============= -->
        <div class="main-content-wrap  d-flex flex-column">
            <div class="main-content">
                @yield('main-content')
                @include('commonPopup.popup_without_param_code')
            </div>
            @include('layouts.footer')
        </div>
        <!-- ============ Body content End ============= -->
    </div>
    <!--=============== End app-admin-wrap ================-->
    <!-- ============ Search UI Start ============= -->
    @include('layouts.search')
    <!-- ============ Search UI End ============= -->
    {{-- @include('layouts.large-sidebar-customizer') --}}
    <!-- ============ Large Sidebar Layout End ============= -->
    @endif
    <div class="printDiv"></div>
    {{-- @include('layouts.customizer') --}}
    {{-- common js --}}
    <script src="{{  asset('assets/js/common-bundle-script.js')}}"></script>
    
    {{-- page specific javascript --}}


    <script type="text/javascript">
        "use strict";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    </script>                                        
    
    @yield('page-js-inner')
    @yield('page-js-common')

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
    {{-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> --}}
    <script src="{{asset('assets/js/custome.js')}}"></script>
    <script src="{{asset('assets/js/quill.min.js')}}" type="text/javascript"></script>
    {{-- <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script> --}}
    <script src="{{asset('assets/js/moment.min.js')}}"></script>
    {{-- <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script> --}}
    <script src="{{asset('assets/js/daterangepicker.min.js')}}"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.13.4/jquery.mask.min.js"></script> --}}
    <script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
    {{-- <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.js"></script> --}}
    <script src="{{asset('assets/js/additional-methods.js')}}"></script>
    <script src="{{asset('assets/js/plugins/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/js/vendor/calendar/fullcalendar.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@1.10.1/dist/scheduler.min.js" ></script>
    {{-- <script src="{{asset('assets/js/calendar.script.js')}}"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.3.1/main.min.js"></script> --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script> --}}
    <script src="{{asset('assets/js/plugins/hopscotch.min.js')}}"></script>
    <script src="{{asset('assets/js/main.min.js')}}"></script>
    <script src="{{asset('assets/js/Sortable.js')}}"></script>
    <script src="{{asset('assets/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/js/bootstrap-datepicker.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/datepair.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/timer.jquery.min.js')}}" type="text/javascript"></script> 
    <script src="{{asset('assets/js/jquery.number.js')}}" type="text/javascript"></script> 
    <script src="{{asset('assets/js/scripts/script.min.js')}}" type="text/javascript"></script> 
    <script src="{{asset('assets/js/jquery.number.js')}}"  type="text/javascript"></script>
    <script src="{{asset('assets/js/plugins/apexcharts.min.js')}}"></script>
    <script src="{{asset('assets/js/action.js')}}"></script>
    <script src="{{asset('assets/js/rcrop.min.js')}}"></script>
    <script src="{{asset('assets/js/tooltip.script.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js" ></script>

    @yield('page-js')

    <script src="{{ asset('assets\js\custom\common\common.js') }}"></script>
    <script src="{{ asset('assets\js\custom\case\addcase.js?').env('CACHE_BUSTER_VERSION') }}"></script>
    {{-- <script src="{{asset('assets/js/scripts/apexPieDonutChart.script.min.js')}}"></script> --}}
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
          $(document).ready(function () {
             $("[data-toggle=popover]").popover();
          });
        $("#userDropdown").trigger("click");
        $(".settingButtons").trigger("click");
  
          // Initialize Date Pickers
        $('.datepicker').datepicker({
            'format': 'mm/dd/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        $(document).on('keypress , paste', '.number', function (e) {
            if (/^-?\d*[,.]?(\d{0,3},)*(\d{3},)?\d{0,3}$/.test(e.key)) {
                $('.number').on('input', function () {
                    e.target.value = numberSeparator(e.target.value);
                });
            } else {
                e.preventDefault();
                return false;
            }
        });
        
        // Show idle timeout warning dialog.
        function IdleWarning() {
         $("#timeoutPopup").modal("show");
        }
        // Logout the user.
        function IdleTimeout() {
            // $("#logout-form").submit();
            window.location = baseUrl + '/autologout';
        }

        function ResetTimers(){
            $("#timeoutPopup").modal("hide");
        }
       
        <?php if(Auth::User()->auto_logout=="on"){?>
        if ((localStorage.getItem("smart_timer_id") > 0 && localStorage.getItem("pauseCounter") != 'no') || (localStorage.getItem("pauseCounter") == null)){
            console.log("auto_logout > on ");
        <?php //if(Auth::User()->dont_logout_while_timer_runnig == "off"){?>
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
        }
        <?php } ?>
        
    </script>
<style>
.search-ui {background-color: #ffffff !important;}
</style>


<div id="timeoutPopup" class="modal fade bd-example-modal-lg" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" tabindex="-1" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Session About To Timeout</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <p>You will be automatically logged out in <strong><span id="ReminingTimeForLogout"></span> </strong><br />
                            To remain logged in please move the mouse on the screen.
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="notification_popup" class="modal fade bd-example-modal-lg" role="dialog" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Upcoming Reminders</h5>
                <button class="close" type="button" /* data-dismiss="modal" */ aria-label="Close" id="popup_close_btn"><span aria-hidden="true">×</span></button>
            </div>
            <div id="notify_modal_body"> </div>
        </div>
    </div>
</div>
</body>
</html>
