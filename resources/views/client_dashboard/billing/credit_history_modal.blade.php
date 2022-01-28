{{-- Deposite into credit fund --}}
<div id="loadDepositIntoCreditPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Deposit Into Credit Account</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="loadDepositIntoCreditArea">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete credit history entry popup --}}
<div id="deleteCreditHistoryEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteCreditHistoryEntry" id="deleteCreditHistoryEntryForm" method="POST">
            @csrf
            <input type="hidden" value="" name="delete_credit_id" id="delete_credit_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Payment</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this payment and remove all record of it from {{config('app.name')}}?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Withdraw from credit --}}
<div id="withdrawFromCredit" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Withdraw Credit Fund</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="withdrawFromCreditArea">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Export credit history pdf --}}
<div id="export_credit_popup" class="modal fade show modal-overlay" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="exportCreditpopupForm" id="exportCreditpopupForm" name="exportCreditpopupForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Export Credit Summary</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span> </button>
                </div>
                <div class="modal-body">
                    <span class="showError"></span>
                    <div class="clearfix">
                        <div style="float: left; margin-top: 7px;">
                          From:&nbsp;&nbsp;
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center">
                                <input type="text" name="from_date" id="export_credit_start_date" value="" class="date form-control date-range-input hasDatepicker" placeholder="No Start Date">
                                </div>
                            </div>
                            <div class="ml-3"> to </div>
                            <div class="d-flex flex-column ml-3">
                                <div class="d-flex align-items-center">
                                <input type="text" name="to_date" id="export_credit_end_date" value="" class="date form-control date-range-input hasDatepicker" placeholder="No End Date">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Close</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit">Export</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>