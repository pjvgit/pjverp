@extends('layouts.beforelogin')
@section('title', config('app.name').' - Simplify Your Law Practice | Cloud Based Practice Management')
@section('content')

<div class="row">
    <div class="ml-4 mt-3 auth-logo text-center">
        <img src="{{asset('assets/images/logo.png')}}" alt="">
    </div>
    <div class="col-md-12">
        <div class="modal-content">
            <form class="no_validate" id="activation_form" action="{{ route('update/client/profile', $user->token) }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <h5 class="form-text">Welcome to {{ @$user->firmDetail->firm_name }}</h5>
                        <div class="form-text">{{ @$user->firmDetail->firm_name }} uses LegalCase to communicate with clients. Log in to your existing LegalCase account to access {{ @$user->firmDetail->firm_name }}. You can easily switch between different firms in LegalCase.</div>
                    </div>
                
                    @if (\Session::has('password_error'))
                    <div class="alert alert-danger alert-block hide-alert">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <strong>{{ \Session::get('password_error') }}</strong>
                    </div>
                    @endif
                
                    <div class="form-group row ">
                        <label class="col-12 col-sm-4 col-form-label">Email</label>
                        <div class="col-12 col-sm-7">
                            <div class="form-text">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-12 col-sm-4 col-form-label">
                            <label for="activation_form_password_confirmation">Password</label>
                        </div>
                        <div class="col-12 col-sm-7">
                            <input autocomplete="off" class="form-control" type="password" name="password_confirmation" id="activation_form_password_confirmation">
                            <div class="form-control-feedback invisible"></div>
                            <a href="{{ route('client/activate/account', $user->token) }}?forgot_password=true">Forgot password?</a>
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
            password_confirmation: {
                required: true,
                maxlength: 20,
            },
            client_terms_acknowledgement: {
                required: true,
            },
            client_privacy_acknowledgement:{
                required:true
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
</script>
@stop
