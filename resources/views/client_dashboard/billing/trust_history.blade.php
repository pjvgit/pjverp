<div class="container-fluid">
    <div class="justify-content-end pt-2 d-print-none row ">
        <div class="align-self-end text-right col-6">
            <a data-toggle="modal" data-target="#exportPDFpopup" data-placement="bottom" href="javascript:;" onclick="exportPDFpopup();"> 
                <button type="button" class="trust-history-export-pdf mx-1 btn  btn-outline-dark">Export PDF</button>
            </a>
            <a data-toggle="modal" data-target="#withdrawFromTrust" data-placement="bottom" href="javascript:;" onclick="withdrawFromTrust();">
                <button type="button" class="mx-1 btn btn-outline-info">Withdraw from Trust</button>
            </a>
            {{-- <a data-toggle="modal" data-target="#depositAmountPopup" data-placement="bottom" href="javascript:;" onclick="loadDepositPopup();">  --}}
            <a data-toggle="modal" data-target="#depositIntoTrust" data-placement="bottom" href="javascript:;" onclick="depositIntoTrust({{ $client_id }});"> 
                <button type="button" class="mx-1 btn btn-primary">Deposit into Trust</button>
            </a>
        </div>
    </div>
</div>
<p><br></p>
<table class="display table table-striped table-bordered" id="billingTabTrustHistory" style="width:100%">
    <thead>
        <tr>
            <th class="" style="cursor: initial;">Date</th>
            <th class="" style="cursor: initial;">Related To</th>
            <th class="" style="cursor: initial;">Details</th>
            <th class="" style="cursor: initial;">Payment Method</th>
            <th class="" style="cursor: initial;">Allocated To</th>
            <th class="" style="cursor: initial;">Amount</th>
            <th class="" style="cursor: initial;">Balance</th>
            <th class="text-right d-print-none" style="cursor: initial;">Action</th>
        </tr>
    </thead>
</table>
