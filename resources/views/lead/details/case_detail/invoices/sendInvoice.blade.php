<form class="EmailForm" id="EmailForm" name="EmailForm" method="POST">
    <span id="response"></span>
    @csrf
    <div class="showError" style="display:none"></div>
    <input type="hidden" id="invoice_id" value="{{$invoice_id}}" name="invoice_id">
    <div class="col-md-12" bladefile="resources/views/lead/details/case_detail/invoices/sendInvoice.blade.php">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Send By:</label>
            <div class="col-sm-9">
                <select class="form-control country" id="sent_by" name="sent_by" style="width: 30%;">
                    <option value="email">Email</option>
                    <!-- <option value="sms">Text(SMS)</option> -->
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Send To:</label>
            <div class="col-sm-9">
                <input class="form-control" value="{{$userData->email}}" id="email_address" maxlength="250"
                    name="email_address" type="text" placeholder="example@email.com">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Subject:</label>
            <div class="col-sm-9">
                <input class="form-control" value="Invoice from  {{$firmData->firm_name}}" id="email_subject"
                    maxlength="250" name="email_subject" type="text" placeholder="">

            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Message:</label>
            <div class="col-sm-9">
                <textarea class="form-control" id="email_message" maxlength="250" rows="10"
                    name="email_message">Click the link below to review your invoice.</textarea>
            </div>
        </div>

        <div class="justify-content-between modal-footer">
            <div><a href="#" target="_blank">What will my potential client see?</a></div>
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
            <div>
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                </a>
                <button class="btn btn-primary ladda-button example-button submit" id="submit"
                    type="submit">Send</button>
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
                url: baseUrl + "/leads/sendInvoice", // json datasource
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
