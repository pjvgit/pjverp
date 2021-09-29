$(document).ready(function () {
    $(document).on("click", ".edit-minimum-trust", function() {
        $(this).parents('td').find(".setup-input-div").show();
        $(this).parents('td').find(".setup-btn-div").hide();
    });
});

$(document).on("click", ".save-minimum-trust-balance", function() {
    var minBalance = $(this).parents("form.setup-min-trust-balance-form").find("input[name=min_balance]").val();
    minBalance = minBalance.replace(',', '');
    if(minBalance > 0) {
        $(this).parents("form.setup-min-trust-balance-form").find("input[name=min_balance]").val(minBalance);
        var formData = $(this).parents("form.setup-min-trust-balance-form").serialize();
        $.ajax({
            url: baseUrl+"/contacts/clients/save/min/trust/balance",
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
    } else {
        $(this).parents("form.setup-min-trust-balance-form").find("input[name=min_balance]").val("0.00");
    }
});

function trustAllocationList() {
    var clientId = $("#user_id").val();
    $.ajax({
        url: baseUrl+"/contacts/clients/trust/allocation/list",
        type: 'GET',
        data: { client_id: clientId},
        success: function(res) {
            if (res != '') {
                $(".trust-allocations-table tbody").html(res);
            } 
        }
    });
}

$(document).on("click", ".balance-allocation-link", function() {
    var caseId = $(this).attr("data-case-id");
    var clientId = $("#user_id").val();
    $.ajax({
        url: baseUrl+"/contacts/clients/trust/allocation/detail",
        type: 'GET',
        data: { client_id: clientId, case_id: caseId},
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
    amt = amt.replace(",", "");
    if(parseFloat(amt) > parseFloat(totalAmt)) {
        $(this).val((totalAmt > 0) ? totalAmt.toFixed(2) : "0.00");
        $(".unallocate-fund").val("0.00");
    } else {
        var unallocateAmt = totalAmt - amt;
        $(".unallocate-fund").val(unallocateAmt.toFixed(2));
    }

});

$(document).on("click", ".confirm-btn", function() {
    var formData = $(this).parents("form.trust-allocate-form").serialize();

    $.ajax({
        url: baseUrl+"/contacts/clients/save/trust/allocation",
        type: 'POST',
        data: formData,
        success: function(res) {
            if (res.errors != '') {
                return false;
            } else {
                if(res.msg != '') {
                    toastr.success(res.msg, "", {
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                    trustAllocationList();
                }
                $("#trust_allocation_modal").modal("hide");
            }
        }
    });
})