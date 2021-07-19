<?php 
$CommonController= new App\Http\Controllers\CommonController();
if(!$commentData->isEmpty()){ ?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php 
        foreach($commentData as $k=>$v){
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
                        <img src="{{BASE_URL}}public/icon/{{$image}}" width="27"height="21">
                        <a class="name" href="{{BASE_URL}}/contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for {{$v->title}}</a> <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |  <a class="name" href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                    </div>
                </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php } else{ ?>
No recent activity available.
<?php } ?>
