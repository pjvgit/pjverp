function sendInvoiceReminder(id,invoice_id) {
    beforeLoader();
    $("#preloader").show();
    $("#sendInvoiceReminderArea").html('');
    $("#sendInvoiceReminderArea").html('<img src="{{LOADER}}""> Loading...');
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/invoices/sendInvoiceReminder", 
        data: {"id": id,"invoice_id":invoice_id},
        success: function (res) {
            if(typeof(res.errors) != "undefined" && res.errors !== null) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
                $("#preloader").hide();
                $("#sendInvoiceReminderArea").html('');
                return false;
            } else {
                afterLoader()
                $("#sendInvoiceReminderArea").html(res);
                $("#preloader").hide();
                return true;
            }
        },error: function (xhr, status, error) {
            $("#preloader").hide();
            $("#sendInvoiceReminderArea").html('');
            $('.showError').html('');
            var errotHtml =
                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showError').append(errotHtml);
            $('.showError').show();
            afterLoader();
        }
    })
}

function payinvoice(id) {
    $('.showError').html('');
    beforeLoader();
    $("#preloader").show();
    $("#payInvoiceArea").html('');
    $("#payInvoiceArea").html('<img src="{{LOADER}}""> Loading...');
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/invoices/payInvoicePopup", 
        data: {'id':id},
        success: function (res) {
            if(typeof(res.errors) != "undefined" && res.errors !== null) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
                $("#preloader").hide();
                $("#payInvoiceArea").html('');
                return false;
            } else {
                afterLoader()
                $("#payInvoiceArea").html(res);
                $("#preloader").hide();
                return true;
            }
        },error: function (xhr, status, error) {
            $("#preloader").hide();
            $("#payInvoiceArea").html('');
            $('.showError').html('');
            var errotHtml =
                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showError').append(errotHtml);
            $('.showError').show();
            afterLoader();
        }
    })
}