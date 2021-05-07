<div id="showError" style="display:none"></div>
<form class="AddFormData" id="AddFormData" name="AddFormData" method="POST">
    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Lead Referral Source</label>
            <div class="col-sm-9">
                <input class="form-control" value="" id="lead_referral_source" maxlength="250" name="lead_referral_source" type="text"
                    placeholder="Lead Referral Source Name">
            </div>
        </div>
        <hr>
        <div class="loader-bubble loader-bubble-primary" id="innerLoader3" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit">Save
                Lead Referral Source</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {

        $("#innerLoader").css('display', 'none');
        $("#innerLoader").hide();


        $("#AddFormData").validate({
            rules: {
                lead_referral_source: {
                    required: true,
                    minlength: 1,
                    maxlength:60
                }
            },
            messages: {
                lead_referral_source: {
                    required: "Lead Referral Source is a required field.",
                    minlength: "Lead Referral Source must consist of at least 1 characters",
                    maxlength: "Lead Referral Source is more than 60 characters"
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
            url: baseUrl + "/lead_setting/saveReferalSource", // json datasource
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
