@extends('layouts.master')
@section('title', 'Simplify Your Law Practice | Cloud Based Practice Management')
@section('main-content')

@include('billing.submenu')
<?php
$court_case=$payment_type=$include_paid_invoices='';
if(isset($_GET['payment_type']) && $_GET['payment_type']){
    $payment_type=$_GET['payment_type'];
}
if(isset($_GET['court_case']) && $_GET['court_case']){
    $court_case=$_GET['court_case'];
}
if(isset($_GET['include_paid_invoices']) && $_GET['include_paid_invoices']){
    $include_paid_invoices=$_GET['include_paid_invoices'];
}

?>
<div class="border-top m-3"></div>
<div class="d-flex align-items-center mt-0 mb-2">
    <h3 class="test-payment-plans-header my-0 font-weight-bold">Payment Plans</h3>
    <a data-toggle="modal" data-target="#loadAddFeedBack" data-placement="bottom" href="javascript::void(0);" class="ml-auto text-black-50 btn btn-link">
        <button onclick="setFeedBackForm('single','Payment Plans');" type="button" class="feedback-button ml-auto text-black-50 btn btn-link">Tell us what you think!</button>
    </a>
</div>
<div class="row pb-3">
    <div class="col-lg-12 col-md-12">
        <div class="accordion" id="accordionRightIcon">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title ul-collapse__icon--size ul-collapse__right-icon mb-0">
                        <a class="text-default" data-toggle="collapse" href="#accordion-item-icon-right-1"
                            aria-expanded="true">Payment Plans Insights

                        </a>
                    </h6>
                </div>
                <div class="collapse show" id="accordion-item-icon-right-1" data-parent="#accordionRightIcon" style="">
                    <div class="card-body">
                        <div class="row ">
                            <div class="col-md-2">
                                <div style="width: 100%; height: 100%;">
                                    <div colors="light" class="payment_plan_average_insights card"
                                        style="height: 100%;">
                                        <div class="card-body">
                                            <div class="row ">
                                                <div class="col-md-12">
                                                    <div>
                                                        <h5><strong>Average Payment Plan Amount</strong><small
                                                                class="text-muted ml-2 pt-1"><br>(Last 90 Days)</small>
                                                        </h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-3 row ">
                                                <div class="col-md-12">
                                                    <div class="Select has-value is-searchable Select--single">
                                                        <div class="Select-control">
                                                            <span class="Select-multi-value-wrapper"
                                                                id="react-select-4--value">
                                                                <div class="Select-value">
                                                                    <select id="payType"
                                                                        onchange="loadAveragePlannedPayment()"
                                                                        name="payType"
                                                                        class="form-control custom-select col select2">
                                                                        <option value="all">All</option>
                                                                        <option value="automatic_payments">Automatic
                                                                            Payments</option>
                                                                        <option value="manual_payment">Manual Payments
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><br><br><br><br>
                                            <div class="row" id="loadAveragePlannedPayment">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div style="width: 100%; height: 100%;">
                                    <div colors="light" class="payment_plan_planned_payments_insights card"
                                        style="height: 100%;">
                                        <div class="card-body" id="loadPlannedPayment">

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8" >
                                <div class="payment-installments-over-time-card p-3 w-100 h-100 card bg-white">
                                    <div class="row ">
                                        <div class="col-md-9">
                                            <h5 data-testid="payment-installments-over-time-title" class="card-title"><strong>Payment Installments Over
                                                    Time</strong></h5>
                                        </div>
                                        <div align="right" class="col-md-3">
                                            <div class="insights-legend d-flex flex-column mb-3 flex-wrap" style="opacity: 1;">
                                                <div
                                                    class="d-flex flex-row pr-3 align-items-center payment-installments-over-time-legend-item-0 secondary-legend">
                                                    <div class="pr-1 d-flex flex-column" data-testid="legend-Collected Payments">
                                                        <h6 class="insights-legend-data mb-0">Collected Payments</h6>
                                                    </div>
                                                    <div class="align-self-start mt-1 legend-box"
                                                        style="background-color: rgb(148, 99, 136); width: 16px; height: 16px;"></div>
                                                </div>
                                                <div
                                                    class="d-flex flex-row pr-3 align-items-center payment-installments-over-time-legend-item-1 secondary-legend">
                                                    <div class="pr-1 d-flex flex-column" data-testid="legend-Planned Payments">
                                                        <h6 class="insights-legend-data mb-0">Planned Payments</h6>
                                                    </div>
                                                    <div class="align-self-start mt-1 legend-box"
                                                        style="background-color: rgb(244, 236, 242); width: 16px; height: 16px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-0 p-0  flex-column card-body" id="PaymentInstallmentsOverTime">

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <div class="row pl-4 pb-4">
                        <div class="col-md-3 form-group mb-3">
                            <label for="picker1">Case</label>
                            <select id="court_case" name="court_case" class="form-control colformSubmit select2">
                                <option value="">All</option>
                                <?php 
                                foreach($caseList as$k=>$v){
                                    ?>
                                    <option <?php if($court_case==$v->case_id){ echo "selected=selected"; } ?> value="{{$v->case_id}}">{{$v->case_title}}</option>
                               <?php  }?>
                            </select>
                        </div>

                        <div class="col-md-3 form-group mb-3">
                            <label for="picker1">Payment Type
                            </label>
                            <select id="payment_type" name="payment_type" class="form-control colformSubmit select2">
                                <option <?php if($payment_type==""){ echo "selected=selected"; } ?> value="">All</option>
                                <option <?php if($payment_type=="on"){ echo "selected=selected"; } ?> value="on">Autopay Enabled</option>
                                <option <?php if($payment_type=="off"){ echo "selected=selected"; } ?> value="off">Autopay Disabled</option>
                            </select>
                        </div>

                        <div class="col-md-2 form-group mb-3">
                            <div
                                class="m-2 col-form-label d-flex align-items-center h-100 include-paid-invoices-filter form-check">
                                <input id="include-paid-invoices-filter" type="checkbox" name="include_paid_invoices" <?php if($include_paid_invoices!=""){ echo "checked=checked"; } ?>>
                                <label for="include-paid-invoices-filter" class="my-0 form-check-label" > &nbsp;&nbsp;Include Paid
                                    Invoices</label></div>
                        </div>
                        <div class="col-md- form-group mb-3 mt-3">
                            <button class="btn btn-info btn-rounded m-1" type="submit">Apply Filters</button>
                        </div>
                        <div class="col-md-1 form-group mb-3 mt-3 pt-1">
                            <button type="button" class="test-clear-filters text-black-50 btn btn-link">
                                <a href="{{route('payment_plans')}}">Clear Filter</a>
                            </button>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <div class="d-flex justify-content-end mb-2 d-print-none">
                        <span class="my-2 mr-3">
                            <small class="text-muted mx-1">Text Size</small>
                            <button type="button" arial-label="Decrease text size" data-testid="dec-text-size" class="btn-sm py-0 px-1 mx-1 btn btn-outline-light btn-rounded  decrease ">
                                <i class="fas fa-minus fa-xs"></i>
                            </button>
                            <button type="button" arial-label="Increase text size" data-testid="inc-text-size" class="btn-sm py-0 px-1 mx-1 btn btn-outline-light  btn-rounded  increase" >
                                <i class="fas fa-plus fa-xs"></i>
                            </button>
                        </span>
                    </div>
                    <table class="display table table-striped table-bordered" id="paymentPlanGrid" style="width:100%">
                        <thead>
                            <tr>
                                <th class="invoice-number sortable-header" style="cursor: pointer;">Id</th>
                                <th class="invoice-number sortable-header" style="cursor: pointer;">Number</th>
                                <th class="contact-name sortable-header" style="cursor: pointer;">Contact</th>
                                <th class="case-name sortable-header" style="cursor: pointer;">Case</th>
                                <th class="total-amount sortable-header" style="cursor: pointer;">Total</th>
                                <th class="paid-amount sortable-header" style="cursor: pointer;">Paid</th>
                                <th class="amount-due regular-header text-nowrap" style="cursor: pointer;">Amount Due</th>
                                <th class="percentage-complete sortable-header text-nowrap" style="cursor: pointer;">% Complete</th>
                                <th class="autopay sortable-header text-nowrap" style="cursor: pointer;">Autopay</th>
                                <th class="next-payment-due-date sortable-header " style="cursor: pointer;">Next Payment Due</th>
                                <th class="next-payment-amount sortable-header" style="cursor: pointer;">Next Payment Amount</th>
                                <th class="final-payment-date sortable-header" style="cursor: pointer;">Final Payment Date</th>
                                <th class="payment-plan-actions nosort" style="cursor: initial;"></th>
                            </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
th td{
   white-space: nowrap;
}
</style>

<!-- <div id="payInvoice" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Record Payment</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <span class="showError"></span>
                        <div id="payInvoiceArea">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->
@section('page-js-inner')
<script src="{{ asset('assets\js\custom\feedback.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#court_case").select2({
            theme: "classic"
        });
        $("#payment_type").select2({
            theme: "classic"
        });
        
        $('#payInvoice').on('hidden.bs.modal', function () {
            paymentPlanGrid.ajax.reload(null, false);
        });
        $('.dropdown-toggle').dropdown();
        var paymentPlanGrid =  $('#paymentPlanGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "ajax":{
                url :baseUrl +"/payment_plans/loadAllPlans", // json datasource
                type: "post",  // method  , by default get
                data :{ 'payment_type' :'{{$payment_type}}','court_case' :'{{$court_case}}','include_paid_invoices' :'{{$include_paid_invoices}}', },
                error: function(){  // error handling
                    $(".paymentPlanGrid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] },{'bSortable': false,'aTargets': ['nosort']}],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'total_amt'},
                { data: 'total_paid'},
                { data: 'total_due'},
                { data: 'completed'},
                { data: 'next_payment_on'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id',sorting:false},],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    $('td:eq(0)', nRow).html('<a href="{{BASE_URL}}bills/invoices/view/'+aData.invoice_decode_id+'">'+aData.invoice_id+' </a>');

                    $('td:eq(1)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/clients/'+aData.uid+'">'+aData.contact_name+'</a></div>');

                    $('td:eq(2)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/info">'+aData.case_title+'</a></div>');

                    $('td:eq(3)', nRow).html('<div class="text-left p-2" style=" white-space: nowrap;">'+aData.total_amt_display+'</div>');

                    $('td:eq(4)', nRow).html('<div class="text-left p-2" style=" white-space: nowrap;">'+aData.total_paid_display+'</div>');

                    $('td:eq(5)', nRow).html('<div class="text-left p-2" style=" white-space: nowrap;">'+aData.total_due_display+'</div>');

                    
                    $('td:eq(6)', nRow).html('<div class="text-left p-2" style=" white-space: nowrap;">'+aData.completed_display+'</div>');

                    $('td:eq(7)', nRow).html('');
                    
                    $('td:eq(8)', nRow).html('<div class="text-left p-2" style=" white-space: nowrap;">'+aData.next_payment_on_display+'</div>');
                    
                    $('td:eq(9)', nRow).html('<div class="text-left p-2" style=" white-space: nowrap;">'+aData.next_payment_amount_display+'</div>');

                    $('td:eq(10)', nRow).html('<div class="text-left p-2" style=" white-space: nowrap;">'+aData.final_date_display+'</div>');
                    
                    var dollor='<span data-toggle="tooltip" data-placement="top" title="Record Payment"><a data-toggle="modal"  data-target="#payInvoice" data-placement="bottom" href="javascript:;"  onclick="payinvoice('+aData.invoice_id+');"><i class="fas fa-dollar-sign align-middle p-2"></i></a></span>';
              

                    var edits='<span data-toggle="tooltip" data-placement="top" title="Edit Plan"><a href="{{BASE_URL}}bills/invoices/'+aData.invoice_decode_id+'/edit?token='+aData.invoice_decode_id+'"><i class="fas fa-pen align-middle p-2"></i></a></span>';

                    $('td:eq(11)', nRow).html('<div class="text-center" style="white-space: nowrap;float:right;">'+edits+' '+dollor+'</div>');


                },
                "initComplete": function(settings, json) {
                        $('td').css('font-size',parseInt(localStorage.getItem("paymentPlanGrid"))+'px');  
                }
        });
        if(localStorage.getItem("paymentPlanGrid")==""){
            localStorage.setItem("paymentPlanGrid","13");
        }

        var originalSize = $('td').css('font-size');        
        var currentSize=localStorage.getItem("paymentPlanGrid");
        $('td').css('font-size', currentSize);    
        $(".increase").click(function(){         
            modifyFontSize('increase');  
        });     
        $(".decrease").click(function(){   
            modifyFontSize('decrease');  
        });  
    });

    function loadPlannedPayment() {
        $('.showError').html('');
        $("#loadPlannedPayment").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/payment_plans/loadPlannedPayment",
            data: {},
            success: function (res) {
                $("#loadPlannedPayment").html(res);
                $("#preloader").hide();
                return true;
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
    // function payinvoice(id) {
    //     $('.showError').html('');
    //     beforeLoader();
    //     $("#preloader").show();
    //     $("#payInvoiceArea").html('');
    //     $("#payInvoiceArea").html('<img src="{{LOADER}}""> Loading...');
    //     $.ajax({
    //         type: "POST",
    //         url: baseUrl + "/bills/invoices/payInvoicePopup", 
    //         data: {'id':id},
    //         success: function (res) {
    //             if(typeof(res.errors) != "undefined" && res.errors !== null) {
    //                 $('.showError').html('');
    //                 var errotHtml =
    //                     '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
    //                 $('.showError').append(errotHtml);
    //                 $('.showError').show();
    //                 afterLoader();
    //                 $("#preloader").hide();
    //                 $("#payInvoiceArea").html('');
    //                 return false;
    //             } else {
    //                 afterLoader()
    //                 $("#payInvoiceArea").html(res);
    //                 $("#preloader").hide();
    //                 return true;
    //             }
    //         },error: function (xhr, status, error) {
    //             $("#preloader").hide();
    //             $("#payInvoiceArea").html('');
    //             $('.showError').html('');
    //             var errotHtml =
    //                 '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
    //             $('.showError').append(errotHtml);
    //             $('.showError').show();
    //             afterLoader();
    //         }
    //     })
    // }
    function loadAveragePlannedPayment() {
        $('.showError').html('');
        $("#loadAveragePlannedPayment").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/payment_plans/loadAveragePlannedPayment",
            data: {
                "payType": $("#payType").val()
            },
            success: function (res) {
                $("#loadAveragePlannedPayment").html(res);
                $("#preloader").hide();
                return true;
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

    function PaymentInstallmentsOverTime() {
        $('.showError').html('');
        $("#PaymentInstallmentsOverTime").html('<img src="{{LOADER}}" > Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/payment_plans/PaymentInstallmentsOverTime",
            data: {
                "payType": $("#payType").val()
            },
            success: function (res) {
                $("#PaymentInstallmentsOverTime").html(res);
                $("#preloader").hide();
                return true;
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

 function modifyFontSize(flag) {  
        var min = 13;
        var max = 19;
        var divElement = $('td');  
        var currentFontSize = parseInt(divElement.css('font-size'));  

        if (flag == 'increase')  
            currentFontSize += 3;  
        else if (flag == 'decrease')  
            currentFontSize -= 3;  
        else  
            currentFontSize = 13;  
            if(currentFontSize>=min && currentFontSize<=max){
            divElement.css('font-size', currentFontSize); 
            localStorage.setItem("paymentPlanGrid",currentFontSize);
        }
    }  
    loadAveragePlannedPayment();
    loadPlannedPayment();
    PaymentInstallmentsOverTime();

</script>
@stop
@endsection
