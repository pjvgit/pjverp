<div class="row ">
    <div class="col-md-12">
        <div class="d-flex">
            <h5>
                <strong>Planned Payments</strong>
                <small class="text-muted ml-2 pt-1"><br>(Next 30 Days)</small>
            </h5>
        </div>
    </div>
</div>
<br>
<br>
<br>
<br>
<div class="row ">
    <div class="text-center col-md-12">
        <h2 data-testid="total-amount"><strong>${{number_format($totalAmount,2)}}</strong></h2>
    </div>
</div>

<?php if($isEmpty==0){
?><div class="row ">
    <div class="col-md-12">
        <div style="opacity: 0.15;">
            <div class="recharts-responsive-container" style="width: 100%; height: 40px;">
                <div class="recharts-wrapper" style="position: relative; cursor: default; width: 175px; height: 40px;">
                    <svg class="recharts-surface" width="175" height="40" viewBox="0 0 175 40" version="1.1">
                        <defs>
                            <clipPath id="recharts1-clip">
                                <rect x="5" y="5" height="30" width="165"></rect>
                            </clipPath>
                        </defs>
                        <g class="recharts-layer recharts-bar insights-stacked-bar-0 bar-0">
                            <g class="recharts-layer recharts-bar-rectangles">
                                <g class="recharts-layer recharts-bar-rectangle">
                                    <path fill="#661051" width="82.5" height="27" x="5" y="6" radius="0"
                                        class="recharts-rectangle" d="M 5,6 h 82.5 v 27 h -82.5 Z"
                                        style="cursor: default;"></path>
                                </g>
                            </g>
                        </g>
                        <g class="recharts-layer recharts-bar insights-stacked-bar-1 bar-1">
                            <g class="recharts-layer recharts-bar-rectangles">
                                <g class="recharts-layer recharts-bar-rectangle">
                                    <path fill="#0070BB" width="82.5" height="27" x="87.5" y="6" radius="0"
                                        class="recharts-rectangle" d="M 87.5,6 h 82.5 v 27 h -82.5 Z"
                                        style="cursor: default;"></path>
                                </g>
                            </g>
                        </g>
                    </svg></div>
                <div style="position: absolute; width: 0px; height: 0px; visibility: hidden; display: none;"></div>
            </div>
        </div>
        <div class="insights-legend d-flex flex-column pl-1 pr-1 flex-wrap" style="opacity: 0.15;">
            <div class="label d-flex flex-column invoice-overview-legend-autopay">
                <h5 class="currency font-weight-bold m-0">
                    <div style="white-space: nowrap;">
                        <div class="align-self-start mt-1"
                            style="background-color: rgb(102, 16, 81); display: inline-block; height: 14px; margin-right: 5px; width: 14px;">
                        </div>$0.00
                    </div>
                </h5>
                <div class="font-weight-light pb-2">Autopay</div>
            </div>
            <div class="label d-flex flex-column invoice-overview-legend-manual">
                <h5 class="currency font-weight-bold m-0">
                    <div style="white-space: nowrap;">
                        <div class="align-self-start mt-1"
                            style="background-color: rgb(0, 112, 187); display: inline-block; height: 14px; margin-right: 5px; width: 14px;">
                        </div>$0.00
                    </div>
                </h5>
                <div class="font-weight-light pb-2">Manual</div>
            </div>
        </div>
    </div>
</div>

<?php
}else{
?>
<div class="row ">
    <div class="col-md-12">

        <div class="recharts-responsive-container" style="width: 100%; ">
            <div class="progress mb-3" style="height:35px;cursor: pointer;height: 30px;border-radius: 0; ">

                <div class="progress-bar" style="background-color:rgb(102, 16, 81);width: {{$autoPayPercentage}}%"
                    role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip"
                    data-placement="top" title="" data-html="true"
                    data-original-title="Autopay {{$autoPayPercentage}}%  <br>">
                </div>
                <div class="progress-bar" style="background-color: rgb(0, 112, 187);width: {{$manualPayPercentage}}%"
                    role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip"
                    data-placement="top" title="" data-html="true"
                    data-original-title="Manual {{$manualPayPercentage}}% "></div>
            </div>
        </div>
        <div class="insights-legend d-flex flex-column pl-1 pr-1 flex-wrap" style="opacity: 1;">
            <div class="label d-flex flex-column invoice-overview-legend-autopay">
                <h5 class="currency font-weight-bold m-0">
                    <div style="white-space: nowrap;">
                        <div class="align-self-start mt-1"
                            style="background-color: rgb(102, 16, 81); display: inline-block; height: 14px; margin-right: 5px; width: 14px;">
                        </div>${{number_format($AutoInvoiceInstallment,2)}}
                    </div>
                </h5>
                <div class="font-weight-light pb-2">Autopay</div>
            </div>
            <div class="label d-flex flex-column invoice-overview-legend-manual">
                <h5 class="currency font-weight-bold m-0">
                    <div style="white-space: nowrap;">
                        <div class="align-self-start mt-1"
                            style="background-color: rgb(0, 112, 187); display: inline-block; height: 14px; margin-right: 5px; width: 14px;">
                        </div>${{number_format($ManualInvoiceInstallment,2)}}
                    </div>
                </h5>
                <div class="font-weight-light pb-2">Manual</div>
            </div>
        </div>

    </div>
</div><?php
}?>

<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

</script>
