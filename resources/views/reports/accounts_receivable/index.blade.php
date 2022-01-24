@extends('layouts.master')
@section('title','Accounts Receivable - Report')
@section('main-content')

<div class="row d-flex">
    <div class="col-md-2">
    @include('reports.sidebar')
    </div>    
    <div class="col-md-10">
        <div class="nav-header">
            <h3 class="font-weight-bold">
                        Accounts Receivable Report
            </h3>
        </div>
        <div class="card text-left">
            <div class="card-body row">
            <form class="run_report" id="run_report" name="run_report">
                <div class="d-flex align-items-center">
                        <div class="col-md-3 form-group p-1">
                            <label for="picker1">Filter By Client</label>
                            <select id="client_id" name="client_id" class="form-control select2">
                                <option value=""></option>
                                <optgroup label="Client">
                                    <?php foreach(userClientList() as $Clientkey=>$Clientval){ ?>
                                    <option value="{{$Clientval->id}}" <?php echo ($client_id == $Clientval->id) ? ' selected' : ''; ?>>{{substr($Clientval->name,0,30)}}</option>
                                    <?php } ?>
                                </optgroup>
                                <optgroup label="Company">
                                    <?php foreach(userCompanyList() as $Companykey=>$Companyval){ ?>
                                    <option value="{{$Companyval->id}}" <?php echo ($client_id == $Companyval->id) ? ' selected' : ''; ?> >{{substr($Companyval->name,0,50)}}</option>
                                    <?php } ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-3 form-group p-1">
                            <label for="picker1">Filter By Case</label>
                            <select id="case_id" name="case_id" class="form-control select2">
                                <option value=""></option>
                                <?php foreach(userCaseList() as $k=>$v){?>
                                    <option value="{{$v->id}}" <?php echo ($case_id == $v->id) ? ' selected' : ''; ?> >{{$v->case_title}}</option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group p-1">
                            <label for="picker1">Group By Case</label>
                            <select id="broup_by" name="broup_by" class="form-control">
                                <option value="">None</option>
                                <option value="client">Client</option>
                                <option value="case">Case</option>
                                <option value="practive_area">Practice Area</option>                                
                            </select>
                        </div>
                        <div class="col-md-3 form-group p-1">
                            <label for="picker1">Filter By Lead Attorney</label>
                            <select id="staff_id" name="staff_id" class="form-control select2">
                                <option value="all">All</option>
                                <?php foreach(firmUserList() as $k=>$v){?>
                                    <option value="{{$v->id}}" <?php echo ($staff_id == $v->id) ? ' selected' : ''; ?> >{{$v->full_name}}</option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-1 form-group p-1">
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
                            <input type="submit" class="btn btn-primary btn-rounded m-1" name="submit" value="Run Report"/>
                            <button type="button" class="test-clear-filters text-black-50 btn btn-link resetClear">
                                <a href="{{route('reporting/accounts_receivable')}}">Reset Filters</a>
                            </button>
                        </div>
                </div>
            </form>
            </div>
        </div>
       
        <div class="reportList">
            <?php 
            $receivables_group_total = 0.00;
            $receivables_group_paid = 0.00;
            $receivables_group_due = 0.00;
            ?>
            @if(isset($request->submit) && $request->submit != '' )
            <div class="report_summary_box">
                Total Receivable
                <hr class="inset">
                <span class="receivables_group_due">$0.00</span>
            </div>
            @endif
            <br>
            <br>
            @if(count($Invoices) > 0)
            <table class="table reporting report_table report_table_spaced accounts_receivable_reporting">
                <tbody>
                    <tr class="header info_header">
                        <td class="receivables_title" colspan="3">
                        </td>
                        <td class="receivables_group_total">
                            $0.00
                        </td>
                        <td class="receivables_group_paid">
                            $0.00
                        </td>
                        <td class="receivables_group_due">
                            $0.00
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
                               <a target="_blank" href="{{ route('bills/invoices/view', $aData->decode_id) }}">#{{$aData->invoice_id}} </a>
                            </td>
                            <td>
                                <?php 
                                if($aData->user_level == 2){
                                    $route = route('contacts/clients/view', $aData->uid);
                                }else if($aData->user_level == 5 && $aData->is_lead_invoice == 'yes'){
                                    $route = route('case_details/info', $aData->uid);
                                }else{
                                    $route = route('contacts/companies/view', $aData->uid);
                                }
                                ?>
                                <a target="_blank" href="{{$route}}">{{$aData->contact_name}} </a>
                            </td>
                            <td>
                            @if($aData->ctitle == null)
                                @if($aData->user_level == 5 && $aData->is_lead_invoice == 'yes')
                                    <a target="_blank" class="name" href="{{route('case_details/invoices', $aData->uid)}}">Potential Case: {{ $aData->contact_name }}</a>
                                @else
                                    None
                                @endif
                            @else
                                <a target="_blank" class="name" href="{{route('info', $aData->case_unique_number)}}">{{$aData->ctitle}}</a>
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
</style>
@endsection

@section('page-js')
<script type="text/javascript">
    $(document).ready(function () {
        $(".select2").select2({
            theme: "classic",
            allowClear: true,
            placeholder: ''
        });
        $("#trustDropdown").trigger('click');
        $(".receivables_group_due").html("$"+$("#receivables_group_due").val());
        $(".receivables_group_total").html("$"+$("#receivables_group_total").val());
        $(".receivables_group_due").html("$"+$("#receivables_group_due").val());
    });
</script>
@endsection
