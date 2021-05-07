<form class="editActivityForm" id="editActivityForm" name="editActivityForm" method="POST">
    <span id="response"></span>
    @csrf
    <input class="form-control field" value="{{$activityData->id}}" maxlength="250" name="id" type="hidden">
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Name</label>
            <div class="col-sm-9">
                <input class="form-control field" value="{{($activityData->title)??''}}" maxlength="250" name="activity_title" type="text">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Default description </label>
            <div class="col-sm-9">
                <textarea class="form-control field" name="description" rows="3">{{($activityData->default_description)??''}}</textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">&nbsp;</label>
            <div class="col-md-9 form-group mb-3">
                <label class="switch pr-5 switch-success mr-3"><span>Flat Fee Activity</span>
                    <input type="checkbox" name="flat_fees" id="flat_fees" <?php if($activityData->flat_fees!="0.00"){ echo "checked=checked";} ?>> <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="form-group row" id="showAmount"  <?php if($activityData->flat_fees=="0.00"){ ?> style="display:none;" <?php } ?>>
            <label for="inputEmail3" class="col-sm-3 col-form-label">Default fee</label>
            <div class="col-sm-4">
                <input class="form-control field" id="amountinput" value="{{($activityData->flat_fees)??''}}" maxlength="20" name="default_fees"
                    type="number">
            </div>
        </div>
        </span>
        <div class="modal-footer">
            <div class="col-md-2 form-group">
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
                </div>
            </div>
            <a href="#">
                <button class="btn btn-secondary  btn-rounded mr-1 " type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary  btn-rounded submit " id="submitButton" value="savenote" type="submit">Update
                Activity</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();
        $("#editActivityForm").validate({
            rules: {
                activity_title: {
                    required: true
                }
            },
            messages: {
                activity_title: {
                    required: "Name can't be blank",
                }
            }
        });
    });
    $('.showError').html('');
    afterLoader();
    $('#editActivityForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#editActivityForm').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#editActivityForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/activities/updateActivity", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&update=yes';
            },
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
                    $('#editActivityForm').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    afterLoader()
                    window.location.reload();
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        });
    });

    $('input[name="flat_fees"]').click(function () {
        if ($("#flat_fees").prop('checked') == true) {
            $("#showAmount").show();
        } else {
            $("#showAmount").hide();
        }
    });
    $('#amountinput').on('keypress', function (event) {
        var regex = new RegExp("^[.0-9]+$");
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
    });
    $("#activity_title").focus();

</script>
