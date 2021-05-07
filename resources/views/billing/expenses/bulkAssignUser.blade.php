    <span id="showError" class="showError" style="display: none;"></span>
    <form class="saveUserAssignBulkForm" id="saveUserAssignBulkForm" name="saveUserAssignBulkForm" method="POST">
        @csrf
        <div class="form-group row">

            <div class="col-12 form-group mb-3">
                <select class="form-control staff_id" id="staff_id" name="staff_id"
                    data-placeholder="Select firm user to re-assign to">
                    <option value="">Select firm user to re-assign to</option>
                    <?php foreach($loadFirmStaff as $firmKey=>$firmVal){ ?>
                    <option value="{{$firmVal['id']}}">{{$firmVal['user_name']}}</option>
                    <?php } ?>

                </select>
                <span id="cnl"></span>
            </div>
        </div>
        <div class="modal-footer">
            <div class="col-md-2 form-group">
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
                </div>
            </div>
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
            </a>
            <button type="submit" id="disabledButton" name="save" value="s"
                class="btn btn-primary submit">Confirm</button>
        </div>
    </form>
    <script type="text/javascript">
        $(document).ready(function () {
            afterLoader();
            $("#staff_id").select2({
                placeholder: "Select firm user to re-assign to",
                theme: "classic",
                allowClear: true,
                dropdownParent: $("#bulkAssignUserPopup"),
            });
            $('#disabledButton').attr("disabled", true);
            $('#staff_id').on("select2:select", function (e) {
                $('#disabledButton').removeAttr("disabled");
            });

            $('#staff_id').on("select2:unselecting", function (e) {
                $('#disabledButton').attr("disabled", true);
            });
            $('#saveUserAssignBulkForm').submit(function (e) {
                beforeLoader();
                e.preventDefault();
                if (!$('#saveUserAssignBulkForm').valid()) {
                    afterLoader();
                    return false;
                }
                var array = [];
                $("input[class=task_checkbox]:checked").each(function (i) {
                    array.push($(this).val());
                });
                var dataString = '';
                dataString = $("#saveUserAssignBulkForm").serialize();
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/bills/expenses/saveBulkAssignUser", // json datasource
                    data: dataString + '&entry_id=' + JSON.stringify(array),
                    success: function (res) {

                        if (res.errors != '') {
                            $('.showError').html('');
                            var errotHtml =
                                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                            $.each(res.errors, function (key, value) {
                                errotHtml += '<li>' + value + '</li>';
                            });
                            errotHtml += '</ul></div>';
                            $('.showError').append(errotHtml);
                            $('.showError').show();

                            afterLoader();
                            $('#assignCaseBulk').animate({
                                scrollTop: 0
                            }, 'slow');
                            return false;
                        } else {
                            window.location.reload();
                        }
                    },
                    error: function (xhr, status, error) {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        $('#assignCaseBulk').animate({
                            scrollTop: 0
                        }, 'slow');
                        afterLoader();
                    }
                });
            });
        });

    </script>
