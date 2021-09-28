<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="robots" content="noindex">
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    <style>
        body {
            height: 842px;
            width: 795px;
            /* to centre page on screen*/
            margin-left: auto;
            margin-right: auto;
            font-family: sans-serif !important;
        }

        table {
            border-collapse: collapse;
        }

    </style>

</head>

<body style="padding:25px;">
    <table style="width:100%;">
        <tbody>
            <tr>
                <td style="width: 70%;">
                    {{($firmAddress['firm_name'])??''}}<br>
                    {{($firmAddress['countryname'])??''}}<br>
                    {{($firmAddress['main_phone'])??''}}<br>
                </td>
                <td style="width: 30%;;text-align: right;">
                    <h2> {{($firmAddress['firm_name'])??''}}</h2>
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <br>
    <table style="width:100%;">
        <tbody>
            <tr>
                <td style="width: 30%;">
                    {{ucfirst(substr($userData['first_name'],0,50))}} {{ucfirst(substr($userData['middle_name'],0,50))}}
                    {{ucfirst(substr($userData['last_name'],0,50))}}
                </td>
                <td style="width: 70%;;text-align: right;">
                    <b>Credit Account Summary for {{ucfirst(substr($userData['first_name'],0,50))}}
                        {{ucfirst(substr($userData['middle_name'],0,50))}}
                        {{ucfirst(substr($userData['last_name'],0,50))}}</b>
                    <br>Credit Balance on {{date('F,d,Y', strtotime($endDate))}}:
                    @if(!empty($creditHistory) && count($creditHistory))
                        @php
                            $lastCreditBalance = $creditHistory->last();
                            $finalBalance = $lastCreditBalance->total_balance ?? 0;
                        @endphp
                    @endif
                    ${{number_format($finalBalance ?? 0, 2)}}
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <br>
    <br>

    <span style="float: right;padding:5px;">Credit account activity from {{date('F d,Y', strtotime($startDate))}} to {{date('F d,Y', strtotime($endDate))}}</span>
    <br>
    <hr>
    <br>
    @if(!empty($creditHistory) && count($creditHistory))
    <table style="width:100%;text-align: left;font-size: 12px;" border="1">
        <thead class="bg-gray-300">
            <tr style="padding-left: 4px;background-color:gainsboro;">
                <th scope="col" style="width: 15%;padding:5px;">Date</th>
                <th scope="col" style="width: 15%;padding:5px;">Invoice</th>
                <th scope="col" style="width: 40%;padding:5px;">Details</th>
                <th scope="col" style="width: 15%;text-align: right;padding:5px;">Amount</th>
                <th scope="col" style="width: 15%;text-align: right;padding:5px;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($creditHistory) && count($creditHistory))
            <tr>
                <td style="padding:5px;">{{ (\Carbon\Carbon::parse($startDate)->lt(\Carbon\Carbon::now())) ? date('m/d/Y', strtotime($startDate)) : convertUTCToUserTimeZone('dateOnly') }}</td>
                <td style="padding:5px;">--</td>
                <td style="padding:5px;">Initial Balance</td>
                <td style="padding:5px;text-align: right;">--</td>
                <td style="padding:5px;text-align: right;">${{ ($initialBalance) ? number_format($initialBalance->total_balance) : "0.00" }}</td>
            </tr>
            @endif
            
            @if(!empty($creditHistory) && count($creditHistory))
            @forelse($creditHistory as $k=>$v)
                @php
                    $isRefund = ($v->is_refunded == "yes") ? "(Refunded)" : "";
                    if($v->payment_type == "withdraw")
                        $dText = "Withdraw from Credit (Operating Account)";
                    else if($v->payment_type == "refund withdraw")
                        $dText = "Refund Withdraw from Credit (Operating Account)";
                    else if($v->payment_type == "payment")
                        $dText = "Payment from Credit (Operating Account)";
                    else if($v->payment_type == "refund payment")
                        $dText = "Refund Payment from Credit (Operating Account)";
                    else if($v->payment_type == "refund deposit")
                        $dText = "Refund Deposit into Credit (Operating Account)";
                    else
                        $dText = "Deposit into Credit (Operating Account)";  
                        
                    if($v->payment_type == "deposit" || $v->payment_type == "refund withdraw") {
                        $amt = '$'.number_format($v->deposit_amount, 2);
                    } else {
                        $amt = '-$'.number_format($v->deposit_amount, 2);
                    }
                @endphp
                <tr>
                    <td style="padding:5px;">{{date('m/d/Y',strtotime($v->payment_date))}}</td>
                    <td style="padding:5px;">{{ ($v->related_to_invoice_id) ? $v->invoice->invoice_id : "--" }}</td>
                    <td style="padding:5px;">{{ $dText }}</td>
                    <td style="padding:5px;text-align: right;">{{ $amt }}</td>
                    <td style="padding:5px;text-align: right;">{{ "$".number_format($v->total_balance, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td style="padding:5px;text-align:center;" colspan="5">No data available</td>
                </tr>
            @endforelse
            @endif
        </tbody>
    </table>
    @else
    <p>No activity during this time period</p>
    @endif
</body>

</html>
