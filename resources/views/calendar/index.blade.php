@extends('layouts.master')
@section('title', 'Legalcase - Simplify Your Law Practice | Cloud Based Practice Management')

@section('page-css')
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.css"> --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.6.0/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.6.0/main.css">
<style>
.agenda-table th, .agenda-table td {
    padding: 5px 10px !important;
}
.fc-view.fc-AgendaView-view{
    max-height: 700px !important;
    overflow-y: auto !important;
}
.accordion{
    height: 700px;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 7px;
}
.card .collapse-scroll{
    max-height: 250px;
    overflow-y: auto;
    overflow-x: hidden;
    margin-right: 3px;
}
.agenda-task-incomplete {
    color: #ca4245;
}
</style>    
@endsection
@section('main-content')
<?php
// $timezoneData = unserialize(TIME_ZONE_DATA); 
?>
{{-- @if(!isset($evetData)) --}}
<div class="loadscreen" id="preloaderData" style="display: none;">
    <div class="loader"><img class="logo mb-3" src="{{asset('public/images/logo.png')}}" style="display: none" alt="">
        <div class="loader-bubble loader-bubble-primary d-block"></div>
    </div>
</div>
<div class="loadscreen" id="preloaderAgendaView" style="display: none;">
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
        
        <div class="accordion scrollbar-primary" id="accordionRightIcon">
            <div class="filter-list">
                <div>
                    <div role="group" class="d-flex btn-group">
                        <button type="button" value="all" id="all" class="w-100 btn btn-outline-secondary active" onclick="allTask()">All</button>
                        <button type="button" value="unread" id="unread" class="w-100 btn btn-outline-secondary" onclick="allUnread()">Unread</button>
                    </div>
                </div>
            </div>

            <div class="card ul-card__v-space">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title ul-collapse__icon--size ul-collapse__right-icon mb-0">
                        <a data-toggle="collapse" class="text-default" href="#accordion-item-icon-right-coll-1"
                            aria-expanded="true">Staff</a>
                    </h6>
                </div>
                <div id="accordion-item-icon-right-coll-1" class="collapse show collapse-scroll scrollbar-primary" style="">
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
                                if($v1->id==$authUser->id){?>
                                       
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
                <div id="accordion-item-icon-right-coll-2" class="collapse show collapse-scroll scrollbar-primary">
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

            {{-- <div class="card ul-card__v-space">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title ul-collapse__icon--size ul-collapse__right-icon mb-0">
                        <a data-toggle="collapse" class="text-default collapsed"
                            href="#accordion-item-icon-right-coll-3">Other</a>
                    </h6>
                </div>
                <div id="accordion-item-icon-right-coll-3" class="collapse collapse-scroll scrollbar-primary">
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
            </div> --}}
            <div class="sticky-bottom-filter">
                <div class="filter-list">
                    <div class="form-group row">
                        <div class="col-12 form-group mb-3">
                            <label>Case</label>
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
                <div class="form-groupW">
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
        <div id="calendar-sync-status" class="w-100 justify-content-end d-flex align-items-center p-1">
            <i class="fas fa-check text-success mr-1"></i>
            <button type="button" class="calendar-service font-weight-bold p-1 border-0 btn btn-link" aria-label="calendar sync status">Google Calendar</button>
            <span class="status-text mr-1">&nbsp;-&nbsp;<strong>Status: </strong><span class="sync-status">Up to date</span></span>
        </div>
    </div>
</div>
<input type="hidden" name="loadType" id="loadType" value="all">
</div>
{{-- @else
<div id="event_detail_view_div">
    @include('calendar.event.event_detail')
</div>
@endif --}}

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

<div id="addCaseReminderPopup" class="modal fade bd-example-modal-lg modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static" style="">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Set Statute of Limitations Reminders</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="addCaseReminderPopupArea">

                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>

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
$defaultView="dayGridMonth";
if(isset($_GET['view']) &&  $_GET['view']=='week'){
    $defaultView="timeGridWeek";
}
if(isset($_GET['view']) &&  $_GET['view']=='day'){
    $defaultView="timeGridDay";
}
if(isset($_GET['view']) &&  $_GET['view']=='agenda'){
    $defaultView="agendaview";
}
?>
<style> 
.modal { overflow: auto !important; }
.layout-horizontal-bar .main-content-wrap {margin-top: 104px !important;}
#calendarq {cursor: pointer;}
.user-circle {border-radius: 50%;line-height: 25px;text-align: center;}
/* .fc-time{display: none;} */
.agenda-custom-view { overflow: auto;}
.agenda-custom-view table { width: 100%; }
/* .fc-daygrid-event-dot { display: none; } */
.fc-event-title { font-weight: 500 !important; }
.fc-event { overflow: hidden; }
</style>
@section('page-js')
<script src='https://cdn.jsdelivr.net/npm/moment-timezone@0.5.31/builds/moment-timezone-with-data.min.js'></script>
{{-- <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.js'></script> --}}
{{-- <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/moment@5.5.0/main.global.min.js'></script> --}}
{{-- <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment-with-locales.min.js'></script> --}}
{{-- <script src='https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.6/moment-timezone-with-data.js'></script> --}}

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.6.0/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.6.0/main.min.js"></script>

<script src="{{ asset('assets\js\custom\calendar\addevent.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script src="{{ asset('assets\js\custom\calendar\viewevent.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script src="{{ asset('assets\js\custom\feedback.js?').env('CACHE_BUSTER_VERSION') }}"></script>

<script type="text/javascript">
var calendar; var userTimezone = 'local';
// var authUserTimezone = "{{ $authUser->user_timezone }}";
var authUserTimezone = "{{ $userOffset }}";
$(document).ready(function () {
    
    var localTimezone = getTimeZone();
    if(localTimezone == authUserTimezone) {
        calendarTimezone = 'local';
    } else {
        calendarTimezone = "{{ $authUser->user_timezone }}";
    }

    const { sliceEvents, createPlugin, Calendar } = FullCalendar
    const CustomViewConfig = {
        classNames: [ 'agenda-custom-view' ],
        duration: { days: 31 },
        visibleRange: function(currentDate) {
            return {
            start: currentDate.clone(),
            end: currentDate.clone().add(31, 'days') // exclusive end, so 3
            };
        },
        content: function(props) {
            // var viewData = '';
            $("#preloaderAgendaView").show();
            var eventTypes = [];
            $.each($("input[name='event_type[]']:checked"), function(){
                eventTypes.push($(this).val());
            });
            var byuser = getByUser();
            var bysol=getSOL();
            var mytask=getMytask();
            $.ajax({
                url: 'loadEventCalendar/loadAgendaView',
                type: 'POST',
                data: {
                    start: props.dateProfile.currentRange.start.toUTCString(),
                    end: props.dateProfile.currentRange.end.toUTCString(),
                    event_type: JSON.stringify(eventTypes),
                    byuser: JSON.stringify(byuser),
                    case_id: $(".case_or_lead option:selected").val(),
                    searchbysol:bysol,
                    searchbymytask:mytask,
                    dateFilter:getDate(),
                    taskLoad:$("#loadType").val()
                },
                success: function (doc) {
                    // viewData = doc;
                    $('.agenda-custom-view').html(doc);
                },
                complete:function(data){
                    $("#preloaderAgendaView").hide();
                }
            });
            // return {html: viewData};
        }
    }
    const CustomViewPlugin = createPlugin({
        views: {
            agendaview: CustomViewConfig
        }
    });

    var calendarEl = document.getElementById('calendarq');
    calendar = new FullCalendar.Calendar(calendarEl, {
        // schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
        timeZone: calendarTimezone,
        plugins: [ CustomViewPlugin, ],
        initialView: "{{ $defaultView }}",
        // initialDate: new Date,
        initialDate: "{{ \Carbon\Carbon::now((!(empty($authUser->user_timezone))) ? $authUser->user_timezone : 'UTC')->format('Y-m-d') }}",
        themeSystem: "bootstrap4",
        height: 700,
        dayMaxEvents: true, // allow "more" link when too many events
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'staffView,timeGridDay,timeGridWeek,dayGridMonth,agendaview'
        },
        buttonText:{
            month:    'Month',
            week:     'Week',
            day:      'Day',
            today : 'Today',
        },
        customButtons: {
            agendaview: {
                text: 'Agenda',
                click: function () {
                    calendar.changeView('agendaview');
                }
            },
            staffView: {
                text: 'Staff',
                click: function () {
                    calendar.changeView('staffView');
                    calendar.refetchResources();
                    calendar.refetchEvents();
                }
            },                
            timeGridDay: {
                text: 'Day',
                click: function () {
                    calendar.changeView('timeGridDay');
                    calendar.refetchEvents();
                }
            },                
        },
        views: {
            /* agendaview: {
                type: 'agendaview',
            }, */
            staffView: {
                type: 'resourceTimeGridDay',
            },    
        },
        // displayEventTime: false,
        eventTimeFormat: { // like '14:30:00'
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        },
        resources: function(info, successCallback, failureCallback) {
            var byuser = getByUser();
            $.ajax({
                url: 'loadEventCalendar/loadStaffView',
                type: 'POST',
                data: {resType: "resources", byuser: byuser, view_name: 'staff'},
                success: function (doc) {
                    successCallback(doc);
                }
            });
        },
        events: function(info, successCallback, failureCallback) {
            $("#preloaderData").show();
            var startDate = info.start;
            var endDate = info.end;
            var eventTypes = [];
            $.each($("input[name='event_type[]']:checked"), function(){
                eventTypes.push($(this).val());
            });
            var byuser = getByUser();
            var bysol=getSOL();
            var mytask=getMytask();
            $.ajax({
                url: "{{ route('newloadevents') }}",
                data: {
                    // our hypothetical feed requires UNIX timestamps
                    // start: info.start.valueOf(),
                    // end: info.end.valueOf(),
                    start: moment(startDate).format('YYYY-MM-DD'),
                    end: moment(endDate).format('YYYY-MM-DD'),
                    event_type: JSON.stringify(eventTypes),
                    byuser: JSON.stringify(byuser),
                    case_id: $(".case_or_lead option:selected").val(),
                    searchbysol:bysol,
                    searchbymytask:mytask,
                    dateFilter:getDate(),
                    taskLoad:$("#loadType").val(),
                    // timezone: userTimezone,
                    timezoneOffset: calendarTimezone
                },
                success: function(data) {
                    successCallback(data);
                    $("#preloaderData").hide();
                }
            });
        },
        eventDidMount: function(info) {
            info.el.querySelectorAll('.fc-event-title')[0].innerHTML = info.event.title;
            $(info.el).tooltip({
                title: info.event.extendedProps.tTitle,
            });
        },
        viewDidMount: function(arg){
            var view = arg.view;
            if (view.type == 'dayGridMonth' || view.type == 'timeGridWeek' || view.type == 'timeGridDay') {
            }else{
                var currentdate = view.activeStart;
                var endDate = view.activeEnd;
                // $('#datepicker').datepicker().datepicker('setDate', new Date(currentdate));
                // date = moment(currentdate).format('YYYY-MM-DD');
            }
            
            if(localStorage.getItem('weekends')=='hide'){
                var chk="";
            }else{
                var chk="checked=checked";
            }

            $("#addevbutton").remove();
            @can('event_add_edit')
            $(' <span id="addevbutton">\
                <a data-toggle="modal" data-target="#loadAddEventPopup" data-placement="bottom" href="javascript:;"> \
                    <button class="btn btn-primary btn-rounded m-0" type="button" onclick="loadAddEventPopup(null, '+"'events'"+');">Add Event</button>\
                </a></span>').insertAfter(".fc-agendaview-button"); 
            $( "#dp" ).insertAfter( ".fc-next-button" );
            @endcan
            
            $("#settingicon").remove();
            $('<span id="settingicon"> <button class="btn btn-secondry dropdown-toggle" id="shuesuid" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i aria-hidden="true" class="fas fa-cog icon"></i> </button><div class="dropdown-menu bg-transparent shadow-none p-0 m-0" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 34px, 0px); top: 0px; left: 0px; will-change: transform "><div class="card"><div tabindex="-1" role="menu" aria-hidden="false" class="dropdown-menu dropdown-menu-right show" x-placement="top-end"> <b class="ml-2">Actions:</b> <button type="button" tabindex="0" role="menuitem" class="dropdown-item mb-1" onclick="markAsRead()">Mark All As Read</button>  <b class="ml-2">Settings:</b><a href="'+baseUrl+'/locations" tabindex="0" role="menuitem" class="dropdown-item">Locations</a> <b class="ml-2">Format Options:</b><label class="checkbox checkbox-outline-primary ml-2"><input type="checkbox" class="showweekend ml-2" '+chk+' value="1" onclick="callWeekend()"><span>Show Weekends</span><span class="checkmark"></span></label><span></span></button> </div> </div> </div> </span>').insertAfter(".fc-agendaview-button"); 

            $("#printicon").remove();
            $('<span id="printicon"><a href="{{ route("print_events") }}" class="btn btn-link"><i class="fas fa-print text-black-50" data-toggle="tooltip" data-placement="top"title="" data-original-title="Print"></i></a></span>').insertAfter(".fc-agendaview-button"); 
        
            $("#shuesuid").trigger('click');
            $('[data-toggle="tooltip"]').tooltip();
        },
        eventClick: function(info) {
            var event = info.event;
            if(event.extendedProps.mytask=="yes"){
                var redirectURL=baseUrl+'/tasks?id='+event.id;
                window.location.href=redirectURL;
            }else if(event.extendedProps.mysol=="yes"){
                var redirectURL=baseUrl+'/court_cases/'+event.extendedProps.case_id+'/info';
                window.location.href=redirectURL;
            }else {
                loadEventComment(event.extendedProps.event_id, event.id, 'events');
            }
        },
        dateClick: function(info) {
            @can('event_add_edit')
            loadAddEventPopup(info.dateStr, 'events');
            @endcan
        }
    });
    calendar.render();

    calendarEl.querySelector('.fc-today-button').addEventListener('click', function() {
        $('#datepicker').datepicker('setDate', new Date());
    });

    $('body').on('click', '.fc-prev-button, .fc-next-button', function() {
    // calendarEl.querySelector('.fc-prev-button, .fc-next-button').addEventListener('click', function() {
        var view = calendar.view;
        if (view.type == 'dayGridMonth') {
            var date = calendar.getDate();
            $('#datepicker').datepicker().datepicker('setDate', new Date(date));
        }else{
            var currentdate = view.activeStart;
            $('#datepicker').datepicker().datepicker('setDate', new Date(currentdate));
        }
    });

        $("#datepicker").datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        }).on('changeDate', function(ev) {
            var date = new Date(ev.date);
            calendar.gotoDate( date );
        });

        $('#deleteFromCommentBox').on('hidden.bs.modal', function () {
            // $('#calendarq').fullCalendar('refetchEvents');
            calendar.refetchEvents();
        });

        $("input:checkbox.event_type").click(function () {
            // $('#calendarq').fullCalendar('refetchEvents');
            calendar.refetchEvents();
            resetButton();
        });
        $("input:checkbox.byuser").click(function () {
            // $('#calendarq').fullCalendar( 'refetchResources' );
            calendar.refetchResources();
            // $('#calendarq').fullCalendar('refetchEvents');
            calendar.refetchEvents();
        });
        $("input:checkbox.sol").click(function () {
            // $('#calendarq').fullCalendar('refetchEvents');
            calendar.refetchEvents();
        });
        $("input:checkbox.mytask").click(function () {
            // $('#calendarq').fullCalendar('refetchEvents');
            calendar.refetchEvents();
        });
        
        $('#checkalltype').on('change', function () {
            $('.event_type').prop('checked', $(this).prop("checked"));
            // $('#calendarq').fullCalendar('refetchEvents');
            calendar.refetchEvents();
            resetButton();
        });
        $('#case_or_lead').on('change', function () {
            $('.event_type').prop('checked', $(this).prop("checked"));
            var SU = getCheckedUser();
        });
        $("#unreadTask").click(function () {
            // $('#calendarq').fullCalendar('refetchEvents');
            calendar.refetchEvents();
        });

        $("#unread").click(function () {
            // $('#calendarq').fullCalendar('refetchEvents');
            calendar.refetchEvents();
        });
        $("#all").click(function () {
            // $('#calendarq').fullCalendar('refetchEvents');
            calendar.refetchEvents();
        });
        
    });

    
    function changeCase() {
        var selectdValue = $(".case_or_lead option:selected").val() // or
        // $('#calendarq').fullCalendar('refetchEvents');
        calendar.refetchEvents();
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
    /* function loadReminderPopupIndex(evnt_id) {
        $("#reminderDataIndex").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadReminderPopupIndex", // json datasource
                data: {
                    "evnt_id": evnt_id
                },
                success: function (res) {
                    $("#reminderDataIndex").html('Loading...');
                    $("#reminderDataIndex").html(res);
                    $("#preloader").hide();
                 
                }
            })
        })
    } */
    // Made common code, check addevent.js file
    /* function loadEventComment(evnt_id) {
        $("#loadCommentPopup").modal('show');
        $("#eventCommentPopup").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadCommentPopupFromCalendar", // json datasource
                data: {
                    "evnt_id": evnt_id
                },
                success: function (res) {
                    $("#eventCommentPopup").html('Loading...');
                    $("#eventCommentPopup").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } */
    // Made common code , check addevent.js file
    /* function loadAddEventPopup() {
        $("#AddEventPage").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadAddEventPageFromCalendar", // json datasource
                data: {
                   
                },
                success: function (res) {
                    $("#AddEventPage").html('Loading...');
                    $("#AddEventPage").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } */
    // Made common code, This code is not in use
    /* function loadAddEventPopupFromCalendar(selectedate) {
        $("#loadAddEventPopupFromCalendar").modal("show");
        $("#AddEventPageFromCalendar").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadAddEventPageSpecificaDate", // json datasource
                data: {
                    selectedate:selectedate
                },
                success: function (res) {
                    $("#AddEventPageFromCalendar").html('Loading...');
                    $("#AddEventPageFromCalendar").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } */
    // Made common code. check addevent.js file
    /* function editEventFunction(evnt_id) {
        $("#preloader").show();
        $("#loadEditEventPopup .modal-dialog").addClass("modal-xl");
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadEditEventPageFromCalendarView", // json datasource
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
    // This code not in use
    /* function editSingleEventFunction(evnt_id) {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadSingleEditEventPageFromCalendar", // json datasource
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
    // Not in use
    /* function deleteEventFunction(id,types) {
      
      if(types=='single'){
          $("#deleteSingle").text('Delete Event');
      }else{
        $("#deleteSingle").text('Delete Recurring Event');
      }
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/deleteEventPopup", 
                data: {
                    "event_id": id
                },
                success: function (res) {
                    $("#eventID").html('');
                    $("#eventID").html(res);
                    $("#preloader").hide();
                }
            })
        })
    } */

    // SOL reminders
    function addCaseReminder(case_id) {
        $("#reminderDataIndexInView").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/addCaseReminderPopup", // json datasource
                data: {"case_id": case_id},
                success: function (res) {
                    $("#addCaseReminderPopupArea").html(res);
                }
            })
        })
    }

    function resetButton(){
        var t= $("input:checkbox.event_type:checked").length;
            if(t==0){ $("#resetLink").hide(); }else{ $("#resetLink").show();}
    }

    function callWeekend(){
        if($(".showweekend").prop('checked') == true){
            localStorage.setItem('weekends', 'show');
            calendar.setOption('hiddenDays', []);
        }else{
            localStorage.setItem('weekends', 'hide');
            calendar.setOption('hiddenDays', [0, 6]);
        }
        calendar.setOption('weekends', localStorage.getItem('weekends'));
        calendar.refetchEvents();
    }
    function markAsRead() {
        $("#preloaderData").show();
        $("#markEventAsRead").modal("show");
        $("#preloaderData").hide();
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
            type: "GET",
            url: baseUrl + "/events/mark/read", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&is_all_event=yes';
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
                    // window.location.reload();
                    $("#markEventAsRead").modal("hide");
                    $("#innerLoader").css('display', 'none');
                    $('#submit').removeAttr("disabled");
                    $(".eventCount").html(res.unreadEventCount);
                    calendar.refetchEvents();
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

    // Get user timezone offset
    function getTimeZone() {
        var offset = new Date().getTimezoneOffset(), o = Math.abs(offset);
        return (offset < 0 ? "+" : "-") + ("00" + Math.floor(o / 60)).slice(-2) + ":" + ("00" + (o % 60)).slice(-2);
    }
</script>
@stop
@endsection
