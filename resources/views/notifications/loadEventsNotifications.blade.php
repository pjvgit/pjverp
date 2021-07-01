<?php 
$CommonController= new App\Http\Controllers\CommonController();

if(!$commentData->isEmpty()){?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>

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
                        href="##{{BASE_URL}}event/view/{{base64_encode($v->event_id)}}"> {{$v->event_name}} </a> </a>
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

        <?php 
        } ?>
    </tbody>
</table>
<span class="EventsNotify">{!! $commentData->links() !!}</span>
<?php } else { 
echo "No recent activity available.";
}?>
