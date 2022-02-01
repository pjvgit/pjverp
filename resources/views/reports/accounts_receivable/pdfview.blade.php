@extends('layouts.pdflayout')
<div class="row d-flex">
        <div class="nav-header">
            <h3 class="font-weight-bold">
                Accounts Receivable Report
            </h3>
        </div>
        <div class="reportList  d-flex" id="printHtml">
            <?php 
            $receivables_group_total = 0.00;
            $receivables_group_paid = 0.00;
            $receivables_group_due = 0.00;
            ?>
            @if(isset($request->submit) && $request->submit != '' )
            <?php 
                $receivables_group_total_client_common = 0.00;
                $receivables_group_paid_client_common = 0.00;
                $receivables_group_due_client_common = 0.00;

                if(count($clientArray) > 0){
                    foreach($clientArray as $client => $Invoices){
                        foreach($Invoices as $k => $aData){
                            $receivables_group_total_client_common += $aData->total_amount;
                            $receivables_group_paid_client_common += $aData->paid_amount;
                            $due_amount = str_replace(",","",number_format($aData->total_amount,2)) - str_replace(",","",number_format($aData->paid_amount,2));
                            $receivables_group_due_client_common += $due_amount;
                        }
                    }
                }else{
                    foreach($Invoices as $k => $aData){
                        $receivables_group_total_client_common += $aData->total_amount;
                        $receivables_group_paid_client_common += $aData->paid_amount;
                        $due_amount = str_replace(",","",number_format($aData->total_amount,2)) - str_replace(",","",number_format($aData->paid_amount,2));
                        $receivables_group_due_client_common += $due_amount;
                    }
                }
            ?>
            <table class="table d-flex" >
                <tbody>
                     <tr class="header info_header">
                        @if(count($clientArray) > 0)                       
                        
                        <td>
                            <div class="report_summary_box">
                                Total Invoice
                                <hr class="inset">
                                <span class="receivables_group_total_client">${{number_format($receivables_group_total_client_common,2)}}</span>
                            </div>
                        </td>
                        <td>
                            <div class="report_summary_box">
                                Total Payable
                                <hr class="inset">
                                <span class="receivables_group_paid_client">${{number_format($receivables_group_paid_client_common,2)}}</span>
                            </div>
                        </td>
                        @endif
                        <td>
                            <div class="report_summary_box">
                                Total Receivable
                                <hr class="inset">
                                <span class="receivables_group_due_client">${{number_format($receivables_group_due_client_common,2)}}</span>
                            </div>
                        </td>
                        @endif
                    </tr>
                </tbody>
            </table>
            <br>
            @if(count($clientArray) > 0)
                @foreach($clientArray as $client => $Invoices)
                <table class="table reporting report_table report_table_spaced accounts_receivable_reporting">
                    <tbody>
                        <tr class="header info_header">
                            <th class="receivables_title" colspan="3">
                            {{ ($grp_by == 'client') ? 'Client' : (($grp_by == 'case') ? 'Case' : 'Practice') }} : {{ ($client != '') ? $client : "Not Specified" }}
                            </th>
                            <th class="receivables_group_total_client">
                               
                            </th>
                            <th class="receivables_group_paid_client">
                               
                            </th>
                            <th class="receivables_group_due_client">
                               
                            </th>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="header">
                            <th>Invoice #</th>
                            <th>Client</th>
                            <th>Case</th>
                            <th>Invoice Total</th>
                            <th>Amount Paid</th>
                            <th>Amount Receivable</th>
                            <th>Due Date</th>
                            <th>Invoice Status</th>
                            <th>Days Aging</th>
                        </tr>
                        <?php 
                        $receivables_group_total_client = 0.00;
                        $receivables_group_paid_client = 0.00;
                        $receivables_group_due_client = 0.00;
                        ?>
                        @foreach($Invoices as $k => $aData)
                        <?php 
                        $receivables_group_total_client += $aData->total_amount;
                        $receivables_group_paid_client += $aData->paid_amount;
                        $due_amount = str_replace(",","",number_format($aData->total_amount,2)) - str_replace(",","",number_format($aData->paid_amount,2));
                        $receivables_group_due_client += $due_amount;
                        $receivables_group_due += $due_amount;
                        $receivables_group_total += $aData->total_amount;
                        $receivables_group_paid += $aData->paid_amount;
                        ?>
                        <tr class="even receivable_col">
                                <td>
                                #{{$aData->invoice_id}}
                                </td>
                                <td>{{$aData->contact_name}}</td>
                                <td>
                                @if($aData->ctitle == null)
                                    @if($aData->user_level == 5 && $aData->is_lead_invoice == 'yes')
                                        Potential Case: {{ $aData->contact_name }}
                                    @else
                                        None
                                    @endif
                                @else
                                    {{$aData->ctitle}}
                                @endif
                                </td>
                                <td> ${{$aData->total_amount_new}}</td>
                                <td> ${{$aData->paid_amount_new}} </td>
                                <td> ${{$aData->due_amount_new}}</td>
                                <td> 
                                    @if($aData->due_date!=NULL)
                                        {{date('m/d/Y',strtotime($aData->due_date))}}
                                    @else
                                        --
                                    @endif
                                </td>
                                <td> {{$aData->status}} </td>
                                <td> {{$aData->days_aging}} </td>
                        </tr>
                        @endforeach
                        <tr class="header total_row">
                            <td></td>
                            <td></td>
                            <td></td>
                            <th>${{number_format($receivables_group_total_client,2)}}</th>
                            <th>${{number_format($receivables_group_paid_client,2)}}</th>
                            <th>${{number_format($receivables_group_due_client,2)}}</th>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                @endforeach
            @endif
            
            @if($grp_by == '' && count($Invoices) > 0)
            <table class="table reporting report_table report_table_spaced accounts_receivable_reporting">
                <tbody>
                    <tr class="header info_header">
                    <?php 
                        $receivables_group_total_client_common = 0.00;
                        $receivables_group_paid_client_common = 0.00;
                        $receivables_group_due_client_common = 0.00;
                        foreach($Invoices as $k => $aData){
                            $receivables_group_total_client_common += $aData->total_amount;
                            $receivables_group_paid_client_common += $aData->paid_amount;
                            $due_amount = str_replace(",","",number_format($aData->total_amount,2)) - str_replace(",","",number_format($aData->paid_amount,2));
                            $receivables_group_due_client_common += $due_amount;
                        }
                    ?>
                        <td class="receivables_title" colspan="3">
                        </td>
                        <td class="receivables_group_total">
                            ${{ number_format($receivables_group_total_client_common,2)}}
                        </td>
                        <td class="receivables_group_paid">
                            ${{ number_format($receivables_group_paid_client_common,2)}}
                        </td>
                        <td class="receivables_group_due">
                            ${{ number_format($receivables_group_due_client_common,2)}}
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr class="header">
                        <th>Invoice #</th>
                        <th>Client</th>
                        <th>Case</th>
                        <th>Invoice Total</th>
                        <th>Amount Paid</th>
                        <th>Amount Receivable</th>
                        <th>Due Date</th>
                        <th>Invoice Status</th>
                        <th>Days Aging</th>
                    </tr>
                    @foreach($Invoices as $k => $aData)
                    <?php 
                    $receivables_group_total += $aData->total_amount;
                    $receivables_group_paid += $aData->paid_amount;
                    $due_amount = str_replace(",","",number_format($aData->total_amount,2)) - str_replace(",","",number_format($aData->paid_amount,2));
                    $receivables_group_due += $due_amount;
                    ?>
                    <tr class="even receivable_col">
                            <td>
                               #{{$aData->invoice_id}}
                            </td>
                            <td>
                               {{$aData->contact_name}}
                            </td>
                            <td>
                            @if($aData->ctitle == null)
                                @if($aData->user_level == 5 && $aData->is_lead_invoice == 'yes')
                                    Potential Case: {{ $aData->contact_name }}
                                @else
                                    None
                                @endif
                            @else
                                {{$aData->ctitle}}
                            @endif
                            </td>
                            <td> ${{$aData->total_amount_new}}</td>
                            <td> ${{$aData->paid_amount_new}} </td>
                            <td> ${{$aData->due_amount_new}}</td>
                            <td> 
                                @if($aData->due_date!=NULL)
                                    {{date('m/d/Y',strtotime($aData->due_date))}}
                                @else
                                    --
                                @endif
                            </td>
                            <td> {{$aData->status}} </td>
                            <td> {{$aData->days_aging}} </td>
                    </tr>
                    @endforeach
                    <tr class="header total_row">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>${{number_format($receivables_group_total,2)}}</td>
                        <td>${{number_format($receivables_group_paid,2)}}</td>
                        <td>${{number_format($receivables_group_due,2)}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            @endif
            <input type="hidden" name="receivables_group_total" id="receivables_group_total" value="{{number_format($receivables_group_total,2)}}" />
            <input type="hidden" name="receivables_group_paid" id="receivables_group_paid" value="{{number_format($receivables_group_paid,2)}}" />
            <input type="hidden" name="receivables_group_due" id="receivables_group_due" value="{{number_format($receivables_group_due,2)}}" />
        </div>
    </div>
</div>
<style>
div.report_summary_box {
    float: left;
    padding: 15px;
    width: 190px;
    margin-right: 10px;
    margin-bottom: 10px;
    background-color: #d9eeff;
    font-weight: 400;
    font-size: 18px;
}
hr{
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}
.filters{
    border:solid 1px;
}
</style>