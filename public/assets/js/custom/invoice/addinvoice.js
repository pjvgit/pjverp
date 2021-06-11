/**
 * Open flat fee delete modal
 * @param {*} id 
 */
function openFlatFeeDelete(id) {
    $("#flat_fee_delete_existing_dialog").modal("show");
    $("#flat_fee_delete_entry_id").val(id);
}

/**
 * Delete flat fee
 * @param {*} action 
 */
function actionFlatFeeEntry(action) {
    $('#removeExistingFlatFeeEntryForm').submit(function (e) {

        beforeLoader();
        e.preventDefault();

        if (!$('#removeExistingFlatFeeEntryForm').valid()) {
            beforeLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#removeExistingFlatFeeEntryForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/deleteFlatFeeEntry", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&delete=yes&action=' + action;
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
}

/**
 * Open time entry delete modal
 */
function openTimeDelete(id) {
    $("#delete_existing_dialog").modal("show");
    $("#delete_time_entry_id").val(id);
}

/**
 * Delete time entry
 * @param {*} action 
 */
function actionTimeEntry(action) {
    $('#removeExistingEntryForm').submit(function (e) {

        beforeLoader();
        e.preventDefault();

        if (!$('#removeExistingEntryForm').valid()) {
            beforeLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#removeExistingEntryForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/deleteTimeEntry", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&delete=yes&action=' + action;
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
}

/**
 * Open expense entry delete modal
 */
function openExpenseDelete(id) {
    $("#delete_expense_existing_dialog").modal("show");
    $("#delete_expense_entry_id").val(id);
}

/**
 * Delete expense entry
 */
 function actionExpenseEntry(action) {
    $('#removeExistingExpenseEntryForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#removeExistingExpenseEntryForm').valid()) {
            beforeLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#removeExistingExpenseEntryForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/deleteExpenseEntry", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&delete=yes&action=' + action;
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
}

/**
 * Save non billable check of flat fee/time entry/expense
 */
$(document).on("change", ".nonbillable-check", function() {
    var id = $(this).val();
    var checkType = $(this).attr("data-check-type");
    var isCheck = "yes";
    if($(this).is(":checked")) {
        isCheck = "no";
    }
    $.ajax({
        url: baseUrl+"/bills/invoices/save/nonbillable/check",
        type: "GET",
        data: {id: id, check_type: checkType, is_check: isCheck},
        success: function(data) {
            console.log(data);
        }
    });
});