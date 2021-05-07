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
                    <b>Trust Account Summary for {{ucfirst(substr($userData['first_name'],0,50))}}
                        {{ucfirst(substr($userData['middle_name'],0,50))}}
                        {{ucfirst(substr($userData['last_name'],0,50))}}</b>
                    <br>Trust Balance on {{date('m/d/Y')}}:
                    ${{number_format($UsersAdditionalInfo['trust_account_balance'],2)}}
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <br>
    <br>

    <span style="float: right;padding:5px;">Trust account activity through {{date('F,d,Y')}}</span>

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
            <?php 
            if(!$allHistory->isEmpty()){?>
            <tr>
                <td style="padding:5px;">{{date('m/d/Y',strtotime($allHistory[0]->created_at))}}</td>
                <td style="padding:5px;">--</td>
                <td style="padding:5px;">Initial Balance</td>
                <td style="padding:5px;text-align: right;">--</td>
                <td style="padding:5px;text-align: right;">$0.00</td>
            </tr>
            <?php } ?>
            <?php 
            if(!$allHistory->isEmpty()){
            foreach($allHistory as $k=>$v){
                $isRefender=''; 
                if($v->is_refunded=="yes"){
                        $isRefender="(Refunded)";
                }
                if($v->fund_type=="withdraw"){
                    if($v->withdraw_from_account!=null){
                         $ftype="Withdraw from Trust to Operating(".$v->withdraw_from_account.")";
                    }else{
                        $ftype="Withdraw from Trust".$isRefender;
                    }
                }else if($v->fund_type=="refund_withdraw"){
                    $ftype="Refund Withdraw from Trust";
                }else if($v->fund_type=="refund_deposit"){
                    $ftype="Refund Deposit into Trust";
                }else{
                    $ftype="Deposit into Trust";
                }

                $tansactionLable=$tansactionAmount=$tansactionBalance='';
                if($v->fund_type=="diposit"){
                    
                    $tansactionAmount="$".number_format($v->amount_paid,2);
                    $tansactionBalance="$".number_format($v->current_trust_balance,2);
                }
                if($v->fund_type=="withdraw"){
                 
                    $tansactionAmount="-$".number_format($v->withdraw_amount,2);
                    $tansactionBalance="$".number_format($v->current_trust_balance,2);
                }
                if($v->fund_type=="refund_withdraw"){
                   
                    $tansactionAmount="$".number_format($v->refund_amount,2);
                    $tansactionBalance="$".number_format($v->current_trust_balance,2);
                }
                if($v->fund_type=="refund_deposit"){
                   
                    $tansactionAmount="$".number_format($v->refund_amount,2);
                    $tansactionBalance="$".number_format($v->current_trust_balance,2);
                }
                ?>
            <tr>
                <td style="padding:5px;">{{date('m/d/Y',strtotime($v->payment_date))}}</td>
                <td style="padding:5px;">--</td>
                <td style="padding:5px;">{{$ftype}}</td>
                <td style="padding:5px;text-align: right;">{{$tansactionAmount}}</td>
                <td style="padding:5px;text-align: right;">{{$tansactionBalance}}</td>
            </tr>
            <?php }  }else{
                ?>
                 <tr>
                    <td style="padding:5px;text-align:center;" colspan="5">No data available</td>
                </tr>
                <?php 
            }
            ?>
        </tbody>
    </table>
</body>

</html>
