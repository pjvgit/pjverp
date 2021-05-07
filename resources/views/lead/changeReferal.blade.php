<div id="showError" style="display:none"></div>
<form class="EditFormData" id="EditFormData" name="EditFormData" method="POST">
    <input class="form-control" id="id" value="{{$id}}" name="id" type="hidden">

    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Update Lead Referral Source</label>
            <div class="col-sm-9">
                <select class="form-control contact_group" id="referal_source" name="referal_source"
                    data-placeholder="Select Referral Source">
                    <option value="">Select Referral Source</option>
                    <?php 
                    foreach($ReferalResource as $kcs=>$vcs){?>
                    <option <?php if($vcs->id==$referal_source){ echo "selected=selected"; }?> value="{{$vcs->id}}">{{$vcs->title}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <hr>
        <div class="loader-bubble loader-bubble-primary" id="innerLoader3" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit">Save
                Lead Referral Source</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {

        $("#innerLoader").css('display', 'none');
        $("#innerLoader").hide();


        $("#EditFormData").validate({
            rules: {
                lead_referral_source: {
                    required: true,
                   
                }
            },
            messages: {
                lead_referral_source: {
                    required: "Lead Referral Source is a required field.",
                   
                }
            }
        });

    });

    $('#EditFormData').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#EditFormData').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#EditFormData").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/lead_setting/changeSaveReferalResource", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
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
                    toastr.success('Your lead referral source has been updated.', "", {
                        progressBar: !0,
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                    $("#changeSource").modal("hide");
                }
            }
        });
    });

</script>
