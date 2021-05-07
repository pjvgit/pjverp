<form class="saveStatus" id="saveStatus" name="saveStatus" action="#" method="POST">
    @csrf
    <input type="hidden" name="case_id" value="{{$case_id}}" id="case_id">

    <div class="col-md-12">
        <span id="response"></span>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
        <div class="form-group row">
            <div class="col-md-12 form-group mb-3">
                <textarea maxlength="500" name="case_update" placeholder="What's going on with this case?" class="form-control" rows="5"></textarea>
            </div>
        </div>
       

        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit"
                data-style="expand-left"><span class="ladda-label">Save Update</span><span
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
        $("#saveStatus").validate({
            rules: {
                case_update: {
                    required: true
                }
            },
            messages: {
                case_update: {
                    required: "Description can't be blank"
                }
            }
        });

        $("#innerLoader").css('display', 'none');
        $("#response").hide();
        $('#saveStatus').submit(function (e) {
            $("#innerLoader").css('display', 'block');
            e.preventDefault();
            if (!$('#saveStatus').valid()) {
                $("#innerLoader").css('display', 'none');
                return false;
            }
            var dataString = $("form").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/case/saveCaseUpdate", // json datasource
                data: dataString,
                success: function (res) {
                    window.location.reload();
                    // $("#preloader").hide();
                    // $("#response").html(
                    //     '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Your status update has been added.</div>'
                    // );
                    // $("#response").show();
                    // $("#innerLoader").css('display', 'none');
                    // return false;
                }
            });

        });

    });

</script>
