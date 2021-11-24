function SendAnotherWelcomeEmail(id) {
    $("#preloader").show();
    $.ajax({
        type: "POST",
        url: baseUrl + "/contacts/SendWelcomeEmail", // json datasource
        data: {
            "user_id": id
        },
        success: function(res) {
            window.location.reload();
            // $("#preloader").hide();
            // $("#responseMain").html(
            //     '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Welcome email sent!</div>'
            // );
            // $("#responseMain").show();
        }
    })
}

function loadPicker(id) {
    $("#preloader").show();
    $.ajax({
        type: "POST",
        url: baseUrl + "/contacts/loadColorPicker", // json datasource
        data: {
            "user_id": id
        },
        success: function(res) {
            $("#colorModel").html(res);
            $("#preloader").hide();
        }
    })
}

function loadPermission(id) {
    $("#preloader").show();
    $(function() {
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/loadPermissionModel", // json datasource
            data: {
                "user_id": id
            },
            success: function(res) {
                $("#permissionModel").html(res);
                $("#preloader").hide();
            }
        })
    })
}

function SendWelcomeEmail(id) {
    $("#preloader").show();
    $(function() {
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/SendWelcomeEmail", // json datasource
            data: {
                "user_id": id
            },
            success: function(res) {
                window.location.reload();
                //    $("#preloader").hide();
                //    $("#responseMain").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Welcome email sent!</div>');
                //             $("#responseMain").show();
            }
        })
    })
}

function loadRateBox(id) {
    $("#preloader").show();
    $(function() {
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/loadRateBox", // json datasource
            data: {
                "user_id": id
            },
            success: function(res) {
                $("#rateModel").html(res);
                $("#preloader").hide();
            }
        })
    })
}




function taskMarkAsCompleted(id) {
    $("#preloader").show();

    $.ajax({
        type: "POST",
        url: baseUrl + "/leads/markAsCompleted",
        data: {
            "task_id": id
        },
        success: function(res) {
            if (res.errors != '') {
                toastr.error('There were some problems with your input.', "", {
                    progressBar: !0,
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                });
                return false;
            } else {
                toastr.success('Your task have been updated.', "", {
                    progressBar: !0,
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                });
                window.location.reload();
            }
        }
    })
}

function taskMarkAsInCompleted(id) {
    $("#preloader").show();
    $.ajax({
        type: "POST",
        url: baseUrl + "/leads/markAsCompleted",
        data: {
            "task_id": id,
            "type": "incomplete"
        },
        success: function(res) {
            if (res.errors != '') {
                toastr.error('There were some problems with your input.', "", {
                    progressBar: !0,
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                });
                return false;
            } else {
                toastr.success('Your task have been updated.', "", {
                    progressBar: !0,
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                });
                window.location.reload();
            }
        }
    })
}

function loadTaskView(task_id) {
    $(".task-details-drawer").fadeIn();
    $("#taskViewArea").html('<img src="' + loaderImage + '"> Loading...');
    $("#preloader").show();
    $.ajax({
        type: "POST",
        url: baseUrl + "/leads/loadTaskDetailPage", // json datasource
        data: { "task_id": task_id },
        success: function(res) {
            $("#taskViewArea").html('<img src="' + loaderImage + '"> Loading...');
            $("#taskViewArea").html(res);
            $("#preloader").hide();
        }
    })
}

function loadTimeEntryPopup(description = null, duration = null, smart_timer_id = null) {
    $("#preloader").show();
    $("#addTimeEntry").html('<img src="' + loaderImage + '"> Loading...');
    $(function() {
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/loadTimeEntryPopup", // json datasource
            data: { "description": description, "duration": duration, "smart_timer_id": smart_timer_id },
            success: function(res) {
                $("#addTimeEntry").html('');
                $("#addTimeEntry").html(res);
                $("#preloader").hide();
            }
        })
    })
}

function beforeLoader() {
    $(".innerLoader").css('display', 'block');
    $('.submit').prop("disabled", true);
}

function afterLoader() {
    $(".innerLoader").css('display', 'none');
    $('.submit').removeAttr("disabled");
}

var commaCounter = 10;

function numberSeparator(Number) {
    Number += '';
    for (var i = 0; i < commaCounter; i++) {
        Number = Number.replace(',', '');
    }

    x = Number.split('.');
    y = x[0];
    z = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;

    while (rgx.test(y)) {
        y = y.replace(rgx, '$1' + ',' + '$2');
    }
    commaCounter++;
    return y + z;
}