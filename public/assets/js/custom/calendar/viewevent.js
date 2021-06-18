
$(document).ready(function () {
    var toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'], // toggled buttons
        ['blockquote', 'code-block'],
        [{
            'header': 1
        }, {
            'header': 2
        }], // custom button values
        [{
            'list': 'ordered'
        }, {
            'list': 'bullet'
        }],
        
        [{
            'size': ['small', false, 'large', 'huge']
        }], // custom dropdown
        [{
            'header': [1, 2, 3, 4, 5, 6, false]
        }],

        [{
            'color': []
        }, {
            'background': []
        }], // dropdown with defaults from theme
        [{
            'font': []
        }],
        [{
            'align': []
        }],

        ['clean'] // remove formatting button
    ];

    var quill = new Quill('#editor', {
        modules: {
            toolbar: toolbarOptions
        },
        theme: 'snow'
    });
    afterLoader();


    $('#addComment').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        var delta =quill.root.innerHTML;
        if(delta=='<p><br></p>'){
            toastr.error('Unable to post a blank comment', "", {
                positionClass: "toast-top-full-width",
                containerId: "toast-top-full-width"
            });
            afterLoader();
        }else{
            var dataString = $("#addComment").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveEventComment", // json datasource
                data: dataString + '&delta=' + delta,
                success: function (res) {
                    afterLoader();
                    $(this).find(":submit").prop("disabled", true);
                    $("#innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        afterLoader();
                        return false;
                    } else {
                        toastr.success('Your comment was posted', "", {
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        loadCommentHistory($("#event_id").val());
                        quill.root.innerHTML='';
                        afterLoader();
                    }
                }
            });
        }
    });
});
loadCommentHistory($("#event_id").val());
function loadCommentHistory(event_id) {
    $.ajax({
        type: "POST",
        url: baseUrl + "/court_cases/loadCommentHistory",
        data: {
            "event_id": event_id
        },
        success: function (res) {
            $("#commentHistory").html(res);
        }
    })
}
loadReminderHistory($("#event_id").val());
function loadReminderHistory(event_id) {
    $.ajax({
        type: "POST",
        url: baseUrl + "/court_cases/loadReminderHistory",
        data: {
            "event_id": event_id
        },
        success: function (res) {
            $("#reminder_list").html(res);
        }
    })
}
function toggelComment(){
    $("#linkArea").hide();
    $("#editorArea").show();
}
function deleteEventFromCommentFunction(id,types) {
    
    if(types=='single'){
        $("#deleteSingle").text('Delete Event');
    }else{
    $("#deleteSingle").text('Delete Recurring Event');
    }
    $("#preloader").show();
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/deleteEventFromCommentPopup", 
            data: {
                "event_id": id
            },
            success: function (res) {
                $("#deleteFromComment").html('');
                $("#deleteFromComment").html(res);
                $("#preloader").hide();
            }
        })
    })
}
