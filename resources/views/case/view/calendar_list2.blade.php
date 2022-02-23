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
        <div id="case-calendar-container" data-court-case-id="12126380" data-can-edit-events="true">
            <div class="case-calendar-view mt-2">
                <div class="w-100 d-flex align-items-center">
                    <div class="d-flex ml-auto align-items-center">
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
                        <a data-toggle="modal" data-target="#loadAddEventPopup" data-placement="bottom"
                            href="javascript:;"> <button class="btn btn-primary btn-rounded m-1" type="button"
                                onclick="loadAddEventPopup();">Add Event</button></a>
                        @endcan

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
                        @forelse ($allEvents as $key => $item)
                            @if(count($item->eventRecurring))
                                @forelse ($item->eventRecurring as $key1 => $item1)
                                    @if(isset($oDate) && date('Y', strtotime($oDate)) != date('Y', strtotime($item1->start_date)))
                                    <tr>
                                        <th colspan="6">
                                            <h2 class="mb-2 mt-4 font-weight-bold text-dark">{{ date('Y',strtotime($item1->start_date)) }}</h2>
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
                                    <tr>
                                        <td class="event-date-and-time  c-pointer" style="width: 50px;">
                                            @if(isset($oDate) && $item1->start_date==$oDate)
                                            @else
                                                @php
                                                    $dateandMonth= date('d',strtotime($item1->user_start_date));
                                                    $dateOfEvent=date('M',strtotime($item1->user_start_date)); 
                                                    $oDate=$item1->start_date;
                                                @endphp
                                                <div class="d-flex">
                                                    <div style="width: 45px;">
                                                        <div
                                                            class="col-12 p-0 text-center text-white bg-dark font-weight-bold rounded-top">
                                                            <?php echo $dateOfEvent; ?></div>
                                                        <div class="col-12 p-0 text-center rounded-bottom"
                                                            style="background-color: rgb(237, 237, 235); color: rgb(70, 74, 76);">
                                                            <h4 class="py-1 m-0 font-weight-bold">
                                                                {{$dateandMonth}}
                                                            </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="ml-2 mt-3">
                                            @php
                                            if($item->start_time==NULL || $item->end_time==NULL && $item->is_all_day == "yes"){
                                                echo "All Day";
                                            }else{                        
                                                echo date('h:i A',strtotime($item1->start_date));
                                                echo "-";
                                                echo date('h:i A',strtotime($item1->end_date));
                                            }
                                            @endphp
                                            </div>
                                        </td>
                                        <td>{{ $item->event_title }}</td>
                                        @php
                                            $isAuthUserLinked = $item->eventLinkedStaff->where('users.id', auth()->id())->first();
                                        @endphp
                                        <td class="c-pointer">
                                            @if(!empty($item->eventType) && ($isAuthUserLinked || auth()->user()->parent_user == 0))
                                            <div class="d-flex align-items-center mt-3">
                                                <div class="mr-1"
                                                    style="width: 15px; height: 15px; border-radius: 30%; background-color: {{ @$item->eventType->color_code }}">
                                                </div><span>{{ @$item->eventType->title }}</span>
                                            </div>
                                            @else
                                            <i class="table-cell-placeholder mt-3"></i>
                                            @endif
                                        </td>
                                        <td class="event-users">
                                            @if(!empty($item->eventLinkedStaff) && ($isAuthUserLinked || auth()->user()->parent_user == 0))
                                                @php
                                                    $totalUser = count($item->eventLinkedStaff) + count($item->eventLinkedContact) + count($item->eventLinkedLead);    
                                                @endphp
                                                @if($totalUser > 1)
                                                    @php
                                                    $userListHtml = "<table><tbody>";
                                                    $userListHtml.="<tr><td colspan='2'><b>Staff</b></td></tr>";
                                                    foreach($item->eventLinkedStaff as $linkuserValue){
                                                        $userListHtml.="<tr><td>
                                                        <span> 
                                                            <i class='fas fa-2x fa-user-circle text-black-50 pb-2'></i>
                                                            <a href=".url('contacts/attorneys/'.$linkuserValue->decode_id)."> ".$linkuserValue->full_name."</a>
                                                        </span>
                                                        </td>";
                                                        if($linkuserValue->pivot->attending == "yes") {
                                                            $userListHtml .= "<td>Attending</td></tr>";
                                                        } else {
                                                            $userListHtml .= "<td></td></tr>";
                                                        }
                                                    }
                                                    if(count($item->eventLinkedContact)) {
                                                        $userListHtml.="<tr><td colspan='2'><b>Contacts/Leads</b></td></tr>";
                                                        foreach($item->eventLinkedContact as $linkuserValue){
                                                            $userListHtml.="<tr><td>
                                                            <span> 
                                                                <i class='fas fa-2x fa-user-circle text-black-50 pb-2'></i>
                                                                <a href=".(($linkuserValue->user_level == 4) ? route('contacts/companies/view', $linkuserValue->id) : route('contacts/clients/view', $linkuserValue->id))."> ".$linkuserValue->full_name."</a>
                                                            </span>
                                                            </td>";
                                                            if($linkuserValue->pivot->attending == "yes") {
                                                                $userListHtml .= "<td>Attending</td></tr>";
                                                            } else {
                                                                $userListHtml .= "<td></td></tr>";
                                                            }
                                                        }
                                                    }
                                                    if(count($item->eventLinkedLead)) {
                                                        $userListHtml.="<tr><td colspan='2'><b>Contacts/Leads</b></td></tr>";
                                                        foreach($item->eventLinkedLead as $linkuserValue){
                                                            $userListHtml.="<tr><td>
                                                            <span> 
                                                                <i class='fas fa-2x fa-user-circle text-black-50 pb-2'></i>
                                                                <a href=".route('case_details/info', $linkuserValue->id)."> ".$linkuserValue->full_name."</a>
                                                            </span>
                                                            </td>";
                                                            if($linkuserValue->pivot->attending == "yes") {
                                                                $userListHtml .= "<td>Attending</td></tr>";
                                                            } else {
                                                                $userListHtml .= "<td></td></tr>";
                                                            }
                                                        }
                                                    }
                                                    $userListHtml .= "</tbody></table>";
                                                    @endphp
                                                    <a class="mt-3 event-name d-flex align-items-center pop" tabindex="0" role="button"
                                                    href="javascript:;" data-toggle="popover" title=""
                                                    data-content="{{$userListHtml}}" data-html="true" {{-- data-original-title="Staff" --}}
                                                    style="float:left;">{{ $totalUser ?? 0 }} People</a>
                                                @else
                                                    <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                                                    href="{{ route('contacts/attorneys/info', base64_encode(@$item->eventLinkedStaff[0]->id)) }}">{{ @$item->eventLinkedStaff[0]->full_name}}</a>
                                                @endif
                                            @else
                                                <i class="table-cell-placeholder mt-3"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup" data-placement="bottom" href="javascript:;" onclick="editEventFunction({{$item->id}},{{$item1->id}});">
                                                <i class="fas fa-pen pr-2  align-middle"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            
                            @endif
                        @empty
                        @endforelse
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
            if($(this).is(":checked")) {
                $("#upcoming_event").val('on');
            } else {
                $("#upcoming_event").val('off');
            }
            $('#submit').click();
            // tab1Page = 1;
            // loadMoreEvent(tab1Page, filter = 'true');
        });

        // For load more events
        // loadMoreEvent(1, filter = null);
    });
    $('#loadEditEventPopup,#loadAddEventPopup').on('hidden.bs.modal', function () {
        // $("#preloader").show();
          window.location.reload();  
        // loadMoreEvent(1, filter = 'true');      
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
    function editEventFunction(evnt_id, recurring_event_id = null) {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadEditEventPage", // json datasource
                data: {
                    "evnt_id":evnt_id,
                    "from":"edit",
                    "recurring_event_id": recurring_event_id
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
</script>
@stop
