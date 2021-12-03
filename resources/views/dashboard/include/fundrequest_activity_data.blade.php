<tr role="row" class="odd">
    <td class="sorting_1" style="font-size: 13px;">
        <div class="text-left">
            <?php 
            $ImageArray=[];
            $ImageArray['add']="activity_bill_added.png";
            $ImageArray['update']="activity_bill_updated.png";
            $ImageArray['share']="activity_bill_shared.png";
            $ImageArray['delete']="activity_bill_deleted.png";
            $ImageArray["view"]="activity_bill_viewed.png";
            $ImageArray["pay"]="activity_bill_paid.png";
            $image=$ImageArray[$v->action];
            ?>
            <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
            <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name}}
                {{$v->last_name}} ({{$v->user_title}})
            </a> 
                {{$v->activity}} 
            @if($v->deposit_id)
                #R-{{sprintf('%05d', $v->deposit_id)}}
            @endif
            @if($v->action == "share")
            @if($v->ulevel=="2" && !empty($v->client_id))
                to <a class="name" href="{{ route('contacts/clients/view', $v->client_id) }}">{{$v->fullname}} (Client)</a>
            @endif
            @if($v->ulevel=="4" && !empty($v->client_id))
                to <a class="name" href="{{ route('contacts/companies/view', $v->client_id) }}">{{$v->fullname}} (Company)</a>
            @endif
            @if($v->ulevel=="5" && !empty($v->client_id))
                to <a class="name" href="{{ route('case_details/invoices', @$v->client_id) }}">{{$v->fullname}} (Lead)</a>
            @endif
            @endif
            <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web
            <?php  if($v->case_unique_number!=NULL){  ?>
                | <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
            <?php }  ?>
        </div>
    </td>
</tr>