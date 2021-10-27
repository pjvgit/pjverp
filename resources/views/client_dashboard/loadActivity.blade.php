<h2 class="mx-2 mb-0 text-nowrap hiddenLable">        {{ ucfirst($userProfile->first_name) .' '.ucfirst($userProfile->last_name) }} (Client)    </h2>
<?php
$CommonController= new App\Http\Controllers\CommonController();
?>
<div class="table-responsive">
    <table class="display table table-striped table-bordered" id="caseHistoryGrid" style="width:100%">
        <thead>
            <tr>
                <th width="100%">id</th>
            </tr>
        </thead>

    </table>
</div>
