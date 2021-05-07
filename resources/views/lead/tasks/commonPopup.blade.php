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
<script type="text/javascript">
    function loadAddTaskPopup() {
        $("#preloader").show();
        $("#addTaskArea").html('Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/loadAddTaskPopup", // json datasource
                data: {
                    "user_id": ""
                },
                success: function (res) {
                    $("#addTaskArea").html('');
                    $("#addTaskArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function editTask(id,fromView) {
        if(fromView=="yes"){
            fromView="yes";
        }else{
            fromView="no";
        }
        $("#preloader").show();
        $("#editTaskArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/loadEditTaskPopup", // json datasource
                data: {
                    "task_id": id,
                    "from_view":fromView
                },
                success: function (res) {
                    $("#editTaskArea").html('');
                    $("#editTaskArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
</script>
