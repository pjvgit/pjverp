<?php
$CommonController= new App\Http\Controllers\CommonController();
?>
<div class="row ">
    <div class="col">
        <div class="float-right">
            <?php 
            if(isset($_GET['show']) && $_GET['show']=='all'){?>
             <a  href="{{BASE_URL}}leads/{{$user_id}}/case_details/tasks">
                <button class="btn btn-outline-secondary m-1 btn-rounded" id="btn_pause_resume" type="button" >Show Incomplete Task</button>
            </a>
        <?php } else { ?>
            <a  href="{{BASE_URL}}leads/{{$user_id}}/case_details/tasks?show=all">
                <button class="btn btn-outline-secondary m-1 btn-rounded" id="btn_pause_resume" type="button" >Show All Task</button>
            </a>
        <?php } ?>
            <a data-toggle="modal" data-target="#loadAddTaskPopup" data-placement="bottom" href="javascript:;">
               <button class="btn btn-outline-secondary m-1 btn-rounded" type="button" onclick="loadAddTaskPopup('',{{$LeadData['user_id']}});">Add Task</button>
           </a>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="display table table-striped table-bordered" id="taskList" style="width:100%">
        <thead>
            <tr>
                <th width="50%">Task</th>
                <th width="10%">Due</th>
                <th width="15%">Assigned To</th>
                <th width="25%"></th>
            </tr>
        </thead>

    </table>
</div>
