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
            $ImageArray["change"]="activity_attorney_permissions.png";
            $ImageArray["archive"]="activity_attorney_archived.png";
            $image=$ImageArray[$v->action];
            ?>
            <img src="{{ asset('images/'.$image) }}" width="27" height="21">
                <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">
                {{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})
                </a> {{$v->activity}} 
                
                <?php if($v->ulevel=="2"){?> <a class="name" href="{{ route('contacts/clients/view', $v->client_id) }}">{{$v->fullname}} (Client)</a>
                <?php } ?>
                
                <?php if($v->ulevel=="4"){?> <a class="name" href="{{route('contacts/companies/view',$v->client_id) }}">{{$v->fullname}} (Company)</a>
                <?php } ?>

                <?php if($v->ulevel=="3"){?> <a class="name" href="{{route('contacts/attorneys/info',base64_encode($v->client_id)) }}">{{$v->fullname}} ({{$v->user_title}})</a>
                <?php } ?>
                
                <?php if($v->action=="link"){ ?> to case <?php
                    if($v->case_title!=""){?>
                    <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>                    
                    <?php } ?>
                <?php } ?>
                <?php if($v->action=="unlink"){ ?> from case  <?php
                    if($v->case_title!=""){?>
                    <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>                    
                    <?php } ?>
                <?php } ?>                
                <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web 
                <?php
                if($v->case_title!=""){?>
        |       <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>                    
        <?php } ?>
            </div>
    </td>
</tr>
