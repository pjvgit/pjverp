@extends('layouts.master')
@section('title', 'Create New Invoice - Billing')
@section('main-content')
@include('billing.submenu')
<?php
$practice_area_id=($_GET['practice_area_id'])??'';
$lead_attorney_id=($_GET['lead_attorney_id'])??'';
$office_id=($_GET['office_id'])??'';
$balance_filter=($_GET['balance_filter'])??'';
$fee_structure_filter=($_GET['fee_structure_filter'])??'';
?>
<div class="separator-breadcrumb border-top"></div>
<div class="row">

    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>

                <div class="d-flex align-items-center pl-4 pb-4">
                    <h3> Create a New Invoice</h3>
                    <ul class="d-inline-flex nav nav-pills pl-4">
                        <li class="d-print-none nav-item">
                            <a href="{{route('bills/invoices/open')}}"
                                class="nav-link {{ request()->is('bills/invoices/open*') ? 'active' : '' }}">From
                                Open Balances
                            </a>
                        </li>
                        <li class="d-print-none nav-item arrow-right">
                            <a href="{{route('bills/invoices/new')}}"
                                class="nav-link {{ request()->is('bills/invoices/new*') ? 'active' : '' }} {{ request()->is('bills/invoices/load_new*') ? 'active' : '' }}">From
                                Scratch</a>
                        </li>

                    </ul>

                </div>

                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <div class="row pl-2 pb-2">
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Lead Attorney</label>
                            <select id="lead_attorney_id" name="lead_attorney_id"
                                class="form-control custom-select col select2Dropdown">
                                <option value="all">Show All</option>
                                @forelse (firmUserList() as $item)
                                    <option value="{{ $item->id }}" <?php if($lead_attorney_id==$item->id){ echo "selected=selected"; } ?>>{{ $item->full_name }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Practice Area</label>
                            <select id="practice_area_id" name="practice_area_id"
                                class="form-control custom-select col select2Dropdown">
                                <option value="all">Show All</option>
                                @forelse (casePracticeAreaList() as $key => $item)
                                    <option value="{{ $key }}" <?php if($practice_area_id==$key){ echo "selected=selected"; } ?>>{{ $item }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Office</label>
                            <select id="office_id" name="office_id" class="form-control custom-select col select2Dropdown">
                                <option value="all">Show All</option>
                                @forelse (firmOfficeList() as $key => $item)
                                    <option value="{{ $key }}" <?php if($office_id==$key){ echo "selected=selected"; } ?>>{{ $item }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Balance Type</label>
                            <select id="balance_filter" name="balance_filter" class="form-control custom-select col select2Dropdown">
                                <option value="all" <?php if($balance_filter=="all"){ echo "selected=selected"; } ?>>Show All</option>
                                <option value="unpaid" <?php if($balance_filter=="unpaid"){ echo "selected=selected"; } ?>>Unpaid Balances Only</option>
                                <option value="uninvoiced" <?php if($balance_filter=="uninvoiced"){ echo "selected=selected"; } ?>>Un-Invoiced Balances Only</option>
                            </select>

                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Fee Structure</label>
                            <select id="fee_structure_filter" name="fee_structure_filter" class="form-control custom-select col select2Dropdown">
                                <option value="all" <?php if($fee_structure_filter=="all"){ echo "selected=selected"; } ?> >Show All</option>
                                <option value="hourly" <?php if($fee_structure_filter=="hourly"){ echo "selected=selected"; } ?>>Hourly</option>
                                <option value="contingency" <?php if($fee_structure_filter=="contingency"){ echo "selected=selected"; } ?>>Contingency</option>
                                <option value="flat" <?php if($fee_structure_filter=="flat"){ echo "selected=selected"; } ?>>Flat</option>
                                <option value="mixed" <?php if($fee_structure_filter=="mixed"){ echo "selected=selected"; } ?>>Mixed</option>
                                <option value="pro_bono" <?php if($fee_structure_filter=="pro_bono"){ echo "selected=selected"; } ?>>Pro Bono</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3 mt-2 pt-2">
                            <button class="btn btn-info btn-rounded m-1 filter-btn" type="submit">Apply Filters</button>
                            <a href="{{route('bills/invoices/open')}}" class="test-clear-filters text-black-50 btn btn-link clear-filter-btn">Clear Filters</a>
                        </div>

                    </div>
                </form>
                <?php  if($upcomingInvoice<=0){?>
                <div id="open_bills_list">
                    <div class="empty-state">
                        <img alt="Invoice List Example" class="thumbnail"
                            srcset="https://assets.mycase.com/packs/empty-state/create-new-invoice-9b639a02cc.png 1x, https://assets.mycase.com/packs/empty-state/create-new-invoice@2x-33b2c97763.png 2x"
                            src="https://assets.mycase.com/packs/empty-state/create-new-invoice-9b639a02cc.png">
                        <div class="text-container" id="open-from-balances-from-scratch">
                            <h2>Invoices: From Open Balances</h2>
                            <ul>
                                <li> This page will show you any cases that have uninvoiced time/expense entries that
                                    need to be invoiced. </li>
                                <li> Since you don't have any uninvoiced entries, click the "From Scratch" option to
                                    create a brand new invoice. </li>
                            </ul>
                        </div>
                    </div>

                    <style>
                        .empty-state {
                            height: 260px;
                            margin: auto;
                            padding-top: 30px;
                            width: 700px;
                            background-image: none;
                        }

                        .arrow-right:after {
                            position: absolute;
                            pointer-events: none;
                            right: -120px;
                            top: -11px;
                            z-index: 1;
                        }

                        .empty-state .thumbnail {
                            float: left;
                            height: 223px;
                            width: 223px
                        }

                        .empty-state .text-container {
                            margin-left: 249px;
                            position: relative
                        }

                        .empty-state h2 {
                            color: #93c2e2;
                            font-size: 18pt;
                            margin-top: 0
                        }

                        .empty-state ul {
                            color: #636c72;
                            font-size: 14pt;
                            font-weight: 400;
                            padding-left: 20px;
                            text-align: left
                        }

                        .empty-state li {
                            padding-bottom: 10px
                        }

                        .arrow-right {
                            position: relative;
                        }

                        .arrow-right:after {
                            content: url('{{BASE_URL}}images/arrow-left.png');
                            position: absolute;
                            pointer-events: none;
                            right: -120px;
                            top: -11px;
                            z-index: 1;
                        }

                    </style>
                    <br>
                </div>
                <?php }else{
                    ?>
                <div class="open-bills">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="text-center align-middle">
                                    The following cases have uninvoiced time entries, expenses, flat fees, or overdue balances.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="display table table-striped table-bordered" id="invoiceGrid" style="width:100%">
                        <thead>
                            <tr>
                                <th class="col-md-auto">Contact/Case</th>
                                <th class="col-md-auto">Lead Attorney</th>
                                <th class="col-md-auto">Fee Structure</th>
                                <th class="col-md-auto">Practice Area</th>
                                <th class="col-md-auto">Uninvoiced Amount</th>
                                <th class="col-md-auto">Unpaid Balance</th>
                                <th class="col-md-auto">Payment Plan</th>
                                <th class="col-md-auto">Last Invoiced</th>
                                <th class="col-md-auto"></th>
                            </tr>
                        </thead>
                        <tbody class="lazy-load-data">
                        </tbody>
                    </table>
                </div>
                <?php
                } ?>
            </div>

            <form id="batch_billing_form"  name="batch_billing_form" accept-charset="UTF-8" method="post">
                @csrf
                <input type="hidden" name="batch[cases]" id="batch_cases" value="">
                <div id="batch_billing_tab" style="right: 0px; position: fixed; top: 65px;" class=""></div>
                <div id="batch_discount_callout" class="callout_right"
                    style="display: none; position: fixed; top: 346px;min-height:60px;max-height:350px;auto;overflow-y:scroll;">
                    <div style="position: relative;">
                        <div class="p-2">
                            <div id="batch_discount_header"></div>
                            <div id="batch_discount_lines"></div>
                            <div>
                                <a class="btn btn-outline-secondary m-1 btn-rounded add_field_button" href="#">Add
                                    Discount, Tax, or Addition</a>
                                <div id="batch_discount_loading" style="display: none; float: left; margin-left: 2px;">
                                    <img style="vertical-align: middle;" class="retina"
                                        src="https://assets.mycase.com/packs/retina/ajax_arrows-0ba8e6a4d4.gif"
                                        width="16" height="16"> </div>
                                <a class="btn btn-link btn-sm float-right" href="#"
                                    onclick="toggle_discounts();; return false;">Close<i
                                        class="fas fa-lg fa-caret-right pl-2"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="batch_billing" class="batch_billing_box"
                    style="visibility: visible; display: none; position: fixed; top: 10px;">

                    <div class="p-2">
                        {{-- <div class="showError" style="display:none"></div> --}}
                        <div class="d-flex align-items-center">
                            <h4 class="pt-2 pl-2">Batch Billing Setup</h4>
                            <div class="ml-auto btn-info  rounded p-2 ">
                                <span id="batch_num_cases_selected">0 case</span> selected
                            </div>
                        </div>
                        <hr>
                        <div>
                            <label>
                                Filter time and expense entries by date range:
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <input type="text" name="batch[start_date]" id="batch-start-date" value=""
                                            class="form-control date py-0 w-100  date_picker_from">
                                    </div>
                                    <div class="flex-grow-1 px-2">
                                        to
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="text" name="batch[end_date]" id="batch-end-date" value=""
                                            class="form-control  py-0 w-100 date_picker_to">
                                    </div>
                                </div>
                            </label>
                        </div>


                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <label>
                                    Invoice Date
                                    <input type="text" name="batch[invoice_date]" id="batch-invoice-date"
                                        value="{{ convertUTCToUserTimeZone('dateOnly') }}" class="form-control date py-0 hasDatepicker">
                                </label>
                            </div>

                            <div class="flex-grow-1 px-2">
                                <label>
                                    Payment Terms
                                    <select name="batch[payment_terms]" onchange="paymentTerm()"
                                        id="batch-payment-terms" class="custom-select">
                                        <option selected="selected" value=""></option>
                                        <option value="0">Due Date</option>
                                        <option value="1">On Receipt</option>
                                        <option value="2">Net 15</option>
                                        <option value="3">Net 30</option>
                                        <option value="4">Net 60</option>
                                    </select>
                                </label>
                            </div>

                            <div class="flex-grow-1">
                                <label>
                                    Due Date
                                    <input type="text" name="batch[due_date]" id="batch-due-date" value=""
                                        class="form-control date py-0 w-100 hasDatepicker">
                                </label>
                            </div>
                        </div>

                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <label class="w-100">
                                    Terms &amp; Conditions
                                    <textarea name="batch[terms]" id="batch_terms" class="form-control"></textarea>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <label class="w-100">
                                    Notes
                                    <textarea name="batch[notes]" id="batch_notes" class="form-control"></textarea>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex">
                            <button class="btn btn-outline-secondary m-1" type="button" onclick="toggle_discounts(); return false;"> <i class="fas fa-lg fa-caret-left pr-2"></i>Adjustments</button>
                            <div class="ml-auto btn-info  p-2 rounded">
                                <span id="batch_num_discounts">0</span>
                                applied
                            </div>
                        </div>

                        <div class="d-flex align-items-center pt-2">
                            <div class="form-check">
                                <input type="checkbox" name="batch[share]" id="batch-share-checkbox" value="1"
                                    class="batch-share-checkbox form-check-input">
                                <label class="form-check-label" for="batch-share-checkbox">
                                    Share This Invoice
                                </label>
                            </div>

                            <div class="ml-auto">
                                <select name="batch[sharing_user]" id="batch_sharing_user"
                                    class="custom-select disabled" disabled="disabled">
                                    <option value="billing_only">Billing Contact Only</option>
                                    <option value="all_contacts">All Case Contacts</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex align-items-center pt-2">
                            <div class="form-check">
                                <input type="checkbox" name="batch[draft]" id="batch_draft" value="1"
                                    class="batch-draft-checkbox form-check-input">
                                <label class="form-check-label" for="batch_draft">Save as Draft?
                                    <span id="save-as-draft-help">
                                        <span class="tooltip-wrapper" style="position: relative;">
                                            <span> <i class="question-mark-icon" data-toggle="tooltip"
                                                    data-placement="top" title="" data-original-title="This will create a batch of invoices with a Draft status.
                                          Drafts cannot be shared during batch billing as they are
                                          intended to be reviewed before sharing."></i></span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>


                        <div class="d-flex align-items-center pt-2">
                            <div class="form-check">
                                <input type="checkbox" name="batch[trust]" id="batch_trust" value="1"
                                    class="apply-trust-checkbox form-check-input">
                                <label class="form-check-label" for="batch_trust">Apply Trust Balance <span
                                        id="apply-trust-balances-help">
                                        <span class="tooltip-wrapper" style="position: relative;">
                                            <span><i class="question-mark-icon" data-toggle="tooltip"
                                                    data-placement="top" title="" data-original-title="Only applies trust balance if a single trust
                                          account is associated with case."></i></span>
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <div class="ml-auto">
                                <select name="batch[trust_account]" id="batch_trust_account" class="custom-select">
                                    <option value="none">Don't Show Trust</option>
                                    <option value="balance">Show Trust Summary</option>
                                    <option value="full">Show Trust History</option>
                                </select>
                            </div>
                        </div>



                        <div class="d-flex align-items-center pt-2 batch-forward-invoice">
                            <div class="form-check">
                                <input type="checkbox" name="batch[forward]" id="batch_forward" value="1"
                                    class="batch-forward-invoice-checkbox form-check-input">
                                <label class="form-check-label" for="batch_forward">Forward Unpaid Invoice
                                    Balances</label>
                            </div>
                        </div>

                        <hr>
                        <button type="submit" id="submit1" class="btn btn-primary submit">Create Invoices</button>

                    </div>
                </div>
                <div id="batch_billing_under" class="batch_billing_box"
                    style="display: none; position: fixed; top: 15px;">
                </div>
            </form>

        </div>
    </div>
</div>
<div id="dialog" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Please correct the following issues</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="grantAccessModalArea">
                </div>
            </div>
            <div class="modal-footer  pb-1">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Ok</button>
                </a>
            </div>
        </div>
    </div>
</div>
<div id="batchSaved" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Batch Saved</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                Successfully created <span id="createdBill"></span> bill.
            </div>
            <div class="modal-footer  pb-1">
                <a href="{{BASE_URL}}/bills/invoices/open">
                    <button class="btn btn-secondary  m-1" type="button">Continue Billing</button>
                </a>
                <a href="{{BASE_URL}}/bills/invoices?type=all">
                    <button class="btn btn-secondary  m-1" type="button">View Bills</button>
                </a>
            </div>
        </div>
    </div>
</div>

<div id="progress" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Processing Bill</h5>
            </div>
            <div class="modal-body">
                <div class="progress mb-3">
                    <div class="progress-bar w-100 progress-bar-striped progress-bar-animated bg-success" id="dynamic" role="progressbar" style="width: 100%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
            
        </div>
    </div>
</div>
<div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>

@include('case.view.timebilling.billingContactPopup')

<style>
    #batch_billing_tab {
        background: url('{{BASE_URL}}images/batch_billing_tab.png') 0 0 no-repeat;
        cursor: pointer;
        height: 152px;
        position: absolute;
        right: 0;
        top: 205px;
        width: 40px;
        z-index: 401;
    }

    .batch_billing_box {
        background-color: #eceeef;
        -webkit-box-shadow: 0 0 5px #878787;
        box-shadow: 0 0 5px #878787;
        height: 870px;
        max-height: calc(100vh - 104px);
        position: absolute;
        width: 350px;
        overflow-y: auto
    }

    .batch_billing_box .float-left {
        float: left
    }

    #batch_billing_under {
        right: 0;
        top: 155px;
        width: 405px;
        z-index: 399
    }

    #batch_billing_tab .expanded {
        background-position: 0 -200px
    }

    #batch_discount_callout {
        background-color: #eceeef;
        border: 1px solid #9d9d9d;
        -webkit-box-shadow: 0 0 5px #878787;
        box-shadow: 0 0 5px #878787;
        position: absolute;
        right: 405px;
        top: 486px;
        width: 450px;
        z-index: 401
    }

    #batch_billing {
        right: 0;
        top: 150px;
        width: 400px;
        z-index: 400
    }

    #main_content {
        min-height: 720px
    }

    .ui-progressbar-value {
        background-image: url(/packs/pbar-ani-f92b637e12.gif)
    }

    #batch_online_disabled {
        display: none
    }

    .batch-success-text {
        font-size: 13px
    }

    .batch-success-table {
        background-color: var(--white);
        color: var(--black);
        width: 500px
    }

    .batch-success-table a {
        color: var(--link-color);
        outline: 0
    }

    .batch-success-table th:first-child {
        text-align: left
    }

    .batch-success-table th:nth-child(2) {
        text-align: right
    }

    .batch-success-table td {
        border-bottom: 1px solid var(--gray-lighter);
        padding: 10px
    }

    .batch-success-table td,
    .batch-success-table th {
        border-left: 1px solid var(--gray-lighter);
        border-right: 1px solid var(--gray-lighter)
    }

    .batch-success-table th {
        border-top: 1px solid var(--gray-lighter);
        padding-left: 10px;
        padding-right: 10px
    }

    .batch-success-table .total-amount,
    .batch-success-table th {
        background-color: var(--table-head-bg);
        color: var(--table-head-color)
    }

    .batch-success-table td:first-child {
        text-align: left
    }

    .batch-success-table td:nth-child(2) {
        text-align: right
    }

    .batch-success-table td.total-amount-text {
        border: 0;
        text-align: right
    }

    .payment-plan-tooltip {
        background: url(/packs/payment_plan_invoice_icon-82c97579a8.svg) 0 0 no-repeat
    }

    .payment-plan-autopay-tooltip,
    .payment-plan-tooltip {
        cursor: pointer;
        display: inline-block;
        height: 20px;
        position: relative;
        width: 20px
    }

    .payment-plan-autopay-tooltip {
        background: url(/packs/payment_plan_invoice_recurring_icon-4ea99b7266.svg) 0 0 no-repeat
    }

    .icon-hidden {
        visibility: hidden
    }

    i.question-mark-icon {
        background-image: url("{{BASE_URL}}svg/question_mark_icon.svg");
        height: 19px;
        width: 19px;
        display: inline-block;
    }

    i {
        font-size: 15px;
    }
    .payment-plan-tooltip {
    background: url('{{BASE_URL}}svg/payment_plan_invoice_icon.svg') 0 0 no-repeat;
}
</style>
@section('page-js-inner')
<script type="text/javascript">
var start = 0;
    $(document).ready(function () {     
        $("#preloader").show();   
        loadMoreData(0);

        $('#batchSaved').on('hidden.bs.modal', function () {
            window.location.reload();
        });

        $(".select2Dropdown").select2({
            theme: "classic"
        });
        $('.hasDatepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': false,
            'todayHighlight': true
        });
        $('#batch-invoice-date').datepicker({
            format: 'mm/dd/yyyy',
            'todayHighlight': true
        }).on('changeDate', function (e) {
            paymentTerm();
        });
        $('.date_picker_from').datepicker({
            format: 'mm/dd/yyyy',
            'todayHighlight': true
        }).on('changeDate', function (selected) {
            var maxDate = new Date(selected.date.valueOf());
            $('.date_picker_to').val();
            $('.date_picker_to').datepicker('setStartDate', maxDate);
        });

        $('.date_picker_to').datepicker({
            format: 'mm/dd/yyyy',
            'todayHighlight': true
        }).on('changeDate', function (selected) {
            var maxDate = new Date(selected.date.valueOf());
            $('.date_picker_from').datepicker('setEndDate', maxDate);
        });

        $('.dropdown-toggle').dropdown();
        var groupColumn = 0;
        var invoiceGrid = $('#invoiceGrid1').DataTable({
            "columnDefs": [{
                "visible": false,
                "targets": groupColumn
            }],
            serverSide: true,
            "dom": '<"top">rt<"bottom"l><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "aaSorting": [],
            "order": [
                [0, "desc"]
            ],
            "ajax": {
                url: baseUrl + "/bills/invoices/loadUpcomingInvoices",
                type: "post",
                "data": function(d){
                    d.load = 'true';
                    d.type = "all";
                    d.practice_area_id = $("#practice_area_id").val();
                    d.lead_attorney_id = $("#lead_attorney_id").val();
                    d.firm_office_id = $("#office_id").val();
                    d.billing_method = $("#fee_structure_filter").val();
                    d.balance_filter = $("#balance_filter").val();
                },
                error: function () {
                    $("#invoiceGrid_processing").css("display", "none");
                }
            },
            // "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true, // enable page resize
            pageLength: {{USER_PER_PAGE_LIMIT}},
            lengthMenu : [ [10, 25, 50, 100, 99999999], [10, 25, 50, 100, "All"] ],
            columns: [{
                    data: 'contact_name'
                },
                {
                    data: 'id'
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'contact_name',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                },
                {
                    data: 'id',
                    'sorting': false
                }
            ],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                $('td:eq(0)', nRow).html(
                    '<div class="text-left pl-3"><input id="select-row-74" client_id=' + aData
                    .selected_user + '  case_id=' + aData.case_id + '  class="client_box allSelect task_checkbox ' + aData
                    .selected_user + '  client_' + aData.selected_user +
                    '" type="checkbox" value="' + aData.id + '" name="expenceId[' + aData.id +
                    ']"> <a class="name" href="' + baseUrl + '/court_cases/' + aData
                    .case_unique_number + '/info">' + aData.ctitle + '</a></div>');

                $('td:eq(1)', nRow).html('<div class="text-left">' + aData.lead_attorney_name +
                    '</div>');

                $('td:eq(2)', nRow).html('<div class="text-left">' + aData.fee_structure +
                    '</div>');

                $('td:eq(3)', nRow).html('<div class="text-left">' + aData.practice_area_filter +
                    '</div>');
                $('td:eq(4)', nRow).html('<div class="text-left">' + aData.uninvoiced_balance +
                    '</div>');
                $('td:eq(5)', nRow).html('<div class="text-left">' + aData.unpaid_balance +'</div>');
                if(aData.payment_plan_active_for_case=="yes"){
                    $('td:eq(6)', nRow).html('<div class="text-left"><a class="payment-plan-tooltip" data-toggle="tooltip" data-placement="top" title="" data-original-title="Payment Plans are active on this case."></a></div>');
                }else{
                    $('td:eq(6)', nRow).html('<div class="text-left">--</div>');
                }
                $('td:eq(7)', nRow).html('<div class="text-left">' + aData.last_invoice +
                    '</div>');
                console.log("client: "+aData.selected_user);
                if(aData.selected_user && aData.deleted_at == null && aData.is_billing_contact == 'yes') {
                    $('td:eq(8)', nRow).html('<div class="text-left"><a class="name" href="' + baseUrl +
                        '/bills/invoices/new?page=open&court_case_id=' + aData.ccid + '&token=' + aData
                        .token + '">Invoice This Case</a></div>');
                } else {
                    $('td:eq(8)', nRow).html('<div class="text-left"><a class="name" data-toggle="modal" data-target="#editBillingContactPopup"\
                        data-placement="bottom" href="javascript:;" onclick="editBillingContactPopup(' + aData.ccid + ');" data-case-id="' + aData.ccid + '">Setup Billing</a></div>');
                }

            },
            "initComplete": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip();
                $('#checkall').on('change', function () {
                    $('.task_checkbox').prop('checked', $(this).prop("checked"));
                    if ($('.task_checkbox:checked').length == "0") {
                        $('#actionbutton').attr('disabled', 'disabled');
                    } else {
                        $('#actionbutton').removeAttr('disabled');
                    }
                });

                $('.client_box').change(function () {
                    var client_id = $(this).attr('client_id');
                    if ($('.client_' + client_id + ':checked').length == $('.client_' +
                            client_id).length) {
                        $('.mainBox_' + client_id).prop('checked', true);
                    } else {
                        $('.mainBox_' + client_id).prop('checked', false);

                    }


                    if ($('.allSelect:checked').length == $('.allSelect').length) {
                        $('#selectAll').prop('checked', true);
                        $("#batch_billing_tab").toggleClass("expand");
                        $("#batch_billing").toggle("slide", {direction: "right"});
                        $("#batch_billing_tab").animate({ right: "400px"});
                    } else {
                        $('#selectAll').prop('checked', false);
                        $("#batch_billing_tab").removeClass("expand");
                    }

                   
                    if($('.client_box:checked').length>1){
                      $("#batch_num_cases_selected").html($('.client_box:checked').length+' cases');

                    }else{
                      $("#batch_num_cases_selected").html($('.client_box:checked').length+' case');

                    }
                });

                $('#selectAll').on('change', function () {
                    $('input.allSelect').each(function () {
                        $('input.allSelect').prop('checked', $("#selectAll").prop(
                            "checked"));
                    });

                    if ($("#batch_billing_tab").hasClass("expand")) {
                    }else{
                      $("#batch_billing_tab").toggleClass("expand");
                      $("#batch_billing").toggle("slide", {direction: "right"});
                      $("#batch_billing_tab").animate({ right: "400px"});
                    }
                    if($('.client_box:checked').length>1){
                      $("#batch_num_cases_selected").html($('.client_box:checked').length+' cases');
                    $("#batch_cases_selected").html("("+$('.client_box:checked').length+' cases)');

                    }else{
                      $("#batch_num_cases_selected").html($('.client_box:checked').length+' case');
                    $("#batch_cases_selected").html("("+$('.client_box:checked').length+' case)');

                    }
                });
            },
            "drawCallback": function (settings) {
                var api = this.api();
                var rows = api.rows({
                    page: 'current'
                }).nodes();
                var last = null;

                api.column(groupColumn, {
                    page: 'current'
                }).data().each(function (group, i) {
                    $maker = api.row(rows[i]).data().maker;
                    $id = api.row(rows[i]).data().selected_user;
                    $cid = api.row(rows[i]).data().uid;
                    group = (group != "") ? group : "No Billing Contact";
                    if (last !== group) {
                        if(group != "No Billing Contact") {
                            $(rows).eq(i).before(
                                '<tr class="group"><td colspan="15"><input type="checkbox" onclick="selectClient(' +
                                $id + ')" id="checkAllClientCase" class="allSelect ' + $id +
                                ' mainBox_' + $id + '"> <a class="name" href="' + baseUrl +
                                '/contacts/clients/'+$cid+'">' + group + '</a></td></tr>');
                        } else {
                            $(rows).eq(i).before(
                                '<tr class="group"><td colspan="15">' + group + '</td></tr>');
                        }
                        last = group;
                    }
                });


            }
        });

        // For filter
        // $(document).on('click', ".filter-btn", function() {
        //     $('#invoiceGrid').DataTable().ajax.reload(null, false);
        // });

        // For reset/clear filter
        $(document).on('click', ".clear-filter-btn", function() {
            window.location.href='bills/invoices/open';
        });

        $('#checkAllClientCase').on('change', function () {
            $('.task_checkbox').prop('checked', $(this).prop("checked"));
            if ($(this).prop("checked") == true) {
                $('.task_checkbox').parent().parent().addClass('table-info');

            } else {
                $('.task_checkbox').parent().parent().removeClass('table-info');
            }

        });
        $('#invoiceGrid tr:first').after(
            '<tr class="group"><td colspan="15"><input type="checkbox" id="selectAll" > Select All <span id="batch_cases_selected"></span></td></tr>'
        );
        // Order by the grouping
        // $('#invoiceGrid tbody').on('click', 'tr.group', function () {
        //     var currentOrder = table.order()[0];
        //     if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
        //         table.order([groupColumn, 'desc']).draw();
        //     } else {
        //         table.order([groupColumn, 'asc']).draw();
        //     }
        // });
    $("#batch_billing_tab").on("click", function () {
        $("#batch_billing").toggle("slide", {
            direction: "right"
        });
        $("#batch_discount_callout").hide();

        $("#batch_billing_tab").toggleClass("expand");
        if ($("#batch_billing_tab").hasClass("expand")) {
            $("#batch_billing_tab").animate({
                right: "400px"
            });
        } else {

            $("#batch_billing_tab").animate({
                right: "0px"
            });
        }
    });
    // $("#batch_billing_form").validate({
    //     rules: {
    //         'discounts[amount][]': {
    //             required: true
    //         }
    //     },
    //     messages: {
    //         'discounts[amount][]': {
    //             required: "Item can't be blank"
    //         }
    //     }
    // });
    
    $('#batch_billing_form').submit(function (e) {
        // var current_progress = 0;
        // var interval = setInterval(function() {
        //         current_progress += 10;
        //         $("#dynamic")
        //         .css("width", current_progress + "%")
        //         .attr("aria-valuenow", current_progress);
        //         if (current_progress >= 100)
        //             clearInterval(interval);
        //     }, 1000);
        $("#progress").modal("show");
        beforeLoader();
        e.preventDefault();
        if (!$('#batch_billing_form').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        var array = [];
        $('input.client_box:checked').each(function () {
            array.push($(this).attr('case_id'));
        });
        // alert(array);
        dataString = $("#batch_billing_form").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/createInvoiceBatch", // json datasource
            data: dataString+'&case_id='+JSON.stringify(array),
            success: function (res) {
                beforeLoader();
                if (res.errors != '') {
                    $('.showError').html('');
                    var errotHtml ='<ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $('#batch_billing').animate({
                        scrollTop: 0
                    }, 'slow');
                    $("#grantAccessModalArea").html(errotHtml);
                    $("#progress").modal("hide");
                    $( "#dialog" ).modal("show");
                  
                    afterLoader();
                    return false;
                } else {
                    $("#progress").modal("hide");
                    $("#batch_billing_form")[0].reset();
                    $("#createdBill").html(res.countInvoice);
                    $("#batchSaved").modal("show");
                    afterLoader();
                }
            },
            error: function (xhr, status, error) {
                $("#progress").modal("hide");
                $( "#dialog" ).modal("show");
                $('#grantAccessModalArea').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('#grantAccessModalArea').append(errotHtml);
                $('#batch_billing').animate({
                    scrollTop: 0
                }, 'slow');
                afterLoader();
            }
        });
    });
    
});

    function paymentTerm() {
        var setDate = '';
        var selectdValue = $("#batch-payment-terms option:selected").val();
        var bill_invoice_date = $("#batch-invoice-date").val();
        if (selectdValue == 0 || selectdValue == 1) {
            var minDate = $('#batch-invoice-date').datepicker('getDate');
            $('#batch-due-date').datepicker("setDate", minDate);
        } else if (selectdValue == 2) {
            CheckIn = $("#batch-invoice-date").datepicker('getDate');
            CheckOut = moment(CheckIn).add(15, 'day').toDate();
            $('#batch-due-date').datepicker('update', CheckOut);

        } else if (selectdValue == 3) {
            CheckIn = $("#batch-invoice-date").datepicker('getDate');
            CheckOut = moment(CheckIn).add(30, 'day').toDate();
            $('#batch-due-date').datepicker('update', CheckOut);

        } else {
            CheckIn = $("#batch-invoice-date").datepicker('getDate');
            CheckOut = moment(CheckIn).add(60, 'day').toDate();
            $('#batch-due-date').datepicker('update', CheckOut);
        }
    }

    function selectClient(i) {
        $('input.' + i).each(function () {
            $('.' + i).prop('checked', $(this).prop("checked"));
        });
        if ($('.allSelect:checked').length == $('.allSelect').length) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }

        if ($("#batch_billing_tab").hasClass("expand")) {
        }else{
            if ($('.allSelect:checked').length == $('.allSelect').length) {
                $("#batch_billing_tab").toggleClass("expand");
                $("#batch_billing").toggle("slide", {direction: "right"});
                $("#batch_billing_tab").animate({ right: "400px"});
            }
        }
        if($('.client_box:checked').length>1){
          $("#batch_num_cases_selected").html($('.client_box:checked').length+' cases');
          $("#batch_cases_selected").html("("+$('.client_box:checked').length+' cases)');

        }else{
          $("#batch_num_cases_selected").html($('.client_box:checked').length+' case');
          $("#batch_cases_selected").html("("+$('.client_box:checked').length+' case)');

        }
        $('input.client_box:checked').each(function () {
           
        });

    }
    function toggle_discounts(){
        $("#batch_discount_callout").toggle("slide", {
            direction: "right"
        });
    } 
    
    var max_fields      = 50; //maximum input boxes allowed
	var wrapper   		= $("#batch_discount_lines"); //Fields wrapper
	var add_button      = $(".add_field_button"); //Add button ID
	
	var x = 1; //initlal text box count
	$(add_button).click(function(e){ //on add input button click
		e.preventDefault();
		if(x < max_fields){ //max input box allowed
			x++; //text box increment
			$(wrapper).append('<div><div id="discount_'+x+'" class="batch-billing-discount-container"><div class="batch-billing-discount-inputs-container pt-2"><div class="d-flex align-items-baseline"><div> <label class="w-100"> Item <select name="discounts[discount_type][]" id="discount_label_'+x+'" class="custom-select"><option value="discount">Discount</option><option value="intrest">Interest</option><option value="tax">Tax</option><option value="addition">Addition</option></select> </label></div><div class="pl-1"> <label> Applied To <select name="discounts[discount_applied_to][]" id="discount_applied_'+x+'" class="custom-select discount-applied-to"><option value="flat_fees">Flat Fees</option><option value="time_entries">Time Entries</option><option value="expenses">Expenses</option><option value="balances_forward">Balance Forward Total</option><option value="subtotal">Sub-Total</option></select> </label></div><div class="pl-1"> <label> Amount <input type="text" name="discounts[amount][]" id="discounts__amount" value="" class="form-control"> </label></div><div class="pl-1"> <label> Type <select name="discounts[discount_amount_type][]" id="discount_type_'+x+'" class="custom-select"><option value="percentage">%</option><option value="amount">$</option></select> </label></div><div class="ml-auto pl-1"> <a href="#" onclick="remove_discount('+x+'); return false;" ><i class="fas fa-trash align-middle p-2"></i></a></div></div><div class="d-flex pb-1"> <label class="w-100"> Notes <input type="text" name="discounts[notes][]" id="discounts__notes" value="" class="form-control"> </label></div><hr></div></div></div>'); //add input box
		}
        $("#batch_num_discounts").html($(".batch-billing-discount-inputs-container").length);
	});
	
	$(wrapper).on("click",".remove_field", function(e){ //user click on remove text
		e.preventDefault(); $(this).parent('div').remove(); x--;
	})
    function remove_discount(x){
        $("#discount_"+x).remove(); x--;
        $("#batch_num_discounts").html($(".batch-billing-discount-inputs-container").length);

    }

    function add_discount(){
       var s='<div id="discount_'+x+'" class="batch-billing-discount-container"><div class="batch-billing-discount-inputs-container pt-2"><div class="d-flex align-items-baseline"><div> <label class="w-100"> Item <select name="discounts[][discount_type]" id="discount_label_'+x+'" class="custom-select"><option value="discount">Discount</option><option value="interest">Interest</option><option value="tax">Tax</option><option value="addition">Addition</option></select> </label></div><div class="pl-1"> <label> Applied To <select name="discounts[][discount_applied_to]" id="discount_applied_'+x+'" class="custom-select discount-applied-to"><option value="flat_fees">Flat Fees</option><option value="time_entries">Time Entries</option><option value="expenses">Expenses</option><option value="balances_forward">Balance Forward Total</option><option value="subtotal">Sub-Total</option></select> </label></div><div class="pl-1"> <label> Amount <input type="text" name="discounts[][amount]" id="discounts__amount" value="" class="form-control"> </label></div><div class="pl-1"> <label> Type <select name="discounts[][discount_amount_type]" id="discount_type_'+x+'" class="custom-select"><option value="percent">%</option><option value="amount">$</option></select> </label></div><div class="ml-auto pl-1"> <a href="#" onclick="remove_discount(); return false;"><i class="delete-icon"></i></a></div></div><div class="d-flex pb-1"> <label class="w-100"> Notes <input type="text" name="discounts[][notes]" id="discounts__notes" value="" class="form-control"> </label></div><hr></div></div>';

    }
   
    $('#batch-share-checkbox').on('change', function () {
        if ($(this).prop("checked") == true) {
            $('#batch_sharing_user').removeAttr('disabled');

        } else {
            $('#batch_sharing_user').attr("disabled","disabled");    
        }

    });

    var lazyLoadingActive = 1;
    var pageLength =  '100';
    var roundPage = 1;
    $(window).scroll(function() {
        if($(window).scrollTop() + $(window).height() >= $(document).height()) {
            if(lazyLoadingActive == 1)
                loadMoreData(roundPage);
        }
    });

    function loadMoreData(round){
        $("#preloader").show(); 
        // beforeLoader();       
        $.ajax(
        {
            url: baseUrl + "/bills/invoices/loadUpcomingInvoicesWithLoader",
            type: "post",
            data: {
                load : 'true',
                type : "all",
                practice_area_id : $("#practice_area_id").val(),
                lead_attorney_id : $("#lead_attorney_id").val(),
                firm_office_id : $("#office_id").val(),
                billing_method : $("#fee_structure_filter").val(),
                balance_filter : $("#balance_filter").val(),
                pageLength : pageLength,
                start : (round == 0) ?  0 : ((round * pageLength) + 1)
            },
            error: function () {
                $("#invoiceGrid_processing").css("display", "none");
            }
        })
        .done(function(result)
        {
            roundPage = round + 1;
            var res = JSON.parse(result);
            var resultHtml = '';
            var last = null;
            if(!$.trim(res.data)){
                lazyLoadingActive = 999;
            }
            $.each(res.data, function(i, v){
                var contactGroup = i.split('_');

                resultHtml +='<tr class="group"><td colspan="15"><input type="checkbox" onclick="selectClient(' +
                contactGroup[1] + ')" id="checkAllClientCase"class="allSelect  mainBox_' + contactGroup[1] + ' "> <a class="name" href="' + baseUrl +
                '/contacts/clients/'+contactGroup[1]+'">'+contactGroup[0]+'</a></td></tr>';

                $.each(v, function(j, aData){
                    start = start + 1;
                    // resultHtml +='<tr class="group"><td colspan="15"><input type="checkbox" onclick="selectClient(' +
                    // aData.id + ','+ aData.case_id +')" id="checkAllClientCase"class="allSelect case_id' + aData.case_id + ' ' + aData.id +
                    // ' mainBox_' + aData.id + ' "> <a class="name" href="' + baseUrl +
                    // '/contacts/clients/'+aData.selected_user+'">'+aData.contact_name+'</a></td></tr>';
                                    
                    resultHtml +='<tr><td><div class="text-left pl-3"><input id="select-row-74" client_id=' + aData
                    .selected_user + '  case_id="' + aData.case_id + '"  class="client_box allSelect case_id' + aData.case_id + ' task_checkbox ' + aData
                    .selected_user + '  client_' + aData.selected_user +
                    '" type="checkbox" value="' + aData.id + '" name="expenceId[' + aData.id +
                    ']"  >&nbsp;<a class="name" href="' + baseUrl + '/court_cases/' + aData
                    .case_unique_number + '/info">' + aData.ctitle + '</a></div></td>';

                    resultHtml +='<td><div class="text-left">' + aData.lead_attorney_name +
                    '</div></td>';

                    resultHtml +='<td><div class="text-left">' + aData.fee_structure +
                        '</div></td>';

                    resultHtml +='<td><div class="text-left">' + aData.practice_area_filter +
                        '</div></td>';
                    resultHtml +='<td><div class="text-left">' + aData.uninvoiced_balance +
                        '</div></td>';

                    resultHtml +='<td><div class="text-left">' + aData.unpaid_balance +'</div></td>';
                    
                    if(aData.payment_plan_active_for_case=="yes"){
                        resultHtml +='<td><div class="text-left"><a class="payment-plan-tooltip" data-toggle="tooltip" data-placement="top" title="" data-original-title="Payment Plans are active on this case."></a></div></td>';
                    }else{
                        resultHtml +='<td><div class="text-left">--</div></td>';
                    }
                    resultHtml +='<td><div class="text-left">' + aData.last_invoice +
                        '</div></td>';
                    console.log("client: "+aData.selected_user);
                    console.log("case_id: "+aData.case_id);                    
                    console.log("case_setup: "+aData.setup_billing);                    
                    if(aData.setup_billing == 'yes') {
                        resultHtml +='<td><div class="text-left"><a class="name" href="' + baseUrl +
                            '/bills/invoices/new?page=open&court_case_id=' + aData.ccid + '&token=' + aData
                            .token + '">Invoice This Case</a></div></td>';
                    } else {
                        resultHtml +='<td><div class="text-left"><a class="name" data-toggle="modal" data-target="#editBillingContactPopup"\
                            data-placement="bottom" href="javascript:;" onclick="editBillingContactPopup(' + aData.ccid + ');" data-case-id="' + aData.ccid + '">Setup Billing</a></div></td>';
                    }
                    resultHtml +='</tr>';
                });
               
            });
            // afterLoader();
            $("#preloader").hide();
            $(".lazy-load-data").append(resultHtml);
            // $(".lazy-load-data").html('');
            // $(".lazy-load-data").html(resultHtml);
            // $("#preloader").hide();
            reloadBilling();
        })
        .fail(function(jqXHR, ajaxOptions, thrownError)
        {
            console.log('server not responding...');
            afterLoader();
            $("#preloader").hide();
        });
    }

function reloadBilling(){
    $('[data-toggle="tooltip"]').tooltip();

    $('#checkall').on('change', function () {
        $('.task_checkbox').prop('checked', $(this).prop("checked"));
        if ($('.task_checkbox:checked').length == "0") {
            $('#actionbutton').attr('disabled', 'disabled');
        } else {
            $('#actionbutton').removeAttr('disabled');
        }
    });

    $('#selectAll').on('change', function () {
        $('input.allSelect').each(function () {
            $('input.allSelect').prop('checked', $("#selectAll").prop(
                "checked"));
        });

        if ($("#batch_billing_tab").hasClass("expand")) {
        }else{
            $("#batch_billing_tab").toggleClass("expand");
            $("#batch_billing").toggle("slide", {direction: "right"});
            $("#batch_billing_tab").animate({ right: "400px"});
        }
        if($('.client_box:checked').length>1){
            $("#batch_num_cases_selected").html($('.client_box:checked').length+' cases');
            $("#batch_cases_selected").html("("+$('.client_box:checked').length+' cases)');
        }else{
            $("#batch_num_cases_selected").html($('.client_box:checked').length+' case');
            $("#batch_cases_selected").html("("+$('.client_box:checked').length+' case)');
        }
    });

    $('.client_box').on('change', function () {
        var client_id = $(this).attr('client_id');
        if ($('.client_' + client_id + ':checked').length == $('.client_' +
                client_id).length) {
            $('.mainBox_' + client_id).prop('checked', true);
        } else {
            $('.mainBox_' + client_id).prop('checked', false);
        }

        if ($('.allSelect:checked').length == $('.allSelect').length) {
            $('#selectAll').prop('checked', true);
            $("#batch_billing_tab").toggleClass("expand");
            $("#batch_billing").toggle("slide", {direction: "right"});
            $("#batch_billing_tab").animate({ right: "400px"});
        } else {
            $('#selectAll').prop('checked', false);
            $("#batch_billing_tab").removeClass("expand");
        }
        
        if($('.client_box:checked').length>1){
            $("#batch_num_cases_selected").html($('.client_box:checked').length+' cases');
            $("#batch_cases_selected").html("("+$('.client_box:checked').length+' cases)');
        }else{
            $("#batch_num_cases_selected").html($('.client_box:checked').length+' case');
            $("#batch_cases_selected").html("("+$('.client_box:checked').length+' case)');
        }
    });
}

function selectClient(i) {
    $('input.' + i).each(function () {
        $('.' + i).prop('checked', $(this).prop("checked"));
    });
    if ($('.allSelect:checked').length == $('.allSelect').length) {
        $('#selectAll').prop('checked', true);
    } else {
        $('#selectAll').prop('checked', false);
    }

    if ($("#batch_billing_tab").hasClass("expand")) {
    }else{
        if ($('.allSelect:checked').length == $('.allSelect').length) {
            $("#batch_billing_tab").toggleClass("expand");
            $("#batch_billing").toggle("slide", {direction: "right"});
            $("#batch_billing_tab").animate({ right: "400px"});
        }
    }
    if($('.mainBox_'+i).is(":checked")){
        $('.mainBox_'+i).prop('checked', true);
        $(".client_"+i).prop('checked', true);
    }else{
        $('.mainBox_'+i).prop('checked', false);
        $(".client_"+i).prop('checked', false);
    } 
    if($('.client_box:checked').length>1){
        $("#batch_num_cases_selected").html($('.client_box:checked').length+' cases');
        $("#batch_cases_selected").html("("+$('.client_box:checked').length+' cases)');
    }else{
        $("#batch_num_cases_selected").html($('.client_box:checked').length+' case');
        $("#batch_cases_selected").html("("+$('.client_box:checked').length+' case)');
    }
}

</script>
@stop
@endsection
