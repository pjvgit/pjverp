<form class="deleteIeIntakeForm" id="deleteIeIntakeForm" name="deleteIeIntakeForm" method="POST">
    <div id="showError" style="display:none"></div>
    @csrf
    <input class="form-control" id="id" value="{{$primary_id}}" name="id" type="hidden">
    <div class=" col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label">
               <p>Are you sure you want to delete <b>{{$intakeForm['form_name']}}</b>? This form will no longer be accessible.</p>
            </label>
        </div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button"
                    data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit">
                <span class="ladda-label">Yes, Delete</span>
            </button>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $('button').attr('disabled', false);
        $("#preloader").hide();

        $('#deleteIeIntakeForm').submit(function (e) {
            $(".submit").attr("disabled", true);
            $(".innerLoader").css('display', 'block');
            e.preventDefault();

            if (!$('#deleteIeIntakeForm').valid()) {
                $(".innerLoader").css('display', 'none');
                $('.submit').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString =$("#deleteIeIntakeForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/saveDeleteIntakeFormFromList", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                    $(".innerLoader").css('display', 'block');
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
                        $(".innerLoader").css('display', 'none');
                        $('.submit').removeAttr("disabled");
                        return false;
                    } else {
                        window.location.reload();
                    }
                }
            });
        });
    });

</script>
