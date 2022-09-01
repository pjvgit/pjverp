@extends('layouts.master')

@section('title', 'Integrations & Apps')

@section('main-content')
<div class="bootstrap mb-5">
    <h3 class="h3 font-weight-bold">Integrations &amp; Apps</h3>
    <div class="row">
        <div class="col-md-4 col-12">
            <div class="card marketplace-app mt-1 mb-1" id="mc-calendar-integration">
                <div class="installed"></div>
                <div class="card-body">
                    <div class="card-title">
                        <h4>Calendar Integration</h4> </div>
                    <div class="d-flex flex-row">
                        <div class="p-2"> <img src=" {{ asset('icon/calendar_sync.png') }}" width="250" height="96"> </div>
                        <div class="card-text p-2"> Sync LegalCase with your Google or Outlook 365 calendars for 2-way synchronization of calendar information. <a href="#calendar_instruction" id="calendar-integration-instruction">How does it work?</a> </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex align-items-center">
                        {{-- <div class="marketplace-actions ml-auto d-flex-align-items-center"> 
                            <a class="calendar-integration-uninstall" target="_blank" href="https://login.microsoftonline.com/common/adminconsent?client_id=a6ce0a65-b5ac-4dbe-87c9-dbfb31828762&state=12345&redirect_uri=https://localhost/outlook/access/token" >Sync with LegalCase</a> 
                        </div> --}}
                        @if($syncAccount)
                        <div class="marketplace-actions ml-auto d-flex-align-items-center"> 
                            <i class="fas fa-cog ml-auto" aria-hidden="true"></i> 
                            <a id="calendar-integration-settings" class="px-1" href="javascript:;" data-sync-id="{{ $syncAccount->id }}" >Settings</a> 
                            <i class="fas fa-times" aria-hidden="true"></i> 
                            <a class="calendar-integration-uninstall" href="#uninstall_sync_calendar" data-toggle="modal">Uninstall</a> 
                        </div>
                        @else
                        <div class="marketplace-actions ml-auto d-flex-align-items-center">
                            <i class="fas fa-cloud-download-alt ml-auto" aria-hidden="true"></i>
                            <a href="javascript:;" id="calendar-integration-sync" class="pl-1 text-dark calendar-integration-sync">
                              Sync with LegalCase
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Choose service to link your calendar --}}
<div id="link_your_calendar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="header-left-group">
                    <h1 class="mb-0 h5"><span class="modal-title">Link Your Calendar</span></h1></div>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <h4 class="text-center mt-3 mb-4">Choose a calendar service to link with LegalCase</h4>
                <div class="mb-4">
                    <div class="d-flex justify-content-center">
                        <button type="button" id="google-service-auth-btn" class="mr-3 rounded btn btn-secondary cal-service" style="width: 12em; height: 12em;">
                            <div class="d-flex flex-column align-items-center"><i class="calendar-integration-google mb-1"></i><span>Google Calendar</span></div>
                        </button>
                        <button type="button" id="outlook-service-auth-btn" class="rounded btn btn-secondary cal-service" style="width: 12em; height: 12em;">
                            <div class="d-flex flex-column align-items-center"><i class="calendar-integration-outlook mb-1"></i><span>Outlook Calendar</span></div>
                        </button>
                    </div>
                </div>
                <div class="d-flex justify-content-center mb-4">
                    <a href="#" id="sync_cal_btn" class="btn btn-primary">Set Up Sync</a>
                </div>
                <div>
                    <p><span class="font-weight-bold">What to expect: </span>Once you select your service, you'll need to grant LegalCase permission to access your calendar. After you've granted permission, you'll be returned to LegalCase to finish configuring the application.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Events synced successfully modal --}}
<div id="calendar_inte_work" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="header-left-group">
                    <h1 class="mb-0 h5"><span class="modal-title">How Calendar Sync Works</span></h1></div>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success fade show" role="alert">
                    <div class="d-flex align-items-start"><i aria-hidden="true" class="fa fa-check fa-lg mr-3 mt-1"></i>
                        <div class="w-100" style="overflow: hidden;"><strong>{{ @$syncAccount->service_name }} Sync Successful! </strong><span>Events will sync <strong>automatically</strong>. It may take 5-10 minutes for events to sync.</span></div>
                    </div>
                </div>
                <div class="row ">
                    <div class="col-12 col-md-4"><i class="calendar-instruction-mc-in-google"></i></div>
                    <div class="pl-0 col-12 col-md-8">
                        <h4><strong>LegalCase Events in {{ @$syncAccount->service_name }}</strong></h4>
                        <ul class="pl-3">
                            <li><strong>View &amp; Create</strong> LegalCase events by selecting the LegalCase checkbox on {{ @$syncAccount->service_name }} calendar.</li>
                            <br>
                            <li>Your LegalCase events from the last 3 months and onwards will be synced.</li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="mt-4 row ">
                    <div class="col-12 col-md-4"><i class="calendar-instruction-google-in-mc p-2"></i></div>
                    <div class="pl-0 col-12 col-md-8">
                        <h4><strong>{{ @$syncAccount->service_name }} Events in LegalCase</strong></h4>
                        <ul class="pl-3">
                            <li>{{ @$syncAccount->service_name }} events will <strong>automatically</strong> be synced to your LegalCase Calendar.</li>
                            <br>
                            <li><strong>View</strong> the {{ @$syncAccount->service_name }} sync status at the bottom of your LegalCase Calendar.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="justify-content-between modal-footer">
                <div class="w-100 d-flex justify-content-end">
                    <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                    <a href="{{ route('events/') }}" class="btn btn-primary">Go To Calendar</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Calendar synced settings modal --}}
<div id="calendar_inte_setting" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="header-left-group">
                    <h1 class="mb-0 h5"><span class="modal-title">{{ @$syncAccount->service_name }} Sync</span></h1>
                </div>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body" id="calendar_inte_setting_body">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">Sync Now</button>
            </div>
        </div>
    </div>
</div>

{{-- Uninstall synced calendar modal --}}
<div id="uninstall_sync_calendar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="header-left-group">
                    <h1 class="mb-0 h5"><span class="modal-title">Uninstall Calendar Sync</span></h1></div>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row ">
                    <div class="col-10">
                        <div><strong>Are you sure you want to uninstall your calendar sync?</strong></div>
                        <div class="mt-2">This will stop all new LegalCase updates to your {{ @$syncAccount->service_name }} Calendar and vice versa.</div>
                        <div class="d-flex mt-4" data-testid="delete-calendar-section">
                            <div class="">
                                <label class="d-inline-flex align-items-center">
                                    <input id="delete-calendar-checkbox-option" name="delete-calendar-checkbox" type="checkbox"><span class="ml-2 "></span></label>
                            </div><span><i class="fas fa-circle pr-1 text-danger"></i>Delete "{{ @$syncAccount->calendar_id }}" calendar and events from my {{ @$syncAccount->service_name }} Calendar account<div class="text-black-50 small">All 7 non-recurring event(s) will be removed from your {{ @$syncAccount->service_name }} Calendar</div><div class="text-black-50 small">All 2 recurring event(s) will be removed from your {{ @$syncAccount->service_name }} Calendar</div></span></div>
                    </div>
                    <div class="col-2"><i class="calendar-integration-google mb-1"></i></div>
                </div>
            </div>
            <div class="justify-content-between modal-footer">
                <div class="w-100 d-flex justify-content-end">
                    <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
                    <button type="button" class="ml-2 btn btn-danger" onclick="uninstallCalendra()">Uninstall</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-js')
<script src="{{ asset("assets\js\custom\integration\index.js?").env('CACHE_BUSTER_VERSION') }}"></script>
<script>
@if(session()->get('show_success_modal') == 'yes')
    $("#calendar_inte_work").modal('show');
@endif
</script>
@endsection
