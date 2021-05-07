@extends('layouts.master')
@section('title', 'Timesheet Calendar- Billing')
@section('main-content')

@include('billing.submenu')
<div class="separator-breadcrumb border-top"></div>
<div class="row">

    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex flex-row align-items-center justify-content-between">
                    <h3 class="my-0 font-weight-bold">Timesheet Calendar</h3>
                    <div class="d-flex flex-row align-items-center">
                        <a class="mr-2" href="/time_tracking">Find more billable time with the Smart Time Finder</a>
                        <button type="button" class="btn btn-secondary">Tell us what you think!</button>
                    </div>
                </div>
                <br>
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                <div class="d-flex flex-row align-items-center justify-content-between">
                    <div class="col-md-3 form-group mb-3">
                        <label for="picker1">Firm User</label>
                      
                        <select class="form-control staff_user dropdownSelect2" id="staff_user_main_form" name="staff_user" onchange="changeStaffUser();">
                            <option value="0">All</option>
                             <?php foreach($loadFirmStaff as $loadFirmStaffkey=>$CasevloadFirmStaffvalal){ ?>
                            <option <?php if($CasevloadFirmStaffvalal->id==Auth::User()->id){ echo "selected=selected"; } ?> value="{{$CasevloadFirmStaffvalal->id}}">{{$CasevloadFirmStaffvalal->first_name}}
                                {{$CasevloadFirmStaffvalal->last_name}}  <?php if($CasevloadFirmStaffvalal->user_title){ echo "(".$CasevloadFirmStaffvalal->user_title.")"; } ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="d-flex flex-row align-items-center">
                      
                        <div class="d-flex flex-row align-items-center">
                            <input type="hidden" value="" id="currentBox">
                            <div role="group" class="view-btn-group mb-2 mr-auto btn-group">
                                <button type="button" class="btn btn-outline-secondary btn-sm hamburger"
                                    onclick="loadCalender('b');">Billable</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm hamburger"
                                    onclick="loadCalender('nb');">Non-Billable</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm hamburger active"
                                    onclick="loadCalender('all');">All</button>
                            </div>
                        </div>
                    </div>
                </div>
                
            </form>
                <div class="master-timesheet-calendar" id="loadCalender">

                    <div id="calendarq" class="p-3"></div>
                </div>
                <div class="d-flex flex-row w-100 p-3" id="loadSummary">
                    <div>
                        <div class="mr-auto">Billable Total: <strong class="test-billable-total">$500.00</strong></div>
                        <div class="text-muted">* Billable includes flat fee time entries</div>
                    </div>
                    <div class="ml-auto text-right">
                        <div>
                            <div class="d-flex">
                                <div class="undefined d-flex flex-row">
                                    <div class="mr-2"><span class="badge badge-success null">NEW</span></div>
                                    <div></div>
                                </div><span class="user-billing-target mr-1">Billing Target:</span><strong>12 hrs/
                                    day</strong>
                            </div><button type="button" id="link-target-22089933" class="p-0 btn btn-link">Edit
                                goal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modals -->
<div id="loadAllTimeEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timeEntryTitle"></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="loadAllTimeEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="loadTimeEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="addTimeEntry">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="loadEditTimeEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="loadEditTimeEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="deleteTimeEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteTimeEntryForm" id="deleteTimeEntryForm" name="deleteTimeEntryForm" method="POST">
            @csrf
            <input type="hidden" value="" name="entry_id" id="delete_entry_id">
            <input type="hidden" value="timesheet" name="from" id="from">
            
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Time Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this time entry?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                            style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<style>
    .fc-title {
        font-size: 15px;
        color: #0070c0 !important;
        font-weight: bold;
    }

    .fc-time-grid-container {
        display: none !important;
    }
    #calendarq {
    cursor: pointer;
}
</style>
@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        $("#staff_user_main_form").select2({
            placeholder: "Select...",
            theme: "classic"
        });
        setTimeout(function () {
            var CalenderData = new Array();
            CalenderData = <?= json_encode($CalenderArray); ?>;
            $('#calendarq').fullCalendar({
                html: true,
               buttonText: {
                    today:    'Today',
                    month:    'By Month',
                    week:     'By Week',
                },
                header: {
                    left: 'prev,',
                    center: 'title',
                    right: 'today month,agendaWeek next'
                },
                // defaultDate: '2019-01-12',
                navLinks: true, // can click day/week names to navigate views
                editable: false,
                aspectRatio: 2.75,
                eventLimit: true, // allow "more" link when too many events
                eventColor: '#FFFFFF',
                events: function (start, end, timezone, callback) {
                    $.ajax({
                        url: baseUrl + "/bills/dashboard/loadDataOnly",
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            "start": start.format(),
                            "end": end.format(),
                            "type": $("#currentBox").val(),
                            "forUser":$("#staff_user_main_form").val()
                        },
                        success: function (doc) {


                            var events = [];
                            if (!!doc.CalenderArray) {
                                $.map(doc.CalenderArray, function (r) {
                                    events.push({
                                        title: r.title,
                                        start: r.start,
                                        end: r.end,
                                        mcolor: r.color,

                                    });
                                });
                            }
                            callback(events);

                        },
                        complete: function () {

                            var startDate = start.format();
                            var endDate = end.format();
                            var type = $("#currentBox").val();

                            callBakeC(startDate, endDate, type);
                        }
                    })
                },
               
                eventRender: function(info) {
                var newCell = info.el.insertCell(3);
                var newText = document.createTextNode('Hello');
                newCell.appendChild(newText);
                },
                dayClick: function(date, jsEvent, view) {
                    loadTimeEntry(date.format());
                },eventClick: function(event) {
                    loadTimeEntry(event.start.format());
                     
                },
            }).on('click', '.fc-agendaWeek-button', function () {
                $("#Mtotal").hide();
            }).on('click', '.fc-month-button', function () {
                $("#Mtotal").show();
            });
        }, 0);
    });

    $('#deleteTimeEntryForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteTimeEntryForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteTimeEntryForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/deleteTimeEntryForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        if(res.from=="timesheet"){
                            $("#deleteTimeEntry").modal("hide");
                        }else{
                            window.location.reload();
                            afterLoader();
                        }
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });
        function loadEditTimeEntryPopup(id) {
        $("#preloader").show();
        $("#loadEditTimeEntryPopupArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/loadEditTimeEntryPopup", // json datasource
                data: {
                    'entry_id': id,"from":"timesheet",
                },
                success: function (res) {
                    $("#loadEditTimeEntryPopupArea").html('');
                    $("#loadEditTimeEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function deleteTimeEntry(id) {
        $("#deleteTimeEntry").modal("show");
        $("#delete_entry_id").val(id);
    }

    function loadTimeEntryPopup(currentDate) {
        $("#preloader").show();
        $("#loadTimeEntry").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/loadTimeEntryPopup", // json datasource
                data: {"from":"timesheet",  "curDate":currentDate,},
                success: function (res) {
                    $("#addTimeEntry").html('');
                    $("#addTimeEntry").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function changeStaffUser(){
        var D=$("#currentBox").val();
        loadCalender(D);
    }
    function loadCalender(type=null) {
        $("#currentBox").val(type)
        $('.showError').html('');
        $("#loadCalender").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/loadCalender",
            data: {
                "type": type,
                "from":"timesheet",
                "forUser":$("#staff_user_main_form").val()
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $("#loadCalender").html('');
                    return false;
                } else {
                    $("#loadCalender").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
            }
        })
    }
    function loadSummary(start,end,type) {
        $('.showError').html('');
        beforeLoader();
        $("#loadSummary").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/loadSummary",
            data: {
                "start": start,
                "end": end,
                "type":type,
                "from":"timesheet",
                "forUser":$("#staff_user_main_form").val()

            },
            success: function (res) {
            
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#loadSummary").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#loadSummary").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        })
    }
    function callBakeC(start,end,type){
        loadSummary(start,end,type);
    }
    function loadTimeEntry(currentDate){
        // Store
        localStorage.setItem("curDate", currentDate);
        $("#loadAllTimeEntryPopup").modal("show");
        $("#preloader").show();
        $("#loadAllTimeEntryPopupArea").html('<img src="{{LOADER}}"> Loading...');
      
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/time_entries/timesheet_calendar/loadAllSavedTimeEntry", // json datasource
                data: {
                    "currentDate":currentDate,
                    "forUser":$("#staff_user_main_form").val()
                },
                success: function (res) {
                    $("#loadAllTimeEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadTimeEntryByDate(curdate,type) {
        $("#preloader").show();
        $("#loadAllTimeEntryPopupArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/time_entries/timesheet_calendar/reloadTimeEntry", // json datasource
                data: {'curdate':curdate,'type':type, "forUser":$("#staff_user_main_form").val()},
                success: function (res) {
                    $("#loadAllTimeEntryPopupArea").html('');
                    $("#loadAllTimeEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    $('#loadTimeEntryPopup,#deleteTimeEntry,#loadEditTimeEntryPopup').on('hidden.bs.modal', function () {
        var currentDate=localStorage.getItem("curDate");
        loadTimeEntry(currentDate);
    });

    $('#loadAllTimeEntryPopup').on('hidden.bs.modal', function () {
        localStorage.setItem("curDate", '');
        var CurType=$("#currentBox").val();
        loadCalender(CurType);
    });
    loadCalender("all");
</script>
@stop
@endsection
