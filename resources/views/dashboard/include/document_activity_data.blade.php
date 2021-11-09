<tr role="row" class="odd">
    <td class="sorting_1" style="font-size: 13px;">
        <div class="text-left">
            <?php 
            $ImageArray=[];
            $ImageArray['add']="activity_document_added.png";
            $ImageArray['update']="activity_document_updated.png";
            $ImageArray['delete']="activity_document_deleted.png";
            $ImageArray['archive']="activity_document_archived.png";
            $ImageArray['unarchive']="activity_document_unarchived.png";
            $ImageArray['comment']="activity_document_commented.png";
            $image=$ImageArray[$v->action];
            ?>
            <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
            <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->creator_name}} ({{$v->user_title}})</a> {{$v->activity}} </a>  <a href="#">{{$v->document_name}}</a>
            <abbr class="timeago"
                title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web  |
                <a class="name"
                    href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
        </div>
    </td>
</tr>