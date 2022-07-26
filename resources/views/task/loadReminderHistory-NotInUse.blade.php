<div class="my-reminders mb-2 row ">
    <?php 
    if(!$eventReminderData->isEmpty()){
        foreach($eventReminderData as $kk=>$vv){?>
    <div class="pr-0 col-4">
        <span>{{ucfirst($vv->reminder_user_type)}}</span>
    </div>
    <div class="pl-0 col-8">
        <li class="reminder-list-item">{{ucfirst($vv->reminder_type)}}
            {{$vv->reminer_number}} {{$vv->reminder_frequncy}}(s) before event
        </li>
    </div>
    <?php } }
    else{
        ?>
    <div class="detail-info  col-9">
        <p class="d-inline" style="opacity: 0.7;">None</p>
    </div>
    <?php
    }
    ?>
</div>
