<tr role="row" class="odd">
    <td class="sorting_1" style="font-size: 13px;">
        <div class="text-left">
            <?php 
            $ImageArray=[];
            $ImageArray['add']="activity_client_added.png";
            $ImageArray['update']="activity_client_updated.png";
            $ImageArray['link']="activity_client_linked.png";
            $ImageArray['unlink']="activity_client_unlinked.png";
            $ImageArray["pay"]="activity_ledger_deposited.png";            
            $ImageArray["close"]="activity_case_archived.png";
            $ImageArray["reopen"]="activity_case_unarchived.png";
            $ImageArray["delete"]="activity_case_deleted.png";
            $image=$ImageArray[$v->action];
            ?>
            <img src="{{ asset('images/'.$image) }}" width="27" height="21">
                <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">
                {{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})
                </a> {{$v->activity}} 
                
                <?php if($v->deleteCase != NULL){?>
                <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>                    
                <?php }else{?>
                    {{$v->case_title}}
                <?php } ?>

                <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web 
        </div>
    </td>
</tr>