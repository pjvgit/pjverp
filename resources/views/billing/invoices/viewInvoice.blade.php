@extends('layouts.master')
{{-- <?php $s = sprintf('%06d', $findInvoice->id);?> --}}
@section('title', 'Invoice #'.$invoiceNo.' - Invoices - Billing')
@section('main-content')
@include('billing.submenu')
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex align-items-center pl-4 pb-4">
                    <h3> Invoice #{{$invoiceNo}} </h3>
                    <input type="hidden" value="{{ @$findInvoice->id }}" id="invoice_id">
                    <div class="ml-auto d-flex align-items-center flex-row-reverse">
                        @if($findInvoice->status != "Forwarded")
                        <div id="receive_payment_button" class="invoice-show-page-button pl-1">
                          <a class="btn btn-success receive-payment-action m-1" id="record-payment-btn" data-toggle="modal"  data-target="#payInvoice" onclick="payinvoice('{{$findInvoice->invoice_unique_token}}');" data-placement="bottom" href="javascript:;"   title="Edit" data-testid="edit-button" class="btn btn-link">Record Payment</a>
                        </div>

                        <div class="pl-1">
                            {{-- <a class="btn btn-outline-secondary  m-1" href="{{BASE_URL}}bills/invoices/{{base64_encode($findInvoice->id)}}/edit?token={{base64_encode($findInvoice->id)}}">Edit</a> --}}
                            <a class="btn btn-outline-secondary  m-1" href="{{ route('bills/invoices/edit', base64_encode($findInvoice->id)) }}?token={{base64_encode($findInvoice->id)}}">Edit</a>
                        </div>
                        @endif
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
                        @if($findInvoice->status != "Forwarded")
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
                        @endif
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
                        @if($findInvoice->status != "Forwarded")
                        <a id="delete-bill" class="btn btn-lg btn-link px-2 text-black-50" data-toggle="modal"
                            data-target="#deleteInvoicePopup" data-placement="bottom" href="javascript:;">
                            <i class="fas fa-trash test-delete-bill" data-bill-id="12211253" data-toggle="tooltip"
                                data-placement="top" title="" data-original-title="Delete"
                                onclick="deleteInvoice({{$findInvoice->id}})"></i>
                        </a>
                        @endif
                    </div>

                </div>
            </div>
            <div id="history_page" class="history-page">
                <div class="border" id="invoice_activity_history_div">
                    @include('billing.invoices.partials.load_invoice_activity_history')
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
                        <div id="invoice_total_div">
                            @include('billing.invoices.partials.load_invoice_detail')
                        </div>
                        <div style="padding: 20px;">
                            <br>
                            <div id="payment_history_div">
                            @include('billing.invoices.partials.load_invoice_payment_history')
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

// Scroll to payment plan
$(".scrollTo").on('click', function(e) {
    e.preventDefault();
    var target = $(this).attr('href');
    $('html, body').animate({
    scrollTop: ($(target).offset().top - 150)
    }, 2000);
});
</script>
<script src="{{ asset('assets\js\custom\invoice\viewinvoice.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
@stop
@endsection
