<form class="CloneForm" id="CloneForm" name="CloneForm" method="POST">
    <span id="response"></span>
    @csrf
    <div class="showError" style="display:none"></div>
   
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">New intake form name:</label>
            <div class="col-sm-9">
                <input class="form-control" value="Clone:{{$intakeForm['form_name']}}" id="form_name" maxlength="250" name="form_name" type="text" placeholder="Form name">            
                
                <input class="form-control" value="{{$formId}}" id="form_id" maxlength="250" name="form_id" type="hidden" placeholder="Form name">
            </div>
        </div>
        <hr>
        <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit">Clone</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $("#innerLoader").css('display', 'none');
        $("#innerLoader").hide();
        $("#CloneForm").validate({
            rules: {
                form_name: {
                    required: true,
                    minlength: 1,
                    maxlength:255
                }
            },
            messages: {
                form_name: {
                    required: "Name is a required field.",
                    minlength: "Name is too sort (minimum is 1 character)",
                    maxlength: "Name is too long (maximum is 255 characters)"
                }
            }
        });
        $('#CloneForm').submit(function (e) {
            e.preventDefault();
            $(this).find(":submit").prop("disabled", true);
            $("#innerLoader").css('display', 'block');

            if (!$('#CloneForm').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#CloneForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/intake_form/cloneSaveIntakeForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    $("#innerLoader").css('display', 'block');
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
                        $("#innerLoader").css('display', 'none');
                        $('#submit').removeAttr("disabled");
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (jqXHR, exception) {
                    $("#innerLoader").css('display', 'none');
                    $('.showError').html('');
                    var errotHtml ='<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                },
            });
        });

    });

</script>
