@extends('layouts.master')
@section('title','Communications')
@section('main-content')
<div class="row" bladefile="resources/views/communications/messages/viewMessage.blade.php">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
            <div id="showError" class="showError" style="display:none"></div>
            @if($messagesData)
            <div style="float: right;">    
                <button onclick="printData()" class="btn btn-link text-black-50 pendo-case-print d-print-none">
                    <i class="fas fa-print"></i> Print
                </button>
                <a class="btn btn-sm btn-secondary ml-1 archiveMessage" style="display:none;" href="javascript:void(0);" onclick="archiveMessage(); return false;">Archive</a>    
                <a class="btn btn-sm btn-secondary ml-1 unarchiveMessage" style="display:none;" href="javascript:void(0);" onclick="unarchiveMessage(); return false;">Unarchive</a>    
            </div>
            <div id="printArea">
                <div class="message_details_header" data-message-id="{{$messagesData->id}}">
                    <input class="form-control" value="{{$messagesData->id}}" id="message_id" name="message_id" type="hidden">
                    
                    <div style="float: left; width: 50px;">
                    <img src="{{ asset('images/message.svg') }}" width="42" height="42">
                    </div>
                    <div style="margin-left: 50px;">
                        <h2 class="details_header" style="margin-bottom: 0px; padding-bottom: 10px; font-weight: normal;">{{$messagesData->subject}}</h2>
                        <!-- A {{ucwords($messagesData->replies_is)}} Message Sent To: -->
                        A {{ ($messagesData->replies_is == 'private') ? 'Private' : '' }} Conversation Between:
                        {{-- @if($messagesData->user_id != '')
                            @php $i = count($clientList); @endphp            
                            @foreach($clientList as $k=>$v)
                                @php $i = $i - 1; @endphp    
                            <span class="message_user_name">
                                @php $u = explode("|",$v); @endphp
                                @if($u[1] == '2')
                                <a href="{{ route('contacts/clients/view', $k) }}">{{$u[0].' (Client)'}}@if($i >= 1),@endif</a>                
                                @else
                                <a href="{{ route('contacts/attorneys/info', base64_encode($k)) }}">{{$u[0].' (Attorney)'}}@if($i >= 1),@endif</a>
                                @endif
                            </span>
                            @endforeach
                        @endif --}}
                        @forelse ($clientList as $key => $item)
                            @if($key == auth()->id())
                                You
                            @else
                                @if($item['user_level'] == '2')
                                <a href="{{ route('contacts/clients/view', $key) }}">{{ $item['full_name'] }}{{ '('.$item['user_title'].')' }}</a>                
                                @else
                                <a href="{{ route('contacts/attorneys/info', base64_encode($key)) }}">{{ $item['full_name'] }}{{ '('.$item['user_title'].')' }}</a>
                                @endif
                            @endif
                            @if(!$loop->last)
                                ,
                            @endif
                        @empty
                        @endforelse
                        <br>
                        <div class="clearfix">
                            <div style="float: left; margin-top: 3px;">
                            Case:
                            </div>
                            @if($messagesData->case_unique_number)
                            <div id="case_link_text" style="float: left; margin-left: 5px; margin-top: 3px;">
                                <a href="{{ route('info',$messagesData->case_unique_number) }}">{{$messagesData->case_title}}</a>
                            </div>
                            @else
                            <div style="float: left; margin-top: 3px;">
                                <p> &nbsp;Not linked to a case</p>
                            </div>                           
                            @endif
                        </div>
                    </div>
                </div>
                <br>

                <div class="message_details_body">

                    <div id="comments_page" class="padded_page" style="">       
                        @if(count($messageList) > 0)
                        @foreach ($messageList as $key => $msg)
                        <div id="comment_{{$msg->id}}" class="comment_row comment_message comment_row_me">
                        <div class="comment_padding">
                            <table style="width: 100%;">
                                <tbody><tr>
                                <td style="width: 76px; vertical-align: top;">
                                    <div style="position: relative; overflow: visible;">
                                    <div style="background-color: white;border: 0px solid #222222;border-radius: 0px;width: 56px;height: 56px;padding: 0px;display: inline-block;overflow: hidden;box-shadow: 0px 0px 4px #222222;">
                                    @if($msg->createdByUser->profile_image != '' && file_exists(public_path().'/images/users/'.$msg->createdByUser->profile_image))
                                        <img class="" src="{{asset('images/users/'.$msg->createdByUser->profile_image)}}" width="56" height="56">
                                    @else
                                        <img class="" src="{{asset('assets/images/faces/default_face.svg')}}" width="56" height="56">
                                    @endif
                                    </div>
                                    </div>
                                </td>
                                <td style="background-color: rgb(246, 251, 255); overflow: visible;">
                                    <div class="callout_left">
                                    <div class="comment_wrapper">
                                        <div class="comment_header clearfix">
                                        <div style="font-weight: bold; float: left;">
                                            <a href="{{ $msg->createdByUser->user_route_link }}">{{$msg->createdByUser->full_name}} ({{$msg->createdByUser->user_title}})</a>
                                        </div>
                                        <div style="float: right;">
                                        {{ $msg->created_date_new }}
                                        </div>
                                        </div>
                                        <div class="comment_body wrap-long-words">
                                        <div><br>{!! $msg->reply_message !!}
                                        </div>
                                    </div>                                  
                                    </div>
                                    </div>
                                </td>
                                </tr>
                            </tbody>
                            </table>
                        </div>
                        </div>
                        <br>
                        @endforeach
                        @else
                        <div id="messages_page_data">  <div class="no_items text-center">There are no messages.</div><br></div>
                        @endif                        
                    </div>
                </div>
                </div>
                @if ($messagesData->is_global == "0" || $messagesData->is_global_for == 'staff')
                <br>
                <form class="replyEmails" id="replyEmails" name="replyEmails" method="POST">
                    @csrf
                    <input class="form-control" value="{{$messagesData->replies_is}}" id="replies_is" name="replies_is" type="hidden">
                    <input class="form-control" value="{{$messagesData->is_global_for}}" id="is_global_for" name="is_global_for" type="hidden">
                    <input class="form-control" value="{{$messagesData->case_id}}" id="selected_case_id" name="selected_case_id" type="hidden">
                    <input class="form-control" value="{{$messagesData->user_id}}" id="selected_user_id" name="selected_user_id" type="hidden">
                    <input class="form-control" value="{{$messagesData->created_by}}" id="created_by" name="created_by" type="hidden">
                    <input class="form-control" value="{{$messagesData->subject}}" id="subject" name="subject" type="hidden">
                    <input class="form-control" value="{{$messagesData->id}}" id="message_id" name="message_id" type="hidden">
                    <span id="response"></span>    
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <div id="editorMessage" class="field">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="justify-content-between modal-footer">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;">
                        </div>        
                        </button>
                        &nbsp;
                        <div>
                            @if($messagesData->for_staff == 'no'){
                            <button class="btn btn-outline-secondary btn-rounded  m-1" id="saveandtime" value="saveandtime"
                                type="submit">Save + <i class="far fa-clock fa-lg"></i>
                            </button>
                            @endif
                            <button class="btn btn-primary  btn-rounded m-1 submit" id="submitButton" value="savenote"
                                type="submit">Reply
                            </button>
                        </div>
                    </div>
                    <input class="form-control" value="" id="current_submit" maxlength="250" name="current_submit" type="hidden">
                    <input class="form-control" value="{{$messagesData->case_id}}" id="case_link" maxlength="250" name="case_link" type="hidden">
                </form>
                @else
                <div style="text-align: center; padding: 20px;">
                    <em>You cannot reply to this message.</em>
                </div>
                @endif
            @else
                <div id="messages_page_data">  <div class="no_items text-center">no records found</div><br></div>
            @endif
            </div>
        </div>
    </div>
</div>
@endsection
@section('bottom-js')
<style>
    body>#editorMessage {
        margin: 50px auto;
        max-width: 720px;
    }

    #editorMessage {
        height: 200px;
        background-color: white;
    }
</style>

<script type="text/javascript">
<?php if($messagesData->is_archive_msg == 'yes'){?>
    $(".archiveMessage").css("display","none");
    $(".unarchiveMessage").css("display","block");
<?php }else{?>
    $(".archiveMessage").css("display","block");
    $(".unarchiveMessage").css("display","none");
<?php }?>
$(document).on("click", ":submit", function (e) {
    $("#current_submit").val($(this).val());
});
$('#replyEmails').submit(function (e) {
        $("#preloader").show();
        beforeLoader();
        e.preventDefault();
        var delta = quill.root.innerHTML;
        if (delta == '<p><br></p>') {
            toastr.error('Initial message can\'t be blank', "", {
                positionClass: "toast-top-full-width",
                containerId: "toast-top-full-width"
            })
            $("#preloader").hide();
            afterLoader();
            return false;
        }
        if (!$('#replyEmails').valid()) {
            afterLoader();
            $("#preloader").hide();
            return false;
        }
        var dataString = '';
        dataString = $("#replyEmails").serialize();
        console.log(dataString);
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/replyMessageToUserCase", // json datasource
            data: dataString + '&delta=' + delta,
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
                    $("#preloader").hide();
                    // $("#replyEmails").scrollTop(0);
                    $('#replyEmails').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    $("#preloader").hide();
                    toastr.success('Your message has been send', "", {
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                    quill.root.innerHTML = '';
                    afterLoader()
                    
                    if($("#current_submit").val() == 'saveandtime'){            
                        $("#loadMessagesEntryPopup").modal('hide');
                        $("#loadTimeEntryPopup").modal('show');
                        if($("#current_submit").val() == 'saveandtime'){
                            loadTimeEntryPopupByCase($("#case_link").val());
                        }
                    }else{
                        window.location.reload();
                    }
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
var quill = new Quill('#editorMessage', {
    modules: {
        toolbar: toolbarOptions
    },
    theme: 'snow'
});
function deleteDraft(){
    toastr.warning('Draft message has been deleted', "", {
        positionClass: "toast-top-full-width",
        containerId: "toast-top-full-width"
    })
}  

function printData()
{
    var divContents = document.getElementById("printArea").innerHTML;
    var a = window.open('');
    a.document.write('<html><body>');
    a.document.write(divContents);
    a.document.write('</body></html>');
    a.document.close();
    a.print();
}

function archiveMessage(){
    var message_id = $('#message_id').val();
    $.ajax({
        type: "POST",
        url: baseUrl + "/contacts/clients/archiveMessageToUserCase", // json datasource
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
                toastr.success('Message was archived', "", {
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                });
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
}


function unarchiveMessage(){
    var message_id = $('#message_id').val();
    $.ajax({
        type: "POST",
        url: baseUrl + "/contacts/clients/unarchiveMessageToUserCase", // json datasource
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
                toastr.success('Message was unarchived', "", {
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                });
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
}
</script>

@endsection