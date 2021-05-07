<div id="showError" style="display:none"></div>
<form class="AddFormData" id="AddFormData" name="AddFormData" method="POST">
    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">No Hire Reason</label>
            <div class="col-sm-9">
                <input class="form-control" value="" id="no_hire_reason_name" maxlength="250" name="no_hire_reason_name" type="text"
                    placeholder="No Hire Reason Name">
            </div>
        </div>
        <hr>
        <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit">Save No Hire Reason</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {

        $("#innerLoader").css('display', 'none');
        $("#innerLoader").hide();


        $("#AddFormData").validate({
            rules: {
                no_hire_reason_name: {
                    required: true,
                    minlength: 1,
                    maxlength:60
                }
            },
            messages: {
                no_hire_reason_name: {
                    required: "No Hire Reason is a required field.",
                    minlength: "No Hire Reason must consist of at least 1 characters",
                    maxlength: "No Hire Reason is more than 60 characters"
                }
            }
        });

    });

    $('#AddFormData').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#AddFormData').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#AddFormData").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/lead_setting/saveReason", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
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
                    window.location.reload();
                }
            }
        });
    });

</script>
