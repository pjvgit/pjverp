@extends('layouts.master')
<?php $s = sprintf('%06d', $findInvoice->id);?>
@section('title', 'Invoice #'.$s.' - Invoices - Billing')
@section('main-content')
@include('billing.submenu')
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex align-items-center pl-4 pb-4">
                    <h3> Invoice #{{$s}} </h3>
                    <input type="hidden" value="{{ @$findInvoice->id }}" id="invoice_id">
                    <div class="ml-auto d-flex align-items-center flex-row-reverse">
                        <div id="receive_payment_button" class="invoice-show-page-button pl-1">
                          <a class="btn btn-success receive-payment-action m-1" id="record-payment-btn" data-toggle="modal"  data-target="#payInvoice" onclick="payinvoice('{{$findInvoice->invoice_unique_token}}');" data-placement="bottom" href="javascript:;"   title="Edit" data-testid="edit-button" class="btn btn-link">Record Payment</a>
                        </div>

                        <div class="pl-1">
                            {{-- <a class="btn btn-outline-secondary  m-1" href="{{BASE_URL}}bills/invoices/{{base64_encode($findInvoice->id)}}/edit?token={{base64_encode($findInvoice->id)}}">Edit</a> --}}
                            <a class="btn btn-outline-secondary  m-1" href="{{ route('bills/invoices/edit', base64_encode($findInvoice->id)) }}?token={{base64_encode($findInvoice->id)}}">Edit</a>
                        </div>

                        <div id="send-pay-link" class="pl-1">
                            <a id="delete-bill" class="btn btn-outline-secondary m-1" data-toggle="modal"
                                data-target="#emailInvoicePopup" data-placement="bottom" href="javascript:;"
                                onclick="emailInvoicePopup('{{$findInvoice->invoice_unique_token}}')">Email Invoice</a>
                        </div>

                        <div id="share-via-portal" class="pl-1">
                            <a id="delete-bill" class="btn btn-outline-secondary m-1" data-toggle="modal"
                                data-target="#shareInvoicePopup" data-placement="bottom" href="javascript:;"
                                onclick="shareInvoice({{$findInvoice->id}})">Share via Portal
                            </a>



                        </div>
                        <?php
                        if($SharedInvoiceCount>0){?>
                        <div id="share-via-portal" class="pl-1">
                            <a id="delete-bill" class="btn btn-outline-secondary m-1" data-toggle="modal"
                                data-target="#reminderPopup" data-placement="bottom" href="javascript:;"
                                onclick="reminderPopup({{$findInvoice->id}})">Remind
                            </a>
                        </div>
                        <?php } ?>

                        <a class="btn btn-lg btn-link px-2 mr-2 text-black-50" href="#">
                            <i class="fas fa-link test-payment-link-icon" data-toggle="tooltip" data-placement="top"
                                title=""
                                data-original-title="A client credit card entry link is not available because your firm has not signed up for  {{config('app.name')}} Payments. Please enable  {{config('app.name')}} Payments to use this feature"></i>
                        </a>
                        <?php $id=base64_encode($findInvoice->id);?>
                        <a class="btn btn-lg btn-link px-2 text-black-50 bill-export-invoice"
                            onclick="downloadPDF('{{$id}}');">
                            <i class="fas fa-fw fa-cloud-download-alt test-download-bill" data-toggle="tooltip"
                                data-placement="top" title="" data-original-title="Download"></i>
                        </a>

                        <a class="btn btn-lg btn-link px-2 text-black-50 print-bill-icon-action"  onclick="printPDF('{{$id}}');">
                            <i class="fas fa-print test-print-bill" id="print-bill-button" data-toggle="tooltip"
                                data-original-title="Print"></i>
                        </a>
                        <a id="delete-bill" class="btn btn-lg btn-link px-2 text-black-50" data-toggle="modal"
                            data-target="#deleteInvoicePopup" data-placement="bottom" href="javascript:;">
                            <i class="fas fa-trash test-delete-bill" data-bill-id="12211253" data-toggle="tooltip"
                                data-placement="top" title="" data-original-title="Delete"
                                onclick="deleteInvoice({{$findInvoice->id}})"></i>
                        </a>
                    </div>

                </div>
            </div>
            <div id="history_page" class="history-page">
                <div class="border">
                    <?php if(!empty($lastEntry)){?>
                    <table class="table table-striped table-hover table-borderless mb-0 single-bill-invoice-history nowrap">
                        <tbody>
                            <?php 
                                    $value=$lastEntry;
                                    $depositInto=$notes=$print=$refund='';
                                    if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Operating Account"){
                                            $depositInto="Operating (Operating Account)";
                                        } else  if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Trust Account"){  
                                            $depositInto= "Trust (Trust Account)";
                                        }else{
                                            $depositInto="<i class='table-cell-placeholder'></i>";
                                        } 
                                        
                                        if($value->acrtivity_title=="Payment Received" && $value->notes==NULL){
                                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="Deposited Into: '.$value->deposit_into.'" data-original-title="Dismissible popover">View Payment Notes</a>';
                                        }else if($value->acrtivity_title=="Payment Received" && $value->notes!=NULL){
                                            $notes=$value->notes;
                                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes==NULL){
                                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="Deposited Into: '.$value->deposit_into.'" data-original-title="Dismissible popover">View Refund Notes</a>';
                                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes!=NULL){
                                            $notes=$value->notes;
                                        }else{
                                            $notes="<i class='table-cell-placeholder'></i>";
                                        }  


                                        if(in_array($value->acrtivity_title,["Payment Received","Payment Refund"])){
                                            $print='<a href="javascript:void(0);" onclick="PrintTransaction('.$value->id.');"  ><i class="fas fa-print test-print-bill" id="print-bill-button" data-toggle="tooltip" data-original-title="Print"></i></a>';
                                        }else{
                                            $print="";
                                        }  
                                        if($value->acrtivity_title=="Payment Received" && $value->status==1){
                                            $refund='<a data-toggle="modal"  data-target="#RefundPopup" data-placement="bottom" href="javascript:;"  onclick="RefundPopup('.$value->id.');"><button type="button"  class="btn btn-link ">Refund</button></a>|<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                                        }else if($value->acrtivity_title=="Payment Received" && $value->status=2){
                                            $refund='';
                                        }else if($value->acrtivity_title=="Payment Refund" && $value->status==4 || $value->status==2){
                                            $refund='<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                                        }else{
                                            $refund='';
                                        }


                                    ?>
                                    <tr id="" class="invoice-history-row nowrap">
                                        <td class="first_child invoice-history-row-type nowrap">
                                            <?php if($value->acrtivity_title=="Unshared w/Contacts"){?>
                                                <span class="bill-history-indicator status_indicator_red"></span>
                                            <?php }else if($value->acrtivity_title=="Payment Received"){?>
                                                <span class="bill-history-indicator status_indicator_green"></span>
                                            <?php }else if($value->acrtivity_title=="Payment Refund"){?>
                                                <span class="bill-history-indicator status_indicator_yellow"></span>
                                            <?php }else{
                                                ?> <span class="bill-history-indicator"></span>
                                            <?php } ?>
                                            {{$value->acrtivity_title}}
                                        </td>
                                        <td class="invoice-history-row-date">
                                            {{$value->added_date}}
                                        </td>
                                        <td class="invoice-history-row-pay-type">
                                            <?php  $Displayval='<i class="table-cell-placeholder"></i>';
                                            if($value->pay_method!=''){
                                                    $Displayval=$value->pay_method;
                                            }
                                            echo $Displayval;
                                            if($value->refund_amount!=NULL){
                                                echo " (Refunded)";
                                            }
                                            ?>
                                        </td>
                                        <td class="invoice-history-row-amount">
                                            <?php if($value->acrtivity_title=="Payment Received"){?>
                                                <?php if($value->refund_amount!=NULL){
                                                    ?>${{$value->refund_amount}}<?php 
                                                }else{
                                                    ?> ${{number_format($value->amount,2)}}<?php
                                                }?>
                                            <?php }else if($value->acrtivity_title=="Payment Refund"){?>
                                                (${{number_format($value->amount,2)}})
                                                
                                            <?php } else {  ?>
                                            <i class="table-cell-placeholder"></i> 
                                            <?php  } ?>
                                        </td>
                                        <td class="invoice-history-row-user">
                                            <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($value->responsible['id'])}}">
                                                {{substr($value->responsible['cname'],0,100)}}
                                                ({{$value->responsible['user_title']}})</a>
                                        </td>
                                        <td class="invoice-history-row-deposited-into">
                                           <?php echo $depositInto;?>
                                        </td>
                                        <td style="overflow: visible;" class="invoice-history-row-notes">
                                            <div style="position: relative;">
                                                <?php echo $notes;?>
                                            </div>
                                        </td>

                                        <td class="invoice-history-row-print" style="text-align: center;">
                                            <?php echo $print;?>
                                        </td>

                                        <td class="invoice-history-actions last_child"
                                            style="text-align: right; white-space: nowrap;">
                                            <?php echo $refund;?>
                                        </td>
                                    </tr>

                        </tbody>
                    </table>
                    <?php } ?>
                    <?php 
                        if(!$InvoiceHistory->isEmpty()){?>
                    <table class="table table-striped table-hover table-borderless mb-0 bill-invoice-history">
                        <thead class="collapsible-invoice-history-row">
                            <tr>
                                <th>Activity</th>
                                <th>Date</th>
                                <th>Pay Method</th>
                                <th>Amount</th>
                                <th>Responsible User</th>
                                <th>Deposited Into</th>
                                <th>Notes</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                    $depositInto=$notes=$print=$refund='';
                                    foreach ($InvoiceHistory as $key => $value) {
                                        if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Operating Account"){
                                            $depositInto="Operating (Operating Account)";
                                        } else  if($value->acrtivity_title=="Payment Received" && $value->deposit_into=="Trust Account"){  
                                            $depositInto= "Trust (Trust Account)";
                                        }else{
                                            $depositInto="<i class='table-cell-placeholder'></i>";
                                        } 
                                        
                                        if($value->acrtivity_title=="Payment Received" && $value->notes==NULL){
                                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="Deposited Into: '.$value->deposit_into.'" data-original-title="Dismissible popover">View Payment Notes</a>';
                                        }else if($value->acrtivity_title=="Payment Received" && $value->notes!=NULL){
                                            $notes=$value->notes;
                                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes==NULL){
                                            $notes='<a href="javascript:void(0);" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Notes" data-content="Deposited Into: '.$value->deposit_into.'" data-original-title="Dismissible popover">View Refund Notes</a>';
                                        }else if($value->acrtivity_title=="Payment Refund" && $value->notes!=NULL){
                                            $notes=$value->notes;
                                        }else{
                                            $notes="<i class='table-cell-placeholder'></i>";
                                        }  


                                        if(in_array($value->acrtivity_title,["Payment Received","Payment Refund"])){
                                            $print='<a href="javascript:void(0);" onclick="PrintTransaction('.$value->id.');"  ><i class="fas fa-print test-print-bill" id="print-bill-button" data-toggle="tooltip" data-original-title="Print"></i></a>';
                                        }else{
                                            $print="";
                                        }  
                                        if($value->acrtivity_title=="Payment Received" && $value->status==1){
                                            $refund='<a data-toggle="modal"  data-target="#RefundPopup" data-placement="bottom" href="javascript:;"  onclick="RefundPopup('.$value->id.');"><button type="button"  class="btn btn-link ">Refund</button></a>|<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                                        }else if($value->acrtivity_title=="Payment Received" && $value->status=2){
                                            $refund='';
                                        }else if($value->acrtivity_title=="Payment Refund" && $value->status==4 || $value->status==2){
                                            $refund='<a data-toggle="modal"  data-target="#Deleteopup" data-placement="bottom" href="javascript:;"  onclick="DeletePopup('.$value->id.');"><button type="button"  class="btn btn-link ">Delete</button></a>';
                                        }else{
                                            $refund='';
                                        }


                                    ?>
                                    <tr id="" class="invoice-history-row nowrap">
                                        <td class="first_child invoice-history-row-type">
                                            <?php if($value->acrtivity_title=="Unshared w/Contacts"){?>
                                                <span class="bill-history-indicator status_indicator_red"></span>
                                            <?php }else if($value->acrtivity_title=="Payment Received"){?>
                                                <span class="bill-history-indicator status_indicator_green"></span>
                                            <?php }else if($value->acrtivity_title=="Payment Refund"){?>
                                                <span class="bill-history-indicator status_indicator_yellow"></span>
                                            <?php }else{
                                                ?> <span class="bill-history-indicator"></span>
                                            <?php } ?>
                                            {{$value->acrtivity_title}}
                                        </td>
                                        <td class="invoice-history-row-date">
                                            {{$value->added_date}}
                                        </td>
                                        <td class="invoice-history-row-pay-type">
                                            <?php 
                                            $Displayval='<i class="table-cell-placeholder"></i>';
                                            
                                            if($value->pay_method!=''){
                                                    $Displayval=$value->pay_method;
                                            }
                                            echo $Displayval;
                                            if($value->refund_amount!=NULL){
                                                echo " (Refunded)";
                                            }
                                            ?>
                                        </td>
                                        <td class="invoice-history-row-amount">
                                            
                                            <?php if($value->acrtivity_title=="Payment Received"){?>
                                                <?php if($value->refund_amount!=NULL){
                                                    ?>${{$value->refund_amount}}<?php 
                                                }else{
                                                    ?> ${{number_format($value->amount,2)}}<?php
                                                }?>
                                            
                                            <?php }else if($value->acrtivity_title=="Payment Refund"){?>
                                                (${{number_format($value->amount,2)}})
                                                
                                            <?php } else {  ?>
                                            <i class="table-cell-placeholder"></i> 
                                            <?php  } ?>
                                        </td>
                                        <td class="invoice-history-row-user">
                                            <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($value->responsible['id'])}}">
                                                {{substr($value->responsible['cname'],0,100)}}
                                                ({{$value->responsible['user_title']}})</a>
                                        </td>
                                        <td class="invoice-history-row-deposited-into">
                                           <?php echo $depositInto;?>
                                        </td>
                                        <td style="overflow: visible;" class="invoice-history-row-notes">
                                            <div style="position: relative;">
                                                <?php echo $notes;?>
                                            </div>
                                        </td>

                                        <td class="invoice-history-row-print" style="text-align: center;">
                                            <?php echo $print;?>
                                        </td>

                                        <td class="invoice-history-actions last_child"
                                            style="text-align: right; white-space: nowrap;">
                                            <?php echo $refund;?>
                                        </td>
                                    </tr>
                            <?php 
                                } ?>

                        </tbody>
                    </table>
                    <?php 
                    } ?>
                </div>
                <br>
                <?php
                if($InvoiceHistory->count()){?>
                <div class="text-center">
                    <button
                        class="btn btn-sm btn-outline-secondary btn-rounded   mt-2 px-4 view-history-btn show-history-btn">
                        View History
                    </button>

                    <button
                        class="btn btn-sm btn-outline-secondary btn-rounded  mt-2 px-4 close-history-btn toggle-history-btn">
                        Close History
                    </button>
                </div>
                <?php } ?>
                <br>


            </div>

            <div id="preview_page">

                <div style="padding: 30px 50px;">
                    <div class="invoice invoice_page" style="padding: 0 0 20px 0;">
                        <table style="width: 100%; margin: 0; padding: 0; table-layout: fixed;" class="invoice">
                            <tbody style="margin: 0; padding: 0;">
                                <tr>
                                    <td style="width: 130px; padding: 0px !important; vertical-align: top;" rowspan="4">
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
                                    <td style="vertical-align: top; white-space: nowrap; width: 350px;"
                                        class="bill-address pt-4" rowspan="2">
                                        {{$firmData->firm_name}}<br>
                                        {{$firmData->countryname}}<br>

                                        {{$firmData->main_phone}}
                                    </td>
                                    <td rowspan="4">
                                        &nbsp;
                                    </td>
                                    <td class="pt-4"
                                        style="vertical-align: top; white-space: normal; width: 320px; padding-right: 20px; text-align: right;"
                                        rowspan="1">
                                        <span class="bill_firm_name"
                                            style="font-size: 24px;">{{$firmData->firm_name}}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top; white-space: nowrap; width: 320px; padding-right: 20px;"
                                        rowspan="3">

                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tbody>
                                                <tr>
                                                    <td style="padding-top: 0px !important; width: 45%">&nbsp;</td>
                                                    <td
                                                        style="text-align: right; font-size: 20px; font-weight: bold; padding: 5px; padding-top: 0px;">
                                                        Invoice
                                                    </td>
                                                </tr>
                                                <tr class="invoice_info_row">
                                                    <td class="invoice_info_bg" style="white-space: nowrap;">Invoice
                                                        #</td>
                                                    <td style="text-align: right;">{{$s}}</td>
                                                </tr>
                                                <tr class="invoice_info_row">
                                                    <td class="invoice_info_bg" style="white-space: nowrap;">Invoice
                                                        Date</td>
                                                    <td style="text-align: right;">
                                                        {{date('M j, Y',strtotime($findInvoice->created_at))}}</td>
                                                </tr>
                                                <tr class="invoice_info_row">
                                                    <td class="invoice_info_bg" style="white-space: nowrap;">Due
                                                        Date</td>
                                                    <td style="text-align: right;">
                                                        {{($findInvoice->due_date) ? date('M j, Y',strtotime($findInvoice->due_date)) : NULL}}
                                                    </td>
                                                </tr>
                                                <tr class="invoice_info_row">
                                                    <td class="invoice_info_bg"
                                                        style="white-space: nowrap; vertical-align: top;">Balance
                                                        Due</td>
                                                    <td style="text-align: right;">
                                                        ${{number_format($findInvoice->due_amount,2)}}
                                                    </td>
                                                </tr>
                                                <tr class="invoice_info_row">
                                                    <td class="invoice_info_bg" style="white-space: nowrap;">Payment
                                                        Terms</td>
                                                    <?php
                                                            $items=array("0"=>"Due date","1"=>"Due on receipt","2"=>"Net 15","3"=>"Net 30","4"=>"Net 60","5"=>"");
                                                            ?>

                                                    <td style="text-align: right;">
                                                        <?php echo $items[$findInvoice->payment_term]; ?></td>
                                                </tr>
                                                <tr class="invoice_info_row">
                                                    <td class="invoice_info_bg" style="white-space: nowrap;">Case / Matter</td>
                                                    <td style="text-align: right; white-space: normal; word-break: break-word"
                                                        class="court-case-name">
                                                        @if($findInvoice->case_id == 0)
                                                            None
                                                        @else
                                                        <a class="bill-court-case-link"
                                                            {{-- href="{{BASE_URL}}court_cases/{{@$caseMaster->case_unique_number}}/info">{{$caseMaster->case_title}}</a> --}}
                                                            href="{{ route('info', @$caseMaster->case_unique_number) }} ">{{ @$caseMaster->case_title }}</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top; width: 350px; word-wrap: break-word;" rowspan="2">
                                        <span class="billing_user_name">
                                            <a href="{{BASE_URL}}contacts/clients/{{$userMaster->id}}">{{$userMaster->first_name}}
                                                {{$userMaster->middle_name}} {{$userMaster->last_name}}</a><br>

                                                {{($userMaster->street)??''}}   {{($userMaster->apt_unit)??''}}<br>
                                                {{($userMaster->city)??''}} {{($userMaster->state)??''}} {{($userMaster->postal_code)??''}}
                                        </span>
                                        <p></p>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                        <div style="padding: 20px;" class="">
                            <hr>
                        </div>

                        <div style="padding: 20px;">
                            <br>
                            <div id="invoice_total_div">
                                @include('billing.invoices.partials.load_invoice_total')
                            </div>
                            <br>
                            <br>
                            <div id="payment_history_div">
                            @include('billing.invoices.load_invoice_payment_history')
                            </div>
                        </div>
                    </div>
                
                </div>
            </div>
        </div>
    </div>
</div>
<div id="deleteInvoicePopup" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteInvoiceForm" id="deleteInvoiceForm" name="deleteInvoiceForm" method="POST">
            @csrf
            <input type="hidden" value="" name="invoiceId" id="delete_invoice_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Confirm Delete</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this invoice?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                            style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="shareInvoicePopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-xl ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Share via the Client Portal</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="shareInvoicePopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="grantAccessModal" class="modal fade show modal-overlay" tabindex="-3" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
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

<div id="reminderPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Send Reminder</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="reminderPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="emailInvoicePopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Email Invoice</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="emailInvoicePopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<?php if(session('invoiceUpdate')==true){ ?>
    <div id="reshareUpdatedInvoice" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="reshareUpdatedInvoiceForm" id="reshareUpdatedInvoiceForm" name="reshareUpdatedInvoiceForm" method="POST">
            @csrf
            <input type="hidden" value="{{$findInvoice->id}}" name="share_invoice_id" id="share_invoice_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Your invoice has been updated</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Would you like to send an email notification to all shared contacts?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                            style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Dont Send</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Send</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php } ?>


<div id="payInvoice" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Record Payment</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="showError" style="display:none"></div>

                        <div id="payInvoiceArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="deleteEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deletePaymentEntry" id="deletePaymentEntry" name="deletePaymentEntry" method="POST">
            @csrf
            <input type="hidden" value="" name="payment_id" id="delete_payment_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Payment</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this payment and remove all record of it from {{config('app.name')}}?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                type="submit">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>




<div id="RefundPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Refund Payment</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                    </button>
            </div>
            <div class="modal-body">
                <div id="RefundPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{asset('public/assets/styles/css/invoice_show.css')}}" />
<style>
    
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
    .nonbillableRow {
        color: #aaa !important;
    }
   
</style>
@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
       
        $('[data-toggle="tooltip"]').tooltip();

        $(".show-history-btn").click(function () {
            $(".bill-invoice-history").show();
            $(".close-history-btn").show();
            $(".show-history-btn").hide();
            $(".single-bill-invoice-history").hide();

        });
        $(".close-history-btn").click(function () {
            $(".bill-invoice-history").hide();
            $(".close-history-btn").hide();
            $(".show-history-btn").show();
            $(".single-bill-invoice-history").show();
        });


        $(".single-bill-invoice-history").show();
        $(".bill-invoice-history").hide();
        $(".close-history-btn").hide();
        $(".show-history-btn").show();

        $('#deleteInvoiceForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteInvoiceForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteInvoiceForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/deleteInvoice", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
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
                        afterLoader();
                        URL = baseUrl + '/bills/invoices?type=all';
                        window.location.href = URL;
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

        $('#reshareUpdatedInvoiceForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#reshareUpdatedInvoiceForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#reshareUpdatedInvoiceForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/resendUpdatedInvoice", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
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

        $('#deletePaymentEntry').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deletePaymentEntry').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deletePaymentEntry").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/deletePaymentEntry", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
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
    });


    function deleteInvoice(id) {
        $("#deleteInvoicePopup").modal("show");
        $("#delete_invoice_id").val(id);
    }

    function shareInvoice(id) {
        beforeLoader();
        $("#preloader").show();
        $("#shareInvoicePopupArea").html('');
        $("#shareInvoicePopupArea").html('Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/shareInvoice",
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
                    $("#shareInvoicePopupArea").html('');
                    $('#shareInvoicePopup').animate({
                        scrollTop: 0
                    }, 'slow');
                    $("#preloader").hide();
                    return false;
                } else {
                    afterLoader()
                    $("#shareInvoicePopupArea").html(res);
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
                $('#shareInvoicePopup').animate({
                    scrollTop: 0
                }, 'slow');
                $("#shareInvoicePopupArea").html('');
                $("#preloader").hide();
                afterLoader();
            }
        })
    }

    function reminderPopup(id) {
        $("#preloader").show();
        $("#reminderPopupArea").html('Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/sendReminder",
                data: {
                    "id": id
                },
                success: function (res) {
                    $("#reminderPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function downloadPDF(id) {
        $("#preloader").show();
        $("#reminderPopupArea").html('Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/downloadInvoice",
                data: {
                    "id": id
                },
                success: function (res) {
                    var anchor = document.createElement('a');
                    anchor.href = res.url;
                    anchor.target = '_blank';
                    anchor.download = res.file_name;
                    anchor.click();

                    // window.open(res.url, '_blank');
                    // window.print();
                    // window.location.href=res.url;
                    $("#preloader").hide();
                }
            })
        })
    }


    function printPDF(id) {
        $("#preloader").show();
        $("#reminderPopupArea").html('Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/downloadInvoice",
                data: {
                    "id": id
                },
                success: function (res) {
                    printView(res.url)
                    // window.open(res.url, '_blank');
                    // window.print();
                    // window.location.href=res.url;
                    $("#preloader").hide();
                }
            })
        })
    }

    function printView(path){
        window.open('{{ url("/") }}print?path='+path, '_blank');
    }
    function emailInvoicePopup(id) {
        $('.showError').html('');
        beforeLoader();
        $("#preloader").show();
        $("#emailInvoicePopupArea").html('');
        $("#emailInvoicePopupArea").html('Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/emailInvoice",
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
                    $("#emailInvoicePopupArea").html('');
                    $('#emailInvoicePopup').animate({
                        scrollTop: 0
                    }, 'slow');
                    $("#preloader").hide();
                    return false;
                } else {
                    afterLoader()
                    $("#emailInvoicePopupArea").html(res);
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
                $('#shareInvoicePopup').animate({
                    scrollTop: 0
                }, 'slow');
                $("#emailInvoicePopupArea").html('');
                $("#preloader").hide();
                afterLoader();
            }
        })
    }
    function payinvoice(id) {
        $("#preloader").show();
        $("#payInvoiceArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/bills/invoices/payInvoice", // json datasource
                data: {'id':id},
                success: function (res) {
                    $("#payInvoiceArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function RefundPopup(id) {
        $("#preloader").show();
        $("#RefundPopupArea").html('Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/refundPopup", 
                data: {'transaction_id':id},
                success: function (res) {
                    $("#RefundPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function DeletePopup(id) {
        $("#deleteEntry").modal("show");
        $("#delete_payment_id").val(id);
    }
    function PrintTransaction(id) {
      
        $("#preloader").show();
        $("#reminderPopupArea").html('Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/InvoiceHistoryInlineView",
                data: { "id": id },
                success: function (res) {
                    window.open(res.url, '_blank');
                    $("#preloader").hide();
                }
            })
        })
    }
    
    <?php if(session('invoiceUpdate')==true){ ?>
    $("#reshareUpdatedInvoice").modal("show");

    $('#reshareUpdatedInvoice').on('hidden.bs.modal', function () {
        {{session(['invoiceUpdate' => ''])}}
    });
    <?php } ?>

    
</script>
<script src="{{ asset('assets\js\custom\invoice\viewinvoice.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
@stop
@endsection
