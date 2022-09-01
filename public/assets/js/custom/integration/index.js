
//For open modal to sync calendar
$("#calendar-integration-sync").on("click", function() {
    $("#link_your_calendar").modal('show');
});

// For select service and submit 
$(".cal-service").on("click", function() {
    $(".cal-service").removeClass("btn-cta-secondary");
    $(this).removeClass("btn-secondary");
    $(this).addClass("btn-cta-secondary");
    if($(this).attr('id') == 'google-service-auth-btn') {
        $("#sync_cal_btn").prop("href", baseUrl+"/google/oauth");
    }
    else if($(this).attr('id') == 'outlook-service-auth-btn') {
        $("#sync_cal_btn").prop("href", baseUrl+"/outlook/oauth");
    } else {
        $("#sync_cal_btn").prop("href", "javascript:;");
    }
});

// For open synced calendar 
$("#calendar-integration-settings").on("click", function() {
    var syncId = $(this).attr('data-sync-id');
    $.ajax({
        url: baseUrl+'/load/calendar/setting',
        type: 'GET',
        data: {sync_id: syncId},
        success: function(response) {
            $("#calendar_inte_setting_body").html(response);
            $("#calendar_inte_setting").modal('show');
        }
    })
});

// For uninstall sync calendar
function uninstallCalendra() {
    var isDeleteEvent = $("#delete-calendar-checkbox-option:checked").val();
    $.ajax({
        url: baseUrl+'/uninstall/sync/calendar',
        type: 'GET',
        data: {is_delete_event: isDeleteEvent},
        success: function(response) {
            window.location.reload();
        }
    })
}

function syncEventNow() {
    $.ajax({
        url: baseUrl+'/event/sync/calendar',
        type: 'GET',
        data: {},
        success: function(response) {

        }
    })
}

$(document)
 .ajaxStart(function () {
     $('#preloader').show();   //ajax request went so show the loading image
 })
 .ajaxStop(function () {
     $('#preloader').hide();   //got response so hide the loading image
 });