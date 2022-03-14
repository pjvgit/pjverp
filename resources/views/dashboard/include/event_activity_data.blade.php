<tr role="row" class="odd">
    <td class="sorting_1" style="font-size: 13px;">
        <div class="text-left">
            <?php 
            $imageLink=[];
            $imageLink["add"]="activity_event_added.png";
            $imageLink["update"]="activity_event_updated.png";
            $imageLink["delete"]="activity_event_deleted.png";
            $imageLink["comment"]="activity_event_commented.png";
            $imageLink["view"]="activity_event_viewed.png";
            $image=$imageLink[$v->action];
        ?>
        <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
        @php
            if($v->user_level == 2) {
                $url = route('contacts/clients/view', $v->user_id);   
            } else {                                
                $url = route('contacts/attorneys/info', base64_encode($v->user_id));
            }
        @endphp
        <a class="name" href="{{ $url }}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> 
        {{$v->activity}}
        @if($v->deleteEvents == null && $v->delete_recurring_event == Null) 
            <a class="name" href="{{ route('events/detail',base64_encode($v->event_id)) }}"> {{$v->eventTitle}} </a> 
        @else 
            {{$v->event_name}} 
        @endif
        <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web 
        <?php  if($v->event_for_case!=NULL && $v->events_for != NULL){  ?>
            | <a class="name" href="{{ route('info', $v->events_for['case_unique_number']) }}">{{$v->events_for['case_title']}}</a>
        <?php }else if($v->event_for_lead!=NULL){ ?>
            | <a class="name" href="{{ route('case_details/info',$v->events_for['id']) }}">{{$v->events_for['first_name']}} {{$v->events_for['last_name']}}</a>
        <?php } ?>
        </div>
    </td>
</tr>