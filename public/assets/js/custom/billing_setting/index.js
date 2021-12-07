/**
 * Edit invoice preferences
*/
$(document).on("click", ".edit-billing-defaults", function() {
    var settingId = $(this).attr("data-setting-id");
    var url = $(this).attr("data-url");
    $.ajax({
        url: url,
        type: 'GET',
        data: {setting_id: settingId},
        success: function(data) {
            $("#firm-billing-defaults").html(data);
        }
    })
});

// Save invoice preferences
$(document).on("click", "#save_billing_settings", function() {
    $.ajax({
        url: $("#billing_defaults_form").attr("action"),
        type: 'POST',
        data: $("#billing_defaults_form").serialize(),
        success: function(data) {
            $("#firm-billing-defaults").html(data);
            toastr.success('Preferences saved', "", {
                positionClass: "toast-top-full-width",
                containerId: "toast-top-full-width"
            });
        }
    })
});

// Cancel invoice preferences changes
$(document).on("click", "#cancel_edit_billing_settings", function() {
    var settingId = $(this).attr("data-setting-id");
    var url = $(this).attr("data-url");
    swal({
        title: 'Confirmation',
        text: "Are you sure you want to cancel your changes?",
        // type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0CC27E',
        cancelButtonColor: '#FF586B',
        confirmButtonText: 'Ok',
        cancelButtonText: 'Cancel',
        confirmButtonClass: 'btn btn-primary ml-5',
        cancelButtonClass: 'btn btn-default',
        reverseButtons: true,
        buttonsStyling: false
        }).then(function () {
            $(function () {
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {setting_id: settingId},
                    success: function(data) {
                        $("#firm-billing-defaults").html(data);
                    }
                });
            });
        }, function (dismiss) {
        
    });
});

// Edit invoice customization settings
$(document).on("click", "#edit-idp-btn", function() {
    var customizeId = $(this).attr("data-customize-id");
    var url = $(this).attr("data-url");
    $.ajax({
        url: url,
        type: 'GET',
        data: {customize_id: customizeId},
        success: function(data) {
            $("#invoice-customization-defaults").html(data);
        }
    })
});

// Save invoice customization setting
$(document).on("click", "#save_customiz_settings", function() {
    $.ajax({
        url: $("#customization_form").attr("action"),
        type: 'POST',
        data: $("#customization_form").serialize(),
        success: function(data) {
            $("#invoice-customization-defaults").html(data);
            toastr.success('Preferences saved', "", {
                positionClass: "toast-top-full-width",
                containerId: "toast-top-full-width"
            });
        }
    })
});

// Cancel invoice customization saving
$(document).on("click", "#cancel_customiz_btn", function() {
    var customizeId = $(this).attr("data-customize-id");
    var url = $(this).attr("data-url");
    $.ajax({
        url: url,
        type: 'GET',
        data: {customize_id: customizeId},
        success: function(data) {
            $("#invoice-customization-defaults").html(data);
            toastr.success('Preferences saved', "", {
                positionClass: "toast-top-full-width",
                containerId: "toast-top-full-width"
            });
        }
    });
});

/**
 * Online payment settings
 */
$(document).on("change", "#is_accept_online_payment", function() {
    if($(this).is(":checked")) {
        $("#key_div").show();
    } else {
        $("#key_div").hide();
    }
});

// $("#billing_payment_form").validate({
var paymentValidateOptions = {
    ignore: [],
    rules: {
        'public_key': {
            required: "#is_accept_online_payment:checked"
        },
        'private_key': {
            required: "#is_accept_online_payment:checked"
        }
    },
}

// Save invoice payment preferences
$(document).on("click", "#save_payment_settings", function() {
    $("#billing_payment_form").validate(paymentValidateOptions)
    if($("#billing_payment_form").valid()) {
        $.ajax({
            url: $("#billing_payment_form").attr("action"),
            type: 'POST',
            data: $("#billing_payment_form").serialize(),
            success: function(data) {
                $("#firm-payment-defaults").html(data);
                toastr.success('Preferences saved', "", {
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                });
            }
        });
    }
});