<?php 
$CommonController= new App\Http\Controllers\CommonController();
 
if(!$commentData->isEmpty()){ ?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>
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
                    <img src="{{BASE_URL}}icon/{{$image}}" width="27" height="21">
                    <a class="name"
                        href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->creator_name}}
                        ({{$v->user_title}})</a> {{$v->activity}} </a> <a href="#">{{$v->document_name}}</a>
                    <abbr class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                </div>
            </td>
        </tr>

        <?php 
    } ?>
    </tbody>
</table>
<?php } else{ ?>
No recent activity available.
<?php } ?>
