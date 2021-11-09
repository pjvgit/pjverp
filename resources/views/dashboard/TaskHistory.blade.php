<?php 
$CommonController= new App\Http\Controllers\CommonController();
 
if(!$commentData->isEmpty()){ ?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php  foreach($commentData as $k=>$v){ 
        
        ?>
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
                    <a class="name" href="{{ $url }}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} 
                    <a class="name" href="{{ route('tasks',['id'=>$v->task_id]) }}"> {{$v->task_name}} </a> </a> <abbr
                        class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <?php  if($v->task_for_case!=NULL){  ?>  
                    <a class="name" href="{{ route('info',$v->task_for['case_unique_number']) }}">{{$v->task_for['case_title']}}</a>
                    <?php }else if($v->task_for_lead!=NULL){
                    \Log::info("resources/views/dashboard/TaskHistory.blade.php line 32 >".json_encode($v->task_for));
                    ?>  
                    <a class="name" href="{{ route('case_details/info', $v->task_for['id']) }}">{{@$v->task_for['first_name'] .' '.@$v->task_for['last_name']}}</a>
                    <?php } ?>                         
                </div>
            </td>
        </tr>
        <?php 
        }?>
    </tbody>
</table>
<?php } else{ ?>
No recent activity available.
<?php } ?>
