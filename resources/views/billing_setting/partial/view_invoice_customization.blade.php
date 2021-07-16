<div class="row">
    <div class="col-3">Invoice Theme</div>
    <p class="col-9">Standard</p>
</div>
<div class="row">
    <div class="col-3">Case Number</div>
    <p class="col-9">Do not show case number</p>
</div>
<div class="row">
    <div class="col-3">Time Entries and Expenses</div>
    <p class="col-9">Include Non-Billable Time Entries and Expenses</p>
</div>
<div class="row">
    <div class="col-3">Include Columns per Billing Type</div>
    <div class="col-9">
        <div class="mb-2">
            <div>Flat Fee</div>
            <div class="text-muted flat-fee-columns">Date, Item, Flat Fee Notes, Amount</div>
        </div>
        <div class="mb-2">
            <div>Time Entry</div>
            <div class="text-muted time-entry-columns">Date, Employee, Activity, Time Entry Notes, Rate, Hour, Line Total</div>
        </div>
        <div class="mb-2">
            <div>Expense</div>
            <div class="text-muted expense-columns">Date, Employee, Expense, Expense Notes, Cost, Quantity, Line Total</div>
        </div>
        <div class="text-right pt-2 pb-1">
            <button type="button" id="edit-idp-btn" class="btn btn-cta-primary" data-customize-id="{{ @$customize->id }}" data-url="{{ route('billing/settings/edit/customization') }}">Edit</button>
        </div>
    </div>
</div>