<div class="d-flex align-items-center pl-4 pb-0">
    <h3>Leads</h3>
    <ul class="d-inline-flex nav nav-pills pl-4">
        <li class="d-print-none nav-item">
            <a href="{{route('leads/statuses')}}"
                class="nav-link {{ request()->is('leads/statuses') ? 'active' : '' }} ">Status</a>
        </li>
        <li class="d-print-none nav-item">
            <a href="{{route('leads/active')}}"
                class="nav-link   {{ request()->is('leads/active') ? 'active' : '' }}">Active</a>
        </li>
        <li class="d-print-none nav-item">
            <a href="{{route('leads/donthire')}}"
                class="nav-link  {{ request()->is('leads/donthire') ? 'active' : '' }}">Do Not
                Hire</a>
        </li>
        <li class="d-print-none nav-item">
            <a href="{{route('leads/converted')}}"
                class="nav-link  {{ request()->is('leads/converted') ? 'active' : '' }}">Converted</a>
        </li>
        <li class="d-print-none nav-item">
            <a href="{{route('leads/onlineleads')}}"
                class="nav-link  {{ request()->is('leads/onlineleads') ? 'active' : '' }}">Online
                Leads</a>
        </li>
    </ul>

    <div class="ml-auto d-flex align-items-center d-print-none">
        <a class="btn btn-link pr-4 d-print-none text-black-50" rel="facebox" href="/lead_setting#Statuses">Customize</a>
        <a data-toggle="modal" data-target="#loadAddFeedBack" data-placement="bottom" href="javascript::void(0);">
            <button onclick="setFeedBackForm('single','Lead Management');" type="button" class="feedback-button mr-2 text-black-50 btn btn-link">Tell us what you think</button>
        </a>
        <button onclick="printEntry();return false;" class="btn btn-link text-black-50 pendo-case-print d-print-none">
            <i class="fas fa-print"></i> Print
        </button>
        <span id="settingicon" class="pr-2">
            <button class="btn btn-secondry dropdown-toggle settingButtons" id="shuesuid" type="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i aria-hidden="true" class="fas fa-cog icon"></i>
            </button>
            <div class="dropdown-menu bg-transparent shadow-none p-0 m-0" x-placement="bottom-start"
                style="position: absolute; transform: translate3d(0px, 34px, 0px); top: 0px; left: 0px; will-change: transform ">
                <div class="card">
                    <div tabindex="-1" role="menu" aria-hidden="false" class="dropdown-menu dropdown-menu-right show"
                        x-placement="top-end">

                        <a href="{{route('lead_setting')}}" class="dropdown-item">
                            Lead Settings</a>


                        <button type="button" tabindex="0" role="menuitem" class="dropdown-item"
                            onclick="markAsRead()">Lead Custom Fields</button>
                    </div>
                </div>
            </div>
        </span>
        <a data-toggle="modal" data-target="#loadAddTaskPopup" data-placement="bottom" href="javascript:;">
            <button disabled class="btn btn-primary btn-rounded m-1" id="leadButton" type="button"
                onclick="loadAddTaskPopup();">Add Task</button></a>
    </div>

</div>
