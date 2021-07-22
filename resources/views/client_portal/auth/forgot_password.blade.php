@extends('layouts.beforelogin')
@section('title', config('app.name').' - Simplify Your Law Practice | Cloud Based Practice Management')
@section('content')

<div class="row">
    <div class="ml-4 mt-3 auth-logo text-center">
        <img src="{{asset('assets/images/logo.png')}}" alt="">
    </div>
    <div class="col-md-12">
        <div class="modal-content">

            <form class="no_validate" id="activation_form" action="{{ route('save/client/profile', $user->token) }}" method="post">
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
                      <input autocomplete="off" class="form-control" type="password" name="password_confirmation" id="activation_form_password_confirmation">
                      <div class="form-control-feedback hidden-xs-up"></div>
                    </div>
                  </div>
              
                    <div class="form-group row ">
                        <div class="col-12">
                            <input name="client_terms_acknowledgement" type="hidden" value="0">
                            <input type="checkbox" value="1" name="client_terms_acknowledgement" id="activation_form_client_terms_acknowledgement"> 
                            <label for="activation_form_client_terms_acknowledgement">I Have Read &amp; Accept the <a target="_blank" class="btn-link" href="{{ route('terms/client/portal') }}">Terms &amp; Conditions</a> (including E-SIGN)</label>
                            <span class="terms-error"></span>
                        </div>
                        <div class="col-12 form-control-feedback invisible"></div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-12">
                            <input name="client_privacy_acknowledgement" type="hidden" value="0">
                            <input type="checkbox" value="1" name="client_privacy_acknowledgement" id="activation_form_client_privacy_acknowledgement"> 
                            <label for="activation_form_client_privacy_acknowledgement">I Have Read &amp; Accept the <a target="_blank" class="btn-link" href="{{ route('privacy') }}">Privacy Policy</a></label>
                            <span class="privacy-error"></span>
                        </div>
                        <div class="col-12 form-control-feedback invisible"></div>
                    </div>
                    <input type="text" name="time_zone" id="activation_form_time_zone" value="">
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
                maxlength: 20,
            },
            password_confirmation: {
                required: true,
                minlength: 6,
                maxlength: 20,
                equalTo : "#activation_form_password"
            },
            client_terms_acknowledgement: {
                required: true,
            },
            client_privacy_acknowledgement:{
                required:true
            },
        },
        messages: {
            password_confirmation: {
                equalTo : "Please enter the same password again."
            },
        },
        errorPlacement: function (error, element) {
            if (element.is('#activation_form_client_terms_acknowledgement')) {
                error.insertAfter('.terms-error');
            }else if (element.is('#activation_form_client_privacy_acknowledgement')) {
                error.insertAfter('.privacy-error');
            } else {
                element.after(error);
            }
        }
    });
});

// For timezone
var timezone_offset_minutes = new Date().getTimezoneOffset();
timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
$.ajax({
    url: "{{ route('get/timezone') }}",
    type: "POST",
    global: false,
    data: {timezone_offset_minutes: timezone_offset_minutes, _token: $('meta[name="csrf-token"]').attr('content')},
    success: function(data) {
        $("#activation_form_time_zone").val(data);
    }
});
</script>
@stop
