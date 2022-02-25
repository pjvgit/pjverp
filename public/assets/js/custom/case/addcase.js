function loadAllStep(action = '') {
    console.log("add case js > loadAllStep > calling");

    $('#smartwizard,#smartwizard1').smartWizard({
        selected: 0,
        theme: 'default',
        transitionEffect: 'fade',
        showStepURLhash: false,
        enableURLhash: false,
        backButtonSupport: true, // Enable the back button support
        keyNavigation: false,
        toolbarSettings: {
            toolbarPosition: 'none',
            toolbarButtonPosition: 'end',
        },
        anchorSettings: {
            anchorClickable: false, // Enable/Disable anchor navigation
            enableAllAnchors: false, // Activates all anchors clickable all times
            markDoneStep: true, // Add done state on navigation
            markAllPreviousStepsAsDone: true, // When a step selected by url hash, all previous steps are marked done
            removeDoneStepOnNavigateBack: false, // While navigate back done step after active step will be cleared
            enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
        },
    });
    $("#user_type").select2({
        placeholder: "Search for an existing contact or company",
        theme: "classic",
        allowClear: true,
        dropdownParent: $("#AddCaseModelUpdate"),
    });
    $('#createCase').on('click', '.remove', function () {
        var $row = $(this).parents('.fieldGroup').remove();
    });

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

function selectUserAutoLoad(id) {
    $("#innerLoader").css('display', 'block');
    $.ajax({
        type: "POST",
        url: baseUrl + "/case/saveSelectdUser",
        data: {
            "selectdValue": id
        },
        success: function (res) {
           
            $(".text-center-also").remove();
            $("#innerLoader").css('display', 'none');
            $("#beforetext").remove(); 
            $("#beforebutton").remove();
            $("#submit_with_user").show(); 
            $("#submit").remove(); 
            $("#loadUserAjax").html(res);
            
        }
    })
}
function selectUser() {
    $("#innerLoader").css('display', 'block');
    var selectdValue = $("#user_type option:selected").val() // or
    $.ajax({
        type: "POST",
        url: baseUrl + "/case/saveSelectdUser",
        data: {"selectdValue": selectdValue},
        success: function (res) {
            $(".text-center-also").remove();
            $("#innerLoader").css('display', 'none');
            $("#beforetext").remove(); 
            $("#beforebutton").remove();
            $("#submit_with_user").show(); 
            $("#submit").remove(); 
            $("#loadUserAjax").html(res);
            
        }
    })
}
function removeUser(id) {
    $("#innerLoader").css('display', 'block');
    $.ajax({
        type: "POST",
        url: baseUrl + "/case/remomeSelectedUser",
        data: {
            "selectdValue": id
        },
        success: function (res) {
            $("#loadUserAjax").html(res);
            $("#innerLoader").css('display', 'none');
        }
    })
}
function StatusLoadStep2() {
    $('#smartwizard').data('smartWizard')._showStep(1); // go to step 3....
}
function backStep1() {
    $('#smartwizard').smartWizard('prev');
}

function StatusLoadStep3() {
    var case_name = $("#case_name").val();
    $.ajax({
        type: "POST",
        url: baseUrl + "/case/checkCaseNameExists", // json datasource
        data: {case_name : case_name},
        success: function (res) {
            if (res.errors != '') {
                $('#showError2').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><ul>';
                        $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                errotHtml += '</ul></div>';
                $('#showError2').append(errotHtml);
                $('#showError2').show();
                $('#AddCaseModelUpdate').animate({
                    scrollTop: 0
                }, 'slow');
                result = false;                    
            }else{
                $('#showError2').html('');
                $('#showError2').hide();
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/case/loadBillingContact",
                    data: {"selectdValue": ''},
                    success: function (res) {
                        $("#loadBillingAjax").html(res);
                        $("#innerLoader").css('display', 'none');
                        $('#smartwizard').data('smartWizard')._showStep(2); // go to step 3....
                    }
                })
            }
        }
    });
}

function backStep2() {
    $('#smartwizard').smartWizard('prev');
}

function StatusLoadStep4() {
    $('#smartwizard').data('smartWizard')._showStep(3); 

}
function backStep3() {
    $('#smartwizard').smartWizard('prev');
    
}
function saveFinalStep() {
    var dataString = $("#createCase").serialize();

        $.ajax({
            type: "POST",
            url: baseUrl + "/case/saveAllStep", // json datasource
            data: dataString,
            success: function (res) {
                if (res.errors != '') {
                    $('#showError4').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><ul>';
                            $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                    errotHtml += '</ul></div>';
                    $('#showError4').append(errotHtml);
                    $('#showError4').show();
                    $('#AddCaseModelUpdate').animate({
                        scrollTop: 0
                    }, 'slow');
                    return false;
                } else {
                    if($("#returnPage").val() == '' || $("#returnPage").val() === undefined){
                        $("#returnPage").val('');
                        window.location.reload();
                    }else{
                        localStorage.setItem("case_id", res.case_id);
                        $('#AddCaseModelUpdate').modal("hide");
                        loadCaseDropdown();
                    }
                }
            }
        });
    
}
 

function callOnClick(){
    $("#beforebutton").hide();
    $("#beforetext").show();
    $("#submit_without_user").show();
    $("#submit").show();
    $("#submit_with_user").hide();
    $("#case_name").focus();

}        
$("#case_area_text").hide();
function caseShowText() {
    $("#case_area_text").show();
    $("#case_area_dropdown").hide();
    return false;
}

function caseShowDropdown() {
    $("#case_area_text").hide();
    $("#case_area_dropdown").show()
    return false;
}

function selectMethod() {
    $("#innerLoader").css('display', 'block');
    var selectdValue = $("#billingMethod option:selected").val();
    if (selectdValue == 'mixed' || selectdValue == 'flat') {
        $("#billing_rate_text").show();
    } else {
        $("#billing_rate_text").hide();
    }
}

$(".all-users-checkbox").click(function () {
    $(".users-checkbox").prop('checked', $(this).prop('checked'));
});
$(".users-checkbox").click(function () {
    if ($('.users-checkbox:checked').length == $('.users-checkbox').length) {
        $('.all-users-checkbox').prop('checked', true);
    } else {
        $('.all-users-checkbox').prop('checked', false);
    }
});

$('#AddCaseModelUpdate').on('hidden.bs.modal', function () {
    if($("#returnPage").val() == ''){
        window.location.reload();
        $("#returnPage").val('');
    }

    if($("#returnPage").val() != ''){
        setTimeout(function(){
            $('body').toggleClass("modal-open");
        }, 500);
    }
});

function selectAttorney() {
    var selectdValue = $("#originating_attorney option:selected").val();
    $("#" + selectdValue).prop('checked', true);
}

function selectLeadAttorney() {
    var selectdValue = $("#lead_attorney option:selected").val();
    $("#" + selectdValue).prop('checked', true);
}

function backStep3() {
    $('#smartwizard').smartWizard('prev');
    return false;
}

function AddContactModal(action) {
    
    var action = (action != '') ?  'loadStep1' : { action : 'add_case_with_billing'}
    $("#innerLoader").css('display', 'none');
    $("#preloader").show();
    $("#step-1-again").html('');
    $(function () {
        $.ajax({
            type: "POST",
            // url:  baseUrl +"/contacts/loadAddContactFromCase", // json datasource
            url:  baseUrl +"/contacts/loadAddContact", // json datasource
            // data: 'loadStep1',
            data: action,
            success: function (res) {
                $("#step-1-again").html(res);
                $("#preloader").hide();
                $("#innerLoader").css('display', 'none');
                return false;
            }
        })
    })
}