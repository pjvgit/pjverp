<div id="showError" style="display:none"></div>
<form class="AddLocationForm" id="AddLocationForm" name="AddLocationForm" method="POST">
    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-10">
                <input class="form-control" value="" maxlength="255" id="location_name" name="location_name" type="text"
                    placeholder="Location name">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Address</label>
            <div class="col-sm-10">
                <input class="form-control" value="" maxlength="255" id="address1" name="address1" type="text"
                    placeholder="Address">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
            <div class="col-sm-10">
                <input class="form-control" value="" maxlength="255" id="address2" name="address2" type="text"
                    placeholder="Address2">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
            <div class="col-sm-4">
                <input class="form-control" value="" maxlength="255" id="city" name="city" type="text"
                    placeholder="City">
            </div>
            <div class="col-sm-3">
                <input class="form-control" value="" maxlength="255" id="state" name="state" type="text"
                    placeholder="State">
            </div> 
            <div class="col-sm-3">
                <input class="form-control" value="" maxlength="255" id="zip" name="zip" type="text"
                    placeholder="Zip">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
            <div class="col-sm-10">
                <select class="form-control country" id="SelectAddCountry" name="country" data-placeholder="Select Country"
                style="width: 100%;">
                <option value="">Select Country</option>
                <?php foreach($country as $key=>$val){?>
                <option value="{{$val->id}}"> {{$val->name}}</option>
                <?php } ?>
            </select>
            </div>
        </div>
        
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
        <div class="justify-content-between modal-footer">
            <div></div>
            <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display:none;"></div>
            <div class="mr-0">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
                </a>
                <button class="btn btn-primary example-button ml-1" id="submit"  type="submit"
                data-style="expand-left">Save </button>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#SelectAddCountry").select2({
            placeholder: "Select a country",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#addLocationModal"),
        });
        $("#innerLoader").css('display', 'none');
        $("#AddLocationForm").validate({
            rules: {
                location_name: {
                    required: true,
                }
            },
            messages: {
                location_name: {
                    required: "Name can't be blank",
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
    $('#AddLocationForm').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#AddLocationForm').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }

        var dataString = $("form").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/saveAddLocationPopup", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader").css('display', 'block');
                if (res.errors != '') {
                    $('#showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger">Sorry, something went wrong. Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
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
                   $("#addLocationModal").modal("hide");
                   toastr.success('Location saved successfully.', "", {
                        progressBar: !0,
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                }
            }
        });
    });

</script>
