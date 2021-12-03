<tr role="row" class="odd">
    <td class="sorting_1" style="font-size: 13px;">
        <div class="text-left">
            <?php 
            $ImageArray=[];
            $ImageArray['add']="activity_bill_added.png";
            $ImageArray['update']="activity_bill_updated.png";
            $ImageArray['share']="activity_bill_shared.png";
            $ImageArray['delete']="activity_bill_deleted.png";
            $image=$ImageArray[$v->action];
            ?>
            <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
            <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name.' '.$v->last_name}} ({{$v->user_title}})</a> 
            {{$v->activity}} 
            @if($v->depositForUser->user_level=="2")
                <a class="name" href="{{ route('contacts/clients/view', $v->deposit_for) }}">{{@$v->depositForUser->full_name}} (Client)</a>
            @elseif($v->depositForUser->user_level=="4")
                <a class="name" href="{{ route('contacts/companies/view', $v->deposit_for) }}">{{$v->depositForUser->full_name}} (Company)</a>
            @endif
            about <abbr class="timeago" title="{{$v->all_history_created_at}}">{{ $v->time_ago }}</abbr> via web
            <?php  if($v->case_unique_number!=NULL){  ?>
                | <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
            <?php }  ?>
        </div>
    </td>
</tr>
