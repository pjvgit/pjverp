@if(!empty($clientCaseInfo))
<p data-testid="introduction-text"><strong>Allocate trust funds to case</strong>
<br>Enter the dollar amount you want earmarked for each case.
<br>Note: the unallocated funds total will be updated as the new amount is entered</p>
<form class="trust-allocate-form">
    @csrf
<div class="row ">
    <div class="col-md-12">
        <input type="hidden" name="case_id" value="{{ $clientCaseInfo->case_id }}" >
        <input type="hidden" name="client_id" value="{{ @$userAddInfo->user_id }}" >
        <input type="hidden" name="page" id="page" value="{{ $page }}" >
        <label class=""><strong>Trust (Trust Account)</strong></label>
        <div class="row ">
            <div class="pr-1 col-sm-4">
                <div class="allocation-in-bank-507379 input-group">
                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                    <input class="form-control allocate-fund number minStrictNumber" maxlength="15" name="allocated_balance" value="{{ number_format(@$clientCaseInfo->allocated_trust_balance, 2) }}" data-total-amt="{{ @$clientCaseInfo->allocated_trust_balance + $userAddInfo->unallocate_trust_balance }}">
                </div>
                <span class="ta-amt-error error"></span>
            </div>
            <div class="px-1 col-sm-4">
                <label class="col-form-label ">{{ ucfirst(@$clientCaseInfo->case->case_title) }}</label>
            </div>
        </div>
        <br>
        <div class="row ">
            <div class="pr-1 col-sm-4">
                <div class="unallocation-in-bank-507379 input-group">
                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                    <input readonly class="form-control unallocate-fund" name="unallocated_balance" value="{{ number_format(@$userAddInfo->unallocate_trust_balance, 2) }}" data-unallocate-amt="{{ @$userAddInfo->unallocate_trust_balance }}">
                </div>
            </div>
            <div class="px-1 col-sm-4">
                <label class="col-form-label ">Unallocated Funds</label>
            </div>
        </div>
        <br>
    </div>
</div>
<div class="modal-footer">
    <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;"></div>
    <button type="button" class="close-btn-0 btn btn-link mr-2" data-dismiss="modal">Cancel</button>
    <button type="button" class="confirm-btn btn btn-primary mr-2">OK</button>
</div>
</form>
@else
<center>
<p data-testid="introduction-text"><strong>Unable to fetch trust allocation data. Please try again</strong></p>
<button type="button" class="close-btn-0 btn btn-link mr-2" data-dismiss="modal" aria-label="Close">Cancel</button>
</center>
@endif