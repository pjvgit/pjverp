<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('public/images/fav.png')}}" />
    <title>{{ config('app.name').' - Simplify Your Law Practice | Cloud Based Practice Management' }}</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome-free-5.10.1-web/css/all.css') }}">
    <link rel="stylesheet" href="{{asset('assets/styles/css/themes/lite-purple.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/styles/css/plugins/toastr.css')}}" />
    <script>
        var baseUrl = '<?php echo URL('/');?>';
        var loaderImage = '<?=LOADER?>';
    </script>
</head>
<body>

<nav class="navbar navbar-fixed-top justify-content-center">
    <div class="navbar-brand mh-100">
        <div class="ml-4 mt-3 auth-logo text-center">
            <img src="{{asset('assets/images/logo.png')}}" alt="">
        </div>
    </div>
</nav>
<div class="container">
    <div id="primary-user-error-alert" class="alert alert-danger d-none"> Sorry, your primary user could not be changed, please try again later </div>
    <div class="row">
        <div class="col-12">
            <div class="text-center"> <i class="fas fa-user fa-5x" aria-hidden="true"></i>
                <div class="font-weight-bold">{{ $client->full_name }}</div>
            </div>
        </div>
        <div class="col-12 text-center pt-3"> Which account would you like to log into? </div>
        <div class="col-12">
            <div class="list-group account-container" role="group" data-primary-user-url="https://auth.mycase.com/login_sessions/primary_user">
                @include('client_portal.auth.partial.load_user_account_list', ['firms' => $firms])
            </div>
        </div>
        <div class="col-12 text-center">
            <div id="standard-options">
                <form action="" method="get">
                    <input type="hidden" name="change_account" id="change_account" value="no">
                    <button type="button" class="btn btn-sm btn-link text-black-50 change-account"> Change Primary Account </button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-secondary"> Log Out </button>
                </form>
            </div>
            <div id="select-options" class="d-none"> <span class="py-2">
                <small class="alert alert-info">
                  Click on an account above to select it as your primary account
                </small>
              </span>
                <button class="btn btn-sm btn-link text-black-50 cancel-change-account"> Cancel </button>
            </div>
        </div>
    </div>
</div>
<script src="{{  asset('assets/js/common-bundle-script.js')}}"></script>
<script src="{{ asset('assets\client_portal\js\profile\switchaccount.js') }}" ></script>
</body>
</html>