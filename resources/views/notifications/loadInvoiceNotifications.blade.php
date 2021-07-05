<?php 
$CommonController= new App\Http\Controllers\CommonController();
if(!$commentData->isEmpty()){
?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>
        
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
                            <a class="name" href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} 
                            @if ($v->deleteInvoice == NULL)
                            <a href="{{BASE_URL}}bills/invoices/view/{{base64_encode($v->activity_for)}}"> #{{sprintf('%06d', $v->activity_for)}} </a> 
                            @else
                             #{{sprintf('%06d', $v->activity_for)}}
                            @endif
                            <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web | <a class="name" href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                    <?php } else{ ?>
                        <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                        <a class="name" href="{{BASE_URL}}/contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}} {{$v->last_name}} ({{$v->user_title}})</a> {{$v->activity}} for {{$v->title}}</a> <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |<a class="name" href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <?php }
         ?>
    </tbody>
</table>
<span class="InvoiceNotify">{!! $commentData->links() !!}</span>
<?php } else { 
    echo  "No recent activity available.";
}
?>

{{-- <div class="files-per-page-selector float-right" style="white-space: nowrap; margin-top: 5px;"><label
        for="rows-per-page" class="mr-2">Rows Per Page:</label><select id="per_page" name="per_page"
        class="custom-select w-auto">
        <option value="5" selected="">5</option>
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
    </select>
</div> --}}
