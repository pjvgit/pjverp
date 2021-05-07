<div id="addCall" class="modal fade bd-example-modal-lg" style="width: 100%;" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Phone Call</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="addCallArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<div id="deleteCallLog" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteCallLogFormCommon" id="deleteCallLogFormCommon" name="deleteCallLogFormCommon" method="POST">
            @csrf
            <input type="hidden" name="call_id" id="call_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirmation</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <span id="response"></span>
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Are you sure you want to delete this Call Log?</h6>
                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader3"
                            style="display: none;"></div>
                        <div class="form-group row float-right">
                            <a href="#">
                                <button class="btn btn-secondary  m-1" type="button"
                                    data-dismiss="modal">Cancel</button>
                            </a>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Yes</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="editCall" class="modal fade bd-example-modal-lg"  role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Phone Call</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="editCallArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<div id="addTaskFromLog" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
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
                        <div id="addTaskFromLogArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">

    function addCall(id) {
        $("#preloader").show();
        $("#addCallArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/addCall", // json datasource
                data: {
                    'case_id': "{{$CaseMaster['case_id']}}"
                },
                success: function (res) {
                    $("#addCallArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }


    function editCall(id) {
        $("#preloader").show();
        $("#editCallArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/editCall", // json datasource
                data: {
                    'id': id
                },
                success: function (res) {
                    $("#editCallArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function deleteCallLog(id) {
        $("#call_id").val(id);
    }
    function addTaskFromLog() {
        $("#preloader").show();
        $("#addTaskFromLogArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/loadAddTaskPopup", // json datasource
                data: {
                    'case_id': "{{$CaseMaster['case_id']}}"
                },
                success: function (res) {
                    $("#addTaskFromLogArea").html('');
                    $("#addTaskFromLogArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

</script>
