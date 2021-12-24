$(document).ready(function() {
    $(".select2-case").select2({
        placeholder: "Select a case",
        theme: "classic",
        allowClear: true
    });
    $(".select2-bank-account").select2({
        placeholder: "Select a bank account",
        theme: "classic",
        allowClear: true
    });

    $('#depositIntoTrustAccount').on('hidden.bs.modal', function () {
        $('#billingTabTrustHistory').DataTable().ajax.reload(null, false);
    });

    // For apply filter
    $(".apply-filter").on("click", function() {
        $("#billingTabTrustHistory").DataTable().ajax.reload(null, false);
    });
});

/**
 * Deposit into trust
 * @param {*} clientId 
 */
function depositIntoTrust(clientId = null) {
    $('.showError').html('');
    
    $("#preloader").show();
    $("#depositIntoTrustArea").html('');
    $("#depositIntoTrustArea").html('Loading...');
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/dashboard/depositIntoTrust",
        data: {
            "id": null
        },
        success: function (res) {
            if (typeof (res.errors) != "undefined" && res.errors !== null) {
                $('.showError').html('');
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                $.each(res.errors, function (key, value) {
                    errotHtml += '<li>' + value + '</li>';
                });
                errotHtml += '</ul></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
              
                $("#preloader").hide();
                $("#depositIntoTrustArea").html('');
                return false;
            } else {
                afterLoader()
                $("#depositIntoTrustArea").html(res);
                if(clientId != null) {
                    $("#contact").val(clientId).trigger('change');
                    setTimeout(() => {
                        $("#contact").trigger({
                            type: 'select2:select',
                            params: {
                                data: {
                                    id: clientId
                                }
                            }
                        });
                    }, 300);
                    $("#contact_div").hide();
                }
                $("#preloader").hide();
                return true;
            }
        },
        error: function (xhr, status, error) {
            $('.showError').html('');
            var errotHtml =
                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showError').append(errotHtml);
            $('.showError').show();
          
        }
    })
}

/**
 * withdraw from trust
 */
function withdrawFromTrust() {
    $("#preloader").show();
    $("#withdrawFromTrustArea").html('<img src="{{LOADER}}""> Loading...');
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/withdrawFromTrust", 
            data: {"user_id": $("#user_id").val()},
            success: function (res) {
                $("#withdrawFromTrustArea").html(res);
                $("#preloader").hide();
            }
        })
    })
}

/**
 * Refund trust balance
 * @param {*} id 
 */
function RefundPopup(id) {
    $("#preloader").show();
    $("#RefundPopupArea").html('<img src="{{LOADER}}""> Loading...');
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/refundPopup", 
            data: {"user_id": $("#user_id").val(),'transaction_id':id},
            success: function (res) {
                if(res.error && res.msg != '') {
                    var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><ul>';
                    errotHtml += '<li>' + res.msg + '</li></ul></div>';
                    $('#RefundPopupArea').html(errotHtml);
                    $("#preloader").hide();
                    return false;
                } else {
                    $("#RefundPopupArea").html(res);
                    $("#preloader").hide();
                }
            }
        })
    })
}

// For delete payment entry
function deleteEntry(id) {
    $("#deleteEntry").modal("show");
    $("#delete_payment_id").val(id);
}

// For delete payment entry
$('#deletePaymentEntry').submit(function (e) {
    beforeLoader();
    e.preventDefault();

    if (!$('#deletePaymentEntry').valid()) {
        beforeLoader();
        return false;
    }
    var dataString = '';
    dataString = $("#deletePaymentEntry").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/contacts/clients/deletePaymentEntry", // json datasource
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
