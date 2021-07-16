<form class="edit-invoice-display-preferences-form">
    <div class="row py-2">
        <div class="col-3">Invoice Theme</div>
        <div class="col-9 mb-6 pb-2">
            <div class="row">
                <div class="col-5 zoom-img">
                    <div class="card text-center thumbnail-card">
                        <div class="card-header mb-2">Standard</div>
                        <div class="card-body">
                            <div class="ml-4 standard-thumbnail"><img src="{{ asset('images/thumbnail_standard.png') }}" ></div>
                            <div class="pt-3">
                                <input id="input-standard-thumbnail" name="thumbnail" type="radio"> Standard</div>
                        </div>
                    </div>
                </div>
                <div class="col-5 zoom-img">
                    <div class="card text-center thumbnail-card">
                        <div class="card-header mb-2">Modern</div>
                        <div class="card-body">
                            <div class="ml-4 modern-thumbnail"><img src="{{ asset('images/thumbnail_modern.png') }}" ></div>
                            <div class="pt-3">
                                <input id="input-modern-thumbnail" name="thumbnail" type="radio" checked=""> Modern</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row py-2">
        <div class="col-3">Case Number</div>
        <div class="col-9 mb-6 pb-2">
            <input id="checkbox-show-case-number" name="date" type="checkbox" name="show_case_no_after_case_name"> Show Case Number After Case Name</div>
    </div>
    <div class="row py-2">
        <div class="col-3">Time Entries and Expenses</div>
        <div class="col-9 mb-6 pb-2">
            <input id="checkbox-include-non-billables" name="non-billables" type="checkbox" checked="">&ensp;Include Non-Billable Time Entries and Expenses</div>
    </div>
    <div class="row">
        <div class="col-3">Include Columns per Billing Type</div>
        <div class="col-9">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th class="py-2 pl-4 gray-section-header">Flat Fee</th>
                            <th class="py-2 pl-4 gray-section-header">Show on Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-2 pl-4">Date</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-flat_fee-date" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Employee</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-flat_fee-employee" name="date" type="checkbox">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Item</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-flat_fee-item" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Flat Fee Notes</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-flat_fee-notes" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Amount</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-flat_fee-amount" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                    </tbody>
                    <thead>
                        <tr>
                            <th class="py-2 pl-4 gray-section-header">Time Entry</th>
                            <th class="py-2 pl-4 gray-section-header">Show on Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-2 pl-4">Date</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-date" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Employee</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-employee" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Activity</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-activity" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Time Entry Notes</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-notes" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Rate</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-rate" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Hour</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-hour" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Line Total</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-total" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                    </tbody>
                    <thead>
                        <tr>
                            <th class="py-2 pl-4 gray-section-header">Expense</th>
                            <th class="py-2 pl-4 gray-section-header">Show on Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-2 pl-4">Date</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-date" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Employee</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-employee" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Expense</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-expense" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Expense Notes</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-notes" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Cost</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-cost" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Quantity</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-quantity" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Line Total</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-total" name="date" type="checkbox" checked="">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="text-right pt-2 pb-1 edit-idp-footer">
                <button type="button" class="btn btn-link cancel_form_button">Cancel</button>
                <button type="submit" class="save-idp-btn btn btn-cta-primary mr-2">Save</button>
            </div>
        </div>
    </div>
</form>