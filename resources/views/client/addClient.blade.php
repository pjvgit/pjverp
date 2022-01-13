<div id="showError" style="display:none"></div>
<form class="createNewUser" id="createNewUser" name="createNewUser" method="POST">
    <span id="response"></span>

    <?php if($case_id!=''){?>
    <input class="form-control" value="{{$case_id}}" id="case_id" name="case_id" type="hidden" placeholder="case_id">
    <?php } ?>
    <?php if($adjustment_token!=''){?>
    <input class="form-control" value="{{$adjustment_token}}" id="adjustment_token" name="adjustment_token" type="hidden" placeholder="adjustment_token">
    <?php } ?>
    <?php if($company_id!=''){?>
    <input class="form-control" value="{{$company_id}}" id="company_id" name="company_id" type="hidden" placeholder="adjustment_token">
    <?php } ?>
    @csrf
    <div class="col-md-12" bladename="resources/views/client/addClient.blade.php">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-4">
                <input class="form-control" value="" id="first_name" maxlength="250" name="first_name" type="text"
                    placeholder="Enter your first name">
            </div>
            <div class="col-sm-2">
                <input class="form-control" value="" id="middle_name" maxlength="250" name="middle_name" type="text"
                    placeholder="M">
            </div>
            <div class="col-sm-4">
                <input class="form-control" id="last_name" value="" maxlength="250" name="last_name" type="text"
                    placeholder="Enter your last name">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Email</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="email" name="email" value="" maxlength="191" type="text"
                    placeholder="Enter Email">
            </div>
        </div>
        <div class="form-group row" id="show_contact_group_dropdown">
            <label for=" inputEmail3" class="col-sm-2 col-form-label">Contact Group</label>
            <div class="col-md-6 form-group mb-3">
                <select class="form-control contact_group" id="contact_group" name="contact_group"
                    data-placeholder="Select Contact Group">
                    <!-- <option value="">Select Contact Group</option> -->
                    <?php foreach($ClientGroup as $key=>$val){?>
                    <option value="{{$val->id}}"> {{$val->group_name}}</option>
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
                <input class="form-control" id="contact_group_text" value="" maxlength="255" name="contact_group_text"
                    type="text" placeholder="Enter new contact group">
            </div>
            <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="openOldContactGroup();"
                    href="javascript:;">Cancel</a></label>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Enable Client Portal</label>
            <div class="col-md-10 form-group mb-3">
                <label class="switch pr-5 switch-success mr-3"><span>Enable</span>
                    <input type="checkbox" value="1" name="client_portal_enable" id="client_portal_enable" <?php if($client_portal_access->client_portal_access=="yes"){ echo "Checked=checked";} ?>><span
                        class="slider"></span>
                </label>
                <br>
                <small>Securely share documents, invoices, and messages with your client. They will receive a welcome
                    email with login instructions. Your client will only have access to items that you explicitly
                    share.</small>
                <p> <a href="#">What will my clients see?</a></p>
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Cell phone</label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="mobile_number" maxlength="255" value="" name="cell_phone"
                    placeholder="Enter cell phone">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Work Phone</label>

            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="work_phone" value="" maxlength="255" name="work_phone"
                    placeholder="Enter work phone">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Home Phone</label>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="home_phone" value="" maxlength="255" name="home_phone"
                    placeholder="Enter home phone">
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Address</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="address" name="address" maxlength="255" value="" type="text"
                    placeholder="Enter address">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Address2</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="address2" name="address2" maxlength="255" value="" type="text"
                    placeholder="Enter address2">
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="city" name="city" value="" maxlength="255" placeholder="Enter city">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="state" name="state" value="" maxlength="255" placeholder="Enter state">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="postal_code" value="" maxlength="255" name="postal_code"
                    placeholder="Enter postal code">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Country</label>
            <div class="col-md-10 form-group mb-3">
                <select class="form-control" id="countryListData" name="country" data-placeholder="Select Country"
                    style="width: 100%;">
                    <option value="">Select Country</option>
                    <?php foreach($country as $key=>$val){?>
                    <option value="{{$val->id}}"> {{$val->name}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="">
            <a class="collapsed" id="collapsed" data-toggle="collapse" href="javascript:void(0);"
                data-target="#addmorearea" aria-expanded="false">
                Add More Information <i class="fas fa-sort-down align-text-top"></i></a>
        </div>
        <br>
        {{-- <a href="javascript:void(0);" id="hideshowaddmore" onclick="ontogleClass();">Add More Information<span class="Select-arrow ml-1 mt-3"></span></a> --}}
        <span id="addmorearea" class="collapse">
            <div class="form-group row addmore">
                <br>
                <label for="inputEmail3" class="col-sm-2 col-form-label">Birthday</label>
                <div class="col-md-10 form-group mb-3">
                    <input class="form-control datepicker" id="dob" readonly value="" name="dob" type="text"
                        placeholder="mm/dd/yyyy">

                </div>
            </div>
            <div class="form-group row" id="show_company_dropdown">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Company</label>
                <div class="col-md-6 form-group mb-3">
                    <select id="company_name" name="company_name[]" multiple
                        class="company_name form-control custom-select col" style="width:100%">
                        <option value="">Select company</option>
                        <?php foreach($CompanyList as $companyKey=>$companyVal){?>
                        <option value="{{$companyVal->id}}" <?php if($companyVal->id==$company_id){ echo "selected=selected"; } ?> > {{$companyVal->name}}
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="openNewCompany();"
                        href="javascript:;">Add
                        new Company</a></label>
            </div>
            <div class="form-group row" id="show_company_text">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Company</label>
                <div class="col-md-6 form-group mb-3">
                    <input class="form-control" id="company_name_text" value="" maxlength="255" name="company_name_text"
                        type="text" placeholder="Enter new company name">
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
                    <input class="form-control" id="job_title" value="" maxlength="255" name="job_title" type="text"
                        placeholder="Enter job title">
                </div>
            </div>
            <div class="form-group row addmore">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Driver License, State</label>
                <div class="col-md-4 form-group mb-3">
                    <input class="form-control" id="driver_license" maxlength="255" name="driver_license" value=""
                        placeholder="Enter driver license">
                </div>
                <div class="col-md-3 form-group mb-3">
                    <input class="form-control" id="driver_state" maxlength="255" name="driver_state" value=""
                        placeholder="Enter license state">
                </div>

            </div>
            <div class="form-group row addmore">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Website</label>
                <div class="col-md-10 form-group mb-3">
                    <input class="form-control" id="website" value="" maxlength="512" name="website" type="text"
                        placeholder="Enter website">
                </div>
            </div>
            <div class="form-group row addmore">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Fax Number</label>
                <div class="col-md-10 form-group mb-3">
                    <input class="form-control" id="fax_number" value="" maxlength="16" name="fax_number" type="text"
                        placeholder="Enter fax number">
                </div>
            </div>
            <div class="form-group row addmore">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Notes</label>
                <div class="col-md-10 form-group mb-3">
                    <textarea name="notes" class="form-control" rows="5" maxlength="512"
                        placeholder="Enter notes"></textarea>
                </div>
            </div>
        </span>
        <div class="">
            <a class="collapsed" id="collapsed1" data-toggle="collapse" href="javascript:void(0);"
                data-target="#addmorearea1" aria-expanded="false">
                Custom Fields <i class="fas fa-sort-down align-text-top"></i></a>
        </div>

        <span id="addmorearea1" class="collapse">
            <div>
                <div class="form-group row"><label for="custom_fields_empty_state"
                        class="col-sm-3 col-form-label"></label>
                    <div class="col">
                        <div>Have more information you want to add? You can create custom fields for
                            contacts by going to "Settings"
                            and clicking "Custom Fields".<a class="ml-2"
                                href="#" target="_blank" rel="noopener noreferrer">Learn More</a></div>
                    </div>
                </div>
            </div>
        </span>
        <hr>
        <div class="form-group row">
            <a class="col-sm-2" href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <label for="inputEmail3" class="col-sm-5 col-form-label">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display:none;"></div>
            </label>
            <label for="inputEmail3" class="col-sm-5 col-form-label">
                <button class="btn btn-primary float-right example-button m-1 submitContact" id="submit" name="save" value="saveonly"
                    type="submit">Save Contact</button>

                <button class="btn btn-outline-secondary m-1 float-right submitContact" id="submit" type="submit" name="savandaddcase"
                    value="savandaddcase">Save & Add Case</button>
            </label>
        </div>



    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
        $(".datepicker").datepicker({'todayHighlight': true});
        $("#innerLoader").css('display', 'none');
        $("#innerLoader").hide();
        $("#show_contact_group_text").hide();
        $("#show_contact_group_dropdown").show();
        $('#company_name').select2();
        // $("#countryListData").select2({
        //     placeholder: "Select a country",
        //     theme: "classic",
        //     allowClear: true,
        //     dropdownParent: $("#AddContactModal")
        // });
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
                    required: "Email is required to use the Client Portal.",
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
    
    $('.submitContact').click(function (e) {
    // $('#createNewUser').submit(function (e) {
        var page = $(this).val();
        $(".submitContact").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#createNewUser').valid()) {
            $("#innerLoader").css('display', 'none');
            $('.submitContact').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#createNewUser").serialize();
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/saveAddContact", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&saveandaddcase=yes';
            },
            success: function (res) {
                $("#preloader").hide();
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
                    $('.submitContact').removeAttr("disabled");
                    $('#AddContactModal').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    toastr.success('Your client has been created.', "", {
                    progressBar: !0,
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                    });
                    if (page == 'savandaddcase') {
                        localStorage.setItem("addedClient", res.user_id);
                        loadStep1(res.user_id);
                        $('#AddCaseModel').modal('show');
                    }else{
                        <?php if($adjustment_token !=''){?>
                            var URLS=baseUrl+'/bills/invoices/load_new?court_case_id=&token={{$adjustment_token}}&contact='+res.user_id;
                            window.location.href=URLS;
                        <?php }else{ ?>
                            window.location.reload();
                        <?php } ?>
                    }

                }
            }
        });
    });

    // $(":submit").on('click', function () {
    //     console.log("resources/views/client/addClient.blade.php > 373");
    //     // alert($(this).val());
    //     if ($(this).val() == 'savandaddcase') {
    //         $('#createNewUser').submit(function (e) {
    //             $(".submitContact").attr("disabled", true);
    //             $("#innerLoader").css('display', 'block');
    //             e.preventDefault();

    //             if (!$('#createNewUser').valid()) {
    //                 $("#innerLoader").css('display', 'none');
    //                 $('.submitContact').removeAttr("disabled");
    //                 return false;
    //             }
    //             var dataString = '';
    //             dataString = $("#createNewUser").serialize();
    //             $("#preloader").show();
    //             $.ajax({
    //                 type: "POST",
    //                 url: baseUrl + "/contacts/saveAddContact", // json datasource
    //                 data: dataString,
    //                 beforeSend: function (xhr, settings) {
    //                     settings.data += '&saveandaddcase=yes';
    //                 },
    //                 success: function (res) {
    //                     $("#preloader").hide();
    //                     $("#innerLoader").css('display', 'block');
    //                     if (res.errors != '') {
    //                         $('#showError').html('');
    //                         var errotHtml =
    //                             '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
    //                         $.each(res.errors, function (key, value) {
    //                             errotHtml += '<li>' + value + '</li>';
    //                         });
    //                         errotHtml += '</ul></div>';
    //                         $('#showError').append(errotHtml);
    //                         $('#showError').show();
    //                         $("#innerLoader").css('display', 'none');
    //                         $('.submitContact').removeAttr("disabled");
    //                         $('#AddContactModal').animate({
    //                             scrollTop: 0
    //                         }, 'slow');

    //                         return false;
    //                     } else {
    //                         loadStep1(res.user_id);
    //                         $('#AddCaseModel').modal('show');

    //                     }
    //                 }
    //             });
    //         });
    //     } else {
    //         $('#createNewUser').submit(function (e) {
    //         console.log("resources/views/client/addClient.blade.php > 424");
    //             var me = $(this);
    //             e.preventDefault();

    //             if ( me.data('requestRunning') ) {
    //                 return;
    //             }

    //             me.data('requestRunning', true);
    //              $(".submitContact").attr("disabled", true);
    //             $("#innerLoader").css('display', 'block');
    //             e.preventDefault();

    //             if (!$('#createNewUser').valid()) {
    //                 $("#innerLoader").css('display', 'none');
    //                 $('.submitContact').removeAttr("disabled");
    //                 me.data('requestRunning', false);
    //                 return false;
    //             }
    //             var dataString = '';
    //             dataString = $("#createNewUser").serialize();
    //             $("#preloader").show();
    //             $.ajax({
    //                 type: "POST",
    //                 url: baseUrl + "/contacts/saveAddContact", // json datasource
    //                 data: dataString,
    //                 success: function (res) {
    //                     $("#innerLoader").css('display', 'block');
    //                     if (res.errors != '') {
    //                         $("#preloader").hide();
    //                         $('#showError').html('');
    //                         var errotHtml =
    //                             '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
    //                         $.each(res.errors, function (key, value) {
    //                             errotHtml += '<li>' + value + '</li>';
    //                         });
    //                         errotHtml += '</ul></div>';
    //                         $('#showError').append(errotHtml);
    //                         $('#showError').show();
    //                         $("#innerLoader").css('display', 'none');
    //                         $('.submitContact').removeAttr("disabled");
    //                         // $("#AddContactModal").scrollTop(0);
    //                         $('#AddContactModal').animate({
    //                             scrollTop: 0
    //                         }, 'slow');

    //                         return false;
    //                     } else {
    //                         <?php if($adjustment_token !=''){?>
    //                             var URLS=baseUrl+'/bills/invoices/load_new?court_case_id=&token={{$adjustment_token}}&contact='+res.user_id;
    //                             window.location.href=URLS;
    //                         <?php }else{ ?>
    //                         localStorage.setItem("addedClient", res.user_id);
    //                         toastr.success('Your client has been created.', "", {
    //                             progressBar: !0,
    //                             positionClass: "toast-top-full-width",
    //                             containerId: "toast-top-full-width"
    //                         });
    //                         window.location.reload();
    //                         // $("#response").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Changes saved.</div>');
    //                         // $("#response").show();
    //                         // $("#innerLoader").css('display', 'none');
    //                         // // $('#EditContactModal').modal('hide'); 
    //                         // $('#submit').removeAttr("disabled");                
    //                         <?php } ?>
    //                     }
    //                 },complete: function() {
    //                     me.data('requestRunning', false);
    //                 }
    //             });
    //         });
    //     }
    // });


    $('#collapsed').click(function () {
        $("#collapsed").find('i').toggleClass('fa-sort-up align-bottom').toggleClass(
            'fa-sort-down align-text-top');

    });
    $('#collapsed1').click(function () {
        $("#collapsed1").find('i').toggleClass('fa-sort-up align-bottom').toggleClass(
            'fa-sort-down align-text-top');

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


    function createCompany() {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/createCompany", // json datasource
                data: {
                    "company_name_text": $("#company_name_text").val()
                },
                success: function (res) {
                    if (res.errors != '') {
                        $('#companyError').html('');
                        var errotHtml = '';
                        $.each(res.errors, function (key, value) {
                            errotHtml += value;
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
            url: baseUrl + "/contacts/realoadCompanySelection", // json datasource
            data: {
                "res": res
            },
            success: function (res) {
                $("#show_company_dropdown").html('');
                $("#show_company_dropdown").html(res);
                $("#preloader").hide();
            }
        });
    }

    $('#company_name').on("select2:unselect", function (e) {
        var unselected_value = $('#company_name').val();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/removeCompany", // json datasource
            data: {
                "unselected_value": unselected_value
            },
            success: function (res) {

            }
        });
    }).trigger('change');

    $("#first_name").focus();

    $('input[name=dob]').datepicker({
    'endDate': '+0d',
    'todayHighlight': true
    });

</script>

