<div id="showError" style="display:none"></div>
<form class="CasePracticeArea" id="CasePracticeArea" name="CasePracticeArea" method="POST">
    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-10">
                <input class="form-control" value="{{$CasePracticeArea->id}}" id="id" name="id" type="hidden">
                <input class="form-control" value="{{$CasePracticeArea->title}}" maxlength="255" id="area_name"
                    name="area_name" type="text" placeholder="Enter name">
            </div>
        </div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit"
                data-style="expand-left"><span class="ladda-label">Update Practice Area</span><span
                    class="ladda-spinner"></span><span class="ladda-spinner"></span></button>
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
        $("#CasePracticeArea").validate({
            rules: {
                area_name: {
                    required: true,
                    minlength: 2
                }
            },
            messages: {
                area_name: {
                    required: "Name can't be blank",
                    minlength: "Name must consist of at least 2 characters"
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
    $('#CasePracticeArea').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#CasePracticeArea').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }

        var dataString = $("form").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/saveEditPracticeArea", // json datasource
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
                    return false;
                } else {
                    window.location.reload();
                    $('#submit').removeAttr("disabled");
                }
            }
        });
    });

</script>
