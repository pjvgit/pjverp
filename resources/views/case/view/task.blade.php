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