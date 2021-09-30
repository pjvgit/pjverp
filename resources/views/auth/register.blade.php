@extends('layouts.beforelogin')
@section('title', config('app.name').' :: Signup')
@section('content')
<div class="row">
    <div class="col-md-6 text-center "
        style="background-size: cover;background-image: url({{asset('assets/images/photo-long-3.jpg')}})">
        <div class="pl-3 auth-right">
            <div class="auth-logo text-center mt-4">
                <a href="{{ route('index') }}"> <img src="{{asset('assets/images/logo.png')}}" alt=""></a>
            </div>
            <div class="flex-grow-1"></div>
            <div class="w-100 mb-4"> Login to your account.
                <a class="btn btn-outline-primary btn-outline-email btn-block btn-icon-text btn-rounded"
                    href="{{ route('login') }}">
                    <i class=" i-Mail-with-At-Sign"></i> Sign in with Email
                </a>
            </div>
            <div class="flex-grow-1"></div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="p-4">
            <h1 class="mb-3 text-18">Try {{config('app.name')}} FREE!</h1>
            <h6 class="mb-4 text-4">Full access. No Credit card required.</h6>
            @include('pages.messages')
            <form method="POST" id="signup_form" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label for="username">First Name</label>
                    <input id="name" type="text"
                        class="form-control-rounded form-control {{ $errors->has('first_name') ? ' is-invalid' : '' }}"
                        name="first_name" value="{{ old('first_name') }}" maxlength="240" placeholder="First Name"
                        required autocomplete="first_name" autofocus>

                    @if ($errors->has('first_name'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('first_name') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="username">Last Name</label>
                    <input id="name" type="text"
                        class="form-control-rounded form-control {{ $errors->has('last_name') ? ' is-invalid' : '' }}"
                        name="last_name" value="{{ old('last_name') }}" maxlength="240" placeholder="Last Name" required
                        autocomplete="last_name" autofocus>

                    @if ($errors->has('last_name'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('last_name') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input id="email" type="email"
                        class="form-control-rounded form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                        name="email" value="{{ old('email') }}" maxlength="240" placeholder="Email Address" required
                        autocomplete="email">

                    @if ($errors->has('email'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="email">Firm Name</label>
                    <input id="firm_name" type="text"
                        class="form-control-rounded form-control{{ $errors->has('firm_name') ? ' is-invalid' : '' }}"
                        name="firm_name" value="{{ old('firm_name') }}" maxlength="240" placeholder="Firm Name" required
                        autocomplete="firm_name">

                    @if ($errors->has('firm_name'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('firm_name') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="email">Mobile Number</label>
                    <input id="mobile_number" type="text"
                        class="form-control-rounded form-control{{ $errors->has('mobile_number') ? ' is-invalid' : '' }}"
                        name="mobile_number" value="{{ old('mobile_number') }}"
                        placeholder="Mobile Number" required
                        autocomplete="mobile_number"> <!-- pattern="[(][0-9]{3}[)][0-9]{3}-[0-9]{4}"-->

                    @if ($errors->has('mobile_number'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('mobile_number') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="email">Number of Firm Employees</label>
                    <input id="employee_no" type="number"
                        class="form-control-rounded form-control{{ $errors->has('employee_no') ? ' is-invalid' : '' }}"
                        name="employee_no" value="{{ old('employee_no') }}" placeholder="Number of Firm Employees"
                        max-length="5" min="1" required autocomplete="employee_no">

                    @if ($errors->has('employee_no'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('employee_no') }}</strong>
                    </span>
                    @endif
                </div>
                <i class="i-Lock"></i>
                <p class="privacy">By clicking "Start Your Free Trial", you assert that you have read and agreed to our
                    <a href="/terms" target="_blank">Terms of Service</a>. For information about how we collect, store
                    and use your personal data, please read our <a href="/privacy" target="_blank">Privacy Policy</a>.
                </p>
                <button type="submit" class="btn btn-primary btn-block btn-rounded mt-3">Start Your Free Trial</button>
            </form>
        </div>
    </div>
</div>

@endsection
@section('page-js-script')
<script type="text/javascript">
    $(document).ready(function () {
        $('#signup_form').submit(function () {
            $('#preloader').show();
        });
    });

</script>
@stop
