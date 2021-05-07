@extends('layouts.master')
@section('title', 'Legalcase - Simplify Your Law Practice | Cloud Based Practice Management')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 
?>
<div class="loadscreen" id="preloaderData" style="display: block;">
    <div class="loader"><img class="logo mb-3" src="{{asset('public/images/logo.png')}}" style="display: none" alt="">
        <div class="loader-bubble loader-bubble-primary d-block"></div>
    </div>
</div>

<div class="row">
    <div class="col-2">
    </div>
    <div class="col-8  pt-1" >
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
            <button class="btn btn-secondry dropdown-toggle" id="shuesuid" type="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i aria-hidden="true" class="fas fa-cog icon"></i>
            </button>
            <div class="dropdown-menu bg-transparent shadow-none p-0 m-0" x-placement="bottom-start"
                style="position: absolute; transform: translate3d(0px, 34px, 0px); top: 0px; left: 0px; will-change: transform;">
                <div class="card">
                    <div tabindex="-1" role="menu" aria-hidden="false" class="dropdown-menu dropdown-menu-right show"
                        x-placement="top-end">
                        <b class="ml-2">Actions:</b>
                        <button type="button" tabindex="0" role="menuitem" class="dropdown-item">Mark All As
                            Read</button>
                        <br>
                        <b class="ml-2">Settings:</b>
                        <a href="/locations" tabindex="0" role="menuitem" class="dropdown-item">Locations</a>
                        <br>
                        <b class="ml-2">Format Options:</b>
                        <button type="button" tabindex="0" role="menuitem"
                            class="show-weekend-dropdown-item pl-4 dropdown-item">
                            <input readonly="" type="checkbox" class="show-weekend-input-box form-check-input"
                                checked="" style="height: 16px; margin-top: 1px; width: 16px;">
                            <span>Show Weekends</span>
                        </button>
                    </div>
                </div>
            </div>

            <a data-toggle="modal" data-target="#loadAddEventPopup" data-placement="bottom" href="javascript:;"> <button
                    class="btn btn-primary btn-rounded m-1" type="button" onclick="loadAddEventPopup();">Add
                    Event</button></a>
        </div>
    </div>
</div>
<br>
{{-- <div class="separator-breadcrumb border-top"></div> --}}
<div class="row">
    <div class="col-md-2 pt-3">
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
                                        <input type="checkbox" checked="checked" class="byuser"
                                            value="{{$v1->id}}"><span>My Calendar</span><span class="checkmark"></span>
                                    </label>
                                <?php }else{ ?>
                                        
                                    <label class="checkbox checkbox-outline-staff-{{$k1}}">
                                        <input type="checkbox"  class="byuser"
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
                        <a data-toggle="collapse" class="text-default collapsed"
                            href="#accordion-item-icon-right-coll-2">Event Type</a>
                    </h6>
                </div>
                <div id="accordion-item-icon-right-coll-2" class="collapse show">
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
                                <input type="checkbox" class="event_type"
                                    value="{{$v->id}}"><span>{{$v->title}}</span><span class="checkmark"></span>
                            </label>
                            <?php } ?>

                        </div>
                        <button type="button" class="edit-event-types-calendar-picker btn btn-link"><i
                                aria-hidden="true" class="fa fa-plus icon-plus icon"></i><span
                                class="ml-2">Customize</span></button>
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
                            <input type="checkbox" class="mytask" value="mytask" checked="checked"><span>My Tasks</span><span
                                class="checkmark"></span><span class="ml-1 calendar-help-bubble"
                                style="cursor: pointer;"><i id="help-bubble-1" aria-hidden="true"
                                    class="fa fa-question-circle icon-question-circle icon text-primary"></i></span>
                        </label>

                    </div>
                </div>
            </div>
            <div class="sticky-bottom-filter">
                <div class="filter-list">
                    <div>
                        <div role="group" class="d-flex btn-group">
                            <button type="button" value="all"
                                class="w-100 btn btn-outline-secondary active">All</button>
                            <button type="button" value="unread" class="w-100 btn btn-outline-secondary">Unread</button>
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
            <div id="calendarq"></div>
        </div>
    </div>
</div>

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
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.3.1/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.3.1/main.min.js'></script>
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
<style> .modal { overflow: auto !important; }</style>

<style>
    .layout-horizontal-bar .main-content-wrap {

        margin-top: 104px !important;
    }
    #calendarq {
    cursor: pointer;
}
</style>
@section('page-js')
<script type="text/javascript">
    $(document).ready(function () {
        $(document).ready(function () {
            $("#preloaderData").show();

            /* initialize the external events
                    -----------------------------------------------------------------*/


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

            $('#calendarq').fullCalendar({
                
                customButtons: {
                    myCustomButton: {
                        text: 'Add Event ',
                        click: function () {
                            loadAddTaskPopup();
                        }
                    },
                    //...other buttons
                },
                header: {
                    left: 'prev,next today', //myCustomButton
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
               

                defaultView: '{{$defaultView}}',
                themeSystem: "bootstrap4",
                droppable: false,
                title: true,
                editable: true,
                eventLimit: true, // allow "more" link when too many events
                drop: function () {
                    // is the "remove after drop" checkbox checked?
                    if ($('#drop-remove').is(':checked')) {
                        // if so, remove the element from the "Draggable Events" list
                        $(this).remove();
                    }
                },
                loading: function (bool) {
                    $("#preloaderData").show();
                },
                events: function (start, end, timezone, callback) {
                    var eventTypes = getCheckedUser();
                    var byuser = getByUser();
                    var bysol=getSOL();
                    $.ajax({
                        url: 'loadEventCalendar/load',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            start: start.format(),
                            end: end.format(),
                            event_type: JSON.stringify(eventTypes),
                            byuser: JSON.stringify(byuser),
                            selectdValue: $(".case_or_lead option:selected").val(),
                            searchbysol:bysol

                        },
                        success: function (doc) {
                            console.log(doc.result);
                            var events = [];
                            if (!!doc.result) {
                                $.map(doc.result, function (r) {
                                    if (r.event_title == null) {
                                        var t = r.start_time_user +
                                            ' -' + "<No Title>";
                                    } else {
                                        var t = r.start_time_user +
                                            ' -' + r.event_title
                                    }
                                    if (r.event_title == null) {
                                        var tTitle = "<No Title>";
                                    } else {
                                        var tTitle = r.event_title
                                    }
                                    if(r.etext!=''){
                                        var color= r.etext.color_code
                                    }else{
                                        var color= r.colorcode
                                    }
                                    events.push({
                                        id: r.id,
                                        title: t,
                                        tTitle: tTitle,
                                        start: r.start_date,
                                        end: r.end_date,
                                        color: color
                                    });
                                });

                            }
                            if (!!doc.sol_result) {
                                $.map(doc.sol_result, function (r) {
                                    var t = '<span class="calendar-badge d-inline-block undefined badge badge-secondary" style="background-color: rgb(92, 92, 92); width: 30px;">SOL</span>'+' ' + r.event_title
                                    var tplain = 'SOL'+' -' + r.event_title
                                    events.push({
                                        id: r.id,
                                        title: t,
                                        tTitle: tplain,
                                        start: r.start_date,
                                        end: r.end_date,
                                        color:'#b7b3b3'
                                    });
                                });
                            }
                            callback(events);

                        },

                    });
                },
                eventRender: function (event, element) {
                    $(element).tooltip({
                        title: event.tTitle
                    });
                    element.find('.fc-title').html(event.title);
                },
                eventAfterAllRender: function (view) {
                    $("#preloaderData").hide();
                },
                eventClick: function(event) {
                    loadEventComment(event.id);
                },
                dayClick: function(date, jsEvent, view) {
                    loadAddEventPopupFromCalendar(date.format());
                }

            });

            jQuery(".js-form-add-event").on("submit", function (e) {
                e.preventDefault();

                var data = $('#newEvent').val();
                $('#newEvent').val('');
                $('#external-events').prepend(
                    '<li class="list-group-item bg-success fc-event">' + data + '</li>');

                initEvent();
            });

        });
        $("#shuesuid").trigger('click');

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
        
        $('#checkalltype').on('change', function () {
            $('.event_type').prop('checked', $(this).prop("checked"));
            $('#calendarq').fullCalendar('refetchEvents');
            resetButton();
        });

        $('#case_or_lead').on('change', function () {
            $('.event_type').prop('checked', $(this).prop("checked"));
            var SU = getCheckedUser();
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
    function resetButton(){
        var t= $("input:checkbox.event_type:checked").length;
            if(t==0){ $("#resetLink").hide(); }else{ $("#resetLink").show();}
    }
    resetButton();
</script>
@stop
@endsection
