$(document).on("change", ".load-client-reminder, .load-client-reminder-all", function() {
    if ($(this).is(":checked")) {
        var lastNo = $(".task-fieldGroup").length;
        $(".reminder_user_type option[value='client-lead']").show();
        $('#' + lastNo).trigger("change");
    } else {
        var checkedLen = $('input[name="linked_contact_checked_attend[]"]:checked').length;
        var checkedL = $('input[name="client-share-all"]:checked').length;
        if (checkedLen <= 0 && checkedL <= 0) {
            $(".reminder_user_type option[value='client-lead']:selected").parents('.task-fieldGroup').remove();
            $(".reminder_user_type option[value='client-lead']").hide();
        }
    }
});

$(".add-more-task-reminder").click(function() {
    var fieldHTML = '<div class="form-group task-fieldGroup">' + $(".task-fieldGroupCopy").html() +
        '</div>';
    $(document).find('#CreateTask .task-fieldGroup:last').after(fieldHTML);
    var checkedLen = $('input[name="linked_contact_checked_attend[]"]:checked').length;
    var checkedL = $('input[name="client-share-all"]:checked').length;
    if (checkedLen <= 0 && checkedL <= 0) {
        $(".reminder_user_type option[value='client-lead']").hide();
    }
});
$('#CreateTask').on('click', '.remove', function() {
    var $row = $(this).parents('.task-fieldGroup').remove();
});

function loadDefaultTaskReminder() {
    $.ajax({
        type: "POST",
        url: baseUrl + "/tasks/loadDefaultTaskReminder",
        success: function(res) {
            $('body').find('#CreateTask .task-fieldGroup:last').after(res);
        }
    });
}