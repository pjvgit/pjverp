<tr role="row" class="odd">
    <td class="sorting_1" style="font-size: 13px;">
        <div class="text-left">
            @php
                $imageLink=[];
                $imageLink["login"]="activity_client_login.png";
                $image=$imageLink[$v->action];
            @endphp
            <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
            <a class="name" href="{{ route('contacts/clients/view', $v->client_id) }}">{{ $v->fullname }} (Client)</a>
            {{$v->activity}} 
            <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web
        </div>
    </td>
</tr>