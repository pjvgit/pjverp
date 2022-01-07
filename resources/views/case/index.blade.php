@extends('layouts.master')
@section('title', 'Cases')
@section('main-content')
@include('case.case_submenu')
<style>.morecontent span {
    display: none;
}
.morelink {
    display: block;
}
</style>
<?php
$pa=$cs=$la=$mc=$i=""; 
if(isset($_GET['pa'])){
    $pa= base64_decode($_GET['pa']);
}
if(isset($_GET['la'])){
    $la= base64_decode($_GET['la']);
}
if(isset($_GET['cs'])){
    $cs= base64_decode($_GET['cs']);
}
if(isset($_GET['mc'])){
     $mc= $_GET['mc'];
}

if(isset($_GET['i'])){
     $i= $_GET['i'];
}
?>
<div class="row">
   
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex align-items-center pl-4 pb-4">
                    <h3> <i class="fas fa-briefcase mr-2"></i> Cases</h3>
                    <ul class="d-inline-flex nav nav-pills pl-4">
                        <li class="d-print-none nav-item">
                            <a href="{{route('court_cases')}}" class="nav-link <?php if(!isset($_GET['i'])) echo "active"; ?> ">Open</a>
                        </li>
                        <li class="d-print-none nav-item">
                            <a href="{{route('court_cases')}}?i=c" class="nav-link <?php if(isset($_GET['i']) && $_GET['i']=='c') echo "active"; ?>">Closed</a>
                        </li>
                    </ul>
                    <div class="ml-auto d-flex align-items-center d-print-none">
                        <a data-toggle="modal" data-target="#loadAddFeedBack" data-placement="bottom" href="javascript::void(0);">
                            <button onclick="setFeedBackForm('single','Cases Tab');" type="button" class="feedback-button mr-2 text-black-50 btn btn-link">Tell us what you think</button>
                        </a>
                        <button onclick="printEntry();return false;" class="btn btn-link">
                            <i class="fas fa-print text-black-50" data-toggle="tooltip" data-placement="top"
                                    title="" data-original-title="Print"></i>
                        </button>        
                        @can('case_add_edit')                
                        <a data-toggle="modal"  data-target="#AddCaseModelUpdate" data-placement="bottom" href="javascript:;" > <button disabled class="btn btn-primary btn-rounded m-1" type="button" onclick="loadAllStep();" >Add Case</button></a>
                        @endcan
                    </div>   
                </div>
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <div class="row pl-4 pb-4">
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Practice Area</label>
                            <select id="pa" name="pa" class="form-control custom-select col select2">
                                <option value="">Select...</option>
                                <?php 
                                    foreach($practiceAreaList as $k=>$v){?>
                                <option <?php if($pa==$v->id){ echo "selected=selected"; }?>  value="{{base64_encode($v->id)}}">{{$v->title}}</option>
                                <?php } ?>
            
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Lead Attorney</label>
                            <select id="la" name="la" class="form-control custom-select col select2">
                                <option value="">Select...</option>
                                <?php 
                                    foreach($CaseLeadAttorney as $ka=>$va){?>
                                <option <?php if($la==$va->id){ echo "selected=selected"; }?> value="{{base64_encode($va->id)}}">{{$va->created_by_name}}</option>
                                <?php } ?>
            
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Case Stage</label>
                            <select id="cs" name="cs" class="form-control custom-select col select2">
                                <option value="">Select...</option>
                                <?php 
                                foreach($caseStageList as $kcs=>$vcs){?>
                                <option <?php if($cs==$vcs->id){ echo "selected=selected"; }?>  value="{{base64_encode($vcs->id)}}">{{$vcs->title}}</option>
                                <?php } ?>
            
                            </select>
                        </div>  
                        <div class="col-md-4 form-group mb-3 mt-3 pt-2">
                            <label class="switch pr-3 switch-success"><span>Show Only My Cases</span>
                                <input type="checkbox" name="mc" <?php if($mc=="on"){ echo "checked=checked"; }?> ><span class="slider "></span>
                            </label>
                            <button class="btn btn-info btn-rounded m-1" type="submit">Apply Filters</button>
                            <button type="button" class="test-clear-filters text-black-50 btn btn-link"><a href="{{route('court_cases')}}">Clear Filters</a></button>
                        </div>
                        
                    </div>
                </form>
                <div class="table-responsive" id="printHtml">
                    <h3 id="hiddenLable">Cases</h3>
                    <table class="display table table-striped table-bordered" id="employee-grid" style="width:100%">
                        <thead>
                            <tr>
                                <th width="1%">id</th>
                                
                                 <th width="15%">Case</th>
                                <th width="10%">Number</th>
                                <th width="10%">Case Stage</th>
                                <th width="10%">Firm Members</th>
                                <th width="10%">Next Event</th>
                                <th width="15%">Next Task</th>
                                <th width="15%" class="text-center">Status Update</th>
                                <th width="10%" class="text-center">Added</th>
                                <th width="10%" class="text-center"></th>
                            </tr>
                        </thead>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="AddCaseModelUpdate" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body" bladeFile="resources/views/case/index.blade.php">
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
                                    <li class="text-center"><a href="#step-4">4<br /><small>Staff</small></a>
                                    </li>
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
                                                    <a data-toggle="modal" data-target="#AddContactModal" data-placement="bottom" href="javascript:;">
                                                        <button class="btn btn-primary btn-rounded m-1" type="button" onclick="AddContactModal();">Add New
                                                            Contact</button></a>

                                                </label>
                                                <div class="text-center col-2">Or</div>
                                                <div class="col-7 form-group mb-3">
                                                    <select onchange="selectUser();" class="form-control user_type"  style="width:100%;" id="user_type" name="user_type"
                                                        data-placeholder="Search for an existing contact or company">
                                                        <option value="">Search for an existing contact or company</option>
                                                        <optgroup label="Client">
                                                            <?php
                                                        foreach($CaseMasterClient as $Clientkey=>$Clientval){
                                                            ?>
                                                            <option value="{{$Clientval->id}}">{{substr($Clientval->first_name,0,30)}}
                                                                {{substr($Clientval->last_name,0,30)}}</option>
                                                            <?php } ?>
                                                        </optgroup>
                                                        <optgroup label="Company">
                                                            <?php foreach($CaseMasterCompany as $Companykey=>$Companyval){ ?>
                                                            <option value="{{$Companyval->id}}">{{substr($Companyval->first_name,0,50)}}</option>
                                                            <?php } ?>
                                                        </optgroup>
                                                    </select>
                                                    <span id="UserTypeError"></span>
                                                </div>
                                                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                                            </div>

                                            <div id="loadUserAjax"></div>

                                            <div class="m-2 empty-state text-center text-center-also">
                                                <p class="font-weight-bold">Start creating your case by adding a new or existing contact.</p>
                                                <div>All cases need at least one client to bill.</div><a href="#" rel="noopener noreferrer"
                                                    target="_blank">Learn more about adding a case and a contact at the same time.</a>
                                            </div>

                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                                                <label for="inputEmail3" class="col-sm-10 col-form-label"></label>
                                                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                                            </div>

                                            <div class="modal-footer">
                                                <div class="no-contact-warning mr-2" style="display:none;" id="beforetext">You must have a contact to bill.
                                                    Are you sure you want to continue?</div>
                                                <button type="button" id="beforebutton" onclick="callOnClick();"
                                                    class="btn btn-primary ladda-button example-button m-">Continue to case details
                                                </button>

                                                <button class="btn btn-primary ladda-button example-button m-1" type="button" id="submit" style="display:none;" onclick="StatusLoadStep2();">Continue without picking a contact
                                                </button>
                                                
                                                <button type="button" class="btn btn-primary ladda-button example-button m-1" id="submit_with_user" style="display:none;" type="submit" onclick="StatusLoadStep2();">Continue to case Details
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
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Case name</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control" id="case_name" value="" name="case_name" type="text"
                                                        placeholder="E.g. John Smith - Divorce">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Case number</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control" id="case_number" value="" name="case_number" type="text"
                                                        placeholder="Enter case number">
                                                    <small>A unique identifier for this case.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row" id="area_dropdown">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Practice area</label>
                                                <div class="col-md-6 form-group mb-3">
                                                    <select id="practice_area" name="practice_area" class="form-control custom-select col">
                                                        <option value="-1"></option>
                                                        <?php 
                                                            foreach($practiceAreaList as $k=>$v){?>
                                                        <option value="{{$v->id}}">{{$v->title}}</option>
                                                        <?php } ?>

                                                    </select>
                                                </div>
                                                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showText();" href="javascript:;">Add
                                                        new practice area</a></label>
                                            </div>
                                            <div class="form-group row" id="area_text">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Practice area</label>
                                                <div class="col-md-6 form-group mb-3">
                                                    <input class="form-control" id="practice_area_text" value="" name="practice_area_text" type="text"
                                                        placeholder="Enter new practice area">
                                                </div>
                                                <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showDropdown();"
                                                        href="javascript:;">Cancel</a></label>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Case stage
                                                </label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <select id="case_status" name="case_status" class="form-control custom-select col">
                                                        <option value="0"></option>
                                                        <?php 
                                                        foreach($caseStageList as $kcs=>$vcs){?>
                                                        <option value="{{$vcs->id}}">{{$vcs->title}}</option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Date opened</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control datepicker" id="case_open_date" value="{{ convertUTCToUserTimeZone('dateOnly') }}" name="case_open_date" type="text"
                                                    placeholder="mm/dd/yyyy">

                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Office
                                                </label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <select id="case_office" name="case_office" class="form-control custom-select col">
                                                        <?php  foreach($firmAddress as $k=>$v){?>
                                                            <option value="{{ $v->id }}">{{ $v->office_name }}</option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <textarea name="case_description" class="form-control" rows="5"></textarea>
                                                </div>
                                            </div>
                                            @if(IsCaseSolEnabled() == 'yes')
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Statute of Limitations</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <input class="form-control datepicker" id="case_statute" value="" name="case_statute" type="text"
                                                    placeholder="mm/dd/yyyy">

                                                </div>
                                            </div>
                                            <div class="form-group row" id="addMoreReminder">
                                                <label for="sol_reminders" class="col-sm-2 col-form-label">SOL Reminders</label>
                                                <div class="col">
                                                    @forelse (firmSolReminders() as $key => $item)
                                                        <div class="row form-group fieldGroup">
                                                            <div class="col-md-3 form-group mb-3">
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
                                                            <button type="button" class="btn btn-link pl-0 add-more">Add a reminder</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <div class="fieldGroupCopy copy hide" style="display: none;">
                                                <div class="col-md-3 form-group mb-3">
                                                    <select id="reminder_type" name="reminder_type[]" class="form-control custom-select  ">
                                                    @foreach(getEventReminderTpe() as $k =>$v)
                                                        <option value="{{$k}}">{{$v}}</option>
                                                    @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2 form-group mb-3">
                                                    <input class="form-control" id="reminder_days" value="1" name="reminder_days[]" type="number" min="0"> 
                                                </div> <span class="pt-2">Days</span>
                                                <div class="col-md-2 form-group mb-3">   
                                                    <button class="btn remove" type="button"><i class="fa fa-trash"
                                                    aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Conflict Check</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <label class="switch pr-5 switch-success mr-3"><span>Completed</span>
                                                        <input type="checkbox" name="conflict_check" id="conflict_check"><span class="slider"></span>
                                                    </label>

                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-2 col-form-label">Conflict Check Notes</label>
                                                <div class="col-md-10 form-group mb-3">
                                                    <textarea name="conflict_check_description" class="form-control" rows="5"></textarea>
                                                </div>
                                            </div>
                                        
                                            <div class="form-group row float-left">
                                                <button type="button" class="btn btn-outline-secondary m-1"  onclick="backStep1();">
                                                    <span class="ladda-label">Go Back</span>
                                                </button>
                                            </div>
                                            <div class="form-group row float-right">
                                                <button  type="button" class="btn btn-primary ladda-button example-button m-1"  onclick="StatusLoadStep3();">
                                                    <span class="ladda-label">Continue to Billing</span>
                                                </button>
                                            </div>
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                                <div class="col-md-2 form-group mb-3">
                                                    <div class="loader-bubble loader-bubble-primary" id="innerLoader1" style="display: none;"></div>
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
                                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Billing Contact</label>
                                                    <div class="col-md-10 form-group mb-3" id="loadBillingAjax">
                                                        <small>Choosing a billing contact allows you to batch bill this case.</small>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Billing Method</label>
                                                    <div class="col-md-10 form-group mb-3">
                                                        <select onchange="selectMethod();" id="billingMethod" name="billingMethod"
                                                            class="form-control custom-select col">
                                                            <option value=""></option>
                                                            <option value="hourly">Hourly</option>
                                                            <option value="contingency">Contingency</option>
                                                            <option value="flat">Flat Fee</option>
                                                            <option value="mixed">Mix of Flat Fee and Hourly</option>
                                                            <option value="pro_bono">Pro Bono</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row" id="billing_rate_text">
                                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Flat fee Amount</label>
                                                    <div class="input-group mb-3 col-sm-5">
                                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                        <input class="form-control case_rate number" name="default_rate" maxlength="10" type="text"
                                                            aria-label="Amount (to the nearest dollar)">
                                                    </div>
                                                </div>
                                            
                                                <div class="form-group row float-left">
                                                    <button type="button" class="btn btn-outline-secondary m-1"  onclick="backStep2();">
                                                        <span class="ladda-label">Go Back</span>
                                                    </button>
                                                </div>
                                                <div class="form-group row float-right">
                                                    <button type="button"  class="btn btn-primary ladda-button example-button m-1" data-style="expand-right" onclick="StatusLoadStep4();">
                                                        <span class="ladda-label">Continue to staff</span>
                                                    </button>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                                    <div class="col-md-2 form-group mb-3">
                                                        <div class="loader-bubble loader-bubble-primary" id="innerLoader3" style="display: none;"></div>
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
                                            <label for="inputEmail3" class="col-sm-3 col-form-label">Lead Attorney</label>
                                            <div class="col-md-9 form-group mb-3">
                                                <select id="lead_attorney" onchange="selectLeadAttorney();" name="lead_attorney" class="form-control custom-select col">
                                                    <option value=""></option>
                                                    <?php foreach($loadFirmUser as $key=>$user){?>
                                                    <option <?php if($user->id==Auth::User()->id){ echo "selected=selected"; } ?> value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
                                                    <?php } ?>
                                                </select>
                                                <small>The user you select will automatically be checked in the table below.</small>
                                            </div>

                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-3 col-form-label">Originating Attorney</label>
                                            <div class="col-md-9 form-group mb-3">
                                                <select onchange="selectAttorney();" id="originating_attorney" name="originating_attorney"
                                                    class="form-control custom-select col">
                                                    <option value=""></option>
                                                    <?php foreach($loadFirmUser as $key=>$user){?>
                                                    <option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
                                                    <?php } ?>
                                                </select>
                                                <small>The user you select will automatically be checked in the table below.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row" id="billing_rate_text">
                                            <label for="inputEmail3" class="col-sm-12 col-form-label">Who from your firm should have access to this
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
                                                    <th><input class="test-all-users-checkbox" id="select-all" type="checkbox"></th>
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
                                                    <td><input <?php if($user->id==Auth::User()->id){ echo "checked=checked";} ?> class="test-all-users-checkbox" type="checkbox" id="{{$user->id}}" name="selectedUSer[{{$user->id}}]"></td>
                                                    <td>{{$user->first_name}}</td>
                                                    <td>{{$user->last_name}}</td>
                                                    <td>{{$user->user_title}}</td>
                                                    <td>
                                                        <select  onchange="selectRate({{$user->id}});" name="rate_type[{{$user->id}}]" id="cc{{$user->id}}"
                                                            class="rate test-billing-rate-dropdown form-control mr-1" >
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
                                                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                            <input class="form-control case_rate number" name="new_rate[{{$user->id}}]" maxlength="10"  type="text"
                                                                aria-label="Amount (to the nearest dollar)">
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                        
                                        <div class="form-group row float-left">
                                            <button type="button" class="btn btn-outline-secondary m-1"  onclick="backStep3();">
                                                <span class="ladda-label">Go Back</span>
                                            </button>
                                        </div>

                                        <div class="form-group row float-right">
                                            <button type="button" onclick="saveFinalStep()" class="btn btn-primary ladda-button example-button m-1" data-style="expand-right">
                                                <span class="ladda-label">Save & Finish</span>
                                            </button>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                            <div class="col-md-2 form-group mb-3">
                                                <div class="loader-bubble loader-bubble-primary" id="innerLoader4" style="display: none;"></div>
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
<div id="AddCaseModel" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
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
                        <div id="smartwizard1">
                            <ul>
                                <li class="text-center"><a href="#step-1">1<br /><small>Clients & Contacts</small></a></li>
                                <li class="text-center"><a href="#step-2">2<br /><small>Case Details</small></a></li>
                                <li class="text-center"><a href="#step-3">3<br /><small>Billing</small></a></li>
                                <li class="text-center"><a href="#step-4">4<br /><small>Staff</small></a>
                                </li>
                            </ul>
                            <div>
                                <div id="step-1">
                                    
                                </div>
                                <div id="step-2">
                                
                                </div>
                                <div id="step-3">
                                

                                </div>
                                <div id="step-4">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end of main-content -->
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


<div id="ShowColorPicker" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Change Color</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="colorModel">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="ShowPrice" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Default Rate</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="rateModel">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="loadPermission" class="modal fade bd-example-modal-xl show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">All firm cases permission</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="permissionModel">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="changeStatus" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Change Status</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="statusLoad">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="statusUpdate" class="modal fade show" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Status Update</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="updateLoad">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadTaskDetailsView" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            
            <div class="modal-body">
                <div id="loadTaskDetailsViewArea"></div>
            </div>
        </div>
    </div>
</div>

<div id="deleteTask" class="modal fade modal-overlay" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Confirmation</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="eventID">
                        <form class="deleteTaskForm" id="deleteTaskForm" name="deleteTaskForm" method="POST">
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


<div id="loadReminderPopupIndexInViewOverlay" class="modal fade bd-example-modal-lg modal-overlay" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static" style="">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Set Task Reminders</h5>
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
<style> .modal { overflow: auto !important; }</style>
@endsection

@section('page-js')
<script src="{{ asset('assets\js\custom\feedback.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
                        
        $('button').attr('disabled',false);
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

        var showChar = 110;  // How many characters are shown by default
        var ellipsestext = "...";
        var moretext = "Show more";
        var lesstext = "Show less";
        
        var dataTable =  $('#employee-grid').DataTable( {
            serverSide: true,
        // "dom": 'frtip',
        //	"dom": '<"toolbar">frtip',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :"loadCase", // json datasource
                type: "post",  // method  , by default get
                data :{ 'pa' : "{{$pa}}",'la' : "{{$la}}" , 'cs' : "{{$cs}}", 'mc' : "{{$mc}}", 'i' : "{{$i}}" },
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id'},
                { data: 'id'},
                { data: 'case_title' }, 
                { data: 'case_number'},
                { data: 'case_number' },
                { data: 'case_number' },
                { data: 'case_number'},
                { data: 'case_number'},
                { data: 'id','orderable': false},
                { data: 'id','orderable': false},],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                

                    $('td:eq(0)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/info">'+aData.case_title+'</a><br><small class="d-block practice-area">'+aData.practice_area_text+'</small></div>');
                    if(aData.case_number!=null){
                        $('td:eq(1)', nRow).html('<div class="text-left"><a class="case-number" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/info">'+aData.case_number+'</a></div>');
                    }else{
                        $('td:eq(1)', nRow).html('<div class="text-left"><a class="case-number" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/info"></a></div>');
                    }
                    // if (aData.case_status == 1) {
                    //     $('td:eq(2)', nRow).html('Discovery <a data-toggle="modal"  data-target="#changeStatus" data-placement="bottom" href="javascript:;"  onclick="changeStatus('+aData.id+');"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a>');
                    // } else if (aData.case_status == 2) {
                    //     $('td:eq(2)', nRow).html('In Trial <a data-toggle="modal"  data-target="#changeStatus" data-placement="bottom" href="javascript:;"  onclick="changeStatus('+aData.id+');"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a>');
                    // } else if (aData.case_status == 3) {
                    //     $('td:eq(2)', nRow).html('On Hold <a data-toggle="modal"  data-target="#changeStatus" data-placement="bottom" href="javascript:;"  onclick="changeStatus('+aData.id+');"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a>');
                    // }else{
                    //     $('td:eq(2)', nRow).html('Not Specified <a data-toggle="modal"  data-target="#changeStatus" data-placement="bottom" href="javascript:;"  onclick="changeStatus('+aData.id+');"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a>');
                    // }
                    $('td:eq(2)', nRow).html('<span style="white-space: nowrap;"> '+aData.case_stage_text+'@can("case_add_edit") <a data-toggle="modal"  data-target="#changeStatus" data-placement="bottom" href="javascript:;"  onclick="changeStatus('+aData.id+');"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a>@endcan</span>');
                                
                    var obj = aData.case_staff_details;
                    var i;
                    var urlList='';
                    for (i = 0; i < obj.length; ++i) {
                        urlList+='<a href="'+baseUrl+'/contacts/attorneys/'+obj[i].decode_id+'">'+obj[i].first_name+' '+obj[i].last_name+'</a>';
                        if(obj[i].lead_attorney==obj[i].id){
                            urlList+='(Lead Attorney)';
                        }
                        urlList+="<br>";
                    
                    }

                    var obj_upcoming_event = aData.upcoming_event;
                    var eventTitle=eventDate='';
                    if(obj_upcoming_event != null){
                        if(obj_upcoming_event.event_title!=null && obj_upcoming_event.event_title!=''){
                                eventTitle='<a href="#">'+obj_upcoming_event.event_title+'</a> ';
                        }else{
                                eventTitle='<a href="#">&lt;No Title&gt;</a> ';
                        }
                        eventDate=moment(obj_upcoming_event.start_date_time).format("MMM, DD YYYY, hh:mm a");
                    }else{
                        eventTitle='<i class="table-cell-placeholder"></i>';
                    }
                    $('td:eq(3)', nRow).html('<div class="text-left" style="white-space: nowrap;">'+urlList+'</div>');
                    
                    $('td:eq(4)', nRow).html('<div class="text-left" style="white-space: nowrap;">'+eventTitle+' <br> '+eventDate+'</div>');
                    
                    var obj_upcoming_task = aData.upcoming_task;
                    var taskTitle=taskDate=taskOverDue=taskAllLink='';
                    if(obj_upcoming_task != null) {
                        if(obj_upcoming_task.task_title!=null && obj_upcoming_task.task_title!=''){
                                taskTitle='<a data-toggle="modal"  data-target="#loadTaskDetailsView" data-placement="bottom" href="javascript:;"  onclick="loadTaskDetailsView('+obj_upcoming_task.id+');">'+obj_upcoming_task.task_title+'</a> ';
                        }else{
                                taskTitle='<a href="#">&lt;No Title&gt;</a> ';
                        }
                        
                        if(obj_upcoming_task.overdueTaskCounter!=0){
                            taskDate='<span class="badge badge-danger mr-1 p-1">DUE</span>'+obj_upcoming_task.task_due_date;
                            taskOverDue='<div class="overdue-tasks">'+aData.overdue_tasks_count+' Overdue Task</div>';
                        }else{
                            taskOverDue='';
                            taskDate='<span class="badge badge-secondary mr-1 p-1">DUE</span>'+obj_upcoming_task.task_due_date;
                        }
                        taskAllLink='<a href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/tasks" class="view-all-tasks d-print-none"><small>View all tasks</small></a>';

                    }else{
                        taskTitle='<i class="table-cell-placeholder"></i>';
                    }

                    $('td:eq(5)', nRow).html('<div class="text-left" style="white-space: nowrap;">'+taskTitle+' <br> '+taskDate+'<br>'+taskOverDue+''+taskAllLink+'</div>');
                        var commentBy= '';
                        var comment= ''
                        var updateobj = aData.case_update;
                    if(updateobj != null){
                        var commentBy= updateobj.created_by_user.first_name ?? '' +' '+ updateobj.created_by_user.last_name ?? '';
                        var comment= updateobj.update_status;
                        var createdAt= updateobj.created_new_date;
                    
                        $('td:eq(6)', nRow).html('<div class="text-left"><div class="status-update"><div class="test-created-by-info">Created '+createdAt+'<small> by <a class="test-created-by-link pendo-case-info-status-created-by" href="#">'+commentBy+'</a></small></div><div class="d-print-none"><div class="mt-1 status-update-description text-break"><small><div class="more">'+comment+'</div></small></div></div><a data-toggle="modal"  data-target="#statusUpdate" data-placement="bottom" href="javascript:;"  onclick="loadCaseUpdate('+aData.id+');"><small>Add New Update</small></a></div>');
                    }else{
                        $('td:eq(6)', nRow).html('<div class="text-left"><div class="status-update">No Status Update</div><a data-toggle="modal"  data-target="#statusUpdate" data-placement="bottom" href="javascript:;"  onclick="loadCaseUpdate('+aData.id+');"><small>Add New Update</small></a></div>');
                    }
                    $('td:eq(7)', nRow).html('<div class="text-left"><div class="details">'+aData.created_new_date+'<small> by <a href="'+baseUrl+'/contacts/attorneys/'+aData.createdby+'">'+aData.created_by_name+'</a></small></div></div>');
                    @can('case_add_edit')
                    $('td:eq(8)', nRow).html('<div class="text-left" style="white-space: nowrap;"><a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/info"> <i class="fas fa-eye pr-2  align-middle d-print-none"></i title="View Details"></i> <a data-toggle="modal"  data-target="#EditCaseModel" data-placement="bottom" href="javascript:;"  onclick="updateCaseDetails('+aData.id+');"><i class="fas fa-pen align-middle d-print-none"></i></a></a></div>');
                    @else
                    $('td:eq(8)', nRow).html('<div class="text-left" style="white-space: nowrap;"><a class="name" href="'+baseUrl+'/court_cases/'+aData.case_unique_number+'/info"> <i class="fas fa-eye pr-2  align-middle d-print-none"></i title="View Details"></i> </a></div>');
                    @endcan
                    //  $('td:eq(6)', nRow).html('<div class="text-center"><a data-toggle="tooltip" data-placement="bottom" title="View" class="btn btn-primary btn-sm" href="'+baseUrl+'/project?id='+ aData.decode_id +'"> View</a></div>');
                    //loadTaskView
                    
                },
                "initComplete": function(settings, json) {
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
                    
                }
            });
            $("div.toolbar").html('<small class="text-muted mx-1">Text Size</small><button type="button" arial-label="Decrease text size" data-testid="dec-text-size" class="btn-sm py-0 px-1 mx-1 btn btn-outline-light decrease "><i class="fas fa-minus fa-xs"></i></button><button type="button" arial-label="Increase text size" data-testid="inc-text-size" class="btn-sm py-0 px-1 mx-1 btn btn-outline-light increase" ><i class="fas fa-plus fa-xs"></i></button>');
            // Toolbar extra buttons
            var btnFinish = $('<button></button>').text('Finish')
                .addClass('btn btn-info')
                .on('click', function () { alert('Finish Clicked'); });
            var btnCancel = $('<button></button>').text('Cancel')
                .addClass('btn btn-danger')
                .on('click', function () { $('#smartwizard').smartWizard("reset"); });
            
            var isAdded=localStorage.getItem("addedClient");
            if(isAdded!=null){
                $("#AddCaseModelUpdate").modal("show");
                selectUserAutoLoad(isAdded);
                localStorage.removeItem("addedClient");
            }
            
            if(localStorage.getItem("caseList")==""){
                localStorage.setItem("caseList","13");
            } 
    
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

            $('#AddCaseModel,#AddCaseModelUpdate').on('hidden.bs.modal', function () {
                // dataTable.ajax.reload();
                window.location.reload(null, false);   
        
            });    
            
            
            $('#AddContactModal').on('hidden.bs.modal', function () {
                //loadStep1();
                // $('#AddCaseModel').modal('show'); // To fix modal backdrop issue 
            //  $('#AddCaseModel').modal('hide');   
            
            });
            $('#changeStatus,#statusUpdate,#EditCaseModel').on('hidden.bs.modal', function () {
                $("#preloader").show();

                dataTable.ajax.reload(null, false);
                setTimeout(function(){ 
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
                    $("#preloader").hide();

                }, 1000);
            });
            var originalSize = $('td').css('font-size');        
            var currentSize=localStorage.getItem("caseList");
            $('td').css('font-size', currentSize+'px');    
            //Increase the font size 
            $(".increase").click(function(){         
                modifyFontSize('increase');  
            });     
            
            //Decrease the font size
            $(".decrease").click(function(){   
                
                modifyFontSize('decrease');  
            });

            $("#createCase").validate({
                rules: {
                    case_name:{
                        required:true
                    }
                },
                messages: {
                    
                    case_name: {
                        required: "Case name is a required field"
                    }
                },
                errorPlacement: function (error, element) {
                    if (element.is('#case_list')) {
                        error.appendTo('#CaseListError');
                    } else {
                        element.after(error);
                    }
                }
            });
            $(".add-more").click(function () {
                var fieldHTML = '<div class="row form-group fieldGroup">' + $(".fieldGroupCopy").html() +
                    '</div>';
                $('body').find('.fieldGroup:last').before(fieldHTML);
            });
            $('#createCase').on('click', '.remove', function () {
                var $row = $(this).parents('.fieldGroup').remove();
            });

            $('#case_statute').datepicker({
                'format': 'm/d/yyyy',
                'autoclose': true,
                'todayBtn': "linked",
                'clearBtn': true,
                'todayHighlight': true
            }).on('change.dp', function (e) {
                $("#addMoreReminder").show();
            });

            $('#select-all').on('change', function () {
            $('.test-all-users-checkbox').prop('checked', $(this).prop("checked"));
            
        });
        

    });

    function loadTaskDetailsView(task_id) {
        $("#loadTaskDetailsViewArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/tasks/loadTaskViewPage", // json datasource
            data: { "task_id": task_id},
            success: function (res) {
                $("#loadTaskDetailsViewArea").html(res);
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
            localStorage.setItem("caseList",currentFontSize);
        }
    }  
    
    function loadAllStep() {
        $('#smartwizard').smartWizard("reset"); 
        $('#createCase')[0].reset();
        $("#user_type").select2("val", "");
    }
    function loadStep1(id) {
        $("#preloader").show();
        $("#step-1").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/case/loadStep1", // json datasource
                data: {"user_id":id},
                success: function (res) {
                    $("#AddContactModal").modal('hide');
                $("#step-1").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function changeStatus(id) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url:  baseUrl +"/case/loadStatus", // json datasource
            data: {
                "case_id": id
            },
            success: function (res) {
                $("#statusLoad").html(res);
                $("#preloader").hide();
            }
        })
    }

    function loadCaseUpdate(id) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url:  baseUrl +"/case/loadCaseUpdate", // json datasource
            data: {
                "case_id": id
            },
            success: function (res) {
                $("#updateLoad").html(res);
                $("#preloader").hide();
            }
        })
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


    function AddContactModal() {
        $("#innerLoader").css('display', 'none');
        $("#preloader").show();
        $("#step-1-again").html('');
        $(function () {
            $.ajax({
                type: "POST",
                // url:  baseUrl +"/contacts/loadAddContactFromCase", // json datasource
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
            data: {"selectdValue": selectdValue},
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
    function backStep3() {
        $('#smartwizard').smartWizard('prev');
        
    }

    function loadReminderPopupIndexInCaseList(task_id) {
        $("#reminderDataIndexInView").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/tasks/loadReminderPopupIndexDontRefresh", // json datasource
                data: {
                    "task_id": task_id,
                    "from_view":"yes"
                },
                success: function (res) {
                    $("#reminderDataIndexInView").html(res);
                }
            })
        })
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
                        window.location.href=baseUrl+'/court_cases/'+res.case_unique_number+'/info';
                    }
                }
            });
        
    }
    
    $("#addMoreReminder").hide();
    $("#innerLoader1").css('display', 'none');
    $("#area_text").css('display', 'none');
    
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
    
    function selectMethod() {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#billingMethod option:selected").val();
        if (selectdValue == 'mixed' || selectdValue == 'flat') {
            $("#billing_rate_text").show();
        } else {
            $("#billing_rate_text").hide();
        }
    }
    function selectAttorney() {
        var selectdValue = $("#originating_attorney option:selected").val();
        $("#" + selectdValue).prop('checked', true);
    }
    function selectLeadAttorney() {
        var selectdValue = $("#lead_attorney option:selected").val();
        $("#" + selectdValue).prop('checked', true);
    }
    function selectRate(id) {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#cc"+id+" option:selected").val();
        // alert(selectdValue); 
        if (selectdValue == 'Default_Rate') {
            $("#default_"+id).show();
            $("#custome_"+id).hide();
        } else {
            $("#custome_"+id).show();
            $("#default_"+id).hide();
        }
    }
    function callOnClick(){
        $("#beforebutton").hide();
        $("#beforetext").show();
        $("#submit").show();
        $("#submit_with_user").hide();
    }
    $("#case_name").focus();

    function printEntry()
    {
        $('#employee-grid_length').hide();
        $('#employee-grid_info').hide();
        $('#employee-grid_paginate').hide();
        $('#hiddenLable').show();
        var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        $(".main-content-wrap").remove();
        window.print(canvas);
        // w.close();
        window.location.reload();
        return false;  
    }
    $('#hiddenLable').hide();

</script>
@stop
