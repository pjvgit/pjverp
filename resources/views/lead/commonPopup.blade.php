<div id="deleteLead" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <form class="DeletLeadForm" id="DeletLeadForm" name="DeletLeadForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Lead</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <span id="response"></span>
                    <div class="row">
                        <div class="col-md-12">
                            <p class="font-weight-bold">Are you sure you want to delete this lead?</p>
                            <p>Deleting this lead will also permanently delete all of the following items associated
                                with this lead and their
                                potential case:</p>
                            <ul>
                                <li>Events</li>
                                <li>Notes</li>
                                <li>Intake Forms</li>
                                <li>Documents (both signed and unsigned documents)</li>
                                <li>Tasks</li>
                                <li>Invoices and all associated payment activity</li>
                            </ul>
                            <div class="alert alert-info show" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="w-100">Leads with recorded {{config('app.name')}} credit card or check
                                        transaction
                                        cannot be deleted</div>
                                </div>
                            </div>
                            <input type="hidden" name="user_id" id="delete_lead_id">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <a href="#">
                                <button class="btn btn-secondary  m-1" type="button"
                                    data-dismiss="modal">Cancel</button>
                            </a>
                            <button class="btn btn-primary ladda-button example-button m-1" id="submit"
                                type="submit">Delete Lead</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="addLead" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Lead</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="addLeadArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
  
    function addLead() {
        $("#preloader").show();
        $("#addLeadArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/addLead", // json datasource
                data: 'loadStep1',
                success: function (res) {
                    $("#addLeadArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

</script>
