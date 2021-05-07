<form class="saveStatusCodeForm" id="saveStatusCodeForm" name="saveStatusCodeForm" action="#" method="POST">
    @csrf
    <input type="hidden" name="case_id" value="{{$CaseMaster[0]->id}}">
    <div class="col-md-12">
        <span id="response"></span>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Case stage
            </label>
            <div class="col-md-9 form-group mb-3">
                <select id="case_status" name="case_status" class="form-control custom-select col">
                    <option value="0"></option>
                    <?php 
                    foreach($caseStageList as $kcs=>$vcs){?>
                    <option <?php if($vcs->id==$CaseMaster[0]->case_status){ echo "selected='selected'"; } ?>
                        value="{{$vcs->id}}">{{$vcs->title}}</option>
                    <?php } ?>
                </select>
            </div>

        </div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit"
                data-style="expand-left"><span class="ladda-label">Save Status</span><span
                    class="ladda-spinner"></span><span class="ladda-spinner"></span></button>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader"></div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#innerLoader").css('display', 'none');
        $("#response").hide();
        $('#saveStatusCodeForm').submit(function (e) {
            $("#innerLoader").css('display', 'block');
            e.preventDefault();
            var dataString = $("#saveStatusCodeForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/case/saveStatus", // json datasource
                data: dataString ,
                success: function (res) {
                    window.location.reload();
                    // $("#preloader").hide();
                    // $("#response").html(
                    //     '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Changes saved.</div>'
                    // );
                    // $("#response").show();
                    // $("#innerLoader").css('display', 'none');
                    // return false;
                }
            });

        });

    });

</script>
