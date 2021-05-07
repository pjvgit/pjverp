<div id="showError" style="display:none"></div>
<form class="EditFormData" id="EditFormData" name="EditFormData" method="POST">
    <input class="form-control" id="id" value="{{$id}}" name="id" type="hidden">

    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <div class="form-group row" id="show_contact_group_dropdown">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Reason</label>
            <div class="col-sm-6">
                <select class="form-control contact_group" id="not_hire_reasons_id" name="not_hire_reasons_id"
                    data-placeholder="Search No Hire Reasons">
                    <?php 
                    foreach($HireReason as $kcs=>$vcs){?>
                    <option value="{{$vcs->id}}">{{$vcs->title}}</option>
                    <?php } ?>
                </select>
               
            </div>
            <label for="inputEmail3" class="col-sm-3 col-form-label"> 
                <a onclick="openNewContactGroup();" href="javascript:;">Add new referral source</a>
            </label>
        </div>
        <div class="form-group row" id="show_contact_group_text">
            <label for=" inputEmail3" class="col-sm-2 col-form-label">Reason</label>
            <div class="col-md-6 form-group mb-3">
                <input class="form-control" id="not_hire_reasons_text" value="" maxlength="255" name="not_hire_reasons_text"
                    type="text" placeholder="">
            </div>
            <label for="inputEmail3" class="col-sm-3 col-form-label"> 
                <a onclick="openOldContactGroup();" href="javascript:;">Cancel</a>
            </label>
        </div>
        <hr>
        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader3" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit">Mark As No Hire</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {

        $(".innerLoader").css('display', 'none');
        $(".innerLoader").hide();
        $("#EditFormData").validate({
            rules: {
                not_hire_reasons_id: {
                    required: true,
                },
                not_hire_reasons_text: {
                    required: true,
                }
            },
            messages: {
                not_hire_reasons_id: {
                    required: "Please select atleast one no hire reason.",
                },
                not_hire_reasons_text: {
                    required: "Please enter no hire reason.",
                }
            }
        });

    });

    $('#EditFormData').submit(function (e) {
        $(".submit").attr("disabled", true);
        $(".innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#EditFormData').valid()) {
            $(".innerLoader").css('display', 'none');
            $('.submit').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#EditFormData").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/lead_setting/SavedoNotHire", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                $(".innerLoader").css('display', 'block');
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
                    $(".innerLoader").css('display', 'none');
                    $('.submit').removeAttr("disabled");
                    return false;
                } else {
                    toastr.success('Your lead has been marked as no hire.', "", {
                        progressBar: !0,
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                   window.location.reload();
                }
            }
        });
    });

    function openNewContactGroup() {
        $("#show_contact_group_text").show();
        $("#show_contact_group_dropdown").hide();
        return false;
    }

    function openOldContactGroup() {
        $("#show_contact_group_text").hide();
        $("#show_contact_group_dropdown").show()
        return false;
    }
    $("#show_contact_group_text").hide();

</script>
