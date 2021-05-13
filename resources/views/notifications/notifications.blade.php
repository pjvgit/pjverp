@extends('layouts.master')
@section('title', config('app.name').' :: Dashboard')
@section('main-content')
<?php
 $CommonController= new App\Http\Controllers\CommonController();

 ?>
<div class="row">

    <div class="col-12">
        <div id="activity-container">
            <div class="dashboard-activity-container">
                <div class="card">
                    <div class="card-header">
                        <h4 class="w-80 float-left"> Recent Activity</h4>  
                        <div class="files-per-page-selector float-right" style="white-space: nowrap; ">
                            <label class="mr-2">Rows Per Page:</label>
                            <select id="per_page" onchange="onchangeLength();" name="per_page" class="custom-select w-auto">
                                <option value="10" selected="">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="activity-card-body card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link  active show" id="all-tab" data-toggle="tab" href="#allEntry"
                                    onclick="loadAllNotification();" role="tab" aria-controls="homeBasic"
                                    aria-selected="false">All</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="invoice-tab" data-toggle="tab"
                                    onclick="loadInvoiceNotification(1);" href="#invoiceEntry" role="tab"
                                    aria-controls="profileBasic" aria-selected="true">Invoices</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="deposit-tab" data-toggle="tab"
                                    onclick="loadDepositNotification(1);" href="#depositActivity" role="tab"
                                    aria-controls="contactBasic" aria-selected="false">Deposit Request</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="time-tab" data-toggle="tab"
                                    onclick="loadTimeEntryNotification(1);" href="#timeEntry" role="tab"
                                    aria-controls="contactBasic" aria-selected="false">Time Entries</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" id="expense-tab" data-toggle="tab"
                                    onclick="loadExpensesNotification(1);" href="#expenses" role="tab"
                                    aria-controls="contactBasic" aria-selected="false">Expenses</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="event-tab" data-toggle="tab"
                                    onclick="loadEventsNotification(1);" href="#eventEntry" role="tab"
                                    aria-controls="contactBasic" aria-selected="false">Events</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="document-tab" onclick="loadDocumentNotification(1);"
                                    data-toggle="tab" href="#documentActivity" role="tab" aria-controls="contactBasic"
                                    aria-selected="false">Documents</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="task-tab" onclick="loadTasksNotification(1);"
                                    data-toggle="tab" href="#taskActivity" role="tab" aria-controls="contactBasic"
                                    aria-selected="false">Tasks</a>
                            </li>
                        </ul>
                        <div class="notifications_holder">
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade active show" id="allEntry" role="tabpanel"
                                    aria-labelledby="home-basic-tab">
                                    
                                </div>
                                <div class="tab-pane fade" id="invoiceEntry" role="tabpanel"
                                    aria-labelledby="profile-basic-tab">
                                    No recent activity available.


                                </div>
                                <div class="tab-pane fade" id="depositActivity" role="tabpanel"
                                aria-labelledby="profile-basic-tab">
                                No recent activity available.


                            </div>

                                <div class="tab-pane fade" id="timeEntry" role="tabpanel"
                                    aria-labelledby="profile-basic-tab">
                                    No recent activity available.


                                </div>
                                <div class="tab-pane fade" id="expenses" role="tabpanel"
                                    aria-labelledby="profile-basic-tab">
                                    No recent activity available.


                                </div>
                                <div class="tab-pane fade" id="eventEntry" role="tabpanel"
                                    aria-labelledby="contact-basic-tab">
                                    No recent activity available.

                                </div>
                                <div class="tab-pane fade" id="documentActivity" role="tabpanel"
                                    aria-labelledby="contact-basic-tab">
                                    No recent activity available.

                                </div>

                                <div class="tab-pane fade" id="taskActivity" role="tabpanel"
                                    aria-labelledby="contact-basic-tab">
                                    No recent activity available.

                                </div>
                            </div>
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('commonPopup.popup_without_param_code')

@endsection
@section('page-js')
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', '.AllNotify .pagination a', function(event){
            event.preventDefault(); 
            var page = $(this).attr('href').split('page=')[1];
            loadAllNotification(page);
        });

        $(document).on('click', '.InvoiceNotify .pagination a', function(event){
            event.preventDefault(); 
            var page = $(this).attr('href').split('page=')[1];
            loadInvoiceNotification(page);
        });

        $(document).on('click', '.TimeEntryNotify .pagination a', function(event){
            event.preventDefault(); 
            var page = $(this).attr('href').split('page=')[1];
            loadTimeEntryNotification(page);
        });

        $(document).on('click', '.DepositNotify .pagination a', function(event){
            event.preventDefault(); 
            var page = $(this).attr('href').split('page=')[1];
            loadDepositNotification(page);
        });

        $(document).on('click', '.ExpensesNotify .pagination a', function(event){
            event.preventDefault(); 
            var page = $(this).attr('href').split('page=')[1];
            loadExpensesNotification(page);
        });
        
        $(document).on('click', '.EventsNotify .pagination a', function(event){
            event.preventDefault(); 
            var page = $(this).attr('href').split('page=')[1];
            loadEventsNotification(page);
        });
        $(document).on('click', '.TasksNotify .pagination a', function(event){
            event.preventDefault(); 
            var page = $(this).attr('href').split('page=')[1];
            loadTasksNotification(page);
        });

        $(document).on('click', '.DocumentNotify .pagination a', function(event){
            event.preventDefault(); 
            var page = $(this).attr('href').split('page=')[1];
            loadDocumentNotification(page);
        });
    });
    function onchangeLength(){
        let activeTav=$("ul.nav-tabs li a.active").attr("id");
        if(activeTav=="all-tab"){
            loadAllNotification(1);
        }else  if(activeTav=="invoice-tab"){
            loadInvoiceNotification(1);
        }else if(activeTav=="time-tab"){
            loadTimeEntryNotification(1);
        }else if(activeTav=="expense-tab"){
            loadExpensesNotification(1);
        }else if(activeTav=="event-tab"){
            loadEventsNotification(1);
        }else if(activeTav=="task-tab"){
            loadTasksNotification(1);
        }else if(activeTav=="deposit-tab"){
            loadDepositNotification(1);
        }else if(activeTav=="document-tab"){
            loadDocumentNotification(1);
        }
    }
    function loadAllNotification(page=null) {
       
        $("#innerLoader").css('display', 'none');
        $("#allEntry").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/notifications/loadAllNotification?per_page="+$("#per_page").val()+"&page="+page, // json datasource
                data: 'bulkload',
                success: function (res) {
                    $("#allEntry").html(res);
                    return false;
                }
            })
        })
    }
    function loadInvoiceNotification(page=null) {
        $("#innerLoader").css('display', 'none');
        $("#invoiceEntry").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/notifications/loadInvoiceNotification?per_page="+$("#per_page").val()+"&page="+page, // json datasource
                data: 'bulkload',
                success: function (res) {
                    $("#invoiceEntry").html(res);
                    return false;
                }
            })
        })
    }
    function loadTimeEntryNotification(page=null) {
        $("#innerLoader").css('display', 'none');
        $("#timeEntry").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/notifications/loadTimeEntryNotification?per_page="+$("#per_page").val()+"&page="+page, // json datasource
                data: 'bulkload',
                success: function (res) {
                    $("#timeEntry").html(res);
                    return false;
                }
            })
        })
    }
    function loadExpensesNotification(page=null) {
        $("#innerLoader").css('display', 'none');
        $("#expenses").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/notifications/loadExpensesNotification?per_page="+$("#per_page").val()+"&page="+page, // json datasource
                data: 'bulkload',
                success: function (res) {
                    $("#expenses").html(res);
                    return false;
                }
            })
        })
    }
    function loadEventsNotification(page=null) {
        $("#innerLoader").css('display', 'none');
        $("#eventEntry").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/notifications/loadEventsNotification?per_page="+$("#per_page").val()+"&page="+page, // json datasource
                data: 'bulkload',
                success: function (res) {
                    $("#eventEntry").html(res);
                    return false;
                }
            })
        })
    }
    function loadTasksNotification(page=null) {
        $("#innerLoader").css('display', 'none');
        $("#taskActivity").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/notifications/loadTasksNotification?per_page="+$("#per_page").val()+"&page="+page, // json datasource
                data: 'bulkload',
                success: function (res) {
                    $("#taskActivity").html(res);
                    return false;
                }
            })
        })
    }
    function loadDepositNotification(page=null) {
        $("#innerLoader").css('display', 'none');
        $("#depositActivity").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/notifications/loadDepositRequestsNotification?per_page="+$("#per_page").val()+"&page="+page, // json datasource
                data: 'bulkload',
                success: function (res) {
                    $("#depositActivity").html(res);
                    return false;
                }
            })
        })
    }
    function loadDocumentNotification(page=null) {
        $("#innerLoader").css('display', 'none');
        $("#documentActivity").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/notifications/loadDocumentNotification?per_page="+$("#per_page").val()+"&page="+page, // json datasource
                data: 'bulkload',
                success: function (res) {
                    $("#documentActivity").html(res);
                    return false;
                }
            })
        })
    }
    loadAllNotification(1);
</script>
@endsection
