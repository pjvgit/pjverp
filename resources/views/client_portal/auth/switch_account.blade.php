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
    <link rel="stylesheet" href="{{asset('assets/styles/css/themes/lite-purple.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/styles/css/plugins/toastr.css')}}" />
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
            <div class="text-center"> <i class="fa fa-user fa-5x" aria-hidden="true"></i>
                <div class="font-weight-bold">{{ $client->full_name }}</div>
            </div>
        </div>
        <div class="col-12 text-center pt-3"> Which account would you like to log into? </div>
        <div class="col-12">
            <div class="list-group account-container" role="group" data-primary-user-url="https://auth.mycase.com/login_sessions/primary_user">
                @forelse ($firms as $item)
                    <form class="launchpad-select-user-form" action="{{ route('login/sessions/selectuser') }}" method="post">
                        @csrf
                        {{-- <input type="hidden" name="authenticity_token" value="1HGsB+gb4nzgStIlrdyv+++bLpIhnrw1tKFRJgtwc9krpnk3hLeysBxWiYG8GClUw/Em/qNbGOIggxLM5Psr9g=="> --}}
                        <button id="launchpad-83a0987a-1d5c-4bc3-a549-5de8416cc223" class="btn btn-outline-secondary my-2 btn-block text-break selected-primary launchpad">
                            <div class="d-flex align-items-center">
                                <div class=""> {{ $item->firm_name }}
                                    @if(count($item->user) && $item->user[0]->is_primary_account == 'yes')
                                    <div class="badge badge-pill badge-primary primary-badge ">Primary</div>
                                    @endif
                                </div>
                                <div class="ml-auto">
                                    <div class="launchpad-user-type"> Client / Contact <i class="fa fa-lg fa-arrow-circle-right ml-2" aria-hidden="true"></i> </div>
                                    <div class="launchpad-spinner d-none">
                                        <div class="spinner-border spinner-border-sm" role="status"> <span class="sr-only">Loading...</span> </div>
                                    </div>
                                </div>
                            </div>
                        </button>
                        <input type="hidden" name="client_id" id="client_id" value="{{ encodeDecodeId(@$item->user[0]->id, 'encode') }}">
                        <input type="hidden" name="redirect_uri" id="redirect_uri" value="https://le-and-nash-llc.mycase.com/user_sessions/o_auth_callback"> 
                    </form>
                @empty
                @endforelse
                {{-- <form class="launchpad-select-user-form" action="https://auth.mycase.com/login_sessions/select_user" accept-charset="UTF-8" method="post">
                    <input name="utf8" type="hidden" value="✓">
                    <input type="hidden" name="authenticity_token" value="1HGsB+gb4nzgStIlrdyv+++bLpIhnrw1tKFRJgtwc9krpnk3hLeysBxWiYG8GClUw/Em/qNbGOIggxLM5Psr9g==">
                    <button id="launchpad-83a0987a-1d5c-4bc3-a549-5de8416cc223" class="btn btn-outline-secondary my-2 btn-block text-break selected-primary launchpad" data-user-id="83a0987a-1d5c-4bc3-a549-5de8416cc223">
                        <div class="d-flex align-items-center">
                            <div class=""> Le and Nash LLC - le-and-nash-llc.mycase.com
                                <div class="badge badge-pill badge-primary primary-badge ">Primary</div>
                            </div>
                            <div class="ml-auto">
                                <div class="launchpad-user-type"> Client / Contact <i class="fa fa-lg fa-arrow-circle-right ml-2" aria-hidden="true"></i> </div>
                                <div class="launchpad-spinner d-none">
                                    <div class="spinner-border spinner-border-sm" role="status"> <span class="sr-only">Loading...</span> </div>
                                </div>
                            </div>
                        </div>
                    </button>
                    <input type="hidden" name="user_id" id="user_id" value="83a0987a-1d5c-4bc3-a549-5de8416cc223">
                    <input type="hidden" name="client_id" id="client_id" value="tCEM8hNY7GaC2c8P">
                    <input type="hidden" name="redirect_uri" id="redirect_uri" value="https://le-and-nash-llc.mycase.com/user_sessions/o_auth_callback"> 
                </form>
                <form class="launchpad-select-user-form" action="https://auth.mycase.com/login_sessions/select_user" accept-charset="UTF-8" method="post">
                    <input name="utf8" type="hidden" value="✓">
                    <input type="hidden" name="authenticity_token" value="Y+qq1EI7nCnviDFarXQD3oYOtEpt3RpBYBIe/Z5KFJCcPX/kLpfM5ROUav68sIVxqmS8Ju8Yvpb0MF0XccFMvw==">
                    <button id="launchpad-c838d91d-4020-45c5-85f6-18352583705b" class="btn btn-outline-secondary my-2 btn-block text-break  launchpad" data-user-id="c838d91d-4020-45c5-85f6-18352583705b">
                        <div class="d-flex align-items-center">
                            <div class=""> plutus - plutus14.mycase.com
                                <div class="badge badge-pill badge-primary primary-badge d-none">Primary</div>
                            </div>
                            <div class="ml-auto">
                                <div class="launchpad-user-type"> Client / Contact <i class="fa fa-lg fa-arrow-circle-right ml-2" aria-hidden="true"></i> </div>
                                <div class="launchpad-spinner d-none">
                                    <div class="spinner-border spinner-border-sm" role="status"> <span class="sr-only">Loading...</span> </div>
                                </div>
                            </div>
                        </div>
                    </button>
                    <input type="hidden" name="user_id" id="user_id" value="c838d91d-4020-45c5-85f6-18352583705b">
                    <input type="hidden" name="client_id" id="client_id" value="tCEM8hNY7GaC2c8P">
                    <input type="hidden" name="redirect_uri" id="redirect_uri" value="https://plutus14.mycase.com/user_sessions/o_auth_callback"> 
                </form> --}}
            </div>
        </div>
        <div class="col-12 text-center">
            <div id="standard-options">
                <form action="https://auth.mycase.com/login_sessions/logout?client_id=tCEM8hNY7GaC2c8P" accept-charset="UTF-8" method="get">
                    <input name="utf8" type="hidden" value="✓">
                    <button class="btn btn-sm btn-link text-black-50 change-account"> Change Primary Account </button>
                    <button class="btn btn-sm btn-secondary"> Log Out </button>
                </form>
            </div>
        </div>
        <div class="col-12 text-center">
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

</body>
</html>