<form class="grantAccessPageConfirm" id="grantAccessPageConfirm" name="grantAccessPageConfirm" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <p>
                In order to share this, {{$UserMasterData->first_name}} {{$UserMasterData->last_name}} must have their client portal enabled. Please click "Grant Access" to enable their portal. An email with login instructions will be automatically sent to {{$UserMasterData->first_name}} {{$UserMasterData->last_name}}'s inbox.
            </p>
            <input class="form-control" value="{{$UserMasterData->id}}" maxlength="255" id="client_id" name="client_id"
                type="hidden">
        </div>
    </div>
    
    <div class="justify-content-between modal-footer">
        <div></div>
        <div class="mr-0">
            <button class="btn btn-primary example-button ml-1" id="submit" type="submit" data-style="expand-left">
                Grant access
            </button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        
        $('#grantAccessPageConfirm').submit(function (e) {
            e.preventDefault();
            $(this).find(":submit").prop("disabled", true);
            $("#innerLoader").css('display', 'block');
            if (!$('#grantAccessPageConfirm').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString = $("#grantAccessPageConfirm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveConfirmGrantAccessPage", // json datasource
                data: dataString,
                success: function (res) {
                    $(this).find(":submit").prop("disabled", true);
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
                        $("#innerLoader").css('display', 'none');
                        $("#cleintUSER_"+{{$UserMasterData->id}}).prop('checked',"checked");
                        var userId = $("#grantAccessPageConfirm #client_id").val();
                        $("#loadTaskSection #cleintUSER_"+ userId).attr('data-client_portal_enable', "1");
                        $("#loadTaskSection #attend_user_"+ userId).prop('disabled', false);
                        $("#loadTaskSection #attend_user_"+ userId).removeClass('not-enable-portal');
                        if ($('.lead_client_share_all_users:checked').length == $('.lead_client_share_all_users').length) {
                            $("#SelectAllLeadShare").prop('checked', true);
                        } else {
                            $("#SelectAllLeadShare").prop('checked', false);
                        }
                        $("#loadGrantAccessModal").modal("hide");

                    }
                }
            });
        });
        
    });

</script>
