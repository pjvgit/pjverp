// Get default firm reminder for client
var reminderAdded = false;
$(document).on("change", ".load-default-reminder, .load-default-reminder-all", function() {
    // alert(reminderAdded);
    if ($(this).is(":checked") && !reminderAdded) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/load/firm/defaultReminder",
            dataType: "JSON",
            success: function(res) {
                $(".reminder_user_type option[value='client-lead']").show();
                if (res.default_reminder && res.default_reminder.length > 0) {
                    $.each(res.default_reminder, function(ind, item) {
                        $(".add-more").trigger("click");
                        var lastNo = $(".fieldGroup").length;
                        // alert(lastNo);
                        $('body').find('#reminder_user_type:last').attr("ownid", lastNo);
                        $('body').find('#reminder_user_type:last').attr("id", lastNo);
                        $('body').find('#reminder_type:last').attr("id", "reminder_type_" + lastNo);
                        $('body').find('#reminder_number:last').attr("id", "reminder_number_" + lastNo);
                        $('body').find('#reminder_time_unit:last').attr("id", "reminder_time_unit_" + lastNo);

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
        var checkedL = $('input[name="client-share-all"]:checked').length;
        if (checkedLen <= 0 && checkedL <= 0) {
            $(".reminder_user_type option[value='client-lead']:selected").parents('.fieldGroup').remove();
            reminderAdded = false;
            $(".reminder_user_type option[value='client-lead']").hide();
        }
    }
});

// CHange reminder type based on reminder user type
function chngeTy(sel) {
    if (sel.value == 'client-lead') {
        $("#reminder_type_" + sel.id + " option[value='popup']").hide();
    } else {
        $("#reminder_type_" + sel.id + " option[value='popup']").show();
    }
}

/**
 * Load grant access modal
 * @param {} id 
 */
function loadGrantAccessModal(id) {
    if ($("#cleintUSER_" + id).prop('checked') == true && $("#cleintUSER_" + id).attr("data-client_portal_enable") == 0) {
        // alert(id);
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

                    $(".add-more").trigger('click');
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
$(document).on("click", ".add-more, .add-new-reminder", function() {
    var fieldHTML = '<div class="form-group fieldGroup">' + $(".fieldGroupCopy").html() + '</div>';
    $('body').find('.fieldGroup:last').after(fieldHTML);
    var checkedLen = $('input[name="ContactInviteClientCheckbox[]"]:checked').length;
    var checkedL = $('input[name="client-share-all"]:checked').length;
    if (checkedLen <= 0 && checkedL <= 0) {
        $(".reminder_user_type option[value='client-lead']").hide();
    }
    $("#is_reminder_updated").val("yes");
});

/**
 * Change/select recurring event type
 */
function selectType() {
    $(".innerLoader").css('display', 'block');
    var selectdValue = $("#event-frequency option:selected").val() // or
    if (selectdValue == 'DAILY') {
        $("#repeat_daily").show();
        $("#repeat_custom").hide();
        $(".repeat_yearly").hide();
        $(".repeat_monthly").hide();
    } else if (selectdValue == 'CUSTOM') {
        $("#repeat_custom").show();
        $("#repeat_daily").hide();
        $(".repeat_monthly").hide();
        $(".repeat_yearly").hide();
    } else if (selectdValue == 'MONTHLY') {
        $(".repeat_yearly").hide();
        $(".repeat_monthly").show();
        $("#repeat_custom").hide();
        $("#repeat_daily").hide();
        updateMonthlyWeeklyOptions();
    } else if (selectdValue == 'YEARLY') {
        $(".repeat_yearly").show();
        $(".repeat_monthly").hide();
        $("#repeat_custom").hide();
        $("#repeat_daily").hide();
        updateMonthlyWeeklyOptions();
    } else if (selectdValue == 'WEEKLY') {
        updateMonthlyWeeklyOptions();
        $("#repeat_daily").hide();
        $("#repeat_custom").hide();
        $(".repeat_monthly").hide();
        $(".repeat_yearly").hide();
    } else {
        $("#repeat_daily").hide();
        $("#repeat_custom").hide();
        $(".repeat_monthly").hide();
        $(".repeat_yearly").hide();
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
            $('body').find('.fieldGroup:last').after(res);
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
    alert();
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
function deleteEventFunction(eventRecurringId, eventId, types) {
    if(types=='single'){
        $("#deleteSingle").text('Delete Event');
    }else{
      $("#deleteSingle").text('Delete Recurring Event');
    }
      $("#preloader").show();
      $(function () {
          // alert(id);
          $.ajax({
              type: "POST",
              url: baseUrl + "/court_cases/deleteEventPopup", 
              data: {
                  "event_recurring_id": eventRecurringId, 'event_id': eventId
              },
              success: function (res) {
                  $("#deleteEventModalBody").html('');
                  $("#deleteEventModalBody").html(res);
                  $("#preloader").hide();
              }
          })
      })
  }