$('#deleteTimeEntryForm').submit(function (e) {
    beforeLoader();
    e.preventDefault();

    if (!$('#deleteTimeEntryForm').valid()) {
        beforeLoader();
        return false;
    }
    var dataString = '';
    dataString = $("#deleteTimeEntryForm").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/deleteTimeEntryForm", // json datasource
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
                if(res.from=="timesheet"){
                    $("#deleteTimeEntry").modal("hide");
                }else{
                    window.location.reload();
                    afterLoader();
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

$('#deleteExpenseEntryFormUnique').submit(function (e) {
    beforeLoader();
    e.preventDefault();

    if (!$('#deleteExpenseEntryFormUnique').valid()) {
        beforeLoader();
        return false;
    }
    var dataString = '';
    dataString = $("#deleteExpenseEntryFormUnique").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/expenses/deleteExpenseEntryFormUnique", // json datasource
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
$('#deleteInvoiceFormCommon').submit(function (e) {
    beforeLoader();
    e.preventDefault();

    if (!$('#deleteInvoiceFormCommon').valid()) {
        beforeLoader();
        return false;
    }
 
    var dataString = '';
    dataString = $("#deleteInvoiceFormCommon").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/invoices/deleteInvoiceForm2", // json datasource
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

$('#deleteCallLogFormCommon').submit(function (e) {
    beforeLoader();
    e.preventDefault();
    var dataString = '';
    dataString = $("#deleteCallLogFormCommon").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/leads/deleteCallLog", // json datasource
        data: dataString,
        beforeSend: function (xhr, settings) {
            settings.data += '&delete=yes';
        },
        success: function (res) {
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

$('#discardDeleteNotesForm').submit(function (e) {
    beforeLoader();
    e.preventDefault();

    if (!$('#discardDeleteNotesForm').valid()) {
        beforeLoader();
        return false;
    }
    var dataString = '';
    dataString = $("#discardDeleteNotesForm").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/contacts/clients/discardDeleteNote", // json datasource
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

$('#deleteNoteForm').submit(function (e) {
    beforeLoader();
    e.preventDefault();

    if (!$('#deleteNoteForm').valid()) {
        beforeLoader();
        return false;
    }
    var dataString = '';
    dataString = $("#deleteNoteForm").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/contacts/clients/deleteNote", // json datasource
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

$('#deleteTask').submit(function (e) {
    $("#innerLoader1").css('display', 'block');
    e.preventDefault();
    var dataString = $("#deleteTaskForm").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/tasks/deleteTask", // json datasource
        data: dataString,
        success: function (res) {
            $("#innerLoader1").css('display', 'block');
            if (res.errors != '') {
                $('#showError2').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                $.each(res.errors, function (key, value) {
                    errotHtml += '<li>' + value + '</li>';
                });
                errotHtml += '</ul></div>';
                $('#showError2').append(errotHtml);
                $('#showError2').show();
                $("#innerLoader1").css('display', 'none');
                return false;
            } else {
                window.location.reload();
            }
        }
    });
});