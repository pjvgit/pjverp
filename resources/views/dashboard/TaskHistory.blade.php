<?php 
$CommonController= new App\Http\Controllers\CommonController();
 
if(!$commentData->isEmpty()){ ?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php  foreach($commentData as $k=>$v){ ?>
        @include('dashboard.include.task_activity_data')
        <?php } ?>
    </tbody>
</table>
<?php } else{ ?>
No recent activity available.
<?php } ?>
