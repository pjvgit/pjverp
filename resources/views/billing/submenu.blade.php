<div class="breadcrumb">
    <ul>
        <li>
            <a href="{{route('bills/dashboard')}}"
                class="{{ Route::currentRouteName()=='bills/dashboard' ? 'myactive' : '' }}">Dashboard</a>
        </li>
        <li>
            <a href="{{route('bills/time_entries')}}?i=o&type=own"
                class="{{ Route::currentRouteName()=='bills/time_entries' ? 'myactive' : '' }}">Time Entries</a>
        </li>
        <li>
            <a href="{{route('bills/expenses')}}?i=o&type=own"
                class="{{ Route::currentRouteName()=='bills/expenses' ? 'myactive' : '' }}">Expenses</a>
        </li>
        <li>
            <a href="{{route('bills/retainer_requests')}}?type=all"
                class="{{ Route::currentRouteName()=='bills/retainer_requests' ? 'myactive' : '' }}">Requested Funds</a>
        </li>
        <li>
            <a href="{{route('bills/invoices')}}?type=all" class="{{ request()->is('bills/invoices*') ? 'myactive' : '' }} ">Invoices</a>
        </li>
        <li>
            <a href="{{route('payment_plans')}}" class="{{ request()->is('payment_plans*') ? 'myactive' : '' }} ">Payment Plans</a>
        </li>
        <li>
            <a href="{{route('bills/activities')}}"
                class="{{ Route::currentRouteName()=='bills/activities' ? 'myactive' : '' }}">Saved Activities</a>
        </li>

        <li>
            <a href="{{route('bills/account_activity')}}"
            class="{{ Route::currentRouteName()=='bills/account_activity' ? 'myactive' : '' }}">Account Activity</a>
        </li>

        <li>
            <a href="{{route('insights/financials')}}" class="{{ Route::currentRouteName()=='insights/financials' ? 'myactive' : '' }}">Financial Insights</a>
        </li>
        <li>
            <a href="{{route('time_entries/timesheet_calendar')}}"
                class="{{ Route::currentRouteName()=='time_entries/timesheet_calendar' ? 'myactive' : '' }}">Timesheet Calendar</a>
        </li>
     
        <li class="">
            <a id="billing-guide-color" target="_blank" href="#">
                <i class="fas fa-bookmark fa-bill-guide-icon" aria-hidden="true"></i> Billing Guide
            </a>
        </li>
    </ul>
</div>
<style>
    .myactive {
        font-weight: bold;
    }
</style>
