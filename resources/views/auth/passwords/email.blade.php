@extends('layouts.beforelogin')
@section('title', config('app.name').' :: Forgot Password')
@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="p-4">
            <div class="auth-logo text-center mb-4">
                <a href="{{ route('index') }}">  <img src="{{asset('assets/images/logo.png')}}" alt=""></a>
            </div>
            <h1 class="mb-3 text-18">Forgot Password?</h1>
            @include('pages.messages')
            <form method="POST" id="reset_form" action="{{ route('password.email') }}">
                @csrf
                Enter your login email below. We will send you an email with a link to reset your account password. <br>
                <p>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input id="email"
                            class="form-control form-control-rounded {{ $errors->has('email') ? ' is-invalid' : '' }}"
                            name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-rounded mt-3">
                        {{ __('Submit') }}
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
    $(document).ready(function () {
        $('#reset_form').submit(function () {
            $('#preloader').show();
        });
    });
</script>
@stop