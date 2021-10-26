<div class="row col-md-12">
    <div class="ml-auto d-flex d-print-none">
        <div id="bulk-dropdown" class="mr-2 actions-button btn-group">
            <div class="mx-2">
                <div class="btn-group">
                    <button class="btn btn-light  m-1 dropdown-toggle" data-toggle="dropdown" id="Taskactionbutton"
                        disabled="disabled" aria-haspopup="true" aria-expanded="false">
                        Action
                    </button>
                    <div class="dropdown-menu bg-transparent shadow-none p-0 m-0 " x-placement="top-start">
                        <div class="card">
                            <div class="card-body">
                                <button type="button" tabindex="0" onclick="markasCompleted()" role="menuitem"
                                    class="bulk-mark-tasks-as-complete dropdown-item"><span>Mark as
                                        completed</span>
                                </button>
                                <a class="align-items-center" data-toggle="modal" data-target="#changeDueDate"
                                    data-placement="bottom" href="javascript:;">
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
        <a data-toggle="modal" data-target="#loadAddTaskPopup" data-placement="bottom" href="javascript:;">
            <button class="btn btn-primary btn-rounded m-1" type="button" onclick="loadAddTaskPopup({{$CaseMaster->case_id}});">
                Add Task
            </button>
        </a>
    </div>
</div>
<div class="row col-md-12">
    <div class="col-md-3 form-group mb-3">
        <label for="picker1">Assigned To</label>
        <select onchange="filterTaskByAssignTo();" class="form-control user_type select2" id="user_type" name="at">
            <option value="all_users"  selected="">All Users</option>
            <option value="firm_users">Firm Users</option>
            <option value="clients">Clients</option>
        </select>
    </div>
    <div class="col-md-3 form-group mb-3">
        <label for="picker1">Completion Status</label>
        <select id="task_status" name="ts" onchange="filterTaskByStatus();"  class="form-control custom-select col select2">
            <option value="all_tasks"  selected="">All Tasks</option>
            <option value="incomplete">Incomplete</option>
            <option value="needs_review">Needs Review</option>
            <option value="complete">Complete</option>
        </select>
    </div>
</div>
<div class="row col-md-12" id="taskDyncamic"></div>

<div id="loadPrint" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Print Tasks</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row"  >
                    <div class="col-md-12">
                        <b>Select the task due date range to print:</b>
                    </div>
                    <div class="col-md-12" >
                        <label for="print_task_range">From</label>&nbsp;    
                        <div class="input-daterange input-group" id="datepicker" style="display:ruby;">
                            <input style="width: 115px;" type="text" class="form-control" name="print_task_range_from" value="{{date('m/d/Y')}}"
                            id="print_task_range_from" />
                            <span class="input-group-addon">&nbsp;To&nbsp;</span>
                            <input style="width: 115px;"  type="text" class="form-control" name="print_task_range_to" value="{{date('m/d/Y')}}"
                            id="print_task_range_to" />
                        </div>
                    </div>
                    <div class="col-md-12" >
                        </br>
                        <label><input type="checkbox" id="include_without_due_date" class="test-include-without-due-date"> Include tasks without due date</label>
                    </div>
                    <div class="col-md-12" >
                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary example-button m-1" id="loadNext" onClick="printTasks();" >Print</span></button>
                        </div>
                    </div>
                   <div class="form-group row">
                        <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .modal {
        overflow: auto !important;
    }
</style>
@section('page-js-inner')
<script type="text/javascript">
    function printEntry()
    {
        $("#loadPrint").modal('show');
    }

    function printTasks(){
        $("#preloader").show();
        var print_task_range_from = $("#print_task_range_from").val();
        var print_task_range_to = $("#print_task_range_to").val();
        var include_without_due_date = $("#include_without_due_date").prop('checked');
        
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/case/loadTaskPortion?print_task_range_from="+print_task_range_from+"&print_task_range_to="+print_task_range_to+"&include_without_due_date="+include_without_due_date, // json datasource", // json datasource
                data: {"case_id": "{{$CaseMaster->case_id}}"},
                success: function (res) {
                    var canvas = $(".printDiv").html(res);
                    window.print();
                    $("#loadPrint").modal('hide');
                    $("#preloader").hide();
                    return false;
                }
            })
        })
    }
</script>
@endsection