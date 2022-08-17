$(document).ready(function() {
    $('#replyEmails').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        var dataString = '';
        dataString = $("#replyEmails").serialize();
       
        $.ajax({
            type: "POST",
            url: baseUrl + "/client/messages/replyMessageToUserCase", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                $("#innerLoaderTime").css('display', 'block');
                if (res.errors != '') {
                    $('#showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError').append(errotHtml);
                    $('#showError').show();
                    afterLoader();
                    // $("#replyEmails").scrollTop(0);
                    $('#replyEmails').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    afterLoader()
                    window.location.reload();                  
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        });
    });


    $(document).on('change', '.sendTo', function() {
        sendMessage();
    });

    $(document).on('change', '#case_id', function() {
        beforeLoader();
        var case_id = $('#case_id').val();
        $.ajax({
            type: "POST",
            url: baseUrl + "/client/messages/getCaseStaffList", // json datasource
            data: {'case_id' : case_id},
            success: function (res) {
                afterLoader();
                console.log('staffList' + res.staffList);
                $(".staffList").show();
                var listHtml = '<div class="form-input__label">To*</div><div class="listView">';
                $.each(res.staffList, function (key, value) {
                    listHtml += '<input class="mr-2 sendTo" name="send_to[]"  type="checkbox" value="'+value.id+'" ><label>'+value.first_name+' '+value.last_name+' ('+value.user_title+')</label></br>';
                });
                listHtml += '</div></div>';
                $(".staffList").html('').html(listHtml);
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        });
    });

    $(document).on('blur', '#message_subject', function() {
        sendMessage();
    });

    var mousedownHappened = false;

    $('body').on('mousedown', '#sendMessageButton', function(){
        if ((($(".sendTo").val() == undefined) ? $(".sendTo").val() != undefined : $(".sendTo").val() != '' ) && $("#message_subject").val() != '' && $("#message_body").val() != '') {
            mousedownHappened = true;
        } else {
            mousedownHappened = false;
        }
    });

    $(document).on('blur', '#message_body', function() {
        if (mousedownHappened) {
            $("#action").val('submit');
            sendMessage();
            return false;
        } else {
            sendMessage();
        }
    });

    $(document).on('submit', '#addMessage', function() {
        $("#action").val('submit');
        sendMessage();
        return false;
    });

    $(document).on('click', '.closeMessage', function() {
        window.location.reload();
    });

    $(document).on('change', '#caseFilter', function(){
        var folder = $("#folder").val();
        var url = 'messages?';
        if(folder != ''){
            url +='folder='+folder;
        }
        if($(this).val() != ''){
            url +='&case_id='+$(this).val();
        }
        window.location.href = url;
    });

    $(document).on('change', '#homeCaseFilter', function(){
        var url = 'home?';
        if($(this).val() != ''){
            url +='&case_id='+$(this).val();
        }
        window.location.href = url;
    });
    
});
function archiveMessage(){
    beforeLoader();
    var message_id = $('#message_id').val();
    $.ajax({
            type: "POST",
            url: baseUrl + "/client/messages/archiveMessageToUserCase", // json datasource
            data: {'message_id' : message_id},
            success: function (res) {
                $("#innerLoaderTime").css('display', 'block');
                if (res.errors != '') {
                    $('#showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError').append(errotHtml);
                    $('#showError').show();
                    afterLoader();
                    // $("#replyEmails").scrollTop(0);
                    $('#replyEmails').animate({
                        scrollTop: 0
                    }, 'slow');
                    return false;
                } else {
                    window.location.href = baseUrl+"/client/messages";
                    // window.location.reload();
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        });
}


function unarchiveMessage(){
    beforeLoader();
    var message_id = $('#message_id').val();
    $.ajax({
            type: "POST",
            url: baseUrl + "/client/messages/unarchiveMessageToUserCase", // json datasource
            data: {'message_id' : message_id},
            success: function (res) {
                $("#innerLoaderTime").css('display', 'block');
                if (res.errors != '') {
                    $('#showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError').append(errotHtml);
                    $('#showError').show();
                    afterLoader();
                    // $("#replyEmails").scrollTop(0);
                    $('#replyEmails').animate({
                        scrollTop: 0
                    }, 'slow');
                    return false;
                } else {
                    // window.location.reload();
                    window.location.href = baseUrl+"/client/messages";
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        });
}

function addNewMessage(){
    beforeLoader();
    $.ajax({
        type: "GET",
        url: baseUrl + "/client/messages/addMessagePopup", // json datasource
        data: {},
        success: function (res) {
            $("#addMessagePopup").modal('show');
            $("#messageView").html('').html(res);
            $(".staffList").hide();
            afterLoader();
        },
        error: function (xhr, status, error) {
            $('.showError').html('');
            var errotHtml =
                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showError').append(errotHtml);
            $('.showError').show();
            afterLoader();
        }
    });
}

function submitBtnDisableCheck(){
    console.log($(".sendTo").val() +' > '+ $("#case_id").val() +' > '+ $("#message_subject").val() +' > '+ $("#message_body").val());
    if((($(".sendTo").val() == undefined) ? $(".sendTo").val() != undefined : $(".sendTo").val() != '' )  && $("#message_subject").val() != '' && $("#message_body").val() != ''){
        $(":submit").removeAttr("disabled");
    }else{
        // $(":submit").prop("disabled", true);
    }
}

function sendMessage(){
    submitBtnDisableCheck();
    $(".saved").html('').html('Saving...');
    var dataString = $("#addMessage").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/client/messages/sendOrDraftMessage", // json datasource
        data: dataString,
        success: function (res) {
            $(".saved").html('').html('Saved');
            if(res.redirect == 'yes'){
                $("#addMessagePopup").modal('hide');
                window.location.reload();
            }
        },
        error: function (xhr, status, error) {
            $('.showError').html('');
            var errotHtml =
                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showError').append(errotHtml);
            $('.showError').show();
        }
    });
}

function discardDraft(id){
    $.ajax({
        type: "POST",
        url: baseUrl + "/client/messages/discardDraftMessage", // json datasource
        data: {'id' : id},
        success: function (res) {
            $("#addMessagePopup").modal('hide');
            window.location.reload();
        },
        error: function (xhr, status, error) {
            $('.showError').html('');
            var errotHtml =
                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showError').append(errotHtml);
            $('.showError').show();
            afterLoader();
        }
    });
}

function openDraftMessage(id){
    $.ajax({
        type: "POST",
        url: baseUrl + "/client/messages/openDraftMessage", // json datasource
        data: {'id' : id},
        success: function (res) {
            $("#addMessagePopup").modal('show');
            $("#messageView").html('').html(res);
            afterLoader();
            submitBtnDisableCheck();
        },
        error: function (xhr, status, error) {
            $('.showError').html('');
            var errotHtml =
                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showError').append(errotHtml);
            $('.showError').show();
            afterLoader();
        }
    });
}