
<div class="d-flex flex-column">

    <div class="p-2">

        <div class=" w-100">

            <div class="progress mb-3" style="height:35px;cursor: pointer;border-radius: 0;">
                <?php
           $finalAmount=$InvoicesUnsentAmount+ $InvoicesDraftAmount+ $InvoicesSentAmount + $InvoicesPaidPartialAmount+ $InvoicesOverdueAmount+ $InvoicesPaidAmount;
            if($finalAmount>0){
                $InvoicesUnsentAmountShow=number_format(($InvoicesUnsentAmount/$finalAmount*100),2);
                $InvoicesDraftAmountShow=number_format(($InvoicesDraftAmount/$finalAmount*100),2);
                $InvoicesSentAmountShow=number_format(($InvoicesSentAmount/$finalAmount*100),2);
                $InvoicesPaidPartialAmountShow=number_format(($InvoicesPaidPartialAmount/$finalAmount*100),2);
                $InvoicesOverdueAmountShow=number_format(($InvoicesOverdueAmount/$finalAmount*100),2);
                $InvoicesPaidAmountShow=number_format(($InvoicesPaidAmount/$finalAmount*100),2);
            }else{
                $InvoicesUnsentAmountShow=$InvoicesDraftAmountShow=$InvoicesSentAmountShow=$InvoicesPaidPartialAmountShow=$InvoicesOverdueAmountShow=$InvoicesPaidAmountShow=0;
            }
            ?>
                <div class="progress-bar"
                    style="background-color: rgb(155, 155, 155);width: {{$InvoicesUnsentAmountShow}}%"
                    role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip"
                    data-placement="top" title="" data-original-title="Unsent {{$InvoicesUnsentAmountShow}}%">
                </div>
                <div class="progress-bar"
                    style="background-color: rgb(213, 213, 213);width: {{$InvoicesDraftAmountShow}}%" role="progressbar"
                    aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-placement="top"
                    title="" data-original-title="Draft {{$InvoicesDraftAmountShow}}%"></div>
                <div class="progress-bar"
                    style="background-color: rgb(51, 101, 138);width: {{$InvoicesSentAmountShow}}%" role="progressbar"
                    aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-placement="top"
                    title="" data-original-title="Sent {{$InvoicesSentAmountShow}}%"></div>
                <div class="progress-bar"
                    style="background-color: rgb(254, 204, 0);width: {{$InvoicesPaidPartialAmountShow}}%"
                    role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip"
                    data-placement="top" title=""
                    data-original-title="Paid Partial {{$InvoicesPaidPartialAmountShow}}%">
                </div>
                <div class="progress-bar"
                    style="background-color: rgb(208, 2, 27);width: {{$InvoicesOverdueAmountShow}}%" role="progressbar"
                    aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-placement="top"
                    title="" data-original-title="Overdue {{$InvoicesOverdueAmountShow}}%">
                </div>
                <div class="progress-bar" style="background-color: rgb(25, 191, 51);width: {{$InvoicesPaidAmountShow}}%"
                    role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip"
                    data-placement="top" title="" data-original-title="Paid {{$InvoicesPaidAmountShow}}%"></div>
            </div>
            <div class="insights-legend d-flex flex-row pl-1 pr-1 flex-wrap" style="opacity: 1;">
                <div class="d-flex row justify-content-between w-100">
                    <div class="col-4 label d-flex flex-column invoice-overview-legend-unsent">
                        <h5 class="currency font-weight-bold m-0">
                            <div style="white-space: nowrap;">
                                <div class="align-self-start mt-1"
                                    style="background-color: rgb(155, 155, 155); display: inline-block; height: 14px; margin-right: 5px; width: 14px;">
                                </div>${{number_format($InvoicesUnsentAmount,2)}}
                            </div>
                        </h5><a href="{{BASE_URL}}bills/invoices?type=unsent"
                            class="bills-unsent-link font-weight-light">Unsent</a>
                    </div>
                    <div class="col-4 label d-flex flex-column invoice-overview-legend-draft">
                        <h5 class="currency font-weight-bold m-0">
                            <div style="white-space: nowrap;">
                                <div class="align-self-start mt-1"
                                    style="background-color: rgb(213, 213, 213); display: inline-block; height: 14px; margin-right: 5px; width: 14px;">
                                </div>${{number_format($InvoicesDraftAmount,2)}}
                            </div>
                        </h5><a href="{{BASE_URL}}bills/invoices?type=draft"
                            class="bills-draft-link font-weight-light">Draft</a>
                    </div>
                    <div class="col-4 label d-flex flex-column invoice-overview-legend-sent">
                        <h5 class="currency font-weight-bold m-0">
                            <div style="white-space: nowrap;">
                                <div class="align-self-start mt-1"
                                    style="background-color: rgb(51, 101, 138); display: inline-block; height: 14px; margin-right: 5px; width: 14px;">
                                </div>${{number_format($InvoicesSentAmount,2)}}
                            </div>
                        </h5><a href="{{BASE_URL}}bills/invoices?type=sent"
                            class="bills-sent-link font-weight-light">Sent</a>
                    </div>
                    <div class="col-4 label d-flex flex-column invoice-overview-legend-partial">
                        <h5 class="currency font-weight-bold m-0">
                            <div style="white-space: nowrap;">
                                <div class="align-self-start mt-1"
                                    style="background-color: rgb(254, 204, 0); display: inline-block; height: 14px; margin-right: 5px; width: 14px;">
                                </div>
                                ${{number_format($InvoicesPaidPartialAmount,2)}}
                            </div>
                        </h5><a href="{{BASE_URL}}bills/invoices?type=partial"
                            class="bills-partial-link font-weight-light">Partial</a>
                    </div>
                    <div class="col-4 label d-flex flex-column invoice-overview-legend-overdue">
                        <h5 class="currency font-weight-bold m-0">
                            <div style="white-space: nowrap;">
                                <div class="align-self-start mt-1"
                                    style="background-color: rgb(208, 2, 27); display: inline-block; height: 14px; margin-right: 5px; width: 14px;">
                                </div>${{number_format($InvoicesOverdueAmount,2)}}
                            </div>
                        </h5><a href="{{BASE_URL}}bills/invoices?type=overdue"
                            class="bills-overdue-link font-weight-light">Overdue</a>
                    </div>
                    <div class="col-4 label d-flex flex-column invoice-overview-legend-paid">
                        <h5 class="currency font-weight-bold m-0">
                            <div style="white-space: nowrap;">
                                <div class="align-self-start mt-1"
                                    style="background-color: rgb(25, 191, 51); display: inline-block; height: 14px; margin-right: 5px; width: 14px;">
                                </div>${{number_format($InvoicesPaidAmount,2)}}
                            </div>
                        </h5><a href="{{BASE_URL}}bills/invoices?type=paid"
                            class="bills-paid-link font-weight-light">Paid</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('[data-toggle="tooltip"]').tooltip();
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
           
            var d=start.format('YYYY/MM/DD') +'-'+end.format('YYYY/MM/DD');
                overViewInvoice(d);

        });
        
    $('#daterange').trigger("apply.daterangepicker");
   
</script>
