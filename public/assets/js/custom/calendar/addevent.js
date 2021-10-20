// Get default firm reminder for client
var reminderAdded = false;
$(document).on("change", ".load-default-reminder", function() {
    if($(this).is(":checked") && !reminderAdded) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/load/firm/defaultReminder",
            dataType: "JSON",
            success: function (res) {
                if(res.default_reminder) {
                    if(res.default_reminder.length > 0) {
                        $.each(res.default_reminder, function(ind, item) {
                            $(".add-more").trigger("click");
                            var lastNo = $(".fieldGroup").length;
                            // alert(lastNo);
                            $('body').find('#reminder_user_type:last').attr("ownid",lastNo);
                            $('body').find('#reminder_user_type:last').attr("id",lastNo);
                            $('body').find('#reminder_type:last').attr("id","reminder_type_"+lastNo);
                            $('body').find('#reminder_number:last').attr("id","reminder_number_"+lastNo);
                            $('body').find('#reminder_time_unit:last').attr("id","reminder_time_unit_"+lastNo);

                            $('body').find("#"+lastNo+" option[value='client-lead']").show();
                            $('body').find('#'+lastNo).val(item.reminder_user_type);
                            $('#'+lastNo).trigger("change");
                            $('body').find('#reminder_number_'+lastNo).val(item.reminer_number);
                            $('body').find('#reminder_time_unit_'+lastNo).val(item.reminder_frequncy);
                            $('body').find('#reminder_type_'+lastNo).val(item.reminder_type);
                        });
                        reminderAdded = true;
                    } else {
                        $(".reminder_user_type").append('<option value="client-lead">Clients/Leads</option>');
                    }
                }
            }
        });
    } else {
        var checkedLen = $('input[name="ContactInviteClientCheckbox[]"]:checked').length;
        var checkedL = $('input[name="client-share-all"]:checked').length;
        if(checkedLen <= 0 && checkedL <= 0) {
            $(".reminder_user_type option[value='client-lead']:selected").parents('.fieldGroup').remove();
            reminderAdded = false;
        }
    }
});

// CHange reminder type based on reminder user type
function chngeTy(sel){
    if(sel.value=='client-lead'){
        $("#reminder_type_"+sel.id+" option[value='popup']").hide();
    }else{
        $("#reminder_type_"+sel.id+" option[value='popup']").show();
    }
}

/**
 * Load grant access modal
 * @param {} id 
 */
function loadGrantAccessModal(id) {   
    if($("#cleintUSER_"+id).prop('checked')==true && $("#cleintUSER_"+id).attr("data-client_portal_enable") == 0){
        // alert(id);
        $("#cleintUSER_"+id).prop('checked',false);
        $("#loadGrantAccessModal").modal();
        $("#innerLoader").css('display', 'none');
        $("#preloader").show();
        $("#grantCase").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/court_cases/loadGrantAccessPage", // json datasource
                data: {"client_id":id},
                success: function (res) {
                    $("#grantCase").html(res);
                    $("#preloader").hide();
                    $("#innerLoader").css('display', 'none');
                    
                    $(".add-more").trigger('click');
                    return false;
                }
            })
        })
    } 
    var checkecCounter=$('input[name="clientCheckbox[]"]:checked').length;
    if(checkecCounter>0){
        $(".reminder_user_type option[value='client-lead']").show();
    }else{
        $(".reminder_user_type option[value='client-lead']").hide();
    }
}