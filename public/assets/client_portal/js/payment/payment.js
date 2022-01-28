$(document).ready(function() {
    // $('#card_number').mask('0000 0000 0000 0000');
    jQuery('#preloader').fadeOut(1000);
    $.validator.setDefaults({ignore: ":hidden"});
});

$("#card_form").validate({
    ignore: [],
    rules: {
        'name_on_card': {
            required: true,
            validName: true
        },
        'phone_number': {
            required: true,
            number: true,
        },
        'card_number': {
            required: true
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
        else
        error.insertAfter(element);
    },
});

$("#card_form").submit(function (event) {
    event.preventDefault();
    $("#error-alert").hide();
    if ($(this).valid()) {
        $(".preloader").css("display", "inline-block");
        Conekta.token.create($(this)[0], conektaSuccessResponseHandler, conektaErrorResponseHandler);
    }
    return false;
});

/**
 * Cash payment
 */
$("#cash_pay_form").validate({
    ignore: [],
    rules: {
        'name': {
            required: true,
        },
        'phone_number': {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 13
        },
    },
    messages: {
        name: {
            required: "Por favor ingresa tu nombre",
        },
        phone_number: {
            required: "Por favor ingresa tu número telefónico",
            number: "Ingrese un número telefónico válido con lada. No use paréntesis.",
            minlength: "El número telefónico debe tener al menos 10 números",
            maxlength: "El número telefónico no puede tener más de 13 números"
        },
    },
    errorPlacement: function (error, element) {
        error.insertAfter(element);
    },
    submitHandler: function(form) {
        form.submit();
    }
});

$(document).on('keypress , paste', '.phone-number', function (e) {
    if (/^[0-9]+$/.test(e.key)) {
        $('.number').on('input', function () {
            e.target.value = numberSeparator(e.target.value);
        });
    } else {
        e.preventDefault();
        return false;
    }
});

/**
 * Bank tansfer payment
 */
$("#bank_pay_form").validate({
    ignore: [],
    rules: {
        'bt_name': {
            required: true,
        },
        'bt_phone_number': {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 13
        },
    },
    messages: {
        bt_name: {
            required: "Por favor ingresa tu nombre",
        },
        bt_phone_number: {
            required: "Por favor ingresa tu número telefónico",
            number: "Ingrese un número telefónico válido con lada. No use paréntesis.",
            minlength: "El número telefónico debe tener al menos 10 números",
            maxlength: "El número telefónico no puede tener más de 13 números"
        },
    },
    errorPlacement: function (error, element) {
        error.insertAfter(element);
    },
    submitHandler: function(form) {
        form.submit();
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
            required: true
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

/**
 * Show credit card emi options as per the amount entered by lawyer
 */
$(document).on('input , paste', '.online-pay-amount', function (e) {
    var amount = $(this).val().replace(/,/g,'');
    if(amount > 0) {
        console.log(amount);
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