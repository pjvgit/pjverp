@extends('layouts.beforelogin')
@section('title', config('app.name').' :: Login')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="p-4">
            <div class="auth-logo text-center mb-4">
                <img src="{{asset('assets/images/logo.png')}}" alt="">
            </div>
            <h1 class="mb-3 text-18">Welcome to the {{config('app.name')}}, {{$verifyUser->first_name}} </h1>
            @include('pages.messages')
           
            <form method="POST" id="password_update" action="{{ route('setupusersave') }}">
                @csrf
                <input type="hidden" name="utoken" value="{{$verifyUser->token}}">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label " for="inputEmail3">Set Password</label>
                    <div class="col-sm-9">
                        <input id="email" autofocus type="password"
                        class="form-control form-control-rounded {{ $errors->has('password') ? ' is-invalid' : '' }}"
                        name="password" value="{{ old('password') }}" required autocomplete="password" autofocus>
                        @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label " for="inputEmail3">Confirm Password</label>
                    <div class="col-sm-9">
                        <input id="email" autofocus type="password"
                        class="form-control form-control-rounded {{ $errors->has('confirm_password') ? ' is-invalid' : '' }}"
                        name="confirm_password" value="{{ old('confirm_password') }}" required autocomplete="confirm_password" autofocus>
                        @if ($errors->has('confirm_password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('confirm_password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label " for="inputEmail3">Timezone</label>
                    <div class="col-sm-9">
                        <select name="user_timezone" class="form-control select2 form-control-rounded"  placeholder="Select Timezone">
                            <?php 
                            $timezoneData = unserialize(TIME_ZONE_DATA); //

                            foreach(array_flip($timezoneData) as $key=>$val){?>
                            <option value="{{$key}}">{{$val}}</option>
                            <?php }?>
                          </select>
                          @if ($errors->has('user_timezone'))
                          <span class="invalid-feedback" role="alert">
                              <strong>{{ $errors->first('user_timezone') }}</strong>
                          </span>
                          @endif
                    </div>
                </div>
                
                <hr>
                <div class="form-group row float-right">
                        <button class="btn btn-rounded btn-primary btn-block">Start Your Free Trial</button>
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
    });
</script>
@stop
