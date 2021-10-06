<?php 
$CommonController= new App\Http\Controllers\CommonController();
if(!$commentData->isEmpty()){
    ?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>
            @if($v->type =="fundrequest")
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
                            #R-{{sprintf('%06d', $v->deposit_id)}}
                        @endif
                        @if($v->action == "share")
                            @if($v->client_level=="2" && !empty($v->client_id))
                                to <a class="name" href="{{ route('contacts/clients/view', @$v->client_id) }}">{{$v->client_name}} (Client)</a>
                            @endif

                            @if($v->client_level=="4" && !empty($v->client_id))
                                to <a class="name" href="{{ route('contacts/companies/view', @$v->client_id) }}">{{$v->client_name}} (Company)</a>
                            @endif
                        @endif
                        <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web
                    </div>
                </td>
            </tr>
        @else
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
                        #R-{{sprintf('%06d', $v->deposit_id)}}</a>  
                        <?php if($v->ulevel=="2"){?>
                            to <a class="name" href="{{ route('contacts/clients/view', $v->deposit_for) }}">{{$v->fullname}} (Client)</a>
                        <?php } ?>

                        <?php if($v->ulevel=="4"){?>
                            to <a class="name"
                            href="{{ route('contacts/companies/view', $v->deposit_for) }}">{{$v->fullname}} (Company)</a>
                            <?php } ?>

                        <abbr class="timeago"
                            title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web
                    </div>
                </td>
            </tr>
        @endif
        <?php 
        } ?>
    </tbody>
</table>
<span class="DepositNotify">{{$commentData->links()}}</span>
<?php } else { 
    echo  "No recent activity available.";
}
?>
