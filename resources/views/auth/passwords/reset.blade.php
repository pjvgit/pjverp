@extends('layouts.beforelogin')
@section('title', config('app.name').' :: Reset Password')
@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="p-4">
            <div class="auth-logo text-center mb-4">
                <a href="{{ route('index') }}">  <img src="{{asset('assets/images/logo.png')}}" alt=""><a/>
            </div>
            <h1 class="mb-3 text-18">Reset Your {{config('app.name')}} Password</h1>
            @include('pages.messages')
            <form id="password_update" method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input id="email"
                        class="form-control form-control-rounded {{ $errors->has('email') ? ' is-invalid' : '' }}"
                        name="email" readonly value="{{ $email ?? old('email') }}" required autocomplete="email"
                        autofocus>
                    @if ($errors->has('email'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input id="password" type="password"
                        class="form-control form-control-rounded {{ $errors->has('password') ? ' is-invalid' : '' }}"
                        name="password" required autocomplete="off">
                    @if ($errors->has('password'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                    @endif

                </div>
                <div class="form-group">
                    <label for="password">Confirm Password</label>
                    <input id="password" type="password"
                        class="form-control form-control-rounded {{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                        name="password_confirmation" required autocomplete="off">
                    @if ($errors->has('password_confirmation'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                    </span>
                    @endif

                </div>

                <button type="submit" class="btn btn-primary btn-block btn-rounded mt-3">
                    {{ __('Reset') }}
                </button>
            </form>

        </div>
    </div>
    <div class="col-md-6 text-center "
        style="background-size: cover;background-image: url({{asset('assets/images/photo-long-3.jpg')}}">
        <div class="pr-3 auth-right">
            @if (Route::has('login'))
            Login to your account.
            <a href="{{ route('login') }}"
                class="btn btn-rounded btn-outline-primary btn-outline-email btn-block btn-icon-text">
                <i class="i-Mail-with-At-Sign"></i> Sign in with Email
            </a>
            @endif

        </div>
    </div>
</div>
@endsection

@section('page-js-script')
<script type="text/javascript">
    "use strict";
    $('#password_update').attr('autocomplete', 'off');
    $(document).ready(function () {
        $('#password_update').submit(function () {
            $('#preloader').show();
        });
    });

</script>
@stop
