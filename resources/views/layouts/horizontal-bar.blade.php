<div class="horizontal-bar-wrap">
    <div class="header-topnav">
        <div class="container-fluid">
            <div class=" topnav rtl-ps-none" id="" data-perfect-scrollbar data-suppress-scroll-x="true">
                <ul class="menu float-left">
                    <li class="{{ request()->is('dashboard*') ? 'open' : '' }} {{ request()->is('notifications*') ? 'open' : '' }}">
                        <div>
                            <div>
                                <label class="toggle" for="drop-2">
                                    Home
                                </label>
                                <a href="{{route('dashboard')}}">
                                    Home
                                </a>
                                <input type="checkbox" id="drop-2">
                                <ul>
                                    <li class="nav-item ">
                                        <a class="" href="{{route('dashboard')}}">
                                            <span class="item-name">Dashboard</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{route('notifications')}}" class="">
                                            <span class="item-name">Recent Activity</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <li class="{{ request()->is('events*') ? 'open' : '' }}">
                        <div>
                            <div>
                                <label class="toggle" for="drop-2">
                                    Calender
                                </label>
                                <a href="{{route('events/')}}">Calender</a><input type="checkbox" id="drop-2"><input
                                    type="checkbox" id="drop-2">
                                <ul>

                                    <li class="nav-item">
                                        <a href="{{BASE_URL}}events?view=day" class="">
                                            <span class="item-name">Day</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{BASE_URL}}events?view=week" class="">
                                            <span class="item-name">Week</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{BASE_URL}}events?view=month" class="">
                                            <span class="item-name">Month</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#" class="">
                                            <span class="item-name">Agenda</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{route('locations')}}" class="">
                                            <span class="item-name">Location</span>
                                        </a>
                                    </li>


                                </ul>
                            </div>
                        </div>
                    </li>
                    <!-- end ui kits -->

                    <li class="{{ request()->is('tasks*') ? 'open' : '' }}">
                        <div>
                            <div>
                                <label class="toggle" for="drop-2">Tasks</label>
                                <a href="{{route('tasks')}}">Tasks</a><input type="checkbox" id="drop-2">

                            </div>
                        </div>
                    </li>
                    <li class="{{ request()->is('contacts/*') ? 'open' : '' }}">

                        <div>


                            <div>
                                <label class="toggle" for="drop-2">Contacts</label>
                                <a href="{{route('contacts/client')}}?target=active">Contacts</a><input type="checkbox" id="drop-2">
                                <ul>
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='contacts/client' ? 'open' : '' }}"
                                            href="{{route('contacts/client')}}?target=active" title=''>
                                            <span class="item-name">Clients</span>
                                        </a>
                                    </li>
                                    <li><a class="{{ Route::currentRouteName()=='contacts/company' ? 'open' : '' }}"
                                            href="{{route('contacts/company')}}?target=active"> Companies </a></li>
                                    <li><a class="{{ Route::currentRouteName()=='contacts/contact_groups' ? 'open' : '' }}"
                                            href="{{route('contacts/contact_groups')}}"> Contact Groups </a></li>


                                </ul>
                            </div>
                        </div>
                    </li>
                    <li class="{{ request()->is('court_cases*') ? 'open' : '' }}">
                        <div>
                            <div>
                                <label class="toggle" for="drop-2">Cases</label>
                                <a href="{{route('court_cases')}}">Cases</a><input type="checkbox" id="drop-2">
                                <ul>
                                    <li class="nav-item">
                                        <a class="<?php if(Route::currentRouteName()=='court_cases'  && !isset($_GET['i'])){ echo 'open'; } ?>"
                                            href="{{route('court_cases')}}" title=''>
                                            <span class="item-name">My Open Cases</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="<?php if(Route::currentRouteName()=='court_cases'  &&  isset($_GET['i'])){ echo 'open'; } ?>"
                                            href="{{route('court_cases')}}?i=c" title=''>
                                            <span class="item-name">My Close Cases</span>
                                        </a>
                                    </li>


                                    <li><a class="{{ Route::currentRouteName()=='apexAreaCharts' ? 'open' : '' }}"
                                            href="{{route('apexAreaCharts')}}"> Firm Open Cases </a></li>
                                    <li><a class="{{ Route::currentRouteName()=='apexBarCharts' ? 'open' : '' }}"
                                            href="{{route('apexBarCharts')}}"> Firm Close Cases </a></li>
                                    <li><a class="{{ Route::currentRouteName()=='apexBubbleCharts' ? 'open' : '' }}"
                                            href="{{route('practice_areas')}}"> Practice Areas </a></li>
                                    <li><a class="{{ Route::currentRouteName()=='apexColumnCharts' ? 'open' : '' }}"
                                            href="{{route('apexColumnCharts')}}"> Case Insights </a></li>


                                </ul>
                            </div>
                        </div>
                    </li>
                    <!-- end extra uikits -->

                    <li class="{{ request()->is('apps/*') ? 'active' : '' }}">

                        <div>


                            <div>
                                <label class="toggle" for="drop-2">
                                    Documents
                                </label>
                                <a href="#">
                                    Documents
                                </a><input type="checkbox" id="drop-2">
                                <ul>

                                    <li class="nav-item">
                                        <a href="#" class="">
                                            <span class="item-name">Case Documents</span>
                                        </a>
                                    </li>


                                    <li class="nav-item">
                                        <a href="#" class="">
                                            <span class="item-name">Unread Case Documents</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <!-- end apps -->

                    <li class="{{ request()->is('bills*') ? 'open' : '' }} {{ request()->is('payment_plans*') ? 'open' : '' }}">
                        <div>
                            <div>
                                <label class="toggle" for="drop-2">
                                    Billing
                                </label>
                                <a href="{{route('bills/dashboard')}}">
                                    Billing
                                </a><input type="checkbox" id="drop-2">
                                <ul>
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='bills/dashboard' ? 'open' : '' }}"
                                            href="{{route('bills/dashboard')}}">
                                            <span class="item-name">Dashboard</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='bills/time_entries' ? 'open' : '' }}"
                                            href="{{route('bills/time_entries')}}?i=o&type=own">
                                            <span class="item-name">Time Entries</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='bills/expenses' ? 'open' : '' }}"
                                            href="{{route('bills/expenses')}}?i=o&type=own">
                                            <span class="item-name">Expenses</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='bills/retainer_requests' ? 'open' : '' }}"
                                            href="{{route('bills/retainer_requests')}}">
                                            <span class="item-name">Requested Funds</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='bills/invoices' ? 'open' : '' }}"
                                            href="{{route('bills/invoices')}}?type=all">
                                            <span class="item-name">Invoices</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='payment_plans' ? 'open' : '' }}"
                                            href="{{route('payment_plans')}}">
                                            <span class="item-name">Payment Plans</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='bills/activities' ? 'open' : '' }}"
                                            href="{{route('bills/activities')}}">
                                            <span class="item-name">Saved Activities</span>
                                        </a>
                                    </li>
                                   
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='bills/account_activity' ? 'open' : '' }}"
                                            href="{{route('bills/account_activity')}}">
                                            <span class="item-name">Account Activity</span>
                                        </a>
                                    </li>  
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='insights/financials' ? 'open' : '' }}"
                                            href="{{route('insights/financials')}}">
                                            <span class="item-name">Financial Insights</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='time_entries/timesheet_calendar' ? 'open' : '' }}"
                                            href="{{route('time_entries/timesheet_calendar')}}">
                                            <span class="item-name">Timesheet Calendar</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <!-- end Forms -->


                    <li class="{{ request()->is('charts/*') ? 'active' : '' }}">

                        <div>


                            <div>
                                <label class="toggle" for="drop-2">
                                    Reports
                                </label>
                                <a href="#">
                                    Reports
                                </a><input type="checkbox" id="drop-2">
                                <ul>

                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='echarts' ? 'open' : '' }}"
                                            href="{{route('echarts')}}" title='charts'>
                                            <i class="nav-icon mr-2 i-Bar-Chart-2"></i>
                                            <span class="item-name">echarts</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='chartjs' ? 'open' : '' }}"
                                            href="{{route('chartjs')}}">
                                            <i class="nav-icon mr-2 i-File-Clipboard-Text--Image"></i>
                                            <span class="item-name">ChartJs</span>
                                        </a>
                                    </li>



                                </ul>


                            </div>
                        </div>
                    </li>
                    <li class="{{ request()->is('forms/*') ? 'active' : '' }}">

                        <div>


                            <div>
                                <label class="toggle" for="drop-2">
                                    Communications
                                </label>
                                <a href="#">
                                    Communications
                                </a><input type="checkbox" id="drop-2">
                                <ul>

                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='forms-basic' ? 'open' : '' }}"
                                            href="{{route('forms-basic')}}">
                                            <i class="nav-icon mr-2 i-File-Clipboard-Text--Image"></i>
                                            <span class="item-name">Basic Elements</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <li class="{{ request()->is('leads*') ? 'open' : '' }}">
                        <div>
                            <div>
                                <label class="toggle" for="drop-2">
                                    Leads
                                </label>
                                <a href="{{route('leads/statuses')}}">Leads</a>

                                <ul class="text-nowrap">
                                    <li class="nav-item">
                                        <a href="{{route('leads/statuses')}}" class="">
                                            <span class="item-name">Manage Pipeline</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{route('leads/tasks')}}" class="">
                                            <span class="item-name">Tasks</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{route('leads/statuses')}}" class="">
                                            <span class="item-name">Lead Insight</span>
                                        </a>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    </li>
                    <!-- end charts -->



                </ul>


            </div>
        </div>
    </div>

</div>
<!--=============== Horizontal bar End ================-->
<style>
    .nav-item
    {width: auto !important;
    }
    </style>