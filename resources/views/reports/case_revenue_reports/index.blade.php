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
                            <option value="all">All</option>
                            <option value="hourly">Hourly</option>
                            <option value="contingency">Contingency</option>
                            <option value="flat">Flat Fee</option>
                            <option value="mixed">Mixed</option>
                            <option value="pro_bono">Pro Bono</option>
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
            </form>
            </div>
        </div>
        <div class="reportList">
            @if(count($cases) > 0)
            <table class="table reporting report_table report_table_spaced accounts_receivable_reporting">
                <tbody>
                    <tr class="header info_header">
                        <td width="200px" class="receivables_title" >
                        </td>
                        <th class="receivables_group_total" colspan="12">
                            Billed
                        </th>
                        <th class="receivables_group_paid" colspan="8">
                            Collected
                        </th>
                    </tr>
                    <tr class="header">
                        <th width="200px">Case Name</th>
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
                    </tr>
                    @foreach($cases as $k => $case)
                    <tr class="">
                        <td>{{$case->case_title}}</td>
                        <td></td>
                        <td>{{$case->caseDuration}}</td>
                        <td>{{$case->caseTaskTimeEntry}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endforeach
                    <tr class="header total_row">
                        <th width="200px">Page Total (Sum of the {{count($cases)}} rows displayed)</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            @else
                <span class="alert alert-info">No billing activity found.</span>
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
        $('#hiddenLable').show();        
        var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        $(".main-content-wrap").remove();
        window.print();
        window.location.reload();
        return false;  
    }
    function exportCSV(){
        $("#preloader").show();
        $("#export_csv").val(1);
        $("#export_csv").prop("type",'text');
        $("#submitForm").trigger('click');
    }
</script>
@endsection
