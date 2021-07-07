<?php
$upcoming_events=""; 
if(isset($_GET['upcoming_events'])){
    $upcoming_events=$_GET['upcoming_events'];
}
$CommonController= new App\Http\Controllers\CommonController();

?>
<div class="col-md-12">

    <div id="calendar_page" class="case_info_page" style="">
        <div id="case-calendar-container" data-court-case-id="12126380" data-can-edit-events="true">
            <div class="case-calendar-view mt-2">
                <div class="w-100 d-flex align-items-center">
                    <div class="d-flex ml-auto align-items-center">
                        <form action="" method="get">
                            <div class="custom-control custom-switch mr-2 upcoming-toggle d-flex align-items-center">
                                <label class="switch pr-3 switch-success"><span>Only show upcoming events</span>
                                    <input type="checkbox" id="mc"
                                        <?php if(isset($upcoming_events) && $upcoming_events!=''){ echo "checked=checked";}?>
                                        name="upcoming_events"><span class="slider"></span>
                                    <i id="event-toggle-note" aria-hidden="true"
                                        class="fa fa-question-circle icon-question-circle icon ml-1"></i>
                                </label>
                            </div>
                            <input type="submit" style="display: none;" id="submit" name="search" value="true">
                        </form>
                        <a data-toggle="modal" data-target="#loadAddEventPopup" data-placement="bottom"
                            href="javascript:;"> <button class="btn btn-primary btn-rounded m-1" type="button"
                                onclick="loadAddEventPopup();">Add Event</button></a>

                    </div>
                </div>
                <?php
                if($allEvents->isEmpty()){?>
                <div class="mt-3 empty-events alert alert-info fade show" role="alert"><div class="d-flex align-items-start"><div class="w-100">There are no upcoming events scheduled.</div></div></div>
                <?php } ?>
                <table class="mt-3 border-light event-list-view table table-sm table-hover">
                    <tbody>
                        <?php foreach($allEvents as $key=>$val){?>
                        <tr>
                            <th colspan="6">
                                <h2 class="mb-2 mt-4 font-weight-bold text-dark">{{ $key}}</h2>
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
                        <?php foreach ($val as $kk=>$vv){?>
                        <tr class="event-row false ">
                            <td class="event-date-and-time  c-pointer" style="width: 50px;">
                                <?php  
                                if(isset($oDate) && $vv->start_date==$oDate){
                                    //Dont do anything
                                }else{
                                    $dateandMonth= date('d',strtotime($vv->start_date));
                                    $dateOfEvent=date('M',strtotime($vv->start_date)); 
                                    $oDate=$vv->start_date;
                                 ?>
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
                                <?php } ?>
                            </td>
                            <td class="border-left c-pointer">
                                <div class="ml-2 mt-3"> <?php
                                if($vv->start_date==NULL || $vv->end_time==NULL){
                                    echo "All Day";
                                }else{

                                    $start_time = date("H:i:s", strtotime(convertUTCToUserTime(date('Y-m-d H:i:s',strtotime($vv->start_date.' '.$vv->start_time)),Auth::User()->user_timezone)));

                                    $end_time = date("H:i:s", strtotime(convertUTCToUserTime(date('Y-m-d H:i:s',strtotime($vv->end_date.' '.$vv->end_time)),Auth::User()->user_timezone)));

                                    echo date('h:i A',strtotime($start_time));
                                    echo "-";
                                    echo date('h:i A',strtotime($end_time));

                                    // echo date('h:i A',strtotime($vv['start_time']));
                                    // echo "-";
                                    // echo date('h:i A',strtotime($vv['end_time']));
                                }
                                    ?>
                                </div>
                            </td>
                            <td class="c-pointer">
                                <div class="mt-3 event-name d-flex align-items-center">
                                    <span><span class="">{{($vv->event_title)??'<No Title>'}}</span></span>
                                        <?php if($vv->is_event_private=='yes'){?>
                                            <span class="text-danger"> &nbsp;[Private]</span>
                                            <?php } ?>
                                            
                                </div>
                            </td>
                            {{-- <td class="c-pointer">
                                <?php 
                                if($vv->etext!=''){
                                   ?>
                                <div class="d-flex align-items-center mt-3">
                                    <div class="mr-1"
                                        style="width: 15px; height: 15px; border-radius: 30%; background-color: {{$vv->etext['color_code']}}">
                                    </div><span>{{$vv->etext['title']}}</span>

                                    
                                </div><?php 
                                }else{?>
                                <i class="table-cell-placeholder mt-3"></i>
                                <?php } ?>
                            </td> --}}
                            <td class="c-pointer">
                                @if(!empty($vv->eventType))
                                <div class="d-flex align-items-center mt-3">
                                    <div class="mr-1"
                                        style="width: 15px; height: 15px; border-radius: 30%; background-color: {{ @$vv->eventType->color_code }}">
                                    </div><span>{{ @$vv->eventType->title }}</span>
                                </div>
                                @else
                                <i class="table-cell-placeholder mt-3"></i>
                                @endif
                            </td>
                            <td class="event-users">

                                {{-- <?php
                                if(!$vv->caseuser->isEmpty()){
                                    if(count($vv->caseuser)>1){
                                        $userListHtml="";
                                        foreach($vv->caseuser as $linkuserValue){
                                            $userListHtml.="<span> <i class='fas fa-2x fa-user-circle text-black-50 pb-2'></i><a href=".BASE_URL.'contacts/attorneys/'.$linkuserValue->decode_user_id."> ".substr($linkuserValue->first_name,0,15) . " ". substr($linkuserValue->last_name,0,15)."</a></span><br>";
                                        }
                                    ?>
                                <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                                    href="javascript:;" data-toggle="popover" data-trigger="focus" title=""
                                    data-content="{{$userListHtml}}" data-html="true" data-original-title="Staff"
                                    style="float:left;">{{count($vv->caseuser)}} People</a>
                                <?php 
                                    }else{
                                        ?>
                                <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                                    href="{{BASE_URL}}/contacts/attorneys/{{$vv->caseuser[0]->decode_user_id}}">{{substr($vv->caseuser[0]->first_name,0,15)}}
                                    {{substr($vv->caseuser[0]->last_name,0,15)}}</a>
                                <?php
                                    }
                                }else{ 
                                    ?> <i class="table-cell-placeholder mt-3"></i>
                                <?php
                                }
                                ?> --}}

                                @if(!empty($vv->eventLinkedStaff))
                                    @if(count($vv->eventLinkedStaff) > 1)
                                        @php
                                        $userListHtml="";
                                        foreach($vv->eventLinkedStaff as $linkuserValue){
                                            $userListHtml.="<span> <i class='fas fa-2x fa-user-circle text-black-50 pb-2'></i><a href=".BASE_URL.'contacts/attorneys/'.$linkuserValue->decode_user_id."> ".substr($linkuserValue->first_name,0,15) . " ". substr($linkuserValue->last_name,0,15)."</a></span><br>";
                                        }
                                        @endphp
                                        <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                                        href="javascript:;" data-toggle="popover" data-trigger="focus" title=""
                                        data-content="{{$userListHtml}}" data-html="true" data-original-title="Staff"
                                        style="float:left;">{{count($vv->eventLinkedStaff)}} People</a>
                                    @else
                                        <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                                        href="{{BASE_URL}}/contacts/attorneys/{{ @$vv->eventLinkedStaff[0]->decode_user_id }}">{{ @$vv->eventLinkedStaff[0]->full_name}}</a>
                                    @endif
                                @else
                                    <i class="table-cell-placeholder mt-3"></i>
                                @endif
                            </td>
                            <td class="event-users">
                                <?php if($vv->is_event_private=='no'){?>
                                <div class="mt-3 float-right">

                                    <a class="align-items-center" data-toggle="modal" data-target="#loadReminderPopupIndex"
                                    data-placement="bottom" href="javascript:;"
                                    onclick="loadReminderPopupIndex({{$vv->id}});">
                                    <i class="fas fa-bell pr-2 align-middle"></i>
                                    </a>


                                    <a class="align-items-center" data-toggle="modal" data-target="#loadCommentPopup"
                                    data-placement="bottom" href="javascript:;"
                                    onclick="loadEventComment({{$vv->id}});">
                                    <i class="fas fa-comment pr-2 align-middle"></i>
                                    </a>
                                    <?php 
                                    if($vv->parent_evnt_id=="0"){
                                        ?>
                                    <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup"
                                    data-placement="bottom" href="javascript:;"
                                    onclick="editSingleEventFunction({{$vv->id}});">
                                    <i class="fas fa-pen pr-2  align-middle"></i> </a>
                                    <?php }else{?>
                                        <a class="align-items-center" data-toggle="modal" data-target="#loadEditEventPopup"
                                        data-placement="bottom" href="javascript:;"
                                        onclick="editEventFunction({{$vv->id}});">
                                        <i class="fas fa-pen pr-2  align-middle"></i> </a>
                                    <?php } ?>
                                   
                                    <?php 
                                    if($vv->parent_evnt_id=="0"){
                                        ?>
                                          <a class="align-items-center" data-toggle="modal" data-target="#deleteEvent"
                                          data-placement="bottom" href="javascript:;"
                                          onclick="deleteEventFunction({{$vv->id}},'single');">
                                          <i class="fas fa-trash pr-2  align-middle"></i> </a>
                                        <?php
                                    }else{?>
                                    <a class="align-items-center" data-toggle="modal" data-target="#deleteEvent"
                                        data-placement="bottom" href="javascript:;"
                                        onclick="deleteEventFunction({{$vv->id}},'multiple');">
                                        <i class="fas fa-trash pr-2  align-middle"></i> </a>
                                    <?php } ?>
                                </div>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php 
                        } 
                            } ?>
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

<div id="loadGrantAccessModal" class="modal fade modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Sharing with a client</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="grantCase">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style> .modal { overflow: auto !important; }</style>

@section('page-js-inner')

<script type="text/javascript">
    $(document).ready(function () {
        $("input:checkbox#mc").click(function () {
            $('#submit').click();
        });
    });
    $('#loadEditEventPopup,#loadAddEventPopup').on('hidden.bs.modal', function () {
        $("#preloader").show();
          window.location.reload();   
       
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
    function loadGrantAccessModal(id) {        
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
        
        

       
    }
   
</script>
@stop
