<?php
$upcoming_events=""; 
if(isset($_GET['upcoming_events'])){
    $upcoming_events=$_GET['upcoming_events'];
}
// $CommonController= new App\Http\Controllers\CommonController();

?>
<div class="col-md-12">
    <div id="calendar_page" class="case_info_page" style="">
    <h3 id="hiddenLable">{{($CaseMaster->case_title)??''}}</h3>
        <div id="case-calendar-container" data-can-edit-events="true">
            <div class="case-calendar-view mt-2">
                <div class="w-100 d-flex align-items-center">
                    <div class="d-flex ml-auto align-items-center">
                        <input type="hidden" id="case_id" value="{{ $CaseMaster->case_id }}" >
                        <form action="" method="get">
                            <div class="custom-control custom-switch mr-2 upcoming-toggle d-flex align-items-center">
                                <input type="hidden" name="upcoming_events" id="upcoming_event" value="{{ (isset($_GET['upcoming_events']) && $_GET['upcoming_events'] == "off") ? 'off' : 'on' }}">
                                <label class="switch pr-3 switch-success" style="margin-top: 10px;"><span>Only show upcoming events</span>
                                    <input type="checkbox" id="mc" {{ (!isset($_GET['upcoming_events'])) ? "checked" : "" }} 
                                        {{ (isset($_GET['upcoming_events']) && $_GET['upcoming_events'] == "on") ? "checked" : "" }} value="on"><span class="slider"></span>
                                </label>
                                <i id="event-toggle-note" aria-hidden="true" class="fa fa-question-circle icon-question-circle icon ml-1" data-toggle="tooltip" title="Recurring events are limited to 1 year from today"></i>
                            </div>
                            <input type="submit" style="display: none;" id="submit" name="search" value="true">
                        </form>
                        @can('event_add_edit')
                            @can('case_add_edit')
                        <a data-toggle="modal" data-target="#loadAddEventPopup" data-placement="bottom"
                            href="javascript:;"> <button class="btn btn-primary btn-rounded m-1" type="button"
                                onclick="loadAddEventPopup();">Add Event</button></a>
                            @endcan
                        @endcan

                    </div>
                </div>
                
                @php
                    $authUser = auth()->user();
                @endphp
                <table class="mt-3 border-light event-list-view table table-sm table-hover" id="event_list_table">
                    <tbody>
                    @if(count($allEvents) == 0)
                        <tr>
                            <th colspan="6">
                                <div class="mt-3 empty-events alert alert-info fade show" role="alert">
                                    <div class="d-flex align-items-start">
                                        <div class="w-100">There are no upcoming events scheduled.</div>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    @else
                        <tr>
                            <th colspan="6">
                                <h2 class="mb-2 mt-4 font-weight-bold text-dark">{{ date('Y', strtotime(@$allEvents->first()->user_start_date)) }}</h2>
                            </th>
                        </tr>
                        <tr>
                            <th width="5%">Date</th>
                            <th width="20%">Time</th>
                            <th width="35%">Title</th>
                            <th width="15%">Type</th>
                            <th width="15%">Users</th>
                            <th width="13%"></th>
                        </tr>
                        @include('case.event.load_event_list')
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('case.event.event_modals')

{{-- Duplicate code, Made common code --}}
{{-- <div id="deleteEvent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Delete Recurring Event</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="eventID">
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div> --}}

{{-- Made common code, so commented --}}
{{-- <div id="loadReminderPopup" class="modal fade bd-example-modal-lg modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static" style="">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Event Reminders</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="reminderDAta">
                    
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div> --}}

@include('calendar.partials.load_grant_access_modal')

<style> 
.modal { overflow: auto !important; } 
</style>

@section('page-js-inner')
{{-- <script src="{{ asset('assets\js\custom\calendar\listevent.js?').env('CACHE_BUSTER_VERSION') }}"></script> --}}
<script type="text/javascript">
    $(document).ready(function () {
        $("input:checkbox#mc").click(function () {
            if($(this).is(":checked")) {
                $("#upcoming_event").val('on');
            } else {
                $("#upcoming_event").val('off');
            }
            $('#submit').click();
        });
    });
    /* $('#loadEditEventPopup,#loadAddEventPopup').on('hidden.bs.modal', function () {
        // $("#preloader").show();
          window.location.reload();  
        // loadMoreEvent(1, filter = 'true');      
    }); */

    // Made common code, so commented
    /* function loadReminderPopup(evnt_id) {
        $("#reminderDAta").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadReminderPopup", // json datasource
                data: {
                    "evnt_id": evnt_id
                },
                success: function (res) {
                    $("#reminderDAta").html('Loading...');
                    $("#reminderDAta").html(res);
                    $("#preloader").hide();
                 
                }
            })
        })
    } */
    
    // Made common code, check addevent.js file
    /* function loadEventReminderPopup(event_id, event_recurring_id) {
        $("#eventReminderData").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadEventReminderPopup", // json datasource
                data: {
                    "event_id": event_id, event_recurring_id: event_recurring_id,
                },
                success: function (res) {
                    $("#eventReminderData").html('Loading...');
                    $("#eventReminderData").html(res);
                    $("#preloader").hide();
                 
                }
            })
        })
    } */
    // Made common code, check addevent.js file
    /* function loadEventComment(event_id, event_recurring_id) {
        $("#eventCommentPopup").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadEventCommentPopup", // json datasource
                data: {
                    "event_id": event_id, event_recurring_id: event_recurring_id,
                },
                success: function (res) {
                    $("#eventCommentPopup").html('Loading...');
                    $("#eventCommentPopup").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } */
    // Made common code, check addevent.js file
    /* function loadAddEventPopup() {
        $("#AddEventPage").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadAddEventPage", // json datasource
                data: {
                    "case_id": <?=$CaseMaster->case_id?>
                },
                success: function (res) {
                    $("#AddEventPage").html('Loading...');
                    $("#AddEventPage").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } */

    // Made common code, check addevent.js file
    /* function editEventFunction(evnt_id, event_recurring_id = null) {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadEditEventPage", // json datasource
                data: {
                    "evnt_id":evnt_id,
                    "from":"edit",
                    "event_recurring_id": event_recurring_id
                },
                success: function (res) {
                    $("#loadCommentPopup").modal('hide');
                    $("#loadEditEventPopup .modal-dialog").addClass("modal-xl");
                    $("#EditEventPage").html('');
                    $("#EditEventPage").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } */

    // Made common code. Not in use
    /* function editSingleEventFunction(evnt_id) {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadSingleEditEventPage", // json datasource
                data: {
                    "evnt_id":evnt_id
                },
                success: function (res) {
                    $("#loadCommentPopup").modal('hide');

                    $("#EditEventPage").html('');
                    $("#EditEventPage").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } */

    // Made common code, check addevent.js file
    /* function deleteEventFunction(eventRecurringId, eventId, types) {
      if(types=='single'){
          $("#deleteSingle").text('Delete Event');
      }else{
        $("#deleteSingle").text('Delete Recurring Event');
      }
        $("#preloader").show();
        $(function () {
            // alert(id);
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/deleteEventPopup", 
                data: {
                    "event_recurring_id": eventRecurringId, 'event_id': eventId
                },
                success: function (res) {
                    $("#deleteEventModalBody").html('');
                    $("#deleteEventModalBody").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } */

    function printEntry()
    {
        $('#hiddenLable').show();
        var canvas = $(".printDiv").html(document.getElementById("calendar_page").innerHTML);
        $(".main-content-wrap").remove();
        window.print(canvas);
        // w.close();
        $(".printDiv").html('');
        $('#hiddenLable').hide();
        window.location.reload();
        return false;  
    }
    $('#hiddenLable').hide();

    document.onreadystatechange = function() {
        if (document.readyState !== "complete") {
            document.querySelector(
                "#preLoader").style.display = "show";
        } else {
            document.querySelector(
                "#preLoader").style.display = "none";
        }
    };
</script>
@stop
