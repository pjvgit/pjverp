<form class="EditLead" id="EditLead" name="EditLead" method="POST">
    <input class="form-control" value="{{($UserMaster->id)??''}}" id="id" maxlength="250" name="id" type="hidden">    
    <input class="form-control" value="{{($LeadAdditionalInfo->id)??''}}" id="id" maxlength="250" name="user_id" type="hidden">
    <input class="form-control" value="fromdetail" id="id" maxlength="250" name="fromdetail" type="hidden">
    <div class="showError" style="display:none"></div>

    <span id="response"></span>
    @csrf
    <div class="col-md-12" bladefile="resources/views/lead/details/editLead.blade.php">
       
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Name</label>
            <div class="col-sm-4">
                <input class="form-control" value="{{($UserMaster->first_name)??''}}" id="first_name" maxlength="250" name="first_name" type="text"
                    placeholder="First name">
            </div>
            <div class="col-sm-2">
                <input class="form-control" value="{{($UserMaster->middle_name)??''}}" id="middle_name" maxlength="250" name="middle_name" type="text"
                    placeholder="M">
            </div>
            <div class="col-sm-3">
                <input class="form-control" id="last_name" value="{{($UserMaster->last_name)??''}}" maxlength="250" name="last_name" type="text"
                    placeholder="Last name">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Email</label>
            <div class="col-md-9 form-group mb-3">
                <input class="form-control" id="email" name="email" value="{{($UserMaster->email)??''}}" maxlength="191" type="text"
                    placeholder="example@email.com" autocomplete="off">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Cell phone</label>
            <div class="col-md-9 form-group mb-10">
                <input class="form-control" id="home_phone" value="{{($UserMaster->mobile_number)??''}}" maxlength="255" name="home_phone"
                    placeholder="(xxx)-xxx-xxxx">
            </div>

        </div>
        <div class="" id="accordionRightIcon">
            <div class="">
                <a class="collapsed" id="collapsed"  data-toggle="collapse" href="javascript:void(0);" data-target="#accordion-item-icons-1" aria-expanded="false">
                    Add More Contact Information  <i class="fas fa-sort-down align-text-top"></i></a>
            </div>
            <br>
            <div class="collapse" id="accordion-item-icons-1" style="">
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Work Phone</label>

                    <div class="col-md-3 form-group mb-10">
                        <input class="form-control" id="work_phone" value="{{($UserMaster->work_phone)??''}}" maxlength="255" name="work_phone"
                            placeholder="(xxx)-xxx-xxxx">
                    </div>

                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Home Phone</label>
                    <div class="col-md-3 form-group mb-10">
                        <input class="form-control" id="mobile_number" maxlength="255" value="{{($UserMaster->home_phone)??''}}" name="cell_phone"
                            placeholder="(xxx)-xxx-xxxx">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Address</label>
                    <div class="col-md-9 form-group mb-3">
                        <input class="form-control" id="address" name="address" maxlength="255" value="{{($UserMaster->street)??''}}" type="text"
                            placeholder="Address">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Address2</label>
                    <div class="col-md-9 form-group mb-3">
                        <input class="form-control" id="address2" name="address2" maxlength="255" value="{{($LeadAdditionalInfo->address2)??''}}" type="text"
                            placeholder="Address 2">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                    <div class="col-md-4 form-group mb-3">
                        <input class="form-control" id="city" name="city" value="{{($UserMaster->city)??''}}" maxlength="255" placeholder="City">
                    </div>
                    <div class="col-md-2 form-group mb-3">
                        <input class="form-control" id="state" name="state" value="{{($UserMaster->state)??''}}" maxlength="255"
                            placeholder="State">
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <input class="form-control" id="postal_code" value="{{($UserMaster->postal_code)??''}}" maxlength="255" name="postal_code"
                            placeholder="Zip code">
                    </div>

                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Country</label>
                    <div class="col-md-9 form-group mb-3">
                        <select class="form-control country" id="country" name="country"
                            data-placeholder="Select Country" style="width: 100%;">
                            <option value="{{($LeadAdditionalInfo->cell_phone)??''}}">Select Country</option>
                            <?php foreach($country as $key=>$val){?>
                            <option <?php if($val->id==$UserMaster->country){ echo "selected=selected"; } ?> value="{{$val->id}}"> {{$val->name}}</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row addmore">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Birthday</label>
                    <div class="col-md-9 form-group mb-3">
                        <input class="form-control datepicker" id="dob" readonly value="{{($LeadAdditionalInfo->dob) ? date('m/d/Y',strtotime($LeadAdditionalInfo->dob)) : convertUTCToUserTimeZone('dateOnly')}}"name="dob" type="text"
                            placeholder="mm/dd/yyyy">

                    </div>
                </div>
                <div class="form-group row addmore">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Driver License, State</label>
                    <div class="col-md-4 form-group mb-3">
                        <input class="form-control" id="driver_license" maxlength="255" name="driver_license" value="{{($LeadAdditionalInfo->driver_license)??''}}"
                            placeholder="Driver license">
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <input class="form-control" id="driver_state" maxlength="255" name="driver_state" value="{{($LeadAdditionalInfo->license_state)??''}}"
                            placeholder="License state">
                    </div>

                </div>
            </div>
        </div>
        <div class="form-group row" id="show_contact_group_dropdown">
            <label for=" inputEmail3" class="col-sm-3 col-form-label">Referral Source</label>
            <div class="col-md-6 form-group mb-3">
                <select class="form-control contact_group" id="referal_source" name="referal_source"
                    data-placeholder="Select Referral Source">
                    <option value="{{($LeadAdditionalInfo->cell_phone)??''}}">Select Referral Source</option>
                    <?php 
                    foreach($ReferalResource as $kcs=>$vcs){?>
                    <option <?php if($vcs->id==$LeadAdditionalInfo->referal_source){ echo "selected=selected"; }?> value="{{$vcs->id}}">{{$vcs->title}}</option>
                    <?php } ?>
                </select>
            </div>
            <label for="inputEmail3" class="col-sm-3 col-form-label"> <a onclick="openNewContactGroup();"
                    href="javascript:;">Add new referral source</a></label>
        </div>
        <div class="form-group row" id="show_contact_group_text">
            <label for=" inputEmail3" class="col-sm-3 col-form-label">Referral Source</label>
            <div class="col-md-6 form-group mb-3">
                <input class="form-control" id="referal_source_text" value="{{($LeadAdditionalInfo->cell_phone)??''}}" maxlength="255" name="referal_source_text"
                    type="text" placeholder="">
            </div>
            <label for="inputEmail3" class="col-sm-3 col-form-label"> <a onclick="openOldContactGroup();"
                    href="javascript:;">Cancel</a></label>
        </div>
        <div class="form-group row" id="show_company_dropdown">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Referred By</label>
            <div class="col-md-6 form-group mb-10">
                <select onchange="selectUser();" class="form-control user_type" id="refered_by" name="refered_by"
                    data-placeholder="Search for an existing contact or company">
                    <option value="{{($LeadAdditionalInfo->cell_phone)??''}}">Search for an existing contact or company</option>
                    <optgroup label="Client">
                        <?php
                        foreach($CaseMasterClient as $Clientkey=>$Clientval){
                        ?>
                        <option <?php if($Clientval->id==$LeadAdditionalInfo->refered_by){ echo "selected=selected"; }?> value="{{$Clientval->id}}">{{$Clientval->first_name}} {{$Clientval->last_name}} (Client)</option>
                        <?php } ?>
                    </optgroup>
                    <optgroup label="Company">
                        <?php foreach($CaseMasterCompany as $Companykey=>$Companyval){ ?>
                        <option <?php if($Companyval->id==$LeadAdditionalInfo->refered_by){ echo "selected=selected"; }?> value="{{$Companyval->id}}">{{$Companyval->first_name}} (Company)</option>
                        <?php } ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="form-group row addmore">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Lead Details
            </label>
            <div class="col-md-9 form-group mb-3">
                <textarea name="lead_detail" class="form-control" rows="3" maxlength="512"
                    placeholder="Add notes related to this individual...">{{$LeadAdditionalInfo->lead_detail}}</textarea>
            </div>
        </div>
        <div>
            <a class="collapsed" id="collapsed1"  data-toggle="collapse" href="javascript:void(0);" data-target="#accordion-item-icons-2" aria-expanded="false">
                Custom Fields for Lead   <i class="fas fa-sort-down align-text-top"></i></a>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label"></label>

                    <div class="col-md-9 form-group mb-10">
                        <div class="collapse" id="accordion-item-icons-2" style="">
                            Have more information you want to add? You can create custom fields for contacts by going to "Settings" and clicking "Custom Fields".Learn More
                        </div>
                    </div>
                
                </div>
          
        </div>
        </span>
        <hr>
        <div class="loader-bubble loader-bubble-primary" id="innerLoaderTime" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submitButton" type="submit">Save Lead</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        // $('#dateadded').datepicker({
        //     onSelect: function (dateText, inst) {},
        //     showOn: 'focus',
        //     showButtonPanel: true,
        //     closeText: 'Clear', // Text to show for "close" button
        //     onClose: function (selectedDate) {
        //         var event = arguments.callee.caller.caller.arguments[0];
        //         // If "Clear" gets clicked, then really clear it
        //         if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
        //             $(this).val('');
        //         }
        //     }
        // });
        // $('#dob').datepicker({
        //     maxDate: -0,
        //     onSelect: function (dateText, inst) {},
        //     showOn: 'focus',
        //     showButtonPanel: true,
        //     closeText: 'Clear', // Text to show for "close" button
        //     onClose: function (selectedDate) {
        //         var event = arguments.callee.caller.caller.arguments[0];
        //         // If "Clear" gets clicked, then really clear it
        //         if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
        //             $(this).val('');
        //         }
        //     }
        // });

        $('#dateadded').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        $('#dob').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
             endDate: '+0d',
             'todayHighlight': true
        });
        $("#innerLoaderTime").css('display', 'none');
        $("#innerLoaderTime").hide();
        $("#show_contact_group_text").hide();
        $("#show_contact_group_dropdown").show();


        $("#EditLead").validate({
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
                    email: true
                },
                website: {
                    url: false
                },
                home_phone: {
                    number: true
                },
                work_phone: {
                    number: true
                },
                cell_phone: {
                    number: true
                },
                fax_number: {
                    number: true
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
                    minlength: "Email is not formatted correctly"
                },
                website: {
                    url: "Please enter valid website url"
                },
                home_phone: {
                    number: "Please enter numeric value"
                },
                work_phone: {
                    number: "Please enter numeric value"
                },
                cell_phone: {
                    number: "Please enter numeric value"
                },
                fax_number: {
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
   
    $('#EditLead').submit(function (e) {
        $("#submitButton").attr("disabled", true);
        $("#innerLoaderTime").css('display', 'block');
        e.preventDefault();

        if (!$('#EditLead').valid()) {
            $("#innerLoaderTime").css('display', 'none');
            $('#submitButton').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#EditLead").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/updateLead", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&update=yes';
            },
            success: function (res) {
                $("#innerLoaderTime").css('display', 'block');
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
                    $("#innerLoaderTime").css('display', 'none');
                    $('#submitButton').removeAttr("disabled");
                    // $("#editLead").scrollTop(0);
                    $('#editLead').animate({ scrollTop: 0 }, 'slow');

                    return false;
                } else {
                   window.location.reload();
                }
            }
        });

     
    });

    $('#collapsed').click(function() { 
        $("#collapsed").find('i').toggleClass('fa-sort-up align-bottom').toggleClass('fa-sort-down align-text-top'); 
    }); 
    $('#collapsed1').click(function() { 
        $("#collapsed1").find('i').toggleClass('fa-sort-up align-bottom').toggleClass('fa-sort-down align-text-top'); 
    }); 

    function ontogleClass() {
        $("#addmorearea").toggle();
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
    // $('.decimal').keyup(function(){
    //     var val = $(this).val();
    //     if(isNaN(val)){
    //         val = val.replace(/[^0-9\.]/g,'');
    //         if(val.split('.').length>2) 
    //             val =val.replace(/\.+$/,"");
    //     }
    //     $(this).val(val); 
    // });

    $('input.decimal').keyup(function(event) {
            // skip for arrow keys
            if(event.which >= 37 && event.which <= 40) return;
            // format number
            $(this).val(function(index, value) {
                if(value.split('.').length>2) 
                    return value =value.replace(/\.+$/,"");
                return value.replace(/[^0-9\.]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            });
        });

$("#first_name").focus();
</script>
