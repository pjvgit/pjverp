
var allowSubmit = false;
/**
 * Chnage primary account
 */
$(".change-account").on("click", function() {
    $("#select-options").removeClass("d-none");
    $("#standard-options").addClass("d-none");
    $("#change_account").val("yes");
    allowSubmit = false;
});

/**
 * Cancel to change primary account
 */
$(".cancel-change-account").on("click", function() {
    $("#select-options").addClass("d-none");
    $("#standard-options").removeClass("d-none");
    $("#change_account").val("no");
    allowSubmit = true;
});

// $(".launchpad-select-user-form").on("submit", function(e) {
$(document).on("click", ".launchpad", function(e) {
    var changeAccount = $("#change_account").val();
    if(changeAccount == 'yes' && !allowSubmit) {
        e.preventDefault();
        $.ajax({
            url: baseUrl+"/selectuser/primary/account",
            type: "POST",
            data: $(this).parents('form').serialize(),
            success: function(data) {
                console.log(data);
                if(data.success && data.view != '') {
                    $(".account-container").html(data.view);
                    $(".cancel-change-account").trigger("click");
                }
            }
        });
        return false;
    } else {
        $(this).parents('form').submit();
    }
});