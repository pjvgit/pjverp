

/* (function popupNotification() {
    $.ajax({
        url: baseUrl+"/get/popup/notification",
        type: 'GET',
        success: function(data) {
            if(data != "") {
                $("#notify_modal_body").html(data);
                $("#notification_popup").modal('show');
            }
        },
        complete: function() {
            // Schedule the next request when the current one's complete
            setTimeout(popupNotification, 1000 * 60 * 5);
        }
    });
})(); */

/**
 * Snooze popup notification
 */
$(document).on("change", ".snooze-time", function() {
    var snoozeTime = $(this).val();
    var snoozeType = $(this).find(':selected').attr("data-snooze-type");
    var reminderId = $(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-id');
    $.ajax({
        url: baseUrl+"/update/popup/notification",
        type: 'GET',
        data: {'reminder_id': reminderId, 'type': 'event', 'snooze_time': snoozeTime, 'snooze_type': snoozeType},
        success: function(data) {
            console.log(data);
            if(data.status == "success") {
                popupNotification();
            }
        },
    });
});

/**
 * Dismiss popup notification
 */
$(document).on("change", ".dismiss-notification", function() {
    var disVal = $(this).val();
    var reminderIds = [$(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-id')];
    if(disVal == "dismiss-all") {
        $(document).find("#popup_reminder_table tbody tr").each(function() {
            reminderIds.push($(this).attr('data-reminder-id'));
        })
    }
    $.ajax({
        url: baseUrl+"/update/popup/notification",
        type: 'GET',
        data: {'reminder_id': reminderIds, 'type': 'event', is_dismiss: 'yes'},
        success: function(data) {
            console.log(data);
            if(data.status == "success") {
                popupNotification();
            }
        },
    });
});