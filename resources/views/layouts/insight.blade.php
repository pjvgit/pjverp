<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.3.1/main.min.css"> --}}
    <link rel="stylesheet" href="{{asset('assets/styles/css/plugins/hopscotch.css')}}" />
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" /> --}}
    <link rel="stylesheet" href="{{asset('assets/styles/css/select2.min.css')}}">
    {{-- <link rel="stylesheet" type="text/css" href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" /> --}}
    <link rel="stylesheet" href="{{asset('assets/styles/css/bootstrap-datepicker3.min.css')}}">
    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" /> --}}
    <link rel="stylesheet" href="{{asset('assets/styles/css/daterangepicker.css')}}">
    {{-- page specific css --}}
    @yield('page-css')
    <script>
        var baseUrl = '<?php echo URL('/'); ?>';
    </script>
</head>

<body class="text-left">
   
    <div class="app-admin-wrap layout-horizontal-bar clearfix">
        <div class="main-content">
            @yield('main-content')
        </div>
    </div>
    
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
    
    @yield('page-js')
    @yield('page-js-inner')
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
        $("[data-toggle=popover]").popover();
        $("#userDropdown").trigger("click");
        $(".settingButtons").trigger("click");
  
          // Initialize Date Pickers
        $('.datepicker').datepicker({
            'format': 'm/d/yyyy',
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
    </script>
<style>
.search-ui {background-color: #ffffff !important;}
</style>

</body>
</html>
