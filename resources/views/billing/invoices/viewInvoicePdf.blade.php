@extends('layouts.pdflayout')

<?php
$paid=$Invoice['amount_paid'];
$invoice=$Invoice['invoice_amount'];
$finalAmt=$invoice-$paid;
?>
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
                    {{ucfirst(substr($userData['last_name'],0,50))}}</b><br>
                {{($userData['street'])??''}} {{($userData['apt_unit'])??''}}<br>
                {{($userData['city'])??''}} {{($userData['state'])??''}} {{($userData['postal_code'])??''}}<br>
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
    <p>{{ucfirst(substr($caseMaster['case_title'],0,100))}}</p>
</h3>
<?php   $discount=$addition=$timeEntryTime=$timeEntryAmount=$expenseTime=$expenseAmount=0;?>

<?php
    if(!$FlatFeeEntryForInvoice->isEmpty()){?>
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
        $flatFeeEntryAmount=0;
        $nonBillDataFlateFee=[];
        foreach($FlatFeeEntryForInvoice as $k=>$v){
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
<?php } ?>
<br>
<?php
    if(!$TimeEntryForInvoice->isEmpty()){?>
<b>Time Entries</b>
<table style="width: 100%; border-collapse: collapse;" border="1">
    <tbody>
        <tr class="invoice_info_row">
            <td class="invoice_info_bg">
                Date
            </td>
            <td class="invoice_info_bg">
                EE
            </td>
            <td class="invoice_info_bg">
                Activity
            </td>
            <td class="invoice_info_bg">
                Description
            </td>
            <td class="invoice_info_bg" style="width: 65px; text-align: right;">
                Rate
            </td>
            <td class="invoice_info_bg" style="width: 65px; text-align: right;">
                Hours
            </td>
            <td class="invoice_info_bg" style="width: 140px; text-align: right;">
                Line Total
            </td>
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
                    <?php 
                        if($v->rate_type=="flat"){
                            echo "flat";
                        }else{
                            echo number_format($v->duration,1);
                        } ?>
                </td>
                <td style="vertical-align: top; text-align: right;" class="">
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
        <?php }else{
                $nonBillData[]=$v;
            }
        } ?>

        <?php
        if(!empty($nonBillData)){
            ?>
            <tr class="invoice_info_row nonbillable-title">
                <td class="invoice_info_bg" colspan="7">
                    Non-billable Time Entries:
                </td>
                </tr>
            <?php 
            foreach($nonBillData as $k=>$v){
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
                    <td style="vertical-align: top; text-align: right;" class="nonbillableRow" >
                        ${{number_format($v->entry_rate,2)}}
                    </td>
                    <td style="vertical-align: top; text-align: right;" class="nonbillableRow">
                        <?php 
                            if($v->rate_type=="flat"){
                                echo "flat";
                            }else{
                                echo number_format($v->duration,1);
                            } ?>
                    </td>
                    <td style="vertical-align: top; text-align: right;" class="nonbillableRow">
                        <?php
                            if($v->rate_type=="flat"){
                                $Total=$v->entry_rate;
                                
                            }else{
                                $Total= ($v->duration * $v->entry_rate);
                                
                            }
                            echo "$".number_format($Total,2);

                            ?>
                    </td>
                </tr>
                <?php
            }

        }?>
        <tr>
            <td colspan="5" class="total-summary-column" style="text-align:right;">
                Totals:
            </td>

            <td class="total-entries-total-hours total-data-column" style="text-align:right;">
                {{$timeEntryTime}}
            </td>

            <td class="total-data-column">
                ${{number_format($timeEntryAmount,2)}}
            </td>
        </tr>
    </tbody>
</table>
<br>
<?php } ?>
<?php 
    if(!$ExpenseForInvoice->isEmpty()){?>
<h3>Expenses</h3>
<table style="width: 100%; border-collapse: collapse;" border="1">
<tbody>
    <tr class="invoice_info_row">
        <td class="invoice_info_bg">
            Date
        </td>
        <td class="invoice_info_bg">
            EE
        </td>
        <td class="invoice_info_bg">
            Activity
        </td>
        <td class="invoice_info_bg">
            Description
        </td>
        <td class="invoice_info_bg" style="width: 65px; text-align: right;">
            Cost
        </td>
        <td class="invoice_info_bg" style="width: 65px; text-align: right;">
            Quantity
        </td>
        <td class="invoice_info_bg" style="width: 140px; text-align: right;">
            Line Total
        </td>
    </tr>
    <?php 
        $expenseTime=0;
        $expenseAmount=0;
        $expenseNonBill=[];
        foreach($ExpenseForInvoice as $k=>$v){
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
        <td style="vertical-align: top; text-align: right;" class="">
            ${{number_format($v->cost,2)}}
        </td>
        <td style="vertical-align: top; text-align: right;" class="">

            <?php 
                echo number_format($v->duration,1);?>
        </td>
        <td style="vertical-align: top; text-align: right;" class="">
            <?php
                    echo "$".$Total= ($v->duration * $v->cost);
                    $expenseAmount=$expenseAmount+$Total;
                ?>
        </td>
    </tr>
    <?php } else{
        $expenseNonBill[]=$v;
        }
        } ?>

    <?php
    if(!empty($expenseNonBill)){
        ?>
        <tr class="invoice_info_row nonbillable-title">
            <td class="invoice_info_bg" colspan="7">
                Non-billable Time Expenses:
            </td>
            </tr>
        <?php 
        foreach($expenseNonBill as $k=>$v){
            ?>
            <tr class="invoice_info_row ">
                <td class="time-entry-date" style="vertical-align: top;">
                    {{date('m/d/Y',strtotime($v->entry_date))}}
                </td>
                <td class="time-entry-ee" style="vertical-align: top;">
                    {{($v->first_name[0])??''}}{{($v->last_name[0])??''}}
                </td>
                <td class="time-entry-activity" style="vertical-align: top;">
                    {{$v->activity_title}}
                </td>
                <td class="time-entry-description" style="vertical-align: top;">
                    <p class="invoice_notes">
                        {{$v->description}}
                    </p>
                </td>
                <td style="vertical-align: top; text-align: right;" class="nonbillableRow" >
                    ${{number_format($v->entry_rate,2)}}
                </td>
                <td style="vertical-align: top; text-align: right;" class="nonbillableRow">
                    <?php 
                        if($v->rate_type=="flat"){
                            echo "flat";
                        }else{
                            echo number_format($v->duration,1);
                        } ?>
                </td>
                <td style="vertical-align: top; text-align: right;" class="nonbillableRow">
                    <?php
                        if($v->rate_type=="flat"){
                            $Total=$v->entry_rate;
                            
                        }else{
                            $Total= ($v->duration * $v->entry_rate);
                            
                        }
                        echo "$".number_format($Total,2);

                        ?>
                </td>
            </tr>
            <?php
        }

    }?>
    <tr>
        <td colspan="6" style="text-align: right; padding-top: 5px;text-align:right;">
            Expense Total:
        </td>
        <td
            style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;text-align:right;">
            ${{number_format($expenseAmount,2)}}
        </td>
    </tr>
</tbody>
</table>
<br>
<?php } ?>
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
                            $items=array("discount"=>"Discount","intrest"=>"Intrest","tax"=>"Tax","addition"=>"Addition");
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
                Flat Fee Sub-Total:<br>
                Time Entry Sub-Total:<br>
                Expense Sub-Total:<br>
                <span style="font-weight: bold;">Sub-Total:</span><br>
                <br>
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
                ${{number_format($flatFeeEntryAmount,2)}}<br>
                ${{number_format($timeEntryAmount,2)}}<br>
                ${{number_format($expenseAmount,2)}}<br>
                ${{number_format($timeEntryAmount+$expenseAmount+$flatFeeEntryAmount,2)}}<br>
                <br>

                <?php if($discount!="0"){?>
                ${{number_format($discount,2)}}<br>
                <?php } ?>

                <?php if($addition!="0"){?>
                ${{number_format($addition,2)}}<br>
                <?php } ?>
                <br>
                ${{number_format($timeEntryAmount+$expenseAmount+$flatFeeEntryAmount-$discount+$addition,2)}}<br>
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
                ${{number_format($timeEntryAmount+$expenseAmount+$flatFeeEntryAmount-$discount+$addition,2)}}
            </td>
        </tr>

    </tbody>
</table>
<br>

<?php if(isset($InvoiceInstallment) && !$InvoiceInstallment->isEmpty()){?>
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
<?php } ?>
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
            <td class="invoice_info_bg" style="width: 18%;">
                Deposited Into
            </td>
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
                <?php if($hVal->acrtivity_title=="Payment Received"){
                        echo $hVal->deposit_into;
                    } ?>
            </td>
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