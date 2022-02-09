<div class="d-flex align-items-center pl-4 pb-4">
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
                Leads ({{$leadCount}})</a>
        </li>
    </ul>

    <div class="ml-auto d-flex align-items-center d-print-none">
        @can("lead_add_edit")
        <a class="btn btn-link pr-4 d-print-none text-black-50" rel="facebox" href="{{BASE_URL}}lead_setting#Statuses">Customize</a>
        @endcan
        <a data-toggle="modal" data-target="#loadAddFeedBack" data-placement="bottom" href="javascript::void(0);">
            <button onclick="setFeedBackForm('single','Lead Management');" type="button" class="btn btn-link pr-4 d-print-none text-black-50">Tell us what you think</button>
        </a>
        <?php
        if(request()->is('leads/onlineleads') || request()->is('leads/statuses')){
            ?>
              <a class="btn btn-link pr-4 d-print-none text-black-50" rel="facebox" href="javascript:void(0);" onclick="printLead('{{\Route::current()->getName()}}')"> <i class="fas fa-print"></i> Print</a> 
       <?php
        }else{
            ?>
              <a class="btn btn-link pr-4 d-print-none text-black-50" rel="facebox" href="javascript:void(0);" onclick="printData();"> <i class="fas fa-print"></i> Print</a> 
       <?php
        }?>
        @can("lead_add_edit")
        <span id="settingicon" class="pr-2">
            <button class="btn btn-secondry dropdown-toggle settingButtons" id="shuesuid" type="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i aria-hidden="true" class="fas fa-cog icon"></i>
            </button>
            <div class="dropdown-menu bg-transparent shadow-none p-0 m-0" x-placement="bottom-start"
                style="position: absolute; transform: translate3d(0px, 34px, 0px); top: 0px; left: 0px; will-change: transform ">
                <div class="card">
                    <div tabindex="-1" role="menu" aria-hidden="false"
                        class="dropdown-menu dropdown-menu-right show" x-placement="top-end">
                        
                        <a href="{{route('lead_setting')}}" class="dropdown-item">
                            Lead Settings</a>
                     
                        <a href="{{route('custom_fields')}}?group=client" class="dropdown-item">Lead Custom Fields</a>
                     
                    </div>
                </div>
            </div>
        </span>
        @endcan
        @can("lead_add_edit")
        <a data-toggle="modal" data-target="#addLead" data-placement="bottom" href="javascript:;">
            <button disabled class="btn btn-primary btn-rounded m-1" id="leadButton" type="button"
                onclick="addLead();">Add Lead</button></a>
        @endcan
    </div>
</div>
