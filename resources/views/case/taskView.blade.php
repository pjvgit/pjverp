<?php
$controllerLoad = new App\Http\Controllers\CommonController();
?>
<div class="d-flex align-items-center p-3">
    <a class="btn btn-sm btn-outline-secondary btn-rounded " class="close" type="button" onclick="backTocase();" aria-label="Close">
        <i class="fas fa-arrow-left"></i>
        <span class="sr-only">Back</span>
    </a>
    <div class="ml-auto d-flex align-items-center flex-row-reverse">
        @can('billing_add_edit')
          <a id="add-time-entry-task-details" class="btn btn-rounded btn-sm btn-outline-primary ml-1" data-toggle="modal" data-target="#loadTimeEntryPopup" onclick="loadTimeEntryPopupByCaseWithoutRefreshTask({{$TaskData->case_id}});">
            <span class="time-entry-button">Add Time Entry</span>
          </a>
        @endcan
          <a class="btn btn-sm btn-rounded btn-outline-secondary edit-task-button ml-1 task-form-link" data-toggle="modal"  data-target="#editTask"
                                                data-placement="bottom" href="javascript:;"
                                                onclick="editTask({{$TaskData->id}});">
            Edit
        </a>
        @can('case_add_edit','delete_items')
        <a class="btn btn-sm  btn-rounded btn-outline-danger" data-toggle="modal" data-target="#deleteTask" data-placement="bottom" onclick="deleteTaskFunction({{$TaskData->id}});" >Delete</a>
        @endcan
    </div>
</div>
<div class="row p-2 m-0">
    <div class="col-6">
        <div class="test-task-details-name">
        <h3 class="font-weight-bold mb-0">
            <i class="fas h3 fa-clipboard-check"></i>
            {{$TaskData->task_title}}
        </h3>
        </div>
        <table class="table table-bordered mt-3" id="taskReviewArea">
            <tbody>
                <tr class="border-bottom-0 task-checklist-background-incomplete <?php if($TaskData->status=="0"){?>  table-info <?php } else{  ?>table-success<?php } ?>">
                    <td class="align-middle border-bottom-0 border-right-0 text-center task-checkbox-column">
                        <div class="task-completed" data-task-completion-status="complete"></div>
                        <input type="checkbox" <?php if($TaskData->status=="1"){ echo "checked=checked";}?> name="complete_19667270" id="complete_19667270" onclick="markAsCompleteTask({{$TaskData->id}});" class="form-control cursor-pointer" style="width:20px;" value="1" >
                    </td>

                    <td class="border-bottom-0 border-left-0">
                    <?php if($TaskData->status=="1"){?>
                        <div id="task_details_complete" class="align-middle lead">Completed on {{date('M d,Y',strtotime($TaskCompletedBy['task_completed_date']))}} by <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($TaskCompletedBy['uid'])}}"> {{$TaskCompletedBy['completed_by_name']}} ({{$controllerLoad->getUserLevelText($TaskCompletedBy['user_type'])}})</a></div>
                    <?php }else{ ?>
                        <div id="task_details_complete"  class="align-middle lead">Mark As Complete</div>
                    <?php } ?>
                    </td>
                </tr>
                <?php if (!$TaskChecklist->isEmpty()) { ?>
                <tr class="border-top-0 task-checklist-background-incomplete <?php if($TaskData->status=="0"){?>  table-info <?php } else{  ?>table-success<?php } ?>">
                    <td class="text-center border-top-0 border-right-0 task-checkbox-column float-left">
                    <?php $findComletedPErcent = ($TaskChecklistCompleted / count($TaskChecklist) * 100);?>

                        <span class="checklist-completion-percentage-details checklist-details-completion-percentage-19667270">{{number_format($findComletedPErcent)}}%</span>
                    </td>

                    <td class="border-top-0 border-left-0">
                        <div class="checklist-details-progress-bar-19667270 ui-progressbar ui-widget ui-widget-content ui-corner-all" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="ui-progressbar-value ui-widget-header ui-corner-left" style="width:{{$findComletedPErcent}}%;background-color:#5c9ccc;"></div></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if (!$TaskChecklist->isEmpty()) {?>
                    <div class="mb-3">
                        <ul class="list-group" id="checklistReloadArea">
                            <?php foreach ($TaskChecklist as $ckkey => $ckval) {
                                if ($ckval->status == "1") {?>
                                    <tr class="checklist-item-details">
                                        <td class="text-center task-checkbox-column">
                                            <input type="checkbox"  checked="checked" class="cursor-pointer form-control" style="width:20px;" name="complete_checklist_item_18768293" id="complete_checklist_item_18768293" value="1" onclick="updateCheckList({{$ckval->id}},{{$ckval->status}},{{$task_id}});"  style="">
                                        </td>
                                        <td class="checklist-item-name-details">
                                            <a href="javascript:void(0);" >
                                                {{$ckval->title}} tasdfats
                                            </a>
                                            <div>
                                            <small id="checklist_details_completed_by_18768388">Completed on {{date('M d,Y',strtotime($ckval->updated_at))}}  by <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($ckval->uid)}}">{{$ckval->first_name}} {{$ckval->last_name}} ({{$controllerLoad->getUserLevelText($ckval->user_type)}})</a></small>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } else {?>
                                    <tr class="checklist-item-details">
                                        <td class="text-center task-checkbox-column">
                                            <input type="checkbox"  class="cursor-pointer form-control" style="width:20px;" name="complete_checklist_item_18768293" id="complete_checklist_item_18768293" value="1" onclick="updateCheckList({{$ckval->id}},{{$ckval->status}},{{$task_id}});"  style="">
                                        </td>
                                        <td class="checklist-item-name-details">
                                            <a href="javascript:void(0);" onclick="updateCheckList({{$ckval->id}},{{$ckval->status}},{{$task_id}});" >
                                                {{$ckval->title}}
                                            </a>
                                            <div>
                                                <small id="checklist_details_completed_by_18768293"></small>
                                            </div>
                                        </td>
                                    </tr>
                                <?php }?>
                        <?php }?>
                        </ul>
                    </div>
                <?php }?>
            </tbody>
        </table>

        <table class="mt-3 table table-sm table-borderless">
            <tbody>
                <tr>
                    <th class="w-25">Due</th>
                    <td>
                        <span id="task-details-due-date" data-past-due-date="false" class="">
                        <?php if ($TaskData->task_due_on == '9999-12-30') { ?>
                            <div class="mb-1 ">No due date</div>
                        <?php } elseif ($TaskData->task_due_on >= date('Y-m-d') && $TaskData->task_due_on != '9999-12-30') {?>
                            <div class=" mb-1 ">
                                {{date('m/d/Y',strtotime($TaskData->task_due_on))}}</div>
                        <?php } else {?>
                            <div class=" mb-1 text-danger">
                                    {{date('m/d/Y',strtotime($TaskData->task_due_on))}}
                            </div>
                        <?php }
                        if ($TaskData->task_due_on >= date('Y-m-d') && $TaskData->task_due_on != '9999-12-30') {  ?>
                            <small class="text-muted"> in
                                {{ daysReturns($TaskData->task_due_on) }}</small>
                            <?php } else if ($TaskData->task_due_on != '9999-12-30') {?>
                            <small class="text-muted"> {{ daysReturns($TaskData->task_due_on) }}
                                ago</small>
                        <?php }?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th class="w-25">Description</th>
                    <td>
                    <?php if ($TaskData->description == '') {?>
                        <span class="text-muted">No Description</span>
                    <?php } else {?>
                            <span class="text-black-50">{{$TaskData->description}}</span>
                    <?php }?>
                    </td>
                </tr>
                <tr>
                    <th class="w-25">Priority</th>
                    <td>
                        <?php if ($TaskData->task_priority == "1") {?>
                        <div class="h4  mb-1 text-black-50">Low</div>
                        <?php } else if ($TaskData->task_priority == "2") {?>
                        <div class="h4  mb-1 text-secondary-task"">Medium</div>
                            <?php } else if ($TaskData->task_priority == "3") {?>
                                <div class=" h4 f mb-1 text-warning">High</div>
                        <?php } else {?>
                        <div class="mb-1 ">None</div>
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <th class="w-25">Case</th>
                    <td class="task-case-link">
                            <a href="{{BASE_URL}}court_cases/{{$CaseMasterData['case_unique_number']}}/info">{{$CaseMasterData['case_title']}}</a>
                    </td>
                </tr>
                <tr>
                    <th class="w-25">Created By</th>
                    <td> 
                        <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($TaskCreatedBy->uid)}}"  class="d-flex align-items-center user-link" title="{{$TaskCreatedBy->user_title}}">{{$TaskCreatedBy->created_by_name}} ({{$TaskCreatedBy->user_title}})</a>
                    </td>
                </tr>
                <tr>
                    <th class="w-25">Assigned To</th>
                    <td>
                    <?php
                        $counter = 0;
                        if (!$TaskAssignedTo->isEmpty()) {
                            foreach ($TaskAssignedTo as $k => $v) {?>
                                <div class="d-flex flex-row">
                                    <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->created_by)}}" class="d-flex align-items-center user-link" title="{{$v->user_title}}">{{$v->created_by_name}} ({{$v->user_title}})</a>
                                                        
                                </div>  
                        <?php } 
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th class="w-25">Reminders</th>
                    <td id="reminderAreaUpdate" data-reminder-id="19667270">
                        <?php if (!$TaskReminders->isEmpty()) {
                        foreach ($TaskReminders as $kr => $kv) {?>
                            <div>
                                <div><strong>{{ucfirst($kv->reminder_user_type)}}</strong> - {{ucfirst($kv->reminder_type)}} {{$kv->reminer_number}} day before due date.</div>
                            </div>
                        <?php }
                        } else { ?>
                            <div class="mb-3"><span class="text-black-50">None</span></div>
                        <?php } ?>
                    </td>
                   
                </tr>
                <tr> <th class="w-25"></th>
                    <td>
                        <a class=" ml-1 task-form-link" data-toggle="modal"  data-target="#loadReminderPopupIndexInViewOverlay" data-placement="bottom" href="javascript:;" onclick="loadReminderPopupIndexInCaseList({{$TaskData->id}});">
                        Edit Reminders
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-6">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item"><a class="nav-link active" id="home-basic-tab" data-toggle="tab"
                    href="#homeBasic" role="tab" aria-controls="homeBasic" aria-selected="true">Comments</a>
            </li>
            <li class="nav-item"><a class="nav-link" id="profile-basic-tab" data-toggle="tab"
                    href="#profileBasic" role="tab" aria-controls="profileBasic"
                    aria-selected="false">History</a></li>
        </ul>
        <div class="tab-content p-0" id="myTabContent">
            <div class="tab-pane fade show active" id="homeBasic" role="tabpanel"
                aria-labelledby="home-basic-tab">
                <div class="mt-2 pb-5">
                    @canany(['commenting_add_edit', 'commenting_view'])
                    <div>
                        <div id="loadComment"></div>
                        @can('commenting_add_edit')
                        <div class="w-100 mt-2">
                            <form class="addComment" id="addComment" name="addComment" method="POST">
                                @csrf
                                <input class="form-control" id="id" value="{{ $TaskData->id}}" name="task_id" type="hidden">

                            <div class="">
                                <div style="display: none;"></div>
                                <div class="ck ck-reset ck-editor ck-rounded-corners" role="application"
                                    dir="ltr"
                                    aria-labelledby="ck-editor__aria-label_ec2fb41ee79e213b7b154997c49e19645"
                                    lang="en">

                                    <div id="editor">

                                    </div>
                                    <div class="ck ck-editor__main" role="presentation">
                                        <div class="ck ck-content ck-editor__editable ck-rounded-corners ck-blurred ck-editor__editable_inline"
                                            role="textbox" aria-label="Rich Text Editor, main"
                                            contenteditable="true">
                                            <p><br data-cke-filler="true"></p>
                                        </div>
                                    </div>
                                </div>
                            </div><button type="submit" id="submit"
                                class="mt-1 float-right btn btn-primary">Post Comment</button>
                            </form>
                        </div>
                        @else
                            <div class="mt-2 alert alert-warning fade show" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="w-100">You do not have permission to add a comment for this task.</div>
                                </div>
                            </div>
                        @endcan
                    </div>
                    @else
                        <div class="mt-2 alert alert-warning fade show" role="alert">
                            <div class="d-flex align-items-start">
                                <div class="w-100">You do not have permission to view comments for this task.</div>
                            </div>
                        </div>
                    @endcanany
                </div>
            </div>
            <div class="tab-pane fade" id="profileBasic" role="tabpanel" aria-labelledby="profile-basic-tab">
                <ul class="list-group list-group-flush" id="list-group">

                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    body>#editor {
        margin: 50px auto;
        max-width: 720px;
    }

    #editor {
        height: 100px;
        background-color: white;
    }

</style>
<script type="text/javascript">
    $(document).ready(function () {
        $("[data-toggle=popover]").popover();

        $('[data-toggle="tooltip"]').tooltip();
                $('#closeView').on('click', function () {
            // window.location.reload();
            window.location.href=baseUrl+'/tasks';
            //$("#taskViewArea").fadeOut();

        });

        var toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'], // toggled buttons
            ['blockquote', 'code-block'],
            [{
                'header': 1
            }, {
                'header': 2
            }], // custom button values
            [{
                'list': 'ordered'
            }, {
                'list': 'bullet'
            }],

            [{
                'size': ['small', false, 'large', 'huge']
            }], // custom dropdown
            [{
                'header': [1, 2, 3, 4, 5, 6, false]
            }],

            [{
                'color': []
            }, {
                'background': []
            }], // dropdown with defaults from theme
            [{
                'font': []
            }],
            [{
                'align': []
            }],

            ['clean'] // remove formatting button
        ];

        var quill = new Quill('#editor', {
            modules: {
                toolbar: toolbarOptions
            },
            theme: 'snow'
        });
        $('#addComment').submit(function (e) {
            $("#submit").attr("disabled", true);

            e.preventDefault();
            var delta =quill.root.innerHTML;
            if(delta=='<p><br></p>'){
                toastr.error('Unable to post a blank comment', "", {
                    positionClass: "toast-top-full-width",
                    containerId: "toast-top-full-width"
                })
                $('#submit').removeAttr("disabled");

            }else{
                var dataString = $("#addComment").serialize();
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/tasks/saveTaskComment", // json datasource
                    data: dataString + '&delta=' + delta,
                    success: function (res) {
                        $('#submit').removeAttr("disabled");
                        $("#innerLoader").css('display', 'block');
                        if (res.errors != '') {
                            return false;
                        } else {
                            toastr.success('Your comment was posted', "", {
                                positionClass: "toast-top-full-width",
                                containerId: "toast-top-full-width"
                            });
                            loadCommentHistory({{$TaskData->id}});
                            quill.root.innerHTML='';
                        }
                    }
                });
            }
        });
    });
    loadCommentHistory({{$TaskData->id}});
    function loadCommentHistory(task_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadTaskCommentUpdatedView",
            data: {
                "task_id": task_id
            },
            success: function (res) {
                $("#loadComment").html(res);
            }
        })
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
                // loadTaskView(id);
            }
        })
    }

    function loadTaskView(task_id) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadTaskDetailPage", // json datasource
            data: {
                "task_id": task_id
            },
            success: function (res) {
                $("#reloadData").html(res);
                $("#preloader").hide();

            }
        })
    }
    loadTaskHistory({{$TaskData->id}});
    function loadTaskHistory(task_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadTaskHistory",
            data: {
                "task_id": task_id
            },
            success: function (res) {
                $("#list-group").html(res);
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
            }
        })
    }

    $('#loadReminderPopupIndexInViewOverlay').on('hidden.bs.modal', function () {
        reloadReminderArea({{$TaskData->id}});
        // alert("S")
    });

    function reloadReminderArea(task_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadReminderArea", // json datasource
            data: {
                "task_id": task_id
            },
            success: function (res) {
                $("#reminderAreaUpdate").html(res);
            }
        })
    }
    function loadChecklistView(task_id) {

        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadCheckListViewForTask", // json datasource
            data: {
                "task_id": task_id
            },
            success: function (res) {
                $("#taskReviewArea").html('<img src="{{LOADER}}""> Loading...');
                $("#taskReviewArea").html(res);
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
                loadChecklistView({{$TaskData->id}});
            }
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
    });

    

    function editTask(id) {
        console.log("editTask > resources/views/case/taskView.blade.php > " + id);
        $("#loadTaskDetailsView").modal("hide");
        $("#editTaskArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadEditTaskPopup", // json datasource
                data: {
                    "task_id": id
                },
                success: function (res) {
                    $("#editTaskArea").html(res);
                }
            })
        })
    }

    function backTocase(){
        $("#loadTaskDetailsView").modal("hide");
        setTimeout( function(){ 
            filterTaskByStatus() 
        },200 );
    }    
</script>
