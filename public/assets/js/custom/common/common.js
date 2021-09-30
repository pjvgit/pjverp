
popupNotification();

// Schedule the next request when the current one's complete
setInterval(function() {
    popupNotification();
}, (1000 * 60 * 1));

function popupNotification() {
    $.ajax({
        url: baseUrl+"/get/popup/notification",
        type: 'GET',
        success: function(data) {
            if(data != "") {
                if (typeof $.cookie('is_popup_dismissed') === 'undefined'){
                    $("#notify_modal_body").html(data);
                    $("#notification_popup").modal('show');
                } else {
                    console.log("cookie set");
                }
            } else {
                $("#notification_popup").modal('hide');
            }
        },
        // complete: function() {
        //     // Schedule the next request when the current one's complete
        //     setTimeout(popupNotification(), 1000 * 60 * 5);
        // }
    });
}

/**
 * Snooze popup notification
 */
$(document).on("click", ".snooze-time", function() {
    var snoozeTime = $(this).val();
    var snoozeType = $(this).attr("data-snooze-type");
    var reminderId = $(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-id');
    var reminderType = $(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-type');
    $.ajax({
        url: baseUrl+"/update/popup/notification",
        type: 'GET',
        data: {'reminder_id': reminderId, 'snooze_time': snoozeTime, 'snooze_type': snoozeType, 'reminder_type': reminderType},
        success: function(data) {
            if(data.status == "success") {
                popupNotification();
            }
        },
    });
});

/**
 * Dismiss popup notification
 */
$(document).on("click", ".dismiss-notification", function() {
    var disVal = $(this).val();
    var reminderEventId = [], reminderTaskId = [], solReminderId = [];
    if(disVal == "dismiss-all") {
        $(document).find("#popup_reminder_table tbody tr").each(function() {
            if($(this).attr('data-reminder-type') == "event") {
                reminderEventId.push($(this).attr('data-reminder-id'));
            } else if($(this).attr('data-reminder-type') == "task") {
                reminderTaskId.push($(this).attr('data-reminder-id'));
            } else {
                solReminderId.push($(this).attr('data-reminder-id'));
            }
        })
    } else {
        if($(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-type') == "event") {
            reminderEventId.push($(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-id'));
        } else if($(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-type') == "task") {
            reminderTaskId.push($(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-id'));
        } else {
            solReminderId.push($(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-id'));
        }
    }
    
    $.ajax({
        url: baseUrl+"/update/popup/notification",
        type: 'GET',
        data: {'reminder_event_id': reminderEventId, 'reminder_task_id': reminderTaskId, is_dismiss: 'yes', 'sol_reminder_id': solReminderId},
        success: function(data) {
            if(data.status == "success") {
                popupNotification();
            }
        },
    });
});

$(document).on("click", "#popup_close_btn", function() {
    var date = new Date();
    var minutes = 30;
    date.setTime(date.getTime() + (minutes * 60 * 1000));
    $.cookie('is_popup_dismissed', 'yes', { expires: date });
    $("#notification_popup").modal('hide');
})

$(document).ready(function() {
    // Max amount validation rule
    jQuery.validator.addMethod("maxamount", function(value, element, params){
        value = value.replace(',', '');
        params = $(element).attr("data-max-amount");
        if (parseFloat(value) > parseFloat(params)) {
            return false;  // FAIL validation when matches
        } else {
            return true;   // PASS validation otherwise
        };
    }, function(params, element) {
        return 'Please enter a value less than or equal to ' + $(element).attr("data-max-amount");
    });

    $.validator.addMethod('minStrict', function (value, el, param) {
        value = value.replace(',', '');
        return value > 0;
    }, 'Should be greater than 0');
});

