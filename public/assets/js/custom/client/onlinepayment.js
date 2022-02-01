// For online payment
$('select[name="online_payment_method"]').change(function() {
    var inputValue = $(this).val();
    if(inputValue != "") {
        var targetBox = $("." + inputValue);
        $(".selectt").not(targetBox).hide();
        $(targetBox).show();
    } else {
        $(".selectt").hide();
    }
});

/**
 * Show credit card emi options as per the amount entered by lawyer
 */
 $(document).on('input , paste', '.online-pay-amount', function (e) {
    var amount = $(this).val().replace(/,/g,'');
    if(amount > 0) {
        $(".emi-li").hide();
        $("input[name=emi_month][value=0]").parents("li").show();
        if(amount >= 1200) {
            $("input[name=emi_month][value=3]").parents("li").show();
            $("input[name=emi_month][value=6]").parents("li").show();
            $("input[name=emi_month][value=8]").parents("li").show();
            $("input[name=emi_month][value=12]").parents("li").show();
        } else if(amount >= 800) {
            $("input[name=emi_month][value=3]").parents("li").show();
            $("input[name=emi_month][value=6]").parents("li").show();
            $("input[name=emi_month][value=8]").parents("li").show();
        } else if(amount >= 600) {
            $("input[name=emi_month][value=3]").parents("li").show();
            $("input[name=emi_month][value=6]").parents("li").show();
        } else if(amount >= 300) {
            $("input[name=emi_month][value=3]").parents("li").show();
        } else {
            $("input[name=emi_month][value=0]").parents("li").show();
        }
    }
});

// CHeck online payment full
$('.payfullOnline').change(function () {
    if ($(this).is(":checked")) {
        $(".online-pay-amount").val($(this).val());
        $(".online-pay-amount").attr("readonly", true);
    } else {
        $(".online-pay-amount").val("");
        $(".online-pay-amount").removeAttr("readonly");
    }
});

// Get selected client detail
$("#online_client_id").change(function() {
    var clientId = $(this).val();
    if(clientId != "") {
        $.ajax({
            url: baseUrl+"/bills/invoices/get/client/detail",
            type: 'GET',
            data: {'client_id': clientId},
            success: function(data) {
                if(data.client != null) {
                    $("#cash_name, #bt_name").val(data.client.client_name);
                    $("#cash_phone_number, #bt_phone_number").val(data.client.mobile_number);
                }
            }
        });
    } else {
        $("#cash_name, #bt_name").val("");
        $("#cash_phone_number, #bt_phone_number").val("");
    }
});

/**
 * From lawyer portal, online payment
 */
$("#pay_online_payment").validate({
    ignore: ":hidden",
    rules: {
        'client_id': {
            required: true,
        },
        amount: {
            required: true,
            maxamount: true,
            minStrict: true
        },
        online_payment_method: {
            required: true
        },
        'name_on_card': {
            required: true,
            validName: true
        },
        'phone_number': {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 13
        },
        'card_number': {
            required: true,
            digits: true
        },
        'expiry_month': {
            required: true,
            validMonth: true
        },
        'expiry_year': {
            required: true,
            validYear: true
        },
        'cvv': {
            required: true,
            validCvv: true
        },
        'name': {
            required: true
        },
        'phone_number': {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 13,
        },
        'bt_name': {
            required: true
        },
        'bt_phone_number': {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 13
        },
    },
    messages: {
        name_on_card: {
            required: "Favor de ingresar esta información.",
        },
        phone_number: {
            required: "Favor de ingresar esta información.",
            number: "Ingrese un número telefónico válido con lada. No use paréntesis.",
        },
        card_number: {
            required: "Favor de ingresar esta información.",
        },
        expiry_month: {
            required: "Favor de ingresar esta información.",
        },
        expiry_year: {
            required: "Favor de ingresar esta información.",
        },
        cvv: {
            required: "Favor de ingresar esta información.",
        },
    },
    errorPlacement: function (error, element) {
        if (element.attr("name") == "expiry_month")
        error.insertAfter(".card-date-error");
        else if (element.attr("name") == "expiry_year")
        error.insertAfter(".card-date-error");
        else if (element.attr("name") == "client_id")
        error.insertAfter(".clientid-error");
        else
        error.insertAfter(element);
    },
});

// For online payment
function onlinePaymentConfirmation() {
    $(".innerLoader").show();
    if (!$('#pay_online_payment').valid()) {
        $(".innerLoader").hide();
        return false;
    } else {
        if($("#online_payment_method").val() == "credit-card") {
            // Conekta Public Key
            Conekta.setPublicKey($("#conekta_key").val());
            Conekta.token.create($('#pay_online_payment')[0], conektaSuccessResponseHandler, conektaErrorResponseHandler);
        } else {
            didOnlinePayment();
        }
        return false;
    }
}
function didOnlinePayment() {
    var f= $('.online-pay-amount').val().replace(/,/g, '');
    var currentAmt = numberWithCommasDecimal(parseFloat(f).toFixed(2));
    swal({
        title: 'Confirm the payment amount of $' + currentAmt + '?',
        text: "",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0CC27E',
        cancelButtonColor: '#FF586B',
        confirmButtonText: 'Confirm Payment',
        cancelButtonText: 'Close',
        confirmButtonClass: 'btn btn-success ml-3',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false,
        reverseButtons: true
    }).then(function () {
        beforeLoader();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/fund/online/payment", // json datasource
            data: $("#pay_online_payment").serialize(),
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                if (res.errors != '') {
                    /* $("#error-alert .error-text").text(res.errors);
                    $("#error-alert").show();
                    $('.scrollbar').animate({
                        scrollTop: $('#error-alert').offset().top - 20 
                    }, 'slow'); */
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> '+res.errors+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').html(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    return false;
                } else {
                    swal('Payment Successful!', res.msg, 'success').then(function(){
                        window.location.reload();
                    });
                    afterLoader(); 
                }
            },
            error: function (jqXHR, exception) {
                afterLoader();
                /* $("#error-alert .error-text").text("Sorry, something went wrong. Please try again later.");
                $("#error-alert").show(); */
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').html(errotHtml);
                $('.showError').show();
            },
        });

    });
}