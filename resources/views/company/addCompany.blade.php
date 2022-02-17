<div id="showError" style="display:none"></div>
<form class="editForm" id="editForm" name="editForm" method="POST">
    <span id="response"></span>
    @csrf
    <?php 
    if($case_id!=''){?>
    <input class="form-control" value="{{$case_id}}" id="case_id" name="case_id" type="hidden" placeholder="M">
    <?php } ?>
    <div class="col-md-12" bladefile="resources/views/company/addCompany.blade.php">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-10">
                <input class="form-control" maxlength="255" value="" id="company_name" name="company_name" type="text"
                    placeholder="Enter company name">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Email</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="email" maxlength="191" name="email" value="" type="text"
                    placeholder="Enter Email">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Website</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="website" maxlength="512" value="" name="website" type="text"
                    placeholder="Enter website">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Main phone</label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="main_phone" value="" maxlength="255" name="main_phone"
                    placeholder="Enter main phone">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Fax Number</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="fax_number" value="" maxlength="16" name="fax_number" type="text"
                    placeholder="Enter fax number">
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
            <label for="inputEmail3" class="col-sm-2 col-form-label">City, State, Zip Code</label>
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
                <select class="form-control countryDown" id="countryDown" name="country" style="width: 100%;">
                    <option value="">Select Country</option>
                    <?php foreach($country as $key=>$val){?>
                    <option value="{{$val->id}}"> {{$val->name}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Provate Notes</label>
            <div class="col-md-10 form-group mb-3">
                <textarea name="notes" class="form-control" rows="5" maxlength="512"
                    placeholder="Enter notes"></textarea>
            </div>
        </div>
        <div class="">
            <a class="collapsed" id="collapsed" data-toggle="collapse" href="javascript:void(0);"
                data-target="#addmorearea" aria-expanded="false">
                Custom Fields <i class="fas fa-sort-down align-text-top"></i></a>
        </div>
        <span id="addmorearea" class="collapse">
            <div>
                <div class="form-group row">
                    <label for="custom_fields_empty_state" class="col-sm-3 col-form-label"></label>
                    <div class="col">
                        <div>Have more information you want to add? You can create custom fields for contacts by going
                            to "Settings" and clicking "Custom Fields".<a class="ml-2" href="#" target="_blank"
                                rel="noopener noreferrer">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </span>
        <hr>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit">
                <span class="ladda-label">Save & Close</span></button>
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
        $("#innerLoader").css('display', 'none');

        // $('#country').select2();
        $(".countryDown").select2({
            placeholder: "Select a country",
            theme: "classic",
            allowClear: true,
            selectOnClose: true,
            dropdownParent: $("#addCompanyModel"),
        });
        $('#collapsed').click(function () {
            $("#collapsed").find('i').toggleClass('fa-sort-up align-bottom').toggleClass(
                'fa-sort-down align-text-top');
        });
        
        $("#editForm").validate({
            rules: {
                company_name: {
                    required: true,
                    minlength: 2
                },
                email: {
                    required: true,
                    email: true
                },
                website: {
                    url: false
                },
                home_phone: {
                    number: true
                },
                fax_number: {
                    number: true
                }
            },
            messages: {
                company_name: {
                    required: "Please enter company name",
                    minlength: "Company name must consist of at least 2 characters"
                },
                email: {
                    required: "Please enter email",
                    minlength: "Email is not formatted correctly"
                },
                website: {
                    url: "Please enter valid website url"
                },
                home_phone: {
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
    });
    $('#editForm').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#editForm').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }

        var dataString = $("#editForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/saveAddCompany", // json datasource
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
                    $('#addCompanyModel').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    // $("#response").html(
                    //     '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Your company has been added.</div>'
                    // );
                    // $("#response").show();
                    // $("#innerLoader").css('display', 'none');
                    $('#submit').removeAttr("disabled");
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
                $('#addCompanyModel').animate({
                        scrollTop: 0
                }, 'slow');
            }
        });
    });
    $("#company_name").focus();

</script>
