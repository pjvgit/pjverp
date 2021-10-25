<?php
// $upcoming_events=""; 
// if(isset($_GET['upcoming_events'])){
//     $upcoming_events=$_GET['upcoming_events'];
// }
// $CommonController= new App\Http\Controllers\CommonController();

?>
<div class="col-md-12">
    <div id="calendar_page" class="case_info_page" style="">
        <div id="case-calendar-container" data-court-case-id="12126380" data-can-edit-events="true">
            <div class="case-calendar-view mt-2">
                <div class="w-100 d-flex align-items-center">
                    <div class="d-flex ml-auto align-items-center">
                        <form action="" method="get">
                            <div class="custom-control custom-switch mr-2 upcoming-toggle d-flex align-items-center">
                                <label class="switch pr-3 switch-success" style="margin-top: 10px;"><span>Only show upcoming events</span>
                                    <input type="checkbox" id="mc" value="true" checked
                                        {{-- <?php if(isset($upcoming_events) && $upcoming_events!=''){ echo "checked=checked";}?> --}}
                                        name="upcoming_events"><span class="slider"></span>
                                </label>
                                <i id="event-toggle-note" aria-hidden="true" class="fa fa-question-circle icon-question-circle icon ml-1" data-toggle="tooltip" title="Recurring events are limited to 1 year from today"></i>
                            </div>
                            <input type="submit" style="display: none;" id="submit" name="search" value="true">
                        </form>
                        <a data-toggle="modal" data-target="#loadAddEventPopup" data-placement="bottom"
                            href="javascript:;"> <button class="btn btn-primary btn-rounded m-1" type="button"
                                onclick="loadAddEventPopup();">Add Event</button></a>

                    </div>
                </div>
                
                
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
                    @endif
                    </tbody>
                </table>
            </div>
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
                <h5 class="modal-title" id="deleteSingleEvent"></h5>
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

@include('calendar.partials.load_grant_access_modal')

<style> .modal { overflow: auto !important; }</style>

@section('page-js-inner')
<script src="{{ asset('assets\js\custom\calendar\listevent.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("input:checkbox#mc").click(function () {
            // $('#submit').click();
            tab1Page = 1;
            loadMoreEvent(tab1Page, filter = 'true');
        });

        // For load more events
        loadMoreEvent(1, filter = null);
    });
    $('#loadEditEventPopup,#loadAddEventPopup').on('hidden.bs.modal', function () {
        $("#preloader").show();
        //   window.location.reload();  
        loadMoreEvent(1, filter = 'true');      
    });
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
        $("#eventCommentPopup").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadCommentPopup", // json datasource
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
    }
    function editEventFunction(evnt_id) {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadEditEventPage", // json datasource
                data: {
                    "evnt_id":evnt_id,
                    "from":"edit"
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
    }
    function editSingleEventFunction(evnt_id) {
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
    }
    function deleteEventFunction(id,types) {
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
    /* function loadGrantAccessModal(id) {        
        if($("#cleintUSER_"+id).prop('checked')==true && $("#cleintUSER_"+id).attr("data-client_portal_enable") == 0){
            $("#cleintUSER_"+id).prop('checked',false);
            $("#loadGrantAccessModal").modal();
            $("#innerLoader").css('display', 'none');
            $("#preloader").show();
            $("#grantCase").html('');
            $(function () {
                $.ajax({
                    type: "POST",
                    url:  baseUrl +"/court_cases/loadGrantAccessPage", // json datasource
                    data: {"client_id":id},
                    success: function (res) {
                        $("#grantCase").html(res);
                        $("#preloader").hide();
                        $("#innerLoader").css('display', 'none');
                        
                        $(".add-more").trigger('click');
                        return false;
                    }
                })
            })
        } var checkecCounter=$('input[name="clientCheckbox[]"]:checked').length;
                        if(checkecCounter>0){
                            $(".reminder_user_type option[value='client-lead']").show();
                            $(".reminder_type option[value='text-sms']").show();
                        }else{
                            $(".reminder_user_type option[value='client-lead']").hide();
                            $(".reminder_type option[value='text-sms']").hide();
                        }
        
        

       
    } */

    
    /* var tab1Page = 1;
    $(window).scroll(function() {
        if($(window).scrollTop() + $(window).height() >= $(document).height()) {
            tab1Page++;
            var totalPage = $('#event_list_table tbody tr .event-last-page').val();
            if(tab1Page <= totalPage)
                loadMoreEvent(tab1Page, filter = null);
        }
    });
   
    // Load more events
    function loadMoreEvent(page, filter = null) {
        var divId = 'event_list_table tbody';
        var upcoming = $("input:checkbox#mc:checked").val();
        $.ajax({
            url : '?page=' + page,
            data: {upcoming: upcoming},
            beforeSend: function() {
                $(".load-more-loader").show();
            }
        }).done(function (data) {
            $(".load-more-loader").parents('tr').hide();
            if(data != "") {
                if(filter) {
                    $('#'+divId).html(data);
                } else {
                    $('#'+divId).append(data);
                }
                $('#'+divId+' .pagination').hide();
            } else {
                $('#'+divId).html('<tr><td colspan="6" class="text-center"><h4 class="all-pdng-cls">No record found</h4></td></tr>');
            }
        }).fail(function () {
            $(".load-more-loader").hide();
            $('#'+divId).append('<tr><td colspan="6" class="text-center"><h4 class="all-pdng-cls">No record found</h4></td></tr>');
        });
    } */


    function printEntry()
    {
        $('#hiddenLable').show();
        var canvas = $(".printDiv").html();
        window.print(canvas);
        // w.close();
        $('#hiddenLable').hide();
        return false;  
    }
</script>
@stop
