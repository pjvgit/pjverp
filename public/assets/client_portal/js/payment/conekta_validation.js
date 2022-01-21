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