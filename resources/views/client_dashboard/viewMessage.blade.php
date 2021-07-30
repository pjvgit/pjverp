<div class="message_details_header" data-message-id="12275283">
    <div style="float: left; width: 50px;">
      <img src="{{ asset('images/message.svg') }}" width="42" height="42">
    </div>
    <div style="margin-left: 50px;">
        <h2 class="details_header" style="margin-bottom: 0px; padding-bottom: 10px; font-weight: normal;">{{$messagesData->subject}}</h2>
        A {{$messagesData->replies_is}} Message Sent To:
        @if($messagesData->user_id != '')
        <span class="message_user_name">
            <a href="{{ route('contacts/clients/view', $messagesData->user_id) }}">{{$messagesData->user_name}}</a>
        </span>
        @endif
        <br>
        <div class="clearfix">
            <div style="float: left; margin-top: 3px;">
            Case:
            </div>
            <div id="case_link_text" style="float: left; margin-left: 5px; margin-top: 3px;">
                <a href="{{ route('info',$messagesData->case_unique_number) }}">{{$messagesData->case_title}}</a>
            </div>
        </div>
    </div>
</div>
<br>
<div class="message_details_body">

    <div id="comments_page" class="padded_page" style="">
        <div>

          
        <div id="comment_37053130" class="comment_row comment_message comment_row_me">
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
                            <a href="{{ route('contacts/attorneys/info', base64_encode(Auth::user()->id)) }}">{{Auth::user()->first_name .' '.Auth::user()->last_name}}</a>
                        </div>

                        <div style="float: right;">
                        {{ date("M d,H:ia", strtotime($messagesData->created_at))}}
                        </div>
                        </div>

                        <div class="comment_body wrap-long-words">
                        <div><p>{!! $messagesData->message !!}</p>
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
</div>
<script>

</script>