@extends('layouts.master')
@section('title', 'Financial Insights- Billing')
@section('main-content')
@include('billing.submenu')
<?php
$range=$practice_area=$lead_attorney=$office=""; 
if(isset($_GET['date_range'])){
    $range= $_GET['date_range'];
}
if(isset($_GET['bank_account'])){
    $account= $_GET['bank_account'];
}
if(isset($_GET['practice_area'])){
    $practice_area= $_GET['practice_area'];
}
if(isset($_GET['lead_attorney'])){
    $lead_attorney= $_GET['lead_attorney'];
}
if(isset($_GET['office'])){
    $office= $_GET['office'];
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
                        <h3> Financial Insights</h3>
                        <input type="hidden" name="type" value="">

                        <div class="ml-auto d-flex align-items-center ">
                            <button  onclick="printEntry();return false;"  class="btn btn-outline-secondary mr-1 btn-rounded">
                                <i class="fas fa-print"></i><span
                                    class="sr-only">Print This Page</span></button>
                            <a data-toggle="modal" data-target="#loadAddFeedBack" data-placement="bottom" href="javascript::void(0);">
                                <button onclick="setFeedBackForm('single','Financial Insights');" type="button" class="btn btn-outline-secondary m-1">Tell us what you think</button>
                            </a>
                        </div>
                    </div>
                    <div class="row pl-4 pb-4">
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Date Range</label>
                            <input type="text" class="form-control" id="daterange" name="date_range" value="{{$range}}"
                                placeholder="" />
                        </div>

                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Lead Attorney</label>
                            <select id="lead_attorney" name="lead_attorney" class="form-control colformSubmit">
                                <option></option>
                                
                                <?php
                                foreach($leadAttorneysList as $key=>$val){?>
                                <option <?php if($lead_attorney==$val->id){ echo "selected=selected";}?>
                                    value="{{$val->id}}">{{$val->first_name}} {{$val->last_name}} ({{$val->user_title}})</option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Practice Area</label>
                            <select id="practice_area" name="practice_area" class="form-control colformSubmit">
                                <option></option>
                                <?php
                                foreach($practiceAreaList as $key=>$val){?>
                                <option <?php if($practice_area==$val->id){ echo "selected=selected";}?>
                                    value="{{$val->id}}">{{$val->title}}</option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Office</label>
                            <select id="office" name="office" class="form-control colformSubmit">
                                <option></option>
                                <option <?php if($office=="primary"){ echo "selected=selected";}?>
                                    value="primary">Primary</option>
                               
                            </select>
                        </div>
                        <div class="col-md- form-group mb-3 mt-3">
                            <button class="btn btn-info btn-rounded m-1" type="submit">Apply Filters</button>
                        </div>
                        <div class="col-md-1 form-group mb-3 mt-3 pt-1">
                            <button type="button" class="test-clear-filters text-black-50 btn btn-link">
                                <a href="{{route('insights/financials')}}">Clear Filters</a>
                            </button>
                        </div>
                    </div>
                </form>
                <div class="table-responsive" id="printHtml">
                    <div class="financial-insights-container container-fluid">
                        <div class="row ">
                            <div class="p-2 col" style="min-width: 400px;">
                                <div class="collected-versus-billed-insights insights-card card"
                                    style="height: auto; width: 100%;">
                                    <div class="card-body">
                                        <h5 class="d-flex flex-row justify-content-between card-title"><strong>
                                                Case Revenue Collected vs. Billed</strong>
                                        </h5>
                                        <div class="mt-4 p-0 card-body">
                                            <div class="recharts-responsive-container"
                                                style="width: 100%; height: 150px;">
                                                Total Collected
                                                <div class="row">
                                                    <div class="col-md-11">
                                                        <div class="progress mb-3" style="height:35px;">
                                                            <?php 
                                                            if($totalCollectedInvoicedAmount>0){
                                                                $PercetangeCollected=number_format(($totalCollectedInvoicedAmount/$totalInvoicedAmount)*100,2);
                                                            }else{
                                                                $PercetangeCollected=0;
                                                            }
                                                            ?>
                                                            <div class="progress-bar progress-bar-animated"
                                                                role="progressbar" aria-valuemax="100"
                                                                data-toggle="tooltip" data-placement="top" title=""
                                                                data-original-title="${{number_format($totalCollectedInvoicedAmount,2)}}"
                                                                style="background-color:rgb(25, 191, 51);width: {{$PercetangeCollected}}%;font-size: 16px;"
                                                                aria-valuenow="25" aria-valuemin="0"
                                                                aria-valuemax="100">

                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="col-md-1 pt-2">
                                                        ${{number_format($totalCollectedInvoicedAmount,2)}}
                                                    </div>
                                                </div>
                                                Total Billed
                                                <div class="row">
                                                    <div class="col-md-11">
                                                        <div class="progress mb-3" style="height:35px;">
                                                            <div class="progress-bar progress-bar-animated"
                                                                role="progressbar" data-toggle="tooltip"
                                                                data-placement="top" title=""
                                                                data-original-title="${{number_format($totalInvoicedAmount,2)}}"
                                                                style="background-color:rgb(47, 71, 89);width: 100%;font-size: 16px;"
                                                                aria-valuenow="25" aria-valuemin="0"
                                                                aria-valuemax="100">

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1 pt-2" style="white-space: nowrap;">
                                                        ${{number_format($totalInvoicedAmount,2)}}
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="financial-insights-container container-fluid">
                    <div class="row ">
                        <div class="p-2  col-4" style="min-width: 400px;">
                            <div class="collected-versus-billed-insights insights-card card"
                                style="width: 100%; min-width: 400px;min-hight:400px;">
                                <div class="card-body">
                                    <h5 class="d-flex flex-row justify-content-between card-title"><strong>Collected By
                                            Practice Area</strong></h5>
                                    <div class="p-0 card-body">
                                        <div class="d-flex flex-row justify-content-center">
                                            <?php 
                                            if($totalSumPA>0){?>
                                                    <div id="insightByPracticeArea"></div>
                                            <?php }else{ ?>
                                                <div id="insightByPracticeAreaEmpty" style="opacity: 0.15;"></div>
                                            <?php } ?>
                                            <span id="totals">${{number_format($totalSumPA,2)}} collected</span>
                                        </div>
                                      
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-2 col-4" style="min-width: 400px;">
                            <div class="collected-by-billing-type-insights insights-card ml-2 card"
                                style="width: 100%; min-width: 400px;">
                                <div class="card-body">
                                    <h5 class="d-flex flex-row justify-content-between card-title"><strong>Collected By
                                            Billing Type</strong></h5>
                                    <div class="p-0 card-body">
                                        <div class="d-flex flex-row justify-content-center">
                                            <?php 
                                            if($totalSumPA>0){?>
                                                    <div id="insightByBillingType"></div>
                                            <?php }else{ ?>
                                                <div id="insightByBillingTypeEmpty" style="opacity: 0.15;"></div>
                                            <?php } ?>
                                            <span id="totals">${{number_format($totalSumPA,2)}} collected</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row ">
                        <div class="p-2 col" style="min-width: 400px;">
                            <div class="employee-hours-insights insights-card card" style="width: 100%;">
                                <div class="card-body">
                                    <h5 class="d-flex flex-row justify-content-between card-title"><strong>Hours Recorded by Employee</strong></h5>
                                    <div class="mt-4 p-0 card-body">
                                        <div class="recharts-responsive-container" style="width: 100%; height: auto;">
                                           <?php
                                           foreach($timeEntryList as $key=>$val){ 

                                                $nonBillEntry=$timeEntryList[$key]['nonBillableHours'];
                                                
                                                $BillEntry=$timeEntryList[$key]['billableHours'];

                                                $Gtotal= $BillEntry + $nonBillEntry;

                                                if($nonBillEntry>0){
                                                     $NB=$nonBillEntry/$Gtotal*100;
                                                }else{
                                                    $NB=0;
                                                }
                                                if($BillEntry>0){
                                                    $B=$BillEntry/$Gtotal*100;
                                                }else{
                                                    $B=0;
                                                }
                                           ?>
                                           <div class="recharts-wrapper"
                                                style="position: relative; cursor: default; height: auto;">
                                                {{$timeEntryList[$key]['user_name']}}
                                  
                                                <div class="row">
                                                    <div class="col-md-11">
                                                        <div class="progress mb-3"  style="height:35px;" >
                                                            <div class="progress-bar" role="progressbar" style="background-color:rgb(51, 101, 138);width:{{$B}}%"  data-toggle="tooltip" data-html="true" data-placement="top" title=""
                                                            data-original-title="{{$timeEntryList[$key]['user_name']}}<br> Billable : {{$timeEntryList[$key]['billableHours']}}">
                                                            </div>
                                                            
                                                            <div class="progress-bar" role="progressbar" style="background-color:rgb(105, 182, 214);width:{{$NB}}%" data-html="true" data-toggle="tooltip" data-placement="top" title=""
                                                            data-original-title=" {{$timeEntryList[$key]['user_name']}}<br> Non-Billable : {{$timeEntryList[$key]['nonBillableHours']}}" >
                                                        </div>
                                                        
                                                        </div>

                                                    </div>
                                                    <div class="col-md-1 pt-1">
                                                        {{number_format($Gtotal,1)}}
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } ?>
                                            <div
                                                style="position: absolute; width: 0px; height: 0px; visibility: hidden; display: none;">
                                            </div>
                                        </div>
                                        <div class="insights-legend d-flex flex-row undefined flex-wrap"
                                            style="opacity: 1;">
                                            <div class="d-flex flex-row pr-3 align-items-center legend-item-0">
                                                <div class="undefined legend-box"
                                                    style="background-color: rgb(51, 101, 138); width: 16px; height: 16px;">
                                                </div><span
                                                    class="pl-1 insights-legend-data font-weight-light mb-0">Billable</span>
                                            </div>
                                            <div class="d-flex flex-row pr-3 align-items-center legend-item-1">
                                                <div class="undefined legend-box"
                                                    style="background-color: rgb(105, 182, 214); width: 16px; height: 16px;">
                                                </div><span
                                                    class="pl-1 insights-legend-data font-weight-light mb-0">Non-Billable</span>
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
        <div class="court-cases-insights-feedback-box mt-3 "
            style="border-width: 2px; border-radius: 5px; border-style: dashed; border-color: gainsboro; padding: 20px; text-align: center;">
            <div class="footer-info-text mb-2" style="font-weight: bold;">What other charts, graphs or insights do
                you want to see here?</div><button type="button" class="feedback-button btn btn-secondary">Submit
                feedback</button>
        </div>
    </div>
</div>

<!-- <div id="table-responsive1">
    <table>
        <tr>
            <td><strong> Collected By
                Practice Area</strong></td>
        </tr>
        <tr>
            <td><strong>  <?php 
                if($totalSumPA>0){?>
                        <div id="insightByPracticeArea"></div>
                <?php }else{ ?>
                    <div id="insightByPracticeAreaEmpty" style="opacity: 0.15;"></div>
                <?php } ?>
                <span id="totals">${{number_format($totalSumPA,2)}} collected</span></td>
        </tr>
        <tr>
            <td><strong> Case Revenue Collected vs. Billed</strong></td>
        </tr>
    </table>
</div>
-->
</div>
<iframe id="ifmcontentstoprint" style="height: 0px; width: 0px; position: absolute"></iframe>
<style>
    .insights-card {
        border-top: 3px solid #0070c0;
    }
</style>
@section('page-js-inner')
<script src="{{ asset('assets\js\custom\feedback.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
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
            "opens": "right",
            "minDate": "01/01/2020"
        }, function (start, end, label) {
            $("#daterange").val(label);
        });
        $('#daterange').trigger("apply.daterangepicker");

        // $('#daterange').data('daterangepicker').setStartDate('03/01/2014');
        $("#lead_attorney").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true
        });
        $("#practice_area").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true
        });
        $("#office").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true
        });
        $('.formSubmit').change(function () {
            this.form.submit();
        });
        $('.dropdown-toggle').dropdown();

        $('#actionbutton').attr('disabled', 'disabled');
        var PracticeAreaCollection = <?= json_encode($finalPracticeAreaListPercent) ?>;
        var areaLabel = [];
        var areaData = [];

        jQuery.each(PracticeAreaCollection, function (i, val) {
            areaLabel.push(val.title);
            areaData.push(parseFloat(val.totals));
        });
        var options = {
            chart: {
                type: 'donut',
                width: '100%'
            },
            labels: areaLabel,
            series: areaData,
            legend: {
                position: 'bottom',
                formatter: function(seriesName, opts) {
                    return ["$"+opts.w.globals.series[opts.seriesIndex]+'<br>'+seriesName]
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 310
                    },
                    legend: {
                        position: 'center'
                    }
                }
            }]
        };
        var chart = new ApexCharts(document.querySelector("#insightByPracticeArea"), options);
        chart.render(); 

        //Empty Chart
        var options = {
            chart: {
                type: 'donut',
                width: '100%'
            },
            toolbar: {
                show: false,
            },
            tooltip: {
                enabled: false
            },
            series: [33,33,33],
            legend: {
                position: 'bottom',
                show: false,
            },
            dataLabels: {
                enabled: false
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 310
                    },
                    legend: {
                        position: 'center'
                    }
                }
            }]
        };
        var chart = new ApexCharts(document.querySelector("#insightByPracticeAreaEmpty"), options);
        chart.render(); 
            

        var BillingTypeCollection = <?= json_encode($displayChartBillingType) ?>;
        var billLabel = [];
        var billData = [];

        jQuery.each(BillingTypeCollection, function (i, val) {
            billLabel.push(i);
            billData.push(parseFloat(val));
        });
        var options = {
            chart: {
                type: 'donut',
                width: '100%'
            },
            labels: billLabel,
            series: billData,
            legend: {
                position: 'bottom',
                formatter: function(seriesName, opts) {
                    return ["$"+opts.w.globals.series[opts.seriesIndex]+'<br>'+seriesName]
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 310
                    },
                    legend: {
                        position: 'center'
                    }
                }
            }]
        };
        var chart = new ApexCharts(document.querySelector("#insightByBillingType"), options);
        chart.render();

          //Empty Chart
          var options = {
            chart: {
                type: 'donut',
                width: '100%'
            },
            toolbar: {
                show: false,
            },
            tooltip: {
                enabled: false
            },
            series: [33,33,33],
            legend: {
                position: 'bottom',
                show: false,
            },
            dataLabels: {
                enabled: false
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 310
                    },
                    legend: {
                        position: 'center'
                    }
                }
            }]
        };
        var chart = new ApexCharts(document.querySelector("#insightByBillingTypeEmpty"), options);
        chart.render(); 
    });
    function printEntryOld()
    {
        // var content = document.getElementById("table-responsive");
        // var pri = document.getElementById("ifmcontentstoprint").contentWindow;
        // pri.document.open();
        // pri.document.write(content.innerHTML);
        // pri.document.close();
        // pri.focus();
        // pri.print();


        // var printContents = document.getElementById("table-responsive").innerHTML;
        // var originalContents = document.body.innerHTML;
        // document.body.innerHTML = printContents;
        // window.print();
        // document.body.innerHTML = originalContents;

        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/insights/financials/printInsightActivity",
                data :{ 'range':"{{$range}}",'practice_area':"{{$practice_area}}",'lead_attorney':"{{$lead_attorney}}",'office':"{{$office}}" },
                success: function (res) {
                    setTimeout(function(){  
                        w=window.open();
                        w.document.write(res);
                        w.print();
                        // w.close();
                        $("#preloader").hide();
                        return false;
                    }, 4000);
                }
            })
        });
    }
    function printEntry()
    {
        var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        $(".main-content-wrap").remove();
        window.print(canvas);
        // w.close();
        window.location.reload();
        return false;  
    }
</script>
@stop
@endsection