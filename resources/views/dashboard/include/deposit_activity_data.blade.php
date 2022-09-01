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
            $image=$ImageArray[$v->action];
            ?>
            <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
            <a class="name" href="{{ ($v->user_level == '2') ? route('contacts/clients/view', $v->user_id) : route('contacts/attorneys/info', base64_encode($v->created_by)) }}">
                {{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})
            </a> 
                {{$v->activity}} 
            <?php if($v->deposit_id){ ?>
                #R-{{sprintf('%05d', $v->deposit_id)}}
            <?php } if($v->ulevel=="2" && $v->deposit_for){?>
                <a class="name" href="{{ route('contacts/clients/view', $v->deposit_for) }}">{{$v->fullname}} (Client)</a>
            <?php } if($v->ulevel=="4" && $v->deposit_for){?>
                <a class="name" href="{{ route('contacts/companies/view', $v->deposit_for) }}">{{$v->fullname}} (Company)</a>
            <?php } if($v->ulevel=="5"  && $v->deposit_for != ''){ ?>
                for <a class="name" href="{{ route('case_details/invoices', @$v->deposit_for) }}">{{$v->fullname}} (Lead)</a>
            <?php } ?>                                        
            <abbr class="timeago"  title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web
        </div>
    </td>
</tr>