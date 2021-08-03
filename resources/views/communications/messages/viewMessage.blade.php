@extends('layouts.master')
@section('title','Communications')
@section('main-content')
<div class="row">
    <div class="col-md-12" id="printArea">
        <div class="card mb-4">
            <div class="card-body">
            @if($messagesData)
            <div id="printArea">
                <div class="message_details_header" data-message-id="{{$messagesData->id}}">
                    <div style="float: left; width: 50px;">
                    <img src="{{ asset('images/message.svg') }}" width="42" height="42">
                    </div>
                    <div style="margin-left: 50px;">
                        <h2 class="details_header" style="margin-bottom: 0px; padding-bottom: 10px; font-weight: normal;">{{$messagesData->subject}}</h2>
                        A {{ucwords($messagesData->replies_is)}} Message Sent To:
                        @if($messagesData->user_id != '')
                            @foreach($clientList as $k=>$v)
                            <span class="message_user_name">
                                <a href="{{ route('contacts/clients/view', $k) }}">{{$v.' (Client)'}}</a>
                            </span>,
                            @endforeach
                        @endif
                        <br>
                        <div class="clearfix">
                            <div style="float: left; margin-top: 3px;">
                            Case:
                            </div>
                            @if($messagesData->case_unique_number)
                            <div id="case_link_text" style="float: left; margin-left: 5px; margin-top: 3px;">
                                <a href="{{ route('info',$messagesData->case_unique_number) }}">{{$messagesData->case_title}}</a>
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
                                    <div style="background-color: white;border: 0px solid #222222;border-radius: 0px;width: 56px;height: 56px;padding: 0px;display: inline-block;overflow: hidden;box-shadow: 0px 0px 4px #222222;"><img class="" src="https://assets.mycase.com/api_images/default_avatars/v1/default_avatar_64.svg" width="56" height="56"></div>
                                    </div>
                                </td>
                                <td style="background-color: rgb(246, 251, 255); overflow: visible;">
                                    <div class="callout_left">
                                    <div class="comment_wrapper">
                                        <div class="comment_header clearfix">
                                        <div style="font-weight: bold; float: left;">
                                            <a href="{{ route('contacts/attorneys/info', base64_encode(Auth::user()->id)) }}">{{Auth::user()->first_name .' '.Auth::user()->last_name}} (Attorney)</a>
                                        </div>
                                        <div style="float: right;">
                                        {{ date("M d,H:ia", strtotime($msg->created_at))}}
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
                <br>
            @else
                <div id="messages_page_data">  <div class="no_items text-center">no records found</div><br></div>
            @endif
            </div>
        </div>
    </div>
</div>
@endsection