@extends('layouts.master')
@section('title', 'Payment History - Account Activity - Billing')
@section('main-content')

@include('billing.submenu')
<?php
$range=$account=""; 
if(isset($_GET['date_range'])){
    $range= $_GET['date_range'];
}
if(isset($_GET['bank_account'])){
    $account= $_GET['bank_account'];
}
?>
<div class="separator-breadcrumb border-top"></div>
<div class="row">

    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <div class="d-flex align-items-center pl-4 pb-4">
                        <h3> Account Activity</h3>
                        <ul class="d-inline-flex nav nav-pills pl-4">
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/account_activity')}}?type=payment_history"
                                    class="nav-link <?php if(!isset($_GET['type']) || $_GET['type']=='payment_history') echo "active"; ?> ">Payment
                                    History</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/trust_account_activity')}}?type=trust_account"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='trust_account') echo "active"; ?>">Trust
                                    Accounting</a>
                            </li>

                        </ul>
                        <div class="ml-auto d-flex align-items-center d-print-none">
                            <button onclick="printEntry();return false;" class="btn btn-link">
                                <i class="fas fa-print text-black-50" data-toggle="tooltip" data-placement="top"
                                    title="" data-original-title="Print"></i>
                                <span class="sr-only">Print This Page</span>
                            </button>
                        </div>

                    </div>
                    <div class="row pl-4 pb-4">

                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Date Range</label>
                            <input type="text" class="form-control" id="daterange" name="date_range" value="{{$range}}"
                                placeholder="" />
                        </div>

                        <div class="col-md-3 form-group mb-3">
                            <label for="picker1">Bank Account</label>
                            <select id="bank_account" name="bank_account" class="form-control colformSubmit">
                                <option></option>
                                <option <?php if($account=="trust_account"){ echo "selected=selected";}?>  value="trust_account">Trust Account</option>
                                <option <?php if($account=="operating_account"){ echo "selected=selected";}?> value="operating_account">Operating Account</option>
                            </select>
                        </div>
                        <div class="col-md- form-group mb-3 mt-3">
                            <button class="btn btn-info btn-rounded m-1" type="submit">Apply Filters</button>
                        </div>
                        <div class="col-md-1 form-group mb-3 mt-3 pt-1">
                            <button type="button" class="test-clear-filters text-black-50 btn btn-link">
                                <a href="{{route('bills/account_activity')}}">Clear Date</a>
                            </button>
                        </div>

                    </div>
                </form>
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="paymentHistoryActivityTab" style="width:100%">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th width="10%">Date</th>
                                <th class="text-nowrap" width="10%">Related To</th>
                                <th class="text-nowrap" width="10%">Contact</th>
                                <th class="text-nowrap" width="10%">Case</th>
                                <th class="text-nowrap" width="10%">Entered By</th>
                                <th class="text-nowrap" width="30%">Notes</th>
                                <th class="text-nowrap" width="10%">Amount</th>
                                <th class="d-print-none" width="10%">Total </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!--Over-->
<style>
    .pagination {
        width: 80%;
        float: right;
    }
    td,th{
        white-space: nowrap;
    }
</style>

@section('page-js-inner')
<script type="text/javascript">
    "use strict";
    $(document).ready(function () {
        $("#button").removeAttr('disabled');
        $('#daterange').daterangepicker({
            locale: {
                applyLabel: 'Select'
            },
            ranges: {
                'All Days': ['01/01/2020', moment()],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'Last 90 Days': [moment().subtract(89, 'days'), moment()],
                'Month to date': [moment().startOf('month').format('MM/DD/YYYY'), moment()],
                'Year to date': [moment().startOf('year').format('MM/DD/YYYY'), moment()]
            },
            "showCustomRangeLabel": false,
            "alwaysShowCalendars": true,
            "autoUpdateInput": true,
            "opens": "center",
            "minDate": "01/01/2020"
        }, function (start, end, label) {
            $("#daterange").val(label);
        });
        // $('#daterange').data('daterangepicker').setStartDate('03/01/2014');
        $("#bank_account").select2({
            placeholder: "Select a bank account",
            theme: "classic",
            allowClear: true
        });
        $('.formSubmit').change(function () {
            this.form.submit();
        });
        var paymentHistoryActivityTab = $('#paymentHistoryActivityTab').DataTable({
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            searching: false,
            stateSave: true,
            "order": [
                [0, "desc"]
            ],
            "ajax": {
                url: baseUrl + "/bills/activities/loadAccountActivity", // json datasource
                type: "post", // method  , by default get
                data: { 'range': '{{$range}}','account': '{{$account}}'},
                error: function () { // error handling
                    $(".paymentHistoryActivityTab-error").html("");
                    $("#paymentHistoryActivityTab_processing").css("display", "none");
                }
            },
            "aoColumnDefs": [{
                "bVisible": false,
                "aTargets": [0]
            }],
            pageResize: true, // enable page resize
            pageLength: <?php echo USER_PER_PAGE_LIMIT; ?>,
            columns: [

                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                }
            ],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                $('td:eq(0)', nRow).html('<div class="text-left">' + aData.added_date + '</div>');

                if(aData.section=="invoice"){
                    $('td:eq(1)', nRow).html('<a href="{{BASE_URL}}bills/invoices/view/' + aData
                    .decode_id + '">#' + aData.related + '</a>');
                }else if(aData.section=="request"){
                    $('td:eq(1)', nRow).html('#R-' + aData.related + '</a>');
                }else{
                    $('td:eq(1)', nRow).html('<i class="table-cell-placeholder"></i>');
                }
                var Contact = JSON.parse(aData.contact);
                if(Contact==null || aData.contact==null){
                    $('td:eq(2)', nRow).html('<i class="table-cell-placeholder"></i>');
                }else{
                    $('td:eq(2)', nRow).html('<div class="text-left"><a class="name" href="' + baseUrl +
                    '/contacts/clients/' + Contact.id + '">' + Contact.name + '</a></div>');
                }
                var Case = JSON.parse(aData.case);
                if(Case==null || aData.case==null){
                    $('td:eq(3)', nRow).html('<i class="table-cell-placeholder"></i>');
                }else{
                $('td:eq(3)', nRow).html('<div class="text-left"><a class="name" href="' + baseUrl +
                    '/court_cases/' + Case.case_unique_number + '/activity">' + Case
                    .case_title + '</a></div>');
                }
                $('td:eq(4)', nRow).html('<div class="text-left">' + aData.entered_by + '</div>');
                if(aData.from_pay=="trust"){

                    $('td:eq(5)', nRow).html('<div class="text-left">Payment from Trust (Trust Account) to Operating (Operating Account)</div>');
                }else{
                    $('td:eq(5)', nRow).html('<div class="text-left">Payment into Operating (Operating Account)	</div>');

                }
                if(aData.c_amt=="0.00"){
                    $('td:eq(6)', nRow).html('<div class="text-left"><i class="table-cell-placeholder"></i></div>');
                }else{
                    $('td:eq(6)', nRow).html('<div class="text-left">$<span class="payRow">' + aData
                    .c_amt + '</span></div>');
                }
                
                $('td:eq(7)', nRow).html('<div class="text-left">$<span class="payRow">' + aData
                    .t_amt + '</span></div>');

            },
            "initComplete": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip();
                $("[data-toggle=popover]").popover();
                $('.payRow').number(true, 2);
            }
        });
        // var paymentHistoryActivityTab = $('#paymentHistoryActivityTab').DataTable({
        //     serverSide: true,
        //     "dom": '<"top">rt<"bottom"pl><"clear">',
        //     responsive: false,
        //     processing: true,
        //     searching: false,
        //     "order": [
        //         [0, "desc"]
        //     ],
        //     "ajax": {
        //         url: baseUrl + "/bills/activities/loadAccountActivity", // json datasource
        //         type: "post", // method  , by default get
        //         data: { 'range': '{{$range}}','account': '{{$account}}'},
        //         error: function () { // error handling
        //             $(".paymentHistoryActivityTab-error").html("");
        //             $("#paymentHistoryActivityTab_processing").css("display", "none");
        //         }
        //     },
        //     "aoColumnDefs": [{
        //         "bVisible": false,
        //         "aTargets": [0]
        //     }],
        //     pageResize: true, // enable page resize
        //     pageLength: <?php echo USER_PER_PAGE_LIMIT; ?>,
        //     columns: [

        //         {
        //             data: 'id',
        //             'sorting': false
        //         },
        //         {
        //             data: 'id',
        //             'sorting': false
        //         },
        //         {
        //             data: 'id',
        //             'sorting': false
        //         },
        //         {
        //             data: 'id',
        //             'sorting': false
        //         },
        //         {
        //             data: 'id',
        //             'sorting': false
        //         },
        //         {
        //             data: 'id',
        //             'sorting': false
        //         },
        //         {
        //             data: 'id',
        //             'sorting': false
        //         },
        //         {
        //             data: 'id',
        //             'sorting': false
        //         },
        //         {
        //             data: 'id',
        //             'sorting': false
        //         }
        //     ],
        //     "fnCreatedRow": function (nRow, aData, iDataIndex) {

        //         $('td:eq(0)', nRow).html('<div class="text-left">' + aData.added_date + '</div>');
              
        //         $('td:eq(1)', nRow).html('<a href="{{BASE_URL}}bills/invoices/view/'+aData.decode_id+'">#'+aData.invoice_id+'</a>');
        //         var Contact = JSON.parse(aData.contact);
        //         $('td:eq(2)', nRow).html('<div class="text-left"><a class="name" href="' + baseUrl +
        //             '/contacts/clients/' + Contact.id +'">' +  Contact.name + '</a></div>');

        //         var Case = JSON.parse(aData.case);
        //         $('td:eq(3)', nRow).html('<div class="text-left"><a class="name" href="' + baseUrl +
        //             '/court_cases/' + Case.case_unique_number + '/activity">' +  Case.case_title + '</a></div>');

        //         $('td:eq(4)', nRow).html('<div class="text-left">' + aData.entered_by + '</div>');

        //         if(aData.status==0){
        //             if(aData.deposit_into=="Operating Account"){
        //                 $('td:eq(5)', nRow).html('<div class="text-left">Payment into Operating (Operating Account)</div>');
        //                 $('td:eq(6)', nRow).html('<div class="text-left">$<span class="payRow">' + aData.amount_paid + '</span></div>');
        //             }else{
        //                 $('td:eq(5)', nRow).html('<div class="text-left">Payment from Trust (Trust Account) to Operating (Operating Account)</div>');
        //                 $('td:eq(6)', nRow).html('<div class="text-left">$<span class="payRow">' + aData.amount_paid + '</span></div>');
        //             }
        //         }else{
        //             $('td:eq(5)', nRow).html('<div class="text-left"><a href="javascript:void(0);" data-toggle="popover" data-trigger="focus" title="Notes" data-content="'+aData.refund_title+'" class="pr-2"><img style="border: none;" src="{{BASE_URL}}public/svg/note.svg"></a> Refund  Payment into Operating (Operating Account)</div>');
        //             $('td:eq(6)', nRow).html('<div class="text-left">-$<span class="payRow">' + aData.amount_refund + '</span></div>');

        //         }
        //         $('td:eq(7)', nRow).html('<div class="text-left">$<span class="payRow">' + aData.total + '</span></div>');

        //     },
        //     "initComplete": function (settings, json) {
        //         $('[data-toggle="tooltip"]').tooltip();
        //         $("[data-toggle=popover]").popover();
        //         $('.payRow').number(true, 2);
        //     }
        // });
        $('#actionbutton').attr('disabled', 'disabled');


    });
    function printEntry()
    {
        var info = $('#paymentHistoryActivityTab').DataTable().page.info();
        var current_page=info.page;
        var length=info.length;
        var orderon=$('#paymentHistoryActivityTab').dataTable().fnSettings().aaSorting;
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/printAccountActivity",
                data :{ 'range': '{{$range}}','account': '{{$account}}','current_page':current_page,'length':length,'orderon':orderon },
                success: function (res) {
                    window.open(res.url, '_blank');
                    window.print();
                    $("#preloader").hide();
                }
            })
        });
    }
</script>
@stop
@endsection
