@extends('client_portal.layouts.master')

@section('main-content')
	
<div class="app-container__content">
    <section class="settings" id="settings_view">
        <ul class="nav nav-tabs nav-justified text-center" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() == "client/account") {{ "active" }} @endif" id="profile-basic-tab" href="{{ route('client/account') }}" role="tab" aria-controls="profileBasic" aria-selected="false">My Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() == "client/account/preferences") {{ "active" }} @endif" id="preference-basic-tab" href="{{ route('client/account/preferences') }}" role="tab" aria-controls="preferenceBasic" aria-selected="true">My Preferences</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade @if(Route::currentRouteName() == "client/account") {{ "show active" }} @endif" id="profileBasic" role="tabpanel" aria-labelledby="profile-basic-tab">
                <h1 class="primary-heading">Contact Info</h1>
                <form class="p-3" action="{{ route('client/account/save') }}" method="POST" id="profile_form">
                    @csrf
                    <input type="hidden" name="id" value="{{ $user->id }}">
                    <div class="u-font-small d-flex flex-wrap">
                        <div class="col-md-3 form-group mb-3">
                            <label for="first_name">First Name*</label>
                            <input class="form-control" id="first_name" name="user[first_name]" type="text" value="{{ $user->first_name }}">
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="middle_name">Middle</label>
                            <input class="form-control" id="middle_name" name="user[middle_name]" type="text" value="{{ $user->middle_name }}">
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label for="last_name">Last Name*</label>
                            <input class="form-control" id="last_name" name="user[last_name]" type="text" value="{{ $user->last_name }}">
                        </div>
                    </div>
                    <div class="u-font-small d-flex flex-wrap">
                        <div class="col-md-4 form-group mb-3">
                            <label class="form-input__label" for="street">Street Address</label>
                            <input class="form-control" id="street" name="user[street]" type="text" value="{{ $user->street }}">
                        </div>
                        <div class="col-md-1 form-group mb-3">
                            <label class="form-input__label" for="apt_unit">Apt/Unit</label>
                            <input class="form-control" id="apt_unit" name="user[apt_unit]" type="text" value="{{ $user->apt_unit }}">
                        </div>
                    </div>
                    <div class="u-font-small d-flex flex-wrap">
                        <div class="col-md-2 form-group mb-3">
                            <label class="form-input__label" for="city">City</label>
                            <input class="form-control" id="city" name="user[city]" type="text" value="{{ $user->city }}">
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label class="form-input__label" for="state">State</label>
                            <input class="form-control" id="state" name="user[state]" type="text" value="{{ $user->state }}">
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label class="form-input__label" for="postal_code">Zip</label>
                            <input class="form-control" id="postal_code" name="user[postal_code]" type="text" value="{{ $user->postal_code }}">
                        </div>
                    </div>
                    <div class="settings__input u-font-small">
                        <div class="col-md-3 form-group mb-3">
                            <label class="form-input__label" for="home_phone">Home Phone</label>
                            <input id="home_phone" name="user[home_phone]" class="form-control" value="{{ $user->home_phone }}">
                        </div>
                    </div>
                    <div class="settings__input u-font-small">
                        <div class="col-md-3 form-group mb-3">
                            <label class="form-input__label" for="work_phone">Work Phone</label>
                            <input id="work_phone" name="user[work_phone]" class="form-control" value="{{ $user->work_phone }}">
                        </div>
                    </div>
                    <div class="settings__input u-font-small">
                        <div class="col-md-3 form-group mb-3">
                            <label class="form-input__label" for="mobile_number">Cell Phone</label>
                            <input id="mobile_number" name="user[mobile_number]" class="form-control" value="{{ $user->mobile_number }}">
                        </div>
                    </div>
                    <input type="submit" class="btn btn-primary settings__submit" value="Save Changes">
                </form>
                <h1 class="primary-heading">Change Email</h1>
                <form class="detail-view__background p-3" data-action="{{ route('client/change/email') }}" method="POST" id="change_email_form">
                    <div class="col-md-4 form-group mb-3">
                        <label class="form-input__label" for="current">Current Email</label>
                        <label id="current_email">{{ $user->email }}</label>
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <div class="form-input is-required">
                            <label class="form-input__label" for="email">New Email</label>
                            <input id="email" name="new_email" required="" type="email" class="form-control">
                            <span class="error new_email_error"></span>
                        </div>
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <div class="form-input is-required">
                            <label class="form-input__label" for="password">Current Password</label>
                            <input id="password" name="old_password" required="" type="password" class="form-control">
                            <span class="error old_password_error"></span>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Update Email">
                </form>
                <h1 class="primary-heading">Change Password</h1>
                <form class="p-3" data-action="{{ route("client/change/password") }}" method="POST" id="chnage_password_form">
                    @csrf
                    <div class="settings__input u-font-small">
                        <div class="col-md-4 form-group mb-3">
                            <label class="form-input__label" for="current_password">Current Password</label>
                            <input id="current_password" name="current_password" type="password" class="form-control">
                            <span class="error current_password_error"></span>
                        </div>
                    </div>
                    <div class="settings__input u-font-small">
                        <div class="col-md-4 form-group mb-3">
                            <label class="form-input__label" for="new_password">New Password</label>
                            <input id="new_password" name="password" type="password" class="form-control">
                            <span class="error password_error"></span>
                        </div>
                    </div>
                    <div class="settings__input u-font-small">
                        <div class="col-md-4 form-group mb-3">
                            <label class="form-input__label" for="password_confirmation">Confirm Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control">
                            <span class="error password_confirmation_error"></span>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Update Password">
                </form>
            </div>
            <div class="tab-pane fade @if(Route::currentRouteName() == "client/account/preferences") {{ "show active" }} @endif" id="preferenceBasic" role="tabpanel" aria-labelledby="preference-basic-tab">
                <form class="p-3" action="{{ route('client/account/save/preferences') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $user->id }}">
                    <div class="col-md-4 form-group mb-3">
                        <label class="form-input__label" for="time_zone">Time Zone</label>
                        <select name="user_timezone" class="form-control select2" placeholder="Select Timezone">
                            @php
                                $timezoneData = unserialize(getTimezoneList()); //
                            @endphp
                            @forelse(array_flip($timezoneData) as $key=>$val)
                                <option value="{{$key}}" {{ ($user->user_timezone == $key) ? 'selected' : '' }}>{{$val}}</option>
                            @empty
                            @endforelse
                        </select>
                        @if ($errors->has('preferences.time_zone'))
                          <span class="invalid-feedback" role="alert">
                              <strong>{{ $errors->first('preferences.time_zone') }}</strong>
                          </span>
                        @endif
                    </div>
                    <div class="mt-4 mb-2">
                        <h1 class="settings__title">Automatic Logout</h1>
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type='hidden' value='off' name='auto_logout'>
                                <input class="form-check-input mr-2" name="auto_logout" type="checkbox" {{ ($user->auto_logout == 'on') ? 'checked' : '' }}>Automatically log me out after a period of inactivity</label>
                        </div>
                        <div class="col-md-4 form-group mt-3" id="logout_after_div" style="display: {{ ($user->auto_logout == 'on') ? 'block' : 'none' }};">
                            <label class="form-input__label" for="logout_minutes">Log me out after</label>
                            <select class="form-control" id="logout_minutes" name="sessionTime">
                                <option value="10" {{ ($user->sessionTime == 10) ? 'selected' : '' }}>10 minutes of inactivity</option>
                                <option value="30" {{ ($user->sessionTime == 30) ? 'selected' : '' }}>30 minutes of inactivity</option>
                                <option value="60" {{ ($user->sessionTime == 60) ? 'selected' : '' }}>60 minutes of inactivity</option>
                            </select>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Save Preferences">
                </form>
            </div>
        </div>
    </section>
    <div></div>
</div>

@endsection

@section('page-js')
<script src="{{ asset('assets\client_portal\js\profile\editprofile.js') }}"></script>
@endsection