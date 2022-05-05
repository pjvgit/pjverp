@extends('layouts.master')
@section('title', 'All invoices - Billing')
@section('main-content')

@include('billing.submenu')
<?php
if(isset($_GET['global_search']) && $_GET['global_search']!="")
{
    $MixVal=explode("-",$_GET['global_search']);
    $serachOn=base64_decode($MixVal[1]);
    $serachBy=base64_decode($MixVal[0]);
}else{
    $serachOn="";
    $serachBy="";
    $_GET['global_search']='';
}

?>
<div class="separator-breadcrumb border-top"></div>

<div class="row pb-3">
    <div class="col-lg-12 col-md-12">
        <div class="accordion" id="accordionRightIcon">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title ul-collapse__icon--size ul-collapse__right-icon mb-0"><a class="text-default"
                            data-toggle="collapse" href="#accordion-item-icon-right-1" aria-expanded="true">Invoice
                            Overview

                        </a></h6>
                </div>
                <div class="collapse show" id="accordion-item-icon-right-1" data-parent="#accordionRightIcon" style="">
                    <div class="card-body">
                        <div class="p-2">
                            <div class="progress mb-3" style="height:40px;cursor: pointer;">
                                <?php
                               $finalAmount=$InvoicesUnsentAmount+ $InvoicesDraftAmount+ $InvoicesSentAmount + $InvoicesPaidPartialAmount+ $InvoicesOverdueAmount+ $InvoicesPaidAmount;
                            
                                if($finalAmount>0){
                                   $InvoicesUnsentAmountShow=number_format(($InvoicesUnsentAmount/$finalAmount*100),2);
                                   $InvoicesDraftAmountShow=number_format(($InvoicesDraftAmount/$finalAmount*100),2);
                                   $InvoicesSentAmountShow=number_format(($InvoicesSentAmount/$finalAmount*100),2);
                                   $InvoicesPaidPartialAmountShow=number_format(($InvoicesPaidPartialAmount/$finalAmount*100),2);
                                   $InvoicesOverdueAmountShow=number_format(($InvoicesOverdueAmount/$finalAmount*100),2);
                                   $InvoicesPaidAmountShow=number_format(($InvoicesPaidAmount/$finalAmount*100),2);
                                }
                                else{
                                    $InvoicesUnsentAmountShow=0;
                                   $InvoicesDraftAmountShow=0;
                                   $InvoicesSentAmountShow=0;
                                   $InvoicesPaidPartialAmountShow=0;
                                   $InvoicesOverdueAmountShow=0;
                                   $InvoicesPaidAmountShow=0;   
                                }

                                ?>
                                <div class="progress-bar" style="background-color: rgb(155, 155, 155);width: {{$InvoicesUnsentAmountShow}}%" role="progressbar"  aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-placement="top" title="" data-original-title="Unsent {{$InvoicesUnsentAmountShow}}%">{{$InvoicesUnsentAmountShow}}%</div>
                                <div class="progress-bar" style="background-color: rgb(213, 213, 213);width: {{$InvoicesDraftAmountShow}}%"  role="progressbar"  aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-placement="top" title="" data-original-title="Draft {{$InvoicesDraftAmountShow}}%">{{$InvoicesDraftAmountShow}}%</div>
                                <div class="progress-bar" style="background-color: rgb(51, 101, 138);width: {{$InvoicesSentAmountShow}}%" role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-placement="top" title="" data-original-title="Sent {{$InvoicesSentAmountShow}}%">{{$InvoicesSentAmountShow}}%</div>
                                <div class="progress-bar" style="background-color: rgb(254, 204, 0);width: {{$InvoicesPaidPartialAmountShow}}%" role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-placement="top" title="" data-original-title="Paid Partial {{$InvoicesPaidPartialAmountShow}}%">{{$InvoicesPaidPartialAmountShow}}%</div>
                                <div class="progress-bar" style="background-color: rgb(208, 2, 27);width: {{$InvoicesOverdueAmountShow}}%" role="progressbar"  aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-placement="top" title="" data-original-title="Overdue {{$InvoicesOverdueAmountShow}}%">{{$InvoicesOverdueAmountShow}}%</div>
                                <div class="progress-bar" style="background-color: rgb(25, 191, 51);width: {{$InvoicesPaidAmountShow}}%" role="progressbar"  aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" data-toggle="tooltip" data-placement="top" title="" data-original-title="Paid {{$InvoicesPaidAmountShow}}%">{{$InvoicesPaidAmountShow}}%</div>
                            </div>
                            
                            <div class="insights-legend d-flex flex-row pl-1 pr-1 flex-wrap" >
                                <div class="d-flex row justify-content-between w-100">
                                    <div class="col-1 label d-flex flex-column invoice-overview-legend-unsent">
                                        <h5 class="currency font-weight-bold m-0">
                                            <div style="white-space: nowrap;">
                                                <div class="align-self-start mt-1 d-inline-block mr-1"
                                                    style="background-color: rgb(155, 155, 155); height: 14px; width: 14px;">
                                                </div>${{number_format($InvoicesUnsentAmount,2)}}
                                            </div>
                                        </h5>
                                        <p>Unsent</p>
                                    </div>
                                    <div class="col-1 label d-flex flex-column invoice-overview-legend-draft">
                                        <h5 class="currency font-weight-bold m-0">
                                            <div style="white-space: nowrap;">
                                                <div class="align-self-start mt-1 d-inline-block mr-1"
                                                    style="background-color: rgb(213, 213, 213); height: 14px; width: 14px;">
                                                </div>${{number_format($InvoicesDraftAmount,2)}}
                                            </div>
                                        </h5>
                                        <p>Draft</p>
                                    </div>
                                    <div class="col-1 label d-flex flex-column invoice-overview-legend-sent">
                                        <h5 class="currency font-weight-bold m-0">
                                            <div style="white-space: nowrap;">
                                                <div class="align-self-start mt-1 d-inline-block mr-1"
                                                    style="background-color: rgb(51, 101, 138); height: 14px; width: 14px;">
                                                </div>${{number_format($InvoicesSentAmount,2)}}
                                            </div>
                                        </h5>
                                        <p>Sent</p>
                                    </div>
                                    <div class="col-1 label d-flex flex-column invoice-overview-legend-partial">
                                        <h5 class="currency font-weight-bold m-0">
                                            <div style="white-space: nowrap;">
                                                <div class="align-self-start mt-1 d-inline-block mr-1"
                                                    style="background-color: rgb(254, 204, 0); height: 14px; width: 14px;">
                                                </div>${{number_format($InvoicesPaidPartialAmount,2)}}
                                            </div>
                                        </h5>
                                        <p>Partial</p>
                                    </div>
                                    <div class="col-1 label d-flex flex-column invoice-overview-legend-overdue">
                                        <h5 class="currency font-weight-bold m-0">
                                            <div style="white-space: nowrap;">
                                                <div class="align-self-start mt-1 d-inline-block mr-1"
                                                    style="background-color: rgb(208, 2, 27); height: 14px; width: 14px;">
                                                </div>${{number_format($InvoicesOverdueAmount,2)}}
                                            </div>
                                        </h5>
                                        <p>Overdue</p>
                                    </div>
                                    <div class="col-1 label d-flex flex-column invoice-overview-legend-paid">
                                        <h5 class="currency font-weight-bold m-0">
                                            <div style="white-space: nowrap;">
                                                <div class="align-self-start mt-1 d-inline-block mr-1"
                                                    style="background-color: rgb(25, 191, 51); height: 14px; width: 14px;">
                                                </div>${{number_format($InvoicesPaidAmount,2)}}
                                            </div>
                                        </h5>
                                        <p>Paid</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="row">

    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <div class="d-flex align-items-center pl-4 pb-4">
                        <h3> Invoices</h3>
                        <ul class="d-inline-flex nav nav-pills pl-4">
                            <input type="hidden" name="type" value="{{$_GET['type'] ?? 'all'}}">
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=all&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='all' ) echo "active"; ?>">All</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=unsent&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='unsent') echo "active"; ?>">Unsent</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=sent&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='sent') echo "active"; ?>">Sent</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=paid&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='paid') echo "active"; ?>">Paid</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=partial&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='partial') echo "active"; ?>">Partial</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=overdue&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='overdue') echo "active"; ?>">Overdue</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=forwarded&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='forwarded') echo "active"; ?>">Forwarded</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=draft&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='draft') echo "active"; ?>">Draft</a>
                            </li>
                            <li class="d-print-none nav-item">
                                <a href="{{route('bills/invoices')}}?type=batches&global_search={{$_GET['global_search']}}"
                                    class="nav-link <?php if(isset($_GET['type']) && $_GET['type']=='batches') echo "active"; ?>">Batches</a>
                            </li>

                        </ul>
                        @can('billing_add_edit')
                        <div class="ml-auto d-flex align-items-center d-print-none">
                            <div id="bulk-dropdown" class="mr-2 actions-button btn-group">
                                <div class="mx-2">
                                    <div class="btn-group show">
                                        <button class="btn btn-info m-1 dropdown-toggle" data-toggle="dropdown"
                                            id="actionbutton" disabled="disabled" aria-haspopup="true" aria-expanded="false">
                                            Action
                                        </button>
                                        <div class="dropdown-menu bg-transparent shadow-none p-0 m-0 ">
                                            <div class="card">
                                                <div tabindex="-1" role="menu" aria-hidden="false"
                                                    class="dropdown-menu dropdown-menu-right show" x-placement="top-end"
                                                    style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(-112px, -423px, 0px);">
                                                    <h6 tabindex="-1" class="dropdown-header"> Change Status to </h6>
                                                        <button type="button" tabindex="0" role="menuitem" class="dropdown-item" onclick="setBulkStatusAction('Draft');">Draft</button>
                                                        
                                                        <button type="button" tabindex="0" role="menuitem"
                                                        class="dropdown-item" onclick="setBulkStatusAction('Unsent');" >Unsent</button>
                                                        
                                                        <button type="button" tabindex="0" role="menuitem" class="dropdown-item" onclick="setBulkStatusAction('Sent');">Sent</button>
                                                    <div tabindex="-1" class="dropdown-divider"></div>
                                                    <h6 tabindex="-1" class="dropdown-header"> Share </h6>
                                                    <button
                                                        type="button" tabindex="0" role="menuitem"
                                                        class="dropdown-item" onclick="setBulkSharesAction('BC');">Share with Billing Contact</button>
                                                        <button
                                                        type="button" tabindex="0" role="menuitem"
                                                        class="dropdown-item"  onclick="setBulkSharesAction('AC');">Share with All Case Contacts</button>
                                                    <div tabindex="-1" class="dropdown-divider">

                                                    </div>
                                                    <h6 tabindex="-1" class="dropdown-header">Apply Funds</h6>
                                                    <button type="button" tabindex="0" role="menuitem"
                                                        class="dropdown-item" onclick="applyTrustBalance();">Apply Trust Funds
                                                    </button>
                                                    <button type="button" tabindex="0" role="menuitem"
                                                        class="dropdown-item" onclick="applyCreditBalance();">Apply Credit Funds
                                                    </button>
                                                    <div tabindex="-1" class="dropdown-divider"></div>
                                                        <button type="button" tabindex="0" role="menuitem"
                                                        class="dropdown-item" onclick="downloadBulkInvoice();">Export as PDF</button>
                                                        <button type="button" tabindex="0" role="menuitem"
                                                        class="dropdown-item m2" onclick="setBulkEnableOnlinePaymentPopup();">Enable Online Payments</button>
                                                        @can('delete_items')
                                                        <button type="button" tabindex="0" role="menuitem" class="dropdown-item" onclick="deleteBulkInvoice();" >Delete Invoices</button>
                                                        @endcan
                                                        <button type="button" tabindex="0" role="menuitem"
                                                        class="dropdown-item" onclick="adjustmentBulkInvoice();">Adjustments</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('bills/invoices/open') }}" class="btn btn-primary btn-rounded m-1">Add Invoice</a>
                        </div>
                        @endcan
                    </div>
                <div id="invoices_list" class="invoices-list-container">
                    <div class="row pl-4 pb-4">
                        <div class="col-md-3 form-group mb-3">
                            <select class="form-control global_search" id="global_search" name="global_search"
                            data-placeholder="Filter bills by case, billing contact or batch...">
                            <option value="">Filter bills by case, billing contact or batch...</option>
                            <optgroup label="Cases" style="background-color:gray;">
                                <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                                <option uType="case" <?php if($serachOn=="case" && $Caseval->id==$serachBy){ echo "selected=selected"; } ?> value="{{base64_encode($Caseval->id)}}-{{base64_encode('case')}}"><?php if($serachOn=="case" && $Caseval->id==$serachBy){ echo "Case :"; } ?>{{substr($Caseval->case_title,0,100)}} <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?> </option>
                                <?php } ?>
                            </optgroup>
                            {{-- <optgroup label="Potential Cases">
                                <?php foreach($potentialCaseList as $potentialCaseListKey=>$potentialCaseListVal){ ?>
                                <option uType="potential_case" <?php if($serachOn=="potential_case" && $potentialCaseListVal->id==$serachBy){ echo "selected=selected"; } ?> value="{{base64_encode($potentialCaseListVal->id)}}-{{base64_encode('potential_case')}}">Potential Case : {{substr($potentialCaseListVal->first_name,0,100)}} </option>
                                <?php } ?>
                            </optgroup> --}}
                            <optgroup label="Contacts">
                                <?php foreach($CaseMasterClient as $CaseMasterClientKey=>$CaseMasterClientVal){ ?>
                                <option uType="contact"  <?php if($serachOn=="contact" && $CaseMasterClientVal->id==$serachBy){ echo "selected=selected"; } ?> value="{{base64_encode($CaseMasterClientVal->id)}}-{{base64_encode('contact')}}"> <?php if($serachOn=="contact" && $CaseMasterClientVal->id==$serachBy){ echo "Billing Contact:"; } ?> {{substr($CaseMasterClientVal->first_name,0,100)}} {{substr($CaseMasterClientVal->last_name,0,100)}}</option>
                                <?php } ?>
                            </optgroup>
                            
                            {{-- <optgroup label="Companies">
                                <?php foreach($CaseMasterCompanies as $CaseMasterCompaniesKey=>$CaseMasterCompaniesVal){ ?>
                                <option uType="company" <?php if($serachOn=="company" && $CaseMasterCompaniesVal->id==$serachBy){ echo "selected=selected"; } ?> value="{{base64_encode($CaseMasterCompaniesVal->id)}}-{{base64_encode('company')}}"><?php if($serachOn=="company" && $CaseMasterCompaniesVal->id==$serachBy){ echo "Billing Contact:"; } ?>{{substr($CaseMasterCompaniesVal->first_name,0,100)}} </option>
                                <?php } ?>
                            </optgroup> --}}
                            {{-- <optgroup label="Leads">
                                <?php foreach($caseLeadList as $caseLeadListKey=>$caseLeadListVal){ ?>
                                <option uType="lead" <?php if($serachOn=="lead" && $caseLeadListVal->id==$serachBy){ echo "selected=selected"; } ?>  value="{{base64_encode($caseLeadListVal->id)}}-{{base64_encode('lead')}}"><?php if($serachOn=="lead" && $caseLeadListVal->id==$serachBy){ echo "Billing Contact:"; } ?>{{substr($caseLeadListVal->first_name,0,100)}} {{substr($caseLeadListVal->last_name,0,100)}}</option>
                                <?php } ?>
                            </optgroup> --}}
                            <optgroup label="Batches">
                                <?php foreach($InvoicesBatches as $batchesKey=>$batchesVal){ ?>
                                <option uType="batch" <?php if($serachOn=="batches" && $batchesVal->invoice_batch_id==$serachBy){ echo "selected=selected"; } ?>  value="{{base64_encode($batchesVal->invoice_batch_id)}}-{{base64_encode('batches')}}"><?php if($serachOn=="batches" && $batchesVal->invoice_batch_id==$serachBy){ echo "Batch : "; } ?>{{$batchesVal->batch_code}}</option>
                                <?php } ?>
                            </optgroup>
                            
                        </select>
                        </div>
                    </div>
                </form>
                    <?php if($InvoiceCounter<=0){?>
                    <div class="empty-state">
                        <img alt="Invoice Example" class="thumbnail"
                            src="{{BASE_URL}}images/invoice.png">
                        <div class="text-container">
                            <h2>Add your first invoice</h2>
                            <ul>
                                <li> Easily generate invoices from time entries and expenses and share them through
                                    the client portal. </li>
                                <li> Track the status of your invoices at a glance, view payments history and set up
                                    online payments to get paid faster. </li>
                            </ul>
                        </div>
                    </div>
                    <?php }else{
                        ?>
                         <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="invoiceGrid" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="1%">id</th>
                                        <th class="col-md-auto nosort"><input type="checkbox" id="checkall"></th>
                                        <th class="col-md-auto nosort" ></th>
                                        <th width="10%">Number</th>
                                        <th width="10%">Contact</th>
                                        <th width="10%">Case</th>
                                        <th width="8%">Total</th>
                                        <th width="8%">Paid</th>
                                        <th width="8%">Amount Due</th>
                                        <th width="10%">Due</th>
                                        <th width="10%">Created</th>
                                        <th width="8%" class="nosort">Status</th>
                                        <th width="8%" class="nosort">Viewed</th>
                                        <th width="15%" class="nosort"></th>
                                    </tr>
                                </thead>
        
                            </table>
                        </div><?php 
                    }?>
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .empty-state .thumbnail {
        float: left;
        height: 223px;
        width: 223px;
    }

    .empty-state {
        height: 260px !important;
        margin: auto;
        padding-top: 30px;
        width: 700px;
        background-image: none;
    }

    .empty-state .text-container {
        margin-left: 249px;
        position: relative;
    }

    .empty-state h2 {
        color: #93c2e2;
        font-size: 18pt;
        margin-top: 0;
    }

    ol,
    ul,
    dl {
        margin-top: 0;
        margin-bottom: 1rem;
    }

    .empty-state ul {
        color: var(--gray-light);
        font-size: 14pt;
        font-weight: 400;
        padding-left: 20px;
        text-align: left;
    }

td,th{
    white-space: nowrap;
}
.select2-results__group{
    background-color: #4297d7;
    color: white;
    padding-left: 13px;
}
.select2Selected{
    background-color: red;
    background-image: none;
}
</style>

@include('billing.invoices.partials.invoice_action_modal')

<div id="deleteBulkInvoice" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteBulkInvoiceForm" id="deleteBulkInvoiceForm" name="deleteBulkInvoiceForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Deleting Selected Invoices</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            <p>Are you sure you want to delete this Invoice?</p>
                            <ul>
                                <li>This action can’t be undone</li>
                                <li>Time entries, expenses and flat fee entries will be put back into the system in their "Open" state.</li>
                                <li>All payments and refunds made against the invoice WILL BE DELETED. If trust funds were used to pay the invoice, those funds will be returned to the client's trust fund. Other funds with different origin will be sent to the client's trust fund as well.</li>
                                <li>Firm users working on the case will receive a notification</li>
                            </ul>
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
<div id="adjustmentBulkInvoice" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <form class="adjustmentBulkInvoiceForm" id="adjustmentBulkInvoiceForm" name="adjustmentBulkInvoiceForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Adjustments</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            <div class="tab-content" id="myTabContent">
                                
                                    <div class="bootstrap form-component">
                                        <div>
                                            <div class="form-group row"><label for="discount_type"
                                                    class="col-sm-3 col-form-label">Adjustment Type</label>
                                                <div class="col">
                                                    <div>
                                                        <div class=""><select id="discount_type" name="discount_type"
                                                                class="form-control custom-select  ">
                                                                <option value="discount">Discount</option>
                                                                <option value="intrest">Interest</option>
                                                                <option value="tax">Tax</option>
                                                                <option value="addition">Addition</option>
                                                            </select></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row"><label for="discount_applied_to"
                                                    class="col-sm-3 col-form-label">Apply to</label>
                                                <div class="col">
                                                    <div>
                                                        <div class=""><select id="discount_applied_to"
                                                                name="discount_applied_to"
                                                                class="form-control custom-select  ">
                                                                <option value="flat_fees">Flat Fees</option>
                                                                <option value="time_entries">Time Entries</option>
                                                                <option value="expenses">Expenses</option>
                                                                <option value="balance_forward_total">Balance Forward Total
                                                                </option>
                                                                <option value="sub_total">Subtotal</option>
                                                            </select></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row"><label for="amount"
                                                    class="col-sm-3 col-form-label">Amount</label>
                                                <div class="col">
                                                    <div class="">
                                                        <div class="row">
                                                            <div class="col-5 form-group"><input step="0.01" min="0.10" max="100"
                                                                    name="amount" type="number" class="form-control"
                                                                    value="0"></div>
                                                            <div class="col-3 form-check"><label
                                                                    class="form-check-label "><input name="amountType"
                                                                        type="radio" class="form-check-input"
                                                                        value="percentage" checked=""> Percentage</label>
                                                            </div>
                                                            <div class="col form-check"><label
                                                                    class="form-check-label "><input name="amountType"
                                                                        type="radio" class="form-check-input"
                                                                        value="amount"> Dollar Amount</label></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row"><label for="notes"
                                                    class="col-sm-3 col-form-label">Notes</label>
                                                <div class="col">
                                                    <div>
                                                        <div class="">
                                                            <div class="input-group"><input id="notes" name="notes"
                                                                    class="form-control " type="text" placeholder=""
                                                                    data-testid="notes" value=""></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                   
                            </div>
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
                                type="submit">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="adjustmentNotApplied" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Some Adjustments Could Not Be Applied</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <div class="adjustments-info-modal">
                        <div>Adjustments were not applied to the following invoices:</div>
                        <ul class="my-3">
                            <li id="caseID"></li>
                        </ul>
                    <div>The adjustment may have calculated to 0, the adjustment type may have been invalid for the selected invoice, the invoice has already been forwarded, or the invoice has a payment plan.</div>
                </div>
            </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-12  text-center">
                    <div class="form-group row float-right">
                        <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="setBulkStatusActionPopup" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="setBulkStatusActionForm" id="setBulkStatusActionForm" name="setBulkStatusActionForm" method="POST">
            @csrf
            <input type="hidden" name="status" value="" id="current_status">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Update Invoice Status</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            <div><div class="mb-2">Are you sure you want to update selected invoices to <span id="stateChange"></span>?</div><div class="mb-1 alert alert-info fade show" role="alert"><div class="d-flex align-items-start"><div class="w-100"><span class="font-weight-bold">Note: </span>Statuses will not be updated for forwarded invoices.</div></div></div></div>
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
                                type="submit">Update Status</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="setBulkSharesActionPopup" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="setBulkSharesActionForm" id="setBulkSharesActionForm" name="setBulkSharesActionForm" method="POST">
            @csrf
            <input type="hidden" name="status" value="" id="current_action">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shareTitle"></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            <div><div class="mb-2">Are you sure you want to share the invoices with <span id="tileChange"></span>?</div></div>
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
                                type="submit">Share</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="sharedNotApplied" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Some Invoices Could Not Be Applied</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <div class="adjustments-info-modal">
                        <div>Invoices could not be shared to the following contacts:</div>
                        <ul class="my-3">
                            <li id="contactID"></li>
                        </ul>
                        <div>
                        Invoices can not be shared with companies or contacts who do not have client portal access. Invoices can not be shared if they are not associated with a case. 
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-12  text-center">
                    <div class="form-group row float-right">
                        <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="setBulkEnableOnlinePaymentPopup" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="setBulkEnableOnlinePaymentForm" id="setBulkEnableOnlinePaymentForm" name="setBulkEnableOnlinePaymentForm" method="POST">
            @csrf
            <input type="hidden" name="status" value="" id="current_status">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Enable Online Payments</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label for="firstName1"><span class="modal-title">Enable Online Payments</span></label>
                            <select id="bank_account" name="bank_account"
                                class="form-control custom-select  ">
                                <option value="0">Select Bank Account</option>
                               
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <div class="alert alert-info fade show" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="w-100">
                                        <span class="font-weight-bold">Note: </span>Online payments will not be enabled for forwarded invoices.
                                    </div>
                                </div>
                            </div>
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
                                type="submit">Enable</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="applyTrustBalancePopup" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="applyTrustBalanceForm" id="applyTrustBalanceForm" name="applyTrustBalanceForm" method="POST">
            @csrf
            <input type="hidden" name="status" value="" id="current_status">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Apply Trust Balances to Invoices</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <p>Are you sure you want to apply trust balances to these invoices?</p>
                            <div class="alert alert-warning">
                                Please note that we currently do not support applying allocated case level trust balances
                            </div>
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
                                type="submit">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<div id="creditFundNotApplied" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Funds Summary</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" id="creditFundNotAppliedArea">
                
            </div>
            <div class="modal-footer">
                <div class="col-md-12  text-center">
                    <div class="form-group row float-right">
                        <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="invoiceNotDeleted" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Some Invoices Could Not Be Deleted</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" id="invoiceNotDeletedArea">
                
            </div>
            <div class="modal-footer">
                <div class="col-md-12  text-center">
                    <div class="form-group row float-right">
                        <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- For credit funds --}}
<div id="applyCreditBalancePopup" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="applyCreditBalanceForm" id="applyCreditBalanceForm" name="applyCreditBalanceForm" method="POST">
            @csrf
            <input type="hidden" name="status" value="" id="current_status">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Apply Credit Balances to Invoices</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            Are you sure you want to apply credit balances to these invoices?
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" type="submit">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<div id="trustFundNotApplied" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Funds Summary</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" id="trustFundNotAppliedArea">
                
            </div>
            <div class="modal-footer">
                <div class="col-md-12  text-center">
                    <div class="form-group row float-right">
                        <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('page-js-inner')
<script src="{{ asset('assets\js\custom\invoice\deleteInvoice.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script type="text/javascript">
    $(document).ready(function () {
        localStorage.setItem('adjustment_token', "");
        $(".global_search").select2({
            placeholder: "Filter bills by case, billing contact or batch...",
            theme: "classic",
            allowClear: true
        });
        var searchBy = "{{$serachBy}}";
        if(searchBy != ''){
            $(".select2-selection__rendered").css({"color":"red", "font-weight": "900"});
        }
        $('.dropdown-toggle').dropdown();
        $('#payInvoice').on('hidden.bs.modal', function () {
            invoiceGrid.ajax.reload(null, false);
        });
        var invoiceGrid =  $('#invoiceGrid').DataTable({
            // stateSave: true,
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            // stateSave: true,
            searching: false, "aaSorting": [],
            "order": [[3, "desc"]],
            "ajax":{
                url :baseUrl +"/bills/invoices/loadInvoices",
                type: "post",  
                data :{ 'load' : 'true', 'type':"{{$_GET['type']}}","global_search":"{{$_GET['global_search']}}" },
                error: function(){  
                    $("#invoiceGrid_processing").css("display","none");
                }
            },
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] },{'bSortable': false,'aTargets': ['nosort']}],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},
                { data: 'id'},    
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                    $('td:eq(0)', nRow).html('<div class="text-left"><input id="select-row-74" class="task_checkbox" type="checkbox" value="'+aData.id+'" class="task_checkbox" name="expenceId['+aData.id+']"></div>');

                    if(aData.is_lead_invoice == 'yes'){
                        $('td:eq(1)', nRow).html('<a href="'+baseUrl+'/bills/invoices/potentialview/'+aData.decode_id+'"><button class="btn btn-primary btn-rounded" type="button" id="button">View</button> </a>');
                        $('td:eq(2)', nRow).html('<a href="'+baseUrl+'/bills/invoices/potentialview/'+aData.decode_id+'">'+aData.invoice_id+' </a>');
                    }else{
                        $('td:eq(1)', nRow).html('<a href="'+baseUrl+'/bills/invoices/view/'+aData.decode_id+'"><button class="btn btn-primary btn-rounded" type="button" id="button">View</button> </a>');
                        $('td:eq(2)', nRow).html('<a href="'+baseUrl+'/bills/invoices/view/'+aData.decode_id+'">'+aData.invoice_id+' </a>');
                    }

                    if(aData.user_level == 2)
                        $('td:eq(3)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/clients/'+aData.uid+'">'+aData.contact_name+'</a></div>');
                    else if(aData.user_level == 5 && aData.is_lead_invoice == 'yes')
                        $('td:eq(3)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/leads/'+aData.uid+'/case_details/info">'+aData.contact_name+'</a></div>');
                    else
                        $('td:eq(3)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/companies/'+aData.uid+'">'+aData.contact_name+'</a></div>');
                        
                    if(aData.ctitle == null)
                        if(aData.user_level == 5 && aData.is_lead_invoice == 'yes')
                            $('td:eq(4)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/leads/'+aData.uid+'/case_details/invoices">Potential Case: '+aData.contact_name+'</a></div>');
                        else
                            $('td:eq(4)', nRow).html('<div class="text-left">None</div>');
                    else
                        $('td:eq(4)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/info">'+aData.ctitle+'</a></div>');
                    
                    
                    $('td:eq(5)', nRow).html('<span class="d-none">'+aData.total_amount_new+'</span><div class="text-left">$'+aData.total_amount_new+'</div>');
                    $('td:eq(6)', nRow).html('<span class="d-none">'+aData.paid_amount_new+'</span><div class="text-left">$'+aData.paid_amount_new+'</div>');
                    var fwd = "";
                    if(aData.status == "Forwarded") {
                        $.each(aData.invoice_forwarded_to_invoice, function(invkey, invitem) {
                            fwd = '<div style="font-size: 11px;">Forwarded to <a href="'+baseUrl+'/bills/invoices/view/'+invitem.decode_id+'">'+invitem.invoice_id+'</a></div>'
                        });
                    }
                    $('td:eq(7)', nRow).html('<span class="d-none">'+aData.due_amount_new+'</span><div class="text-left">$'+aData.due_amount_new+'</div><div>'+fwd+'</div>');
                    $('td:eq(8)', nRow).html('<span class="d-none">'+moment(aData.due_date_new).format('YYYYMMDD')+'</span><div class="text-left">'+aData.due_date_new+'</div>');
                    $('td:eq(9)', nRow).html('<span class="d-none">'+moment(aData.created_date_new).format('YYYYMMDD')+'</span><div class="text-left">'+aData.created_date_new+'</div>');
                    
                    if(aData.status=="Paid"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-success" style="display: inline;"></i>'+aData.status;
                    }else if(aData.status=="Partial"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-warning" style="display: inline;"></i>'+aData.status;
                    }else if(aData.status=="Overdue"){
                        var curSetatus='<i class="fas fa-circle fa-sm  mr-1 text-danger" style="display: inline;"></i>'+aData.status;
                    }else{
                        var curSetatus=aData.status;
                    }
                    $('td:eq(10)', nRow).html('<div class="text-left">'+curSetatus+'</div>');
                    if(aData.invoice_shared.length && aData.invoice_shared[0].is_viewed=="yes"){
                        $('td:eq(11)', nRow).html('<div class="text-left">'+aData.invoice_shared[0].viewed_date+'</div>');
                    }else{
                        $('td:eq(11)', nRow).html('<div class="text-left">Never</div>');
                    }
                    @can('billing_add_edit')
                    if(aData.status == "Forwarded") {
                        $('td:eq(12)', nRow).html('');
                    } else {
                        var reminder='';
                        if(aData.status=="Partial" || aData.status=="Draft" || aData.status=="Unsent"){
                            if(aData.is_lead_invoice=="no"){
                                var reminder='<span data-toggle="tooltip" data-placement="top" title="Send Reminder"><a data-toggle="modal"  data-target="#sendInvoiceReminder" data-placement="bottom" href="javascript:;"  onclick="sendInvoiceReminder('+aData.ccid+','+aData.id+');"><i class="fas fa-bell align-middle p-2"></i></a></span>';
                            }
                        }
                        var dollor='&nbsp;';
                        if(aData.status!="Paid"){
                            var dollor='<span data-toggle="tooltip" data-placement="top" title="Record Payment"><a data-toggle="modal"  data-target="#payInvoice" data-placement="bottom" href="javascript:;"  onclick="payinvoice('+aData.id+');"><i class="fas fa-dollar-sign align-middle p-2"></i></a></span>';
                        }
                        var deletes = '';
                        @can('delete_items')
                            deletes='<span data-toggle="tooltip" data-placement="top" title="Delete"><a data-toggle="modal"  data-target="#deleteInvoicePopup" data-placement="bottom" href="javascript:;"  onclick="deleteInvoice('+aData.id+');"><i class="fas fa-trash align-middle p-2"></i></a></span>';
                        @endcan
                        $('td:eq(12)', nRow).html('<div class="text-center" style="white-space: nowrap;float:right;">'+reminder+' '+dollor+' '+deletes+'</div>');
                    }
                    @else
                    $('td:eq(12)', nRow).html('');
                    @endcan
                },
                "initComplete": function(settings, json) {
                    $('[data-toggle="tooltip"]').tooltip();
                    $('#checkall').on('change', function () {
                        $('.task_checkbox').prop('checked', $(this).prop("checked"));
                        if ($('.task_checkbox:checked').length == "0") {
                            $('#actionbutton').attr('disabled', 'disabled');
                        } else {
                            $('#actionbutton').removeAttr('disabled');
                         
                        }
                    });

                    $('.task_checkbox').change(function () { //".checkbox" change 
                        if ($('.task_checkbox:checked').length == $('.task_checkbox').length) {
                            $('#checkall').prop('checked', true);
                        } else {
                            $('#checkall').prop('checked', false);
                        }
                        if ($('.task_checkbox:checked').length == "0") {
                            $('#actionbutton').attr('disabled', 'disabled');
                        } else {
                            $('#actionbutton').removeAttr('disabled');
                           
                        }
                    });
                   
                }
        });
        $('#invoiceGrid').on( 'page.dt', function () {
            $('#checkall').prop('checked', false);
            $('#actionbutton').attr('disabled', 'disabled');

        });
        $('#invoiceGrid tbody').on('change', 'input[type="checkbox"]', function(){
            if ($('.task_checkbox:checked').length == "0") {
                    $('#actionbutton').attr('disabled', 'disabled');
                } else {
                    $('#actionbutton').removeAttr('disabled');
                    
                }
        });
        
        $("#adjustmentBulkInvoiceForm").validate({
            rules: {
                amount: {
                    required: true,
                    number: true
                }
            },
            messages: {
                amount: {
                    required: "Amount can't be blank",
                    number:"Amount must be greater than 0.0"
                },
            }
        });
        
    });
  
    $("input[name='amountType']").on('change', function (e) {
        console.log($(this).val());
        if($(this).val() == 'percentage'){
            $("input[name='amount']").attr('max', '100');
        }else{
            $("input[name='amount']").attr('max', '999999999999999');
        }
    });

    $('#adjustmentBulkInvoiceForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if($("input[name='amountType']:checked").val() == 'percentage'){
                $("input[name='amount']").attr('max', '100');
            }else{
                $("input[name='amount']").attr('max', '999999999999999');
            }
            if (!$('#adjustmentBulkInvoiceForm').valid()) {
                afterLoader();
                return false;
            }
            

            var dataString = '';
            var array = [];
            $("input[class=task_checkbox]:checked").each(function (i) {
                array.push($(this).val());
            });
            
            dataString = $("#adjustmentBulkInvoiceForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/invoices/adjustmentBulkInvoiceForm", // json datasource
                data: dataString + '&invoice_id=' + JSON.stringify(array),
                beforeSend: function (xhr, settings) {
                    settings.data += '&adjustment=yes';
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
                        $("#adjustmentBulkInvoiceForm").trigger('reset');
                        $("#adjustmentBulkInvoice").modal("hide");                            
                        if(res.list != ''){
                            $("#caseID").html(res.list);
                            $("#adjustmentNotApplied").modal("show");
                            $(".my-3").html(res.list);
                        }else{
                            window.location.reload();
                        }                        
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
    function sendInvoiceReminder(id,invoice_id) {
        beforeLoader();
        $("#preloader").show();
        $("#sendInvoiceReminderArea").html('');
        $("#sendInvoiceReminderArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/sendInvoiceReminder", 
            data: {"id": id,"invoice_id":invoice_id},
            success: function (res) {
                if(typeof(res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#sendInvoiceReminderArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#sendInvoiceReminderArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },error: function (xhr, status, error) {
                $("#preloader").hide();
                $("#sendInvoiceReminderArea").html('');
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        })
    }


    $('#setBulkStatusActionForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        if (!$('#setBulkStatusActionForm').valid()) {
            beforeLoader();
            return false;
        }
        var array = [];
        $("input[class=task_checkbox]:checked").each(function (i) {
            array.push($(this).val());
        });
        var dataString = '';
        dataString = $("#setBulkStatusActionForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/setBulkStatusActionForm", // json datasource
            data: dataString + '&invoice_id=' + JSON.stringify(array),
            beforeSend: function (xhr, settings) {
                settings.data += '&bulk_action=yes';
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
    $('#setBulkSharesActionForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        if (!$('#setBulkSharesActionForm').valid()) {
            beforeLoader();
            return false;
        }
        var array = [];
        $("input[class=task_checkbox]:checked").each(function (i) {
            array.push($(this).val());
        });
        var dataString = '';
        dataString = $("#setBulkSharesActionForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/setBulkSharesActionForm", // json datasource
            data: dataString + '&invoice_id=' + JSON.stringify(array),
            beforeSend: function (xhr, settings) {
                settings.data += '&bulk_action=yes';
            },
            success: function (res) {
                afterLoader();
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
                    $("#setBulkSharesActionPopup").modal("hide");
                    if(res.list != ''){                        
                        $("#contactID").html('').html(res.list);
                        $("#sharedNotApplied").modal("show");
                    }else{
                        window.location.reload();
                    }
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

    $('#deleteBulkInvoiceForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        if (!$('#deleteBulkInvoiceForm').valid()) {
            beforeLoader();
            return false;
        }
        var array = [];
        $("input[class=task_checkbox]:checked").each(function (i) {
            array.push($(this).val());
        });
        var dataString = '';
        dataString = $("#deleteBulkInvoiceForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/deleteBulkInvoiceForm", // json datasource
            data: dataString + '&invoice_id=' + JSON.stringify(array),
            beforeSend: function (xhr, settings) {
                settings.data += '&bulk_action=yes';
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
                    if(res.view != "") {
                        $("#deleteBulkInvoice").modal("hide");
                        $("#invoiceNotDeletedArea").html(res.view);
                        $("#invoiceNotDeleted").modal("show");
                    } else {
                        window.location.reload();
                    }
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

    $('#applyTrustBalanceForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        if (!$('#applyTrustBalanceForm').valid()) {
            beforeLoader();
            return false;
        }
        var array = [];
        $("input[class=task_checkbox]:checked").each(function (i) {
            array.push($(this).val());
        });
        var dataString = '';
        dataString = $("#applyTrustBalanceForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/applyTrustBalanceForm", // json datasource
            data: dataString + '&invoice_id=' + JSON.stringify(array),
            beforeSend: function (xhr, settings) {
                settings.data += '&bulk_action=yes';
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
                    appliedTrust(res);
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

    function appliedTrust(res) {
        $("#applyTrustBalancePopup").modal("hide");
       $("#trustFundNotApplied").modal("show");
       $("#trustFundNotAppliedArea").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/trustBalanceResponse", 
            data: {"response":res},
            success: function (res) {
                if(typeof(res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#trustFundNotAppliedArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#trustFundNotAppliedArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },error: function (xhr, status, error) {
                $("#preloader").hide();
                $("#trustFundNotAppliedArea").html('');
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        })
    }

    function setBulkStatusAction(action) {
        $("#setBulkStatusActionPopup").modal("show");
        $("#stateChange").html(action);
        $("#current_status").val(action);
    }
    function setBulkSharesAction(action) {
        $("#setBulkSharesActionPopup").modal("show");
        if(action=="BC"){
            $("#shareTitle").html('Share with Billing Contact');
            var actionLink="their respective billing contacts";
        }else{
            $("#shareTitle").html('Share with All Case Contacts');
            var actionLink="all of the case contacts";
        }
        $("#tileChange").html(actionLink);
        $("#current_action").val(action);
    }

    
    // function payinvoice(id) {
    //     $('.showError').html('');
    //     beforeLoader();
    //     $("#preloader").show();
    //     $("#payInvoiceArea").html('');
    //     $("#payInvoiceArea").html('<img src="{{LOADER}}""> Loading...');
    //     $.ajax({
    //         type: "POST",
    //         url: baseUrl + "/bills/invoices/payInvoicePopup", 
    //         data: {'id':id},
    //         success: function (res) {
    //             if(typeof(res.errors) != "undefined" && res.errors !== null) {
    //                 $('.showError').html('');
    //                 var errotHtml =
    //                     '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
    //                 $('.showError').append(errotHtml);
    //                 $('.showError').show();
    //                 afterLoader();
    //                 $("#preloader").hide();
    //                 $("#payInvoiceArea").html('');
    //                 return false;
    //             } else {
    //                 afterLoader()
    //                 $("#payInvoiceArea").html(res);
    //                 $("#preloader").hide();
    //                 return true;
    //             }
    //         },error: function (xhr, status, error) {
    //             $("#preloader").hide();
    //             $("#payInvoiceArea").html('');
    //             $('.showError').html('');
    //             var errotHtml =
    //                 '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
    //             $('.showError').append(errotHtml);
    //             $('.showError').show();
    //             afterLoader();
    //         }
    //     })
    // }

    function deleteBulkExpense(id) {
        $("#deleteInvoicePopup").modal("show");
        $("#delete_invoice_id").val(id);
    }
    function deleteBulkInvoice() {
        $("#deleteBulkInvoice").modal("show");
    }
    function applyTrustBalance() {
        $("#applyTrustBalancePopup").modal("show");
    }
    function adjustmentBulkInvoice() {
        $("#adjustmentBulkInvoice").modal("show");
    }
    function setBulkEnableOnlinePaymentPopup() {
        $("#setBulkEnableOnlinePaymentPopup").modal("show");
    }
    
    function downloadBulkInvoice(){
        $("#preloader").show();
        var array = [];
        $("input[class=task_checkbox]:checked").each(function (i) {
            array.push($(this).val());
        });
        var dataString = '';
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/downloadBulkInvoice", // json datasource
            data: dataString + '&invoice_id=' + JSON.stringify(array),
            beforeSend: function (xhr, settings) {
                settings.data += '&bulk_action=yes';
            },
            success: function (res) {
                $("#preloader").hide();
                    window.open(res.url, '_blank');
            },
            error: function (xhr, status, error) {
                $("#preloader").hide();
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                alert(errotHtml);
              
            }
        });
    }
    $('#checkall').prop('checked', false);
    $('#actionbutton').attr('disabled', 'disabled');

    $('.global_search').on('select2:select', function (e) {
        $(".filterBy").submit();
    });
    $('.global_search').on('select2:unselecting', function (e) {
        $(".filterBy").submit();
    });

    $('#adjustmentNotApplied,#trustFundNotApplied,#invoiceNotDeleted,#creditFundNotApplied,#sharedNotApplied').on('hidden.bs.modal', function () {
        window.location.reload();
    });

    // For apply credit fund
    function applyCreditBalance() {
        $("#applyCreditBalancePopup").modal("show");
    }

    $('#applyCreditBalanceForm').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        if (!$('#applyCreditBalanceForm').valid()) {
            beforeLoader();
            return false;
        }
        var array = [];
        $("input[class=task_checkbox]:checked").each(function (i) {
            array.push($(this).val());
        });
        var dataString = '';
        dataString = $("#applyCreditBalanceForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/applyCreditBalanceForm", // json datasource
            data: dataString + '&invoice_id=' + JSON.stringify(array),
            beforeSend: function (xhr, settings) {
                settings.data += '&bulk_action=yes';
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
                    appliedCredit(res);
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

    function appliedCredit(res) {
        $("#applyCreditBalancePopup").modal("hide");
       $("#creditFundNotApplied").modal("show");
       $("#creditFundNotAppliedArea").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/invoices/creditBalanceResponse", 
            data: {"response":res},
            success: function (res) {
                if(typeof(res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal server error. Please reload the screen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $("#preloader").hide();
                    $("#creditFundNotAppliedArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#creditFundNotAppliedArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },error: function (xhr, status, error) {
                $("#preloader").hide();
                $("#creditFundNotAppliedArea").html('');
                $('.showError').html('');
                var errotHtml =
                    '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                afterLoader();
            }
        })
    }
</script>
@stop
@endsection
