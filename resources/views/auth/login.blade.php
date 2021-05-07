@extends('layouts.beforelogin')
@section('title', config('app.name').' :: Login')
@section('content')

<div class="row">
    <div class="col-md-6">
        <div class="p-4">
            <div class="auth-logo text-center mb-4">
              <a href="{{ route('index') }}">  <img src="{{asset('assets/images/logo.png')}}" alt=""></a>
            </div>
            <h1 class="mb-3 text-18">Login to Your Account</h1>
            @include('pages.messages')
            <form method="POST" id="login_form" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input id="email" autofocus
                        class="form-control form-control-rounded {{ $errors->has('email') ? ' is-invalid' : '' }}"
                        name="email" value="{{ old('email') }}" placeholder="Email Address" required autocomplete="email" autofocus>
                    @if ($errors->has('email'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password"
                        class="form-control form-control-rounded {{ $errors->has('password') ? ' is-invalid' : '' }}"
                        name="password" required placeholder="Password" autocomplete="current-password">
                    @if ($errors->has('password'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                    @endif

                </div>

                <button class="btn btn-rounded btn-primary btn-block mt-2" id="loginCheck">Login</button>
            </form>
            @if (Route::has('password.request'))
            <div class="mt-3 text-center">
                <a href="{{ route('password.request') }}" class="text-muted"><u>Forgot Password?</u></a>
            </div>
            @endif
        </div>
    </div>

    <div class="col-md-6 text-center "
        style="background-size: cover;background-image: url({{asset('assets/images/photo-long-3.jpg')}}">
        <div class="pr-3 auth-right">
            @if (Route::has('register'))
            Don't have an account? Test it out.
            <a href="{{ route('register') }}" class="btn btn-rounded btn-outline-primary  btn-block btn-icon-text ">
                <i class="i-Mail-with-At-Sign"></i> Start Your Free Trial
            </a>
            @endif
          
        </div>
    </div>
</div>
@endsection

@section('page-js-script')
<script type="text/javascript">
    $(document).ready(function () {
        $('#login_form').submit(function () {
            $('#preloader').show();
        });
    });
</script>
@stop
