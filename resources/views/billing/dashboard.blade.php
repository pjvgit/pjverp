@extends('layouts.master')
@section('title', 'Time & Billing Dashboard')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 
?>
@include('billing.submenu')
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-md-12">
        <div id="main_content" class="pb-5">
            <div class="row">
                <div class="col-7">
                    <div class="card mb-3 billing-actions-container">
                        <div class="card-header">
                            <h4 class="">Billing Actions</h4>
                        </div>
                        <div class="card-body">
                            <table class="w-100 text-center">
                                <tbody>
                                    <tr>
                                        @can('billing_add_edit') 
                                        <td class="pb-4">
                                            <a class="add-invoice billing-dashboard-add-invoice"
                                                href="{{route('bills/invoices/open')}}">
                                                <img class="d-block mx-auto"
                                                    src="{{ asset('svg/invoice_add.svg') }}" width="42"
                                                    height="42">Create An Invoice</a>
                                        </td>
                                        @else
                                        <td class="pb-4">
                                            <img class="d-block mx-auto" src="{{ asset('icon/invoice_add__inactive.svg') }}" width="42" height="42">
                                            <div class="text-muted">Create An Invoice</div>
                                        </td>
                                        @endcan

                                        @can('billing_add_edit') 
                                        <td class="pb-4">
                                            <a data-toggle="modal" data-target="#recordPayment" data-placement="bottom"
                                                href="javascript:;" onclick="recordPayment();"
                                                id="dashboard-record-payment">
                                                <img class="d-block mx-auto" src="{{ asset('svg/payment.svg') }}"
                                                    width="42" height="42">Make Invoice Payment
                                            </a>
                                        </td>
                                        @else
                                        <td class="pb-4">
                                            <img class="d-block mx-auto" src="{{ asset('icon/payment__inactive.svg') }}" width="42" height="42">
                                            <div class="text-muted">Make Invoice Payment</div>
                                        </td>
                                        @endcan

                                        @can('billing_add_edit') 
                                        <td class="pb-4">
                                            <a data-toggle="modal" data-target="#depositIntoTrust"
                                                data-placement="bottom" href="javascript:;"
                                                onclick="depositIntoTrust();">
                                                <img class="d-block mx-auto" src="{{ asset('svg/trust.svg') }}"
                                                    width="42" height="42">Deposit Into Trust
                                            </a>
                                        </td>
                                        @else
                                        <td class="pb-4">
                                            <img class="d-block mx-auto" src="{{ asset('icon/trust__inactive.svg') }}" width="42" height="42">
                                            <div class="text-muted">Deposit Into Trust</div>
                                        </td>
                                        @endcan

                                        @can('billing_add_edit') 
                                        <!-- TODO: change the checking here once we do ACH -->
                                        <td class="pb-4">
                                            <a request-funds-btn" data-toggle="modal" data-target="#addRequestFund"
                                                onclick="addRequestFundPopup();" href="javascript:;">
                                                <span><img class="d-block mx-auto"
                                                        src="{{ asset('svg/request.svg') }}" width="42" height="42">
                                                    Request Trust Fund
                                                </span>
                                            </a>
                                        </td>@else
                                        <td class="pb-4">
                                            <img class="d-block mx-auto" src="{{ asset('icon/request__inactive.svg') }}" width="42" height="42">
                                            <div class="text-muted">Request Trust Fund</div>
                                        </td>
                                        @endcan
                                    </tr>
                                    <tr>
                                        @can('billing_add_edit') 
                                        <td>
                                            <a data-toggle="modal" data-target="#loadTimeEntryPopup"
                                                data-placement="bottom" href="javascript:;"
                                                id="billing-dashboard-add-time-entry"
                                                class="add-time-entry billing-dashboard-add-time-entry"
                                                onclick="loadTimeEntryPopup();">
                                                <img class="d-block mx-auto" src="{{ asset('svg/time_entry.svg') }}"
                                                    width="42" height="42">Add Time Entry
                                            </a>
                                        </td>
                                        @else
                                        <td class="pb-4">
                                            <img class="d-block mx-auto" src="{{ asset('icon/time_entry__inactive.svg') }}" width="42" height="42">
                                            <div class="text-muted">Add Time Entry</div>
                                        </td>
                                        @endcan

                                        @can('billing_add_edit') 
                                        <td>
                                            <a data-toggle="modal" data-target="#loadExpenseEntryPopup"
                                                data-placement="bottom" href="javascript:;"
                                                onclick="loadExpenseEntryPopup();" class="add-expense-button">
                                                <img class="d-block mx-auto" src="{{ asset('svg/expense.svg') }}"
                                                    width="42" height="42">Add Expense
                                            </a>
                                        </td>
                                        @else
                                        <td class="pb-4">
                                            <img class="d-block mx-auto" src="{{ asset('icon/expense__inactive.svg') }}" width="42" height="42">
                                            <div class="text-muted">Add Expense</div>
                                        </td>
                                        @endcan

                                        @if(getInvoiceSetting() && getInvoiceSetting()->is_non_trust_retainers_credit_account == "yes" && auth()->user()->hasDirectPermission('billing_add_edit'))
                                            <td>
                                            
                                            <a data-toggle="modal" data-target="#loadDepositIntoCreditPopup" data-placement="bottom" href="javascript:;"
                                                onclick="loadDepositIntoCredit(this);" class="add-expense-button" data-auth-user-id="{{ auth()->id() }}">
                                                <img class="d-block mx-auto" src="{{ asset('svg/credit_active.svg') }}" width="42" height="42">
                                                <div >Deposit Into Credit</div>
                                                </a>
                                            </td>
                                        @else
                                        <td>
                                            <img class="d-block mx-auto" src="{{ asset('svg/credit__inactive.svg') }}" width="42"
                                                height="42">
                                            <div class="text-muted">Deposit Into Credit</div>
                                        </td>
                                        @endif
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="activity-container">
                        <div class="dashboard-activity-container">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Recent Activity</h4>
                                </div>
                                <div class="activity-card-body card-body">
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link  active show" id="home-basic-tab" data-toggle="tab"
                                                href="#allEntry" onclick="loadAllActivity();" role="tab"
                                                aria-controls="homeBasic" aria-selected="false">All</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="profile-basic-tab" data-toggle="tab"
                                                onclick="loadInvoiceActivity();" href="#invoiceEntry" role="tab"
                                                aria-controls="profileBasic" aria-selected="true">Invoices</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="contact-basic-tab" data-toggle="tab"
                                                onclick="loadTimeEntryActivity();" href="#timeEntry" role="tab"
                                                aria-controls="contactBasic" aria-selected="false">Time Entries</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="contact-basic-tab" onclick="loadExpenseActivity();"
                                                data-toggle="tab" href="#expensesEntry" role="tab"
                                                aria-controls="contactBasic" aria-selected="false">Expenses</a>
                                        </li>
                                    </ul>
                                    <div class="notifications_holder">
                                        <div class="tab-content" id="myTabContent">
                                            <div class="tab-pane fade active show" id="allEntry" role="tabpanel"
                                                aria-labelledby="home-basic-tab">

                                            </div>
                                            <div class="tab-pane fade" id="invoiceEntry" role="tabpanel"
                                                aria-labelledby="profile-basic-tab">

                                            </div>
                                            <div class="tab-pane fade" id="timeEntry" role="tabpanel"
                                                aria-labelledby="contact-basic-tab">
                                            </div>
                                            <div class="tab-pane fade" id="expensesEntry" role="tabpanel"
                                                aria-labelledby="contact-basic-tab">


                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-3">
                                        <a href="{{ route('notifications') }}" class="pendo-view-all-activity">View all
                                            activity</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-5">
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4>My Timesheet</h4>
                                    <a data-toggle="modal" data-target="#loadAddFeedBack" data-placement="bottom" href="javascript::void(0);">
                                        <button onclick="setFeedBackForm('single','Timesheet Calender');" type="button" class="feedback-button mr-2 text-black-50 btn btn-link">Tell us what you think</button>
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="timesheet-calendar-container">
                                        <input type="hidden" value="" id="currentBox">
                                     
                                        <div role="group" class="view-btn-group mb-2 mr-auto btn-group float-right">
                                            <button type="button" class="btn btn-outline-secondary btn-sm hamburger" onclick="loadCalender('b');">Billable</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm hamburger" onclick="loadCalender('nb');" >Non-Billable</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm hamburger active" onclick="loadCalender('all');">All</button>
                                        </div>
                                        <div class="week-view mb-4 ">
                                            <div class="totals-calendar">
                                                <div class="table-responsive">
                                                    <div id="loadCalender">
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="loadSummary">
                                            </div>
                                        </div>
                                    </div>
                                    <div id="timesheet_overview">
                                    </div>
                                    <div class="d-flex justify-content-center m2">
                                        <a href="#">Find more billable time with the Smart Time Finder</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="trust-account-overview">
                        <div class="card" style="height: 100%; width: 100%;">
                            <div class="p-0 card-body">
                                <div class="header card-header">
                                    <h4 class="d-flex flex-row justify-content-between align-items-center">
                                        <span>Trust Account Overview</span><button type="button"
                                            class="feedback-button btn btn-link">Want to see more graphs like
                                            this?</button></h4>
                                </div>
                                <div class="p-2 card-body" id="trustAccountOverview">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="invoice-overview mt-2" data-time-zone="America/Los_Angeles">
                        <div class="card" style="height: 100%; width: 100%;">
                            <div class="p-0 card-body">
                                <div class="header card-header">
                                    <h4 class="d-flex flex-row justify-content-between align-items-center">
                                        <span>Invoice Overview</span></h4>
                                </div>
                                <div class="chart-filter d-flex ml-2 mt-2">
                                    <div class="date-range form-group d-flex mb-0" style="width: 200px;">
                                        <?php
                                        $startDate=date('m/d/Y',strtotime("first day of this month"));
                                        $endDate=convertUTCToUserTimeZone('dateOnly');
                                        ?>
                                        <input type="text" class="form-control" id="daterange" name="date_range"
                                            value="{{$startDate}} - {{$endDate}}" placeholder="" />
                                        <div class="align-self-center">
                                            <span id="why-date-range" data-toggle="tooltip" data-placement="right"
                                                title="" data-original-title="Filter by invoice date ">
                                                <i aria-hidden="true" class="fa fa-question-circle ml-1"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-2 card-body" id="overViewInvoice">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="recordPayment" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Record Payment</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="recordPaymentArea">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- <div id="depositIntoTrust" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Deposit Into Trust</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="depositIntoTrustArea">
                </div>
            </div>
        </div>
    </div>
</div> --}}
{{-- <div id="loadDepositIntoCreditPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Deposit Into Non-Trust Credit Account</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="showError" style="display:none"></div>
                <div id="loadDepositIntoCreditArea">
                </div>
            </div>
        </div>
    </div>
</div> --}}
@include('client_dashboard.billing.credit_history_modal')

<!-- Modals -->
<div id="loadAllTimeEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timeEntryTitle"></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="loadAllTimeEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadEditTimeEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="loadEditTimeEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="deleteTimeEntry" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteTimeEntryForm" id="deleteTimeEntryForm" name="deleteTimeEntryForm" method="POST">
            @csrf
            <input type="hidden" value="" name="entry_id" id="delete_entry_id">
            <input type="hidden" value="timesheet" name="from" id="from">
            
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Time Entry</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row">
                        <div class="col-md-12" id="confirmAccess">
                            Are you sure you want to delete this time entry?
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

@include('commonPopup.popup_without_param_code')
@include('client_dashboard.billing.modal')
<style>
    .progress-bar {
        padding: auto;
    }

</style>
@section('page-js-inner')
<script src="{{ asset('assets\js\custom\feedback.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $(".country").select2({
            placeholder: "Select country",
            theme: "classic",
            allowClear: true
        });
        // $('#calendarq').fullCalendar({  defaultView: 'agendaWeek'});
    });

    function recordPayment() {
        $('.showError').html('');
        
        $("#preloader").show();
        $("#recordPaymentArea").html('');
        $("#recordPaymentArea").html('Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/recordPayment",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#preloader").hide();
                    $("#recordPaymentArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#recordPaymentArea").html(res);
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
              
            }
        })
    }

    /* function depositIntoTrust() {
        $('.showError').html('');
        
        $("#preloader").show();
        $("#depositIntoTrustArea").html('');
        $("#depositIntoTrustArea").html('Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/depositIntoTrust",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#preloader").hide();
                    $("#depositIntoTrustArea").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#depositIntoTrustArea").html(res);
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
              
            }
        })
    } */

    function loadExpenseActivity() {
        $('.showError').html('');
        
        $("#expensesEntry").html('');
        $("#expensesEntry").html('<img src="{{ asset("images/ajax_arrows.gif") }}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/notifications/loadExpensesNotification",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#expensesEntry").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#expensesEntry").html(res);
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
              
            }
        })
    }

    function loadTimeEntryActivity() {
        $('.showError').html('');
        
        $("#timeEntry").html('');
        $("#timeEntry").html('<img src="{{ asset("images/ajax_arrows.gif") }}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/notifications/loadTimeEntryNotification",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#timeEntry").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#timeEntry").html(res);
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
              
            }
        })
    }

    function loadInvoiceActivity() {
        $('.showError').html('');
        
        $("#invoiceEntry").html('');
        $("#invoiceEntry").html('<img src="{{ asset("images/ajax_arrows.gif") }}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/notifications/loadInvoiceNotification",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#invoiceEntry").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#invoiceEntry").html(res);
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
              
            }
        })
    }

    function loadAllActivity() {
        $('.showError').html('');
        $("#allEntry").html('');
        $("#allEntry").html('<img src="{{ asset("images/ajax_arrows.gif") }}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/loadAllHistory",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $("#allEntry").html('');
                    return false;
                } else {
                    $("#allEntry").html(res);
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
            }
        })
    }

    function loadTimeEntryOverview() {
        $('.showError').html('');
        
        $("#timesheet_overview").html('');
        $("#timesheet_overview").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/loadTimeEntryOverview",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#timesheet_overview").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#timesheet_overview").html(res);
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
              
            }
        })
    }

    function overViewInvoice(d) {
        $('.showError').html('');
        
        $("#overViewInvoice").html('');
        $("#overViewInvoice").html('<img src="{{ asset("images/ajax_arrows.gif") }}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/loadInvoiceOverview",
            data: {
                "id": null,
                'fulldate': d
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#overViewInvoice").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#overViewInvoice").html(res);
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
              
            }
        })
    }

    function loadTrustAccountOverview() {
        $('.showError').html('');
        
        $("#trustAccountOverview").html('');
        $("#trustAccountOverview").html('<img src="{{ asset("images/ajax_arrows.gif") }}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/loadTrustAccountOverview",
            data: {
                "id": null
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#trustAccountOverview").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#trustAccountOverview").html(res);
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
              
            }
        })
    }
    /* function loadDepositIntoCredit() {
        $('.showError').html('');
        $("#loadDepositIntoCreditArea").html('');
        $("#loadDepositIntoCreditArea").html('<img src="{{LOADER}}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/loadDepositIntoCredit",
            data: {"logged_in_user": "{{Auth::User()->id}}"},
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml ='<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $("#loadDepositIntoCreditArea").html('');
                    return false;
                } else {
                    afterLoader();
                    $("#loadDepositIntoCreditArea").html(res);
                    $("#preloader").hide();
                    return true;
                }
            },
            error: function (xhr, status, error) {
                $('.showError').html('');
                var errotHtml ='<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                $('.showError').append(errotHtml);
                $('.showError').show();
                $("#loadDepositIntoCreditArea").html('');

              
            }
        })
    } */

    function loadCalender(type=null) {
        $("#currentBox").val(type)
        $('.showError').html('');
        
        $("#loadCalender").html('<img src="{{ asset("images/ajax_arrows.gif") }}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/loadCalender",
            data: {
                "type": type
            },
            success: function (res) {
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#loadCalender").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#loadCalender").html(res);
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
              
            }
        })
    }
    function loadSummary(start,end,type) {
        $('.showError').html('');
        
        $("#loadSummary").html('<img src="{{ asset("images/ajax_arrows.gif") }}"> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/bills/dashboard/loadSummary",
            data: {
                start: start,
                end: end,
                type:type
            },
            success: function (res) {
            
                if (typeof (res.errors) != "undefined" && res.errors !== null) {
                    $('.showError').html('');
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                  
                    $("#loadSummary").html('');
                    return false;
                } else {
                    afterLoader()
                    $("#loadSummary").html(res);
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
              
            }
        })
    }
    function callBakeC(start,end,type){
        loadSummary(start,end,type);
    }
    function loadTimeEntryPopup(currentDate) {
        $("#preloader").show();
        $("#loadTimeEntry").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/loadTimeEntryPopup", // json datasource
                data: {"from":"timesheet",  "curDate":currentDate,},
                success: function (res) {
                    $("#addTimeEntry").html('');
                    $("#addTimeEntry").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

     function loadTimeEntry(currentDate){
        // Store
        localStorage.setItem("curDate", currentDate);
        $("#loadAllTimeEntryPopup").modal("show");
        $("#preloader").show();
        $("#loadAllTimeEntryPopupArea").html('<img src="{{LOADER}}"> Loading...');
      
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/time_entries/timesheet_calendar/loadAllSavedTimeEntry", // json datasource
                data: {
                    "currentDate":currentDate,
                    "forUser":$("#staff_user_main_form").val()
                },
                success: function (res) {
                    $("#loadAllTimeEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadTimeEntryByDate(curdate,type) {
        $("#preloader").show();
        $("#loadAllTimeEntryPopupArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/time_entries/timesheet_calendar/reloadTimeEntry", // json datasource
                data: {'curdate':curdate,'type':type, "forUser":$("#staff_user_main_form").val()},
                success: function (res) {
                    $("#loadAllTimeEntryPopupArea").html('');
                    $("#loadAllTimeEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadEditTimeEntryPopup(id) {
            $("#preloader").show();
            $("#loadEditTimeEntryPopupArea").html('<img src="{{LOADER}}"> Loading...');
            $(function () {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/bills/loadEditTimeEntryPopup", // json datasource
                    data: {
                        'entry_id': id,"from":"timesheet",
                    },
                    success: function (res) {
                        $("#loadEditTimeEntryPopupArea").html('');
                        $("#loadEditTimeEntryPopupArea").html(res);
                        $("#preloader").hide();
                    }
                })
            })
        }

        function deleteTimeEntry(id) {
            $("#deleteTimeEntry").modal("show");
            $("#delete_entry_id").val(id);
        }
    $('#loadTimeEntryPopup,#deleteTimeEntry,#loadEditTimeEntryPopup').on('hidden.bs.modal', function () {
        var currentDate=localStorage.getItem("curDate");
        loadTimeEntry(currentDate);
    });
    $('#loadAllTimeEntryPopup').on('hidden.bs.modal', function () {
        localStorage.setItem("curDate", '');
        var CurType=$("#currentBox").val();
        loadCalender(CurType);
    });
    setTimeout(function () {
        $('[data-toggle="tooltip"]').tooltip();
        loadAllActivity();
        loadTimeEntryOverview();
        overViewInvoice('');
        loadTrustAccountOverview();
        loadCalender('all');
    }, 1000);
    
    
    $("button.hamburger").click(function(){
        $('.hamburger.active').removeClass('active')
        $(this).addClass('active');
    });
   
</script>
<script src="{{ asset('assets\js\custom\client\creditfund.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script src="{{ asset('assets\js\custom\client\fundrequest.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
<script src="{{ asset('assets\js\custom\client\trusthistory.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
@stop
@endsection
