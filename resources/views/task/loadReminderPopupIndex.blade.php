<form class="editReminderIndexIndex" id="editReminderIndex" name="editReminderIndex" method="POST">
    <input class="form-control" id="id" value="{{$task_id}}" name="task_id" type="hidden">
    <input class="form-control" id="id" value="{{$from_view}}" name="from_view" type="hidden">

    @csrf
    <div class="row" bladefile="resources/views/task/loadReminderPopupIndex.blade.php">
    <div class="col-md-12">

        <div class="text-muted mb-3">You can only edit reminders that you created. Reminders assigned to you by another
            firm user will need to be edited by the creator.</div>
      
            <div class="form-group row">
                <label for="reminders" class="col-sm-1 col-form-label"></label>
                <div class="col">
                    <div>
                        <?php
                            foreach($TaskReminder as $rkey=>$rval){
                            ?>
                            <div class="row form-group fieldGroup">
                                <div class="">
                                    <div class="d-flex col-12 pl-0 align-items-center">
                                        <div class="pl-0 col-2">
                                            <select id="reminder_user_type" name="reminder_user_type[]" class="form-control custom-select  ">
                                                @forelse (reminderUserType() as $key => $item)
                                                <option value="{{ $key }}" {{ ($rval->reminder_user_type == $key) ? 'selected' : '' }}>{{ $item }}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                        </div>
                                        <div class="pl-0 col-2">
                                            <select id="reminder_type" name="reminder_type[]" class="form-control custom-select valid" aria-invalid="false">
                                            @foreach(getEventReminderTpe() as $k =>$v)
                                                                    <option value="{{$k}}" <?php if($rval->reminder_type == $k){ echo "selected=selected"; } ?>>{{$v}}</option>
                                                            @endforeach
                                            </select>
                                        </div>
                                        <input name="reminder_number[]" class="form-control col-2 reminder-number" value="{{$rval->reminer_number}}">
                                        <div class="col-3">
                                            <select id="reminder_time_unit" name="reminder_time_unit[]" class="form-control custom-select  ">
                                            <option <?php if($rval->reminder_frequncy=="day"){ echo "selected=selected"; } ?> value="day">days</option>
                                                <option <?php if($rval->reminder_frequncy=="week"){ echo "selected=selected"; } ?> value="week">weeks</option>
                                            </select>
                                        </div> before task &nbsp;
                                        <button class="btn remove" type="button">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        <div class="fieldGroup">
                        </div>
                        <div><button type="button" class="row btn btn-link p-0 test-add-new-reminder">Add a reminder</button></div>
                    </div>
                </div>
            </div>  

            <div class="fieldGroupCopy copy hide" style="display: none;">
                <div class="">
                    <div class="d-flex col-12 pl-0 align-items-center">
                        <div class="pl-0 col-2">
                            <select id="reminder_user_type" name="reminder_user_type[]"
                                class="form-control custom-select  ">
                                <option value="me">Me</option>
                                <option value="attorney">Attorneys</option>
                                <option value="paralegal">Paralegals</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                        <div class="pl-0 col-2">
                            <select id="reminder_type" name="reminder_type[]"
                                class="form-control custom-select  ">
                                @foreach(getEventReminderTpe() as $k =>$v)
                                        <option value="{{$k}}">{{$v}}</option>
                                @endforeach
                            </select>
                        </div>
                        <input name="reminder_number[]" class="form-control col-2 reminder-number" value="1">
                        <div class="col-3">
                                <select id="reminder_time_unit" name="reminder_time_unit[]"
                                    class="form-control custom-select  ">
                                    <option value="day">days</option>
                                    <option value="week">weeks</option>
                                </select>
                        </div> before task &nbsp;
                        <button class="btn remove" type="button">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>

    </div>
</div><!-- end of main-content -->
<div class="modal-footer">
    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
    <button type="submit" name="submit" class="btn btn-primary">Set Reminders</button>
</div>
</form>
<script type="text/javascript">
    $(document).ready(function () {

        $(".test-add-new-reminder").click(function () {
          
            var fieldHTML = '<div class="row form-group fieldGroup">' + $(".fieldGroupCopy").html() +
                '</div>';
               
            $('body').find('#reminderDataIndex .fieldGroup:last').after(fieldHTML);
        });
        $('#editReminderIndex').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroup').remove();
        });


        
        $('#editReminderIndex').submit(function (e) {
            e.preventDefault();
            // $("#submit").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            if (!$('#editReminderIndex').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString ='';
             dataString = $("#editReminderIndex").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/saveTaskReminderPopup", // json datasource
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
                        return false;
                    } else {    
                        toastr.success('Reminders successfully updated', "", {
                                positionClass: "toast-top-full-width",
                                containerId: "toast-top-full-width"
                            });
                            $("#loadReminderPopupIndex").modal('hide');
                            $("#loadReminderPopupIndexCommon").modal('hide');

                        
                        $("#innerLoader").css('display', 'none');
                         if(res.setSession!=''){
                            window.location.reload();
                         }
                       
                    }
                }
            });
        });
      
    });

</script>
