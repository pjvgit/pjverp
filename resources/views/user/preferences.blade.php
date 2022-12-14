@extends('layouts.master')
@section('title', 'My Settings')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA);
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
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card1 mb-4">
                            <div class="card-body">
                                <h3>My Settings</h3>
                                <div class="card-title mb-3">Account Preferences
                                    <p class="privacy">Edit your account preferences. These preferences only affect your
                                        account. </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="card mb-4">
                            <form id="editPreferance" method="POST" action="{{ route('account/savePreferences') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="firm-default-preferences-form ">
                                    <div class="card-body">

                                        <div class="default-event-reminders-section row form-group">
                                            <label for="default-event-reminders" class="col-3 col-form-label">Default
                                                Event Reminders

                                            </label>
                                            <div class="form-control-plaintext col-9">
                                                <div>
                                                    <?php foreach($UserPreferanceEventReminder as $rkey=>$rval){ ?>
                                                    <div class="row form-group fieldGroup">
                                                        <div class="">
                                                            <div class="d-flex col-10 pl-0 align-items-center">
                                                                <div class="pl-0 col-4">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="event_reminder_type"
                                                                                name="event_reminder_type[]"
                                                                                class="reminder_type form-control custom-select  ">
                                                                                <option
                                                                                                <?php if($rval->reminder_type=="popup"){ echo "selected=selected"; } ?>
                                                                                                value="popup">popup
                                                                                            </option>
                                                                                            <option
                                                                                                <?php if($rval->reminder_type=="email"){ echo "selected=selected"; } ?>
                                                                                                value="email">email
                                                                                            </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div><input name="event_reminder_number[]"
                                                                    class="form-control col-2 reminder-number"
                                                                    value="{{$rval->reminer_number}}">
                                                                <div class="col-4">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="reminder_time_unit"
                                                                                name="event_reminder_time_unit[]"
                                                                                class="form-control custom-select  ">
                                                                                < <option
                                                                                <?php if($rval->reminder_frequncy=="minute"){ echo "selected=selected"; } ?>
                                                                                value="minute">minutes
                                                                            </option>
                                                                            <option
                                                                                <?php if($rval->reminder_frequncy=="hour"){ echo "selected=selected"; } ?>
                                                                                value="hour">hours
                                                                            </option>
                                                                            <option
                                                                                <?php if($rval->reminder_frequncy=="day"){ echo "selected=selected"; } ?>
                                                                                value="day">days
                                                                            </option>
                                                                            <option
                                                                                <?php if($rval->reminder_frequncy=="week"){ echo "selected=selected"; } ?>
                                                                                value="week">weeks
                                                                            </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-3">
                                                                    before event
                                                                </div>
                                                                    <a class="remove cursor-pointer">
                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                    </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php } ?>
                                                    <div class="fieldGroup"></div>
                                                    <div>                                                        <img src="{{BASE_URL}}public/svg/add-sign.svg">

                                                        <button type="button"
                                                            class="btn btn-link p-0 test-add-new-reminder add-more">Add
                                                            a reminder
                                                        </button>
                                                    </div>
                                                    <div class="fieldGroupCopy copy hide" style="display: none;">
                                                        <div class="">
                                                            <div class="d-flex col-10 pl-0 align-items-center">
                                                                <div class="pl-0 col-4">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="event_reminder_type"
                                                                                name="event_reminder_type[]"
                                                                                class="reminder_type form-control custom-select  ">
                                                                                <option value="popup">popup</option>
                                                                                <option value="email">email</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div><input name="event_reminder_number[]"
                                                                    class="form-control col-2 reminder-number"
                                                                    value="1">
                                                                <div class="col-4">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="reminder_time_unit"
                                                                                name="event_reminder_time_unit[]"
                                                                                class="form-control custom-select  ">
                                                                                <option value="minute">minutes</option>
                                                                                <option value="hour">hours</option>
                                                                                <option value="day">days</option>
                                                                                <option value="week">weeks</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-3">
                                                                    before event
                                                                </div>
                                                                <a class="remove cursor-pointer">
                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                    </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="default-sol-reminders-section row form-group">
                                            <label for="default-sol-reminders" class="col-3 col-form-label">Default
                                                Task Reminders
                                            </label>
                                            <div class="form-control-plaintext col-9">
                                                <div>
                                                    <?php foreach($UserPreferanceTaskReminder as $rkey=>$rval){ ?>
                                                    <div class="row form-group fieldGroup-2">
                                                        <div class="">
                                                            <div class="d-flex col-10 pl-0 align-items-center">
                                                                <div class="pl-0 col-4">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="reminder_type"
                                                                                name="task_reminder_type[]"
                                                                                class="reminder_type form-control custom-select  ">
                                                                                <option
                                                                                <?php if($rval->reminder_type=="popup"){ echo "selected=selected"; } ?>
                                                                                value="popup">popup
                                                                            </option>
                                                                            <option
                                                                                <?php if($rval->reminder_type=="email"){ echo "selected=selected"; } ?>
                                                                                value="email">email
                                                                            </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <input name="task_reminder_number[]"
                                                                    class="form-control col-2 reminder-number"
                                                                    value="{{$rval->reminer_number}}">
                                                                <div class="col-4">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="reminder_time_unit"
                                                                                name="task_reminder_time_unit[]"
                                                                                class="form-control custom-select  ">
                                                                                <option
                                                                                                <?php if($rval->reminder_frequncy=="minute"){ echo "selected=selected"; } ?>
                                                                                                value="minute">minutes
                                                                                            </option>
                                                                                            <option
                                                                                                <?php if($rval->reminder_frequncy=="hour"){ echo "selected=selected"; } ?>
                                                                                                value="hour">hours
                                                                                            </option>
                                                                                            <option
                                                                                                <?php if($rval->reminder_frequncy=="day"){ echo "selected=selected"; } ?>
                                                                                                value="day">days
                                                                                            </option>
                                                                                            <option
                                                                                                <?php if($rval->reminder_frequncy=="week"){ echo "selected=selected"; } ?>
                                                                                                value="week">weeks
                                                                                            </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-3 nowrap">
                                                                    before due date
                                                                </div>
                                                                <a class="remove-2 cursor-pointer ml-2">
                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                    </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php } ?>
                                                    <div class="fieldGroup-2"></div>
                                                    <div>
                                                        <img src="{{BASE_URL}}public/svg/add-sign.svg">
                                                        <button type="button"
                                                            class="btn btn-link p-0 test-add-new-reminder add-more-2">Add
                                                            a reminder
                                                        </button>
                                                    </div>
                                                    <em style="padding-top: 5px;">
                                                        Choose 0 days to be reminded on the task due date.
                                                    </em>
                                                    <div class="fieldGroupCopy-2 copy hide" style="display: none;">
                                                        <div class="">
                                                            <div class="d-flex col-10 pl-0 align-items-center">
                                                                <div class="pl-0 col-4">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="reminder_type"
                                                                                name="task_reminder_type[]"
                                                                                class="reminder_type form-control custom-select  ">
                                                                                <option value="popup">popup</option>
                                                                                <option value="email">email</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <input name="task_reminder_number[]"
                                                                    class="form-control col-2 reminder-number"
                                                                    value="1">
                                                                <div class="col-4">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="reminder_time_unit"
                                                                                name="task_reminder_time_unit[]"
                                                                                class="form-control custom-select  ">
                                                                                <option value="minute">minutes</option>
                                                                                <option value="hour">hours</option>
                                                                                <option value="day">days</option>
                                                                                <option value="week">weeks</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-3 nowrap">
                                                                    before due date
                                                                </div>
                                                                <a class="remove-2 cursor-pointer ml-2">
                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                    </a>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="default-sol-reminders-section row form-group">
                                            <label for="default-sol-reminders" class="col-3 col-form-label">Time Zone
                                            </label>
                                            <div class="form-control-plaintext col-9">
                                                <select class="form-control timeZone" id="timeZone" name="timeZone"
                                                    data-placeholder="Select User Type">
                                                    <?php  foreach($timezoneData as $k=>$v){?>
                                                    <option
                                                        <?php if($v==Auth::User()->user_timezone){ echo "Selected=selected";} ?>
                                                        value="{{$v}}">{{$k}}</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-3 col-form-label">
                                                <label>Getting Started Tips</label>
                                            </div>
                                            <div class="col">
                                                <div class="form-control-plaintext">
                                                    <input type="radio" name="tooltips" id="tooltips_on"
                                                        <?php if($user->started_tips=="on"){ echo "checked=checked"; } ?>
                                                        value="on">
                                                    Turn all tips <strong>on</strong>
                                                </div>
                                                <div class="form-control-plaintext">
                                                    <input type="radio" name="tooltips" id="tooltips_off"
                                                        <?php if($user->started_tips=="off"){ echo "checked=checked"; } ?>
                                                        value="off">
                                                    Turn all tips <strong>off</strong></div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-3 col-form-label">
                                                <label>Automatic Logout</label>
                                            </div>
                                            <div class="col">
                                                <div class="mt-1">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="auto_logout_enabled"
                                                                id="auto_logout_enabled_true" value="true"
                                                                class="form-check-input"
                                                                <?php if($user->auto_logout=="on"){ echo "checked=checked"; } ?>">
                                                            Enabled
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="auto_logout_enabled"
                                                                id="auto_logout_enabled_false" value="false"
                                                                <?php if($user->auto_logout=="off"){ echo "checked=checked"; } ?>
                                                                class="form-check-input">
                                                            Disabled
                                                        </label>
                                                    </div>

                                                </div>

                                                <div id="auto-logout-options" class="mt-3 ">
                                                    <div class="form-group">
                                                        <label for="logout_minutes">Automatically log me out
                                                            after</label>
                                                        <select name="logout_minutes" id="logout_minutes"
                                                            class="custom-select">
                                                            <option
                                                                <?php if($user->sessionTime=="10"){ echo "Selected=selected";} ?>
                                                                value="10">10 minutes of inactivity</option>
                                                            <option
                                                                <?php if($user->sessionTime=="30"){ echo "Selected=selected";} ?>value="30">
                                                                30 minutes of inactivity</option>
                                                            <option
                                                                <?php if($user->sessionTime=="60"){ echo "Selected=selected";} ?>
                                                                value="60">60 minutes of
                                                                inactivity</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="timer_running_logout">
                                                            <input type="checkbox" name="timer_running_logout"
                                                                id="timer_running_logout" value="1"
                                                                <?php if($user->dont_logout_while_timer_runnig=="on"){echo "checked=checked" ; } ?>>
                                                            Do not log me out if I have a timer running.
                                                        </label>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="d-flex flex-row justify-content-end">
                                            <button type="submit" class="btn btn-outline-secondary btn-rounded m-1">
                                                Save Preferences</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>


                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Two-Factor Authentication</h4>

                        <div id="tfa_table">
                            <div class="bootstrap" style="max-width: 1000px;">
                                <div class="bootstrap">
                                    <p>Increase the security of your MyCase account by enabling <a target="_blank"
                                            rel="noopener noreferrer"
                                            href="https://support.google.com/accounts/answer/1066447?hl=en">Two-Factor
                                            Authentication.</a></p>
                                    <p>To get started, click the "Enable Two-Factor Authentication" button, then follow
                                        the on-screen instructions.</p>
                                    <p>Please Note: You will need a smartphone to enable this feature.</p>
                                    <p><button type="button" class="btn btn-outline-secondary">Enable Two-Factor
                                            Authentication</button></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('page-js-inner')
    <script type="text/javascript">
        $(document).ready(function () {
            $(".add-more").click(function () {
                var fieldHTML = '<div class="row form-group fieldGroup">' + $(".fieldGroupCopy")
                    .html() + '</div>';
                $('body').find('.fieldGroup:last').before(fieldHTML);
                $('body').find('#reminder_user_type:last').attr("ownid", $(".fieldGroup").length);
                $('body').find('#reminder_user_type:last').attr("id", $(".fieldGroup").length);
                $('body').find('#reminder_type:last').attr("id", "reminder_type_" + $(".fieldGroup")
                    .length);
            });
            $('#editPreferance').on('click', '.remove', function () {
                var $row = $(this).parents('.fieldGroup').remove();
            });
            $(".add-more-2").click(function () {
                var fieldHTML = '<div class="row form-group fieldGroup-2">' + $(".fieldGroupCopy-2")
                    .html() + '</div>';
                $('body').find('.fieldGroup-2:last').before(fieldHTML);
                $('body').find('#reminder_user_type:last').attr("ownid", $(".fieldGroup-2").length);
                $('body').find('#reminder_user_type:last').attr("id", $(".fieldGroup-2").length);
                $('body').find('#reminder_type:last').attr("id", "reminder_type_" + $(".fieldGroup-2")
                    .length);
            });
            $('#editPreferance').on('click', '.remove-2', function () {
                var $row = $(this).parents('.fieldGroup-2').remove();
            });
            $(".timeZone").select2({
                theme: "classic",
                allowClear: true
            });

            $('input[type=radio][name=auto_logout_enabled]').change(function () {
                if (this.value == 'true') {
                    $("#auto-logout-options").show();
                } else {
                    $("#auto-logout-options").hide();
                }
            }); 
            <?php if($user->auto_logout == "off") { ?>
                $("#auto-logout-options").hide(); 
            <?php } ?>
        });

    </script>
    @stop
    @endsection
