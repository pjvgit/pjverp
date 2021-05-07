<div id="showError" style="display:none"></div>
<form class="EditStatus" id="EditStatus" name="EditStatus" method="POST">
    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <div class="form-group row">
            <div class="col-sm-12">
                <select class="form-control contact_group" id="status" name="status"
                data-placeholder="Select...">
                <option value="">Select...</option>
                <?php 
                foreach($LeadStatus as $kcs=>$vcs){?>
                <option  value="{{$vcs->id}}">{{$vcs->title}}</option>
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
            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit">Submit</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $("#innerLoader").css('display', 'none');
        $("#innerLoader").hide();
        $("#EditStatus").validate({
            rules: {
                status: {
                    required: true,
                }
            },
            messages: {
                status: {
                    required: "Please select a status.",
                }
            }
        });
    });

    $('#EditStatus').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#EditStatus').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#EditStatus").serialize();
        var array = [];
        $("input[class=leadRow]:checked").each(function (i) {
            array.push($(this).val());
        });
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/saveChangeBulkStatus", // json datasource
            data: dataString + '&leads_id=' + JSON.stringify(array),
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
                    window.location.reload();
                }
            }
        });
    });

</script>
