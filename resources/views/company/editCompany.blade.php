<form class="editCompany" id="editCompany" name="editCompany" method="POST">
    <span id="response"></span>
    @csrf

    <div class="col-md-12" bladename="resources/views/company/editCompany.blade.php">
        <div id="showError" class="showError" style="display:none"></div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-10">
                <input class="form-control" value="{{$company->id}}" id="id" name="id" type="hidden">
                <input class="form-control" maxlength="255" value="{{ $company->first_name ?? old('company_name') }}"
                    id="company_name" name="company_name" type="text" placeholder="Enter company name">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Email</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="email" maxlength="191" name="email"
                    value="{{ $company->email ?? old('email') }}" type="text" placeholder="Enter Email">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Website</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="website" maxlength="512"
                    value="{{ $companyAdditionalInfo->website ?? old('website') }}" name="website" type="text"
                    placeholder="Enter website">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Main phone</label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="main_phone" maxlength="255"
                    value="{{ $company->mobile_number ?? old('main_phone') }}" name="main_phone"
                    placeholder="Enter main phone">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Fax Number</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="fax_number" maxlength="16"
                    value="{{ $companyAdditionalInfo->fax_number ?? old('fax_number') }}" name="fax_number" type="text"
                    placeholder="Enter fax number">
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Address</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="address" maxlength="255" name="address"
                    value="{{ $company->street ?? old('address') }}" type="text" placeholder="Enter address">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Address2</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="address2" maxlength="255" name="address2"
                    value="{{ $companyAdditionalInfo->address2 ?? old('address2') }}" type="text"
                    placeholder="Enter address2">
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">City, State, Zip Code</label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="city" name="city" maxlength="255"
                    value="{{ $company->city ?? old('city') }}" placeholder="Enter city">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="state" name="state" maxlength="255"
                    value="{{ $company->state ?? old('state') }}" placeholder="Enter state">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="postal_code" maxlength="255"
                    value="{{ $company->postal_code ?? old('postal_code') }}" name="postal_code"
                    placeholder="Enter postal code">
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Country</label>
            <div class="col-md-10 form-group mb-3">
                <select class="form-control country select2" id="country" name="country"
                    data-placeholder="Select Country" style="width: 100%;">
                    <option value="">Select Country</option>
                    <?php foreach($country as $key=>$val){?>
                    <option <?php if($company->country==$val->id){ echo "selected=selected"; }?> value="{{$val->id}}">
                        {{$val->name}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Private Notes</label>
            <div class="col-md-10 form-group mb-3">
                <textarea name="notes" class="form-control" maxlength="512" rows="5"
                    placeholder="Enter notes">{{ $companyAdditionalInfo->notes ?? old('notes') }}</textarea>
            </div>
        </div>
        <div class="">
            <a class="collapsed" id="collapsed"  data-toggle="collapse" href="javascript:void(0);" data-target="#addmorearea" aria-expanded="false">
                Custom Fields  <i class="fas fa-sort-down align-text-top"></i></a>
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
            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit">
                <span class="ladda-label">Save & Close</span></button>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>

    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();
        $(".select2").select2({
            placeholder: "Select a country",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#EditCompany"),
        });
        $('#collapsed').click(function () {
            $("#collapsed").find('i').toggleClass('fa-sort-up align-bottom').toggleClass('fa-sort-down align-text-top');
        });
      
        $(".innerLoader").css('display', 'none');
        $("#editCompany").validate({
            rules: {
                company_name: {
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
    $('#editCompany').submit(function (e) {
      beforeLoader();
        e.preventDefault();

        if (!$('#editCompany').valid()) {
            afterLoader();
            return false;
        }

        var dataString = $("#editCompany").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/saveEditCompany", // json datasource
            data: dataString,
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
                    $('#EditCompany').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    // $("#response").html(
                    //     '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Your company has been updated.</div>'
                    // );
                    // $("#response").show();
                    // $(".innerLoader").css('display', 'none');
                    $('.submit').removeAttr("disabled");
                    window.location.reload();
                }
            },error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $('#EditCompany').animate({
                        scrollTop: 0
                    }, 'slow');

                }
        });
    });

    $("#company_name").focus();

</script>
