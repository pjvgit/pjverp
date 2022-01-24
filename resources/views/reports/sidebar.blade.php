<div class="d-flex h-100">
    <nav class="flex-column navbar-fixed-left" style="width: 200px; min-width: 200px;">
        <div class="mb-3 mt-2">
            <h5 class="border-bottom border-dark d-flex justify-content-between pb-1">
            <span>Financial Reports</span></h5>
            <div class="collapse show">
                <ul class="nav flex-column nav-pills">
                    <li class="nav-item"><a href="/reporting/accounts_receivable" class="p-1 border-0 nav-link {{ Route::currentRouteName()=='reporting/accounts_receivable' ? 'active' : '' }} ">Accounts Receivable</a></li>
                    <li class="nav-item"><a href="/reporting/case_revenue_reports" class="p-1 border-0 nav-link {{ Route::currentRouteName()=='reporting/case_revenue_reports' ? 'active' : '' }}">Case Revenue</a></li>
                </ul>
            </div>
        </div>
        <div class="mb-3 mt-2 m2">
            <h5 class="border-bottom border-dark d-flex justify-content-between pb-1">
            <span>Case &amp; Contact Reports</span></h5>
            <div class="collapse show">
                <ul class="nav flex-column nav-pills">
                        <li class="nav-item"><a href="/reporting/case_list_reports" class="p-1 border-0 nav-link">Case List Report</a></li>
                    <li class="nav-item"><a href="/reporting/contact_reports" class="p-1 border-0 nav-link">Contact Report</a></li>
                    <li class="nav-item"><a href="/reporting/sol" class="p-1 border-0 nav-link">Statute of Limitations</a></li>
                </ul>
            </div>
        </div>
        <div class="mb-3 mt-2 m2">
            <h5 class="border-bottom border-dark d-flex justify-content-between pb-1">
            <span>Productivity Reports</span></h5>
            <div class="collapse show">
                <ul class="nav flex-column nav-pills">
                        <li class="nav-item"><a href="/reporting/user_time" class="p-1 border-0 nav-link">User Time &amp; Expenses</a></li>
                    <li class="nav-item"><a href="/reporting/firm_time" class="p-1 border-0 nav-link">Firm Time &amp; Expenses</a></li>
                    <li class="nav-item"><a href="/reporting/case_time" class="p-1 border-0 nav-link">Case Time &amp; Expenses</a></li>
                </ul>
            </div>
        </div>
        <div class="mb-3 mt-2 m2">
            <h5 class="border-bottom border-dark d-flex justify-content-between pb-1">
            <span>Leads Reports</span></h5>
            <div class="collapse show">
                <ul class="nav flex-column nav-pills">
                        <li class="nav-item"><a href="/reporting/consultation_fees/collected_versus_billed_reports" class="p-1 border-0 nav-link">Consultation Fee Revenue</a></li>
                    <li class="nav-item"><a href="/reporting/leads_referral_reports" class="p-1 border-0 nav-link">Leads Referral Source</a></li>
                    <li class="nav-item"><a href="/reporting/leads_pipeline_value_reports" class="p-1 border-0 nav-link">Leads Forecasted Pipeline Value</a></li>
                </ul>
            </div>
        </div>
    </nav>
</div>