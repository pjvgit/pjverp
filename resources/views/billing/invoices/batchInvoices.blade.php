@extends('layouts.master')
@section('title', 'All invoices - Billing')
@section('main-content')

@include('billing.submenu')
<?php
if(isset($_GET['global_search']) && $_GET['global_search']!="")
{
    $MixVal=explode("-",$_GET['global_search']);
    $serachOn=base64_decode($MixVal[1]);
    $serachBy=base64_decode($MixVal[0]);
}else{
    $serachOn="";
    $serachBy="";
    $_GET['global_search']='';
}
?>
<div class="separator-breadcrumb border-top"></div>
<div class="row" id="printHtml">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <div class="d-flex align-items-center pl-4 pb-4">
                        <h3> Invoices</h3>
                        <ul class="d-inline-flex nav nav-pills pl-4 d-print-none">
                            <input type="hidden" name="type" value="{{$_GET['type']}}">
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=all&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='all' ) echo "active"; ?>">All</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=unsent&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='unsent') echo "active"; ?>">Unsent</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=sent&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='sent') echo "active"; ?>">Sent</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=paid&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='paid') echo "active"; ?>">Paid</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=partial&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='partial') echo "active"; ?>">Partial</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=overdue&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='overdue') echo "active"; ?>">Overdue</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=forwarded&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='forwarded') echo "active"; ?>">Forwarded</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=draft&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='draft') echo "active"; ?>">Draft</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=batches&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='batches') echo "active"; ?>">Batches</a>
                            </li>

                        </ul>

                        <div class="ml-auto d-flex align-items-center d-print-none">
                            <button onclick="printEntry();return false;" class="btn btn-link text-black-50 pendo-case-print d-print-none">
                                <i class="fas fa-print"></i> Print
                            </button>

                            <a href="{{BASE_URL}}bills/invoices/open">
                                <button class="btn btn-primary btn-rounded m-1" type="button" id="button">Add
                                    Invoice</button>
                            </a>
                        </div>

                    </div>


                    <div id="invoices_list" class="invoices-list-container">
                </form>
                <?php if($InvoiceCounter<=0){?>
                <div class="empty-state">
                    <img alt="Invoice Example" class="thumbnail" src="{{BASE_URL}}images/invoice.png">
                    <div class="text-container">
                        <h2>Add your first invoice</h2>
                        <ul>
                            <li> Easily generate invoices from time entries and expenses and share them through
                                the client portal. </li>
                            <li> Track the status of your invoices at a glance, view payments history and set up
                                online payments to get paid faster. </li>
                        </ul>
                    </div>
                </div>
                <?php }else{
                        ?>
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="invoiceGrid" style="width:100%">
                        <thead>
                            <tr>                                
                                <th width="0%" class="col-md-auto">id</th>
                                <th width="40%" class="col-md-auto">Batch</th>
                                <th width="20%">Created By</th>
                                <th width="10%" class="col-md-auto nosort">Invoices</th>
                                <th width="10%" class="col-md-auto nosort">Draft</th>
                                <th width="10%" class="col-md-auto nosort">Unsent</th>
                                <th width="10%" class="col-md-auto nosort">Sent</th>
                            </tr>
                        </thead>
                    </table>
                </div><?php 
                    }?>
                <br>
            </div>
        </div>
    </div>
</div>
</div>

@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        $(".global_search").select2({
            placeholder: "Filter bills by case, billing contact or batch...",
            theme: "classic",
            allowClear: true
        });
        $('.dropdown-toggle').dropdown();
        $('#payInvoice').on('hidden.bs.modal', function () {
            invoiceGrid.ajax.reload(null, false);
        });
        var invoiceGrid = $('#invoiceGrid').DataTable({
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            searching: false,
            stateSave: true,
            "aaSorting": [],
            "order": [
                [0, "desc"]
            ],
            "ajax": {
                url: baseUrl + "/bills/invoices/loadBatchInvoices",
                type: "post",
                data: {
                    'load': 'true'
                },
                error: function () {
                    $("#invoiceGrid_processing").css("display", "none");
                }
            },
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] },{'bSortable': false,'aTargets': ['nosort']}],
            pageResize: true, 
            pageLength: {{ USER_PER_PAGE_LIMIT}},
            columns: [
                {data: 'id'},
                {data: 'batch_code'},
                {data: 'created_name'},
                {data: 'total_invoice','sorting': false},
                {data: 'draft_invoice','sorting': false},
                {data: 'unsent_invoice','sorting': false},
                {data: 'sent_invoice','sorting': false}
            ],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                $('td:eq(0)', nRow).html('<div class="text-left"><a class="name" href="' + baseUrl +'/bills/invoices?type=all&global_search=' + aData.decode_id + '-'+aData.decode_type+'">' + aData.batch_code +'</a></div>');
            }
        });
    });

    function printEntry()
    {
        $('#invoiceGrid_length').hide();
        $('#invoiceGrid_info').hide();
        $('#invoiceGrid_paginate').hide();
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
@endsection
