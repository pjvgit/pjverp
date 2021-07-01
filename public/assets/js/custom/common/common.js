

/* (function popupNotification() {
    $.ajax({
        url: baseUrl+"/get/popup/notification",
        type: 'GET',
        success: function(data) {
            console.log(data);
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