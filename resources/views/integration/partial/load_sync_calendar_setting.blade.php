<ul class="nav nav-tabs">
    <li class="nav-item"><a href="#unresolved_items" data-toggle="tab" class="nav-link">Unresolved Items</a></li>
    <li class="nav-item"><a href="#cal_settings" data-toggle="tab" class="nav-link active">Settings</a></li>
</ul>   
<div class="tab-content">
    <div class="tab-pane fade" id="unresolved_items" role="tabpanel">
        <div>
            <div class="alert alert-success show" role="alert">
                <div class="d-flex align-items-start"><i aria-hidden="true" class="fa fa-check fa-lg mr-3 mt-1"></i>
                    <div class="w-100" style="overflow: hidden;">All done here! You have no unresolved events.</div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade pt-3 active show" id="cal_settings" role="tabpanel">
        <div class="mb-1">
            <div class="mb-2 no-gutters row ">
                <div class="col">
                    <div class="alert alert-info show" role="alert">
                        <div class="d-flex align-items-start">
                            <div class="w-100"><strong>Note: </strong>Your events will be <strong>automatically</strong> synced. It may take approximately <strong>5-10 minutes</strong> for a change to appear on your LegalCase/{{ @$syncAccount->service_name }} Calendar.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2 no-gutters row ">
                <div class="col-4"><strong>Account:</strong></div>
                <div class="col-8"><span>{{ $syncAccount->email ?? '' }}</span></div>
            </div>
            <div class="mb-2 no-gutters row ">
                <div class="col-4"><strong>Service:</strong></div>
                <div class="col-8"><span>{{ $syncAccount->service_name }} Calendar</span></div>
            </div>
            <div class="mb-2 no-gutters row ">
                <div class="col-4"><strong>Synced Calendar:</strong></div>
                <div class="synced-calendar col-8"><span>{{ $syncAccount->calendar_name ?? '' }}</span></div>
            </div>
            <div class="no-gutters row ">
                <div class="col-4"><strong>Status:</strong></div>
                <div class="col-8">
                    <div class="flex-row align-items-center" >
                        <div>
                            <div class="bg-success mr-1 d-inline-block" style="width: 10px; height: 10px; border-radius: 50%;"></div> <span>Active</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>