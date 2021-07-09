<table style="width: 100%; margin: 0; padding: 0; table-layout: fixed;" class="invoice">
    <tbody style="margin: 0; padding: 0;">
        <tr>
            <td style="width: 130px; padding: 0px !important; vertical-align: top;" rowspan="4">
                <?php if($findInvoice->status=="Draft"){?>
                <i class="invoice-banner-draft"></i>
                <?php }else  if($findInvoice->status=="Sent"){?>
                <i class="invoice-banner-sent"></i>
                <?php }else if($findInvoice->status=="Unsent"){?>
                <i class="invoice-banner-unsent"></i>
                <?php }else if($findInvoice->status=="Partial"){?>
                    <i class="invoice-banner-partial"></i>
                <?php }else if($findInvoice->status=="Paid"){?>
                    <i class="invoice-banner-paid"></i>
                <?php }else if($findInvoice->status=="Overdue"){?>
                    <i class="invoice-banner-overdue"></i>
                <?php } ?>
            </td>
            <td style="vertical-align: top; white-space: nowrap; width: 350px;"
                class="bill-address pt-4" rowspan="2">
                {{@$firmData->firm_name}}<br>
                {{@$firmData->countryname}}<br>

                {{@$firmData->main_phone}}
            </td>
            <td rowspan="4">
                &nbsp;
            </td>
            <td class="pt-4"
                style="vertical-align: top; white-space: normal; width: 320px; padding-right: 20px; text-align: right;"
                rowspan="1">
                <span class="bill_firm_name"
                    style="font-size: 24px;">{{@$firmData->firm_name}}</span>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; white-space: nowrap; width: 320px; padding-right: 20px;"
                rowspan="3">

                <table style="width: 100%; border-collapse: collapse;">
                    <tbody>
                        <tr>
                            <td style="padding-top: 0px !important; width: 45%">&nbsp;</td>
                            <td
                                style="text-align: right; font-size: 20px; font-weight: bold; padding: 5px; padding-top: 0px;">
                                Invoice
                            </td>
                        </tr>
                        <tr class="invoice_info_row">
                            <td class="invoice_info_bg" style="white-space: nowrap;">Invoice
                                #</td>
                            <td style="text-align: right;">{{$invoiceNo}}</td>
                        </tr>
                        <tr class="invoice_info_row">
                            <td class="invoice_info_bg" style="white-space: nowrap;">Invoice Date</td>
                            <td style="text-align: right;">
                                {{date('M j, Y',strtotime($findInvoice->created_at))}}</td>
                        </tr>
                        <tr class="invoice_info_row">
                            <td class="invoice_info_bg" style="white-space: nowrap;">Due Date</td>
                            <td style="text-align: right;">
                                @if(count($InvoiceInstallment))
                                <a href="#payment_plan" class="scrollTo">See Payment Plan</a> 
                                @else
                                {{($findInvoice->due_date) ? date('M j, Y',strtotime($findInvoice->due_date)) : NULL}}
                                @endif
                            </td>
                        </tr>
                        <tr class="invoice_info_row">
                            <td class="invoice_info_bg"
                                style="white-space: nowrap; vertical-align: top;">Balance
                                Due</td>
                            <td style="text-align: right;">
                                ${{number_format($findInvoice->due_amount,2)}}
                            </td>
                        </tr>
                        <tr class="invoice_info_row">
                            <td class="invoice_info_bg" style="white-space: nowrap;">Payment
                                Terms</td>
                            <?php
                                    $items=array("0"=>"Due date","1"=>"Due on receipt","2"=>"Net 15","3"=>"Net 30","4"=>"Net 60","5"=>"");
                                    ?>

                            <td style="text-align: right;">
                                <?php echo $items[$findInvoice->payment_term]; ?></td>
                        </tr>
                        <tr class="invoice_info_row">
                            <td class="invoice_info_bg" style="white-space: nowrap;">Case / Matter</td>
                            <td style="text-align: right; white-space: normal; word-break: break-word"
                                class="court-case-name">
                                @if($findInvoice->case_id == 0)
                                    None
                                @else
                                <a class="bill-court-case-link"
                                    {{-- href="{{BASE_URL}}court_cases/{{@$caseMaster->case_unique_number}}/info">{{$caseMaster->case_title}}</a> --}}
                                    href="{{ route('info', @$caseMaster->case_unique_number) }} ">{{ @$caseMaster->case_title }}</a>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; width: 350px; word-wrap: break-word;" rowspan="2">
                <span class="billing_user_name">
                    <a href="{{BASE_URL}}contacts/clients/{{$userMaster->id}}">{{$userMaster->first_name}}
                        {{$userMaster->middle_name}} {{$userMaster->last_name}}</a><br>

                        {{($userMaster->street)??''}}   {{($userMaster->apt_unit)??''}}<br>
                        {{($userMaster->city)??''}} {{($userMaster->state)??''}} {{($userMaster->postal_code)??''}}
                </span>
                <p></p>
            </td>
        </tr>

    </tbody>
</table>
<div style="padding: 20px;" class="">
    <hr>
</div>

<div style="padding: 20px;">
    <br>
    <?php 
        $discount=0;
        $addition=0;
        $timeEntryTime=$timeEntryAmount=0;
        $expenseTime=$expenseAmount=0;
        $flatFeeEntryAmount=0;
        if(!$FlatFeeEntryForInvoice->isEmpty()){?>
            <div class="line-items-table">
                <h3>Flat Fees</h3>
                <table style="width: 100%; border-collapse: collapse;">
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
                            <td colspan="4" class="total-summary-column">
                                Flat Fee Total:
                            </td>

                            <td class="total-data-column"> ${{number_format($flatFeeEntryAmount,2)}}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php }  if(!$TimeEntryForInvoice->isEmpty()){?>
        <div class="line-items-table">
            <h3>Time Entries</h3>
            <table style="width: 100%; border-collapse: collapse;">
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
                        <td colspan="5" class="total-summary-column">
                            Totals:
                        </td>

                        <td class="total-entries-total-hours total-data-column">
                            {{$timeEntryTime}}
                        </td>

                        <td class="total-data-column">
                            ${{number_format($timeEntryAmount,2)}}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <?php 
        if(!$ExpenseForInvoice->isEmpty()){?>

    <div class="line-items-table">
        <h3>Expenses</h3>

        <table style="width: 100%; border-collapse: collapse;">
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
                    <td colspan="6" style="text-align: right; padding-top: 5px;">
                        Expense Total:
                    </td>
                    <td
                        style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;">
                        ${{number_format($expenseAmount,2)}}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php } ?>


    <?php  if(!$InvoiceAdjustment->isEmpty()){?>
        <div class="line-items-table">
            <h3>Adjustments</h3>
            <table style="width: 100%; border-collapse: collapse;">
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
                        <td
                            style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;">
                            (${{number_format($discount,2)}})
                        </td>
                    </tr>
                    <tr>
                        <?php } ?>
                        <?php if($addition!="0"){?>

                        <td colspan="6" style="text-align: right; padding-top: 5px;">
                            Addition Total:
                        </td>
                        <td
                            style="text-align: right; padding-top: 5px; padding-right: 5px; font-weight: bold;">
                            ${{number_format($addition,2)}}
                        </td>
                        <?php } ?>

                    </tr>
                </tbody>
            </table>
        </div>
        <?php } ?>

    <table style="width: 100%; border-collapse: collapse;">
        <tbody>
            <tr class="invoice_info_row">
                <?php if($findInvoice->terms_condition!=""){?>
                <td style="width: 580px; vertical-align: top;">
                    <div style="font-weight: bold;">Terms & Conditions:</div> <br>
                    <p class="invoice_notes">{{$findInvoice->terms_condition}} </p>
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
                    ${{number_format($findInvoice->paid_amount,2)}}
                </td>
            </tr>


            <tr class="invoice_info_row"> 
                <?php if($findInvoice->notes!=""){?>
                    <td style="width: 580px; vertical-align: top;">
                        <div style="font-weight: bold;">Notes:</div> <br>
                        <p class="invoice_notes">{{$findInvoice->notes}} </p>
                    
                    </td>
                <?php } else {  ?>  
                    <td style="border: none;">
                        &nbsp;
                    </td>
                <?php }?>
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
                    $paidAmount = ($findInvoice->paid_amount) ? (float) $findInvoice->paid_amount : 0;
                    $total = @$timeEntryAmount + @$expenseAmount + @$flatFeeEntryAmount + $addition;
                    $F= number_format($total - ($discount + $paidAmount), 2);
                    if($F<=0){
                        $fAmt=0;
                    }else{
                        $fAmt=$F;
                    }?>
                    ${{$fAmt}}
                </td>
            </tr>

            <?php
            if(!empty($InvoiceInstallment)){?>
            <tr class="invoice_info_row">
                <td style="border: none;">
                <a name="payment_plan">&nbsp;</a>
                </td>
                <td style="border: none;">&nbsp;</td>
                <td class="" style="vertical-align: top; border: none; padding: 0; padding-top: 15px;" colspan="2">
                <div style="margin: 0px; border: 1px solid black;">
                    <table class="payment_plan" style="width: 100%; border-collapse: collapse;" id="payment_plan">
                    <tbody><tr class="header">
                        <th class="invoice_info_bg installment_due" style="padding: 5px;">Installment Due</th>
                        <th class="invoice_info_bg status" style="padding: 5px;"></th>
                        <th class="invoice_info_bg adjustment" style="padding: 5px;">Adjustment</th>
                        <th class="invoice_info_bg amount_due" style="padding: 5px;">Amount</th>
                    </tr>
                    <?php    foreach($InvoiceInstallment as $lk=>$lv){ ?>
                        <tr class="even  ">
                        <td style="border: none; border-bottom: 1px solid #cccccc;" class="nonbillable js-payment_plan_date">
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
                        <td style="text-align: right; border: none; border-bottom: 1px solid #cccccc;" class="nonbillable js-payment_plan_amount">
                            @if($lv->status=="unpaid" && $lv->adjustment!=0.00 )
                                ${{ number_format(($lv->installment_amount - $lv->adjustment), 2) }}
                            @else
                            ${{ number_format($lv->installment_amount,2) }}
                            @endif
                        </td>
                        </tr>
                        <?php } ?>
                    </tbody></table>
                </div>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <br>
</div>