<form class="AddNote" id="AddNote" name="AddNote" method="POST">
    <span id="response"></span>
    @csrf
    <div id="showError" class="showError" style="display:none"></div>
    <div class="col-md-12">
        <input class="form-control" value="{{$casae_id}}" id="case_id" maxlength="250" name="case_id" type="hidden">
        <input class="form-control" value="{{$note_id}}" id="note_id" maxlength="250" name="note_id" type="hidden">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Case / Contact</label>
            <div class="col-sm-9">{{$caseMaster['case_title']}}</div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Date</label>
            <div class="col-sm-9">
                <input class="form-control field" value="{{date('m/d/Y',strtotime(convertUTCToUserDate($ClientNotes['note_date'], auth()->user()->user_timezone)))}}" id="dateadded" maxlength="250" name="note_date"
                    type="text">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Subject</label>
            <div class="col-sm-9">
                <input class="form-control field" value="{{$ClientNotes['note_subject']}}" id="dateadded" maxlength="250" placeholder="Subject"
                    name="note_subject" type="text">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Notes</label>
            <div class="col-sm-9">

                <div id="editor"  class="field">
                
                </div>
            </div>
        </div>
        <br>
        <div class="justify-content-between modal-footer">
            <div id="status" class="px-2" style="display: none;">Saved</div>
            &nbsp;
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;">
            </div>
            <div>
                {{-- <a href="#"><button class="btn btn-secondary  btn-rounded m-1" type="button" data-dismiss="modal">Cancel</button> </a> --}}
                <button class="btn btn-outline-danger btn-rounded  m-1" id="discard_draft" value="discard_draft" type="button" onclick="discardDeleteNotes({{$ClientNotes['id']}})">Discard Draft</button>
                <button class="btn btn-outline-secondary btn-rounded  m-1" id="save_draft" value="save_draft" type="submit">Save Draft</button>
                <button class="btn btn-primary  btn-rounded m-1 submit" id="submitButton" value="publish_note" type="submit">Publish Save</button>
            </div>
        </div>
    </div>
    <input class="form-control" value="" id="current_submit" maxlength="250" name="current_submit" type="hidden">

</form>


<style>
    body>#editor {
        margin: 50px auto;
        max-width: 720px;
    }

    #editor {
        height: 300px;
        background-color: white;
    }

</style>
<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();
        $(document).on("click", ":submit", function(e){
            $("#current_submit").val($(this).val());
        });
        var timeout;
        $('body').on('keyup', '.field', function (event) {
           
            if (timeout)
                clearTimeout(timeout);
            timeout = setTimeout(function (event) {
                autosave();
            }, 400); //i find 400 milliseconds works good
        });

        $("#AddNote").validate({
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
                    required: "Note can't be blank",
                },
                note_date: {
                    required: "Date is a required field",
                },
            },
        });
    });
    $(document).on("click", ":submit", function(e){
        $("#current_submit").val($(this).val());
    });
    $('#AddNote').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        var delta = quill.root.innerHTML;
        if (delta == '<p><br></p>') {
            toastr.error('Unable to post a blank note', "", {
                positionClass: "toast-top-full-width",
                containerId: "toast-top-full-width"
            })
            afterLoader();

        }
        if (!$('#AddNote').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#AddNote").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/updateNote", // json datasource
            data: dataString + '&delta=' + delta,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                $("#innerLoaderTime").css('display', 'block');
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
                    afterLoader();
                    // $("#AddNote").scrollTop(0);
                    $('#AddNote').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    // toastr.success('Your note was posted', "", {
                    //     positionClass: "toast-top-full-width",
                    //     containerId: "toast-top-full-width"
                    // });
                    quill.root.innerHTML = '';
                    $("#addNoteModal").modal("hide");
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

    function autosave() {
        afterLoader();
        $("#status").show();
        $("#status").html("Processing....");
        var delta = quill.root.innerHTML;
        var dataString = '';
        dataString = $("#AddNote").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/updateNote", // json datasource
            data: dataString + '&delta=' + delta,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                $("#innerLoaderTime").css('display', 'block');
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
                    afterLoader();
                    // $("#AddNote").scrollTop(0);
                    $('#AddNote').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {

                    $("#status").html("Saved");
                    afterLoader()
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
    }

    var toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'], // toggled buttons
        ['blockquote', 'code-block'],
        [{
            'header': 1
        }, {
            'header': 2
        }], // custom button values
        [{
            'list': 'ordered'
        }, {
            'list': 'bullet'
        }],

        [{
            'size': ['small', false, 'large', 'huge']
        }], // custom dropdown
        [{
            'header': [1, 2, 3, 4, 5, 6, false]
        }],

        [{
            'color': []
        }, {
            'background': []
        }], // dropdown with defaults from theme
        [{
            'font': []
        }],
        [{
            'align': []
        }],

        ['clean'] // remove formatting button
    ];

    var quill = new Quill('#editor', {
        modules: {
            toolbar: toolbarOptions
        },
        theme: 'snow'
    });
    quill.root.innerHTML = '{!! !empty($ClientNotes['notes']) ? $ClientNotes['notes'] : ''  !!} ';
    $("#first_name").focus();

</script>
