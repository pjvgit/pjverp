@extends('layouts.master')
@section('main-content')
@section('title', $CaseMaster->case_title. ' - Case Details')

<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 
$userTitle = unserialize(USER_TITLE); 

?><div class="d-flex align-items-center pl-4 pb-4">
    <i class="fas fa-3x fa-suitcase"></i>

    <div class="case-name-holder d-flex flex-column pl-2">
        <h2 id="case-name-header" class="test-case-name-header mb-0">
            {{$CaseMaster->case_title}} 

            <?php if($CaseMaster->case_close_date!=NULL){?>
            <span class="text-danger"> [Closed]</span>
            <?php } ?>
        </h2>

        <div class="d-flex">
            <div id="court-case-number-header" class="case-number-header border-right pr-2 d-print-none">
                {{$CaseMaster->case_unique_number}}
            </div>

            <div class="statute-of-limitations-field" data-can-edit-case="true" data-court-case-id="12065562"
                data-sol-date="" data-sol-satisfied="false">
                <div class="d-flex align-items-center pl-2 d-print-none">
                    <div class="pr-2">Statute of Limitations:</div>
                    <div class="text-muted">
                        <?php 
                      if(isset($CaseMaster->case_statute_date)){
                        echo date('m/d/Y',strtotime($CaseMaster->case_statute_date));

                      }else{
                        echo "Not Specified";
                      }?>
                    </div>
                </div>
            </div>
        </div>

        <div class="case-date-generated only-print">
            <span class="case-generated-info">
                Case details generated <?php 
                if(isset($CaseMaster->case_created_date)){
                  echo date('m/d/Y',strtotime($CaseMaster->case_created_date));

                }else{
                  echo "Not Specified";
                }?>
            </span>
        </div>
    </div>

    <div class="ml-auto d-print-none">
        <button class="text-black-50 pr-0 feedback-button btn btn-link pendo-case-feedback-link"
            onclick="onFeedbackClick()">
            Tell us what you think
        </button>
        <button onclick="printCaseDetails()" class="btn btn-link text-black-50 pendo-case-print d-print-none">
            <i class="fas fa-print"></i> Print
        </button>
        <a data-toggle="modal" data-target="#EditCaseModel" data-placement="bottom" href="javascript:;"> <button
                class="btn btn-primary btn-rounded m-1 px-5" type="button" onclick="updateCaseDetails({{$CaseMaster->case_id}});">Edit Case</button></a>
    </div>
</div>
<div class="row">
    <div class="col-md-2">
        <div class="card mb-4">
            <div class="card-body">
                <span id="responseMain"></span>
                <nav class="test-general-settings-nav p-0 pt-0" role="navigation">
                    <ul class="nav nav-pills flex-column text-wrap">
                        <div class="mb-3">
                            <div class="test-court-case-left-panel">
                                <div class="mb-4">
                                    <h6 class="font-weight-bold">Contact Info:</h6>

                                    <?php foreach($caseCllientSelection as $key=>$val){
                                     
                                      if($val->user_level==4){
                                      ?>
                                    <div class="d-flex align-items-center mb-3 rounded-circle">
                                        <div class="mx-1">
                                           
                                            <i class="fas fa-building fa-2x text-black-50"></i>
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <a class="font-weight-bolder pendo-left-details-company"
                                                href="{{BASE_URL}}contacts/companies/{{$val->id}}">{{$val->first_name}}</a>
                                            <small><a class="text-break pendo-left-details-company-email"
                                                    href="mailto:{{$val->email}}">{{$val->email}}</a></small>
                                        </div>
                                    </div>
                                    <?php } else { ?>
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="mr-1">
                                          
                                            <?php if($val->profile_image!="" && $val->is_published=="yes"){
                                                ?><img class="rounded-circle" alt="" src="{{BASE_URL}}public/profile/{{$val->profile_image}}" width="32" height="32"><?php
                                            }else{
                                                ?><i class="fas fa-2x fa-user-circle text-black-50"></i><?php
                                            }?>
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <div class="d-flex flex-wrap align-items-center">
                                                <a class="font-weight-bolder pendo-left-details-contact"
                                                    href="{{BASE_URL}}contacts/clients/{{$val->id}}">{{substr($val->first_name,0,15)}}
                                                    {{substr($val->last_name,0,15)}}</a>
                                                <small class="ml-1 text-lowercase">(Client)</small>
                                            </div>
                                            <small><a class="text-break pendo-left-details-contact-email"
                                                    href="mailto:{{$val->email}}">{{$val->email}}</a></small>
                                        </div>
                                    </div>
                                    <?php 
                                  }
                                    }?>



                                </div>

                                <div class="mb-4">
                                    <div class="font-weight-bold">Opened:</div>
                                    <?php 
                                    if(isset($CaseMaster->case_open_date)){
                                      echo date('m/d/Y',strtotime($CaseMaster->case_open_date));

                                    }else{
                                      echo "Not Specified";
                                    }?>

                                </div>
                                <?php if(isset($CaseMaster->case_close_date)){ ?>
                                <div class="mb-4">
                                    <div class="font-weight-bold">Closed:</div>
                                    <?php 
                                
                                      echo date('m/d/Y',strtotime($CaseMaster->case_close_date));
                                ?>

                                </div>
                                <?php } ?>
                                <div class="mb-4">
                                    <div class="font-weight-bold">Practice Area:</div>
                                    <div class="text-muted">
                                        <?php 
                                            $Area="Not Specified";
                                            foreach($practiceAreaList as $k=>$v){
                                              if($CaseMaster->practice_area==$v->id){ $Area= $v->title; }
                                             } 
                                             echo $Area;
                                             ?>
                                    </div>
                                </div>

                                <div class="mb-4 test-case-stage">
                                    <div class="font-weight-bold">Case Stage:</div>
                                    <div id="left-panel-case-stage-value" class="pendo-update-case-stage">
                                        <div class="case-stage-inline-editor">
                                            <div class="view-mode" id="statusEditor">
                                                <div class="w-100 p-0 editable">
                                                    <span class="case-stage-value">
                                                        <?php 
                                                        $Stage="Not Specified";
                                                        foreach($caseStageList as $ks=>$vs){
                                                          if($CaseMaster->case_status==$vs->id){ $Stage= $vs->title; }
                                                        } 
                                                        
                                                        ?>
                                                        <span class="text-muted">{{$Stage}}</span>
                                                        <i class="fas fa-pen fa-sm text-black-50 c-pointer pl-1"
                                                            onclick="ShowStatus();"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="edit-mode d-print-none" id="statusList">
                                                <select id="case_status" onchange="selectMethod()" name="case_status"
                                                    class="form-control custom-select col">
                                                    <option value="0">--</option>
                                                    <?php foreach($caseStageList as $kcs=>$vcs){?>
                                                    <option
                                                        <?php if($CaseMaster->case_status==$vcs->id){ echo "selected=selected"; }?>
                                                        value="{{$vcs->id}}">{{$vcs->title}}</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="font-weight-bold">Office:</div>
                                    Primary
                                </div>

                                <div class="mb-4 test-lead-attorney">
                                    <div class="font-weight-bold test-lead-attorney-label">Lead Attorney:</div>
                                    <div class="text-muted test-lead-attorney-value">
                                        <?php 
                                    if(!$leadAttorney->isEmpty()){
                                        ?>
                                         <a class="pendo-left-details-created-by-link" href="{{BASE_URL}}contacts/attorneys/{{base64_encode($leadAttorney[0]->id)}}">
                                            {{substr($leadAttorney[0]->first_name,0,15)}} {{substr($leadAttorney[0]->last_name,0,15)}}</a>
                                            <?php 
                                   
                                    }else{
                                      echo "Not Specified";
                                    }?>

                                    </div>
                                </div>

                                <div class="mb-4 test-originating-attorney">
                                    <div class="font-weight-bold test-originating-attorney-label">Originating Attorney:
                                    </div>
                                    <div class="text-muted test-originating-attorney-value"><?php 
                                      if(!$originatingAttorney->isEmpty()){
                                          ?>
                                            <a class="pendo-left-details-created-by-link" href="{{BASE_URL}}contacts/attorneys/{{base64_encode($originatingAttorney[0]->id)}}">
                                                {{substr($originatingAttorney[0]->first_name,0,15)}} {{substr($originatingAttorney[0]->last_name,0,15)}}</a>
                                                <?php
                                    
                                      }else{
                                        echo "Not Specified";
                                      }?></div>
                                </div>

                                <div class="mb-4">
                                    {{-- 1:Attorney 2: Paralegal 3:Staff 4: None  --}}
                                    <div class="font-weight-bold">Staff:</div>
                                    <div class="mb-3">
                                        <?php 
                                        if(!$staffList->isEmpty()){
                                            foreach($staffList as $staffKey=>$staffVal){ ?>
                                            <div>
                                                <a class="pendo-left-details-staff-link"
                                                    href="{{BASE_URL}}contacts/attorneys/{{$staffVal->decode_id}}">{{substr($staffVal->first_name,0,15)}}
                                                    {{substr($staffVal->last_name,0,15)}}
                                                </a>
                                                <small><?php
                                                if($staffVal->user_title!=''){
                                                    echo "(".$staffVal->user_title.")";
                                                }
                                                    ?>
                                                </small>
                                            </div>
                                        <?php }
                                       }else{
                                        echo "Not Specified";
                                       } 
                                       ?>
                                    </div>
                                </div>
                                <div class="mb-4 text-wrap">
                                    <div class="font-weight-bold">Description:</div>
                                    <p>{{($CaseMaster->case_description)??"Not Specified"}}</p>
                                </div>

                                <div class="mb-2">
                                    <div class="font-weight-bold">Created:</div>
                                    <?php 
                                    if(isset($CaseMaster->case_created_date)){
                                      echo date('m/d/Y',strtotime($CaseMaster->case_created_date));
                    
                                    }else{
                                      echo "-";
                                    }?> by:
                                    <a class="pendo-left-details-created-by-link" href="{{BASE_URL}}contacts/attorneys/{{base64_encode($CaseMaster->case_created_by)}}">
                                        {{substr($CaseMaster->first_name,0,15)}} {{substr($CaseMaster->last_name,0,15)}}</a>
                                </div>
                                <hr>
                                <?php if($CaseMaster->case_close_date!=NULL){?>
                                    <div class="p-2 mt-2">
                                        <div>
                                            <a data-toggle="modal" data-target="#ReopenCaseModel" data-placement="bottom" href="javascript:;"> <button class="mb-3 btn btn-primary btn-block " type="button" >Reopen Case</button></a>
                                        </div>
                                        <div>
                                            <a data-toggle="modal" data-target="#DeleteCaseModel" data-placement="bottom" href="javascript:;"> <button class="mb-3 btn  btn-outline-danger btn-block " type="button" >Delete Case</button></a>
                                        </div>
                                    </div>
                                <?php }else{ ?>
                                    <div class="p-2 mt-2">
                                        <div>
                                            <a data-toggle="modal" data-target="#CloseCaseModel" data-placement="bottom" href="javascript:;"> <button class="mb-3 btn btn-outline-danger btn-block archive-case-button pendo-close-case" type="button" onclick="closeCase({{$CaseMaster->case_id}});">Close Case</button></a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    <div class="col-md-10">
        <div class="card mb-4 o-hidden">
            @include('pages.errors')
            <div class="card-body" style="min-height:1000px;">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?php if(in_array(Route::currentRouteName(),["info","recent_activity","calendars","notes","tasks"])){ echo "active show"; } ?> " id="profile-basic-tab"  href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/info')}}" >Items & Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link   <?php if(in_array(Route::currentRouteName(),["overview","time_entries","expenses","payment_activity"])){ echo "active show"; } ?>" id="contact-basic-tab"  href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/overview')}}" >Time & Billing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  <?php if(in_array(Route::currentRouteName(),["communications/messages","communications/calls","communications/mailbox"])){ echo "active show"; } ?>" id="contact-basic-tab" href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/communications/messages')}}">Communications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  <?php if(Route::currentRouteName()=="case_link"){ echo "active show"; } ?>" id="contact-basic-tab"  href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/case_link')}}" >Contacts & Staff</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if(Route::currentRouteName()=="status_updates"){ echo "active show"; } ?> id="contact-basic-tab"  href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/status_updates')}}" >Status
                            Updates</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade  <?php if(in_array(Route::currentRouteName(),["info","recent_activity","calendars","notes","tasks"])){ echo "active show"; } ?> " id="intemInfo" role="tabpanel"
                        aria-labelledby="profile-basic-tab">
                        <div class="nav nav-pills test-info-page-subnav pt-0 pb-2 d-print-none">
                            <div class="nav-item">
                                <a class="nav-link pendo-case-items-info <?php if(Route::currentRouteName()=="info"){ echo "active"; } ?>" data-page="info"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/info')}}">
                                    <span><i class="i-Info-Window  text-16 mr-1"></i> Info</span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link pendo-case-recent-activity <?php if(Route::currentRouteName()=="recent_activity"){ echo "active"; } ?>" data-page="recent_activity"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/recent_activity')}}">
                                    <span class="d-flex">
                                        <i class="i-Administrator text-16 mr-1" height="40"></i>&nbsp; Activity &amp;
                                        Timeline </span>
                                </a>
                            </div>

                            <div class="nav-item">
                                <a class="nav-link  pendo-case-calendar <?php if(Route::currentRouteName()=="calendars"){ echo "active"; } ?>" data-page="calendar"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/calendars')}}"><span>
                                        <i class="i-Calendar-3  text-16 mr-1"></i>Calendar</span></a>
                            </div>

                            <div class="nav-item">
                                <a class="nav-link  pendo-case-documents" id="documentsButton" data-page="documents"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/documents')}}"><span>
                                        <i class="i-Folder-With-Document text-16 mr-1"></i> Documents</span></a>
                            </div>

                            <div class="nav-item">
                                <a class="nav-link  pendo-case-tasks <?php if(Route::currentRouteName()=="tasks"){ echo "active"; } ?>" data-page="tasks"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/tasks')}}"><span><i
                                            class="i-File-Clipboard text-16 mr-1"></i> Tasks</span></a>
                            </div>

                            <div class="nav-item">
                                <a class="nav-link  pendo-case-notes <?php if(Route::currentRouteName()=="notes"){ echo "active"; } ?> " data-page="notes"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/notes')}}"><span><i
                                            class="i-Evernote text-16 mr-1"></i>
                                        Notes</span></a>
                            </div>

                            <div class="nav-item">
                                <a class="nav-link pendo-case-intake-forms" data-page="intake_forms"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/intake_forms')}}"><span><i
                                            class="i-Settings-Window  text-16 mr-1"></i> Intake Forms</span></a>
                            </div>

                            <div class="nav-item">
                                <a class="workflow_submenu_button nav-link  pendo-case-workflow" data-page="workflows"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/workflows')}}"><span><i
                                            class="i-Network text-16 mr-1"></i>
                                        Workflows</span></a>
                            </div>
                        </div>

                        <div class="row">
                            <?php if(Route::currentRouteName()=="info"){ ?>
                                    @include('case.view.info',['CaseMaster','practiceAreaList','caseStageList','lastStatusUpdate'])
                            <?php }?>
                           
                             <?php  if(Route::currentRouteName()=="recent_activity"){ ?>
                                 @include('case.view.activity',['CaseMaster'])
                            <?php } ?>

                            <?php if(Route::currentRouteName()=="calendars"){?>
                                @include('case.view.calender',['CaseMaster'])
                            <?php } ?>

                            <?php  if(Route::currentRouteName()=="notes"){?>
                                @include('case.view.loadNotes',['CaseMaster'])
                            <?php } ?>
                            <?php  if(Route::currentRouteName()=="tasks"){?>
                               
                                @include('case.view.task',['CaseMaster'])
                            <?php } ?>
                        </div>
                    </div>
                    <div class="tab-pane fade <?php if(in_array(Route::currentRouteName(),["overview","time_entries","expenses","invoices","payment_activity"])){ echo "active show"; } ?>" id="timeBilling" role="tabpanel" aria-labelledby="contact-basic-tab">
                      <div class="nav nav-pills test-info-page-subnav pt-0 pb-2 d-print-none">
                            <div class="nav-item">
                                <a class="nav-link pendo-case-items-info <?php if(Route::currentRouteName()=="overview"){ echo "active"; } ?>" data-page="info"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/overview')}}">
                                    <span><i class="i-Calendar-3  text-16 mr-1"></i> Overview</span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link pendo-case-items-info <?php if(Route::currentRouteName()=="time_entries"){ echo "active"; } ?>" data-page="info"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/time_entries')}}">
                                    <span><i class="i-Clock-Forward  text-16 mr-1"></i> Time Entries</span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link pendo-case-items-info <?php if(Route::currentRouteName()=="expenses"){ echo "active"; } ?>" data-page="info"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/expenses')}}">
                                    <span><i class="i-Info-Window  text-16 mr-1"></i> Expenses</span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link pendo-case-items-info <?php if(Route::currentRouteName()=="invoices"){ echo "active"; } ?>" data-page="info"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/invoices')}}">
                                    <span><i class="fas fa-fw fa-file-invoice  text-16 mr-1"></i> Invoices</span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link pendo-case-items-info <?php if(Route::currentRouteName()=="payment_activity"){ echo "active"; } ?>" data-page="info"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/payment_activity')}}">
                                    <span><i class="fas fa-dollar-sign align-middle  text-16 mr-1"></i>Payment Activity</span>
                                </a>
                            </div>
                        </div>

                        <div class="row">
                            <?php if(Route::currentRouteName()=="overview"){ ?>
                                      @include('case.view.timebilling.overview')
                            <?php } ?>
                            <?php if(Route::currentRouteName()=="time_entries"){ ?>
                                @include('case.view.timebilling.time_entries')
                            <?php } ?>
                            <?php if(Route::currentRouteName()=="expenses"){ ?>
                                @include('case.view.timebilling.expenses')
                            <?php } ?>
                            <?php if(Route::currentRouteName()=="invoices"){ ?>
                                @include('case.view.timebilling.invoices')
                            <?php } ?>
                            <?php if(Route::currentRouteName()=="payment_activity"){ ?>
                                @include('case.view.timebilling.payment_activity')
                            <?php } ?>
                        </div>
                    </div>
                    <div class="tab-pane fade <?php if(in_array(Route::currentRouteName(),["communications/messages","communications/calls","communications/emails"])){ echo "active show"; } ?>" id="communications" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <div class="nav nav-pills test-info-page-subnav pt-0 pb-2 d-print-none">
                            <div class="nav-item">
                                <a class="nav-link pendo-case-recent-activity <?php if(Route::currentRouteName() =="communications/messages"){ echo "active"; } ?>" data-page="recent_activity"
                                    href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/communications/messages')}}">
                                    <span class="d-flex"> <i class="i-Newspaper-2 text-16 mr-1"></i>&nbsp;Messages  </span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link  pendo-case-calendar <?php if(Route::currentRouteName()=="communications/calls"){ echo "active"; } ?>" data-page="calendar" href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/communications/calls')}}">
                                    <span> <i class="i-Old-Telephone text-16 mr-1"></i>Call Log</span>
                                </a>
                            </div>
                            <div class="nav-item">
                                <a class="nav-link  pendo-case-calendar <?php if(Route::currentRouteName()=="communications/emails"){ echo "active"; } ?>" data-page="calendar" href="{{URL::to('court_cases/'.$CaseMaster->case_unique_number.'/communications/emails')}}">
                                    <span> <i class="i-Email  text-16 mr-1"></i>Emails</span>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <?php if(Route::currentRouteName()=="communications/messages"){ ?>
                                      @include('case.view.timebilling.communications')
                            <?php } ?>
                            <?php if(Route::currentRouteName()=="communications/calls"){ ?>
                                @include('case.view.timebilling.calls')
                            <?php } ?>

                            <?php if(Route::currentRouteName()=="communications/emails"){ ?>
                                @include('case.view.timebilling.email')
                            <?php } ?>

                        </div>
                        
                    </div>
                    <div class="tab-pane fade <?php if(Route::currentRouteName()=="case_link"){ echo "active show"; } ?> " id="contactStaff" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <?php  if(Route::currentRouteName()=="case_link"){ ?>
                            @include('case.view.case_link')
                        <?php } ?>                    
                    </div>
                    <div class="tab-pane fade  <?php if(Route::currentRouteName()=="status_updates"){ echo "active show"; } ?> " id="statusUpdateTab" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <div class="row">
                            <?php 
                                if(Route::currentRouteName()=="status_updates"){
                                ?>
                                    @include('case.view.status_updates',['allStatus','CaseMaster'])
                            <?php
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
<div id="CloseCaseModel" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Close Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="CloseCaseModelArea">
                            <img src="{{LOADER}}""> Loading...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ReopenCaseModel" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="ReopenCaseForm" id="ReopenCaseForm" name="ReopenCaseForm" method="POST">
           
            @csrf    
            <input type="hidden" value="{{$CaseMaster->case_id}}" name="case_id">
             <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Reopen Case</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="showError" style="display:none"></div>
                        <div class="col-md-12">
                            <div id="ReopenCaseModelArea">
                                <div class="alert alert-warning">Are you sure you want to reopen this case?</div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-2 form-group">
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
                        </div>
                    </div>
                    <a href="#">
                        <button class="btn btn-secondary  btn-rounded mr-1 " type="button" data-dismiss="modal">Cancel</button>
                    </a>
                    <button class="btn btn-primary  btn-rounded submit " id="submitButton" value="savenote" type="submit">Reopen
                        Case</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="DeleteCaseModel" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="DeleteCaseForm" id="DeleteCaseForm" name="DeleteCaseForm" method="POST">
            @csrf    
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Delete Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                  
                        <input type="hidden" value="{{$CaseMaster->case_id}}" name="case_id">
                        <div class="col-md-12">
                            <div id="DeleteCaseModelArea">
                                <div id="case_confirm_delete_message" class="case-delete-message" style="">
                                    Are you sure you want to delete this case?
                                    <div class="case-delete-warning">
                                    Deleting this case will also <span class="error">permanently</span> delete
                                    the following items associated with this case:
                                    <ul>
                                        <li>{{$caseStat->case_event_counter}} events</li>
                                        <li>0 documents</li>
                                        <li>{{$caseStat->case_event_counter}} tasks</li>
                                        <li>0 messages</li>
                                        <li>{{$caseStat->case_note_counter}} note</li>
                                        <li>{{$caseStat->case_timeentry_counter}} time entry</li>
                                        <li>{{$caseStat->case_expenseentry_counter}} expense</li>
                                        <li>{{$caseStat->case_invoice_counter}} invoices and <span class="bold">all associated payment activity</span>.
                                        Note that any payments from trust will be refunded back into a client's account.
                                        </li>
                                    </ul>
                                    </div>
                                    Deleting a case is <span class="error">permanent</span> and cannot be undone.
                                    <div class="delete-popup-container">
                                    &nbsp;
                                    <div class="delete-popup-loading" id="delete_popup_loading" style="display: none;">
                                         Deleting
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                  
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-2 form-group">
                    <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
                    </div>
                </div>
                <a href="#">
                    <button class="btn btn-secondary  btn-rounded mr-1 " type="button" data-dismiss="modal">Cancel</button>
                </a>
                <button class="btn btn-primary  btn-rounded submit " id="submitButton"  type="submit">Yes, Delete</button>
            </div>
        </div>
    </form>
    </div>
</div>
@include('commonPopup.popup_without_param_code')
@endsection
@section('page-js')
<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="popover"]').popover();
        $('#duedate').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            "orientation": "bottom",
            'todayHighlight': true
        });
        $("#statusEditor").show();
        $("#statusList").hide();
        $("#innerLoader").css('display', 'none');

        $('#ShowColorPicker').on('hidden.bs.modal', function () {
            window.location.reload();
        });
        $('#DeleteModal').on('hidden.bs.modal', function () {
            window.location.reload();
        });
        $('#ReopenCaseForm').submit(function (e) {
            $(".innerLoader").css('display', 'block');
            e.preventDefault();
            if (!$('#ReopenCaseForm').valid()) {
                $(".innerLoader").css('display', 'none');
                return false;
            }
            var dataString = $("#ReopenCaseForm").serialize();
        
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/ReopenClosedCase", // json datasource
                data: dataString ,
                success: function (res) {
                    $(".innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        $(".innerLoader").css('display', 'none');
                        return false;
                    } else {
                        window.location.reload();
                    }
                }
            });
        });
        var ClientNotesyGrid =  $('#ClientNotesyGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/contacts/clients/ClientNotes", // json datasource
                type: "post",  // method  , by default get
                data :{ 'case_id' : '{{$CaseMaster->case_id}}' },
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            // "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                   
                    var isdraft=createdat=updateat=sub="";
                    if(aData.is_draft=="yes"){
                        isdraft='<span class="badge badge-pill badge-success p-2 m-1">Draft</span>';
                    }
                    if(aData.created_at!=null){
                        createdat='Note Added: '+aData.created_date_new+' by '+aData.note_created_by+' ('+aData.created_by_user_title+')';
                    }
                    if(aData.updated_at!=null){
                        updateat='Last Updated: '+aData.updated_date_new+' by '+aData.note_updated_by+' ('+aData.updated_by_user_title+')';
                    }
                    if(aData.note_subject!=null){
                        sub=aData.note_subject;
                    }else{
                        sub="-";
                    }

                    $('td:eq(0)', nRow).html('<div class="text-left"><div class="expanded-content"><a class="text-default" data-toggle="collapse" onclick="hidez('+aData.id+')" href="#accordion-item-group'+aData.id+'" value="'+aData.id+'"><div class="c-pointer d-flex mb-3 test-note-subject">'+isdraft+'<span class="font-weight-bold pt-2">'+sub+'</span><i aria-hidden="true" class="fa fa-angle-down icon-angle-down icon icon-angle-down-'+aData.id+'" style="margin-left: 8px;padding-top: 10px;"></i></div></a><div class="collapse" id="accordion-item-group'+aData.id+'" va="'+aData.id+'"><div><p class="note-note"><p>'+aData.notes+'</p></p></div><div><div class="test-note-created-at text-black-50 font-italic small">'+createdat+'</div><div class="test-note-updated-at text-black-50 font-italic small">'+updateat+'</div></div><div class="d-flex align-items-center"><div class="d-flex flex-row"><a data-toggle="modal"  data-target="#editNoteModal" data-placement="bottom" href="javascript:;" href="javascript:;"><button class="btn btn-outline-secondary btn-rounded " type="button" onclick="loadEditNotBox('+aData.id+');"><i class="fas fa-pencil-alt mr-1"></i>Edit Note</button></a><button type="button" class="mr-1 add-time-entry-button text-dark btn btn-link"><a data-toggle="modal"  data-target="#loadTimeEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadTimeEntryPopupForNotes('+aData.id+');"><i class="fas fa-stopwatch mr-1"></i>Add Time Entry</a></button><button type="button" class="mr-1 delete-note-button text-dark btn btn-link"><a data-toggle="modal"  data-target="#deleteNote" data-placement="bottom" href="javascript:;" class="text-dark" onclick="deleteNote('+aData.id+');"><i class="fas fa-trash mr-1"></i>Delete Note</a></button></div><div class="btn c-pointer"><a class="btn" onclick="hideshow('+aData.id+')">Hide Details</a><i aria-hidden="true" class="fa fa-angle-up icon-angle-up icon"></i></div></div></div></div></div>');

                    $('td:eq(1)', nRow).html('<div class="text-left">'+aData.created_date_new+'</div>');

                    $('td:eq(2)', nRow).html('<div class="text-center"><a data-toggle="modal"  data-target="#editNoteModal" data-placement="bottom" href="javascript:;"  onclick="loadEditNotBox('+aData.id+');"><i class="fas fa-pen align-middle p-2"></i></a><a data-toggle="modal"  data-target="#loadTimeEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadTimeEntryPopupForNotes('+aData.id+');"><i class="fas fa-stopwatch mr-1 p-2 align-middle"></i></a><a data-toggle="modal"  data-target="#deleteNote" data-placement="bottom" href="javascript:;"  onclick="deleteNote('+aData.id+');"><i class="fas fa-trash align-middle p-2"></i></a></div>');
                },
                "initComplete": function(settings, json) {
                    $("#caseHistoryGrid thead").remove();
                    var currentSize=localStorage.getItem("clientNoteList");
                    $('td').css('font-size', currentSize +'px');
                   
                }
        });
        $('#DeleteCaseForm').submit(function (e) {
            $(".innerLoader").css('display', 'block');
            e.preventDefault();
            if (!$('#DeleteCaseForm').valid()) {
                $(".innerLoader").css('display', 'none');
                return false;
            }
            var dataString = $("#DeleteCaseForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/DeleteClosedCase", // json datasource
                data: dataString ,
                success: function (res) {
                    $(".innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        $(".innerLoader").css('display', 'none');
                        return false;
                    } else {
                        window.location.reload();
                    }
                }
            });
        });
        $('#addNoteModal').on('hidden.bs.modal', function () {
            window.location.reload();
        });
        $(".increase").click(function(){         
            modifyFontSize('increase');  
        });     
        $(".decrease").click(function(){   
            modifyFontSize('decrease');  
        });
        $("#dueDateChange").validate({
            rules: {
                duedate: {
                    required: true
                }
            },
            messages: {

                duedate: {
                    required: "Due date cannot be empty"
                }
            }
        });

        $('#dueDateChange').submit(function (e) {
            e.preventDefault();
            $("#innerLoader1").css('display', 'block');
            if (!$('#dueDateChange').valid()) {
                $("#innerLoader1").css('display', 'none');
                return false;
            }

            var dataString = $("#dueDateChange").serialize();
            var array = [];
            $("input[class=task_checkbox]:checked").each(function (i) {
                array.push($(this).val());
            });
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/changeDueDate", // json datasource
                data: dataString + '&task_id=' + JSON.stringify(array),
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
        });
    });
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
            localStorage.setItem("clientNoteList",currentFontSize);
        }
    }  
    function ShowStatus() {
        $("#statusEditor").toggle();
        $("#statusList").toggle();
    }
    function selectMethod() {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#case_status option:selected").val();
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/saveStatus", // json datasource
            data: {
                'case_status': selectdValue,
                'case_id': {{$CaseMaster->case_id}}
            },
            success: function (res) {
                $("#preloader").hide();
                $("#response").html(
                    '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><b>Success!</b> Changes saved.</div>'
                );
                $("#response").show();
                $("#innerLoader").css('display', 'none');
                window.location.reload();
            }
        });
    }
    function updateCaseDetails(id) {
        
        $("#preloader").show();
        $("#step-edit-1").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/case/editCase", // json datasource
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
    function closeCase(id) {
        $("#CloseCaseModelArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url:  baseUrl +"/court_cases/closeCase", // json datasource
            data: {"case_id": id},
            success: function (res) {
                
                $("#CloseCaseModelArea").html(res);
                $("#preloader").hide();
            }
        })
    }
    function loadAddNotBox() {
        $("#preloader").show();
        $("#addNoteModalArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/addNotes", 
                data: {"case_id": "{{$CaseMaster->case_id}}"},
                success: function (res) {
                    $("#addNoteModalArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadEditNotBox(id) {
        $("#preloader").show();
        $("#editNoteModalArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/editNotes", 
                data: {"case_id": "{{$CaseMaster->case_id}}","id": id},
                success: function (res) {
                    $("#editNoteModalArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function markasCompleted() {
        var array = [];
        $("input[class=task_checkbox]:checked").each(function (i) {
            array.push($(this).val());
        });
        if (array.length === 0) {} else {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/markAsCompleted", // json datasource
                data: {
                    "task_id": JSON.stringify(array)
                },
                success: function (res) {
                    window.location.reload();
                }
            })
        }
    }

    function changeDueDate() {
        $("#reminderDataIndex").html('Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadTaskReminderPopupIndex", // json datasource
                data: {
                    "task_id": task_id
                },
                success: function (res) {
                    $("#reminderDataIndex").html('Loading...');
                    $("#reminderDataIndex").html(res);
                    $("#preloader").hide();

                }
            })
        })
    }
    function discardNotes(id) {
       $("#discardNotes").modal("show");
       $("#discard_note_id").val(id);
   }
    function expandAllnote(){
        $('.collapse').collapse('show');
        $("#co").show();
        $("#ex").hide();
        $(".icon-angle-down").hide();
    }
    function collapseAllnote(){
        $('.collapse').collapse('hide');
        $("#ex").show();
        $("#co").hide();
        $(".icon-angle-down").show();
    }
    function loadTimeEntryPopupForNotes(id) {
        $("#preloader").show();
        $("#addTimeEntry").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/contacts/clients/loadTimeEntryPopup", // json datasource
                data: {"note_id": id},
                success: function (res) {
                    $("#addTimeEntry").html('');
                    $("#addTimeEntry").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function loadTaskPortion(page=null,status=null) {
        $("#taskDyncamic").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/case/loadTaskPortion?page="+page+"&status="+status, // json datasource
                data: {"case_id": "{{$CaseMaster->case_id}}"},
                success: function (res) {
                    $("#taskDyncamic").html(res);
                }
            })
        })
    }
    function filterTaskByAssignTo(){
        var status=$("#task_status").val();
        loadTaskPortion(page=null,status);
    }
    function filterTaskByStatus(){
        var status=$("#task_status").val();
        loadTaskPortion(page=null,status);
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
    } function loadReminderPopupIndex(task_id) {
        $("#reminderDataIndex").html('<img src="{{LOADER}}""> Loading...');
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadTaskReminderPopupIndex", // json datasource
                data: {
                    "task_id": task_id
                },
                success: function (res) {
                    $("#reminderDataIndex").html('<img src="{{LOADER}}""> Loading...');
                    $("#reminderDataIndex").html(res);
                    $("#preloader").hide();

                }
            })
        })
    }
    function editTask(id) {
        $("#editTaskArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadEditTaskPopup", // json datasource
                data: {
                    "task_id": id
                },
                success: function (res) {
                    $("#editTaskArea").html('');
                    $("#editTaskArea").html(res);
                }
            })
        })
    }
    $(document).on('click', '.taskListPager .pagination a', function(event){
        event.preventDefault(); 
        var page = $(this).attr('href').split('page=')[1];
        loadTaskPortion(page);
    });
    <?php if(Route::currentRouteName()=="tasks"){  ?> loadTaskPortion("1","all_task"); <?php } ?>
</script>
@stop
