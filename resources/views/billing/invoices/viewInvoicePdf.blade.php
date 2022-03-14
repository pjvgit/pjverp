@extends('layouts.pdflayout')
@if (isset($invoiceSetting) && !empty($invoiceSetting) && $invoiceSetting['invoice_theme'] == "modern")
<div class="invoice-modern-theme">
<style>
@media only screen and (max-width: 320px) {
.invoice-modern-theme .line-items-table {
    margin-left: 10px;
    margin-right: 10px;
    }
}
.invoice-modern-theme {
    padding: 15px;
}

.invoice-modern-theme tr.invoice_info_row td {
    border: 0;
    border-bottom: 1px solid #ababab;
    padding: 5px;
}

.invoice-modern-theme .invoice_info_bg {
    background-color: #fff;
    font-weight: 700;
}

.invoice-modern-theme tr.invoice_info_row td {
    border: 0;
    border-bottom: 1px solid #ababab;
    padding: 5px;
}

.invoice-modern-theme .line-items-table {
    margin-left: 30px;
    margin-right: 30px;
}

.invoice-modern-theme h3 {
    margin-bottom: 5px;
}

.invoice-modern-theme h3 {
    margin-bottom: 5px;
}

.invoice-modern-theme .line-items-table .invoice_info_bg {
    background-color: #fff;
    font-size: 12.8px;
    font-weight: 400;
    text-transform: uppercase;
}

.invoice-modern-theme tr.invoice_info_row td {
    border: 0;
    border-bottom: 1px solid #ababab;
    padding: 5px;
}

.invoice-modern-theme .line-items-table {
    margin-left: 30px;
    margin-right: 30px;
}

.invoice-modern-theme .invoice-summary-section {
    margin-top: 30px;
}

.invoice-modern-theme .invoice-summary-section .notes {
    vertical-align: text-top;
    width: 75%;
}

.invoice-modern-theme .totals-table .invoice_info_row td {
    padding-left: 15px;
    padding-right: 15px;
}

.invoice-modern-theme tr.invoice_info_row td.totals-border-top {
    border-top: 8px solid #ababab;
}

.invoice-modern-theme tr.invoice_info_row td {
    border: 0;
    border-bottom: 1px solid #ababab;
    padding: 5px;
}

tr.invoice_info_row td {
    border: 1px solid #000;
    padding: 5px;
    word-wrap: break-word;
}

.invoice-modern-theme .totals-table .invoice_info_row .balance-due-wrapper {
    padding-left: 0;
    padding-right: 0;
}

.invoice-modern-theme tr.invoice_info_row td.totals-border-bot {
    border-bottom: 8px solid #ababab;
}

.invoice-modern-theme .line-items-table .invoice_info_bg {
    background-color: #fff;
    font-size: 12.8px;
    font-weight: 400;
    text-transform: uppercase;
}

.invoice-modern-theme tr.invoice_info_row td {
    border: 0;
    border-bottom: 1px solid #ababab;
    padding: 5px;
}

.invoice-modern-theme .totals-table .balance-due-box {
    background-color: #eceeef;
    padding: 8px 15px;
}

.invoice-modern-theme .line-items-table {
    margin-left: 30px;
    margin-right: 30px;
}

.invoice-modern-theme .payment-section {
    margin-top: 25px;
}

.invoice-modern-theme .payment-section {
    margin-top: 25px;
}

.invoice-modern-theme .line-items-table {
    margin-left: 30px;
    margin-right: 30px;
}

</style>
@else 
<div id="preview_page">  
@endif
<?php
// $paid=$Invoice['amount_paid'];
// $invoice=$Invoice['invoice_amount'];
$invoice=$Invoice['total_amount'];
$paid=$Invoice['paid_amount'];
$finalAmt=$invoice-$paid;
$flatFeeEntryAmount=$forwardedInvoices=$discount=$addition=$timeEntryTime=$timeEntryAmount=$expenseTime=$expenseAmount=$totalFwdAmt = 0;
?>
<table style="width: 100%; margin: 0; padding: 0; table-layout: fixed;" class="invoice">
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
<table style="width: 100%; margin: 0; padding: 0; table-layout: fixed;" class="invoice">
    <tbody>
        <tr>
            <td style="width: 60%;"><b>
                    @if(!empty($case_client_company))
                    {{$case_client_company->first_name}} {{$case_client_company->middle_name}} {{$case_client_company->last_name}} </br>
                    @endif
                    {{ucfirst(substr($userData['first_name'],0,50))}}
                    {{ucfirst(substr($userData['middle_name'],0,50))}}
                    {{ucfirst(substr($userData['last_name'],0,50))}}</b><br>

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
                                {{sprintf('%06d', $Invoice['id'])}}</td>
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
    <p>{{ucfirst(substr(@$caseMaster['case_title'],0,100))}} 
    @if(isset($invoiceSetting) && !empty($invoiceSetting) && $invoiceSetting['show_case_no_after_case_name'] == "yes")
        ({{ $caseMaster->case_number }})
    @endif
    </p>
</h3>
<?php if(isset($FlatFeeEntryForInvoice) && !$FlatFeeEntryForInvoice->isEmpty()){?>
@if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']))
<b>Flat Fee</b>
@endif
<table style="width: 100%; border-collapse: collapse;font-size: 12px;" border="1">
    <tbody>
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']))
        <tr class="invoice_info_row">
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("date", $invoiceSetting['flat_fee']))
            <td class="invoice_info_bg">
                Date
            </td>
            @endif
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("employee", $invoiceSetting['flat_fee']))
            <td class="invoice_info_bg">
                EE
            </td>
            @endif
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("item", $invoiceSetting['flat_fee']))
            <td class="invoice_info_bg">
                Item
            </td>
            @endif
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("notes", $invoiceSetting['flat_fee']))
            <td class="invoice_info_bg">
                Description
            </td>
            @endif
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("amount", $invoiceSetting['flat_fee']))
            <td class="invoice_info_bg" style=" text-align: right;width:10%;">
                Amount
            </td>
            @endif
        </tr>
        @endif
        <?php
       
        $nonBillDataFlateFee=[];
        foreach($FlatFeeEntryForInvoice as $k=>$v){
            if($v->time_entry_billable=="yes"){
                ?>
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']))
            <tr class="invoice_info_row ">
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("date", $invoiceSetting['flat_fee']))
                <td class="time-entry-date" style="vertical-align: top;">
                    {{date('m/d/Y',strtotime($v->entry_date))}}
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("employee", $invoiceSetting['flat_fee']))
                <td class="time-entry-ee" style="vertical-align: top;">
                    {{$v->first_name[0]}}{{$v->last_name[0]}}
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("item", $invoiceSetting['flat_fee']))
                <td class="time-entry-activity" style="vertical-align: top;">
                    Flat Fee
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("notes", $invoiceSetting['flat_fee']))
                <td class="time-entry-description" style="vertical-align: top;">
                    <p class="invoice_notes">
                        {{$v->description}}
                    </p>
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("amount", $invoiceSetting['flat_fee']))
                <td style="vertical-align: top; text-align: right;" class="" >
                    ${{number_format($v->cost,2)}}
                </td>
                @endif
            </tr>
            @endif
        <?php 
        $flatFeeEntryAmount+=$v->cost;
        }else{
                $nonBillDataFlateFee[]=$v;
            }
        } ?>

        <?php
        if(!empty($nonBillDataFlateFee)){
            ?>
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']))
            <tr class="invoice_info_row nonbillable-title">
                <td class="invoice_info_bg" colspan="7">
                Non-billable Flat Fees:
                </td>
            </tr>
            <?php 
            foreach($nonBillDataFlateFee as $k=>$v){
                ?>
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']))
                <tr class="invoice_info_row ">
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("date", $invoiceSetting['flat_fee']))
                    <td class="time-entry-date" style="vertical-align: top;">
                        {{date('m/d/Y',strtotime($v->entry_date))}}
                    </td>
                    @endif
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("employee", $invoiceSetting['flat_fee']))
                    <td class="time-entry-ee" style="vertical-align: top;">
                        {{$v->first_name[0]}}{{$v->last_name[0]}}
                    </td>
                    @endif
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("item", $invoiceSetting['flat_fee']))
                    <td class="time-entry-activity" style="vertical-align: top;">
                        Flat Fee
                    </td>
                    @endif
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("notes", $invoiceSetting['flat_fee']))
                    <td class="time-entry-description" style="vertical-align: top;">
                        <p class="invoice_notes">
                            {{$v->description}}
                        </p>
                    </td>
                    @endif
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("amount", $invoiceSetting['flat_fee']))
                    <td style="vertical-align: top; text-align: right;" class="nonbillableRow" >
                        ${{number_format($v->cost,2)}}
                    </td>                                   
                    @endif                    
                </tr>
                @endif
                <?php
            }?>
            @endif
        <?php } ?>
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']))
        <tr>
            <td colspan="{{ (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && $invoiceSetting['flat_fee']) ? count($invoiceSetting['flat_fee']) - ( in_array('amount', $invoiceSetting['flat_fee']) ? 1 : 0 ) :'3' }}" class="total-summary-column" style="text-align: right;">
                Flat Fee Total:
            </td>
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']) && in_array("amount", $invoiceSetting['flat_fee']))
            <td class="total-data-column"  style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;"> 
                    ${{number_format($flatFeeEntryAmount,2)}}
            </td>
            @endif
        </tr>
        @endif
    </tbody>
</table>
@if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['flat_fee']))
<br>
@endif
<?php } ?>
<?php if(!$TimeEntryForInvoice->isEmpty()){?>
@if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']))
<b>Time Entries</b>
@endif
<table style="width: 100%; border-collapse: collapse;" border="1">
    <tbody>
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']))
        <tr class="invoice_info_row">
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("date", $invoiceSetting['time_entry']))
            <td class="invoice_info_bg">
                Date
            </td>
            @endif
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("employee", $invoiceSetting['time_entry']))
            <td class="invoice_info_bg">
                EE
            </td>
            @endif
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("activity", $invoiceSetting['time_entry']))
            <td class="invoice_info_bg">
                Activity
            </td>
            @endif
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("notes", $invoiceSetting['time_entry']))
            <td class="invoice_info_bg">
                Description
            </td>
            @endif
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("amount", $invoiceSetting['time_entry']))
            <td class="invoice_info_bg" style="width: 65px; text-align: right;">
                Rate
            </td>
            @endif
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("hour", $invoiceSetting['time_entry']))
            <td class="invoice_info_bg" style="width: 65px; text-align: right;">
                Hours
            </td>
            @endif
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("line_total", $invoiceSetting['time_entry']))
            <td class="invoice_info_bg" style="width: 140px; text-align: right;">
                Line Total
            </td>
            @endif
        </tr>
        @endif
        <?php
        $nonBillData=[];
        foreach($TimeEntryForInvoice as $k=>$v){
            if($v->time_entry_billable=="yes"){
                ?>
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']))
            <tr class="invoice_info_row ">
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("date", $invoiceSetting['time_entry']))
                <td class="time-entry-date" style="vertical-align: top;">
                    {{date('m/d/Y',strtotime($v->entry_date))}}
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("employee", $invoiceSetting['time_entry']))
                <td class="time-entry-ee" style="vertical-align: top;">
                    {{@$v->first_name[0]}}{{@$v->last_name[0]}}
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("activity", $invoiceSetting['time_entry']))
                <td class="time-entry-activity" style="vertical-align: top;">
                    {{$v->activity_title}}
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("notes", $invoiceSetting['time_entry']))
                <td class="time-entry-description" style="vertical-align: top;">
                    <p class="invoice_notes">
                        {{$v->description}}
                    </p>
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("amount", $invoiceSetting['time_entry']))
                <td style="vertical-align: top; text-align: right;" class="" >
                    ${{number_format($v->entry_rate,2)}}
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("hour", $invoiceSetting['time_entry']))
                <td style="vertical-align: top; text-align: right;" class="">
                    <?php 
                        if($v->rate_type=="flat"){
                            echo "flat";
                        }else{
                            echo number_format($v->duration, getInvoiceSetting()->time_entry_hours_decimal_point ?? 1);
                        } ?>
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("line_total", $invoiceSetting['time_entry']))
                <td style="vertical-align: top; text-align: right;" class="">
                    <?php
                        if($v->rate_type=="flat"){
                            $Total=$v->entry_rate;
                            // $timeEntryAmount=$timeEntryAmount+$v->entry_rate;
                        }else{
                            $Total= ($v->duration * $v->entry_rate);
                            // $timeEntryAmount=$timeEntryAmount+$Total;
                            
                            // $timeEntryTime=$timeEntryTime+$v->duration;
                        }
                        echo "$".number_format($Total,2);

                        ?>
                </td>
                @endif                            
            </tr>
            @endif
            <?php
                if($v->rate_type=="flat"){
                    $timeEntryAmount=$timeEntryAmount+$v->entry_rate;
                }else{
                    $timeEntryAmount=$timeEntryAmount+($v->duration * $v->entry_rate);
                    $timeEntryTime=$timeEntryTime+$v->duration;
                }
            ?>
        <?php }else{
                $nonBillData[]=$v;
            }
        } ?>

        <?php
        if(!empty($nonBillData)){
            ?>
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']))
            <tr class="invoice_info_row nonbillable-title">
                <td class="invoice_info_bg" colspan="7">
                    Non-billable Time Entries:
                </td>
            </tr>
            @endif
            <?php 
            foreach($nonBillData as $k=>$v){
                ?>
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']))
                <tr class="invoice_info_row ">
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("date", $invoiceSetting['time_entry']))
                    <td class="time-entry-date" style="vertical-align: top;">
                        {{date('m/d/Y',strtotime($v->entry_date))}}
                    </td>
                    @endif
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("employee", $invoiceSetting['time_entry']))
                    <td class="time-entry-ee" style="vertical-align: top;">
                        {{$v->first_name[0]}}{{$v->last_name[0]}}
                    </td>
                    @endif
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("activity", $invoiceSetting['time_entry']))
                    <td class="time-entry-activity" style="vertical-align: top;">
                        {{$v->activity_title}}
                    </td>
                    @endif
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("notes", $invoiceSetting['time_entry']))
                    <td class="time-entry-description" style="vertical-align: top;">
                        <p class="invoice_notes">
                            {{$v->description}}
                        </p>
                    </td>
                    @endif
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("amount", $invoiceSetting['time_entry']))
                    <td style="vertical-align: top; text-align: right;" class="nonbillableRow" >
                        ${{number_format($v->entry_rate,2)}}
                    </td>
                    @endif
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("hour", $invoiceSetting['time_entry']))
                    <td style="vertical-align: top; text-align: right;" class="nonbillableRow">
                        <?php 
                            if($v->rate_type=="flat"){
                                echo "flat";
                            }else{
                                echo number_format($v->duration,1);
                            } ?>
                    </td>
                    @endif
                    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("line_total", $invoiceSetting['time_entry']))
                    <td style="vertical-align: top; text-align: right;" class="nonbillableRow">
                        <?php
                            if($v->rate_type=="flat"){
                                $Total= $v->entry_rate;
                                
                            }else{
                                $Total=  str_replace(",","",number_format($v->duration * $v->entry_rate,2));
                                
                            }
                            echo "$".number_format($Total,2);

                            ?>
                    </td>
                    @endif
                </tr>
                @endif
                <?php
            }
        }?>
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']))
        <tr>
            <td colspan="{{ (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) &&  $invoiceSetting['time_entry']) ? count($invoiceSetting['time_entry']) - 2 : '5' }}" class="total-summary-column" style="text-align: right;">
            Totals
            </td>                       
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("hour", $invoiceSetting['time_entry']))
            <td class="total-entries-total-hours total-data-column" style="text-align: right; font-weight: bold;">
                    {{$timeEntryTime}}
            </td>
            @endif
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']) && in_array("line_total", $invoiceSetting['time_entry']))
            <td class="total-data-column" style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;">
                    ${{number_format($timeEntryAmount,2)}}
            </td>
            @endif
        </tr>
        @endif
    </tbody>
</table>
@if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['time_entry']))
<br>
@endif
<?php } ?>
<?php  if(!$ExpenseForInvoice->isEmpty()){?>
@if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']))
<h3>Expenses</h3>
@endif
<table style="width: 100%; border-collapse: collapse;" border="1">
<tbody>
    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']))
    <tr class="invoice_info_row">
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("date", $invoiceSetting['expense']))
        <td class="invoice_info_bg">
            Date
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("employee", $invoiceSetting['expense']))
        <td class="invoice_info_bg">
            EE
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("expense", $invoiceSetting['expense']))
        <td class="invoice_info_bg">
            Activity
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("notes", $invoiceSetting['expense']))
        <td class="invoice_info_bg">
            Description
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("amount", $invoiceSetting['expense']))
        <td class="invoice_info_bg" style="width: 65px; text-align: right;">
            Cost
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("quantity", $invoiceSetting['expense']))
        <td class="invoice_info_bg" style="width: 65px; text-align: right;">
            Quantity
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("line_total", $invoiceSetting['expense']))
        <td class="invoice_info_bg" style="width: 140px; text-align: right;">
            Line Total
        </td>
        @endif
    </tr>
    @endif
    <?php 
        $expenseTime=0;
        $expenseAmount=0;
        $expenseNonBill=[];
        foreach($ExpenseForInvoice as $k=>$v){
            if($v->time_entry_billable=="yes"){
                ?>
    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense'])) 
    <tr class="invoice_info_row ">
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("date", $invoiceSetting['expense']))
        <td class="time-entry-date" style="vertical-align: top;">
            {{date('m/d/Y',strtotime($v->entry_date))}}
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("employee", $invoiceSetting['expense']))
        <td class="time-entry-ee" style="vertical-align: top;">
            {{$v->first_name[0]}}{{$v->last_name[0]}}
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("expense", $invoiceSetting['expense']))
        <td class="time-entry-activity" style="vertical-align: top;">
            {{$v->activity_title}}
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("notes", $invoiceSetting['expense']))
        <td class="time-entry-description" style="vertical-align: top;">
            <p class="invoice_notes">
                {{$v->description}}
            </p>
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("amount", $invoiceSetting['expense']))
        <td style="vertical-align: top; text-align: right;" class="">
            ${{number_format($v->cost,2)}}
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("quantity", $invoiceSetting['expense']))
        <td style="vertical-align: top; text-align: right;" class="">

            <?php 
                echo number_format($v->duration,1);?>
        </td>
        @endif
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("line_total", $invoiceSetting['expense']))
        <td style="vertical-align: top; text-align: right;" class="">
            <?php
                    echo "$".$Total=  str_replace(",","",number_format($v->duration * $v->cost,2));
                ?>
        </td>
        @endif
    </tr>
    @endif
    <?php
    $expenseAmount=$expenseAmount+(str_replace(",","",number_format($v->duration * $v->cost,2)));
     } else{
        $expenseNonBill[]=$v;
        }
        } ?>

    <?php
    if(!empty($expenseNonBill)){
        ?>
        @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']))
        <tr class="invoice_info_row nonbillable-title">
            <td class="invoice_info_bg" colspan="7">
                Non-billable Expenses:
            </td>
        </tr>
        @endif
        <?php 
        foreach($expenseNonBill as $k=>$v){
            ?>
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']))
            <tr class="invoice_info_row ">
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("date", $invoiceSetting['expense']))
                <td class="time-entry-date" style="vertical-align: top;">
                    {{date('m/d/Y',strtotime($v->entry_date))}}
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("employee", $invoiceSetting['expense']))
                <td class="time-entry-ee" style="vertical-align: top;">
                    {{($v->first_name[0])??''}}{{($v->last_name[0])??''}}
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("expense", $invoiceSetting['expense']))
                <td class="time-entry-activity" style="vertical-align: top;">
                    {{$v->activity_title}}
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("notes", $invoiceSetting['expense']))
                <td class="time-entry-description" style="vertical-align: top;">
                    <p class="invoice_notes">
                        {{$v->description}}
                    </p>
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("amount", $invoiceSetting['expense']))
                <td style="vertical-align: top; text-align: right;" class="nonbillableRow" >
                    ${{number_format($v->cost,2)}}
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("quantity", $invoiceSetting['expense']))
                <td style="vertical-align: top; text-align: right;" class="nonbillableRow">
                    <?php 
                        if($v->rate_type=="flat"){
                            echo "flat";
                        }else{
                            echo str_replace(",","",number_format($v->duration,1));
                        } ?>
                </td>
                @endif
                @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("line_total", $invoiceSetting['expense']))
                <td style="vertical-align: top; text-align: right;" class="nonbillableRow">
                    <?php
                        if($v->rate_type=="flat"){
                            $Total=$v->cost;
                            
                        }else{
                            $Total=  str_replace(",","",number_format($v->duration * $v->cost,2));
                            
                        }
                        echo "$".number_format($Total,2);

                        ?>
                </td>
                @endif
            </tr>
            @endif
            <?php
        }

    }?>
    @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']))
    <tr>
        <td colspan="{{ (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && $invoiceSetting['expense']) ? count($invoiceSetting['expense']) - 1 : '6' }}" style="text-align: right; padding-top: 5px;">
            Expense Total:
        </td>
        <td style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;">
            @if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']) && in_array("line_total", $invoiceSetting['expense']))
            ${{number_format($expenseAmount,2)}}
            @endif
        </td>
    </tr>
    @endif
</tbody>
</table>
@if (isset($invoiceSetting) && !empty($invoiceSetting) && isset($invoiceSetting['expense']))
<br>
@endif
<?php } ?>
</div>
@if (count($Invoice->forwardedInvoices))
<h3>Unpaid Invoice Balance Forward</h3>
<table style="width: 100%; border-collapse: collapse;font-size: 12px;" border="1">
    <tbody>
        <tr class="invoice_info_row">
            <td class="invoice_info_bg">Invoice #</td>
            <td class="invoice_info_bg">Invoice Total</td>
            <td class="invoice_info_bg">Amount Paid</td>
            <td class="invoice_info_bg">Due Date</td>
            <td class="invoice_info_bg" style=" text-align: right;width:10%;">Balance Forward</td>
        </tr>
        @forelse ($Invoice->forwardedInvoices as $invkey => $invitem)
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
                $totalFwdAmt = $Invoice->forwardedInvoices->sum('due_amount');
            @endphp
            <td class="total-data-column" style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;"> ${{ number_format($totalFwdAmt, 2) }}</td>
        </tr>
    </tbody>
</table>
<br>
@endif
<?php 
    if(!$InvoiceAdjustment->isEmpty()){?>
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
            <?php if($Invoice->terms_condition!=""){?>
            <td style="width: 580px; vertical-align: top;">
                <div style="font-weight: bold;">Terms & Conditions:</div> <br>
                <p class="invoice_notes">{{$Invoice->terms_condition}} </p>
            </td>
            <?php } else { 
                ?>
                <td style="border: none;">
                    &nbsp;
                </td>
            <?php }?>
                
            <td style="width: 10px; border: none;" rowspan="1">
                &nbsp;
            </td>
            <td style="text-align: right; border-right: none; vertical-align: top; width: 140px; line-height: 1.8;"
                rowspan="1">
                <?php if(isset($FlatFeeEntryForInvoice) && !$FlatFeeEntryForInvoice->isEmpty()){ ?>
                Flat Fee Sub-Total:<br>
                <?php } ?>
                Time Entry Sub-Total:<br>
                Expense Sub-Total:<br>
                <span style="font-weight: bold;">Sub-Total:</span><br>
                <br>
                <?php  if (count($Invoice->forwardedInvoices)){ ?>
                <span>Balance Forward:</span>
                <br>
                <?php } ?>
                <?php if($discount!="0"){?>
                <span>Discounts:</span><br>
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
                <?php if(isset($FlatFeeEntryForInvoice) && !$FlatFeeEntryForInvoice->isEmpty()){ ?>
                ${{number_format($flatFeeEntryAmount,2)}}<br>
                <?php } ?>
                ${{number_format($timeEntryAmount,2)}}<br>
                ${{number_format($expenseAmount,2)}}<br>
                ${{number_format($timeEntryAmount+$expenseAmount+$flatFeeEntryAmount,2)}}<br>
                <br>
                <?php  
                if (count($Invoice->forwardedInvoices)){
                    $forwardedInvoices = $Invoice->forwardedInvoices->sum('due_amount'); ?>
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
                ${{number_format($Invoice['paid_amount'],2)}}
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
                <?php 
                    $F = str_replace(",","",number_format($timeEntryAmount+$expenseAmount+$flatFeeEntryAmount+$addition+$forwardedInvoices-($discount+$Invoice['paid_amount']),2));
                    if($F<=0){
                        $fAmt=0;
                    }else{
                        $fAmt=$F;
                    }
                ?>
                ${{number_format($fAmt,2)}}
            </td>
        </tr>

    </tbody>
</table>
<br>

@if(!empty($InvoiceInstallment) && count($InvoiceInstallment))
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
                                                style="padding: 5px;text-align:left;font-size: 12px;white-space: nowrap;">Installment
                                                Due</th>
                                                <th class="invoice_info_bg status" style="padding: 5px;"></th>
                                                <th class="invoice_info_bg adjustment" style="padding: 5px;">Adjustment</th>
                                            <th class="invoice_info_bg amount_due"
                                                style="padding: 5px;text-align:right;font-size: 12px;white-space: nowrap;">Amount</th>
                                        </tr>
                                        <?php    foreach($InvoiceInstallment as $lk=>$lv){ ?>
                                        <tr class="even  ">
                                            <td style="border: none; border-bottom: 1px solid #cccccc;font-size: 12px;"
                                                class="nonbillable js-payment_plan_date">
                                                {{date('M d,Y',strtotime($lv->due_date))}}
                                            </td>
                                            <td style="border: none; border-bottom: 1px solid #cccccc;white-space: nowrap;" class="nonbillable js-payment_plan_status">
                                                <?php if($lv->status=="paid"){?>
                                                    <i class="fas fa-check-circle" style="color: #40BC53"></i>&nbsp;&nbsp;Manual payment successful
                                                <?php } ?>
                                              </td>
                                              <td style="border: none; border-bottom: 1px solid #cccccc;white-space: nowrap;" class="nonbillable js-payment_plan_adjustment">
                                                <?php if($lv->status=="unpaid" && $lv->adjustment!=0.00 ){?>
                                                    (${{$lv->adjustment  }}) prepayment
                                                    &nbsp;&nbsp; (${{$lv->installment_amount}} - ${{$lv->adjustment}})

                                                <?php } ?>
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
@endif
<?php if(isset($InvoiceHistoryTransaction) && !$InvoiceHistoryTransaction->isEmpty()){?>
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
                @if($hVal->acrtivity_title=="Payment Received" && in_array($hVal->payment_from, ['online','client_online']))
                    Operating Account
                @elseif($hVal->acrtivity_title=="Payment Received" && $hVal->pay_method != 'Non-Trust Credit Account')
                    {{ $hVal->deposit_into }}
                @endif
            </td> --}}
        </tr>
        <?php } 
                } ?>
    </tbody>
</table>

<?php } ?>
&nbsp;
&nbsp;
&nbsp;
<br>

@if(!empty($Invoice->invoice_setting))
    @if(!empty($Invoice->applyTrustCreditFund))
    <div>
        @include('billing.invoices.partials.load_invoice_account_summary_pdf')
    </div>
    @endif
@endif