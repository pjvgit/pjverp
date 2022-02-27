<form class="editReminderIndexIndex" id="editReminderIndex" name="editReminderIndex" method="POST">
    <input class="form-control" id="event_id" value="{{ $event_id}}" name="event_id" type="text">
    <input class="form-control" id="event_recurring_id" value="{{ $event_recurring_id}}" name="event_recurring_id" type="text">
    @csrf
    <div class="row">
    <div class="col-md-12">

        <div class="text-muted mb-3">You can only edit reminders that you created. Reminders assigned to you by another
            firm user will need to be edited by the creator.</div>
      
            <div class="form-group row">
                <label for="reminders" class="col-sm-2 col-form-label">Reminders</label>
                <div class="col">
                    <div>
                        @forelse($eventReminder as $rkey => $rval)
                            <div class="row form-group fieldGroup">
                                <div class="">
                                    <div class="d-flex col-10 pl-0 align-items-center">
                                        <div class="pl-0 col-3">
                                            <div>
                                                <input type="hidden" name="reminder[id][]" value="{{ $rkey }}">
                                                <div class="">
                                                    {{-- <select id="reminder_user_type" name="reminder_user_type[]" class="form-control custom-select  "> --}}
                                                    <select id="reminder_user_type" name="reminder[user_type][]" class="form-control custom-select  ">
                                                        @forelse (reminderUserType() as $key => $item)
                                                        <option value="{{ $key }}" {{ ($rval->reminder_user_type == $key) ? "selected" : "" }}>{{ $item }}</option>
                                                        @empty
                                                        @endforelse
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pl-0 col-3">
                                            <div>
                                                <div class="">
                                                    {{-- <select id="reminder_type" name="reminder_type[]" class="form-control custom-select valid" aria-invalid="false"> --}}
                                                    <select id="reminder_type" name="reminder[type][]" class="form-control custom-select valid" aria-invalid="false">
                                                        @foreach(getEventReminderTpe() as $k =>$v)
                                                                <option value="{{$k}}" <?php if($rval->reminder_type == $k){ echo "selected=selected"; } ?>>{{$v}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div><input name="reminder[number][]" class="form-control col-2 reminder-number" value="{{$rval->reminer_number}}">
                                        <div class="col-4">
                                            <div>
                                                <div class="">
                                                    {{-- <select id="reminder_time_unit" name="reminder_time_unit[]" class="form-control custom-select  "> --}}
                                                    <select id="reminder_time_unit" name="reminder[time_unit][]" class="form-control custom-select  ">
                                                        <option <?php if($rval->reminder_frequncy=="minute"){ echo "selected=selected"; } ?> value="minute">minutes</option>
                                                        <option <?php if($rval->reminder_frequncy=="hour"){ echo "selected=selected"; } ?> value="hour">hours</option>
                                                        <option <?php if($rval->reminder_frequncy=="day"){ echo "selected=selected"; } ?> value="day">days</option>
                                                        <option <?php if($rval->reminder_frequncy=="week"){ echo "selected=selected"; } ?> value="week">weeks</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn remove" type="button" data-remind-id="{{ $rkey }}">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                        @endforelse
                        <div class="fieldGroup">
                        </div>
                        <div><button type="button" class="btn btn-link p-0 test-add-new-reminder add-more-reminder">Add a reminder</button></div>
                    </div>
                </div>
            </div>  

    </div>
</div><!-- end of main-content -->
<div class="modal-footer">
    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
    <button type="submit" name="submit" class="btn btn-primary">Set Reminders</button>
</div>
<input type="hidden" id="deleted_reminder_id" name="deleted_reminder_id" >
</form>
{{-- Copy reminder fields --}}
<div class="fieldGroupCopy copy hide" style="display: none;">
    <div class="">
        <div class="d-flex col-10 pl-0 align-items-center">
            <div class="pl-0 col-3">
                <div>
                    <div class="">
                        <select id="reminder_user_type" name="reminder[user_type][]"
                            class="form-control custom-select  ">
                            @forelse (reminderUserType() as $key => $item)
                            <option value="{{ $key }}">{{ $item }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                </div>
            </div>
            <div class="pl-0 col-3">
                <div>
                    <div class="">
                        <select id="reminder_type" name="reminder[type][]"
                            class="form-control custom-select  ">
                            @foreach(getEventReminderTpe() as $k =>$v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div><input name="reminder[number][]" class="form-control col-2 reminder-number" value="1">
            <div class="col-4">
                <div>
                    <div class="">
                        <select id="reminder_time_unit" name="reminder[time_unit][]"
                            class="form-control custom-select  ">
                            <option value="minute">minutes</option>
                            <option value="hour">hours</option>
                            <option value="day">days</option>
                            <option value="week">weeks</option>
                        </select>
                    </div>
                </div>
            </div>
            <button class="btn remove" type="button" data-remind-id="">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {

        $(".add-more-reminder").click(function () {
            var fieldHTML = '<div class="row form-group fieldGroup">' + $(".fieldGroupCopy").html() +
                '</div>';
            $('body').find('.fieldGroup:last').before(fieldHTML);
        });
        $('#editReminderIndex').on('click', '.remove', function () {
            var remindId = $(this).attr("data-remind-id");
            if($("#deleted_reminder_id").val() != '')
                $("#deleted_reminder_id").val($('#deleted_reminder_id').val() + ','+remindId);
            else
                $("#deleted_reminder_id").val($('#deleted_reminder_id').val() + remindId);
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
             dataString = $("form").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveEventReminderPopup", // json datasource
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
                        $("#loadEventReminderPopup").modal('hide');
                        $("#innerLoader").css('display', 'none');
                        loadReminderHistory($("#event_id").val(), $("#event_recurring_id").val());
                    }
                }
            });
        });
      
    });

</script>
