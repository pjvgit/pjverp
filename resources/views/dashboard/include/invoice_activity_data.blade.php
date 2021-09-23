<tr role="row" class="odd">
            <td class="sorting_1" style="font-size: 13px;">
                <div class="text-left">
                    <?php 
                    $imageLink=[];
                    $imageLink["add"]="activity_bill_added.png";
                    $imageLink["update"]="activity_bill_updated.png";
                    $imageLink["delete"]="activity_bill_deleted.png";
                    $imageLink["pay"]="activity_bill_paid.png";
                    $imageLink["refund"]="activity_bill_refunded.png";
                    $image=$imageLink[$v->action];
                    
                    if(in_array($v->action,["add","update","delete","pay","refund"])){ ?>
                    <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                    <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}}  for invoice
                    @if ($v->deleteInvoice == NULL)
                        <a href="{{ route('bills/invoices/view',base64_encode($v->activity_for)) }}"> #{{sprintf('%06d', $v->activity_for)}} </a> 
                    @else
                        #{{sprintf('%06d', $v->activity_for)}}
                    @endif 
                    <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <?php  if($v->case_unique_number!=NULL){  ?>
                        <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
                    <?php } ?>
                    <?php } else{ ?>
                    <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                    <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> 
                    {{$v->activity}} for {{$v->title}} 
                    <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <?php  if($v->case_unique_number!=NULL){  ?>
                        <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
                    <?php }  ?>
                    <?php } ?>
                </div>
            </td>
        </tr>