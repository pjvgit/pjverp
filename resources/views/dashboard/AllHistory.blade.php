<?php if(!$commentData->isEmpty()){ ?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid" bladefile="resources/views/dashboard/AllHistory.blade.php">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>
             <?php  if($v->type=="deposit"){?>
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
                        <a class="name"
                            href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name}}
                            {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} 
                        @if($v->deposit_id)
                            #R-{{sprintf('%05d', $v->deposit_id)}}
                        @endif 
                        <?php if($v->ulevel=="2" && $v->deposit_for){?>
                            to <a class="name" href="{{ route('contacts/clients/view', $v->deposit_for) }}">{{$v->fullname}} (Client)</a>
                        <?php } ?>

                        <?php if($v->ulevel=="4" && $v->deposit_for){?>
                            to <a class="name"
                            href="{{route('contacts/companies/view', $v->deposit_for) }}">{{$v->fullname}} (Company)</a>
                            <?php } ?>

                        {{$v->ulevel}} <abbr class="timeago"
                            title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web
                    </div>
                </td>
            </tr>
            <?php } else if($v->type=="document"){?>
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
                            $ImageArray["view"]="activity_bill_viewed.png";
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
    
        <?php }else if($v->type=="task"){?>
        <tr role="row" class="odd">
            <td class="sorting_1" style="font-size: 13px;">
                <div class="text-left">
                    <?php 
                        $imageLink=[];
                        $imageLink["add"]="activity_task_added.png";
                        $imageLink["update"]="activity_task_updated.png";
                        $imageLink["delete"]="activity_task_deleted.png";
                        $imageLink["incomplete"]="activity_task_incomplete.png";
                        $imageLink["complete"]="activity_task_completed.png";
                        $imageLink["view"]="activity_bill_viewed.png";
                        $imageLink["comment"]="activity_task_commented.png";
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
                        {{$v->activity}} <a class="name"
                        href="{{ route('tasks',['id'=>$v->task_id]) }}"> {{$v->task_name}} </a> </a> <abbr class="timeago"
                        title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <?php  if($v->task_for_case!=NULL){  ?>

                    <a class="name"
                        href="{{ route('info', $v->task_for['case_unique_number']) }}">{{$v->task_for['case_title']}}</a><?php
                              }else if($v->task_for_lead!=NULL){
                                ?> <a class="name"
                        href="{{route('case_details/info', $v->task_for['id']) }}">{{$v->task_for['first_name']}}
                        {{$v->task_for['last_name']}}</a><?php
                                }
                                ?>
                </div>
            </td>
        </tr>
        <?php }else if($v->type=="event"){?>
            @include('dashboard.include.event_activity_data')
        <?php }else if($v->type=="notes"){?>
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
                        href="{{route('contacts/attorneys/info', base64_encode($v->notes_for['id'])) }}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for client <a class="name"
                        href="{{ route('contacts/clients/view', base64_encode($v->notes_for['id'])) }}"><?php echo $v->notes_for['first_name'] .' '.$v->notes_for['last_name'];?>
                        (Client)</a> <abbr class="timeago" title="{{$v->all_history_created_at}}">about
                        {{$v->time_ago}}</abbr> via web
                    <?php } ?>
                    <?php if($v->notes_for_company!=NULL){?>
                    <a class="name"
                        href="{{route('contacts/attorneys/info', base64_encode($v->notes_for['id'])) }}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for company <a class="name"
                        href="{{route('contacts/companies/view', $v->notes_for_company)}}"><?php echo $v->notes_for['first_name'] .' '.$v->notes_for['last_name'];?>
                        (Company)</a> <abbr class="timeago" title="{{$v->all_history_created_at}}">about
                        {{$v->time_ago}}</abbr> via web
                    <?php } ?>
                </div>
            </td>
        </tr>
        <?php } else if($v->type=="expenses"){ ?>
        <tr role="row" class="odd">
            <td class="sorting_1" style="font-size: 13px;">
                <div class="text-left">
                    <?php 
                    $imageLink=[];
                    $imageLink["add"]="activity_expense_added.png";
                    $imageLink["update"]="activity_expense_updated.png";
                    $imageLink["delete"]="activity_expense_deleted.png";
                    $image=$imageLink[$v->action];
                ?>
                    <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                    <a class="name"
                        href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for 
                        <?php if($v->ExpenseEntry){ ?><a data-toggle="modal"  data-target="#loadEditExpenseEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadEditExpenseEntryPopup({{$v->expense_id}});">  {{$v->title}} </a> <?php }else{ ?>{{$v->title}} <?php } ?>  
                        <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web 
                    <a class="name"
                        href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
                </div>
            </td>
        </tr><?php
            }else if($v->type=="time_entry"){
                ?>
        <tr role="row" class="odd">
            <td class="sorting_1" style="font-size: 13px;">
                <div class="text-left">
                    <?php 
                    $imageLink=[];
                    $imageLink["add"]="activity_time-entry_added.png";
                    $imageLink["update"]="activity_time-entry_updated.png";
                    $imageLink["delete"]="activity_time-entry_deleted.png";
                    $image=$imageLink[$v->action];
                ?>
                    <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                    <a class="name"
                        href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for 
                        <?php if(!$v->timeEntry){ ?> <a data-toggle="modal"  data-target="#loadEditTimeEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadEditTimeEntryPopup({{$v->time_entry_id}});"> {{$v->title}}</a>  <?php }else{ ?> {{$v->title}}<?php } ?>
                        <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <a class="name"
                        href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>
                </div>
            </td>
        </tr>
        <?php
            }else if($v->type=="invoices" || $v->type=="lead_invoice"){
            ?>
        @include('dashboard.include.invoice_activity_data')
        <?php } ?>
        @if($v->type == "credit")
        <tr role="row" class="odd">
            <td class="sorting_1" style="font-size: 13px;">
                <div class="text-left">
                    <?php 
                    $ImageArray=[];
                    $ImageArray['add']="activity_bill_added.png";
                    $ImageArray['update']="activity_bill_updated.png";
                    $ImageArray['share']="activity_bill_shared.png";
                    $ImageArray['delete']="activity_bill_deleted.png";
                    $image=$ImageArray[$v->action];
                    ?>
                    <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                    <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">{{$v->first_name.' '.$v->last_name}} ({{$v->user_title}})</a> 
                    {{$v->activity}} 
                    @if($v->depositForUser->user_level=="2")
                        <a class="name" href="{{ route('contacts/clients/view', $v->deposit_for) }}">{{@$v->depositForUser->full_name}} (Client)</a>
                    @elseif($v->depositForUser->user_level=="4")
                        <a class="name" href="{{ route('contacts/companies/view', $v->deposit_for) }}">{{$v->depositForUser->full_name}} (Company)</a>
                    @endif
                    about <abbr class="timeago" title="{{$v->all_history_created_at}}">{{ $v->time_ago }}</abbr> via web
                </div>
            </td>
        </tr>
        @elseif($v->type =="fundrequest")
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
                                to <a class="name" href="{{ route('contacts/clients/view', @$v->client_id) }}">{{$v->fullname}} (Client)</a>
                            @endif

                            @if($v->ulevel=="4" && !empty($v->client_id))
                                to <a class="name" href="{{ route('contacts/companies/view', @$v->client_id) }}">{{$v->fullname}} (Company)</a>
                            @endif
                        @endif
                        <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web
                    </div>
                </td>
            </tr>
        @elseif($v->type == "user")
            @include('dashboard.include.user_activity_data')
        @elseif($v->type == "case")
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
                        $image=$ImageArray[$v->action];
                        ?>
                        <img src="{{ asset('images/'.$image) }}" width="27" height="21">
                            <a class="name" href="{{ route('contacts/attorneys/info', base64_encode($v->user_id)) }}">
                            {{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})
                            </a> {{$v->activity}} 
                            <?php if($v->case_title!=""){?>
                            <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>                    
                            <?php } ?>

                            <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web 
                            <?php if($v->case_title!=""){?>
                            |       <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>                    
                            <?php } ?>
                        </div>
                </td>
            </tr>
        @elseif($v->type == "contact")                                
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
                            
                            <?php if($v->action=="link"){ ?> to case <?php } ?>
                            <?php if($v->action=="unlink"){ ?> from case <?php } ?>
                            
                            <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web 
                            <?php
                            if($v->case_title!=""){?>
                    |       <a class="name" href="{{ route('info',$v->case_unique_number) }}">{{$v->case_title}}</a>                    
                    <?php } ?>
                        </div>
                </td>
            </tr>

            @endif
    <?php } ?>
    </tbody>
</table>
<?php } else{ ?>
No recent activity available.
<?php } ?>