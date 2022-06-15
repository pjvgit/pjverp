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

$('body').on("click", ".add-more-task-reminder", function() {
    var modalId = $(this).parents('div.modal').attr("id");
    // alert(modalId);
    var fieldHTML = '<div class="form-group task-fieldGroup">' + $(".task-fieldGroupCopy").html() +
        '</div>';
    $('#'+modalId).find('.task-fieldGroup:last').after(fieldHTML);
    /* var checkedLen = $('input[name="linked_contact_checked_attend[]"]:checked').length;
    var checkedL = $('input[name="client-share-all"]:checked').length;
    if (checkedLen <= 0 && checkedL <= 0) {
        $(".reminder_user_type option[value='client-lead']").hide();
    } */
    var checkedLen = $('#'+modalId+' input[name="linked_contact_checked_attend[]"]:checked').length;
    if (checkedLen > 0) {
        $("#"+modalId+" select.reminder_user_type").children("option[value='client-lead']").show();
    } else {
        $("#"+modalId+" select.reminder_user_type").children("option[value='client-lead']").hide();
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

// CHange reminder type based on reminder user type
function changeTaskReminderUserType(sel) {
    if (sel.value == 'client-lead') {
        $(sel).parents('div.task-fieldGroup').find(".reminder_type option[value='popup']").hide();
        $(sel).parents('div.task-fieldGroup').find(".reminder_type").val('email');
    } else {
        $(sel).parents('div.task-fieldGroup').find(".reminder_type option[value='popup']").show();
    }
}