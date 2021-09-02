<?php
 if(!empty($taskHistoryData)){
    $userTypes = unserialize(USER_TYPE);

    $CommonController= new App\Http\Controllers\CommonController();
     foreach ($taskHistoryData as $key => $value) {
     ?>
        <li class="px-0 list-group-item">
            <div class="w-100 no-gutters row ">
                <?php $OwnDate=$CommonController->convertUTCToUserTime($value->created_at,Auth::User()->user_timezone);?>
                <div class="text-nowrap col-12 col-sm-4 col-md-3">
                    <span> {{date('m/d/y',strtotime(convertUTCToUserDate($OwnDate, auth()->user()->user_timezone)))}} {{date('h:ia',strtotime($OwnDate))}}</span>
                </div>&nbsp;&nbsp;
                <div class="col">
                    <div>
                        <strong>{{$value->task_action}}</strong>
                        <span class="pl-1">– <span>{{substr($value->created_by_name,0,25)}} ({{$userTypes[$value->user_type]}})</span></span>
                    </div>
                </div>
            </div>
        </li>
<?php }
    }
?>
