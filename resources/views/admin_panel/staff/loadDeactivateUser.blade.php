<?php
$reasons = unserialize(REASON_TITLE); 
?>
<form class="saveDeactivateForm" id="saveDeactivateForm" name="saveDeactivateForm" method="POST">
    @csrf
    <div class="row">
        <input type="hidden" value="{{$user->id}}" name="user_id">
        <div class="col-md-12 form-group mb-3">
            <label for="picker2">Why are you deactivating this user?</label>
            <select name="reason" id="reason" class="form-control">
                <option value="">Select Reason</option>
                <?php foreach($reasons as $kk=>$vv){?>
                    <option value="{{$kk}}">{{$vv}}</option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-12 form-group mb-3" id="reasonBox">
            <label for="picker1">Tell us more about why you're deactivating this person:</label>
            <textarea class="form-control" name="other_reason"></textarea>
        </div>

        <div class="col-md-12 form-group mb-3">
            <label for="picker1"><b>Reassign Tasks and Events</b>
            </label>
            <label class="switch pr-5 switch-success mr-3"><span>Reassign this user's tasks and events to:
                </span>
                <input type="checkbox" name="assigntouser" id="assigntouser" value="1"><span class="slider"></span>
            </label>
        </div>
        <div class="col-md-12 form-group mb-3">
            <select name="assign_to" class="form-control" id="assign_to" disabled>
                <option value="">Select User</option>
                @foreach($allUser as $k=>$v)
                <option value="{{$v->id}}">{{ $v->first_name.' '.$v->last_name}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12 form-group mb-3">
            <label for="picker1">User might be linked to additional court cases for events and tasks to be reassigned
                correctly.
            </label>
            <br><br>
            <label for="picker1"><b>Are you sure you want to deactivate test asdsa?</b><br>
                You will not be able to reactivate this user for 30 days.
            </label>

        </div>
        
        <div class="col-md-12">
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader"></div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit"
                    data-style="expand-left"><span class="ladda-label">Confirm
                        Deactivation</span><span class="ladda-spinner"></span><span
                        class="ladda-spinner"></span></button>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
    
    $(document).ready(function () {
        $("#innerLoader").css('display', 'none');
        $("#response").hide();
        $("#saveDeactivateForm").validate({
            rules: {
                reason: {
                    required: true
                },
                other_reason: {
                    required: {
                        depends: function (element) {
                            var status = false;
                            if ($("#reason :selected").val() == 5) {
                                var status = true;
                            }
                            return status;
                        }
                    }
                },
                assign_to: {
                    required: {
                        depends: function (element) {
                            var status = false;
                            if ($("#assigntouser:checked").val() !== undefined) {
                                var status = true;
                                
                            }
                            return status;
                        }
                    }
                }
            },
            messages: {
                reason: {
                    required: "Please select a reason for deactivating this user"
                },
                other_reason: {
                    required: "Please tell us more about why you're deactivating this person"
                },
                assign_to: {
                    required: "Please select a user to reassign tasks and events"
                },

            },

            errorPlacement: function (error, element) {
                if (element.is('#default_rate')) {
                    error.appendTo('#TypeError');
                } else {
                    element.after(error);
                }
            }
        });

        $("#reasonBox").hide();
        $('#reason').change(function () {
            var selValue = $("#reason :selected").val();
            if (selValue == 5) {
                $("#reasonBox").show();
            } else {

                $("#reasonBox").hide();
            }
        });

        $("#assign_to").attr('disabled', true);
        $("input[name='assigntouser']").on("change", function () {
            var radioValue = $("input[name='assigntouser']:checked").val();
            if (radioValue == "1") {
                $('#assign_to').removeAttr("disabled");
            } else {
                $("#assign_to").attr('disabled', true);
            }
        });

        $('#saveDeactivateForm').submit(function (e) {
            $("#innerLoader").css('display', 'block');
            e.preventDefault();

            if (!$('#saveDeactivateForm').valid()) {
                $("#innerLoader").css('display', 'none');
                return false;
            }

            var dataString = $("#saveDeactivateForm").serialize();
            var url = "{{ route('admin/deactivateStaff') }}";
            $.ajax({
                type: "POST",
                url: url, // json datasource
                headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
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
                        return false;
                    } else {
                        window.location.reload();
                    }
                }
            });
        });
    });
</script>