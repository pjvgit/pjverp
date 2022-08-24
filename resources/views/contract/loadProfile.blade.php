<?php
$userTitle = unserialize(USER_TITLE); 
?>
<div id="showError" style="display:none"></div>
<div id="response" style="display:none"></div>

<form class="updateProfile" id="updateProfile" name="updateProfile" method="POST">
    @csrf
    <input type="hidden" name="uid" value="{{base64_encode($userProfile->id)}}">
    <div class="col-md-12" bladeFile="resources/views/contract/loadProfile.blade.php"> 
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-4">
                <input class="form-control" value="{{ $userProfile->first_name ?? old('first_name') }}" id="first_name" name="first_name" type="text"
                    placeholder="Enter your first name">
            </div>
            <div class="col-sm-2">
                <input class="form-control" value="{{ $userProfile->middle_name ?? old('middle_name') }}" id="middle_name" name="middle_name" type="text" placeholder="M">
            </div>
            <div class="col-sm-4">
                <input class="form-control" id="last_name" value="{{ $userProfile->last_name ?? old('last_name') }}" name="last_name" type="text"
                    placeholder="Enter your last name">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Email</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="oldEmail" name="oldEmail" value="{{ $userProfile->email ?? old('email') }}" type="hidden" placeholder="Enter Email">

                <input class="form-control" id="email" name="email" value="{{ $userProfile->email ?? old('email') }}" type="text" placeholder="Enter Email">
                <small class="error" id="newEmail">If you change a firm user's email, they will receive a new welcome email in order to re-activate their account.</small>
            </div>
            
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">User
                Type</label>
            <div class="col-md-10 form-group mb-3">
                <select class="form-control user_type select2" id="user_type" name="user_type"
                    data-placeholder="Select User Type">
                    <option value="">Select User Type</option>
                    <option <?php if($userProfile->user_type=="1") { echo "selected=selected"; }?> value="1">Attorney</option>
                    <option <?php if($userProfile->user_type=="2") { echo "selected=selected"; }?> value="2">Paralegal</option>
                    <option <?php if($userProfile->user_type=="3") { echo "selected=selected"; }?> value="3">Staff</option>
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
                <select class="form-control country select2" id="user_title" name="user_title"
                    data-placeholder="Select User Title" style="width: 100%;">
                    <option value="">Select User Title</option>
                    <?php foreach($userTitle as $keyTitle=>$valTitle){?>
                    <option  <?php if($userProfile->user_title==$valTitle) { echo "selected=selected"; }?>  value="{{$valTitle}}"> {{$valTitle}}</option>
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
                <input class="form-control" name="default_rate" id="default_rate" value="{{ $userProfile->default_rate ?? old('default_rate') }}" type="text" aria-label="Amount (to the nearest dollar)">
                <div class="input-group-append"><span class="input-group-text">/hr</span></div>
                <div  class="input-group col-sm-12" id="TypeError"></div>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Address</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="street" name="street" value="{{ $userProfile->street ?? old('street') }}" type="text" placeholder="Enter street">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="apt_unit" value="{{ $userProfile->apt_unit ?? old('apt_unit') }}" name="apt_unit" type="text"
                    placeholder="Enter apt/unit">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="city" name="city" value="{{ $userProfile->city ?? old('city') }}" placeholder="Enter city">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="state" name="state" value="{{ $userProfile->state ?? old('state') }}" placeholder="Enter state">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="postal_code" value="{{ $userProfile->postal_code ?? old('postal_code') }}" name="postal_code"
                    placeholder="Enter postal code">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
            <div class="col-md-10 form-group mb-3">
                <select class="form-control country" id="country" name="country" data-placeholder="Select Country"
                    style="width: 100%;">
                    <?php foreach($country as $key=>$val){?>
                    <option value="{{$val->id}}"> {{$val->name}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Phone</label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="home_phone" value="{{ $userProfile->home_phone ?? old('home_phone') }}" name="home_phone" placeholder="Enter home phone">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="work_phone" value="{{ $userProfile->work_phone ?? old('work_phone') }}" name="work_phone" placeholder="Enter work phone">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="mobile_number" value="{{ $userProfile->mobile_number ?? old('cell_phone') }}" name="cell_phone"
                    placeholder="Enter cell phone">
            </div>
        </div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>

            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit"
                data-style="expand-left"><span class="ladda-label">Save</span><span
                    class="ladda-spinner"></span><span class="ladda-spinner"></span></button>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader"></div>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>

    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $(".select2").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#DeleteModal"),
        });
        $(".country").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#DeleteModal"),
        });
        

        $("#newEmail").css('display', 'none');
        $("#innerLoader").css('display', 'none');
        $("#updateProfile").validate({
            rules: {
                first_name: {
                    required: true,
                    minlength: 2
                },
                last_name: {
                    required: true,
                    minlength: 2
                },
                oldEmail: {
                    email: true
                },
                email: {
                    required: true,
                    email: true,
                    remote: {
                        url: baseUrl  + "/validate_email",
                        type: "post",
                        data: {
                            "id": "<?=$userProfile->id?>"
                        },
                    }
                },
                user_type: {
                    required: true
                },
                default_rate:{
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
                    minlength: "Email is not formatted correctly",
                    remote : "Email address is already taken"
                },
                user_type: {
                    required: "User Type can't be blank"
                },
                default_rate:{
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
        
       
        $("#email").on('change keyup paste', function() {
            var oldEmail= $("#oldEmail").val();
            var email= $("#email").val();
            if(oldEmail!=email){
                $("#newEmail").css('display', 'block');
            }else{
                $("#newEmail").css('display', 'none');
            }
           
        });
    });
    $('#updateProfile').submit(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        

        if (!$('#updateProfile').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }

        var dataString = $("#updateProfile").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/saveProfile", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader").css('display', 'none');
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
                    $('#DeleteModal').animate({
                        scrollTop: 0
                    }, 'slow');
                    return false;
                } else {
                    // $("#preloader").hide();
                    // $("#response").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Profile data has been updated.</div>');
                    // $("#response").show();
                    // $("#innerLoader").css('display', 'none');
                    // $('#submit').removeAttr("disabled");
                    window.location.reload();
                    // return false;
                   
                }
            }
        });
    });

   

</script>
