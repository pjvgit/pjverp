<?php 
$CommonController= new App\Http\Controllers\CommonController();
?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php 
        foreach($commentData as $k=>$v){
            if($v->type=="expenses"){
                ?>
            <tr role="row" class="odd">
                <td class="sorting_1" style="font-size: 13px;">
                    <div class="text-left">
                        <?php 
                        if($v->action=="add"){
                            $image="activity_expense_added.png";
                        }else if($v->action=="update"){
                            $image="activity_expense_updated.png";
                        }else if($v->action=="delete"){
                            $image="activity_expense_deleted.png";
                        }?>
                        <img src="{{BASE_URL}}public/icon/{{$image}}" width="27"height="21">
                        <a class="name" href="{{BASE_URL}}/contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for {{$v->title}}</a> <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |  <a class="name" href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                    </div>
                </td>
        </tr>
            <?php
            }else if($v->type=="time_entry"){
                ?>
        <tr role="row" class="odd">
            <td class="sorting_1" style="font-size: 13px;">
                <div class="text-left">
                    <?php 
                                if($v->action=="add"){
                                    $image="activity_time-entry_added.png";
                                }else if($v->action=="update"){
                                    $image="activity_time-entry_updated.png";
                                }else if($v->action=="delete"){
                                    $image="activity_time-entry_deleted.png";
                                }?>
                    <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                    <a class="name"
                        href="{{BASE_URL}}/contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for {{$v->title}}</a> <abbr
                        class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                </div>
            </td>
        </tr>
        <?php
            }else{ 
            ?>
        <tr role="row" class="odd">
            <td class="sorting_1" style="font-size: 13px;">
                <div class="text-left">
                    <?php 
                                        if($v->action=="add"){
                                            $image="activity_bill_added.png";
                                        }else if($v->action=="update"){
                                            $image="activity_bill_updated.png";
                                        }else if($v->action=="pay"){
                                            $image="activity_bill_paid.png";
                                        }else if($v->action=="delete"){
                                            $image="activity_bill_deleted.png";
                                        }?>
                    <?php 
                                        if(in_array($v->action,["add","update","delete"])){ ?>
                    <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                    <a class="name"
                        href="{{BASE_URL}}/contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}}
                    #{{sprintf('%06d', $v->activity_for)}}</a> <abbr class="timeago"
                        title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                    <?php } else{ ?>
                    <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                    <a class="name"
                        href="{{BASE_URL}}/contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
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
