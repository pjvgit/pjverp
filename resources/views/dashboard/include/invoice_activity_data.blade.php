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
                    $imageLink["share"]="activity_bill_shared.png";
                    $imageLink["unshare"]="activity_bill_unshared.png";
                    $imageLink["email"]="activity_bill_email_shared.png";
                    $imageLink["view"]="activity_bill_viewed.png";
                    $imageLink["pay_delete"]="activity_ledger_deleted.png";
                    $image=$imageLink[$v->action];
                    ?>
                    @if(in_array($v->action,["add","update","delete","pay","refund","pay_delete"]))
                        <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                        @if($v->user_level == '2') 
                        <a class="name" href="{{ route('contacts/clients/view', $v->created_by) }}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> 
                        @else
                        <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->created_by)) }}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} 
                        @endif
                        @if($v->action == "pay") for invoice  @endif
                        @if ($v->deleteInvoice == NULL)
                            @if($v->type == 'lead_invoice')
                            <a href="{{ route('bills/invoices/potentialview',base64_encode($v->activity_for)) }}"> #{{sprintf('%06d', $v->unique_invoice_number)}} </a> 
                            @else
                            <a href="{{ route('bills/invoices/view',base64_encode($v->activity_for)) }}"> #{{sprintf('%06d', $v->unique_invoice_number)}} </a> 
                            @endif
                        @else
                            #{{sprintf('%06d', $v->unique_invoice_number)}}
                        @endif 
                        <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                        <?php  if($v->case_unique_number!=NULL && $v->deleteCase ==NULL){  ?>
                            <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
                        <?php }else{ ?>
                            {{$v->case_title}}
                            <?php }    
                            if($v->type == 'lead_invoice'){  ?>
                            <a class="name" href="{{ route('case_details/info',$v->user_id) }}">{{$v->fullname}}</a>
                        <?php } ?>
                    @elseif(in_array($v->action,["share","unshare","email"]))
                        <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                        <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->created_by)) }}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> 
                        {{$v->activity}} 
                        
                        @if ($v->deleteInvoice == NULL)
                            @if($v->type == 'lead_invoice')
                            <a href="{{ route('bills/invoices/potentialview',base64_encode($v->activity_for)) }}"> #{{sprintf('%06d', $v->unique_invoice_number)}} </a> 
                            @else
                            <a href="{{ route('bills/invoices/view',base64_encode($v->activity_for)) }}"> #{{sprintf('%06d', $v->unique_invoice_number)}} </a> 
                            @endif
                        @else
                            #{{sprintf('%06d', $v->unique_invoice_number)}}
                        @endif 
                        {{ ($v->action == "unshare") ? "from the portal with" : (($v->action == "share") ? "in the portal with" : "") }}
                        @if($v->action == "email") to @endif
                        <a class="name" href="{{ route('contacts/clients/view', $v->client_id) }}">{{ $v->fullname }}</a>
                        <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web

                    @elseif(in_array($v->action,["view"]))
                        <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                        <a class="name" href="{{ route('contacts/clients/view', $v->user_id) }}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> 
                        {{$v->activity}} 
                        
                        @if ($v->deleteInvoice == NULL)
                            <a href="{{ route('bills/invoices/view',base64_encode($v->activity_for)) }}"> #{{sprintf('%06d', $v->unique_invoice_number)}} </a> 
                        @else
                            #{{sprintf('%06d', $v->unique_invoice_number)}}
                        @endif 
                        <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web
                        <?php  if($v->case_unique_number!=NULL && $v->deleteCase == NULL){  ?>
                            | <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
                        <?php }else{ ?>
                            | {{$v->case_title}}
                        <?php }  ?>
                    @else
                        <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                        <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->created_by)) }}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> 
                        {{$v->activity}} for {{$v->title}} 
                        <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                        <?php  if($v->case_unique_number!=NULL && $v->deleteCase == NULL){  ?>
                            <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
                        <?php }else{ ?>
                            {{$v->case_title}}
                        <?php }  ?>
                    @endif
                </div>
            </td>
        </tr>