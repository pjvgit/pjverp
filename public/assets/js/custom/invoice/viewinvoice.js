// Get invoice payment history
function getInvoicePaymentHistory() {
    $("#preloader").show();
    var invoiceId = $("#invoice_id").val();
    $.ajax({
        type: "GET",
        url:  baseUrl +"/bills/invoices/paymentHistory",
        data: {'id':invoiceId},
        success: function (res) {
            $("#payment_history_div").html(res);
            $("#preloader").hide();
        }
    });
}

/**
 * Update invoice detail
 */
function updateInvoiceDetail() {
    $("#preloader").show();
    var invoiceId = $("#invoice_id").val();
    $.ajax({
        type: "GET",
        // url:  baseUrl +"/bills/invoices/paymentHistory",
        // data: {'id':invoiceId},
        success: function (res) {
            $("#invoice_total_div").html(res);
            getInvoiceActivityHistory();
            getInvoicePaymentHistory();
            refreshAccountHistory();
            $("#preloader").hide();
        }
    });
}

// Get invoice payment history
function getInvoiceActivityHistory() {
    $("#preloader").show();
    var invoiceId = $("#invoice_id").val();
    $.ajax({
        type: "GET",
        url:  baseUrl +"/bills/invoices/activityHistory",
        data: {'id':invoiceId},
        success: function (res) {
            $("#invoice_activity_history_div").html(res);
            $("#preloader").hide();
        }
    });
}

// Get invoice payment history
function refreshAccountHistory() {
    $("#preloader").show();
    var invoiceId = $("#invoice_id").val();
    $.ajax({
        type: "GET",
        url:  baseUrl +"/bills/invoices/refreshAccountHistory",
        data: {'id':invoiceId},
        success: function (res) {
            $("#invoice_account_summary").html(res);
            $("#preloader").hide();
        }
    });
}