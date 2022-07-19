$(document).ready(function() {
    $(document).on("click", ".edit-minimum-trust", function() {
        $(this).parents('td').find(".setup-input-div").show();
        $(this).parents('td').find(".setup-btn-div").hide();
    });

    trustAllocationList();
});

$(document).on("click", ".save-minimum-trust-balance", function() {
    var minBalance = $(this).parents("form.setup-min-trust-balance-form").find("input[name=min_balance]").val();
    minBalance = minBalance.replace(/,/g, '');
    // if (minBalance > 0) {
        $(this).parents("form.setup-min-trust-balance-form").find("input[name=min_balance]").val(minBalance);
        var formData = $(this).parents("form.setup-min-trust-balance-form").serialize();
        $.ajax({
            url: baseUrl + "/contacts/clients/save/min/trust/balance",
            type: 'POST',
            data: formData,
            success: function(res) {
                if (res.errors != '') {
                    return false;
                } else {
                    toastr.success(res.msg, "", {
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                    trustAllocationList();
                }
            }
        });
    /* } else {
        $(this).parents("form.setup-min-trust-balance-form").find("input[name=min_balance]").val("0.00");
    } */
});

function trustAllocationList() {
    var clientId = $("#user_id").val();
    $.ajax({
        url: baseUrl + "/contacts/clients/trust/allocation/list",
        type: 'GET',
        data: { client_id: clientId },
        success: function(res) {
            if (res != '') {
                $(".trust-allocations-table tbody").html(res);
            }
        }
    });
}

$(document).on("click", ".balance-allocation-link", function() {
    var caseId = $(this).attr("data-case-id");
    var clientId = $(this).attr("data-user-id");
    var page = $(this).attr("data-page");
    console.log(page);
    $.ajax({
        url: baseUrl + "/contacts/clients/trust/allocation/detail",
        type: 'GET',
        data: { client_id: clientId, case_id: caseId, page: page },
        success: function(res) {
            if (res != '') {
                $("#trust_allocation_modal_body").html(res);
            }
        }
    });
});

$(document).on("input", ".allocate-fund", function() {
    var totalAmt = $(this).attr("data-total-amt");
    var amt = $(this).val();
    amt = (!amt.trim()) ? 0 : amt.replace(",", "");
    // if(amt > 0) {
        // $('.ta-amt-error').text('');
    if (parseFloat(amt) > parseFloat(totalAmt)) {
        $(this).val((parseFloat(totalAmt) > 0) ? parseFloat(totalAmt).toFixed(2) : "0.00");
        $(".unallocate-fund").val("0.00");
    } else {
        var unallocateAmt = parseFloat(totalAmt) - parseFloat(amt);
        // $(".unallocate-fund").val(parseFloat(unallocateAmt).toFixed(2));
        $(".unallocate-fund").val(unallocateAmt.toLocaleString());
    }
    /* } else {
        $(this).val('0');
        $('.ta-amt-error').text('Should be greater than or equal to 0');
    } */
});

$(document).on("click", ".confirm-btn", function() {
    var formData = $(this).parents("form.trust-allocate-form").serialize();
    var page = $("#page").val();
    var amt = $(".trust-allocate-form .allocate-fund").val();
    if(parseFloat(amt) >= 0) {
        $('.ta-amt-error').text('');
    beforeLoader();
    $.ajax({
        url: baseUrl + "/contacts/clients/save/trust/allocation",
        type: 'POST',
        data: formData,
        success: function(res) {
            afterLoader();
            if (res.errors != '') {
                return false;
            } else {
                $("#trust_allocation_modal").modal("hide");
                if (page == "invoice_payment") {
                    reallocateContact();
                } else {
                    if (res.msg != '') {
                        toastr.success(res.msg, "", {
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        // trustAllocationList();
                        window.location.reload();
                    }
                }
            }
        }
    });
    } else {
        $('.ta-amt-error').text('Should be greater than or equal to 0');
    }
});

$('#trust_allocation_modal').on('hidden.bs.modal', function() {
    $('body').toggleClass("modal-open");
});

// Validation to check value should be >= 0
$.validator.addMethod('minStrictNumber', function(value, el, param) {
    value = value.replace(/,/g, '');
    console.log(value);
    return value >= 0;
}, 'Should be greater than or equal to 0');