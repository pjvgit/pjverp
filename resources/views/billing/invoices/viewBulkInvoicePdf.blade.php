@extends('layouts.pdflayout')

<?php
foreach($pdfData as $keyData=>$valueData){
// $paid=$valueData['Invoice']['amount_paid'];
// $invoice=$valueData['Invoice']['invoice_amount'];
$invoice=$valueData['Invoice']['total_amount'];
$paid=$valueData['Invoice']['paid_amount'];
$finalAmt=$invoice-$paid;
?>
<div class="page" style="page-break-after: always;">
    <table style="width:100%;">
        <tbody>
            <tr>
                <td style="width: 70%;">
                    {{($valueData['firmAddress']['firm_name'])??''}}<br>
                    {{($valueData['firmAddress']['countryname'])??''}}<br>
                    {{($valueData['firmAddress']['main_phone'])??''}}<br>
                </td>
                <td style="width: 30%;float:right;text-align:right;">
                    <h2> {{($valueData['firmAddress']['firm_name'])??''}}</h2>
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
                        {{ucfirst(substr($valueData['userData']['user_name'],0,100))}}</b><br>
                        {!! nl2br($valueData['Invoice']['bill_address_text']) !!}
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
                                    {{sprintf('%06d', $valueData['Invoice']['id'])}}</td>
                            </tr>


                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Invoice Date :</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    {{date("F d,Y",strtotime($valueData['Invoice']['invoice_date']))}}</td>
                            </tr>
                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Payment Terms:</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    <?php
                                    $items=array("0"=>"Due date","1"=>"Due on receipt","2"=>"Net 15","3"=>"Net 30","4"=>"Net 60","5"=>"");
                                    ?>
                                    <?php echo $items[$valueData['Invoice']['payment_term']]; ?>

                                </td>
                            </tr>
                            <tr style="padding-left: 4px;">
                                <td scope="col" style="width: 10%;text-align:right;"><b>Due Date:</b></td>
                                <td scope="col" style="width: 10%;text-align:left;">
                                    <?php 
                                    if($valueData['Invoice']['due_date']!=NULL){?>
                                    {{date("F d,Y",strtotime($valueData['Invoice']['due_date']))}}
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
        <p>{{ucfirst(substr($valueData['caseMaster']['case_title'],0,100))}}</p>
    </h3>
    <?php   $flatFeeEntryAmount=$discount=$addition=$timeEntryTime=$timeEntryAmount=$expenseTime=$expenseAmount=$forwardedInvoices=0;?>
    <?php if(!$valueData['FlatFeeEntryForInvoice']->isEmpty()){?>
    <b>Flat Fee</b>
    <table style="width: 100%; border-collapse: collapse;font-size: 12px;" border="1">
        <tbody>
            <tr class="invoice_info_row">
                <td class="invoice_info_bg">
                    Date
                </td>
                <td class="invoice_info_bg">
                    EE
                </td>
                <td class="invoice_info_bg">
                    Item
                </td>
                <td class="invoice_info_bg">
                    Description
                </td>
                <td class="invoice_info_bg" style=" text-align: right;width:10%;">
                    Amount
                </td>
            </tr>
            <?php
        
            $nonBillDataFlateFee=[];
            foreach($valueData['FlatFeeEntryForInvoice'] as $k=>$v){
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
                        Flat Fee
                    </td>
                    <td class="time-entry-description" style="vertical-align: top;">
                        <p class="invoice_notes">
                            {{$v->description}}
                        </p>
                    </td>
                    <td style="vertical-align: top; text-align: right;" class="" >
                        ${{number_format($v->cost,2)}}
                    </td>
                    
                </tr>
            <?php 
            $flatFeeEntryAmount+=$v->cost;
            }else{
                    $nonBillDataFlateFee[]=$v;
                }
            } ?>

            <?php
            if(!empty($nonBillDataFlateFee)){
                ?>
                <tr class="invoice_info_row nonbillable-title">
                    <td class="invoice_info_bg" colspan="7">
                    Non-billable Flat Fees:
                    </td>
                    </tr>
                <?php 
                foreach($nonBillDataFlateFee as $k=>$v){
                    ?>
                    <tr class="invoice_info_row ">
                        <td class="time-entry-date" style="vertical-align: top;">
                            {{date('m/d/Y',strtotime($v->entry_date))}}
                        </td>
                        <td class="time-entry-ee" style="vertical-align: top;">
                            {{$v->first_name[0]}}{{$v->last_name[0]}}
                        </td>
                        <td class="time-entry-activity" style="vertical-align: top;">
                            Flat Fee
                        </td>
                        <td class="time-entry-description" style="vertical-align: top;">
                            <p class="invoice_notes">
                                {{$v->description}}
                            </p>
                        </td>
                        <td style="vertical-align: top; text-align: right;" class="nonbillableRow" >
                            ${{number_format($v->cost,2)}}
                        </td>
                        
                    </tr>
                    <?php
                }

            }?>
            <tr>
                <td colspan="4" class="total-summary-column" style="text-align:right;">
                    Flat Fee Total:
                </td>

                <td class="total-data-column" style="text-align:right;"> ${{number_format($flatFeeEntryAmount,2)}}
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <?php } ?>  
    <?php
    if(!$valueData['TimeEntryForInvoice']->isEmpty()){?>
    <b>Time Entries</b>
    <table style="width: 100%; border-collapse: collapse;font-size: 12px;" border="1">
        <tbody>
            <tr class="invoice_info_row">
                <td class="invoice_info_bg" style="width: 10%;">
                    Date
                </td>
                <td class="invoice_info_bg" style="width: 10%;">
                    EE
                </td>
                <td class="invoice_info_bg" style="width: 20%;">
                    Activity
                </td>
                <td class="invoice_info_bg" style="width: 30%;">
                    Description
                </td>
                <td class="invoice_info_bg" style="width: 65px; text-align: right;width: 10%;">
                    Rate
                </td>
                <td class="invoice_info_bg" style="width: 65px; text-align: right;width: 10%;">
                    Hours
                </td>
                <td class="invoice_info_bg" style="width: 140px; text-align: right;width: 10%;">
                    Line Total
                </td>
            </tr>
            <?php 
             
                foreach($valueData['TimeEntryForInvoice'] as $k=>$v){
                    ?>
            <tr class="invoice_info_row ">
                <td class="time-entry-date" style="vertical-align: top;width: 10%;">
                    {{date('m/d/Y',strtotime($v->entry_date))}}
                </td>
                <td class="time-entry-ee" style="vertical-align: top;width: 10%;">
                    {{$v->first_name[0]}}{{$v->last_name[0]}}
                </td>
                <td class="time-entry-activity" style="vertical-align: top;width: 20%;">
                    {{$v->activity_title}}
                </td>
                <td class="time-entry-description" style="vertical-align: top;width: 30%;">
                    <p class="invoice_notes">
                        {{$v->description}}
                    </p>
                </td>
                <td style="vertical-align: top; text-align: right;width: 10%;" class="nonbillable">
                    ${{number_format($v->entry_rate,2)}}
                </td>
                <td style="vertical-align: top; text-align: right;width: 10%;" class="nonbillable">
                    <?php 
                                if($v->rate_type=="flat"){
                                    echo "flat";
                                }else{
                                    echo number_format($v->duration,1);
                                } ?>
                </td>
                <td style="vertical-align: top; text-align: right;width: 10%;" class="nonbillable">
                    <?php
                                if($v->rate_type=="flat"){
                                    $Total=$v->entry_rate;
                                    $timeEntryAmount=$timeEntryAmount+$v->entry_rate;
                                }else{
                                    $Total= ($v->duration * $v->entry_rate);
                                    $timeEntryAmount=$timeEntryAmount+$Total;
                                    
                                    $timeEntryTime=$timeEntryTime+$v->duration;
                                }
                                echo "$".number_format($Total,2);

                                ?>
                </td>
            </tr>
            <?php } ?>
            <tr style="padding-left: 4px;">
                <td scope="col" colspan="5" style="width: 80%;text-align: right;">Totals:</td>
                <td scope="col" style="width: 10%;text-align: right;"> <b>{{number_format($timeEntryTime,1)}}</b></td>
                <td scope="col" style="width: 10%;text-align: right;">
                    <b>${{number_format($timeEntryAmount,2)}}</b>
                </td>
            </tr>
        </tbody>
    </table>
    <?php } ?>
    <?php 
    if(!$valueData['ExpenseForInvoice']->isEmpty()){?>
    <b>Expenses</b>
    <table style="width: 100%; border-collapse: collapse;font-size: 12px;" border="1">
        <tbody>
            <tr class="invoice_info_row">
                <td class="invoice_info_bg" width="10%">
                    Date
                </td>
                <td class="invoice_info_bg" width="10%">
                    EE
                </td>
                <td class="invoice_info_bg" width="20%">
                    Activity
                </td>
                <td class="invoice_info_bg" width="30%">
                    Description
                </td>
                <td class="invoice_info_bg" style="width: 65px; text-align: right;" width="10%">
                    Cost
                </td>
                <td class="invoice_info_bg" style="width: 65px; text-align: right;" width="10%">
                    Quantity
                </td>
                <td class="invoice_info_bg" style="width: 140px; text-align: right;" width="10%">
                    Line Total
                </td>
            </tr>
            <?php 
              $expenseTime=0;
                $expenseAmount=0;
                foreach($valueData['ExpenseForInvoice'] as $k=>$v){?>
            <tr class="invoice_info_row ">
                <td class="time-entry-date" style="vertical-align: top;width: 10%;">
                    {{date('m/d/Y',strtotime($v->entry_date))}}
                </td>
                <td class="time-entry-ee" style="vertical-align: top;width: 10%;">
                    {{$v->first_name[0]}}{{$v->last_name[0]}}
                </td>
                <td class="time-entry-activity" style="vertical-align: top;width: 20%;">
                    {{$v->activity_title}}
                </td>
                <td class="time-entry-description" style="vertical-align: top;width: 30%;">
                    <p class="invoice_notes">
                        {{$v->description}}
                    </p>
                </td>
                <td style="vertical-align: top; text-align: right;width: 10%;" class="nonbillable">
                    ${{number_format($v->cost,2)}}
                </td>
                <td style="vertical-align: top; text-align: right;width: 10%;" class="nonbillable">

                    <?php 
                        echo number_format($v->duration,1);?>
                </td>
                <td style="vertical-align: top; text-align: right; width: 10%;" class="nonbillable">
                    <?php
                         echo $Total= ($v->duration * $v->cost);
                            $expenseAmount=$expenseAmount+$Total;
                        ?>
                </td>
            </tr>
            <?php } ?>
            <tr style="padding-left: 4px;">
                <td scope="col" colspan="6" style="text-align: right;width:90%;" width="90%"> Expense Total:</td>
                <td scope="col" style="width: 10%;text-align: right;" width="10%">
                    <b> ${{number_format($expenseAmount,2)}}</b>
                </td>
            </tr>
        </tbody>
    </table>
    <?php } ?>
    @if (count($valueData['Invoice']->forwardedInvoices))
    <b>Unpaid Invoice Balance Forward</b>
    <table style="width: 100%; border-collapse: collapse;font-size: 12px;" border="1">
        <tbody>
            <tr class="invoice_info_row">
                <td class="invoice_info_bg">Invoice #</td>
                <td class="invoice_info_bg">Invoice Total</td>
                <td class="invoice_info_bg">Amount Paid</td>
                <td class="invoice_info_bg">Due Date</td>
                <td class="invoice_info_bg" style=" text-align: right;width:10%;">Balance Forward</td>
            </tr>
            @forelse ($valueData['Invoice']->forwardedInvoices as $invkey => $invitem)
                <tr class="invoice_info_row">
                    <td>{{ $invitem->invoice_id }}</td>
                    <td>${{ $invitem->total_amount_new }}</td>
                    <td>${{ $invitem->paid_amount_new }}</td>
                    <td>{{ ($invitem->due_date) ? date('m/d/Y', strtotime($invitem->due_date)) : "" }}</td>
                    <td style="vertical-align: top; text-align: right;">${{ $invitem->due_amount_new }}</td>
                </tr>
            @empty                        
            @endforelse
            <tr>
                <td colspan="4" class="total-summary-column" style="text-align: right;">
                    Balance Forward:
                </td>
                @php
                    $forwardedInvoices = $valueData['Invoice']->forwardedInvoices->sum('due_amount');
                @endphp
                <td class="total-data-column" style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;"> ${{ number_format($forwardedInvoices, 2) }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    @endif
    <?php 
    if(!$valueData['InvoiceAdjustment']->isEmpty()){?>
    <h3>Adjustments</h3>
    <table style="width: 100%; border-collapse: collapse;font-size: 12px;" border="1">
        <tbody>
            <tr class="invoice_info_row">
                <td class="invoice_info_bg">
                    Item
                </td>
                <td class="invoice_info_bg">
                    Applied To
                </td>
                <td class="invoice_info_bg">
                    Type
                </td>
                <td class="invoice_info_bg">
                    Description
                </td>
                <td class="invoice_info_bg" style="width: 65px; text-align: right;">
                    Basis
                </td>
                <td class="invoice_info_bg" style="width: 65px; text-align: right;">
                    Percentage
                </td>
                <td class="invoice_info_bg" style="width: 140px; text-align: right;">
                    Line Total
                </td>
            </tr>
            <?php 
                
                    foreach($valueData['InvoiceAdjustment'] as $k=>$v){?>
            <tr class="invoice_info_row ">
                <td class="time-entry-date" style="vertical-align: top;">
                    <?php
                            $items=array("discount"=>"Discount","intrest"=>"Interest","tax"=>"Tax","addition"=>"Addition");
                            echo $items[$v->item];
                            ?>
                </td>
                <td class="time-entry-ee" style="vertical-align: top;">
                    <?php 
                        $AppliedTo=array("flat_fees"=>"Flat Fees","time_entries"=>"Time Entries","expenses"=>"Expenses","balance_forward_total"=>"Balance Forward Total","sub_total"=>"Sub Total");
                        echo $AppliedTo[$v->applied_to];
                        ?>
                </td>
                <td class="time-entry-activity" style="vertical-align: top;">

                    <?php 
                            $adType=array("percentage"=>"% - Percentage","amount"=>"$ - Amount");
                            echo $adType[$v->ad_type];
                            ?>
                </td>
                <td class="time-entry-description" style="vertical-align: top;">
                    <p class="invoice_notes">
                        {{$v->notes}}
                    </p>
                </td>
                <td style="vertical-align: top; text-align: right;" class="nonbillable">
                    <?php 
                            if($v->ad_type=="amount"){
                                echo "-";
                            }else{
                                echo $v->basis;
                            }
                            ?>

                </td>
                <td style="vertical-align: top; text-align: right;" class="nonbillable">
                    <?php 
                            if($v->ad_type=="amount"){
                                echo "-";
                            }else{
                                echo $v->percentages."%";
                            }
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

    <table style="width: 100%; border-collapse: collapse;font-size: 12px;border-left: none;
        border-bottom: none;border-top: none;" border="1">
        <tbody>
            <tr class="invoice_info_row">
                <td style="width: 580px; vertical-align: top; border: none;">
                </td>
                <td style="width: 10px; border: none;" rowspan="1">
                    &nbsp;
                </td>
                <td style="text-align: right; border-right: none; vertical-align: top; width: 140px; line-height: 1.8;"
                    rowspan="1">
                    <?php if(!$valueData['FlatFeeEntryForInvoice']->isEmpty()){?>
                    Flat Fee Sub-Total:<br>
                    <?php } ?>
                    Time Entry Sub-Total:<br>
                    Expense Sub-Total:<br>
                    <span style="font-weight: bold;">Sub-Total:</span><br>
                    <br>
                    <?php  if (count($valueData['Invoice']->forwardedInvoices)){ ?>
                    <span>Balance Forward:</span>
                    <br>
                    <?php } ?>
                    <?php if($discount!="0"){?>
                    <span>Discounts:</span>
                    <br>
                    <?php } ?>

                    <?php if($addition!="0"){?>
                    <span>Additions:</span>
                    <br>
                    <?php } ?><br>
                    <span style="font-weight:bold;">Total:</span><br>
                    <span style="font-weight:bold;">Amount Paid:</span><br>
                </td>
                <td style="text-align: right; border-left: none; vertical-align: top; width: 85px; line-height: 1.8;"
                    rowspan="1">
                    <?php if(!$valueData['FlatFeeEntryForInvoice']->isEmpty()){?>
                    ${{number_format($flatFeeEntryAmount,2)}}<br>
                    <?php } ?>
                    ${{number_format($timeEntryAmount,2)}}<br>
                    ${{number_format($expenseAmount,2)}}<br>
                    ${{number_format($timeEntryAmount+$expenseAmount,2)}}<br>
                    <br>
                    <?php  
                    if (count($valueData['Invoice']->forwardedInvoices)){
                        $forwardedInvoices = $valueData['Invoice']->forwardedInvoices->sum('due_amount'); ?>
                        ${{ number_format($forwardedInvoices,2) }} <br>
                    <?php } ?>

                    <?php if($discount!="0"){?>
                    ${{number_format($discount,2)}}<br>
                    <?php } ?>

                    <?php if($addition!="0"){?>
                    ${{number_format($addition,2)}}<br>
                    <?php } ?>
                    <br>
                    ${{number_format($timeEntryAmount+$expenseAmount+$flatFeeEntryAmount-$discount+$addition+$forwardedInvoices,2)}}<br>
                    ${{number_format($valueData['Invoice']['paid_amount'],2)}}
                </td>
            </tr>


            <tr class="invoice_info_row">
                <td style="border: none;">
                    &nbsp;
                </td>
                <td style="border: none;">
                    &nbsp;
                </td>
                <td class="invoice_info_bg"
                    style="text-align: right; border-right: none; vertical-align: top; font-weight: bold; ">
                    Balance Due:
                </td>
                <td class="invoice_info_bg" id="invoice-balance-due"
                    style="text-align: right; border-left: none; vertical-align: top; font-weight: bold; ">
                    ${{number_format($timeEntryAmount+$expenseAmount+$flatFeeEntryAmount+$addition+$forwardedInvoices-($discount+$valueData['Invoice']['paid_amount']),2)}}
                </td>
            </tr>

        </tbody>
    </table>
    <br>

    <?php if(isset($valueData['InvoiceInstallment']) && !$valueData['InvoiceInstallment']->isEmpty()){?>
    <table style="width: 100%; border-collapse: collapse;font-size: 12px;border-left: none;float:right;
            border-bottom: none;border-top: none;margin-bottom:10px;float:left;" border="0">
        <tr>
            <td>
            </td>
        </tr>
        <tr>
            <td>
                <table style="width: 35%; border-collapse: collapse;font-size: 12px;border-left: none;float:right;
                border-bottom: none;border-top: none;margin-bottom:10px;" border="0">
                    <tbody>

                        <tr class="invoice_info_row" style="font-size: 12px;">
                            <td style="border: none;">
                                <a name="payment_plan">&nbsp;</a>
                            </td>
                            <td style="border: none;">&nbsp;</td>
                            <td class="" style="vertical-align: top; border: none; padding: 0;" colspan="2">
                                <div style="margin: 0px; border: 1px solid black;">
                                    <table class="payment_plan" style="width: 100%; border-collapse: collapse;">
                                        <tbody>
                                            <tr class="header">
                                                <th class="invoice_info_bg installment_due"
                                                    style="padding: 5px;text-align:left;font-size: 12px;">Installment
                                                    Due</th>
                                                <th class="invoice_info_bg amount_due"
                                                    style="padding: 5px;text-align:right;font-size: 12px;">Amount</th>
                                            </tr>
                                            <?php    foreach($valueData['InvoiceInstallment'] as $lk=>$lv){ ?>
                                            <tr class="even  ">
                                                <td style="border: none; border-bottom: 1px solid #cccccc;font-size: 12px;"
                                                    class="nonbillable js-payment_plan_date">
                                                    {{date('M d,Y',strtotime($lv->due_date))}}
                                                </td>

                                                <td style="text-align: right; border: none; border-bottom: 1px solid #cccccc;font-size: 12px;"
                                                    class="nonbillable js-payment_plan_amount">
                                                    ${{number_format($lv->installment_amount,2)}}
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
    <br>
    <?php } ?>
    <?php if(isset($valueData['InvoiceHistoryTransaction']) && !$valueData['InvoiceHistoryTransaction']->isEmpty()){?>
    <h3> Payment History</h3>
    <table
        style="width: 100%; border-collapse: collapse;font-size: 12px;border-left: none;float:right; border-bottom: none;border-top: none;margin-bottom:10px;"
        border="1">
        <tbody>
            <tr class="invoice_info_row invoice_header_row invoice-table-row">
                <td class="invoice_info_bg" style="width: 12%;">
                    Activity
                </td>
                <td class="invoice_info_bg" style="width: 10%;">
                    Date
                </td>
                <td class="invoice_info_bg" style="width: 33%;">
                    Payment Method
                </td>
                <td class="invoice_info_bg" style="width: 12%;">
                    Amount
                </td>
                <td class="invoice_info_bg" style="width: 15%;">
                    Responsible User
                </td>
                {{-- <td class="invoice_info_bg" style="width: 18%;">
                    Deposited Into
                </td> --}}
            </tr>
            <?php  foreach($valueData['InvoiceHistoryTransaction'] as $hKey=>$hVal){
                    if(in_array($hVal->acrtivity_title,["Payment Received","Payment Refund","Payment Pending"])){ ?>
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
                    <?php if(in_array($hVal->acrtivity_title,["Payment Received","Payment Pending"])){?>
                    ${{number_format($hVal->amount,2)}}
                    <?php }else if($hVal->acrtivity_title=="Payment Refund"){?>
                    (${{number_format($hVal->amount,2)}})
                    <?php } ?>
                </td>
                <td class="payment-history-column-user" style="vertical-align: top;">
                    {{substr($hVal->createdByUser->full_name,0,100)}}  
                    ({{$hVal->createdByUser->user_title}})
                </td>
                {{-- <td class="payment-history-column-deposited-into" style="vertical-align: top;">
                    <?php if($hVal->acrtivity_title=="Payment Received" && $hVal->pay_method != 'Non-Trust Credit Account'){
                        echo $hVal->deposit_into;
                    } ?>
                </td> --}}
            </tr>
            <?php } 
                } ?>
        </tbody>
    </table>
    <?php } ?>
    <br>
</div>
<?php } ?>
<style type="text/css" media="print">
    div.page {
        page-break-after: always;
        page-break-inside: avoid;
    }
</style>
