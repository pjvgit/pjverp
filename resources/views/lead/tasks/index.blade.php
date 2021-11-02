@extends('layouts.master')
@section('title', 'Leads')
@section('main-content')
@include('lead.lead_submenu')
<?php 
$at="everyone";
$status='0';
if(isset($_GET['at'])){
    $at= $_GET['at'];
}
if(isset($_GET['status'])){
    $status= $_GET['status'];
}

?>
<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                @include('lead.tasks.mainMenu')
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <div class="row pl-4 ">
                        <div class="col-md-2 form-group">
                            <label for="picker1">Show Tasks Assigned to:</label>
                            <select  class="form-control user_type dropdownArea" id="assign_to" name="at">
                                <option <?php if($at=='everyone'){ echo "selected=selected"; }?>  value="everyone">Everyone</option>
                                <option <?php if($at=='me'){ echo "selected=selected"; }?>  value="me">Me</option>
                            </select>
                        </div>

                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Status:</label>
                            <select class="form-control user_type dropdownArea" id="status" name="status">
                                <option <?php if($status=='1'){ echo "selected=selected"; }?>  value="1">Complete</option>
                                <option <?php if($status=='0'){ echo "selected=selected"; }?>  value="0">Incomplete</option>
                            </select>
                        </div>
                    </div>
                </form>
                <div class="table-responsive" id="printHtml">
                    <h3 id="hiddenLable">Leads</h3>
                    <table class="display table table-striped table-bordered" id="employee-grid" style="width:100%">
                        <thead>
                            <tr>
                                <th width="1%">id</th>     
                                <th width="10%"></th>
                                <th width="20%">Name</th>
                                <th width="20%">Lead</th>
                                <th width="10%">Due</th>
                                <th width="20%">Assign To</th>
                                <th width="19%" class="text-center"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <aside  id="taskViewArea" class="task-details-drawer">
    </aside>
</div>

<div id="deleteTask" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Delete Task</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="eventID">
                        <form class="deleteTask" id="deleteTask" name="deleteTask" method="POST">
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
                <h5 class="modal-title" id="deleteSingle">Set Event Reminders</h5>
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
<style>
    .afterLoadClass{
        position: absolute; top: 0px; width: 560px; right: 0px; background-color: white; height: 100%; display: inline-table; box-shadow: rgba(0, 0, 0, 0.5) 1px 0px 7px; z-index: 100; min-height: 850px;
    }

</style>
@include('lead.tasks.commonPopup')
@endsection
@section('page-js')

<script type="text/javascript">
    $(document).ready(function () {
        $(".dropdownArea").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true
        });
        $("#taskViewArea").hide();
        $('button').attr('disabled',false);
        var dataTable =  $('#employee-grid').DataTable( {
        serverSide: true,
        responsive: false,
        processing: true,
        stateSave: true,
        searching:false,
        "order": [[0, "desc"]],
        "ajax":{
            url :"loadLeadTask", 
            type: "post", 
            data :{ "at": '{{$at}}',"status": '{{$status}}'},
            error: function(){ 
                $(".employee-grid-error").html("");
                $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                $("#employee-grid_processing").css("display","none");
            }
        },
        "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
        pageResize: true,
        pageLength:{{USER_PER_PAGE_LIMIT}},
        columns: [
            { data: 'id'},  
            { data: 'id','orderable': false},
            { data: 'id','orderable': false},
            { data: 'id'},
            { data: 'id' }, 
            { data: 'id','orderable': false},
            { data: 'id','orderable': false},],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                if(aData.status=="1"){
                    $('td:eq(0)', nRow).html('<div class="text-center"><a href="javascript:void(0);" onclick="taskMarkAsInCompleted('+aData.id+');"><button  class="btn btn-outline-secondary m-1" type="button"> Mark Incomplete</button></a></div>');    
                }else{
                    $('td:eq(0)', nRow).html('<div class="text-center"><a href="javascript:void(0);" onclick="taskMarkAsCompleted('+aData.id+');"><button  class="btn btn-outline-secondary m-1" type="button"> Mark Complete</button></a></div>');    
                }
                
               
                $('td:eq(1)', nRow).html('<div class="text-left mt-2"><a href="#" onclick="loadTaskView('+aData.id+')"> '+aData.task_title+'</a></div>');
                $('td:eq(2)', nRow).html('<div class="text-left mt-2"><a href="'+baseUrl+'/leads/'+aData.uid+'/lead_details/info">'+aData.created_by_name+'</a></div>');
                $('td:eq(3)', nRow).html('<div class="text-left mt-2">'+aData.task_due_date+'</div>');
                
                var obj = JSON.parse(aData.assign_to);
                var i;
                var urlList='';
                if(obj.length>1){
                    for (i = 0; i < obj.length; ++i) {
                        urlList+=obj[i].first_name+' '+obj[i].last_name+'<br>';
                    }
                    $('td:eq(4)', nRow).html('<div class="text-left mt-2"><a class=" event-name d-flex align-items-center" tabindex="0" role="button" href="javascript:;" data-toggle="popover"  style="float:left;" data-trigger="hover" data-content="<b>Assign To:</b><br>'+urlList+'" data-html="true" data-original-title=""><i class="fas fa-user-friends mr-1"></i>'+obj.length+' Users</a></div>');
                }else{
                    for (i = 0; i < obj.length; ++i) {
                        urlList+='<a href="'+baseUrl+'/contacts/attorneys/'+obj[i].decode_user_id+'">'+obj[i].first_name+' '+obj[i].last_name+'</a><br>';
                    }
                    $('td:eq(4)', nRow).html('<div class="text-left">'+urlList+'</div>');
                }
                

                $('td:eq(5)', nRow).html('<div class="d-flex align-items-center float-right d-print-none"><a data-toggle="modal"  data-target="#editTask" onclick="editTask('+aData.id+');" data-placement="bottom" href="javascript:;"   title="Edit" data-testid="edit-button" class="btn btn-link"><i class="fas fa-pencil-alt  m-2"></i><//a><a data-toggle="modal"  data-target="#deleteTask" data-placement="bottom" href="javascript:;"   title="Delete" data-testid="delete-button" class="btn btn-link" onclick="deleteTaskFunction('+aData.id+');"><i class="fas fa-trash"></i></a></div>');
                        
            },
            "initComplete": function(settings, json) {
                $('[data-toggle="tooltip"]').tooltip();
                $("[data-toggle=popover]").popover();
                <?php
                    if(Session::get('task_id')!=""){
                        ?>
                     loadTaskView({{Session::get('task_id')}}); 
                        <?php
                        Session::put('task_id', "");
                    } 
                    ?>
            }
        });

        

        //Close the popup and reload the datatable via ajax
        $('#changeSource,#doNotHire,#deleteBulkLead').on('hidden.bs.modal', function () {
            dataTable.ajax.reload(null, false);
        });

        $('#actionbutton').attr('disabled', 'disabled');
        $('.dropdownArea').change(function() {
            this.form.submit();
        });

        $('#selectAll').prop('checked', false);
       
         
        $('#deleteTask').submit(function (e) {

            $("#innerLoader1").css('display', 'block');
            e.preventDefault();
            var dataString = $("form").serialize();
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
    });

    function deleteTaskFunction(id) {
        $("#task_id").val(id);
    }
    function loadTaskView(task_id) {
        $(".task-details-drawer").fadeIn();
        $("#taskViewArea").html('Loading...');
        $("#preloader").show();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/loadTaskDetailPage", // json datasource
                data: { "task_id": task_id},
                success: function (res) {
                    $("#taskViewArea").html('Loading...');
                    $("#taskViewArea").html(res);
                    $("#preloader").hide();
                }
            })
    }


    function taskMarkAsCompleted(id) {
        $("#preloader").show();

        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/markAsCompleted",
            data: {
                "task_id": id
            },
            success: function (res) {
                if (res.errors != '') {
                    toastr.error('There were some problems with your input.', "", {
                        progressBar: !0,
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                    return false;
                } else {
                    toastr.success('Your task have been updated.', "", {
                        progressBar: !0,
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                    window.location.reload();
                }
            }
        })
    }

    function taskMarkAsInCompleted(id) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/markAsCompleted",
            data: {
                "task_id": id,
                "type":"incomplete"
            },
            success: function (res) {
                if (res.errors != '') {
                    toastr.error('There were some problems with your input.', "", {
                        progressBar: !0,
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                    return false;
                } else {
                    toastr.success('Your task have been updated.', "", {
                        progressBar: !0,
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                    window.location.reload();
                }
            }
        })
    }
  

    function loadStep1(id) {
        $("#preloader").show();
        $("#step-1").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/loadStep1", // json datasource
                data: {"id":id},
                success: function (res) {
                    $("#step-1").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }


    function changeSource(id,referal_source) {
        $("#preloader").show();
        $("#changeSourceArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/lead_setting/changeReferalResource", // json datasource
                data: {'id':id,
                'referal_source':referal_source},
                success: function (res) {
                    $("#changeSourceArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function doNotHire(id) {
        $("#preloader").show();
        $("#doNotHireArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/lead_setting/doNotHire", // json datasource
                data: {'id':id},
                success: function (res) {
                    $("#doNotHireArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function editLead(id) {
        $("#preloader").show();
        $("#editLeadArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/editLead", // json datasource
                data: {'id':id},
                success: function (res) {
                $("#editLeadArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function assignLead() {
        $("#assignLead").modal();
        $("#preloader").show();
        $("#assignBulkLead").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/loadAssignPopup", // json datasource
                data: {'id':''},
                success: function (res) {
                
                    $("#assignBulkLead").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    //Call when performed status change for bulk action
    function changeBulkStatus() {
        $("#changeStatusBulk").modal();
        $("#preloader").show();
        $("#changeStatusBulkArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/loadChangeBulkStatus", // json datasource
                data: {'id':''},
                success: function (res) {
                
                    $("#changeStatusBulkArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    //Call when performed status dont hire change for bulk action
    function doNotHireBulk() {
        $("#doNotHireBulk").modal();
        $("#preloader").show();
        $("#doNotHireBulkArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/loadChangeBulkDonothire", // json datasource
                data: {'id':''},
                success: function (res) {
                
                    $("#doNotHireBulkArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    //Delete bulk lead
    function deleteBulkLead() {
        $("#deleteBulkLead").modal();
    }

    

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

    setTimeout(function(){  
        $('#taskViewArea').addClass('afterLoadClass'); 
    }, 500);

    function printEntry()
    {
        $('#employee-grid_length').hide();
        $('#employee-grid_info').hide();
        $('#employee-grid_paginate').hide();
        $('#hiddenLable').show();
        var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        $(".main-content-wrap").remove();
        window.print(canvas);
        // w.close();
        window.location.reload();
        return false;  
    }
    $('#hiddenLable').hide();
</script>
<script src="{{ asset('assets\js\custom\feedback.js?').env('CACHE_BUSTER_VERSION') }}"></script>
@stop
