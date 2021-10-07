<div class="d-flex flex-column">

    <div class="account-balance pl-2">
        <div class="d-flex flex-column flex-wrap p-2"><span>Account Balance</span>
            <div class="d-flex">
                <h3 class="m-0">${{number_format(($AccountActivityCredited - $AccountActivityDebited),2)}}</h3><a
                    class="ml-3 align-self-end font-weight-light"
                    href="{{BASE_URL}}bills/trust_account_activity?type=trust_account">View Account Activity</a>
            </div>
        </div>
    </div>
    <div class="p-2"><span class="month-label font-weight-light pl-2">{{date("M Y")}}</span>
        <div class="recharts-responsive-container" style="width: 100%; height: 40px;">
            <div class="progress mb-3" style="height:35px;cursor: pointer;border-radius: 0;">
                <?php
            $finalAmount=$AccountActivityCredited+ $AccountActivityDebited;
            if($finalAmount>0){
                $creaditAmt=number_format(($AccountActivityCredited/$finalAmount*100),2);
                $debitAmt=number_format(($AccountActivityDebited/$finalAmount*100),2);
               
            }else{
                $creaditAmt=$debitAmt=0;
                $finalAmount=$AccountActivityCredited+ $AccountActivityDebited=0;
              }
            ?>
                <div class="progress-bar" style="background-color:rgb(64, 188, 83);width: {{$creaditAmt}}%"
                    role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip"
                    {{-- data-placement="top" title="" data-html="true" data-original-title="Incoming {{$creaditAmt}}%  <br> Trust Account :  ${{number_format(($AccountActivityCredited+$AccountActivityDebited),2)}}"> --}}
                    data-placement="top" title="" data-html="true" data-original-title="Incoming {{$creaditAmt}}%  <br> Trust Account :  ${{number_format(($AccountActivityCredited),2)}}">
                </div>
                <div class="progress-bar" style="background-color: rgb(0, 118, 50);width: {{$debitAmt}}%"
                    role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip"
                    data-placement="top" title="" data-html="true" data-original-title="Outgoing {{$debitAmt}}%  <br> Trust Account :  ${{number_format(($AccountActivityDebited),2)}}"></div>
            </div>
        </div>
    </div>
    <div class="insights-legend d-flex flex-row pl-1 pr-1 flex-wrap" style="opacity: 1;">
        <div class="d-flex flex-row justify-content-between w-100">
            <div class="label d-flex flex-column null">
                <h5 class="currency incoming-trust-balance font-weight-bold m-0">
                    {{-- ${{number_format(($AccountActivityCredited+$AccountActivityDebited),2)}}</h5><a --}}
                    ${{number_format(($AccountActivityCredited),2)}}</h5><a
                    href="{{BASE_URL}}bills/trust_account_activity?type=trust_account" class="font-weight-light">Incoming</a>
            </div>
            <div class="label d-flex flex-column text-right">
                <h5 class="currency outgoing-trust-balance font-weight-bold m-0">
                    ${{number_format($AccountActivityDebited,2)}}</h5>
                    <a href="{{BASE_URL}}bills/trust_account_activity?type=trust_account" class="font-weight-light">Outgoing</a>
            </div>
        </div>
    </div>
</div>

<script>
    $('[data-toggle="tooltip"]').tooltip();
   
</script>
