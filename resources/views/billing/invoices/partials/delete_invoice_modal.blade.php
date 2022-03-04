<div id="deleteInvoicePopup" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" bladefile="resources\views\billing\invoices\partials\delete_invoice_modal.blade.php">
        <form class="deleteInvoiceForm" id="deleteInvoiceForm" name="deleteInvoiceForm" method="POST">
            <input type="hidden" id="delete_invoice_id" name="invoice_id">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirm Delete</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            <p>Are you sure you want to delete this Invoice?</p>
                            <ul>
                                <li>This action can’t be undone</li>
                                <li>Time entries, expenses and flat fee entries will be put back into the system in their "Open" state.</li>
                                <li>All payments and refunds made against the invoice WILL BE DELETED. If trust funds were used to pay the invoice, those funds will be returned to the client's trust fund. Other funds with different origin will be sent to the client's trust fund as well.</li>
                                <li>Firm users working on the case will receive a notification</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>