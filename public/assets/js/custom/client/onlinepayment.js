$('.payfullOnline').change(function () {
    if ($(this).is(":checked")) {
        $(".online-pay-amount").val($(this).val());
        $(".online-pay-amount").attr("readonly", true);
    } else {
        $(".online-pay-amount").val("");
        $(".online-pay-amount").removeAttr("readonly");
    }
});