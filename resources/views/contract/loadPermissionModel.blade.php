<form class="savePermission" id="savePermission" name="savePermission" method="POST">
    @csrf
    <input type="hidden" name="user_id" value="{{$ContractUser->id}}">
    
    <span id="response"></span>
    <div class="row">      
        <div class="col-md-6">
            <div class=" mb-4">
                <table class="table table-set">
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
                                <input type="radio" name="clientsPermission" {{ (in_array('client_add_edit', $userPermissions)) ? 'checked' : '' }} value="client_add_edit"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="clientsPermission" {{ (in_array('client_view', $userPermissions)) ? 'checked' : '' }} value="client_view"><span class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-ban text-black-50 disabled" sr-only="not allowed"></i>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Leads</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="leadsPermission" {{ (in_array('lead_add_edit', $userPermissions)) ? 'checked' : '' }}  value="lead_add_edit"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="leadsPermission" {{ (in_array('lead_view', $userPermissions)) ? 'checked' : '' }} value="lead_view"><span class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="leadsPermission" {{ (!in_array('lead_add_edit', $userPermissions) && !in_array('lead_view', $userPermissions)) ? 'checked' : '' }} value="hidden"><span class="checkmark"></span>
                                </label>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Cases</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="casesPermission" {{ (in_array('case_add_edit', $userPermissions)) ? 'checked' : '' }}   checked="checked" value="case_add_edit"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="casesPermission"  {{ (in_array('case_view', $userPermissions)) ? 'checked' : '' }}  value="case_view"><span class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-ban text-black-50 disabled" sr-only="not allowed"></i>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Events</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="eventsPermission"  {{ (in_array('event_add_edit', $userPermissions)) ? 'checked' : '' }}  checked="checked" value="event_add_edit"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="eventsPermission" {{ (in_array('event_view', $userPermissions)) ? 'checked' : '' }}  value="event_view"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="eventsPermission" {{ (!in_array('event_add_edit', $userPermissions) && !in_array('event_view', $userPermissions)) ? 'checked' : '' }} value="hidden"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Documents</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="documentsPermission" {{ (in_array('document_add_edit', $userPermissions)) ? 'checked' : '' }} checked="checked" value="document_add_edit"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="documentsPermission"  {{ (in_array('document_view', $userPermissions)) ? 'checked' : '' }} value="document_view"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="documentsPermission"  {{ (!in_array('document_add_edit', $userPermissions) && !in_array('document_view', $userPermissions)) ? 'checked' : '' }} value="hidden"><span
                                        class="checkmark"></span>
                                </label>
                            </td>

                            <td></td>
                        </tr>
                        <tr>
                            <td>Commenting</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="commentingPermission"  {{ (in_array('commenting_add_edit', $userPermissions)) ? 'checked' : '' }} checked="checked" value="commenting_add_edit"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="commentingPermission" {{ (in_array('commenting_view', $userPermissions)) ? 'checked' : '' }}   value="commenting_view"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="commentingPermission" {{ (!in_array('commenting_add_edit', $userPermissions) && !in_array('commenting_view', $userPermissions)) ? 'checked' : '' }}  value="hidden"><span
                                        class="checkmark"></span>
                                </label>
                            </td>

                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-nowrap">Text Messaging</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="textMessagingPermission"  {{ (in_array('text_messaging_add_edit', $userPermissions)) ? 'checked' : '' }}  value="text_messaging_add_edit"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-ban text-black-50 disabled" sr-only="not allowed"></i>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="textMessagingPermission"  {{ (!in_array('text_messaging_add_edit', $userPermissions)) ? 'checked' : '' }} value="hidden" ><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Messages</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="messagesPermission"  {{ (in_array('messaging_add_edit', $userPermissions)) ? 'checked' : '' }} value="messaging_add_edit"><span
                                        class="checkmark"></span>
                                </label>
                            </td>  
                            <td class="text-center">
                                <i class="fas fa-ban text-black-50 disabled" sr-only="not allowed"></i>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="messagesPermission"  {{ (!in_array('messaging_add_edit', $userPermissions)) ? 'checked' : '' }} value="hidden"><span
                                        class="checkmark"></span>
                                </label>
                            </td>

                        </tr>
                        <tr>
                            <td colspan="4">
                                <div>
                                    <label class="checkbox checkbox-outline-success">
                                        <input type="checkbox" id="allMessagesFirmwide"
                                            name="allMessagesFirmwide" {{ (in_array('access_all_messages', $userPermissions)) ? 'checked' : '' }} value="access_all_messages" ><span>Allow
                                            access to all
                                            messages.</span><span class="checkmark"></span>
                                    </label>
                                </div>
                                <div>
                                    <label class="checkbox checkbox-outline-success">
                                        <input type="checkbox" id="canDeleteMessages" name="canDeleteMessages" {{ (in_array('can_delete_messages', $userPermissions)) ? 'checked' : '' }} value="can_delete_messages" ><span> Can delete messages</span><span class="checkmark"></span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Billing</td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="billingPermission"  {{ (in_array('billing_add_edit', $userPermissions)) ? 'checked' : '' }}  value="billing_add_edit"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="billingPermission"  {{ (in_array('billing_view', $userPermissions)) ? 'checked' : '' }} value="billing_view"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="billingPermission"  {{ (!in_array('billing_add_edit', $userPermissions) && !in_array('billing_view', $userPermissions)) ? 'checked' : '' }} value="hidden"><span
                                        class="checkmark"></span>
                                </label>
                            </td>

                        </tr>
                        <tr>
                            <td colspan="4">
                                <label class="checkbox checkbox-outline-success">
                                    <input type="checkbox" id="restrictBilling"  {{ (in_array('billing_restrict_time_entry_and_expense', $userPermissions)) ? 'checked' : '' }} value="billing_restrict_time_entry_and_expense" name="restrictBilling">Restrict to time
                                    entries and expenses.<span class="checkmark"></span>
                                </label>
                                <label class="checkbox checkbox-outline-success">
                                    <input type="checkbox" id="financialInsightsPermission"
                                        name="financialInsightsPermission"  {{ (in_array('billing_access_financial_insight', $userPermissions)) ? 'checked' : '' }} value="billing_access_financial_insight"><span>Allow
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
                                    <input type="radio" name="reportingPermission" {{ (in_array('reporting_entire_firm', $userPermissions)) ? 'checked' : '' }} value="reporting_entire_firm"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="reportingPermission" {{ (in_array('reporting_personal_only', $userPermissions)) ? 'checked' : '' }}  value="reporting_personal_only"><span
                                        class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="radio radio-outline-success">
                                    <input type="radio" name="reportingPermission" {{ (!in_array('reporting_entire_firm', $userPermissions) && !in_array('reporting_personal_only', $userPermissions)) ? 'checked' : '' }}  value="hidden"><span
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
            SHOULD {{$ContractUser->first_name}} USER BE ABLE TO...
            <div class=" mb-4">
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-6 col-form-label">Access data from every case in the system or only those he/she is linked to?</label>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success ">
                            <input type="radio" name="access_case" {{ (in_array('access_all_cases', $userPermissions)) ? 'checked' : '' }} value="access_all_cases"><span class="text-space pl-3"> All firm
                                cases</span><span class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3 p-0">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="access_case" {{ (in_array('access_only_linked_cases', $userPermissions)) ? 'checked' : '' }} value="access_only_linked_cases"><span class="text-space pl-3">Only linked cases</span><span
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
                            <input type="radio" name="add_new" value="add_firm_user" {{ (in_array('add_firm_user', $userPermissions)) ? 'checked' : '' }} ><span class="pl-3"> Yes</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="add_new" {{ (!in_array('add_firm_user', $userPermissions)) ? 'checked' : '' }} value="no"><span class="pl-3">No</span><span class="checkmark"></span>
                        </label>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-6 col-form-label">Edit user permission settings?</label>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="edit_permisssion" value="edit_firm_user_permission" {{ (in_array('edit_firm_user_permission', $userPermissions)) ? 'checked' : '' }} ><span class="pl-3"> Yes</span><span class="checkmark"></span>

                            
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="edit_permisssion" {{ (!in_array('edit_firm_user_permission', $userPermissions)) ? 'checked' : '' }} value="no"><span class="pl-3">No</span><span class="checkmark"></span>
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
                            <input type="radio" name="delete_item" value="delete_items" {{ (in_array('delete_items', $userPermissions)) ? 'checked' : '' }}  ><span class="pl-3"> Yes</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="delete_item" value="no" {{ (!in_array('delete_items', $userPermissions)) ? 'checked' : '' }} ><span class="pl-3">No</span><span
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
                            <input type="radio" name="import_export" value="edit_import_export_settings" {{ (in_array('edit_import_export_settings', $userPermissions)) ? 'checked' : '' }} ><span class="pl-3"> Yes</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="import_export" value="no" {{ (!in_array('edit_import_export_settings', $userPermissions)) ? 'checked' : '' }} ><span class="pl-3">No</span><span
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
                            <input type="radio" name="custome_fields" value="edit_custom_fields_settings" {{ (in_array('edit_custom_fields_settings', $userPermissions)) ? 'checked' : '' }}><span class="pl-3"> Yes</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="custome_fields" value="no" {{ (!in_array('edit_custom_fields_settings', $userPermissions)) ? 'checked' : '' }} ><span class="pl-3">No</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                </div>
                <hr>
                @if(auth()->user()->parent_user == 0 && $ContractUser->parent_user != 0)
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-6 col-form-label">Manage your firm's preferences, billing,
                        and payment
                        options?
                    </label>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="manage_firm" value="manage_firm_and_billing_settings" {{ (in_array('manage_firm_and_billing_settings', $userPermissions)) ? 'checked' : '' }}><span class="pl-3"> Yes</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <label class="radio radio-outline-success">
                            <input type="radio" name="manage_firm" value="no" {{ (!in_array('manage_firm_and_billing_settings', $userPermissions)) ? 'checked' : '' }}><span class="pl-3">No</span><span
                                class="checkmark"></span>
                        </label>
                    </div>
                </div>
                @endif
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
