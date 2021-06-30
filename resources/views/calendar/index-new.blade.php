@extends('layouts.master')
@section('title', 'Legalcase - Simplify Your Law Practice | Cloud Based Practice Management')

@section('page-css')
<link rel="stylesheet" href="{{asset('assets/plugins/fullcalendar-5.8.0/lib/main.min.css')}}">
<style>
.agenda-table th, .agenda-table td {
    padding: 5px 10px !important;
}
.fc-view.fc-CustomView-view{
max-height: 500px !important;
    overflow-y: auto !important;
}
.fc-day-grid-event > .fc-content {
    white-space: nowrap;
    overflow: hidden;
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
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card ul-card__v-space">
                <div class="card-header header-elements-inline">
                    
                    <h6 class="card-title ul-collapse__icon--size ul-collapse__right-icon mb-0">
                        <a data-toggle="collapse" class="text-default" href="#accordion-item-icon-right-coll-2"
                            aria-expanded="true">Event Type</a>
                            <span class="ml-1 calendar-help-bubble" style="cursor: pointer;">
                                <i id="help-bubble-1" aria-hidden="true" class="fa fa-question-circle icon-question-circle icon text-primary"  tabindex="0" role="button" href="javascript:;" data-toggle="popover" data-trigger="hover"  title="Event Types" data-content='<div><p><b>No Event Types Checked:</b>&nbsp;All events will show whether they have an Event Type or not.</p><p><b>Select All Checked:</b> Only events that have an Event Type will show.</p><p><b>Specific Event Type Checked:</b>&nbsp;Only events that have the selected Event Types will show. Events with no event types will not show.</p></div>' data-html="true" data-original-title="" style="float:revert;"></i>
                    </h6>
                   
                </div>
                <div id="accordion-item-icon-right-coll-2" class="collapse show">
                    <div class="collapse show">
                        <div class="py-3 pl-1">
                            <label class="checkbox checkbox-outline-primary">
                                <input type="checkbox" id="checkalltype" value="0">
                                <span>Select All</span><span class="checkmark"></span>
                            </label>
                            <div class="cp-checkbox-container mt-1 ml-2">
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
                                <i id="help-bubble-1" aria-hidden="true" class="fa fa-question-circle icon-question-circle icon text-primary"  tabindex="0" role="button" href="javascript:;" data-toggle="popover" data-trigger="hover"  title="New task icons" data-content='<div style="Width:150px; class="popover-inner w-100" role="tooltip"><h3 class="popover-header"></h3><div class="popover-body"><div><div class="line-help-bubble"><span class="calendar-badge d-inline-block undefined badge badge-secondary" style="background-color: rgb(202, 66, 69);float:left; width: 30px;"><div>DUE</div></span><div style="float:left;margin-left: 5px;padding-left:3px;">High priority</div></div><br><br><div class="line-help-bubble"><span class="calendar-badge d-inline-block undefined badge badge-secondary" style="background-color: rgb(254, 193, 8); width: 30px;float:left;"><div>DUE</div></span><div style="margin-left: 5px;padding-right:3px;float:left;"> Medium priority</div></div><br><br><div class="line-help-bubble"><span class="calendar-badge d-inline-block undefined badge badge-secondary" style="background-color: rgb(40, 167, 68); width: 30px;float:left;"><div>DUE</div></span><div style="margin-left: 5px;float:left;;padding-left:3px;">Low/No priority</div></div><br></div></div></div>' data-html="true" data-original-title="" style="float:revert;"></i>
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
                            <select onChange="changeCaseUser()" class="form-control case_or_lead" id="case_or_lead"
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
                <button class="w-100 btn btn-outline-secondary m-1" type="button">Tell us what you think</button>
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

<div id="loadAddEventPopup" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
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
</div>
<div id="loadAddEventPopupFromCalendar" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
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
</div>
<div id="deleteEvent" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
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
</div>

<div id="loadEditEventPopup" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="EditEventPage">
        </div>
    </div>
</div>

<div id="loadCommentPopup" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="eventCommentPopup">

        </div>
    </div>
</div>

<div id="loadReminderPopup" class="modal fade bd-example-modal-lg modal-overlay" tabindex="-1" role="dialog"
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

@if(!isset($evetData))
<span id="dp"><input type="text" class="form-control" style="width: 100px;margin-right: 1px;height: 28px;" name="datefilter" value="{{date('m/d/Y')}}" id="datepicker"></span>
@endif
<?php 
$defaultView="month";
if(isset($_GET['view']) && $_GET['view']=='day'){
    $defaultView="agendaDay";

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
<script src="{{asset('assets/plugins/fullcalendar-5.8.0/lib/main.min.js')}}"></script>
<script src="{{ asset('assets\js\custom\calendar\viewevent.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script type="text/javascript">

$(document).ready(function() {
    // Custom agenda view
    const { sliceEvents, createPlugin, Calendar } = FullCalendar
    const CustomViewConfig = {
        classNames: [ 'custom-view' ],
        content: function(props) {
        let segs = sliceEvents(props, true); // allDay=true
        let html =
            '<div class="view-title">' +
            props.dateProfile.currentRange.start.toUTCString() +
            '</div>' +
            '<div class="view-events">' +
            segs.length + ' events:' +
            '<ul>' +
                segs.map(function(seg) {
                return seg.def.title + ' (' + seg.range.start.toUTCString() + ')'
                }).join('') +
            '</ul>' +
            '</div>'

        return { html: html }
        }
    }
    const CustomViewPlugin = createPlugin({
        views: {
        custom: CustomViewConfig
        }
    });
    // calendar.render();


    var calendarEl = document.getElementById('calendarq');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        themeSystem: 'bootstrap',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth,custom' // buttons for switching between views
        },
        views: {
            dayGridMonth: { // name of view
                displayEventEnd: true,
            },
            timeGridDay: {
                dayHeaders: false,
                titleFormat: { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' }
            },
            agendaView: {
                type: 'custom',
                buttonText: 'Agenda',
                duration: { days: 30 },
                click:  $('#calendarq').fullCalendar('changeView', 'AgendaView', {
                    start: moment().clone().subtract(1, 'days'),
                    end: moment().clone().add(30, 'days') // exclusive end, so 3
                })
            },
        },
        dayMaxEvents: true, // allow "more" link when too many events
        displayEventTime: true, 
        events: function(info, successCallback, failureCallback) {
            var eventTypes = getCheckedUser();
            var byuser = getByUser();
            var bysol=getSOL();
            var mytask=getMytask();
            $.ajax({
                url: 'loadEventCalendar/load',
                type: 'POST',
                dataType: 'json',
                data: {
                    start: moment(info.start.valueOf()).format('YYYY-MM-DD'),
                    end: moment(info.end.valueOf()).format('YYYY-MM-DD'),
                    event_type: JSON.stringify(eventTypes),
                    byuser: JSON.stringify(byuser),
                    selectdValue: $(".case_or_lead option:selected").val(),
                    searchbysol:bysol,
                    searchbymytask:mytask,
                    dateFilter:getDate(),
                    taskLoad:$("#loadType").val()
                },
                success: function (doc) {
                    var events = [];
                    if (!!doc.result) {
                        $.map(doc.result, function (r) {
                            
                            if (r.event_title == null) {
                                var tTitle = "<No Title>";
                            } else {
                                var tTitle = r.event_title
                            }
                            // if(r.etext!=''){
                            //     var color= r.etext.color_code
                            // }else{
                            //     var color= r.colorcode
                            // }
                            var color="#00cfd2"
                            if (r.event_title == null) {
                                var t = '<div class="user-circle mr-1 d-inline-block" style="width: 10px; height: 10px; background-color: '+color+';"></div>'+r.start_time_user +
                                    ' -' + "<No Title>";
                            } else {
                                var t = '<div class="user-circle mr-1 d-inline-block" style="width: 10px; height: 10px; background-color: '+color+';"></div>'+r.start_time_user +
                                    ' -' + r.event_title
                            }
                            var t = r.event_title;
                            events.push({
                                id: r.id,
                                title: t,
                                tTitle: tTitle,
                                start: r.start_date+'T'+r.st,
                                end: r.end_date+'T'+r.et,
                                backgroundColor: '#d5e9ce',  
                                textColor:'#000000',
                                color: color,
                                eventOverlap: false,
                                slotEventOverlap: false,
                            });
                        });
                    }
                    if (!!doc.sol_result) {
                        $.map(doc.sol_result, function (r) {
                            var t = '<span class="calendar-badge d-inline-block undefined badge badge-secondary" style="background-color: rgb(92, 92, 92); width: 30px;">SOL</span>'+' ' + r.event_title
                            var tplain = 'SOL'+' -' + r.event_title
                            events.push({
                                mysol:'yes',
                                case_id:r.case_unique_number,
                                id: r.id,
                                title: t,
                                tTitle: tplain,
                                start: r.start_date+'T'+r.st,
                                end: r.end_date+'T'+r.et,
                                textColor:'#000000',
                                backgroundColor: 'rgb(236, 238, 239)',
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
                                
                            });
                        });
                    }
                    successCallback(events);
                },
            });
        },
        eventContent: function(arg) {
            return {
                html: arg.event.title
            }
        },
        eventTimeFormat: { // like '14:30:00'
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },
        eventClick: function(info) {
            // console.log(info.event.extendedProps.mytask);
            if(info.event.extendedProps.mytask=="yes"){
                var redirectURL=baseUrl+'/tasks?id='+info.event.id;
                window.location.href=redirectURL;
            }else if(info.event.extendedProps.mysol=="yes"){
                var redirectURL=baseUrl+'/court_cases/'+info.event.case_id+'/info';
                window.location.href=redirectURL;
            }else {
                loadEventComment(info.event.id);
            }
        },
        dateClick: function(date, allDay, jsEvent, view) {
            loadAddEventPopupFromCalendar(date.format());
        },
        
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


    $('#loadCommentPopup,#loadEditEventPopup,#loadCommentPopup,#deleteFromCommentBox,#loadAddEventPopupFromCalendar,#loadEditEventPopup').on('hidden.bs.modal', function () {
        calendar.refetchEvents();
    });

    $("input:checkbox.event_type").click(function () {
        $('#calendarq').fullCalendar('refetchEvents');
        resetButton();
    });
    $("input:checkbox.byuser").click(function () {
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


function changeCaseUser() {
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
resetButton();

function loadReminderPopup(evnt_id) {
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
}
function loadReminderPopupIndex(evnt_id) {
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
}
function loadEventComment(evnt_id) {
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
}
function loadAddEventPopup() {
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
}
function loadAddEventPopupFromCalendar(selectedate) {
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
}
function editEventFunction(evnt_id) {
    $("#preloader").show();
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
}
function editSingleEventFunction(evnt_id) {
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
}
function deleteEventFunction(id,types) {
    
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
}
</script>
@stop
@endsection