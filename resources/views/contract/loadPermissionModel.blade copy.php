<form class="savePermission" id="savePermission" name="savePermission" method="POST">
    @csrf
    <input type="hidden" name="user_id" value="{{$ContractUser[0]->id}}">
    
    <span id="response"></span>
    <div class="row">      
        <div class="col-md-6">
            <div class=" mb-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-center col-md-3">Add & Edit</th>
                            <th class="text-center col-md-3">View Only</th>
                            <th class="text-center col-md-2">Hidden</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                       
                        <tr>
                            <td>Clients</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                <input type="radio" name="clientsPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->clientsPermission == "3" ) { ?> checked="checked" <?php } ?> value="3"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="clientsPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->clientsPermission == "2" ) { ?> checked="checked" <?php } ?>   value="2"><span class="checkmark"></span>
                                </label>
                            </td>
                            <td>
                                <i class="fas fa-ban text-black-50 disabled" sr-only="not allowed"></i>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Leads</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="leadsPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->leadsPermission == "3" ) { ?> checked="checked" <?php } ?>  value="3"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="leadsPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->leadsPermission == "2" ) { ?> checked="checked" <?php } ?> value="2"><span class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="leadsPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->leadsPermission == "1" ) { ?> checked="checked" <?php } ?> value="1"><span class="checkmark"></span>
                                </label>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Cases</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="casesPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->casesPermission == "3" ) { ?> checked="checked" <?php } ?>   checked="checked" value="3"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="casesPermission"  <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->casesPermission == "2" ) { ?> checked="checked" <?php } ?>    value="2"><span class="checkmark"></span>
                                </label>
                            </td>
                            <td>
                                <i class="fas fa-ban text-black-50 disabled" sr-only="not allowed"></i>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Events</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="eventsPermission"  <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->eventsPermission == "3" ) { ?> checked="checked" <?php } ?>  checked="checked" value="3"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="eventsPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->eventsPermission == "2" ) { ?> checked="checked" <?php } ?>  value="2"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="eventsPermission"  <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->eventsPermission == "1" ) { ?> checked="checked" <?php } ?> value="1"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Documents</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="documentsPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->documentsPermission == "3" ) { ?> checked="checked" <?php } ?> checked="checked" value="3"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="documentsPermission"  <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->documentsPermission == "2" ) { ?> checked="checked" <?php } ?> value="2"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="documentsPermission"  <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->documentsPermission == "1" ) { ?> checked="checked" <?php } ?> value="1"><span
                                        class="checkmark"></span>
                                </label>
                            </td>

                            <td></td>
                        </tr>
                        <tr>
                            <td>Commenting</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="commentingPermission"  <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->commentingPermission == "3" ) { ?> checked="checked" <?php } ?> checked="checked" value="3"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="commentingPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->commentingPermission == "2" ) { ?> checked="checked" <?php } ?>   value="2"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="commentingPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->commentingPermission == "1" ) { ?> checked="checked" <?php } ?>  value="1"><span
                                        class="checkmark"></span>
                                </label>
                            </td>

                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-nowrap">Text Messaging</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="textMessagingPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->textMessagingPermission == "3" ) { ?> checked="checked" <?php } ?>  value="3"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td>
                                <i class="fas fa-ban text-black-50 disabled" sr-only="not allowed"></i>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="textMessagingPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->textMessagingPermission == "1" ) { ?> checked="checked" <?php } ?> value="1" ><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Messages</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="messagesPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->messagesPermission == "3" ) { ?> checked="checked" <?php } ?> value="3"><span
                                        class="checkmark"></span>
                                </label>
                            </td>  <td>
                                <i class="fas fa-ban text-black-50 disabled" sr-only="not allowed"></i>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="messagesPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->messagesPermission == "1" ) { ?> checked="checked" <?php } ?> value="1"><span
                                        class="checkmark"></span>
                                </label>
                            </td>

                        </tr>
                        <tr>
                            <td colspan="4">
                                <div>
                                    <label class="checkbox checkbox-outline-success">
                                        <input type="checkbox" id="allMessagesFirmwide"
                                            name="allMessagesFirmwide" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->allMessagesFirmwide == "1" ) { ?> checked="checked" <?php } ?>  ><span>Allow
                                            access to all
                                            messages.</span><span class="checkmark"></span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Billing</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="billingPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->billingPermission == "3" ) { ?> checked="checked" <?php } ?>  value="3"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="billingPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->billingPermission == "2" ) { ?> checked="checked" <?php } ?> value="2"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="billingPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->billingPermission == "1" ) { ?> checked="checked" <?php } ?> value="1"><span
                                        class="checkmark"></span>
                                </label>
                            </td>

                        </tr>
                        <tr>
                            <td colspan="4">
                                <label class="checkbox checkbox-outline-success">
                                    <input type="checkbox" id="restrictBilling"  <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->restrictBilling == "1" ) { ?> checked="checked" <?php } ?> name="restrictBilling">Restrict to time
                                    entries and expenses.<span class="checkmark"></span>
                                </label>
                                <label class="checkbox checkbox-outline-success">
                                    <input type="checkbox" id="financialInsightsPermission"
                                        name="financialInsightsPermission"  <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->financialInsightsPermission == "1" ) { ?> checked="checked" <?php } ?> ><span>Allow
                                        access
                                        to Financial Insights screen.</span><span class="checkmark"></span>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-center col-md-3">Entire Firm</th>
                            <th class="text-center col-md-3">Personal Only</th>
                            <th class="text-center col-md-2">Hidden</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Reporting</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="reportingPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->reportingPermission == "3" ) { ?> checked="checked" <?php } ?> value="3"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="reportingPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->reportingPermission == "2" ) { ?> checked="checked" <?php } ?>  value="2"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="reportingPermission" <?php if(!$ContractAccessPermission->isEmpty() && $ContractAccessPermission[0]->reportingPermission == "1" ) { ?> checked="checked" <?php } ?>  value="1"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
        <div class="col-md-6">
            SHOULD {{$ContractUser[0]->first_name}} USER BE ABLE TO...
            <div class=" mb-4">
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-6 col-form-label">Access data from every case in the system
                        or only those
                        he/she is linked to?</label>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="access_case" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->access_case == "0" ) { ?> checked="checked" <?php } ?>   value="0"><span> All firm
                                cases</span><span class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="access_case" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->access_case == "1" ) { ?> checked="checked" <?php } ?> value="1"><span>Only linked cases</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-6 col-form-label">Add new attorneys, paralegals, and support
                        staff to
                        your firm's {{config('app.name')}} account?</label>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="add_new" value="0" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->add_new == "0" ) { ?> checked="checked" <?php } ?> ><span> Yes</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="add_new" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->add_new == "1" ) { ?> checked="checked" <?php } ?> value="1"><span>No</span><span class="checkmark"></span>
                        </label>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-6 col-form-label">Edit user permission settings?</label>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="edit_permisssion" value="0" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->edit_permisssion == "0" ) { ?> checked="checked" <?php } ?> ><span> Yes</span><span class="checkmark"></span>

                            
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="edit_permisssion" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->edit_permisssion == "1" ) { ?> checked="checked" <?php } ?> value="1"><span>No</span><span class="checkmark"></span>
                        </label>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-6 col-form-label">Delete items (events, documents, etc.) from
                        {{config('app.name')}}?
                    </label>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="delete_item" value="0" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->delete_item == "0" ) { ?> checked="checked" <?php } ?>  ><span> Yes</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="delete_item" value="1" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->delete_item == "1" ) { ?> checked="checked" <?php } ?>><span>No</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-6 col-form-label">Edit import-export capabilities?
                    </label>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="import_export" value="0" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->import_export == "0" ) { ?> checked="checked" <?php } ?> ><span> Yes</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="import_export" value="1" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->import_export == "1" ) { ?> checked="checked" <?php } ?>><span>No</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-6 col-form-label">Edit custom fields settings?
                    </label>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="custome_fields" value="0" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->custome_fields == "0" ) { ?> checked="checked" <?php } ?>><span> Yes</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="custome_fields" value="1" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->custome_fields == "1" ) { ?> checked="checked" <?php } ?>><span>No</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-6 col-form-label">Manage your firm's preferences, billing,
                        and payment
                        options?
                    </label>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="manage_firm" value="0" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->manage_firm == "0" ) { ?> checked="checked" <?php } ?>><span> Yes</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="manage_firm" value="1" <?php if(!$ContractUserPermission->isEmpty() && $ContractUserPermission[0]->manage_firm == "1" ) { ?> checked="checked" <?php } ?>><span>No</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group row float-right  pr-12">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                </a>

                <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit"
                    data-style="expand-left"><span class="ladda-label">Save Permissions</span><span
                        class="ladda-spinner"></span><span class="ladda-spinner"></span></button>
            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                <div class="col-md-2 form-group mb-3">
                    <div class="loader-bubble loader-bubble-primary" id="innerLoader"></div>
                </div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
   
    $(document).ready(function () {
        $("#innerLoader").css('display', 'none');

        $("#innerLoader").hide();
        $("#response").hide();
       

        $('#savePermission').submit(function (e) {
            $("#innerLoader").css('display', 'block');
            e.preventDefault();
            if (!$('#savePermission').valid()) {
                $("#innerLoader").css('display', 'none');
                return false;
            }
            var dataString = $("#savePermission").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/savePermissionModel", // json datasource
                data: dataString,
                success: function (res) {
                    // $("#preloader").hide();
                    // $("#response").html(
                    //     '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Changes saved.</div>'
                    //     );
                    // $("#response").show();
                    // $("#innerLoader").css('display', 'none');
                    // return false;
                    window.location.reload();
                }
            });

        });

    });

</script>
