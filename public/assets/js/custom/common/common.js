// popupNotification();

// Schedule the next request when the current one's complete
setInterval(function() {
    // popupNotification();
}, (1000 * 60 * 1));

function popupNotification() {
    $.ajax({
        url: baseUrl + "/get/popup/notification",
        type: 'GET',
        success: function(result) {
            if (result.view != "") {
                // if (typeof $.cookie('is_popup_dismissed') === 'undefined') {
                    $("#notify_modal_body").html(result.view);
                    $("#notification_popup").modal('show');
                // } else {
                //     console.log("cookie set");
                // }
            } else {
                $("#notification_popup").modal('hide');
                if(result.appNotificaionCount.eventCount){
                    $(".eventCount").html('').html(result.appNotificaionCount.eventCount);
                }else{
                    $(".eventCount").html('');
                }
                if(result.appNotificaionCount.taskCount){
                    $(".taskCount").html('').html(result.appNotificaionCount.taskCount);
                }else{
                    $(".taskCount").html('');
                }
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            if (xhr.status == 401) {
                window.location = baseUrl + '/autologout';
                // swal({
                //     type: 'warning',
                //     title: 'Session alert!',
                //     html: 'Your session has expired!. You will be redirected to login page.',
                // }).then(function(result) {
                //     window.location.reload();
                // });

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
    // var snoozeTime = $(this).val();
    // var snoozeType = $(this).attr("data-snooze-type");
    // var reminderId = $(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-id');
    // var reminderType = $(document).find("#popup_reminder_table tbody tr:first").attr('data-reminder-type');
    // $.ajax({
    //     url: baseUrl + "/update/popup/notification",
    //     type: 'GET',
    //     data: { 'reminder_id': reminderId, 'snooze_time': snoozeTime, 'snooze_type': snoozeType, 'reminder_type': reminderType },
    //     success: function(data) {
    //         if (data.status == "success") {
    //             popupNotification();
    //         }
    //     },
    // });

    var snoozeTime = $(this).val();
    var snoozeType = $(this).attr("data-snooze-type");
    var reminderEventId = [],
        reminderTaskId = [],
        solReminderId = [];
    $(document).find("#popup_reminder_table tbody tr").each(function() {
        if ($(this).attr('data-reminder-type') == "event") {
            reminderEventId.push($(this).attr('data-reminder-id'));
        } else if ($(this).attr('data-reminder-type') == "task") {
            reminderTaskId.push($(this).attr('data-reminder-id'));
        } else {
            solReminderId.push($(this).attr('data-reminder-id'));
        }
    });
    
    $.ajax({
        url: baseUrl + "/update/popup/notification",
        type: 'GET',
        data: {'snooze_all' : 'yes', 'reminder_event_id': reminderEventId, 'reminder_task_id': reminderTaskId, 'sol_reminder_id': solReminderId, 'snooze_time': snoozeTime, 'snooze_type': snoozeType },
        success: function(data) {
            if (data.status == "success") {
                popupNotification();
            }
        },
    });
    
});

/**
 * single snooze-button popup notification
 */
 $(document).on("click", ".snooze-button", function() {
    var snoozeTime = 10;
    var snoozeType = $(this).attr("data-snooze-type");
    var reminderId = $(this).attr('data-reminder-id');
    var reminderType = $(this).attr('data-reminder-type');
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
 * single dismiss-button popup notification
 */
 $(document).on("click", ".dismiss-button", function() {
    var disVal = $(this).attr('data-reminder-id');
    var disType = $(this).attr('data-reminder-type');

    var reminderEventId = [],
        reminderTaskId = [],
        solReminderId = [];
    
    if (disType == "event") {
        reminderEventId.push(disVal);
    } else if (disType == "task") { 
        reminderTaskId.push(disVal);
    } else {
        solReminderId.push(disVal); 
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
        value = value.replace(/,/g, '');
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
        value = value.replace(/,/g, '');
        return value > 0;
    }, 'Should be greater than 0');

    // For popover
    $(".pop").popover({ trigger: "manual", html: true, animation: false })
        .on("mouseenter", function() {
            var _this = this;
            $(this).popover("show");
            $(".popover").on("mouseleave", function() {
                $(_this).popover('hide');
            });
        }).on("mouseleave", function() {
            var _this = this;
            setTimeout(function() {
                if (!$(".popover:hover").length) {
                    $(_this).popover("hide");
                }
            }, 300);
        });

    // To resolve, Bootstrap modal makes window scrollbar disappear after closing issue
    $(document).on('hidden.bs.modal', '.modal', function () {
        // $('.modal:visible').length && $(document.body).addClass('modal-open');
        $('body').removeClass('modal-open');
    });
});

/**
 * Format number to comma seperated number
 */
function numberWithCommasDecimal(x) {
    return x.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
}

// Start smart timer Modules
// localStorage.setItem("counter", "0");
// removeLocalStorage();


let hour = 0;
let minute = 0;
let seconds = 0;
let totalSeconds = 0;

let intervalId = null;
$(".logoutTimerAlert").hide();

if(!localStorage.getItem('smart_timer_id')){
    console.log('smart_timer_id');
    removeLocalStorage();
}

var smart_timer_id = localStorage.getItem("smart_timer_id");
var smart_timer_created_by = localStorage.getItem("smart_timer_created_by");
console.log("smart_timer_id : " + smart_timer_id);
console.log("localStorage > smart_timer_id : " + localStorage.getItem("smart_timer_id"));
console.log("localStorage > counter : " + localStorage.getItem("counter"));
console.log("localStorage > pauseCounter : " + localStorage.getItem("pauseCounter"));
console.log("localStorage > smart_timer_created_by : " + smart_timer_created_by);
$(".timer-actions-button").hide();
$.ajax({
    url: baseUrl + "/checkTimerExits",
    type: 'GET',
    data: {},
    success: function(data) {
        if (data.status == "success" && data.smartTimer.id > 0) {
            localStorage.setItem("smart_timer_created_by", data.smartTimer.user_id);
            localStorage.setItem("smart_timer_id", data.smartTimer.id);
            localStorage.setItem("deleteTimer", '');
            localStorage.setItem("totalSeconds", data.runningSeconds);
            totalSeconds = data.runningSeconds;                
            hour = Math.floor(totalSeconds / 3600);
            minute = Math.floor((totalSeconds - hour * 3600) / 60);
            seconds = totalSeconds - (hour * 3600 + minute * 60);
            $(".time-status").html(pad(hour, 2) + ":" + pad(minute, 2) + ":" + pad(seconds, 2));
            $("#smart_timer_id").val(data.smartTimer.id);
            $("#timer_case_id").val(data.smartTimer.case_id);
            $("#timer_text_field").val(data.smartTimer.comments);
            if (data.smartTimer.stopped_at != null) {
                if (data.smartTimer.is_pause == 0) {
                    $(".logoutTimerAlert").show();
                    $(".js-timer-root .text-nowrap").html('');
                    localStorage.setItem("pauseCounter", 'yes');
                    intervalId = setInterval(timerstart, 1000);
                    $(".timer-actions-button").hide();
                } else {
                    $(".js-timer-root .text-nowrap").html("&nbsp;<i class='fas fa-circle' style='color: red !important;'></i>&nbsp;");
                    $(".timerAction").removeClass("fa-pause").addClass("fa-play");
                    $(".timerAction").attr('id', 'startCounter');
                    localStorage.setItem("pauseCounter", 'no');
                    $(".timer-actions-button").show();
                }
            } else {
                if (data.smartTimer.is_pause == 0) {
                    $(".logoutTimerAlert").show();
                    $(".js-timer-root .text-nowrap").html('');
                    localStorage.setItem("pauseCounter", 'yes');
                    intervalId = setInterval(timerstart, 1000);
                    $(".timer-actions-button").hide();
                } else {
                    $(".js-timer-root .text-nowrap").html("&nbsp;<i class='fas fa-circle' style='color: red !important;'></i>&nbsp;");
                    $(".timerAction").removeClass("fa-pause").addClass("fa-play");
                    $(".timerAction").attr('id', 'startCounter');
                    localStorage.setItem("pauseCounter", 'no');
                    $(".timer-actions-button").show();
                }
            }
        } else {
            removeLocalStorage();
        }
    }
});

$(window).on('storage', function (e) {
    var storageEvent = e.originalEvent;
    if(localStorage.getItem("smart_timer_id") != null) {
        console.log("storage > smart_timer_id >" + localStorage.getItem("smart_timer_id"));
        console.log("storage > event > key > " + storageEvent.key);
        if ((storageEvent.key == 'pauseCounter')) {
            if(storageEvent.oldValue == 'yes'){
                console.log('storage > Pause Timer');
                $(".timerCounter").hide();
                $(".logoutTimerAlert").hide();
                if (intervalId) {
                    clearInterval(intervalId);
                    localStorage.setItem("pauseCounter", 'no');
                    intervalId = null;
                }
                $(".timerAction").removeClass("fa-pause").addClass("fa-play");
                $(".timerAction").attr('id', 'startCounter');
                $(".js-timer-root .text-nowrap").html("&nbsp;<i class='fas fa-circle' style='color: red !important;'></i>&nbsp;");
            }
            if(storageEvent.oldValue == 'no'){   
            // Event detected, do some really useful thing here ;)
                console.log('storage > Resume Timer');
                $(".timerCounter").hide();
                $(".logoutTimerAlert").show();
                if (!intervalId) {
                    localStorage.setItem("pauseCounter", 'yes');
                    intervalId = setInterval(timerstart, 1000);
                }
                $(".timerAction").removeClass("fa-play").addClass("fa-pause");
                $(".timerAction").attr('id', 'pauseCounter');
            }
        }
        if ((storageEvent.key == 'deleteTimer')) {
            console.log("storage > delete timer event fire >" + localStorage.getItem("smart_timer_id"));
            $(".timerCounter").hide();
            $(".logoutTimerAlert").hide();
            $("#smart_timer_id").val('');
            $("#timer_case_id").val('');
            $(".js-timer-root .text-nowrap").html("");
            $(".js-timer-root .time-status").html("");
            $(".js-timer-root .text-nowrap").html("Start Timer");
            clearInterval(intervalId);
            localStorage.setItem("pauseCounter", 'no');
            intervalId = null;
            localStorage.setItem("totalSeconds", 0);
        }
    }else{
        console.log("storage > delete timer event fire >" + localStorage.getItem("smart_timer_id"));
        $(".timerCounter").hide();
        $(".logoutTimerAlert").hide();
        $("#smart_timer_id").val('');
        $("#timer_case_id").val('');
        $(".js-timer-root .text-nowrap").html("");
        $(".js-timer-root .time-status").html("");
        $(".js-timer-root .text-nowrap").html("Start Timer");
        clearInterval(intervalId);
        localStorage.setItem("pauseCounter", 'no');
        intervalId = null;
        localStorage.setItem("totalSeconds", 0);
    }
});

if (smart_timer_created_by == null) {
    removeLocalStorage();
}

$(".startTimer").on('click', function() {
    $(".timerCounter").show();
    if (localStorage.getItem("totalSeconds") == 0 && (smart_timer_id == '' || smart_timer_id == null)) {
        $.ajax({
            url: baseUrl + "/createTimer",
            type: 'POST',
            data: {},
            success: function(data) {
                if (data.status == "success") {
                    $(".logoutTimerAlert").show();
                    localStorage.setItem("pauseCounter", 'yes');
                    localStorage.setItem("smart_timer_id", data.smart_timer_id);
                    localStorage.setItem("smart_timer_created_by", data.smart_timer_created_by);
                    $("#smart_timer_id").val(data.smart_timer_id);
                    localStorage.setItem("totalSeconds", 0);
                    intervalId = setInterval(timerstart, 1000);
                    console.log("localStorage > smart_timer_created_by : " + localStorage.getItem("smart_timer_created_by"));
                }
            }
        });
    } else {
        $("#smart_timer_id").val(localStorage.getItem("smart_timer_id"));
        $("#timer_case_id").val(localStorage.getItem("timer_case_id"));
    }
});

$(document).on("change", "#timer_case_id", function() {
    localStorage.setItem("timer_case_id", $(this).val());
});


$(document).on("click", "#startCounter", function() {
    $(".timer-actions-button").hide();
    resumeTimer();
    $(".logoutTimerAlert").show();
    if (!intervalId) {
        localStorage.setItem("pauseCounter", 'yes');
        intervalId = setInterval(timerstart, 1000);
    }
    $(".timerAction").removeClass("fa-play").addClass("fa-pause");
    $(".timerAction").attr('id', 'pauseCounter');
});

$(document).on("click", "#pauseCounter", function() {
    $(".timer-actions-button").show();
    pauseTimer();
    $(".logoutTimerAlert").hide();
    if (intervalId) {
        clearInterval(intervalId);
        localStorage.setItem("pauseCounter", 'no');
        intervalId = null;
    }
    $(".timerAction").removeClass("fa-pause").addClass("fa-play");
    $(".timerAction").attr('id', 'startCounter');
    $(".js-timer-root .text-nowrap").html("&nbsp;<i class='fas fa-circle' style='color: red !important;'></i>&nbsp;");
});

$(document).on("click", "#timer_case_id", function() {
    localStorage.setItem("timer_case_id", $(this).val());
});

function timerstart() {
    if(localStorage.getItem("smart_timer_id") != null) {
        localStorage.setItem("deleteTimer", 'no');
        $("#timer_case_id").val(localStorage.getItem("timer_case_id"));
        $(".js-timer-root .text-nowrap .time-status").html("");
        $(".js-timer-root .text-nowrap").html("&nbsp;<i class='fas fa-circle' style='color: green !important;'></i>&nbsp;");
        totalSeconds = localStorage.getItem("totalSeconds");
        // console.log("timerstart > totalSeconds : " + localStorage.getItem("totalSeconds"));
        // console.log("totalSeconds : " + totalSeconds);

        ++totalSeconds;
        hour = Math.floor(totalSeconds / 3600);
        minute = Math.floor((totalSeconds - hour * 3600) / 60);
        seconds = totalSeconds - (hour * 3600 + minute * 60);
        $(".time-status").html(pad(hour, 2) + ":" + pad(minute, 2) + ":" + pad(seconds, 2));
        localStorage.setItem("counter", totalSeconds);
        localStorage.setItem("totalSeconds", totalSeconds);
    }
}

function pad(str, max) {
    str = str.toString();
    return str.length < max ? pad("0" + str, max) : str;
}

function deleteTimer() {
    var smart_timer_id = $("#smart_timer_id").val();
    swal({
        title: 'Delete timer',
        text: "Are you sure you want to delete this timer?",
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
                    localStorage.setItem("deleteTimer", 'yes');
                    $("#preloader").show();
                    removeLocalStorage();
                    $("#smart_timer_id").val("");
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    $("#preloader").show();
                    removeLocalStorage();
                    $("#smart_timer_id").val("");
                    window.location.reload();
                }
            });
        });

    });

}

function saveTimer() {
    $("#pauseCounter").trigger('click');
    var total_time = $(".time-status").html();
    var smart_timer_id = $("#smart_timer_id").val();
    var timer_text_field = $("#timer_text_field").val();
    var case_id = $("#timer_case_id").val();
    $.ajax({
        url: baseUrl + "/saveTimer",
        type: 'POST',
        data: { "total_time": total_time, "smart_timer_id": smart_timer_id, "case_id": case_id, "timer_text_field": timer_text_field },
        success: function(data) {
            if (data.status == "success") {
                localStorage.setItem("counter", "0");
                $("#loadTimeEntryPopup").modal("show");
                $("#timer_text_field").val('');
                $("#timer_case_id").val('');
                if (case_id > 0) {
                    loadTimeEntryPopupByCase(case_id, timer_text_field, data.duration, smart_timer_id);
                } else {
                    loadTimeEntryPopup(timer_text_field, data.duration, smart_timer_id);
                }
            }
        }
    });
}

function pauseTimer() {
    var smart_timer_id = $("#smart_timer_id").val();
    var total_time = $(".time-status").html();
    $.ajax({
        url: baseUrl + "/pauseTimer",
        type: 'POST',
        data: { "smart_timer_id": smart_timer_id, "total_time": total_time },
        success: function(data) {
            if (data.status == "error") {
                removeLocalStorage();
            }
        },
    });
}

function resumeTimer() {
    var smart_timer_id = $("#smart_timer_id").val();
    var total_time = $(".time-status").html();
    $.ajax({
        url: baseUrl + "/resumeTimer",
        type: 'POST',
        data: { "smart_timer_id": smart_timer_id, "total_time": total_time },
        success: function(data) {
            if (data.status == "error") {
                removeLocalStorage();
            }
        },
    });
}

$(document).mouseup(function(e) {
    if ($(e.target).closest(".timerCounter").length === 0) {
        $(".timerCounter").hide();
    }
});

$('#logout-form').submit(function(e) {
    if (localStorage.getItem("smart_timer_id") > 0) {
        // alert("You have a timer running right now. If you logout, your active timer will be paused.");
        removeLocalStorage();
        // $("#preloader").show();
        $("#pauseCounter").trigger('click');
        return true;
    } else {
        return true;
    }
});

function browserClose() {
    var smart_timer_id = $("#smart_timer_id").val();
    var total_time = $(".time-status").html();
    $.ajax({
        url: baseUrl + "/browserClose",
        type: 'POST',
        data: { "smart_timer_id": smart_timer_id, "total_time": total_time },
        success: function(data) {

        }
    });
}

function removeLocalStorage() {
    localStorage.setItem("smart_timer_created_by", '');
    localStorage.setItem("smart_timer_id", '');
    localStorage.setItem("deleteTimer", '');
    localStorage.setItem("pauseCounter", 'no');
    localStorage.setItem("timer_case_id", '');
    localStorage.setItem("counter", "0");
    localStorage.setItem("totalSeconds", 0);
    localStorage.setItem("deleteTimer", 'no');
    $(".logoutTimerAlert").hide();
}

//https://www.cluemediator.com/detect-browser-or-tab-close-event-using-javascript
window.addEventListener("beforeunload", function(e) {
    // $("#preloader").show();
    // *********** perform database operation here
    // browserClose();
    // before closing the browser ************** //

    // added the delay otherwise database operation will not work
    for (var i = 0; i < 500000000; i++) {}
    return undefined;
});
// End