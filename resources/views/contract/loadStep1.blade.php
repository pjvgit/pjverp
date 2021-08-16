<?php
$userTitle = unserialize(USER_TITLE); 
?>
<div id="showError" style="display:none"></div>


<h4 class="border-bottom border-gray pb-2">Add New User</h4>
<p class="alert alert-primary"><i class="fas fa-info-circle"></i> Adding new users may result in additional charges. <a href="#" target="_blank" rel="noopener noreferrer">Please go here to learn more about new users and pricing.</a></p>
<form class="createNewUser" id="createNewUser" name="createNewUser" method="POST">
    @csrf
    <?php 
    if($case_id!=''){?>
    <input class="form-control" value="{{$case_id}}" id="case_id" name="case_id" type="hidden" placeholder="M">
    <?php } ?>
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-4">
                <input class="form-control" maxlength="255" value="" id="first_name" name="first_name" type="text"
                    placeholder="Enter your first name">
            </div>
            <div class="col-sm-2">
                <input class="form-control" maxlength="255" value="" id="middle_name" name="middle_name" type="text" placeholder="M">
            </div>
            <div class="col-sm-4">
                <input class="form-control" maxlength="255" id="last_name" value="" name="last_name" type="text"
                    placeholder="Enter your last name">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Email</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="email" maxlength="191" name="email" value="" type="text" placeholder="Enter Email">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">User
                Type</label>
            <div class="col-md-10 form-group mb-3">
                <select class="form-control user_type" id="user_type" name="user_type"
                    data-placeholder="Select User Type">
                    <option value="">Select User Type</option>
                    <option value="1">Attorney</option>
                    <option value="2">Paralegal</option>
                    <option value="3">Staff</option>
                </select>
                <small>User type is used to group users for reminder
                    notifications.</small><br>
                    <span id="UserTypeError"></span>
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">User
                Title</label>
            <div class="col-md-10 form-group mb-3">
                <select class="form-control country" id="user_title" name="user_title"
                    data-placeholder="Select User Title" style="width: 100%;">
                    <option value="">Select User Title</option>
                    <?php foreach($userTitle as $keyTitle=>$valTitle){?>
                    <option value="{{$valTitle}}"> {{$valTitle}}</option>
                    <?php } ?>
                </select>
                <small>User Title is used for display purposes and email/text
                    communication.
                </small><br>
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Default
                Rate</label>
            <div class="input-group mb-3 col-sm-10">
                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                <input class="form-control number" name="default_rate" id="default_rate" type="text"  maxlength="20"  aria-label="Amount (to the nearest dollar)">
                <div class="input-group-append"><span class="input-group-text">/hr</span></div>
                <div  class="input-group col-sm-12" id="TypeError"></div>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Address</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="street" name="street" value=""  maxlength="255"  type="text" placeholder="Enter street">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="apt_unit" value="" maxlength="255"  name="apt_unit" type="text"
                    placeholder="Enter apt/unit">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="city" name="city"  maxlength="255" value="" placeholder="Enter city">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="state" name="state"  maxlength="255" value="" placeholder="Enter state">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="postal_code" value="" maxlength="255"  name="postal_code"
                    placeholder="Enter postal code">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
            <div class="col-md-10 form-group mb-3">
                <select class="form-control country" id="country" name="country" data-placeholder="Select Country"
                    style="width: 100%;">
                    <option value="">Select Country</option>
                    <?php foreach($country as $key=>$val){?>
                    <option value="{{$val->id}}"> {{$val->name}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Phone</label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="home_phone" value=""  maxlength="255" name="home_phone" placeholder="Enter home phone">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="work_phone" value=""  maxlength="255" name="work_phone" placeholder="Enter work phone">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="mobile_number" value="" maxlength="255"  name="cell_phone"
                    placeholder="Enter cell phone">
            </div>
        </div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>

            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit"
                data-style="expand-left"><span class="ladda-label">Create User</span><span
                    class="ladda-spinner"></span><span class="ladda-spinner"></span></button>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display: none;"></div>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>

    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#innerLoader").css('display', 'none');
        $("#createNewUser").validate({
            rules: {
                first_name: {
                    required: true,
                    minlength: 2
                },
                last_name: {
                    required: true,
                    minlength: 2
                },
                email: {
                    required: true,
                    email: true
                },
                user_type: {
                    required: true
                },
                default_rate:{
                    number:true
                },
                home_phone:{
                    number:true
                },
                work_phone:{
                    number:true
                },
                cell_phone:{
                    number:true
                }
            },
            messages: {
                first_name: {
                    required: "Please enter first name",
                    minlength: "First name must consist of at least 2 characters"
                },
                last_name: {
                    required: "Please enter last name",
                    minlength: "Last name must consist of at least 2 characters"
                },
                email: {
                    required: "Please enter email address",
                    minlength: "Email is not formatted correctly"
                },
                user_type: {
                    required: "User Type can't be blank"
                },
                default_rate:{
                    number: "Please enter numeric value"
                },
                work_phone:{
                    number: "Please enter numeric value"
                },
                home_phone:{
                    number: "Please enter numeric value"
                },
                cell_phone:{
                    number: "Please enter numeric value"
                }
            },
            
            errorPlacement: function (error, element) {
                if (element.is('#user_type')) {
                    error.appendTo('#UserTypeError');
                }else if (element.is('#default_rate')) {
                    error.appendTo('#TypeError');
                }  else {
                    element.after(error);
                }
            }
        });
    });
    $('#createNewUser').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#createNewUser').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }

        var dataString = $("form").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/saveStep1", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader").css('display', 'block');
                if (res.errors != '') {
                    $('#showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError').append(errotHtml);
                    $('#showError').show();
                    $("#innerLoader").css('display', 'none');
                    $('#submit').removeAttr("disabled");
                    return false;
                } else {
                    loadStep2(res);
                }
            }
        });
    });

    function loadStep2(res) {
        console.log(res);
        $('#smartwizard').smartWizard("next");
        $("#innerLoader").css('display', 'none');
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/loadStep2", // json datasource
            data: {
                "user_id": res.user_id,
                "case_id": {{ $case_id ?? 0 }}
            },
            success: function (res) {
                $("#step-2").html(res);
                $("#preloader").hide();
            }
        })

        return false;
    }

     //Amount validation
     $('input.number').keyup(function(event) {
            // skip for arrow keys
            if(event.which >= 37 && event.which <= 40) return;
            // format number
            $(this).val(function(index, value) {
                if(value.split('.').length>2) 
                    return value =value.replace(/\.+$/,"");
                return value.replace(/[^0-9\.]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            });
        });

</script>
