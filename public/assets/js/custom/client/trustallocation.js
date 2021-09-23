$(document).ready(function () {
    $(document).on("click", ".edit-minimum-trust", function() {
        $(this).parents('td').find(".setup-input-div").show();
        $(this).parents('td').find(".setup-btn-div").hide();
    });
});

$(document).on("click", ".save-minimum-trust-balance", function() {
    var formData = $(this).parents("form.setup-min-trust-balance-form").serialize();

    $.ajax({
        url: baseUrl+"/contacts/clients/save/min/trust/balance",
        type: 'POST',
        data: formData,
        success: function(res) {
            console.log(res);
            if (res.errors != '') {
                return false;
            } else {
                toastr.success(res.msg, "", {
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                });
                window.location.reload();
            }
        }
    });
})