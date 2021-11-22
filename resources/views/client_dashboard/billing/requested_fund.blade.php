<h2 class="mx-2 mb-0 text-nowrap hiddenLable">        {{ ucfirst($userProfile->first_name) .' '.ucfirst($userProfile->last_name) }} (Client)    </h2>
<div class="container-fluid">
    <div class="justify-content-end pt-2 d-print-none row ">
        <div class="align-self-end text-right col-5">
            @can('billing_add_edit')
            <a data-toggle="modal" data-target="#addRequestFund" data-placement="bottom" href="javascript:;"
                onclick="addRequestFundPopup();">
                <button type="button" class="mx-1 btn btn-primary">Request Fund</button>
            </a>
            @endcan
        </div>
    </div>
</div>
<p><br></p>
<?php
if($totalData<=0){
?>
<div class="col-lg-12 col-md-12 col-sm-12">
    <div class=" card-icon-bg card-icon-bg-primary o-hidden mb-4">
        <div class="card-body text-center just-inn">
            <img alt="Expense Example" class="thumbnail" src="{{BASE_URL}}images/retainer_requests.png">
            <div class="text-container mb-4">
                <h2 class="">Manage your Requested Funds</h2>
                <ul class="">
                    <li> Fast and easy way to request Trust and Retainer funds from your clients. </li>
                    <li> Emails automatically sent and include your custom messaging. </li>
                    <li> With {{config('app.name')}} Payments, clients can pay anytime in just a few steps.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .just-inn ul {
        color: var(--gray-light);
        font-size: 14pt;
        font-weight: 400;
        padding-left: 20px;
        text-align: left;
    }

    .just-inn h2 {
        color: var(--lochmara-42);
        font-size: 18pt;
        margin-top: 0;
        text-align: left;
    }

    .thumbnail {
        float: left;
        height: 223px;
        width: 223px;
    }

</style>


<?php

}else{?>
<table class="display table table-striped table-bordered" id="requestFundGrid" style="width:100%">
    <thead>
        <tr>
            <th class="" style="cursor: initial;">Number</th>
            <th class="" style="cursor: initial;">Contact</th>
            <th class="" style="cursor: initial;">Account</th>
            <th class="" style="cursor: initial;">Allocated To</th>
            <th class="" style="cursor: initial;">Amount</th>
            <th class="" style="cursor: initial;">Paid To</th>
            <th class="" style="cursor: initial;">Amount Due</th>
            <th class="" style="cursor: initial;">Due</th>
            <th class="" style="cursor: initial;">Date Sent</th>
            <th class="" style="cursor: initial;">Viewed</th>
            <th class="" style="cursor: initial;">Status</th>
            <th class="text-right d-print-none " style="cursor: initial;width:10%;">Action</th>
        </tr>
    </thead>
</table>
<?php } ?>
