<div id="showError" style="display:none"></div>
<h4 class="border-bottom border-gray pb-2">Access Permissions</h4>


<form class="createStep4" id="createStep4" name="createStep4" method="POST">
    @csrf
    <input type="hidden" name="user_id" value="{{$user->id}}">
    <input type="hidden" name="case_id" value="{{$case_id}}">
             
    <div class=" col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-6 col-form-label">   <h6>Should this user be able to...</h6></label>
            <a href="#" class="col text-right col-sm-6" target="_blank" rel="noopener noreferrer">Learn more about user permissions</a>
        </div>
    <table class="table">
        <thead >
            <tr>
                <th></th>
                <th class="text-center col-md-2">Add & Edit</th>
                <th class="text-center col-md-2">View Only</th>
                <th class="text-center col-md-2">Hidden</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Clients</td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="clientsPermission" checked="checked" value="3"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="clientsPermission" value="2"><span class="checkmark"></span>
                    </label>
                </td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Leads</td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="leadsPermission" checked="checked" value="3"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="leadsPermission" value="2"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="leadsPermission" value="1"><span class="checkmark"></span>
                    </label>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>Cases</td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="casesPermission" checked="checked" value="3"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="casesPermission" value="2"><span class="checkmark"></span>
                    </label>
                </td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Events</td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="eventsPermission" checked="checked"  value="3"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="eventsPermission" value="2"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="eventsPermission" value="1"><span class="checkmark"></span>
                    </label>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>Documents</td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="documentsPermission" checked="checked"  value="3"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="documentsPermission" value="2"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="documentsPermission" value="1"><span class="checkmark"></span>
                    </label>
                </td>

                <td></td>
            </tr>
            <tr>
                <td>Commenting</td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="commentingPermission"  checked="checked" value="3"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="commentingPermission" value="2"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="commentingPermission" value="1"><span class="checkmark"></span>
                    </label>
                </td>

                <td></td>
            </tr>
            <tr>
                <td class="text-nowrap">Text Messaging</td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="textMessagingPermission"  checked="checked" value="3"><span class="checkmark"></span>
                    </label>
                </td>

                <td></td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="textMessagingPermission" value="1"><span class="checkmark"></span>
                    </label>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>Messages</td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="messagesPermission"  checked="checked" value="3"><span class="checkmark"></span>
                    </label>
                </td>
                <td></td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="messagesPermission" value="1"><span class="checkmark"></span>
                    </label>
                </td>
                <td>
                    <div>
                        <label class="checkbox checkbox-outline-success">
                            <input type="checkbox" id="allMessagesFirmwide" name="allMessagesFirmwide"><span>Allow
                                access to all
                                messages.</span><span class="checkmark"></span>
                        </label>
                    </div>
                    <div>
                        <label class="checkbox checkbox-outline-success">
                            <input type="checkbox" id="canDeleteMessages" name="canDeleteMessages"><span> Can delete messages</span><span class="checkmark"></span>
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Billing</td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="billingPermission"  checked="checked" value="3"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="billingPermission" value="2"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="billingPermission" value="1"><span class="checkmark"></span>
                    </label>
                </td>

                <td>
                    <div>
                        <label class="checkbox checkbox-outline-success">
                            <input type="checkbox" id="restrictBilling" name="restrictBilling"><span>Restrict to time
                                entries and expenses.</span><span class="checkmark"></span>
                        </label>
                    </div>
                    <div>
                        <label class="checkbox checkbox-outline-success">
                            <input type="checkbox" id="financialInsightsPermission" name="financialInsightsPermission"><span>Allow
                                access
                                to Financial Insights screen.</span><span class="checkmark"></span>
                        </label>
                    </div>

                </td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th></th>
                <th class="text-center col-md-2">Entire Firm</th>
                <th class="text-center col-md-2">Personal Only</th>
                <th class="text-center col-md-2">Hidden</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Reporting</td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="reportingPermission" checked="checked"  value="3"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="reportingPermission" value="2"><span class="checkmark"></span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="radio radio-outline-success">
                        <input type="radio" name="reportingPermission" value="1"><span class="checkmark"></span>
                    </label>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="form-group row float-right">
        <button class="btn btn-primary ladda-button example-button m-1" data-style="expand-right">
            <span class="ladda-label">Finish</span>
        </button>
    </div>
    <div class="form-group row">
        <label for="inputEmail4" class="col-sm-8 col-form-label"></label>
        <div class="col-md-2 form-group mb-3">
            <div class="loader-bubble loader-bubble-primary" id="innerLoader4" style="display: none;"></div>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
    </div>
    </div>
</form>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ready(function () {
        $("#innerLoader4").css('display', 'none');

    });
    $('#createStep4').submit(function (e) {
        $("#innerLoader4").css('display', 'block');
        e.preventDefault();

        var dataString = $("#createStep4").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/saveStep4", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader4").css('display', 'block');
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
                    $("#innerLoader4").css('display', 'none');
                    return false;
                } else {
                    loadStep4();
                }
            }
        });
    });

    function loadStep4() {

        $("#innerLoader4").css('display', 'none');
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/loadFinishStep", // json datasource
            success: function (res) {
                $("#step-4").html(res);
                $("#preloader").hide();
            }
        })

        return false;
    }

</script>
