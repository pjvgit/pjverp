<form class="AddNote" id="AddNote" name="AddNote" method="POST">

    <span id="response"></span>
    @csrf
    <div id="showError" class="showError" style="display:none"></div>
    <div class="col-md-12">
        <input class="form-control" value="{{$client_id}}" id="client_id" maxlength="250" name="client_id"
            type="hidden">
        <input class="form-control" value="{{$note_id}}" id="note_id" maxlength="250" name="note_id" type="hidden">
        <input class="form-control" value="" id="currentButton" maxlength="250" name="currentButton" type="hidden">

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Case / Contact</label>
            <div class="col-sm-9">
                {{$userData['first_name']}} {{$userData['middle_name']}} {{$userData['last_name']}}

            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Date</label>
            <div class="col-sm-9">
                <input class="form-control field" value="{{convertUTCToUserTimeZone('dateOnly')}}" id="dateadded" maxlength="250" name="note_date"
                    type="text">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Subject</label>
            <div class="col-sm-9">
                <input class="form-control field" value="" id="dateadded" maxlength="250" placeholder="Subject"
                    name="note_subject" type="text">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Notes</label>
            <div class="col-sm-9">

                <div id="editor" class="field">

                </div>
            </div>
        </div>
        </span>
        <br>
        <div class="justify-content-between modal-footer">
            <div id="status" class="px-2" style="display: none;">Saved</div>
            &nbsp;
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;">
            </div>
            <div>
                {{-- <a href="#">
                    <button class="btn btn-secondary  btn-rounded m-1" type="button"
                        data-dismiss="modal">Cancel</button>
                </a> --}}
                <button class="btn btn-outline-danger btn-rounded  m-1" id="discard_draft" value="discard_draft"
                    type="button" onclick="discardNotes({{$note_id}})">Discard</button>

                <button class="btn btn-outline-secondary btn-rounded  m-1" id="saveandtime" onclick="setButton('st')"  value="saveandtime"
                    type="submit">Save + <i class="far fa-clock fa-lg"></i></button>


                <button class="btn btn-primary  btn-rounded m-1 submit" id="submitButton" value="savenote" onclick="setButton('s')" type="submit">Save</button>
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

            errorPlacement: function (error, element) {

            }
        });
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
            return false;
        }
        if (!$('#AddNote').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#AddNote").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/saveNote", // json datasource
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
                    quill.root.innerHTML = '';
                    afterLoader()
                    if($("#currentButton").val()=='s'){
                        window.location.reload();
                    }else{
                        $("#addNoteModal").modal("hide");
                        localStorage.setItem("addTimeEntry", "{{$client_id}}");
                    }   
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
    function autosave() {
        afterLoader();
        $("#status").show();
        $("#status").html("Processing....");
        var delta = quill.root.innerHTML;
        var dataString = '';
        dataString = $("#AddNote").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/saveNote", // json datasource
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
    function setButton(id){
        $("#currentButton").val(id);
    } 
    $("#first_name").focus();

</script>
