{{-- <div class="container-fluid">
    <div class="justify-content-end pt-2 d-print-none row ">
        <div class="align-self-end text-right col-5">
            <button type="button" class="trust-history-export-pdf mx-1 btn btn-secondary">Export PDF</button>
            <button type="button" class="mx-1 btn btn-secondary">Withdraw from Trust</button>
            <button type="button" class="mx-1 btn btn-primary">Deposit into Trust</button>
        </div>
    </div>
</div>

<table class="display table table-striped table-bordered mt-4" id="caseHistoryGrid1" style="width:100%">
    <thead>
        <tr>
            <th class="YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">Date</th>
            <th class="YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">Related To</th>
            <th class="YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">Details</th>
            <th class="YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">Payment Method</th>
            <th class="YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">Allocated To</th>
            <th class="YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">Amount</th>
            <th class="YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">Balance</th>
            <th class="text-right d-print-none YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;">Action</th>
        </tr>
    </thead>
</table> --}}
 
<div class="container-fluid mb-12">
    <div class="justify-content-end pt-2 d-print-none row ">
        <div class="align-self-end text-right col-6">
            @can('billing_add_edit')
                <a class="btn btn-primary client-add-invoice-button" href="{{ route('bills/invoices/load_new', ['court_case_id' => 'none', 'contact' => $client_id]) }}">Add Invoice</a>
            @endcan
        </div>
    </div>
</div>
@if($userProfile->has("invoices"))
<table class="display table table-striped table-bordered" id="billing_invoice_table" style="width:100%" data-url="{{ route('contacts/clients/load/invoices') }}" data-client-id="{{ @$client_id }}">
    <thead>
        <tr>
            <th></th>
            <th>Number</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Amount Due</th>
            <th>Due</th>
            <th>Created</th>
            <th>Status</th>
            <th>Viewed</th>
            <th></th>
        </tr>
    </thead>
</table>
@else
<div class="text-center">
    <p>There are no invoices.</p> <p><a href="{{ route('bills/invoices/load_new', ['court_case_id' => 'none', 'contact' => $client_id]) }}">Add Invoice</a></p>
</div>
@endif