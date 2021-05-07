<form class="linkWithCase" id="linkWithCase" name="linkWithCase" method="POST">
    <span id="response"></span>
    @csrf
    <div id="showError" class="showError" style="display:none"></div>
    <input type="hidden" name="company_id" value="{{$company_id}}">
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Case Link</label>
            <div class="col-sm-8">
                <select class="form-control contact_group" id="case_id" name="case_id"
                    data-placeholder="Type a case name">
                    <option value="">Select a case</option>
                </select>
                <span id="callername"></span>
            </div>
            <div class="col-sm-2"><a id="browse_all_courtcases" tabindex="-1" href="#"
                    onclick="showAllCourtCasesAutocomplete(); return false;">Browse All</a></div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Company Contact Link</label>
            <div class="col-sm-8">
                Select the contacts at <b>{{$companyData['first_name']}}</b> that should be linked to this case:
                <table class="no_padding">
                    <tbody>
                        <?php 
                        foreach($clientList as $k=>$v){?>
                        <tr>
                            <td style="padding: 0 !important; padding-top: 3px !important;">
                                <input type="checkbox" name="client_links[]" id="client_links_" value="{{$v->id}}">
                            </td>
                            <td
                                style="padding: 0 !important; padding-left: 5px !important; padding-top: 3px !important;">
                                {{$v->name}} (Client)
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
        <hr>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label"><b>Sharing </b></label>
            <table class="no_padding">
                <tbody>
                    <tr>
                        <td>
                            <div id="share_link">
                                <label id="court_case_user_link_share_label">
                                    <input type="checkbox" name="user_link_share" id="court_case_user_link_share">
                                    Share all existing case events and documents with
                                    selected contacts
                                </label>

                                <br>

                                <label id="court_case_user_link_share_read_label">
                                    <input type="checkbox" name="user_link_share_read"
                                        id="court_case_user_link_share_read">
                                    Automatically mark all items as read
                                </label>

                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <hr>
        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submitButton" type="submit">Add
                Case Link</button>
        </div>

    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();
        $('.contact_group').select2({
            dropdownParent: $("#addExistingCase"),
            ajax: {
                data: function (params) {
                    var query = {
                        search: params.term,
                        company_id: '{{$company_id}}'
                    }
                    // Query parameters will be ?search=[term]&type=public
                    return query;
                },
                type: "POST",
                dataType: 'json',
                url: baseUrl + "/contacts/companies/loadCaseData", // json datasource
                quietMillis: 50,

                processResults: function (data) {
                    return {
                        results: $.map(data.items, function (item) {
                            return {
                                text: item.case_title,
                                id: item.id
                            }
                        })
                    };
                }
            }
        });
        $("#linkWithCase").validate({
            rules: {
                case_id: {
                    required: true
                },
            },
            messages: {
                case_id: {
                    required: "Court case was not found.",
                },
            },
            errorPlacement: function (error, element) {
                if (element.is('#case_id')) {
                    error.appendTo('#callername');
                } else {
                    element.after(error);
                }
            }
        });
    });

    $('#linkWithCase').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        if (!$('#linkWithCase').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#linkWithCase").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/companies/saveLinkCase", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
            },
            success: function (res) {
                if (res.errors != '') {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"></button><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();

                    $('#addExistingCaseArea').animate({
                        scrollTop: 0
                    }, 'slow');
                    afterLoader();
                    return false;
                } else {
                    window.location.reload();
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        });
    });

    function showAllCourtCasesAutocomplete() {
        $(".contact_group").select2("open");
    }

</script>
