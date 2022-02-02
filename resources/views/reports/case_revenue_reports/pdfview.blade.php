@extends('layouts.pdflayout')
<div class="row d-flex">
        <div class="nav-header">
            <h3 class="font-weight-bold">
                Case Revenue Report - {{$from}} - {{$to}}
            </h3>
        </div>
        <div class="reportList" >
            @if(count($cases) > 0)
            <table class="table reporting report_table report_table_spaced accounts_receivable_reporting" id="report_table">
                <thead>
                    <tr class="header info_header">
                        <td class="receivables_title" >
                        </td>
                        <th class="receivables_group_total" colspan="12">
                            Billed
                        </th>
                        <th class="receivables_group_paid" colspan="9">
                            Collected
                        </th>
                    </tr>
                    <tr class="header">
                        <th>Case Name</th>
                        <td>Flat Fees</td>
                        <td>Hours</td>
                        <td>Time Entries</td>
                        <td>Expenses</td>
                        <td>Balances Forwarded</td>
                        <td>Interest</td>
                        <td>Tax</td>
                        <td>Additions</td>
                        <td>Discounts</td>
                        <td>Non-billable Hours</td>
                        <td>Non-billable Amounts</td>
                        <th>Total</th>
                        <td>Flat Fees</td>
                        <td>Time Entries</td>
                        <td>Expenses</td>
                        <td>Balances Forwarded</td>
                        <td>Interest</td>
                        <td>Tax</td>
                        <td>Additions</td>
                        <td>Discounts</td>
                        <th>Total Receivable amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $totalCaseFlatfees = $totalCaseDuration = $totalCaseTimeEntry = $totalCaseExpenseEntry = 0;
                        $totalCaseBalanceForwarded = $totalCaseInterestAdjustment = $totalCaseTaxAdjustment = $totalCaseAdditionsAdjustment = $totalCaseDiscountsAdjustment = 0;
                        $totalCaseNonBillableDuration = $totalCaseNonBillableEntry =  $totalCaseBilled = 0;
                        $totalPaidFlatfee = $totalPaidTimeEntry = $totalPaidExpenses = $totalPaidBalanceForward = $totalPaidInterest = $totalPaidTax = $totalPaidAdditions = $totalPaidDiscounts = 0;
                        $totalcaseInvoicePaidAmount = 0;
                    ?>
                    @foreach($cases as $k => $case)
                    <?php
                        $totalCaseFlatfees += str_replace(",","",$case->caseFlatfees); 
                        $totalCaseDuration += str_replace(",","",$case->caseDuration);
                        $totalCaseTimeEntry += str_replace(",","",$case->caseTimeEntry);
                        $totalCaseExpenseEntry += str_replace(",","",$case->caseExpenseEntry);
                        $totalCaseBalanceForwarded += str_replace(",","",$case->caseBalanceForwarded);
                        $totalCaseInterestAdjustment += str_replace(",","",$case->caseInterestAdjustment);
                        $totalCaseTaxAdjustment += str_replace(",","",$case->caseTaxAdjustment);
                        $totalCaseAdditionsAdjustment += str_replace(",","",$case->caseAdditionsAdjustment);
                        $totalCaseDiscountsAdjustment += str_replace(",","",$case->caseDiscountsAdjustment);
                        $totalCaseNonBillableDuration += str_replace(",","",$case->caseNonBillableDuration);
                        $totalCaseNonBillableEntry += str_replace(",","",$case->caseNonBillableEntry);

                        $totalBilled  = str_replace(",","",$case->caseFlatfees) + str_replace(",","",$case->caseTimeEntry) + str_replace(",","",$case->caseExpenseEntry) + str_replace(",","",$case->caseBalanceForwarded) + str_replace(",","",$case->caseInterestAdjustment) + str_replace(",","",$case->caseTaxAdjustment) + str_replace(",","",$case->caseAdditionsAdjustment)  + str_replace(",","",$case->caseNonBillableEntry) -  str_replace(",","",$case->caseDiscountsAdjustment);
                        $totalCaseBilled += str_replace(",","",$case->caseFlatfees) + str_replace(",","",$case->caseTimeEntry) + str_replace(",","",$case->caseExpenseEntry) + str_replace(",","",$case->caseBalanceForwarded) + str_replace(",","",$case->caseInterestAdjustment) + str_replace(",","",$case->caseTaxAdjustment) + str_replace(",","",$case->caseAdditionsAdjustment)  + str_replace(",","",$case->caseNonBillableEntry) -  str_replace(",","",$case->caseDiscountsAdjustment);

                        $totalPaidInvoice = str_replace(",","",$case->caseInvoicePaidAmount);
                        $totalcaseInvoicePaidAmount +=$totalPaidInvoice;

                        $paidFlatfee = $paidTimeEntry = $paidExpenses = $paidBalanceForward = $paidInterest = $paidTax = $paidAdditions = $paidDiscounts = 0;
                        
                        
                        if(str_replace(",","",$case->caseFlatfees) > 0 && $totalPaidInvoice > 0){
                            if($totalPaidInvoice > str_replace(",","",$case->caseFlatfees)){
                                $paidFlatfee = str_replace(",","",$case->caseFlatfees);
                                $totalPaidInvoice -= $paidFlatfee;
                            }else{
                                $paidFlatfee = $totalPaidInvoice;
                                $totalPaidInvoice -= $paidFlatfee;
                            }
                            $totalPaidFlatfee += $paidFlatfee;
                        }     
                        if(str_replace(",","",$case->caseTimeEntry) > 0 && $totalPaidInvoice > 0){
                            if($totalPaidInvoice > str_replace(",","",$case->caseTimeEntry)){
                                $paidTimeEntry = str_replace(",","",$case->caseTimeEntry);
                                $totalPaidInvoice -= $paidTimeEntry;
                            }else{
                                $paidTimeEntry = $totalPaidInvoice;
                                $totalPaidInvoice -= $paidTimeEntry;
                            }
                            $totalPaidTimeEntry += $paidTimeEntry;
                        }    
                        if(str_replace(",","",$case->caseExpenseEntry) > 0  && $totalPaidInvoice > 0){
                            if($totalPaidInvoice > str_replace(",","",$case->caseExpenseEntry)){
                                $paidExpenses = str_replace(",","",$case->caseExpenseEntry);
                                $totalPaidInvoice -= $paidExpenses;
                            }else{
                                $paidExpenses = $totalPaidInvoice;
                                $totalPaidInvoice -= $paidExpenses;
                            }
                            $totalPaidExpenses += $paidExpenses;
                        }   
                        if(str_replace(",","",$case->caseAdditionsAdjustment) > 0  && $totalPaidInvoice > 0){
                            if($totalPaidInvoice > str_replace(",","",$case->caseAdditionsAdjustment)){
                                $paidAdditions = str_replace(",","",$case->caseAdditionsAdjustment);
                                $totalPaidInvoice -= $paidAdditions;
                            }else{
                                $paidAdditions = $totalPaidInvoice;
                                $totalPaidInvoice -= $paidAdditions;
                            }
                            $totalPaidAdditions += $paidAdditions;
                        }   
                        if(str_replace(",","",$case->caseTaxAdjustment) > 0  && $totalPaidInvoice > 0){
                            if($totalPaidInvoice > str_replace(",","",$case->caseTaxAdjustment)){
                                $paidTax = str_replace(",","",$case->caseTaxAdjustment);
                                $totalPaidInvoice -= $paidTax;
                            }else{
                                $paidTax = $totalPaidInvoice;
                                $totalPaidInvoice -= $paidTax;
                            }
                            $totalPaidTax += $paidTax;
                        } 
                        if(str_replace(",","",$case->caseInterestAdjustment) > 0  && $totalPaidInvoice > 0){
                            if($totalPaidInvoice > str_replace(",","",$case->caseInterestAdjustment)){
                                $paidInterest = str_replace(",","",$case->caseInterestAdjustment);
                                $totalPaidInvoice -= $paidInterest;
                            }else{
                                $paidInterest = $totalPaidInvoice;
                                $totalPaidInvoice -= $paidInterest;
                            }
                            $totalPaidInterest += $paidInterest;
                        }  
                        if(str_replace(",","",$case->caseDiscountsAdjustment) > 0  && $totalPaidInvoice > 0){
                            if($totalPaidInvoice > str_replace(",","",$case->caseDiscountsAdjustment)){
                                $paidDiscounts = str_replace(",","",$case->caseDiscountsAdjustment);
                                $totalPaidInvoice -= $paidDiscounts;
                            }else{
                                $paidDiscounts = $totalPaidInvoice;
                                $totalPaidInvoice -= $paidDiscounts;
                            }
                            $totalPaidDiscounts += $paidDiscounts;
                        }  
                        if(str_replace(",","",$case->caseBalanceForwarded) > 0  && $totalPaidInvoice > 0){
                            if($totalPaidInvoice > str_replace(",","",$case->caseBalanceForwarded)){
                                $paidBalanceForward = str_replace(",","",$case->caseBalanceForwarded);
                                $totalPaidInvoice -= $paidBalanceForward;
                            }else{
                                $paidBalanceForward = $totalPaidInvoice;
                                $totalPaidInvoice -= $paidBalanceForward;
                            }
                            $totalPaidBalanceForward += $paidBalanceForward;
                        }                            
                    ?>        

                    <tr class="">
                        <td>{{$case->case_title}}</td>
                        <td>{{($case->caseFlatfees > 0) ? '$'.$case->caseFlatfees : '--'}}</td>
                        <td>{{($case->caseDuration > 0) ? $case->caseDuration : '--'}}</td>
                        <td>{{($case->caseTimeEntry > 0) ? '$'.$case->caseTimeEntry : '--'}}</td>
                        <td>{{($case->caseExpenseEntry > 0) ? '$'.$case->caseExpenseEntry : '--'}}</td>
                        <td>{{($case->caseBalanceForwarded > 0) ? '$'.$case->caseBalanceForwarded : '--'}}</td>
                        <td>{{($case->caseInterestAdjustment > 0) ? '$'.$case->caseInterestAdjustment : '--'}}</td>
                        <td>{{($case->caseTaxAdjustment > 0) ? '$'.$case->caseTaxAdjustment : '--'}}</td>
                        <td>{{($case->caseAdditionsAdjustment > 0) ? '$'.$case->caseAdditionsAdjustment : '--'}}</td>
                        <td>{{($case->caseDiscountsAdjustment > 0) ? '$-'.$case->caseDiscountsAdjustment : '--'}}</td>
                        <td>{{($case->caseNonBillableDuration > 0) ? $case->caseNonBillableDuration : '--'}}</td>
                        <td>{{($case->caseNonBillableEntry > 0) ? '$'.$case->caseNonBillableEntry : '--'}}</td>
                        <th>${{number_format($totalBilled,2)}}</th>
                        <td>{{($paidFlatfee > 0) ? '$'.number_format($paidFlatfee,2) : '--'}}</td>
                        <td>{{($paidTimeEntry > 0) ? '$'.number_format($paidTimeEntry,2) : '--'}}</td>
                        <td>{{($paidExpenses > 0) ? '$'.number_format($paidExpenses,2) : '--'}}</td>
                        <td>{{($paidBalanceForward > 0) ? '$'.number_format($paidBalanceForward,2) : '--'}}</td>
                        <td>{{($paidInterest > 0) ? '$'.number_format($paidInterest,2) : '--'}}</td>
                        <td>{{($paidTax > 0) ? '$'.number_format($paidTax,2) : '--'}}</td>
                        <td>{{($paidAdditions > 0) ? '$'.number_format($paidAdditions,2) : '--'}}</td>
                        <td>{{($paidDiscounts > 0) ? '$-'.number_format($paidDiscounts,2) : '--'}}</td>
                        <th>${{$case->caseInvoicePaidAmount}}</th>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="header total_row">
                        <th>Page Total (Sum of the {{count($cases)}} rows displayed)</th>
                        <th>${{number_format($totalCaseFlatfees,2)}}</th>
                        <th>{{$totalCaseDuration}}</th>
                        <th>${{number_format($totalCaseTimeEntry,2)}}</th>
                        <th>${{number_format($totalCaseExpenseEntry,2)}}</th>
                        <th>${{number_format($totalCaseBalanceForwarded,2)}}</th>
                        <th>${{number_format($totalCaseInterestAdjustment,2)}}</th>
                        <th>${{number_format($totalCaseTaxAdjustment,2)}}</th>
                        <th>${{number_format($totalCaseAdditionsAdjustment,2)}}</th>
                        <th>$-{{number_format($totalCaseDiscountsAdjustment,2)}}</th>
                        <th>{{number_format($totalCaseNonBillableDuration,2)}}</th>
                        <th>${{number_format($totalCaseNonBillableEntry,2)}}</th>
                        <th>${{number_format($totalCaseBilled,2)}}</th>
                        <th>${{number_format($totalPaidFlatfee,2)}}</th>
                        <th>${{number_format($totalPaidTimeEntry,2)}}</th>
                        <th>${{number_format($totalPaidExpenses,2)}}</th>
                        <th>${{number_format($totalPaidBalanceForward,2)}}</th>
                        <th>${{number_format($totalPaidInterest,2)}}</th>
                        <th>${{number_format($totalPaidTax,2)}}</th>
                        <th>${{number_format($totalPaidAdditions,2)}}</th>
                        <th>$-{{number_format($totalPaidDiscounts,2)}}</th>
                        <th>${{number_format($totalcaseInvoicePaidAmount,2)}}</th>                        
                    </tr>
                </tfoot>
            </table>
            @else
                <span class="alert alert-info d-flex">No billing activity found.</span>
            @endif
        </div>
    </div>
</div>