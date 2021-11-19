<?php 
$CommonController= new App\Http\Controllers\CommonController();
if(!$commentData->isEmpty()){
?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>       
            @include('dashboard.include.time_entry_activity_data')
        <?php } ?>
    </tbody>
</table>
@if(isset($request) && $request->ajax() && $request->per_page != '')
<span class="TimeEntryNotify">{!! $commentData->links() !!}</span>
@endif
<?php } else {   echo  "No recent activity available."; } ?>