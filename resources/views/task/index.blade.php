@extends('layouts.master')
@section('title', 'Task Lists')
@section('main-content')

<style>
    .morecontent span {
        display: none;
    }

    .morelink {
        display: block;
    }

</style>
<?php
 
$ts=$at=$cl=$daterange=$filter_type=$task_read='';

if(isset($_GET['ts'])){
     $ts= $_GET['ts'];
}
if(isset($_GET['at'])){
     $at= $_GET['at'];
}
if(isset($_GET['cl'])){
     $cl= $_GET['cl'];
}
if(isset($_GET['daterange'])){
     $daterange= $_GET['daterange'];
}
if(isset($_GET['filter_type'])){
     $filter_type= $_GET['filter_type'];
}
if(isset($_GET['sort'])){
     $orderby= $_GET['sort'];
}else{
    $orderby='asc';
}
if(isset($_GET['sort_on'])){
     $orderon= $_GET['sort_on'];
}else{
    $orderon='task_due_on';
}
if(isset($_GET['defaultsort'])){
     $defaultsort= '';
}else{
    $defaultsort='yes';
}
if(isset($_GET['task_read'])){
     $task_read= $_GET['task_read'];
}
?>
<div class="row">

    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex align-items-center">
                    <h3 class="my-0 mr-1 font-weight-bold"><i class="fas fa-clipboard-list fa-sm mr-2"></i>Tasks</h3>
                    <h5 class="d-none d-print-inline mt-2 font-weight-bold text-muted">&nbsp;as of today 10/07/2020</h5>
                    <div class="ml-auto d-flex d-print-none"><button type="button"
                            class="feedback-button mr-2 text-black-50 btn btn-link">Tell us what you think</button><span
                            class="mt-2 text-muted">|</span>
                        <div>
                            <a href="{{route('tasks/markasread')}}">
                                <button type="button" class="mr-2 btn btn-link">Mark all as read</button>
                            </a>
                            <div id="bulk-dropdown" class="mr-2 actions-button btn-group">
                                <div class="mx-2">

                                    <div class="btn-group">
                                        <button class="btn btn-light m-1 dropdown-toggle" data-toggle="dropdown"
                                            id="actionbutton" disabled="disabled" aria-haspopup="true"
                                            aria-expanded="false">
                                            Action
                                        </button>
                                        <div class="dropdown-menu bg-transparent shadow-none p-0 m-0 "
                                            x-placement="top-start">
                                            <div class="card">
                                                <div class="card-body">
                                                    <button type="button" tabindex="0" onclick="getCheckedUser()"
                                                        role="menuitem"
                                                        class="bulk-mark-tasks-as-read dropdown-item"><span>Mark as
                                                            read</span>
                                                    </button>
                                                    <button type="button" tabindex="0" onclick="markasCompleted()"
                                                        role="menuitem"
                                                        class="bulk-mark-tasks-as-complete dropdown-item"><span>Mark as
                                                            completed</span>
                                                    </button>
                                                    <a class="align-items-center" data-toggle="modal"
                                                        data-target="#changeDueDate" data-placement="bottom"
                                                        href="javascript:;">
                                                        <button type="button" tabindex="0" role="menuitem"
                                                            class="bulk-mark-tasks-as-complete dropdown-item">Change due
                                                            date</button>
                                                    </a>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <a data-toggle="modal" data-target="#loadAddTaskPopup" data-placement="bottom"
                                href="javascript:;"> <button class="btn btn-primary btn-rounded m-1" type="button"
                                    onclick="loadAddTaskPopup();">Add Task</button></a>
                        </div>
                    </div>
                </div>
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <input type="hidden" name="filter_type" id="filter_type" value="{{$filter_type}}">
                    <input type='hidden' name="sort" id='sort' value='{{$orderby}}'>
                    <input type='hidden' name="sort_on" id='sort_on' value='{{$orderon}}'>
                    <input type='hidden' name="defaultsort" id='defaultsort' value='{{$defaultsort}}'>
                    <div class="row pl-4 pb-4">
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Assigned To</label>
                            <select onchange="selectUser();" class="form-control user_type select2" id="user_type_assign_to" name="at">

                                <option <?php if($at=="allfirmuser"){ echo "selected=selected"; }?> value="allfirmuser">
                                    All Firm User</option>
                                <option <?php if($at=="me"){ echo "selected=selected"; }?> value="me">Me</option>
                                <option <?php if($at=="everyoneelse"){ echo "selected=selected"; }?>
                                    value="everyoneelse">Everyone else</option>
                                <optgroup label="Staff">
                                    <?php 
                                foreach($loadFirmStaff as $kcs=>$vcs){?>
                                    <option <?php if($at==$vcs->id){ echo "selected=selected"; }?> value="{{$vcs->id}}">
                                        {{$vcs->first_name}} {{$vcs->last_name}}</option>
                                    <?php } ?>

                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Completion Status</label>
                            <select id="la" name="ts" class="form-control custom-select col select2">
                                <option <?php if($ts=="all"){ echo "selected=selected"; }?> value="all">All Statuses
                                </option>
                                <option <?php if($ts=="1"){ echo "selected=selected"; }?> value="1">Complete</option>
                                <option <?php if($ts=="0"){ echo "selected=selected"; }?> value="0">Incomplete</option>


                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">By Case / Lead</label>
                            <select class="form-control case_or_lead select2" id="case_or_lead_index" name="cl"
                                data-placeholder="Select...">
                                <option value="">Select case</option>
                                <optgroup label="Case">
                                    <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                                    <option <?php if($cl==$Caseval->id){ echo "selected=selected"; }?>
                                        value="{{$Caseval->id}}">{{$Caseval->case_title}}</option>
                                    <?php } ?>
                                </optgroup>
                                <optgroup label="Leads">
                                    <?php foreach($caseLeadList as $caseLeadListKey=>$caseLeadListVal){ ?>
                                    <option <?php if($cl==$caseLeadListKey) { echo "selected=selected"; } ?> 
                                    value="{{$caseLeadListKey}}">{{substr($caseLeadListVal,0,100)}}</option>
                                    <?php } ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Due Date</label>

                            <input type="text" class="form-control" id="daterange" name="daterange"
                                value="{{$daterange}}" />
                        </div>
                        <div class="col-md-4 form-group mb-3 mt-3 pt-2">
                            <label class="switch pr-3 switch-success"><span>Show unread tasks only</span>
                                <input type="checkbox" <?php if($task_read=='on'){ echo "checked=checked"; }?>
                                    name="task_read">
                                <span class="slider "></span>
                            </label>
                            <button class="btn btn-info btn-rounded m-1" type="submit">Apply Filters</button>
                            <button type="button" class="test-clear-filters text-black-50 btn btn-link"><a
                                    href="{{route('tasks')}}">Clear Filters</a></button>
                        </div>

                    </div>
                </form>
                <?php 
                if(isset($_GET['sort_on']) && isset($_GET['defaultsor']) && $_GET['defaultsor']==''){
                   
                            ?>
                    <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="employee-grid" style="width:100%">
                        <thead>

                            <tr>
                                <th class="text-center" style="cursor: initial;">
                                    <label class="sr-only ">Select all rows</label>
                                    <input type="checkbox" class="mx-1" name="all" id="checkall">
                                </th>
                                <?php if($orderby=="asc" && $orderon=="status"){
                                                $norder="desc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-up fa-fw icon-fw icon"></i>';
                                            }else if($orderby=="desc" && $orderon=="status"){
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-down fa-fw icon-fw icon"></i>';
                                            }else{
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-sort fa-fw icon-sort icon-fw icon"></i>';
                                            }?>

                                <th class="task-status-cell align-middle" style="cursor: pointer;"
                                    onclick='sortTable("status","<?php echo $norder;?>");'>STATUS
                                    <?php echo $icon;?>
                                </th>
                                <?php if($orderby=="asc" && $orderon=="task_title"){
                                                $norder="desc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-down fa-fw icon-fw icon"></i>';
                                            }else if($orderby=="desc" && $orderon=="task_title"){
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-up fa-fw icon-fw icon"></i>';
                                            }else{
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-sort fa-fw icon-sort icon-fw icon"></i>';
                                            }?>
                                <th class="task-name-cell align-middle" style="cursor: pointer;"
                                    onclick='sortTable("task_title","<?php echo $norder;?>");'>
                                    TASK NAME <?php echo $icon;?>

                                </th>
                                <th class="task-subtasks-cell align-middle " style="cursor: initial;">SUBTASKS</th>

                                <?php if($orderby=="asc" && $orderon=="task_priority"){
                                                $norder="desc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-down fa-fw icon-fw icon"></i>';
                                            }else if($orderby=="desc" && $orderon=="task_priority"){
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-up fa-fw icon-fw icon"></i>';
                                            }else{
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-sort fa-fw icon-sort icon-fw icon"></i>';
                                            }?>
                                <th class="task-priority-cell align-middle text-nowrap YXd6tPOgoO-RylXVRzzZh"
                                    style="cursor: pointer;"
                                    onclick='sortTable("task_priority","<?php echo $norder;?>");'>PRIORITY
                                    <?php echo $icon;?></th>

                                <?php if($orderby=="asc" && $orderon=="task_due_on"){
                                                $norder="desc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-down fa-fw icon-fw icon"></i>';
                                            }else if($orderby=="desc" && $orderon=="task_due_on"){
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-up fa-fw icon-fw icon"></i>';
                                            }else{
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-sort fa-fw icon-sort icon-fw icon"></i>';
                                            }?>
                                <th class="task-due-cell align-middle" style="cursor: pointer;"
                                    onclick='sortTable("task_due_on","<?php echo $norder;?>");'> DUE
                                    <?php echo $icon;?>
                                </th>
                                <?php if($orderby=="asc" && $orderon=="case_title"){
                                                $norder="desc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-down fa-fw icon-fw icon"></i>';
                                            }else if($orderby=="desc" && $orderon=="case_title"){
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-up fa-fw icon-fw icon"></i>';
                                            }else{
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-sort fa-fw icon-sort icon-fw icon"></i>';
                                            }?>
                                <th class="task-case-lead-cell align-middle" style="cursor: pointer;"
                                    onclick='sortTable("case_title","<?php echo $norder;?>");'>
                                    CASE/LEAD <?php echo $icon;?>
                                </th>
                                <th class="task-assigned-to-cell align-middle YXd6tPOgoO-RylXVRzzZh"
                                    style="cursor: initial;">
                                    ASSIGNED TO
                                </th>
                                <th class="task-actions-cell align-middle YXd6tPOgoO-RylXVRzzZh"
                                    style="cursor: initial;"></th>
                            </tr>
                        </thead>
                        @foreach($task as $subrow)
                        <tr class="">
                            <td class="text-center">
                                <label for="select-row-74" class="sr-only ">Select row</label>
                                <input id="select-row-74" type="checkbox" class="task_checkbox" value="{{$subrow->id}}"
                                    name="selectedTask[{{$subrow->id}}]">
                            </td>
                            <td class="task-status-cell align-middle">
                                <?php if($subrow->status=='0'){?>
                                <a class="align-items-center" data-toggle="popover" data-trigger="hover" title=""
                                    data-content="Incomplete" data-placement="top" data-html="true"
                                    data-original-title="" style="float:left;" href="javascript:;"
                                    onclick="taskStatus({{$subrow->id}},{{$subrow->status}});">
                                    <i class="fas fa-check fa-2x text-muted" style="opacity: 0.2;"></i>
                                </a>
                                <?php }else{ ?>
                                <a class="align-items-center" data-toggle="popover" data-trigger="hover" title=""
                                    data-content="Complete" data-html="true" data-original-title="" style="float:left;"
                                    data-placement="top" href="javascript:;"
                                    onclick="taskStatus({{$subrow->id}},{{$subrow->status}});">
                                    <i class="fas fa-check fa-2x  text-success"></i>
                                </a>

                                <?php } ?>
                            </td>
                            <td class="task-name-cell align-middle">

                                <?php if($subrow->task_due_on > date('Y-m-d')){?>
                                <a href="javascript:void(0);" onclick="loadTaskViewFromTask({{ $subrow->id }})"
                                    class="p-0 w-100 text-left  btn btn-link">{{ $subrow->task_title }}</a>
                                <?php }else{ ?>
                                <a href="javascript:void(0);" onclick="loadTaskViewFromTask({{ $subrow->id }})"
                                    class="p-0 w-100 text-left text-danger btn btn-link">{{ $subrow->task_title }}</a>

                                <?php } ?>


                                <?php if($subrow->status=='1'){
                                $CommonController= new App\Http\Controllers\CommonController();
                                $OwnDate=$CommonController->convertUTCToUserTime($subrow->task_completed_date,Auth::User()->user_timezone);
                                            ?>
                                <small class="text-muted">Completed by {{$subrow->task_completed['first_name']}}
                                    {{$subrow->task_completed['last_name']}} on
                                    {{date('m/d/Y',strtotime($OwnDate))}}</small>
                                <?php } ?>
                            </td>
                            <td class="task-subtasks-cell align-middle">
                                <div>{{$subrow->checklist_counter}}</div>
                            </td>
                            <td class="task-priority-cell align-middle text-nowrap">
                                <?php if($subrow->task_priority == "1"){?>
                                <i class="fas fa-circle fa-sm  mr-1 text-black-50"></i>Low
                                <?php }else if($subrow->task_priority == "2"){?>
                                <i class="fas fa-circle fa-sm mr-1 text-secondary-task"></i>Medium
                                <?php }else if($subrow->task_priority == "3") {?>
                                <i class="fas fa-circle fa-sm mr-1 text-warning"></i>High
                                <?php }else{ ?>
                                <div></div>
                                <?php } ?>
                            </td>
                            <td class="task-due-cell align-middle">
                                <?php if($subrow->task_due_on > date('Y-m-d')){
                                                if($subrow->task_due_on!='9999-12-30'){?>
                                <span class="font-weight-bold">{{date('M j, Y',strtotime($subrow->task_due_on))}}</span>
                                <?php } ?>
                                <?php }else{ ?>
                                <span
                                    class="font-weight-bold text-danger">{{date('M j, Y',strtotime($subrow->task_due_on))}}</span>

                                <?php } ?>
                            </td>
                            <td class="task-case-lead-cell align-middle">
                                <?php
                                            if(isset($subrow->case_name)){?>
                                <a href="{{BASE_URL}}court_cases/{{$subrow->case_name['case_unique_number']}}/info">{{$subrow->case_name['case_title']}}
                                </a>
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
                                <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                                    href="javascript:;" data-toggle="popover" data-trigger="hover" title=""
                                    data-content="{{$userListHtml}}" data-html="true" data-original-title=""
                                    style="float:left;">{{count($subrow->task_user)}} Users</a>
                                <?php 
                                                }else{
                                                    if(isset($subrow->task_user[0])){?>
                                <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                                    href="{{BASE_URL}}contacts/attorneys/{{base64_encode($subrow->task_user[0]->id)}}">{{substr($subrow->task_user[0]->first_name,0,15)}}
                                    {{substr($subrow->task_user[0]->last_name,0,15)}}</a>
                                <?php }
                                                }
                                            }else{ 
                                                ?> <i class="table-cell-placeholder mt-3"></i>
                                <?php
                                            }
                                            ?>

                            </td>
                            <td class="task-actions-cell align-middle">
                                <div class="actions-cell float-right">
                                    <div class="d-flex align-item-center task-action-buttons-16333660">
                                        <div>

                                            <a class="align-items-center" data-toggle="modal"
                                                data-target="#loadTimeEntryPopup" data-placement="bottom"
                                                href="javascript:;" onclick="loadTimeEntryPopup({{$subrow->id}});">
                                                <span data-toggle="popover" data-trigger="hover" title=""
                                                    data-content="Add time entry" data-placement="top" data-html="true">
                                                    <i class="fas fa-clock pr-3 align-middle"></i></span>
                                            </a>
                                        </div>
                                        <div>

                                            <a class="align-items-center" data-toggle="modal"
                                                data-target="#loadReminderPopupIndex" data-placement="bottom"
                                                href="javascript:;" onclick="loadReminderPopupIndex({{$subrow->id}});">
                                                <span data-toggle="popover" data-trigger="hover" title=""
                                                    data-content="Reminder" data-placement="top" data-html="true"> <i
                                                        class="fas fa-bell pr-3 align-middle"></i> </span>
                                            </a>
                                        </div>
                                        <div>
                                            <a class="align-items-center" data-toggle="modal" data-target="#editTask"
                                                data-placement="bottom" href="javascript:;"
                                                onclick="editTask({{$subrow->id}});">
                                                <span data-toggle="popover" data-trigger="hover" title=""
                                                    data-content="Edit" data-placement="top" data-html="true"> <i
                                                        class="fas fa-pen pr-3  align-middle"></i> </span></a>
                                        </div>
                                        <div>
                                            <a class="align-items-center" data-original-title="" data-toggle="modal"
                                                data-target="#deleteTask" data-placement="bottom"
                                                onclick="deleteTaskFunction({{$subrow->id}});" href="javascript:;">
                                                <span data-toggle="popover" data-trigger="hover" title=""
                                                    data-content="Delete" data-placement="top" data-html="true"><i
                                                        class="fas fa-trash pr-3  align-middle"></i> </span></a>

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach

                        <?php 
                                    if($task->isEmpty()){
                                    ?>
                        <tr>
                            <td colspan="10" class="text-center"> No task available </td>
                        </tr>
                        <?php 
                                    }?>
                    </table>
                    {!! $task->links() !!}
                </div>
                <?php

                }else{
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
                    <table class="display table table-striped table-bordered" id="employee-grid" style="width:100%">
                        <thead>

                            <tr>
                                <th class="text-center" style="cursor: initial;">
                                    <label class="sr-only ">Select all rows</label>
                                    <input type="checkbox" class="mx-1" name="all" id="checkall">
                                </th>
                                <?php if($orderby=="asc" && $orderon=="status"){
                                                $norder="desc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-up fa-fw icon-fw icon"></i>';
                                            }else if($orderby=="desc" && $orderon=="status"){
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-down fa-fw icon-fw icon"></i>';
                                            }else{
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-sort fa-fw icon-sort icon-fw icon"></i>';
                                            }?>

                                <th class="task-status-cell align-middle" style="cursor: pointer;"
                                    onclick='sortTable("status","<?php echo $norder;?>");'>STATUS
                                    <?php echo $icon;?>
                                </th>
                                <?php if($orderby=="asc" && $orderon=="task_title"){
                                                $norder="desc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-down fa-fw icon-fw icon"></i>';
                                            }else if($orderby=="desc" && $orderon=="task_title"){
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-up fa-fw icon-fw icon"></i>';
                                            }else{
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-sort fa-fw icon-sort icon-fw icon"></i>';
                                            }?>
                                <th class="task-name-cell align-middle" style="cursor: pointer;"
                                    onclick='sortTable("task_title","<?php echo $norder;?>");'>
                                    TASK NAME <?php echo $icon;?>

                                </th>
                                <th class="task-subtasks-cell align-middle " style="cursor: initial;">SUBTASKS</th>

                                <?php if($orderby=="asc" && $orderon=="task_priority"){
                                                $norder="desc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-down fa-fw icon-fw icon"></i>';
                                            }else if($orderby=="desc" && $orderon=="task_priority"){
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-up fa-fw icon-fw icon"></i>';
                                            }else{
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-sort fa-fw icon-sort icon-fw icon"></i>';
                                            }?>
                                <th class="task-priority-cell align-middle text-nowrap YXd6tPOgoO-RylXVRzzZh"
                                    style="cursor: pointer;"
                                    onclick='sortTable("task_priority","<?php echo $norder;?>");'>PRIORITY
                                    <?php echo $icon;?></th>

                                <?php if($orderby=="asc" && $orderon=="task_due_on"){
                                                $norder="desc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-down fa-fw icon-fw icon"></i>';
                                            }else if($orderby=="desc" && $orderon=="task_due_on"){
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-up fa-fw icon-fw icon"></i>';
                                            }else{
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-sort fa-fw icon-sort icon-fw icon"></i>';
                                            }?>
                                <th class="task-due-cell align-middle" style="cursor: pointer;"
                                    onclick='sortTable("task_due_on","<?php echo $norder;?>");'> DUE
                                    <?php echo $icon;?>
                                </th>
                                <?php if($orderby=="asc" && $orderon=="case_title"){
                                                $norder="desc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-down fa-fw icon-fw icon"></i>';
                                            }else if($orderby=="desc" && $orderon=="case_title"){
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-caret-up fa-fw icon-fw icon"></i>';
                                            }else{
                                                $norder="asc";
                                                $icon='<i aria-hidden="true" class="fa fa-sort fa-fw icon-sort icon-fw icon"></i>';
                                            }?>
                                <th class="task-case-lead-cell align-middle" style="cursor: pointer;"
                                    onclick='sortTable("case_title","<?php echo $norder;?>");'>
                                    CASE/LEAD <?php echo $icon;?>
                                </th>
                                <th class="task-assigned-to-cell align-middle YXd6tPOgoO-RylXVRzzZh"
                                    style="cursor: initial;">
                                    ASSIGNED TO
                                </th>
                                <th class="task-actions-cell align-middle YXd6tPOgoO-RylXVRzzZh"
                                    style="cursor: initial;"></th>
                            </tr>
                        </thead>
                       
                        @foreach($result as $key=>$row)

                        <?php if($key=='9999-12-30'){?>
                        <tr class="row-group-header table-active" role="button">
                            <td class="text-center"></td>
                            <td colspan="7"><strong>No due date</strong></td>
                            <td class="text-right">
                            <a class="" id="{{$key}}" onclick="hideShow('{{$key}}')"  data-toggle="collapse" href="javascript:void(0);" data-target="#accordion-item-icons-{{$key}}" aria-expanded="true">
                                <i class="fas fa-sort-down align-text-top"></i></a>
                            </td>
                        </tr>
                        <?php }else if($key < date('Y-m-d')){
                                    ?>
                        <tr class="row-group-header table-danger" role="button">
                            <td class="text-center"></td>
                            <td colspan="7"><strong>Overdue</strong></td>
                            <td class="text-right">
                                <a class="" id="{{$key}}"  onclick="hideShow('{{$key}}')"  data-toggle="collapse" href="javascript:void(0);" data-target="#accordion-item-icons-{{$key}}" aria-expanded="true">
                                    <i class="fas fa-sort-down align-text-top"></i></a>
                                </td>
                        </tr>
                        <?php } else {?>
                        <tr class="row-group-header table-secondary-task" role="button">
                            <td class="text-center"></td>
                            <td colspan="7">
                                <?php
                                $controllerLoad= new App\Http\Controllers\CommonController();
                    ?>
                                <div><strong>Due {{date('M j, Y',strtotime($key))}}
                                    </strong>&nbsp;<small class="text-muted">- in {{$controllerLoad->daysReturns($key)}} </small>
                                </div>
                            </td>
                            <td class="text-right">
                                <a class="" id="{{$key}}"   onclick="hideShow('{{$key}}')"  data-toggle="collapse" href="javascript:void(0);" data-target="#accordion-item-icons-{{$key}}" aria-expanded="true">
                                    <i class="fas fa-sort-down align-text-top"></i></a>
                                </td>
                        </tr>
                        <?php } ?>


                        @foreach($row as $subrow)
                        <tr class="collapse show"  id="accordion-item-icons-{{$key}}">
                            <td class="text-center">
                                <label for="select-row-74" class="sr-only ">Select row</label>
                                <input id="select-row-74" type="checkbox" value="{{$subrow->id}}" class="task_checkbox"
                                    name="selectedTask[{{$subrow->id}}]">
                            </td>
                            <td class="task-status-cell align-middle">
                                <?php if($subrow->status=='0'){?>
                                <a class="align-items-center" data-toggle="popover" data-trigger="hover" title=""
                                    data-content="Incomplete" data-placement="top" data-html="true"
                                    data-original-title="" style="float:left;" href="javascript:;"
                                    onclick="taskStatus({{$subrow->id}},{{$subrow->status}});">
                                    <i class="fas fa-check fa-2x text-muted" style="opacity: 0.2;"></i>
                                </a>
                                <?php }else{ ?>
                                <a class="align-items-center" data-toggle="popover" data-trigger="hover" title=""
                                    data-content="Complete" data-html="true" data-original-title="" style="float:left;"
                                    data-placement="top" href="javascript:;"
                                    onclick="taskStatus({{$subrow->id}},{{$subrow->status}});">
                                    <i class="fas fa-check fa-2x  text-success"></i>
                                </a>

                                <?php } ?>
                            </td>
                            <td class="task-name-cell align-middle">

                                <?php if($subrow->task_due_on > date('Y-m-d')){?>
                                <a  href="javascript:void(0);" onclick="loadTaskViewFromTask({{ $subrow->id }})"
                                    class="p-0 w-100 text-left  btn btn-link">{{ $subrow->task_title }}</a>
                                <?php }else{ ?>
                                <a  href="javascript:void(0);" onclick="loadTaskViewFromTask({{ $subrow->id }})"
                                    class="p-0 w-100 text-left text-danger btn btn-link">{{ $subrow->task_title }}</a>

                                <?php } ?>


                                <?php if($subrow->status=='1'){
                                                $CommonController= new App\Http\Controllers\CommonController();
                                                $OwnDate=$CommonController->convertUTCToUserTime($subrow->task_completed_date,Auth::User()->user_timezone);
                                            ?>
                                <small class="text-muted">Completed by {{$subrow->task_completed['first_name']}}
                                    {{$subrow->task_completed['last_name']}} on
                                    {{date('m/d/Y',strtotime($OwnDate))}}</small>
                                <?php } ?>
                            </td>
                            <td class="task-subtasks-cell align-middle">
                                <div>{{$subrow->checklist_counter}}</div>
                            </td>
                            <td class="task-priority-cell align-middle text-nowrap">
                                <?php if($subrow->task_priority == "1"){?>
                                <i class="fas fa-circle fa-sm  mr-1 text-black-50"></i>Low
                                <?php }else if($subrow->task_priority == "2"){?>
                                <i class="fas fa-circle fa-sm mr-1 text-secondary-task"></i>Medium
                                <?php }else if($subrow->task_priority == "3") {?>
                                <i class="fas fa-circle fa-sm mr-1 text-warning"></i>High
                                <?php }else{ ?>
                                <div></div>
                                <?php } ?>
                            </td>
                            <td class="task-due-cell align-middle">
                                <?php if($subrow->task_due_on > date('Y-m-d')){
                                                if($subrow->task_due_on!='9999-12-30'){?>
                                <span class="font-weight-bold">{{date('M j, Y',strtotime($subrow->task_due_on))}}</span>
                                <?php } ?>
                                <?php }else{ ?>
                                <span
                                    class="font-weight-bold text-danger">{{date('M j, Y',strtotime($subrow->task_due_on))}}</span>

                                <?php } ?>
                            </td>
                            <td class="task-case-lead-cell align-middle">
                                <?php if(isset($subrow->case_name)){?>
                                        <a href="{{BASE_URL}}court_cases/{{$subrow->case_name['case_unique_number']}}/info">       {{$subrow->case_name['case_title']}}
                                        </a>
                                <?php }else if($subrow->lead_id){
                                    ?><a href="{{BASE_URL}}leads/{{$subrow->lead_id}}/lead_details/info">
                                    {{$subrow->lead_name['first_name']}} {{$subrow->lead_name['last_name']}}
                                        </a><?php 
                                } ?>
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
                                <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                                    href="javascript:;" data-toggle="popover" data-trigger="hover" title=""
                                    data-content="{{$userListHtml}}" data-html="true" data-original-title=""
                                    style="float:left;">{{count($subrow->task_user)}} Users</a>
                                <?php 
                                                }else{
                                                    if(isset($subrow->task_user[0])){?>
                                <a class="mt-3 event-name d-flex align-items-center" tabindex="0" role="button"
                                    href="{{BASE_URL}}contacts/attorneys/{{base64_encode($subrow->task_user[0]->id)}}">{{substr($subrow->task_user[0]->first_name,0,15)}}
                                    {{substr($subrow->task_user[0]->last_name,0,15)}}</a>
                                <?php }
                                                }
                                            }else{ 
                                                ?> <i class="table-cell-placeholder mt-3"></i>
                                <?php
                                            }
                                            ?>

                            </td>
                            <td class="task-actions-cell align-middle">
                                <div class="actions-cell float-right">
                                    <div class="d-flex align-item-center task-action-buttons-16333660">
                                        <div>

                                            <a class="align-items-center" data-toggle="modal"
                                                data-target="#loadTimeEntryPopup" data-placement="bottom"
                                                href="javascript:;" onclick="loadTimeEntryPopup({{$subrow->id}});">
                                                <span data-toggle="popover" data-trigger="hover" title=""
                                                    data-content="Add time entry" data-placement="top" data-html="true">
                                                    <i class="fas fa-clock pr-3 align-middle"></i></span>
                                            </a>
                                        </div>
                                        <div>

                                            <a class="align-items-center" data-toggle="modal"
                                                data-target="#loadReminderPopupIndex" data-placement="bottom"
                                                href="javascript:;" onclick="loadReminderPopupIndex({{$subrow->id}});">
                                                <span data-toggle="popover" data-trigger="hover" title=""
                                                    data-content="Reminder" data-placement="top" data-html="true"> <i
                                                        class="fas fa-bell pr-3 align-middle"></i> </span>
                                            </a>
                                        </div>
                                        <div>
                                            <a class="align-items-center" data-toggle="modal" data-target="#editTask"
                                                data-placement="bottom" href="javascript:;"
                                                onclick="editTask({{$subrow->id}});">
                                                <span data-toggle="popover" data-trigger="hover" title=""
                                                    data-content="Edit" data-placement="top" data-html="true"> <i
                                                        class="fas fa-pen pr-3  align-middle"></i> </span></a>
                                        </div>
                                        <div>
                                            <a class="align-items-center" data-original-title="" data-toggle="modal"
                                                data-target="#deleteTask" data-placement="bottom"
                                                onclick="deleteTaskFunction({{$subrow->id}});" href="javascript:;">
                                                <span data-toggle="popover" data-trigger="hover" title=""
                                                    data-content="Delete" data-placement="top" data-html="true"><i
                                                        class="fas fa-trash pr-3  align-middle"></i> </span></a>

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach

                        <?php 
                                    if(empty($result)){
                                    ?>
                        <tr>
                            <td colspan="10" class="text-center"> No task available </td>
                        </tr>
                        <?php 
                                    }?>
                    </table>
                    {!! $task->links() !!}
                </div>

                <?php 
                }
                ?>
            </div>
        </div>
    </div>
    <aside  id="taskViewArea" class="task-details-drawer" style="">
    </aside>
</div>

<div id="loadAddTaskPopup" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Task</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="addTaskArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>

<div id="deleteTask" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Confirmation</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="eventID">
                        <form class="deleteTaskForm" id="deleteTaskForm" name="deleteTaskForm" method="POST">
                            <div id="showError2" style="display:none"></div>
                            @csrf
                            <input class="form-control" id="task_id" value="" name="task_id" type="hidden">
                            <div class=" col-md-12">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label">
                                        Are you sure you want to delete this task?
                                        <input type="radio" style="display:none;" name="delete_event_type"
                                            checked="checked" class="pick-option mr-2" value="SINGLE_EVENT">
                                    </label>
                                </div>
                                <div class="form-group row float-right">
                                    <a href="#">
                                        <button class="btn btn-secondary  m-1" type="button"
                                            data-dismiss="modal">Cancel</button>
                                    </a>
                                    <button class="btn btn-primary ladda-button example-button m-1" id="submit"
                                        type="submit">
                                        <span class="ladda-label">Yes, Delete</span>
                                    </button>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                    <div class="col-md-2 form-group mb-3">
                                        <div class="loader-bubble loader-bubble-primary" id="innerLoader1"
                                            style="display: none;"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="editTask" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Task</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="editTaskArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>

<div id="AddContactModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="step-1-again">
                        </div>
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
                <h5 class="modal-title" id="deleteSingle">Set Task Reminders</h5>
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

<div id="loadTimeEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Time Entry</h5>
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

<div id="changeDueDate" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Change Due Date</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="eventID">
                        <form class="dueDateChange" id="dueDateChange" name="dueDateChange" method="POST">
                            <div id="showError2" style="display:none"></div>
                            @csrf
                            <div class="col-md-12 form-group mb-3">
                                <label for="firstName1">Change due date to:</label>
                                <input type="text" class="form-control" name="duedate" id="duedate">

                            </div>
                            <div class=" col-md-12">
                                <div class="form-group row float-right">
                                    <a href="#">
                                        <button class="btn btn-secondary  m-1" type="button"
                                            data-dismiss="modal">Cancel</button>
                                    </a>
                                    <button class="btn btn-primary ladda-button example-button m-1" id="submit"
                                        type="submit">
                                        <span class="ladda-label">Update</span>
                                    </button>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                    <div class="col-md-2 form-group mb-3">
                                        <div class="loader-bubble loader-bubble-primary" id="innerLoader1"
                                            style="display: none;"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-------------------------------->


<div id="loadTimeEntryPopupInView" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="addTimeEntryInView">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="loadReminderPopupIndexInView" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
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

<div id="editTaskInView" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Task</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="editTaskAreaInView">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>

@include('commonPopup.add_case')
<style>
    .modal {
        overflow: auto !important;
    }
    .afterLoadClass{
        position: absolute; top: 0px; width: 560px; right: 0px; background-color: white; height: 100%; display: inline-table; box-shadow: rgba(0, 0, 0, 0.5) 1px 0px 7px; z-index: 100; min-height: 850px;
    }

</style>

@section('page-js')
<script type="text/javascript">"use strict";

    $(document).ready(function () {
        $('.dropdown-toggle').dropdown();  
        $('[data-toggle="tooltip"]').tooltip();
     
      $('#manual').on('click', function () {
        $(this).tooltip('toggle');
      });
    });
    $(document).ready(function () {
        $("#user_type_assign_to").select2({
            placeholder: "Search for an existing contact or company",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#AddCaseModelUpdate"),
        });
        $(".select2").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
        });
        $('#duedate').datepicker({

            onSelect: function (dateText, inst) {
                $("#addMoreReminder").show();
            },
            showOn: 'focus',
            showButtonPanel: true,
            closeText: 'Clear', // Text to show for "close" button
            onClose: function () {
                var event = arguments.callee.caller.caller.arguments[0];
                // If "Clear" gets clicked, then really clear it
                if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
                    $(this).val('');
                    $("#addMoreReminder").hide();
                }
            }
        });
        $('#daterange').daterangepicker({
            locale: { 
                cancelLabel: 'Clear',
                applyLabel: 'Select' 
            },ranges: {
                'All Days': [moment().subtract(10, 'years'), moment()],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'Last 90 Days': [moment().subtract(89, 'days'), moment()],
                'Month to date': [moment().startOf('month').format('MM/DD/YYYY'), moment()],
                'Year to date': [moment().startOf('year').format('MM/DD/YYYY'), moment()]
            },
            "showCustomRangeLabel": false,
            "alwaysShowCalendars": true,
            "autoUpdateInput": true,
            "opens": "center",
        }, function (start, end, label) {
            $("#filter_type").val(label);
        });
        <?php if (isset($_GET['filter_type']) && $_GET['filter_type'] == '' || !isset($_GET['filter_type'])) { ?>
            $('input[name="daterange"]').val(''); 
        <?php } ?>
        $('#checkall').on('change', function () {
            $('.task_checkbox').prop('checked', $(this).prop("checked"));
            if ($(this).prop("checked") == true) {
                $('.task_checkbox').parent().parent().addClass('table-info');

            } else {
                $('.task_checkbox').parent().parent().removeClass('table-info');
            }
            if ($('.task_checkbox:checked').length == "0") {
                $('#actionbutton').attr('disabled', 'disabled');

            } else {
                $('#actionbutton').removeAttr('disabled');

            }
        });
        //deselect "checked all", if one of the listed checkbox product is unchecked amd select "checked all" if all of the listed checkbox product is checked
        $('.task_checkbox').change(function () { //".checkbox" change 
            if ($('.task_checkbox:checked').length == $('.task_checkbox').length) {
                $('#checkall').prop('checked', true);


            } else {
                $('#checkall').prop('checked', false);


                // $('#actionbutton').attr('data-toggle', '');

            }
            if ($('.task_checkbox:checked').length == "0") {
                $('#actionbutton').attr('disabled', 'disabled');

            } else {
                $('#actionbutton').removeAttr('disabled');

            }
        });
        $('#actionbutton').attr('disabled', 'disabled');

        // $('button').attr('disabled', false);
        var showChar = 110; // How many characters are shown by default
        var ellipsestext = "...";
        var moretext = "Show more";
        var lesstext = "Show less";

        $('#AddCaseModel').on('hidden.bs.modal', function () {
            // dataTable.ajax.reload();
            $("#preloader").show();
            window.location.reload();

        });

        $('#AddContactModal').on('hidden.bs.modal', function () {
            //loadStep1();
            $("#preloader").show();
            $('#AddCaseModel').modal('show');
            //  $('#AddCaseModel').modal('hide');   

        });

        $('#editTask,#loadAddTaskPopup').on('hidden.bs.modal', function () {
            $("#preloader").show();
            window.location.reload();

        });
        $('#changeStatus,#statusUpdate,#EditCaseModel').on('hidden.bs.modal', function () {
            $("#preloader").show();

            dataTable.ajax.reload(null, false);
            setTimeout(function () {
                $('.more').each(function () {
                    var content = $(this).html();
                    if (content.length > showChar) {
                        var c = content.substr(0, showChar);
                        var h = content.substr(showChar, content.length - showChar);
                        var html = c + '<span class="moreellipses">' + ellipsestext +
                            '&nbsp;</span><span class="morecontent"><span>' + h +
                            '</span>&nbsp;&nbsp;<a href="" class="morelink">' +
                            moretext + '</a></span>';
                        $(this).html(html);
                    }
                });
                $(".morelink").click(function () {
                    if ($(this).hasClass("less")) {
                        $(this).removeClass("less");
                        $(this).html(moretext);
                    } else {
                        $(this).addClass("less");
                        $(this).html(lesstext);
                    }
                    $(this).parent().prev().toggle();
                    $(this).prev().toggle();
                    return false;
                });
                $("#preloader").hide();

            }, 1000);
        });
        $("#dueDateChange").validate({
            rules: {
                duedate: {
                    required: true
                }
            },
            messages: {

                duedate: {
                    required: "Due date cannot be empty"
                }
            }
        });

        $('#dueDateChange').submit(function (e) {
            e.preventDefault();
            $("#innerLoader1").css('display', 'block');
            if (!$('#dueDateChange').valid()) {
                $("#innerLoader1").css('display', 'none');
                return false;
            }

            var dataString = $("#dueDateChange").serialize();
            var array = [];
            $("input[class=task_checkbox]:checked").each(function (i) {
                array.push($(this).val());
            });
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/changeDueDate", // json datasource
                data: dataString + '&task_id=' + JSON.stringify(array),
                success: function (res) {
                    $("#innerLoader1").css('display', 'block');
                    if (res.errors != '') {
                        $('#showError2').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('#showError2').append(errotHtml);
                        $('#showError2').show();
                        $("#innerLoader1").css('display', 'none');
                        return false;
                    } else {
                        window.location.reload();

                    }
                }
            });
        });
    });

    function loadAddTaskPopup(id) {
        $("#preloader").show();
        $("#addTaskArea").html('');
        $("#addTaskArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadAddTaskPopup", // json datasource
                data: {
                    "user_id": id
                },
                success: function (res) {
                    $("#addTaskArea").html('');
                    $("#addTaskArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function editTask(id) {
        $("#preloader").show();
        $("#editTaskArea").html('');
        $("#editTaskArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadEditTaskPopup", // json datasource
                data: {
                    "task_id": id
                },
                success: function (res) {
                    $("#editTaskArea").html('');
                    $("#editTaskArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function changeStatus(id) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/loadStatus", // json datasource
            data: {
                "case_id": id
            },
            success: function (res) {
                $("#statusLoad").html(res);
                $("#preloader").hide();
            }
        })
    }

    function loadCaseUpdate(id) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/loadCaseUpdate", // json datasource
            data: {
                "case_id": id
            },
            success: function (res) {
                $("#updateLoad").html(res);
                $("#preloader").hide();
            }
        })
    }


    function updateCaseDetails(id) {

        $("#preloader").show();
        $("#step-edit-1").html('');
        $("#step-edit-1").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/case/editCase", // json datasource
                data: {
                    "case_id": id
                },
                success: function (res) {
                    $("#step-edit-1").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }


    function AddContactModal() {
        $("#innerLoader").css('display', 'none');
        $("#preloader").show();
        $("#step-1-again").html('');
        $("#step-1-again").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/loadAddContactFromCase", // json datasource
                data: 'loadStep1',
                success: function (res) {
                    //$('#AddCaseModel').modal('hide').remove();
                    // $('#AddCaseModel').data('modal', null);
                    //   $('#AddCaseModel').modal('hide'); 
                    $("#step-1-again").html(res);
                    $("#preloader").hide();
                    $("#innerLoader").css('display', 'none');

                    return false;
                }
            })
        })
    }

    function deleteTaskFunction(id) {
        $("#task_id").val(id);
    }

    $('#deleteTaskForm').submit(function (e) {

        $("#innerLoader1").css('display', 'block');
        e.preventDefault();
        var dataString = $("#deleteTaskForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/deleteTask", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader1").css('display', 'block');
                if (res.errors != '') {
                    $('#showError2').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError2').append(errotHtml);
                    $('#showError2').show();
                    $("#innerLoader1").css('display', 'none');
                    return false;
                } else {
                    window.location.reload();
                }
            }
        });

        $('.collapsed').click(function() { 
           
            
        });
    });

    function hideShow(id){
        $("#"+id).find('i').toggleClass('fa-sort-up align-bottom').toggleClass('fa-sort-down align-text-top'); 
    }

    function taskStatus(id, status) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/taskStatus", // json datasource
            data: {
                "task_id": id,
                "status": status
            },
            success: function (res) {
                window.location.reload();

            }
        })
    }

    function loadReminderPopupIndex(task_id) {
        $("#reminderDataIndex").html('<img src="{{LOADER}}""> Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadTaskReminderPopupIndex", // json datasource
                data: {
                    "task_id": task_id
                },
                success: function (res) {
                    $("#reminderDataIndex").html('<img src="{{LOADER}}""> Loading...');
                    $("#reminderDataIndex").html(res);
                    $("#preloader").hide();

                }
            })
        })
    }

    function loadTimeEntryPopup(id) {
        $("#preloader").show();
        $("#addTimeEntry").html('');
        $("#addTimeEntry").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadTimeEntryPopup", // json datasource
                data: {
                    "task_id": id
                },
                success: function (res) {
                    $("#addTimeEntry").html('');
                    $("#addTimeEntry").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function sortTable(sorton, sorttype) {
        $("#sort").val(sorttype);
        $("#sort_on").val(sorton);
        $("#filterBy").submit();
    }

    function getCheckedUser() {
        var array = [];
        $("input[class=task_checkbox]:checked").each(function (i) {
            array.push($(this).val());
        });
        if (array.length === 0) {} else {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/bulkMarkAsRead", // json datasource
                data: {
                    "task_id": JSON.stringify(array)
                },
                success: function (res) {
                    window.location.reload();
                }
            })
        }
    }

    function markasCompleted() {
        var array = [];
        $("input[class=task_checkbox]:checked").each(function (i) {
            array.push($(this).val());
        });
        if (array.length === 0) {} else {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/markAsCompleted", // json datasource
                data: {
                    "task_id": JSON.stringify(array)
                },
                success: function (res) {
                    window.location.reload();
                }
            })
        }
    }

    function changeDueDate() {
        $("#reminderDataIndex").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadTaskReminderPopupIndex", // json datasource
                data: {
                    "task_id": task_id
                },
                success: function (res) {
                    $("#reminderDataIndex").html('Loading...');
                    $("#reminderDataIndex").html(res);
                    $("#preloader").hide();

                }
            })
        })
    }
    function loadTaskViewFromTask(task_id) {
        $(".task-details-drawer").fadeIn();
        $("#taskViewArea").html('Loading...');
        $("#preloader").show();
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadTaskDetailPage", // json datasource
                data: { "task_id": task_id},
                success: function (res) {
                    $("#taskViewArea").html('Loading...');
                    $("#taskViewArea").html(res);
                    $("#preloader").hide();

                }
            })
    }
    $(".task-details-drawer").fadeOut();
    $('#checkall').prop('checked', "");
    $('.task_checkbox').prop('checked', "");
    
    /***********************************************/

    <?php
    if(Session::get('task_id')!=""){
        ?>
        loadTaskViewFromTask({{Session::get('task_id')}}); 
        <?php
        Session::put('task_id', "");
    } 
    ?>
    <?php
    if(isset($_REQUEST['id'])){
        ?>loadTaskViewFromTask({{$_REQUEST['id']}});<?php
    }
    ?>
     

    function loadTimeEntryPopupInView(id) {
        $("#preloader").show();
        $("#addTimeEntryInView").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadTimeEntryPopup", // json datasource
                data: {
                    "task_id": id,
                    "from_view":"yes"
                },
                success: function (res) {
                    $("#addTimeEntryInView").html('');
                    $("#addTimeEntryInView").html(res);
                    $("#preloader").hide();
                    
                }
            })
        })
    }

    function loadReminderPopupIndexInView(task_id) {
        $("#reminderDataIndexInView").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadTaskReminderPopupIndex", // json datasource
                data: {
                    "task_id": task_id,
                    "from_view":"yes"
                },
                success: function (res) {
                    $("#reminderDataIndexInView").html('Loading...');
                    $("#reminderDataIndexInView").html(res);
                    $("#preloader").hide();

                }
            })
        })
    }

    function editTaskInView(id) {
        $("#preloader").show();
        $("#editTaskAreaInView").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadEditTaskPopup", // json datasource
                data: {
                    "task_id": id,
                    "from_view":"yes"
                },
                success: function (res) {
                    $("#editTaskAreaInView").html('');
                    $("#editTaskAreaInView").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    setTimeout(function(){  
        $('#taskViewArea').addClass('afterLoadClass'); 
    }, 500);

    function loadCaseDropdown(){
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/loadCaseList", // json datasource
            data: {'case_id':localStorage.getItem("case_id")},
            success: function (res) {
                $("#case_or_lead").html(res);
            }
        })
   }
    
</script>
@stop

@endsection