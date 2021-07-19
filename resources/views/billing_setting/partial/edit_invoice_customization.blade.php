<form class="edit-invoice-display-preferences-form" id="customization_form" method="POST" action="{{ route('billing/settings/update/customization') }}">
    @csrf
    <input type="hidden" name="id" value="{{ @$customize->id }}">
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
                                <input id="input-standard-thumbnail" name="invoice_theme" value="standard" type="radio" @if(isset($customize) && $customize->invoice_theme == "standard") checked @endif @if(!isset($customize)) checked @endif> Standard</div>
                        </div>
                    </div>
                </div>
                <div class="col-5 zoom-img">
                    <div class="card text-center thumbnail-card">
                        <div class="card-header mb-2">Modern</div>
                        <div class="card-body">
                            <div class="ml-4 modern-thumbnail"><img src="{{ asset('images/thumbnail_modern.png') }}" ></div>
                            <div class="pt-3">
                                <input id="input-modern-thumbnail" name="invoice_theme" value="modern" type="radio" @if(isset($customize) && $customize->invoice_theme == "modern") checked @endif> Modern</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row py-2">
        <div class="col-3">Case Number</div>
        <div class="col-9 mb-6 pb-2">
            <input id="checkbox-show-case-number" type="checkbox" name="show_case_no_after_case_name" value="yes" @if(isset($customize) && $customize->show_case_no_after_case_name == "yes") checked @endif>&ensp;Show Case Number After Case Name</div>
    </div>
    <div class="row py-2">
        <div class="col-3">Time Entries and Expenses</div>
        <div class="col-9 mb-6 pb-2">
            <input id="checkbox-include-non-billables" name="non_billable_time_entries_and_expenses" type="checkbox" @if(isset($customize) && $customize->non_billable_time_entries_and_expenses == "yes") checked @endif @if(!isset($customize)) checked @endif>&ensp;Include Non-Billable Time Entries and Expenses</div>
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
                                <input id="checkbox-flat_fee-date" name="column[flat fee][1][date]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('date', $flatFeeColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Employee</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-flat_fee-employee" name="column[flat fee][1][employee]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('employee', $flatFeeColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Item</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-flat_fee-item" name="column[flat fee][1][item]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('item', $flatFeeColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Flat Fee Notes</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-flat_fee-notes" name="column[flat fee][1][notes]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('notes', $flatFeeColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Amount</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-flat_fee-amount" name="column[flat fee][1][amount]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('amount', $flatFeeColumn)) checked @endif>
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
                                <input id="checkbox-time_entry-date" name="column[time entry][1][date]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('date', $timeEntryColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Employee</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-employee" name="column[time entry][1][employee]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('employee', $timeEntryColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Activity</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-activity" name="column[time entry][1][activity]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('activity', $timeEntryColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Time Entry Notes</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-notes" name="column[time entry][1][notes]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('notes', $timeEntryColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Rate</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-rate" name="column[time entry][1][amount]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('amount', $timeEntryColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Hour</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-hour" name="column[time entry][1][hour]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('hour', $timeEntryColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Line Total</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-time_entry-total" name="column[time entry][1][line_total]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('line_total', $timeEntryColumn)) checked @endif>
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
                                <input id="checkbox-expense-date" name="column[expense][1][date]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('date', $expenseColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Employee</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-employee" name="column[expense][1][employee]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('employee', $expenseColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Expense</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-expense" name="column[expense][1][expense]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('expense', $expenseColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Expense Notes</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-notes" name="column[expense][1][notes]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('notes', $expenseColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Cost</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-cost" name="column[expense][1][amount]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('amount', $expenseColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Quantity</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-quantity" name="column[expense][1][quantity]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('quantity', $expenseColumn)) checked @endif>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 pl-4">Line Total</td>
                            <td class="py-2 pl-4">
                                <input id="checkbox-expense-total" name="column[expense][1][line_total]" type="checkbox" @if(!isset($customize)) checked @elseif(in_array('line_total', $expenseColumn)) checked @endif>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="text-right pt-2 pb-1 edit-idp-footer">
                <button type="button" class="btn btn-link cancel_form_button">Cancel</button>
                <button type="button" id="save_customiz_settings" class="btn btn-primary mr-2">Save</button>
            </div>
        </div>
    </div>
</form>