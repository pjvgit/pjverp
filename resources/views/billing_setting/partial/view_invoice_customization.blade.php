<div class="row">
    <div class="col-3">Invoice Theme</div>
    <p class="col-9">{{ ucfirst(@$customize->invoice_theme) }}</p>
</div>
<div class="row">
    <div class="col-3">Case Number</div>
    <p class="col-9">{{ (isset($customize) && $customize->show_case_no_after_case_name == 'yes') ? "Show case number" : "Do not show case number" }}</p>
</div>
<div class="row">
    <div class="col-3">Time Entries and Expenses</div>
    <p class="col-9">{{ (isset($customize) && $customize->non_billable_time_entries_and_expenses == 'yes') ? "Include Non-Billable Time Entries and Expenses" : "Hide Non-Billable Time Entries and Expenses" }}</p>
</div>
<div class="row">
    <div class="col-3">Include Columns per Billing Type</div>
    <div class="col-9">
        <div class="mb-2">
            <div>Flat Fee</div>
            <div class="text-muted flat-fee-columns">{{ (isset($customize) && $customize->flatFeeColumn) ? ucwords(implode(", ", getColumnsIfYes($customize->flatFeeColumn->toArray(), 'yes') ?? [])) : "" }}</div>
        </div>
        <div class="mb-2">
            <div>Time Entry</div>
            <div class="text-muted time-entry-columns">{{ (isset($customize) && $customize->timeEntryColumn) ? ucwords(implode(", ", getColumnsIfYes($customize->timeEntryColumn->toArray(), 'yes') ?? [])) : "" }}</div>
        </div>
        <div class="mb-2">
            <div>Expense</div>
            <div class="text-muted expense-columns">{{ (isset($customize) && $customize->expenseColumn) ? ucwords(implode(", ", getColumnsIfYes($customize->expenseColumn->toArray(), 'yes') ?? [])) : "" }}</div>
        </div>
        <div class="text-right pt-2 pb-1">
            <button type="button" id="edit-idp-btn" class="btn btn-primary" data-customize-id="{{ @$customize->id }}" data-url="{{ route('billing/settings/edit/customization') }}">Edit</button>
        </div>
    </div>
</div>