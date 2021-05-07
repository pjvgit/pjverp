<div class="col-md-12">
  
    <form class="saveCaseStageText" id="saveCaseStageText" name="saveCaseStageText" method="POST">
        <input class="form-control" id="id" value="{{ $CaseStage->id}}" name="id" type="hidden">

        @csrf
        <div class="form-group row">
            <div class="col-md-12 form-group mb-3">
                <input class="form-control" id="stage_name" value="{{ $CaseStage->title}}" name="stage_name" type="text"
                    placeholder="Enter case stage">
            </div>
        </div>

        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
            </a>

            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit"
                data-style="expand-left"><span class="ladda-label">Save Stage</span><span
                    class="ladda-spinner"></span><span class="ladda-spinner"></span></button>
        </div>
        
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display: none;"></div>
            </div>
        </div>
        
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#saveCaseStageText").validate({
            rules: {
                stage_name:{
                    required:true
                }
            },
            messages: {
                stage_name: {
                    required: "Case stage name can't be blank"
                }
            }
        });
        $('#saveCaseStageText').submit(function (e) {
            $("#innerLoader").css('display', 'block');
            e.preventDefault();
            if (!$('#saveCaseStageText').valid()) {
                $("#innerLoader").css('display', 'none');
                return false;
            }
            var dataString = $("form").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/case_stages/saveEditCaseStage", // json datasource
                data: dataString,
                success: function (res) {
                    $("#EditCaseStageModel").modal("hide");
                    toastr.success('Stage name has been updated.', "", {
                        progressBar: !0,
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                }
            });
        });
    });


</script>
