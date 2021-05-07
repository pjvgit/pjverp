<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('public/images/fav.png')}}" />
    <title>@yield('title')</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/styles/css/themes/lite-purple.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/styles/css/plugins/toastr.css')}}" />
</head>

<body>
    <div class='loadscreen-bl' id="preloader">
        <div class="loader spinner-bubble spinner-bubble-primary">
        </div>
    </div>

    <div class="auth-layout-wrap" style="background-image: url({{asset('assets/images/photo-wide-4.jpg')}})">
        <div class="auth-content">
            <div class="card o-hidden">
                @yield('content')
                <?php /* <h1>{{ __('messages.welcome') }}</h1>
                        <li><a href="{{ url('locale/en') }}" ><i class="fa fa-language"></i> EN</a></li>
                        <li><a href="{{ url('locale/fr') }}" ><i class="fa fa-language"></i> FR</a></li> */?>
            </div>
        </div>
    </div>
    <!-- jQuery -->
    <script src="{{asset('assets/js/common-bundle-script.js')}}"></script>
    <script src="{{asset('assets/js/script.js')}}"></script>

    <script src="{{asset('public/assets/js/plugins/jquery-3.3.1.min.js')}}"></script>
    <script src="{{asset('public/assets/js/plugins/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('public/assets/js/plugins/perfect-scrollbar.min.js')}}"></script>
    <script src="{{asset('public/assets/js/scripts/script.min.js')}}"></script>
    <script src="{{asset('public/assets/js/scripts/sidebar.large.script.min.js')}}"></script>
    <script src="{{asset('public/assets/js/plugins/toastr.min.js')}}"></script>

    @section('page-js-script')
    <script type="text/javascript">
        $(document).ready(function () {
            $("body").load(function () {
                $('#preloader').hide();
            });

        });

    </script>
    @stop
    @yield('page-js-script')
</body>

</html>
