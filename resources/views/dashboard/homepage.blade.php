@extends('layouts.master')
@section('title', config('app.name').' :: Dashboard')
@section('main-content')
<?php
 $CommonController= new App\Http\Controllers\CommonController();

 ?>
<div class="row">
    <?php 
    if(Auth::User()->welcome_page_widget_is_display=="yes"){?>
    <div class="col-12 mb-3 p-2 card border-top-0 border-right-0 border-left-0 rounded-0" id="widgetArea">

        <div class="row no-gutters mb-3">
            <div class="strong d-flex align-items-center justify-content-center col-4 offset-4">
                <strong>Welcome to {{config('app.name')}}, {{Auth::User()->first_name}}
                    {{Auth::User()->last_name}}</strong>
            </div>
            <div class="d-flex justify-content-end align-items-center col-4">
                <div class="d-flex align-items-center">
                    <div class="mr-1 dismissForever" id="dismissForever" style="display: none;">
                        <div>
                            <span>Are you sure?</span>
                            <button class="btn btn-primary btn-sm ml-1 confirm">Dismiss Forever</button>
                            <button class="btn btn-secondary btn-sm ml-1 cancel">No</button>
                        </div>

                    </div>
                    <span class="dismissForever">|</span>
                </div>
                <div class="align-items-center" id="dismissButton">
                    <button id="dismiss-welcome-panel" onclick="dismissModal();"
                        class="btn btn-link btn-sm pendo-dismiss-welcome">Dismiss</button>
                    <span>|</span>
                </div>

                <button id="closeButton" onclick="openFun();" class="pendo-collapse-welcome btn btn-link btn-sm"
                    type="button">
                    Collapse panel<i class="ml-1 fas fa-chevron-up"></i>
                </button>

                <button id="openButton" onclick="closeFun();" class="pendo-open-welcome btn btn-link btn-sm"
                    type="button">
                    Open panel<i class="ml-1 fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <div class="welcome-content mr-5 ml-5" id="showContent">
            <div class="col-12 intro-panel">
                <div class="card-deck justify-content-center">
                    <div class="card text-center ">
                        <div class="card-highlight bg-dark"></div>
                        <div class="card-body">
                            <h5 class="mt-3">Get a Personalized Demo</h5>
                            <div class="icons"><i class="brittany-icon rounded-circle"></i></div>
                            <div class="description">I know you are busy – let me demo LegalCase for you in 15 minutes.
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 mt-auto mb-2">
                            <button type="button"
                                class="btn btn-primary pendo-schedule-demo-welcome-block test-welcome-block-schedule-demo">Schedule
                                a Demo</button></div>
                    </div>
                    @can('add_firm_user')
                    <div class="card text-center ">
                        <div class="card-highlight bg-dark"></div>
                        <div class="card-body">
                            <h5 class="mt-3">Invite Firm Members</h5>
                            <div class="icons"><i class="welcome-panel-add-firm-user"></i></div>
                            <div class="description">Invite the entire firm so everyone can see case information from
                                anywhere.</div>
                        </div>
                        <div class="card-footer bg-white border-top-0 mt-auto  mb-2">
                            <button type="button" data-toggle="modal" data-target="#AddBulkUserModal"
                            data-placement="bottom" href="javascript:;" onclick="AddBulkUserModal();"
                            class="btn btn-outline-primary pendo-welcome-block-add-case">Invite Firm Members</button>
                        </div>
                    </div>
                    @endcan
                    @can('case_add_edit')
                    <div class="card text-center ">
                        <div class="card-highlight bg-dark"></div>
                        <div class="card-body">
                            <h5 class="mt-3">Add a Case</h5>
                            <div class="icons"><i class="welcome-panel-add-case"></i></div>
                            <div class="description">A case is a digital file that holds all events, documents, tasks,
                                bills, etc.,
                                related to a client matter.</div>
                        </div>
                        <div class="card-footer bg-white border-top-0 mt-auto  mb-2">
                            <button type="button" data-toggle="modal" data-target="#AddCaseModelUpdate"
                                data-placement="bottom" href="javascript:;" onclick="loadAllStep();"
                                class="btn btn-outline-primary pendo-welcome-block-add-case">Add Case</button></div>
                    </div>
                    @endcan


                    <div class="card text-center ">
                        <div class="card-highlight bg-dark"></div>
                        <div class="card-body">
                            <h5 class="mt-3">Guided Implementation</h5>
                            <div class="icons"><i class="import-icon"></i></div>
                            <div class="description">Quickly get your firm setup with guided data migration and training
                                from our
                                Implementation Team.</div>
                        </div>
                        <div class="card-footer bg-white border-top-0 mt-auto  mb-2"><button type="button"
                                class="btn btn-outline-primary pendo-guided-implementation">Learn More</button></div>
                    </div>
                </div>
            </div>
            <div class="p-2 d-flex justify-content-center  mt-3">
                <span>We're here to help!
                    <a href="{{ route('ayuda') }}" target="_blank" rel="noopener noreferrer"
                        class="pendo-get-free-support error ">Get free support.[Pending]</a>
                </span>
            </div>
        </div>
    </div>

    <?php } ?>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Add Item</h4>
            </div>

            <div class="card-body p-1 text-nowrap">
                <div class="dashboard-add-item-section d-flex">
                    @canany(['event_add_edit','event_view'])
                    <div class="flex-fill p-3 text-center border-right">
                        @can('event_add_edit')
                        <a data-toggle="modal" data-target="#loadAddEventPopup" data-placement="bottom"
                            href="javascript:;" onclick="loadAddEventPopup();"
                            class="dashboard-event test-add-event pendo-add-event pendo-exp2-add-event">
                            <img alt="" class="mr-1" src="{{ asset('svg/calendar.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline">Event</span>
                        </a>
                        @else
                        <div class="text-muted">
                            <img alt="" class="mr-1" src="{{ asset('icon/calendar__inactive.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline">Event</span>
                        </div>
                        @endcan
                    </div>
                    @endcanany
                    @canany(['document_add_edit','document_view'])
                    <div class="flex-fill p-3 text-center border-right">
                        @can('document_add_edit')
                        <a class="pendo-add-document" rel="facebox" href="#">
                            <img alt="" class="mr-1" src="{{ asset('svg/document.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline error">Document</span>
                        </a>
                        @else
                        <div class="text-muted">
                            <img alt="" class="mr-1" src="{{ asset('icon/document__inactive.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline">Document</span>
                        </div>
                        @endcan
                    </div>
                    @endcanany

                    <div class="flex-fill p-3 text-center border-right">
                        <a data-toggle="modal" data-target="#loadAddTaskPopup" data-placement="bottom"
                            href="javascript:;" onclick="loadAddTaskPopup();">
                            <img alt="" class="mr-1" src="{{ asset('svg/task.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline"> Task</span>
                        </a>
                    </div>

                    @canany(['lead_add_edit','lead_view'])
                    <div class="flex-fill p-3 text-center border-right">
                        @can('lead_add_edit')
                        <a data-toggle="modal" data-target="#addLead" data-placement="bottom" href="javascript:;"
                            onclick="addLead();">
                            <img alt="" class="mr-1" src="{{ asset('svg/Add_Lead.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline"> Lead</span>
                        </a>
                        @else
                        <div class="text-muted">
                            <img alt="" class="mr-1" src="{{ asset('icon/lead_inactive.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline">Lead</span>
                        </div>
                        @endcan
                    </div>
                    @endcanany
                    @canany(['client_add_edit','client_view'])
                    <div class="flex-fill p-3 text-center border-right">
                        @can('client_add_edit')
                        <a data-toggle="modal" data-target="#typeSelectDashboard" data-placement="bottom"
                            href="javascript:;" class="typeSelectDashboard">
                            <img alt="" class="mr-1" src="{{ asset('svg/contact.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline"> Contact</span>
                        </a>
                        @else
                        <div class="text-muted">
                            <img alt="" class="mr-1" src="{{ asset('icon/contact__inactive.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline">Contact</span>
                        </div>
                        @endcan
                    </div>
                    @endcanany
                    @canany(['case_add_edit','case_view'])
                    <div class="flex-fill p-3 text-center border-right">
                        @can('case_add_edit')
                        <a data-toggle="modal" data-target="#AddCaseModelUpdate" data-placement="bottom"
                            href="javascript:;" onclick="loadAllStep();">
                            <img alt="" class="mr-1" src="{{ asset('svg/court_case.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline"> Case</span>
                        </a>
                        @else
                        <div class="text-muted">
                            <img alt="" class="mr-1" src="{{ asset('icon/court_case__inactive.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline">Case</span>
                        </div>
                        @endcan
                    </div>
                    @endcanany

                    @can('messaging_add_edit')
                    <div class="flex-fill p-3 text-center border-right">
                        <a data-toggle="modal" data-target="#addNewMessagePopup" data-placement="bottom"
                            href="javascript:;" onclick="addNewMessagePopup();">
                            <img alt="" class="mr-1" src="{{ asset('svg/message.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline">Message</span>
                        </a>
                    </div>
                    @endcan
                    @canany(['billing_add_edit','billing_view'])
                    <div class="flex-fill p-3 text-center border-right">
                        @can('billing_add_edit')
                        <a data-toggle="modal" data-target="#loadTimeEntryPopup" data-placement="bottom"
                            href="javascript:;" onclick="loadTimeEntryPopup();">
                            <img alt="" class="mr-1" src="{{ asset('svg/time_entry.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline"> Time Entry</span>
                        </a>
                        @else
                        <div class="text-muted">
                            <img alt="" class="mr-1" src="{{ asset('icon/time_entry__inactive.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline">Time Entry</span>
                        </div>
                        @endcan
                    </div>
                    
                    
                    <div class="flex-fill p-3 text-center border-right">
                        @can('billing_add_edit')
                        <a data-toggle="modal" data-target="#loadExpenseEntryPopup" data-placement="bottom"
                            href="javascript:;" onclick="loadExpenseEntryPopup();">
                            <img alt="" class="mr-1" src="{{ asset('svg/expense.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline"> Expense</span>
                        </a>
                        @else
                        <div class="text-muted">
                            <img alt="" class="mr-1" src="{{ asset('icon/expense__inactive.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline">Expense</span>
                        </div>
                        @endcan
                    </div>

                    <div class="flex-fill p-3 text-center border-right">
                        @can('billing_add_edit')
                        <a class="pendo-add-invoice" href="{{ route('bills/invoices/open') }}">
                            <img alt="" class="mr-1" src="{{ asset('svg/invoice_add.svg') }}" width="24"
                                height="24">
                            <span class="d-none d-lg-inline"> Invoice</span>
                        </a>
                        @else
                        <div class="text-muted">
                            <img alt="" class="mr-1" src="{{ asset('icon/invoice_add__inactive.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline">Invoice</span>
                        </div>
                        @endcan
                    </div>
                    @endcanany
                    @canany(['client_add_edit', 'client_view', 'case_add_edit', 'case_view'])
                    <div class="flex-fill p-3 text-center">
                        @canany(['client_add_edit', 'case_add_edit'])
                        <a data-toggle="modal" data-target="#addNoteModal" data-placement="bottom" href="javascript:;"
                            onclick="loadAddNotBox();">
                            <img alt="" class="mr-1" src="{{ asset('svg/note-.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline"> Note</span>
                        </a>
                        @else
                        <div class="text-muted">
                            <img alt="" class="mr-1" src="{{ asset('icon/note__inactive.svg') }}" width="24" height="24">
                            <span class="d-none d-lg-inline">Note</span>
                        </div>
                        @endcanany
                    </div>
                    @endcanany
                </div>
            </div>
        </div>
    </div>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">

    <div class="col-7">
        <div id="activity-container">
            <div class="dashboard-activity-container">
                <div class="card">
                    <div class="card-header">
                        <h4>Recent Activity</h4>
                    </div>
                    <div class="activity-card-body card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link  active show" id="home-basic-tab" data-toggle="tab" href="#allEntry"
                                    onclick="loadAllActivity();" role="tab" aria-controls="homeBasic"
                                    aria-selected="false">All</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="profile-basic-tab" data-toggle="tab"
                                    onclick="loadInvoiceActivity();" href="#invoiceEntry" role="tab"
                                    aria-controls="profileBasic" aria-selected="true">Invoices</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contact-basic-tab" data-toggle="tab"
                                    onclick="loadEventActivity();" href="#eventEntry" role="tab"
                                    aria-controls="contactBasic" aria-selected="false">Events</a>
                            </li>
                            <li class="nav-item m2">
                                <a class="nav-link" id="contact-basic-tab" onclick="loadDocumentActivity();"
                                    data-toggle="tab" href="#documentsEntry" role="tab" aria-controls="contactBasic"
                                    aria-selected="false">Documents</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contact-basic-tab" onclick="loadTaskActivity();"
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
                                <div class="tab-pane fade" id="eventEntry" role="tabpanel"
                                    aria-labelledby="contact-basic-tab">
                                    No recent activity available.

                                </div>
                                <div class="tab-pane fade" id="documentsEntry" role="tabpanel"
                                    aria-labelledby="contact-basic-tab">
                                    No recent activity available. 

                                </div>

                                <div class="tab-pane fade" id="taskActivity" role="tabpanel"
                                    aria-labelledby="contact-basic-tab">
                                    No recent activity available.

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
        <div id="activity-container">
            <div class="dashboard-activity-container">
                <?php if(isset($InvoicesOverdue) && !$InvoicesOverdue->isEmpty()){?>
                <div class="mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h4>Alerts</h4>
                        </div>
                        <div class="card-body">

                            <div class="d-flex align-items-center">
                                <small class="d-flex align-items-center">
                                    <i class="fas fa-circle fa-xs text-danger mr-1"></i>
                                </small>
                                <h6 class="font-weight-bold mb-0 mr-1">Overdue Invoices</h6>
                            </div>
                       
                            <table class="table table-sm table-borderless">
                                <tbody> 
                                    <?php foreach($InvoicesOverdue as $k=>$v){?>
                                    <tr class="border-bottom">
                                        <td class="overdue-invoice">
                                            <small><a class="text-dark pendo-overdue-invoice-invoice-link"
                                                    href="{{BASE_URL}}bills/invoices/view/{{$v->decode_id}}">#{{sprintf('%06d', $v->id)}}</a></small>
                                            <a class="pendo-overdue-invoice-court-case"
                                                href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{substr($v->case_title,0,100)}}</a>
                                            <span class="font-weight-bold">${{number_format($v->due_amount,2)}}</span>
                                            <small>(due {{date('m/d/Y',strtotime($v->due_date))}})</small>
                                        </td>

                                        <td>
                                            <div class="d-flex flex-row-reverse flex-nowrap">
                                                <a class="btn btn-link py-0 text-black-50 pendo-overdue-invoice-view-invoice"
                                                    href="{{BASE_URL}}bills/invoices/view/{{$v->decode_id}}">
                                                    <i class="fas fa-eye" title="" data-toggle="tooltip"
                                                        data-placement="top" data-original-title="View Invoice"></i>
                                                    <span class="sr-only">View Invoice</span>
                                                </a> </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <td>
                                            <a class="text-black-50 pendo-view-all-overdue-invoices"
                                                href="{{BASE_URL}}bills/invoices?type=overdue&global_search=">View all overdue
                                                invoices ({{$totalEvetdueInvoiceCount}})</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            @if(!empty($clientList))
                            <div class="d-flex align-items-center">
                                <small class="d-flex align-items-center">
                                    <i class="fas fa-circle fa-xs text-danger mr-1"></i>
                                </small>
                                <h6 class="font-weight-bold mb-0 mr-1">Low Trust Balances</h6>
                            </div>
                       
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    @forelse ($clientList as $k => $v)
                                        @php
                                            $userUrl = route('contacts/companies/billing/trust/allocation', $v->id);
                                            if($v->user_level=="2") {
                                                $userUrl = route('contacts/clients/billing/trust/allocation', $v->id);
                                            }
                                        @endphp
                                        @if($v->userAdditionalInfo->minimum_trust_balance > $v->userAdditionalInfo->unallocate_trust_balance)
                                            <tr class="border-bottom">
                                                <td class="overdue-invoice">
                                                    <small><a class="pendo-low-trust-user" href="{{ $userUrl }}">{{ $v->full_name }}</a> ({{ $v->user_type_text }})
                                                    <span class="font-weight-bold">${{number_format($v->userAdditionalInfo->minimum_trust_balance - $v->userAdditionalInfo->unallocate_trust_balance,2)}}</span>
                                                    <small>(under min. trust balance)</small>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-row-reverse flex-nowrap">
                                                    <a class="btn btn-link py-0 text-black-50 pendo-low-trust-view-user" href="{{ $userUrl }}">
                                                        <i class="fas fa-eye" title="" data-toggle="tooltip" data-placement="top" data-original-title="View Client"></i>
                                                        <span class="sr-only">View {{$CommonController->getUserTypeText($v->user_level)}}</span>
                                                    </a> 
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        @if (count($v->clientCasesSelection))
                                            @forelse ($v->clientCasesSelection as $item)
                                                <tr class="border-bottom">
                                                    <td class="overdue-invoice">
                                                        <small><a class="pendo-low-trust-user" href="{{ $userUrl }}">{{ $v->full_name }}</a> ({{ $v->user_type_text }})
                                                        <span class="font-weight-bold">${{number_format($item->minimum_trust_balance - $item->allocated_trust_balance,2)}}</span>
                                                        <small>(under min. trust balance)</small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-row-reverse flex-nowrap">
                                                        <a class="btn btn-link py-0 text-black-50 pendo-low-trust-view-user" href="{{ $userUrl }}">
                                                            <i class="fas fa-eye" title="" data-toggle="tooltip" data-placement="top" data-original-title="View Client"></i>
                                                            <span class="sr-only">View {{$CommonController->getUserTypeText($v->user_level)}}</span>
                                                        </a> 
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                
                                            @endforelse
                                        @endif
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                            @endif
                        </div>
                    </div>
                </div>
                <?php } ?>
                <div class="card mb-3 ">
                    <div class="card-header d-flex">
                        <h4>Upcoming Events</h4>
                        <button data-toggle="modal" data-target="#loadAddEventPopup" data-placement="bottom"
                            href="javascript:;" onclick="loadAddEventPopup();"
                            class="ml-auto btn btn-link p-0 add-event pendo-add-event-upcoming pendo-exp2-add-event text-black-50 " @cannot('event_add_edit') disabled @endcannot>
                            <i class="far fa-calendar-alt fa-lg p-2"></i>Add Event
                        </button>
                    </div>
                    <div class="card-body">
                        <?php  
                        $authUser = auth()->user();
                        if(isset($upcomingTenEvents) && !$upcomingTenEvents->isEmpty()){
                            foreach($upcomingTenEvents as $k=>$v){
                            $currentTime = convertUTCToUserTime(date('Y-m-d H:i:s'), $authUser->user_timezone);
                            $convertedStartDateTime = convertUTCToUserTime(date('Y-m-d H:i:s',strtotime($v->start_date .$v->event->start_time)), $authUser->user_timezone);
                            $convertedEndDateTime = convertUTCToUserTime(date('Y-m-d H:i:s',strtotime($v->end_date .$v->event->end_time)), $authUser->user_timezone);
                            if($convertedStartDateTime > $currentTime)
                            {
                            ?>
                            <div class="p-3 border-bottom c-pointer event-row cursor-pointer"
                                onclick="toggleUpcomingEvent({{$v->id}})">
                                <div id="event-row-{{$v->id}}" class="d-flex flex-row align-items-center">
                                    <div class="upcoming-event mr-auto">
                                        {{date('D M d',strtotime($convertedStartDateTime))}} -
                                        @if($v->start_date != $v->end_date)
                                        <small>({{date('M d, Y',strtotime($convertedStartDateTime))}}
                                            ,{{date('h:ia',strtotime($convertedStartDateTime))}} —
                                            {{date('M d, Y',strtotime($convertedEndDateTime))}},
                                            {{date('h:ia',strtotime($convertedEndDateTime))}})</small>
                                        @else
                                        <small>({{date('h:ia',strtotime($convertedStartDateTime))}} —
                                            {{date('h:ia',strtotime($convertedEndDateTime))}})</small>
                                        @endif
                                        <a class="pendo-upcoming-event-appt-link"
                                            href="{{ route('events/detail', $v->id) }}">
                                            {{$v->event->event_title}}</a>
                                        <i class="ml-1 fas fa-angle-down upcoming-event-toggle-down"></i>
                                        <i class="ml-1 fas fa-angle-up upcoming-event-toggle-up d-none"></i>
                                    </div>
                                    <?php 
                                        $list='';
                                        $allUSer = encodeDecodeJson($v->event_linked_staff);
                                        $USerArray=[];
                                        if(!empty($allUSer)){
                                            foreach($allUSer as $m=>$km){
                                                $user = getUserDetail($km->user_id);
                                                $USerArray[]=$user->first_name ." ".$user->last_name ." (".$user->user_type_text.")";
                                            }
                                            $list=implode('<br>',$USerArray);
                                            ?>
                                    <div data-html="true" data-trigger="hover" data-toggle="tooltip" data-placement="top"
                                        data-container="body"
                                        data-original-title="<span class='text-pre-wrap'>{{$list}}</span>">
                                        <i class="fas fa-user-friends"></i>
                                        <span>{{count($allUSer)}} invited</span>
                                    </div>
                                    <?php
                                        }else{
                                            ?>
                                    <div><i class="fas fa-user-friends"></i><span>0 invited</span></div>
                                    <?php
                                        }
                                        ?>

                                </div>
                                <div id="event-details-{{$v->id}}" style="display: none;">
                                    @if ($v->event->case_id!=NULL && $v->event->case)
                                    <a class="pendo-upcoming-event-case-link" href="{{ route('info', @$v->event->case->case_unique_number) }} ">{{ @$v->event->case->case_title }}</a>
                                    @elseif($v->event->lead_id!=NULL && $v->event->leadUser)
                                    <a class="pendo-upcoming-event-case-link" href="{{ route('case_details/info', $v->event->lead_id) }} ">{{$v->full_name}}</a>
                                    @else
                                    {{ "<No Title>" }}
                                    @endif
                                </div>
                            </div>
                            <?php 
                            } 
                        }
                    }else{
                        echo "You have no upcoming events.<br>";
                    }
                    ?>
                        <br>
                        <a class="p-2 pendo-view-all-events" href="{{BASE_URL}}events?view=month">View all events</a>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header d-flex">
                        <h4>Upcoming Task</h4>
                        <button data-toggle="modal" data-target="#loadAddTaskPopup" data-placement="bottom"
                            href="javascript:;" onclick="loadAddTaskPopup();"
                            class="ml-auto btn btn-link p-0 add-event pendo-add-event-upcoming pendo-exp2-add-event text-black-50 ">
                            <i class="fas fa-clipboard-check fa-lg p-2"></i>Add Task
                        </button>
                    </div>
                    <div class="card-body">
                        <?php  
                        if(isset($upcomingTask) && !$upcomingTask->isEmpty()){
                        foreach($upcomingTask as $k=>$kv){
                          ?>
                            <div class="task-item-row cursor-pointer p-3 border-bottom"
                                onclick="toggleUpcomingTask({{$kv->id}})">
                                <div id="task-row-{{$kv->id}}" class="d-flex flex-row align-items-center">
                                    <div class="mr-auto">
                                        <div class="d-flex align-items-center">
                                            <small class="mr-1">
                                                <i class="fas fa-circle fa-xs text-info"></i>
                                            </small>

                                            <a class="test-task-item text-dark pendo-upcoming-task-link"
                                                href="{{BASE_URL}}tasks?id={{$kv->id}}">{{$kv->task_title}}</a>
                                            <i class="ml-1 fas fa-angle-down upcoming-task-toggle-down "></i>
                                            <i class="ml-1 fas fa-angle-up upcoming-task-toggle-up d-none"></i>
                                        </div>

                                        <div id="task-details-{{$kv->id}}" style="display: none;">
                                            <?php 
                                    if($kv->case_id!=NULL){
                                    ?>
                                            <div>
                                                <small>
                                                    <span class="font-weight-bold">Case: </span>
                                                    <a class="pendo-upcoming-event-case-link"
                                                        href="{{BASE_URL}}court_cases/{{$kv->case_unique_number}}/info">{{$kv->case_title}}</a>
                                                </small>
                                            </div>
                                            <?php }else{
                                        ?>
                                            <div>
                                                <small>
                                                    <span class="font-weight-bold">Lead: </span>
                                                    <a class="pendo-upcoming-event-case-link"
                                                        href="{{BASE_URL}}leads/{{$kv->lead_id}}/case_details/info">{{$kv->first_name}}
                                                        {{$kv->middle_name}} {{$kv->last_name}}</a>
                                                </small>
                                            </div>
                                            <?php 
                                    }
                                        ?>
                                            <div>
                                                <small class="d-flex align-items-center">
                                                   
                                                    <?php  
                                            $totalUser=count($kv->task_user);
                                            
                                            if($totalUser==1){
                                                ?>
                                                    <span class="font-weight-bold">Assigned to: </span>
                                                    <div class="mx-1">
                                                        <a class="pendo-upcoming-task-assigned-link"
                                                            href="{{BASE_URL}}contacts/attorneys/{{base64_encode($kv->task_user[0]->id)}}">{{$kv->task_user[0]->first_name}}
                                                            {{$kv->task_user[0]->middle_name}}
                                                            {{$kv->task_user[0]->last_name}}</a>
                                                    </div>
                                                    <?php 
                                            }
                                            if($totalUser>1){
                                                $list='';
                                                $USerArray=[];
                                                if(!$kv->task_user->isEmpty()){
                                                    foreach($kv->task_user as $m=>$km){
                                                        $USerArray[]=$km->first_name ." ".$km->middle_name ." ".$km->last_name ." (".$CommonController->getUserLevelText($km->user_type).")";
                                                    }
                                                    $list=implode('<br>',$USerArray);
                                                }
                                                ?>
                                                 <span class="font-weight-bold">Assigned to: </span>
                                                    <div data-html="true" data-trigger="hover" data-toggle="tooltip"
                                                        data-placement="top" data-container="body"
                                                        data-original-title="<span class='text-pre-wrap'>{{$list}}</span>"
                                                        class="ml-2">
                                                        <i class="fas fa-user-friends"></i>
                                                        <span>{{$totalUser}} Users</span>
                                                    </div>
                                                    <?php
                                            }?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <?php 
                                        if($kv->task_due_on <= date('Y-m-d')){?>
                                        <small><i class="fas fa-circle fa-xs text-danger mr-1"></i></small>
                                        <div class="task-item-due-date error">
                                            {{date('m/d/Y',strtotime($kv->task_due_on))}}
                                        </div>
                                        <?php }else{ 
                                            ?>
                                            
                                            <div class="task-item-due-date ">
                                                {{date('m/d/Y',strtotime($kv->task_due_on))}}
                                            </div>
                                            <?php
                                        } ?>
                                    </div>

                                    <div>
                                    <?php 
                                    if($kv->case_id!=NULL){
                                    ?>
                                        <a class="btn btn-link text-black-50 pendo-upcoming-task-view-case-icon"
                                            href="{{BASE_URL}}court_cases/{{$kv->case_unique_number}}/info">
                                            <i class="fas fa-suitcase" title="" data-toggle="tooltip" data-placement="top"
                                                data-original-title="View Case"></i>
                                            <span class="sr-only">View Case</span>
                                        </a>
                                    <?php   
                                    }else if($kv->lead_id!=NULL){
                                    ?>
                                        <a class="btn btn-link text-black-50 pendo-upcoming-task-view-lead-icon"
                                            href="{{BASE_URL}}leads/{{$kv->lead_id}}/case_details/info">
                                            <i class="far fa-address-card" title="" data-toggle="tooltip"
                                                data-placement="top" data-original-title="View Lead"></i>
                                            <span class="sr-only">View Lead</span>
                                        </a>
                                    <?php }else{ ?>
                                        <span style="padding-left: 40px;"></span>
                                    <?php } ?>
                                    </div>

                                    <div>
                                        <button id="test-mark-upcoming-task-complete-{{$kv->id}}"
                                            class="task-item btn btn-sm btn-outline-secondary pendo-upcoming-task-complete-button"
                                            value="18642690" onclick="taskStatus({{$kv->id}},{{$kv->status}});">
                                            Mark Complete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php } 
                        }else{ ?>
                            <span class="pb-5">You have no tasks due in the next 7 days.</span> <br>
                        <?php }?>
                        <br>
                        <a class="p-2 pendo-view-all-events" href="{{BASE_URL}}tasks">View all tasks</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<div id="AddCaseModelUpdate" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" bladefile="resources/views/dashboard/homepage.blade.php">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!--  SmartWizard html -->
                        <form class="createCase" id="createCase" name="createCase" method="POST">
                            @csrf
                            <div id="smartwizard">
                                <ul>
                                    <li class="text-center"><a href="#step-1">1<br /><small>Clients & Contacts</small></a></li>
                                    <li class="text-center"><a href="#step-2">2<br /><small>Case Details</small></a></li>
                                    <li class="text-center"><a href="#step-3">3<br /><small>Billing</small></a></li>
                                    <li class="text-center"><a href="#step-4">4<br /><small>Staff</small></a></li>
                                </ul>
                                <div id="NewCaseDetail">
                                    <div id="step-1">

                                        <div class="col-md-12">
                                            <div class="form-group row">

                                                <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                                                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                                            </div>

                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-3 ">
                                                    <a data-toggle="modal" data-target="#AddContactModal"
                                                        data-placement="bottom" href="javascript:;">
                                                        <button class="btn btn-primary btn-rounded m-1" type="button"
                                                            onclick="AddContactModal();">Add New
                                                            Contact</button></a>

                                                </label>
                                                <div class="text-center col-2">Or</div>
                                                <div class="col-7 form-group mb-3">
                                                    <select onchange="selectUser();" class="form-control user_type"
                                                        style="width:100%;" id="user_type" name="user_type"
                                                        data-placeholder="Search for an existing contact or company">
                                                        <option value="">Search for an existing contact or company
                                                        </option>
                                                        <optgroup label="Client">
                                                            <?php foreach(userClientList() as $Clientkey=>$Clientval){ ?>
                                                            <option value="{{$Clientval->id}}">{{substr($Clientval->name,0,30)}}</option>
                                                            <?php } ?>
                                                        </optgroup>
                                                        <optgroup label="Company">
                                                            <?php foreach(userCompanyList() as $Companykey=>$Companyval){ ?>
                                                            <option value="{{$Companyval->id}}">{{substr($Companyval->name,0,50)}}</option>
                                                            <?php } ?>
                                                        </optgroup>
                                                    </select>
                                                    <span id="UserTypeError"></span>
                                                </div>
                                                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                                            </div>

                                            <div id="loadUserAjax"></div>

                                            <div class="m-2 empty-state text-center text-center-also">
                                                <p class="font-weight-bold">Start creating your case by adding a new or
                                                    existing contact.</p>
                                                <div>All cases need at least one client to bill.</div><a href="#"
                                                    rel="noopener noreferrer" target="_blank">Learn more about adding a
                                                    case and a contact at the same time.</a>
                                            </div>

                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                                                <label for="inputEmail3" class="col-sm-10 col-form-label"></label>
                                                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                                            </div>

                                            <div class="modal-footer">
                                                <div class="no-contact-warning mr-2" style="display:none;"
                                                    id="beforetext">You must have a contact to bill.
                                                    Are you sure you want to continue?</div>
                                                <button type="button" id="beforebutton" onclick="callOnClick();"
                                                    class="btn btn-primary ladda-button example-button m-">Continue to
                                                    case details
                                                </button>

                                                <button class="btn btn-primary ladda-button example-button m-1"
                                                    type="button" id="submit" style="display:none;"
                                                    onclick="StatusLoadStep2();">Continue without picking a contact
                                                </button>

                                                <button type="button"
                                                    class="btn btn-primary ladda-button example-button m-1"
                                                    id="submit_with_user" style="display:none;" type="submit"
                                                    onclick="StatusLoadStep2();">Continue to case Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="step-2">
                                        <div id="showError2" style="display:none"></div>       
                                        <div class=" col-md-12">
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Case
                                                    name</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control" id="case_name" value="" name="case_name"
                                                        type="text" placeholder="E.g. John Smith - Divorce">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Case
                                                    number</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control" id="case_number" value=""
                                                        name="case_number" type="text" placeholder="Enter case number">
                                                    <small>A unique identifier for this case.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row" id="area_dropdown">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Practice
                                                    area</label>
                                                <div class="col-md-6 form-group mb-3">
                                                    <select id="practice_area" name="practice_area"
                                                        class="form-control custom-select col">
                                                        <option value="-1"></option>
                                                        <?php 
                                                            foreach($practiceAreaList as $k=>$v){?>
                                                        <option value="{{$v->id}}">{{$v->title}}</option>
                                                        <?php } ?>

                                                    </select>
                                                </div>
                                                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a
                                                        onclick="showText();" href="javascript:;">Add
                                                        new practice area</a></label>
                                            </div>
                                            <div class="form-group row" id="area_text">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Practice
                                                    area</label>
                                                <div class="col-md-6 form-group mb-3">
                                                    <input class="form-control" id="practice_area_text" value=""
                                                        name="practice_area_text" type="text"
                                                        placeholder="Enter new practice area">
                                                </div>
                                                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a
                                                        onclick="showDropdown();" href="javascript:;">Cancel</a></label>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Case stage
                                                </label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <select id="case_status" name="case_status"
                                                        class="form-control custom-select col">
                                                        <option value="0"></option>
                                                        <?php 
                                                        foreach($caseStageList as $kcs=>$vcs){?>
                                                        <option value="{{$vcs->id}}">{{$vcs->title}}</option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Date
                                                    opened</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control datepicker" id="case_open_date"
                                                        value="{{ convertUTCToUserTimeZone('dateOnly') }}" name="case_open_date" type="text"
                                                        placeholder="mm/dd/yyyy">

                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Office
                                                </label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <select id="case_office" name="case_office"
                                                        class="form-control custom-select col">
                                                        <option value="1">Primary</option>

                                                    </select>
                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3"
                                                    class="col-sm-2 col-form-label">Description</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <textarea name="case_description" class="form-control"
                                                        rows="5"></textarea>
                                                </div>
                                            </div>
                                            @if(IsCaseSolEnabled() == 'yes')
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Statute of Limitations</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control datepicker" id="case_statute" value="" name="case_statute" type="text" placeholder="mm/dd/yyyy">

                                                </div>
                                            </div>
                                            <div class="form-group row" id="addMoreReminder">
                                                <label for="sol_reminders" class="col-sm-2 col-form-label">SOL Reminders</label>
                                                <div class="col">
                                                    @forelse (firmSolReminders() as $key => $item)
                                                        <div class="row form-group fieldGroup">
                                                            <div class="col-md-2 form-group mb-3">
                                                                <select id="reminder_type" name="reminder_type[]" class="form-control custom-select  ">
                                                                    @foreach(getEventReminderTpe() as $k =>$v)
                                                                            <option value="{{$k}}" <?php if(@$item->reminder_type == $k){ echo "selected=selected"; } ?>>{{$v}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-2 form-group mb-3">
                                                                <input class="form-control" id="reminder_days" value="{{ @$item->reminer_days }}" name="reminder_days[]" type="number" min="0"> 
                                                            </div> <span class="pt-2">Days</span>
                                                            <div class="col-md-2 form-group mb-3">   
                                                                <button class="btn remove" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                                            </div>
                                                        </div>
                                                    @empty
                                                    @endforelse
                                                    <div class="test-sol-reminders fieldGroup">
                                                        <div>
                                                            <button type="button" class="btn btn-link pl-0 add_more_reminder">Add
                                                                a reminder</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="fieldGroupCopy copy hide" style="display: none;">
                                                <div class="col-md-3 form-group mb-3">
                                                    <select id="reminder_type" name="reminder_type[]"
                                                        class="form-control custom-select  ">
                                                        @foreach(getEventReminderTpe() as $k =>$v)
                                                            <option value="{{$k}}">{{$v}}</option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                                <div class="col-md-2 form-group mb-3">
                                                    <input class="form-control" id="reminder_days" value="1"
                                                        name="reminder_days[]" type="number" type="number" min="0">
                                                </div> <span class="pt-2">Days</span>
                                                <div class="col-md-2 form-group mb-3">
                                                    <button class="btn remove" type="button"><i class="fa fa-trash"
                                                            aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @endif
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Conflict
                                                    Check</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <label
                                                        class="switch pr-5 switch-success mr-3"><span>Completed</span>
                                                        <input type="checkbox" name="conflict_check"
                                                            id="conflict_check"><span class="slider"></span>
                                                    </label>

                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Conflict Check
                                                    Notes</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <textarea name="conflict_check_description" class="form-control"
                                                        rows="5"></textarea>
                                                </div>
                                            </div>

                                            <div class="form-group row float-left">
                                                <button type="button" class="btn btn-outline-secondary m-1"
                                                    onclick="backStep1();">
                                                    <span class="ladda-label">Go Back</span>
                                                </button>
                                            </div>
                                            <div class="form-group row float-right">
                                                <button type="button"
                                                    class="btn btn-primary ladda-button example-button m-1"
                                                    onclick="StatusLoadStep3();">
                                                    <span class="ladda-label">Continue to Billing</span>
                                                </button>
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
                                    </div>
                                    <div id="step-3">
                                        <div class=" col-md-12">

                                            <div id="showError3" style="display:none;"></div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Billing
                                                    Contact</label>
                                                <div class="col-md-10 form-group mb-3" id="loadBillingAjax">
                                                    <small>Choosing a billing contact allows you to batch bill this
                                                        case.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Billing
                                                    Method</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <select onchange="selectMethod();" id="billingMethod"
                                                        name="billingMethod" class="form-control custom-select col">
                                                        <option value=""></option>
                                                        <option value="hourly">Hourly</option>
                                                        <option value="contingency">Contingency</option>
                                                        <option value="flat">Flat Fee</option>
                                                        <option value="mixed">Mix of Flat Fee and Hourly</option>
                                                        <option value="pro_bono">Pro Bono</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row" id="billing_rate_text" style="display: none;">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Flat fee
                                                    Amount</label>
                                                <div class="input-group mb-3 col-sm-5">
                                                    <div class="input-group-prepend"><span
                                                            class="input-group-text">$</span></div>
                                                    <input class="form-control case_rate number" name="default_rate"
                                                        maxlength="10" type="text"
                                                        aria-label="Amount (to the nearest dollar)">
                                                </div>
                                            </div>

                                            <div class="form-group row float-left">
                                                <button type="button" class="btn btn-outline-secondary m-1"
                                                    onclick="backStep2();">
                                                    <span class="ladda-label">Go Back</span>
                                                </button>
                                            </div>
                                            <div class="form-group row float-right">
                                                <button type="button"
                                                    class="btn btn-primary ladda-button example-button m-1"
                                                    data-style="expand-right" onclick="StatusLoadStep4();">
                                                    <span class="ladda-label">Continue to Staff</span>
                                                </button>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                                <div class="col-md-2 form-group mb-3">
                                                    <div class="loader-bubble loader-bubble-primary" id="innerLoader3"
                                                        style="display: none;"></div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>

                                            </div>

                                        </div>

                                    </div>
                                    <div id="step-4">
                                        <div id="showError4" style="display:none"></div>


                                        <div class=" col-md-12">
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-3 col-form-label">Lead
                                                    Attorney</label>
                                                <div class="col-md-9 form-group mb-3">
                                                    <select id="lead_attorney" onchange="selectLeadAttorney();"
                                                        name="lead_attorney" class="form-control custom-select col">
                                                        <option value=""></option>
                                                        <?php foreach($loadFirmUser as $key=>$user){?>
                                                        <option <?php if($user->id==Auth::User()->id){ echo "selected=selected"; } ?> value="{{$user->id}}">{{$user->first_name}}
                                                            {{$user->last_name}}</option>
                                                        <?php } ?>
                                                    </select>
                                                    <small>The user you select will automatically be checked in the
                                                        table below.</small>
                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-3 col-form-label">Originating
                                                    Attorney</label>
                                                <div class="col-md-9 form-group mb-3">
                                                    <select onchange="selectAttorney();" id="originating_attorney"
                                                        name="originating_attorney"
                                                        class="form-control custom-select col">
                                                        <option value=""></option>
                                                        <?php foreach($loadFirmUser as $key=>$user){?>
                                                        <option value="{{$user->id}}">{{$user->first_name}}
                                                            {{$user->last_name}}</option>
                                                        <?php } ?>
                                                    </select>
                                                    <small>The user you select will automatically be checked in the
                                                        table below.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-12 col-form-label">Who from your
                                                    firm should have access to this
                                                    case?</label>
                                            </div>
                                            <table style="table-layout: auto;" class="firm-users-table table table-sm">
                                                <colgroup>
                                                    <col style="width: 8%;">
                                                    <col>
                                                    <col>
                                                    <col>
                                                    <col style="width: 20%;">
                                                    <col style="width: 15%;">
                                                </colgroup>
                                                <thead>
                                                    <tr>
                                                        <th><input class="all-users-checkbox" id="select-all"
                                                                type="checkbox"></th>
                                                        <th>First Name</th>
                                                        <th>Last Name</th>
                                                        <th>User Title</th>
                                                        <th>Billing Rate</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($loadFirmUser as $key=>$user){?>
                                                    <tr>
                                                        <td><input <?php if($user->id==Auth::User()->id){ echo "checked=checked";} ?> class="users-checkbox" type="checkbox" id="{{$user->id}}" name="selectedUSer[{{$user->id}}]">
                                                        </td>
                                                        <td>{{$user->first_name}}</td>
                                                        <td>{{$user->last_name}}</td>
                                                        <td>{{$user->user_title}}</td>
                                                        <td>
                                                            <select onchange="selectRate({{$user->id}});"
                                                                name="rate_type[{{$user->id}}]" id="cc{{$user->id}}"
                                                                class="rate test-billing-rate-dropdown form-control mr-1">
                                                                <option value="Default_Rate">Default Rate</option>
                                                                <option value="Case_Rate">Case Rate</option>
                                                            </select>
                                                        </td>
                                                        <td id="default_{{$user->id}}">
                                                            <?php if($user->default_rate){
                                                            echo "$".$user->default_rate;
                                                        } ?>
                                                        </td>
                                                        <td id="custome_{{$user->id}}" style="display:none;">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend"><span
                                                                        class="input-group-text">$</span></div>
                                                                <input class="form-control case_rate number"
                                                                    name="new_rate[{{$user->id}}]" maxlength="10"
                                                                    type="text"
                                                                    aria-label="Amount (to the nearest dollar)">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>

                                            <div class="form-group row float-left">
                                                <button type="button" class="btn btn-outline-secondary m-1"
                                                    onclick="backStep3();">
                                                    <span class="ladda-label">Go Back</span>
                                                </button>
                                            </div>

                                            <div class="form-group row float-right">
                                                <button type="button" onclick="saveFinalStep()"
                                                    class="btn btn-primary ladda-button example-button m-1"
                                                    data-style="expand-right">
                                                    <span class="ladda-label">Save & Finish</span>
                                                </button>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                                <div class="col-md-2 form-group mb-3">
                                                    <div class="loader-bubble loader-bubble-primary" id="innerLoader4"
                                                        style="display: none;"></div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="AddBulkUserModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Bulk User Add</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="AddBulkUserModalArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<style>
    i.brittany-icon,
    i.calendar-icon {
        background-repeat: no-repeat;
        height: 42px;
        width: 42px;
    }

    i.brittany-icon {
        background-image: url({{ asset('images/brittany.jpg') }});
        background-size: cover;
    }

    i.welcome-panel-add-firm-user {
        background-image: url({{ asset('svg/lawyer_add.svg') }});
        height: 42px;
        width: 42px;
    }

    i.welcome-panel-add-case {
        background-image: url({{ asset('svg/court_case.svg') }});
        height: 42px;
        width: 42px;
    }

    i.import-icon {
        background-image: url({{ asset('svg/import-icon.svg') }});
        height: 42px;
        width: 42px;
    }

    .rounded-circle {
        border-radius: 50% !important;
    }

    i {
        display: inline-block;
    }
    .modal { overflow: auto !important; }
</style>

@include('dashboard.include.modal')

@endsection
@section('page-js')
<script src="{{ asset('assets\js\custom\calendar\addevent.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script src="{{ asset('assets\js\custom\task\addtask.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();

        var isAdded = localStorage.getItem("addedClient");
        if (isAdded != null) {
            $("#AddCaseModelUpdate").modal("show");
            selectUserAutoLoad(isAdded);
            localStorage.removeItem("addedClient");
        }
        var isAddedNote = localStorage.getItem("addTimeEntryForDashboard");
        if (isAddedNote != null) {
            $("#loadTimeEntryPopup").modal("show");
            loadTimeEntryPopup();
            localStorage.removeItem("addTimeEntryForDashboard");
        }
        $("#closeButton").show();
        $("#openButton").hide();
        $(".dismissForever").hide();
        $(".select2").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
        });

        $("#user_type").select2({
            placeholder: "Search for an existing contact or company",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#AddCaseModelUpdate"),
        });   
        $(".confirm").click(function () {
            $("#preloader").show();
            $.ajax({
                type: "POST",
                url: baseUrl + "/dashboard/dismissWidget",
                data: {
                    "legalToken": "proceed"
                },
                success: function (res) {
                    $("#widgetArea").fadeOut();
                    $("#preloader").hide();
                }
            })
        });
        $(".cancel").click(function () {
            $(".dismissForever").hide();
            $("#dismissButton").show();
        });
        $(".typeSelectDashboard").click(function () {
            $("#typeSelectDashboard").modal("show");
        });


        // Smart Wizard
        $('#smartwizard,#smartwizard1').smartWizard({
            selected: 0,
            theme: 'default',
            transitionEffect: 'fade',
            showStepURLhash: false,
            enableURLhash: false,
            backButtonSupport: true, // Enable the back button support
            keyNavigation: false,
            toolbarSettings: {
                toolbarPosition: 'none',
                toolbarButtonPosition: 'end'
            },
            anchorSettings: {
                anchorClickable: false, // Enable/Disable anchor navigation
                enableAllAnchors: false, // Activates all anchors clickable all times
                markDoneStep: true, // Add done state on navigation
                markAllPreviousStepsAsDone: true, // When a step selected by url hash, all previous steps are marked done
                removeDoneStepOnNavigateBack: false, // While navigate back done step after active step will be cleared
                enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
            },

        });

        $(".add_more_reminder").click(function () {
            var fieldHTML = '<div class="row form-group fieldGroup">' + $(".fieldGroupCopy").html() +
                '</div>';
            $('body').find('.fieldGroup:last').before(fieldHTML);
        });
        $("#addMoreReminder").hide();   
        $("#case_statute").on('change.dp', function (e) {
            $("#addMoreReminder").show();
        });
        $('#createCase').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroup').remove();
        });

        @if(session()->has('show_your_firm_popup') && session()->get('show_your_firm_popup') == "yes")
            $("#your_firm_popup").modal("show");
        @endif

        @if(Session::has('firmCaseCount') && session()->get('firmCaseCount') == 0 && !session()->has('show_your_firm_popup'))
            $("#AddCaseModelUpdate").modal("show");
        @endif
    });
    function showText() {
        $("#area_text").show();
        $("#area_dropdown").hide();
        return false;
    }

    function showDropdown() {
        $("#area_text").hide();
        $("#area_dropdown").show()
        return false;
    }
    showDropdown();
    function openFun() {
        $("#showContent").fadeOut();
        $("#closeButton").hide();
        $("#openButton").show();
        $("#dismissButton").hide();

        $(".dismissForever").hide();
    }

    function closeFun() {
        $("#showContent").fadeIn();
        $("#closeButton").show();
        $("#openButton").hide();
        $("#dismissButton").show();
        $(".dismissForever").hide();
    }

    function dismissModal() {

        $(".dismissForever").show();
        $("#dismissButton").hide();

    }

    function loadAllStep() {
        $('#smartwizard').smartWizard("reset");
        $('#createCase')[0].reset();
        $("#user_type").select2("val", "");
        
    }


    function selectUserAutoLoad(id) {
        $("#innerLoader").css('display', 'block');
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/saveSelectdUser",
            data: {
                "selectdValue": id
            },
            success: function (res) {

                $(".text-center-also").remove();
                $("#innerLoader").css('display', 'none');
                $("#beforetext").remove();
                $("#beforebutton").remove();
                $("#submit_with_user").show();
                $("#submit").remove();
                $("#loadUserAjax").html(res);

            }
        })
    }

    function selectUser() {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#user_type option:selected").val() // or
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/saveSelectdUser",
            data: {
                "selectdValue": selectdValue
            },
            success: function (res) {

                $(".text-center-also").remove();
                $("#innerLoader").css('display', 'none');
                $("#beforetext").remove();
                $("#beforebutton").remove();
                $("#submit_with_user").show();
                $("#submit").remove();
                $("#loadUserAjax").html(res);

            }
        })
    }

    function removeUser(id) {
        $("#innerLoader").css('display', 'block');
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/remomeSelectedUser",
            data: {
                "selectdValue": id
            },
            success: function (res) {
                $("#loadUserAjax").html(res);
                $("#innerLoader").css('display', 'none');
            }
        })
    }

    function StatusLoadStep2() {
        $('#smartwizard').data('smartWizard')._showStep(1); // go to step 3....
    }

    function backStep1() {
        $('#smartwizard').smartWizard('prev');
    }

    function StatusLoadStep3() {
        var case_name = $("#case_name").val();
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/checkCaseNameExists", // json datasource
            data: {case_name : case_name},
            success: function (res) {
                if (res.errors != '') {
                    $('#showError2').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><ul>';
                            $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                    errotHtml += '</ul></div>';
                    $('#showError2').append(errotHtml);
                    $('#showError2').show();
                    $('#AddCaseModelUpdate').animate({
                        scrollTop: 0
                    }, 'slow');
                    result = false;                    
                }else{
                    $.ajax({
                        type: "POST",
                        url: baseUrl + "/case/loadBillingContact",
                        data: {"selectdValue": ''},
                        success: function (res) {
                            $("#loadBillingAjax").html(res);
                            $("#innerLoader").css('display', 'none');
                            $('#smartwizard').data('smartWizard')._showStep(2); // go to step 3....
                        }
                    })
                }
            }
        });
    }

    function backStep2() {
        $('#smartwizard').smartWizard('prev');
    }

    function StatusLoadStep4() {
        $('#smartwizard').data('smartWizard')._showStep(3);

    }

    $(".all-users-checkbox").click(function () {
        $(".users-checkbox").prop('checked', $(this).prop('checked'));
    });
    $(".users-checkbox").click(function () {
        if ($('.users-checkbox:checked').length == $('.users-checkbox').length) {
            $('.all-users-checkbox').prop('checked', true);
        } else {
            $('.all-users-checkbox').prop('checked', false);
        }
    });
    
    function selectAttorney() {
        var selectdValue = $("#originating_attorney option:selected").val();
        $("#" + selectdValue).prop('checked', true);
    }
    
    function selectLeadAttorney() {
        var selectdValue = $("#lead_attorney option:selected").val();
        $("#" + selectdValue).prop('checked', true);
    }

    function backStep3() {
        $('#smartwizard').smartWizard('prev');

    }

    function saveFinalStep() {
        var dataString = $("#createCase").serialize();
    
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/saveAllStep", // json datasource
            data: dataString,
            success: function (res) {
                if (res.errors != '') {
                    $('#showError4').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError4').append(errotHtml);
                    $('#showError4').show();
                    $('#AddCaseModelUpdate').animate({
                        scrollTop: 0
                    }, 'slow');
                    return false;
                } else {
                    window.location.href = baseUrl + '/court_cases/' + res.case_unique_number + '/info';
                }
            }
        });

    }   

    
    function selectMethod() {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#billingMethod option:selected").val();
        if (selectdValue == 'mixed' || selectdValue == 'flat') {
            $("#billing_rate_text").show();
        } else {
            $("#billing_rate_text").hide();
        }
    }

    function AddOnlyContactModal() {
        $("#innerLoader").css('display', 'none');
        $("#preloader").show();
        $("#step-1-again").html('');
        $(function () {
            $.ajax({
                type: "POST",
                // url: baseUrl + "/contacts/loadAddContactFromCase", // json datasource
                url:  baseUrl +"/contacts/loadAddContact", // json datasource
                data: 'loadStep1',
                success: function (res) {
                    $("#step-1-again").html(res);
                    $("#preloader").hide();
                    $("#innerLoader").css('display', 'none');
                    return false;
                }
            })
        })
    }

    function AddContactModal() {
        $("#innerLoader").css('display', 'none');
        $("#preloader").show();
        $("#step-1-again").html('');
        $(function () {
            $.ajax({
                type: "POST",
                // url: baseUrl + "/contacts/loadAddContactFromCase", // json datasource
                url:  baseUrl +"/contacts/loadAddContact", // json datasource
                // data: 'loadStep1',
                data: { action : 'add_case'},
                success: function (res) {
                    $("#step-1-again").html(res);
                    $("#preloader").hide();
                    $("#innerLoader").css('display', 'none');
                    return false;
                }
            })
        })
    }

    function loadStep1(id) {        
        window.location.reload();
    }

    function toggleUpcomingEvent(appointmentId) {
        const toggleDownSelector = $('#event-row-' + appointmentId + ' .upcoming-event-toggle-down');
        const toggleUpSelector = $('#event-row-' + appointmentId + ' .upcoming-event-toggle-up');
        $('#event-details-' + appointmentId).toggle();

        if (toggleDownSelector.is(':visible')) {
            toggleDownSelector.addClass('d-none');
            toggleUpSelector.removeClass('d-none');
        } else {
            toggleUpSelector.addClass('d-none');
            toggleDownSelector.removeClass('d-none');
        }
    }

    function toggleUpcomingTask(taskId) {
        const toggleDownSelector = $('#task-row-' + taskId + ' .upcoming-task-toggle-down');
        const toggleUpSelector = $('#task-row-' + taskId + ' .upcoming-task-toggle-up');
        $('#task-details-' + taskId).toggle();

        if (toggleDownSelector.is(':visible')) {
            toggleDownSelector.addClass('d-none');
            toggleUpSelector.removeClass('d-none');
        } else {
            toggleUpSelector.addClass('d-none');
            toggleDownSelector.removeClass('d-none');
        }
    }

    function taskStatus(id, status) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/taskStatus", // json datasource
            data: {
                "task_id": id,
                "status": status
            },
            success: function (res) {
                window.location.reload();

            }
        })
    }
    function AddBulkUserModal() {
        $("#innerLoader").css('display', 'none');
        $("#preloader").show();
        $("#AddBulkUserModalArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/dashboard/AddBulkUserModal", // json datasource
                data: 'bulkAdd',
                success: function (res) {
                    $("#AddBulkUserModalArea").html(res);
                    $("#preloader").hide();
                    return false;
                }
            })
        })
    }
    function callOnClick(){
        $("#beforebutton").hide();
        $("#beforetext").show();
        $("#submit").show();
        $("#submit_with_user").hide();
    }
    loadAllActivity();

// For select module button
$(".module-btn").click(function() {
    if($(this).hasClass("btn-secondary")) {
        var selectedModule = $(".module-btn.btn-primary").length;
        if(selectedModule >= 2) {
            $(".module-error").text("Please choose no more than 2 topics. To make a change, unselect one of your choices.");
        } else {
            $(this).removeClass("btn-secondary");
            $(this).addClass("btn-primary");
        }
    } else {
        $(this).removeClass("btn-primary");
        $(this).addClass("btn-secondary");
        $(".module-error").text("");
    }
    $('#interest_module').val("");
    $(".module-btn.btn-primary").each(function() {
        $('#interest_module').val($('#interest_module').val() + ","+$(this).attr("data-option"));
    });
});
/**
* Save user interested modules
*/
$("#take_to_legalcase").click(function() {
    var intModule = $('#interest_module').val();
    var looking = $('input[type="radio"][name="looking_out"]:checked').val();

    $.ajax({
        url: "{{ route('save/user/interested/detail') }}",
        type: "GET",
        data: {interest_module: intModule, looking_out: looking},
        success: function(data) {
            $("#your_firm_popup").modal("hide");
            // $("#AddCaseModelUpdate").modal("show");
        }
    })
});
$("#your_firm_popup").on("hidden.bs.modal", function() {
    $("#AddCaseModelUpdate").modal("show");
})

$("#AddCaseModelUpdate").on("hidden.bs.modal", function() {
    $.ajax({
        url: baseUrl + "/case/removeTempSelectedUser",
        type: "GET",
        success: function(data) {
            window.location.reload();
        }
    })
})
</script>
@endsection
