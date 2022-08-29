
//For open modal to sync calendar
$("#calendar-integration-sync").on("click", function() {
    $("#link_your_calendar").modal('show');
});

// For select service and submit 
$(".cal-service").on("click", function() {
    $(".cal-service").removeClass("btn-cta-secondary");
    $(this).removeClass("btn-secondary");
    $(this).addClass("btn-cta-secondary");
    if($(this).attr('data-testid') == 'google-service-auth-btn') {
        $("#sync_cal_btn").prop("href", "{{route('google/oauth')}}");
    }
    else if($(this).attr('data-testid') == 'outlook-service-auth-btn') {
        $("#sync_cal_btn").prop("href", "{{route('outlook/oauth')}}");
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
})