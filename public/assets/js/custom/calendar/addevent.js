// Get default firm reminder for client
var reminderAdded = false;
$(document).on("change", ".load-default-reminder, .load-default-reminder-all", function() {
    // alert(reminderAdded);
    var modalId = $(this).parents('section.sharing-list').parents('div.modal').attr("id");
    if ($(this).is(":checked") && !reminderAdded) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/load/firm/defaultReminder",
            dataType: "JSON",
            success: function(res) {
                $(".reminder_user_type option[value='client-lead']").show();
                if (res.default_reminder && res.default_reminder.length > 0) {
                    $.each(res.default_reminder, function(ind, item) {
                        $(".add-more-event-reminder").trigger("click");
                        var lastNo = $(".fieldGroupEventReminder").length;
                        $('body').find('#'+modalId+' .fieldGroupEventReminder:last .reminder_user_type').attr("ownid", lastNo);
                        $('body').find('#'+modalId+' .fieldGroupEventReminder:last .reminder_user_type').attr("id", lastNo);
                        $('body').find('#'+modalId+' .fieldGroupEventReminder:last .reminder_type').attr("id", "reminder_type_" + lastNo);
                        $('body').find('#'+modalId+' .fieldGroupEventReminder:last .reminder-number').attr("id", "reminder_number_" + lastNo);
                        $('body').find('#'+modalId+' .fieldGroupEventReminder:last .reminder_time_unit').attr("id", "reminder_time_unit_" + lastNo);

                        $('body').find("#" + lastNo + " option[value='client-lead']").show();
                        $('body').find('#' + lastNo).val(item.reminder_user_type);
                        $('#' + lastNo).trigger("change");
                        $('body').find('#reminder_number_' + lastNo).val(item.reminer_number);
                        $('body').find('#reminder_time_unit_' + lastNo).val(item.reminder_frequncy);
                        $('body').find('#reminder_type_' + lastNo).val(item.reminder_type);
                    });
                    reminderAdded = true;
                }
            }
        });
    } else {
        var checkedLen = $('input[name="ContactInviteClientCheckbox[]"]:checked').length;
        // var checkedL = $('input[name="client-share-all"]:checked').length;
        // if (checkedLen <= 0 && checkedL <= 0) {
        if (checkedLen > 0) {
            $(".reminder_user_type option[value='client-lead']").show();
        } else {
            $(".reminder_user_type option[value='client-lead']:selected").parents('.fieldGroupEventReminder').remove();
            reminderAdded = false;
            $(".reminder_user_type option[value='client-lead']").hide();
        }
    }
});

// CHange reminder type based on reminder user type
function changeEventReminderUserType(sel) {
    if (sel.value == 'client-lead') {
        $(sel).parents('div.fieldGroupEventReminder').find(".reminder_type option[value='popup']").hide();
        $(sel).parents('div.fieldGroupEventReminder').find(".reminder_type").val('email');
        // $("#reminder_type_" + sel.id + " option[value='popup']").hide();
    } else {
        $(sel).parents('div.fieldGroupEventReminder').find(".reminder_type option[value='popup']").show();
        // $("#reminder_type_" + sel.id + " option[value='popup']").show();
    }
}

/**
 * Load grant access modal
 * @param {} id 
 */
function loadGrantAccessModal(id) {
    if ($("#cleintUSER_" + id).prop('checked') == true && $("#cleintUSER_" + id).attr("data-client_portal_enable") == 0) {
        $("#cleintUSER_" + id).prop('checked', false);
        $("#loadGrantAccessModal").modal();
        $("#innerLoader").css('display', 'none');
        $("#preloader").show();
        $("#grantCase").html('');
        $(function() {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadGrantAccessPage", // json datasource
                data: { "client_id": id },
                success: function(res) {
                    $("#grantCase").html(res);
                    $("#preloader").hide();
                    $("#innerLoader").css('display', 'none');

                    $(".add-more-event-reminder").trigger('click');
                    return false;
                }
            })
        })
    }
    var checkecCounter = $('input[name="clientCheckbox[]"]:checked').length;
    if (checkecCounter > 0) {
        $(".reminder_user_type option[value='client-lead']").show();
    } else {
        $(".reminder_user_type option[value='client-lead']").hide();
    }
}

// For add reminder
$('body').on("click", ".add-more-event-reminder, .add-new-reminder", function() {
    var modalId = $(this).parents('div.modal').attr("id");
    var fieldHTML = '<div class="form-group fieldGroupEventReminder">' + $(".fieldGroupEventReminderCopy").html() + '</div>';
    $('#'+modalId).find('.fieldGroupEventReminder:last').after(fieldHTML);
    // setTimeout(function(){
        var checkedLen = $('#'+modalId+' input[name="ContactInviteClientCheckbox[]"]:checked').length;
        if (checkedLen > 0) {
            $("#"+modalId+" select.reminder_user_type").children("option[value='client-lead']").show();
        } else {
            $("#"+modalId+" select.reminder_user_type").children("option[value='client-lead']").hide();
        }
    // },2000); 
    $("#is_reminder_updated").val("yes");
});

/**
 * Change/select recurring event type
 */
function selectType(selectdValue = null, modalId) {
    $(".innerLoader").css('display', 'block');
    // var selectdValue = $("#event-frequency option:selected").val() // or
    if (selectdValue == 'DAILY') {
        $("#"+modalId+" #repeat_daily").show();
        $("#"+modalId+" #repeat_custom").hide();
        $("#"+modalId+" .repeat_yearly").hide();
        $("#"+modalId+" .repeat_monthly").hide();
    } else if (selectdValue == 'CUSTOM') {
        $("#"+modalId+" #repeat_custom").show();
        $("#"+modalId+" #repeat_daily").hide();
        $("#"+modalId+" .repeat_monthly").hide();
        $("#"+modalId+" .repeat_yearly").hide();
    } else if (selectdValue == 'MONTHLY') {
        $("#"+modalId+" .repeat_yearly").hide();
        $("#"+modalId+" .repeat_monthly").show();
        $("#"+modalId+" #repeat_custom").hide();
        $("#"+modalId+" #repeat_daily").hide();
        updateMonthlyWeeklyOptions();
    } else if (selectdValue == 'YEARLY') {
        $("#"+modalId+" .repeat_yearly").show();
        $("#"+modalId+" .repeat_monthly").hide();
        $("#"+modalId+" #repeat_custom").hide();
        $("#"+modalId+" #repeat_daily").hide();
        updateMonthlyWeeklyOptions();
    } else if (selectdValue == 'WEEKLY') {
        updateMonthlyWeeklyOptions();
        $("#"+modalId+" #repeat_daily").hide();
        $("#"+modalId+" #repeat_custom").hide();
        $("#"+modalId+" .repeat_monthly").hide();
        $("#"+modalId+" .repeat_yearly").hide();
    } else {
        $("#"+modalId+" #repeat_daily").hide();
        $("#"+modalId+" #repeat_custom").hide();
        $("#"+modalId+" .repeat_monthly").hide();
        $("#"+modalId+" .repeat_yearly").hide();
    }
    $(".innerLoader").css('display', 'none');
}

/* $('#loadEditEventPopup,#loadAddEventPopup').on('shown.bs.modal', function () {
    reminderAdded = false; 
}); */

function loadDefaultEventReminder() {
    $.ajax({
        type: "POST",
        url: baseUrl + "/court_cases/loadDefaultEventReminder",
        success: function(res) {
            $('body').find('.fieldGroupEventReminder:last').after(res);
        }
    });
}

/**
 * Load add event popup
 * @param {*} selectedDate 
 */
function loadAddEventPopup(selectedDate = null, fromPageRoute = null) {
    $("#loadAddEventPopup").modal('show');
    $("#AddEventPage").html('Loading...');
    $("#preloader").show();
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadAddEventPage", // json datasource
            data: {
                "case_id": $("#case_id").val(),
                "lead_id": $("#lead_id").val(),
                "selectedate": selectedDate,
                "from_page_route": fromPageRoute,
            },
            success: function (res) {
                $("#AddEventPage").html('Loading...');
                $("#AddEventPage").html(res);
                $("#preloader").hide();
            }
        });
    });
}

/**
 * Load event detail/comment popup 
 */
function loadEventComment(event_id, event_recurring_id, fromPageRoute = null) {
    $("#loadCommentPopup").modal('show');
    $("#eventCommentPopup").html('Loading...');
    $("#preloader").show();
    markEventAsRead(event_id);
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadEventCommentPopup", // json datasource
            data: {
                "event_id": event_id, event_recurring_id: event_recurring_id,
                "from_page_route": fromPageRoute,
            },
            success: function (res) {
                $("#eventCommentPopup").html('Loading...');
                $("#eventCommentPopup").html(res);
                $("#preloader").hide();
            }
        })
    })
}

/**
 * Load event reminder popup from event listing 
 */
function loadEventReminderPopup(event_id, event_recurring_id) {
    $("#eventReminderData").html('Loading...');
    $("#preloader").show();
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadEventReminderPopup", // json datasource
            data: {
                "event_id": event_id, event_recurring_id: event_recurring_id,
            },
            success: function (res) {
                $("#eventReminderData").html('Loading...');
                $("#eventReminderData").html(res);
                $("#preloader").hide();
                
            }
        })
    })
}

/**
 * Load event popup for single/multiple event
 */
function editEventFunction(evnt_id, event_recurring_id = null, fromPageRoute = null) {
    $("#preloader").show();
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadEditEventPage", // json datasource
            data: {
                "evnt_id":evnt_id,
                "from":"edit",
                "event_recurring_id": event_recurring_id,
                "from_page_route": fromPageRoute,
            },
            success: function (res) {
                $("#loadCommentPopup").modal('hide');
                $("#loadEditEventPopup .modal-dialog").addClass("modal-xl");
                $("#EditEventPage").html('');
                $("#EditEventPage").html(res);
                $("#preloader").hide();
            }
        })
    })
}

/**
 * Load delete event popup for single/multiple event
 * @param {*} eventRecurringId 
 * @param {*} eventId 
 * @param {*} types 
 */
function deleteEventFunction(eventRecurringId, eventId, types, fromPageRoute = null) {
    if(types=='single'){
        $("#deleteSingle").text('Delete Event');
    }else{
      $("#deleteSingle").text('Delete Recurring Event');
    }
    $("#preloader").show();
    $.ajax({
        type: "POST",
        url: baseUrl + "/court_cases/deleteEventPopup", 
        data: {
            "event_recurring_id": eventRecurringId, 'event_id': eventId,
            "from_page_route": fromPageRoute,
        },
        success: function (res) {
            $("#deleteEventModalBody").html('');
            $("#deleteEventModalBody").html(res);
            $("#preloader").hide();
        }
    })
}

// Mark event as read
function markEventAsRead(eventId) {
    $.ajax({
        type: "GET",
        url: baseUrl + "/events/mark/read", 
        data: {
            'event_id': eventId,
        },
        success: function (res) {
            $('#calendarq').fullCalendar('refetchEvents');
        }
    })
}

// Validation to check end on date is greater than start date
$.validator.addMethod("dateGreaterThan", function(value, element) {
    var startDate = $('#start_date').val();
    return Date.parse(startDate) <= Date.parse(value) || value == "";
}, "The end date must be after the start date");

// Hide comment count after the read comment from comment popup
$('#loadCommentPopup').on('hidden.bs.modal', function () {
    var eventId = $("input[name='event_recurring_id']").val();
    $(".comment-count-"+eventId).hide();
});