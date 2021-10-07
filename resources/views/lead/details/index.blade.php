@extends('layouts.master')
@section('main-content')
@section('title', substr($LeadData['leadname'],0,200).' - Lead Details')
@include('lead.lead_submenu')
<?php
$show="";
if(isset($_GET['show']) && $_GET['show']=='all'){
    $show='all';
} 

$timezoneData = unserialize(TIME_ZONE_DATA); 
$userTitle = unserialize(USER_TITLE); 
$callfor=$status=$type='';

if(isset($_GET['callfor'])){
     $callfor= $_GET['callfor'];
}
if(isset($_GET['status'])){
     $status= $_GET['status'];
}
if(isset($_GET['type'])){
     $type= $_GET['type'];
}
?>
<div class="row ">
   
    <div class="col">
        <h4 class="lead-name-header">{{substr($LeadData['leadname'],0,200)}}
            <?php if($LeadData['do_not_hire_reason']!=NULL){ ?>
                <span class="no-hire-status error">[No Hire]</span>
            <?php } ?>
        </h4> 
    </div>
    <div class="col">
        <div class="float-right">
            <?php if($LeadData['do_not_hire_reason']==NULL){ ?>
            <a data-toggle="modal"  data-target="#AddCaseModel" data-placement="bottom" href="javascript:;" > <button onclick="loadStep1('{{$LeadData->user_id}}');" class="btn btn-primary btn-rounded m-1 px-5" type="button">Convert to Case</button></a>
            <?php } ?>
        </div>
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-2 lead-details-left-panel d-print-none">
        <div class="card">
            <div class="card-body">
                <div class="p-1">
                    <div class="lead-picture-container mb-3 d-flex justify-content-center position-relative">
                       
                        <?php if($LeadData['is_published']=="no"){ ?>
                            <i class="fas fa-10x fa-user-circle text-black-50"></i>
                    <?php } else{ ?> 
                            <img class="mw-100 mh-100 align-items:start m-auto" src="{{BASE_URL}}profile/{{$LeadData['profile_image']}}" width="126" height="130">
                    <?php } ?>
                        <button onclick="changeProfileImage()" type="button"
                            class="bg-light edit-profile-pic px-3 btn btn-secondary"
                            style="right: 10px; bottom: 8px; position: absolute; opacity: 0.8;"><i aria-hidden="true"
                                class="fa fa-edit icon-edit icon" ></i></button></div>
                    <div class="section mb-2 p-1">
                        <div class="lead-info-section-header"><strong>Lead Information</strong></div>
                        <div class="row ">
                            <div class="col"><label class="mr-2 label">Name</label><span class="info-row-value">{{substr($LeadData['leadname'],0,50)}}</span></div>
                        </div>
                        <div class="row ">
                            <div class="col"><label class="mr-2 label">Status</label><span
                                    class="info-row-value">{{$LeadData['lead_status_title']}}</span></div>
                        </div>
                        <div class="row ">
                            <div class="col"><label class="mr-2 label">Referral Source</label><span
                                    class="info-row-value">{{$LeadData['referal_resource_title']}}</span></div>
                        </div>
                        <div class="row ">
                            <div class="col"><label class="mr-2 label">Details</label><span
                                    class="info-row-value">{{$LeadData['lead_detail']}}</span></div>
                        </div>
                    </div>
                    <div class="section mb-3 p-1">
                        <div class="lead-info-section-header"><strong>Email Address</strong></div>
                        <span class="info-row-value"><a href="mailto:{{$LeadData['email']}}">{{$LeadData['email']}}</a></span>
                    </div>
                    <div class="section mb-1 p-1">
                        <div class="lead-info-section-header"><strong>Phone Numbers</strong></div>
                        
                        <?php  if($LeadData['mobile_number']!=NULL){?>
                            <div class="row ">
                                <div class="col">
                                    <label class="mr-2 label">Cell</label><span class="info-row-value">{{$LeadData['mobile_number']}}</span>
                                </div>
                            </div>
                        <?php } ?>

                        <?php  if($LeadData['work_phone']!=NULL){?>
                            <div class="row ">
                                <div class="col">
                                    <label class="mr-2 label">Work</label><span class="info-row-value">{{$LeadData['work_phone']}}</span>
                                </div>
                            </div>
                        <?php } ?>

                        <?php  if($LeadData['home_phone']!=NULL){?>
                            <div class="row ">
                                <div class="col">
                                    <label class="mr-2 label">Home</label><span class="info-row-value">{{$LeadData['home_phone']}}</span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <hr>
                <div>Created at {{date('M d, Y',strtotime($LeadData['created_date_new']))}}</div>by 
                <a href="{{BASE_URL}}contacts/attorneys/{{base64_encode($createdByAndDate['id'])}}" class=" align-items-center user-link" title="{{$createdByAndDate['user_title']}}">{{$createdByAndDate['first_name']}} {{$createdByAndDate['last_name']}}</a>
                <hr>
                <div class="opportunity-details-buttons">
                    <div>
                        <?php
                        if($LeadData['do_not_hire_reason']!=NULL){
                        ?>
                        <a data-toggle="modal"  data-target="#reactivateLead" data-placement="bottom" href="javascript:;"    data-testid="mark-no-hire-button" class="btn btn-primary btn-block mb-3 reactivate" >Reactivate</a>
                        <?php } else { ?>
                        <a data-toggle="modal"  data-target="#doNotHire" data-placement="bottom" href="javascript:;"    data-testid="mark-no-hire-button" class="btn btn-outline-danger btn-block mb-3 did-not-hire" onclick="doNotHire({{$LeadData['lead_additional_info_id']}});">Did Not Hire</a>
                        <?php } ?>
                    </div>
                    <a data-toggle="modal"  data-target="#deleteLead" data-placement="bottom" href="javascript:;"   title="Delete" data-testid="delete-button" class="btn btn-outline-danger btn-block  delete-opportunity" >Delete</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-10">
        <div class="card mb-4 o-hidden">
            @include('pages.errors')
            <div class="card-body">
                
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?php if(in_array(Route::currentRouteName(),["lead_details/info","lead_details/notes","lead_details/activity"])){ echo "active show"; } ?>" id="profile-basic-tab" href="{{URL::to('leads/'.$user_id.'/lead_details/info')}}">Lead Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(in_array(Route::currentRouteName(),["case_details/info","case_details/activity","case_details/tasks","case_details/notes","case_details/calendars","case_details/intake_forms"])){ echo "active show"; } ?>" id="contact-basic-tab"  href="{{URL::to('leads/'.$user_id.'/case_details/info')}}" >Potential Case Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  <?php if(in_array(Route::currentRouteName(),["communications/text_messages","communications/calls","communications/mailbox"])){ echo "active show"; } ?>" id="contact-basic-tab" href="{{URL::to('leads/'.$user_id.'/communications/text_messages')}}">Communications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  <?php if(in_array(Route::currentRouteName(),["case_details/invoices","case_details/trust_history","case_details/credit_history"])){ echo "active show"; } ?>" id="billing-basic-tab" href="{{URL::to('leads/'.$user_id.'/case_details/invoices')}}">Billing</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade <?php if(in_array(Route::currentRouteName(),["lead_details/info","lead_details/notes","lead_details/activity"])){ echo "active show"; } ?> " id="intemInfo" role="tabpanel" aria-labelledby="profile-basic-tab">
                        <div class="nav nav-pills test-info-page-subnav pt-0 pb-2 d-print-none">
                            <div class="nav-item">
                                <a class="nav-link pendo-case-items-info <?php if(Route::currentRouteName()=="lead_details/info"){ echo "active"; } ?>"
                                    data-page="info"
                                    href="{{URL::to('leads/'.$user_id.'/lead_details/info')}}">
                                    <span><i class="i-Info-Window  text-16 mr-1"></i> Lead Info</span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link  pendo-case-notes <?php if(Route::currentRouteName()=="lead_details/notes"){ echo "active"; } ?>" data-page="notes"
                                    href="{{URL::to('leads/'.$user_id.'/lead_details/notes')}}"><span><i
                                            class="i-Evernote text-16 mr-1"></i>
                                        Notes</span></a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link pendo-case-recent-activity <?php if(Route::currentRouteName()=="lead_details/activity"){ echo "active"; } ?>"
                                    data-page="recent_activity"
                                    href="{{URL::to('leads/'.$user_id.'/lead_details/activity')}}">
                                    <span class="d-flex">
                                        <i class="i-Administrator text-16 mr-1" height="40"></i>&nbsp; Activity  </span>
                                </a>
                            </div>

                        </div>
                        <hr class="mt-2">
                        <?php
                        if(Route::currentRouteName()=="lead_details/info"){
                        ?>
                                @include('lead.details.leadInfo',compact('LeadData'))
                        <?php
                        }
                        ?>
                        <?php
                        if(Route::currentRouteName()=="lead_details/notes"){
                        ?>
                                @include('lead.details.leadNotes',compact('notesData','user_id'))
                        <?php
                        }
                        ?>
                        <?php
                        if(Route::currentRouteName()=="lead_details/activity"){
                        ?>
                                @include('lead.details.leadActivity',compact('LeadData'))
                        <?php
                        }
                        ?>    
                    </div>
                    
                    <div class="tab-pane fade <?php if(in_array(Route::currentRouteName(),["case_details/info","case_details/activity","case_details/tasks","case_details/notes","case_details/calendars","case_details/intake_forms"])){ echo "active show"; } ?> " id="timeBilling" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <div class="nav nav-pills test-info-page-subnav pt-0 pb-2 d-print-none">
                            <div class="nav-item">
                                <a class="nav-link pendo-case-items-info <?php if(Route::currentRouteName()=="case_details/info"){ echo "active"; } ?>"
                                    data-page="info"
                                    href="{{URL::to('leads/'.$user_id.'/case_details/info')}}">
                                    <span><i class="i-Info-Window  text-16 mr-1"></i> Case Info</span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link pendo-case-recent-activity <?php if(Route::currentRouteName()=="case_details/activity"){ echo "active"; } ?>"
                                    data-page="recent_activity"
                                    href="{{URL::to('leads/'.$user_id.'/case_details/activity')}}">
                                    <span class="d-flex">
                                        <i class="i-Administrator text-16 mr-1" height="40"></i>&nbsp; Activity  </span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link  pendo-case-calendar <?php if(Route::currentRouteName()=="case_details/calendars"){ echo "active"; } ?>" data-page="calendar"
                                    href="{{URL::to('leads/'.$user_id.'/case_details/calendars')}}"><span>
                                        <i class="i-Calendar-3  text-16 mr-1"></i>Calendar</span></a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link pendo-case-recent-activity <?php if(Route::currentRouteName()=="case_details/tasks"){ echo "active"; } ?>"
                                    data-page="recent_activity"
                                    href="{{URL::to('leads/'.$user_id.'/case_details/tasks')}}">
                                    <span class="d-flex">
                                        <i
                                            class="i-File-Clipboard text-16 mr-1"></i>&nbsp; Tasks  </span>
                                </a>
                            </div>
                            
                            <div class="nav-item">
                                <a class="nav-link pendo-case-recent-activity <?php if(Route::currentRouteName()=="case_details/notes"){ echo "active"; } ?>"
                                    data-page="recent_activity"
                                    href="{{URL::to('leads/'.$user_id.'/case_details/notes')}}">
                                    <span class="d-flex">
                                        <i
                                            class="i-Evernote text-16 mr-1"></i>&nbsp; Case Notes  </span>
                                </a>
                            </div>
                           
                            <div class="nav-item">
                                <a class="nav-link  pendo-case-calendar <?php if(Route::currentRouteName()=="case_details/intake_forms"){ echo "active"; } ?>" data-page="calendar"
                                    href="{{URL::to('leads/'.$user_id.'/case_details/intake_forms')}}"><span>
                                        <i class="i-Settings-Window  text-16 mr-1"></i>Intake Forms</span></a>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <?php
                        if(Route::currentRouteName()=="case_details/info"){
                        ?>
                                @include('lead.details.case_detail.caseInfo',compact('LeadData','assignedToData'))
                        <?php
                        }
                        ?>
                      
                        <?php
                        if(Route::currentRouteName()=="case_details/activity"){
                        ?>
                                @include('lead.details.case_detail.caseHistory',compact('LeadData'))
                        <?php
                        }
                        ?>  

                        <?php
                        if(Route::currentRouteName()=="case_details/tasks"){
                        ?>
                                @include('lead.details.case_detail.taskInfo',compact('LeadData'))
                        <?php
                        }
                        ?>  
                         <?php
                         if(Route::currentRouteName()=="case_details/notes"){
                         ?>
                                 @include('lead.details.case_detail.caseNotes',compact('LeadData','CaseNotesData'))
                         <?php
                         }
                         ?>  

                        <?php
                        if(Route::currentRouteName()=="case_details/calendars"){
                        ?>
                                @include('lead.details.case_detail.calender',compact('LeadData','CaseNotesData'))
                        <?php
                        }
                        ?>  

                        <?php
                        if(Route::currentRouteName()=="case_details/intake_forms"){
                        ?>
                                @include('lead.details.case_detail.intakeFormList',compact('LeadData','CaseNotesData','totalForm'))
                        <?php
                        }
                        ?>  
                    </div>
                    <div class="tab-pane fade <?php if(in_array(Route::currentRouteName(),["communications/text_messages","communications/calls","communications/mailbox"])){ echo "active show"; } ?>" id="communications" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <div class="nav nav-pills test-info-page-subnav pt-0 pb-2 d-print-none">
                            <div class="nav-item">
                                <a class="nav-link pendo-case-recent-activity <?php if(Route::currentRouteName() =="communications/text_messages"){ echo "active"; } ?>" data-page="recent_activity"
                                    href="{{URL::to('leads/'.$user_id.'/communications/text_messages')}}">
                                    <span class="d-flex"> <i class="i-Newspaper-2 text-16 mr-1"></i>&nbsp; Text Messages  </span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link  pendo-case-calendar <?php if(Route::currentRouteName()=="communications/calls"){ echo "active"; } ?>" data-page="calendar" href="{{URL::to('leads/'.$user_id.'/communications/calls')}}">
                                    <span> <i class="i-Old-Telephone text-16 mr-1"></i>Call Log</span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link  pendo-case-calendar <?php if(Route::currentRouteName()=="communications/mailbox"){ echo "active"; } ?>" data-page="calendar" href="{{URL::to('leads/'.$user_id.'/communications/mailbox')}}">
                                    <span> <i class="i-Email  text-16 mr-1"></i>Emails</span>
                                </a>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <?php
                        if(Route::currentRouteName()=="communications/text_messages"){
                        ?>
                                @include('lead.details.communication.text_messages')
                        <?php
                        }
                        ?>  

                        <?php
                        if(Route::currentRouteName()=="communications/calls"){
                        ?>
                                @include('lead.details.communication.calls',compact('totalCalls','getAllFirmUser'))
                        <?php
                        }
                        ?>  
                         <?php
                         if(Route::currentRouteName()=="communications/mailbox"){
                         ?>
                                 @include('lead.details.case_detail.invoices.invoiceList',compact('LeadData','CaseNotesData'))
                         <?php
                         }
                         ?> 

                    </div>
                    <div class="tab-pane fade <?php if(in_array(Route::currentRouteName(),["case_details/invoices","case_details/trust_history","case_details/credit_history"])){ echo "active show"; } ?> " id="intemInfo" role="tabpanel" aria-labelledby="billing-basic-tab">
                        <div class="nav nav-pills test-info-page-subnav pt-0 pb-2 d-print-none">
                                <div class="nav-item">
                                    <a class="nav-link  pendo-case-calendar <?php if(Route::currentRouteName()=="case_details/invoices"){ echo "active"; } ?>" 
                                    data-page="invoice" href="{{URL::to('leads/'.$user_id.'/case_details/invoices')}}"><span>Invoices and Requests</span></a>
                                </div>
                                <div class="nav-item">
                                    <a class="nav-link  pendo-case-notes  <?php if(Route::currentRouteName()=="case_details/trust_history"){ echo "active"; } ?>" data-page="Trust" 
                                    href="{{URL::to('leads/'.$user_id.'/case_details/trust_history')}}"><span>Trust History</span></a>
                                </div>
                                <div class="nav-item">
                                    <a class="nav-link  pendo-case-notes  <?php if(Route::currentRouteName()=="case_details/credit_history"){ echo "active"; } ?>" data-page="Credit" 
                                    href="{{URL::to('leads/'.$user_id.'/case_details/credit_history')}}"><span>Credit History</span></a>
                                </div>                                  
                        </div> 
                        <hr class="mt-2">                                
                        <?php
                        if(Route::currentRouteName()=="case_details/invoices"){
                        ?>
                                @include('lead.details.case_detail.invoices.invoiceList',compact('LeadData'))
                        <?php
                        }
                        ?>                                
                        <?php
                        if(Route::currentRouteName()=="case_details/trust_history"){
                        ?>
                                @include('lead.details.case_detail.invoices.trustHistory',compact('LeadData'))
                        <?php
                        }
                        ?>                               
                        <?php
                        if(Route::currentRouteName()=="case_details/credit_history"){
                        ?>
                                @include('lead.details.case_detail.invoices.creditHistory',compact('LeadData'))
                        <?php
                        }
                        ?> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <aside  id="taskViewArea" class="task-details-drawer">
      </aside>
</div>

<div id="EditCaseModel" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="step-edit-1">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>
<div id="doNotHire" class="modal fade" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Are you sure you want to mark this lead as no hire?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="doNotHireArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="deleteLead" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <form class="DeletLeadForm" id="DeletLeadForm" name="DeletLeadForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Lead</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <span id="response"></span>
                    <div class="row">
                        <div class="col-md-12">
                            <p class="font-weight-bold">Are you sure you want to delete this lead?</p>
                            <p>Deleting this lead will also permanently delete all of the following items associated
                                with this lead and their
                                potential case:</p>
                            <ul>
                                <li>Events</li>
                                <li>Notes</li>
                                <li>Intake Forms</li>
                                <li>Documents (both signed and unsigned documents)</li>
                                <li>Tasks</li>
                                <li>Invoices and all associated payment activity</li>
                            </ul>
                            <div class="alert alert-info show" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="w-100">Leads with recorded {{config('app.name')}} credit card or check transaction
                                        cannot be deleted</div>
                                </div>
                            </div>
                            <input type="hidden" name="user_id" value="{{$user_id}}" id="delete_lead_id">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <a href="#">
                                <button class="btn btn-secondary  m-1" type="button"
                                    data-dismiss="modal">Cancel</button>
                            </a>
                            <button class="btn btn-primary ladda-button example-button m-1" id="submitLead"
                                type="submit">Delete Lead</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="reactivateLead" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <form class="reactivateLeadForm" id="reactivateLeadForm" name="reactivateLeadForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Reactivate Lead</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <span id="response"></span>
                    <div class="row">
                        <div class="col-md-12">
                            Are you sure you want to reactivate this lead?
                            <input type="hidden" name="user_id" value="{{$LeadData['user_id']}}" id="user_id">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <a href="#">
                                <button class="btn btn-secondary  m-1" type="button"
                                    data-dismiss="modal">Cancel</button>
                            </a>
                            <button class="btn btn-primary ladda-button example-button m-1 submit" type="submit">Reactivate Lead</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="deleteTask" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Delete Task</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="eventID">
                        <form class="deleteTask" id="deleteTask" name="deleteTask" method="POST">
                            <div id="showError2" style="display:none"></div>
                            @csrf
                            <input class="form-control" id="task_id" value="" name="task_id" type="hidden">
                            <div class=" col-md-12">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label">
                                        Are you sure you want to delete this task?
                                        <input type="radio" style="display:none;" name="delete_event_type"
                                            checked="checked" class="pick-option mr-2" value="SINGLE_EVENT">
                                    </label>
                                </div>
                                <div class="form-group row float-right">
                                    <a href="#">
                                        <button class="btn btn-secondary  m-1" type="button"
                                            data-dismiss="modal">Cancel</button>
                                    </a>
                                    <button class="btn btn-primary ladda-button example-button m-1" id="submit"
                                        type="submit">
                                        <span class="ladda-label">Yes, Delete</span>
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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadTimeEntryPopupInView" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Time Entry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="addTimeEntryInView">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="loadReminderPopupIndexInView" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static" style="">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Set Event Reminders</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="reminderDataIndexInView">

                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>
<div id="editTaskInView" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Task</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="editTaskAreaInView">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>

        </div>
    </div>
</div>

<div id="deleteIntakeFromFromList" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Delete Intake Form</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="deleteIntakeFromFromListArea">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="emailIntakeFormFromPC" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Email Intake Form</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="emailIntakeFormFromPCArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="addNewInvoice" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Invoice</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="addNewInvoiceArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="editInvoice" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Invoice</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="editInvoiceArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- <div id="deleteInvoice" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Delete Invoice</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="eventID">
                        <form class="deleteInvoiceForm" id="deleteInvoiceForm" name="deleteInvoiceForm" method="POST">
                            <div class="showError" id="showError" style="display:none"></div>
                            @csrf
                            <input class="form-control" id="invoice_id" value="" name="invoice_id" type="hidden">
                            <div class=" col-md-12">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label">
                                        Are you sure you want to delete this invoice?
                                        <input type="radio" style="display:none;" name="delete_event_type"
                                            checked="checked" class="pick-option mr-2" value="SINGLE_EVENT">
                                    </label>
                                </div>
                                <div class="form-group row float-right">
                                    <a href="#">
                                        <button class="btn btn-secondary  m-1" type="button"
                                            data-dismiss="modal">Cancel</button>
                                    </a>
                                    <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                        type="submit">
                                        <span class="ladda-label">Yes, Delete</span>
                                    </button>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                    <div class="col-md-2 form-group mb-3">
                                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader1"
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
</div> -->


<div id="sendInvoice" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Send Invoice</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="sendInvoiceArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="changeProfileImageModal" class="modal fade show modal-overlay" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Edit Profile Picture</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="showError" style="display:none"></div>
                    <div class="row col-md-12">
                        <div class="col-4">
                            <div class="lead-picture-container mb-3 d-flex justify-content-center position-relative h-100" id="lead-picture-container" >
                                <?php if($LeadData['is_published']=="no"){ ?>
                                    <i class="default-avatar-256" data-testid="default-image"></i>
                            <?php } else{ ?> 
                                    <img class="mw-100 mh-100 w-100 h-100 align-items:start m-auto" src="{{BASE_URL}}profile/{{$LeadData['profile_image']}}">
                            <?php } ?>
                                
                            </div>
                        </div>
                        <div class="col-8">
                            <form class="changeProfileImageForm" id="changeProfileImageForm" name="changeProfileImageForm" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" value="{{$LeadData['user_id']}}" name="user_id" id="_contact_id">
                            <div class="p-3 h-100">
                                <div>
                                    <div class="file-uploader align-item-center">
                                        <div class="p-3 text-center" data-testid="test-dropzone" tabindex="0" style="border-width: 2px; border-radius: 5px; border-style: dashed;">
                                            <input id="client_fileupload" name="file" accept="image/png, image/jpeg, image/jpg" multiple="" type="file" autocomplete="off" tabindex="-1" style="opacity: 0;"><p  onclick="openCall()">Click here to add a new image</p>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            </div>
                        </div>
                        <div class="processingLoader" style="display: none;"><img src="{{LOADER}}"> Processing...</div>
                        <div class="uploadingLoader" style="display: none;"><img src="{{LOADER}}"> Uploading...</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                        <?php if($LeadData['is_published']=="yes"){ ?>
                        <div class="form-group row float-left pl-3"> <div><button type="button" class="btn btn-danger" onclick="deleteProfile();">Delete current picture</button></div></div>
                            <?php } ?>
                        <div class="form-group row float-right">
                            <form class="updateProfileImageForm" id="updateProfileImageForm" name="updateProfileImageForm" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" value="{{$LeadData['user_id']}}" name="user_id" id="_contact_id">
                               
                                <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary ladda-button example-button m-1" id="MyImageSubmit"
                                type="submit">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
       
    </div>
</div>

<input type="text" value="" id="myInput" style="opacity: 00000;">
@include('lead.details.commonPopup')
@include('lead.details.communication.CommunicationCommonPopup')
<style>
    .notes-container {
        border: solid 1px #f5f2f2;
    }
    .note-row {
        border-bottom: solid 1px #f5f2f2;
        margin-left: 1px;
        margin-right: 1px;    display: flex;
    }
    td,th{
        font-size: 13px;
    }
    .morecontent span {
        display: none;
    }
    .morelink {
        display: block;
    }
    .afterLoadClass{
            position: absolute; top: 0px; width: 560px; right: 0px; background-color: white; height: 100%; display: inline-table; box-shadow: rgba(0, 0, 0, 0.5) 1px 0px 7px; z-index: 100; min-height: 850px;
    }
    
    </style>
@endsection

@section('page-js')
<script src="{{ asset('assets/js/custom/lead/converttocase.js') }}"></script>
<script type="text/javascript">
  function myFunction(id) {
    var links=$("#"+id).attr("link");
    $("#myInput").val(links);
        var copyText = document.getElementById("myInput");
        /* Select the text field */
        copyText.select();
        copyText.setSelectionRange(0, 99999); /*For mobile devices*/
        /* Copy the text inside the text field */
        document.execCommand("copy");
        /* Alert the copied text */
        // alert("Copied the text: " + copyText.value);

        toastr.success('Link Copied', "", {
            progressBar: !0,
            positionClass: "toast-top-full-width",
            containerId: "toast-top-full-width"
        });
    }
    $('button').attr('disabled',false);
    // $('#submit').attr('disabled',true);
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ready(function () {
        // $("#submit").attr("disabled","disabled");
        $("#taskViewArea").hide();
        $("#statusEditor").show();
        $("#statusList").hide();
        $("#innerLoader").css('display', 'none');

        $('#ShowColorPicker').on('hidden.bs.modal', function () {
            window.location.reload();
        });
        $('#DeleteModal').on('hidden.bs.modal', function () {
            window.location.reload();
        });

        $('#reactivateLeadForm').submit(function (e) {
            $(".submit").attr("disabled", true);
            $(".innerLoader").css('display', 'block');
            e.preventDefault();

            if (!$('#reactivateLeadForm').valid()) {
                $(".innerLoader").css('display', 'none');
                $('.submit').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#reactivateLeadForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/reactivateLead", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&reactivate=yes';
                },
                success: function (res) {
                    $(".innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('#showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('#showError').append(errotHtml);
                        $('#showError').show();
                        $(".innerLoader").css('display', 'none');
                        $('.submit').removeAttr("disabled");
                        return false;
                    } else {
                        window.location.reload();
                    }
                }
            });
        });
        $('#DeletLeadForm').submit(function (e) {
            $("#submitLead").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            e.preventDefault();

            if (!$('#DeletLeadForm').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#DeletLeadForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/deleteLead", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                    $("#innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('#showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('#showError').append(errotHtml);
                        $('#showError').show();
                        $("#innerLoader").css('display', 'none');
                        $('#submitLead').removeAttr("disabled");
                        return false;
                    } else {
                        window.location.href=baseUrl+'/leads/statuses';
                    }
                }
            });
        })

        var dataTable =  $('#employee-grid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/leads/leadActivity", // json datasource
                type: "post",  // method  , by default get
                data :{ 'user_id' : '{{$user_id}}' },
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            // "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [{ data: 'id'}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                   if(aData.acrtivity_title=='added'){
                       var img='<img src="'+baseUrl+'/public/icon/activity_note_added.png" width="27" height="21">';
               
                   }else if(aData.acrtivity_title=='edited'){
                        var img='<img src="'+baseUrl+'/public/icon/activity_note_updated.png" width="27" height="21">';
                   
                   }else if(aData.acrtivity_title=='deleted'){
                        var img='<img src="'+baseUrl+'/public/icon/activity_note_deleted.png" width="27" height="21">';

                   }else{
                        var img='<i class="fas fa-user text-black-50 ml-1"></i>';
                   }
                   var note ='';
                   if(aData.acrtivity_title=='added' || aData.acrtivity_title=='edited' || aData.acrtivity_title=='deleted'){
                        var note =' a note for lead ';
                   }

                   $('td:eq(0)', nRow).html('<div class="text-left"> '+img+' <a class="name" href="'+baseUrl+'/contacts/attorneys/'+aData.decode_id+'">'+aData.created_by_name+'</a> '+aData.acrtivity_title+note+' <a class="name" href="'+baseUrl+'/leads/'+aData.for_lead+'">'+aData.lead_name+'</a> <abbr class="timeago" title="'+aData.note_created_at+'">about  '+aData.time_ago+'</abbr> via web </div>');
                
                },
                "initComplete": function(settings, json) {
                    $("#employee-grid thead").remove();
                    $('td').css('font-size',parseInt('13px'));  

                }
        });

        var dataTable =  $('#caseHistoryGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/leads/caseActivityHistory", // json datasource
                type: "post",  // method  , by default get
                data :{ 'user_id' : '{{$user_id}}' },
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            // "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [{ data: 'id'}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                   if(aData.acrtivity_title=='added'){
                       var img='<img src="'+baseUrl+'/public/icon/activity_note_added.png" width="27" height="21">';
               
                   }else if(aData.acrtivity_title=='updated'){
                        var img='<img src="'+baseUrl+'/public/icon/activity_note_updated.png" width="27" height="21">';
                   
                   }
                   if(aData.type==1){
                       var type=" task ";
                       var linkName='<a class="name" href="'+baseUrl+'/leads/tasks">'+aData.task_name+'</a>';
                   }else{
                        var type=" case ";
                       var linkName='<a class="name" href="'+baseUrl+'/leads/'+aData.for_lead+'/case_details/info">'+aData.case_name+'</a>';
                   }

                   var leadName= ' | <a class="name" href="'+baseUrl+'/leads/'+aData.for_lead+'/lead_details/info">'+aData.lead_name+'</a>';

                   var userTitle='';
                   if(aData.user_title!=''){
                    var userTitle='('+aData.user_title+')';
                   }
                   $('td:eq(0)', nRow).html('<div class="text-left"> '+img+' <a class="name" href="'+baseUrl+'/contacts/attorneys/'+aData.decode_id+'">'+aData.created_by_name+''+userTitle+'</a> '+aData.acrtivity_title+' '+type+' '+linkName+' <a class="name" href="'+baseUrl+'/leads/'+aData.for_lead+'">'+aData.lead_name+'</a> <abbr class="timeago" title="'+aData.note_created_at+'">about  '+aData.time_ago+'</abbr> via web '+leadName+'</div>');
                
                },
                "initComplete": function(settings, json) {
                    $("#caseHistoryGrid thead").remove();
                    $('td').css('font-size',parseInt('13px'));  
                }
        });

        var dataTable =  $('#taskList').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"p><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false, "ordering": false,
            "ajax":{
                url :baseUrl +"/leads/loadAllTaskByLead", // json datasource
                type: "post", 
                data :{ 'id' : {{$user_id}},'show':'{{$show}}' },
                error: function(){  
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            pageResize: true,  
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'task_title',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    if(aData.status=="1"){
                         $('td:eq(0)', nRow).html('<div class="text-left"><a href="javascript:void(0);" onclick="taskMarkAsInCompleted('+aData.id+');"><button  class="btn btn-outline-secondary m-1 btn-rounded" type="button"> Mark Incomplete</button></a><a href="#" onclick="loadTaskView('+aData.id+')"> '+aData.task_title+'</a></div>');    
                    }else{
                        $('td:eq(0)', nRow).html('<div class="text-left"><a href="javascript:void(0);" onclick="taskMarkAsCompleted('+aData.id+');"><button  class="btn btn-outline-secondary m-1 btn-rounded" type="button"> Mark Complete</button></a><a href="#" onclick="loadTaskView('+aData.id+')"> '+aData.task_title+'</a></div>');    
                    }
                    
                   
                    $('td:eq(1)', nRow).html('<div class="d-flex align-items-center">'+aData.task_due_date+'</div>');

                    var obj = JSON.parse(aData.assign_to);
                var i;
                var urlList='';
                if(obj.length>1){
                    for (i = 0; i < obj.length; ++i) {
                        urlList+=obj[i].first_name+' '+obj[i].last_name+'<br>';
                    }
                    $('td:eq(2)', nRow).html('<div class="text-left mt-2"><a class=" event-name d-flex align-items-center" tabindex="0" role="button" href="javascript:;" data-toggle="popover"  style="float:left;" data-trigger="hover" data-content="<b>Assign To:</b><br>'+urlList+'" data-html="true" data-original-title=""><i class="fas fa-user-friends mr-1"></i>'+obj.length+' Users</a></div>');
                }else{
                    for (i = 0; i < obj.length; ++i) {
                        urlList+='<a href="'+baseUrl+'/contacts/attorneys/'+obj[i].decode_user_id+'">'+obj[i].first_name+' '+obj[i].last_name+'</a><br>';
                    }
                    $('td:eq(2)', nRow).html('<div class="text-left">'+urlList+'</div>');
                }
                    
                $('td:eq(3)', nRow).html('<div class="d-flex align-items-center float-right"><a data-toggle="modal"  data-target="#editTask" onclick="editTask('+aData.id+');" data-placement="bottom" href="javascript:;"   title="Edit" data-testid="edit-button" class="btn btn-link"><i class="fas fa-pencil-alt  m-2"></i><//a><a data-toggle="modal"  data-target="#deleteTask" data-placement="bottom" href="javascript:;"   title="Delete" data-testid="delete-button" class="btn btn-link" onclick="deleteTaskFunction('+aData.id+');"><i class="fas fa-trash "></i></a></div>');
                },
                "initComplete": function(settings, json) {
                    $('th').css('font-size',parseInt('13px'));  
                    $('td').css('font-size',parseInt('13px'));
                    $("[data-toggle=popover]").popover();  
                }
        });

        var dataTable =  $('#intakeFormList').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"p><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false, "ordering": false,
            "ajax":{
                url :baseUrl +"/leads/loadIntakeForms", // json datasource
                type: "post", 
                data :{ 'id' : '{{$user_id}}' },
                error: function(){  
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            pageResize: true,  
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    if(aData.is_filled=='yes'){
                        $('td:eq(0)', nRow).html('<div class="text-left"><a target="_blank" href="'+baseUrl+'/forms/'+aData.unique_token+'/show_pdf">'+aData.form_name+'</a></div>'); 
                    }else{
                        $('td:eq(0)', nRow).html('<div class="text-left"><a href="'+baseUrl+'/cform/'+aData.form_unique_id+'">'+aData.form_name+'</a></div>'); 
                    }

                    $('td:eq(1)', nRow).html('<div class="text-left">'+aData.added_date+'</div>'); 

                    if(aData.status=="0"){
                        var fLabel='<span class="intake-form-status d-flex align-items-center">Sent via  {{config('app.name')}}</span>';
                    }else if(aData.status=="1"){
                        var fLabel='<span class="intake-form-status d-flex align-items-center"><i class="fas fa-circle text-warning mr-2"></i>Pending</span>';
                    }else if(aData.status=="2"){
                        var fLabel='<span class="intake-form-status d-flex align-items-center"><i class="fas fa-circle text-success mr-2"></i>Submitted '+aData.submitted_date+'</span>';
                    }else if(aData.status=="3"){
                        var fLabel='<span class="intake-form-status d-flex align-items-center"><i class="far fa-circle text-black-50 mr-2"></i>Pending</span>';
                    }
                    $('td:eq(2)', nRow).html('<div class="text-left">'+fLabel+'</div>');

                    if(aData.is_filled=='yes'){
                        var downloadOption='<a  onclick="downloadIntakeForm('+aData.id+');" data-testid="edit-button" class="btn btn-link"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Download"><i class="fas fa-cloud-download-alt align-middle"></i></span></a>';
                        // var downloadOption='<a onclick="showLoad()" href="{{BASE_URL}}leads/downloadIntakeForm?id='+aData.id+'" data-testid="edit-button" class="btn btn-link"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Download"><i class="fas fa-cloud-download-alt align-middle"></i></span></a>';

                    }else{
                        var downloadOption='<a data-placement="bottom" href="javascript:;"  data-testid="edit-button" class="btn btn-link" style="color:gray;"><i class="fas fa-cloud-download-alt align-middle disabled"></i></a>';
                    }

                    if(aData.status=="2"){
                        var g='<a  data-testid="edit-button" style="color:gray;" class="btn btn-link"><span data-toggle="tooltip" data-trigger="hover" title=""><i class="fas fa-paper-plane align-middle"></i></span></a><a href="javascript:;"   style="color:gray;" class="btn btn-link copyButton"><span ><i class="fas fa-link align-middle" data="MyText"></i></span></a>';
                    }else{
                        var g='<a data-toggle="modal"  data-target="#emailIntakeFormFromPC" onclick="emailFormFunction('+aData.intake_form_id+');" data-placement="bottom" href="javascript:;"   title="Edit" data-testid="edit-button" class="btn btn-link"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Send Email"><i class="fas fa-paper-plane align-middle"></i></span></a><a onclick="myFunction('+aData.id+')" link="'+baseUrl+'/cform/'+aData.form_unique_id+'" id="'+aData.id+'" data-placement="bottom" href="javascript:;"   title="Copy"  class="btn btn-link copyButton"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Copy Link"><i class="fas fa-link align-middle" data="MyText"></i></span></a>';
                    }

                    $('td:eq(3)', nRow).html('<div class="d-flex align-items-center float-right">'+downloadOption+''+g+'<a data-toggle="modal"  data-target="#deleteIntakeFromFromList" data-placement="bottom" href="javascript:;"   title="Delete" data-testid="delete-button" class="btn btn-link" onclick="deleteIntakeFromFromList('+aData.intake_form_id+','+aData.id+');" ><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Delete"><i class="fas fa-trash "></i></span></a></div>');
                },
                "initComplete": function(settings, json) { 
                    $("[data-toggle=popover]").popover();
                    $("[data-toggle=tooltip]").tooltip();
                }
        });

        var dataTableinvoiceList =  $('#invoiceList_old').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"p><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false, "ordering": false,
            "ajax":{
                url :baseUrl +"/leads/loadInvoices", // json datasource
                type: "post", 
                data :{ 'user_id' : '{{$user_id}}' },
                error: function(){  
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            pageResize: true,  
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    $('td:eq(0)', nRow).html('<div class="text-left"><a href="'+baseUrl+'/bills/invoices/potentialview/'+aData.decoded_id+'">'+aData.invoice_num+'</a></div>'); 
                   
                    if(aData.invoice_amount==null){
                        $('td:eq(1)', nRow).html('<div class="text-left"></div>'); 
                    }else{
                        $('td:eq(1)', nRow).html('<div class="text-left">$'+aData.invoice_amt+'</div>'); 
                    }
                    
                    $('td:eq(2)', nRow).html('<div class="text-left">$'+aData.invoice_paid_amt+'</div>'); 
                    $('td:eq(3)', nRow).html('<div class="text-left">'+aData.added_date+'</div>'); 
                    $('td:eq(4)', nRow).html('<div class="text-left">'+aData.newduedate+'</div>'); 
                    
                    if(aData.status=="2"){
                        var fLabel='<span class="intake-form-status d-flex align-items-center"><i class="fas fa-circle text-black mr-2"></i>Unsent</span>';
                    }else if(aData.status=="1"){
                        var fLabel='<span class="intake-form-status d-flex align-items-center"><i class="fas fa-circle text-success mr-2"></i>Sent</span>';
                    }
                    if(aData.is_overdue=="overdue"){
                        var fLabel='<span class="intake-form-status d-flex align-items-center"><i class="fas fa-circle error mr-2"></i>Overdue</span>';

                    }
                    if(aData.is_pay!=""){
                        if(aData.is_pay=="Partial"){
                            var f='text-warning';
                        }else{
                            var f='text-success';
                        }
                        var fLabel='<span class="intake-form-status d-flex align-items-center"><i class="fas fa-circle  '+f+' mr-2"></i>'+aData.is_pay+'</span>';

                    }
                    $('td:eq(5)', nRow).html('<div class="text-left">'+fLabel+'</div>');

                  

                    var downloadOption='<a data-toggle="modal"  data-target="#downloadInvoice" onclick="downloadInvoice('+aData.id+');" data-placement="bottom" href="javascript:;"   title="Edit" data-testid="edit-button" class="btn btn-link"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Download"><i class="fas fa fa-download align-middle"></i></span></a>';

                    var emailOption='<a data-toggle="modal"  data-target="#sendInvoice" onclick="sendInvoice('+aData.id+');" data-placement="bottom" href="javascript:;"   title="Edit" data-testid="edit-button" class="btn btn-link"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Send Email"><i class="fas fa fa-envelope align-middle"></i></span></a>';

                    var editOption='<a  data-toggle="modal"  data-target="#editInvoice" onclick="editInvoice('+aData.id+')" data-placement="bottom"   href="javascript:;"  class="btn btn-link copyButton"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Edit"><i class="fas fa-pen align-middle" data="MyText"></i></span></a>';

                    var deleteOption='<a data-toggle="modal"  data-target="#deleteInvoice" data-placement="bottom" href="javascript:;"   title="Delete" data-testid="delete-button" class="btn btn-link" onclick="deleteInvoiceFunction('+aData.id+','+aData.id+');" ><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Delete"><i class="fas fa-trash "></i></span></a>';
                    
                    var payOption='';
                    if(aData.is_pay!="Paid"){
                        payOption='<a  data-toggle="modal"  data-target="#payInvoice" onclick="payPotentialInvoice('+aData.id+')" data-placement="bottom"   href="javascript:;"  class="btn btn-link"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Pay"><i class="fas fa-dollar-sign align-middle" data="MyText"></i></span></a>';
                    }
                    

                    $('td:eq(6)', nRow).html('<div class="d-flex align-items-center float-right">'+downloadOption+' '+emailOption+' '+payOption+' '+editOption+' '+deleteOption+'</div>');
                },
                "initComplete": function(settings, json) {
                    $('th').css('font-size',parseInt('13px'));  
                    $('td').css('font-size',parseInt('13px'));      
                    $("[data-toggle=popover]").popover();
                    $("[data-toggle=tooltip]").tooltip();
                }
        });
        $('#addNewInvoice,#payInvoice').on('hidden.bs.modal', function () {
            dataTableinvoiceList.ajax.reload(null, false);
        });

        var showChar = 300;  // How many characters are shown by default
        var ellipsestext = "...";
        var moretext = "Show more";
        var lesstext = "Show less";
        
        var dataTablecallList =  $('#callList').DataTable( {
            serverSide: true,
            "dom": '<"toolbar"><"top">rt<"bottom"p><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false, "ordering": false,
            "ajax":{
                url :baseUrl +"/leads/loadCalls", // json datasource
                type: "post", 
                data :{ 'user_id' : '{{$user_id}}','callfor':'{{$callfor}}' ,'status':'{{$status}}','type':'{{$type}}' },
                error: function(){  
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            pageResize: true,  
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    $('td:eq(0)', nRow).html('<div class="text-left">'+aData.utc_time+' By <a href="'+baseUrl+'/contacts/attorneys/'+aData.created_by_decode_id+'">'+aData.created_name+'</a></div>'); 
                    
                    if(aData.caller_name==null){
                        $('td:eq(1)', nRow).html('<div class="text-left">'+aData.caller_name_text+'<br>'+aData.phone_number+'</div>');
                    }else{
                        $('td:eq(1)', nRow).html('<div class="text-left"><a href="'+baseUrl+'/contacts/clients/'+aData.caller_name+'">'+aData.caller_full_name+'</a><br>'+aData.phone_number+'</div>');
                    }

                    $('td:eq(2)', nRow).html('<div class="text-left"><a href="'+baseUrl+'/contacts/attorneys/'+aData.call_for_decode_id+'">'+aData.call_for_name+'</a></div>'); 

                    if(aData.call_type=="0"){
                        var fLabel='Incoming';
                    }else if(aData.call_type=="1"){
                        var fLabel='Outgoing';
                    }
                    $('td:eq(3)', nRow).html('<div class="text-left">'+fLabel+'</div>');

                    $('td:eq(4)', nRow).html('<div class="text-left more">'+aData.message+'</div>');

                    if(aData.call_resolved=="yes"){
                        var downloadOption='<label class="switch pr-3 switch-success mr-3"><span id="ListOresolveText_'+aData.id+'">Resolved</span><span id="ListOnonResolveText_'+aData.id+'" style="display:none;" class="error">Unresolved</span><input id="'+aData.id+'" type="checkbox" class="yes" name="call_resolved" checked="checked"><span class="slider"></span></label>';
                    }else if(aData.call_resolved=="no"){
                        var downloadOption='<label class="switch pr-3 switch-success mr-3"><span id="ListOresolveText_'+aData.id+'" style="display:none;" class="error">Resolved</span><span id="ListOnonResolveText_'+aData.id+'" >Unresolved</span><input id="'+aData.id+'" type="checkbox" class="no"  name="call_resolved" ><span class="slider"></span></label>';
                    }
                    $('td:eq(5)', nRow).html('<div class="d-flex align-items-center">'+downloadOption+'</div>');

                    var addTaskOption='<a  data-toggle="modal"  data-target="#addTaskFromLog" onclick="addTaskFromLog('+aData.id+')" data-placement="bottom"   href="javascript:;"  class="btn btn-link copyButton"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Add Task"><i class="fas fa-tasks  align-middle" data="MyText"></i></span></a>';


                    var editOption='<a  data-toggle="modal"  data-target="#editCall" onclick="editCall('+aData.id+')" data-placement="bottom"   href="javascript:;"  class="btn btn-link copyButton"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Edit Call Log"><i class="fas fa-pen align-middle" data="MyText"></i></span></a>';

                    var deleteOption='<a data-toggle="modal"  data-target="#deleteCallLog" data-placement="bottom" href="javascript:;"   title="Delete" data-testid="delete-button" class="btn btn-link" onclick="deleteCallLog('+aData.id+');" ><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Delete Call Log"><i class="fas fa-trash "></i></span></a>';


                    $('td:eq(6)', nRow).html('<div class="d-flex align-items-center float-right">'+addTaskOption+'  ' +editOption+' '+deleteOption+'</div>');
                },
                "initComplete": function(settings, json) {    
                    $("[data-toggle=popover]").popover();
                    $("[data-toggle=tooltip]").tooltip();  
                    var currentSize=localStorage.getItem("callList");
                    $('td').css('font-size', currentSize +'px'); 

                    $('.more').each(function() {
                        var content = $(this).html();
                        if(content.length > showChar) {
                            var c = content.substr(0, showChar);
                            var h = content.substr(showChar, content.length - showChar);
                            var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';
                            $(this).html(html);
                        }
                    });
                    $(".morelink").click(function(){
                        if($(this).hasClass("less")) {
                            $(this).removeClass("less");
                            $(this).html(moretext);
                        } else {
                            $(this).addClass("less");
                            $(this).html(lesstext);
                        }
                        $(this).parent().prev().toggle();
                        $(this).prev().toggle();
                        return false;
                    }); 
                    $('input[name="call_resolved"]').click(function () {
                        var id=$(this).attr("id");
                        $.ajax({
                            type: "POST",
                            url:  baseUrl +"/leads/changeCallType", // json datasource
                            data: {'id':id},
                            success: function (res) {
                                if(res.report=='yes'){
                                    $("#ListOresolveText_"+id).show();
                                    $("#ListOnonResolveText_"+id).hide();
                                }else{
                                    $("#ListOresolveText_"+id).hide();
                                    $("#ListOnonResolveText_"+id).show();
                                }
                                // dataTablecallList.ajax.reload();
                            }
                        })
                    });
                }
        });

       // $("div.toolbar").html('<small class="text-muted mx-1">Text Size</small><button type="button" arial-label="Decrease text size" data-testid="dec-text-size" class="btn-sm py-0 px-1 mx-1 btn btn-outline-light decrease "><i class="fas fa-minus fa-xs"></i></button><button type="button" arial-label="Increase text size" data-testid="inc-text-size" class="btn-sm py-0 px-1 mx-1 btn btn-outline-light increase" ><i class="fas fa-plus fa-xs"></i></button>');
        // Toolbar extra buttons
        
        if(localStorage.getItem("callList")==""){
            localStorage.setItem("callList","13");
        }     
        var originalSize = $('td').css('font-size');        
        var currentSize=localStorage.getItem("callList");
        $('td').css('font-size', currentSize +'px');    
        //Increase the font size 
        $(".increase").click(function(){         
            modifyFontSize('increase');  
        });     
        
        //Decrease the font size
        $(".decrease").click(function(){   
            modifyFontSize('decrease');  
        });

         var btnFinish = $('<button></button>').text('Finish')
            .addClass('btn btn-info')
            .on('click', function () { alert('Finish Clicked'); });
        var btnCancel = $('<button></button>').text('Cancel')
            .addClass('btn btn-danger')
            .on('click', function () { $('#smartwizard').smartWizard("reset"); });
            
  
        $('#smartwizard').smartWizard({
            selected: 0,
            theme: 'default',
            transitionEffect: 'fade',
            showStepURLhash: false,
            enableURLhash: false,
            backButtonSupport: true, // Enable the back button support
            keyNavigation: false,
            toolbarSettings: {
                toolbarPosition: 'none',
                toolbarButtonPosition: 'end',
                toolbarExtraButtons: [btnFinish, btnCancel]
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
       
    });
    $('#deleteTask').submit(function (e) {

        $("#innerLoader1").css('display', 'block');
        e.preventDefault();
        var dataString = $("form").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/deleteTask", // json datasource
            data: dataString,
            success: function (res) {
                $("#innerLoader1").css('display', 'block');
                if (res.errors != '') {
                    $('#showError2').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError2').append(errotHtml);
                    $('#showError2').show();
                    $("#innerLoader1").css('display', 'none');
                    return false;
                } else {
                    window.location.reload();
                }
            }
        });

        $('.collapsed').click(function() { 

            
        });
    });
    $('#deleteNoteForm').submit(function (e) {
            $("#submit").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            e.preventDefault();

            if (!$('#deleteNoteForm').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#deleteNoteForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/deleteNote", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                    $("#innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('#showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('#showError').append(errotHtml);
                        $('#showError').show();
                        $("#innerLoader").css('display', 'none');
                        $('#submit').removeAttr("disabled");
                        return false;
                    } else {
                        window.location.reload();
                    }
                }
            });
    });

    $('#deleteCaseNoteForm').submit(function (e) {
            $("#submit").attr("disabled", true);
            $(".innerLoader").css('display', 'block');
            e.preventDefault();

            if (!$('#deleteCaseNoteForm').valid()) {
                $(".innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#deleteCaseNoteForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/deleteCaseNote", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                    $(".innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('#showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('#showError').append(errotHtml);
                        $('#showError').show();
                        $(".innerLoader").css('display', 'none');
                        $('#submit').removeAttr("disabled");
                        return false;
                    } else {
                        window.location.reload();
                    }
                }
            });
    });
    $('#deleteInvoiceForm_old').submit(function (e) {
        $(".submit").attr("disabled", true);
        $(".innerLoader").css('display', 'block');
        e.preventDefault();
        var dataString = $("#deleteInvoiceForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/deleteInvoice", // json datasource
            data: dataString,
            success: function (res) {
                $(".innerLoader").css('display', 'block');
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
                    $(".innerLoader").css('display', 'none');
                    $('.submit').removeAttr("disabled");
                    return false;
                } else {
                    window.location.reload();
                }
            }
        });
    });
    $('#deleteCallLogForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteCallLogForm').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteCallLogForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/deleteCallLog", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
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

        $("#client_fileupload").on("change", function() {
            var fileName = $(this).val();
            if(fileName!=''){
             $("#changeProfileImageForm").submit();
            }
        });

        $('#changeProfileImageForm').submit(function (e) {
            $(".processingLoader").show();
            e.preventDefault();
            var dataString = new FormData(this);
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/uploadImage", // json datasource
                data: dataString,
                cache:false,
                contentType: false,
                processData: false,
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
                        $(".processingLoader").hide();
                        return false;
                    } else {
                       cropProfileImage();
                       $("#submit").removeAttr("disabled");
                       $(".processingLoader").hide();
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
        $('#updateProfileImageForm').submit(function (e) {
            $(".uploadingLoader").show();
            e.preventDefault();
            var dataString = '';
            dataString = $("#updateProfileImageForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/updateProfileImageForm", // json datasource
                data: dataString,
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
                        $(".uploadingLoader").hide();
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
        function deleteProfile(){
            $(".file-uploader").html('<img src="{{LOADER}}"">Deleting profile image...');
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/deleteProfileImageForm", // json datasource
                data: {"user_id": "{{$LeadData['user_id']}}"},
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                    window.location.reload();
                }
            });
        }
        function cropProfileImage(){
            $(".loaderShow").show();
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/cropImage", 
                data: {"user_id": "{{$LeadData['user_id']}}"},
                success: function (res) {
                    $("#lead-picture-container").html("<img src='"+res.image+"'>");
                    $(".loaderShow").hide();
                    $("#MyImageSubmit").removeAttr("disabled");
                    afterLoader();
                }
            })
        }
    function modifyFontSize(flag) {  
        var min = 13;
        var max = 19;
        var divElement = $('td');  
        var currentFontSize = parseInt(divElement.css('font-size'));  

        if (flag == 'increase')  
            currentFontSize += 3;  
        else if (flag == 'decrease')  
            currentFontSize -= 3;  
        else  
            currentFontSize = 13;  
            if(currentFontSize>=min && currentFontSize<=max){
            divElement.css('font-size', currentFontSize); 
            localStorage.setItem("callList",currentFontSize);
        }
    }  

    function deleteTaskFunction(id) {
        $("#task_id").val(id);
    }

    function doNotHire(id) {
        $("#preloader").show();
        $("#doNotHireArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/doNotHireFromDetail", // json datasource
                data: {'id':id},
                success: function (res) {
                    $("#doNotHireArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function ShowStatus() {
        $("#statusEditor").toggle();
        $("#statusList").toggle();
    }

  
    function updateCaseDetails(id) {

        $("#preloader").show();
        $("#step-edit-1").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/case/editCase", // json datasource
                data: {
                    "case_id": id
                },
                success: function (res) {
                    $("#step-edit-1").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadTimeEntryPopupInView(id) {
        $("#preloader").show();
        $("#addTimeEntryInView").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadTimeEntryPopup", // json datasource
                data: {
                    "task_id": id,
                    "from_view":"yes"
                },
                success: function (res) {
                    $("#addTimeEntryInView").html('<img src="{{LOADER}}"> Loading...');
                    $("#addTimeEntryInView").html(res);
                    $("#preloader").hide();
                    
                }
            })
        })
    }

    function loadReminderPopupIndexInView(task_id) {
        $("#reminderDataIndexInView").html('<img src="{{LOADER}}"> Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadTaskReminderPopupIndex", // json datasource
                data: {
                    "task_id": task_id,
                    "from_view":"yes"
                },
                success: function (res) {
                    $("#reminderDataIndexInView").html('<img src="{{LOADER}}"> Loading...');
                    $("#reminderDataIndexInView").html(res);
                    $("#preloader").hide();

                }
            })
        })
    }
    function deleteIntakeFromFromList(id,primary_id) {
       
        $("#preloader").show();
        $("#deleteIntakeFromFromListArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/deleteIntakeFormFromList", // json datasource
                data: {"id": id,'primary_id':primary_id},
                success: function (res) {
                    $("#deleteIntakeFromFromListArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function emailFormFunction(id) {
        $("#preloader").show();
        $("#emailIntakeFormFromPCArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/popupOpenSendEmailIntakeFormFromList", // json datasource
                data: {'form_id':id,"lead_id":{{$LeadData['user_id']}}},
                success: function (res) {
                $("#emailIntakeFormFromPCArea").html(res);
                    $("#preloader").hide();
                }
            })
        }) 
    }  
    
    function downloadIntakeForm(id) {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/downloadIntakeForm?id="+id, // json datasource
                data: {'id':id},
                success: function (res) {
                    var url = res.url;
                    // if(res.data!=''){
                    //     window.open(url, '_blank');
                    // }else{
                    //     toastr.error('No data found.')
                    // }

                    // console.log(e.file);
                    var link = document.createElement('a');
                    link.href = res.url;
                    link.download = res.file_name;
                    link.click();
                    link.remove()
                    $("#preloader").hide();
                }
            })
        }) 
    }

    function viewIntakeForm(id) {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/inlineViewIntakeForm?id="+id, // json datasource
                data: {'id':id},
                success: function (res) {
                    var url = res.url;
                    if(res.data!=''){
                        window.open(url, '_blank');
                    }else{
                        toastr.error('No data found.')
                    }
                    $("#preloader").hide();
                }
            })
        }) 
    }
    function showLoad(){
        $("#preloader").show();
    }
    //Invoice
    function addNewInvoice() {
        $("#addNewInvoiceArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/addNewInvoices", // json datasource
                data: {'user_id':{{$user_id}}},
                success: function (res) {
                    $("#addNewInvoiceArea").html(res);
                    $("#preloader").hide();
                }
            })
        }) 
    }  
    function editInvoice(id) {
        $("#editInvoiceArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/editInvoice", // json datasource
                data: {'id':id},
                success: function (res) {
                    $("#editInvoiceArea").html(res);
                    $("#preloader").hide();
                }
            })
        }) 
    }  
    function deleteInvoiceFunction(id) {
        $("#invoice_id").val(id);
    }
    function sendInvoice(id) {
        $("#sendInvoiceArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/openSendInvoicePopup", // json datasource
                data: {'invoice_id':id,'user_id':{{$user_id}}},
                success: function (res) {
                    $("#sendInvoiceArea").html(res);
                    $("#preloader").hide();
                }
            })
        }) 
    }  

    function downloadInvoice(id) {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/downloadInvoiceForm?id="+id, 
                data: {'id':id},
                success: function (res) {
                    var url = res.url;
                    var link = document.createElement('a');
                    link.href = res.url;
                    link.download = res.file_name;
                    link.click();
                    link.remove()
                    $("#preloader").hide();
                }
            })
        }) 
    }
    function changeProfileImage(){
        $("#changeProfileImageModal").modal("show");
        $(".showError").hide();
    }
    function openCall(){
        $('input[type="file"]').click();
    }

    setTimeout(function(){  
        $('#taskViewArea').addClass('afterLoadClass'); 
        $("#MyImageSubmit").attr("disabled","disabled");
    }, 500);
</script>
@stop
