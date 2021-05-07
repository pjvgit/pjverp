<form class="archiveCompanyForm" id="archiveCompanyForm" name="archiveCompanyForm" method="POST">
    <span id="response"></span>
    @csrf
    <div id="showError" class="showError" style="display:none"></div>
    <input type="hidden" name="company_id" value="{{$company_id}}">
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"><b>Are you sure you want to unarchive this company?
                </b></label>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Company Contact</label>
            <div class="col-sm-8">
                <?php
                if(!$caseCllientSelection->isEmpty()){
                    ?>
                Also unarchive the following company contacts:
                <table class="no_padding">
                    <tbody>
                        <?php 
                            foreach($caseCllientSelection as $k=>$v){?>
                        <tr>
                            <td style="padding: 0 !important; padding-top: 3px !important;">
                                <input type="checkbox" checked="checked" name="client_links[]" id="client_links_"
                                    value="{{$v->id}}">
                            </td>
                            <td
                                style="padding: 0 !important; padding-left: 5px !important; padding-top: 3px !important;">
                                {{$v->name}} (Client)
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php
                }else{
                    echo "There are no active contacts linked to this company.";
                }
                ?>
            </div>
        </div>
        <?php
        if(!$caseCllientSelection->isEmpty()){
            ?>
        <hr>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label"><b>Contact Login </b></label>
            <table class="no_padding">
                <tbody>
                    <tr>
                        <td>
                            <div id="share_link">
                                <label id="court_case_user_link_share_label">
                                    <input type="checkbox" name="disable_login" id="disable_login">
                                    Enable login for any company contacts that are checked above.
                                </label>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php } ?>
        <hr>
        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submitButton"
                type="submit">Unarchive Company</button>
        </div>

    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();

        $("#archiveCompanyForm").validate({
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

    $('#archiveCompanyForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        if (!$('#archiveCompanyForm').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#archiveCompanyForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/companies/unarchiveCompany", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
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
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        });
    });

    function showAllCourtCasesAutocomplete() {
        $("#company_id").select2("open");
    }

</script>
