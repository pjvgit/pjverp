@extends('layouts.master')
<?php $s = sprintf('%06d', $findInvoice->id);?>
@section('title', 'Edit Invoice #'.$s.' - Invoices - Billing')@section('main-content')
@include('billing.submenu')
<div class="separator-breadcrumb border-top"></div>
<div class="row">

    <div class="col-md-12">
        <form class="saveInvoiceForm" id="saveInvoiceForm" name="saveInvoiceForm" method="POST" action="{{route('bills/invoices/updateInvoiceEntry')}}">
            @csrf
            @if ($errors->any())
            <div class="alert alert-danger">
                Unable to save your invoice. Please correct the errors below.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <br><br>
                <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
                </ul>
            </div>
        @endif
            <input type="hidden" value="{{$findInvoice->id}}" name="invoice_id" id="invoice_id">

            <div class="card text-left">
                <div class="card-body" id="main_content">
                    <span id="responseMain"></span>
                    <div class="d-flex align-items-center pb-3">
                        <?php $s = sprintf('%06d', $findInvoice->id);?>
                        <h4 class="my-0">Invoice #{{$s}}</h4>
                        
                          <div class="ml-auto">
                            <a class="btn btn-link text-black-50" target="_blank" href="#">Manage Firm Invoice Settings</a>
                          </div>
                      </div>

                    <div id="invoice_info_table" class="pt-2">
                        <table style="width: 100%; margin: 0; padding: 0; table-layout: fixed;" class="invoice">                            <tbody>
                                <tr>
                                    <td rowspan="3" style="padding: 0px; vertical-align: top; width: 130px;">
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
                                                        <option uType="client" <?php if($val->id==$findInvoice->user_id){ echo "selected=selected";} ?> value="{{$val->id}}"> {{substr($val->name,0,200)}} (Client) </option>
                                                        <?php } ?> --}}
                                                        @forelse ($ClientList as $key => $item)
                                                        <option uType="client"  value="{{ $key }}" {{ (isset($findInvoice) && $key == $findInvoice->user_id) ? "selected" : "" }}> {{ substr($item,0,200) }} (Client)</option>
                                                        @empty
                                                        @endforelse
                                                    </optgroup>
                                                    <optgroup label="Company">
                                                        {{-- <?php foreach($CompanyList as $CompanyListKey=>$CompanyListVal){ ?>
                                                        <option uType="company" <?php if($CompanyListVal->id==$findInvoice->user_id){ echo "selected=selected";} ?> value="{{$CompanyListVal->id}}"> {{substr($CompanyListVal->first_name,0,200)}} (Company)</option><?php } ?> --}}
                                                        @forelse ($CompanyList as $key => $item)
                                                        <option uType="company"  value="{{ $key }}" {{ (isset($findInvoice) && $key == $findInvoice->user_id) ? "selected" : "" }}> {{ substr($item,0,200) }} (Company)</option>
                                                        @empty
                                                        @endforelse
                                                    </optgroup>
                                                </select>
                                                
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
                                            $formatted_value = sprintf("%06d", $findInvoice->id);
                                            ?>
                                            <input class="form-control" name="invoice_number_padded" value="{{$formatted_value}}">
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
                                                        <option value="none" <?php if($case_id=="none"){ echo "selected=selected";} ?>>None</option>
                                                        <?php foreach($caseListByClient as $key=>$val){ ?>
                                                        <option value="{{$val->id}}" <?php if($val->id==$findInvoice->case_id){ echo "selected=selected";} ?>  > 
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
                                            name="bill_invoice_date" value="{{date('m/d/Y',strtotime($findInvoice->invoice_date))}}">
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding-right: 5px; padding-top: 18px; text-align: right; vertical-align: top; width: 80px;">
                                        Address</td>
                                    <td rowspan="4"
                                        style="width: 350px; white-space: nowrap; vertical-align: top; padding-top: 16px;">
                                        <textarea id="bill_address_text" name="bill[address_text]" class="form-control"style="width: 70%; height: 104px; resize: none; overflow-y: hidden;">{{$UsersAdditionalInfo['full_address']}}</textarea>
                                    </td>
                                    <td style="width: auto;">&nbsp;</td>
                                    <td style="width: 120px; text-align: right; padding-right: 5px;">
                                        Payment Terms</td>
                                    <td><select id="bill_payment_terms" onchange="paymentTerm()" class="custom-select form-control select2Dropdown"
                                            name="payment_terms">
                                            <option value="" <?php if($findInvoice->payment_term=="5"){ echo "selected=selected";} ?> ></option>
                                            <option <?php if($findInvoice->payment_term=="0"){ echo "selected=selected";} ?> value="0">Due Date</option>
                                            <option <?php if($findInvoice->payment_term=="1"){ echo "selected=selected";} ?> value="1">Due on Receipt</option>
                                            <option <?php if($findInvoice->payment_term=="2"){ echo "selected=selected";} ?> value="2">Net 15</option>
                                            <option <?php if($findInvoice->payment_term=="3"){ echo "selected=selected";} ?> value="3">Net 30</option>
                                            <option <?php if($findInvoice->payment_term=="4"){ echo "selected=selected";} ?> value="4">Net 60</option>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td style="width: auto;">&nbsp;</td>  <td style="width: auto;">&nbsp;</td>
                                    <td style="width: 120px; text-align: right; padding-right: 5px; ">
                                        Due Date</td>
                                    <td>
                                        <?php
                                        if($findInvoice->due_date!=NULL){
                                            $dt=date('m/d/Y',strtotime($findInvoice->due_date));
                                        }else{
                                            $dt="";
                                        }?>

                                        <input id="bill_due_date" class="form-control date datepicker" name="bill_due_date"
                                            value="{{$dt}}"></td>
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

                                            <input type="checkbox" name="automated_reminders" id="automated_reminders" <?php if($findInvoice->automated_reminder=="yes"){ echo "checked=checked";} ?>><span
                                                class="slider"  >
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
                                            <option <?php if($findInvoice->status=="Draft"){ echo "selected=selected";} ?>  value="Draft">Draft</option>
                                            <option value="Unsent" <?php if($findInvoice->is_sent == "no" && $findInvoice->status != "Draft"){ echo "selected=selected";} ?>>Unsent</option>
                                            <option value="Sent" <?php if($findInvoice->is_sent == "yes" && $findInvoice->status != "Draft"){ echo "selected=selected";} ?>>Sent</option>
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
                                    <td><input value="" class="date range_select disabled form-control  hasDatepicker"
                                            style="width: 115px;" disabled="disabled" type="text" name="bill_from_date"
                                            id="bill_from_date"></td>
                                    <td style="color: black; vertical-align: middle;" class="range_select disabled">to</td>
                                    <td><input value="" class="date range_select disabled  form-control  hasDatepicker"
                                            style="width: 115px;" disabled="disabled" type="text" name="bill_to_date"
                                            id="bill_to_date"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="entries" style="margin: 5px;">

                        <div class="invoice_case_gradient">
                            <h2><i class="fas fa-briefcase mr-2"></i> {{@$caseMaster['case_title']}} </h2>
                        </div>
                        <div class="invoice_entry_header">
                            <table>
                                <tr>
                                    <td width="100%">
                                        <h3 class="entry-header">Flat Fees</h3>
                                    </td>
                                    <td width="1%">
                                        <span data-toggle="tooltip" data-placement="left" title="Remove all flat fees">
                                            <a data-toggle="modal" data-target="#removeAllExistingFlatFeeEntry"
                                                data-placement="bottom" href="javascript:;"> <i
                                                    class="fas fa-trash align-middle pr-2"></i></a>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                            <div class="clear-header"></div>
                        </div>
                        <table id="entries_13285222" class="data invoice_entries time_entries_table">
                            <colgroup>
                                <col width="3%"> <!-- blank placeholder -->
                                <col width="10%"> <!-- date -->
                                <col width="6%"> <!-- EE -->
                                <col width="12%"> <!-- Employee -->
                                <col width="13%"> <!-- Activity -->
                                <col width="44%"> <!-- Notes -->
                                <col width="10%"> <!-- Rate -->
                                <col width="4%"> <!-- non-billable checkbox -->
                            </colgroup>
                            <tbody>
                                <tr>
                                    <th style="border-right: none;">&nbsp;</th>
                                    <th style="border-left: none;">Date</th>
                                    <th> EE </th>
                                    <th> Employee</th>
                                    <th> Item </th>
                                    <th> Falt Fee Notes</th>
                                    <th> Amount </th>
                                    <th style="font-size: 11px; line-height: 12px; text-align: center;"> Non<br>Billable</th>
                                </tr>
                                <?php
                                if($FlatFeeEntryForInvoice->isEmpty()){?>
                                    <tr class="no_entries">
                                        <td colspan="9"
                                            style="text-align: center; padding-top: 10px !important; padding-bottom: 10px !important;">
                                            This matter has no unbilled flat fees entries.
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php 
                                $flateFeeTotal=0;
                                foreach($FlatFeeEntryForInvoice as $k=>$v){
                                    $flateFeeTotal+=$v->cost;
                                ?>
                                
                                <tr id="time-79566738-7" class="invoice_entry time_entry ">
                                    <td style="vertical-align: center; text-align: center; border-right: none;"
                                        class="tdTime">
                                        <div class="invoice_entry_actions">
                                            <a class="image_link_sprite image_link_sprite_cancel" href="javascript:void(0);" {{-- onclick="openTimeDelete({{$v->itd}});" --}} onclick="openFlatFeeDelete({{$v->itd}});">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                        <input type="hidden" value="{{$v->itd}}" name="flatFeeEntrySelectedArray[]">
                                    </td>
                                    <td style="border-left: none;" class="">
                                        <a data-toggle="modal" data-target="#editNewFlatFeeEntry" onclick="editSingleFlatFeeEntry({{$v->itd}})" data-placement="bottom"
                                            href="javascript:;" class="ml-0">
                                            {{date('d/m/Y',strtotime($v->entry_date))}}
                                        </a>
                                    </td>
                                    <td class="pl-2" style="overflow: visible;">
                                        <div id="time-79566738-7-initials" class="mycase_select">
                                            <a data-toggle="modal" data-target="#editNewFlatFeeEntry"  onclick="editSingleFlatFeeEntry({{$v->itd}})" data-placement="bottom"
                                                href="javascript:;" class="ml-0">
                                                {{ @$v->first_name[0] }}{{ @$v->last_name[0] }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="pl-2" style="overflow: visible;">
                                        <div id="time-79566738-7-user" class="mycase_select">
                                            <a data-toggle="modal" data-target="#editNewFlatFeeEntry"  onclick="editSingleFlatFeeEntry({{$v->itd}})" data-placement="bottom"
                                                href="javascript:;" class="ml-0"> {{$v->first_name}} {{$v->last_name}}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="pl-2" style="overflow: visible;">
                                        <div id="time-79566738-7-user">
                                            <a data-toggle="modal" data-target="#editNewFlatFeeEntry"  onclick="editSingleFlatFeeEntry({{$v->itd}})" data-placement="bottom" href="javascript:;" class="ml-0"> Flat Fee
                                            </a>
                                        </div>
                                    </td>
                                    <td style="text-align: left;" class="billable_toggle time-entry-rate  ">
                                        <a data-toggle="modal" data-target="#editNewFlatFeeEntry" onclick="editSingleFlatFeeEntry({{$v->itd}})" data-placement="bottom"
                                            href="javascript:;" class="ml-0">
                                            {{$v->description}}
                                        </a>
                                    </td>
                                    <td style="text-align: left;" class="billable_toggle time-entry-hours row_total">
                                        <a data-toggle="modal" data-target="#editNewFlatFeeEntry"  onclick="editSingleFlatFeeEntry({{$v->itd}})" data-placement="bottom" href="javascript:;" class="ml-0">
                                           {{$v->cost}}
                                        </a>
                                    </td>
                                    
                                    <td style="text-align: center; padding-top: 10px !important;">
                                        <input type="checkbox" class="invoice_entry_nonbillable_flat nonbillable-check" data-check-type="flat" id="invoice_entry_nonbillable_flat_{{$v->itd}}" <?php if($v->time_entry_billable=="no"){ echo "checked=checked"; } ?>
                                            name="flat_fee_entry[]" priceattr="{{$v->cost}}" value="{{$v->itd}}">
                                    </td>
                                </tr>
                                <?php } ?>

                                <tr class="footer">
                                    <td colspan="3">
                                        <div class="locked">
                                            <a data-toggle="modal" data-target="#addNewFlatFeeEntry"
                                                onclick="addSingleFlatFeeEntry()" data-placement="bottom" href="javascript:;"
                                                class="ml-4">
                                                <i class="fas fa-plus align-middle"></i> Add Flat Fee Line</a>
                                        </div>
                                    </td>
                                    <td colspan="2" style="text-align: right;">
                                        <div class="locked">
                                            {{@$caseMaster['case_title']}} flat fee totals:
                                        </div>
                                    </td>
                                   
                                    <td>
                                        <div class="locked" style="text-align: right;">
                                            $<span id="flat_fee_entry_table_total"
                                                class="flat_fee_table_total">{{number_format($flateFeeTotal,2)}}</span>

                                        </div>
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>

                            </tbody>
                        </table>
                        <br>


                        <div class="invoice_entry_header">
                            <table>
                                <tr>
                                    <td width="100%">
                                        <h3 class="entry-header">Time Entries</h3>
                                    </td>
                                    <td width="1%">
                                        <span data-toggle="tooltip" data-placement="left" title="Remove all time entries">
                                            <a data-toggle="modal" data-target="#removeAlllExistingTimeEntry"
                                                data-placement="bottom" href="javascript:;"> <i
                                                    class="fas fa-trash align-middle pr-2"></i></a>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                            <div class="clear-header"></div>
                        </div>

                        <table id="entries_13285222" class="data invoice_entries time_entries_table">
                            <colgroup>
                                <col width="3%"> <!-- blank placeholder -->
                                <col width="10%"> <!-- date -->
                                <col width="6%"> <!-- EE -->
                                <col width="12%"> <!-- Employee -->
                                <col width="13%"> <!-- Activity -->
                                <col width="24%"> <!-- Notes -->
                                <col width="10%"> <!-- Rate -->
                                <col width="10%"> <!-- Hours -->
                                <col width="10%"> <!-- Total -->
                                <col width="4%"> <!-- non-billable checkbox -->
                            </colgroup>
                            <tbody>
                                <tr>
                                    <th style="border-right: none;">&nbsp;</th>
                                    <th style="border-left: none;">Date</th>
                                    <th> EE </th>
                                    <th> Employee</th>
                                    <th> Activity </th>
                                    <th> Time Entry Notes</th>
                                    <th> Rate </th>
                                    <th> Hours </th>
                                    <th> Line Total </th>
                                    <th style="font-size: 11px; line-height: 12px; text-align: center;"> Non<br>Billable
                                    </th>
                                </tr>
                                <?php
                                if($TimeEntry->isEmpty()){?>
                                <tr class="no_entries">
                                    <td colspan="9"
                                        style="text-align: center; padding-top: 10px !important; padding-bottom: 10px !important;">
                                        This matter has no unbilled time entries.
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php 
                                $timeEntryTime=$timeEntryAmount=0;
                                foreach($TimeEntry as $k=>$v){
                                ?>
                                
                                <tr id="time-79566738-7" class="invoice_entry time_entry ">
                                    <td style="vertical-align: center; text-align: center; border-right: none;"
                                        class="tdTime">
                                        <div class="invoice_entry_actions">
                                            <a class="image_link_sprite image_link_sprite_cancel" href="javascript:void(0);"
                                                onclick="openTimeDelete({{$v->itd}});"><i class="fas fa-times"></i></a>
                                        </div>
                                        <input type="hidden" value="{{$v->itd}}" name="timeEntrySelectedArray[]">
                                    </td>
                                    <td style="border-left: none;" class="">
                                        <a data-toggle="modal" data-target="#editNewTimeEntry"
                                            onclick="editSingleTimeEntry({{$v->itd}})" data-placement="bottom"
                                            href="javascript:;" class="ml-0">
                                            {{date('d/m/Y',strtotime($v->entry_date))}}</a>
                                    </td>
                                    <td class="pl-2" style="overflow: visible;">
                                        <div id="time-79566738-7-initials" class="mycase_select">
                                            <a data-toggle="modal" data-target="#editNewTimeEntry"
                                                onclick="editSingleTimeEntry({{$v->itd}})" data-placement="bottom"
                                                href="javascript:;" class="ml-0">
                                                {{ @$v->first_name[0] }}{{ @$v->last_name[0] }}</a>
                                        </div>
                                    </td>
                                    <td class="pl-2" style="overflow: visible;">
                                        <div id="time-79566738-7-user" class="mycase_select">
                                            <a data-toggle="modal" data-target="#editNewTimeEntry"
                                                onclick="editSingleTimeEntry({{$v->itd}})" data-placement="bottom"
                                                href="javascript:;" class="ml-0"> {{$v->first_name}} {{$v->last_name}}</a>
                                        </div>
                                    </td>
                                    <td style="overflow: visible;" class="pl-2">
                                        <div id="time-79566738-7-activity" class="mycase_select time-entry-activity">
                                            <a data-toggle="modal" data-target="#editNewTimeEntry"
                                                onclick="editSingleTimeEntry({{$v->itd}})" data-placement="bottom"
                                                href="javascript:;" class="ml-0"> {{$v->title}}</a>
                                        </div>
                                    </td>
                                    <td class="p-2">
                                        <a data-toggle="modal" data-target="#editNewTimeEntry"
                                            onclick="editSingleTimeEntry({{$v->itd}})" data-placement="bottom"
                                            href="javascript:;" class="ml-0"> {{$v->description}}</a>
                                    </td>
                                    <td style="text-align: right;" class="billable_toggle time-entry-rate <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>">
                                        <a data-toggle="modal" data-target="#editNewTimeEntry"
                                            onclick="editSingleTimeEntry({{$v->itd}})" data-placement="bottom"
                                            href="javascript:;" class="ml-0">
                                            {{$v->entry_rate}}</a>
                                    </td>
                                    <?php
                                    if($v->rate_type=="flat"){?>
                                    <td style="text-align: right;" class="billable_toggle time-entry-hours <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>">
                                        <a data-toggle="modal" data-target="#editNewTimeEntry"
                                            onclick="editSingleTimeEntry({{$v->itd}})" data-placement="bottom"
                                            href="javascript:;" class="ml-0">
                                            flat
                                        </a>
                                    </td>
                                    <?php }else{?>
                                    <td style="text-align: right;" class="billable_toggle time-entry-hours row_total <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>">
                                        <a data-toggle="modal" data-target="#editNewTimeEntry"
                                            onclick="editSingleTimeEntry({{$v->itd}})" data-placement="bottom"
                                            href="javascript:;" class="ml-0">
                                           {{$v->duration}}
                                        </a>
                                    </td>
                                    <?php } ?>
                                    <td class="billable_toggle pr-2">
                                        <div class="locked row_total <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>" style="text-align: right;">
                                            <?php 
                                            
                                            if($v->rate_type=="flat"){
                                                echo $Total=$v->entry_rate;
                                                if($v->time_entry_billable=="yes"){
                                                    $timeEntryAmount=$timeEntryAmount+$v->entry_rate;
                                                }
                                            }else{
                                                echo $Total= ($v->duration * $v->entry_rate);
                                                if($v->time_entry_billable=="yes"){
                                                    $timeEntryAmount=$timeEntryAmount+$Total;
                                                    $timeEntryTime=$timeEntryTime+$v->duration;
                                                }
                                            }

                                          
                                            ?>
                                            <input type="hidden" value="{{$Total}}" class="amount_{{$v->itd}}">
                                        </div>
                                    </td>
                                    <td style="text-align: center; padding-top: 10px !important;">
                                        <input type="checkbox" class="invoice_entry_nonbillable_time nonbillable-check" data-check-type="time"
                                            id="invoice_entry_nonbillable_time_{{$v->itd}}" <?php if($v->time_entry_billable=="no"){ echo "checked=checked"; } ?>
                                            name="linked_staff_checked_share[]" priceattr="{{$Total}}" value="{{$v->itd}}">
                                    </td>
                                </tr>
                                <?php } ?>

                                <tr class="footer">
                                    <td colspan="4">
                                        <div class="locked">

                                            <a data-toggle="modal" data-target="#addNewTimeEntry"
                                                onclick="addSingleTimeEntry()" data-placement="bottom" href="javascript:;"
                                                class="ml-4">
                                                <i class="fas fa-plus align-middle"></i> Add Time Entry Line</a>
                                        </div>
                                    </td>
                                    <td colspan="3" style="text-align: right;">
                                        <div class="locked">
                                            {{@$caseMaster['case_title']}} totals:
                                        </div>
                                    </td>
                                    <td>
                                        <div class="locked time_entry_table_hours_total"
                                            style="text-align: right; padding-right: 5px;">{{$timeEntryTime}}</div>
                                    </td>
                                    <td>
                                        <div class="locked" style="text-align: right;">
                                            $<span id="time_entry_table_total"
                                                class="table_total">{{number_format($timeEntryAmount,2)}}</span>

                                        </div>
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>

                            </tbody>
                        </table>

                        <div style="margin-top: 15px;">

                            <div class="invoice_entry_header">

                                <table>
                                    <tr>
                                        <td width="100%">
                                            <h3 class="entry-header">Expenses</h3>
                                        </td>
                                        <td width="1%">
                                            <span data-toggle="tooltip" data-placement="left" title="Remove all expenses">
                                                <a data-toggle="modal" data-target="#removeAlllExistingExpenseEntry"
                                                    data-placement="bottom" href="javascript:;"> <i
                                                        class="fas fa-trash align-middle pr-2"></i></a>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                                <div class="clear-header"></div>
                            </div>

                            <table id="expenses_13285222" class="data expenses_table invoice_entries">
                                <colgroup>
                                    <col width="3%"> <!-- blank placeholder -->
                                    <col width="10%"> <!-- date -->
                                    <col width="6%"> <!-- EE -->
                                    <col width="12%"> <!-- Employee -->
                                    <col width="15%"> <!-- Activity -->
                                    <col width="24%"> <!-- Notes -->
                                    <col width="10%"> <!-- Cost -->
                                    <col width="10%"> <!-- Quantity -->
                                    <col width="10%"> <!-- Total -->
                                    <col width="4%"> <!-- non-billable checkbox -->

                                </colgroup>
                                <tbody>
                                    <tr>
                                        <th style="width: 30px; border-right: none;">
                                            &nbsp;
                                        </th>
                                        <th style="width: 100px; border-left: none;">
                                            Date
                                        </th>
                                        <th style="width: 60px;">
                                            EE
                                        </th>
                                        <th style="width: 120px;">
                                            Employee
                                        </th>
                                        <th style="width: 150px;">
                                            Expense
                                        </th>
                                        <th style="width: 250px;">
                                            Expense Notes
                                        </th>
                                        <th style="width: 100px;">
                                            Cost
                                        </th>
                                        <th style="width: 100px;">
                                            Quantity
                                        </th>
                                        <th style="width: 100px;">
                                            Line Total
                                        </th>
                                        <th style="width: 40px; font-size: 11px; line-height: 12px; text-align: center;">
                                            Non<br>Billable
                                        </th>
                                    </tr>
                                    <?php
                                    if($ExpenseEntry->isEmpty()){?>
                                    <tr class="no_entries" style="">
                                        <td colspan="9"
                                            style="text-align: center; padding-top: 10px !important; padding-bottom: 10px !important;">
                                            This matter has no unbilled expenses.
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <?php 
                                    $expenseTime=0;
                                    $expenseAmount=0;
                                    foreach($ExpenseEntry as $k=>$v){
                                    ?>

                                    <tr id="time-79566738-7" class="invoice_entry time_entry ">
                                        <td style="vertical-align: center; text-align: center; border-right: none;"
                                            class="tdTimeExpense">
                                            <div class="invoice_entry_actions">
                                                <a class="image_link_sprite image_link_sprite_cancel"
                                                    href="javascript:void(0);" onclick="openExpenseDelete({{$v->eid}});"><i
                                                        class="fas fa-times"></i></a>
                                            </div>
                                            <input type="hidden" value="{{$v->eid}}" name="expenseEntrySelectedArray[]">

                                        </td>

                                        <td style="border-left: none;" class="">
                                            <a data-toggle="modal" data-target="#editNewExpenseEntry"
                                                onclick="editNewExpenseEntry({{$v->eid}})" data-placement="bottom"
                                                href="javascript:;"
                                                class="ml-0">{{date('d/m/Y',strtotime($v->entry_date))}}</a>
                                        </td>
                                        <td class="pl-2" style="overflow: visible;">
                                            <div id="time-79566738-7-initials" class="mycase_select">
                                                <a data-toggle="modal" data-target="#editNewExpenseEntry"
                                                    onclick="editNewExpenseEntry({{$v->eid}})" data-placement="bottom"
                                                    href="javascript:;"
                                                    class="ml-0">{{ @$v->first_name[0] }}{{ @$v->last_name[0] }}</a>
                                            </div>
                                        </td>
                                        <td class="pl-2" style="overflow: visible;">
                                            <div id="time-79566738-7-user" class="mycase_select">
                                                <a data-toggle="modal" data-target="#editNewExpenseEntry"
                                                    onclick="editNewExpenseEntry({{$v->eid}})" data-placement="bottom"
                                                    href="javascript:;" class="ml-0"> {{$v->first_name}}
                                                    {{$v->last_name}}</a>
                                            </div>
                                        </td>
                                        <td style="overflow: visible;" class="pl-2">
                                            <div id="time-79566738-7-activity" class="mycase_select time-entry-activity">
                                                <a data-toggle="modal" data-target="#editNewExpenseEntry"
                                                    onclick="editNewExpenseEntry({{$v->eid}})" data-placement="bottom"
                                                    href="javascript:;" class="ml-0"> {{$v->title}}</a>
                                            </div>
                                        </td>
                                        <td class="p-2 <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>">
                                            <a data-toggle="modal" data-target="#editNewExpenseEntry"
                                                onclick="editNewExpenseEntry({{$v->eid}})" data-placement="bottom"
                                                href="javascript:;" class="ml-0"> {{$v->description}}</a>
                                        </td>
                                        <td style="text-align: right;" class="billable_toggle time-entry-rate <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?> ">
                                            <a data-toggle="modal" data-target="#editNewExpenseEntry"
                                                onclick="editNewExpenseEntry({{$v->eid}})" data-placement="bottom"
                                                href="javascript:;" class="ml-0">{{$v->cost}}</a>
                                        </td>
                                        <td style="text-align: right;" class="billable_toggle time-entry-hours <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?> ">
                                            <a data-toggle="modal" data-target="#editNewExpenseEntry"
                                                onclick="editNewExpenseEntry({{$v->eid}})" data-placement="bottom"
                                                href="javascript:;" class="ml-0">
                                                <?php 
                                                if($v->rate_type=="flat"){
                                                    echo "flat";
                                                }else{
                                                    echo $v->duration;
                                                } ?>
                                            </a>
                                        </td>
                                        <td class="billable_toggle pr-2 <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>">
                                            <div class="locked row_total" style="text-align: right;">
                                                <?php 
                                                echo $Total= ($v->duration * $v->cost);
                                                if($v->time_entry_billable=="yes"){
                                                    $expenseAmount=$expenseAmount+$Total;
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td style="text-align: center; padding-top: 10px !important;">
                                            <input type="checkbox" class="invoice_expense_entry_nonbillable_time nonbillable-check" data-check-type="expense"
                                                id="invoice_expense_entry_nonbillable_time{{$v->eid}}"  <?php if($v->time_entry_billable=="no"){ echo "checked=checked"; } ?>
                                                name="invoice_expense_entry_nonbillable_time[]" priceattr="{{$Total}}" value="{{$v->eid}}">
                                        </td>
                                    </tr>
                                    <?php } ?>


                                    <tr class="footer">
                                        <td colspan="5">
                                            <div class="locked">
                                                <a data-toggle="modal" data-target="#addNewExpenseEntry"
                                                    onclick="addSingleExpenseEntry()" data-placement="bottom"
                                                    href="javascript:;" class="ml-4"> <i
                                                        class="fas fa-plus align-middle"></i> Add
                                                    Expense</a>
                                            </div>
                                        </td>
                                        <td colspan="3" style="text-align: right;">
                                            <div class="locked">
                                                {{@$caseMaster['case_title']}} expense total:
                                            </div>
                                        </td>
                                        <td>
                                            <div class="locked" style="text-align: right;">
                                                $<span id="expense_table_total"
                                                    class="table_expense_total">{{number_format($expenseAmount,2)}}</span>
                                            </div>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>


                        <div style="margin-top: 15px;">


                            <div class="invoice_entry_header">
                                <h3>Adjustments</h3>
                            </div>
                            <table id="discounts_13285222" class="data invoice_entries discount_table">
                                <tbody>
                                    <tr>
                                        <th style="width: 30px; border-right: none;">
                                            &nbsp;
                                        </th>
                                        <th style="width: 100px; border-left: none;">
                                            Item
                                        </th>
                                        <th style="width: 120px;">
                                            Applied To
                                        </th>
                                        <th style="width: 150px;">
                                            Type
                                        </th>
                                        <th style="width: 250px;">
                                            Notes
                                        </th>
                                        <th style="width: 100px;">
                                            Basis
                                        </th>
                                        <th style="width: 100px;">
                                            Percentage
                                        </th>
                                        <th style="width: 100px;">
                                            Amount
                                        </th>
                                        <th style="width: 48px; font-size: 11px; line-height: 12px; text-align: center;">
                                            &nbsp;
                                        </th>
                                    </tr>
                                    <?php if($InvoiceAdjustment->isEmpty()){?>
                                    <tr class="no_entries" style="">
                                        <td colspan="9"
                                            style="text-align: center; padding-top: 10px !important; padding-bottom: 10px !important;">
                                            This invoice has no discounts, write-offs or additions
                                        </td>
                                    </tr>
                                    <?php } ?>

                                    <?php 
                                    $discount=0;
                                    $addition=0;
                                    foreach($InvoiceAdjustment as $k=>$v){
                                    ?>

                                    <tr id="discount-new161114807394472" class="invoice_entry discount">
                                        <td style="vertical-align: center; text-align: center; border-right: none;"
                                            class="tdTimeExpense">
                                            <div class="invoice_entry_actions">
                                                <a class="image_link_sprite image_link_sprite_cancel"
                                                    href="javascript:void(0);" onclick="openAdjustmentDelete({{$v->id}});"><i
                                                        class="fas fa-times"></i></a>
                                            </div>
                                        </td>

                                        <td class="bill-adjustment-type" style="border-left: none; overflow: visible;">
                                            <a data-toggle="modal" data-target="#editAdjustmentEntry"
                                        onclick="editAdjustmentEntry({{$v->id}})" data-placement="bottom" href="javascript:;"
                                        class="ml-4"><div id="discount-new161114807394472-discount_type" class="mycase_select">
                                                <?php
                                                $items=array("discount"=>"Discount","intrest"=>"Interest","tax"=>"Tax","addition"=>"Addition");
                                                echo $items[$v->item];
                                                ?></div></a>
                                        </td>
                                        <td class="" style="overflow: visible;">
                                            <a data-toggle="modal" data-target="#editAdjustmentEntry"
                                            onclick="editAdjustmentEntry({{$v->id}})" data-placement="bottom" href="javascript:;"
                                            class="ml-4">  <div id="discount-new161114807394472-discount_applied_to" class="mycase_select">
                                                <?php 
                                                $AppliedTo=array("flat_fees"=>"Flat Fees","time_entries"=>"Time Entries","expenses"=>"Expenses","balance_forward_total"=>"Balance Forward Total","sub_total"=>"Sub Total");
                                                echo $AppliedTo[$v->applied_to];
                                                ?>
                                            </div>
                                            </a>
                                        </td>
                                        <td class="" style="overflow: visible;">
                                            <a data-toggle="modal" data-target="#editAdjustmentEntry"
                                            onclick="editAdjustmentEntry({{$v->id}})" data-placement="bottom" href="javascript:;"
                                            class="ml-4">   <div id="discount-new161114807394472-discount_amount_type"
                                                class="mycase_select">
                                                <?php 
                                                $adType=array("percentage"=>"% - Percentage","amount"=>"$ - Amount");
                                                echo $adType[$v->ad_type];
                                                ?>
                                            </div>
                                            </a>
                        </div>
                        </td>
                        <td>
                            <pre id="discount-new161114807394472-notes" class="new_edit"
                                style="height: auto;">{{$v->notes}}</pre>
                        </td>
                        <td style="text-align: right;" class="">
                            <a data-toggle="modal" data-target="#editAdjustmentEntry"
                                        onclick="editAdjustmentEntry({{$v->id}})" data-placement="bottom" href="javascript:;"
                                        class="ml-4">
                                <?php 
                                                if($v->ad_type=="amount"){
                                                    echo "-";
                                                }else{
                                                    echo $v->basis;
                                                }
                                                ?></a>
                        </td>
                        <td style="text-align: right;" class="">
                            <a data-toggle="modal" data-target="#editAdjustmentEntry"
                                        onclick="editAdjustmentEntry({{$v->id}})" data-placement="bottom" href="javascript:;"
                                        class="ml-4">
                                <?php 
                                                if($v->ad_type=="amount"){
                                                    echo "-";
                                                }else{
                                                    echo $v->percentages."%";
                                                }
                                                ?>

                            </a>
                        </td>

                        <td style="text-align: right;" class="">
                            <a data-toggle="modal" data-target="#editAdjustmentEntry"
                            onclick="editAdjustmentEntry({{$v->id}})" data-placement="bottom" href="javascript:;"
                            class="ml-4">
                            {{$v->amount}}</a>

                            <?php 
                                                if($v->item=="discount"){
                                                    $discount+=$v->amount;
                                                }else{
                                                    $addition+=$v->amount;
                                                }
                                                ?>
                        </td>
                        <td>&nbsp;</td>
                        </tr>
                        <?php } ?>


                        <tr class="footer">
                            <td colspan="4">
                                <div class="locked">
                                    <a data-toggle="modal" data-target="#addNewAdjustmentEntry"
                                        onclick="addNewAdjustmentEntry()" data-placement="bottom" href="javascript:;"
                                        class="ml-4"> <i class="fas fa-plus align-middle"></i> Add
                                        Adjustment</a>
                                </div>
                            </td>
                            <td colspan="3" style="text-align: right;">
                                <div class="locked">
                                    <?php if($discount!="0"){?>
                                    <span class="billing-discounts-area p-2">
                                        {{@$caseMaster['case_title']}} discounts:
                                    </span>
                                    <?php } ?>
                                    <?php if($addition!="0"){?>
                                    <div style="border: none; padding-top: 7px;" class="billing-additions-area p-2">
                                        {{@$caseMaster['case_title']}} additions:
                                    </div>
                                    <?php } ?>
                                    <div style="border: none; padding-top: 7px; display: none;"
                                        class="billing-write-offs-area">
                                        {{@$caseMaster['case_title']}} write-offs:
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="locked" style="text-align: right;">
                                    <?php if($discount!="0"){?>
                                    <div class="billing-discounts-area">
                                        ($<span id="discounts_section_total"
                                            class="table_total amount">{{$discount}}</span>)
                                    </div>
                                    <?php } ?>
                                    <?php if($addition!="0"){?>
                                    <div style="border: none; padding-top: 7px;" class="billing-additions-area ">
                                        $<span id="additions_section_total" class="table_total amount">{{$addition}}</span>
                                    </div>
                                    <?php } ?>
                                    <div style="border: none; padding-top: 7px; display: none;"
                                        class="billing-write-offs-area">
                                        ($<span id="write_offs_section_total" class="table_total">0.00</span>)
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                        </tr>

                        </tbody>
                        </table>


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
                                                style="border: none; padding-bottom: 7px; ">
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
                                                style="border: none; padding-bottom: 7px; ">
                                                $<span id="flat_fee_total_amount" class="flat_fee_total_amount">{{number_format($flateFeeTotal,2)}}</span>
                                            </div>
                                            <div style="border: none; padding-bottom: 7px;" class="time-entries-totals">
                                                $<span id="time_entry_total_amount"
                                                    class="time_entry_total_amount">{{number_format($timeEntryAmount,2)}}</span>
                                            </div>
                                            <div style="border: none; padding-bottom: 7px;" class="expense-totals">
                                                $<span id="expense_total_amount"
                                                    class="expense_total_amount">{{number_format($expenseAmount,2)}}</span>
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
                                            <?php if($discount!="0"){?>
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

                                <input type="hidden" value="{{$flateFeeTotal}}" name="flat_fee_sub_total_text" id="flat_fee_sub_total_text">
                                <input type="hidden" value="{{$timeEntryAmount}}" name="time_entry_sub_total_text" id="time_entry_sub_total_text">
                                <input type="hidden" value="{{$expenseAmount}}" name="expense_sub_total_text"
                                    id="expense_sub_total_text">
                                <input type="hidden" value="" name="sub_total_text" id="sub_total_text">
                                <input type="hidden" value="" name="total_text" id="total_text">
                                <input type="hidden" value="{{$discount}}" name="discount_total_text"
                                id="discount_total_text">
                                <input type="hidden" value="{{$addition}}" name="addition_total_text"
                                id="addition_total_text">
                                <input type="hidden" value="" name="final_total_text" id="final_total_text">
                                <input type="hidden" value="{{$adjustment_token}}" name="adjustment_token" id="adjustment_token">
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
                                <textarea style="width: 100%; height: 150px;" class="boxsizingBorder" name="bill[terms_and_conditions]" id="bill_terms_and_conditions">{{$findInvoice->terms_condition}}</textarea>
                            </td>
                            <td style="padding: 0px 5px 5px 5px;">
                                <textarea style="width: 100%; height: 150px;" class="boxsizingBorder" name="bill[bill_notes]" id="bill_bill_notes">{{$findInvoice->notes}}</textarea>
                            </td>
                            </tr>
                        </tbody>
                        </table>
                    </div>



                    <div class="invoice_option_header clearfix">
                        <div style="float: right;" class="mt-2">
                            <label class="switch switch-success"><span>Enabled</span>
                                <input type="checkbox" name="payment_plan" id="payment_plan" <?php if($findInvoice->payment_plan_enabled=="yes"){ echo "checked=checked"; } ?>><span class="slider"></span>
                            </label>
                            
                        </div>
                        <h3 id="payment-plan" class="invoice_header">
                        <img src="{{ asset("svg/payment_plan.svg") }}" width="28" height="28">
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
                                                <?php
                                                  $sum=0;
                                                if(!$InvoiceInstallment->isEmpty()){
                                                  
                                                $usRemove="";

                                                foreach($InvoiceInstallment as $key=>$value){
                                                    if(!in_array($key,[0,1])){
                                                        $usRemove="tablePaymentPlanRemove";
                                                    }
                                                    $sum+=$value->installment_amount;
                                                    ?>
                                                    <tr class="invoice_entry payment_plan_row {{$usRemove}}" id="row_{{$key}}">
                                                        <td style="vertical-align: center; text-align: center; border-right: none;" class="remove_button" >
                                                            <div class="payment_plan_entry"> 
                                                                <a class="image_link_sprite image_link_sprite_cancel " href="javascript:void(0);">
                                                                    <i class="fas fa-times"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td style="border-left: none;" class="">
                                                            <div id="invoice_entry_date_plan" class="invoice-entry-date" data-entry-id="plan161122040617875"> 
                                                                <input value="{{date('m/d/Y',strtotime($value->due_date))}}" id="invoice_entry_date_text_plan161122040617875" style="width: 100%;border:none;" class="invoice-entry-date-text boxsizingBorder datepicker" type="text" name="new_payment_plans[{{$key}}][due_date]" placeholder="Choose Date">
                                                            </div>
                                                        </td>
                                                        <td style="text-align: right; border-right: none;" class=""> 
                                                            <input value="{{number_format($value->installment_amount,2)}}" id="invoice_plan_amount_text_plan161122040617875" style="width: 100%; text-align: right;border:none;" class="boxsizingBorder edit_payment_plan_amount" type="text" name="new_payment_plans[{{$key}}][amount]" onblur="installmentCalculation(this)">
                                                        </td>
                                                        <td style="vertical-align: center; text-align: center; border-right: none;dispaly:none;">
                                                            <div class="payment_plan_entry"> 
                                                                <a class="image_link_sprite image_link_sprite_cancel" href="javascript:void(0);" >
                                                                    <i class="fas fa-pen"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td style="vertical-align: middle;" class="tablePaymentPlanEdit">
                                                            <p class="autopay-field m-0" data-testid="autopay-field" style="display: none; padding-left: 20px;">
                                                            </p>
                                                        </td>
                                                    </tr>
                                                   <?php
                                                }
                                            }
                                            ?>
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
                                                                                id="amount_per_installment_field"  name="amount_per_installment_field" class="form-control"
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
                                                                        <button type="button"  name="installmentBreak" class="submitbutton btn btn-outline-secondary btn-rounded m-1" style="width: 40%;"><strong>Recalculate</strong></button>
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
                                <img src="{{ asset("svg/share.svg") }}" width="28" height="28">
                                Share Invoice via Client Portal
                            </h3>
                        </div>
                    
                        <div class="sharing-table-container" data-bill_id="" data-sharing-from="bill_form">
                            <table class="sharing-table">
                                <tbody>
                                    <tr>
                                        <td id="sharing_left_side" class="sharing-left-side" style="vertical-align: top;padding-top: 13px;">
                                            <div class="bootstrap">
                                                <div class="alert alert-info alert-dismissible "
                                                    data-tip-name="client_portal_invoice_sharing">
                                                    <button type="button" class="close dismiss-tip-button" data-dismiss="alert"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                    <div class="tooltip-message">Your client will get a notification email that they now have an invoice available in their portal, with an option to login. If you wish to directly email this invoice as a PDF from {{config('app.name')}}, click the "Email Invoice" option on the invoice details view. <a class="text-nowrap" target="_blank" href="#">What will my client see?</a>
                                                    </div>
                                                </div>
                                            </div>
                    
                                            <table class="data invoice_entries">
                                                <tbody>
                                                    <tr>
                                                        <th class="invoice-sharing-header-checkbox" width="5%">
                                                            Share
                                                        </th>
                                                        <th width="75%">
                                                            Contact Name
                                                        </th>
                                                        <th class="invoice-sharing-header" width="20%">
                                                            Last Login
                                                        </th>
                                                    </tr>
                                                    <?php 
                                                    foreach($getAllClientForSharing as $k=>$v){?>
                                                    <tr class="invoice-sharing-row client-id-21672788" id="row_{{$v->user_id}}">
                                                        <td style="text-align: center;padding:10px;">
                                                            <div class="locked">
                                                                <input type="checkbox" name="portalAccess[]"  value="{{$v->user_id}}"  id="portalAccess_{{$v->user_id}}" class="invoiceSharingBox invoice-sharing-box"  uid="{{$v->user_id}}"  em="{{$v->email}}" pe="{{$v->client_portal_enable}}" onclick="checkPortalAccess({{$v->user_id}})"  <?php if(in_array($v->user_id,$SharedInvoice)) { echo "checked=checked"; } ?> >
                                                            </div>
                                                        </td>
                                                        <td class="invoice-sharing-name">
                                                            <div class="locked pl-1">
                                                                {{ucfirst($v->unm)}} 
                                                                <?php 
                                                                if($v->user_level=="2"){
                                                                    echo "(Client)";
                                                                }else{
                                                                    echo "(Company)";
                                                                }?>
                                                            </div>
                                                        </td>
                                                        <td class="invoice-sharing-last-login">
                                                            <div class="locked last-login-at pl-1">
                                                                <?php if($v->email==""){
                                                                    echo "Disabled";
                                                                }else{
                                                                    $CommonController= new App\Http\Controllers\CommonController();
                                                                    if($v->last_login!=NULL){
                                                                        $loginDate=$CommonController->convertUTCToUserTime($v->last_login,Auth::User()->user_timezone);
                                                                        echo date('F jS Y, h:i:s A',strtotime($loginDate));
                                                                    }else{
                                                                        echo "Never";
                                                                    }
                                                                }
                                                                ?></div>
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                            <div class="reminder-tip text-right">*Once shared, you will have the option of sending reminders to clients.</div>
                                        </td>
                                        <td id="sharing_right_side" style="width: 300px; padding-left: 10px; height: 100%;padding-top:10px;">
                    
                                            <div class="get-paid-now-ads bootstrap">
                                                <img src="{{ asset('images/get_paid_now_ads.png') }}">
                                                <div class="pt-2 get-paid-now-text">
                                                    <ul>
                                                        <li>
                                                            <strong>Built for Law Firms</strong> by {{config('app.name')}}
                                                        </li>
                                                        <li>
                                                            <strong>Get paid faster</strong> by letting your clients pay online
                                                        </li>
                                                        <li>
                                                            <strong>Save money</strong> with free eCheck payments and competitive credit
                                                            card fees
                                                        </li>
                                                    </ul>
                                                    <div class="text-center">
                                                        <a target="_blank" class="btn btn-payment show-me-how-btn mb-2"
                                                            href="#">Show Me</a>
                                                        <p><small>Sign up today to start receiving payments through {{config('app.name')}}</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
                                    <a href="{{BASE_URL}}bills/invoices/view/{{base64_encode($findInvoice->id)}}" class="btn btn-primary ladda-button example-button m-1">Yes</a>
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
<!-- For Time Entry -->
{{-- <div id="delete_existing_dialog" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="removeExistingEntryForm" id="removeExistingEntryForm" name="removeExistingEntryForm" method="POST">
            @csrf
            <input type="hidden" value="" name="time_entry_id" id="delete_time_entry_id">
            <input type="hidden" value="{{$adjustment_token}}" name="token_id" id="token_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Remove Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Would you like to <strong>remove</strong> the selected entry from this invoice or
                            permanently <strong>delete</strong> it from {{config('app.name')}}?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-10  text-center">
                        <div class="form-group row float-left">
                            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                                style="display: none;"></div>
                        </div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <input class="btn btn-primary ladda-button example-button m-1 submit"
                                onclick="actionTimeEntry('delete')" name="action" value="Delete" id="submit"
                                type="submit">
                            <input class="btn btn-primary ladda-button example-button m-1 submit" name="action"
                                value="Remove" id="submit" onclick="actionTimeEntry('remove')" type="submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div> --}}
<div id="removeAlllExistingTimeEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="removeAlllExistingTimeEntryForm" id="removeAlllExistingTimeEntryForm" name="removeAlllExistingTimeEntryForm" method="POST">
            <input type="hidden" value="{{$adjustment_token}}" name="token_id" id="token_id">
            <input type="hidden" value="{{(isset($caseMaster)) ? base64_encode($caseMaster['id']) : 0 }}" name="case_id">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Remove All Time Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            <div>
                                <p>Would you like to <strong>remove</strong> all Time Entries from this invoice?</p>
                                <p><strong>Note:</strong> Pre-existing entries will still exist in
                                    {{config('app.name')}}.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-10  text-center">
                        <div class="form-group row float-left">
                            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                                style="display: none;"></div>
                        </div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <input class="btn btn-primary ladda-button example-button m-1 submit" name="action"
                                value="Remove All Entries" id="submit" type="submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="addNewTimeEntry" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Add Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="addNewTimeEntryArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="editNewTimeEntry" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Update Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="editNewTimeEntryArea">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- For Time Entry -->


<!-- For Expense Entry -->
{{-- <div id="delete_expense_existing_dialog" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="removeExistingExpenseEntryForm" id="removeExistingExpenseEntryForm"
            name="removeExistingExpenseEntryForm" method="POST">
            @csrf
            <input type="hidden" value="" name="expense_entry_id" id="delete_expense_entry_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Remove Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Would you like to <strong>remove</strong> the selected entry from this invoice or
                            permanently <strong>delete</strong> it from {{config('app.name')}}?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-10  text-center">
                        <div class="form-group row float-left">
                            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                                style="display: none;"></div>
                        </div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <input class="btn btn-primary ladda-button example-button m-1 submit"
                                onclick="actionExpenseEntry('delete')" name="action" value="Delete" id="submit"
                                type="submit">
                            <input class="btn btn-primary ladda-button example-button m-1 submit" name="action"
                                value="Remove" id="submit" onclick="actionExpenseEntry('remove')" type="submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div> --}}

<div id="removeAlllExistingExpenseEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="removeAlllExistingExpenseEntryForm" id="removeAlllExistingExpenseEntryForm"
            name="removeAlllExistingExpenseEntryForm" method="POST">
            @csrf
            <input type="hidden" value="{{ (isset($caseMaster)) ? base64_encode(@$caseMaster['id']) : 0 }}" name="case_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Remove All Expenses</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            <div>
                                <p>Would you like to <strong>remove</strong> all Expenses from this invoice?</p>
                                <p><strong>Note:</strong> Pre-existing entries will still exist in
                                    {{config('app.name')}}.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-10  text-center">
                        <div class="form-group row float-left">
                            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                                style="display: none;"></div>
                        </div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <input class="btn btn-primary ladda-button example-button m-1 submit" name="action"
                                value="Remove All Entries" id="submit" type="submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="addNewExpenseEntry" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Add Expense</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="addNewExpenseEntryArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="editNewExpenseEntry" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Update Expense</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="editNewExpenseEntryArea">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- For Expense Entry -->


<!-- For Adjustment Entry -->
<div id="addNewAdjustmentEntry" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Add Adjustment</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="addNewAdjustmentEntryArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="editAdjustmentEntry" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Update Adjustment</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="editAdjustmentEntryArea">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- <div id="delete_flatfee_existing_dialog_bbox" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="removeExistingFlateFeesForm" id="removeExistingFlateFeesForm"
            name="removeExistingFlateFeesForm" method="POST">
            @csrf
            <input type="hidden" value="" name="adjustment_entry_id" id="delete_flatefees_existing_dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Remove Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Would you like to <strong>remove</strong> the selected entry from this invoice or
                            permanently <strong>delete</strong> it from {{config('app.name')}}?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-10  text-center">
                        <div class="form-group row float-left">
                            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                                style="display: none;"></div>
                        </div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <input class="btn btn-primary ladda-button example-button m-1 submit"
                                onclick="actionAdjustmentEntry('delete')" name="action" value="Delete" id="submit"
                                type="submit">
                            <input class="btn btn-primary ladda-button example-button m-1 submit" name="action"
                                value="Remove" id="submit" onclick="actionAdjustmentEntry('remove')" type="submit">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div> --}}
<!-- For Adjustment Entry -->

<div id="confirmAccessModal" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="EnableAccessForm" id="EnableAccessForm" name="EnableAccessForm" method="POST">
            @csrf
            <input type="hidden" value="{{$userData['id']}}" name="client_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Sharing with a client</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            <div>
                                Invoices can only be shared with contacts enabled for the Client Portal. Would you like to give Client Portal access to {{ucfirst($userData['first_name'])}} {{$userData['middle_name']}}  {{$userData['last_name']}}?
                            </div>
                            <br>

                            <p>Note: Only items explicitly shared with contacts will be accessible. An email with login instructions will be automatically sent.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                             <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Confirm Access</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="grantAccessModal" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Sharing with a client</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="grantAccessModalArea">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- For Flat fee entry --}}
<div id="addNewFlatFeeEntry" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Add Flat Fee</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="addNewFlatFeeEntryArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="editNewFlatFeeEntry" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Edit Flat Fee</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="editNewFlatFeeEntryArea">
                </div>
            </div>
        </div>
    </div>
</div>

@include('billing.invoices.partials.modal')

<style>
        .strike{
        text-decoration: line-through;
        color: #aaa;
    }
    div.invoice_page {
        background-color: #fff;
        border: 1px solid #333;
        padding: 20px;
        min-height: 600px;
        -webkit-box-shadow: 0 0 4px #333;
        box-shadow: 0 0 4px #333;
    }

    application-4e0803672e.css:1 div.invoice,
    div.invoice td,
    div.invoice th {
        color: #000;
        font-size: 12px;
    }

    show-33e822d17a.css:1 .invoice_page {
        position: relative;
    }

    i.invoice-banner-draft {
        background-image: url('{{ asset("images/invoice_banner_draft.png") }}');
        height: 127px;
        width: 127px;
    }

    i.invoice-banner-sent {
        background-image: url('{{ asset("images/invoice_banner_sent.png") }}');
        height: 127px;
        width: 127px;
    }

    i.invoice-banner-unsent {
        background-image: url('{{ asset("images/invoice_banner_unsent.png") }}');
        height: 127px;
        width: 127px;
    }
    i.invoice-banner-partial {
        background-image: url('{{ asset("images/invoice_banner_partial.png") }}');
        height: 127px;
        width: 127px;
    }
    i.invoice-banner-paid {
        background-image: url('{{ asset("images/invoice_banner_paid.png") }}');
        height: 127px;
        width: 127px;
    }
    i.invoice-banner-overdue {
        background-image: url('{{ asset("images/invoice_banner_overdue.png") }}');
        height: 127px;
        width: 127px;
    }
    div.invoice,
    div.invoice td,
    div.invoice th {
        color: #000;
        font-size: 12px;
    }

    tr.invoice_info_row td {
        border: 1px solid #000;
        padding: 5px;
        word-wrap: break-word;
    }

    i {
        display: inline-block;
    }
   
   
    i.invoice-banner-draft {
        background-image: url('{{BASE_URL}}public/images/invoice_banner_draft.png');
        height: 127px;
        width: 127px;
        display: block;
    }

    i.invoice-banner-sent {
        background-image: url('{{BASE_URL}}public/images/invoice_banner_sent.png');
        height: 127px;
        width: 127px;
        display: block;
    }

    i.invoice-banner-unsent {
        background-image: url('{{BASE_URL}}public/images/invoice_banner_unsent.png');
        height: 127px;
        width: 127px;
        display: block;
    }


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
<script src="{{ asset('assets\js\custom\invoice\addinvoice.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        <?php if($findInvoice->payment_plan_enabled=="yes"){?>
            $("#payment_plan_details").slideToggle();
        <?php } ?>
        // $("#payment_plan").prop('checked',false);
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

        $('.invoice_entry_nonbillable_flat').change(function () { //".checkbox" change 
            var id = $(this).attr('id');
            var val = $(this).val;
            var sum = 0;
            $('input[name="flat_fee_entry[]"]').each(function (i) {
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
            $(".flat_fee_table_total").html(sum);
            $("#flat_fee_sub_total_text").val(sum);
            $('.flat_fee_table_total').number(true, 2);

            $(".flat_fee_total_amount").html(sum);
            $('.flat_fee_total_amount').number(true, 2);
            recalculate();
        });


       
        var wrapper = $('.field_wrapper'); //Input field wrapper
        <?php 
        if($InvoiceInstallment->isEmpty()){?>
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
        <?php } ?>

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
                'startDate': "dateToday",
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
            $('input[name="invoice_expense_entry_nonbillable_time[]"]').each(function (i) {
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
        // $('input[name="linked_staff_checked_share[]"]').prop('checked', false);
        // $('input[name="expense_entry[]"]').prop('checked', false);
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
                    'startDate': "dateToday",
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

    });
    $('#removeAlllExistingTimeEntryForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#removeAlllExistingTimeEntryForm').valid()) {
            beforeLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#removeAlllExistingTimeEntryForm").serialize();
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
            $('#bill_due_date').datepicker('update', CheckOut).focus();
           
        }else if(selectdValue==3){
            CheckIn = $("#bill_invoice_date").datepicker('getDate');
            CheckOut = moment(CheckIn).add(30, 'day').toDate();
            $('#bill_due_date').datepicker('update', CheckOut).focus();
           
        }else{
            CheckIn = $("#bill_invoice_date").datepicker('getDate');
            CheckOut = moment(CheckIn).add(60, 'day').toDate();
            $('#bill_due_date').datepicker('update', CheckOut).focus();
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
        var URLS=baseUrl+'/bills/invoices/new?court_case_id='+case_id+'&token={{$adjustment_token}}';
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
        var flat_fee_sub_total_text = parseFloat($("#flat_fee_sub_total_text").val());
        total = expense_total_amount + time_entry_total_amount + flat_fee_sub_total_text;

        var discount_amount = parseFloat($("#discount_total_text").val());
        var addition_amount = parseFloat($("#addition_total_text").val());

        var final_total=total-discount_amount+addition_amount;
        if(final_total <= 0 ){
            final_total=0;
        }
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

    /* function actionTimeEntry(action) {
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
                        // window.location.reload();
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
    } */

    /* function openTimeDelete(id) {
        $("#delete_existing_dialog").modal("show");
        $("#delete_time_entry_id").val(id);
    } */

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
                "id": "{{ (isset($caseMaster)) ? base64_encode($caseMaster['id']) : 0 }}",
                "invoice_id":"{{$findInvoice->id}}"
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


    function addSingleFlatFeeEntry() {
        $('.showError').html('');
        beforeLoader();
        $("#addNewFlatFeeEntryArea").html('');
        $("#addNewFlatFeeEntryArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/addSingleFlatFeeEntry",
            data: {
                "id": "{{ (isset($caseMaster)) ? base64_encode($caseMaster['id']) : 0 }}",
                "invoice_id":"{{$findInvoice->id}}"
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
                    $("#addNewFlatFeeEntryArea").html('');
                    $('#addSingleFlatFeeEntry').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    afterLoader()
                    $("#addNewFlatFeeEntryArea").html(res);
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
                $('#addSingleFlatFeeEntry').animate({
                    scrollTop: 0
                }, 'slow');

                afterLoader();
            }
        })
    }
    /* function openExpenseDelete(id) {
        $("#delete_expense_existing_dialog").modal("show");
        $("#delete_expense_entry_id").val(id);
    } */

    /* function actionExpenseEntry(action) {
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
    } */

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
                "id": "{{ (isset($caseMaster)) ? base64_encode($caseMaster['id']) : 0 }}",
                "invoice_id":"{{$findInvoice->id}}"

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
                "id": "{{ (isset($caseMaster)) ? base64_encode($caseMaster['id']) : 0}}",
                "adjustment_token":"{{$adjustment_token}}",
                "invoice_id":"{{$findInvoice->id}}"

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
    // $('input[name=client_portal_enable]').attr("checked",false);


    /* function openAdjustmentDelete(id) {
        $("#delete_flatfee_existing_dialog_bbox").modal("show");
        $("#delete_flatefees_existing_dialog").val(id);
    } */

    /* function actionAdjustmentEntry(action) {
        $('#removeExistingFlateFeesForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#removeExistingFlateFeesForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#removeExistingFlateFeesForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/deleteAdustmentEntry", // json datasource
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
    } */

    setTimeout(function(){  $("#payment_plan_balance").html("{{($findInvoice->total_amount-$sum)}}");
    $('#payment_plan_balance').number(true, 2); }, 1000);

    function editSingleFlatFeeEntry(id) {
        $('.showError').html('');
        beforeLoader();
        $("#editNewFlatFeeEntryArea").html('');
        $("#editNewFlatFeeEntryArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/editSingleFlatFeeEntry",
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
                    $("#editNewFlatFeeEntryArea").html('');
                    $('#editNewTimeEntry').animate({
                        scrollTop: 0
                    }, 'slow');

                    return false;
                } else {
                    afterLoader()
                    $("#editNewFlatFeeEntryArea").html(res);
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
   
</script>
@stop
@endsection
