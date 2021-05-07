<div id="EditContactModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="editContactArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<div id="AddCaseModel" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!--  SmartWizard html -->
                        <div id="smartwizard">
                            <ul>
                                <li class="text-center"><a href="#step-1">1<br /><small>Clients & Contacts</small></a></li>
                                <li class="text-center"><a href="#step-2">2<br /><small>Case Details</small></a></li>
                                <li class="text-center"><a href="#step-3">3<br /><small>Billing</small></a></li>
                                <li class="text-center"><a href="#step-4">4<br /><small>Staff</small></a>
                                </li>
                            </ul>
                            <div>
                                <div id="step-1">
                                    
                                </div>
                                <div id="step-2">
                                
                                </div>
                                <div id="step-3">
                                

                                </div>
                                <div id="step-4">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
            {{-- <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary ml-2" id="next-btn" type="button">Create User</button>
            </div> --}}
        </div>
    </div>
</div>


<div id="EditCompany" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Company</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="EditcompanyModel">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="payInvoice" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Record Payment</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <span class="showError"></span>
                        <div id="payInvoiceArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="loadTimeEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="addTimeEntry">
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    
    function loadClientEditBox(id) {
        $("#preloader").show();
        $("#editContactArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/loadEditContact", // json datasource
                data: {
                    "user_id": id
                },
                success: function (res) {
                    $("#editContactArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    
    function loadStep1(id) {
        $('#smartwizard').smartWizard("reset");
        $("#preloader").show();
        $("#step-1").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/case/loadStep1", // json datasource
                data: {"user_id":{{$client_id}},'link':'yes'},
                success: function (res) {
                    $("#AddContactModal").modal('hide');
                    $("#step-1").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    
function EditCompany(id) {
    
    $("#preloader").show();
    $("#EditcompanyModel").html('<img src="{{LOADER}}""> Loading...');
    $(function () {
        $.ajax({
            type: "POST",
            url:  baseUrl +"/contacts/loadEditCompany", // json datasource
            data: {"id":id},
            success: function (res) {
               $("#EditcompanyModel").html(res);
                $("#preloader").hide();
            }
        })
    })
}

function payinvoice(id) {
        $('.showError').html('');
        beforeLoader();
        $("#preloader").show();
        $("#payInvoiceArea").html('');
        $("#payInvoiceArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/payInvoicePopup", 
            data: {'id':id},
            success: function (res) {
                if(typeof(res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#payInvoiceArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#payInvoiceArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },error: function (xhr, status, error) {
                $("#preloader").hide();
                $("#payInvoiceArea").html('');
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        })
    }
    
    
</script>
