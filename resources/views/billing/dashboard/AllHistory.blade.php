<?php 
$CommonController= new App\Http\Controllers\CommonController();
if(!$commentData->isEmpty()){ ?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid" bladename="resources/views/billing/dashboard/AllHistory.blade.php">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>
        <?php if($v->type=="invoices" || $v->type=="lead_invoice"){?>
            @include('dashboard.include.invoice_activity_data')
        <?php }else if($v->type=="deposit"){?>
            @include('dashboard.include.deposit_activity_data')
        <?php }else if($v->type=="expenses"){ ?>
            @include('dashboard.include.expenses_activity_data')
        <?php }else if($v->type=="time_entry"){ ?>
            @include('dashboard.include.time_entry_activity_data')
        <?php }else if($v->type=="credit"){ ?>
            @include('dashboard.include.credit_activity_data')
        <?php }else if($v->type=="fundrequest"){ ?>
            @include('dashboard.include.fundrequest_activity_data')
        <?php } ?>
    <?php } ?>
    </tbody>
</table>
<?php } else{ ?>
No recent activity available.
<?php } ?>