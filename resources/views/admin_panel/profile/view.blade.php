@extends('admin_panel.layouts.master')
@section('page-title', 'Profile')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/css/select2.min.css')}}">
@endsection

@section('main-content')
<div class="breadcrumb justify-content-between align-items-center">
    <h1>Profile</h1>
    <ul class="m2">
        <li><a href="">Dashboard</a></li>
        <li>Version 2</li>
    </ul>    
</div>
<div class="separator-breadcrumb border-top"></div>
@if ($errors->any())
<div class="alert alert-danger">
<strong>Whoops!</strong> There were some problems with your input.
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
</button>
<br><br>
<ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
</ul>
</div>
@endif
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h3>
                            My Profile
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card mb-4">
                    <div class="card-body">
                        <form id="basic_info" method="POST" action="{{ route('admin/saveProfile') }}">
                            @csrf
                            <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
                                <div class="col-sm-5">
                                    <input class="form-control"
                                        value="{{ $userProfile->first_name ?? old('first_name') }}" id="first_name"
                                        name="first_name" type="text" placeholder="Enter your first name">
                                </div>
                                <div class="col-sm-5">
                                    <input class="form-control" id="last_name"
                                        value="{{ $userProfile->last_name ?? old('last_name') }}" name="last_name"
                                        type="text" placeholder="Enter your last name">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">Email address</label>
                                <div class="col-md-10 form-group mb-3">
                                    <input class="form-control " id="email" name="email"
                                        value="{{ $userProfile->email ?? old('email') }}" type="email"
                                        placeholder="Enter email address">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">Timezone</label>
                                <div class="col-md-10 form-group mb-3">
                                <select class="form-control select2" id="timezone" name="timezone"
                                    data-placeholder="Select User Timezone">
                                    <?php 
                                    $timezoneData = unserialize(TIME_ZONE_DATA); //
                                    foreach($timezoneData as $k=>$v){?>
                                    <option <?php echo ($v==Auth::User()->timezone) ? "selected" : ""; ?> value="{{$v}}">{{$k}}</option>
                                    <?php } ?>
                                </select>
                                </div>
                            </div>
                            <div class="form-group row float-right">
                                <button class="btn btn-primary ladda-button example-button m-1"
                                    data-style="expand-right">
                                    <span class="ladda-label">Update Info</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <hr>
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="card-title mb-3">Change Password</div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card mb-4">
                    <div class="card-body">
                        <form id="password_info" method="POST" action="{{ route('admin/savePassword') }}"
                            autocomplete="off">
                            @csrf
                            <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">Current
                                    Password</label>
                                <div class="col-md-10 form-group mb-3">
                                    <input class="form-control " id="password" autocomplete="new-password"
                                        name="current_password" placeholder="Enter Current Password">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">New Password </label>
                                <div class="col-md-10 form-group mb-3">
                                    <input class="form-control " id="new_password" autocomplete="new-password"
                                        name="new_password" placeholder="Enter New Password">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">Confirm Password
                                </label>
                                <div class="col-md-10 form-group mb-3">
                                    <input class="form-control " id="confirm_password"
                                        autocomplete="new-password" name="confirm_password"
                                        placeholder="Enter Confirm Password">
                                </div>
                            </div>
                            <div class="form-group row float-right">
                                <button class="btn btn-primary ladda-button example-button m-1"
                                    data-style="expand-right">
                                    <span class="ladda-label">Update Password</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page-js')
<script src="{{asset('assets/js/select2.min.js')}}"></script>
<script>
    $("#timezone").select2({
        placeholder: "Select...",
        theme: "classic",
        allowClear: true
    });
</script>
@endsection