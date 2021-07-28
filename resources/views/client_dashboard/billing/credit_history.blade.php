<div class="container-fluid mb-12">
    <div class="justify-content-end pt-2 d-print-none row ">
        <div class="align-self-end text-right col-6">
            <a data-toggle="modal" data-target="#exportPDFpopup" data-placement="bottom" href="javascript:;" onclick="exportPDFpopup();"> 
                <button type="button" class="trust-history-export-pdf mx-1 btn  btn-outline-dark">Export PDF</button>
            </a>
            <a data-toggle="modal" data-target="#withdrawFromCredit" data-placement="bottom" href="javascript:;" onclick="withdrawFromCredit();">
                <button type="button" class="mx-1 btn btn-outline-info">Withdraw from Credit</button>
            </a>
            <a data-toggle="modal" data-target="#loadDepositIntoCreditPopup" data-placement="bottom" href="javascript:;" onclick="loadDepositIntoCredit(this);" data-auth-user-id="{{ auth()->id() }}" data-client-id="{{ @$client_id }}"> 
                <button type="button" class="mx-1 btn btn-primary">Deposit into Credit</button>
            </a>
        </div>
    </div>
</div>

<table class="display table table-striped table-bordered" id="billing_credit_history_table" style="width:100%" data-url="{{ route('contacts/clients/loadCreditHistory') }}" data-client-id="{{ @$client_id }}">
    <thead>
        <tr>
            <th class="" style="cursor: initial;">Date</th>
            <th class="" style="cursor: initial;">Related To</th>
            <th class="" style="cursor: initial;">Details</th>
            <th class="" style="cursor: initial;">Payment Method</th>
            <th class="" style="cursor: initial;">Amount</th>
            <th class="" style="cursor: initial;">Balance</th>
            <th class="text-right d-print-none" style="cursor: initial;">Action</th>
        </tr>
    </thead>
</table>
