@extends('layouts.beforelogin')
@section('title', config('app.name').' - Simplify Your Law Practice | Cloud Based Practice Management')
@section('content')

<div class="row">
    <div class="ml-4 mt-3 auth-logo text-center">
        <img src="{{asset('assets/images/logo.png')}}" alt="">
    </div>
    <div class="col-md-12">
        <div class="modal-content">

            <form class="no_validate" id="activation_form" action="{{ route('reset/password', $user->token) }}" method="post">
                @csrf
                <div class="modal-body">
                  <div class="form-group">
                    <h5 class="form-text">Reset Password</h5>
                    <div class="form-text">We already have verified your email address. Please reset your password.</div>
                  </div>
              
              
                  <div class="form-group row ">
                    <div class="col-12 col-sm-4 col-form-label">
                      <label for="activation_form_password">Reset password</label>
                    </div>
                    <div class="col-12 col-sm-7">
                        <input autocomplete="off" class="form-control" type="password" name="password" id="activation_form_password">
                    </div>
                    <div class="col-12 col-sm-7 offset-sm-4 form-control-feedback hidden-xs-up"></div>
                    <div class="col-9 col-sm-5 offset-sm-4 form-text">Minimum of 6 characters</div>
                  </div>
                  <div class="form-group row ">
                    <div class="col-12 col-sm-4 col-form-label">
                      <label for="activation_form_password_confirmation">Confirm password</label>
                    </div>
                    <div class="col-12 col-sm-7">
                      <input autocomplete="off" class="form-control" type="password" name="confirm_password" id="activation_form_password_confirmation">
                      <div class="form-control-feedback hidden-xs-up"></div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                    <button id="activation-form-submit" type="submit" class="btn btn-primary">Log In</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('page-js-script')
<script type="text/javascript">
  
$(document).ready(function () {
    $('#password_update').submit(function () {
        $('#preloader').show();
    });

    $("#activation_form").validate({
        rules: {
            password: {
                required: true,
                minlength: 6,
                // maxlength: 20,
            },
            confirm_password: {
                required: true,
                minlength: 6,
                // maxlength: 20,
                equalTo : "#activation_form_password"
            },
        },
        messages: {
            confirm_password: {
                equalTo : "Please enter the same password again."
            },
        },
        errorPlacement: function (error, element) {
                element.after(error);
        }
    });
});

</script>
@stop
