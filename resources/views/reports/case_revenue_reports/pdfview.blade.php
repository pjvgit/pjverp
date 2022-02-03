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

                        // collected amount
                        $totalPaidFlatfee += str_replace(",","",$case->paidFlatfee);
                        $totalPaidTimeEntry += str_replace(",","",$case->paidTimeEntry);
                        $totalPaidExpenses += str_replace(",","",$case->paidExpenses);
                        $totalPaidBalanceForward += str_replace(",","",$case->paidBalanceForward);
                        $totalPaidInterest += str_replace(",","",$case->paidInterest);
                        $totalPaidTax += str_replace(",","",$case->paidTax);
                        $totalPaidAdditions += str_replace(",","",$case->paidAdditions);
                        $totalPaidDiscounts += str_replace(",","",$case->paidDiscounts);

                        $totalCollected = str_replace(",","",$case->paidFlatfee)
                        + str_replace(",","",$case->paidTimeEntry)
                        + str_replace(",","",$case->paidExpenses)
                        + str_replace(",","",$case->paidBalanceForward)
                        + str_replace(",","",$case->paidInterest)
                        + str_replace(",","",$case->paidTax)
                        + str_replace(",","",$case->paidAdditions)
                        - str_replace(",","",$case->paidDiscounts);
                        $totalcaseInvoicePaidAmount = $totalPaidFlatfee + $totalPaidTimeEntry + $totalPaidExpenses + $totalPaidBalanceForward + $totalPaidInterest + $totalPaidTax + $totalPaidAdditions - $totalPaidDiscounts;

                        // $totalPaidInvoice = str_replace(",","",$case->caseInvoicePaidAmount);
                        // $totalcaseInvoicePaidAmount +=$totalPaidInvoice;                          
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
                        <td>{{($case->paidFlatfee > 0) ? '$'.number_format($case->paidFlatfee,2) : '--'}}</td>
                        <td>{{($case->paidTimeEntry > 0) ? '$'.number_format($case->paidTimeEntry,2) : '--'}}</td>
                        <td>{{($case->paidExpenses > 0) ? '$'.number_format($case->paidExpenses,2) : '--'}}</td>
                        <td>{{($case->paidBalanceForward > 0) ? '$'.number_format($case->paidBalanceForward,2) : '--'}}</td>
                        <td>{{($case->paidInterest > 0) ? '$'.number_format($case->paidInterest,2) : '--'}}</td>
                        <td>{{($case->paidTax > 0) ? '$'.number_format($case->paidTax,2) : '--'}}</td>
                        <td>{{($case->paidAdditions > 0) ? '$'.number_format($case->paidAdditions,2) : '--'}}</td>
                        <td>{{($case->paidDiscounts > 0) ? '$-'.number_format($case->paidDiscounts,2) : '--'}}</td>
                        <th>${{number_format($totalCollected,2)}}</th>
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