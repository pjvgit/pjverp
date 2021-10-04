<?php
$CommonController= new App\Http\Controllers\CommonController();
?>
<div class="row ">
    <div class="col">
    </div>
    <div class="col">
        <div class="float-right">          
            <div id="bulk-dropdown" class="mr-2 actions-button btn-group">
                <div class="mx-2">
                    <div class="btn-group show">
                        <button class="btn btn-info m-1 dropdown-toggle" data-toggle="dropdown"
                            id="actionbutton" disabled="disabled" aria-haspopup="true" aria-expanded="false">
                            Add New
                        </button>
                        <div class="dropdown-menu bg-transparent shadow-none p-0 m-0 ">
                            <div class="card">
                                <button type="button" tabindex="0" role="menuitem" class="dropdown-item">
                                    <a data-toggle="modal" data-target="#addNewInvoice" data-placement="bottom" href="javascript:;"
                                        onclick="addNewInvoice();">Consultation Invoice</a>
                                </button>
                                <button type="button" tabindex="0" role="menuitem" class="dropdown-item">
                                    <a data-toggle="modal" data-target="#addRequestFund" data-placement="bottom" href="javascript:;"
                                        onclick="addRequestFundPopup();">Funds Request</a>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
if($totalInvoiceData<=0){
?>
<br>
<div class="d-flex flex-column justify-content-center align-items-center mt-4" style="height: 250px;">
    <i class="fas fa-file-invoice fa-2x m-3"></i>
    <h5 class="mt-2"><strong>Invoice for your consultation fee</strong></h5><span class="font-weight-light">Create
        and send an invoice to your lead for consultation fees or any pre-case fees.</span>
    <a data-toggle="modal" data-target="#addNewInvoice" data-placement="bottom" href="javascript:;">
        <button type="button" data-testid="empty-state-add-invoice" class="mt-2 font-weight-light btn btn-link"
        onclick="addNewInvoice();">Add Invoice</button>
    </a>
    
</div>
<?php
}else{?>
<div class="table-responsive">
    <table class="display table table-striped table-bordered" id="invoiceList" style="width:100%">
        <thead>
            <tr>
                <th width="5%">TYPE</th>
                <th width="10%">NUMBER</th>
                <th width="10%">AMOUNT</th>
                <th width="10%">CREATED</th>
                <th width="10%">DUE</th>
                <th width="10%">ACCOUNT</th>
                <th width="15%">ALLOCATAION</th>
                <th width="10%">STATUS</th>
                <th width="20%"></th>
            </tr>
        </thead>
    </table>
</div>
<?php } ?>
@section('page-js-inner')
<script src="{{ asset('assets\js\custom\client\fundrequest.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script type="text/javascript">
    $(document).ready(function () {
    $("#actionbutton").trigger("click");
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var dataTableinvoiceList =  $('#invoiceList').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"p><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false, "ordering": false,
            "ajax":{
                url :baseUrl +"/leads/loadInvoices", // json datasource
                type: "post", 
                data :{ 'user_id' : '{{$user_id}}' },
                error: function(){  
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            pageResize: true,  
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    if(aData.invoice_id){
                        $('td:eq(0)', nRow).html('<div class="text-left"><i class="fas fa-file-invoice"></i></div>'); 
                    }else{
                        $('td:eq(0)', nRow).html('<div class="text-left"><i class="fas fa-hand-holding-usd"></i></div>'); 
                    }
                    $('td:eq(1)', nRow).html('<div class="text-left"><a href="'+baseUrl+'/bills/invoices/potentialview/'+aData.decode_id+'">'+aData.invoice_id+'</a></div>'); 
                   
                    if(aData.total_amount==null){
                        $('td:eq(2)', nRow).html('<div class="text-left"></div>'); 
                    }else{
                        $('td:eq(2)', nRow).html('<div class="text-left">$'+aData.total_amount+'</div>'); 
                    }
                    
                    $('td:eq(3)', nRow).html('<div class="text-left">'+aData.invoice_date+'</div>'); 
                    $('td:eq(4)', nRow).html('<div class="text-left">'+aData.due_date+'</div>'); 
                    
                    $('td:eq(5)', nRow).html('<div class="text-left"><i class="table-cell-placeholder"></i></div>'); 
                    $('td:eq(6)', nRow).html('<div class="text-left"><i class="table-cell-placeholder"></i></div>'); 

                    if(aData.is_pay=="Partial"){
                        var f='text-warning';
                    }else{
                        var f='text-success';
                    }
                    var fLabel='<span class="intake-form-status d-flex align-items-center"><i class="fas fa-circle  '+f+' mr-2"></i>'+aData.status+'</span>';
                    $('td:eq(7)', nRow).html('<div class="text-left">'+fLabel+'</div>');
                    var editOption='<a  data-toggle="modal"  data-target="#editInvoice" onclick="editInvoice('+aData.id+')" data-placement="bottom"   href="javascript:;"  class="btn btn-link copyButton"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Edit"><i class="fas fa-pen align-middle" data="MyText"></i></span></a>';
                    // var sendOption='<a class="btn btn-lg btn-link px-2 mr-2 text-black-50" data-toggle="modal"  data-target="#sendInvoice" onclick="sendInvoice('+aData.id+')" data-placement="bottom"><i class="fas fa-envelope" data-toggle="tooltip" data-placement="top" title="" data-original-title="Send"></i></a>';
                    var downloadOption='<a class="btn btn-lg btn-link px-2 text-black-50 bill-export-invoice" data-toggle="modal"  data-target="#downloadInvoice" onclick="downloadInvoice('+aData.id+');"> <i class="fas fa-fw fa-cloud-download-alt test-download-bill" data-toggle="tooltip" data-placement="top" title="" data-original-title="Download"></i></a>';
                    $('td:eq(8)', nRow).html('<div class="d-flex align-items-center float-right">'+editOption+downloadOption+'</div>');
                },
                "initComplete": function(settings, json) {
                    $('th').css('font-size',parseInt('13px'));  
                    $('td').css('font-size',parseInt('13px'));      
                    $("[data-toggle=popover]").popover();
                    $("[data-toggle=tooltip]").tooltip();
                }
        });
        $('#addNewInvoice,#payInvoice').on('hidden.bs.modal', function () {
            dataTableinvoiceList.ajax.reload(null, false);
        });
    });
</script>
@endsection