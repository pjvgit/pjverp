function deleteInvoice(id, redirect_link = '') {
    var invIdArr = [id];
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/invoices/getStaffandClientListOfInvoice", 
        data: {"invoice_id":invIdArr},
        success: function (res) {
            $("#deleteInvoicePopup").modal("show");
            $("#delete_invoice_id").val(id);
            $("#redirect_link").val(redirect_link); 
            $(".showuserlist").show();
            $(".showStaffList").html('').html(res.html); 
        }
    });      
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
                if($("#redirect_link").val() != '') {
                    URL = baseUrl + '/bills/invoices?type=all';
                    window.location.href = URL;
                    }else{
                    window.location.reload();
                }
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