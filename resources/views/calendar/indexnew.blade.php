@extends('layouts.master')
@section('title', 'Legalcase - Simplify Your Law Practice | Cloud Based Practice Management')

@section('page-css')
<style>
.agenda-table th, .agenda-table td {
    padding: 5px 10px !important;
}
.fc-view.fc-AgendaView-view{
    max-height: 500px !important;
    overflow-y: auto !important;
}
.accordion{
    height: 700px;
    overflow-y: auto;
    overflow-x: hidden;
}
.card.ul-card__v-space{
    max-height: 250px;
    overflow-y: auto;
    overflow-x: hidden;
}
</style>    
@endsection
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 
?>
@if(!isset($evetData))
<div class="loadscreen" id="preloaderData" style="display: none;">
    <div class="loader"><img class="logo mb-3" src="{{asset('public/images/logo.png')}}" style="display: none" alt="">
        <div class="loader-bubble loader-bubble-primary d-block"></div>
    </div>
</div>

<div class="row">
    <div class="col-2">
    </div>
    <div class="col-8 " >
        <span  id="resetLink"  style="display: none;">
        <div class="d-flex justify-content-center p-2 alert alert-info"  role="alert">
            <div class="mb-0 font-weight-light event-type-filter-warning"><strong>Note!</strong> Calendar view is
                limited due to Event Type filter(s). <a href="{{route('events/')}}"
                    style="text-decoration: underline;">Reset</a></div>
        </div>
        </span>
    </div>
    <div class="col-2 pt-1 ">
        <div class="btn-group show float-right ">
            
           
        </div>
    </div>
</div>
<br>

{{-- <div class="separator-breadcrumb border-top"></div> --}}
<div id="calendar_view_div">
<div class="row">
    <div class="col-md-2 pt-0">
       
        <div class="accordion" id="accordionRightIcon">
            <div class="card ul-card__v-space">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title ul-collapse__icon--size ul-collapse__right-icon mb-0">
                        <a data-toggle="collapse" class="text-default" href="#accordion-item-icon-right-coll-1"
                            aria-expanded="true">Staff</a>
                    </h6>
                </div>
                <div id="accordion-item-icon-right-coll-1" class="collapse show" style="">
                    <div class="collapse show">
                        <div class="py-3 pl-2">
                            <?php
                                if(!empty($staffData)) {
                                 foreach($staffData as $k1=>$v1){?>
                                <style>
                                    .checkbox-outline-staff-{{$k1}} .checkmark {
                                        background: #fff;
                                        border: 1px solid {{$v1->default_color}} !important;
                                    }
    
                                    .checkbox-outline-staff-{{$k1}} .checkmark::after {
                                        border-color:{{$v1->default_color}} !important;
                                    }
                                    .checkbox-outline-staff-{{$k1}} input:checked~.checkmark {
                                        background-color: #fff !important;
                                    }
                                </style>
                                <?php
                                if($v1->id==Auth::User()->id){?>
                                       
                                       <label class="checkbox checkbox-outline-staff-{{$k1}}">
                                        <input type="checkbox" checked="checked" class="byuser" name="byuser[]"
                                            value="{{$v1->id}}"><span>My Calendar</span><span class="checkmark"></span>
                                    </label>
                                <?php }else{ ?>
                                        
                                    <label class="checkbox checkbox-outline-staff-{{$k1}}">
                                        <input type="checkbox"  class="byuser" name="byuser[]"
                                            value="{{$v1->id}}"><span>{{$v1->first_name}} {{$v1->last_name}}</span><span class="checkmark"></span>
                                    </label>
                                <?php } ?>
                            <?php } } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card ul-card__v-space">
                <div class="card-header header-elements-inline pos event-type-header">
                    
                    <h6 class="card-title ul-collapse__icon--size ul-collapse__right-icon mb-0">
                        <a data-toggle="collapse" class="text-default" href="#accordion-item-icon-right-coll-2"
                            aria-expanded="true">Event Type
                        </a> 
                    </h6>
                    <span class="ml-1 calendar-help-bubble question-mark-icon" style="cursor: pointer;">
                            <a class="mt-3 event-name align-items-center" style="float: none;" tabindex="0" role="button" href="javascript:;" data-toggle="popover" data-trigger="focus" title="Event Types" 
                                data-content="<div><p><b>No Event Types Checked:</b>&nbsp;All events will show whether they have an Event Type or not.</p><p><b>Select All Checked:</b> Only events that have an Event Type will show.</p><p><b>Specific Event Type Checked:</b>&nbsp;Only events that have the selected Event Types will show. Events with no event types will not show.</p></div>" 
                                data-html="true" style="float:left;" data-original-title="">
                                <i aria-hidden="true" class="fa fa-question-circle icon-question-circle icon text-primary"></i>
                            </a>
                        </span>
                   
                </div>
                <div id="accordion-item-icon-right-coll-2" class="collapse show">
                    <div class="collapse show">
                        <div class="py-3 pl-1">
                            <label class="checkbox checkbox-outline-primary">
                                <input type="checkbox" id="checkalltype" value="0">
                                <span>Select All</span><span class="checkmark"></span>
                            </label>
                            <div class="cp-checkbox-container mt-1 ml-2">
                                @if(!empty($EventType))
                                <?php foreach($EventType as $k=>$v){?>
                                    <style>
                                        .checkbox-outline-{{$k}} .checkmark {
                                            background: #fff;
                                            border: 1px solid {{$v->color_code}} !important;
                                        }
        
                                        .checkbox-outline-{{$k}} .checkmark::after {
                                            border-color:{{$v->color_code}} !important;
                                        }
                                        .checkbox-outline-{{$k}} input:checked~.checkmark {
                                            background-color: #fff !important;
                                        }
                                    </style>
                                <label class="checkbox checkbox-outline-{{$k}}">
                                    <input type="checkbox" class="event_type" name="event_type[]"
                                        value="{{$v->id}}"><span>{{$v->title}}</span><span class="checkmark"></span>
                                </label>
                                <?php } ?>
                                @endif
                            </div>
                        <button type="button" class="edit-event-types-calendar-picker btn btn-link"><i
                                aria-hidden="true" class="fa fa-plus icon-plus icon"></i><span
                                class="ml-2"><a href="{{ route('item_categories') }}">Customize</a></span></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card ul-card__v-space">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title ul-collapse__icon--size ul-collapse__right-icon mb-0">
                        <a data-toggle="collapse" class="text-default collapsed"
                            href="#accordion-item-icon-right-coll-3">Other</a>
                    </h6>
                </div>
                <div id="accordion-item-icon-right-coll-3" class="collapse">
                    <div class="py-3 pl-2">
                        <label class="checkbox checkbox-dark">
                            <input type="checkbox" class="sol" value="sol" checked="checked"><span>Statute of Limitations (SOL)</span><span
                                class="checkmark"></span>
                        </label>
                        <label class="checkbox checkbox-dark">
                            <input type="checkbox" class="mytask" value="mytask" checked="checked">
                                <span>My Tasks</span>
                                <span class="checkmark"></span>
                                <span class="ml-1 calendar-help-bubble" style="cursor: pointer;">
                                <a class="mt-3 event-name align-items-center" style="float: none;" tabindex="0" role="button" href="javascript:;" data-toggle="popover" data-trigger="focus" title="New task icons" 
                                    data-content="<table><tbody>
                                    <tr><td>
                                        <div class='line-help-bubble'> 
                                            <span class='calendar-badge d-inline-block undefined badge badge-secondary' style='background-color: rgb(202, 66, 69);float:left; width: 30px;'>
                                            DUE</span>
                                            <div style='float:left;margin-left: 5px;padding-left:3px;'>High priority</div>
                                        </div>
                                    </td></tr>
                                    <tr><td>
                                        <div class='line-help-bubble'> 
                                            <span class='calendar-badge d-inline-block undefined badge badge-secondary' style='background-color: rgb(254, 193, 8);float:left; width: 30px;'>
                                            DUE</span>
                                            <div style='float:left;margin-left: 5px;padding-left:3px;'>Medium priority</div>
                                        </div>
                                    </td></tr>
                                    <tr><td>
                                        <div class='line-help-bubble'> 
                                            <span class='calendar-badge d-inline-block undefined badge badge-secondary' style='background-color: rgb(40, 167, 68);float:left; width: 30px;'>
                                            DUE</span>
                                            <div style='float:left;margin-left: 5px;padding-left:3px;'>Low/No priority</div>
                                        </div>
                                    </td></tr></tbody></table>" data-html="true" style="float:left;" data-original-title=""><i aria-hidden="true" class="fa fa-question-circle icon-question-circle icon text-primary"></i></a>
                                </span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="sticky-bottom-filter">
                <div class="filter-list">
                    <div>
                        <div role="group" class="d-flex btn-group">
                            <button type="button" value="all" id="all" class="w-100 btn btn-outline-secondary active" onclick="allTask()">All</button>
                            <button type="button" value="unread" id="unread" class="w-100 btn btn-outline-secondary" onclick="allUnread()">Unread</button>
                        </div>
                    </div>
                </div>
                <br>
                <div class="filter-list">
                    <div class="form-group row">
                        <div class="col-12 form-group mb-3">
                            <select onchange="changeCase()" class="form-control case_or_lead" id="case_or_lead"
                                name="case_or_lead" data-placeholder="Search for an existing contact or company">
                                <option value="">Filter by case</option>
                                <optgroup label="Court Cases">
                                    <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                                    <option value="{{$Caseval->id}}">{{$Caseval->case_title}}
                                        <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?>
                                    </option>
                                    <?php } ?>
                                </optgroup>

                            </select>

                        </div>
                    </div>
                </div>
                <a data-toggle="modal" data-target="#loadAddFeedBack" data-placement="bottom" href="javascript::void(0);">                                    
                    <button onclick="setFeedBackForm('rating','Timesheet Calender');"  type="button" class="w-100 btn btn-outline-secondary m-1">Tell us what you think</button>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-10">
        <div class="row">
            <div id="calendarq" class="col-md-12"></div>
        </div>
    </div>
</div>
<input type="hidden" name="loadType" id="loadType" value="all">
</div>
@else
<div id="event_detail_view_div">
    @include('calendar.event.event_detail')
</div>
@endif

@include('case.event.event_modals')

{{-- Made common code --}}
{{-- <div id="loadAddEventPopup" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Event</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="AddEventPage">

                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div> --}}

{{-- Made common code, not in use --}}
{{-- <div id="loadAddEventPopupFromCalendar" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Event</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="AddEventPageFromCalendar">

                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div> --}}

{{-- Made common code --}}
{{-- <div id="deleteEvent" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-md">
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

{{-- Made common code --}}
{{-- <div id="loadEditEventPopup" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="EditEventPage">
        </div>
    </div>
</div> --}}

{{-- Made common code --}}
{{-- <div id="loadCommentPopup" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="eventCommentPopup">

        </div>
    </div>
</div> --}}

{{-- Made  --}}
{{-- <div id="loadReminderPopup" class="modal fade bd-example-modal-lg modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static" style="">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Set Event Reminders</h5>
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
</div>

<div id="loadReminderPopupIndex" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static" style="">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Set Event Reminders</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="reminderDataIndex">
                    
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div> --}}

<div id="deleteFromCommentBox" class="modal fade modal-overlay" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
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
                    <div class="col-md-12" id="deleteFromComment">
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>


<div id="markEventAsRead" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="markEventAsReadForm" id="markEventAsReadForm" name="markEventAsReadForm" method="POST">
            @csrf
            
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSingle">Mark All Events As Read</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="">
                            <p>Are you sure you want to mark all events as read?</p>
                        </div>
                    </div><!-- end of main-content -->
                </div>
                <div class="modal-footer">
                    <div class="form-group row float-right">
                        <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display: none;"></div>
                         <a href="#">
                            <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                        </a>
                        <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit">Yes, Mark Read</button>
                    </div> 
                </div>  
            </div>
        </form>
    </div>
</div>

@include('calendar.partials.load_grant_access_modal')

@if(!isset($evetData))
<span id="dp"><input type="text" class="form-control" style="width: 100px;margin-right: 1px;height: 28px;" name="datefilter" value="{{ convertUTCToUserTimeZone('dateOnly') }}" id="datepicker"></span>
@endif
<?php 
$defaultView="month";
if(isset($_GET['view']) && $_GET['view']=='day'){
    // $defaultView="agendaDay";
    $defaultView="staffView";

}else if(isset($_GET['view']) &&  $_GET['view']=='week'){
    $defaultView="agendaWeek";

}else if(isset($_GET['view']) && $_GET['view']=='month'){
    $defaultView="month";
}
?>
<style> 
.modal { overflow: auto !important; }
.layout-horizontal-bar .main-content-wrap {margin-top: 104px !important;}
#calendarq {cursor: pointer;}
.user-circle {border-radius: 50%;line-height: 25px;text-align: center;}
/* .fc-time{display: none;} */
</style>
@section('page-js')
{{-- <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@1.10.1/dist/scheduler.min.js" ></script> --}}
<script src="{{ asset('assets\js\custom\calendar\addevent.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script src="{{ asset('assets\js\custom\calendar\viewevent.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script src="{{ asset('assets\js\custom\feedback.js?').env('CACHE_BUSTER_VERSION') }}"></script>

<script type="text/javascript">

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendarq');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: '2022-03-07',
        /* headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        }, */
        customButtons: {
            agendaView: {
                text: 'Agenda',
                click: function() {
                    alert('clicked the custom button!');
                    // $('#calendarq').fullCalendar('changeView', 'AgendaView');
                }
            },
            staffView: {
                text: 'Day',
                click: function () {
                    $('#calendarq').fullCalendar('changeView', 'StaffView');
                    $('#calendarq').fullCalendar('rerenderResources');
                    $('#calendarq').fullCalendar( 'refetchEvents');
                    return true; 
                }
            },
            timeGridDay: {
                text: 'Staff',
                click: function () {
                    $('#calendarq').fullCalendar('changeView', 'agendaDay');
                }
            },
            /* timeGridWeek: {
                text: 'Week',
            },
            dayGridMonth: {
                text: 'Month',
            }, */
        },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'staffView,timeGridDay,timeGridWeek,dayGridMonth,agendaView'
        },
        views: {
            AgendaView: {
                type: 'custom',
                buttonText: 'Agenda',
                duration: { days: 31 },
                visibleRange: function(currentDate) {
                    console.log(currentDate);
                    return {
                    // start: currentDate.clone().subtract(1, 'days'),
                    start: currentDate.clone(),
                    end: currentDate.clone().add(31, 'days') // exclusive end, so 3
                    };
                },
            },
            StaffView: {
                type: 'timeGrid',
                // duration: { days: 2 },
                buttonText: 'Day',
            },
        },
        resources: function(callback, start, end, timezone) {
            var byuser = getByUser();
            var view = $('#calendarq').fullCalendar('getView');
                $.ajax({
                    url: 'loadEventCalendar/loadStaffView',
                    type: 'POST',
                    data: {resType: "resources", byuser: byuser, view_name: view.name},
                    success: function (doc) {
                        callback(doc);
                    }
                });
        },
        events: function(info, successCallback, failureCallback) {
            console.log(info);
            // var view = $('#calendarq').fullCalendar('getView');
            var start = info.startStr;
            var end = info.endStr;
            var eventTypes = [];
            $.each($("input[name='event_type[]']:checked"), function(){
                eventTypes.push($(this).val());
            });
            var byuser = getByUser();
            var bysol=getSOL();
            var mytask=getMytask();
            /* if(view.name == 'AgendaView') {
                alert();
                $.ajax({
                    url: 'loadEventCalendar/loadAgendaView',
                    type: 'POST',
                    data: {
                        start: start,
                        end: end,
                        event_type: JSON.stringify(eventTypes),
                        byuser: JSON.stringify(byuser),
                        case_id: $(".case_or_lead option:selected").val(),
                        searchbysol:bysol,
                        searchbymytask:mytask,
                        dateFilter:getDate(),
                        taskLoad:$("#loadType").val()
                    },
                    success: function (doc) {
                        $('.fc-view').html(doc);
                        $("#preloaderData").hide();
                    }
                });
            } else { */
                $.ajax({
                    url: 'loadEventCalendar/load',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        start: start,
                        end: end,
                        event_type: JSON.stringify(eventTypes),
                        byuser: JSON.stringify(byuser),
                        case_id: $(".case_or_lead option:selected").val(),
                        searchbysol:bysol,
                        searchbymytask:mytask,
                        dateFilter:getDate(),
                        taskLoad:$("#loadType").val()
                    },
                    success: function (doc) {
                        var events = [];
                        if (!!doc.result) {
                            $.map(doc.result, function (r) {
                                // console.log(r);
                                var color="#00cfd2";
                                if(r.etext != '') {
                                    color = r.etext;
                                }
                                if (r.event_title == null) {
                                    var t = '<div class="user-circle mr-1 d-inline-block" style="width: 10px; height: 10px; background-color: '+color+';"></div>'+r.start_time_user +
                                        ' -' + "<No Title>";
                                } else {
                                    var t = '<div class="user-circle mr-1 d-inline-block" style="width: 10px; height: 10px; background-color: '+color+';"></div>'+r.start_time_user +
                                        ' -' + r.event_title
                                }
                                var resource_id = [];
                                if(r.event_linked_staff) {
                                    $.each(r.event_linked_staff, function(ind, item) {
                                        resource_id.push(item.user_id);
                                    });
                                }
                                events.push({
                                    id: r.event_recurring_id,
                                    event_id: r.event_id,
                                    title: t,
                                    tTitle: r.event_title,
                                    /* start: r.start_date+'T'+r.st,
                                    end: r.end_date+'T'+r.et, */
                                    start: r.st,
                                    end: r.et,
                                    backgroundColor: '#d5e9ce',  
                                    textColor:'#000000',
                                    resourceIds: resource_id,
                                });
                            });
                        }
                        if (!!doc.sol_result) {
                            $.map(doc.sol_result, function (r) {
                                console.log(r);
                                if(r.sol_satisfied == 'yes') {
                                    var t = '<span class="calendar-badge d-inline-block undefined badge badge-secondary" style="background-color: rgb(92, 92, 92); width: 30px;"><i aria-hidden="true" class="fa fa-check"></i>SOL</span>'+' ' + r.event_title;
                                } else {
                                    var t = '<span class="calendar-badge d-inline-block undefined badge badge-secondary" style="background-color: rgb(92, 92, 92); width: 30px;">SOL</span>'+' ' + r.event_title;
                                }
                                var tplain = 'SOL'+' -' + r.event_title
                                events.push({
                                    mysol:'yes',
                                    case_id:r.case_unique_number,
                                    id: r.id,
                                    title: t,
                                    tTitle: tplain,
                                    start: r.start_date,
                                    end: r.end_date,
                                    textColor:'#000000',
                                    backgroundColor: 'rgb(236, 238, 239)',
                                    resourceId: r.created_by,
                                });
                            });
                        }
                        if (!!doc.mytask) {
                            $.map(doc.mytask, function (r) {
                                if(r.task_priority==1){
                                    var cds="background-color: rgb(202, 66, 69); width: 30px;";
                                }else if(r.task_priority==2){
                                    var cds="background-color: rgb(254, 193, 8); width: 30px;";
                                }else if(r.task_priority==3){
                                    var cds="background-color: rgb(40, 167, 68); width: 30px;";
                                }else{
                                    var cds="background-color: rgb(40, 167, 68); width: 30px;";
                                }    
                                var t = '<span class="calendar-badge d-inline-block undefined badge badge-secondary" style="'+cds+'">DUE</span>'+' ' + r.task_title
                                var tplain = 'DUE'+' -' + r.task_title
                                events.push({
                                    mytask:'yes',
                                    id: r.id,
                                    title: t,
                                    tTitle: tplain,
                                    start: r.task_due_on,
                                    end: r.task_due_on,
                                    textColor:'#000000',
                                    backgroundColor: 'rgb(236, 238, 239)',
                                    resourceId: r.created_by,
                                });
                            });
                        }
                        // callback(events);
                        successCallback(events)
                    },
                });
            // }
        },
        /* eventRender: function (event, element,view) {
            if(view.name == 'agendaWeek' || view.name == 'agendaDay') {
                element.find('.fc-title').html(event.title);
                element.find('.fc-title').append('<span class="yourCSS"></span> '); 
            } else{
                $(element).tooltip({
                    title: event.tTitle
                });
                element.find('.fc-title').html(event.title);
            }
        }, */
    });

    calendar.render();
});

    $(document).ready(function () {
        $("#datepicker").datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        }).on('changeDate', function(ev) {
            var date = new Date(ev.date);
            // console.log(date);
            $("#calendarq").fullCalendar( 'gotoDate', date );

        });
        function initEvent() {
            $('#external-events .fc-event').each(function () {

                // store data so the calendar knows to render an event upon drop
                $(this).data('event', {
                    title: $.trim($(this)
                        .text()), // use the element's text as the event title
                    color: $(this).css('background-color'),
                    stick: true // maintain when user navigates (see docs on the renderEvent method)
                });

                // make the event draggable using jQuery UI
                $(this).draggable({
                    zIndex: 999,
                    revert: true, // will cause the event to go back to its
                    revertDuration: 0 // original position after the drag
                });

            });
        }
        initEvent();

            /* initialize the calendar
            -----------------------------------------------------------------*/
            var newDate = new Date,
            date = newDate.getDate(),
            month = newDate.getMonth(),
            year = newDate.getFullYear();
            if(localStorage.getItem('weekends')=='hide'){
                var hd=[0, 6];
            }else{
                var hd=[];
            }
            var today = moment().day();

            jQuery(".js-form-add-event").on("submit", function (e) {
                e.preventDefault();
                var data = $('#newEvent').val();
                $('#newEvent').val('');
                $('#external-events').prepend('<li class="list-group-item bg-success fc-event">' + data + '</li>');
                initEvent();
            });
        
        $('#deleteFromCommentBox').on('hidden.bs.modal', function () {
            $('#calendarq').fullCalendar('refetchEvents');
        });

        $("input:checkbox.event_type").click(function () {
            $('#calendarq').fullCalendar('refetchEvents');
            resetButton();
        });
        $("input:checkbox.byuser").click(function () {
            $('#calendarq').fullCalendar( 'refetchResources' );
            $('#calendarq').fullCalendar('refetchEvents');
        });
        $("input:checkbox.sol").click(function () {
            $('#calendarq').fullCalendar('refetchEvents');
        });
        $("input:checkbox.mytask").click(function () {
            $('#calendarq').fullCalendar('refetchEvents');
        });
        
        $('#checkalltype').on('change', function () {
            $('.event_type').prop('checked', $(this).prop("checked"));
            $('#calendarq').fullCalendar('refetchEvents');
            resetButton();
        });
        $('#case_or_lead').on('change', function () {
            $('.event_type').prop('checked', $(this).prop("checked"));
            var SU = getCheckedUser();
        });
        $("#unreadTask").click(function () {
            $('#calendarq').fullCalendar('refetchEvents');
        });

        $("#unread").click(function () {
            $('#calendarq').fullCalendar('refetchEvents');
        });
        $("#all").click(function () {
            $('#calendarq').fullCalendar('refetchEvents');
        });
        
    });

    
    function changeCase() {
        var selectdValue = $(".case_or_lead option:selected").val() // or
        $('#calendarq').fullCalendar('refetchEvents');
    }
    function getCheckedUser() {
        var array = [];
        $("input[class=event_type]:checked").each(function (i) {
            array.push($(this).val());
        });
        return array;
    }
    function getByUser() {
        var array = [];
        $("input[class=byuser]:checked").each(function (i) {
            array.push($(this).val());
        });
        return array;
    }

    function getSOL() {
        if($(".sol").prop('checked')==false){
            return false;
        }else{
            return true;
        }
    }
    function getMytask() {
        if($(".mytask").prop('checked')==false){
            return false;
        }else{
            return true;
        }
    }
    function getDate(){
        return $("#datepicker").val();
    }

    function resetButton(){
        var t= $("input:checkbox.event_type:checked").length;
            if(t==0){ $("#resetLink").hide(); }else{ $("#resetLink").show();}
    }

    function callWeekend(){
        if($(".showweekend").prop('checked') == true){
            localStorage.setItem('weekends', 'show');
            // var newhiddendays = [];         // show Mon-Fr (hide Sat/Sun)
            // $('#calendarq').fullCalendar('option', 'hiddenDays', newhiddendays);
        }else{
            localStorage.setItem('weekends', 'hide');
            // var newhiddendays = [0, 6];         // show Mon-Fr (hide Sat/Sun)
            // $('#calendarq').fullCalendar('option', 'hiddenDays', newhiddendays);
        }
        console.log(localStorage.getItem('weekends'));
        window.location.reload();
    }
    function markAsRead() {
        $("#preloaderData").show();
        $("#markEventAsRead").modal("show");
        $("#preloaderData").hide();
        // $.ajax({
        //     type: "POST",
        //     url: baseUrl + "/tasks/taskAllReadFromCalender", // json datasource
        //     data: {"task_id": ''},
        //     success: function (res) {
        //         window.location.reload();
        //     }
        // })
    }
    $('#markEventAsReadForm').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#markEventAsReadForm').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#markEventAsReadForm").serialize();
       
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/taskAllReadFromCalender", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                $("#innerLoader").css('display', 'block');
                if (res.errors != '') {
                    $('#showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError').append(errotHtml);
                    $('#showError').show();
                    $("#innerLoader").css('display', 'none');
                    $('#submit').removeAttr("disabled");
                    return false;
                } else {
                    window.location.reload();
                }
            }
        });
    });
    function allTask(){
        $("#all").addClass('active');
        $("#unread").removeClass('active');
        $("#loadType").val("all");
    }
    function allUnread(){
        $("#all").removeClass('active');
        $("#unread").addClass('active');
        $("#loadType").val("unread");
    }
    resetButton();
</script>
@stop
@endsection