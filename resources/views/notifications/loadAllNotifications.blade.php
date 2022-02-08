<?php 
$CommonController= new App\Http\Controllers\CommonController();
if(!$commentData->isEmpty()){
?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid" bladefile="resources/views/notifications/loadAllNotifications.blade.php">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>
        <?php if($v->type=="case"){?>
            @include('dashboard.include.case_activity_data')    
        <?php }else if($v->type=="contact"){?>
            @include('dashboard.include.contact_activity_data')
        <?php }else if($v->type=="staff"){?>
            @include('dashboard.include.contact_activity_data')
        <?php }else if($v->type=="document"){?>
            @include('dashboard.include.document_activity_data')
        <?php } else if($v->type=="deposit"){?>
            @include('dashboard.include.deposit_activity_data')
        <?php } else if($v->type=="task"){?>
            @include('dashboard.include.task_activity_data')
        <?php }else if($v->type=="event"){?>
            @include('dashboard.include.event_activity_data')
        <?php }else if($v->type=="notes"){?>
            @include('dashboard.include.notes_activity_data')
        <?php } else if($v->type=="expenses"){ ?>
            @include('dashboard.include.expenses_activity_data')
        <?php }else if($v->type=="time_entry"){ ?>
            @include('dashboard.include.time_entry_activity_data')
        <?php }else if($v->type=="invoices" || $v->type=="lead_invoice"){ ?>
            @include('dashboard.include.invoice_activity_data')
        <?php }else if($v->type=="other"){ ?>
            @include('dashboard.include.other_activity_data')
        <?php } ?>
        @if($v->type == "credit")
            @include('dashboard.include.credit_activity_data')
        @elseif($v->type =="fundrequest")
            @include('dashboard.include.fundrequest_activity_data')
        @elseif($v->type == "user")
            @include('dashboard.include.user_activity_data')
        @endif
        <?php 
        } ?>
    </tbody>
</table>
@if(isset($request) && $request->ajax() && $request->per_page != '')
<span class="AllNotify">{!! $commentData->links() !!}</span>
@endif
<?php } else { 
    echo  "No recent activity available.";
}
?>
