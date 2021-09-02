<div id="showError" style="display:none"></div>
<form class="EditNote" id="EditNote" name="EditNote" method="POST">
    <span id="response"></span>
    @csrf
    <div class="col-md-12">
        <input class="form-control" value="{{$id}}" id="user_id" maxlength="250" name="id"
        type="hidden">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Note For</label>
                 <div class="col-sm-8">
                    {{$LeadAdditionalInfo['potential_case_title']}}
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Date</label>
            <div class="col-sm-9">
                <input class="form-control" value="{{date('m/d/Y',strtotime(convertUTCToUserDate($LeadNotes['note_date'], auth()->user()->user_timezone)))}}" id="dateadded" maxlength="250" name="note_date"
                    type="text">
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Activity</label>
            <div class="col-md-9 form-group mb-3">
                <select class="form-control country" id="note_activity" name="note_activity" style="width: 100%;">
                    <?php foreach($LeadActivity as $key=>$val){?>
                    <option <?php if($val->id==$LeadNotes['note_activity']) { echo "selected=selected"; } ?> value="{{$val->id}}"> {{$val->acrtivity_title}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Subject</label>
            <div class="col-sm-9">
                <input class="form-control" value="{{$LeadNotes['note_subject']}}" id="dateadded" maxlength="250" placeholder="Subject"
                    name="note_subject" type="text">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Notes</label>
            <div class="col-sm-9">
                <textarea rows="5" class="form-control" name="notes" placeholder="Notes">{{$LeadNotes['notes']}}</textarea>
            </div>
        </div>
        </span>
        <hr>
        <div class="loader-bubble loader-bubble-primary innerLoaderTime" id="innerLoaderTime" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1" id="submitButton" type="submit">Save
                Note</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        // $('#dateadded').datepicker({
        //     onSelect: function (dateText, inst) {},
        //     showOn: 'focus',
        //     showButtonPanel: true,
        //     closeText: 'Clear', // Text to show for "close" button
        //     onClose: function (selectedDate) {
        //         var event = arguments.callee.caller.caller.arguments[0];
        //         if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
        //             $(this).val('');
        //         }
        //     }
        // });
        $('#dateadded').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            "orientation": "bottom",
            'todayHighlight': true
        });

        $(".innerLoaderTime").css('display', 'none');
        $(".innerLoaderTime").hide();
        $("#EditNote").validate({
            rules: {
                notes: {
                    required: true
                },
                note_date: {
                    required: true
                }
            },
            messages: {
                notes: {
                    required: "Notes is a required field",
                },
                note_date: {
                    required: "Date is a required field",
                },
            },

            errorPlacement: function (error, element) {

            }
        });
    });

    $('#EditNote').submit(function (e) {
        $("#submitButton").attr("disabled", true);
        $(".innerLoaderTime").css('display', 'block');
        e.preventDefault();

        if (!$('#EditNote').valid()) {
            $(".innerLoaderTime").css('display', 'none');
            $('#submitButton').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#EditNote").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/updateCaseNotePopup", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                $(".innerLoaderTime").css('display', 'block');
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
                    $(".innerLoaderTime").css('display', 'none');
                    $('#submitButton').removeAttr("disabled");
                    // $("#EditNote").scrollTop(0);
                    $('#EditNote').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    window.location.reload();
                }
            }
        });
    });
    $("#first_name").focus();
</script>
