@extends('client_portal.layouts.master')
@section('title', 'Messages | Client Portal')
@section('main-content')
<div class="row" bladefile="resources/views/client_portal/messages/viewMessage.blade.php">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
            <div id="showError" class="showError" style="display:none"></div>
            <!-- --------------------------- -->
            @if($messagesData)
            <div class="message-thread">
                <div class="detail-view__headers">
                    <div class="message-thread__info">

                        <div style="float: right;"> 
                            <a class="btn btn-sm btn-secondary ml-1 archiveMessage" style="display:none;" href="javascript:void(0);" onclick="archiveMessage(); return false;">Archive</a>    
                            <a class="btn btn-sm btn-secondary ml-1 unarchiveMessage" style="display:none;" href="javascript:void(0);" onclick="unarchiveMessage(); return false;">Unarchive</a>    
                        </div>
                        <div class="truncatable-text">
                            <div class="truncatable-text__content"><b>{{$messagesData->subject}}</b></div>
                            <i class="truncatable-text__icon"></i>
                        </div>
                        <p class="truncatable-text">
                            To : {{ implode(', ',$clientList) }}
                            <br>
                            @if($messagesData->case_unique_number)    
                                Case: {{$messagesData->case_title}}
                            @else
                                Not linked to a case
                            @endif
                        </p>
                        
                    </div>
                </div>
                @if(count($messageList) > 0)
                    @foreach ($messageList as $key => $msg)
                    <ul class="detail-view__replies">
                        <li class="comment">
                            <span class="author-avatar">{{$msg->createdByUser->full_name[0]}}</span>
                            <div class="comment__header">
                                <p class="comment__author">{{$msg->createdByUser->full_name}} </p>
                                <p class="comment__date">{{ $msg->created_date_new }}</p>
                            </div>
                            <p class="comment__content">{!! str_replace('<br>','',$msg->reply_message) !!}</p>
                        </li>
                    </ul>
                    @endforeach
                @endif
                <div class="comment"><div>
                    <form class="replyEmails" id="replyEmails" name="replyEmails" method="POST">
                    @csrf
                    <input class="form-control" value="{{$messagesData->replies_is}}" id="replies_is" name="replies_is" type="hidden">
                    <input class="form-control" value="{{$messagesData->case_id}}" id="selected_case_id" name="selected_case_id" type="hidden">
                    <input class="form-control" value="{{$messagesData->created_by}}" id="selected_user_id" name="selected_user_id" type="hidden">
                    <input class="form-control" value="{{$messagesData->subject}}" id="subject" name="subject" type="hidden">
                    <input class="form-control" value="{{$messagesData->id}}" id="message_id" name="message_id" type="hidden">
                    <div class="form-input is-required">
                        <textarea id="message_reply" name="delta" class="form-control" required="" placeholder="Type reply here..."></textarea>
                    </div>
                    
                    <button class="btn btn-primary  btn-rounded m-1 submit" id="submitButton" value="savenote"
                    type="submit">Reply
                </button>
                </form>
            </div>
            </div>
            </div>
            @else
                <div id="messages_page_data">  <div class="no_items text-center">no records found</div><br></div>
            @endif
        </div>
    </div>
</div>
<style>
.detail-view__headers {
    align-items: center;
    background: #f7f7f7;
    border-bottom: 1px solid #cbcccf;
    box-shadow: 0 1px #efefef;
    padding: 1rem;
}
</style>
@endsection

@section('page-js')
<script src="{{ asset('assets\client_portal\js\messages\messages.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script>
$(document).ready(function() {
    <?php if($messagesData->is_archive == 1){?>
        $(".archiveMessage").css("display","none");
        $(".unarchiveMessage").css("display","block");
    <?php }else{?>
        $(".archiveMessage").css("display","block");
        $(".unarchiveMessage").css("display","none");
    <?php }?>
});
</script>
@endsection
