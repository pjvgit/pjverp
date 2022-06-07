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
            font-family: Nunito, sans-serif;

        }

        table {
            border-collapse: collapse;
        }

        th,
        td {
            padding: 5px;
        }

    </style>

</head>
<?php
// $paid=$Invoice['amount_paid'];
// $invoice=$Invoice['invoice_amount'];
$invoice=$Invoice['total_amount'];
$paid=$Invoice['paid_amount'];
$finalAmt=$invoice-$paid;
?>

<body style="padding:25px;">
    <table style="width:100%;">
        <tbody>
            <tr>
                <td style="width: 70%;">
                    {{($firmAddress['firm_name'])??''}}<br>
                    {{($firmAddress['countryname'])??''}}<br>
                    {{($firmAddress['main_phone'])??''}}<br>
                </td>
                <td style="width: 30%;;">
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
                <td style="width: 60%;"><b>
                        {{ucfirst(substr($userData['first_name'],0,50))}}
                        {{ucfirst(substr($userData['middle_name'],0,50))}}
                        {{ucfirst(substr($userData['last_name'],0,50))}}</b>
                        <br>
                        {!! nl2br($Invoice->bill_address_text) !!}
                </td>
                <td style="width: 40%;">

                    <table style="width:100%;text-align: left;font-size: 16px;" border="0">
                        <tbody>
                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Balance:</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    ${{number_format($finalAmt,2)}}
                                </td>
                            </tr>
                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Invoice #:</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    {{ $Invoice['invoice_id'] }}</td>
                            </tr>


                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Invoice Date :</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    {{date("F d,Y",strtotime($Invoice['invoice_date']))}}</td>
                            </tr>
                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Payment Terms:</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    <?php
                                    $items=array("0"=>"Due date","1"=>"Due on receipt","2"=>"Net 15","3"=>"Net 30","4"=>"Net 60","5"=>"");
                                    ?>
                                    <?php echo $items[$Invoice['payment_term']]; ?>

                                </td>
                            </tr>
                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Due Date:</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    <?php 
                                    if($Invoice['due_date']!=NULL){?>
                                    {{date("F d,Y",strtotime($Invoice['due_date']))}}
                                    <?php } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <hr>

    <h3>
        @if($Invoice['is_lead_invoice'] == 'yes') 
        <p>Potential Case: {{ucfirst(substr($userData['first_name'],0,50))}}
                        {{ucfirst(substr($userData['middle_name'],0,50))}}
                        {{ucfirst(substr($userData['last_name'],0,50))}}</p>
        @else
        <p>{{ucfirst(substr(@$caseMaster['case_title'],0,100))}}</p>
        @endif
    </h3>
    <?php if($InvoiceHistory['acrtivity_title']=="Payment Received"){ ?>
    <h4>Payment Recepit</h4>
    <table style="width:50%;text-align: left;font-size: 16px;" border="0">
        <tbody>
            <tr>
                <td scope="col" style="width: 10%;text-align:left;"><b>Date:</b> {{convertUTCToUserTime($InvoiceHistory['created_at'], auth()->user()->user_timezone)}} UTC
                </td>
            </tr>
            <tr>
                <td scope="col" style="width: 10%;text-align:left;"><b>Payment:</b>:
                    ${{number_format($InvoiceHistory['amount'],2)}} on Invoice
                    #{{ $Invoice['invoice_id'] }}</td>
            </tr>
            <tr>
                <td scope="col" style="width: 10%;text-align:left;"><b>Payment
                        Type:</b>{{$InvoiceHistory['pay_method']}}</td>
            </tr>
            <tr>
                <td scope="col" style="width: 10%;text-align:left;"><b>Payment Identifier:</b> {{$InvoiceHistory['id']}}</td>
            </tr>
            <tr>
                <td scope="col" style="width: 10%;text-align:left;"><b>Entered By:</b>
                    {{($firmAddress['firm_name'])??''}}</td>
            </tr>

        </tbody>
    </table>
    <?php }else{ ?>
    <h4>Refund Recepit</h4>
    <table style="width:50%;text-align: left;font-size: 16px;" border="0">
        <tbody>
            <tr>
                <td scope="col" style="width: 10%;text-align:left;"><b>Date:</b> {{convertUTCToUserTime($InvoiceHistory['created_at'], auth()->user()->user_timezone)}} UTC
                </td>
            </tr>
            <tr>
                <td scope="col" style="width: 10%;text-align:left;"><b>Refund:</b>:
                    ${{number_format($InvoiceHistory['amount'],2)}} on Invoice #{{ $Invoice['invoice_id'] }}
            </tr>
            <tr>
                <td scope="col" style="width: 10%;text-align:left;"><b>Payment
                        Type:</b>{{$InvoiceHistory['pay_method']}}</td>
            </tr>
            <tr>
                <td scope="col" style="width: 10%;text-align:left;"><b>Payment Identifier:</b> {{$InvoiceHistory['id']}}</td>
            </tr>
            <tr>
                <td scope="col" style="width: 10%;text-align:left;"><b>Entered By:</b>
                    {{($firmAddress['firm_name'])??''}}</td>
            </tr>

        </tbody>
    </table>
    <?php }?>

</body>

</html>
