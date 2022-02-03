@extends('layouts.master')
@section('title','Case Revenue - Report')
@section('main-content')

<div class="row d-flex">
    <div class="col-md-2">
    @include('reports.sidebar')
    </div>    
    <div class="col-md-10">
        <div class="nav-header">
            <h3 class="font-weight-bold">
                Case Revenue Report
            </h3>
        </div>
        <div class="filters table">
            <div class="cards">
            <form class="run_report" id="run_report" name="run_report">
                <div style="display: none;">
                    <input type="hidden" name="export_csv" id="export_csv" value="0" />
                    <input type="hidden" name="export_pdf" id="export_pdf" value="0" />
                </div>
                <div class="d-flex align-items-center">
                    <div class="col-md-4 form-group p-1">
                        <label for="picker1">Date Range</label>
                        <div class="input-daterange input-group" id="datepicker">
                            <input type="text" class="form-control" name="from" value="{{$from}}"
                                placeholder="Start Date"/>
                            <span class="input-group-addon">&nbsp;&nbsp; to &nbsp;&nbsp;</span>
                            <input type="text" class="form-control" name="to" value="{{$to}}"
                                placeholder="End Date"/>
                        </div>
                    </div>
                    <div class="col-md-2 form-group p-1">
                        <label for="picker1">Case Status - {{$case_status}}</label>
                        <select id="case_status" name="case_status" class="form-control select2">
                            <option value="all">All</option>
                            <option value="open" <?php echo ("open" == $case_status) ? ' selected' : ''; ?>>Open cases only</option>
                            <option value="close" <?php echo ("close" == $case_status) ? ' selected' : ''; ?>>Close cases only</option>
                        </select>
                    </div>
                    <div class="col-md-2 form-group p-1">
                        <label for="picker1">Lead Attorney</label>
                        <select id="lead_id" name="lead_id" class="form-control select2">
                            <option value="all">All</option>
                            <?php foreach(firmUserList() as $k=>$v){?>
                                <option value="{{$v->id}}" <?php echo ($lead_id == $v->id) ? ' selected' : ''; ?> >{{$v->full_name}}</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2 form-group p-1">
                        <label for="picker1">Practice Area</label>
                        <select id="practice_area" name="practice_area" class="form-control select2">
                            <option value="all">All</option>                            
                            <?php foreach(casePracticeAreaList() as $k=>$v){?>
                                <option value="{{$k}}" <?php echo ($practice_area == $k) ? ' selected' : ''; ?> >{{$v}}</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="col-md-2 form-group p-1">
                        <label for="picker1">Office</label>
                        <select id="office" name="office" class="form-control select2">
                            <option value="all">All</option>                                                
                            <?php foreach(firmOfficeList() as $k=>$v){?>
                                <option value="{{$k}}" <?php echo ($office == $k) ? ' selected' : ''; ?> >{{$v}}</option>
                            <?php } ?>
                        </select>                        
                    </div>
                    <div class="col-md-2 form-group p-1">
                        <label for="picker1">Billing Type</label>                        
                        <select id="billing_type" name="billing_type" class="form-control select2">
                            <option value="all" <?php echo ($billing_type == "all") ? ' selected' : ''; ?> >All</option>
                            <option value="hourly" <?php echo ($billing_type == "hourly") ? ' selected' : ''; ?> >Hourly</option>
                            <option value="contingency" <?php echo ($billing_type == "contingency") ? ' selected' : ''; ?> >Contingency</option>
                            <option value="flat" <?php echo ($billing_type == "flat") ? ' selected' : ''; ?> >Flat Fee</option>
                            <option value="mixed" <?php echo ($billing_type == "mixed") ? ' selected' : ''; ?> >Mixed</option>
                            <option value="pro_bono" <?php echo ($billing_type == "pro_bono") ? ' selected' : ''; ?> >Pro Bono</option>
                        </select>
                    </div>
                    <div class="col-md-2 form-group p-1">
                        <label for="picker1">Originating Attorney</label>
                        <select id="staff_id" name="staff_id" class="form-control select2">
                            <option value="all">All</option>
                            <?php foreach(firmUserList() as $k=>$v){?>
                                <option value="{{$v->id}}" <?php echo ($staff_id == $v->id) ? ' selected' : ''; ?> >{{$v->full_name}}</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4 form-group p-1">
                        <div class="btn-group show">
                            <button class="btn btn-primary btn-rounded m-1 dropdown-toggle" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false"  id="trustDropdown">
                                Export
                            </button>
                            <div class="dropdown-menu bg-transparent shadow-none p-0 m-0 ">
                                <div class="card">
                                    <button onclick="exportPDF();return false;" type="button" tabindex="-1" role="menuitem" class="dropdown-item btn">
                                        as  PDF</button>
                                    <button onclick="exportCSV();return false;" type="button" tabindex="-1" role="menuitem" class="dropdown-item btn">
                                        as  CSV</button>
                                </div>
                            </div>
                        </div>
                        <input type="submit" class="btn btn-primary btn-rounded m-1" id="submitForm" name="submit" value="Run Report"/>
                        <button type="button" class="test-clear-filters text-black-50 btn btn-link resetClear">
                            <a href="{{route('reporting/case_revenue_reports')}}">Reset Filters</a>
                        </button>
                    </div>                    
                </div>
                <div class="d-flex align-items-center">       
                &nbsp;&nbsp;<input type="checkbox" name="show_case_with_daterange" {{ ($show_case_with_daterange == 'on') ? 'checked' : '' }}>&nbsp;<label for="checkbox-boolean-input-0" class="my-0 form-check-label ">Only show cases with data in this date range</label>
                </div>
            </form>
            </div>
        </div>
        <div class="reportList"  id="printHtml" @if(count($cases) > 0) style="overflow-x: scroll;" @endif>
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
                        // $totalcaseInvoicePaidAmount +=$totalPaidFlatfee;

                    ?>        

                    <tr class="">
                        <td><a target="_blank" href="{{route('info', $case->case_unique_number)}}">{{$case->case_title}}</a></td>
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
                        <th>${{number_format($totalPaidDiscounts,2)}}</th>
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
<style>
.filters{
    border:solid 1px;
}
hr{
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}
</style>
@endsection

@section('page-js')
<script type="text/javascript">
    <?php if($export_csv_path != ''){ ?>
        window.open("{{$export_csv_path}}");
    <?php } ?>
    
    $("#trustDropdown").trigger('click');
    $('.input-daterange').datepicker({
        format : 'm/d/yyyy',
        clearBtn: true,
        keyboardNavigation: false,
        forceParse: false,
        todayBtn: "linked",
        todayHighlight : true
    });

    $(".select2").select2({
        theme: "classic",
        allowClear: true,
        placeholder: ''
    });
   
    function exportPDF()
    {
        // $('#hiddenLable').show();        
        // var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        // $(".main-content-wrap").remove();
        // window.print();
        // window.location.reload();
        // return false;  

        $("#preloader").show();
        $("#export_pdf").val(1);
        $("#export_pdf").prop("type",'text');
        $("#submitForm").trigger('click');
    }
    function exportCSV(){
        $("#preloader").show();
        $("#export_csv").val(1);
        $("#export_csv").prop("type",'text');
        $("#submitForm").trigger('click');
    }
</script>
@endsection
