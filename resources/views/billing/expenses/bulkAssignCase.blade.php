    <span id="showError" class="showError" style="display: none;"></span>
    <form class="saveAssignBulkForm" id="saveAssignBulkForm" name="saveAssignBulkForm" method="POST">
        @csrf
        <div class="form-group row">

            <div class="col-12 form-group mb-3">
                <select class="form-control case_id dropdownSelect" id="case_id" name="case_id"
                    data-placeholder="Select case">
                    <option value="">Select case</option>
                    <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                    <option value="{{$Caseval->id}}">{{$Caseval->case_title}}
                        <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?></option>
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
            $(".dropdownSelect").select2({
                placeholder: "Select...",
                theme: "classic",
                allowClear: true,
                dropdownParent: $("#assignCaseBulk"),
            });
            $('#disabledButton').attr("disabled", true);
            $('#case_id').on("select2:select", function (e) {
                $('#disabledButton').removeAttr("disabled");
            });

            $('#case_id').on("select2:unselecting", function (e) {
                $('#disabledButton').attr("disabled", true);
            });
            $('#saveAssignBulkForm').submit(function (e) {
                beforeLoader();
                e.preventDefault();
                if (!$('#saveAssignBulkForm').valid()) {
                    afterLoader();
                    return false;
                }
                var array = [];
                $("input[class=task_checkbox]:checked").each(function (i) {
                    array.push($(this).val());
                });
                var dataString = '';
                dataString = $("#saveAssignBulkForm").serialize();
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/bills/expenses/saveBulkAssignCase", // json datasource
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
