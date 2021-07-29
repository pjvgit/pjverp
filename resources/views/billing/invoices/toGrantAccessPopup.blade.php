<form class="grantAccessModalForm" id="grantAccessModalForm" name="grantAccessModalForm" method="POST">
    @csrf
    <input type="hidden" value="{{$UsersAdditionalInfo['user_id']}}" name="client_id">

    <div class="row">
        <div class="col-md-12" id="confirmAccess">
            <div>
                In order to share this invoice, {{ucfirst($UsersAdditionalInfo['first_name'])}}
                {{$UsersAdditionalInfo['middle_name']}} {{$UsersAdditionalInfo['last_name']}} must have their client portal enabled,
                which requires entering their email address. Please enter {{ucfirst($UsersAdditionalInfo['first_name'])}}
                {{$UsersAdditionalInfo['middle_name']}} {{$UsersAdditionalInfo['last_name']}}'s email address and click 'Grant Access'
                to enable their portal. An email with login instructions will be automatically sent.

            </div>
            <br>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-3 col-form-label text-right ">Email</label>
                <div class="col-9 form-group mb-3">
                    <input id="client_email" name="email" class="form-control" value="" placeholder="name@domain.com">
                </div>
            </div>
            <br>
            <p>Note: Only items explicitly shared with contacts will be accessible. An email with login
                instructions will be automatically sent.</p>
        </div>
    </div>
    <div class="modal-footer">
        <div class="col-md-12  text-center">
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
            </div>
            <div class="form-group row float-right">
                <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit">Grant
                    Access</button>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#grantAccessModalForm").validate({
            rules: {
                email: {
                    required: true
                }
            },
            messages: {
                email: {
                    required: "Email is required to use the Client Portal"
                }
            }
        });
        $('#grantAccessModalForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#grantAccessModalForm').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#grantAccessModalForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/graantAccess", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=no';
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
                        $('#grantAccessModal').modal("hide");
                        $("#portalAccess_"+{{$UsersAdditionalInfo['user_id']}}).prop('checked', true);
                        $("#portalAccess_"+{{$UsersAdditionalInfo['user_id']}}).attr('pe', 1);
                        reloadRow({{$UsersAdditionalInfo['user_id']}});

                        // window.location.reload();
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

    });

</script>
