$(document).ready(function() {
    $('#card_number').mask('0000 0000 0000 0000');
    
    //card validation on input fields
    /* $('#card_form input[type=text]').on('keyup',function(){
        cardFormValidate();
    }); */
});

$("#card_form").validate({
    ignore: [],
    rules: {
        'name_on_card': {
            required: true,
            validName: true
        },
        'phone_number': {
            required: true
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
    errorPlacement: function (error, element) {
        if (element.attr("name") == "expiry_month")
        error.insertAfter(".card-date-error");
        else if (element.attr("name") == "expiry_year")
        error.insertAfter(".card-date-error");
        else
        error.insertAfter(element);
    },
    /* submitHandler: function(form) {
        form.submit();
    } */
});

$.validator.addMethod("validMonth", function(value, element) {
    return /^01|02|03|04|05|06|07|08|09|10|11|12$/i.test(value);
}, "Please enter valid month.");

$.validator.addMethod("validCvv", function(value, element) {
    return /^[0-9]{3,3}$/i.test(value);
}, "Please enter valid number.");

$.validator.addMethod("validName", function(value, element) {
    return /^[a-z ,.'-]+$/i.test(value);
}, "Please enter valid name.");

$.validator.addMethod("validYear", function(value, element) {
    return /^2022|2023|2024|2025|2026|2027|2028|2029|2030|2031|2032|2033|2034|2035|2036|2037|2038|2039|2040$/i.test(value);
}, "Please enter valid year.");

//card number validation
/* $('#card_number').validateCreditCard(function(result){
    if(result.valid){
        $("#card_number").removeClass('required');
    }else{
        $("#card_number").addClass('required');
    }
}); */

function cardFormValidate(){
    var cardValid = 0;
    console.log('dhfgshdf');

    
      
    //card details validation
    var cardName = $("#name_on_card").val();
    var expMonth = $("#expiry_month").val();
    var expYear = $("#expiry_year").val();
    var cvv = $("#cvv").val();
    var regName = /^[a-z ,.'-]+$/i;
    var regMonth = /^01|02|03|04|05|06|07|08|09|10|11|12$/;
    var regYear = /^2017|2018|2019|2020|2021|2022|2023|2024|2025|2026|2027|2028|2029|2030|2031$/;
    var regCVV = /^[0-9]{3,3}$/;
    if (cardValid == 0) {
        $("#card_number").addClass('required');
        $("#card_number").focus();
        return false;
    }else if (!regMonth.test(expMonth)) {
        $("#card_number").removeClass('required');
        $("#expiry_month").addClass('required');
        $("#expiry_month").focus();
        return false;
    }else if (!regYear.test(expYear)) {
        $("#card_number").removeClass('required');
        $("#expiry_month").removeClass('required');
        $("#expiry_year").addClass('required');
        $("#expiry_year").focus();
        return false;
    }else if (!regCVV.test(cvv)) {
        $("#card_number").removeClass('required');
        $("#expiry_month").removeClass('required');
        $("#expiry_year").removeClass('required');
        $("#cvv").addClass('required');
        $("#cvv").focus();
        return false;
    }else if (!regName.test(cardName)) {
        $("#card_number").removeClass('required');
        $("#expiry_month").removeClass('required');
        $("#expiry_year").removeClass('required');
        $("#cvv").removeClass('required');
        $("#name_on_card").addClass('required');
        $("#name_on_card").focus();
        return false;
    }else{
        $("#card_number").removeClass('required');
        $("#expiry_month").removeClass('required');
        $("#expiry_year").removeClass('required');
        $("#cvv").removeClass('required');
        $("#name_on_card").removeClass('required');
        return true;
    }
}