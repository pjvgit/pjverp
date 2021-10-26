
@section('title', 'Financial Insights- Billing')
@section('main-content')
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
<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
     
        </div>  
    </div>
</div>
  
@section('page-js-inner')
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


        var PracticeAreaCollection = <?=json_encode($finalPracticeAreaListPercent) ?> ;
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
                formatter: function (seriesName, opts) {
                    return ["$" + opts.w.globals.series[opts.seriesIndex] + '<br>' + seriesName]
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
            series: [33, 33, 33],
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


        var BillingTypeCollection = <?=json_encode($displayChartBillingType) ?> ;
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
                formatter: function (seriesName, opts) {
                    return ["$" + opts.w.globals.series[opts.seriesIndex] + '<br>' + seriesName]
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
            series: [33, 33, 33],
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

</script>
@stop
@endsection
