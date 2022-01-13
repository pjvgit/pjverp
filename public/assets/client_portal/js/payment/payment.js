$(document).ready(function() {
    // $('#card_number').mask('0000 0000 0000 0000');
    jQuery('#preloader').fadeOut(1000);
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

$.validator.addMethod("validMonth", function(value, element) {
    return /^01|02|03|04|05|06|07|08|09|10|11|12$/i.test(value);
}, "Ingrese un fecha de vencimiento válida");

$.validator.addMethod("validCvv", function(value, element) {
    return /^[0-9]{3,3}$/i.test(value);
}, "Introduzca un Código de seguridad de la tarjeta válido.");

$.validator.addMethod("validName", function(value, element) {
    return /^[a-z ,.'-]+$/i.test(value);
}, "Ingrese su nombre (utilice únicamente letras, guiones, espacios y comas)");

$.validator.addMethod("validYear", function(value, element) {
    return /^2022|2023|2024|2025|2026|2027|2028|2029|2030|2031|2032|2033|2034|2035|2036|2037|2038|2039|2040$/i.test(value);
}, "Ingrese un fecha de vencimiento válida");

/**
 * Conekta card validation and generate token
 * @param {} token 
 */
var conektaSuccessResponseHandler = function (token) {
    /* Inserta el token_id en la forma para que se envíe al servidor */
    $("#conekta_token_id").val(token.id);
    /* and submit */
    if($("#card_form").length > 0) {
        // Online payment from client portal
        $("#card_form").get(0).submit();
    } else {
        // Online payment from lawyer
        didOnlinePayment();
    }
};
var conektaErrorResponseHandler = function (response) {
    /* Conekta card erros */
    if (response.message === "The cardholder name is invalid.") {
        response.message = "Ingrese su nombre (utilice únicamente letras, guiones, espacios y comas)";
    } else if (response.message === "The card number is invalid.") {
        response.message = "Ingrese un número de tarjeta válido ";
    } else if (response.message === "The CVC (security code) of the card is invalid.") {
        response.message = "Introduzca un Código de seguridad de la tarjeta válido";
    } else if (response.message === "The card has expired.") {
        response.message = "Ingrese un fecha de vencimiento válida";
    } else if (response.message === "The expiration month is invalid.") {
        response.message = "Ingrese un fecha de vencimiento válida";
    } else if (response.message === "A plan cannot contain spaces or special characters, only dashes, underscores and alphanumeric characters are allowed.") {
        response.message = "Se les permite un plan no puede contener espacios o caracteres especiales, únicos guiones, guiones y caracteres alfanuméricos.";
    } else if (response.message === "The token has already been used.") {
        response.message = "El token ya se ha utilizado.";
    } else if (response.message === "A plan cannot contain spaces or special characters, only dashes, underscores and alphanumeric characters are allowed.") {
        response.message = "Un plan no puede contener espacios o caracteres especiales, solamente guiones, guiones y caracteres alfanuméricos son permitidos.";
    } else {
        //response.message = "";
    }
    $("#error-alert .error-text").text(response.message);
    $("#error-alert").show();
    $(".preloader, .innerLoader").css("display", "none");
    $('.scrollbar').animate({
        scrollTop: $('#error-alert').offset().top - 20 //#DIV_ID is an example. Use the id of your destination on the page
    }, 'slow');
};
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
    ignore: "hidden",
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

$("#card_form").submit(function (event) {
    event.preventDefault();
    $("#error-alert").hide();
    if ($(this).valid()) {
        $(".preloader").css("display", "inline-block");
        Conekta.token.create($(this)[0], conektaSuccessResponseHandler, conektaErrorResponseHandler);
    }
    return false;
});