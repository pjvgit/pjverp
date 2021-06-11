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
            $("#preloader").hide();
        }
    });
}