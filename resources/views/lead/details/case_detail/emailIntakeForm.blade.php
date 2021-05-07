<form class="EmailForm" id="EmailForm" name="EmailForm" method="POST">
    <span id="response"></span>
    @csrf
    <div class="showError" style="display:none"></div>

    <input type="hidden" id="form_id" value="{{$caseIntakeForm['id']}}" name="form_id">
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Intake Form:</label>
            <div class="col-sm-9">
                <label for="inputEmail3" class="col-sm-3 col-form-label">
                    <a href="{{BASE_URL}}cform/{{$caseIntakeForm['form_unique_id']}}">{{$intakeForm['form_name']}}</a></label>
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Send To:</label>
            <div class="col-sm-9">
                <input class="form-control" value="{{$leadData['email']}}" id="email_address" maxlength="250" name="email_address" type="text"
                    placeholder="example@email.com">
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Subject:</label>
            <div class="col-sm-9">
                <input class="form-control" value="Intake Form from {{$firmData->firm_name}}" id="email_suubject"
                    maxlength="250" name="email_suubject" type="text" placeholder="">
                <p class="form-text text-muted mb-1">This is the subject of the email that will be sent to the client
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Message:</label>
            <div class="col-sm-9">
                <textarea class="form-control" id="email_message" maxlength="250"
                    name="email_message">Please click the button below and fill out this intake form at your earliest convenience.</textarea>
                <p class="form-text text-muted mb-1">Your email will include a button to access the Intake Form.</p>
            </div>
        </div>

        <div class="justify-content-between modal-footer">
            <div><a href="#" target="_blank">What will my client see?</a></div>
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
            <div>
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                </a>
                <button class="btn btn-primary ladda-button example-button submit" id="submit" type="submit">Send</button>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $(".innerLoader").css('display', 'none');
        $(".innerLoader").hide();
        $("#EmailForm").validate({
            rules: {

                email_address: {
                    required: true,
                    email: true
                }
            },
            messages: {

                email_address: {
                    required: "You must provide a valid email address.",
                    email: "A valid email address is required."
                },
            }
        });
        $('#EmailForm').submit(function (e) {
            $(".submit").attr("disabled", true);
            $(".innerLoader").css('display', 'block');
            e.preventDefault();

            if (!$('#EmailForm').valid()) {
                $(".innerLoader").css('display', 'none');
                $('.submit').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#EmailForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/sendEmailIntakeFormPC", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    $(".innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        $(".innerLoader").css('display', 'none');
                        $('.submit').removeAttr("disabled");
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (jqXHR, exception) {
                    $(".innerLoader").css('display', 'none');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                },
            });
        });

    });

</script>
