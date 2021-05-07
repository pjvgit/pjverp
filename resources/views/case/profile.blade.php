@extends('layouts.master')
@section('title', config('app.name').' :: Profile')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); //
?>
<div class="breadcrumb">
    <h3>Settings & Preferences</h1>

</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')

    </div>
    <div class="col-md-10">
        <div class="card mb-4 o-hidden">
            @include('pages.errors')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card mb-4">

                            <div class="card-body">
                                <h3>
                                    My Profile
                                </h3>
                                <div class="card-title mb-3">Contact Information <p class="privacy">This is where you
                                        update
                                        your
                                        contact information. It will be available to the attorneys in your firm (but
                                        nothing
                                        other than
                                        your name is visible to clients). </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="card mb-4">
                            <div class="card-body">
                                <form id="basic_info" method="POST" action="{{ route('users.saveBasicInfo') }}">
                                    @csrf
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
                                        <div class="col-sm-3">
                                            <input class="form-control"
                                                value="{{ $user->first_name ?? old('first_name') }}" id="first_name"
                                                name="first_name" type="text" placeholder="Enter your first name">
                                        </div>
                                        <div class="col-sm-3">
                                            <input class="form-control"
                                                value="{{ $user->middle_name ?? old('middle_name') }}" id="middle_name"
                                                name="middle_name" type="text" placeholder="Enter your middle name">
                                        </div>
                                        <div class="col-sm-3">
                                            <input class="form-control" id="last_name"
                                                value="{{ $user->last_name ?? old('last_name') }}" name="last_name"
                                                type="text" placeholder="Enter your last name">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-2 col-form-label">Address</label>
                                        <div class="col-md-10 form-group mb-3">
                                            <input class="form-control" id="street" name="street"
                                                value="{{ $user->street ?? old('street') }}" type="text"
                                                placeholder="Enter street">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                                        <div class="col-md-10 form-group mb-3">
                                            <input class="form-control" id="apt_unit"
                                                value="{{ $user->apt_unit ?? old('apt_unit') }}" name="apt_unit"
                                                type="text" placeholder="Enter apt/unit">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                                        <div class="col-md-4 form-group mb-3">
                                            <input class="form-control" id="city" name="city"
                                                value="{{ $user->city ?? old('city') }}" placeholder="Enter city">
                                        </div>
                                        <div class="col-md-3 form-group mb-3">
                                            <input class="form-control" id="state" name="state"
                                                value="{{ $user->state ?? old('state') }}" placeholder="Enter state">
                                        </div>
                                        <div class="col-md-3 form-group mb-3">
                                            <input class="form-control" id="postal_code"
                                                value="{{ $user->postal_code ?? old('postal_code') }}"
                                                name="postal_code" placeholder="Enter postal code">
                                        </div>

                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                                        <div class="col-md-10 form-group mb-3">
                                            <select id="country" name="country" class="country form-control">
                                                <option value="">Select country</option>
                                                <?php
                                                foreach($country as $key=>$val){?>
                                                <option value="{{$val->id}}"
                                                    <?php if($val->id == $user->country){ echo "selected='selected'"; } ?>>
                                                    {{$val->name}}</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-2 col-form-label">Home Phone</label>
                                        <div class="col-md-4 form-group mb-3">
                                            <input class="form-control" id="home_phone"
                                                value="{{ $user->home_phone ?? old('home_phone') }}" name="home_phone"
                                                placeholder="Enter home phone">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-2 col-form-label">Work Phone</label>
                                        <div class="col-md-4 form-group mb-3">
                                            <input class="form-control" id="home_phone"
                                                value="{{ $user->home_phone ?? old('home_phone') }}" name="home_phone"
                                                placeholder="Enter home phone">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-2 col-form-label">Cell Phone</label>
                                        <div class="col-md-4 form-group mb-3">
                                            <input class="form-control" id="mobile_number"
                                                value="{{ $user->mobile_number ?? old('mobile_number') }}"
                                                name="cell_phone" placeholder="Enter cell phone">
                                        </div>
                                    </div>
                                    <div class="form-group row float-right">
                                        <a href="{{ route('dashboard') }}">
                                            <button class="btn btn-outline-dark m-1" type="button">Cancel</button>
                                        </a>
                                        <button class="btn btn-primary ladda-button example-button m-1"
                                            data-style="expand-right">
                                            <span class="ladda-label">Save Info</span>
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
                                <div class="card-title mb-3">Change Email <p class="privacy">This will change the email
                                        address that you
                                        use when logging in to {{config('app.name')}}, as well as the email address
                                        listed on your
                                        contact page.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="card mb-4">
                            <div class="card-body">
                                <form id="email_info" method="POST" action="{{ route('users.saveEmail') }}"
                                    autocomplete="off">
                                    @csrf
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-2 col-form-label">Email address</label>
                                        <div class="col-md-10 form-group mb-3">
                                            <input class="form-control " id="email" name="email"
                                                value="{{ $user->email ?? old('email') }}" type="email"
                                                placeholder="Enter email address">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-2 col-form-label">Current
                                            Password</label>
                                        <div class="col-md-10 form-group mb-3">
                                            <input class="form-control " type="password" value=""
                                                autocomplete="new-password" id="current_password"
                                                name="current_password" placeholder="Enter current password">
                                        </div>
                                    </div>
                                    <div class="form-group row float-right">
                                        <button class="btn btn-primary ladda-button example-button m-1"
                                            data-style="expand-right">
                                            <span class="ladda-label">Update Email</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

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
                                <form id="password_info" method="POST" action="{{ route('users.savePassword') }}"
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

                <hr>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="card-title mb-3">Profile Picture <p class="privacy">Your profile picture is
                                        displayed
                                        alongside any comments or messages you post in {{config('app.name')}} including the client
                                        portal. </p>
                                    @if(!file_exists(
                                    public_path().'/images/users/'.Auth::user()->profile_image
                                    ))
                                    <p class="privacy"> You're currently using the default picture. </p>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="card mb-4">
                            <div class="card-body">
                                <form id="profile_image" method="POST" action="{{ route('users.saveProfileimage') }}"
                                    autocomplete="off" enctype="multipart/form-data">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-8 form-group mb-3">
                                            <div class="col-md-12 form-group mb-3">
                                                <div class="custom-file">
                                                    <label for="inputEmail3" class="col-form-label">Upload a new image
                                                        to {{config('app.name')}}:</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12 form-group mb-3">
                                                <div class="custom-file">
                                                    <input class="custom-file-input" id="inputGroupFile02"
                                                        name="profile_image" type="file">
                                                    <label class="custom-file-label" for="inputGroupFile02"
                                                        aria-describedby="inputGroupFileAddon02">Choose file</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12 form-group mb-3">
                                                <div class="custom-file">
                                                    <button class="btn btn-primary ladda-button example-button m-1"
                                                        data-style="expand-right">
                                                        <span class="ladda-label">Upload Picture</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <div class="col-md-12 form-group mb-3 float-right">
                                                @if(file_exists(
                                                public_path().'/images/users/'.Auth::user()->profile_image
                                                ))
                                                <img class="border border-dark" src="{{URL::asset('/public/images/users/')}}/{{Auth::user()->profile_image}}"
                                                    id="userDropdown" alt="" data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                @else

                                                <img class="border border-dark" style="max-width: 150px;"
                                                    src="{{asset('assets/images/faces/default_face.svg')}}">
                                                @endif
                                            </div>


                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('page-js-script')
    <script type="text/javascript">
        $(document).ready(function () {
            $('.country').select2();
        });

    </script>
    @stop
    @endsection
