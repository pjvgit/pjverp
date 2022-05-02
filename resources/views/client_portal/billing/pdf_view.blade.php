@extends('layouts.pdflayout')
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
                                ${{ $invoice->due_amount_new }}
                            </td>
                        </tr>
                        <tr style="padding-left: 4px;">
                            <td scope="col" style="width: 10%;text-align:right;"><b>Invoice #:</b></td>
                            <td scope="col" style="width: 10%;text-align:left;">
                                {{ $invoice->invoice_id }}
                            </td>
                        </tr>


                        <tr style="padding-left: 4px;">
                            <td scope="col" style="width: 10%;text-align:right;"><b>Invoice Date :</b></td>
                            <td scope="col" style="width: 10%;text-align:left;">
                                {{date("F d,Y",strtotime($invoice->invoice_date))}}</td>
                        </tr>
                        <tr style="padding-left: 4px;">
                            <td scope="col" style="width: 10%;text-align:right;"><b>Payment Terms:</b></td>
                            <td scope="col" style="width: 10%;text-align:left;">
                                @if(!empty($invoice->invoiceFirstInstallment))
                                @else
                                <?php
                                    $items=array("0"=>"Due date","1"=>"Due on receipt","2"=>"Net 15","3"=>"Net 30","4"=>"Net 60","5"=>"");
                                    ?>
                                <?php echo $items[$invoice->payment_term]; ?>
                                @endif
                            </td>
                        </tr>
                        <tr style="padding-left: 4px;">
                            <td scope="col" style="width: 10%;text-align:right;"><b>Due Date:</b></td>
                            <td scope="col" style="width: 10%;text-align:left;">
                                @if(!empty($invoice->invoiceFirstInstallment))
                                    See Payment Plan
                                @else
                                <?php 
                                    if($invoice->due_date!=NULL){?>
                                {{date("F d,Y",strtotime($invoice->due_date))}}
                                <?php } ?>
                                @endif
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
    <p>{{ucfirst(substr(@$caseMaster['case_title'],0,100))}}</p>
</h3>
@php $totalDiscount=$additionalTotal=$timeEntryTime=$timeEntryAmount=$expenseTime=$expenseAmount=0; @endphp

{{-- For flat fee entry --}}
@if(!empty($invoice->invoiceFlatFeeEntry) && count($invoice->invoiceFlatFeeEntry))
<b>Flat Fee</b>
<table style="width: 100%; border-collapse: collapse;font-size: 12px;" border="1">
    <tbody>
        <tr class="invoice_info_row">
            <td class="invoice_info_bg">Date</td>
            <td class="invoice_info_bg">EE</td>
            <td class="invoice_info_bg">Item</td>
            <td class="invoice_info_bg">Description</td>
            <td class="invoice_info_bg" style=" text-align: right;width:10%;">Amount</td>
        </tr>
        @php
        $flatFeeEntryAmount = $invoice->invoiceFlatFeeEntry->where("time_entry_billable", "yes")->sum('cost');
        $billableFlatFees = $invoice->invoiceFlatFeeEntry->where("time_entry_billable", "yes");
        $nonBillableFlatFees = $invoice->invoiceFlatFeeEntry->where("time_entry_billable", "no");
        @endphp
        @if(!empty($billableFlatFees) && count($billableFlatFees))
        @forelse($billableFlatFees as $key => $item)
            <tr class="invoice_info_row ">
                <td class="time-entry-date" style="vertical-align: top;">
                    {{date('m/d/Y',strtotime($item->entry_date))}}
                </td>
                <td class="time-entry-ee" style="vertical-align: top;">
                    {{ @$item->user->first_name[0] }}{{ @$item->user->last_name[0] }}
                </td>
                <td class="time-entry-activity" style="vertical-align: top;">
                    Flat Fee
                </td>
                <td class="time-entry-description" style="vertical-align: top;">
                    <p class="invoice_notes">
                        {{$item->description}}
                    </p>
                </td>
                <td style="vertical-align: top; text-align: right;" class="" >
                    ${{number_format($item->cost,2)}}
                </td>
            </tr>
        @empty
        @endforelse
        @endif
        @if(!empty($nonBillDataFlateFee) && count($nonBillDataFlateFee))
            <tr class="invoice_info_row nonbillable-title">
                <td class="invoice_info_bg" colspan="7">Non-billable Flat Fees:</td>
            </tr>
            @forelse($nonBillDataFlateFee as $k=>$v)
                <tr class="invoice_info_row ">
                    <td class="time-entry-date" style="vertical-align: top;">
                        {{date('m/d/Y',strtotime($v->entry_date))}}
                    </td>
                    <td class="time-entry-ee" style="vertical-align: top;">
                        {{ @$v->user->first_name[0] }}{{ @$v->user->last_name[0] }}
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
            @empty
            @endforelse
        @endif
        <tr>
            <td colspan="4" class="total-summary-column" style="text-align:right;">Flat Fee Total:</td>
            <td class="total-data-column" style="text-align:right;"> ${{number_format($flatFeeEntryAmount,2)}}</td>
        </tr>
    </tbody>
</table>
<br>
@endif

{{-- For time entry --}}
@if(!empty($invoice->invoiceTimeEntry) && count($invoice->invoiceTimeEntry))
<b>Time Entries</b>
<table style="width: 100%; border-collapse: collapse;" border="1">
    <tbody>
        <tr class="invoice_info_row">
            <td class="invoice_info_bg">Date</td>
            <td class="invoice_info_bg">EE</td>
            <td class="invoice_info_bg">Activity</td>
            <td class="invoice_info_bg">Description</td>
            <td class="invoice_info_bg" style="width: 65px; text-align: right;">Rate</td>
            <td class="invoice_info_bg" style="width: 65px; text-align: right;">Hours</td>
            <td class="invoice_info_bg" style="width: 140px; text-align: right;">Line Total</td>
        </tr>
        @php
            $timeEntryAmount = $invoice->invoiceTimeEntry->where("time_entry_billable", "yes")->sum('calculated_amt');
            $billableTimeEntry = $invoice->invoiceTimeEntry->where("time_entry_billable", "yes");
            $nonBillableTimeEntry = $invoice->invoiceTimeEntry->where("time_entry_billable", "no");
            $timeEntryTime = $invoice->invoiceTimeEntry->where('rate_type', 'hr')->sum('duration');
        @endphp
        @if(!empty($billableTimeEntry) && count($billableTimeEntry))
        @forelse($billableTimeEntry as $k=>$v)
            <tr class="invoice_info_row ">
                <td class="time-entry-date" style="vertical-align: top;">
                    {{date('m/d/Y',strtotime($v->entry_date))}}
                </td>
                <td class="time-entry-ee" style="vertical-align: top;">
                    {{ @$v->user->full_name }}
                </td>
                <td class="time-entry-activity" style="vertical-align: top;">
                    {{ @$v->taskActivity->title }}
                </td>
                <td class="time-entry-description" style="vertical-align: top;">
                    <p class="invoice_notes">
                        {{ $v->description }}
                    </p>
                </td>
                <td style="vertical-align: top; text-align: right;" class="" >
                    ${{ number_format($v->entry_rate,2) }}
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
                    {{ $v->calculated_amt }}
                </td>
            </tr>
        @empty
        @endforelse
        @endif
        @if(!empty($nonBillableTimeEntry) && count($nonBillableTimeEntry))
            <tr class="invoice_info_row nonbillable-title">
                <td class="invoice_info_bg" colspan="7">Non-billable Time Entries:</td>
            </tr>
            @forelse($nonBillableTimeEntry as $k=>$v)
                <tr class="invoice_info_row ">
                    <td class="time-entry-date" style="vertical-align: top;">
                        {{date('m/d/Y',strtotime($v->entry_date))}}
                    </td>
                    <td class="time-entry-ee" style="vertical-align: top;">
                        {{ @$v->user->first_name[0] }}{{ @$v->user->last_name[0] }}
                    </td>
                    <td class="time-entry-activity" style="vertical-align: top;">
                        {{ @$v->taskActivity->title }}
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
                        {{ $v->calculated_amt }}
                    </td>
                </tr>
            @empty
            @endforelse
        @endif
        <tr>
            <td colspan="5" class="total-summary-column" style="text-align:right;">Totals:</td>
            <td class="total-entries-total-hours total-data-column" style="text-align:right;">{{$timeEntryTime}}</td>

            <td class="total-data-column">
                ${{number_format($timeEntryAmount,2)}}
            </td>
        </tr>
    </tbody>
</table>
<br>
@endif

{{-- For expenses --}}
@if(!empty($invoice->invoiceExpenseEntry) && count($invoice->invoiceExpenseEntry))
<h3>Expenses</h3>
<table style="width: 100%; border-collapse: collapse;" border="1">
    <tbody>
        <tr class="invoice_info_row">
            <td class="invoice_info_bg">Date</td>
            <td class="invoice_info_bg">EE</td>
            <td class="invoice_info_bg">Activity</td>
            <td class="invoice_info_bg">Description</td>
            <td class="invoice_info_bg" style="width: 65px; text-align: right;">Cost</td>
            <td class="invoice_info_bg" style="width: 65px; text-align: right;">Quantity</td>
            <td class="invoice_info_bg" style="width: 140px; text-align: right;">Line Total</td>
        </tr>
        @php
            $expenseAmount = $invoice->invoiceExpenseEntry->where("time_entry_billable", "yes")->sum('calulated_cost');
            $billableExpense = $invoice->invoiceExpenseEntry->where("time_entry_billable", "yes");
            $nonBillableExpense = $invoice->invoiceExpenseEntry->where("time_entry_billable", "no");
            $expenseTime = $invoice->invoiceExpenseEntry->sum('duration');
        @endphp
        @if(!empty($billableExpense) && count($billableExpense))
        @forelse($billableExpense as $k=>$v)        
        <tr class="invoice_info_row ">
            <td class="time-entry-date" style="vertical-align: top;">
                {{date('m/d/Y',strtotime($v->entry_date))}}
            </td>
            <td class="time-entry-ee" style="vertical-align: top;">
                {{ @$v->user->first_name[0] }}{{ @$v->user->last_name[0] }}
            </td>
            <td class="time-entry-activity" style="vertical-align: top;">
                {{ @$v->expenseActivity->title }}
            </td>
            <td class="time-entry-description" style="vertical-align: top;">
                <p class="invoice_notes">
                    {{$v->description}}
                </p>
            </td>
            <td style="vertical-align: top; text-align: right;" class="">
                ${{ $v->cost_value }}
            </td>
            <td style="vertical-align: top; text-align: right;" class="">
                {{ $v->qty }}
            </td>
            <td style="vertical-align: top; text-align: right;" class="">
                {{ $v->calulated_cost }}
            </td>
        </tr>
        @empty
        @endforelse
        @endif
        @if(!empty($nonBillableExpense) && count($nonBillableExpense))
            <tr class="invoice_info_row nonbillable-title">
                <td class="invoice_info_bg" colspan="7">
                    Non-billable Expenses:
                </td>
                </tr>
            @forelse($nonBillableExpense as $k=>$v)
                <tr class="invoice_info_row ">
                    <td class="time-entry-date" style="vertical-align: top;">
                        {{date('m/d/Y',strtotime($v->entry_date))}}
                    </td>
                    <td class="time-entry-ee" style="vertical-align: top;">
                        {{ @$v->user->first_name[0] ??''}}{{ @$v->user->last_name[0] ??''}}
                    </td>
                    <td class="time-entry-activity" style="vertical-align: top;">
                        {{ @$v->expenseActivity->title }}
                    </td>
                    <td class="time-entry-description" style="vertical-align: top;">
                        <p class="invoice_notes">
                            {{$v->description}}
                        </p>
                    </td>
                    <td style="vertical-align: top; text-align: right;" class="nonbillableRow" >
                        ${{ $v->cost_value }}
                    </td>
                    <td style="vertical-align: top; text-align: right;" class="nonbillableRow">
                        {{ $v->qty }}
                    </td>
                    <td style="vertical-align: top; text-align: right;" class="nonbillableRow">
                        {{ $v->calulated_cost }}
                    </td>
                </tr>
            @empty
            @endforelse
        @endif
        <tr>
            <td colspan="6" style="text-align: right; padding-top: 5px;text-align:right;">Expense Total:</td>
            <td style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;text-align:right;">
                ${{number_format($expenseAmount,2)}}
            </td>
        </tr>
    </tbody>
</table>
<br>
@endif

{{-- For adjustment --}}
@if(!empty($invoice->invoiceAdjustmentEntry) && count($invoice->invoiceAdjustmentEntry))
<h3>Adjustments</h3>
<table style="width: 100%; border-collapse: collapse;font-size: 12px;" border="1">
    <tbody>
        <tr class="invoice_info_row">
            <td class="invoice_info_bg">Item</td>
            <td class="invoice_info_bg">Applied To</td>
            <td class="invoice_info_bg">Type</td>
            <td class="invoice_info_bg">Description</td>
            <td class="invoice_info_bg" style="width: 65px; text-align: right;">Basis</td>
            <td class="invoice_info_bg" style="width: 65px; text-align: right;">Percentage</td>
            <td class="invoice_info_bg" style="width: 140px; text-align: right;">Line Total</td>
        </tr>
        @php
            $totalDiscount = $invoice->invoiceAdjustmentEntry->where('item', 'discount')->sum('amount');
            $additionalTotal = $invoice->invoiceAdjustmentEntry->whereIn('item', ['addition', 'intrest'])->sum('amount');
        @endphp
        @forelse($invoice->invoiceAdjustmentEntry as $k=>$v)
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
                {{ ($v->ad_type=="amount") ? "-" : $v->basis }}
            </td>
            <td style="vertical-align: top; text-align: right;" class="nonbillable">
                {{ ($v->ad_type=="amount") ? "-" : $v->percentages."%" }}
            </td>
            <td style="vertical-align: top; text-align: right;" class="nonbillable">
                {{$v->amount}}
            </td>
        </tr>
        @empty
        @endforelse
        @if($totalDiscount != "0")
        <tr>
            <td colspan="6" style="text-align: right; padding-top: 5px;">Discount Total:</td>
            <td style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;">
                (${{number_format($totalDiscount,2)}})
            </td>
        </tr>
        @endif
        @if($additionalTotal != "0")
        <tr>
            <td colspan="6" style="text-align: right; padding-top: 5px;">Addition Total:</td>
            <td style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;">
                ${{number_format($additionalTotal,2)}}
            </td>
        </tr>
        @endif
    </tbody>
</table>
<br>
@endif

{{-- For invoice total --}}
<table style="width: 100%; border-collapse: collapse;font-size: 12px;border-left: none; border-bottom: none;border-top: none;" border="1">
    <tbody>
        <tr class="invoice_info_row">
            <td style="width: 580px; vertical-align: top; border: none;"></td>
            <td style="width: 10px; border: none;" rowspan="1">&nbsp;</td>
            <td style="text-align: right; border-right: none; vertical-align: top; width: 140px; line-height: 1.8;" rowspan="1">
                Flat Fee Sub-Total:<br>
                Time Entry Sub-Total:<br>
                Expense Sub-Total:<br>
                <span style="font-weight: bold;">Sub-Total:</span><br>
                <br>
                @if($totalDiscount!="0")
                <span>Discounts:</span>
                <br>
                @endif

                @if($additionalTotal!="0")
                <span>Additions:</span>
                <br>
                @endif
                <br>
                <span style="font-weight:bold;">Total:</span><br>
                <span style="font-weight:bold;">Amount Paid:</span><br>
            </td>
            <td style="text-align: right; border-left: none; vertical-align: top; width: 85px; line-height: 1.8;" rowspan="1">
                ${{number_format($flatFeeEntryAmount ?? 0,2)}}<br>
                ${{number_format($timeEntryAmount ?? 0,2)}}<br>
                ${{number_format($expenseAmount ?? 0,2)}}<br>
                ${{number_format($timeEntryAmount ?? 0 + $expenseAmount ?? 0 + $flatFeeEntryAmount ?? 0,2)}}<br>
                <br>

                @if($totalDiscount!="0")
                ${{number_format($totalDiscount,2)}}<br>
                @endif

                @if($additionalTotal!="0")
                ${{number_format($additionalTotal,2)}}<br>
                @endif
                <br>
                ${{ $invoice->total_amount_new }}<br>
                ${{ $invoice->paid_amount_new }}
            </td>
        </tr>

        <tr class="invoice_info_row">
            <td style="border: none;">&nbsp;</td>
            <td style="border: none;">&nbsp;</td>
            <td class="invoice_info_bg" style="text-align: right; border-right: none; vertical-align: top; font-weight: bold; ">
                Balance Due:
            </td>
            <td class="invoice_info_bg" id="invoice-balance-due" style="text-align: right; border-left: none; vertical-align: top; font-weight: bold; ">
                ${{ $invoice->due_amount_new }}
            </td>
        </tr>
    </tbody>
</table>
<br>

@if(!empty($invoice->invoiceInstallment) && count($invoice->invoiceInstallment))
<table style="width: 100%; border-collapse: collapse;font-size: 12px;border-left: none;float:right; border-bottom: none;border-top: none;margin-bottom:10px;float:left;" border="0">
    <tr><td></td><tr>
        <td>
            <table style="width: 35%; border-collapse: collapse;font-size: 12px;border-left: none;float:right; border-bottom: none;border-top: none;margin-bottom:10px;" border="0">
                <tbody>
                    <tr class="invoice_info_row" style="font-size: 12px;">
                        <td style="border: none;"> <a name="payment_plan">&nbsp;</a></td>
                        <td style="border: none;">&nbsp;</td>
                        <td class="" style="vertical-align: top; border: none; padding: 0;" colspan="2">
                            <div style="margin: 0px; border: 1px solid black;">
                                <table class="payment_plan" style="width: 100%; border-collapse: collapse;">
                                    <tbody>
                                        <tr class="header">
                                            <th class="invoice_info_bg installment_due" style="padding: 5px;text-align:left;font-size: 12px;white-space: nowrap;">Installment Due</th>
                                            <th class="invoice_info_bg status" style="padding: 5px;"></th>
                                            <th class="invoice_info_bg amount_due" style="padding: 5px;text-align:right;font-size: 12px;white-space: nowrap;">Amount</th>
                                        </tr>
                                        @forelse($invoice->invoiceInstallment as $lk=>$lv)
                                        <tr class="even" {{ ($lv->status == 'paid') ? "style=color:#cbcccf;" : ''}}>
                                            <td style="border: none; border-bottom: 1px solid #cccccc;font-size: 12px;" class="nonbillable js-payment_plan_date">
                                                {{date('M d,Y',strtotime($lv->due_date))}}
                                            </td>
                                            <td style="border: none; border-bottom: 1px solid #cccccc;white-space: nowrap;" class="nonbillable js-payment_plan_status">
                                                @if($lv->status=="paid")
                                                    <i class="fas fa-check-circle" style="color: #40BC53"></i>&nbsp;&nbsp;Manual payment successful
                                                @endif
                                            </td>
                                            <td style="text-align: right; border: none; border-bottom: 1px solid #cccccc;font-size: 12px;" class="nonbillable js-payment_plan_amount">
                                                ${{number_format($lv->installment_amount,2)}}
                                            </td>
                                        </tr>
                                        @empty
                                        @endforelse
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

@if(!empty($invoice->invoicePaymentHistory) && count($invoice->invoicePaymentHistory))
<h3> Payment History</h3>
<table style="width: 100%; border-collapse: collapse;font-size: 12px;border-left: none;float:right; border-bottom: none;border-top: none;margin-bottom:10px;" border="1">
    <tbody>
        <tr class="invoice_info_row invoice_header_row invoice-table-row">
            <td class="invoice_info_bg" style="width: 12%;">Activity</td>
            <td class="invoice_info_bg" style="width: 10%;">Date</td>
            <td class="invoice_info_bg" style="width: 33%;">Payment Method</td>
            <td class="invoice_info_bg" style="width: 12%;">Amount</td>
            <td class="invoice_info_bg" style="width: 15%;">Responsible User</td>
            <td class="invoice_info_bg" style="width: 18%;">Deposited Into</td>
        </tr>
        @forelse($invoice->invoicePaymentHistory as $hKey => $hVal)
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
                @if($hVal->acrtivity_title=="Payment Received")
                ${{ number_format($hVal->amount,2) }}
                @elseif($hVal->acrtivity_title=="Payment Refund")
                (${{ number_format($hVal->amount,2) }})
                @endif
            </td>
            <td class="payment-history-column-user" style="vertical-align: top;">
                {{substr($hVal->createdByUser->full_name,0,100)}}
                ({{$hVal->createdByUser->user_title}})
            </td>
            <td class="payment-history-column-deposited-into" style="vertical-align: top;">
                @if($hVal->acrtivity_title=="Payment Received" && $hVal->pay_method != 'Non-Trust Credit Account')
                    {{ $hVal->deposit_into }}
                @endif
            </td>
        </tr>
        @empty
        @endforelse
    </tbody>
</table>
@endif
<br>

@if(!empty($invoice->invoice_setting))
    @if(!empty($invoice->applyTrustCreditFund))
    <div>
        @include('billing.invoices.partials.load_invoice_account_summary_pdf', ['Invoice' => $invoice])
    </div>
    @endif
@endif