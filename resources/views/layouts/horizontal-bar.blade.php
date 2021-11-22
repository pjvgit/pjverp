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
                    @canany(['event_add_edit', 'event_view'])
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
                                        <a href="{{route('events/')}}?view=day" class="">
                                            <span class="item-name">Day</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{route('events/')}}?view=week" class="">
                                            <span class="item-name">Week</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{route('events/')}}?view=month" class="">
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
                    @endcanany
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
                    @canany(['document_add_edit', 'document_view'])
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
                    @endcanany

                    @canany(['billing_add_edit', 'billing_view'])
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
                                    @can('billing_access_financial_insight')
                                    <li class="nav-item">
                                        <a class="{{ Route::currentRouteName()=='insights/financials' ? 'open' : '' }}"
                                            href="{{route('insights/financials')}}">
                                            <span class="item-name">Financial Insights</span>
                                        </a>
                                    </li>
                                    @endcan
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
                    @endcanany
                    @canany(['reporting_entire_firm', 'reporting_personal_only'])
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
                    @endcanany
                    @canany(['messaging_add_edit', 'text_messaging_add_edit', 'commenting_add_edit', 'commenting_view'])
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
                                    @canany(['messaging_add_edit', 'messaging_view'])
                                    <li class="nav-item">
                                        <a class="" href="#">
                                            <i class="nav-icon mr-2 i-File-Clipboard-Text--Image"></i>
                                            <span class="item-name">Mail Box</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="" href="#">
                                            <span class="item-name">Messages</span>
                                        </a>
                                    </li>
                                    @endcanany
                                    <li class="nav-item">
                                        <a class="" href="#">
                                            <span class="item-name">Call Log</span>
                                        </a>
                                    </li>
                                    @canany(['text_messaging_add_edit'])
                                    <li class="nav-item">
                                        <a class="" href="#">
                                            <span class="item-name">Text Messages</span>
                                        </a>
                                    </li>
                                    @endcanany
                                    @canany(['commenting_add_edit', 'commenting_view'])
                                    <li class="nav-item">
                                        <a class="" href="#">
                                            <span class="item-name">Comments</span>
                                        </a>
                                    </li>
                                    @endcanany
                                </ul>
                            </div>
                        </div>
                    </li>
                    @endcan
                    @canany(['lead_add_edit', 'lead_view'])
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
                                        <a href="{{route('leads/statuses')}}" class="">
                                            <span class="item-name">Dashboard</span>
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
                    @endcanany
                    <!-- end charts -->

                    <li class="nav-item px-3">
                        <div id="timers_container" class="timers-container text-nowrap"><div>
                            <div>
                                <div class="nav-item">
                                    <div class="js-timer-root">
                                        <a href="#" class="startTimer">
                                            <i class="far fa-clock fa-lg  timer-clock-icon"></i>
                                            <span class="text-nowrap">Start Timer</span>
                                            <span class="time-status"></span>
                                        </a>
                                    </div>
                                </div>
                                <div class="timerCounter" style="display: none;">
                                    <div class="timer-panel mb-5" style="margin-left: -190px;background-color: #ccc;">
                                        <div class="timer-row d-flex">
                                            <div>
                                                <a href="#">
                                                    <i class="timerAction fas fa-pause" id='pauseCounter'>&nbsp;<span class="time-status"></span></i>
                                                </a> 
                                            </div>
                                            <input type="hidden" name="smart_timer_id" id="smart_timer_id" value="">
                                            <input type="hidden" name="pause_smart_timer_id" id="pause_smart_timer_id" value="">
                                            <span class="timer-secondary-actions d-flex" style="margin-left: 40px;">
                                                <a href="#" onclick="deleteTimer();" class="btn btn-link timer-delete-action">Delete</a>
                                                <a href="#" onclick="saveTimer();" class="btn btn-secondary timer-save-action float-none">Save</a>
                                            </span>
                                        </div>
                                        <div class="input-row">
                                            <label><i class="fa fa-suitcase fa-2x"></i>
                                            <div class="counting-textarea d-flex">
                                                <select id="timer_case_id" name="timer_case_id" class="form-control">
                                                    <option value="">Select case</option>    
                                                    <option value="165">oct 27</option>
                                                </select>
                                            </div>
                                            </label>
                                        </div>
                                        <div class="input-row">
                                            <label>
                                                <img alt="" class="mr-1" src="{{asset('/svg/note-.svg')}}" width="24" height="24">
                                                <div class="counting-textarea d-flex">
                                                    <textarea placeholder="Description" class="form-control timer-text-field" maxlength="1024" rows="1" style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 39px;"></textarea>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

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