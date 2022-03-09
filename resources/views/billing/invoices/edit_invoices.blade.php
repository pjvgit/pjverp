@extends('layouts.master')
<?php $s = sprintf('%06d', $findInvoice->id);?>
@section('title', 'Edit Invoice #'.$s.' - Invoices - Billing')@section('main-content')
@include('billing.submenu')
<div class="separator-breadcrumb border-top"></div>
<div class="row">
<?php
$timeEntryTime=$timeEntryAmount=0;
$expenseTime=0;$expenseAmount=0;
$nonBillableAmount = 0;
?>
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
                            <a class="btn btn-link text-black-50" target="_blank" href="{{route('billing/settings')}}">Manage Firm Invoice Settings</a>
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
                                                        <option uType="client"  value="{{ $item->id }}" {{ (isset($findInvoice) && $item->id == $findInvoice->user_id) ? "selected" : "" }}> {{ substr($item->name,0,200) }} (Client)</option>
                                                        @empty
                                                        @endforelse
                                                    </optgroup>
                                                    <optgroup label="Company">
                                                        {{-- <?php foreach($CompanyList as $CompanyListKey=>$CompanyListVal){ ?>
                                                        <option uType="company" <?php if($CompanyListVal->id==$findInvoice->user_id){ echo "selected=selected";} ?> value="{{$CompanyListVal->id}}"> {{substr($CompanyListVal->first_name,0,200)}} (Company)</option><?php } ?> --}}
                                                        @forelse ($CompanyList as $key => $item)
                                                        <option uType="company"  value="{{ $item->id }}" {{ (isset($findInvoice) && $item->id == $findInvoice->user_id) ? "selected" : "" }}> {{ substr($item->name,0,200) }} (Company)</option>
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
                                                    <?php
                                                    $clientCasesList = [];
                                                    foreach ($caseListByClient as $k =>$v){ 
                                                        array_push($clientCasesList,$v->id);
                                                    }
                                                    ?>
                                                    @if(in_array($findInvoice->case_id, $clientCasesList))
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
                                                    @else
                                                    <i>This Contact is not linked to any billable cases</i>
                                                    @endif
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
                                        <textarea readonly="readonly" id="bill_address_text" name="bill_address_text" class="form-control"style="width: 70%; height: 104px; resize: none; overflow-y: hidden;">
                                        {{$findInvoice->bill_address_text}}       
                                        </textarea>
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
                                                title="{{ (isset($invoiceSetting) && !empty($invoiceSetting) && $invoiceSetting['reminder']) ? $findInvoice->getReminderMessage() : ((isset($invoiceDefaultSetting) && $invoiceDefaultSetting->reminderSchedule) ? $invoiceDefaultSetting->getReminderMessage() : '' ) }}">
                                                <i class="pl-1 fas fa-question-circle fa-lg"></i></span>

                                            <input type="checkbox" name="automated_reminders" id="automated_reminders" <?php if($findInvoice->automated_reminder=="yes"){ echo "checked=checked";} ?> @if($findInvoice->payment_term == 5) disabled @endif><span
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

                    <?php if($findInvoice->case_id != 'none'){ ?>
                    <div id="invoice-date-range-filter" class="invoice_entry_header"
                        style="margin-top: 10px; position: relative;">
                        <div id="range_select_block"
                            style="position: absolute; display: none; top: 0px; right: 0px; width: 500px; height: 60px; z-index: 50;">
                        </div>
                        <table style="color: black;">
                            <tbody>
                                <tr>
                                    <td style="width: 100%; color: black; text-align: right; padding-right: 10px; padding-top: 9px;vertical-align: middle;">
                                        <input type="checkbox" name="range_check_box" id="range_check_box" value="1">
                                        <label for="range_check_box">Filter by date range</label>
                                    </td>
                                    <td style="color: black; vertical-align: middle;" class="range_select disabled">From:
                                    </td>
                                    <td style="width: 100%;">
                                        <div class="input-daterange input-group flex-nowrap align-items-center" id="datepicker" style="align-items: center;">
                                        <input style="width: 115px;" type="text" class="form-control" name="bill_from_date" value="{{$bill_from_date}}"
                                        id="bill_from_date" disabled="disabled" />
                                        <span class="input-group-addon">&nbsp;To&nbsp;</span>
                                        <input style="width: 115px;"  disabled="disabled" type="text" class="form-control" name="bill_to_date" value="{{$bill_to_date}}"
                                        id="bill_to_date" />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <input type="hidden" id="adjustment_delete" name="adjustment_delete" value=""/>
                        </table>
                    </div>
                    <?php } ?>
                    <div id="entries" style="margin: 5px;">

                        <div class="invoice_case_gradient">
                            <h2><i class="fas fa-briefcase mr-2"></i> {{@$caseMaster['case_title'] ?? 'None'}} 
                                @if(isset($invoiceSetting) && !empty($invoiceSetting) && $invoiceSetting['show_case_no_after_case_name'] == "yes")
                                    @if($caseMaster) ({{ $caseMaster->case_number }}) @endif
                                @endif
                            </h2>
                        </div>
                        <?php $flateFeeTotal=0; ?>
                        <div class="invoice_entry_header">
                            <table>
                                <tr>
                                    <td width="100%">
                                        <h3 class="entry-header">Flat Fees</h3>
                                    </td>
                                    <td width="1%">
                                        <span data-toggle="tooltip" data-placement="left" title="Remove all flat fees">
                                            <a data-toggle="modal" data-target="#removeAlllExistingFlatFeeEntry"
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
                                    <th style="border-left: none;">Date
                                        @if (count(getFlatFeeColumnArray()) && !in_array('date', getFlatFeeColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> EE 
                                        @if (count(getFlatFeeColumnArray()) && !in_array('employee', getFlatFeeColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> Employee
                                        @if (count(getFlatFeeColumnArray()) && !in_array('employee', getFlatFeeColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> Item 
                                        @if (count(getFlatFeeColumnArray()) && !in_array('item', getFlatFeeColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> Flat Fee Notes
                                        @if (count(getFlatFeeColumnArray()) && !in_array('notes', getFlatFeeColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> Amount 
                                        @if (count(getFlatFeeColumnArray()) && !in_array('amount', getFlatFeeColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
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
                                <?php }else{ ?>
                                <?php foreach($FlatFeeEntryForInvoice as $k=>$v){
                                    $flateFeeTotal+= ($v->time_entry_billable !="no") ? $v->cost : 0;
                                ?>
                                
                                <tr id="FlatFee-{{$v->itd}}" class="invoice_entry time_entry ">
                                    <td style="vertical-align: center; text-align: center; border-right: none;"
                                        class="tdTime">
                                        <div class="invoice_entry_actions">
                                        <?php $action = ($v->token_id == '9999999') ? 'deleteonly' : 'fulldelete'; ?>
                                            <a class="image_link_sprite image_link_sprite_cancel" href="javascript:void(0);" onclick="openFlatFeeDelete({{$v->itd}},'{{$action}}');">
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
                                    <td style="text-align: left;" class="billable_toggle time-entry-hours flat_amount_{{$v->itd}} <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>">
                                           {{$v->cost}} 
                                    </td>                                    
                                    <td style="text-align: center; padding-top: 10px !important;">
                                        <input type="checkbox" class="invoice_entry_nonbillable_flat nonbillable-check" data-primaryID="{{$v->itd}}" data-token_id="{{$adjustment_token}}" data-check-type="flat" id="invoice_entry_nonbillable_flat_{{$v->itd}}" <?php if($v->time_entry_billable=="no"){ echo "checked=checked"; $nonBillableAmount += 1;} ?>
                                            name="flat_fee_entry[]" priceattr="{{$v->cost}}" value="{{$v->itd}}">
                                    </td>
                                </tr>
                                <?php } }?>
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

                        <?php if($findInvoice->case_id != 'none'){ ?>
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
                                    <th style="border-left: none;">Date
                                        @if (count(getTimeEntryColumnArray()) && !in_array('date', getTimeEntryColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> EE 
                                        @if (count(getTimeEntryColumnArray()) && !in_array('employee', getTimeEntryColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> Employee
                                        @if (count(getTimeEntryColumnArray()) && !in_array('employee', getTimeEntryColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> Activity 
                                        @if (count(getTimeEntryColumnArray()) && !in_array('activity', getTimeEntryColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> Time Entry Notes
                                        @if (count(getTimeEntryColumnArray()) && !in_array('notes', getTimeEntryColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> Rate 
                                        @if (count(getTimeEntryColumnArray()) && !in_array('amount', getTimeEntryColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> Hours 
                                        @if (count(getTimeEntryColumnArray()) && !in_array('hour', getTimeEntryColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
                                    <th> Line Total 
                                        @if (count(getTimeEntryColumnArray()) && !in_array('line_total', getTimeEntryColumnArray()))
                                        <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                        @endif
                                    </th>
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
                                
                                foreach($TimeEntry as $k=>$v){
                                ?>
                                
                                <tr id="time-79566738-7" class="invoice_entry time_entry ">
                                    <td style="vertical-align: center; text-align: center; border-right: none;"
                                        class="tdTime">
                                        <div class="invoice_entry_actions">
                                            <?php $action = ($v->token_id == '9999999') ? 'deleteonly' : 'fulldelete'; ?>   
                                            <a class="image_link_sprite image_link_sprite_cancel" href="javascript:void(0);"
                                                onclick="openTimeDelete({{$v->itd}},'{{$action}}');"><i class="fas fa-times"></i></a>
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
                                    <td style="text-align: right;" class="billable_toggle time-entry-rate  timeentry_amount_{{$v->itd}} <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>">
                                        <a data-toggle="modal" data-target="#editNewTimeEntry"
                                            onclick="editSingleTimeEntry({{$v->itd}})" data-placement="bottom"
                                            href="javascript:;" class="ml-0">
                                            {{$v->entry_rate}}</a>
                                    </td>
                                    <?php
                                    if($v->rate_type=="flat"){?>
                                    <td style="text-align: right;" class="billable_toggle time-entry-hours  timeentry_amount_{{$v->itd}} <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>">
                                        <a data-toggle="modal" data-target="#editNewTimeEntry"
                                            onclick="editSingleTimeEntry({{$v->itd}})" data-placement="bottom"
                                            href="javascript:;" class="ml-0">
                                            flat
                                        </a>
                                    </td>
                                    <?php }else{?>
                                    <td style="text-align: right;" class="billable_toggle time-entry-hours row_total timeentry_amount_{{$v->itd}} <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>">
                                        <a data-toggle="modal" data-target="#editNewTimeEntry"
                                            onclick="editSingleTimeEntry({{$v->itd}})" data-placement="bottom"
                                            href="javascript:;" class="ml-0">
                                        {{str_replace(",","",$v->duration)}}
                                        </a>
                                    </td>
                                    <?php } ?>
                                    <td class="billable_toggle pr-2">
                                        <div class="locked row_total timeentry_amount_{{$v->itd}} <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>" style="text-align: right;">
                                            <?php 
                                            
                                            if($v->rate_type=="flat"){
                                                echo $Total=$v->entry_rate;
                                                if($v->time_entry_billable=="yes"){
                                                    $timeEntryAmount+=$v->entry_rate;
                                                }
                                            }else{
                                                echo $Total= (str_replace(",","",$v->duration) * $v->entry_rate);
                                                if($v->time_entry_billable=="yes"){
                                                    $timeEntryAmount+=$Total;
                                                    $timeEntryTime=$timeEntryTime+str_replace(",","",$v->duration);
                                                }
                                            }

                                          
                                            ?>
                                            <input type="hidden" value="{{$Total}}" class="amount_{{$v->itd}}">
                                        </div>
                                    </td>
                                    <td style="text-align: center; padding-top: 10px !important;">
                                        <input type="checkbox" class="invoice_entry_nonbillable_time nonbillable-check" data-primaryID="{{$v->itd}}" data-token_id="{{$adjustment_token}}" data-check-type="time"
                                            id="invoice_entry_nonbillable_time_{{$v->itd}}" <?php if($v->time_entry_billable=="no"){ echo "checked=checked"; $nonBillableAmount += 1;} ?>
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
                                            {{@$caseMaster['case_title']}} time entry totals:
                                        </div>
                                    </td>
                                    <td>
                                        <div class="locked time_entry_table_hours_total"
                                            style="text-align: right; padding-right: 5px;">{{$timeEntryTime}}</div>
                                    </td>
                                    <td>
                                        <div class="locked" style="text-align: right;">
                                            $<span id="time_entry_table_total"
                                                class="table_total time_entry_table_total">{{number_format($timeEntryAmount,2)}}</span>

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
                                            @if (count(getExpenseColumnArray()) && !in_array('date', getExpenseColumnArray()))
                                            <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                            @endif
                                        </th>
                                        <th style="width: 60px;">
                                            EE
                                            @if (count(getExpenseColumnArray()) && !in_array('employee', getExpenseColumnArray()))
                                            <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                            @endif
                                        </th>
                                        <th style="width: 120px;">
                                            Employee
                                            @if (count(getExpenseColumnArray()) && !in_array('employee', getExpenseColumnArray()))
                                            <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                            @endif
                                        </th>
                                        <th style="width: 150px;">
                                            Expense
                                            @if (count(getExpenseColumnArray()) && !in_array('expense', getExpenseColumnArray()))
                                            <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                            @endif
                                        </th>
                                        <th style="width: 250px;">
                                            Expense Notes
                                            @if (count(getExpenseColumnArray()) && !in_array('notes', getExpenseColumnArray()))
                                            <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                            @endif
                                        </th>
                                        <th style="width: 100px;">
                                            Cost
                                            @if (count(getExpenseColumnArray()) && !in_array('amount', getExpenseColumnArray()))
                                            <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                            @endif
                                        </th>
                                        <th style="width: 100px;">
                                            Quantity
                                            @if (count(getExpenseColumnArray()) && !in_array('quantity', getExpenseColumnArray()))
                                            <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                            @endif
                                        </th>
                                        <th style="width: 100px;">
                                            Line Total
                                            @if (count(getExpenseColumnArray()) && !in_array('line_total', getExpenseColumnArray()))
                                            <img class="help_tip tiny-icon opacity-50" src="{{ asset('images/eye-off.svg') }}" data-toggle="tooltip" data-placement="bottom" title="This invoice column should not be shown.">
                                            @endif
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
                                    
                                    foreach($ExpenseEntry as $k=>$v){
                                    ?>

                                    <tr id="time-79566738-7" class="invoice_entry time_entry ">
                                        <td style="vertical-align: center; text-align: center; border-right: none;"
                                            class="tdTimeExpense">
                                            <div class="invoice_entry_actions">
                                                <?php $action = ($v->token_id == '9999999') ? 'deleteonly' : 'fulldelete'; ?>   
                                                <a class="image_link_sprite image_link_sprite_cancel"
                                                    href="javascript:void(0);" onclick="openExpenseDelete({{$v->eid}},'{{$action}}');"><i
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
                                        <td style="text-align: right;" class="billable_toggle time-entry-rate expenseentry_amount_{{$v->eid}} <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?> ">
                                            <a data-toggle="modal" data-target="#editNewExpenseEntry"
                                                onclick="editNewExpenseEntry({{$v->eid}})" data-placement="bottom"
                                                href="javascript:;" class="ml-0">{{$v->cost}}</a>
                                        </td>
                                        <td style="text-align: right;" class="billable_toggle time-entry-hours expenseentry_amount_{{$v->eid}} <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?> ">
                                            <a data-toggle="modal" data-target="#editNewExpenseEntry"
                                                onclick="editNewExpenseEntry({{$v->eid}})" data-placement="bottom"
                                                href="javascript:;" class="ml-0">
                                                <?php 
                                                if($v->rate_type=="flat"){
                                                    echo "flat";
                                                }else{
                                                    echo str_replace(",","",$v->duration);
                                                } ?>
                                            </a>
                                        </td>
                                        <td class="billable_toggle pr-2">
                                            <div class="locked row_total expenseentry_amount_{{$v->eid}} <?php if($v->time_entry_billable=="no"){ echo "strike"; } ?>" style="text-align: right;">
                                                <?php 
                                                echo $Total= (str_replace(",","",$v->duration) * str_replace(",","",$v->cost));
                                                if($v->time_entry_billable=="yes"){
                                                    $expenseAmount+=$Total;
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td style="text-align: center; padding-top: 10px !important;">
                                            <input type="checkbox" class="invoice_expense_entry_nonbillable_time nonbillable-check"  data-primaryID="{{$v->eid}}" data-token_id="{{$adjustment_token}}"  data-check-type="expense"
                                                id="invoice_expense_entry_nonbillable_time{{$v->eid}}"  <?php if($v->time_entry_billable=="no"){ echo "checked=checked"; $nonBillableAmount += 1;} ?>
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
                        <?php } ?>
                        @if(count($unpaidInvoices))
                        <div style="margin-top: 15px;">
                            <div class="invoice_entry_header">
                                <table>
                                    <tr>
                                        <td width="100%">
                                            <h3 class="entry-header">Unpaid Balances Invoices</h3>
                                        </td>
                                    </tr>
                                </table>
                                <div class="clear-header"></div>
                            </div>
                            <table class="data invoice_entries" id="unpaid_balance_invoices">
                                <thead>
                                    <th style="text-align: center">Forward Invoice</th>
                                    <th>Invoice #</th>
                                    <th>Invoice Total</th>
                                    <th>Amount Paid</th>
                                    <th>Balance Due</th>
                                    <th>Due Date</th>
                                    <th>Line Total</th>
                                </thead>
                                <tbody>
                                    @php
                                        $selectedFwdInv = []; $totalFwdAmt = 0;
                                        if(count($findInvoice->forwardedInvoices)) {
                                            $selectedFwdInv = $findInvoice->forwardedInvoices->pluck("id")->toArray();
                                            $totalFwdAmt = $findInvoice->forwardedInvoices->sum('due_amount');
                                        }
                                    @endphp
                                    @forelse ($unpaidInvoices as $invkey => $invitem)
                                        <tr>
                                            <td style="text-align: center"><input type="checkbox" id="forwarded_invoices_check_{{ $invitem->id }}" class="forwarded-invoices-check" name="forwarded_invoices[]" data-token_id="{{$adjustment_token}}" value="{{ $invitem->id }}" data-due-amount="{{ $invitem->due_amount }}" @if(isset($findInvoice->forwardedInvoices) && in_array($invitem->id, $selectedFwdInv)) checked @endif></td>
                                            <td>{{ $invitem->invoice_id }}</td>
                                            <td>{{ $invitem->total_amount }}</td>
                                            <td>{{ $invitem->paid_amount }}</td>
                                            <td>{{ $invitem->due_amount }}</td>
                                            <td>{{ ($invitem->due_date) ? date("m/d/Y", strtotime($invitem->due_date)) : "" }}</td>
                                            <td style="text-align: right"><span id="unpaid_amt_{{$invitem->id}}">@if(isset($findInvoice->forwardedInvoices) && in_array($invitem->id, $selectedFwdInv)) {{ $invitem->due_amount }} @endif<span></td>
                                        </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" style="text-align: right;">{{ @$caseMaster->case_title }} balance forward:</td>
                                        <td><div class="locked" style="text-align: right;">
                                            $<span id="unpaid_invoice_total">@if(count($findInvoice->forwardedInvoices)) {{ number_format($totalFwdAmt, 2) }} @else 0.00 @endif</span>
                                        </div></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @endif

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
                                    $flatFeeAdjustment = $timeEntryAdjustment = $expenseEntryAdjustment = $forwardedInvoicesAdjustment = 0; 
                                    foreach($InvoiceAdjustment as $k=>$v){
                                        if($v->amount >= 0.01){
                                        switch ($v->applied_to) {
                                            case 'expenses':
                                                $expenseEntryAdjustment++;
                                                break;
                                            case 'balance_forward_total':                                                
                                                $forwardedInvoicesAdjustment++;
                                                break;
                                            case 'time_entries':
                                                $timeEntryAdjustment++;
                                                break;
                                            case 'flat_fees':
                                                $flatFeeAdjustment++;
                                                break;
                                            default:
                                                # code...
                                                break;
                                        }
                                    ?>

                                    <tr id="entry_{{$v->id}}" class="invoice_entry discount">
                                        <td style="vertical-align: center; text-align: center; border-right: none;"
                                            class="tdTimeExpense">
                                            <div class="invoice_entry_actions">
                                                <?php $action = ($v->invoice_id == '' && $v->invoice_id == null) ? 'deleteonly' : 'fulldelete'; ?>
                                                <a class="image_link_sprite image_link_sprite_cancel"
                                                    href="javascript:void(0);" onclick="openAdjustmentDelete({{$v->id}},'{{$action}}');"><i
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
                                                echo ($v->applied_to) ? $AppliedTo[$v->applied_to] : '';
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
                        <td> 
                            <!-- <span data-toggle="tooltip" data-placement="left" title="Remove Adjustment Entry">
                            <a onclick="removeAdjustmentEntry({{$v->id}},{{$v->amount}})" href="javascript:;"> &nbsp; <i class="fas fa-trash align-middle pr-2"></i></a>
                            </span> -->
                        </td>
                        </tr>
                        <?php } } ?>
                        <input type="hidden" value="{{$flatFeeAdjustment++}}" name="flatFeeAdjustment" id="flatFeeAdjustment">
                        <input type="hidden" value="{{$timeEntryAdjustment++}}" name="timeEntryAdjustment" id="timeEntryAdjustment">
                        <input type="hidden" value="{{$expenseEntryAdjustment++}}" name="expenseEntryAdjustment" id="expenseEntryAdjustment">
                        <input type="hidden" value="{{$forwardedInvoicesAdjustment++}}" name="forwardedInvoicesAdjustment" id="forwardedInvoicesAdjustment">
                        <input type="hidden" value="edit" name="invoice_type" id="invoice_type">
                        <input type="hidden" value="{{ $findInvoice->paid_amount }}" name="paid_amount" id="paid_amount">
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
                                            class="table_total amount discounts_section_total">{{$discount}}</span>)
                                    </div>
                                    <?php } ?>
                                    <?php if($addition!="0"){?>
                                    <div style="border: none; padding-top: 7px;" class="billing-additions-area ">
                                        $<span id="additions_section_total" class="table_total amount additions_section_total">{{$addition}}</span>
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
                                            <?php if(!$FlatFeeEntryForInvoice->isEmpty()){ ?>
                                            <div id="flat_fee_total_label" class="flat-fee-totals"
                                                style="border: none; padding-bottom: 7px; ">
                                                Flat Fee Sub-Total:
                                            </div>
                                            <?php } if($case_id !="none"){ ?>
                                            <div id="time_entry_total_label" class="time-entries-totals"
                                                style="border: none; padding-bottom: 7px;">
                                                Time Entry Sub-Total:
                                            </div>
                                            <div id="expense_total_label" style="border: none; padding-bottom: 7px;"
                                                class="expense-totals">
                                                Expense Sub-Total:
                                            </div>
                                            <?php } ?>
                                            <div id="sub_total_label" style="font-weight: bold; border: none;">
                                                Sub-Total:
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: right; width: 105px;">
                                        <div class="locked" style="padding-bottom: 15px;">
                                            <?php if(!$FlatFeeEntryForInvoice->isEmpty()){ ?>
                                            <div id="flat_fee_bottom_total" class="flat-fee-totals"
                                                style="border: none; padding-bottom: 7px; ">
                                                $<span id="flat_fee_total_amount" class="flat_fee_total_amount">{{number_format($flateFeeTotal,2)}}</span>
                                            </div>
                                            <?php } if($case_id!= "none"){ ?>
                                            <div style="border: none; padding-bottom: 7px;" class="time-entries-totals">
                                                $<span id="time_entry_total_amount"
                                                    class="time_entry_total_amount">{{number_format($timeEntryAmount,2)}}</span>
                                            </div>
                                            <div style="border: none; padding-bottom: 7px;" class="expense-totals">
                                                $<span id="expense_total_amount"
                                                    class="expense_total_amount">{{number_format($expenseAmount,2)}}</span>
                                            </div>
                                            <?php } ?>
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
                                            @if(count($unpaidInvoices))
                                            <div id="transfers_bottom_label" style="padding-top: 7px;">
                                                Balance Forward:
                                            </div>
                                            @endif
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
                                        <div class="locked" style="padding-bottom: 7px;">
                                            @if(count($unpaidInvoices))
                                            <div class="billing-additions-area-1"  style="padding-top: 7px;">
                                                $<span id="forwarded_total_amount">{{ number_format($totalFwdAmt, 2) }}</span>
                                            </div>
                                            @endif
                                            <?php if($discount!="0"){?>
                                            <div class="billing-discounts-area">
                                                ($<span id="discounts_section_total"
                                                    class="table_total amount discounts_section_total">{{$discount}}</span>)
                                            </div>
                                            <?php } ?>
                                            <?php if($addition!="0"){?>
                                            <div class="billing-additions-area">
                                                $<span id="additions_section_total"
                                                    class="table_total amount additions_section_total">{{$addition}}</span>
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
                                
                                <tr class="footer no-border totals " id="invoiceRestrict">
                                    <td colspan="4" style="text-align: right; font-weight: bold;">
                                    <div style="display: none;">
                                    <input type="text" value="" name="final_total_amount" id="final_total_amount">
                                    </div>
                                        <span id="2ErrorFinal_total_text"></span>
                                    </td>
                                </tr>
                                @error('final_total_text')
                                <tr class="footer no-border totals">
                                    <td colspan="4"  style="text-align: right;">
                                    <p class="error">{{ $message }}</p>
                                    </td>
                                </tr>
                                @enderror

                                <input type="hidden" value="{{$flateFeeTotal}}" name="flat_fee_sub_total_text" id="flat_fee_sub_total_text">
                                <input type="hidden" value="{{$timeEntryAmount}}" name="time_entry_sub_total_text" id="time_entry_sub_total_text">
                                <input type="hidden" value="{{$expenseAmount}}" name="expense_sub_total_text" id="expense_sub_total_text">
                                <input type="hidden" value="{{$nonBillableAmount}}" name="nonBillableAmount"  id="nonBillableAmount">
                                <input type="hidden" value="" name="sub_total_text" id="sub_total_text">
                                <input type="hidden" value="" name="total_text" id="total_text">
                                <input type="hidden" value="{{$discount}}" name="discount_total_text"
                                id="discount_total_text">
                                <input type="hidden" value="{{$addition}}" name="addition_total_text"
                                id="addition_total_text">
                                <input type="hidden" value="" name="final_total_text" id="final_total_text">
                                <input type="hidden" value="{{$adjustment_token}}" name="adjustment_token" id="adjustment_token">
                                <input type="hidden" value="{{ $totalFwdAmt ?? 0}}" name="forwarded_total_text" id="forwarded_total_text">
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

                    {{-- For Trust and Credit FUnds --}}
                    @if(!empty($invoiceSetting) && $case_id != "none")
                    <div class="apply-funds-container p-3" id="apply-trust-and-credit-funds">
                        <h3 class="section-header p-2 apply-trust-credit-funds">Apply Trust &amp; Credit Funds</h3>
                        <div class="mt-3">
                            @php
                                $caseClientIdArray = $caseMaster->caseAllClient->pluck("id")->toArray();
                                $appliedTrustFund = $findInvoice->applyTrustFund->whereIn("client_id", $caseClientIdArray);
                                $appliedCreditFund = $findInvoice->applyCreditFund->whereIn("client_id", $caseClientIdArray);
                            @endphp
                            <div class="mt-3">
                                <h4>Applied Trust Funds</h4>
                                <div class="row ">
                                    @if(!empty($appliedTrustFund) && count($appliedTrustFund) && ($appliedTrustFund->sum('applied_amount') + $appliedTrustFund->sum('allocate_applied_amount')))
                                    <div class="col-9">
                                        <table class="apply-trust-funds-table border-top border-bottom table table-md table-hover" style="table-layout: auto;">
                                            <thead>
                                                <tr>
                                                    <th class="apply-funds-client" style="cursor: initial;"><span>Client</span></th>
                                                    <th class="apply-funds-account" style="cursor: initial;"><span>Account</span></th>
                                                    <th class="apply-funds-available-amount" style="cursor: initial;"><span>Available Amount</span></th>
                                                    <th class="apply-funds-applied-amount" style="cursor: initial;"><span>Amount Applied</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($appliedTrustFund as $key => $item)
                                                    @if($item->applied_amount > 0)
                                                    <input type="hidden" name="trust[{{ $item->client_id }}][id]" value="{{ $item->id }}" >
                                                    <tr class="apply-funds-row">
                                                        <td class="apply-funds-client">
                                                            <span>{{ @$item->client->full_name }}</span></td>
                                                        <td class="apply-funds-account">
                                                            <div>Trust (Trust Account)</div>
                                                        </td>
                                                        <td class="apply-funds-available-amount">
                                                            <div>${{ @$item->userAdditionalInfo->unallocate_trust_balance }} <span class="allocation-status">(Unallocated)</span></div>
                                                        </td>
                                                        <td class="apply-funds-applied-amount"><span>${{ number_format($item->applied_amount, 2) }}</span></td>
                                                    </tr>
                                                    @endif
                                                    @if($item->allocate_applied_amount > 0)
                                                    <tr class="apply-funds-row">
                                                        <td class="apply-funds-client">
                                                            <input type="hidden" name="trust[{{ $item->client_id }}][id]" value="{{ $item->id }}" >
                                                            {{-- @if($item->applied_amount <= 0) --}}
                                                            <span>{{ @$item->client->full_name }}</span>
                                                            {{-- @endif --}}
                                                        </td>
                                                        <td class="apply-funds-account">
                                                            {{-- @if($item->applied_amount <= 0) --}}
                                                                Trust (Trust Account)
                                                            {{-- @endif --}}
                                                        </td>
                                                        <td class="apply-funds-available-amount">
                                                            @php
                                                                $allocateRow = $caseMaster->caseAllClient->where('id', $item->client_id)->first();
                                                            @endphp
                                                            <div>${{ number_format(@$allocateRow->pivot->allocated_trust_balance ?? 0, 2) }} <span class="allocation-status">(Allocated)</span></div>
                                                        </td>
                                                        <td class="apply-funds-applied-amount"><span>${{ number_format($item->allocate_applied_amount, 2) }}</span></td>
                                                    </tr>
                                                    @endif
                                                @empty
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                    <div class="col-3">
                                        <table class="trust-history-config border table table-md table-hover" style="table-layout: auto;">
                                            <thead>
                                                <tr>
                                                    <th class="jsx-3954552588 account-history-display-setting" style="cursor: initial;"><span>Show Trust Account History on Invoice</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($caseMaster->caseAllClient))
                                                    @php
                                                        $appliedTrustClient = $appliedTrustFund->pluck("show_trust_account_history", "client_id")->toArray();
                                                    @endphp
                                                    @forelse ($caseMaster->caseAllClient as $ckey => $citem)
                                                    @php
                                                        $tempinfo = $invoiceTempInfo->where('client_id', $citem->id)->where('account_type', 'trust')->where('trust_account_type', 'unallocate')->first();
                                                    @endphp
                                                    <tr class="account-history-config-row">
                                                        <td class="account-history-display-setting">
                                                            <input type="hidden" name="trust[{{ $citem->id }}][client_id]" value="{{ $citem->id }}" >
                                                            <div>{{ $citem->full_name }}</div>
                                                            <div class="row form-group">
                                                                <div class="col-12 col-sm-12">
                                                                    <select class="custom-select select2Dropdown trust-history-dd" name="trust[{{ $citem->id }}][show_trust_account_history]" data-client-id="{{ $citem->id }}" data-account-type="trust" data-token-id="{{$adjustment_token}}">
                                                                        @forelse (trustAccountHistoryList() as $skey => $sitem)    
                                                                        <option value="{{ $skey }}" {{ ((isset($appliedTrustClient) && array_key_exists($citem->id, $appliedTrustClient) && $skey == $appliedTrustClient[$citem->id])  || ($tempinfo && $tempinfo->show_account_history == $skey)) ? "selected" : "" }}>{{ $sitem }}</option>
                                                                        @empty
                                                                        @endforelse
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    @endforelse
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <h4>Applied Credit Funds</h4>
                                <div class="row">
                                    @if(!empty($appliedCreditFund) && count($appliedCreditFund) && $appliedCreditFund->sum('applied_amount'))
                                    <div class="col-9">
                                        <table class="apply-credit-funds-table border-top border-bottom table table-md table-hover" style="table-layout: auto;">
                                            <thead>
                                                <tr>
                                                    <th class="apply-funds-client" style="cursor: initial;"><span>Client</span></th>
                                                    <th class="apply-funds-account" style="cursor: initial;"><span>Account</span></th>
                                                    <th class="apply-funds-available-amount" style="cursor: initial;"><span>Available Amount</span></th>
                                                    <th class="apply-funds-applied-amount" style="cursor: initial;"><span>Amount Applied</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($appliedCreditFund as $key => $item)
                                                    <input type="hidden" name="credit[{{ $item->client_id }}][id]" value="{{ $item->id }}" >
                                                    @if($item->applied_amount > 0)
                                                    <tr class="apply-funds-row">
                                                        <td class="apply-funds-client">
                                                            <span>{{ @$item->client->full_name }}</span></td>
                                                        <td class="apply-funds-account">
                                                            <div>Credit Account</div>
                                                        </td>
                                                        <td class="apply-funds-available-amount">
                                                            <div>${{ number_format(@$item->userAdditionalInfo->credit_account_balance ?? 0, 2) }} <span class="allocation-status"></span></div>
                                                        </td>
                                                        <td class="apply-funds-applied-amount"><span>${{ number_format($item->applied_amount, 2) }}</span></td>
                                                    </tr>
                                                    @endif
                                                @empty
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                    <div class="col-3">
                                        <table class="credit-history-config border table table-md table-hover" style="table-layout: auto;">
                                            <thead>
                                                <tr>
                                                    <th class="account-history-display-setting" style="cursor: initial;"><span>Show Credit Account History on Invoice</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($caseMaster->caseAllClient))
                                                    @php
                                                        $appliedCreditClient = $appliedCreditFund->pluck("show_credit_account_history", "client_id")->toArray();
                                                    @endphp
                                                    @forelse ($caseMaster->caseAllClient as $ckey => $citem)
                                                    @php
                                                        $tempinfo = $invoiceTempInfo->where('client_id', $citem->id)->where('account_type', 'credit')->first();
                                                    @endphp
                                                    <tr class="account-history-config-row">
                                                        <td class="account-history-display-setting">
                                                            <input type="hidden" name="credit[{{ $citem->id }}][client_id]" value="{{ $citem->id }}" >
                                                            <div>{{ $citem->full_name }}</div>
                                                            <div class="row form-group">
                                                                <div class="col-12 col-sm-12">
                                                                    <select class="custom-select select2Dropdown trust-history-dd" name="credit[{{ $citem->id }}][show_credit_account_history]" data-client-id="{{ $citem->id }}" data-account-type="credit" data-token-id="{{$adjustment_token}}">
                                                                        @forelse (creditAccountHistoryList() as $skey => $sitem)    
                                                                        <option value="{{ $skey }}" {{ ((isset($appliedCreditClient) && array_key_exists($citem->id, $appliedCreditClient) && $skey == $appliedCreditClient[$citem->id]) || ($tempinfo && $tempinfo->show_account_history == $skey)) ? "selected" : "" }}>{{ $sitem }}</option>
                                                                        @empty
                                                                        @endforelse
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    @endforelse
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <?php if($case_id !="none"){ ?>
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
                    <div style="display:none;" class="error" id="invoice_payment_plan_error">
                        Payment plans must add up to the same total as the invoice.<br>
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
                                                            <p class="autopay-field m-0" data-testid="autopay-field" style="padding-left: 20px;">
                                                            <?php if($value->status=="paid"){?>
                                                                <i class="fas fa-check-circle" style="color: #40BC53"></i>&nbsp;&nbsp;Manual payment successful
                                                            <?php } ?>
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
                                                                        <input id="start_date" name="start_date" class="form-control datepicker" value="{{ convertUTCToUserTimeZone('dateOnly') }}">
                                                                        <div class="d-flex invalid-feedback start_date_error"></div>
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
                                                                        <div class="d-flex invalid-feedback amount_per_installment_field_error"></div>
                                                                    </div>
                                                                    <div class="col-md-3"><label class="pr-0 ">Per payment</label></div>
                                                                </div>
                                                                <div class="row form-group">
                                                                    <div class="pr-1 col-md-3 offset-md-3">
                                                                        <div class="input-group">
                                                                            <input id="number_installment_field"
                                                                                data-testid="number-installment-field"  min="2" type="number"
                                                                                class="form-control" value="" name="number_installment_field" onkeydown="if(event.key==='.'){event.preventDefault();}"  oninput="event.target.value = event.target.value.replace(/[^0-9]*/g,'');"></div>
                                                                        <div class="d-flex invalid-feedback number_installment_field_error"></div>
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
                                                                                    <div class="d-flex invalid-feedback first_payment_amount_error"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row form-group">
                                                                    <div class="col-md-12">
                                                                        <button type="button" id="installmentBreak" name="installmentBreak" class="submitbutton btn btn-outline-secondary btn-rounded m-1" style="width: 40%;"><strong>Recalculate</strong></button>
                                                                    </div>
                                                                    <div class="d-flex invalid-feedback installmentBreak_error"></div>
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
                                                                    <div>If "Online Payments" is enabled, your client will receive a the default payment reminders, each with a payment link.</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pl-2 text-right">
                                                <a data-toggle="modal" data-target="#loadAddFeedBack" data-placement="bottom" href="javascript::void(0);">
                                                    <button onclick="setFeedBackForm('single','Payment Plans');" type="button" class="feedback-button mr-2 text-black-50 btn btn-link">Tell us what you think</button>
                                                </a>
                                            </div>
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
                    <?php } ?>
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
                                        <td id="sharing_left_side" class="sharing-left-side" style="width: 75%; vertical-align: top;padding-top: 13px;">
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
                                                    $CompanyList = [];
                                                    $CompanyArray = [];
                                                    // print_r($getAllClientForSharing);
                                                    foreach($getAllClientForSharing as $k=>$v){
                                                        if($v->user_level == 4)
                                                        {
                                                            $CompanyList[$v->id] = $v->first_name; 
                                                            $CompanyArray[$v->id] = []; 
                                                        }
                                                    }
                                                    foreach($getAllClientForSharing as $k=>$v){
                                                        if($v->user_level=="2")
                                                        {
                                                            $multipleCompnays = explode(",",$v->multiple_compnay_id); 
                                                            foreach($multipleCompnays as $kv => $vv){                                                                  
                                                                if (array_key_exists($vv, $CompanyList)){
                                                                    $CompanyArray[$vv] = array($v->id => $v->unm.'|'.$v->email.'|'.$v->client_portal_enable.'|'.$v->last_login); 
                                                                }
                                                            }                                                            
                                                        }
                                                    }                                                    
                                                    // print_r($CompanyArray);
                                                    ?>

                                                    <?php foreach($getAllClientForSharing as $k=>$v){ ?>
                                                        <?php if($v->user_level=="4"){ ?> 
                                                            <tr class="invoice-sharing-row client-id-21672788" id="row_{{$v->user_id}}">                                                            
                                                                <td colspan="<3">
                                                                <div class="locked" style="font-weight: bold;">
                                                                    {{ucfirst($v->unm)}} (Company)
                                                                </div>
                                                                </td>
                                                            </tr>                                                             
                                                            <?php
                                                             if(!empty($CompanyArray[$v->id])){ 
                                                                    foreach($CompanyArray[$v->id] as $kk =>$vv ){
                                                                    $explodeValues = explode("|", $vv);
                                                            ?>
                                                                <tr class="invoice-sharing-row client-id-21672788" id="row_{{$kk}}">
                                                                    <td style="text-align: center;padding:10px;">
                                                                        <div class="locked">
                                                                            <input type="checkbox" name="portalAccess[]"  value="{{$kk}}"  id="portalAccess_{{$kk}}" class="invoiceSharingBox invoice-sharing-box"  uid="{{$kk}}"  em="{{$explodeValues[1]}}" pe="{{$explodeValues[2]}}" onclick="checkPortalAccess({{$kk}})" <?php if(in_array($kk,$SharedInvoice)) { echo "checked=checked"; } ?>>
                                                                        </div>                                                            
                                                                    </td>
                                                                    <td class="invoice-sharing-name">
                                                                        <div class="locked pl-1">
                                                                            {{ucfirst($explodeValues[0])}}  (Client)
                                                                        </div>
                                                                    </td>
                                                                    <td class="invoice-sharing-last-login">
                                                                        <div class="locked last-login-at pl-1">
                                                                            <?php if($explodeValues[1]==""){
                                                                                echo "Disabled";
                                                                            }else{
                                                                                $CommonController= new App\Http\Controllers\CommonController();
                                                                                if($explodeValues[3]!=NULL){
                                                                                    $loginDate=$CommonController->convertUTCToUserTime($explodeValues[3],Auth::User()->user_timezone);
                                                                                    echo date('F jS Y, h:i:s A',strtotime($loginDate));
                                                                                }else{
                                                                                    echo "Never";
                                                                                }
                                                                            }
                                                                            ?></div>
                                                                    </td>   
                                                                </tr>
                                                            <?php } } else {?>
                                                                <tr>
                                                                <td style="border-right: none;">&nbsp;</td>
                                                                <td style="border-left: none;">
                                                                    <div class="locked" style="font-style: italic; color: gray;">
                                                                    No contacts from this company are linked to this case.
                                                                    </div>
                                                                </td>
                                                                </tr>
                                                                <?php } ?>        
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <tr>
                                                        <td colspan="3" class="invoice_sharing_break">&nbsp;</td>
                                                    </tr>
                                                    <?php foreach($getAllClientForSharing as $k=>$v){
                                                        $show = 0;
                                                        foreach($CompanyArray as $innerKey => $innerValue){
                                                            if (array_key_exists($v->id,$innerValue)){
                                                                $show = 1;
                                                            }                                                  
                                                        }      
                                                        if($show == 0 && $v->user_level=="2"){ ?>    
                                                        <tr class="invoice-sharing-row client-id-21672788" id="row_{{$v->user_id}}">
                                                        <td style="text-align: center;padding:10px;">
                                                            <div class="locked">   
                                                                <input type="checkbox" name="portalAccess[]"  value="{{$v->user_id}}"  id="portalAccess_{{$v->user_id}}" class="invoiceSharingBox invoice-sharing-box"  uid="{{$v->user_id}}"  em="{{$v->email}}" pe="{{$v->client_portal_enable}}" onclick="checkPortalAccess({{$v->user_id}})" <?php if(in_array($v->user_id,$SharedInvoice)) { echo "checked=checked"; } ?>>
                                                            </div>                                                            
                                                        </td>
                                                        <td class="invoice-sharing-name">
                                                            <div class="locked pl-1">
                                                                {{ucfirst($v->unm)}}  (Client)
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
                                                    <?php }  } ?>
                                                </tbody>
                                            </table>
                                            <div class="reminder-tip text-right">*Once shared, you will have the option of sending reminders to clients.</div>
                                        </td>
                                        @if(empty(getFirmOnlinePaymentSetting()) || getFirmOnlinePaymentSetting()->is_accept_online_payment == "no")
                                        <td id="sharing_right_side" style="width: 25%; padding-left: 10px; height: 100%;padding-top:10px;">
                    
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
                                        @endif
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
                            <!-- <a data-toggle="modal"  data-target="#cancelEdit" data-placement="bottom" href="javascript:;"   title="Delete" data-testid="delete-button" class="btn btn-secondary btn-rounded  m-1" >Cancel</a> -->
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
                                    <a href="/bills/invoices/view/{{base64_encode($findInvoice->id)}}" class="btn btn-primary ladda-button example-button m-1">Yes</a>
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
<div id="removeAlllExistingFlatFeeEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="removeAlllExistingFlatFeeEntryForm" id="removeAlllExistingFlatFeeEntryForm"
            name="removeAlllExistingFlatFeeEntryForm" method="POST">
            @csrf
            <input type="hidden" value="{{base64_encode(@$caseMaster['id'])}}" name="case_id" id="delete_flart_fee_entry_id">
            <input type="hidden" value="{{$adjustment_token}}" name="token_id" id="token_id">
            <input type="hidden" value="{{$findInvoice->id}}" name="invoice_id" id="invoice_id">
            <input type="hidden" value="{{$flateFeeTotal}}" name="total" id="total">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Remove All Flat Fees</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                        <div><p>Would you like to <strong>remove</strong> all Flat Fees from this invoice?</p><p><strong>Note:</strong> Only Case Billing Flat Fees (if one exists) will persist in {{config('app.name')}}.</p></div>
                       
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
            <input type="hidden" value="{{$findInvoice->id}}" name="invoice_id" id="invoice_id">
            <input type="hidden" value="{{$timeEntryAmount}}" name="total" id="total">
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
            <input type="hidden" value="{{$adjustment_token}}" name="token_id" id="token_id">
            <input type="hidden" value="{{$findInvoice->id}}" name="invoice_id" id="invoice_id">
            <input type="hidden" value="{{$expenseAmount}}" name="total" id="total">
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
    i.invoice-banner-forwarded {
        background-image: url('{{ asset("images/invoice_banner_forwarded.png") }}');
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
        background-image: url('{{BASE_URL}}images/invoice_banner_draft.png');
        height: 127px;
        width: 127px;
        display: block;
    }

    i.invoice-banner-sent {
        background-image: url('{{BASE_URL}}images/invoice_banner_sent.png');
        height: 127px;
        width: 127px;
        display: block;
    }

    i.invoice-banner-unsent {
        background-image: url('{{BASE_URL}}images/invoice_banner_unsent.png');
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
    #addNewAdjustmentEntryArea .saveAdjustmentForm .select2-container{
        width: 100% !important;
    }
</style>

@section('page-js-inner')
<script src="{{ asset('assets\js\custom\invoice\addinvoice.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script src="{{ asset('assets\js\custom\feedback.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {

        $('[data-toggle="tooltip"]').tooltip({
            trigger : 'hover'
        });
        
        $('[data-toggle="tooltip"]').on('click', function () {
            $(this).tooltip('hide')
        });
        
        
       
        // $("#time_entry_sub_total_text").val({{$timeEntryAmount}});
        // $("#expense_sub_total_text").val({{$expenseAmount}});
        // $("#discount_total_text").val({{$discount}});
        // $("#addition_total_text").val({{$addition}});

        recalculate();
        $("#contact").select2({
            theme: "classic",
            allowClear: true,
            placeholder: "Select...",
        });
        /* $('.datepicker').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            startDate: "dateToday",
            'todayHighlight': true
        }).on("changeDate", function() {
            $("#bill_payment_terms").val("0");
        }); */
        $('[data-toggle="tooltip"]').tooltip();

        // $('.input-daterange').datepicker({
        //     format : 'm/d/yyyy',
        //     clearBtn: true,
        //     keyboardNavigation: false,
        //     forceParse: false,
        //     todayBtn: "linked",
        //     todayHighlight : true
        // });  

        $('#bill_from_date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': false,
            'clearBtn': false,
            'todayHighlight': true
        }).on('changeDate', function (selected) {
            var todayDate = new Date();
            var maxDate = new Date(selected.date.valueOf());
            $('#bill_to_date').val();
            $('#bill_to_date').datepicker('setStartDate', maxDate);
        });

        $('#bill_to_date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': false,
            'clearBtn': false,
            'todayHighlight': true
        }).on('change',function(selected){
            if($(this).val()){
                swal({
                    title: 'warning',
                    text: "Are you sure you want to proceed?<br>Any changes you have made to the invoice entries below will be lost.",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0CC27E',
                    cancelButtonColor: '#FF586B',
                    cancelButtonText: 'Close',
                    confirmButtonText: 'Proceed',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonClass: 'btn btn-danger  mr-2',
                    buttonsStyling: false,
                    reverseButtons: true
                }).then(function(isConfirm){
                    if (isConfirm){     
                        localStorage.setItem('forwarded_invoices', JSON.stringify([]));               
                        $('#adjustment_delete').val('1');
                        changeCase();
                    }
                });
            }
        });

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

        <?php if($findInvoice->payment_plan_enabled=="yes"){?>
            $("#payment_plan").prop('checked',true);
            $("#payment_plan_details").slideToggle();
        <?php }else{ ?>
            if(localStorage.getItem('adjustment_token') == "{{ $adjustment_token }}" &&  localStorage.getItem('payment_plan_checked') == 1){ 
                $("#payment_plan").prop('checked',true);
                $("#payment_plan_details").slideToggle();
                $("#number_installment_field").val(localStorage.getItem('number_installment_field'));
                $("#amount_per_installment_field").val(localStorage.getItem('amount_per_installment_field'));
                $("#installment_frequency_field").val(localStorage.getItem('installment_frequency_field'));
                if(localStorage.getItem("with_first_payment") == '1'){
                    $("#with_first_payment").trigger('click');
                    $("#with_first_payment").prop("checked",true);
                    $("#first_payment_amount").prop("disabled",false);
                    $("#first_payment_amount").val(localStorage.getItem('first_payment_amount'));
                } 
                $("#start_date").val(localStorage.getItem('start_date'));
                setTimeout(() => {
                    $("#installmentBreak").trigger('click');    
                }, 2000);   
            }else{
                $("#payment_plan").prop('checked',false);
                $("#first_payment_amount").prop("disabled",true);
                $("#with_first_payment").prop("checked",false);
            }
            // $("#first_payment_amount").prop("disabled",true);
            // $("#with_first_payment").prop("checked",false);
        <?php } ?>

        $('.invoice_entry_nonbillable_time').change(function () { //".checkbox" change 
            var id = $(this).attr('id');
            var val = $(this).val;
            var sum = 0;
            var primaryid = $(this).data('primaryid');
            if (!$(this).is(":checked")) {
                $(this).parent().prev().css('text-decoration', '');
                $(this).parent().prev().prev().css('text-decoration', '');
                $(this).parent().prev().prev().prev().css('text-decoration', '');
                $(".timeentry_amount_"+primaryid).removeClass("strike");
            } else {
                $(".timeentry_amount_"+primaryid).addClass("strike");
                $(this).parent().prev().css('text-decoration', 'line-through');
                $(this).parent().prev().prev().css('text-decoration', 'line-through');
                $(this).parent().prev().prev().prev().css('text-decoration','line-through');
            }
            $('input[name="linked_staff_checked_share[]"]').each(function (i) {
                if (!$(this).is(":checked")) {
                    // do something if the checkbox is NOT checked
                    var g = parseFloat($(this).attr("priceattr"));
                    sum += g;
                }
            });
            $(".time_entry_table_total").html(sum);
            $("#time_entry_sub_total_text").val(sum);
            $('.time_entry_table_total').number(true, 2);

            $(".time_entry_total_amount").html(sum);
            $('.time_entry_total_amount').number(true, 2);



            recalculate();

        });

        $('.invoice_entry_nonbillable_flat').change(function () { //".checkbox" change 
            var id = $(this).attr('id');
            var val = $(this).val;
            var sum = 0;
            var primaryid = $(this).data('primaryid');
            if (!$(this).is(":checked")) {
                $(this).parent().prev().css('text-decoration', '');
                $(this).parent().prev().prev().css('text-decoration', '');
                $(this).parent().prev().prev().prev().css('text-decoration', '');
                $(".flat_amount_"+primaryid).removeClass("strike");
            } else {
                $(".flat_amount_"+primaryid).addClass("strike");
                $(this).parent().prev().css('text-decoration', 'line-through');
                $(this).parent().prev().prev().css('text-decoration', 'line-through');
                $(this).parent().prev().prev().prev().css('text-decoration','line-through');
            }
            $('input[name="flat_fee_entry[]"]').each(function (i) {
                if (!$(this).is(":checked")) {
                    // do something if the checkbox is NOT checked
                    var g = parseFloat($(this).attr("priceattr"));
                    sum += g;
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
            installmentCalculation();
        });
        $('.invoice_expense_entry_nonbillable_time').change(function () { //".checkbox" change 
            var id = $(this).attr('id');
            var val = $(this).val;
            var sum = 0;
            var primaryid = $(this).data('primaryid');
            if (!$(this).is(":checked")) {
                $(this).parent().prev().css('text-decoration', '');
                $(this).parent().prev().prev().css('text-decoration', '');
                $(this).parent().prev().prev().prev().css('text-decoration', '');
                $(".expenseentry_amount_"+primaryid).removeClass("strike");
            } else {
                $(".expenseentry_amount_"+primaryid).addClass("strike");
                $(this).parent().prev().css('text-decoration', 'line-through');
                $(this).parent().prev().prev().css('text-decoration', 'line-through');
                $(this).parent().prev().prev().prev().css('text-decoration','line-through');
            }
            $('input[name="invoice_expense_entry_nonbillable_time[]"]').each(function (i) {
                if (!$(this).is(":checked")) {
                    // do something if the checkbox is NOT checked
                    var g = parseFloat($(this).attr("priceattr"));
                    sum += g;
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
            if($(this).is(":checked")) {
                localStorage.setItem('payment_plan_checked', 1);  
            }else{
                localStorage.setItem('payment_plan_checked', 0);
                localStorage.setItem('number_installment_field','');
                localStorage.setItem('amount_per_installment_field','');
                localStorage.setItem('installment_frequency_field','');
                localStorage.setItem("with_first_payment",'0');
                localStorage.setItem('first_payment_amount','');
                localStorage.setItem('start_date','');
            }            
            localStorage.setItem('adjustment_token', "{{ $adjustment_token }}");
            $("#payment_plan_details").slideToggle();
        });
        $("input:checkbox#with_first_payment").click(function () {
            if ($(this).is(":checked")) {
                $("#first_payment_amount").removeAttr("disabled");
            } else {
                $("#first_payment_amount").prop("disabled",true);    
                $("#first_payment_amount").val("");
            }
        });

        $("#amount_per_installment_field").blur(function(){
            var currentAmount=$(this).val().replace(/,/g, '');
            var totalAmount= parseFloat($("#final_total_text").val());
            var firstInstallment= parseFloat($("#first_payment_amount").val().replace(/,/g, ''));
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
            var installmentNumber=Math.ceil($(this).val());
            var totalAmount= parseFloat($("#final_total_text").val());
            var totalInstalment=totalAmount/installmentNumber;
            $("#amount_per_installment_field").val(totalInstalment.toFixed(2));
        }); 

        $("#first_payment_amount").blur(function(){
            var totalAmount= parseFloat($("#final_total_text").val().replace(/,/g, ''));
            var firstInstallment= parseFloat($("#first_payment_amount").val().replace(/,/g, ''));
            var amount_per_installment_field= parseFloat($("#amount_per_installment_field").val().replace(/,/g, ''));
            var debitedAmount=totalAmount-firstInstallment;
            var totalInstalment=debitedAmount/amount_per_installment_field;
            if(totalInstalment < 0) {
                totalInstalment = 1;
            }else{
                totalInstalment = Math.ceil(totalInstalment) + 1
            }
            $("#number_installment_field").val(totalInstalment);

        });
        $("#SaveInvoiceButton").on("click",function(){
            $(this).prop("disabled",true);
            $("#innerLoader").show();
        });
        $("#saveInvoiceForm").validate({
            ignore: [],
            rules: {
                contact: {
                    required: true
                },
                court_case_id: {
                    required: true
                },
                final_total_amount: {
                    min : {{ $findInvoice->paid_amount ?? 0 }}
                }
            },
            messages: {
                contact: {
                    required: "Billing user can't be blank"
                },
                court_case_id: {
                    required: "Please select a client"
                },
                final_total_amount: {
                    min: "You cannot lower the amount of this invoice below ${{ $findInvoice->paid_amount }} </br> because payments have already been received for that amount."
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#contact')) {
                    error.appendTo('#1Error');
                } else if (element.is('#court_case_id')) {
                    error.appendTo('#2Error');
                } else if (element.is('#final_total_amount')) {
                    error.appendTo('#2ErrorFinal_total_text');
                    $('html, body').animate({scrollTop:($('#2ErrorFinal_total_text').offset().top - 250)}, 'slow');
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
                var alert = 0;
                var nonBillableAmount = {{$nonBillableAmount}};
                <?php if($case_id == "none"){ ?>
                    var flat_fee_sub_total_text = ($(".flat_fee_total_amount").html() != undefined) ? $(".flat_fee_total_amount").html().replace(/,/g, '') : 0.00;
                    var discount_amount = ($(".discounts_section_total").html() != undefined) ? $(".discounts_section_total").html().replace(/,/g, '') : 0.00;
                    var addition_amount = ($(".additions_section_total").html() != undefined) ? $(".additions_section_total").html().replace(/,/g, '') : 0.00;        
                    if(flat_fee_sub_total_text > 0) {
                        alert++;
                    }
                    if(discount_amount > 0) {
                        alert++;
                    }
                    if(addition_amount > 0) {
                        alert++;
                    }                    
                    if($("#final_total_text").val() == 0 && alert == 0){
                        swal("","You are attempting to save a blank invoice, please edit the invoice to add an activity (such as time entries or expenses) or delete the invoice.",'error');
                        alert++;
                        afterLoader();
                        return false;
                    }else{
                        afterLoader();
                        return true;
                    }   
                <?php }else{ ?>
                    if($("#final_total_text").val() == 0 && nonBillableAmount == 0){
                        swal("","You are attempting to save a blank invoice, please edit the invoice to add an activity (such as time entries or expenses) or delete the invoice.",'error');
                        alert++;
                        afterLoader();
                        return false;
                    }else{
                        afterLoader();
                        return true;
                    }
                <?php } ?>
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
            for(var loopVar=1;loopVar<=Math.ceil(number_installment_field);loopVar++){
               
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
                    firstInstallment=$("#first_payment_amount").val().replace(/,/g, '');
                    countSum+=parseFloat(firstInstallment);
                }else{
                    firstInstallment=amount_per_installment_field;
                    if(loopVar==Math.ceil(number_installment_field)){
                        totalAMT=parseFloat($("#final_total_text").val().replace(/,/g, ''));
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
                var PaymentPlanExits = "{{ count($InvoiceInstallment) }}";
                if(PaymentPlanExits > 0){
                    var payment_plan_balance = $("#payment_plan_balance").text();
                    if(payment_plan_balance == 0){
                        $("#invoice_payment_plan_error").hide();
                        $("#saveInvoiceForm").submit();
                    }else{
                        $("#invoice_payment_plan_error").show();
                        afterLoader();
                        return false;
                    }
                }else{
                    var payment_plan_balance = $("#payment_plan_balance").text();
                    if(payment_plan_balance == 0 || $("#payment_plan").is(":checked") === false){
                        $("#invoice_payment_plan_error").hide();
                        $("#saveInvoiceForm").submit();
                    }else{
                        $("#invoice_payment_plan_error").show();
                        afterLoader();
                        return false;
                    }
                }
            }else{
                var PaymentPlanExits = "{{ count($InvoiceInstallment) }}";
                if(PaymentPlanExits > 0){
                    $("#payment_plan_existing_dialog_bbox").modal("show");
                }else{
                    calculatePaymentPlansForm(x);
                }
            }
        });
        
        // $('input[name="client_portal_enable"]').click(function () {
        //     if ($("#client_portal_enable").prop('checked') == true) {
        //             $("#confirmAccessModal").modal("show");
        //     }
            
        // });
        $('#confirmAccessModal').on('hidden.bs.modal', function () {
            $("#client_portal_enable").prop('checked',false);
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

    var arr = arrShare = {}; 
    $('#removeAlllExistingFlatFeeEntryForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();
        if (!$('#removeAlllExistingFlatFeeEntryForm').valid()) {
            beforeLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#removeAlllExistingFlatFeeEntryForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/deleteAllFlatFeeEntry", // json datasource
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

    if(localStorage.getItem("shared_client")){        
        var jsObj = JSON.parse(localStorage.getItem("shared_client"));
        console.log(jsObj);
        $.each(jsObj, function($key, $val){
            console.log($key +" exists");
            $("#portalAccess_"+$key).prop('checked', true);
            arrShare[$key] = 'checked';
        });
        $(".invoiceSharingBox").trigger('change');
    }
   
    /* function paymentTerm(){
        
        var setDate='';
        var selectdValue = $("#bill_payment_terms option:selected").val();
        var bill_invoice_date=$("#bill_invoice_date").val();
        if(selectdValue==0 || selectdValue==1){
            var minDate =  $('#bill_invoice_date').datepicker('getDate');
            $('#bill_due_date').datepicker("setDate", minDate);
        }else if(selectdValue==2){
            // CheckIn = $("#bill_invoice_date").datepicker('getDate');
            CheckOut = moment().add(15, 'day').toDate();
            $('#bill_due_date').datepicker('update', CheckOut).focus();
           
        }else if(selectdValue==3){
            // CheckIn = $("#bill_invoice_date").datepicker('getDate');
            CheckOut = moment().add(30, 'day').toDate();
            $('#bill_due_date').datepicker('update', CheckOut).focus();
           
        }else{
            // CheckIn = $("#bill_invoice_date").datepicker('getDate');
            CheckOut = moment().add(60, 'day').toDate();
            $('#bill_due_date').datepicker('update', CheckOut).focus();
        }

        if(selectdValue==""){
            $("#automated_reminders").prop("checked",false);
            $('#bill_due_date').val('');
        }else{
            $("#automated_reminders").prop("checked",true);
        }
     
    } */
    function checkPortalAccess(id){
        if ($("#portalAccess_"+id).prop('checked') == true){
            arrShare[id] = 'checked';
        } else {
            delete arrShare[id];
        }
        localStorage.setItem('shared_client', JSON.stringify(arrShare));
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
        var URLS=baseUrl+'/bills/invoices/load_new?contact='+currentclient;
        window.location.href=URLS;
        return true;

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
        var bill_from_date=$("#bill_from_date").val();
        var bill_to_date=$("#bill_to_date").val();
        var adjustment_delete=$('#adjustment_delete').val();
        var URLS=baseUrl+'/bills/invoices/{{base64_encode($findInvoice->id)}}/edit?token={{base64_encode($findInvoice->id)}}';
        if(bill_from_date != '' && bill_to_date != ''){
            URLS+='&bill_from_date='+bill_from_date+'&bill_to_date='+bill_to_date;
        }
        if(adjustment_delete != ''){
            URLS+='&adjustment_delete='+adjustment_delete;
        }
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
    
    function forwardedInvoicesCalculate(){
        var lineTotal = 0.00;
        $(".forwarded-invoices-check").each(function(ind, item) {
            if(localStorage.getItem("forwarded_invoices")){
                var jsObj = JSON.parse(localStorage.getItem("forwarded_invoices"));
                console.log(jsObj);
                if ($(this).val() in jsObj) {
                    console.log($(this).val() +" exists");
                    $("#forwarded_invoices_check_"+$(this).val()).prop('checked', true);
                    arr[$(this).val()] = 'checked';
                }else{
                    delete arr[$(this).val()];
                }
            }
            if($(this).is(":checked")) {
                dueAmt = $(this).attr("data-due-amount");
                $("#unpaid_amt_"+$(this).val()).text(dueAmt);
                lineTotal += parseFloat(dueAmt);
            }else{
                $("#unpaid_amt_"+$(this).val()).text("");
            }
        });
        $("#unpaid_invoice_total").text(lineTotal.toFixed(2));
        $("#forwarded_total_amount").text(lineTotal.toFixed(2));
        $("#forwarded_total_text").val(lineTotal.toFixed(2));
    }
    console.log("localStorage > forwarded_invoices > "+ localStorage.getItem("forwarded_invoices"));
    
    function recalculate() {
        // $(".forwarded-invoices-check").trigger("change");
        forwardedInvoicesCalculate();
        var total =  subtotal = 0;
        var expense_total_amount = ($(".expense_total_amount").html() != undefined) ? $(".expense_total_amount").html().replace(/,/g, '') : 0.00;
        var time_entry_total_amount = ($(".time_entry_total_amount").html() != undefined) ? $(".time_entry_total_amount").html().replace(/,/g, '') : 0.00;
        var flat_fee_sub_total_text = ($(".flat_fee_total_amount").html() != undefined) ? $(".flat_fee_total_amount").html().replace(/,/g, '') : 0.00;
        console.log("time_entry_total_amount = " + time_entry_total_amount);
        console.log("expense_total_amount = " + expense_total_amount);
        console.log("flat_fee_sub_total_text = " + flat_fee_sub_total_text);
        subtotal = parseFloat(expense_total_amount) + parseFloat(time_entry_total_amount) + parseFloat(flat_fee_sub_total_text);
        
        var discount_amount = ($(".discounts_section_total").html() != undefined) ? $(".discounts_section_total").html().replace(/,/g, '') : 0.00;
        var addition_amount = ($(".additions_section_total").html() != undefined) ? $(".additions_section_total").html().replace(/,/g, '') : 0.00;      
        var forwarded_amount = ($("#forwarded_total_amount").html() != undefined) ? $("#forwarded_total_amount").html().replace(/,/g, '') : 0.00;
        console.log("forwarded_amount = " + forwarded_amount);
        console.log("discount_amount = " + discount_amount);
        console.log("addition_amount = " + addition_amount);  

        total = parseFloat(subtotal) + parseFloat(forwarded_amount) + parseFloat(addition_amount);    
        var final_total=total-discount_amount ;
        if(final_total <= 0 ){
            final_total=0;
        }
        console.log("final_total = " + final_total);  
        console.log("paid_amount = " + $("#paid_amount").val());
        
        $(".sub_total_amount").html(subtotal);
        $("#sub_total_text").val(subtotal);
        $('.sub_total_amount').number(true, 2);

        $(".invoice_total").html(total);
        $("#total_text").val(total);
        $('.invoice_total').number(true, 2);
        
        $("#final_total_text").val(final_total);
        $("#final_total_amount").val(final_total);        
        $(".final_total").html(final_total);
        $('.final_total').number(true, 2);

        var PaymentPlanExits = "{{ count($InvoiceInstallment) }}";
        if(PaymentPlanExits == 0){
            $("#payment_plan_balance").html(final_total);
            $('#payment_plan_balance').number(true, 2);
        }

        if($("#payment_plan").is(":checked") === true){
            var sumR=0;
            $('.edit_payment_plan_amount').each(function (i) {
                var gg =$(this).val();
                sumR +=parseFloat(gg);
            });
            console.log("3678 >"+ sumR.toFixed(2) +" == "+ final_total.toFixed(2));
            if(sumR.toFixed(2) == final_total.toFixed(2)){
                $("#payment_plan_balance").html('0.00');
                $('#payment_plan_balance').number(true, 2); 
            }else{
                $("#payment_plan_balance").html(final_total);
                $('#payment_plan_balance').number(true, 2); 
            }
            
        }        
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
        $(".invoiceSharingBox").trigger('change'); 
        // $.ajax({
        //     type: "POST",
        //     url: baseUrl + "/bills/invoices/reloadRow",
        //     data: {"id": id},
        //     success: function (res) {
        //         if (typeof (res.errors) != "undefined" && res.errors !== null) {
        //             $('.showError').html('');
        //             var errotHtml =
        //                 '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
        //             $('.showError').append(errotHtml);
        //             $('.showError').show();
        //             afterLoader();
        //             $("#preloader").hide();
        //             return false;
        //         } else {
        //             afterLoader()
        //             $("#row_"+id).html(res);
        //             $("#preloader").hide();
        //             return true;
        //         }
        //     },
        //     error: function (xhr, status, error) {
        //         $('.showError').html('');
        //         var errotHtml =
        //             '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
        //         $('.showError').append(errotHtml);
        //         $('.showError').show();
        //         $('#editAdjustmentEntry').animate({
        //             scrollTop: 0
        //         }, 'slow');
        //         afterLoader();
        //     }
        // })
    }
    // $('input[name=client_portal_enable]').attr("checked",false);

    setTimeout(function(){
        var PaymentPlanExits = "{{ count($InvoiceInstallment) }}";
        if(PaymentPlanExits == 0){
            $("#payment_plan_balance").html($(".final_total").html());
            $('#payment_plan_balance').number(true, 2); 
        }  
        
        if($("#payment_plan").is(":checked") === false){
            $("#payment_plan_balance").html($(".final_total").html());
            $('#payment_plan_balance').number(true, 2); 
        }
    }, 1000);

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
    // $("#number_installment_field").on("change", function(){
    //     calculatePaymentPlansForm();
    // });

    $('#number_installment_field').keypress(function (e) {
        var key = e.which;
        if(key == 13)  // the enter key code
        {
            calculatePaymentPlansForm(); 
            return false;
        }
    });

    $('#amount_per_installment_field').keypress(function (e) {
        var key = e.which;
        if(key == 13)  // the enter key code
        {
            calculatePaymentPlansForm(); 
            return false;
        }
    });

    $('#first_payment_amount').keypress(function (e) {
        var key = e.which;
        if(key == 13)  // the enter key code
        {
            calculatePaymentPlansForm(); 
            return false;
        }
    });

    function calculatePaymentPlansForm(x){
        var error = 0;
        if($("#amount_per_installment_field").val() == ''){
            $(".amount_per_installment_field_error").html("Amount is required");
            error = 1;
        }else{
            error = 0;
            $(".amount_per_installment_field_error").html("");
        }
        if($("#number_installment_field").val() == ''){
            $(".number_installment_field_error").html("Number is required");
            error = 1;
        }else{
            error = 0;
            $(".number_installment_field_error").html("");
        }
        if ($("#with_first_payment").is(":checked")) {
            if($("#first_payment_amount").val() == ''){
                $(".first_payment_amount_error").html("Amount is required");
                error = 1;
            }else{
                if($("#first_payment_amount").val() != '' && $("#first_payment_amount").val().replace(/,/g, '') >= $("#final_total").html()){
                    $(".first_payment_amount_error").html("Amount exceeds max payment amount (max: $"+$("#final_total").html()+")");
                    if($("#number_installment_field").val() <= '2'){
                        $(".number_installment_field_error").html("Number must be at least 2");
                        error = 1;
                    }
                    error = 1;
                }else{
                    error = 0;
                    $(".first_payment_amount_error").html("");
                }
            }
        }else{
            error = 0;
            $(".first_payment_amount_error").html("");
        }
        console.log(error);
        if(error == 0){
        $(".installmentBreak_error").html("");
        var dataString=firstInstallment = '';
        dataString = $("#paymentPlansForm").serialize();
    
        var number_installment_field=$("#number_installment_field").val();
        var amount_per_installment_field=$("#amount_per_installment_field").val();
        var installment_frequency_field=$("#installment_frequency_field").val();
        var start_date=$("#start_date").val();
        
        var headerHtml='<tr><th style="width: 30px; border-right: none;"> &nbsp;</th><th style="width: 150px; border-left: none;"> Due Date</th><th style="width: 120px; border-right: none;"> Amount</th><th style="width: 30px; border-left: none;"> &nbsp;</th><th><p style="display: none; padding-left: 20px;" class="autopay-field m-0">Status</p></th></tr>';
        var wrapper = $('.field_wrapper').html('').html(headerHtml); //Input field wrapper
       
        localStorage.setItem("start_date", start_date);
        localStorage.setItem("number_installment_field", number_installment_field);
        localStorage.setItem("amount_per_installment_field", amount_per_installment_field);
        localStorage.setItem("installment_frequency_field", installment_frequency_field);
        
        var removeclass='';
        var tt = start_date;
        var date = new Date(tt);
        var newdate = new Date(date);
        var countSum=0;
        for(var loopVar=1;loopVar<=Math.ceil(number_installment_field);loopVar++){
        
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
                firstInstallment=$("#first_payment_amount").val().replace(/,/g, '');
                countSum+=parseFloat(firstInstallment);
            }else{
                firstInstallment=amount_per_installment_field;
                if(loopVar==Math.ceil(number_installment_field)){
                    totalAMT=parseFloat($("#final_total_text").val().replace(/,/g, ''));
                    firstInstallment=totalAMT-countSum;
                }else{
                    countSum+=parseFloat(firstInstallment);
                }
            
            }
            if ($("#with_first_payment").is(":checked") == true){
                localStorage.setItem("with_first_payment", '1');
                localStorage.setItem("first_payment_amount", $("#first_payment_amount").val().replace(/,/g, ''));   
            }else{
                localStorage.setItem("with_first_payment", '0');
                localStorage.setItem("first_payment_amount", '');  
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
        }else{
            $(".installmentBreak_error").html("Unable to calculate payment schedule.");
        }
    }

    function removeAdjustmentEntry(id, amount) {
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this imaginary file!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0CC27E',
            cancelButtonColor: '#FF586B',
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Close',
            confirmButtonClass: 'btn btn-success ml-3',
            cancelButtonClass: 'btn btn-danger',
            buttonsStyling: false,
            reverseButtons: true
        }).then(function () {
            beforeLoader();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/removeAdjustmentEntry", // json datasource
                data: {id : id},
                success: function (res) {
                    afterLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        $('#payInvoice').animate({ scrollTop: 0 }, 'slow');                        
                        return false;
                    } else {
                        if(res.item != 'discount'){
                            var discount = $("#addition_total_text").val();
                            discount = discount - amount;
                            discount = (discount<=0) ? 0 : discount;                        
                            $("#addition_total_text").val(discount.toFixed(2));
                            $(".additions_section_total").text(discount.toFixed(2));
                            if(discount <=0){
                                $(".billing-additions-area").html('');
                            }
                        }else{
                            var discount = $("#discount_total_text").val();
                            discount = discount - amount;
                            discount = (discount<=0) ? 0 : discount;                        
                            $("#discount_total_text").val(discount.toFixed(2));
                            $(".discounts_section_total").text(discount.toFixed(2));
                            if(discount <=0){
                                $(".billing-discounts-area").html('');
                            }
                        }
                        $("#entry_"+id).remove(); 
                        recalculate();
                        return false;
                    }
                },
                error: function (jqXHR, exception) {
                    afterLoader();
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> Sorry, something went wrong. Please try again later.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                },
            });

        });
    }

    $(document).on("change", ".forwarded-invoices-check", function() {
        
        var due = $(this).attr("data-due-amount");
        var isCheck = "no";
        var finaltotal = $("#final_total_text").val();
        if($(this).is(":checked")) {
            finaltotal = parseFloat(finaltotal) + parseFloat(due);
            isCheck = "yes";
            arr[$(this).val()] = 'checked';
        } else {
            delete arr[$(this).val()];
            finaltotal = parseFloat(finaltotal) - parseFloat(due);
        }
        $("#final_total").text(finaltotal.toFixed(2));
        $("#final_total_text").val(finaltotal.toFixed(2));
        localStorage.setItem('forwarded_invoices', JSON.stringify(arr));

        if($("#forwardedInvoicesAdjustment").val() > 0){
            $("#preloader").show();
            var id = $(this).val();
            var token_id = $(this).attr("data-token_id");
            var case_id = $("#court_case_id").val();
            $.ajax({
                url: baseUrl+"/bills/invoices/save/forwardInvoice/check",
                type: "GET",
                data: {case_id:case_id, id: id, is_check: isCheck, token_id:token_id, due:due, page:'edit'},
                success: function(data) {
                    window.location.reload();
                }
            });
        }
       
        forwardedInvoicesCalculate();
    });

    $(document).on("change", ".forwarded-invoices-check-old", function() {
        var lineTotal = 0.00;
        var finaltotal = $("#final_total_text").val();
        $(".forwarded-invoices-check").each(function(ind, item) {
            var dueAmt = 0.00;
            if($(this).is(":checked")) {
                dueAmt = $(this).attr("data-due-amount");
                $("#unpaid_amt_"+$(this).val()).text(dueAmt);
            } else {
                $("#unpaid_amt_"+$(this).val()).text("");
            }
            lineTotal += parseFloat(dueAmt);
        });
        var due = $(this).attr("data-due-amount");        
        var isCheck = "no";
        if($(this).is(":checked")) {
            finaltotal = parseFloat(finaltotal) + parseFloat(due);
            isCheck = "yes";
        } else {
            finaltotal = parseFloat(finaltotal) - parseFloat(due);
        }
        $("#unpaid_invoice_total").text(lineTotal.toFixed(2));
        $("#forwarded_total_amount").text(lineTotal.toFixed(2));
        $("#forwarded_total_text").val(lineTotal.toFixed(2));
        $("#final_total").text(finaltotal.toFixed(2));
        $("#final_total_text").val(finaltotal.toFixed(2));

        if($("#forwardedInvoicesAdjustment").val() > 0){
            $("#preloader").show();
            var id = $(this).val();
            var token_id = $(this).attr("data-token_id");
            var case_id = $("#court_case_id").val();
            $.ajax({
                url: baseUrl+"/bills/invoices/save/forwardInvoice/check",
                type: "GET",
                data: {case_id:case_id, id: id, is_check: isCheck, token_id:token_id, due:due, page:'edit'},
                success: function(data) {
                    window.location.reload();
                }
            });
        }
        
        // $(".total-to-apply").text('$'+totalAppliedAmt.toFixed(2));
    });

function invoiceRestrict(){
    var paid_amount = $("#paid_amount").val();
    var final_total = $("#final_total_text").val();
    
    if(parseFloat(final_total) <= parseFloat(paid_amount)){
        // alert("You cannot lower the amount of this invoice below "+$("#paid_amount").val()+" because payments have already been received for that amount.");
        return false;
    }else{
        return true;
    }   
}

function allowPaymentPlan(){
    $("#payment_plan_existing_dialog_bbox").modal("hide");
    calculatePaymentPlansForm(); 
    return false;
}
<?php if(isset($filterByDate) && $filterByDate=="yes") {?>
    $("input:checkbox#range_check_box").attr("checked","checked");
    $('#bill_from_date').removeAttr("disabled");
    $('#bill_to_date').removeAttr("disabled");
<?php } ?>
$("input:checkbox#range_check_box").click(function () {
    if ($(this).is(":checked")) {
        swal({
            title: 'warning',
            text: "Are you sure you want to proceed?<br>Any changes you have made to the invoice entries below will be lost.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0CC27E',
            cancelButtonColor: '#FF586B',
            cancelButtonText: 'Close',
            confirmButtonText: 'Proceed',
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-danger  mr-2',
            buttonsStyling: false,
            reverseButtons: true
        }).then(function () {
            $(function () {
                $('#bill_from_date').removeAttr("disabled");
                $('#bill_to_date').removeAttr("disabled");
                $('#adjustment_delete').val('1');
            });
        }, function (dismiss) {
            $("#range_check_box").prop('checked', false);
        }); 
    } else {
        $("#bill_from_date").attr("disabled", true);
        $("#bill_to_date").attr("disabled", true);
        $('#adjustment_delete').val('');
        $('#bill_from_date').val('');
        $('#bill_to_date').val('');
        if($('#bill_from_date').val('') != ''){
            var URLS=baseUrl+'/bills/invoices/{{base64_encode($findInvoice->id)}}/edit?token={{base64_encode($findInvoice->id)}}&removeAllCreatedEntry=1';
            window.location.href=URLS;
        }
    }
});
</script>
@stop
@endsection
