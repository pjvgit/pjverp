function loadDepositIntoCredit(ele) {
    var userId = $(ele).attr("data-auth-user-id");
    var clientId = $(ele).attr("data-client-id");
    $('.showError').html('');
    $("#loadDepositIntoCreditArea").html('');
    // $("#loadDepositIntoCreditArea").html('<img src="{{LOADER}}"> Loading...');
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/dashboard/loadDepositIntoCredit",
        data: {"logged_in_user": userId},
        success: function (res) {
            if (typeof (res.errors) != "undefined" && res.errors !== null) {
                $('.showError').html('');
                $('.showError').html('');
                var errotHtml ='<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                $.each(res.errors, function (key, value) {
                    errotHtml += '<li>' + value + '</li>';
                });
                errotHtml += '</ul></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                $("#loadDepositIntoCreditArea").html('');
                return false;
            } else {
                afterLoader();
                $("#loadDepositIntoCreditArea").html(res);
                if(clientId != undefined && clientId != "") {
                    $("#loadDepositIntoCreditPopup #NonTrustContact").val(clientId).trigger("change");
                    localStorage.setItem("selectedNonTrustUser", clientId);
                    $("#loadDepositIntoCreditPopup").modal("hide");
                    depositIntoNonTrustAccount(localStorage.getItem("selectedNonTrustUser"));
                    $("#depositIntoNonTrustAccount").modal("show");
                }
                $("#preloader").hide();
                return true;
            }
        },
        error: function (xhr, status, error) {
            $('.showError').html('');
            var errotHtml ='<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showError').append(errotHtml);
            $('.showError').show();
            $("#loadDepositIntoCreditArea").html('');

          
        }
    })
}