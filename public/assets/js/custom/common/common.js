popupNotification();

// Schedule the next request when the current one's complete
setInterval(function() {
    popupNotification();
}, (1000 * 60 * 1));

function popupNotification() {
    $.ajax({
        url: baseUrl + "/get/popup/notification",
        type: 'GET',
        success: function(data) {
            if (data != "") {
                if (typeof $.cookie('is_popup_dismissed') === 'undefined') {
                    $("#notify_modal_body").html(data);
                    $("#notification_popup").modal('show');
                } else {
                    console.log("cookie set");
                }
            } else {
                $("#notification_popup").modal('hide');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            if (xhr.status == 401) {
                swal({
                    type: 'warning',
                    title: 'Session alert!',
                    html: 'Your session has expired!. You will be redirected to login page.',
                }).then(function(result) {
                    window.location.reload();
                });

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
        url: baseUrl + "/update/popup/notification",
        type: 'GET',
        data: { 'reminder_id': reminderId, 'snooze_time': snoozeTime, 'snooze_type': snoozeType, 'reminder_type': reminderType },
        success: function(data) {
            if (data.status == "success") {
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
    var reminderEventId = [],
        reminderTaskId = [],
        solReminderId = [];
    if (disVal == "dismiss-all") {
        $(document).find("#popup_reminder_table tbody tr").each(function() {
            if ($(this).attr('data-reminder-type') == "event") {
                reminderEventId.push($(this).attr('data-reminder-id'));
            } else if ($(this).attr('data-reminder-type') == "task") {
                reminderTaskId.push($(this).attr('data-reminder-id'));
            } else {
                solReminderId.push($(this).attr('data-reminder-id'));
            }
        })
    } else {
        if ($(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-type') == "event") {
            reminderEventId.push($(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-id'));
        } else if ($(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-type') == "task") {
            reminderTaskId.push($(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-id'));
        } else {
            solReminderId.push($(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-id'));
        }
    }

    $.ajax({
        url: baseUrl + "/update/popup/notification",
        type: 'GET',
        data: { 'reminder_event_id': reminderEventId, 'reminder_task_id': reminderTaskId, is_dismiss: 'yes', 'sol_reminder_id': solReminderId },
        success: function(data) {
            if (data.status == "success") {
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
    jQuery.validator.addMethod("maxamount", function(value, element, params) {
        value = value.replace(',', '');
        params = $(element).attr("data-max-amount");
        if (parseFloat(value) > parseFloat(params)) {
            return false; // FAIL validation when matches
        } else {
            return true; // PASS validation otherwise
        };
    }, function(params, element) {
        return 'Please enter a value less than or equal to ' + $(element).attr("data-max-amount");
    });

    $.validator.addMethod('minStrict', function(value, el, param) {
        value = value.replace(',', '');
        return value > 0;
    }, 'Should be greater than 0');
});

// Start smart timer Modules
localStorage.setItem("counter", "0");

let hour = 0;
let minute = 0;
let seconds = 0;
let totalSeconds = 0;

let intervalId = null;
$(".startTimer").on('click', function() {
    $(".timerCounter").show();
    if (localStorage.getItem("counter") > 0) {
        totalSeconds = localStorage.getItem("counter");
        if (localStorage.getItem("pauseCounter") == 'no') {
            intervalId = setInterval(timerstart, 1000);
        } else {
            timerstart();
        }
    } else {
        $.ajax({
            url: baseUrl + "/createTimer",
            type: 'POST',
            data: {},
            success: function(data) {
                if (data.status == "success") {
                    $("#smart_timer_id").val(data.smart_timer_id);
                    intervalId = setInterval(timerstart, 1000);
                }
            },
        });
    }
});
console.log("localStorage > counter :" + localStorage.getItem("counter"));
console.log("localStorage > pauseCounter :" + localStorage.getItem("pauseCounter"));

if (localStorage.getItem("counter") > 0) {
    totalSeconds = localStorage.getItem("counter");
    if (localStorage.getItem("pauseCounter") == 'no') {
        intervalId = setInterval(timerstart, 1000);
    } else {
        timerstart();
    }
}

$(document).on("click", "#startCounter", function() {
    resumeTimer();
    if (!intervalId) {
        localStorage.setItem("pauseCounter", 'no');
        intervalId = setInterval(timerstart, 1000);
    }
    $(".timerAction").removeClass("fa-play").addClass("fa-pause");
    $(".timerAction").attr('id', 'pauseCounter');
});

$(document).on("click", "#pauseCounter", function() {
    pauseTimer();
    if (intervalId) {
        clearInterval(intervalId);
        localStorage.setItem("pauseCounter", 'yes');
        intervalId = null;
    }
    $(".timerAction").removeClass("fa-pause").addClass("fa-play");
    $(".timerAction").attr('id', 'startCounter');
});

function timerstart() {

    $(".js-timer-root .text-nowrap .time-status").html("");
    if (localStorage.getItem("pauseCounter") == 'no') {
        $(".js-timer-root .text-nowrap").html(" 1 &nbsp; <i class='timer-status-dot active'></i> &nbsp;");
    } else {
        $(".js-timer-root .text-nowrap").html(" 1 &nbsp; <i class='timer-status-dot error'></i> &nbsp;");
    }

    ++totalSeconds;
    hour = Math.floor(totalSeconds / 3600);
    minute = Math.floor((totalSeconds - hour * 3600) / 60);
    seconds = totalSeconds - (hour * 3600 + minute * 60);
    $(".time-status").html(pad(hour, 2) + ":" + pad(minute, 2) + ":" + pad(seconds, 2));
    localStorage.setItem("counter", totalSeconds);
}

function pad(str, max) {
    str = str.toString();
    return str.length < max ? pad("0" + str, max) : str;
}

function deleteTimer() {
    var smart_timer_id = $("#smart_timer_id").val();
    swal({
        title: 'Are you sure you want to delete this practice area?',
        text: "You won't be able to revert this!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0CC27E',
        cancelButtonColor: '#FF586B',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
        confirmButtonClass: 'btn btn-success mr-5',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false
    }).then(function() {
        $(function() {
            $.ajax({
                url: baseUrl + "/deleteTimer",
                type: 'POST',
                data: { "smart_timer_id": smart_timer_id },
                success: function(data) {
                    if (data.status == "success") {
                        $("#smart_timer_id").val("");
                        window.location.reload();
                    }
                },
            });
        });

    });

}

function saveTimer() {
    pauseTimer();
    var smart_timer_id = $("#smart_timer_id").val();
    var case_id = $("#timer_case_id").val();
    $.ajax({
        url: baseUrl + "/saveTimer",
        type: 'POST',
        data: { "smart_timer_id": smart_timer_id, "case_id": case_id },
        success: function(data) {
            if (data.status == "success") {
                localStorage.setItem("counter", "0");
                $("#loadTimeEntryPopup").modal("show");
                if (case_id > 0) {
                    loadTimeEntryPopupByCase(case_id);
                } else {
                    loadTimeEntryPopup();
                }
            }
        },
    });
}

function pauseTimer() {
    var smart_timer_id = $("#smart_timer_id").val();

    $.ajax({
        url: baseUrl + "/pauseTimer",
        type: 'POST',
        data: { "smart_timer_id": smart_timer_id },
        success: function(data) {
            if (data.status == "success") {
                $("#pause_smart_timer_id").val(data.pause_smart_timer_id);
            }
        },
    });
}

function resumeTimer() {
    var smart_timer_id = $("#smart_timer_id").val();
    var pause_smart_timer_id = $("#pause_smart_timer_id").val();
    $.ajax({
        url: baseUrl + "/resumeTimer",
        type: 'POST',
        data: { "smart_timer_id": smart_timer_id, "pause_smart_timer_id": pause_smart_timer_id },
        success: function(data) {
            if (data.status == "success") {
                $("#pause_smart_timer_id").val("");
            }
        },
    });
}
// End