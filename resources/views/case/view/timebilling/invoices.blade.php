<h3 id="hiddenLable">{{($CaseMaster->case_title)??''}}</h3>
<div id="time_entries_page" class="case_info_page col-12 pt-2" style="">
    <div id="new-case-time-entries" data-court-case-id="14011629" data-show-ledes-info="false"
        data-can-add-time-entry="true" data-can-view-billing-rate="true">
        <div id="time-entry-filter-and-table">
            <div class="d-flex justify-content-between py-1">
                <div class="date-range d-flex align-items-center">
                    <div class="date-range-filter-dropdown dropdown">
                       
                    </div>
                </div>
                @if($caseBiller != null)
                    @can(['case_add_edit', 'billing_add_edit'])
                    <a  href="{{ route('bills/invoices/new') }}?court_case_id={{$CaseMaster['case_id']}}&token={{App\Http\Controllers\CommonController::getToken()}}&contact={{$caseBiller['uid'] ?? ''}}">
                    <button disabled class="btn btn-primary btn-rounded m-1" type="button" id="button"
                    >Add Invoice</button></a>
                    @endcan
                @else
                <div class="alert alert-primary alert-dismissible fade show" role="alert">
                    <p>
                        Create an invoice for the time spent on the case then share it with your contacts right from MyCase.You must link an active client to this case before you can add an invoice by clicking Contacts & Staff.
                    </p>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
            </div>
            <div data-testid="mc-table-container" style="font-size: small;">
                <table class="display table table-striped table-bordered" id="invoiceGrid" style="width:100%">
                    <thead>
                        <tr>
                            <th width="1%">id</th>
                            <th class="col-md-auto nosort" ></th>
                            <th width="10%">Number</th>
                            <th width="10%">Contact</th>
                            <th width="10%">Case</th>
                            <th width="8%">Total</th>
                            <th width="8%">Paid</th>
                            <th width="8%">Amount Due</th>
                            <th width="10%">Due</th>
                            <th width="10%">Created</th>
                            <th width="8%" class="nosort">Status</th>
                            <th width="8%" class="nosort">Viewed</th>
                            <th width="15%" class="nosort"></th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
</div>
@include('billing.invoices.partials.delete_invoice_modal')
<style>
    .pagination{width: 80%;float: right;}
    </style>
@section('page-js-inner')
<script src="{{ asset('assets\js\custom\invoice\listinvoice.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script type="text/javascript">
    "use strict";
    $(document).ready(function () {
        $("#button").removeAttr('disabled');
        var invoiceGrid =  $('#invoiceGrid').DataTable({
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            // stateSave: true,
            searching: false, "aaSorting": [],
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/bills/invoices/loadInvoices",
                type: "post",  
                data :{ 'load' : 'true','global_search':"{{base64_encode($CaseMaster['case_id'])}}-{{base64_encode('case')}}"},
                error: function(){  
                    $("#invoiceGrid_processing").css("display","none");
                }
            },
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] },{'bSortable': false,'aTargets': ['nosort']}],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},    
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                   
                    $('td:eq(0)', nRow).html('<a href="'+baseUrl+'/bills/invoices/view/'+aData.decode_id+'"><button class="btn btn-primary btn-rounded" type="button" id="button">View</button> </a>');
                   
                    $('td:eq(1)', nRow).html('<a href="'+baseUrl+'/bills/invoices/view/'+aData.decode_id+'">'+aData.invoice_id+' </a>');

                    if(aData.user_level == 2)
                        $('td:eq(2)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/clients/'+aData.uid+'">'+aData.contact_name+'</a></div>');
                    else
                        $('td:eq(2)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/companies/'+aData.uid+'">'+aData.contact_name+'</a></div>');

                    $('td:eq(3)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/info">'+aData.ctitle+'</a></div>');
                    
                    
                    $('td:eq(4)', nRow).html('<span class="d-none">'+aData.total_amount_new+'</span><div class="text-left">$'+aData.total_amount_new+'</div>');
                    $('td:eq(5)', nRow).html('<span class="d-none">'+aData.paid_amount_new+'</span><div class="text-left">$'+aData.paid_amount_new+'</div>');

                    var fwd = "";
                    if(aData.status == "Forwarded") {
                        $.each(aData.invoice_forwarded_to_invoice, function(invkey, invitem) {
                            fwd = '<div style="font-size: 11px;">Forwarded to <a href="'+baseUrl+'/bills/invoices/view/'+invitem.decode_id+'">'+invitem.invoice_id+'</a></div>'
                        });
                    }
                    $('td:eq(6)', nRow).html('<span class="d-none">'+aData.due_amount_new+'</span><div class="text-left">$'+aData.due_amount_new+'</div><div>'+fwd+'</div>');
                    $('td:eq(7)', nRow).html('<span class="d-none">'+moment(aData.due_date_new).format('YYYYMMDD')+'</span><div class="text-left">'+aData.due_date_new+'</div>');
                    $('td:eq(8)', nRow).html('<span class="d-none">'+moment(aData.created_date_new).format('YYYYMMDD')+'</span><div class="text-left">'+aData.created_date_new+'</div>');
                    
                    if(aData.status=="Paid"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-success" style="display: inline;"></i>'+aData.status;
                    }else if(aData.status=="Partial"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-warning" style="display: inline;"></i>'+aData.status;
                    }else if(aData.status=="Overdue"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-danger" style="display: inline;"></i>'+aData.status;
                    }else {
                        var curSetatus=aData.status;
                    }
                    $('td:eq(9)', nRow).html('<div class="text-left">'+curSetatus+'</div>');

                    if(aData.invoice_shared.length && aData.invoice_shared[0].is_viewed=="yes"){
                        $('td:eq(10)', nRow).html('<div class="text-left">'+aData.invoice_shared[0].viewed_date+'</div>');
                    }else{
                        $('td:eq(10)', nRow).html('<div class="text-left">Never</div>');
                    }
                    @can(['case_add_edit', 'billing_add_edit'])
                    if(aData.status == "Forwarded") {
                        $('td:eq(11)', nRow).html('');
                    } else {
                        var reminder='';
                        if(aData.status=="Partial" || aData.status=="Draft" || aData.status=="Unsent"){
                            if(aData.is_lead_invoice=="no"){
                                var reminder='<span data-toggle="tooltip" data-placement="top" title="Send Reminder"><a data-toggle="modal"  data-target="#sendInvoiceReminder" data-placement="bottom" href="javascript:;"  onclick="sendInvoiceReminder('+aData.ccid+','+aData.id+');"><i class="fas fa-bell align-middle p-2"></i></a></span>';
                            }
                        }
                        var dollor='&nbsp;';
                        if(aData.status!="Paid"){
                            var dollor='<span data-toggle="tooltip" data-placement="top" title="Record Payment"><a data-toggle="modal"  data-target="#payInvoice" data-placement="bottom" href="javascript:;"  onclick="payinvoice('+aData.id+');"><i class="fas fa-dollar-sign align-middle p-2"></i></a></span>';
                        }
                        var deletes = '';
                        @can('delete_items')
                            deletes='<span data-toggle="tooltip" data-placement="top" title="Delete"><a data-toggle="modal"  data-target="#deleteInvoicePopup" data-placement="bottom" href="javascript:;"  onclick="deleteInvoice('+aData.id+');"><i class="fas fa-trash align-middle p-2"></i></a></span>';
                        @endcan
                        $('td:eq(11)', nRow).html('<div class="text-center" style="white-space: nowrap;float:right;">'+reminder+' '+dollor+' '+deletes+'</div>');
                    }
                    @else
                    $('td:eq(11)', nRow).html('');
                    @endcan
                }
        });
    });


    function printEntry()
    {
        $('#hiddenLable').show();
        var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        $(".main-content-wrap").remove();
        window.print(canvas);
        // w.close();
        window.location.reload();
        return false;
    }
    $('#hiddenLable').hide();

</script>
@stop