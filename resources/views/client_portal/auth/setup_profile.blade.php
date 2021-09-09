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
                        <h5 class="form-text">Welcome to {{ @$user->firmDetail->firm_name }}</h5>
                        <div class="form-text">Get 24/7 access to your case, share documents, and send confidential messages using our secure Client Portal, powered by LegalCase. Set your password below to create your account.</div>
                    </div>                
                
                    <div class="form-group row ">
                        <label class="col-12 col-sm-4 col-form-label">Set Password</label>
                        <div class="col-12 col-sm-7">
                            <input autocomplete="off" class="form-control" type="password" name="password" id="activation_form_password">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <div class="col-12 col-sm-4 col-form-label">
                            <label for="activation_form_password_confirmation">Confirm Password</label>
                        </div>
                        <div class="col-12 col-sm-7">
                            <input autocomplete="off" class="form-control" type="password" name="confirm_password" id="activation_form_password_confirmation">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-12 col-sm-4 col-form-label">Time Zone</label>
                        <div class="col-12 col-sm-7">
                            <select name="user_timezone" class="form-control select2" placeholder="Select Timezone">
                                @php
                                    $timezoneData = unserialize(getMyCaseTimezone()); //
                                @endphp
                                @forelse(array_flip($timezoneData) as $key=>$val)
                                    <option value="{{$key}}">{{$val}}</option>
                                @empty
                                @endforelse
                            </select>
                            @if ($errors->has('user_timezone'))
                              <span class="invalid-feedback" role="alert">
                                  <strong>{{ $errors->first('user_timezone') }}</strong>
                              </span>
                            @endif
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
                    <button id="activation-form-submit" type="submit" class="btn btn-primary">Activate Account</button>
                </div>
                </form>
        </div>
    </div>
</div>
@endsection
@section('page-js-script')
<script type="text/javascript">
  
$(document).ready(function () {
    $("#activation_form").validate({
        rules: {
            password: {
                required: true,
                maxlength: 20,
            },
            confirm_password: {
                required: true,
                maxlength: 20,
                equalTo: "#activation_form_password",
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
