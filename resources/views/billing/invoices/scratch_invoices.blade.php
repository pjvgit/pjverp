@extends('layouts.master')
@section('title', 'Create New Invoice - Billing')
@section('main-content')
@include('billing.submenu')
<?php
if(!isset($adjustment_token)){
    $adjustment_token=round(microtime(true) * 1000);
} 
?>
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-md-12">
        <form class="saveInvoiceForm" id="saveInvoiceForm" name="saveInvoiceForm" method="POST" action="{{route('bills/invoices/addInvoiceEntry')}}">
            @csrf
            <div class="card text-left">
                <div class="card-body" id="main_content">
                    <span id="responseMain"></span>
                    <div class="d-flex align-items-center pl-4 pb-4">
                        <h3> Create a New Invoice</h3>
                        <ul class="d-inline-flex nav nav-pills pl-4">
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices/open')}}"
                                    class="nav-link {{ request()->is('bills/invoices/open*') ? 'active' : '' }} {{ request()->is('bills/invoices/load_new*') ? 'active' : '' }}">From
                                    Open Balances
                                </a>
                            </li>
                            <li class="d-print-none nav-item arrow-right">
                                <a href="{{route('bills/invoices/new')}}"
                                    class="nav-link {{ request()->is('bills/invoices/new*') ? 'active' : '' }}  {{ request()->is('bills/invoices/load_new*') ? 'active' : '' }}">From
                                    Scratch</a>
                            </li>
                        </ul>
                    </div>
                    <div id="invoice_info_table" class="pt-2">
                        <table class="invoice" style="width: 100%; margin: 10px 0px 0px; padding: 0px;">
                            <tbody>
                                <tr>
                                    <td rowspan="4" style="padding: 0px; vertical-align: top; width: 130px;">
                                        <i class="invoice-banner-new"></i>
                                    </td>
                                    <td
                                        style="padding-right: 5px; padding-top: 18px; text-align: right; vertical-align: top; width: 80px;">
                                        Contact
                                    </td>
                                    <td style="width: 400px; white-space: nowrap; vertical-align: bottom;">
                                        <div>
                                            <div class="clearfix">
                                                <select class="form-control" id="contact"
                                                    onchange="fetchClientAddress()" name="contact" style="width: 70%;"
                                                    placeholder="Search for an existing contact or company">
                                                    <option></option>
                                                    <optgroup label="Client">
                                                        {{-- <?php foreach($ClientList as $key=>$val){ ?>
                                                        <option uType="client"  value="{{$val->id}}"> {{substr($val->name,0,200)}} (Client)</option>
                                                        <?php } ?> --}}
                                                        @forelse ($ClientList as $key => $item)
                                                        <option uType="client"  value="{{ $key }}" {{ (isset($client_id) && $key == $client_id) ? "selected" : "" }}> {{ substr($item,0,200) }} (Client)</option>
                                                        @empty
                                                        @endforelse
                                                    </optgroup>
                                                    <optgroup label="Company">
                                                        {{-- <?php foreach($CompanyList as $CompanyListKey=>$CompanyListVal){ ?>
                                                        <option uType="company" value="{{$CompanyListVal->id}}"> {{substr($CompanyListVal->first_name,0,200)}} (Company)</option><?php } ?> --}}
                                                        @forelse ($CompanyList as $key => $item)
                                                        <option uType="company"  value="{{ $key }}"> {{ substr($item,0,200) }} (Company)</option>
                                                        @empty
                                                        @endforelse
                                                    </optgroup>
                                                </select>
                                                <a data-toggle="modal"  data-target="#AddContactModal" data-placement="bottom" href="javascript:;"  onclick="AddContactModal();">Add new contact</a>

                                            </div>
                                            <span id="1Error"></span>
                                            
                                       
                                        </div>
                                    </td>
                                    <td style="width: auto;">&nbsp;</td>
                                    <td style="width: 120px; text-align: right; padding-right: 5px; ">
                                        Invoice #</td>
                                    <td>
                                        <div class="form_control" style="width: 200px;">
                                            <?php 
                                            $formatted_value = sprintf("%06d", $maxInvoiceNumber);
                                            ?>
                                            <input class="form-control" name="invoice_number_padded" value="{{ old('invoice_number_padded', $formatted_value) }}">
                                            @error('invoice_number_padded')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding-right: 5px; padding-top: 18px; text-align: right; vertical-align: top; width: 80px;">
                                        Matter</td>
                                    <td style="width: 350px; white-space: nowrap; vertical-align: bottom;">
                                        <div style="position: relative;">
                                            <div id="matter_dropdown" class="">
                                                <div>
                                                    <select onchange="changeCase()"   name="court_case_id" id="court_case_id"
                                                        class="custom-select select2Dropdown" style="width: 70%;">
                                                        <option value=""></option>
                                                        <option value="none">None</option>
                                                        <?php foreach($caseListByClient as $key=>$val){ ?>
                                                        <option
                                                            <?php if($val->id==$client_id){ echo "selected=selected";} ?>
                                                            value="{{$val->id}}"
                                                            <?php if($val->id==$case_id){ echo "selected=selected";} ?>>
                                                            {{substr($val->case_title,0,200)}}
                                                        </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <span id="2Error"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="width: auto;">&nbsp;</td>
                                    <td style="width: 120px; text-align: right; padding-right: 5px;">
                                        Invoice Date
                                    </td>
                                    <td>
                                        <input id="bill_invoice_date" class="form-control date datepicker"
                                            name="bill_invoice_date" value="{{date('m/d/Y')}}">
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding-right: 5px; padding-top: 18px; text-align: right; vertical-align: top; width: 80px;">
                                        Address</td>
                                    <td rowspan="4"
                                        style="width: 350px; white-space: nowrap; vertical-align: top; padding-top: 16px;">
                                        <textarea id="bill_address_text" name="bill[address_text]" class="form-control"style="width: 70%; height: 104px; resize: none; overflow-y: hidden;"></textarea>
                                    </td>
                                    <td style="width: auto;">&nbsp;</td>
                                    <td style="width: 120px; text-align: right; padding-right: 5px;">
                                        Payment Terms</td>
                                    <td><select id="bill_payment_terms" onchange="paymentTerm()" class="custom-select form-control select2Dropdown"
                                            name="payment_terms">
                                            <option value="" selected="selected"></option>
                                            <option value="0">Due Date</option>
                                            <option value="1">Due on Receipt</option>
                                            <option value="2">Net 15</option>
                                            <option value="3">Net 30</option>
                                            <option value="4">Net 60</option>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td style="width: auto;">&nbsp;</td>
                                    <td style="width: 120px; text-align: right; padding-right: 5px; ">
                                        Due Date</td>
                                    <td><input id="bill_due_date" class="form-control date datepicker" name="bill_due_date"
                                            value=""></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td style="width: auto;">&nbsp;</td>
                                    <td colspan="2"
                                        style="width: 120px; text-align: right; padding-right: 5px; vertical-align: top; padding-top: 18px;">
                                        Automated Reminders</td>
                                    <td style="width: 110px; padding-right: 15px; padding-top: 23px; vertical-align: top;">
                                        <label
                                            style="display: inline; position: relative; top: -7px; left: 5px; color: rgb(0, 112, 187);"
                                            class="switch pr-5 switch-success mr-3">
                                            <span data-toggle="tooltip" data-placement="bottom"
                                                title="When a due date is entered and there is a balance due, all shared contacts will be sent automated reminders 7 days before the due date, on the due date, and 7 days after the due date."><i
                                                    class="pl-1 fas fa-question-circle fa-lg"></i></span>

                                            <input type="checkbox" name="automated_reminders" id="automated_reminders"><span
                                                class="slider">
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td style="width: auto;">&nbsp;</td>
                                    <td colspan="2" style="text-align: right; padding-right: 5px;">
                                        Status</td>
                                    <td style=" vertical-align: bottom;">
                                        <select id="bill_sent_status" name="bill_sent_status" class="custom-select">
                                            <option value="Draft">Draft</option>
                                            <option value="Unsent" selected>Unsent</option>
                                            <option value="Sent">Sent</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="invoice-date-range-filter" class="invoice_entry_header"
                        style="margin-top: 10px; position: relative;">
                        <div id="range_select_block"
                            style="position: absolute; display: none; top: 0px; right: 0px; width: 500px; height: 60px; z-index: 50;">
                        </div>
                        <table style="color: black;">
                            <tbody>
                                <tr>
                                    <td
                                        style="width: 100%; color: black; text-align: right; padding-right: 10px; padding-top: 9px;vertical-align: middle;">
                                        <input type="checkbox" name="range_check_box" id="range_check_box" value="1">
                                        <label for="range_check_box">Filter by date range</label>
                                    </td>
                                    <td style="color: black; vertical-align: middle;" class="range_select disabled">From:
                                    </td>
                                    <td><input value="{{$from_date}}" class="date range_select disabled form-control  datepicker"
                                            style="width: 115px;" disabled="disabled" type="text" name="bill_from_date"
                                            id="bill_from_date"></td>
                                    <td style="color: black; vertical-align: middle;" class="range_select disabled">to</td>
                                    <td><input value="{{$bill_to_date}}" class="date range_select disabled  form-control "
                                            style="width: 115px;" disabled="disabled" type="text"  name="bill_to_date"
                                            id="bill_to_date"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="entries" style="margin: 5px;">
                        <div id="entries" style="margin: 5px;">
                            <div style="text-align: center; font-style: italic; border-bottom: 1px solid #8f4a4a; padding-bottom: 20px; padding-top: 15px;">Please select a client and a matter for this invoice
                            </div>
                       </div>

                    <div id="invoice_totals" style="margin: 5px; margin-top: 20px; border: 1px solid #DBDBDB;">
                        <table class="data invoice_entries">
                            <tbody>
                                <tr class="footer no-border totals">
                                    <td style="width: 710px;vertical-align: top;" rowspan="3">
                                        <div class="p-3">
                                            <h3 style="font-size: 20px; margin: 5px;">Invoice Totals</h3>
                                        </div>
                                    </td>
                                    <td style="text-align: right; width: 210px;">
                                        <div class="locked">
                                            <!-- This hidden span holds the case subtotal and updated via the recalculate_table function -->
                                            <!-- the value is used to calculate & display the final sub-total and total for the bill -->
                                            <span id="bill-subtotal-amount" style="display: none;">0.00</span>

                                            <div id="flat_fee_total_label" class="flat-fee-totals"
                                                style="border: none; padding-bottom: 7px; display: none;">
                                                Flat Fee Sub-Total:
                                            </div>
                                            <div id="time_entry_total_label" class="time-entries-totals"
                                                style="border: none; padding-bottom: 7px;">
                                                Time Entry Sub-Total:
                                            </div>
                                            <div id="expense_total_label" style="border: none; padding-bottom: 7px;"
                                                class="expense-totals">
                                                Expense Sub-Total:
                                            </div>
                                            <div id="sub_total_label" style="font-weight: bold; border: none;">
                                                Sub-Total:
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: right; width: 105px;">
                                        <div class="locked" style="padding-bottom: 15px;">
                                            <div id="flat_fee_bottom_total" class="flat-fee-totals"
                                                style="border: none; padding-bottom: 7px; display: none;">
                                                $<span id="flat_fee_total_amount"></span>
                                            </div>
                                            <div style="border: none; padding-bottom: 7px;" class="time-entries-totals">
                                                $<span id="time_entry_total_amount"
                                                    class="time_entry_total_amount">0.00</span>
                                            </div>
                                            <div style="border: none; padding-bottom: 7px;" class="expense-totals">
                                                $<span id="expense_total_amount"
                                                    class="expense_total_amount">0.00</span>
                                            </div>
                                            <div style="border: none;">
                                                $<span id="sub_total_amount" class="sub_total_amount">0.00</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="width: 55px;">
                                        &nbsp;
                                    </td>
                                </tr>

                                <tr class="footer no-border totals">
                                    <td style="text-align: right; font-weight: bold;">
                                        <div class="locked" style="padding-bottom: 7px;">
                                            <div id="transfers_bottom_label" style="padding-top: 7px; display: none;">
                                                Balance Forward:
                                            </div>
                                            <?php
                                            $discount=$addition=$expenseAmount=$timeEntryAmount=0;
                                             if($discount!="0"){?>
                                            <div class="billing-discounts-area">
                                                Discounts:
                                            </div>
                                            <?php } ?>
                                            <?php if($addition!="0"){?>
                                            <div class="billing-additions-area">
                                                Additions:
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="locked" style="padding-bottom: 15px;">
                                            <div class="billing-additions-area" style="display: none;">
                                                $<span id="additions_total_amount">0.00</span>
                                            </div>
                                            <?php if($discount!="0"){?>
                                            <div class="billing-discounts-area">
                                                ($<span id="discounts_section_total"
                                                    class="table_total amount">{{$discount}}</span>)
                                            </div>
                                            <?php } ?>
                                            <?php if($addition!="0"){?>
                                            <div style="border: none; padding-top: 7px;" class="billing-additions-area ">
                                                $<span id="additions_section_total"
                                                    class="table_total amount">{{$addition}}</span>
                                            </div>
                                            <?php } ?>

                                        </div>
                                    </td>
                                    <td>
                                        &nbsp;
                                    </td>
                                </tr>

                                <tr class="footer no-border totals">
                                    <td style="text-align: right; font-weight: bold;">
                                        <div class="locked" style="">
                                            Total:
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="locked">
                                            $<span id="final_total" class="final_total">0.00</span>
                                        </div>
                                    </td>
                                    <td>
                                        &nbsp;
                                    </td>
                                </tr>
                              
                            </tbody>
                        </table>
                    </div>

                    <div style="margin: 20px;">
                        <table style="width: 100%; margin: 0; padding: 0;" class="invoice">
                        <tbody style="margin: 0; padding: 0;">
                            <tr>
                            <td style="width: 50%; padding: 5px; font-weight: bold; white-space: nowrap;">
                                Terms &amp; Conditions
                            </td>
                            <td style="width: 50%; padding: 5px; ">
                                <span style="font-weight: bold;">Notes</span>
                                <span style="font-size: 11px;">(will be shared with clients)</span>
                            </td>
                            </tr>
                            <tr>
                            <td style="padding: 0px 5px 5px 5px;">
                                <textarea style="width: 100%; height: 150px;" class="boxsizingBorder" name="bill[terms_and_conditions]" id="bill_terms_and_conditions"></textarea>
                            </td>
                            <td style="padding: 0px 5px 5px 5px;">
                                <textarea style="width: 100%; height: 150px;" class="boxsizingBorder" name="bill[bill_notes]" id="bill_bill_notes"></textarea>
                            </td>
                            </tr>
                        </tbody>
                        </table>
                    </div>



                    <div class="invoice_option_header clearfix">
                        <div style="float: right;" class="mt-2">
                            <label class="switch switch-success"><span>Enabled</span>
                                <input type="checkbox" name="payment_plan" id="payment_plan"><span class="slider"></span>
                            </label>
                            
                        </div>
                        <h3 id="payment-plan" class="invoice_header">
                        <img src="{{BASE_URL}}public/svg/payment_plan.svg" width="28" height="28">
                        Payment Plan
                        </h3>
                    </div>
                    <div id="payment_plan_details" style="margin-top: 10px; margin-right: 10px; display: none;">
                        <table style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td id="payment_plan_left_side" style="vertical-align: top;">
                                        <table class="data field_wrapper" data-testid="payment-plan-installment-table" >
                                            <tbody>
                                                <tr>
                                                    <th style="width: 30px; border-right: none;">
                                                        &nbsp;
                                                    </th>
                                                    <th style="width: 150px; border-left: none;">
                                                        Due Date
                                                    </th>
                                                    <th style="width: 120px; border-right: none;">
                                                        Amount
                                                    </th>
                                                    <th style="width: 30px; border-left: none;">
                                                        &nbsp;
                                                    </th>
                                                    <th>
                                                        <p style="display: none; padding-left: 20px;" class="autopay-field m-0">Status</p>
                                                    </th>
                                                </tr>
                    
                                                {{-- <tr class="invoice_entry payment_plan_row tablePaymentPlanRemove" id="row_0">
                                                    <td style="vertical-align: center; text-align: center; border-right: none;" >
                                                        <div class="payment_plan_entry">
                                                            <a class="image_link_sprite image_link_sprite_cancel"
                                                                href="javascript:void(0);" onclick="removePaymentPlan(0);"></a>
                                                        </div>
                                                    </td>
                    
                                                    <td style="border-left: none;" class="">
                                                        <div id="invoice_entry_date_plan161122040617875" class="invoice-entry-date"
                                                            data-entry-id="plan161122040617875">
                                                            <input value="" id="invoice_entry_date_text_plan161122040617875"
                                                            style="width: 100%;border:none;"
                                                            class="invoice-entry-date-text boxsizingBorder datepicker" type="text"
                                                            name="new_payment_plans[][due_date]" placeholder="Choose Date">
                                                        </div>
                                                    
                                                    </td>
                                                    <td style="text-align: right; border-right: none;" class="">
                                                        <input value="0.00" id="invoice_plan_amount_text_plan161122040617875"
                                                                style="width: 100%; text-align: right;border:none;"
                                                                class="boxsizingBorder edit_payment_plan_amount" type="text"
                                                                name="new_payment_plans[][amount]">
                                                    
                                                    </td>
                                                    <td style="vertical-align: center; text-align: center; border-right: none;">
                                                        <div class="payment_plan_entry">
                                                            <a class="image_link_sprite image_link_sprite_cancel"
                                                                href="javascript:void(0);" ><i class="fas fa-pen"></i></a>
                                                        </div>
                                                    </td>
                                                    <td style="vertical-align: middle;" class="tablePaymentPlanEdit">
                                                        <p class="autopay-field m-0" data-testid="autopay-field"
                                                            style="display: none; padding-left: 20px;">
                    
                                                        </p>
                                                    </td>
                                                </tr>
                    
                    
                    
                                                <tr class="invoice_entry payment_plan_row tablePaymentPlanRemove" id="row_1">
                                                    <td style="vertical-align: center; text-align: center; border-right: none;" >
                                                        <div class="payment_plan_entry">
                                                            <a class="image_link_sprite image_link_sprite_cancel"
                                                                href="javascript:void(0);" onclick="removePaymentPlan(1);"></a>
                                                        </div>
                                                    </td>
                    
                                                    <td style="border-left: none;" class="">
                                                        <div id="invoice_entry_date_plan161122040617875" class="invoice-entry-date"
                                                            data-entry-id="plan161122040617875">
                                                            <input value="" id="invoice_entry_date_text_plan161122040617875"
                                                            style="width: 100%;border:none;"
                                                            class="invoice-entry-date-text boxsizingBorder datepicker" type="text"
                                                            name="new_payment_plans[][due_date]" placeholder="Choose Date">
                                                        </div>
                                                    
                                                    </td>
                                                    <td style="text-align: right; border-right: none;" class="">
                                                        <input value="0.00" id="invoice_plan_amount_text_plan161122040617875"
                                                                style="width: 100%; text-align: right;border:none;"
                                                                class="boxsizingBorder edit_payment_plan_amount" type="text"
                                                                name="new_payment_plans[][amount]">
                                                    
                                                    </td>
                                                    <td style="vertical-align: center; text-align: center; border-right: none;">
                                                        <div class="payment_plan_entry">
                                                            <a class="image_link_sprite image_link_sprite_cancel"
                                                                href="javascript:void(0);" ><i class="fas fa-pen"></i></a>
                                                        </div>
                                                    </td>
                                                    <td style="vertical-align: middle;" class="tablePaymentPlanEdit">
                                                        <p class="autopay-field m-0" data-testid="autopay-field"
                                                            style="display: none; padding-left: 20px;">
                    
                                                        </p>
                                                    </td>
                                                </tr> --}}

                                            </tbody>
                                        </table>

                                        <table class="data" data-testid="payment-plan-installment-table">
                                            <tbody>
                                                <tr class="footer">
                                                    <td style="width:26%;">
                                                        <a class="add add_button" id="add-pmt-plan" style="margin-left: 15px;" href="javascript:void(0);"><i class="fas fa-plus align-middle"></i> Add Date</a>
                                                    </td>
                                                    <td  style="width:74%;" style="text-align: left; font-weight: bold;" >
                                                        <div class="locked mr-2" style="color: red;width: auto;float: left;">
                                                            $<span id="payment_plan_balance">0.00</span>
                                                        </div>
                                                        <div class="locked" style="font-style: italic; color: gray;">
                                                            (remaining balance)
                                                        </div>
                                                    </td>
                                                
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td id="payment_plan_right_side" style="width: 400px; height: 100%; vertical-align: top;">
                                        <div data-testid="payment-plan-form-container">
                                            <div id="generate-payment-plan-form-root" class="pl-2"
                                                style="width: 100%; height: 100%; min-height: 200px;">
                                                <div class="generate-payment-plan-container">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h4><strong>Generate Payment Plan</strong></h4>
                                                        </div>
                                                        <div class="card-body">
                                                            <form class="paymentPlansForm" id="paymentPlansForm" name="paymentPlansForm" method="POST">
                                                                @csrf
                                                                <div class="row form-group">
                                                                    <div class="col-md-3"><label for="date-field"
                                                                            class="col-form-label ">Start Date</label></div>
                                                                    <div class="col-md-9">
                                                                        <input id="start_date" name="start_date" class="form-control datepicker" value="{{date('m/d/Y', strtotime('+1 day'))}}">

                                                                    </div>
                                                                </div>
                                                                <div class="mb-0 row form-group">
                                                                    <div class="col-md-3"><label for="amount-per-installment-field"
                                                                            class="col-form-label ">Amount/<br>Installment</label></div>
                                                                    <div class="col-md-6">
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend"><span
                                                                                    class="input-group-text">$</span></div><input
                                                                                id="amount_per_installment_field"  name="amount_per_installment_field" class="form-control number"
                                                                                value="">
                                                                        </div>
                                                                        <div class="d-flex invalid-feedback"></div>
                                                                    </div>
                                                                    <div class="col-md-3"><label class="pr-0 ">Per payment</label></div>
                                                                </div>
                                                                <div class="row form-group">
                                                                    <div class="pr-1 col-md-3 offset-md-3">
                                                                        <div class="input-group">
                                                                            <input id="number_installment_field"
                                                                                data-testid="number-installment-field" min="1" type="number"
                                                                                class="form-control" value="" name="number_installment_field"s></div>
                                                                        <div class="d-flex invalid-feedback"></div>
                                                                    </div>
                                                                    <div class="pl-0 col-md-6">
                                                                        <label for="number-installment-field"
                                                                            class="pl-0 col-form-label ">Installments</label></div>
                                                                </div>
                                                                <div class="row form-group">
                                                                    <div class="col-md-3"><label for="installment-frequency-field"
                                                                            class="col-form-label ">Repeat</label></div>
                                                                    <div class="col-md-9">
                                                                        <div class="input-group"><select id="installment_frequency_field"
                                                                                name="installment_frequency_field" class="form-control">
                                                                                <option value="weekly">Weekly</option>
                                                                                <option value="biweekly">Bi-Weekly</option>
                                                                                <option value="monthly">Monthly</option>
                                                                            </select></div>
                                                                    </div>
                                                                </div>
                                                                <div class="row form-group">
                                                                    <div class="pr-0 col-md-6">
                                                                        <div
                                                                            class="col-form-label d-flex align-items-center h-100 form-check">
                                                                            <input id="with_first_payment" name="with_first_payment" type="checkbox"
                                                                                class="my-0 form-check-input"><label
                                                                                for="checkbox-boolean-input-4" 
                                                                                class="my-0 form-check-label ">With first payment of</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="pl-0 col-md-6">
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend"><span
                                                                                    class="input-group-text">$</span></div>
                                                                                    <input id="first_payment_amount" disabled name="first_payment_amount" maxlength="15" class="form-control number" value="">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row form-group">
                                                                    <div class="col-md-12">
                                                                        <button type="button"  name="installmentBreak" class="submitbutton btn btn-outline-secondary btn-rounded m-1" style="width: 40%;"><strong>Apply</strong></button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pl-2 mt-2">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h4><strong>Automatic Payments</strong></h4>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="pl-2 mt-2">
                                                            <div class="row ">
                                                                <div class="col-md-4">
                                                                    <img src="{{ asset('images/automated_payment_plan_marketing.png') }}"  height="80" >
                                                                </div>
                                                                <div class="px-1 col-md-8">
                                                                    <div>The easy, hassle-free way to help your firm electronically collect
                                                                        payment plan installments. <br><strong>Just set-and-forget</strong>
                                                                    </div>
                                                                </div>
                                                            </div><br>
                                                            <div class="row ">
                                                                <div class="col-md-6 offset-md-4">
                                                                    <a target="_blank" href="#" class="btn btn-secondry btn-rounded m-1" style="width: 100%;"><button class="btn btn-primary btn-rounded m-1" type="button">Learn More</button></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pl-2 text-right"><a href="#">Tell us what you think!</a></div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    
                        <p class="" data-automated-reminders-payment-plan-message="true">
                            <strong>Note:</strong> Automated reminders will be sent based on the next installment date. If Automatic Payment
                            is On, reminders will show automatic payment status.
                        </p>
                    </div>
                    
                    <div id="bill_sharing_options" style=" padding-top: 15px; border-top: 1px dotted #9f9f9f;">
                        <div class="invoice_option_header clearfix">
                            <h3 class="invoice_header">
                                <img src="{{BASE_URL}}public/svg/share.svg" width="28" height="28">
                                Share Invoice via Client Portal
                            </h3>
                        </div>
                    
                        <div class="sharing-table-container" data-bill_id="" data-sharing-from="bill_form">
                            <div style="margin-top: 20px; font-style: italic;">
                                Please select a matter to view sharing options.
                              </div>
                        </div>
                    </div>
                    <div class="ml-auto d-print-none float-right pt-4">
                        <div class="loader-bubble loader-bubble-primary innerLoader float-left mr-5" id="innerLoader" style="display: none;">
                        </div>
                        &nbsp;
                            <!-- <button class="btn btn-secondary btn-rounded  m-1" type="button" data-dismiss="modal">Cancel</button> -->
                            <a data-toggle="modal"  data-target="#cancelEdit" data-placement="bottom" href="javascript:;"   title="Delete" data-testid="delete-button" class="btn btn-secondary btn-rounded  m-1" >Cancel</a>
                        </a>
                        <button type="submit" id="SaveInvoiceButton" name="saveinvoice" class="btn btn-primary btn-rounded submit submitbutton">Save Invoice</button></div>
                  
                </div>
            </div>
        </form>
    </div>
</div>
</div>
<div id="AddContactModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="step-1-aDD-CONTACT">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<!-- start cancel -->
<div id="cancelEdit" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Confirm</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="eventID">
                        <form class="cancelEdit" id="cancelEdit" name="cancelEdit" method="POST">
                            <div id="showError2" style="display:none"></div>
                            @csrf
                            <input class="form-control" id="task_id" value="" name="task_id" type="hidden">
                            <div class=" col-md-12">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label">
                                        Are you sure you want to cancel and discard all of your changes?
                                        <input type="radio" style="display:none;" name="delete_event_type"
                                            checked="checked" class="pick-option mr-2" value="SINGLE_EVENT">
                                    </label>
                                </div>
                                <div class="form-group row float-right">
                                    <a href="#">
                                        <button class="btn btn-secondary  m-1" type="button"
                                            data-dismiss="modal">No</button>
                                    </a>
                                    <a href="open" class="btn btn-primary ladda-button example-button m-1">Yes</a>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                    <div class="col-md-2 form-group mb-3">
                                        <div class="loader-bubble loader-bubble-primary" id="innerLoader1"
                                            style="display: none;"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end cancel -->

<style>
    .tooltip {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 12px;
    }

    .tooltip-inner {
        max-width: 200px;
        padding: 3px 8px;
        color: #fff;
        text-align: center;
        background-color: #000;
        border-radius: 4px;
    }

    div.invoice_case_gradient {
        background-color: #93c2e2;
        padding: 10px 20px;
        margin: 15px 0;
    }

    div.invoice_entry_header {
        background-color: #e9e9e9;
        padding: 3px;
    }

    div.invoice_entry_header h3 {
        margin: 0;
        color: #000;
        font-weight: 400;
        font-size: 17px;
        line-height: 30px;
        padding: 0 0 0 20px;
    }

    .remove-all-entries-icon {
        float: right;
        padding-right: 5px;
        padding-top: 2px;
        text-align: right;
    }


    table.invoice_entries td pre.new_edit {
        border: 1px solid transparent;
        font-size: 12px;
        display: block;
        cursor: text;
        color: #111;
        margin: 0;
        padding: 10px 5px;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    table.invoice_entries td a.new_edit_link {
        border: 1px solid transparent;
    }

    /* table.invoice_entries td a {
        display: block;
        cursor: text;
        padding: 10px 5px;
        color: #111;
        border: none;
    } */

    table.invoice_entries td,
    table.invoice_entries th {
        border: 1px solid #efefef;
    }

    #facebox table.data th,
    #main_content table.data th,
    #main_details table.data th,
    .absolute_header table.data th,
    table.reminders th {
        background-color: #dedede;
        padding: 6px 5px;
        color: #000;
        font-weight: 700;
        border: 1px solid #dbdbdb;
        text-align: left;
        overflow: hidden;
        font-size: 13px;
    }

    #facebox table.data,
    #main_content table.data,
    #main_details table.data,
    .absolute_header table.data,
    table.reminders {
        width: 100%;
        border-collapse: collapse;
    }

    table.invoice_entries tr.footer td {
        color: #111;
    }

    div.invoice_entry_header h3 {
        margin: 0;
        color: #000;
        font-weight: 400;
        font-size: 17px;
        line-height: 30px;
        padding: 0 0 0 20px;
    }

    div.invoice_entry_header {
        background-color: #e9e9e9;
        padding: 3px;
    }

    div.invoice_option_header {
        background-color:#93C2E2;
        color: #fff;
        padding: 9px;
    }

    table.field_wrapper td {
        border: 1px solid #efefef;
        padding:3px;
    }
    .get-paid-now-text{border-bottom:1px solid var(--gray);border-left:1px solid var(--gray);border-right:1px solid var(--gray)}.get-paid-now-ads li{list-style:none}.get-paid-now-ads li:before{bottom:26px;color:var(--success);content:"\2022";display:block;font-size:53px;max-height:0;max-width:0;position:relative;right:24px}.show-me-how-btn{min-width:250px}.green_box{height:100%;min-height:270px;width:100%}

</style>

@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        @if(!empty($caseMaster))
        $("#court_case_id").trigger("change");
        @endif

        $("#payment_plan").prop('checked',false);
        $("#first_payment_amount").attr("disabled",true);
        $("#with_first_payment").attr("checked",false);
       
        $("#time_entry_sub_total_text").val({{$timeEntryAmount}});
        $("#expense_sub_total_text").val({{$expenseAmount}});
        $("#discount_total_text").val({{$discount}});
        $("#addition_total_text").val({{$addition}});

        recalculate();
        $("#contact").select2({
            theme: "classic",
            allowClear: true,
            placeholder: "Select...",
        });
        $('.datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            startDate: "dateToday",
            'todayHighlight': true
        });
        $('[data-toggle="tooltip"]').tooltip();

        $('.tdTime').hover(
            function () { //this is fired when the mouse hovers over
                $(this).find('.image_link_sprite_cancel').show();
            },
            function () { //this is fired when the mouse hovers out
                $(this).find('.image_link_sprite_cancel').hide();
            }
        );

        $('.tdTimeExpense').hover(
            function () { //this is fired when the mouse hovers over
                $(this).find('.image_link_sprite_cancel').show();
            },
            function () { //this is fired when the mouse hovers out
                $(this).find('.image_link_sprite_cancel').hide();
            }
        );


        $('.tablePaymentPlanRemove').hover(
            function () { //this is fired when the mouse hovers over
                $(this).find('.image_link_sprite_cancel').show();
            },
            function () { //this is fired when the mouse hovers out
                $(this).find('.image_link_sprite_cancel').hide();
            }
        );

        $('.tablePaymentPlanEdit').hover(
            function () { //this is fired when the mouse hovers over
                $(this).find('.image_link_sprite_cancel').show();
            },
            function () { //this is fired when the mouse hovers out
                $(this).find('.image_link_sprite_cancel').hide();
            }
        );
        
        $('.image_link_sprite_cancel').hide();

        $('.invoice_entry_nonbillable_time').change(function () { //".checkbox" change 
            var id = $(this).attr('id');
            var val = $(this).val;
            var sum = 0;
            $('input[name="linked_staff_checked_share[]"]').each(function (i) {
                if (!$(this).is(":checked")) {
                    // do something if the checkbox is NOT checked
                    var g = parseFloat($(this).attr("priceattr"));
                    sum += g;
                    $(this).parent().prev().css('text-decoration', '');
                    $(this).parent().prev().prev().css('text-decoration', '');
                    $(this).parent().prev().prev().prev().css('text-decoration', '');
                } else {
                    $(this).parent().prev().css('text-decoration', 'line-through');
                    $(this).parent().prev().prev().css('text-decoration', 'line-through');
                    $(this).parent().prev().prev().prev().css('text-decoration','line-through');
                }
            });
            $(".table_total").html(sum);
            $("#time_entry_sub_total_text").val(sum);
            $('.table_total').number(true, 2);

            $(".time_entry_total_amount").html(sum);
            $('.time_entry_total_amount').number(true, 2);



            recalculate();

        });

        var wrapper = $('.field_wrapper'); //Input field wrapper

        for(var i=1;i<=2;i++){
            var fieldHTML = '<tr class="invoice_entry payment_plan_row tablePaymentPlanRemove" id="row_'+x+'"><td style="vertical-align: center; text-align: center; border-right: none;" class="" ><div class="payment_plan_entry"> <a class="image_link_sprite image_link_sprite_cancel " href="javascript:void(0);"></a></div></td><td style="border-left: none;" class=""><div id="invoice_entry_date_plan161122040617875" class="invoice-entry-date" data-entry-id="plan161122040617875"> <input value="" id="invoice_entry_date_text_plan161122040617875" style="width: 100%;border:none;" class="invoice-entry-date-text boxsizingBorder datepicker" type="text" name="new_payment_plans['+i+'][due_date]" placeholder="Choose Date"></div></td><td style="text-align: right; border-right: none;" class=""> <input value="0.00" id="invoice_plan_amount_text_plan161122040617875" style="width: 100%; text-align: right;border:none;" class="boxsizingBorder edit_payment_plan_amount" type="text" name="new_payment_plans['+i+'][amount]" onblur="installmentCalculation(this)"></td><td style="vertical-align: center; text-align: center; border-right: none;dispaly:none;"><div class="payment_plan_entry"> <a class="image_link_sprite image_link_sprite_cancel" href="javascript:void(0);" ><i class="fas fa-pen"></i></a></div></td><td style="vertical-align: middle;" class="tablePaymentPlanEdit"><p class="autopay-field m-0" data-testid="autopay-field" style="display: none; padding-left: 20px;"></p></td></tr>'; //New input field html 
            $(wrapper).append(fieldHTML); //Add field html
            $('.tablePaymentPlanRemove').hover(
                function () { //this is fired when the mouse hovers over
                    $(this).find('.image_link_sprite_cancel').show();
                },
                function () { //this is fired when the mouse hovers out
                    $(this).find('.image_link_sprite_cancel').hide();
                }
            );

            $('.tablePaymentPlanEdit').hover(
                function () { //this is fired when the mouse hovers over
                    $(this).find('.image_link_sprite_cancel').show();
                },
                function () { //this is fired when the mouse hovers out
                    $(this).find('.image_link_sprite_cancel').hide();
                }
            );

            $('.datepicker').datepicker({
                'format': 'm/d/yyyy',
                'autoclose': true,
                'todayBtn': "linked",
                'clearBtn': true,
                startDate: "dateToday",
                 'todayHighlight': true
            });
        }


        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('.field_wrapper'); //Input field wrapper
        
        var x = 3; //Initial field counter is 1
        
        
        //Once add button is clicked
        $(addButton).click(function(){
            x++; //Increment field counter

            var fieldHTML = '<tr class="invoice_entry payment_plan_row tablePaymentPlanRemove" id="row_'+x+'"><td style="vertical-align: center; text-align: center; border-right: none;" class="remove_button" ><div class="payment_plan_entry"> <a class="image_link_sprite image_link_sprite_cancel " href="javascript:void(0);"><i class="fas fa-times"></i></a></div></td><td style="border-left: none;" class=""><div id="invoice_entry_date_plan" class="invoice-entry-date" data-entry-id="plan161122040617875"> <input value="" id="invoice_entry_date_text_plan161122040617875" style="width: 100%;border:none;" class="invoice-entry-date-text boxsizingBorder datepicker" type="text" name="new_payment_plans['+x+'][due_date]" placeholder="Choose Date"></div></td><td style="text-align: right; border-right: none;" class=""> <input value="0.00" id="invoice_plan_amount_text_plan161122040617875" style="width: 100%; text-align: right;border:none;" class="boxsizingBorder edit_payment_plan_amount" type="text" name="new_payment_plans['+x+'][amount]" onblur="installmentCalculation(this)"></td><td style="vertical-align: center; text-align: center; border-right: none;dispaly:none;"><div class="payment_plan_entry"> <a class="image_link_sprite image_link_sprite_cancel" href="javascript:void(0);" ><i class="fas fa-pen"></i></a></div></td><td style="vertical-align: middle;" class="tablePaymentPlanEdit"><p class="autopay-field m-0" data-testid="autopay-field" style="display: none; padding-left: 20px;"></p></td></tr>'; //New input field html 
            $(wrapper).append(fieldHTML); //Add field html

            $('.tablePaymentPlanRemove').hover(
                function () { //this is fired when the mouse hovers over
                    $(this).find('.image_link_sprite_cancel').show();
                },
                function () { //this is fired when the mouse hovers out
                    $(this).find('.image_link_sprite_cancel').hide();
                }
            );

            $('.tablePaymentPlanEdit').hover(
                function () { //this is fired when the mouse hovers over
                    $(this).find('.image_link_sprite_cancel').show();
                },
                function () { //this is fired when the mouse hovers out
                    $(this).find('.image_link_sprite_cancel').hide();
                }
            );

            $('.datepicker').datepicker({
                'format': 'm/d/yyyy',
                'autoclose': true,
                'todayBtn': "linked",
                'clearBtn': true,
                startDate: "dateToday",
                'todayHighlight': true
            });
        });
        
        //Once remove button is clicked
        $(wrapper).on('click', '.remove_button', function(e){
            e.preventDefault();
            $(this).parent('tr').remove(); //Remove field html
            x--; //Decrement field counter
        });
        $('.invoice_expense_entry_nonbillable_time').change(function () { //".checkbox" change 
            var id = $(this).attr('id');
            var val = $(this).val;
            var sum = 0;
            $('input[name="expense_entry[]"]').each(function (i) {
                if (!$(this).is(":checked")) {
                    // do something if the checkbox is NOT checked
                    var g = parseFloat($(this).attr("priceattr"));
                    sum += g;
                    $(this).parent().prev().css('text-decoration', '');
                    $(this).parent().prev().prev().css('text-decoration', '');
                    $(this).parent().prev().prev().prev().css('text-decoration', '');
                } else {
                    $(this).parent().prev().css('text-decoration', 'line-through');
                    $(this).parent().prev().prev().css('text-decoration', 'line-through');
                    $(this).parent().prev().prev().prev().css('text-decoration','line-through');
                }
            });
            $(".table_expense_total").html(sum);
            $("#expense_sub_total_text").val(sum);
            $('.table_expense_total').number(true, 2);

            $(".expense_total_amount").html(sum);
            $('.expense_total_amount').number(true, 2);


            recalculate();

        });
        $('input[name="linked_staff_checked_share[]"]').prop('checked', false);
        $('input[name="expense_entry[]"]').prop('checked', false);
        $('.row_total').number(true, 2);
        $('#amountFiled').number(true, 2);
        $('.amount').number(true, 2);

        
        $("input:checkbox#payment_plan").click(function () {
            $("#payment_plan_details").slideToggle();
        });
        $("input:checkbox#with_first_payment").click(function () {
            if ($(this).is(":checked")) {
                $("#first_payment_amount").removeAttr("disabled");
            } else {
                $("#first_payment_amount").attr("disabled",true);    
                $("#first_payment_amount").val("");
            }
        });

        $("#amount_per_installment_field").blur(function(){
            var currentAmount=$(this).val().replace(',', '');
            var totalAmount= parseFloat($("#final_total_text").val());
            // var totalInstalment=totalAmount/currentAmount;
            // $("#number_installment_field").val(Math.round(totalInstalment));
            var firstInstallment= parseFloat($("#first_payment_amount").val().replace(',', ''));
            if(firstInstallment != '' && firstInstallment > 0) {
                totalAmount = totalAmount - firstInstallment;
            }
            var totalInstalment=totalAmount/currentAmount;
            if(firstInstallment != '' && firstInstallment > 0) {
                totalInstalment += 1;
            }
            $("#number_installment_field").val(Math.ceil(totalInstalment));

        }); 
        $("#number_installment_field").blur(function(){
            var installmentNumber=$(this).val();
            var totalAmount= parseFloat($("#final_total_text").val());
            var totalInstalment=totalAmount/installmentNumber;
            $("#amount_per_installment_field").val(Math.ceil(totalInstalment));
        }); 

        $("#first_payment_amount").blur(function(){
            var totalAmount= parseFloat($("#final_total_text").val().replace(',', ''));
            var firstInstallment= parseFloat($("#first_payment_amount").val().replace(',', ''));
            var amount_per_installment_field= parseFloat($("#amount_per_installment_field").val().replace(',', ''));
            var debitedAmount=totalAmount-firstInstallment;
             var totalInstalment=debitedAmount/amount_per_installment_field;
            $("#number_installment_field").val(Math.ceil(totalInstalment) + 1);

        });
        $("#SaveInvoiceButton").on("click",function(){
            $(this).attr("disabled",true);
            $("#innerLoader").show();
        });
        $("#saveInvoiceForm").validate({
            rules: {
                contact: {
                    required: true
                },
                court_case_id: {
                    required: true
                }
            },
            messages: {
                contact: {
                    required: "Billing user can't be blank"
                },
                court_case_id: {
                    required: "Please select a client"
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#contact')) {
                    error.appendTo('#1Error');
                } else if (element.is('#court_case_id')) {
                    error.appendTo('#2Error');
                } else {
                    element.after(error);
                }
            }
        });

        $('#saveInvoiceForm').submit(function (e) {
            beforeLoader();
            if (!$('#saveInvoiceForm').valid()) {
                afterLoader();
                return false;
            }else{
                return true;
            }
        });

        $("#paymentPlansForm1").validate({
            rules: {
                start_date: {
                    required: true
                },
                amount_per_installment_field: {
                    required: true
                },
                number_installment_field: {
                    required: true,
                    min: 1
                },
                percentage: {
                    min: 0,
                    max: 100
                },
            },
            messages: {
                start_date: {
                    required: "Start date can't be blank"
                },
                amount_per_installment_field: {
                    required: "Amount is required"
                },
                number_installment_field: {
                    required: "Number is required"
                },
            }
        });
        $('#paymentPlansForm1').submit(function (e) {
        
            beforeLoader();
            e.preventDefault();
            if (!$('#paymentPlansForm').valid()) {
                afterLoader();
                return false;
            }
            var dataString=firstInstallment = '';
            dataString = $("#paymentPlansForm").serialize();
           
            var number_installment_field=$("#number_installment_field").val();
            var amount_per_installment_field=$("#amount_per_installment_field").val();
            var installment_frequency_field=$("#installment_frequency_field").val();
            var start_date=$("#start_date").val();
            var start_date=$("#start_date").val();
            var start_date=$("#start_date").val();
           
           var headerHtml='<tr><th style="width: 30px; border-right: none;"> &nbsp;</th><th style="width: 150px; border-left: none;"> Due Date</th><th style="width: 120px; border-right: none;"> Amount</th><th style="width: 30px; border-left: none;"> &nbsp;</th><th><p style="display: none; padding-left: 20px;" class="autopay-field m-0">Status</p></th></tr>';
           var wrapper = $('.field_wrapper').html('').html(headerHtml); //Input field wrapper
           
          
           var removeclass='';
           var tt = start_date;
            var date = new Date(tt);
            var newdate = new Date(date);
            var countSum=0;
            for(var loopVar=1;loopVar<=number_installment_field;loopVar++){
               
                var dd = newdate.getDate();
                var mm = newdate.getMonth()+1;
                var y = newdate.getFullYear();

                var someFormattedDate = mm + '/' + dd + '/' + y;
                
                if(loopVar==1 || loopVar==2 ){
                    var removeclass='';
                }else{
                    var removeclass='<i class="fas fa-times"></i>';
                }

                if ($("#with_first_payment").is(":checked") && loopVar==1) {
                    firstInstallment=$("#first_payment_amount").val().replace(',', '');
                    countSum+=parseFloat(firstInstallment);
                }else{
                    firstInstallment=amount_per_installment_field;
                    if(loopVar==number_installment_field){
                        totalAMT=parseFloat($("#final_total_text").val().replace(',', ''));
                        firstInstallment=totalAMT-countSum;
                    }else{
                        countSum+=parseFloat(firstInstallment);
                    }
                   
                }
                var fieldHTML = '<tr class="invoice_entry payment_plan_row tablePaymentPlanRemove" id="row_'+x+'"><td style="vertical-align: center; text-align: center; border-right: none;" class="remove_button" ><div class="payment_plan_entry"> <a class="image_link_sprite image_link_sprite_cancel " href="javascript:void(0);">'+removeclass+'</a></div></td><td style="border-left: none;" class=""><div id="invoice_entry_date_plan_'+x+'" class="invoice-entry-date" data-entry-id="plan161122040617875"> <input value="'+someFormattedDate+'" id="invoice_entry_date_text_plan161122040617875" style="width: 100%;border:none;" class="invoice-entry-date-text boxsizingBorder datepicker" type="text" name="new_payment_plans['+loopVar+'][due_date]" placeholder="Choose Date"></div></td><td style="text-align: right; border-right: none;" class=""> <input id="invoice_plan_amount_text_plan161122040617875" style="width: 100%; text-align: right;border:none;" class="boxsizingBorder edit_payment_plan_amount" type="text" name="new_payment_plans['+loopVar+'][amount]" onblur="installmentCalculation(this)" value="'+firstInstallment+'"></td><td style="vertical-align: center; text-align: center; border-right: none;dispaly:none;"><div class="payment_plan_entry"> <a class="image_link_sprite image_link_sprite_cancel" href="javascript:void(0);" ><i class="fas fa-pen"></i></a></div></td><td style="vertical-align: middle;" class="tablePaymentPlanEdit"><p class="autopay-field m-0" data-testid="autopay-field" style="display: none; padding-left: 20px;"></p></td></tr>'; //New input field html 
       
                // $(wrapper).append(fieldHTML); //Add field html
                $(this).find('.image_link_sprite_cancel').hide();
                
                $(".tablePaymentPlanRemove").find('.image_link_sprite_cancel').hide();
                $('.tablePaymentPlanRemove').hover(
                    function () { //this is fired when the mouse hovers over
                        $(this).find('.image_link_sprite_cancel').show();
                    },
                    function () { //this is fired when the mouse hovers out
                        $(this).find('.image_link_sprite_cancel').hide();
                    }
                );

                $('.tablePaymentPlanEdit').hover(
                    function () { //this is fired when the mouse hovers over
                        $(this).find('.image_link_sprite_cancel').show();
                    },
                    function () { //this is fired when the mouse hovers out
                        $(this).find('.image_link_sprite_cancel').hide();
                    }
                );

                $('.datepicker').datepicker({
                    'format': 'm/d/yyyy',
                    'autoclose': true,
                    'todayBtn': "linked",
                    'clearBtn': true,
                    startDate: "dateToday",
                    'todayHighlight': true
                });

                if(installment_frequency_field=="weekly"){
                    newdate.setDate(newdate.getDate() + 7);
                }else if(installment_frequency_field=="biweekly"){
                    newdate.setDate(newdate.getDate() + 14);
                }else if(installment_frequency_field=="monthly"){
                    newdate.setMonth(newdate.getMonth() + 1);
                    // newdate.setDate(newdate.getMonth() + 1);
                }
                
            }
            $('.edit_payment_plan_amount').number(true, 2);
            installmentCalculation();
        });

        
        var buttonpressed;
        $('.submitbutton').click(function () {
            buttonpressed = $(this).attr('name');
            if(buttonpressed=="saveinvoice"){
                $("#saveInvoiceForm").submit();
            }else{
                
                var dataString=firstInstallment = '';
                dataString = $("#paymentPlansForm").serialize();
            
                var number_installment_field=$("#number_installment_field").val();
                var amount_per_installment_field=$("#amount_per_installment_field").val();
                var installment_frequency_field=$("#installment_frequency_field").val();
                var start_date=$("#start_date").val();
                var start_date=$("#start_date").val();
                var start_date=$("#start_date").val();
                
                var headerHtml='<tr><th style="width: 30px; border-right: none;"> &nbsp;</th><th style="width: 150px; border-left: none;"> Due Date</th><th style="width: 120px; border-right: none;"> Amount</th><th style="width: 30px; border-left: none;"> &nbsp;</th><th><p style="display: none; padding-left: 20px;" class="autopay-field m-0">Status</p></th></tr>';
                var wrapper = $('.field_wrapper').html('').html(headerHtml); //Input field wrapper
                
                
                var removeclass='';
                var tt = start_date;
                var date = new Date(tt);
                var newdate = new Date(date);
                var countSum=0;
                for(var loopVar=1;loopVar<=number_installment_field;loopVar++){
                
                    var dd = newdate.getDate();
                    var mm = newdate.getMonth()+1;
                    var y = newdate.getFullYear();

                    var someFormattedDate = mm + '/' + dd + '/' + y;
                    
                    if(loopVar==1 || loopVar==2 ){
                        var removeclass='';
                    }else{
                        var removeclass='<i class="fas fa-times"></i>';
                    }

                    if ($("#with_first_payment").is(":checked") && loopVar==1) {
                        firstInstallment=$("#first_payment_amount").val().replace(',', '');
                        countSum+=parseFloat(firstInstallment);
                    }else{
                        firstInstallment=amount_per_installment_field;
                        if(loopVar==number_installment_field){
                            totalAMT=parseFloat($("#final_total_text").val().replace(',', ''));
                            firstInstallment=totalAMT-countSum;
                        }else{
                            countSum+=parseFloat(firstInstallment);
                        }
                    
                    }
                    var fieldHTML = '<tr class="invoice_entry payment_plan_row tablePaymentPlanRemove" id="row_'+x+'"><td style="vertical-align: center; text-align: center; border-right: none;" class="remove_button" ><div class="payment_plan_entry"> <a class="image_link_sprite image_link_sprite_cancel " href="javascript:void(0);">'+removeclass+'</a></div></td><td style="border-left: none;" class=""><div id="invoice_entry_date_plan_'+x+'" class="invoice-entry-date" data-entry-id="plan161122040617875"> <input value="'+someFormattedDate+'" id="invoice_entry_date_text_plan161122040617875" style="width: 100%;border:none;" class="invoice-entry-date-text boxsizingBorder datepicker" type="text" name="new_payment_plans['+loopVar+'][due_date]" placeholder="Choose Date"></div></td><td style="text-align: right; border-right: none;" class=""> <input id="invoice_plan_amount_text_plan161122040617875" style="width: 100%; text-align: right;border:none;" class="boxsizingBorder edit_payment_plan_amount" type="text" name="new_payment_plans['+loopVar+'][amount]" onblur="installmentCalculation(this)" value="'+firstInstallment+'"></td><td style="vertical-align: center; text-align: center; border-right: none;dispaly:none;"><div class="payment_plan_entry"> <a class="image_link_sprite image_link_sprite_cancel" href="javascript:void(0);" ><i class="fas fa-pen"></i></a></div></td><td style="vertical-align: middle;" class="tablePaymentPlanEdit"><p class="autopay-field m-0" data-testid="autopay-field" style="display: none; padding-left: 20px;"></p></td></tr>'; //New input field html 
        
                    $(wrapper).append(fieldHTML); //Add field html
                    $(this).find('.image_link_sprite_cancel').hide();
                    
                    $(".tablePaymentPlanRemove").find('.image_link_sprite_cancel').hide();
                    $('.tablePaymentPlanRemove').hover(
                        function () { //this is fired when the mouse hovers over
                            $(this).find('.image_link_sprite_cancel').show();
                        },
                        function () { //this is fired when the mouse hovers out
                            $(this).find('.image_link_sprite_cancel').hide();
                        }
                    );

                    $('.tablePaymentPlanEdit').hover(
                        function () { //this is fired when the mouse hovers over
                            $(this).find('.image_link_sprite_cancel').show();
                        },
                        function () { //this is fired when the mouse hovers out
                            $(this).find('.image_link_sprite_cancel').hide();
                        }
                    );

                    $('.datepicker').datepicker({
                        'format': 'm/d/yyyy',
                        'autoclose': true,
                        'todayBtn': "linked",
                        'clearBtn': true,
                        startDate: "dateToday",
                        'todayHighlight': true
                    });

                    if(installment_frequency_field=="weekly"){
                        newdate.setDate(newdate.getDate() + 7);
                    }else if(installment_frequency_field=="biweekly"){
                        newdate.setDate(newdate.getDate() + 14);
                    }else if(installment_frequency_field=="monthly"){
                        newdate.setMonth(newdate.getMonth() + 1);
                        // newdate.setDate(newdate.getMonth() + 1);
                    }
                    
                }
                $('.edit_payment_plan_amount').number(true, 2);
                installmentCalculation();
            
            }
        });
        
        // $('input[name="client_portal_enable"]').click(function () {
        //     if ($("#client_portal_enable").prop('checked') == true) {
        //             $("#confirmAccessModal").modal("show");
        //     }
            
        // });
        $('#confirmAccessModal').on('hidden.bs.modal', function () {
            $("#client_portal_enable").attr('checked',false);
        });
        // $('#EnableAccessForm').submit(function (e) {
        //     beforeLoader();
        //     e.preventDefault();
        //     var dataString = $("#EnableAccessForm").serialize();
        //     $.ajax({
        //         type: "POST",
        //         url: baseUrl + "/contacts/changeAccess", // json datasource
        //         data: dataString,
        //         success: function (res) {
        //             afterLoader();
        //             if (res.errors != '') {
        //                 $('.showError').html('');
        //                 var errotHtml =
        //                     '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
        //                 $.each(res.errors, function (key, value) {
        //                     errotHtml += '<li>' + value + '</li>';
        //                 });
        //                 errotHtml += '</ul></div>';
        //                 $('.showError').append(errotHtml);
        //                 $('.showError').show();
        //                 afterLoader();
        //                 return false;
        //             } else {
        //                 $("#client_portal_enable").prop('checked', "checked");
        //                 window.location.reload();
        //             }
        //         },
        //         error: function (xhr, status, error) {
        //             $('.showError').html('');
        //             var errotHtml =
        //                 '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
        //             $('.showError').append(errotHtml);
        //             $('.showError').show();
        //             afterLoader();
        //         }
        //     });
        // });
        // $('input[name="client_portal_access"]').click(function () {
        //     if ($("#client_portal_access").prop('checked') == true) {
        //             $("#grantAccessModal").modal("show");
        //     }
        // });
        $('#grantAccessModal').on('hidden.bs.modal', function () {
            $(".invoiceSharingBox").removeAttr('checked');
        });

        $('#bill_to_date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        }).on('change',function(e){
            reloadByDate();
        });
    });
    $('#removeAlllExistingTimeEntry').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#removeAlllExistingTimeEntry').valid()) {
            beforeLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#removeAlllExistingTimeEntry").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/deleteAllTimeEntry", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&deleteMultiple=yes';
            },
            success: function (res) {
                beforeLoader();
                if (res.errors != '') {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    return false;
                } else {
                    window.location.reload();
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        });
    });
 
    $('.invoiceSharingBox').change(function () { //".checkbox" change 
        if ($('.invoiceSharingBox:checked').length == "0") {
            $('#SaveInvoiceButton').text('Save Invoice');
        } else {
            var c=$('.invoiceSharingBox:checked').length;
            $('#SaveInvoiceButton').text('Save & Share with '+c+' Contacts');
        }
    });

   
    function paymentTerm(){
        
        var setDate='';
        var selectdValue = $("#bill_payment_terms option:selected").val();
        var bill_invoice_date=$("#bill_invoice_date").val();
        if(selectdValue==0 || selectdValue==1){
            var minDate =  $('#bill_invoice_date').datepicker('getDate');
            $('#bill_due_date').datepicker("setDate", minDate);
        }else if(selectdValue==2){
            CheckIn = $("#bill_invoice_date").datepicker('getDate');
            CheckOut = moment(CheckIn).add(15, 'day').toDate();
            $('#bill_due_date').datepicker('update', CheckOut);
           
        }else if(selectdValue==3){
            CheckIn = $("#bill_invoice_date").datepicker('getDate');
            CheckOut = moment(CheckIn).add(30, 'day').toDate();
            $('#bill_due_date').datepicker('update', CheckOut);
           
        }else{
            CheckIn = $("#bill_invoice_date").datepicker('getDate');
            CheckOut = moment(CheckIn).add(60, 'day').toDate();
            $('#bill_due_date').datepicker('update', CheckOut);
        }

        if(selectdValue==""){
            $("#automated_reminders").prop("checked",false);
            $('#bill_due_date').val('');
        }else{
            $("#automated_reminders").prop("checked",true);
        }
     
    }
    function checkPortalAccess(id){
        var em=pa="";
        em=$("#portalAccess_"+id).attr("em");
        pa=$("#portalAccess_"+id).attr("pa");
       
        if ($("#portalAccess_"+id).prop('checked') == true && (em=="" || pa=="0")) {
            $("#portalAccess_"+id).prop('checked', false);
            $('.showError').html('');
            beforeLoader();
            $("#preloader").show();
            $('#grantAccessModal').modal("show");
            $("#grantAccessModalArea").html('');
            $("#grantAccessModalArea").html('Loading...');
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/checkAccess",
                data: {"id": id},
                success: function (res) {
                  
                    if (typeof (res.errors) != "undefined" && res.errors !== null) {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        $("#preloader").hide();
                        $("#grantAccessModalArea").html('');
                        $('#grantAccessModal').animate({
                            scrollTop: 0
                        }, 'slow');

                        return false;
                    } else {
                        if(res=="true"){
                            $('#grantAccessModal').modal("hide");
                            $("#portalAccess_"+id).prop('checked', true);
                            $("#preloader").hide();                            
                            afterLoader()
                            return true;
                        }else{
                            afterLoader()
                            $("#grantAccessModalArea").html(res);
                            $("#preloader").hide();
                            return true;
                        }
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $('#grantAccessModal').animate({
                        scrollTop: 0
                    }, 'slow');

                    afterLoader();
                }
            })
        }
    }
    
    function fetchClientAddress(){
        var currentclient=$("#contact").val();

        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/getCaseList",
            data: {
                "id": currentclient
            },
            success: function (res) {
                $("#court_case_id").html(res);
                $("#court_case_id").trigger("change");
                $("#preloader").hide();
                return true;
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                $('#addNewTimeEntry').animate({
                    scrollTop: 0
                }, 'slow');

                afterLoader();
            }
        })

        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/getAddress",
            data: {
                "id": currentclient
            },
            success: function (res) {
                $("#bill_address_text").val(res.address);
                $("#preloader").hide();
                return true;
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                $('#addNewTimeEntry').animate({
                    scrollTop: 0
                }, 'slow');

                afterLoader();
            }
        })
       
    }
    function changeCase(){
        var case_id=$("#court_case_id").val();
        var contact=$("#contact").val();
        var URLS=baseUrl+'/bills/invoices/load_new?court_case_id='+case_id+'&token={{$adjustment_token}}&contact='+contact;
        window.location.href=URLS;
    }


    function reloadByDate(){
        var case_id=$("#court_case_id").val();
        var bill_from_date=$("#bill_from_date").val();
        var bill_to_date=$("#bill_to_date").val();
        var URLS=baseUrl+'/bills/invoices/load_new?court_case_id='+case_id+'&token={{$adjustment_token}}&from_date='+bill_from_date+'&bill_to_date='+bill_to_date;
        window.location.href=URLS;
    }
    

    
    
    
    function installmentCalculation(){
           var sumR=0;
            // $('input[name="new_payment_plans[][amount]"]').each(function (i) {
            $('.edit_payment_plan_amount').each(function (i) {
                
                var gg =$(this).val();
                sumR +=parseFloat(gg);
            });
            var FT= $("#final_total_text").val();
            var RemainingAmt=parseFloat(FT)-parseFloat(sumR);
            $("#payment_plan_balance").html(RemainingAmt);
            $('#payment_plan_balance').number($("#payment_plan_balance").text(), 2); 
     
    }
    function recalculate() {
        var total = 0;
        var expense_total_amount = parseFloat($("#expense_sub_total_text").val());
        var time_entry_total_amount = parseFloat($("#time_entry_sub_total_text").val());
        total = expense_total_amount + time_entry_total_amount;

        var discount_amount = parseFloat($("#discount_total_text").val());
        var addition_amount = parseFloat($("#addition_total_text").val());

        var final_total=total-discount_amount+addition_amount;
        $(".sub_total_amount").html(total);
        $("#sub_total_text").val(total);
        $('.sub_total_amount').number(true, 2);

        $(".invoice_total").html(total);
        $("#total_text").val(total);
        $('.invoice_total').number(true, 2);
        
        $("#final_total_text").val(final_total);
        $(".final_total").html(final_total);
        $('.final_total').number(true, 2);

        $("#payment_plan_balance").html(final_total);
        $('#payment_plan_balance').number(true, 2);


    }

    function actionTimeEntry(action) {
        $('#removeExistingEntryForm').submit(function (e) {

            beforeLoader();
            e.preventDefault();

            if (!$('#removeExistingEntryForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#removeExistingEntryForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/deleteTimeEntry", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes&action=' + action;
                },
                success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });
    }

    function openTimeDelete(id) {
        $("#delete_existing_dialog").modal("show");
        $("#delete_time_entry_id").val(id);
    }

    function addSingleTimeEntry() {
        $('.showError').html('');
        beforeLoader();
        $("#preloader").show();
        $("#addNewTimeEntryArea").html('');
        $("#addNewTimeEntryArea").html('Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/addSingleTimeEntry",
            data: {
                "id": ""
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#addNewTimeEntryArea").html('');
                    $('#addNewTimeEntry').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    afterLoader()
                    $("#addNewTimeEntryArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                $('#addNewTimeEntry').animate({
                    scrollTop: 0
                }, 'slow');

                afterLoader();
            }
        })
    }

    function editSingleTimeEntry(id) {
        $('.showError').html('');
        beforeLoader();
        $("#preloader").show();
        $("#editNewTimeEntryArea").html('');
        $("#editNewTimeEntryArea").html('Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/editSingleTimeEntry",
            data: {
                "id": id
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#editNewTimeEntryArea").html('');
                    $('#editNewTimeEntry').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    afterLoader()
                    $("#editNewTimeEntryArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                $('#editNewTimeEntry').animate({
                    scrollTop: 0
                }, 'slow');

                afterLoader();
            }
        })
    }



    function openExpenseDelete(id) {
        $("#delete_expense_existing_dialog").modal("show");
        $("#delete_expense_entry_id").val(id);
    }

    function actionExpenseEntry(action) {
        $('#removeExistingExpenseEntryForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#removeExistingExpenseEntryForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#removeExistingExpenseEntryForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/deleteExpenseEntry", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes&action=' + action;
                },
                success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });
    }

    $('#removeAlllExistingExpenseEntry').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#removeAlllExistingExpenseEntryForm').valid()) {
            beforeLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#removeAlllExistingExpenseEntryForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/deleteAllExpenseEntry", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&deleteMultiple=yes';
            },
            success: function (res) {
                beforeLoader();
                if (res.errors != '') {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    return false;
                } else {
                    window.location.reload();
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        });
    });

    function addSingleExpenseEntry() {
        $('.showError').html('');
        beforeLoader();
        $("#preloader").show();
        $("#addNewExpenseEntryArea").html('');
        $("#addNewExpenseEntryArea").html('Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/addSingleExpenseEntry",
            data: {
                "id": ""
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#addNewExpenseEntryArea").html('');
                    $('#addNewExpenseEntry').animate({
                        scrollTop: 0
                    }, 'slow');
                    return false;
                } else {
                    afterLoader()
                    $("#addNewExpenseEntryArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                $('#addNewExpenseEntry').animate({
                    scrollTop: 0
                }, 'slow');
                afterLoader();
            }
        })
    }

    function editNewExpenseEntry(id) {
        $('.showError').html('');
        beforeLoader();
        $("#preloader").show();
        $("#editNewExpenseEntryArea").html('');
        $("#editNewExpenseEntryArea").html('Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/editSingleExpenseEntry",
            data: {
                "id": id
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#editNewExpenseEntryArea").html('');
                    $('#editNewExpenseEntry').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    afterLoader()
                    $("#editNewExpenseEntryArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                $('#editNewExpenseEntry').animate({
                    scrollTop: 0
                }, 'slow');

                afterLoader();
            }
        })
    }
    function addNewAdjustmentEntry() {
        $('.showError').html('');
        beforeLoader();
        $("#preloader").show();
        $("#addNewAdjustmentEntryArea").html('');
        $("#addNewAdjustmentEntryArea").html('Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/addAdjustmentEntry",
            data: {
                "id": "","adjustment_token":"{{$adjustment_token}}"
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#addNewAdjustmentEntryArea").html('');
                    $('#addNewAdjustmentEntry').animate({
                        scrollTop: 0
                    }, 'slow');
                    return false;
                } else {
                    afterLoader()
                    $("#addNewAdjustmentEntryArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                $('#addNewAdjustmentEntry').animate({
                    scrollTop: 0
                }, 'slow');
                afterLoader();
            }
        })
    }
    
    function editAdjustmentEntry(id) {
        $('.showError').html('');
        beforeLoader();
        $("#preloader").show();
        $("#editAdjustmentEntryArea").html('');
        $("#editAdjustmentEntryArea").html('Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/editAdjustmentEntry",
            data: {"id": id},
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#editAdjustmentEntryArea").html('');
                    $('#editAdjustmentEntry').animate({
                        scrollTop: 0
                    }, 'slow');
                    return false;
                } else {
                    afterLoader()
                    $("#editAdjustmentEntryArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                $('#editAdjustmentEntry').animate({
                    scrollTop: 0
                }, 'slow');
                afterLoader();
            }
        })
    }

    function reloadRow(id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/reloadRow",
            data: {"id": id},
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    return false;
                } else {
                    afterLoader()
                    $("#row_"+id).html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                $('#editAdjustmentEntry').animate({
                    scrollTop: 0
                }, 'slow');
                afterLoader();
            }
        })
    }
    function AddContactModal() {
        $("#preloader").show();
        $("#step-1-aDD-CONTACT").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/contacts/loadAddContactFromInvoice", // json datasource
                data: 'loadStep1',
                success: function (res) {
                    $("#step-1-aDD-CONTACT").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    $("input:checkbox#range_check_box").click(function () {
        if ($(this).is(":checked")) {
            $('#bill_from_date').removeAttr("disabled");
            $('#bill_to_date').removeAttr("disabled");
        } else {
            $("#bill_from_date").attr("disabled", true);
            $("#bill_to_date").attr("disabled", true);
            $('#bill_from_date').val('');
            $('#bill_to_date').val('');
            var case_id=$("#court_case_id").val();
            var URLS=baseUrl+'/bills/invoices/new?court_case_id='+case_id+'&token={{$adjustment_token}}';
            window.location.href=URLS;
        }
    });

    <?php if(isset($filterByDate) && $filterByDate=="yes") {?>
        $("input:checkbox#range_check_box").attr("checked","checked");
        $('#bill_from_date').removeAttr("disabled");
        $('#bill_to_date').removeAttr("disabled");
    <?php } ?>

</script>
@stop
@endsection
