$(document).ready(function() {
    $("#profile_form").validate({
        rules: {
            "user[first_name]": {
                required: true,      
            },
            "user[last_name]": {
                required: true,      
            },
        },
        submitHandler: function (form) {
            // Prevent double submission
            if (!this.beenSubmitted) {
                this.beenSubmitted = true;
                form.submit();
            }
        },
    });

    $("#chnage_password_form").validate({
        rules: {
            "current_password": {
                required: true,      
            },
            "password": {
                required: true,      
            },
            "password_confirmation": {
                required: true,      
                equalTo: "#new_password"
            },
        },
        submitHandler: function (form) {
            var url = $("#chnage_password_form").attr('data-action'); 
            $(".error").text('');
            $.ajax({
                url: url,
                type: "POST",
                data: $("#chnage_password_form").serialize(),
                success: function( response ) {
                    if(response.success) {
                        $("#chnage_password_form")[0].reset();
                        toastr.success(response.message, "", {
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                    }
                },
                error: function(response) {
                    if(response.responseJSON) {
                        $.each(response.responseJSON.errors, function(ind, item) {
                            $("."+ind+"_error").text(item);
                        });
                    }
                }
            });
            return false;
        },
    });

    $("input[name='auto_logout']").on("change", function() {
        if($(this).is(":checked")) {
            $("#logout_after_div").show();
        } else {
            $("#logout_after_div").hide();
        }
    });
});

/**
 * CHange user email
 */
$("#change_email_form").validate({
    rules: {
        "new_email": {
            required: true,      
            email: true,
        },
        "old_password": {
            required: true,      
        },
    },
    submitHandler: function (form) {
        var url = $("#change_email_form").attr('data-action'); 
        $(".error").text('');
        $.ajax({
            url: url,
            type: "POST",
            data: $("#change_email_form").serialize(),
            success: function( response ) {
                if(response.success) {
                    $("#change_email_form")[0].reset();
                    $("#current_email").text(response.email);
                    toastr.success(response.message, "", {
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                }
            },
            error: function(response) {
                if(response.responseJSON) {
                    $(".error").text('');
                    $.each(response.responseJSON.errors, function(ind, item) {
                        $("."+ind+"_error").text(item);
                    });
                }
            }
        });
        return false;
    },
});