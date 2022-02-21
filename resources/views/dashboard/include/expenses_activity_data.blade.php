<tr role="row" class="odd">
    <td class="sorting_1" style="font-size: 13px;">
        <div class="text-left">
            <?php 
            $imageLink=[];
            $imageLink["add"]="activity_expense_added.png";
            $imageLink["update"]="activity_expense_updated.png";
            $imageLink["delete"]="activity_expense_deleted.png";
            $image=$imageLink[$v->action];
        ?>
            <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
            <a class="name"
                href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name}}
                {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for 
                <?php if($v->ExpenseEntry){ ?><a data-toggle="modal"  data-target="#loadEditExpenseEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadEditExpenseEntryPopup({{$v->expense_id}});">  {{$v->title}} </a> <?php }else{ ?>{{$v->title}} <?php } ?>  
                <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
            @if($v->deleteCase == '')
                <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
            @else
                {{$v->case_title}}
            @endif
        </div>
    </td>
</tr>