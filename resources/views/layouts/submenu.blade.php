<div class="card mb-4">
    <div class="card-body">
        <nav class="test-general-settings-nav p-0 pt-0" role="navigation">
            <ul class="nav nav-pills flex-column text-wrap">
                <div class="mb-3">
                    <h5 class="pl-3 py-2 border-bottom border-dark">General</h5>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('account/dashboard*') ? 'active' : '' }} "
                            href="{{ route('account/dashboard') }}">Dashboard</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('imports*') ? 'active' : '' }}"
                            href="{{ route('imports/contacts') }}">Import/Export</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link "
                            href="https://mylegal1.mycase.com/custom_fields?group=court_case">Custom Fields</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('form_templates*') ? 'active' : '' }}" href="{{ route('form_templates') }}">Intake Forms</a>
                     
                    </li>

                    <li class="nav-item">
                        <a class="nav-link " href="https://mylegal1.mycase.com/workflows">Workflows</a>
                    </li>

                    <li class="nav-item  ">
                        <a class="nav-link {{ request()->is('case_stages*') ? 'active' : '' }}" href="{{ route('case_stages') }}">Case Stages</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('lead_setting*') ? 'active' : '' }}" href="{{ route('lead_setting') }}">Leads</a>
                    </li>

                </div>

                <div class="mb-3">
                    <h5 class="pl-3 py-2 border-bottom border-dark">Personal</h5>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('load_profile*') ? 'active' : '' }} "
                            href="{{ route('load_profile') }}">My Profile</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('account/preferences*') ? 'active' : '' }} " href="{{BASE_URL}}account/preferences">My
                            Settings</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link " href="#account/notifications">My
                            Notifications</a>
                    </li>
                </div>

                <div class="mb-3">
                    <h5 class="pl-3 py-2 border-bottom border-dark">Firm</h5>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('contacts/attorneys*') ? 'active' : '' }} "
                            href="{{ route('contacts/attorneys') }}">Users</a>
                    </li>
                    <?php
                    if(Auth::User()->parent_user=="0"){; 
                    ?>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('firms/setting*')? 'active' : '' }} "
                            href="{{ route('firms/setting') }}">Firm Settings</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link "
                            href="https://mylegal1.mycase.com/firms/mylegal1/billing_settings">Client Billing
                            &amp; Invoice Settings</a>
                    </li>
                <?php } ?>
                </div>
            </ul>
        </nav>
    </div>
</div>