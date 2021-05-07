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
            font-family: '"Nunito", sans-serif';

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
$paid=$PotentialCaseInvoice['amount_paid'];
$invoice=$PotentialCaseInvoice['invoice_amount'];
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
                <td style="width: 60%;"><b>{{ucfirst(substr($userData['first_name'],0,50))}}
                        {{ucfirst(substr($userData['middle_name'],0,50))}}
                        {{ucfirst(substr($userData['last_name'],0,50))}}</b></td>
                <td style="width: 40%;">

                    <table style="width:100%;text-align: left;font-size: 16px;" border="0">
                        <tbody>
                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Balance:</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    ${{number_format($finalAmt,2)}}</td>
                            </tr>
                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Invoice #:</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    {{$PotentialCaseInvoice['invoice_number']}}</td>
                            </tr>


                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Invoice Date :</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    {{date("F d,Y",strtotime($PotentialCaseInvoice['invoice_date']))}}</td>
                            </tr>
                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Payment Terms:</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    Due Date</td>
                            </tr>

                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Due Date:</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    {{date("F d,Y",strtotime($PotentialCaseInvoice['due_date']))}}</td>
                            </tr>
                        </tbody>
                    </table>


                </td>
            </tr>
        </tbody>
    </table>
    <hr>

    <h3>
        <p>Potential Case: {{ucfirst(substr($userData['first_name'],0,50))}}
            {{ucfirst(substr($userData['middle_name'],0,50))}} {{ucfirst(substr($userData['last_name'],0,50))}}</p>
    </h3> <br>

    <b>Time Entries</b>

    <table style="width:100%;text-align: left;font-size: 12px;" border="1">
        <thead class="bg-gray-300">
            <tr style="padding-left: 4px;">
                <th scope="col" style="width: 10%;">Date</th>
                <th scope="col" style="width: 10%;">EE</th>
                <th scope="col" style="width: 25%;">Activity</th>
                <th scope="col" style="width: 25%;">Description</th>
                <th scope="col" style="width: 10%;text-align: right;">Rate</th>
                <th scope="col" style="width: 10%;text-align: right;">Hours</th>
                <th scope="col" style="width: 10%;text-align: right;">Line Total</th>
            </tr>
        </thead>
        <tbody>
            <tr style="padding-left: 4px;">
                <td scope="col" style="width: 10%;">{{date("m/d/Y",strtotime($PotentialCaseInvoice['invoice_date']))}}
                </td>
                <td scope="col" style="width: 10%;">TU</td>
                <td scope="col" style="width: 25%;">Consultation Fee</td>
                <td scope="col" style="width: 25%;">Consultation Fee</td>
                <td scope="col" style="width: 10%;text-align: right;">
                    ${{number_format($PotentialCaseInvoice['invoice_amount'],2)}}</td>
                <td scope="col" style="width: 10%;text-align: right;">flat</td>
                <td scope="col" style="width: 10%;text-align: right;">
                    ${{number_format($PotentialCaseInvoice['invoice_amount'],2)}}</td>
            </tr>

        </tbody>
    </table>

    <table style="width:100%;text-align: left;font-size: 12px;" border="0">
        <tbody>
            <tr style="padding-left: 4px;">
                <td scope="col" style="width: 10%;"></td>
                <td scope="col" style="width: 10%;"></td>
                <td scope="col" style="width: 25%;"></td>
                <td scope="col" style="width: 25%;"></td>
                <td scope="col" style="width: 10%;text-align: right;">Totals:</td>
                <td scope="col" style="width: 10%;text-align: right;">0.0</td>
                <td scope="col" style="width: 10%;text-align: right;">
                    ${{number_format($PotentialCaseInvoice['invoice_amount'],2)}}</td>
            </tr>

        </tbody>
    </table>
    <br>
    <br>
    <div style="width: 100%">

        <div
            style="width: 55%;border: solid 1px black;padding-left: 5px;padding-top: 5px;min-height: 100px;float:left;font-size: 12px;">
            <b>Notes:</b><br>
            {{$PotentialCaseInvoice['description']}}
        </div>
        <div
            style="width: 40%;border: solid 1px black;padding-top: 5px;min-height: 100px;float: right;margin-left: 20px;">
            <table style="width:100%;text-align: left;font-size: 12px;" border="0">
                <tbody>
                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;">Time Entry Sub-Total:</td>
                        <td scope="col" style="width: 10%;text-align:right;">
                            ${{number_format($PotentialCaseInvoice['invoice_amount'],2)}}</td>
                    </tr>
                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;"><b>Sub-Total:</b></td>
                        <td scope="col" style="width: 10%;text-align:right;">
                            ${{number_format($PotentialCaseInvoice['invoice_amount'],2)}}</td>
                    </tr>
                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;"><br></td>
                        <td scope="col" style="width: 10%;text-align:right;"><br></td>
                    </tr>

                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;"><b>Total:</b></td>
                        <td scope="col" style="width: 10%;text-align:right;">
                            ${{number_format($PotentialCaseInvoice['invoice_amount'],2)}}</td>
                    </tr>
                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;"><b>Amount Paid:</b></td>
                        <td scope="col" style="width: 10%;text-align:right;">
                            ${{number_format($PotentialCaseInvoice['amount_paid'],2)}}</td>
                    </tr>
                    <tr style="padding-left: 4px;">
                        <td colspan="2" style="width: 100%;text-align:right;">
                            <hr>
                        </td>
                    </tr>

                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;"><b>Balance Due:</b></td>
                        <td scope="col" style="width: 10%;text-align:right;">${{number_format($finalAmt,2)}}</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
    <table style="width:100%;text-align: left;font-size: 12px;" border="0">
        <tbody>
            <tr style="padding-left: 4px;">
                <td scope="col" style="width: 100%;"></td>

            </tr>

        </tbody>
    </table>
    <br>
    <?php

    if(!$PotentialCaseInvoicePayment->isEmpty()){?>
    <b>Time Entries</b>
        <table style="width:100%;text-align: left;font-size: 12px;" border="1">
            <thead class="bg-gray-300">
                <tr style="padding-left: 4px;">
                    <th scope="col" style="width: 10%;">Activity</th>
                    <th scope="col" style="width: 10%;">Date</th>
                    <th scope="col" style="width: 25%;">Payment Method </th>
                    <th scope="col" style="width: 25%;">Amount</th>
                    <th scope="col" style="width: 10%;">Responsible User</th>
                    <th scope="col" style="width: 10%;">Deposited Into</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach($PotentialCaseInvoicePayment as $k=>$v){?>
                <tr style="padding-left: 4px;">
                    <td scope="col" style="width: 10%;">Payment Received</td>
                    <td scope="col" style="width: 10%;">{{date("M d,Y",strtotime($v['payment_date']))}}
                    </td>

                    <td scope="col" style="width: 25%;">{{$v['payment_method']}}</td>
                    <td scope="col" style="width: 25%;">${{number_format($v['amount_paid'],2)}}</td>
                
                    <td scope="col" style="width: 10%;text-align: right;">{{$v['first_name']}} {{$v['last_name']}}
                        <?php if($v['user_title']){ echo "(".$v['user_title'].")"; }?></td>
                    <td scope="col" style="width: 10%;text-align: right;">Operating</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
    <br>
    <br>
    <br>
    &nbsp;
</body>

</html>
