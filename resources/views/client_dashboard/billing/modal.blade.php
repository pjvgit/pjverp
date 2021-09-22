<div id="addEmailToClient" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="addEmailtouser" id="addEmailtouser" name="addEmailtouser" method="POST">
            @csrf
            <input type="hidden" value="" name="client_id" id="client_id_for_email">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Enter Email</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showErrorOver" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            In order to send a request, they must have a valid email address. Please enter this information below and click "Save Email" to add it to their record and continue with this request.
                      </div>
                    </div>
                    <br>
                    <div class="form-group row">
                        <label for="due-date" class="col-4 pt-2">E-mail Addess</label>
                        <div class="date-input-wrapper col-8">
                            <div class="">
                                <div>
                                    <input class="form-control" id="email" maxlength="250" name="email" type="text">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1 close-modal" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Save Email</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- For trust balance deposit --}}
<div id="depositIntoTrust" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Deposit Into Trust</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="depositIntoTrustArea">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- For trust amount withdraw --}}
<div id="withdrawFromTrust" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Withdraw Trust Fund</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="withdrawFromTrustArea">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- For refund popup --}}
<div id="RefundPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Refund Payment</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="RefundPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- For delete payment entry --}}
<div id="deleteEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deletePaymentEntry" id="deletePaymentEntry" name="deletePaymentEntry" method="POST">
            @csrf
            <input type="hidden" value="" name="payment_id" id="delete_payment_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Payment</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
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
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>