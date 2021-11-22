<h2 class="mx-2 mb-0 text-nowrap hiddenLable">        {{ ucfirst($userProfile->first_name) .' '.ucfirst($userProfile->last_name) }} (Client)    </h2>
<div class="container-fluid">
    <div class="justify-content-end pt-2 d-print-none row ">
        <div class="pl-0 col-7">
            <div class="container-fluid">
                <div class="row ">
                    <div class="col-4">
                        <label for="trust-history-court-case-select">Trust Allocation</label>
                        <select id="trust_history_case_select" class="form-control select2-case">
                            <option value="">Select a case</option>
                            @forelse ($case as $item)
                                <option value="{{ $item->id }}" data-trust-type="case">{{ $item->case_title }}</option>
                            @empty
                            @endforelse
                            <option value="{{ $userProfile->id }}" data-trust-type="user">{{ $userProfile->first_name.' '.$userProfile->last_name}}</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <label for="trust-history-bank-account-select">Bank Account</label>
                        <select id="trust_history_bank_account_select" class="form-control select2-bank-account">
                            <option value="">Select a bank account</option>
                            <option value="trust">Trust Account</option>
                        </select>
                    </div>
                    <div class="align-self-end col-4">
                        <button type="button" class="btn btn-secondary apply-filter">Apply Filters</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="align-self-end text-right col-5">
            <a data-toggle="modal" data-target="#exportPDFpopup" data-placement="bottom" href="javascript:;" onclick="exportPDFpopup();"> 
                <button type="button" class="trust-history-export-pdf mx-1 btn  btn-outline-dark">Export PDF</button>
            </a>
            @can(['billing_add_edit','client_add_edit'])
            <a data-toggle="modal" data-target="#withdrawFromTrust" data-placement="bottom" href="javascript:;" onclick="withdrawFromTrust();">
                <button type="button" class="mx-1 btn btn-outline-info">Withdraw from Trust</button>
            </a>
            <a data-toggle="modal" data-target="#depositIntoTrust" data-placement="bottom" href="javascript:;" onclick="depositIntoTrust({{ $client_id }});"> 
                <button type="button" class="mx-1 btn btn-primary">Deposit into Trust</button>
            </a>
            @endcan
        </div>
    </div>
</div>
<p><br></p>
<table class="display table table-striped table-bordered" id="billingTabTrustHistory" style="width:100%" data-url="{{ route('contacts/clients/loadTrustHistory') }}" data-client-id="{{ @$client_id }}">
    <thead>
        <tr>
            <th class="" style="cursor: initial;">Date</th>
            <th class="" style="cursor: initial;">Related To</th>
            <th class="" style="cursor: initial;">Details</th>
            <th class="" style="cursor: initial;">Payment Method</th>
            <th class="" style="cursor: initial;">Allocated To</th>
            <th class="" style="cursor: initial;">Amount</th>
            <th class="" style="cursor: initial;">Balance</th>
            <th class="text-center d-print-none" style="cursor: initial;">Action</th>
        </tr>
    </thead>
</table>
