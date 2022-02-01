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
        /* if (element.attr("name") == "expiry_month")
        error.insertAfter(".card-date-error");
        else if (element.attr("name") == "expiry_year")
        error.insertAfter(".card-date-error");
        else */ if (element.attr("name") == "cvv")
        error.insertAfter(".cvv-error");
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