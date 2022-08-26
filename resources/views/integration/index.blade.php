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
                        <div class="p-2"> <img src="https://assets.mycase.com/packs/apps/calendar_sync_v2-16e3360a09.png" width="96" height="96"> </div>
                        <div class="card-text p-2"> Sync LegalCase with your Google or Outlook 365 calendars for 2-way synchronization of calendar information. <a href="#calendar_instruction" id="calendar-integration-instruction">How does it work?</a> </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex align-items-center">
                        {{-- <div class="marketplace-actions ml-auto d-flex-align-items-center"> 
                            <a class="calendar-integration-uninstall" target="_blank" href="https://login.microsoftonline.com/common/adminconsent?client_id=a6ce0a65-b5ac-4dbe-87c9-dbfb31828762&state=12345&redirect_uri=https://localhost/outlook/access/token" >Sync with LegalCase</a> 
                        </div> --}}
                        <div class="marketplace-actions ml-auto d-flex-align-items-center">
                            <i class="fas fa-cloud-download-alt ml-auto" aria-hidden="true"></i>
                            <a href="javascript:;" id="calendar-integration-sync" class="pl-1 text-dark calendar-integration-sync" data-legacy-google-sync-enabled="false" data-legacy-outlook-sync-enabled="false" data-google-auth-url="https://accounts.google.com/o/oauth2/v2/auth?client_id=40606345412-lbq4k0178ie5jrd8lm5k7shprdip8b3b.apps.googleusercontent.com&amp;redirect_uri=https%3A%2F%2Flogin.mycase.com%2Foauth2callback&amp;state=eyJhbGciOiJIUzI1NiJ9.eyJ1dWlkIjoiY2UxODQwOGQtZDkwNy00YTg1LWI2OTEtZjc2YmRmOGUzZmNkIn0.smd9zYLt2vSTcYMmZJt0hLm_NXbM2kH4V5IWRTkpapo&amp;scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fcalendar+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email&amp;response_type=code&amp;access_type=offline&amp;prompt=consent">
                              Sync with LegalCase
                            </a>
                        </div>
                        <div class="marketplace-actions ml-auto d-flex-align-items-center"> 
                            <i class="fas fa-cog ml-auto" aria-hidden="true"></i> 
                            <a id="calendar-integration-settings" class="px-1" href="#" data-updated-google-sync="false" data-outlook-sync="true" data-google-sync-active="false">Settings</a> 
                            <i class="fas fa-times" aria-hidden="true"></i> 
                            <a class="calendar-integration-uninstall" href="#" onclick="MyCase.Apps.Calendar.uninstall()">Uninstall</a> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                        <button type="button" data-testid="google-service-auth-btn" class="mr-3 rounded btn btn-secondary cal-service" style="width: 12em; height: 12em;">
                            <div class="d-flex flex-column align-items-center"><i class="calendar-integration-google mb-1"></i><span>Google Calendar</span></div>
                        </button>
                        <button type="button" data-testid="outlook-service-auth-btn" class="rounded btn btn-secondary cal-service" style="width: 12em; height: 12em;">
                            <div class="d-flex flex-column align-items-center"><i class="calendar-integration-outlook mb-1"></i><span>Outlook Calendar</span></div>
                        </button>
                    </div>
                </div>
                <div class="d-flex justify-content-center mb-4">
                    <a href="{{route('google/oauth')}}" id="sync_cal_btn" class="btn btn-primary">Set Up Sync</a>
                </div>
                <div>
                    <p><span class="font-weight-bold">What to expect: </span>Once you select your service, you'll need to grant LegalCase permission to access your calendar. After you've granted permission, you'll be returned to LegalCase to finish configuring the application.</p>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="calendar_sync_work" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
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
                        <div class="w-100" style="overflow: hidden;"><strong>Google Sync Successful! </strong><span>Events will sync <strong>automatically</strong>. It may take 5-10 minutes for events to sync.</span></div>
                    </div>
                </div>
                <div class="row ">
                    <div class="col-12 col-md-4"><i class="calendar-instruction-mc-in-google"></i></div>
                    <div class="pl-0 col-12 col-md-8">
                        <h4><strong>MyCase Events in Google</strong></h4>
                        <ul class="pl-3">
                            <li><strong>View &amp; Create</strong> MyCase events by selecting the MyCase checkbox on Google calendar.</li>
                            <br>
                            <li>Your MyCase events from the last 3 months and onwards will be synced.</li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="mt-4 row ">
                    <div class="col-12 col-md-4"><i class="calendar-instruction-google-in-mc p-2"></i></div>
                    <div class="pl-0 col-12 col-md-8">
                        <h4><strong>Google Events in MyCase</strong></h4>
                        <ul class="pl-3">
                            <li>Google events will <strong>automatically</strong> be synced to your MyCase Calendar.</li>
                            <br>
                            <li><strong>View</strong> the Google sync status at the bottom of your MyCase Calendar.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="justify-content-between modal-footer">
                <div class="w-100 d-flex justify-content-end">
                    <button type="button" class="btn btn-link">Close</button>
                    <button type="button" class="btn btn-primary">Go To Calendar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-js')
<script type="text/javascript">
$("#calendar-integration-sync").on("click", function() {
    $("#link_your_calendar").modal('show');
});

$(".cal-service").on("click", function() {
    $(".cal-service").removeClass("btn-cta-secondary");
    $(this).removeClass("btn-secondary");
    $(this).addClass("btn-cta-secondary");
    if($(this).attr('data-testid') == 'google-service-auth-btn') {
        $("#sync_cal_btn").prop("href", "{{route('google/oauth')}}");
    }
    else if($(this).attr('data-testid') == 'outlook-service-auth-btn') {
        $("#sync_cal_btn").prop("href", "{{route('outlook/oauth')}}");
    } else {
        $("#sync_cal_btn").prop("href", "javascript:;");
    }
})
</script>
@endsection
