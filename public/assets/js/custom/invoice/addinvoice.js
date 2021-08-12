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
                    $("#FlatFee-"+$("#flat_fee_delete_entry_id").val()).remove();
                    invoice_entry_nonbillable_flat();
                    $("#flat_fee_delete_existing_dialog").modal("hide");
                    afterLoader();
                    // window.location.reload();
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

function invoice_entry_nonbillable_flat(){
    var sum = 0;
    $('input[name="flat_fee_entry[]"]').each(function (i) {
        if (!$(this).is(":checked")) {
            // do something if the checkbox is NOT checked
            var g = parseFloat($(this).attr("priceattr"));
            sum += g;            
        }
    });
    $(".flat_fee_table_total").html(sum);
    $("#flat_fee_sub_total_text").val(sum);
    $('.flat_fee_table_total').number(true, 2);

    $(".flat_fee_total_amount").html(sum);
    $('.flat_fee_total_amount').number(true, 2);
    recalculate();
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

// For delete/remove adjustment entry
function openAdjustmentDelete(id) {
    $("#delete_flatfee_existing_dialog_bbox").modal("show");
    $("#delete_flatefees_existing_dialog").val(id);
}

// For delete/remove adjustment entry
function actionAdjustmentEntry(action) {
    $('#removeExistingFlateFeesForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#removeExistingFlateFeesForm').valid()) {
            beforeLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#removeExistingFlateFeesForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/deleteAdustmentEntry", // json datasource
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

$(document).ready(function () {
    /**
     * For due date
     */
    $('.datepicker').datepicker({
        'format': 'm/d/yyyy',
        'autoclose': true,
        'todayBtn': "linked",
        'clearBtn': true,
        startDate: "dateToday",
        'todayHighlight': true
    });
    $('#bill_due_date').on("changeDate", function() {
        $("#bill_payment_terms").val("0");
    });
});

function addDaysToDate(bill_invoice_date, days){
    var now = new Date(bill_invoice_date);
    now.setDate(now.getDate()+days);
    console.log(now);
    return (now.getMonth() + 1).toString().padStart(2, '0') + '/' + now.getDate().toString().padStart(2, '0') + '/' + now.getFullYear();
}
/**
 * Check payment terms and due date
 */
 function paymentTerm(){
        
    var setDate='';
    var selectdValue = $("#bill_payment_terms option:selected").val();
    var bill_invoice_date=$("#bill_invoice_date").val();
    if(selectdValue==0 || selectdValue==1){
        $('#bill_due_date').datepicker("update", bill_invoice_date);
    }else if(selectdValue==2){
        // CheckIn = $("#bill_invoice_date").datepicker('getDate');
        CheckOut = addDaysToDate(bill_invoice_date, 15)
        $('#bill_due_date').datepicker('update', CheckOut)/* .focus() */;
       
    }else if(selectdValue==3){
        // CheckIn = $("#bill_invoice_date").datepicker('getDate');
        CheckOut = addDaysToDate(bill_invoice_date, 30)
        $('#bill_due_date').datepicker('update', CheckOut)/* .focus() */;
       
    }else if(selectdValue==4){
        // CheckIn = $("#bill_invoice_date").datepicker('getDate');
        CheckOut = addDaysToDate(bill_invoice_date, 60)
        $('#bill_due_date').datepicker('update', CheckOut)/* .focus() */;
    }

    if(selectdValue=="5"){
        $("#automated_reminders").prop("checked",false);/*  */
        $("#automated_reminders").prop("disabled",true);
        $('#bill_due_date').val('');
    }else{
        $("#automated_reminders").prop("disabled",false);
        $("#automated_reminders").prop("checked",true);
    }
 
}

$(document).on("change", ".forwarded-invoices-check", function() {
    var lineTotal = 0.00;
    var finaltotal = $("#final_total_text").val();
    $(".forwarded-invoices-check").each(function(ind, item) {
        var dueAmt = 0.00;
        if($(this).is(":checked")) {
            dueAmt = $(this).attr("data-due-amount");
            $("#unpaid_amt_"+$(this).val()).text(dueAmt);
        } else {
            $("#unpaid_amt_"+$(this).val()).text("");
        }
        lineTotal += parseFloat(dueAmt);
    });
    var due = $(this).attr("data-due-amount");
    if($(this).is(":checked")) {
        finaltotal = parseFloat(finaltotal) + parseFloat(due);
    } else {
        finaltotal = parseFloat(finaltotal) - parseFloat(due);
    }
    $("#unpaid_invoice_total").text(lineTotal.toFixed(2));
    $("#forwarded_total_amount").text(lineTotal.toFixed(2));
    $("#final_total").text(finaltotal.toFixed(2));
    $("#final_total_text").val(finaltotal.toFixed(2));
    $(".total-to-apply").text('$'+totalAppliedAmt.toFixed(2));
});

// Apply trust/credit balance input validation
$('.apply-trust-amt, .credit-trust-amt').keypress(function(event) {
    if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
        event.preventDefault();
    }
});

// Apply trust balance
$(".apply-trust-amt").on("focusout", function() {
    var amt = $(this).val();
    var totalAmt = parseFloat($(this).parents('tr').find(".trust-balance").text());
    if(amt != '') {
        var remainAmt = totalAmt - parseFloat(amt);
        $(this).parents('tr').find(".remain-trust-balance").text(remainAmt.toFixed(2));
    } else {
        $(this).parents('tr').find(".remain-trust-balance").text(totalAmt.toFixed(2));
    }

    var totalAppliedAmt = 0;
    $(".apply-trust-amt").each(function() {
        var amt = $(this).val();
        if(amt != '') {
            totalAppliedAmt = parseFloat(totalAppliedAmt) + parseFloat(amt);
        }
    });
    var applied = parseFloat($(".total-to-apply").text()) + totalAppliedAmt;
    $(".total-to-apply").text(applied.toFixed(2));
    $(".invoice-total-amount").text('$'+$(".final_total").text());
});

// Apply credit balance
$(".apply-credit-amt").on("focusout", function() {
    var amt = $(this).val();
    var totalAmt = parseFloat($(this).parents('tr').find(".credit-balance").text());
    if(amt != '') {
        var remainAmt = totalAmt - parseFloat(amt);
        $(this).parents('tr').find(".remain-credit-balance").text(remainAmt.toFixed(2));
    } else {
        $(this).parents('tr').find(".remain-credit-balance").text(totalAmt.toFixed(2));
    }

    var totalAppliedAmt = 0;
    $(".apply-credit-amt").each(function() {
        var amt = $(this).val();
        if(amt != '') {
            totalAppliedAmt = parseFloat(totalAppliedAmt) + parseFloat(amt);
        }
    });
    var applied = parseFloat($(".total-to-apply").text()) + totalAppliedAmt;
    $(".total-to-apply").text(applied.toFixed(2));
    $(".invoice-total-amount").text('$'+$(".final_total").text());
});