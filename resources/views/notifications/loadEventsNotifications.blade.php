<?php 
$CommonController= new App\Http\Controllers\CommonController();

if(!$commentData->isEmpty()){?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>
        @include('dashboard.include.event_activity_data')
        <?php  } ?>
    </tbody>
</table>
<span class="EventsNotify">{!! $commentData->links() !!}</span>
<?php } else { 
echo "No recent activity available.";
}?>
