$(document).ready(function() {
    $("#addEmailtouser").validate({
        rules: {
            email: {
                required: true,
                email:true
            }
        },
        messages: {
            email: {
                required: "Email can't be blank",
                email : "Email is not formatted correctly"
            }
        }
    });

    $('#addEmailtouser').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        // if (!$('#addEmailtouser').valid()) {
        //     beforeLoader();
        //     return false;
        // }
        var dataString = '';
        dataString = $("#addEmailtouser").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/addEmailtouser", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&delete=yes';
            },
            success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                    $('.showErrorOver').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showErrorOver').append(errotHtml);
                    $('.showErrorOver').show();
                    afterLoader();
                    return false;
                } else {
                    // $("#addRequestFund #contact").val(res.user_id);
                    $("#addEmailToClient").modal("hide");
                    refreshDetail();
                    afterLoader();
                }
            },
            error: function (xhr, status, error) {
            $('.showErrorOver').html('');
            var errotHtml =
                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            $('.showErrorOver').append(errotHtml);
            $('.showErrorOver').show();
            afterLoader();
        }
        });
    });

    $('#addRequestFund').on('hidden.bs.modal', function () {
        requestFundGrid.ajax.reload(null, false);
        $('#requestFundGrid').DataTable().ajax.reload(null, false);
        $('#timeEntryGrid').DataTable().ajax.reload(null, false); // For billing > request fund list
    });

    var requestFundGrid =  $('#requestFundGrid').DataTable( {
        serverSide: true,
        "dom": '<"top">rt<"bottom"pl>',
        responsive: false,
        processing: true,
        stateSave: true,
        searching: false,
        "order": [[0, "desc"]],
        "ajax":{
            url :baseUrl +"/contacts/clients/loadRequestedFundHistory", // json datasource
            type: "post",  // method  , by default get
            data :{ 'user_id' : $("#user_id").val() },
            error: function(){  // error handling
                $(".employee-grid-error").html("");
                $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                $("#employee-grid_processing").css("display","none");
            }
        },
        // "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
        pageResize: true,  // enable page resize
        pageLength:10,
        columns: [
            { data: 'id'},
            { data: 'id'},
            { data: 'id','sorting':false},
            { data: 'id','sorting':false}, 
            { data: 'id'}, 
            { data: 'id'},
            { data: 'id'},
            { data: 'id'},
            { data: 'id'},
            { data: 'id','sorting':false},
            { data: 'id','sorting':false},
            { data: 'id','sorting':false}
        ],
        "fnCreatedRow": function (nRow, aData, iDataIndex) {
            
            $('td:eq(0)', nRow).html('<div class="text-left">#R-00'+aData.id+'</div>');
            var clientLink='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.client_id+'">'+aData.client_name+' (Client)</a>';
            $('td:eq(1)', nRow).html('<div class="text-left">'+clientLink+'</div>');
            if(aData.deposit_into_type == 'credit')
                $('td:eq(2)', nRow).html('<div class="text-left">Credit (Operating Account)</div>');
            else
                $('td:eq(2)', nRow).html('<div class="text-left">Trust(Trust Account)</div>');

            if(aData.allocated_to_case_id != null) {
                var clientLink='<a class="name" href="'+baseUrl+'/court_cases/'+aData.allocate_to_case.case_unique_number+'/info/">'+aData.allocate_to_case.case_title+'</a>';
            } else {
                if(aData.user.user_level == 2)
                    var clientLink='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.client_id+'">'+aData.user.full_name+' ('+aData.user.user_type_text+')</a>';
                else
                    var clientLink='<a class="name" href="'+baseUrl+'/contacts/companies/'+aData.client_id+'">'+aData.user.full_name+' ('+aData.user.user_type_text+')</a>';
            }
            $('td:eq(3)', nRow).html('<div class="text-left">'+clientLink+'</div>');

            $('td:eq(4)', nRow).html('<span class="d-none">'+aData.amt_requested+'</span><div class="text-left">$'+aData.amt_requested+'</div>');
            $('td:eq(5)', nRow).html('<span class="d-none">'+aData.amt_paid+'</span><div class="text-left">$'+aData.amt_paid+'</div>')
            $('td:eq(6)', nRow).html('<span class="d-none">'+aData.amt_due+'</span><div class="text-left">$'+aData.amt_due+'</div>')
            $('td:eq(7)', nRow).html('<span class="d-none">'+moment(aData.due_date_format).format('YYYYMMDD')+'</span><div class="text-left">'+aData.due_date_format+'</div>')
            $('td:eq(8)', nRow).html('<span class="d-none">'+moment(aData.last_send).format('YYYYMMDD')+'</span><div class="text-left">'+aData.send_date_format+'</div>')

            if(aData.is_viewed=="no"){
                var isSeen="Never";
            }else{
                var isSeen="Yes";
            }
            $('td:eq(9)', nRow).html('<div class="text-left">'+isSeen+'</div>')

            if(aData.current_status=="Overdue"){
                var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-danger" style="display: inline;"></i>Overdue';
            }else if(aData.current_status=="Partial"){
                var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-warning" style="display: inline;"></i>Partial';
            }else if(aData.current_status=="Paid"){
                var curSetatus="Paid";
            }else{
                var curSetatus="Sent";
            }
                
            $('td:eq(10)', nRow).html('<div class="text-left">'+curSetatus+'</div>');
            
            var action = '<div class="text-center">\
                <a data-toggle="modal"  data-target="#editFundRequest" data-placement="bottom" href="javascript:;"  onclick="editFundRequest('+aData.id+');">\
                    <i class="fas fa-pen align-middle pr-3"></i>\
                </a>';
            if(aData.status != 'paid') {
                action += '<a data-toggle="modal"  data-target="#sendFundReminder" data-placement="bottom" href="javascript:;"  onclick="sendFundReminder('+aData.id+');">\
                    <i class="fas fa-bell pr-3 align-middle"></i>\
                </a>';
            }
            action += '<a data-toggle="modal" data-placement="bottom" href="javascript:;"  onclick="deleteRequestFund('+aData.id+', this);" data-payment-count="'+((aData.deposit_into_type == "credit") ? aData.credit_fund_payment_history_count : aData.trust_fund_payment_history_count)+'">\
                    <i class="fas fa-trash align-middle "></i>\
                </a>\
            </div>';

            $('td:eq(11)', nRow).html(action);

        },
        "initComplete": function(settings, json) {
            $('td').css('font-size',parseInt('13px'));  
        }
    });
});

// For add fund request
function addRequestFundPopup(caseId = null, isFromTrustAllocation = 'no') {
    $("#preloader").show();
    $("#addRequestFundArea").html('<img src="'+loaderImage+'"> Loading...');
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/addRequestFundPopup",
            data: {
                "user_id": $("#user_id").val(), case_id: caseId, is_from_trust_allocation: isFromTrustAllocation
            },
            success: function (res) {
                $("#addRequestFundArea").html(res);
                $("#preloader").hide();
            }
        })
    })
}

function editFundRequest(id) {
    $("#preloader").show();
    $("#editFundRequestArea").html('<img src="'+loaderImage+'""> Loading...');
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/editFundRequest", 
            data: {"id": id},
            success: function (res) {
                $("#editFundRequestArea").html(res);
                $("#preloader").hide();
            }
        })
    })
}
function deleteRequestFund(id, ele) {
    var paymentCount = $(ele).attr("data-payment-count");
    if(paymentCount > 0) {
        swal({
            title: 'Cannot Delete',
            text: 'This request cannot be deleted because there are payments associated with it.'
        });
    } else {
        $("#deleteRequestFund").modal("show");
        $("#delete_fund_id").val(id);
    }
}
function sendFundReminder(id) {
    $("#preloader").show();
    $("#sendFundReminderArea").html('<img src="'+loaderImage+'""> Loading...');
    $(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/contacts/clients/sendFundReminder", 
            data: {"id": id},
            success: function (res) {
                $("#sendFundReminderArea").html(res);
                $("#preloader").hide();
            }
        })
    })
}

$('#deleteRequestedFundEntry').submit(function (e) {
    beforeLoader();
    e.preventDefault();

    if (!$('#deleteRequestedFundEntry').valid()) {
        beforeLoader();
        return false;
    }
    var dataString = '';
    dataString = $("#deleteRequestedFundEntry").serialize();
    $.ajax({
        type: "POST",
        url: baseUrl + "/contacts/clients/deleteRequestedFundEntry", // json datasource
        data: dataString,
        beforeSend: function (xhr, settings) {
            settings.data += '&delete=yes';
        },
        success: function (res) {
                beforeLoader();
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