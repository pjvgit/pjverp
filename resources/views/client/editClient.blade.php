<div id=".showError" class="showError" style="display:none"></div>
<form class="UpdateClientForm" id="UpdateClientForm" name="UpdateClientForm" method="POST">
    @csrf
    <span id="response"></span>
    <input class="form-control" value="{{$userData->id}}" id="user_id" name="user_id" type="hidden" placeholder="M">
    <?php 
    if(isset($case_id) && $case_id!=''){?>
    <input class="form-control" value="{{$case_id}}" id="case_id" name="case_id" type="hidden" placeholder="M">
    <?php } ?>
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-4">
                <input class="form-control" value="{{ $userData->first_name ?? old('first_name') }}" id="first_name" name="first_name" type="text"
                    placeholder="Enter your first name">
            </div>
            <div class="col-sm-2">
                <input class="form-control" value="{{ $userData->middle_name ?? old('middle_name') }}" id="middle_name" name="middle_name" type="text" placeholder="M">
            </div>
            <div class="col-sm-4">
                <input class="form-control" id="last_name" value="{{ $userData->last_name ?? old('last_name') }}" name="last_name" type="text"
                    placeholder="Enter your last name">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Email</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="email" name="email" value="{{ $userData->email ?? old('email') }}" type="text" placeholder="Enter Email">
            </div>
        </div>
        <div class="form-group row" id="show_contact_group_dropdown">
            <label for=" inputEmail3" class="col-sm-2 col-form-label">Contact Group</label>
            <div class="col-md-6 form-group mb-3">
                <select class="form-control contact_group" id="contact_group" name="contact_group"
                    data-placeholder="Select Contact Group">
                    <!-- <option value="">Select Contact Group</option> -->
                    <?php foreach($ClientGroup as $key=>$val){?>
                    <option <?php if($UsersAdditionalInfo->contact_group_id==$val->id){ echo "selected=selected"; }?>  value="{{$val->id}}"> {{$val->group_name}}</option>
                    <?php } ?>
                </select>

            </div>
            <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="openNewContactGroup();"
                    href="javascript:;">Add
                    new contact group</a></label>
        </div>
        <div class="form-group row" id="show_contact_group_text">
            <label for=" inputEmail3" class="col-sm-2 col-form-label">Contact Group</label>
            <div class="col-md-6 form-group mb-3">
                <input class="form-control" id="contact_group_text" value="" name="contact_group_text" type="text"
                    placeholder="Enter new contact group">
            </div>
            <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="openOldContactGroup();"
                    href="javascript:;">Cancel</a></label>
        </div>  
   
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Enable Client Portal</label>
            <div class="col-md-10 form-group mb-3">
                <label class="switch pr-5 switch-success mr-3"><span>Enable</span>
                    <input type="checkbox"  <?php if($UsersAdditionalInfo->client_portal_enable=="1"){ echo "checked=checked"; }?> name="client_portal_enable" id="client_portal_enable"><span class="slider"></span>
                </label>
                <br>
                <small>Securely share documents, invoices, and messages with your client. They will receive a welcome email with login instructions. Your client will only have access to items that you explicitly share.</small> 
                <p> <a href="3">What will my clients see?</a></p>   
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Cell phone</label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="mobile_number" value="{{ $userData->mobile_number ?? old('mobile_number') }}" name="cell_phone"
                    placeholder="Enter cell phone">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Work Phone</label>

            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="work_phone" value="{{ $userData->work_phone ?? old('work_phone') }}" name="work_phone" placeholder="Enter work phone">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Home Phone</label>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="home_phone" value="{{ $userData->home_phone ?? old('home_phone') }}" name="home_phone" placeholder="Enter home phone">
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Address</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="address" name="address" value="{{ $userData->street ?? old('address') }}" type="text" placeholder="Enter address">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Address2</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="address2" name="address2" value="{{ $UsersAdditionalInfo->address2 ?? old('address2') }}" type="text" placeholder="Enter address2">
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="city" name="city" value="{{ $userData->city ?? old('city') }}" placeholder="Enter city">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="state" name="state" value="{{ $userData->state ?? old('state') }}" placeholder="Enter state">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="postal_code" value="{{ $userData->postal_code ?? old('postal_code') }}" name="postal_code"
                    placeholder="Enter postal code">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Country</label>
            <div class="col-md-10 form-group mb-3">
                <select class="form-control country" id="country" name="country" data-placeholder="Select Country"
                    style="width: 100%;">
                    <option value="">Select Country</option>
                    <?php foreach($country as $key=>$val){?>
                    <option <?php if($userData->country==$val->id){ echo "selected=selected"; }?> value="{{$val->id}}"> {{$val->name}}</option>
                    <?php } ?>
                </select>
            </div>
        </div> 
        
        <div class="">
            <a class="collapsed" id="collapsed"  data-toggle="collapse" href="javascript:void(0);" data-target="#addmorearea" aria-expanded="false">
                Add More Information  <i class="fas fa-sort-down align-text-top"></i></a>
        </div>
      
      

        {{-- <a href="javascript:void(0);" id="hideshowaddmore" onclick="ontogleClass();">Add More Information<span class="Select-arrow ml-1 mt-3"></span></a> --}}
                <span id="addmorearea"  class="collapse" >
<br>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Birthday</label>
            <div class="col-md-10 form-group mb-3">
                <?php
                $DOB=NULL;
                if($UsersAdditionalInfo->dob!=NULL){
                    $DOB=date("m/d/Y",strtotime($UsersAdditionalInfo->dob));
                }?>

                <input class="form-control datepicker" id="dob" readonly value="{{ ($DOB)??old('dob') }}" name="dob" type="text"
                    placeholder="mm/dd/yyyy">

            </div>
        </div>
        <div class="form-group row" id="show_company_dropdown">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Company</label>
            <div class="col-md-6 form-group mb-3">
                <select id="company_name"  name="company_name[]"  multiple  class="form-control custom-select col" style="width:100%">
                    <option value="">Select company</option>
                    <?php foreach($CompanyList as $companyKey=>$companyVal){?>
                        <option <?php if(in_array($companyVal->id,explode(",",$UsersAdditionalInfo->multiple_compnay_id))){ echo "selected=selected"; }?> value="{{$companyVal->id}}"> {{$companyVal->first_name}} {{$companyVal->last_name}}</option>
                        <?php } ?>
                </select>
            </div>
            <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="openNewCompany();" href="javascript:;">Add
                    new Company</a></label>
        </div>
        <div class="form-group row" id="show_company_text">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Company</label>
            <div class="col-md-6 form-group mb-3">
                <input class="form-control" id="company_name_text" value="" name="company_name_text" type="text"
                    placeholder="Enter new company name">
                    <span class="error" id="companyError"></span>

            </div>
            <label for="inputEmail3" class="col-sm-2 col-form-label"> <a onclick="createCompany();"
                href="javascript:;">Create Company</a></label>
            <label for="inputEmail3" class="col-sm-2 col-form-label"> <a onclick="openOldCompany();"
                    href="javascript:;">Cancel</a></label>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Job Title</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="job_title" value="{{ $UsersAdditionalInfo->job_title ?? old('job_title') }}" name="job_title" type="text"
                    placeholder="Enter job title">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Driver License, State</label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="driver_license" name="driver_license" value="{{ $UsersAdditionalInfo->driver_license ?? old('driver_license') }}" placeholder="Enter driver license">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="driver_state" name="driver_state" value="{{ $UsersAdditionalInfo->license_state ?? old('driver_state') }}" placeholder="Enter license state">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Website</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="website" value="{{ $UsersAdditionalInfo->website ?? old('website') }}" name="website" type="text"
                    placeholder="Enter website">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Fax Number</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="fax_number" value="{{ $UsersAdditionalInfo->fax_number ?? old('fax_number') }}" name="fax_number" type="text"
                    placeholder="Enter fax number">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Notes</label>
            <div class="col-md-10 form-group mb-3">
                <textarea name="notes" class="form-control" rows="5"  placeholder="Enter notes">{{ $UsersAdditionalInfo->notes ?? old('notes') }}</textarea>
            </div>
        </div>   
        </span>
        <div class="">
            <a class="collapsed" id="collapsed1"  data-toggle="collapse" href="javascript:void(0);" data-target="#addmorearea1" aria-expanded="false">
                Custom Fields  <i class="fas fa-sort-down align-text-top"></i></a>
        </div>
        <span id="addmorearea1"  class="collapse" >
            <div>
                <div class="form-group row">
                    <label for="custom_fields_empty_state" class="col-sm-3 col-form-label"></label>
                    <div class="col">
                        <div>Have more information you want to add? You can create custom fields for contacts by going to "Settings" and clicking "Custom Fields".<a class="ml-2" href="#" target="_blank" rel="noopener noreferrer">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </span>
        <hr>
        <div class="form-group row">
            <a class="col-sm-2" href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <label for="inputEmail3" class="col-sm-8 col-form-label"> 
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display:none;"></div>
            </label>
            <label for="inputEmail3" class="col-sm-2 col-form-label">
                <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit"
                data-style="expand-left">Update Contact</button>
            </label>
        </div>

    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();
        $(".datepicker" ).datepicker({'todayHighlight': true});
        $('#company_name').select2();

        $("#show_company_text").hide();
        $("#show_company_dropdown").show();
        $("#show_contact_group_text").hide();
        $("#show_contact_group_dropdown").show();
        $("#innerLoader").css('display', 'none');
        $("#UpdateClientForm").validate({
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
                    required: {
                        depends: function (element) {
                            var status = false;
                            if ($("#client_portal_enable:checked").is(':checked')) {
                                var status = true;
                            }
                            return status;
                        }
                    },
                    email: true
                },
                home_phone:{
                    number:true
                },
                work_phone:{
                    number:true
                },
                cell_phone:{
                    number:true
                },
                website:{
                    url:false
                },
               
                fax_number:{
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
                    required: "Email is required to use the Client Portal.",
                    minlength: "Email is not formatted correctly"
                },
                website: {
                    url: "Please enter valid website url"
                },
                work_phone:{
                    number: "Please enter numeric value"
                },
                home_phone:{
                    number: "Please enter numeric value"
                },
                cell_phone:{
                    number: "Please enter numeric value"
                },
                fax_number:{
                    number: "Please enter numeric value"
                },
                postal_code:{
                    number:true
                },
                postal_code:{
                    number: "Please enter numeric value"
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#user_type')) {
                    error.appendTo('#UserTypeError');
                } else if (element.is('#default_rate')) {
                    error.appendTo('#TypeError');
                } else {
                    element.after(error);
                }
            }
        });
        $("#show_company_text").hide();

    });

    
    $('#collapsed').click(function() { 
        $("#collapsed").find('i').toggleClass('fa-sort-up align-bottom').toggleClass('fa-sort-down align-text-top'); 
         
    });
    $('#collapsed1').click(function() { 
        $("#collapsed1").find('i').toggleClass('fa-sort-up align-bottom').toggleClass('fa-sort-down align-text-top'); 
         
    });
    $('#UpdateClientForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        if (!$('#UpdateClientForm').valid()) {
            afterLoader();
            return false;
        }

        var dataString = $("#UpdateClientForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/saveEditContact", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader").css('display', 'block');
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
                    // $("#EditContactModal").scrollTop(0);
                    $('#EditContactModal').animate({ scrollTop: 0 }, 'slow');

                    return false;
                } else {
                    window.location.reload();
                    afterLoader();

                }
            },error: function(xhr, status, error) {
                $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $('#EditContactModal').animate({ scrollTop: 0 }, 'slow');

            }
        });
    });
    function ontogleClass() {
        $("#addmorearea").toggle();
        // alert("CA");
    }
    function openNewContactGroup() {
        $("#show_contact_group_text").show();
        $("#show_contact_group_dropdown").hide();
        return false;
    }
    function openOldContactGroup() {
        $("#show_contact_group_text").hide();
        $("#show_contact_group_dropdown").show()
        return false;
    }

    function openNewCompany() {
        $("#show_company_text").show();
        $("#show_company_dropdown").hide();
        return false;
    }
    function openOldCompany() {
        $("#show_company_text").hide();
        $("#show_company_dropdown").show();
        return false;
    }

    
    function createCompany() {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/contacts/createCompany", // json datasource
                data: {"company_name_text":$("#company_name_text").val()},
                success: function (res) {
                    if (res.errors != '') {
                        $('#companyError').html('');
                        var errotHtml ='';
                        $.each(res.errors, function (key, value) {
                            errotHtml += value ;
                        });
                        errotHtml += '<br>';
                        $('#companyError').append(errotHtml);
                        $('#companyError').show();
                        $("#preloader").hide();
                        return false;
                    } else {
                        
                         reloadCompanySelection(res);
                         openOldCompany();
                    }
                   
                }
            })
        })
    }
    function reloadCompanySelection(res) {
        $.ajax({
            type: "POST",
            url:  baseUrl +"/contacts/realoadCompanySelection", // json datasource
            data: {"res":res,"client_id":{{$userData->id}}},
            success: function (res) {
                $("#show_company_dropdown").html('');
                    $("#show_company_dropdown").html(res);
                    $("#preloader").hide();
            }
        });
    }

    $('#company_name').on("select2:unselect", function(e){
         var unselected_value = $('#company_name').val();
         $.ajax({
            type: "POST",
            url:  baseUrl +"/contacts/removeCompany", // json datasource
            data: {"unselected_value":unselected_value},
            success: function (res) {
                
            }
        });
    }).trigger('change');

$("#first_name").focus();
</script>
