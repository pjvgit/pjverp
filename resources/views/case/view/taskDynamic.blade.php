<?php
$CommonController= new App\Http\Controllers\CommonController();
$result = array();
$yestedayDate=date('Y-m-d', strtotime('-1 day'));
foreach ($task as $element) {
    if($element->task_due_on < $yestedayDate){
        $result[$yestedayDate][] = $element;
    }else{
        $result[$element->task_due_on][] = $element;
    }
}
?>
<div class="table-responsive">
    <table class="display table table-striped table-bordered" style="width:100%">
        <thead>
            <tr class="labels data">
                <th class="text-center" style="cursor: initial;width:5%;">
                    <label class="sr-only ">Select all rows</label>
                    <input type="checkbox" class="mx-1" name="all" id="checkall">
                </th>
                <th class="task-status-cell align-middle" style="cursor: pointer;"></th>
                <th class="task-name-cell align-middle" style="cursor: pointer;width: 55%; border-left: none;">Name</th>
                <th class="task-priority-cell align-middle text-nowrap" style="cursor: pointer;width:5%;"></th>
                <th class="task-priority-cell align-middle text-nowrap YXd6tPOgoO-RylXVRzzZh" style="width:5%; cursor: pointer;">
                    Priority</th>
                <th class="task-due-cell align-middle" style="cursor: pointer;width:10%;"> Due</th>
                <th class="task-assigned-to-cell align-middle YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;width:10%;">
                    Assigned To</th>
                <th class="task-actions-cell align-middle YXd6tPOgoO-RylXVRzzZh" style="cursor: initial;width:5%;"></th>
            </tr>
        </thead>
        @foreach($result as $key=>$row)
        <?php if($key=='9999-12-30'){?>
        <tr class="row-group-header table-active" role="button">
            <td class="text-center"></td>
            <td colspan="7"><strong>No due date</strong></td>
        </tr>
        <?php }else if($key < date('Y-m-d')){?>
        <tr class="row-group-header table-danger" role="button">
            <td class="text-center"></td>
            <td colspan="7"><strong>Overdue</strong></td>

        </tr>
        <?php } else {?>
        <tr class="row-group-header table-secondary-task" role="button">
            <td class="text-center"></td>
            <td colspan="7">
                <div><strong>Due {{date('M j, Y',strtotime($key))}}
                    </strong>&nbsp;<small class="text-muted">- in {{$CommonController->daysReturns($key)}} </small>
                </div>
            </td>
        </tr>
        <?php } ?>

        @foreach($row as $subrow)
        <tr class="collapse show" id="accordion-item-icons-{{$key}}">
            <td class="text-center">
                <label for="select-row-74" class="sr-only ">Select row</label>
                <input id="select-row-74" type="checkbox" value="{{$subrow->id}}" class="task_checkbox"
                    name="selectedTask[{{$subrow->id}}]">
            </td>
            <td class="task-status-cell align-middle">
                <?php if($subrow->status=='0'){?>
                <a class="align-items-center" data-toggle="popover" data-trigger="hover" title=""
                    data-content="Mark as complete" data-placement="top" data-html="true" data-original-title=""
                    style="float:left;" href="javascript:;" onclick="taskStatus({{$subrow->id}},{{$subrow->status}});">
                    <button
                        class="mb-3 btn btn-outline-secondary  btn-block archive-case-button pendo-close-case btn-rounded"
                        type="button">
                        Mark Complete
                    </button></i>
                </a>
                <?php }else{ ?>
                <a class="align-items-center" data-toggle="popover" data-trigger="hover" title=""
                    data-content="Mark as Incomplete" data-html="true" data-original-title="" style="float:left;"
                    data-placement="top" href="javascript:;" onclick="taskStatus({{$subrow->id}},{{$subrow->status}});">
                    <button
                        class="mb-3 btn btn-outline-secondary  btn-block archive-case-button pendo-close-case btn-rounded"
                        type="button">
                        Completed</button>
                </a>

                <?php } ?>
            </td>
            <td class="task-name-cell align-middle">

                <?php if($subrow->task_due_on > date('Y-m-d')){?>
                    <a data-toggle="modal"  data-target="#loadTaskDetailsView" data-placement="bottom" href="javascript:;"  onclick="loadTaskDetailsView({{$subrow->id}});">{{ $subrow->task_title }}</a>
                <?php }else{ ?>
                    <a data-toggle="modal"  data-target="#loadTaskDetailsView" data-placement="bottom" href="javascript:;"  onclick="loadTaskDetailsView({{$subrow->id}});">{{ $subrow->task_title }}</a>

                <?php } ?>
                <?php if($subrow->status=='1'){
                     $OwnDate=$CommonController->convertUTCToUserTime($subrow->task_completed_date,Auth::User()->user_timezone);
                 ?>
                <br><small class="text-muted">Completed by <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($subrow->uid)}}"> {{$subrow->task_completed['first_name']}}
                    {{$subrow->task_completed['last_name']}}   ({{$CommonController->getUserLevelText($subrow->user_type)}}) </a> on
                    {{date('m/d/Y',strtotime(convertUTCToUserDate($OwnDate, auth()->user()->user_timezone)))}}</small>
                <?php } ?>

               
            </td>
            <td class="task-name-cell align-middle">
                <?php
                if($subrow->checklist_counter!=""){?>
                <a href="#" onclick="loadChecklistView({{$subrow->id}});" data-toggle="dropdown" class="dropdown-toggle1" id="taskCounter_{{$subrow->id}}">
                    <img src="{{BASE_URL}}public/images/checklist_icon_dark.png">
                    <span style=""> {{$subrow->checklist_counter}}</span>
                </a>
                <div class="dropdown-menu bg-transparent shadow-none p-0 m-0 " x-placement="bottom-start">
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-bordered mt-3" id="taskReviewArea_{{$subrow->id}}">
                                
                            </table>
                    </div>
                </div>
                <?php } ?>
            </td>
            <td class="task-priority-cell align-middle text-nowrap">
                <?php if($subrow->task_priority == "1"){?>
                <i class="fas fa-circle fa-sm  mr-1 text-black-50"></i>Low
                <?php }else if($subrow->task_priority == "2"){?>
                <i class="fas fa-circle fa-sm mr-1 text-secondary-task"></i>Medium
                <?php }else if($subrow->task_priority == "3") {?>
                <i class="fas fa-circle fa-sm mr-1 text-warning"></i>High
                <?php }else{ ?>
                <div>No Priority</div>
                <?php } ?>
            </td>
            <td class="task-due-cell align-middle">
                <?php if($subrow->task_due_on > date('Y-m-d')){
                                                        if($subrow->task_due_on!='9999-12-30'){?>
                <span class="font-weight-bold">{{date('M j, Y',strtotime($subrow->task_due_on))}}</span>
                <?php } ?>
                <?php }else{ ?>
                <span class="font-weight-bold text-danger">{{date('M j, Y',strtotime($subrow->task_due_on))}}</span>

                <?php } ?>
            </td>

            <td class="task-assigned-to-cell align-middle">
                <?php
                    if($subrow->task_user){
                        if(count($subrow->task_user)>1){
                            $userListHtml="";
                            foreach($subrow->task_user as $linkuserValue){
                                $userListHtml.="<span> <i class='fas fa-2x fa-user-circle text-black-50 pb-2'></i><a href=".BASE_URL.'contacts/attorneys/'.base64_encode($linkuserValue->id)."> ".substr($linkuserValue->first_name,0,15) . " ". substr($linkuserValue->last_name,0,15)."</a></span><br>";
                            }
                        ?>
                <a class="event-name d-flex align-items-center" tabindex="0" role="button" href="javascript:;"
                    data-toggle="popover" data-trigger="hover" title="" data-content="{{$userListHtml}}"
                    data-html="true" data-original-title="" style="float:left;">{{count($subrow->task_user)}}
                    Users</a>
                <?php 
                    }else{
                        if(isset($subrow->task_user[0])){?>
                <a class=" event-name d-flex align-items-center" tabindex="0" role="button"
                    href="{{BASE_URL}}contacts/attorneys/{{base64_encode($subrow->task_user[0]->id)}}">{{substr($subrow->task_user[0]->first_name,0,15)}}
                    {{substr($subrow->task_user[0]->last_name,0,15)}}</a>
                <?php }
                    }
                }else{ 
                    ?> <i class="table-cell-placeholder"></i>
                <?php
                }
                ?>
            </td>
            <td class="task-actions-cell align-middle">
                <div class="actions-cell float-right">
                    <div class="d-flex align-item-center task-action-buttons-16333660">
                        <div>

                            <a class="align-items-center" data-toggle="modal"
                                data-target="#loadReminderPopupIndexCommon" data-placement="bottom" href="javascript:;"
                                onclick="loadReminderPopupIndex({{$subrow->id}});">
                                <span data-toggle="popover" data-trigger="hover" title="" data-content="Reminder"
                                    data-placement="top" data-html="true"> <i class="fas fa-bell pr-3 align-middle"></i>
                                </span>
                            </a>
                        </div>
                        <div>
                            <a class="align-items-center" data-toggle="modal" data-target="#editTask"
                                data-placement="bottom" href="javascript:;" onclick="editTask({{$subrow->id}});">
                                <span data-toggle="popover" data-trigger="hover" title="" data-content="Edit"
                                    data-placement="top" data-html="true"> <i class="fas fa-pen pr-3  align-middle"></i>
                                </span></a>
                        </div>
                        <div>
                            <a class="align-items-center" data-original-title="" data-toggle="modal"
                                data-target="#deleteTask" data-placement="bottom"
                                onclick="deleteTaskFunction({{$subrow->id}});" href="javascript:;">
                                <span data-toggle="popover" data-trigger="hover" title="" data-content="Delete"
                                    data-placement="top" data-html="true"><i
                                        class="fas fa-trash pr-3  align-middle"></i> </span></a>

                        </div>
                    </div>
                </div>
            </td>
        </tr>
        @endforeach
        @endforeach
        <?php  if(empty($result)){?>
        <tr>
            <td colspan="10" class="text-center"> No task available </td>
        </tr>
        <?php  } ?>
    </table>
    <span class="taskListPager">{!! $task->links() !!}</span>
</div>
<div id="loadTaskDetailsView" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            
            <div class="modal-body">
                <div id="loadTaskDetailsViewArea"></div>
            </div>
        </div>
    </div>
</div>


<div id="loadReminderPopupIndexInViewOverlay" class="modal fade bd-example-modal-lg modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static" style="">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Set Task Reminders</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="reminderDataIndexInView">

                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>

<style>
    .modal {
        overflow: auto !important;
    }
</style>

<script>
    $(document).ready(function () {
        $("[data-toggle=popover]").popover();
        $('.dropdown-toggle').dropdown();  
        $('.dropdown-toggle1').dropdown();  
        $('#checkall').on('change', function () {
            $('.task_checkbox').prop('checked', $(this).prop("checked"));
            if ($('.task_checkbox:checked').length == "0") {
                $('#Taskactionbutton').attr('disabled', 'disabled');
            } else {
                $('#Taskactionbutton').removeAttr('disabled');
            }
        });
        //deselect "checked all", if one of the listed checkbox product is unchecked amd select "checked all" if all of the listed checkbox product is checked
        $('.task_checkbox').change(function () { //".checkbox" change 
            if ($('.task_checkbox:checked').length == $('.task_checkbox').length) {
                $('#checkall').prop('checked', true);
            } else {
                $('#checkall').prop('checked', false);

            }
            if ($('.task_checkbox:checked').length == "0") {
                $('#Taskactionbutton').attr('disabled', 'disabled');
            } else {
                $('#Taskactionbutton').removeAttr('disabled');
            }
        });
    });

    function loadTaskDetailsView(task_id) {
        $("#loadTaskDetailsViewArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadTaskViewPage", // json datasource
            data: { "task_id": task_id},
            success: function (res) {
                $("#loadTaskDetailsViewArea").html(res);
            }
        })
    }
    function loadReminderPopupIndexInCaseList(task_id) {
        $("#reminderDataIndexInView").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadReminderPopupIndexDontRefresh", // json datasource
                data: {
                    "task_id": task_id,
                    "from_view":"yes"
                },
                success: function (res) {
                    $("#reminderDataIndexInView").html(res);
                }
            })
        })
    }
    function loadChecklistView(task_id) {
        // $("#taskReviewArea_"+task_id).html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadCheckListViewForTask", // json datasource
            data: {
                "task_id": task_id,
                "forList":"yes"
            },
            success: function (res) {
                $("#taskReviewArea_"+task_id).html(res);

            }
        })
    }
    function updateCheckList(id, status,task_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/updateCheckList", // json datasource
            data: {
                "id": id,
                "status": status
            },
            success: function (res) {
                loadChecklistView(task_id);
                reloadCounter(task_id);
            }
        })
    }

    function markAsCompleteTask(task_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/singleTaskMarkAsComplete", // json datasource
            data: {
                "task_id": task_id
            },
            success: function (res) {
                loadChecklistView(task_id);
             
            }
        })
    }
    function reloadCounter(task_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/reloadCounter", // json datasource
            data: {
                "task_id": task_id
            },
            success: function (res) {
                $("#taskCounter_"+task_id).html(res);
            }
        })
    }
</script>
