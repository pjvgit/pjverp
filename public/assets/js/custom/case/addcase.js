function loadAllStep(action = '') {
    console.log("add case js > loadAllStep > calling");
    $('#smartwizard').smartWizard("reset");
    $('#createCase')[0].reset();
    $("#user_type").select2("val", "");
    $("#returnPage").val(action);
}

function loadCaseDropdown() {
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/loadCaseList", // json datasource
        data: { 'case_id': localStorage.getItem("case_id") },
        success: function(res) {
            $("#case_or_lead").html(res);
        }
    })
}