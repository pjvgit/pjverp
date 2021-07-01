<?php 
$CommonController= new App\Http\Controllers\CommonController();
if(!$commentData->isEmpty()){
?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>
            <?php if($v->type=="contact"){?>
            <tr role="row" class="odd">
                <td class="sorting_1" style="font-size: 13px;">
                    <div class="text-left">
                        <?php 
                        $ImageArray=[];
                        $ImageArray['add']="activity_client_added.png";
                        $ImageArray['update']="activity_client_updated.png";
                        $ImageArray['link']="activity_client_linked.png";
                        $ImageArray["pay"]="activity_ledger_deposited.png";
                        $image=$ImageArray[$v->action];
                        ?>
                        <img src="{{BASE_URL}}public/images/{{$image}}" width="27" height="21">
                            <a class="name" href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">
                            {{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})
                            </a> {{$v->activity}} 
                            
                            <?php if($v->ulevel=="2"){?> <a class="name" href="{{BASE_URL}}contacts/clients/{{$v->client_id}}">{{$v->fullname}} (Client)</a>
                            <?php } ?>
                            
                            <?php if($v->ulevel=="4"){?> <a class="name" href="{{BASE_URL}}contacts/companies/{{$v->client_id}}">{{$v->fullname}} (Company)</a>
                            <?php } ?>

                            <?php if($v->action=="link"){ ?> to case <?php } ?>
                            
                            <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web 
                            <?php
                            if($v->case_title!=""){?>
                    |       <a class="name" href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>                    
                    <?php } ?>
                        </div>
                </td>
            </tr>

        <?php }else if($v->type=="document"){?>
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
                        <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                        <a class="name" href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->creator_name}} ({{$v->user_title}})</a> {{$v->activity}} </a>  <a href="#">{{$v->document_name}}</a>
                        <abbr class="timeago"
                            title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web  |
                            <a class="name"
                                href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                    </div>
                </td>
            </tr>

        <?php } else if($v->type=="deposit"){?>
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
                        <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                        <a class="name"
                            href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
                            {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} 
                        #R-{{sprintf('%06d', $v->deposit_id)}}</a>  
                        <?php if($v->ulevel=="2"){?>
                            to <a class="name" href="{{BASE_URL}}contacts/clients/{{$v->deposit_for}}">{{$v->fullname}} (Client)</a>
                        <?php } ?>

                        <?php if($v->ulevel=="4"){?>
                            to <a class="name"
                            href="{{BASE_URL}}contacts/companies/{{$v->deposit_for}}">{{$v->fullname}} (Company)</a>
                            <?php } ?>

                        {{$v->ulevel}} <abbr class="timeago"
                            title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web
                    </div>
                </td>
            </tr>
            <?php } else if($v->type=="task"){?>
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
                                $image=$imageLink[$v->action];
                            ?>
                            <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                            <a class="name"
                                href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
                                {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} <a class="name"
                                href="{{BASE_URL}}tasks?id={{$v->task_id}}"> {{$v->task_name}} </a> </a> <abbr class="timeago"
                                title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                            <?php  if($v->task_for_case!=NULL){  ?>
    
                            <a class="name"
                                href="{{BASE_URL}}court_cases/{{$v->task_for['case_unique_number']}}/info">{{$v->task_for['case_title']}}</a><?php
                                    }else if($v->task_for_lead!=NULL){
                                        ?> <a class="name"
                                href="{{BASE_URL}}leads/{{$v->task_for['id']}}/case_details/info">{{$v->task_for['first_name']}}
                                {{$v->task_for['last_name']}}</a><?php
                                        }
                                        ?>
    
                        </div>
                    </td>
                </tr>
        <?php }else if($v->type=="event"){?>
        <tr role="row" class="odd">
            <td class="sorting_1" style="font-size: 13px;">
                <div class="text-left">
                    <?php 
                        $imageLink=[];
                        $imageLink["add"]="activity_event_added.png";
                        $imageLink["update"]="activity_event_updated.png";
                        $imageLink["delete"]="activity_event_deleted.png";
                        $image=$imageLink[$v->action];
                    ?>
                    <img src="{{ asset('icon/'.$image) }}" width="27" height="21">
                    <a class="name"
                        href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} <a class="name"
                        href="{{BASE_URL}}event/view/{{base64_encode($v->event_id)}}"> {{$v->event_name}} </a> </a>
                    <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <?php  if($v->event_for_case!=NULL){  ?>

                    <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->events_for['case_unique_number']}}/info">{{$v->events_for['case_title']}}</a><?php
                              }else if($v->event_for_lead!=NULL){
                                ?> <a class="name"
                        href="{{BASE_URL}}leads/{{$v->events_for['id']}}/case_details/info">{{$v->events_for['first_name']}}
                        {{$v->events_for['last_name']}}</a><?php
                                }
                                ?>

                </div>
            </td>
        </tr>

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


                    <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                    <?php if($v->notes_for_case!=NULL){?>
                    <a class="name"
                        href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for case <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->notes_for['case_unique_number']}}/info"><?php echo $v->notes_for['case_title'];?>
                    </a> <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via
                    web |
                    <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->notes_for['case_unique_number']}}/info"><?php echo $v->notes_for['case_title'];?>
                    </a>
                    <?php } ?>

                    <?php if($v->notes_for_client!=NULL){?>
                    <a class="name"
                        href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->notes_for['id'])}}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for client <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->notes_for['case_unique_number']}}/info"><?php echo $v->notes_for['first_name'] .' '.$v->notes_for['last_name'];?>
                        (Client)</a> <abbr class="timeago" title="{{$v->all_history_created_at}}">about
                        {{$v->time_ago}}</abbr> via web
                    <?php } ?>
                    <?php if($v->notes_for_company!=NULL){?>
                    <a class="name"
                        href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->notes_for['id'])}}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for company <a class="name"
                        href="{{BASE_URL}}contacts/companies/{{$v->notes_for_company}}"><?php echo $v->notes_for['first_name'] .' '.$v->notes_for['last_name'];?>
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
                    <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                    <a class="name"
                        href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for  <?php if($v->action!="delete"){ ?><a data-toggle="modal"  data-target="#loadEditExpenseEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadEditExpenseEntryPopup({{$v->expense_id}});">  {{$v->title}} </a> <?php }else{ ?>{{$v->title}} <?php } ?>  <abbr
                        class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
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
                    <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                    <a class="name"
                        href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for <?php if($v->action!="delete"){ ?> <a data-toggle="modal"  data-target="#loadEditTimeEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadEditTimeEntryPopup({{$v->time_entry_id}});"> {{$v->title}}</a>  <?php }else{ ?> {{$v->title}}<?php } ?><abbr
                        class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                </div>
            </td>
        </tr>
        <?php
            }else if($v->type=="invoices"){
            ?>
        <tr role="row" class="odd">
            <td class="sorting_1" style="font-size: 13px;">
                <div class="text-left">
                    <?php 
                    $imageLink=[];
                    $imageLink["add"]="activity_bill_added.png";
                    $imageLink["update"]="activity_bill_updated.png";
                    $imageLink["delete"]="activity_bill_deleted.png";
                    $imageLink["pay"]="activity_bill_paid.png";
                    $image=$imageLink[$v->action];
                                        if(in_array($v->action,["add","update","delete"])){ ?>
                    <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                    <a class="name"
                        href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} <a href="{{BASE_URL}}bills/invoices/view/{{base64_encode($v->activity_for)}}">
                    #{{sprintf('%06d', $v->activity_for)}}</a> <abbr class="timeago"
                        title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                    <?php } else{ ?>
                    <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                    <a class="name"
                        href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for {{$v->title}}</a> <abbr
                        class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <?php }
        } ?>
    </tbody>
</table>
<span class="AllNotify">{!! $commentData->links() !!}</span>
<?php } else { 
    echo  "No recent activity available.";
}
?>
