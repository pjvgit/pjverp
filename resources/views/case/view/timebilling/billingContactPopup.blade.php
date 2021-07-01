<div id="editBillingContactPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="billing_popup_title">Edit Billing Information for <span>{{@$CaseMaster['case_title']}}</span></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="editBillingContactPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editBillingContactPopup(case_id = null) {
    console.log(case_id);
    $('.showError').html('');
    $("#editBillingContactPopupArea").html('');
    $("#editBillingContactPopupArea").html('<img src="{{LOADER}}"> Loading...');
    var caseId = "@if(isset($CaseMaster) && !empty($CaseMaster)) {{$CaseMaster['case_id']}} @endif";
    if(caseId == "") {
        caseId = case_id;
    }
    $.ajax({
        type: "POST",
        url: baseUrl + "/court_cases/overview/editBillingContactPopup",
        data: {"case_id": caseId},
        success: function (res) {
            if (typeof (res.errors) != "undefined" && res.errors !== null) {
                $('.showError').html('');
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                $.each(res.errors, function (key, value) {
                    errotHtml += '<li>' + value + '</li>';
                });
                errotHtml += '</ul></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();

                $("#editBillingContactPopupArea").html('');
                return false;
            } else {
                afterLoader()
                $("#billing_popup_title span").text(res.case.case_title);
                $("#editBillingContactPopupArea").html(res.view);
                return true;
            }
        },
        error: function (xhr, status, error) {
            $('.showError').html('');
            var errotHtml =
                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showError').append(errotHtml);
            $('.showError').show();

        }
    })
}
</script>