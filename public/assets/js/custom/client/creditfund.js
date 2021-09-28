function loadDepositIntoCredit(ele) {
    var userId = $(ele).attr("data-auth-user-id");
    var clientId = $(ele).attr("data-client-id");
    var caseId = $(ele).attr("data-case-id");
    $('.showError').html('');
    $("#loadDepositIntoCreditArea").html('');
    // $("#loadDepositIntoCreditArea").html('<img src="{{LOADER}}"> Loading...');
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/dashboard/loadDepositIntoCredit",
        data: {"logged_in_user": userId, case_id: caseId},
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

// Withdraw credit fund
function withdrawFromCredit() {
    $("#preloader").show();
    var clientId = $("#client_id").val();
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/withdrawFromCredit", 
            data: {"user_id": clientId},
            success: function (res) {
                $("#withdrawFromCreditArea").html(res);
                $("#preloader").hide();
            }
        })
    })
}

// Refund credit fund
function RefundCreditPopup(id) {
    $("#preloader").show();
    var clientId = $("#client_id").val();
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/credit/refundPopup", 
            data: {"user_id": clientId,'transaction_id':id},
            success: function (res) {
                $("#RefundPopupArea").html(res);
                $("#preloader").hide();
            }
        })
    })
}

// For delete credit fund
function deleteCreditEntry(id) {
    $("#deleteCreditHistoryEntry").modal("show");
    $("#delete_credit_id").val(id);
}

// For delete credit fund warning popup
function deleteCreditWarningPopup(clientName = null) {
    swal({
        title: 'Cannot Delete',
        text: "Deleting this transaction would result in a negative balance for "+clientName+". Please review transaction history and any related deposits for this client.",
    });
}

// Delete credit fund entry
$('#deleteCreditHistoryEntryForm').submit(function (e) {
    beforeLoader();
    e.preventDefault();

    if (!$('#deleteCreditHistoryEntryForm').valid()) {
        beforeLoader();
        return false;
    }
    var dataString = '';
    dataString = $("#deleteCreditHistoryEntryForm").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/contacts/clients/deleteCreditHistoryEntry", // json datasource
        data: dataString,
        beforeSend: function (xhr, settings) {
            settings.data += '&delete=yes';
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
                window.location.reload();
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

/**
 * Export pdf popup
 */
function exportCreditPDFpopup() {
    $("#export_credit_start_date").val("");
    $("#export_credit_end_date").val("");
    $('.showError').html('');
    $("#export_credit_popup").modal("show");
}

$(document).ready(function() {
    $('#export_credit_start_date').datepicker({
        'format': 'm/d/yyyy',
        'autoclose': true,
        'todayBtn': "linked",
        'clearBtn': true,
        'todayHighlight': true
    }).on("changeDate", function(selected) {
        var minDate = new Date(selected.date.valueOf());
        $('#export_credit_end_date').datepicker('setStartDate', minDate);
        $('#export_credit_end_date').datepicker('setDate', minDate);
    });

    $('#export_credit_end_date').datepicker({
        'format': 'm/d/yyyy',
        'autoclose': true,
        'todayBtn': "linked",
        'clearBtn': true,
        'todayHighlight': true
    });
})

/**
 * Export pdf file
 */
$('#exportCreditpopupForm').submit(function (e) {
            
    beforeLoader();
    e.preventDefault();
    var dataString = '';
    dataString = $("#exportCreditpopupForm").serialize();
    var clientId = $("#client_id").val();
    $.ajax({
        type: "POST",
        url:  baseUrl +"/contacts/clients/export/credit/history", // json datasource
        data: dataString,
        beforeSend: function (xhr, settings) {
            settings.data += '&export=yes&user_id='+clientId;
        },
         success: function (res) {
            beforeLoader();
            if (res.errors != '') {
                $('.showError').html('');
                var errotHtml ='<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                $.each(res.errors, function (key, value) {
                    errotHtml += '<li>' + value + '</li>';
                });
                errotHtml += '</ul></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
                return false;
            } else {
                $("#preloader").hide();
                $("#export_credit_popup").modal('hide');
                Object.assign(document.createElement("a"), {
                    target: "_blank",
                    href: res.url
                }).click();
                // window.open(url);
                afterLoader();
                
                
            }
        },
        error: function (xhr, status, error) {
            $('.showError').html('');
            var errotHtml ='<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showError').append(errotHtml);
            $('.showError').show();
            afterLoader();
        }
    });
});