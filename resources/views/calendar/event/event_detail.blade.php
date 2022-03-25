@extends('layouts.master')

@section('page-css')
<style>
body > 
#editor {
    margin: 50px auto;
    max-width: 720px;
}
#editor {
    height: 200px;
    background-color: white;
}
</style>
@endsection

@section('main-content')

<div id="event_detail_view_div">
    <div class="modal-header">
        <div class="header-left-group">
            <a href="{{ route('events/') }}">Back to Calendar</a>
            <h5 class="mb-0">
                <span class="modal-title">{{ ($event->event_title) ? $event->event_title : "&lt;no-title&gt" }}</span>
            </h5>
            @php
                $userTimezone = auth()->user()->user_timezone ?? 'UTC';
                if($event->is_full_day == 'no') {
                    $startDateTime= convertUTCToUserTime($eventRecurring->start_date.' '.$event->start_time, $userTimezone);
                    $endDateTime= convertUTCToUserTime($eventRecurring->end_date.' '.$event->end_time, $userTimezone);
                }
                $endOnDate = ($event->end_on && $event->is_no_end_date == 'no') ? 'until '. date('F d, Y', strtotime(convertUTCToUserDate($event->end_on, $userTimezone))) : "";
            @endphp
            @if($event->is_full_day == 'no')
            <h6 class="modal-subtitle mt-2 mb-0">{{date('D, M jS Y, h:ia',strtotime($startDateTime))}} —
                {{date('D, M jS Y, h:ia',strtotime($endDateTime))}}</h6>
            @else
            <h6 class="modal-subtitle mt-2 mb-0">{{ date('D, M j Y',strtotime($eventRecurring->start_date)) }}, All day </h6>
            @endif
        </div>
        <div class="action-buttons">
            <div>
                @can('delete_items')
                <?php if(empty($event->parent_event_id) && $event->is_recurring == "no"){ ?>
                        <a class="align-items-center" data-toggle="modal" data-target="#deleteEventModal"
                            data-placement="bottom" href="javascript:;"
                            onclick="deleteEventFunction({{$eventRecurring->id}}, {{$event->id}},'single');">
                            <button type="button" class="delete-event-button m-1 btn btn-outline-danger">Delete</button> 
                        </a>
                <?php }else if($event->edit_recurring_pattern == "single event" && $event->is_recurring == "yes"){ ?>
                        <a class="align-items-center" data-toggle="modal" data-target="#deleteEventModal"
                            data-placement="bottom" href="javascript:;"
                            onclick="deleteEventFunction({{$eventRecurring->id}}, {{$event->id}},'single');">
                            <button type="button" class="delete-event-button m-1 btn btn-outline-danger">Delete</button> 
                        </a>
                <?php }else{ ?>
                        <a class="align-items-center" data-toggle="modal" data-target="#deleteEventModal"
                        data-placement="bottom" href="javascript:;"
                        onclick="deleteEventFunction({{$eventRecurring->id}}, {{$event->id}},'multiple');">
                        <button type="button" class="delete-event-button m-1 btn btn-outline-danger">Delete</button>
                        </a>
                <?php } ?>
                @endcan
                @can('event_add_edit')
                <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup"
                data-placement="bottom" href="javascript:;"
                onclick="editEventFunction({{$event->id}}, {{$eventRecurring->id}});">
                <button type="button" class="btn btn-primary  pendo-exp2-add-event m-1 btn btn-cta-primary">Edit</button> </a>
                @endcan
                
            </div>
        </div>
    </div>
    <div class="modal-body" style="max-height: 500px;overflow: auto;">
        <div>
            <div class="container-fluid event-detail-modal-body ml-0">
                <div class="row row ">
                    <input type="hidden" value="{{ $event->id }}" id="event_id">
                    <div class="event-detail-column col-6">
                        <div class="event-column-container">
                            <div class="mb-2 row ">
                                <div class="col-3"><b>Event Type</b></div>
                                <div class="detail-info  col-9">
                                    <span class="event-type-badge badge badge-secondary" style="background-color: {{ @$event->eventType->color_code }}; font-size: 12px; height: 20px;">{{ @$event->eventType->title}}</span>
                                </div>
                            </div>
                            <div class="mb-2 row ">
                                <div class="col-3"><b>Location</b></div>
                                <div class="detail-info event-location-section col-9">
                                    @if($event->event_location_id)
                                        {{ $event->eventLocation->full_address }}
                                    @else
                                        <p class="d-inline" style="opacity: 0.7;">Not specified</p>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-2 row ">
                                <div class="col-3"><b>Repeats</b></div>
                                <?php if(isset($event) && $event->event_recurring_type!=NULL){?>
                                <?php if($event->event_recurring_type=='DAILY'){?>
                                <div class="detail-info recurring-rule-text col-9">
                                    {{ ($event->event_interval_day > 1) ? "Every ".$event->event_interval_day." days" : "Daily" }} {{ $endOnDate }}
                                </div>
                                <?php }else if($event->event_recurring_type=='EVERY_BUSINESS_DAY'){?>
                                <div class="detail-info recurring-rule-text col-9">Weekly {{ $endOnDate }} on Weekdays</div>
                                <?php }else if($event->event_recurring_type=='CUSTOM'){ ?>
                                <div class="detail-info recurring-rule-text col-9">
                                    {{ ($event->event_interval_week > 1) ? "Every ".$event->event_interval_week." weeks" : "Weekly" }} {{ $endOnDate }} on {{ implode(", ", $event->custom_event_weekdays) }}
                                </div>
                                <?php }else if($event->event_recurring_type=='WEEKLY'){ ?>
                                <div class="detail-info recurring-rule-text col-9">
                                    Weekly {{ $endOnDate }} on {{ date('l', strtotime($eventRecurring->start_date))."s" }}
                                </div>
                                <?php }else if($event->event_recurring_type=='MONTHLY'){ ?>
                                <div class="detail-info recurring-rule-text col-9">
                                    {{ ($event->event_interval_month > 1) ? "Every ".$event->event_interval_month." months " : "Monthly" }} {{ $endOnDate }}
                                    @if($event->monthly_frequency == "MONTHLY_ON_DAY")
                                        {{ "on the ".date("jS", strtotime($eventRecurring->user_start_date))." day of the month" }}
                                    @elseif($event->monthly_frequency == "MONTHLY_ON_THE")
                                        @php
                                            $day = ceil(date('j', strtotime($eventRecurring->start_date)) / 7);
                                        @endphp
                                        {{ "on the ".$day.date("S", mktime(0, 0, 0, 0, $day, 0))." ".date('l', strtotime($eventRecurring->start_date)) }}
                                    @else
                                    @endif
                                </div>
                                <?php }else if($event->event_recurring_type=='YEARLY'){ ?>
                                <div class="detail-info recurring-rule-text col-9">
                                    {{ ($event->event_interval_year > 1) ? "Every ".$event->event_interval_year." years " : "Yearly" }} {{ $endOnDate }}
                                    @if($event->yearly_frequency == "YEARLY_ON_DAY")
                                        {{ "in ".date("F", strtotime($eventRecurring->user_start_date))." on the ".date("jS", strtotime($eventRecurring->user_start_date))." day of the month" }}
                                    @elseif($event->yearly_frequency == "YEARLY_ON_THE")
                                        @php
                                            $day = ceil(date('j', strtotime($eventRecurring->start_date)) / 7);
                                        @endphp
                                        {{ "on the ".$day.date("S", mktime(0, 0, 0, 0, $day, 0))." ".date('l', strtotime($event->start_date))." in ".date("F", strtotime($eventRecurring->user_start_date)) }}
                                    @else
                                    @endif
                                </div>
                                <?php } ?>
                                <?php }else { ?><div class="detail-info recurring-rule-text col-9">Never</div><?php } ?>
                            </div>
                            <div class="mb-2 row ">
                                <div class="col-3"><b>Case</b></div>
                                <div class="detail-info  col-9">
                                    @if(!empty($event->case))
                                    <a href="{{ route('info', $event->case->case_unique_number) }}">{{$event->case->case_title}}</a>
                                    @else
                                    <p class="d-inline" style="opacity: 0.7;">Not specified</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-2 row ">
                                <div class="col-3"><b>Lead</b></div>
                                <div class="detail-info  col-9">
                                    @if(!empty($event->leadUser))
                                    <a href="{{ route('lead_details/info', $event->leadUser->id) }}">{{$event->leadUser->full_name}}</a>
                                    @else
                                    <p class="d-inline" style="opacity: 0.7;">Not specified</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-2 row ">
                                <div class="col-3"><b>Description</b></div>
                                <div class="detail-info  col-9">
                                    <?php 
                                    if($event->event_description!=''){?>
                                    <p class="d-inline" style="opacity: 0.7;">{{$event->event_description}}</p>
                                    <?php }else{?>
                                    <p class="d-inline" style="opacity: 0.7;">Not specified</p>

                                    <?php } ?>
                                </div>
                            </div>
                            <div class="mb-2 row ">
                                <div class="col-3"><b>Reminders</b></div>
                                <div class="detail-info  col-9">
                                    <div>
                                        <ul id="reminder_list" class="list-unstyled">
                                        
                                        </ul>

                                        <a class="align-items-center" data-toggle="modal" data-target="#loadEventReminderPopup"
                                            data-placement="bottom" onclick="loadEventReminderPopup({{$event->id}}, {{ $eventRecurring->id}})" href="javascript:;">
                                            Edit
                                            Reminders</a>
                                    
                                    </div>
                                </div>
                            </div>
                            @if($event->is_event_private == 'yes')
                            <div class="mb-2 row ">
                                <div class="col-3"><b>Privacy</b></div>
                                <div class="detail-info privacy-message col-9" style="color: rgb(202, 66, 69);">This event is private.</div>
                            </div>
                            @endif
                            <hr>
                            <div>
                                <div class="event-sharing-list">
                                    <div class="mb-2"><b>Shared / Attending</b></div>
                                    <div>
                                        <div class="mb-2 sharing-user">
                                            <div class="row ">                              
                                                @if(!empty($linkedUser))
                                                    @foreach($linkedUser as $kstaff=>$vstaff)
                                                        <div class="col-8">
                                                            <div class="d-flex flex-row">
                                                                @if($vstaff->utype == 'staff')
                                                                <a href="{{ route('contacts/attorneys/info', base64_encode($vstaff->user_id)) }}"
                                                                    class="d-flex align-items-center user-link"
                                                                    title="{{ $vstaff->user_type }}">{{ $vstaff->full_name }}
                                                                    ({{ $vstaff->user_type }})</a>
                                                                @elseif($vstaff->utype == 'lead')
                                                                <a href="{{ route('lead_details/info', $vstaff->user_id) }}"
                                                                    class="d-flex align-items-center user-link"
                                                                    title="{{ $vstaff->user_type }}">{{ $vstaff->full_name }}
                                                                    {{ $vstaff->user_type }}</a>
                                                                @else
                                                                <a href="{{ route('contacts/clients/view', $vstaff->user_id) }}"
                                                                    class="d-flex align-items-center user-link"
                                                                    title="{{ $vstaff->user_type }}">{{ $vstaff->full_name }}
                                                                    {{ $vstaff->user_type }}</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-4"><b
                                                                style="color: rgb(99, 108, 114);"><?php if($vstaff->attending=='yes'){ echo "Attending"; } ?></b>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="event-detail-history col-6 scrollbar scrollbar-primary" style="height: 400px !important;">
                        <div>
                            <div>
                                <div id="editorArea" class="mt-3 mb-3"  style="display: none;">
                                    <form class="addComment" id="addComment" name="addComment" method="POST">
                                        @csrf
                                        <input class="form-control" value="{{ $event->id}}" name="event_id" type="hidden">
                                        <input class="form-control" value="{{ $eventRecurring->id}}" name="event_recurring_id" type="hidden">
                                        <div id="editor">
                                            
                                        </div>
                                        <div class="row ">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary mt-3 mb-3  float-right">Post Comment</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="mt-3 mb-3" id="linkArea" >
                                    <a id="addcomment"  onclick="toggelComment()" href="#">Add a comment...</a>
                                </div>
                                <hr>
                                <div class="detail-label">History</div>
                                <div class="history-contents container-fluid" id="commentHistory">

                                </div>
                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
            
    </div>
</div>

@include('case.event.event_modals')

@section('page-js')
<script src="{{ asset('assets\js\custom\calendar\addevent.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script type="text/javascript">
    "use strict";
$(document).ready(function () {
    var toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'], // toggled buttons
        ['blockquote', 'code-block'],
        [{
            'header': 1
        }, {
            'header': 2
        }], // custom button values
        [{
            'list': 'ordered'
        }, {
            'list': 'bullet'
        }],
        
        [{
            'size': ['small', false, 'large', 'huge']
        }], // custom dropdown
        [{
            'header': [1, 2, 3, 4, 5, 6, false]
        }],

        [{
            'color': []
        }, {
            'background': []
        }], // dropdown with defaults from theme
        [{
            'font': []
        }],
        [{
            'align': []
        }],

        ['clean'] // remove formatting button
    ];

    var quill = new Quill('#editor', {
        modules: {
            toolbar: toolbarOptions
        },
        theme: 'snow'
    });

    afterLoader();

    $('#addComment').submit(function (e) {
        beforeLoader();

        e.preventDefault();
        var delta =quill.root.innerHTML;
        if(delta=='<p><br></p>'){
            toastr.error('Unable to post a blank comment', "", {
                positionClass: "toast-top-full-width",
                containerId: "toast-top-full-width"
            })
            afterLoader();

        }else{
            var dataString = $("#addComment").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveEventComment", // json datasource
                data: dataString + '&delta=' + delta,
                success: function (res) {
                    $(this).find(":submit").prop("disabled", true);
                    $("#innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        afterLoader();

                        return false;
                    } else {
                        toastr.success('Your comment was posted', "", {
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        loadCommentHistory({{$event->id}}, {{$eventRecurring->id}})
                        quill.root.innerHTML='';
                        afterLoader();

                    }
                }
            });
        }
    });
});

// Load event comment history
loadCommentHistory({{$event->id}}, {{$eventRecurring->id}});
function loadCommentHistory(event_id, event_recurring_id) {
    $.ajax({
        type: "POST",
        url: baseUrl + "/court_cases/loadCommentHistory",
        data: {
            "event_id": event_id, "event_recurring_id": event_recurring_id
        },
        success: function (res) {
            $("#commentHistory").html(res);
        }
    })
}
/**
* Load event reminder list
*/
loadReminderHistory({{$event->id}}, {{$eventRecurring->id}});
function loadReminderHistory(event_id, event_recurring_id) {
    $.ajax({
        type: "POST",
        url: baseUrl + "/court_cases/loadReminderHistory",
        data: {
            "event_id": event_id, "event_recurring_id": event_recurring_id
        },
        success: function (res) {
            $("#reminder_list").html(res);
        }
    })
}
function toggelComment(){
    $("#linkArea").hide();
    $("#editorArea").show();
}
</script>
@endsection

@endsection



