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
$invoice=$PotentialCaseInvoice['total_amount'];
$finalAmt=$invoice-$paid;
$flatFeeEntryAmount=$forwardedInvoices=$discount=$addition=$timeEntryTime=$timeEntryAmount=$expenseTime=$expenseAmount=0;
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
                        {{ucfirst(substr($userData['last_name'],0,50))}}</b>
                        </br>
                        {{$userData['email']}}
                    </td>
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
                                    {{$PotentialCaseInvoice['invoice_id']}}</td>
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
    <h3>Potential Case: {{ucfirst(substr($userData['first_name'],0,50))}} {{ucfirst(substr($userData['middle_name'],0,50))}} {{ucfirst(substr($userData['last_name'],0,50))}}</h3> 
    <br>
    <?php if(!$TimeEntryForInvoice->isEmpty()){?>
    <b>Time Entries</b>
    <table style="width:100%;text-align: left;font-size: 12px;" border="1">
        <thead class="bg-gray-300">
            <tr style="padding-left: 4px;">
                <td class="col">Date</td>
                <td class="col">EE</td>
                <td class="col">Activity</td>
                <td class="col">Description</td>
                <td class="col" tyle="width: 65px; text-align: right;">Rate</td>
                <td class="col" tyle="width: 65px; text-align: right;">Hours</td>
                <td class="col" tyle="width: 140px; text-align: right;">Line Total</td>
            </tr>
            <?php
            $nonBillData=[];
            foreach($TimeEntryForInvoice as $k=>$v){
                if($v->time_entry_billable=="yes"){
                    ?>
                <tr class="invoice_info_row ">
                    <td class="time-entry-date" style="vertical-align: top;">
                        {{date('m/d/Y',strtotime($v->entry_date))}}
                    </td>
                    <td class="time-entry-ee" style="vertical-align: top;">
                        {{$v->first_name[0]}}{{$v->last_name[0]}}
                    </td>
                    <td class="time-entry-activity" style="vertical-align: top;">
                        {{$v->activity_title}}
                    </td>
                    <td class="time-entry-description" style="vertical-align: top;">
                        <p class="invoice_notes">
                            {{$v->description}}
                        </p>
                    </td>
                    <td style="vertical-align: top; text-align: right;" class="" >
                        ${{number_format($v->entry_rate,2)}}
                    </td>
                    <td style="vertical-align: top; text-align: right;" class="">
                        <?php echo($v->rate_type=="flat") ?  "flat" : number_format($v->duration,1); ?>
                    </td>
                    <td style="vertical-align: top; text-align: right;" class="">
                        <?php
                            if($v->rate_type=="flat"){
                                $Total=$v->entry_rate;
                                $timeEntryAmount=$timeEntryAmount+$v->entry_rate;
                            }else{
                                $Total= ((int)$v->duration * (int)$v->entry_rate);
                                $timeEntryAmount=$timeEntryAmount+$Total;
                                
                                $timeEntryTime=(int)$timeEntryTime+(int)$v->duration;
                            }
                            echo "$".number_format($Total,2);
                            ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td colspan="5" class="total-summary-column" style="text-align:right;">
                    Totals:
                </td>
                <td class="total-entries-total-hours total-data-column" style="text-align:right; font-weight: bold;">
                    {{$timeEntryTime}}
                </td>
                <td class="total-data-column" style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;">
                    ${{number_format($timeEntryAmount,2)}}
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <?php } } ?>
    <br>
    <?php 
    if(!$InvoiceAdjustment->isEmpty()){?>
    <b>Adjustments</b>
    <table style="width:100%;text-align: left;font-size: 12px;" border="1">
        <thead class="bg-gray-300">
            <tr style="padding-left: 4px;">
                <td class="col">Item</td>
                <td class="col">Applied To</td>
                <td class="col">Type</td>
                <td class="col">Description</td>
                <td class="col" style="width: 65px; text-align: right;">Basis</td>
                <td class="col" style="width: 65px; text-align: right;">Percentage</td>
                <td class="col" style="width: 140px; text-align: right;">Line Total</td>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach($InvoiceAdjustment as $k=>$v){?>
            <tr class="invoice_info_row ">
                <td class="time-entry-date" style="vertical-align: top;">
                    <?php
                    $items=array("discount"=>"Discount","intrest"=>"Interest","tax"=>"Tax","addition"=>"Addition");
                    echo ($v->item != '') ? $items[$v->item] : "";
                    ?>
                </td>
                <td class="time-entry-ee" style="vertical-align: top;">
                    <?php 
                    $AppliedTo=array("flat_fees"=>"Flat Fees","time_entries"=>"Time Entries","expenses"=>"Expenses","balance_forward_total"=>"Balance Forward Total","sub_total"=>"Sub Total");
                    echo ($v->applied_to != '') ? $AppliedTo[$v->applied_to] : "";
                    ?>
                </td>
                <td class="time-entry-activity" style="vertical-align: top;">
                    <?php 
                        $adType=array("percentage"=>"% - Percentage","amount"=>"$ - Amount");
                        echo ($v->ad_type != '') ? $adType[$v->ad_type] : "";
                    ?>
                </td>
                <td class="time-entry-description" style="vertical-align: top;">
                    <p class="invoice_notes">
                        {{$v->notes}}
                    </p>
                </td>
                <td style="vertical-align: top; text-align: right;" class="nonbillable">
                    <?php 
                        echo ($v->ad_type=="amount") ? "-" : $v->basis;
                    ?>
                </td>
                <td style="vertical-align: top; text-align: right;" class="nonbillable">
                    <?php 
                        echo ($v->ad_type=="amount")? "-" : $v->percentages."%";
                    ?>
                </td>
                <td style="vertical-align: top; text-align: right;" class="nonbillable">
                    {{$v->amount}}
                    <?php 
                    if($v->item=="discount"){
                        $discount+=$v->amount;
                    }else{
                        $addition+=$v->amount;
                    }
                    ?>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <?php if($discount!="0"){?>
                <td colspan="6" style="text-align: right; padding-top: 5px;">
                    Discount Total:
                </td>
                <td style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;">
                    (${{number_format($discount,2)}})
                </td>
            </tr>
            <tr>
                <?php } ?>
                <?php if($addition!="0"){?>
                <td colspan="6" style="text-align: right; padding-top: 5px;">
                    Addition Total:
                </td>
                <td style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;">
                    ${{number_format($addition,2)}}
                </td>
                <?php } ?>
            </tr>
        </tbody>
    </table>
    <br>
    <?php } ?>
    <br>
    <div style="width: 100%">
        <div style="width: 55%;border: solid 1px black;padding-left: 5px;padding-top: 5px;min-height: 100px;float:left;font-size: 12px;">
            <b>Notes:</b><br>
            {!! nl2br($PotentialCaseInvoice['notes']) !!}
        </div>
        <div
            style="width: 40%;border: solid 1px black;padding-top: 5px;min-height: 100px;float: right;margin-left: 20px;">
            <table style="width:100%;text-align: left;font-size: 12px;" border="0">
                <tbody>
                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;">Time Entry Sub-Total:</td>
                        <td scope="col" style="width: 10%;text-align:right;">
                        ${{number_format($timeEntryAmount,2)}}</td>
                    </tr>
                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;"><b>Sub-Total:</b></td>
                        <td scope="col" style="width: 10%;text-align:right;">
                        ${{number_format($timeEntryAmount,2)}}</td>
                    </tr>
                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;"><br></td>
                        <td scope="col" style="width: 10%;text-align:right;"><br></td>
                    </tr>
                    <?php if($discount!="0"){?>
                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;">Discounts:</td>
                        <td scope="col" style="width: 10%;text-align:right;">(${{number_format($discount,2)}})</td>
                    </tr>
                    <?php }if($addition!="0"){?>
                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;">Additions:</td>
                        <td scope="col" style="width: 10%;text-align:right;">${{number_format($addition,2)}}</td>
                    </tr>
                    <?php } ?>
                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;"><br></td>
                        <td scope="col" style="width: 10%;text-align:right;"><br></td>
                    </tr>
                    <tr style="padding-left: 4px;">
                        <td scope="col" style="width: 10%;text-align:right;"><b>Total:</b></td>
                        <td scope="col" style="width: 10%;text-align:right;">
                        ${{number_format($timeEntryAmount+$expenseAmount+$flatFeeEntryAmount-$discount+$addition+$forwardedInvoices,2)}}<br></td>
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
                        <td scope="col" style="width: 10%;text-align:right;"><b>${{number_format($timeEntryAmount+$expenseAmount+$flatFeeEntryAmount+$addition+$forwardedInvoices-($discount+$PotentialCaseInvoice['amount_paid']),2)}}</b></td>
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
    <?php if(isset($InvoiceHistoryTransaction) && !$InvoiceHistoryTransaction->isEmpty()){?>
    <b> Payment History</b>    
    <table style="width:100%;text-align: left;font-size: 12px;" border="1">
        <thead class="bg-gray-300">
            <tr style="padding-left: 4px;">
                <td class="col" tyle="width: 12%;">Activity</td>
                <td class="col" tyle="width: 10%;">Date</td>
                <td class="col" tyle="width: 33%;">Payment Method</td>
                <td class="col" tyle="width: 12%;">Amount</td>
                <td class="col" tyle="width: 15%;">Responsible User</td>
                <td class="col" tyle="width: 18%;">Deposited Into</td>
            </tr>
        </thead>
        <tbody>
            <?php  foreach($InvoiceHistoryTransaction as $hKey=>$hVal){
            if(in_array($hVal->acrtivity_title,["Payment Received","Payment Refund"])){ ?>
            <tr class="invoice_info_row invoice-table-row">
                <td class="payment-history-column-activity " style="vertical-align: top;">
                    {{$hVal->acrtivity_title}}
                </td>
                <td class="payment-history-column-formatted-date" style="vertical-align: top;">
                    {{$hVal->added_date}}
                </td>
                <td class="payment-history-column-pay-method" style="vertical-align: top;">
                    {{$hVal->pay_method}}
                </td>
                <td class="payment-history-column-amount" style="vertical-align: top;">
                    <?php if($hVal->acrtivity_title=="Payment Received"){?>
                    ${{number_format($hVal->amount,2)}}
                    <?php }else if($hVal->acrtivity_title=="Payment Refund"){?>
                    (${{number_format($hVal->amount,2)}})
                    <?php } ?>
                </td>
                <td class="payment-history-column-user" style="vertical-align: top;">
                    {{substr($hVal->responsible['cname'],0,100)}}
                    ({{$hVal->responsible['user_title']}})
                </td>
                <td class="payment-history-column-deposited-into" style="vertical-align: top;">
                    @if($hVal->acrtivity_title=="Payment Received" && $hVal->pay_method != 'Non-Trust Credit Account')
                        {{ $hVal->deposit_into }}
                    @endif
                </td>
            </tr>
            <?php }   } ?>
        </tbody>
    </table>
    <?php } ?>
    <br>
</body>
</html>