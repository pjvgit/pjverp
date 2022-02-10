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

function deleteInvoice(id) {
    $("#deleteInvoicePopup").modal("show");
    $("#delete_invoice_id").val(id);
}

$('#deleteInvoiceForm').submit(function (e) {
    beforeLoader();
    e.preventDefault();

    if (!$('#deleteInvoiceForm').valid()) {
        beforeLoader();
        return false;
    }
 
    var dataString = '';
    dataString = $("#deleteInvoiceForm").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/invoices/deleteInvoiceForm", // json datasource
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