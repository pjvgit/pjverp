<form class="editReminderForCase" id="editReminderForCase" name="editReminderForCase" method="POST">
    <input class="form-control" id="id" value="{{ $case_id}}" name="case_id" type="hidden">
    @csrf
    <div class="row">
    <div class="col-md-12">

        <div class="text-muted mb-3"></div>
      
            <div class="form-group row">
                <label for="reminders" class="col-sm-2 col-form-label">Reminders</label>
                <div class="col">
                    <div>
                        <?php
                            foreach($CaseSolReminder as $rkey=>$rval){
                            ?>
                            <div class="row form-group fieldGroup">
                                <div class="">
                                    <div class="d-flex col-10 pl-0 align-items-center">
                                        <div class="pl-0 col-5">
                                            <div>
                                                <div class="">
                                                    <select id="reminder_type" name="reminder_type[]" class="form-control custom-select valid" aria-invalid="false">
                                                        <option <?php if($rval->reminder_type=="popup"){ echo "selected=selected"; } ?> value="popup">popup</option>
                                                        <option <?php if($rval->reminder_type=="email"){ echo "selected=selected"; } ?> value="email">email</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div><input name="reminder_days[]" class="form-control col-2 reminder-number" value="{{$rval->reminer_number}}">
                                        <div class="col-4">
                                            days
                                        </div>
                                        <button class="btn remove" type="button">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        <div class="fieldGroup">
                        </div>
                        <div><button type="button" class="btn btn-link p-0 test-add-new-reminder add-more-index">Add a reminder</button></div>
                    </div>
                </div>
            </div>  

            <div class="fieldGroupCopy copy hide" style="display: none;">
                <div class="">
                    <div class="d-flex col-10 pl-0 align-items-center">
                        
                        <div class="pl-0 col-5">
                            <div>
                                <div class="">
                                    <select id="reminder_type" name="reminder_type[]"
                                        class="form-control custom-select  ">
                                        <option value="popup">popup</option>
                                        <option value="email">email</option>
                                    </select>
                                </div>
                            </div>
                        </div><input name="reminder_days[]" class="form-control col-2 reminder-number" value="1">
                        <div class="col-4">
                            days    
                        </div>
                        <button class="btn remove" type="button">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>

    </div>
</div><!-- end of main-content -->
<div class="modal-footer">
<div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader1"></div>
            </div>
        </div>
    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
    <button type="submit" name="submit" class="btn btn-primary submit">Set Reminders</button>
</div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();
        $(".add-more-index").click(function () {
            var fieldHTML = '<div class="row form-group fieldGroup">' + $(".fieldGroupCopy").html() +
                '</div>';
            $('body').find('.fieldGroup:last').before(fieldHTML);
        });
        $('#editReminderForCase').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroup').remove();
        });


        
        $('#editReminderForCase').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            // $("#submit").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            if (!$('#editReminderForCase').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                afterLoader();
                return false;
            }
            var dataString ='';
             dataString = $("#editReminderForCase").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveCaseReminderPopup", // json datasource
                data: dataString,
                success: function (res) {
                  
                    $("#innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('#showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('#showError').append(errotHtml);
                        $('#showError').show();
                        $("#innerLoader").css('display', 'none');
                        $('#submit').removeAttr("disabled");
                        afterLoader();
                        return false;
                    } else {    
                        $("#addCaseReminderPopup").modal('hide');
                        $("#innerLoader").css('display', 'none');
                        toastr.success(' Reminders successfully updated', "", {
                            progressBar: !0,
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        afterLoader();
                    }
                }
            });
        });
      
    });

</script>
