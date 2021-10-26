@extends('layouts.master')
@section('title', 'Payment History - Account Activity - Billing')
@section('main-content')
<style>
.test-note-callout{
    display: block;
    width: 12px;
    margin-left: 25px;
}
</style>
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
                            </button>
                            <div class="mx-2">
                                <div class="btn-group show">
                                    <button class="btn text-muted dropdown-toggle" data-toggle="dropdown"
                                     aria-haspopup="true" aria-expanded="false"  id="trustDropdown">
                                        <i class="fas fa-file-download text-black-50" data-toggle="tooltip" data-placement="top"
                                            title="" data-original-title="Export Report"></i>
                                    </button>
                                    <div class="dropdown-menu bg-transparent shadow-none p-0 m-0 ">
                                        <div class="card">
                                            <button onclick="exportCSV('csv');return false;" type="button" tabindex="-1" role="menuitem" class="dropdown-item btn">
                                                as CSV</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                <th>Date</th>
                                <th>Related To</th>
                                <th>Contact</th>
                                <th>Case</th>
                                <th>Entered By</th>
                                <th>Notes</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th class="d-print-none">Total </th>
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
        $("#trustDropdown").trigger('click');
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
            /* autoWidth: false,
            columns : [
                { width : '50px' },
                { width : '50px' },
                { width : '50px' },
                { width : '50px' },        
                { width : '50px' },
                { width : '50px' },
                { width : '50px' },
                { width : '50px' },                
                { width : '50px' },
                { width : '50px' }        
            ], */
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
                },
                {
                    data: 'id',
                    'sorting': false
                },
            ],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                $('td:eq(0)', nRow).html('<div class="text-left">' + aData.added_date + '</div>');

                if(aData.section=="invoice" && aData.lead_additional_info == null){
                    $('td:eq(1)', nRow).html('<a href="{{BASE_URL}}bills/invoices/view/' + aData
                    .decode_id + '">#' + aData.related + '</a>');
                } else if(aData.section=="invoice" && aData.lead_additional_info != null){
                    $('td:eq(1)', nRow).html('<a href="{{BASE_URL}}bills/invoices/potentialview/' + aData
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
                    if(aData.enter_by_user_level == "2"){
                        $('td:eq(2)', nRow).html('<div class="text-left"><a class="name" href="' + baseUrl +
                        '/contacts/clients/' + Contact.id + '">' + Contact.name + '</a></div>');
                    } else if(aData.enter_by_user_level == 5) {
                        $('td:eq(2)', nRow).html('<div class="text-left"><a class="name" href="' + baseUrl + '/leads/' + aData.user_id + '/lead_details/info">' + aData.enter_by + '</a></div>');
                    }else{
                        $('td:eq(2)', nRow).html('<div class="text-left"><a class="name" href="' + baseUrl +
                        '/contacts/companies/' + Contact.id + '">' + Contact.name + '</a></div>');
                    }
                }
                var Case = JSON.parse(aData.case);
                if(Case!=null && aData.case!=null){
                    $('td:eq(3)', nRow).html('<div class="text-left"><a class="name" href="' + baseUrl +
                    '/court_cases/' + Case.case_unique_number + '/payment_activity">' + Case.case_title + '</a></div>');
                } else if((aData.section=="request" || aData.section=="invoice") && aData.lead_additional_info != null) {
                    $("td:eq(3)", nRow).html('<div class="text-left"><a class="name" href="' + baseUrl +
                        '/leads/' + aData.user_id + '/case_details/info/">' + aData.lead_additional_info.potential_case_title + '</a></div>')
                }else{
                    $('td:eq(3)', nRow).html('<i class="table-cell-placeholder"></i>');
                }

                var noteContent = '';
                if(aData.notes != null) {
                    noteContent += '<div class="position-relative">\
                            <a class="test-note-callout d-print-none " tabindex="0" data-toggle="popover" data-html="true" data-placement="bottom" data-trigger="focus" title="Notes" data-content="<div>'+aData.notes+'</div>">\
                                <img style="border: none;" src="'+imgBaseUrl+'icon/note.svg'+'">\
                            </a>\
                        </div>';
                }
                $('td:eq(4)', nRow).html('<div style="display: flex !important; justify-content: space-between !important;"><div class="text-left">' + aData.entered_by +'</div>'+ ' ' +noteContent+'</div>');
                
                $('td:eq(5)', nRow).html('<div class="text-left">'+aData.payment_note+'</div>');
                $('td:eq(6)', nRow).html(aData.payment_method);
                if(aData.d_amt=="0.00" && aData.c_amt > 0){
                    $('td:eq(7)', nRow).html('<div class="text-left">$<span class="payRow">' + aData.c_amt + '</span></div>');
                } else if(aData.c_amt=="0.00" && aData.d_amt > 0) {
                    $('td:eq(7)', nRow).html('<div class="text-left">-$<span class="payRow">' + aData.d_amt + '</span></div>');
                }else{
                    $('td:eq(7)', nRow).html('<div class="text-left"><i class="table-cell-placeholder"></i></div>');
                }
                
                $('td:eq(8)', nRow).html('<div class="text-left">$<span class="payRow">' + aData
                    .t_amt + '</span></div>');

            },
            "initComplete": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip();
                $("[data-toggle=popover]").popover();
                $('.payRow').number(true, 2);
            }
        });
        
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
                    $(".printDiv").html(res);
                    var canvas = $(".printDiv").html();
                    window.print(canvas);
                    $(".printDiv").html('');
                    $("#preloader").hide();
                    return false;                    
                }
            })
        });
    }

    function exportCSV(type){
        var info = $('#paymentHistoryActivityTab').DataTable().page.info();
        var current_page=info.page;
        var length=info.length;
        var orderon=$('#paymentHistoryActivityTab').dataTable().fnSettings().aaSorting;
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/printAccountActivity",
                data :{ 'range': '{{$range}}','account': '{{$account}}','current_page':current_page,'length':length,'orderon':orderon,'exportType':type },
                success: function (res) {
                    $("#preloader").hide();
                    swal('Success!', res.msg, 'success');
                    window.open(res.url);
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                }
            })
        });
    }
</script>
@stop
@endsection
