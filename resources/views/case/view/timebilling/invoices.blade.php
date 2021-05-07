<div id="time_entries_page" class="case_info_page col-12 pt-2" style="">
    <div id="new-case-time-entries" data-court-case-id="14011629" data-show-ledes-info="false"
        data-can-add-time-entry="true" data-can-view-billing-rate="true">
        <div id="time-entry-filter-and-table">
            <div class="d-flex justify-content-between py-1">
                <div class="date-range d-flex align-items-center">
                    <div class="date-range-filter-dropdown dropdown">
                       
                    </div>
                </div>
                <a  href="{{BASE_URL}}/bills/invoices/new?court_case_id={{$CaseMaster['case_id']}}&token={{App\Http\Controllers\CommonController::getToken()}}">
                <button disabled class="btn btn-primary btn-rounded m-1" type="button" id="button"
                   >Add Invoice</button></a>
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
<style>
    .pagination{width: 80%;float: right;}
    </style>
@section('page-js-inner')
<script type="text/javascript">
    "use strict";
    $(document).ready(function () {
        $("#button").removeAttr('disabled');
        var invoiceGrid =  $('#invoiceGrid').DataTable({
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
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
                   
                    $('td:eq(0)', nRow).html('<a href="{{BASE_URL}}bills/invoices/view/'+aData.decode_id+'"><button class="btn btn-primary btn-rounded" type="button" id="button">View</button> </a>');
                   
                    $('td:eq(1)', nRow).html('<a href="{{BASE_URL}}bills/invoices/view/'+aData.decode_id+'">'+aData.invoice_id+' </a>');

                    $('td:eq(2)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/clients/'+aData.uid+'">'+aData.contact_name+'</a></div>');


                    $('td:eq(3)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/info">'+aData.ctitle+'</a></div>');
                    
                    
                    $('td:eq(4)', nRow).html('<div class="text-left">$'+aData.total_amount_new+'</div>');
                    $('td:eq(5)', nRow).html('<div class="text-left">$'+aData.paid_amount_new+'</div>');
                    $('td:eq(6)', nRow).html('<div class="text-left">$'+aData.due_amount_new+'</div>');
                    $('td:eq(7)', nRow).html('<div class="text-left">'+aData.due_date_new+'</div>');
                    $('td:eq(8)', nRow).html('<div class="text-left">'+aData.created_date_new+'</div>');
                    
                    if(aData.status=="Paid"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-success" style="display: inline;"></i>'+aData.status;
                    }else if(aData.status=="Partial"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-warning" style="display: inline;"></i>'+aData.status;
                    }else if(aData.status=="Overdue"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-danger" style="display: inline;"></i>'+aData.status;
                    }else if(aData.status=="Unsent"){
                        var curSetatus=aData.status;
                    }else if(aData.status=="Sent"){
                        var curSetatus=aData.status;
                    }else if(aData.status=="Forwarded"){
                        var curSetatus=aData.status;
                    }else if(aData.status=="Overdue"){
                        var curSetatus=aData.status;
                    }else if(aData.status=="Draft"){
                        var curSetatus=aData.status;
                    }
                    $('td:eq(9)', nRow).html('<div class="text-left">'+curSetatus+'</div>');

                    if(aData.is_viewed=="no"){
                        $('td:eq(10)', nRow).html('<div class="text-left">Never</div>');
                    }else{
                        $('td:eq(10)', nRow).html('<div class="text-left">Yes</div>');
                    }

                    var reminder='';
                    if(aData.status=="Partial" || aData.status=="Draft" || aData.status=="Unsent"){
                        var reminder='<span data-toggle="tooltip" data-placement="top" title="Send Reminder"><a data-toggle="modal"  data-target="#sendInvoiceReminder" data-placement="bottom" href="javascript:;"  onclick="sendInvoiceReminder('+aData.ccid+','+aData.id+');"><i class="fas fa-bell align-middle p-2"></i></a></span>';
                    }
                    var dollor='&nbsp;';
                    if(aData.status!="Paid"){
                        var dollor='<span data-toggle="tooltip" data-placement="top" title="Record Payment"><a data-toggle="modal"  data-target="#payInvoice" data-placement="bottom" href="javascript:;"  onclick="payinvoice('+aData.id+');"><i class="fas fa-dollar-sign align-middle p-2"></i></a></span>';
                    }
                    var deletes='<span data-toggle="tooltip" data-placement="top" title="Delete"><a data-toggle="modal"  data-target="#deleteInvoiceCommon" data-placement="bottom" href="javascript:;"  onclick="deleteInvoiceCommon('+aData.id+');"><i class="fas fa-trash align-middle p-2"></i></a></span>';
                    $('td:eq(11)', nRow).html('<div class="text-center" style="white-space: nowrap;float:right;">'+reminder+' '+dollor+' '+deletes+'</div>');
                }
        });
    });
</script>
@stop