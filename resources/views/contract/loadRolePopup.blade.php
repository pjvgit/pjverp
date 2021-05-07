<form class="saveRole" id="saveRole" name="saveRole" method="POST">
    @csrf
    <input type="hidden" name="user_id" value="{{$user_id}}"/>
    <input type="hidden" name="case_id" value="{{$case_id}}"/>
    <div class="col-md-12">
        <span id="response"></span>
        
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
            <select class="custom-select text-nowrap user_role" name="user_role">
                <option value="">Select Role</option>
                @foreach($UserRole as $role)
                <option value="{{$role->id}}" @if($user->user_role==$role->id) selected="selected" @endif>{{$role->role_name}}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>

            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit"
                data-style="expand-left"><span class="ladda-label">Update Role</span><span
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
        $(".user_role").select2({
            placeholder: "Select a role",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#changeRole"),
        });

        $("#innerLoader").hide();

        $("#response").hide();

        $('#saveRole').submit(function (e) {
            e.preventDefault();
            $("#innerLoader").css('display', 'block');
            if (!$('#saveRole').valid()) {
                $("#innerLoader").css('display', 'none');
                $('.submitbutton').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#saveRole").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/saveRolePopup", // json datasource
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
                        $('.submitbutton').removeAttr("disabled");
                        $('#loadExpenseEntryPopup').animate({ scrollTop: 0 }, 'slow');

                        return false;
                    } else {
                            window.location.reload();  
                    }
                },error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $('#loadExpenseEntryPopup').animate({ scrollTop: 0 }, 'slow');
                    afterLoader();
                }
            });
        });

    });

</script>
