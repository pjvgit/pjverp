<form class="UpdateOfficeForm" id="UpdateOfficeForm" name="UpdateOfficeForm" method="POST">
    <span id="response"></span>
    @csrf
    <input class="form-control" value="{{$FirmAddress['id']}}" id="id" maxlength="250" name="id" type="hidden">
    <div class="showError" id="showError" style="display:none"></div>
    <div class="col-md-12">
        <div class="form-group row  pb-2">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Office Name
            </label>
            <div class="col-sm-8">
                <input class="form-control" value="{{$FirmAddress['office_name']}}" id="office_name" maxlength="250"
                    name="office_name" type="text" placeholder="Office name">
            </div>
        </div>
        <div class="form-group row pb-2">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Primary Office?</label>
            <div class="col-md-9 form-group mb-3">
                <div class="form-group form-check">
                    <input class="form-check-input" type="checkbox"
                        <?php if($FirmAddress['is_primary']=="yes"){ echo "checked=checked";} ?>
                        <?php if($FirmAddress['is_primary']=="yes"){  echo "disabled=disabled";} ?>
                        name="primary_office" id="office_primary">
                    <label class="form-check-label" for="office_primary">This is our primary office</label>
                </div>
            </div>
        </div>
        <div class="form-group row  pb-2">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Main phone</label>
            <div class="col-md-9 form-group mb-10">
                <input class="form-control" id="cell_phone" value="{{$FirmAddress['main_phone']}}" maxlength="255"
                    name="main_phone" placeholder="(xxx)-xxx-xxxx">
            </div>

        </div>
        <div class="form-group row  pb-2">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Fax Line</label>
            <div class="col-md-9 form-group mb-10">
                <input class="form-control" id="cell_phone" value="{{$FirmAddress['fax_line']}}" maxlength="255"
                    name="fax_line" placeholder="(xxx)-xxx-xxxx">
            </div>

        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Address <br>
                <small class="text-muted">This address will be displayed on invoices and merge fields for document
                    templates.</small>
            </label>
            <div class="col-md-9 form-group mb-3">
                <input class="form-control" id="address" name="address" maxlength="255"
                    value="{{$FirmAddress['address']}}" type="text" placeholder="Street">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label"></label>

            <div class="col-md-9 form-group mb-3">
                <input class="form-control" id="postal_code" value="{{$FirmAddress['apt_unit']}}" maxlength="255"
                    name="apt_unit" placeholder="Apt/Unit">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
            <div class="col-md-4 form-group mb-3">
                <input class="form-control" id="city" name="city" value="{{$FirmAddress['city']}}" maxlength="255"
                    placeholder="City">
            </div>
            <div class="col-md-2 form-group mb-3">
                <input class="form-control" id="state" name="state" value="{{$FirmAddress['state']}}" maxlength="255"
                    placeholder="State">
            </div>
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="postal_code" value="{{$FirmAddress['postal_code']}}" maxlength="255"
                    name="postal_code" placeholder="Zip code">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
            <div class="col-md-9 form-group mb-3">
                <select class="form-control country select2" id="country" name="country"
                    data-placeholder="Select Country" style="width: 100%;">
                    <option value="">Select Country</option>
                    <?php foreach($country as $key=>$val){?>
                    <option <?php if($FirmAddress['country']==$val->id){ echo "selected=selected    ";} ?>
                        value="{{$val->id}}"> {{$val->name}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
    </span>
    <hr>
    <div class="loader-bubble loader-bubble-primary innerLoader" style="display: none;"></div>

    <div class="form-group row  float-right">
        <div class="col-md-12 form-group ">
            <a href="#">
                <button class="btn btn-secondary  mr-3" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button mr-3 submit" id="submit" type="submit">Update
                Office</button>
        </div>
    </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $("#country").select2({
            placeholder: "Select a country",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#editOffice"),
        });
        $("#UpdateOfficeForm").validate({
            rules: {
                office_name: {
                    required: true,
                    minlength: 2
                },
                main_phone: {
                    number: true
                },
                fax_line: {
                    number: true
                }
            },
            messages: {
                office_name: {
                    required: "Please enter office name",
                    minlength: "Office name must consist of at least 2 characters"
                },
                main_phone: {
                    number: "Main number is invalid"
                },
                fax_line: {
                    number: "Fax phone is invalid"
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
    $('#UpdateOfficeForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#UpdateOfficeForm').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#UpdateOfficeForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/firms/UpdateNewFirm", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
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

    $("#office_name").focus();

</script>
