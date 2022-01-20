<form class="sendEmails" id="sendEmails" name="sendEmails" method="POST">
    @csrf
    <input class="form-control" id="current_contact" name="current_contact" type="hidden" value="{{ ($_REQUEST['page'] == 'user_id') ? $_REQUEST['id'] : ''}}">
    <input class="form-control" id="current_case" name="current_case" type="hidden" value="{{ ($_REQUEST['page'] == 'case_id') ? $_REQUEST['id'] : ''}}">
    <div id="showError" class="showError" style="display:none"></div>
    <span id="response"></span>
    <div class="col-md-12" bladeFile="resources/views/client_dashboard/sendMessage.blade.php">
        <div class="row">
            <div class="col-md-8 form-group mb-3">
                <label for="firstName1">Send To</label>
                <span id="to_user_input_area">
                    <select class="form-control contact_group" id="sendto" name="send_to[]" data-placeholder="Type a name"
                        multiple>
                        <optgroup label="Contact">
                            <?php foreach($clientLists as $clientekey=>$clientval){ ?>
                                <option uType="client" data-set="client" value="client-{{$clientval->id}}" <?php echo (isset($_REQUEST['id']) && $_REQUEST['page'] == 'user_id' && $_REQUEST['id'] == $clientval->id) ? "selected" : ""; ?>>
                                    {{substr($clientval->first_name,0,100)}} {{substr($clientval->last_name,0,100)}} </option>
                                    <?php } ?>
                        </optgroup>
                        <optgroup label="Companies">
                            <?php foreach($CaseMasterCompany as $companykey=>$companyval){ ?>
                            <option uType="company" data-set="company" value="company-{{$companyval->id}}" <?php echo (isset($_REQUEST['id']) && $_REQUEST['page'] == 'user_id' && $_REQUEST['id'] == $companyval->id) ? "selected" : ""; ?>>
                                {{substr($companyval->first_name,0,100)}} </option>
                            <?php } ?>
                        </optgroup>
                        <optgroup label="Cases">
                            <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                            <option uType="case" data-set="case" value="case-{{$Caseval->id}}">
                                {{substr($Caseval->case_title,0,100)}}
                                <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?> </option>
                            <?php } ?>
                        </optgroup>
                        <optgroup label="Firm Users">
                            <?php foreach($loadFirmUser as $staffkey=>$staffval){ ?>
                            <option uType="staff" data-set="staff" value="staff-{{$staffval->id}}" <?php echo (isset($_REQUEST['id']) && $_REQUEST['id'] == $staffval->id) ? "selected" : ""; ?> >
                                {{substr($staffval->first_name,0,100)}} {{substr($staffval->last_name,0,100)}} </option>
                            <?php } ?>
                        </optgroup>
                    </select>
                    <span id="callername"></span>
                </span>

                <?php /* 
                <div id="to_autocomplete_spinner" style="position: absolute; top: 28px; right: 0px; display: none;">
                    <img src="{{BASE_URL}}images/ajax_arrows.gif">
                </div>
                <ul id="searchResult" class="ui-menu"></ul>
                <div class="clear"></div>
                <div id="userDetail"></div>
                <div id="to_selections" class="clearfix">
                    <div id="to_user_21260036" class="badge badge-light border p-2 my-1 mr-1">
                        <span> Client3 m name (Client)
                            <a href="#" onclick="Effect.Fade('to_user_21260036', {duration: 0.3, afterFinish: function() {$('to_user_21260036').remove();}}); return false;">
                                <i class="fas fa-times-circle text-dark"></i>
                            </a>
                            <input type="hidden" name="to[]" id="to_" value="21260036">
                        </span>
                    </div>
                    
                </div>
            </div>
            <div class="col-md-4 form-group mb-3 pt-4">
                <label for="firstName1">&nbsp;</label>
                <input type="checkbox" name="send_global" id="send_global" value="1">
                <label for="send_global">Send a global message</label>
            </div>
            */?>

                <div id="to_user_global_options"
                    style="border: 1px solid rgb(200, 200, 200); background-color: rgb(243, 243, 243); padding: 7px 15px; display: block;">
                    <input type="checkbox" name="message[global_clients]" id="message_global_clients">
                    <label for="message_global_clients">All Contacts</label>
                    &nbsp;&nbsp;
                    <input type="checkbox" name="message[global_lawyers]" id="message_global_lawyers">
                    <label for="message_global_lawyers">All Firm Users</label>
                </div>

            </div>
            <div class="col-md-4 form-group mb-3 pt-4">
                <label for="firstName1">&nbsp;</label>
                <input type="checkbox" name="send_global" id="send_global">
                <label for="send_global">Send a global message</label>
            </div>
        </div>
        <hr>
        <table>
            <tbody>
                <tr>
                    <td>
                        <label class="calico" for="private_reply">Replies</label>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td id="reply_options">
                        <input type="radio" value="false" checked="checked" name="message[private_reply]"
                            id="message_private_reply_false">
                        <label for="message_private_reply_false">Replies are sent to everyone</label><br>
                        <input type="radio" value="true" name="message[private_reply]" id="message_private_reply_true">
                        <label for="message_private_reply_true">Replies are sent only to me (private
                            message)</label>
                    </td>
                    <td></td>
                </tr>

            </tbody>
        </table>
        <div class="row">
            <div class="col-md-8 form-group mb-3">
                <label for="firstName1">Case Link</label>
                <select class="form-control contact_group" id="case_link" name="case_link"
                    data-placeholder="Type a name">
                    <option></option>
                    <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                    <option uType="case" data-set="case" value="{{$Caseval->id}}" <?php echo (isset($_REQUEST['id']) && $_REQUEST['page'] == 'case_id' && $_REQUEST['id'] == $Caseval->id) ? "selected" : ""; ?>>
                        {{substr($Caseval->case_title,0,100)}}
                        <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?> </option>
                    <?php } ?>

                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 form-group mb-3">
                <label for="firstName1">Subject</label>
                <input class="form-control" id="subject" name="subject" type="text" placeholder="">
            </div>

        </div>
        <div class="row">
            <div class="col-md-12 form-group mb-3">
                <div id="editor" class="field">
                </div>
            </div>
        </div>
        <br>
        <div class="justify-content-between modal-footer">
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;">
            </div>
            <a href="#" data-dismiss="modal" onclick="deleteDraft();"> <i class="fas fa-trash  align-middle"></i> Discard Draft</a>
            </button>
            &nbsp;
            
            <div>
                {{-- <a href="#">
                    <button class="btn btn-secondary  btn-rounded m-1" type="button"
                        data-dismiss="modal">Cancel</button>
                </a> --}}
                
                <button class="btn btn-outline-secondary btn-rounded  m-1" id="saveandtime" value="saveandtime"
                    type="submit">Save + <i class="far fa-clock fa-lg"></i>
                </button>
                <button class="btn btn-primary  btn-rounded m-1 submit" id="submitButton" value="savenote"
                    type="submit">Send Message
                </button>
            </div>
        </div>
    </div>
    <input class="form-control" value="" id="current_submit" maxlength="250" name="current_submit" type="hidden">
    <input class="form-control" value="" id="selected_case_id" maxlength="250" name="selected_case_id" type="hidden">
    <input class="form-control" value="" id="selected_user_id" maxlength="250" name="selected_user_id" type="hidden">

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

    /* .select2-results__group{ background-color:#4297d7 !important;color:white; } */
    .select2-results__group {
        border: 1px solid #4297d7;
        background: #5c9ccc url('{{BASE_URL}}icon/ui-bg_gloss-wave.png') 50% 50% repeat-x;
        color: #fff;
        font-weight: 700;
    }


    .clear {
        clear: both;
        margin-top: 20px;
    }

    #searchResult {
        list-style: none;
        padding: 0px;
        width: 480px;
        position: absolute;
        margin: 0;
        z-index: 9991;
        max-height: 300px !important;
        overflow-x: scroll;
        background-color: white;
        padding-left: 5px;
        max-height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 20px;
        z-index: 9991;
    }


    #searchResult li:hover {
        cursor: pointer;
    }

    .ui-widget-header {
        border: 1px solid #4297d7;
        background: #5c9ccc url('{{BASE_URL}}icon/ui-bg_gloss-wave.png') 50% 50% repeat-x;
        color: #fff;
        font-weight: 700;
    }

    .ui-menu-item a {
        text-decoration: none;
        display: block;
        padding: .2em .4em;
        line-height: 1.5;
        zoom: 1;
    }

    li {
        padding: 8px;
    }

    #searchResult li:hover {
        cursor: pointer;

        background-color: #d5e6f2;
    }

</style>
<script type="text/javascript">
    $(document).ready(function () {
        $("#to_user_global_options").hide();

        $("input:checkbox#send_global").click(function () {
            if ($(this).is(":checked")) {
                $("#to_user_global_options").show();
                $("#to_user_input_area").hide();
                $("#message_private_reply_false").attr("disabled", true);
                $("#message_private_reply_true").attr("disabled", true);;
                $("#case_link").attr("disabled", true);
            } else {
                $("#to_user_global_options").hide();
                $("#to_user_input_area").show();
                $("#message_private_reply_false").removeAttr("disabled");
                $("#message_private_reply_true").removeAttr("disabled");
                $("#case_link").removeAttr("disabled");

            }
        });
        afterLoader();
        $(document).on("click", ":submit", function (e) {
            $("#current_submit").val($(this).val());
        });
        // var timeout;
        // $('body').on('keyup', '.field', function (event) {

        //     if (timeout)
        //         clearTimeout(timeout);
        //     timeout = setTimeout(function (event) {
        //         autosave();
        //     }, 400); //i find 400 milliseconds works good
        // });
        $("#sendEmails").validate({
            rules: {
                send_to: {
                    required: true
                },
                subject: {
                    required: true
                }
            },
            messages: {
                send_to: {
                    required: "Send To can't be blank",
                },
                subject: {
                    required: "Subject can't be blank",
                },
            },

            errorPlacement: function (error, element) {
                if (element.is('#sendto')) {
                    error.appendTo('#callername');
                } else {
                    element.after(error);
                }
            }
        });
    });
    $("#sendto").select2({
        placeholder: "Search for an existing contact or company",
        theme: "classic",
        allowClear: true,
        dropdownParent: $("#addNewMessagePopup"),
    });
    $('#sendto').on("select2:select", function (e) {
        var userID = e.params.data.id.split('-');  
        if(userID[0] == 'staff' && userID[1] == "{{Auth::user()->id}}"){
            var wanted_id = e.params.data.id;
            var wanted_option = $('#sendto option[value="'+ wanted_id +'"]');
            wanted_option.prop('selected', false);
            $('#sendto').trigger('change.select2');            
            swal('Error!', 'You cannot send a message to yourself');        
        }else if(userID[0] == 'case'){
            var wanted_id = e.params.data.id;
            var wanted_option = $('#sendto option[value="'+ wanted_id +'"]');
            wanted_option.prop('selected', false);
            $('#sendto').trigger('change.select2');            
            $('#case_link').trigger('change').val(userID[1]);        
        }else if(userID[0] == 'company'){
                        
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/companyContactList",
                data: {
                    "company_id": userID[1]
                },
                success: function (res) {
                    if(res.errors == '0'){
                        $.each(res.contactList, function (key, value) {
                            $('#sendto option[value="client-'+value+'"]').prop('selected', true).trigger('change.select2');                      
                        });
                        
                    }else{
                        swal('Error!', res.msg);  
                    }  
                    var wanted_id = e.params.data.id;
                    var wanted_option = $('#sendto option[value="'+ wanted_id +'"]');
                    wanted_option.prop('selected', false);
                    $('#sendto').trigger('change.select2');                    
                }
            });
        }
    });

    $('#case_link').on('change',function(){
        if($(this).val() != ''){
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/checkBeforProceed",
                data: {
                    "case_id": $(this).val(),
                    "case_name": $("#case_link option:selected").text(),
                    "user_id": $("#user_id").val(),
                    "userName" : $("#userName").val(),
                },
                success: function (res) {
                    if (res.msg != "") {
                        swal({
                            title: 'Please note',
                            text: res.msg,
                            // type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#0CC27E',
                            cancelButtonColor: '#FF586B',
                            confirmButtonText: 'Send now',
                            cancelButtonText: 'Do not send',
                            confirmButtonClass: 'btn btn-primary ml-5',
                            cancelButtonClass: 'btn btn-default',
                            reverseButtons: true,
                            buttonsStyling: false
                            }).then(function () {
                                $(function () {
                                    return true;
                                });
                            }, function (dismiss) {
                                $('#case_link').val('');
                        }); 
                    }
                }
            })
        }
    });


    // $('#sendto').on("select2:select", function (evt) {
    //     var element = evt.params.data.element;
    //     var $element = $(element);

    //     $element.detach();
    //     $(this).append($element);
    //     $(this).trigger("change");
    //     beforeLoader();
    //     // alert( $(this).find(":selected").data("set"));
    //     var selections = (JSON.stringify($("#sendto").select2('data')));
    //     console.log('Selected options: ' + selections);
    //     $(function () {
    //         $.ajax({
    //             type: "POST",
    //             url: baseUrl + "/contacts/clients/checkBeforProceed",
    //             data: {
    //                 "selections": selections
    //             },
    //             success: function (res) {
    //                 if (res.msg != "") {
    //                     $('#sendto option[value="Case551 (asdasdasdas)"]').remove();
    //                     // $('#sendto').select2('destroy').val('').select2();
    //                     swal('Error!', res.msg, 'error');
    //                 }

    //                 afterLoader();
    //             }
    //         })
    //     })

    // });
    $("#searchResult").hide();
    // $("#sent_to").keyup(function () {
    //     $("#to_autocomplete_spinner").show();
    //     $("#searchResult").hide();
    //     var search = $(this).val();

    //     if (search != "") {

    //         $.ajax({
    //             url: baseUrl + "/contacts/clients/searchValue", // json datasource
    //             type: 'post',
    //             data: {
    //                 search: search,
    //                 type: 1
    //             },
    //             success: function (response) {
    //                 $("#searchResult").show();
    //                 $("#searchResult").html(response);
    //                 // binding click event to li
    //                 $("#to_autocomplete_spinner").hide();
    //                 $("#searchResult .ui-menu-item-click").bind("click", function () {
    //                     setText(this);
    //                 });

    //             }
    //         });
    //     }

    // });

    function setText(element) {
        var value = $(element).text();
        var userid = $(element).attr("id");
        var type = $(element).attr("type");
        if (type != "undefined") {
            $(function () {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/contacts/clients/checkBeforProceed",
                    data: {
                        "userid": userid,
                        "type": type
                    },
                    success: function (res) {
                        if (res.msg != "") {
                            swal('Error!', res.msg, 'error');
                        } else {
                            if (type == "case") {

                            }
                        }
                        afterLoader();
                    }
                })
            })

            $("#sent_to").val('');
            $("#searchResult").empty();
        }
    }

    $('#sendEmails').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        var delta = quill.root.innerHTML;
        if (delta == '<p><br></p>') {
            toastr.error('Initial message can\'t be blank', "", {
                positionClass: "toast-top-full-width",
                containerId: "toast-top-full-width"
            })
            afterLoader();
            return false;
        }
        if (!$('#sendEmails').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#sendEmails").serialize();
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/sendNewMessageToUser", // json datasource
            data: dataString + '&delta=' + delta,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                $("#innerLoaderTime").css('display', 'block');
                if (res.errors != '') {
                    $("#preloader").hide();
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
                    // $("#sendEmails").scrollTop(0);
                    $('#sendEmails').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    toastr.success('Your message has been sent', "", {
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                    quill.root.innerHTML = '';
                    afterLoader();
                    $("#preloader").hide();
                    if($("#current_submit").val() == 'saveandtime'){            
                        $("#addNewMessagePopup").modal('hide');
                        $("#loadTimeEntryPopup").modal('show');
                        if($("#current_submit").val() == 'saveandtime'){
                            loadTimeEntryPopupByCase($("#case_link").val());
                        }
                    }else{
                        window.location.reload();
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
        dataString = $("#sendEmails").serialize();
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
                    // $("#sendEmails").scrollTop(0);
                    $('#sendEmails').animate({
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
    function deleteDraft(){
        toastr.warning('Draft message has been deleted', "", {
            positionClass: "toast-top-full-width",
            containerId: "toast-top-full-width"
        })
    }
    $("#first_name").focus();

</script>
