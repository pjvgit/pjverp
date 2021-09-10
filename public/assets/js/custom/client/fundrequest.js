$(document).ready(function() {
    $('#addEmailtouser').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        // if (!$('#addEmailtouser').valid()) {
        //     beforeLoader();
        //     return false;
        // }
        var dataString = '';
        dataString = $("#addEmailtouser").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/addEmailtouser", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&delete=yes';
            },
            success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                    $('.showErrorOver').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showErrorOver').append(errotHtml);
                    $('.showErrorOver').show();
                    afterLoader();
                    return false;
                } else {
                    // $("#addRequestFund #contact").val(res.user_id);
                    $("#addEmailToClient").modal("hide");
                    refreshDetail();
                    afterLoader();
                }
            },
            error: function (xhr, status, error) {
            $('.showErrorOver').html('');
            var errotHtml =
                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showErrorOver').append(errotHtml);
            $('.showErrorOver').show();
            afterLoader();
        }
        });
    });
})