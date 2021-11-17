<tr role="row" class="odd">
    <td class="sorting_1" style="font-size: 13px;">
        <div class="text-left">
            <?php 
                    $imageLink=[];
                    $imageLink["add"]="activity_note_added.png";
                    $imageLink["update"]="activity_note_updated.png";
                    $imageLink["delete"]="activity_note_deleted.png";
                    $image=$imageLink[$v->action];
                ?>


            <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
            <?php if($v->notes_for_case!=NULL){?>
            <a class="name"
                href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name}}
                {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for case <a class="name"
                href="{{ route('info', $v->notes_for['case_unique_number']) }}"><?php echo $v->notes_for['case_title'];?>
            </a> <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via
            web |
            <a class="name"
                href="{{ route('info', $v->notes_for['case_unique_number']) }}"><?php echo $v->notes_for['case_title'];?>
            </a>
            <?php } ?>

            <?php if($v->notes_for_client!=NULL){?>
            <a class="name"
                href="{{ route('contacts/attorneys/info', base64_encode($v->notes_for['id'])) }}">{{$v->first_name}}
                {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for client <a class="name"
                href="{{ route('contacts/clients/view', $v->notes_for['id']) }}"><?php echo $v->notes_for['first_name'] .' '.$v->notes_for['last_name'];?>
                {{"(".$v->notes_for['user_title'].")"}}</a> <abbr class="timeago" title="{{$v->all_history_created_at}}">about
                {{$v->time_ago}}</abbr> via web
            <?php } ?>
            <?php if($v->notes_for_company!=NULL){?>
            <a class="name"
                href="{{ route('contacts/attorneys/info',base64_encode($v->notes_for['id'])) }}">{{$v->first_name}}
                {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for company <a class="name"
                href="{{ route('contacts/companies/view', $v->notes_for_company) }}"><?php echo $v->notes_for['first_name'] .' '.$v->notes_for['last_name'];?>
                (Company)</a> <abbr class="timeago" title="{{$v->all_history_created_at}}">about
                {{$v->time_ago}}</abbr> via web
            <?php } ?>
        </div>
    </td>
</tr>