<form class="ConfirmAccessFormPopup" id="ConfirmAccessFormPopup" name="ConfirmAccessFormPopup" method="POST">
    @csrf
    <input type="hidden" value="{{$UsersAdditionalInfo['user_id']}}" name="client_id">
    <div class="row">
        <div class="col-md-12" id="confirmAccess">
            <div>
                Invoices can only be shared with contacts enabled for the Client Portal. Would you like to give Client
                Portal access to {{ucfirst($UsersAdditionalInfo['first_name'])}} {{$UsersAdditionalInfo['middle_name']}}
                {{$UsersAdditionalInfo['last_name']}}?
            </div>
            <br>

            <p>Note: Only items explicitly shared with contacts will be accessible. An email with login instructions
                will be automatically sent.</p>
        </div>
    </div>
    <div class="modal-footer">
        <div class="col-md-12  text-center">
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
            <div class="form-group row float-right">
                <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit">Confirm
                    Access</button>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
      
        $('#ConfirmAccessFormPopup').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            var dataString = $("#ConfirmAccessFormPopup").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/changeAccess", // json datasource
                data: dataString,
                success: function (res) {
                    afterLoader();
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
                        reloadRow({{$UsersAdditionalInfo['user_id']}});
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
