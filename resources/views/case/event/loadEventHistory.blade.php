<?php
$userTypes = unserialize(USER_TYPE);
 if(count($commentData) > 0){
     foreach ($commentData as $key => $value) {
         if($value->action_type=="1"){
        ?>
        <div class="history-item row ">
            <div class="history-date col-4">
                <p class="date-string">
                    <?php 
                    $CommonController= new App\Http\Controllers\CommonController();
                    $updateDate=$CommonController->convertUTCToUserTime($value->created_at,Auth::User()->user_timezone);?>
                    {{date('D, M jS Y',strtotime($updateDate))}}<br>
                    {{date('h:ia',strtotime($updateDate))}}</p>
            </div>
            <div class="history-info col-8">
                <span>Event updated by
                    <a class=""
                        href="{{BASE_URL}}contacts/attorneys/{{base64_encode($value->created_by)}}">{{substr($value->first_name,0,15)}}
                        {{substr($value->last_name,0,15)}}
                        {{-- ({{$userTypes[$value->user_type]}}) --}}
                        ({{ userTypeList()[$value->user_type] }})
                    </a>
                </span>
            </div>
        </div>
    <?php
         }else{
     ?>
<div class="history-item row ">
    <div class="history-date col-4">
        <p class="date-string">
            <?php 
                    $CommonController= new App\Http\Controllers\CommonController();
                    $OwnDate=$CommonController->convertUTCToUserTime($value->created_at,Auth::User()->user_timezone);?>
            {{date('D, M jS Y',strtotime($OwnDate))}}<br>
            {{date('h:ia',strtotime($OwnDate))}}</p>
    </div>
    <div class="history-info d-flex col-8">
        <img class="comment-avatar" src="{{ asset('assets/images/faces/default_face.svg') }}" width="32" height="32">
        <div class="flex-grow-1">
            <p class="comment-user-link mt-1">
                <a class=""
                    href="{{ route('contacts/attorneys/info', base64_encode($value->id)) }}">{{substr($value->first_name,0,15)}}
                    {{substr($value->last_name,0,15)}}
                    {{-- ({{$userTypes[$value->user_type]}}) --}}
                    ({{ userTypeList()[$value->user_type] }})
                </a> commented</p>
            <div class="comment-message">
                <?php print $value->comment; ?>

            </div>
        </div>
    </div>
</div>
<?php 
         }
    }
}
?>

<div class="history-item row ">
    <?php if(!empty($evetData)){ ?>
    <div class="history-date col-4">
        <p class="date-string">
            <?php 
            $CommonController= new App\Http\Controllers\CommonController();
            $creatdDate=$CommonController->convertUTCToUserTime($evetData->created_at,Auth::User()->user_timezone);?>
            {{date('D, M jS Y',strtotime($creatdDate))}}<br>
            {{date('h:ia',strtotime($creatdDate))}}
        </p>
    </div>
    <?php  } ?>
    @if($eventCreatedBy != '')
    <div class="history-info col-8">
        <span>Event added by
            <a class=""
                href="{{ route('contacts/attorneys/info', base64_encode($eventCreatedBy->id)) }}">{{substr($eventCreatedBy->first_name,0,15)}}
                {{substr($eventCreatedBy->last_name,0,15)}}
                {{-- ({{$userTypes[$eventCreatedBy->user_type]}}) --}}
                ({{ userTypeList()[$value->user_type] }})
            </a>
        </span>
    </div>
    @endif
</div>
