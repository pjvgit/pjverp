
<div id="MixChart"></div>

<div class="insights-legend d-flex flex-row mt-2 pl-3 flex-wrap" style="opacity: 1;">
    <div class="d-flex flex-row pr-3 align-items-center payment-installments-over-time-legend-item-0">
        <div class="align-self-start mt-1 legend-box"
            style="background-color: rgb(25, 191, 51); width: 16px; height: 16px;"></div>
        <div class="pl-1 d-flex flex-column" data-testid="legend-Automatic Payments">
            <h4 class="insights-legend-data font-weight-bold mb-0">Automatic Payments</h4>
        </div>
    </div>
    <div class="d-flex flex-row pr-3 align-items-center payment-installments-over-time-legend-item-1">
        <div class="align-self-start mt-1 legend-box"
            style="background-color: rgb(47, 71, 89); width: 16px; height: 16px;"></div>
        <div class="pl-1 d-flex flex-column" data-testid="legend-Manual Payments">
            <h4 class="insights-legend-data font-weight-bold mb-0">Manual Payments</h4>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var PracticeAreaCollection = <?=json_encode($collectedPaymentDataArray)?>;
        var plannedPaymentDataArray = <?= json_encode($plannedPaymentDataArray) ?> ;
        var manualPaymentDataArray = <?= json_encode($manualPaymentDataArray) ?> ;
        var automaticPaymentDataArray = <?= json_encode($automaticPaymentDataArray) ?> ;


        var options = {
           
            colors: ['rgb(148, 99, 136)', 'rgb(244, 236, 242)', 'rgb(25, 191, 51)', 'rgb(47, 71, 89)'],
            tooltip: {
                custom: function ({
                    series,
                    seriesIndex,
                    dataPointIndex,
                    w
                }) {
                    var data = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
                    return '<div style="width:150px;padding-top:5px;padding-left:5px;padding-right:5px;padding-botton:10px;"><br><b>' +
                        data.month + '</b><br><br>' + data.title1 +
                        '<br><span style="color:green;">' + data.title2 + '</span><br>' + data
                        .title3 + '<br><br></div>';
                }
            },
            series: [{
                name: 'Collected Payments',
                type: 'column',
                data: PracticeAreaCollection
            }, {
                name: 'Planned Payments',
                type: 'column',
                data: plannedPaymentDataArray
            }, {
                name: 'Manual Payments',
                type: 'line',
                data: manualPaymentDataArray
            }, {
                name: 'Automatic Payments',
                type: 'line',
                data: automaticPaymentDataArray
            }],
            chart: {
                height: 250,
                type: 'line',
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false,
                }
            },
            stroke: {
                width: 4,
            },
            markers: {
                size: [4, 7]
            },
            title: {
                text: ''
            },
            dataLabels: {
                enabled: false,
                enabledOnSeries: [1],
            },
            legend: {
                show: false
            },
            xaxis: {
                type: 'datetime',
                tooltip: {
                   enabled: false
                }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "$"+value;
                    }
                },
            },
        };
        var chart = new ApexCharts(document.querySelector("#MixChart"), options);
        chart.render();
    });

</script>
