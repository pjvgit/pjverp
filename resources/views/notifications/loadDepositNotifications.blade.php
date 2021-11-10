<?php 
$CommonController= new App\Http\Controllers\CommonController();
if(!$commentData->isEmpty()){
    ?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>
            @if($v->type =="fundrequest")
            @include('dashboard.include.fundrequest_activity_data')
        @else
            @include('dashboard.include.deposit_activity_data')
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
