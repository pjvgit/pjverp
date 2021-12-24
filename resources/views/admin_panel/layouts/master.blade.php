<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name') }} Admin - @yield('page-title')</title>
        <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
        @yield('before-css')
        {{-- theme css --}}
        <link id="gull-theme" rel="stylesheet" href="{{  asset('assets/styles/css/themes/lite-purple.min.css')}}">
        <link rel="stylesheet" href="{{asset('assets/styles/vendor/perfect-scrollbar.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome-free-5.10.1-web/css/all.css') }}">
        <link rel="stylesheet" href="{{asset('assets/styles/css/admin_custome.css')}}" />
        {{-- page specific css --}}
        @yield('page-css')
        <script>
            var baseUrl = '<?php echo URL('/');?>';
            var loaderImage = "<img src='{{ asset('images/ajax_arrows.gif') }}'/>";
            var imgBaseUrl = "{{ asset('') }}";
        </script>
    </head>


    <body class="text-left">        
        <!-- Pre Loader Strat  -->
        <div class="loadscreen" id="preloader" style="display: block;">
            <div class="loader"><img class="logo mb-3" src="{{asset('images/logo.png')}}" style="display: none"
                    alt="">
                <div class="loader-bubble loader-bubble-primary d-block"></div>
            </div>
        </div>
        <!-- Pre Loader end  -->

        <!-- ============Deafult  Large SIdebar Layout start ============= -->

        {{-- normal layout --}}
        <div class="app-admin-wrap layout-sidebar-large clearfix">
            @include('admin_panel.layouts.header-menu')
            {{-- end of header menu --}}

            @include('admin_panel.layouts.sidebar')
            {{-- end of left sidebar --}}

            <!-- ============ Body content start ============= -->
            <div class="main-content-wrap sidenav-open d-flex flex-column">
                <div class="main-content">
                    @yield('main-content')
                </div>

                @include('admin_panel.layouts.footer')
            </div>
            <!-- ============ Body content End ============= -->
        </div>
        <!-- ============ Search UI Start ============= -->
        @include('admin_panel.layouts.search')
        <!-- ============ Search UI End ============= -->
        <!--=============== End app-admin-wrap ================-->

        <!-- ============ Large Sidebar Layout End ============= -->
        {{-- common js --}}
        <script src="{{  asset('assets/js/common-bundle-script.js')}}"></script>
        {{-- page specific javascript --}}
        @yield('page-js')

        {{-- theme javascript --}}
        {{-- <script src="{{mix('assets/js/es5/script.js')}}"></script> --}}
        <script src="{{asset('assets/js/script.js')}}"></script>        
        <script src="{{asset('assets/js/sidebar.large.script.js')}}"></script>
        <script src="{{asset('assets/js/customizer.script.js')}}"></script>
        {{-- laravel js --}}
        {{-- <script src="{{mix('assets/js/laravel/app.js')}}"></script> --}}
        @yield('bottom-js')
        @if ($message = session('success'))
        <script>
            $(window).on('load', function () {
                toastr.success('{{ $message }}', "", {
                    progressBar: !0,
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                });
            });
        </script>
        {{session(['success' => ''])}}
        @endif
        <script>
            $(".search-bar input").on("click", function(){
                $('body').css('overflow', 'hidden');
                $('.search-ui').css('overflow', 'auto');
            });
        </script>
    </body>
</html>