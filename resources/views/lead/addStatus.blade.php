<div id="showError" style="display:none"></div>
<form class="AddStatus" id="AddStatus" name="AddStatus" method="POST">
    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Status</label>
            <div class="col-sm-9">
                <input class="form-control" value="" id="status_name" maxlength="250" name="status_name" type="text"
                    placeholder="Status name">
            </div>
        </div>
        <hr>
        <div class="loader-bubble loader-bubble-primary" id="innerLoader3" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit">Save
                Status</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {

        $("#innerLoader").css('display', 'none');
        $("#innerLoader").hide();


        $("#AddStatus").validate({
            rules: {
                status_name: {
                    required: true,
                    minlength: 1,
                    maxlength:60
                }
            },
            messages: {
                status_name: {
                    required: "Status is a required field.",
                    minlength: "Status must consist of at least 1 characters",
                    maxlength: "Status is more than 60 characters"
                }
            }
        });

    });

    $('#AddStatus').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#AddStatus').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#AddStatus").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/saveStatus", // json datasource
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
