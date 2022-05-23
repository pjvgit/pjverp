<div id="showError" style="display:none"></div>
<form class="saveStep1" id="saveStep1" name="saveStep1" method="POST">
    <input class="form-control" value="{{($UserMaster->id)??''}}" id="id" maxlength="250" name="id" type="hidden">
    <input class="form-control" value="{{($LeadAdditionalInfo->id)??''}}" id="id" maxlength="250" name="user_id" type="hidden">
         <span id="response"></span>
    @csrf
    <div class="col-md-12" bladeFile="resources/views/lead/loadStep1.blade.php">
        <div>
            <h5 class="text-info"></h5>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Name</label>
            <div class="col-sm-4">
                <input class="form-control" value="{{($UserMaster->first_name)??''}}" id="first_name" maxlength="250"
                    name="first_name" type="text" placeholder="First name">
            </div>
            <div class="col-sm-2">
                <input class="form-control" value="{{($UserMaster->middle_name)??''}}" id="middle_name" maxlength="250"
                    name="middle_name" type="text" placeholder="M">
            </div>
            <div class="col-sm-3">
                <input class="form-control" id="last_name" value="{{($UserMaster->last_name)??''}}" maxlength="250"
                    name="last_name" type="text" placeholder="Last name">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Email</label>
            <div class="col-md-9 form-group mb-3">
                <input class="form-control" id="email" name="email" value="{{($UserMaster->email)??''}}" maxlength="191"
                    type="text" placeholder="example@email.com" autocomplete="off">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Enable Client Portal
            </label>
            <div class="col-md-9 form-group mb-10">
                <div class="col-md-9 form-group mb-3">
                    <label class="switch pr-5 switch-success mr-3"><span></span>
                        <input type="checkbox"
                            <?php if($LeadAdditionalInfo->client_portal_enable=="1"){ echo "checked=checked"; }?>
                            name="client_portal_enable" id="client_portal_enable"><span class="slider"></span>
                    </label>

                </div>
                <div>
                    <div>Securely share documents, invoices, and messages with your client. They will receive a welcome
                        email with login instructions. Your client will only have access to items that you explicitly
                        share.</div><a
                        href="https://help.mycase.com/s/article/What-will-my-client-see-when-I-invite-them-to-the-portal"
                        target="_blank" rel="noopener noreferrer">What will my clients see?</a>
                </div>
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Cell phone</label>
            <div class="col-md-9 form-group mb-10">
                <input class="form-control" id="mobile_number" value="{{($UserMaster->mobile_number)??''}}" maxlength="255"
                    name="cell_phone" placeholder="(xxx)-xxx-xxxx">
            </div>

        </div>
        <div class="" id="accordionRightIcon">
            <div class="">
                <a class="collapsed" id="collapsed" data-toggle="collapse" href="javascript:void(0);"
                    data-target="#accordion-item-icons-1" aria-expanded="false">
                    Add More Contact Information <i class="fas fa-sort-down align-text-top"></i></a>
            </div>
            <br>
            <div class="collapse" id="accordion-item-icons-1" style="">
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Work Phone</label>

                    <div class="col-md-3 form-group mb-10">
                        <input class="form-control" id="work_phone" value="{{($UserMaster->work_phone)??''}}"
                            maxlength="255" name="work_phone" placeholder="(xxx)-xxx-xxxx">
                    </div>

                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Home Phone</label>
                    <div class="col-md-3 form-group mb-10">
                        <input class="form-control" id="home_phone" maxlength="255"
                            value="{{($UserMaster->home_phone)??''}}" name="home_phone" placeholder="(xxx)-xxx-xxxx">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Address</label>
                    <div class="col-md-9 form-group mb-3">
                        <input class="form-control" id="address" name="address" maxlength="255"
                            value="{{($UserMaster->street)??''}}" type="text" placeholder="Address">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Address2</label>
                    <div class="col-md-9 form-group mb-3">
                        <input class="form-control" id="address2" name="address2" maxlength="255"
                            value="{{($LeadAdditionalInfo->address2)??''}}" type="text" placeholder="Address 2">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                    <div class="col-md-4 form-group mb-3">
                        <input class="form-control" id="city" name="city" value="{{($UserMaster->city)??''}}"
                            maxlength="255" placeholder="City">
                    </div>
                    <div class="col-md-2 form-group mb-3">
                        <input class="form-control" id="state" name="state" value="{{($UserMaster->state)??''}}"
                            maxlength="255" placeholder="State">
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <input class="form-control" id="postal_code" value="{{($UserMaster->postal_code)??''}}"
                            maxlength="255" name="postal_code" placeholder="Zip code">
                    </div>

                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Country</label>
                    <div class="col-md-9 form-group mb-3">
                        <select class="form-control country" id="country" name="country"
                            data-placeholder="Select Country" style="width: 100%;">
                            <option value="{{($LeadAdditionalInfo->cell_phone)??''}}">Select Country</option>
                            <?php foreach($country as $key=>$val){?>
                            <option <?php if($val->id==$UserMaster->country){ echo "selected=selected"; } ?>
                                value="{{$val->id}}"> {{$val->name}}</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row addmore">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Birthday</label>
                    <div class="col-md-9 form-group mb-3">
                        <input class="form-control datepicker" id="dob" readonly
                            value="{{($LeadAdditionalInfo->dob) ? date('m/d/Y',strtotime($LeadAdditionalInfo->dob)) : convertUTCToUserTimeZone('dateOnly')}}"
                            name="dob" type="text" placeholder="mm/dd/yyyy">

                    </div>
                </div>
                <div class="form-group row addmore">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Driver License, State</label>
                    <div class="col-md-4 form-group mb-3">
                        <input class="form-control" id="driver_license" maxlength="255" name="driver_license"
                            value="{{($LeadAdditionalInfo->driver_license)??''}}" placeholder="Driver license">
                    </div>
                    <div class="col-md-3 form-group mb-3">
                        <input class="form-control" id="driver_state" maxlength="255" name="driver_state"
                            value="{{($LeadAdditionalInfo->license_state)??''}}" placeholder="License state">
                    </div>

                </div>
            </div>
        </div>
        <div class="" id="accordionRightIconCustom">
            <div class="">
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">

                        <a class="collapsed2" id="collapsed2" data-toggle="collapse" href="javascript:void(0);"
                            data-target="#accordion-item-icons-2" aria-expanded="false">
                            Custom Fields <i class="fas fa-sort-down align-text-top"></i></a>
                    </label>
                    <div class="col-md-9 form-group mb-10">
                        <div class="collapse" id="accordion-item-icons-2" style="">

                            <div>Have more information you want to add? You can create custom fields for
                                contacts by going to "Settings"
                                and clicking "Custom Fields".<a class="ml-2"
                                    href="#" target="_blank" rel="noopener noreferrer">Learn More</a>
                                </div>
                        </div>
                    </div>
                </div>
            </div> <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display: none;"></div>
            <div class="form-group row float-right">
                <button class="btn btn-primary ladda-button example-button m-1" id="submit"
                    type="submit">Continue</button>
            </div>
            <br>
            <br>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        localStorage.setItem("case_id","");
        $('#dateadded').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true
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

        $("#innerLoader").css('display', 'none');
        $("#innerLoader").hide();
        $("#show_contact_group_text").hide();
        $("#show_contact_group_dropdown").show();


        // $("#saveStep1").validate({
        /* var caseStep1ValidateOptions = {
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
            },
            submitHandler: function() {    
                var dataString = '';
                dataString = $("#saveStep1").serialize();
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/leads/saveStep1", // json datasource
                    data: dataString,
                    beforeSend: function (xhr, settings) {
                        settings.data += '&saveandaddcase=yes';
                    },
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
                            $('#submit').removeAttr("disabled");
                            loadStep2(res);
                        }
                    }
                });
                return false;
            }
        }; */
        $("#show_company_text").hide();

    });

    /* $('#saveStep1').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        // e.preventDefault();

        if (!$('#saveStep1').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#saveStep1").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/saveStep1", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&saveandaddcase=yes';
            },
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
                    $('#submit').removeAttr("disabled");
                    loadStep2(res);
                }
            }
        });
    }); */

    $('#collapsed').click(function () {
        $("#collapsed").find('i').toggleClass('fa-sort-up align-bottom').toggleClass(
            'fa-sort-down align-text-top');

    });
    $('#collapsed2').click(function () {
        $("#collapsed2").find('i').toggleClass('fa-sort-up align-bottom').toggleClass(
            'fa-sort-down align-text-top');

    });

   
    /* function loadStep2(res) {
        console.log(res);
       
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/loadStep2", // json datasource
            data: {
                "id": res.user_id,
                "case_id":localStorage.getItem("case_id"),
                "_token": "{{ csrf_token() }}"
            },
            success: function (res) {
                $('#smartwizard').smartWizard("next");
                $("#innerLoader").css('display', 'none');
                $("#step-2").html(res);
                $("#preloader").hide();
            }
        })

        return false;
    } */
    $('input.decimal').keyup(function (event) {
        // skip for arrow keys
        if (event.which >= 37 && event.which <= 40) return;
        // format number
        $(this).val(function (index, value) {
            if (value.split('.').length > 2)
                return value = value.replace(/\.+$/, "");
            return value.replace(/[^0-9\.]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    });

</script>
