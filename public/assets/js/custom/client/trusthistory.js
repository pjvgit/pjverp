$(document).ready(function() {
    var billingTabTrustHistory =  $('#billingTabTrustHistory').DataTable( {
        serverSide: true,
        "dom": '<"top">rt<"bottom"pl>',
        responsive: false,
        processing: true,
        stateSave: true,
        searching: false,
        "order": [[0, "desc"]],
        "ajax":{
            url :baseUrl +"/contacts/clients/loadTrustHistory", // json datasource
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
            { data: 'id'},
            { data: 'id'},
            { data: 'id'},
            { data: 'id'},
            { data: 'id'},
            { data: 'id'}
        ],
        "fnCreatedRow": function (nRow, aData, iDataIndex) {
            
            $('td:eq(0)', nRow).html('<div class="text-left">'+aData.added_date+'</div>');
            if(aData.related_to_fund_request_id != null)
                $('td:eq(1)', nRow).html('<div class="text-left">'+aData.fund_request.padding_id+'</div>');
            else if(aData.related_to_invoice_id != null)
                $('td:eq(1)', nRow).html('<div class="text-left"><a href="'+baseUrl+'/bills/invoices/view/'+aData.decode_id+'" >#'+aData.invoice.invoice_id+'</a></div>');
            else
                $('td:eq(1)', nRow).html('<div class="text-left">-</div>');
            
            var isRefender="";
            if(aData.is_refunded=="yes"){
                isRefender="(Refunded)";
            }

            if(aData.fund_type=="withdraw"){
                if(aData.withdraw_from_account!=null){
                    var ftype="Withdraw from Trust (Trust Account) to Operating("+aData.withdraw_from_account+")";
                }else{
                    var ftype="Withdraw from Trust (Trust Account)" +isRefender;
                }
            }else if(aData.fund_type=="refund_withdraw"){
                var ftype="Refund Withdraw from Trust (Trust Account)";
            }else if(aData.fund_type=="refund_deposit"){
                var ftype="Refund Deposit into Trust (Trust Account)";
            }else{
                var ftype="Deposit into Trust (Trust Account)"+isRefender;
            }
            $('td:eq(2)', nRow).html('<div class="text-left">'+ftype+'</div>');
            
            
            if(aData.fund_type=="withdraw"){
                $('td:eq(3)', nRow).html('<div class="text-left">Trust  '+isRefender+'</div>');
            }else{
                
                $('td:eq(3)', nRow).html('<div class="text-left">'+aData.payment_method+' '+isRefender+'</div>');
            }
            
            if(aData.allocated_to_case_id != null) {
                var clientLink='<a class="name" href="'+baseUrl+'/court_cases/'+aData.allocate_to_case.case_unique_number+'/info/">'+aData.allocate_to_case.case_title+'</a>';
            } else {
                var clientLink='<a class="name" href="'+baseUrl+'/contacts/clients/'+aData.client_id+'">'+aData.client_name+' (Client)</a>';
            }
            $('td:eq(4)', nRow).html('<div class="text-left">'+clientLink+'</div>');

            if(aData.fund_type=="withdraw"){
                $('td:eq(5)', nRow).html('<div class="text-left">-$'+aData.withdraw+'</div>');
            }else if(aData.fund_type=="refund_withdraw"){
                $('td:eq(5)', nRow).html('<div class="text-left">$'+aData.refund+'</div>');
            }else if(aData.fund_type=="refund_deposit"){
                $('td:eq(5)', nRow).html('<div class="text-left">-$'+aData.refund+'</div>');
            }else{
                $('td:eq(5)', nRow).html('<div class="text-left">$'+aData.amount_paid+'</div>');
            }

            $('td:eq(6)', nRow).html('<div class="text-left">$'+aData.trust_balance+'</div>')
            
            if(aData.is_refunded=="yes"){
                var deelete='<span data-toggle="popover" data-trigger="hover" title="" data-content="Delete" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#deleteLocationModal" data-placement="bottom" href="javascript:;" onclick="deleteEntry('+aData.id+');"><button type="button" disabled="" class="py-0 btn btn-link disabled">Delete</button></a></span>';

                var refund='<span data-toggle="popover" data-trigger="hover" title="" data-content="Edit" data-placement="top" data-html="true"><a ><button type="button" disabled="" class="py-0 btn btn-link disabled">Refund</button></a></span>';
            }else{
                var deelete='<span data-toggle="popover" data-trigger="hover" title="" data-content="Delete" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#deleteLocationModal" data-placement="bottom" href="javascript:;" onclick="deleteEntry('+aData.id+');"><button type="button" class="py-0 btn btn-link">Delete</button></a></span>';

                if(aData.fund_type=="refund_withdraw" || aData.fund_type=="refund_deposit"){
                    var refund='<span data-toggle="popover" data-trigger="hover" title="" data-content="Edit" data-placement="top" data-html="true"><a ><button type="button" disabled="" class="py-0 btn btn-link disabled">Refund</button></a></span>';
                }else{
                    var refund='<span data-toggle="popover" data-trigger="hover" title="" data-content="Edit" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#RefundPopup" data-placement="bottom" href="javascript:;"  onclick="RefundPopup('+aData.id+');"><button type="button"  class="py-0 btn btn-link ">Refund</button></a></span>';
                }

            }
            

            $('td:eq(7)', nRow).html('<div class="text-center">'+refund+'<br>'+deelete+'<div role="group" class="btn-group-sm btn-group-vertical"></div></div>');
            
        },
        "initComplete": function(settings, json) {
            $('td').css('font-size',parseInt('13px'));  
        },
        "drawCallback": function (settings) { 
            var response = settings.json;
            $(".trust-total-balance").text(response.trust_total);
        },
    });

    $('#depositIntoTrustAccount').on('hidden.bs.modal', function () {
        billingTabTrustHistory.ajax.reload(null, false);
    });
});


function depositIntoTrust(clientId = null) {
    $('.showError').html('');
    
    $("#preloader").show();
    $("#depositIntoTrustArea").html('');
    $("#depositIntoTrustArea").html('Loading...');
    $.ajax({
        type: "POST",
        url: baseUrl + "/bills/dashboard/depositIntoTrust",
        data: {
            "id": null
        },
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
              
                $("#preloader").hide();
                $("#depositIntoTrustArea").html('');
                return false;
            } else {
                afterLoader()
                $("#depositIntoTrustArea").html(res);
                if(clientId != null) {
                    $("#contact").val(clientId).trigger('change');
                    setTimeout(() => {
                        $("#contact").trigger({
                            type: 'select2:select',
                            params: {
                                data: {
                                    id: clientId
                                }
                            }
                        });
                    }, 300);
                    $("#contact_div").hide();
                }
                $("#preloader").hide();
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