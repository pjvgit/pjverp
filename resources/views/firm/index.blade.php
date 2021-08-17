@extends('layouts.master')
@section('title', 'Firm Setting :: Settings')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 
?>

<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')
    </div>
    <div class="col-md-10">
        <div class="breadcrumb">
            <h3>Firm Settings </h3>

        </div>
        <div class="alert alert-info p-3" role="alert">
            You are an Admin User. No one else can view/edit this page without your permission.
        </div>
        <div class="card mb-4 o-hidden">

            <div class="card-body">
                <div class="row">
                    <div class="col-3 pr-5">
                        <h6>Contact Information</h6>
                        <p>Update your firm's contact information.</p>
                    </div>
                    <div class="col-md-9">
                        <div class="card mb-4">
                            <div class="card-body">

                                <?php if(session('page')=="infopage"){
                                    ?>
                                @include('pages.errors')
                                <?php 
                                } ?>
                                <form id="basic_info" method="POST" action="{{ route('firms/updateFirm') }}">
                                    @csrf
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-2 col-form-label">Firm Name</label>
                                        <div class="col-sm-10">
                                            <input class="form-control"
                                                value="{{ $firmData->firm_name ?? old('firm_name') }}" id="firm_name"
                                                maxlength="255" name="firm_name" type="text" placeholder="">
                                        </div>
                                    </div>
                                    <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime"
                                        style="display: none;">
                                    </div>
                                    <div class="form-group row float-right">

                                        <button class="btn btn-primary mr-3 submit" type="submit" id="saveButton"
                                            onclick="submitForm();">
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
                    <div class="col-3 pr-5">
                        <h6>Billing plan</h6>
                        <p>Choose monthly or annual billing. <br>View our <a href="#">Billing FAQ</a> for more
                            information on billing plans.
                            <br><br>
                            Sales tax may apply. Learn more about sales tax <a href="#">here</a>.

                        </p>
                    </div>
                    <div class="col-md-9">
                        <div class="card mb-4">
                            <div class="card-body">
                                <?php if(session('page')=="email"){
                                    ?>
                                @include('pages.errors')
                                <?php 
                                } ?>
                                <div class="alert alert-info fade show" role="alert">
                                    <div class="d-flex align-items-start">
                                        <div class="w-100"><span>Unable to load your billing plan. If you already have a
                                                subscription, please try again in a few minutes or</span><button
                                                type="button" class="ml-2 p-0 border-0 btn btn-link">subscribe
                                                now.</button></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="row">
                    <div class="col-3 pr-5">
                        <h6>Offices</h6>
                        <p>Manage your firm's locations.</p>
                    </div>
                    <div class="col-md-9">

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <?php  foreach($firmAddress as $k=>$v){?>
                                    <div class="col-lg-4 col-md-6 col-sm-6">
                                        <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                                            <div class="card office-204822">
                                                <div class="card-header office-name">
                                                    <?php if($v->is_primary=="yes"){ echo $v->office_name;} ?>
                                                    <span class="text-muted font-italic primary-label">
                                                        <?php if($v->is_primary=="yes"){ echo "(primary)";}else{ echo $v->office_name;} ?></span>
                                                    <div class="float-right">
                                                        <a data-toggle="modal" data-target="#editOffice"
                                                            data-placement="bottom" href="javascript:;"
                                                            onclick="editOffice({{$v->id}});">
                                                            <i class="fas fa-pen align-middle mr-2"></i>
                                                        </a>

                                                        <?php if($v->is_primary=="no"){?>
                                                        <a data-toggle="modal" data-target="#deleteOffice"
                                                            data-placement="bottom" href="javascript:;"
                                                            onclick="deleteOffice({{$v->id}});">
                                                            <i class="fas fa-trash align-middle mr-2"></i>
                                                        </a>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <div class="card-body p-3 mb-2">
                                                    <dl>
                                                        <dt>Main Phone</dt>
                                                        <dd class="main-phone">{{$v->main_phone}}
                                                        </dd>
                                                        <dt>Fax Line</dt>
                                                        <dd class="fax-line">{{$v->fax_line}}
                                                        </dd>
                                                        <dt>Address</dt>
                                                        <dd>
                                                            {{$v->address}}<br>
                                                            {{$v->apt_unit}}<br>
                                                            {{$v->city}}
                                                            ,
                                                            {{$v->state}}
                                                            {{$v->post_code}}
                                                            <br>
                                                            {{$v->countryname}}
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="ml-auto d-flex align-items-center d-print-none float-right">
                                    <a data-toggle="modal" data-target="#addNewOffice" data-placement="bottom"
                                        href="javascript:;">
                                        <button class="btn btn-outline-secondary btn-rounded m-1" type="button"
                                            onclick="addNewOffice();">Add New Office
                                        </button>
                                    </a>
                                </div>
                                <?php if(session('page')=="password"){
                                    ?>
                                @include('pages.errors')
                                <?php 
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-3 pr-5">
                        <h6>My Payment Information</h6>
                        <p class="mb-4">Update your firm's payment information.</p>
                        <strong>Have a question about billing?</strong>
                        <p>
                            Contact us by email at
                            <a href="mailto:support@legalcase.com">support@legalcase.com</a>,
                            or by phone at <strong>XXXXXXXXXXXX</strong>.
                        </p>
                    </div>
                    <div class="col-md-9">
                        <div class="card mb-4">
                            <div class="card-body">
                                <?php if(session('page')=="image"){
                                    ?>
                                @include('pages.errors')
                                <?php 
                                } ?>
                                <div class="payment-method">
                                    <div class="col-12 p-0">
                                        <div class="mb-2 card border-info">
                                            <div class="py-1 card-body">MyCase does not have any payment information on
                                                file for your account.<br>Please enter your payment information to
                                                prevent any interruption in your service.</div>
                                        </div>
                                        <div class="text-right"><button class="btn btn-outline-secondary btn-rounded"
                                                type="button">Edit Payment Information</button></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-3 pr-5">
                        <h6>Firm Preferences</h6>
                        <p>Update the preferences for your firm.</p>
                    </div>
                    <div class="col-md-9">
                        <div class="card mb-4">

                            <?php if(session('page')=="image"){
                                    ?>
                            @include('pages.errors')
                            <?php 
                                } ?>
                            <form id="editPreferance" method="POST" action="{{ route('firms/editPreferance') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="firm-default-preferences-form card">
                                    <div class="card-body">
                                        <div class="client-portal-access-section row form-group">
                                            <label for="client-portal-access" class="col-3 col-form-label"><b>Default
                                                    Client
                                                    Portal Access</b></label>
                                            <div class="form-control-plaintext col-9">
                                                <input id="client-portal-access" type="checkbox"
                                                    <?php if($firmData->client_portal_access=="yes"){ echo "checked=checked";} ?>
                                                    name="client_portal_access" class="form-check-input">Enabled<span
                                                    class="form-text text-black">This
                                                    is the default setting for Client Portal Access when adding a case
                                                    or
                                                    contact.</span>
                                            </div>
                                        </div>
                                        <div class="default-event-reminders-section row form-group"><label
                                                for="default-event-reminders" class="col-3 col-form-label"><b>Default
                                                    Client/Lead<br>Event Reminders</b></label>
                                            <div class="form-control-plaintext col-9">
                                                <div>
                                                    <div class="form-group row">
                                                        <div class="col">
                                                            <div>
                                                                <?php
                                                                foreach($FirmEventReminder as $rkey=>$rval){
                                                                ?>
                                                                <div class="row form-group fieldGroup">
                                                                    <div class="">
                                                                        <div
                                                                            class="d-flex col-10 pl-0 align-items-center">
                                                                            <div class="pl-0 col-3">
                                                                                <div>
                                                                                    <div class="">
                                                                                        <select id="reminder_user_type"
                                                                                            name="reminder_user_type[]"
                                                                                            class="form-control custom-select  ">
                                                                                            <option
                                                                                                <?php if($rval->reminder_user_type=="me"){ echo "selected=selected"; } ?>
                                                                                                value="me">Clients/Leads
                                                                                            </option>

                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="pl-0 col-3">
                                                                                <div>
                                                                                    <div class="">
                                                                                        <select id="reminder_type"
                                                                                            name="reminder_type[]"
                                                                                            class="form-control custom-select valid"
                                                                                            aria-invalid="false">
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
                                                                            </div><input name="reminder_number[]"
                                                                                class="form-control col-2 reminder-number"
                                                                                value="{{$rval->reminer_number}}">
                                                                            <div class="col-4">
                                                                                <div>
                                                                                    <div class="">
                                                                                        <select id="reminder_time_unit"
                                                                                            name="reminder_time_unit[]"
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
                                                                            <button class="btn remove" type="button">
                                                                                <i class="fa fa-trash"
                                                                                    aria-hidden="true"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php } ?>
                                                                <div class="fieldGroup">
                                                                </div>
                                                                <div>
                                                                    <button type="button"
                                                                        class="btn btn-link p-0 test-add-new-reminder add-more">Add
                                                                        a reminder</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="fieldGroupCopy copy hide" style="display: none;">
                                                        <div class="">
                                                            <div class="d-flex col-10 pl-0 align-items-center">
                                                                <div class="pl-0 col-3">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="reminder_user_type"
                                                                                name="reminder_user_type[]"
                                                                                class="reminder_user_type form-control custom-select  ">
                                                                                <option value="me">Clients/Leads
                                                                                </option>

                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="pl-0 col-3">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="reminder_type"
                                                                                name="reminder_type[]"
                                                                                class="reminder_type form-control custom-select  ">
                                                                                <option value="popup">popup</option>
                                                                                <option value="email">email</option>
                                                                                <option value="text-sms">Text(SMS)
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div><input name="reminder_number[]"
                                                                    class="form-control col-2 reminder-number"
                                                                    value="1">
                                                                <div class="col-4">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="reminder_time_unit"
                                                                                name="reminder_time_unit[]"
                                                                                class="form-control custom-select  ">
                                                                                <option value="minute">minutes</option>
                                                                                <option value="hour">hours</option>
                                                                                <option value="day">days</option>
                                                                                <option value="week">weeks</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <button class="btn remove" type="button">
                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="statute-of-limitations-section row form-group"><label
                                                for="statute-of-limitations" class="col-3 col-form-label"><b>Statute of
                                                    Limitations</b></label>
                                            <div class="form-control-plaintext col-9"><input id="statute-of-limitations"
                                                    type="checkbox" class="form-check-input"
                                                    <?php if($firmData->sol=="yes"){ echo "checked=checked";} ?>
                                                    name="statute_of_limitations">Enabled</div>
                                        </div>
                                        <div class="default-sol-reminders-section row form-group"><label
                                                for="default-sol-reminders" class="col-3 col-form-label"><b>Default
                                                    Statute
                                                    of Limitations Reminders</b></label>
                                            <div class="form-control-plaintext col-9">
                                                <div>
                                                    <div class="form-group row">
                                                        <div class="col">
                                                            <div>
                                                                <?php
                                                                foreach($FirmSolReminder as $rkey=>$rval){
                                                                ?>
                                                                <div class="row form-group fieldGroup-2">
                                                                    <div class="">
                                                                        <div
                                                                            class="d-flex col-10 pl-0 align-items-center">
                                                                            <div class="pl-0 col-5">
                                                                                <div>
                                                                                    <div class="">
                                                                                        <select id="reminder_type"
                                                                                            name="sol_reminder_type[]"
                                                                                            class="form-control custom-select valid"
                                                                                            aria-invalid="false">
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
                                                                            </div><input name="sol_reminder_number[]"
                                                                                class="form-control col-3 reminder-number"
                                                                                value="{{$rval->reminer_days}}">
                                                                            &nbsp;Days&nbsp;&nbsp;
                                                                            <button class="btn remove-2" type="button">
                                                                                <i class="fa fa-trash"
                                                                                    aria-hidden="true"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php } ?>
                                                                <div class="fieldGroup-2">
                                                                </div>
                                                                <div>
                                                                    <button type="button"
                                                                        class="btn btn-link p-0 test-add-new-reminder add-more-2">Add
                                                                        a reminder</button></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="fieldGroupCopy-2 copy hide" style="display: none;">
                                                        <div class="">
                                                            <div class="d-flex col-10 pl-0 align-items-center">

                                                                <div class="pl-0 col-5">
                                                                    <div>
                                                                        <div class="">
                                                                            <select id="reminder_type"
                                                                                name="sol_reminder_type[]"
                                                                                class="reminder_type form-control custom-select  ">
                                                                                <option value="popup">popup</option>
                                                                                <option value="email">email</option>
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <input name="sol_reminder_number[]"
                                                                    class="form-control col-3 reminder-number"
                                                                    value="1">
                                                                &nbsp;Days&nbsp;&nbsp;
                                                                <button class="btn remove-2 col-2" type="button">
                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="firm-logo-section row form-group">
                                            <label for="firm-logo" class="col-3 col-form-label"><b>Firm Logo</b></label>
                                            <div class="form-control-plaintext col-9">

                                                <?php 
                                                 $filePath=public_path('/upload/firm/'.$firmData->firm_logo);
                                                if(file_exists($filePath) && $firmData->firm_logo != NULL){
                                                    ?>
                                                <span id="imageArea">
                                                    <img style="width:50px;height:50px;" src="{{ $firmData->firm_logo_url }}">
                                                    <br>
                                                    <a href="javascript:void(0);" id="updateImage" onclick="updateImage()">Update</a> &nbsp;
                                                    <a href="javascript:void(0);" id="removeImage" onclick="removeImage()">Remove</a>
                                                </span>
                                                <span id="uploaArea" style="display:none;">
                                                    <input id="image-upload" name="firm_logo" type="file" class="w-50 form-control-file">
                                                    <em id="image-upload-text">Logo will be automatically resized to fit
                                                    within 200x50</em>
                                                </span>
                                               
                                                <?php 
                                                }else{?>
                                                <input id="image-upload" name="firm_logo" type="file"
                                                    class="w-50 form-control-file">
                                                <em id="image-upload-text">Logo will be automatically resized to fit
                                                    within 200x50</em>
                                                <?php } ?>
                                            </div>
                                            <input type="hidden" name="imageRemoveFromFirm" id="imageRemoveFromFirm" value="no">
                                        </div>
                                        <div class="d-flex flex-row justify-content-end">
                                            <button type="submit" class="btn btn-outline-secondary btn-rounded m-1">Save
                                                Preferences</button>
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
<div id="addNewOffice" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add New Office</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="addNewOfficeArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="editOffice" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Office Address</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="editOfficeArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="deleteAddress" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteFirmAddress" id="deleteFirmAddress" name="deleteFirmAddress" method="POST">
            @csrf
            <input type="hidden" value="" name="firm_id" id="delete_firm_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirmation</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Deleting this office will transfer all cases connected to this office to your primary
                            office. Are you sure you want to delete this office?
                            <br><br>
                            <p class="text-danger mb-0">This action <b>can not</b> be undone. </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader float-left" id="innerLoader"
                            style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Yes, delete this office</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {

        $(".add-more").click(function () {
            var fieldHTML = '<div class="row form-group fieldGroup">' + $(".fieldGroupCopy").html() +'</div>';
            $('body').find('.fieldGroup:last').before(fieldHTML);
            $('body').find('#reminder_user_type:last').attr("ownid", $(".fieldGroup").length);
            $('body').find('#reminder_user_type:last').attr("id", $(".fieldGroup").length);
            $('body').find('#reminder_type:last').attr("id", "reminder_type_" + $(".fieldGroup").length);
        });
        $('#editPreferance').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroup').remove();
        });
        $(".add-more-2").click(function () {
            var fieldHTML = '<div class="row form-group fieldGroup-2">' + $(".fieldGroupCopy-2").html() +'</div>';
            $('body').find('.fieldGroup-2:last').before(fieldHTML);
            $('body').find('#reminder_user_type:last').attr("ownid", $(".fieldGroup-2").length);
            $('body').find('#reminder_user_type:last').attr("id", $(".fieldGroup-2").length);
            $('body').find('#reminder_type:last').attr("id", "reminder_type_" + $(".fieldGroup-2").length);
        });
        $('#editPreferance').on('click', '.remove-2', function () {
            var $row = $(this).parents('.fieldGroup-2').remove();
        });

        $('#deleteFirmAddress').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteFirmAddress').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteFirmAddress").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/firms/deleteFirm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });

    });

    function submitForm() {
        beforeLoader();
        $("#basic_info").submit();
    }

    function addNewOffice() {
        $("#preloader").show();
        $("#addNewOfficeArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/firms/addNewFirm",
                data: {
                    "add": "true"
                },
                success: function (res) {
                    $("#addNewOfficeArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function editOffice(id) {
        $("#preloader").show();
        $("#editOfficeArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/firms/editFirm",
                data: {
                    "office_id": id
                },
                success: function (res) {
                    $("#editOfficeArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function deleteOffice(id) {
        $("#deleteAddress").modal("show");
        $("#delete_firm_id").val(id);
    }

    function updateImage()
    {
        $("#uploaArea").show();   
        $("#updateImage").hide(); 
        $("#removeImage").hide();   
        
    }
    function removeImage()
    {
        $("#uploaArea").show();   
        $("#updateImage").hide(); 
        $("#removeImage").hide();  
        $("#imageRemoveFromFirm").val('yes');  
    }
</script>
@stop
@endsection
