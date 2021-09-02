<div id="reloadData" style="position: sticky; top: 0px;">
    <div class="border-bottom border-2 bg-light-c px-4 py-3">
        <h4 class="font-weight-bold">{{$TaskData->task_title}}</h4>
        <div>
            <?php
             $controllerLoad= new App\Http\Controllers\CommonController();
 
             if(isset($CaseMasterData) && !empty($CaseMasterData)){?>
            <strong>Case:</strong>
            <a class="ml-2"
                href="{{BASE_URL}}court_cases/{{$CaseMasterData->case_unique_number}}/info">{{$CaseMasterData->case_title}}</a>
            <?php } ?>
        </div>
    </div>
    <button id="closeView" style="position: absolute; top: 0px; right: 0px;" type="button" class="close mr-3 mt-3"
        aria-label="Close">X
    </button>
   
    <div class="task-details-section task-16497173 text-break px-3" style="width: 1000px; overflow: hidden auto;">
        <div class="row ">
            <div class="border-right border-2 h-100 col-md-6">
                <div class="pb-5 px-1">
                    <div class="d-flex mt-1">
                        <div class="ml-auto">
                            <div class="actions-cell float-right">
                                <div class="d-flex align-item-center task-action-buttons-16497173">
                                    <div>
                                        <a class="align-items-center" data-toggle="modal"
                                                data-target="#loadTimeEntryPopupInView" data-placement="bottom"
                                                href="javascript:;" onclick="loadTimeEntryPopupInView({{$TaskData->id}});">
                                                <span data-toggle="tooltip"  title="Add time entry"
                                                    data-content="Add time entry" data-placement="top" data-html="true">
                                                    <i class="fas fa-clock pr-3 align-middle"></i></span>
                                            </a>
                                    </div>
                                    <div> <a class="align-items-center" data-toggle="modal"
                                        data-target="#loadReminderPopupIndexInView" data-placement="bottom"
                                        href="javascript:;" onclick="loadReminderPopupIndexInView({{$TaskData->id}});">
                                        <span data-toggle="tooltip" data-trigger="hover" title="Reminder"
                                            data-content="" data-placement="top" data-html="true"> <i
                                                class="fas fa-bell pr-3 align-middle"></i> </span>
                                    </a>
                                    </div>
                                    <div> <a class="align-items-center" data-toggle="modal" data-target="#editTaskInView"
                                        data-placement="bottom" href="javascript:;"
                                        onclick="editTaskInView({{$TaskData->id}});">
                                        <span data-toggle="tooltip" data-trigger="hover" title="Edit"
                                            data-content="" data-placement="top" data-html="true"> <i
                                                class="fas fa-pen pr-3  align-middle"></i> </span></a>
                                    </div>
                                    <div>  <a class="align-items-center" data-original-title="" data-toggle="modal"
                                        data-target="#deleteTask" data-placement="bottom"
                                        onclick="deleteTaskFunction({{$TaskData->id}});" href="javascript:;">
                                        <span data-toggle="tooltip" data-trigger="hover" title="Delete"
                                            data-content="" data-placement="top" data-html="true"><i
                                                class="fas fa-trash pr-3  align-middle"></i> </span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-2 row ">
                        <div class="c-pointer p-1 col-6 col-md-4 col-lg-4">
                            <div class="p-2 border-3 card">
                                <strong>Status</strong>

                                <?php if($TaskData->status=='0'){?>
                                <div class="h4 font-weight-bold mb-1 text-black-50">
                                    <div>
                                        <a href="javascript:;"
                                            onclick="taskStatus({{$TaskData->id}},{{$TaskData->status}});">
                                            <i class="fas fa-check fa-sm text-muted" style="opacity: 0.2;"></i>
                                            Incomplete </a>
                                    </div>
                                </div>

                                <?php }else{ ?>
                                <div class="h4 font-weight-bold mb-1 text-black-50">
                                    <div>
                                        <a href="javascript:;"
                                            onclick="taskStatus({{$TaskData->id}},{{$TaskData->status}});">
                                            <i class="fas fa-check fa-sm  text-success" style=""></i> Complete
                                        </a>
                                    </div>
                                    <?php
                                             $OwnDate=$controllerLoad->convertUTCToUserTime($TaskData->task_completed_date,Auth::User()->user_timezone);
                                             ?>
                                </div>
                                <small class="text-muted"> On {{date('m/d/Y h:i a',strtotime($OwnDate))}}</small>

                                <?php } ?>



                            </div>
                        </div>
                        <div class=" p-1 col-6 col-md-4 col-lg-4">
                            <div class="p-2 border-3 card"><strong>Due date</strong>
                                <?php 
                                if($TaskData->task_due_on=='9999-12-30'){
                                    ?>
                                <div class="h4 font-weight-bold mb-1 ">No due date</div>
                                <?php
                                }elseif($TaskData->task_due_on >= date('Y-m-d') && $TaskData->task_due_on!='9999-12-30'){?>
                                <div class="h4 font-weight-bold mb-1 ">
                                    {{date('m/d/Y',strtotime(convertUTCToUserDate($TaskData->task_due_on, auth()->user()->user_timezone)))}}</div>
                                <?php }else{ ?>
                                <div class="h4 font-weight-bold mb-1 text-danger">
                                    {{date('m/d/Y',strtotime(convertUTCToUserDate($TaskData->task_due_on, auth()->user()->user_timezone)))}}
                                </div>
                                <?php }
                            
                             if($TaskData->task_due_on >= date('Y-m-d') && $TaskData->task_due_on!='9999-12-30'){
                                 ?>
                                <small class="text-muted"> in
                                    {{$controllerLoad->daysReturns($TaskData->task_due_on)}}</small>
                                <?php }else if($TaskData->task_due_on!='9999-12-30'){ ?>
                                <small class="text-muted"> {{$controllerLoad->daysReturns($TaskData->task_due_on)}}
                                    ago</small>
                                <?php } ?>
                            </div>
                        </div>
                        <div class=" p-1 col-6 col-md-4 col-lg-4">
                            <div class="p-2 border-3 card"><strong>Priority</strong>
                                <?php if($TaskData->task_priority == "1"){?>
                                <div class="h4 font-weight-bold mb-1 text-black-50">Low</div>
                                <?php }else if($TaskData->task_priority == "2"){?>
                                <div class="h4 font-weight-bold mb-1 text-secondary-task"">Medium</div>
                                    <?php }else if($TaskData->task_priority == "3") {?>
                                        <div class=" h4 font-weight-bold mb-1 text-warning">High</div>
                                <?php }else{ ?>
                                <div class="h4 font-weight-bold mb-1 ">None</div>
                                <?php } ?>

                            </div>
                        </div>
                    </div>     
                    <div id="CheckListUpdate">
                    <?php if(!$TaskChecklist->isEmpty()){?>
                        <div class="mb-3">
                            <strong>Subtasks: </strong>
                            <span>{{$TaskChecklistCompleted}} / {{count($TaskChecklist)}} completed</span>
                            <?php  $findComletedPErcent=($TaskChecklistCompleted/count($TaskChecklist) * 100); ?>
                            <div style="height: 10px;" class="my-2 progress">
                                <div class="progress-bar" style="width:{{$findComletedPErcent}}%;" role="progressbar" aria-valuenow="{{$findComletedPErcent}}" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                            <ul class="list-group" id="checklistReloadArea">
                                <?php
                                foreach($TaskChecklist as $ckkey=>$ckval){
                                    if($ckval->status=="1"){?>
                                    <a href="javascript:void(0);" onclick="updateCheckList({{$ckval->id}},{{$ckval->status}});" ><li class="c-pointer list-group-item" ><i class="fas fa-check fa-lg text-success mr-5"></i>
                                        {{$ckval->title}}
                                    </li>
                                    </a>
                                <?php }else{?>
                                    <a href="javascript:void(0);" onclick="updateCheckList({{$ckval->id}},{{$ckval->status}});"> <li class="c-pointer list-group-item" ><i class="fas fa-check fa-lg text-muted opacity-50 mr-5"></i>  {{$ckval->title}}
                                        </li>
                                    </a>
                                    <?php } ?>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                    </div>
                    <div class="my-1 row ">
                        <div class="col-12 col-md-3 col-lg-3"><strong>Description</strong></div>
                        <div class="text-break col-12 col-md-9 col-lg-9"><?php
                        if($TaskData->description==''){?>
                            <span class="text-black-50">None</span>
                            <?php } else { ?>
                            <span class="text-black-50">{{$TaskData->description}}</span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="my-1 row ">
                        <div class="col-12 col-md-3 col-lg-3"><strong>Created By</strong></div>
                        <div class="text-break col-12 col-md-9 col-lg-9">
                            <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($TaskCreatedBy->uid)}}"
                                class="d-flex align-items-center user-link"
                                title="{{$TaskCreatedBy->user_title}}">{{$TaskCreatedBy->created_by_name}}
                                ({{$TaskCreatedBy->user_title}})</a>

                        </div>
                    </div>
                    <div class="my-1 row ">
                        <div class="col-12 col-md-3 col-lg-3"><strong>Assignees</strong></div>
                        <div class="text-break col-12 col-md-9 col-lg-9">
                            <div class="d-flex flex-row mb-1">
                                <div>
                                    <?php
                                     $counter=0;
                                    if(!$TaskAssignedTo->isEmpty()){
                                        foreach($TaskAssignedTo as $k=>$v){?>
                                            <div class="d-flex flex-row">
                                                <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($v->uid)}}"
                                                    class="d-flex align-items-center user-link"
                                                    title="{{$v->user_title}}">{{$v->created_by_name}} ({{$v->user_title}})</a>   
                                                    <?php 
                                                    if($v->time_estimate_total!=0){?>
                                                    <div class="ml-2 mb-0 h5"><span class="badge badge-pill badge-info">{{$v->time_estimate_total}}h</span></div>
                                                    <?php } ?>
                                            </div>
                                     <?php 
                                     $counter+=$v->time_estimate_total;} 
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="my-1 row ">
                        <div class="col-12 col-md-3 col-lg-3"><strong>Time Estimate</strong></div>
                        <div class="text-break col-12 col-md-9 col-lg-9">
                            <?php
                            if($counter!=0){?>
                            <?php echo $counter;?> hour(s)
                            <?php }else{?>
                            <div><span class="text-black-50">None</span></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <hr>
                    <div class="my-1 row ">
                        <div class="col-12 col-md-3 col-lg-3"><strong>Reminders</strong></div>
                        <div class="text-break col-12 col-md-9 col-lg-9">
                            <div class="mb-3">
                                <?php
                                if(!$TaskReminders->isEmpty()){
                                    foreach($TaskReminders as $kr=>$kv){?>
                                <div>
                                    <div><strong>{{ucfirst($kv->reminder_user_type)}}</strong> - {{ucfirst($kv->reminder_type)}} {{$kv->reminer_number}} day before due date.</div>
                                    <small class="text-black-50">Created by {{$kv->created_by_name}}</small>
                                </div>
                            <?php } }else{
                                ?><div class="mb-3"><span class="text-black-50">None</span></div>
                                <?php
                            }
                             ?>
                                
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
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
                            <div>
                               <div id="loadComment"></div>
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
                                        class="mt-1 float-right btn btn-primary">Comment</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="profileBasic" role="tabpanel" aria-labelledby="profile-basic-tab">
                        <ul class="list-group list-group-flush" id="list-group">
                            
                        </ul>
                    </div>
                </div>
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
            // window.location.href=baseUrl+'/tasks';
            $("#taskViewArea").fadeOut();

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
            url: baseUrl + "/tasks/loadTaskComment",
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
                loadTaskView(id);
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

    function updateCheckList(id, status) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/updateCheckList", // json datasource
            data: {
                "id": id,
                "status": status
            },
            success: function (res) {
                loadChecklistView({{$TaskData->id}});
            }
        })
    }

   

    function loadChecklistView(task_id) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadCheckListView", // json datasource
            data: {
                "task_id": task_id
            },
            success: function (res) {
                $("#CheckListUpdate").html(res);
                $("#preloader").hide();

            }
        })
    }
</script>
