{{-- Delete/Remove Flat Fee from Add/Edit Invoice --}}
<div id="flat_fee_delete_existing_dialog" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="removeExistingFlatFeeEntryForm" id="removeExistingFlatFeeEntryForm" name="removeExistingFlatFeeEntryForm" method="POST">
            @csrf
            <input type="hidden" value="" name="flat_fee_id" id="flat_fee_delete_entry_id">
            <input type="hidden" value="{{$adjustment_token}}" name="token_id" id="token_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Remove Flat Fee Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12 confirmAccess" id="confirmAccess">
                            Would you like to <strong>remove</strong> the selected entry from this invoice or
                            permanently <strong>delete</strong> it from {{config('app.name')}}?
                        </div>
                        <div class="col-md-12 confirmAccessRemove">
                            Are you sure you want to <strong>delete</strong> the selected entry?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-10  text-center">
                        <div class="form-group row float-left">
                            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                                style="display: none;"></div>
                        </div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <input class="btn btn-primary ladda-button example-button m-1 submit"
                                onclick="actionFlatFeeEntry('delete')" name="action" value="Delete" id="submit"
                                type="submit">
                            <input class="btn btn-primary ladda-button example-button m-1 submit actionFlatFeeEntryRemove" name="action"
                                value="Remove" id="submit" onclick="actionFlatFeeEntry('remove')" type="submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Delete/Remove time entry from add/edit invoice --}}
<div id="delete_existing_dialog" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="removeExistingEntryForm" id="removeExistingEntryForm" name="removeExistingEntryForm" method="POST">
            @csrf
            <input type="hidden" value="" name="time_entry_id" id="delete_time_entry_id">
            <input type="hidden" value="{{$adjustment_token}}" name="token_id" id="token_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Remove Time Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12 confirmAccess" id="confirmAccess">
                            Would you like to <strong>remove</strong> the selected entry from this invoice or
                            permanently <strong>delete</strong> it from {{config('app.name')}}?
                        </div>
                        <div class="col-md-12 confirmAccessRemove">
                            Are you sure you want to <strong>delete</strong> the selected entry?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-10  text-center">
                        <div class="form-group row float-left">
                            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                                style="display: none;"></div>
                        </div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <input class="btn btn-primary ladda-button example-button m-1 submit"
                                onclick="actionTimeEntry('delete')" name="action" value="Delete" id="submit"
                                type="submit">
                            <input class="btn btn-primary ladda-button example-button m-1 submit actionFlatFeeEntryRemove" name="action"
                                value="Remove" id="submit" onclick="actionTimeEntry('remove')" type="submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Delete/Remove expense entry from add/edit invoice --}}
<div id="delete_expense_existing_dialog" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="removeExistingExpenseEntryForm" id="removeExistingExpenseEntryForm"
            name="removeExistingExpenseEntryForm" method="POST">
            @csrf
            <input type="hidden" value="" name="expense_entry_id" id="delete_expense_entry_id">
            <input type="hidden" value="{{$adjustment_token}}" name="token_id" id="token_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Remove Expense Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12 confirmAccess" id="confirmAccess">
                            Would you like to <strong>remove</strong> the selected entry from this invoice or
                            permanently <strong>delete</strong> it from {{config('app.name')}}?
                        </div>
                        <div class="col-md-12 confirmAccessRemove">
                            Are you sure you want to <strong>delete</strong> the selected entry?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-10  text-center">
                        <div class="form-group row float-left">
                            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                                style="display: none;"></div>
                        </div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <input class="btn btn-primary ladda-button example-button m-1 submit"
                                onclick="actionExpenseEntry('delete')" name="action" value="Delete" id="submit"
                                type="submit">
                            <input class="btn btn-primary ladda-button example-button m-1 submit actionFlatFeeEntryRemove" name="action"
                                value="Remove" id="submit" onclick="actionExpenseEntry('remove')" type="submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Delete/remove adjustment from add/edit invoice --}}
<div id="delete_flatfee_existing_dialog_bbox" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="removeExistingFlateFeesForm" id="removeExistingFlateFeesForm"
            name="removeExistingFlateFeesForm" method="POST">
            @csrf
            <input type="hidden" value="" name="adjustment_entry_id" id="delete_flatefees_existing_dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Remove Adjustment Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12 confirmAccess" id="confirmAccess">
                            Would you like to <strong>remove</strong> the selected entry from this invoice or
                            permanently <strong>delete</strong> it from {{config('app.name')}}?
                        </div>
                        <div class="col-md-12 confirmAccessRemove">
                            Are you sure you want to <strong>delete</strong> the selected entry?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-10  text-center">
                        <div class="form-group row float-left">
                            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                                style="display: none;"></div>
                        </div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <input class="btn btn-primary ladda-button example-button m-1 submit"
                                onclick="actionAdjustmentEntry('delete')" name="action" value="Delete" id="submit"
                                type="submit">
                            <input class="btn btn-primary ladda-button example-button m-1 submit actionFlatFeeEntryRemove" name="action"
                                value="Remove" id="submit" onclick="actionAdjustmentEntry('remove')" type="submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Payment Plan -->
<div id="payment_plan_existing_dialog_bbox" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Warning</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div data-testid="warning-modal">
                    <p>Are you sure you want to recalculate your payment plan?</p>
                    <ul>
                        <li>Previous Payments against the original balance will not be lost.</li>
                        <li>Previous payment history will only be available on the invoice payment history. Individual installment payment history will no longer show here.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-10  text-center">
                    <div class="form-group row float-right">
                        <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                        <input class="btn btn-primary ladda-button example-button m-1" name="action"
                            value="Recalculate" onclick="allowPaymentPlan()">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>