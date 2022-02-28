

 <?php
//  $CommonController= new App\Http\Controllers\CommonController();
//  $convertedStartDateTime= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($evetData->start_date .$evetData->start_time)),Auth::User()->user_timezone);
//  $convertedEndDateTime= $CommonController->convertUTCToUserTime(date('Y-m-d h:i:s',strtotime($evetData->end_date .$evetData->end_time)),Auth::User()->user_timezone);
 ?>
  <div class="modal-header">
    <h5 class="modal-title" id="editEtitle">Edit Event Details</h5>
    <h5 class="modal-title" id="editRtitle">Edit Event Details</h5>
    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="row" bladefilename="resources/views/case/event/loadEditEvent.blade.php">
        <div class="col-md-12" >
        

        <form class="EditEventForm" id="EditEventForm" name="EditEventForm" method="POST">
            <input class="form-control changed" id="event_id" value="{{ $evetData->id}}" name="event_id" type="text">
            @csrf
            <input class="form-control" value="{{ $eventRecurring->id}}" name="recurring_event_id" type="text" id="recurring_event_id">
            <input class="form-control" value="no" name="is_reminder_updated" type="text" id="is_reminder_updated">
            <div id="firstStep">
                <div class="row">
                    <div class="col-8">
                        <?php if(Auth::User()->add_event_guide=="0"){?>   
                            <div class="add-event-helper-tip px-2" id="add_event_guide">
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <a class="close add_event_guide">×</a>
                                    <p class="mb-0"><b>Get Started with Events:</b> Most events are linked to cases. To create events for your firm just check "This event is not linked to a case". After that you can choose whom to share it with and whether their attendance is required. You can also add a location, save your regularly used locations, or add an address to make getting directions easy.<br><a href="https://help.mycase.com/s/article/Creating-a-New-Calendar-Event" rel="noopener noreferrer" target="_blank"><u>Learn more about adding events.</u></a></p></div></div>
                    <?php } ?>

                        <div id="showError" style="display:none"></div>
                        <div class="add-event-helper-tip px-2"></div>
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                            <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                        </div>

                        <input type="hidden" id="text_case_id" value="" name="text_case_id">
                        <input type="hidden" id="text_lead_id" value="" name="text_lead_id">
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Case or Lead</label>
                            <div class="col-8 form-group mb-3">
                                <select onChange="changeCaseUser()" class="form-control case_or_lead" id="case_or_lead" name="case_or_lead"
                                    data-placeholder="Search for an existing contact or company">
                                    <option value="">Search for an existing Case or Lead</option>
                                    <optgroup label="Court Cases">
                                        <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                                        <option uType="case" <?php if($case_id==$Caseval->id){ echo "selected=selected"; }?>
                                            value="{{$Caseval->id}}">{{substr($Caseval->case_title,0,100)}} <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?> <?php if($Caseval->case_close_date!=NULL){  echo "[Closed]"; }?> </option>
                                        <?php } ?>
                                    </optgroup>
                                    <optgroup label="Leads">
                                        <?php foreach($caseLeadList as $caseLeadListKey=>$caseLeadListVal){ ?>
                                        <option uType="lead" value="{{$caseLeadListVal->id}}">{{substr($caseLeadListVal->first_name,0,100)}} {{substr($caseLeadListVal->last_name,0,100)}}</option>
                                        <?php } ?>
                                    </optgroup>

                                    
                                </select>

                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                            <div class="col-md-8 form-group mb-3">
                                <label class="form-check-label">
                                <input class="mr-2 no_case_link" type="checkbox" <?php if($evetData->case_id==''){?> checked="checked" <?php } ?> id="no_case_link" name="no_case_link">
                                    <span>This event is not linked to a case</span>
                                </label>

                                <span id="CaseListError"></span>

                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Event name</label>
                            <div class="col-md-6 form-group mb-3">
                                <input class="form-control" id="event_name" value="{{$evetData->event_title}}" name="event_name" type="text" maxlength="512">
                            </div>
                            <div class="col-md-2 form-group mb-3">
                                <div class="form-category-picker h-100 dropdown-color">
                                    <input type="hidden" value="" id="event_type" name="event_type">
                                    <button type="button" aria-haspopup="true" aria-expanded="false"
                                        class="align-items-start btn-rounded p-0 h-100 w-80 btn btn-secondary">
                                        <div id="chosen-category" class="d-flex justify-content-start" data-color="#6edcff"
                                            style="cursor: pointer; text-align: center;" data-value="928231">
                                            <div id="colorSet"
                                                style="background-color:#B9AFAF; border-radius: 2px; height: 25px; margin: 5px; width: 24px;">
                                            </div>
                                            <span class="Select-arrow ml-1 mt-3"></span>
                                        </div>
                                    </button>
                                    <div tabindex="-1" role="menu" aria-hidden="true" id="dropdown-menu"
                                        class="color-drop-down p-1 dropdown-menu">
                                        <div onclick="selectColor('#E5E5E5','0')" id="no-item-category"
                                            class="d-flex align-items-center justify-content-center"
                                            style="border-bottom: 1px solid rgb(229, 229, 229); color: rgb(229, 229, 229); cursor: pointer; height: 25px; margin: 5px; padding-bottom: 5px;"
                                            data-color="#E5E5E5">None</div>
                                        <?php
                                        foreach($allEventType as $ekey=>$eval){
                                        ?>
                                            <div onclick="selectColor('{{$eval->color_code}}','{{$eval->id}}')"
                                                id="item_category_928233" class="item_category d-flex justify-content-start"
                                                data-color="#ceaff2" data-value="928233" style="cursor: pointer; text-align: center;">
                                                <div id="{{$eval->id}}"
                                                    style="background-color: {{$eval->color_code}}; border-radius: 2px; height: 25px; margin: 5px; width: 24px;">
                                                </div>
                                                <div style="line-height: 35px;">{{$eval->title}}</div>
                                            </div>
                                        <?php } ?>
                                        <div id="customize-types" class="align-items-start"
                                            style="border-top: 1px solid rgb(229, 229, 229); color: rgb(0, 112, 187); cursor: pointer; height: 25px; margin: 5px; padding-top: 5px;"
                                            data-color="#0070bb"><a href="{{BASE_URL}}item_categories">Customize Types</a></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <span id="dateInputPanel">
                            <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">Start</label>
                                <div class="col-md-2 form-group mb-3">
                                    <input class="form-control input-date input-start" id="start_date" value="{{date('m/d/Y',strtotime($eventRecurring->user_start_date))}}" name="start_date" type="text" placeholder="mm/dd/yyyy">
                                    {{-- <input class="form-control input-date input-start" id="start_date" value="{{date('m/d/Y',strtotime($startDate))}}" name="start_date" type="text" placeholder="mm/dd/yyyy"> --}}

                                </div>
                                <div class="col-md-2 form-group mb-3">
                                    <?php 
                                    $time=date('H:i',strtotime($currentDateTime));
                                    $new_time= date('H:i', strtotime($time.'+1 hour')); ?>
                                    <input class="form-control  input-time input-start" id="start_time" value="{{date('h:i A',strtotime($evetData->start_date_time))}}" name="start_time" type="text" placeholder="">

                                </div>
                                <div class="col-md-2 form-group mb-3 pt-2">
                                    <label class="form-check-label"><input class="mr-2 all-day all_day" type="checkbox" id="all_day"
                                    name="all_day"><span>All day</span></label>
                                </div>
                                <div class="col-md-3 form-group mb-3 pt-2">
                                    <label class="form-check-label"><input title="You can not edit recurring events"
                                            class="mr-2 recuring_event" id="recuring_event" <?php if($evetData->is_recurring=='yes'){?> checked <?php } ?>  name="recuring_event" type="checkbox"><span>This event
                                            repeats</span></label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">End</label>
                                <div class="col-md-2 form-group mb-3">
                                    <input class="form-control input-date input-ends" id="end_date" value="{{date('m/d/Y',strtotime($eventRecurring->user_end_date))}}" name="end_date" type="text" placeholder="mm/dd/yyyy">
                                    {{-- <input class="form-control input-date input-ends" id="end_date" value="{{date('m/d/Y',strtotime($endDate))}}" name="end_date" type="text" placeholder="mm/dd/yyyy"> --}}
                                </div>
                                <div class="col-md-2 form-group mb-3">
                                    <?php $new_time= date('H:i', strtotime($new_time.'+1 hour')); ?>
                                    <input class="form-control  input-time input-end" id="end_time" value="{{date('h:i A',strtotime($evetData->end_date_time))}}" name="end_time" type="text" placeholder="">
                                </div>
                                
                            </div>
                            </span>
                        <div class="form-group row" id="repeat_dropdown">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Frequency</label>
                            <div class="col-md-3 form-group mb-3">
                                <select onchange="selectType()" id="event-frequency" name="event_frequency"
                                    class="form-control custom-select  ">
                                    <option <?php if($evetData->event_recurring_type=='DAILY'){?> selected=selected <?php } ?>  value="DAILY">Daily</option>
                                    <option <?php if($evetData->event_recurring_type=='EVERY_BUSINESS_DAY'){?> selected=selected <?php } ?>   value="EVERY_BUSINESS_DAY">Every Business Day</option>
                                    <option <?php if($evetData->event_recurring_type=='CUSTOM'){?> selected=selected <?php } ?>   value="CUSTOM">Weekly</option>
                                    <option <?php if($evetData->event_recurring_type=='WEEKLY'){?> selected=selected <?php } ?>   value="WEEKLY">Weekly on {{date('l',strtotime($evetData->start_date))}}</option>
                                    <option <?php if($evetData->event_recurring_type=='MONTHLY'){?> selected=selected <?php } ?>   value="MONTHLY">Monthly</option>
                                    <option <?php if($evetData->event_recurring_type=='YEARLY'){?> selected=selected <?php } ?>   value="YEARLY">Yearly</option>
                                </select>
                            </div>
                            <div class="col-md-5 form-group mb-3 repeat_yearly ">
                                <select id="yearly-frequency" name="yearly_frequency" class="form-control custom-select  ">
                                    <option <?php if($evetData->yearly_frequency=='YEARLY_ON_DAY'){?> selected=selected <?php } ?>  value="YEARLY_ON_DAY">On day {{date('d',strtotime($evetData->user_start_date))}} of {{date('F')}}</option>
                                    <option  <?php if($evetData->yearly_frequency=='YEARLY_ON_THE'){?> selected=selected <?php } ?>  value="YEARLY_ON_THE">On the fourth {{date('l',strtotime($evetData->user_start_date))}} of {{date('F')}}</option>
                                    <option  <?php if($evetData->yearly_frequency=='YEARLY_ON_THE_LAST'){?> selected=selected <?php } ?>  value="YEARLY_ON_THE_LAST">On the last {{date('l',strtotime($evetData->user_start_date))}} of {{date('F')}}</option>
                                </select>
                            </div>
                            <div class="col-md-5 form-group mb-3 repeat_monthly">
                                <select id="monthly-frequency" name="monthly_frequency" class="form-control custom-select  ">
                                    <option <?php if($evetData->monthly_frequency=='MONTHLY_ON_DAY'){?> selected=selected <?php } ?> value="MONTHLY_ON_DAY">On day {{date('d',strtotime($evetData->start_date))}}</option>
                                    <option <?php if($evetData->monthly_frequency=='MONTHLY_ON_THE'){?> selected=selected <?php } ?> value="MONTHLY_ON_THE">On the fourth {{date("l",strtotime($evetData->start_date))}}</option>
                                    <option <?php if($evetData->monthly_frequency=='MONTHLY_ON_THE_LAST'){?> selected=selected <?php } ?> value="MONTHLY_ON_THE_LAST">On the last {{date("l",strtotime($evetData->start_date))}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" id="repeat_custom">
                            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                            <div class="custom-box">
                                <div>
                                    <div class="custom-rule-form align-items-baseline mt-2 mb-2 d-flex w-50"><span>Repeat
                                            every</span><input class="form-control mx-2 w-25" id="event-interval" name="daily_weekname" type="number"
                                            value="{{($evetData->daily_weekname)??'1'}}"><span class="ml-1">week(s)</span></div>
                                    <div>
                                        
                                        <div class="filter-options d-flex">
                                            <div class="mr-3">
                                                <label class="d-inline-flex align-items-center">
                                                    <input id="Sun-option" name="custom[]" value="Sunday" type="checkbox" <?php if(date("l",strtotime($evetData->start_date))=='Sunday') { echo "checked=checked";} ?>>
                                                    <span class="ml-2 ">Sun</span>
                                                </label>
                                            </div>
                                            <div class="mr-3">
                                                <label class="d-inline-flex align-items-center">
                                                    <input id="Mon-option" name="custom[]" value="Monday" type="checkbox" <?php if(date("l",strtotime($evetData->start_date))=='Monday') { echo "checked=checked";} ?>>
                                                    <span class="ml-2 ">Mon</span>
                                                    </label>
                                                </div>
                                            <div class="mr-3">
                                                <label class="d-inline-flex align-items-center">
                                                    <input id="Tues-option" name="custom[]"  value="Tuesday"  type="checkbox" <?php if(date("l",strtotime($evetData->start_date))=='Tuesday') { echo "checked=checked";} ?>>
                                                    <span class="ml-2 ">Tues</span>
                                                </label>
                                            </div>
                                            <div class="mr-3">
                                                <label class="d-inline-flex align-items-center">
                                                    <input id="Wed-option" name="custom[]" value="Wednesday" type="checkbox" <?php if(date("l",strtotime($evetData->start_date))=='Wednesday') { echo "checked=checked";} ?>>
                                                    <span class="ml-2 ">Wed</span>
                                                </label>
                                            </div>
                                            <div class="mr-3">
                                                <label class="d-inline-flex align-items-center">
                                                    <input id="Thurs-option" name="custom[]"  value="Thursday" type="checkbox" <?php if(date("l",strtotime($evetData->start_date))=='Thursday') { echo "checked=checked";} ?>>
                                                    <span class="ml-2 ">Thurs</span>
                                                </label>
                                            </div>
                                            <div class="mr-3">
                                                <label class="d-inline-flex align-items-center">
                                                    <input id="Fri-option" name="custom[]" value="Friday" type="checkbox" <?php if(date("l",strtotime($evetData->start_date))=='Friday') { echo "checked=checked";} ?>>
                                                    <span class="ml-2 ">Fri</span>
                                                </label>
                                            </div>
                                            <div class="mr-3">
                                                <label class="d-inline-flex align-items-center">
                                                    <input id="Sat-option" name="custom[]" value="Saturday" type="checkbox" <?php if(date("l",strtotime($evetData->start_date))=='Saturday') { echo "checked=checked";} ?>>
                                                    <span class="ml-2 ">Sat</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row repeat_yearly" id="repeat_yearly">
                            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-7  d-flex flex-row align-items-center w-50"><span>Repeat every</span><input
                                    class="form-control mx-2 w-25" id="event-interval"  name="event_interval_year" type="number" value="{{($evetData->event_interval_year)??'1'}}"><span>year(s)</span>
                            </div>
                        </div>
                        <div class="form-group row repeat_monthly" id="repeat_monthly">
                            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-7  d-flex flex-row align-items-center w-50"><span>Repeat every</span><input
                                    class="form-control mx-2 w-25" id="event-interval" name="event_interval_month" type="number"
                                    value="{{($evetData->event_interval_month)??'1'}}"><span>month(s)</span></div>
                        </div>
                        <div class="form-group row" id="repeat_daily">
                            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                            <div class="d-flex flex-row align-items-center w-50"><span>Repeat every</span>
                                <input class="form-control mx-2 w-25" id="event-interval" name="event_interval_day" type="number"
                                value="{{($evetData->event_interval_day)??'1'}}"><span>day(s)</span>
                            </div>
                        </div>
                        <div class="form-group row endondiv" id="endondiv">
                            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-7  d-flex flex-row align-items-center w-50"><span>End on</span>
                                <input class="mx-2 w-50 form-control datepicker" id="end_on" value="{{ (($evetData->is_no_end_date=='no' && $evetData->end_on)) ? \Carbon\Carbon::parse($evetData->end_on)->format('m/d/Y') : "" }}" 
                                    name="end_on" type="text" placeholder="mm/dd/yyyy" {{ (($evetData->is_no_end_date=='no' && $evetData->end_on)) ? "" : "disabled" }}><label class="form-check-label">
                                    <input class=" pt-2" type="checkbox" <?php if($evetData->is_no_end_date=='yes') { echo "checked=checked";} ?>  id="no_end_date_checkbox"
                                        name="no_end_date_checkbox">
                                    <span>No end date</span>
                                </label>
                         
                            </div>
                            
                        </div>
                        <div class="form-group row" >
                            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-7  d-flex flex-row align-items-center w-50">
                                <span id="EndOnListError"></span>
                            </div>
                            
                        </div>
                        <div class="form-group row pre-load-location">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Location</label>
                            <div class="col-md-6 form-group mb-3">
                                <select id="case_location_list" name="case_location_list" class="form-control custom-select col">
                                    <option value="0">Please select location</option>
                                    <?php
                                    foreach($eventLocation as $key=>$val){
                                        ?>
                                    <option <?php if($evetData->event_location_id==$val->id){?> selected=selected <?php } ?> value="{{$val->id}}">{{$val->location_name}}</option>
                                    <?php 
                                    }
                                    ?>
                                </select>
                            </div>
                            <label id="add_new_label" for="inputEmail3" class="col-sm-2 col-form-label">
                                <a onclick="openAddLocation();" href="javascript:;">Add Location</a>
                            </label>

                        </div>
                    
                        <div class="form-group row  add-new ">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Location Name</label>
                            <div class="col-md-6 form-group mb-3">
                                <input class="form-control" id="location_name" value="{{($eventLocationAdded['location_name'])??''}}" name="location_name" type="text"
                                    placeholder="">
                            </div>
                            <label id="cancel_new_label" for="inputEmail3" class="col-sm-2 col-form-label">
                                <a onclick="closeAddLocation();" href="javascript:;">Cancel</a>
                            </label>
                        </div>
                        <div class="form-group row  add-new ">
                            <label for="inputEmail3" class="col-sm-2 ml-2 col-form-label"></label>
                            <div class="col-md-8 form-group mb-3">
                                <label class="form-check-label">
                                <input class="mr-2 location_future_use" type="checkbox" id="location_future_use" <?php if(($eventLocationAdded['location_future_use'])??''=='yes') { echo "checked=checked";} ?> name="location_future_use">
                                    <span> Save location for future use</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row add-new">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Address</label>
                            <div class="col-md-8 form-group mb-3">
                                <input class="form-control" id="address" name="address" maxlength="255" value="{{($eventLocationAdded['address1'])??''}}" type="text"
                                    placeholder="Enter address">
                            </div>
                        </div>
                        <div class="form-group row add-new">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Address2</label>
                            <div class="col-md-8 form-group mb-3">
                                <input class="form-control" id="address2" name="address2" maxlength="255" value="{{($eventLocationAdded['address2'])??''}}" type="text"
                                    placeholder="Enter address2">
                            </div>
                        </div>

                        <div class="form-group row add-new">
                            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                            <div class="col-md-3 form-group mb-3">
                                <input class="form-control" id="city" name="city" value="{{($eventLocationAdded['city'])??''}}" maxlength="255" placeholder="Enter city">
                            </div>
                            <div class="col-md-3 form-group mb-3">
                                <input class="form-control" id="state" name="state" value="{{($eventLocationAdded['state'])??''}}" maxlength="255"
                                    placeholder="Enter state">
                            </div>
                            <div class="col-md-2 form-group mb-3">
                                <input class="form-control" id="postal_code" value="{{($eventLocationAdded['postal_code'])??''}}" maxlength="255" name="postal_code"
                                    placeholder="Enter postal code">
                            </div>

                        </div>
                        <div class="form-group row add-new">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Country</label>
                            <div class="col-md-8 form-group mb-3">
                                <select class="form-control country" id="country" name="country" data-placeholder="Select Country"
                                    style="width: 100%;">
                                    <option value="">Select Country</option>
                                    <?php foreach($country as $key=>$val){?>
                                    <option <?php if(($eventLocationAdded['country'])??''==$val->id){?> selected=selected <?php } ?>  value="{{$val->id}}"> {{$val->name}}</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
                            <div class="col-md-8 form-group mb-3">
                                <textarea id="description" name="description" class="form-control " placeholder="" rows="5"
                                    style="max-height: 600px; overflow: hidden; overflow-wrap: break-word; resize: none; height: 111.6px;"
                                    spellcheck="false" data-gramm="false">{{$evetData->event_description}}</textarea>
                                <p class="form-text text-muted mb-1">This description will be viewable by anyone invited to this
                                    event.
                                </p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="reminders" class="col-sm-2 col-form-label">Reminders</label>
                            <div class="col">
                                <div>
                                    <?php
                                        foreach($eventReminderData as $rkey=>$rval){
                                        ?>
                                        <div class="form-group fieldGroup">
                                            <div class="">
                                                <div class="d-flex col-10 pl-0 align-items-center">
                                                    <div class="pl-0 col-3">
                                                        <div>
                                                            <div class="">
                                                                <select id="{{ $rkey + 1 }}" name="reminder_user_type[]" class="form-control custom-select reminder_user_type">
                                                                    @forelse (reminderUserType() as $key => $item)
                                                                    <option value="{{ $key }}" {{ ($rval->reminder_user_type == $key) ? "selected" : "" }}>{{ $item }}</option>
                                                                    @empty
                                                                    @endforelse
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="pl-0 col-3">
                                                        <div>
                                                            <div class="">
                                                                <select id="reminder_type_{{ $rkey + 1 }}" name="reminder_type[]" class="form-control custom-select valid" aria-invalid="false">
                                                                    @foreach(getEventReminderTpe() as $k =>$v)
                                                                        <option value="{{$k}}" <?php if($rval->reminder_type == $k){ echo "selected=selected"; } ?>>{{$v}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div><input name="reminder_number[]" type="number" min="0" id="reminder_number_{{ $rkey + 1 }}" class="form-control col-2 reminder-number" value="{{$rval->reminer_number}}">
                                                    <div class="col-4">
                                                        <div>
                                                            <div class="">
                                                                <select id="reminder_time_unit_{{ $rkey + 1 }}" name="reminder_time_unit[]" class="form-control custom-select  ">
                                                                    <option <?php if($rval->reminder_frequncy=="minute"){ echo "selected=selected"; } ?> value="minute">minutes</option>
                                                                    <option <?php if($rval->reminder_frequncy=="hour"){ echo "selected=selected"; } ?> value="hour">hours</option>
                                                                    <option <?php if($rval->reminder_frequncy=="day"){ echo "selected=selected"; } ?> value="day">days</option>
                                                                    <option <?php if($rval->reminder_frequncy=="week"){ echo "selected=selected"; } ?> value="week">weeks</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button class="btn remove" type="button">
                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    <div class="fieldGroup">
                                    </div>
                                    <div class="text-muted mb-2">You can only edit reminders that you created. Reminders assigned to you by another firm user will need to be edited by the creator.</div>
                                    <div><button type="button" class="btn btn-link p-0 test-add-new-reminder add-more">Add a reminder</button></div>
                                </div>
                            </div>
                        </div>  
                    
                        @include('case.event.add_more_reminder_div')

                        <div class="form-group row pt-">
                            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-check-label"><input class="mr-2" type="checkbox"  <?php if($evetData->is_event_private=='yes'){?> checked="checked" <?php } ?>  name="is_event_private">
                                    <span>Mark this event as private</span></label>
                            </div>
                        </div>
                        <div id="loadUserAjax"></div>
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                            <label for="inputEmail3" class="col-sm-10 col-form-label"></label>
                            <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
                        </div>
                    </div>
                    <div class="col-4">
                        <?php 
                        if(Auth::User()->add_event_guide2=="0"){?>
                            <div class="client-task-tip" id="add_event_guide2">
                                <div class="alert alert-info">
                                    <a class="close closeGuider add_event_guide2">×</a>
                                    <div class="tooltip-message">Assign a task to your client and they will receive a link
                                        to view and complete it via the client portal. <a href="#" rel="noopener noreferrer"
                                            target="_blank"><u>What will my client see?</u></a>
                                    </div>
                                    <div></div>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="empty-sharing-list text-muted text-left hideUser" id="hideUser"><p>Select a case or lead to share this event with clients and firm members. Check "This event is not linked to a case or lead" to share only with firm members.</p></div> 

                        <section class="sharing-list" id="edit_event_right_section">
                        </section>
                    </div>
                </div>
                <div class="justify-content-between modal-footer">
                    <div>
                        <div>
                        <div><b>Originally Created: </b>{{date('l, F jS Y',strtotime($evetData->created_at))}} by {{substr($userData->first_name,0,15)}} {{substr($userData->last_name,0,15)}}</div>
                        <?php if($updatedEvenByUserData!=''){?>
                            <div><b>Last Modified: </b>{{date('l, F jS Y',strtotime($evetData->updated_at))}} by {{substr($updatedEvenByUserData->first_name,0,15)}} {{substr($updatedEvenByUserData->last_name,0,15)}}</div>  
                        <?php } ?>
                        </div>
                    </div> <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
                    <div>
                        <a href="#">
                            <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
                        </a>
                    
                        {{-- <button class="btn btn-primary example-button m-1" id="submit" type="submit" data-style="expand-left">Save Event </button> --}}
                        <a class="btn btn-primary example-button m-1 text-white" onclick="deleteEventFunction1();"  data-style="expand-left">Save Event </a>
                    
                    </div>
                </div>
            </div>
            <div id="confirmSave" style="display:none;"> 
                <div class="row" >
                    <div class="col-md-12" id="eventID">
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                            <div class="col-md-10 form-group mb-3">
                                <div class="m-2" id="this_event_radio_div">
                                    <label class="form-check-label">
                                        <input type="radio" name="delete_event_type" class="pick-option mr-2" value="SINGLE_EVENT">
                                            <span>This event only</span>
                                    </label>
                                </div>
                                <div class="m-2">
                                    <label class="form-check-label">
                                        <input type="radio" name="delete_event_type" class="pick-option mr-2"  value="THIS_AND_FOLLOWING_EVENTS"><span>This and following events</span>
                                    </label>
                                </div>
                                <div class="m-2">
                                    <label class="form-check-label">
                                        <input type="radio" name="delete_event_type" class="pick-option mr-2" value="ALL_EVENTS" checked="checked">
                                        <span>All events</span>
                                    </label>
                                </div>
                                <small>Editing will remove any comments that were added to events in this series.</small>
                            </div>
                        </div>
                    </div>
                </div><!-- end of main-content -->
                <div class="justify-content-between modal-footer">
                    <div>
                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
          
                    </div>
                    <div>
                     
                        <button class="btn btn-secondary  m-1" type="button" onclick="goBack();" >Go Back</button>
                        <button class="btn btn-primary example-button m-1 submit" data-style="expand-left">Ok </button>
                        
                    </div>
                </div>
            </div>
        </form>
    </div>
</div><!-- end of main-content -->


<script type="text/javascript">
    $(document).ready(function () {
        // loadCaseClient({{$case_id}});
        // loadCaseNoneLinkedStaff({{$case_id}});
        // loadCaseLinkedStaff({{$case_id}});

        @if($evetData->is_recurring == 'no')
            $("#endondiv").hide();
            $('#repeat_dropdown').hide();
            $('#repeat_custom').hide();
            $('#repeat_monthly').hide();
            $('#repeat_yearly').hide();
            $('#repeat_daily').hide();
        @endif
        //disabled datepicker when checkbox is checked
        /* if($("input:checkbox#no_end_date_checkbox").is(":checked")) {
                $('#end_on').val('');
                $("#end_on").attr("disabled", true);
            } else {
                $('#end_on').removeAttr("disabled");
            } */
         
        $(".case_or_lead").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#loadEditEventPopup"),
        });
       
        $('#dateInputPanel .input-time').timepicker({
            'showDuration': false,
            'timeFormat': 'g:i A',
            'forceRoundTime': true 
        });

        // Initialize Date Pickers
        $('#dateInputPanel .input-date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
      
        // Initialize Datepair
        var dateContainer = document.getElementById('dateInputPanel');
        var datepair = new Datepair(dateContainer, {
            'dateClass': 'input-date',
            'timeClass': 'input-time',
            'startClass': 'input-start',
            'endClass': 'input-end'
        });

        $("#start_date").datepicker().on('change',function(e){
            $(this).removeClass('error');
            $("#start_date-error").text('');
            $("#this_event_radio_div").hide();
            var selected = $(this).val();
            $("#end_date").datepicker('setDate', selected);
            updateMonthlyWeeklyOptions();
        });
        $("#end_date").datepicker().on('change',function(e){
            $(this).removeClass('error');
            $("#end_date-error").text('');
        });

        $("#end_on").datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        }).on('changeDate', function(ev) {
            if($('#end_on').valid()){
            $('#datepicker').removeClass('invalid').addClass('success');   
            }
        });

        $(".hideUser").hide();
        $(".innerLoader").css('display', 'none');
        $(".add-new").hide();
        $("#cancel_new_label").hide();
        $("#add_new_label").show();
        $(".dropdown-color").on('click', function () {
            $("#dropdown-menu").toggle();
        });

         
        $("#HideShowNonlink").on('click', function () {
            $(".staff-table-nonlinked").toggle();
        });
       
        $(".hide").hide();
        /* $("#firstStep .add-more").click(function () {
            var fieldHTML = '<div class="row form-group fieldGroup">' + $(".fieldGroupCopy").html() +
                '</div>';
            $('body').find('.fieldGroup:last').before(fieldHTML);
        }); */
        $('#EditEventForm').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroup').remove();
            $("#is_reminder_updated").val("yes");
        });

        $("#EditEventForm").validate({
            rules: {
                case_or_lead: {
                    required: {
                        depends: function (element) {
                            var status = true;
                            if ($("#no_case_link:checked").val() !== undefined) {
                                var status = false;
                            }
                            return status;
                        }
                    }
                },
                end_on: {
                    required: {
                        depends: function (element) {
                            var status = true;
                            if ($("#no_end_date_checkbox:checked").val() !== undefined) {
                                var status = false;
                            }
                            return status;
                        }
                    }
                },
                start_date:{
                    required:true
                },
                end_date:{
                    required:true
                },
                start_time:{
                    required: function() {
                        if($("#loadEditEventPopup input:checkbox.all_day").is(":checked"))
                            return false;
                        else
                            return true;
                    }
                },
                end_time:{
                    required: function() {
                        if($("#loadEditEventPopup input:checkbox.all_day").is(":checked"))
                            return false;
                        else
                            return true;
                    }
                }
            },
            messages: {
                case_or_lead: {
                    required: "Select a court case or check not linked to a case"

                }, 
                end_on: {
                    required: "Please provide an end date or select no end date."

                },
                start_date: {
                    required: "Start date is invalid"

                },
                end_date: {
                    required: "End date is invalid"

                }, 
                start_time: {
                    required: "Start time is invalid"

                },
                end_time: {
                    required: "End time is invalid"

                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#case_or_lead')) {
                    $('.select2-container--classic .select2-selection--single').addClass("input-border-error");
                    error.appendTo('#CaseListError');
                }else if (element.is('#end_on')) {
                    error.appendTo('#EndOnListError');
                } else {
                    element.after(error);
                }
            }
        });
        $('.case_or_lead').on('select2:select', function (e) { 
            $('.select2-container--classic .select2-selection--single').removeClass("input-border-error");
            $('#CaseListError').text('');
        });

        var $form = $('#EditEventForm');
        var startItems = convertSerializedArrayToHash($form.serializeArray()); 

        $('#EditEventForm').submit(function (e) {
            e.preventDefault();
            $(".submit").attr("disabled", true);
            $(".innerLoader").css('display', 'block');
            if (!$('#EditEventForm').valid()) {
                $(".innerLoader").css('display', 'none');
                $('.submit').removeAttr("disabled");
                return false;
            }
            var dataString ='';
            // if($("input[name=delete_event_type][value=SINGLE_EVENT]").is(':checked')) {
                dataString = $("#EditEventForm").serialize(); 
            /* } else {
                var currentItems = convertSerializedArrayToHash($("#EditEventForm").serializeArray());
                var dataString = hashDiff( startItems, currentItems);
                dataString['recurring_event_id'] = $("#recurring_event_id").val();
                dataString['event_id'] = $("#event_id").val();
                dataString['delete_event_type'] = $("input[name=delete_event_type]:checked").val();
            } */
            console.log(dataString);
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveEditEventPage", // json datasource
                data: dataString,
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
                        $('#loadEditEventPopup').animate({ scrollTop: 0 }, 'slow');

                        return false;
                    } else {
                        window.location.reload();
                        // loadMoreEvent(tab1Page = 1);
                        // $('#loadEditEventPopup,#loadAddEventPopup').modal("hide");
                        // $(".innerLoader").css('display', 'none');

                    }
                }
            });
        });
      
            $("input:checkbox.all_day").click(function () {
            if ($(this).is(":checked")) {
                $("#start_time").attr("readonly", true);
                $("#end_time").attr("readonly", true);

            } else {
                $('#start_time').removeAttr("readonly");
                $('#end_time').removeAttr("readonly");

            }
        });
        
        // No end date checkbox click event
        $("input:checkbox#no_end_date_checkbox").click(function () {
            if ($(this).is(":checked")) {
                $('#end_on').val('');
                $("#end_on").attr("disabled", true);
            } else {
                $('#end_on').removeAttr("disabled");
            }
        });


        $("#loadEditEventPopup input:checkbox.recuring_event").change(function () {
            if ($(this).is(":checked")) {
                $("#repeat_dropdown").show();
                $("#endondiv").show();
                $("#no_end_date_checkbox").prop("checked", true);
                if ($("input:checkbox#no_end_date_checkbox").is(":checked")) {
                    $("#end_on").attr("disabled", true);
                } else {
                    $('#end_on').removeAttr("disabled");
                }

            } else {
                $("#endondiv").hide();

                $('#repeat_dropdown').hide();
                $('#repeat_custom').hide();
                
                $('#repeat_monthly').hide();
                $('#repeat_yearly').hide();
                $('#repeat_daily').hide();
                
            }
        });


    });

    function selectColor(c, id) {
        $('#colorSet').css('background-color', c);
        $(".item_category div i").remove();
        $('#' + id).html('<i aria-hidden="true" class="fa fa-check icon-check icon text-white mt-2"></i>');
        $("#event_type").val(id);
    }

    function openAddLocation() {
        $(".pre-load-location").hide();
        $(".add-new").show();
        $("#cancel_new_label").show();
        $("#add_new_label").hide();

    }

    function closeAddLocation() {
        $(".pre-load-location").show();
        $(".add-new").hide();
        $("#cancel_new_label").hide();
        $("#add_new_label").show();
    }

    /* function selectType() {
        $(".innerLoader").css('display', 'block');
        var selectdValue = $("#event-frequency option:selected").val() // or
        if (selectdValue == 'DAILY') {
            $("#repeat_daily").show();
            $("#repeat_custom").hide();
            $(".repeat_yearly").hide();
            $(".repeat_monthly").hide();
        } else if (selectdValue == 'CUSTOM') {
            $("#repeat_custom").show();
            $("#repeat_daily").hide();
            $(".repeat_monthly").hide();
            $(".repeat_yearly").hide();
        } else if (selectdValue == 'MONTHLY') {
            $(".repeat_yearly").hide();
            $(".repeat_monthly").show();
            $("#repeat_custom").hide();
            updateMonthlyWeeklyOptions();
        } else if (selectdValue == 'YEARLY') {
            $(".repeat_yearly").show();
            $(".repeat_monthly").hide();
            $("#repeat_custom").hide();
            updateMonthlyWeeklyOptions();
        } else if (selectdValue == 'WEEKLY') {
            updateMonthlyWeeklyOptions();
            $("#repeat_daily").hide();
            $("#repeat_custom").hide();
            $(".repeat_monthly").hide();
            $(".repeat_yearly").hide();
        } else {
            $("#repeat_daily").hide();
            $("#repeat_custom").hide();
            $(".repeat_monthly").hide();
            $(".repeat_yearly").hide();
        }
        $(".innerLoader").css('display', 'none');
    } */
    
    function selectTypeload(selectdValue) {
        $(".innerLoader").css('display', 'block');
        $('#repeat_dropdown').show();
        $("#repeat_daily").hide();
        $("#repeat_custom").hide();
        $(".repeat_monthly").hide();
        $(".repeat_yearly").hide();
       
        if (selectdValue == 'DAILY') {
            $("#repeat_daily").show();
            $("#repeat_custom").hide();
        } else if (selectdValue == 'CUSTOM') {
            $("#repeat_custom").show();
        } else if (selectdValue == 'MONTHLY') {
            $(".repeat_yearly").hide();
            $(".repeat_monthly").show();
            $("#repeat_custom").hide();
        } else if (selectdValue == 'YEARLY') {
            $(".repeat_yearly").show();
            $(".repeat_monthly").hide();
            $("#repeat_custom").hide();
        }else if (selectdValue == 'WEEKLY') {
            $('#repeat_dropdown').show();

        }else if(selectdValue == 'EVERY_BUSINESS_DAY'){

        } else {
            $("#repeat_daily").hide();
            $("#repeat_custom").hide();
            $(".repeat_monthly").hide();
            $(".repeat_yearly").hide();
            $('#repeat_dropdown').hide();
        }
        $(".innerLoader").css('display', 'none');
    }

    function removeUser(id) {
        $(".innerLoader").css('display', 'block');
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/remomeSelectedUser",
            data: {
                "selectdValue": id
            },
            success: function (res) {
                $("#loadUserAjax").html(res);
                $(".innerLoader").css('display', 'none');
            }
        })
    }

    function loadStep2(res) {

        $('#smartwizard').smartWizard("next");
        $(".innerLoader").css('display', 'none');
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/loadStep2", // json datasource
            data: {
                "user_id": res.user_id
            },
            success: function (res) {
                $("#step-2").html(res);
                $("#preloader").hide();
            }
        })

        return false;
    }

    // Commented as not required
    /* function loadCaseClient(case_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadCaseClientAndLeads",
            data: {
                "case_id": case_id
            },
            success: function (res) {
                $("#CaseClientSection").html(res);
                //$(".innerLoaderCase").css('display', 'none');
            }
        })
    }

    function loadCaseLinkedStaff(case_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadCaseLinkedStaff",
            data: {
                "case_id": case_id,
                "event_id":{{$evetData->id}},
                "from":"edit"
            },
            success: function (res) {
                $("#CaseLinkedStaffSection").html(res);
                //$(".innerLoaderCase").css('display', 'none');
            }
        })
    }
    function loadCaseNoneLinkedStaff(case_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadCaseNoneLinkedStaff",
            data: {
                "case_id": case_id
            },
            success: function (res) {
                $("#CaseNoneLinkedStaffSection").html(res);
            }
        })
    } */
    function loadAllFirmStaff() {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadFirmAllStaff",
            data: {"case_id": ''},
            success: function (res) {
                $("#displayStaffList").html(res);
            }
        })
    }
    function changeCaseUser() {

        $("#dynamicUSerTimes").html('');

        $("#text_lead_id").val('');
        $("#text_case_id").val('');
        var uType=$("#case_or_lead option:selected").attr('uType');
        var selectdValue = $("#case_or_lead option:selected").val();
        if(selectdValue!=''){
            if(uType=="case"){
                $("#text_case_id").val(selectdValue);
                $("#HideShowNonlink").show();
                loadRightSection(selectdValue);
            }else{
                $("#time_tracking_enabled").prop('checked',false)
                $("#text_lead_id").val(selectdValue);
                firmStaff();
            }
            $(".hideUser").hide();
        }else{
            $("#edit_event_right_section").html('');
            $("#HideShowNonlink").hide();
            $(".hideUser").show();
            loadDefaultContent();
        }
    }
    function loadRightSection(case_id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadEventRightSection",
            data: { "case_id": case_id, "event_id":"{{ $evetData->id}}" },
            success: function (res) {
                $("#edit_event_right_section").html(res);
            }
        })
    }

    function firmStaff() {
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/loadAllCaseStaffMember",
            data: "",
            success: function (res) {
                $("#edit_event_right_section").html(res);
              
            }
        })
    }
    changeCaseUser();
    function deleteEventFunction1() {
        if (!$('#EditEventForm').valid()) {
            $(".innerLoader").css('display', 'none');
            $('.submit').removeAttr("disabled");
            return false;
        }
        if(!$("#recuring_event").is(":checked")) {
            $("input[name=delete_event_type][value=SINGLE_EVENT]").attr('checked', 'checked');
            $(".submit").trigger("click");
        } else if($("#recuring_event").is(":checked") && "{{ $evetData->is_recurring }}" == 'no') {
            $("input[name=delete_event_type][value=SINGLE_EVENT]").attr('checked', 'checked');
            $(".submit").trigger("click");
        } else {
            $("input[name=delete_event_type][value=ALL_EVENTS]").attr('checked', 'checked');
            $("#confirmSave").css('display','block');
            $("#firstStep").css('display','none');
            $("#loadEditEventPopup .modal-dialog").removeClass("modal-xl");
            $("#exampleModalCenterTitle").html("Edit Recurring Event");
            $("#editEtitle").hide();
            $("#editRtitle").show();
            $(".innerLoader").css('display', 'none');
            return false;
        }
    }
    function goBack() {
        $("#confirmSave").css('display','none');
        $("#firstStep").css('display','block');
        $("#loadEditEventPopup .modal-dialog").addClass("modal-xl");
        $("#exampleModalCenterTitle").html("Edit Event");
        $("#editEtitle").show();
        $("#editRtitle").hide();
        $(".innerLoader").css('display', 'none');
        $('#showError').css('display', 'none');

    }
   
    $("input:checkbox#no_case_link").click(function () {
        if ($(this).is(":checked")) {
            $("#case_or_lead").attr("disabled", true);
            $('#case_or_lead').prop('selectedIndex',0);
            $("#HideShowNonlink").hide();
            firmStaff();
            $("#edit_event_right_section").html('');
            $(".hideUser").hide();
        } else {
            $("#edit_event_right_section").html('');
            $(".hideUser").show();
            $('#case_or_lead').removeAttr("disabled");
        }
    });
  
        
    $(".add_event_guide").click(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/hideAddEventGuide",
            data: {"type":"1"},
            success: function (res) {
                $("#add_event_guide").html('');
            }
        })
    });

    $(".add_event_guide2").click(function () {
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/hideAddEventGuide",
            data:  {"type":"2"},
            success: function (res) {
                $("#add_event_guide2").html('');
            }
        })
    });

    $("#editRtitle").hide();
    <?php  if($evetData->is_full_day=='yes'){  ?>
            $('input:checkbox.all_day').trigger('click');
            $("#start_time").val('').attr("readonly", true);
            $("#end_time").val('').attr("readonly", true);
    <?php }  ?>
  
    <?php  if(isset($evetData->event_recurring_type)){ ?>
            selectTypeload('{{$evetData->event_recurring_type}}');
            $("#endondiv").show();
    <?php }else{  ?>
            selectTypeload('');
            $("#endondiv").hide();
    <?php } ?>

    <?php if($evetData->event_type_id!=NULL){?>
     selectColor('{{$getEventColorCode}}', '{{$evetData->event_type_id}}');
    <?php } ?>

    // Get weekdays name
    function getWeekdays(date) {
        var weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
        return weekday[date.getDay()];
    }

    // Get nth day of month 
    function getNthDayOfMonth(date, weekday) {
        var nth= ['First', 'Second', 'Third', 'Fourth', 'Fifth'];
        return "On the "+nth[Math.floor(date.getDate()/7)]+' '+getWeekdays(date);
    }

    // Get updated option of weekly/monthly/yearly recurring
    function updateMonthlyWeeklyOptions() {
        var date = new Date($("#start_date").val());
        // for month
        $("#monthly-frequency").find('option').remove();
        $("#monthly-frequency").append(
            '<option value="MONTHLY_ON_DAY">On day '+date.getDate()+'</option><option value="MONTHLY_ON_THE">'+getNthDayOfMonth(date)+'</option>'
        );
        // for year
        $("#yearly-frequency").find('option').remove();
        var monthName = date.toLocaleString('default', { month: 'long' });
        $("#yearly-frequency").append(
            '<option value="YEARLY_ON_DAY">On day '+date.getDate()+' of '+monthName+'</option><option value="YEARLY_ON_THE">'+getNthDayOfMonth(date)+' of '+monthName+'</option>'
        );
        // for week
        $("#event-frequency option[value='WEEKLY']").text("Weekly on "+getWeekdays(date));
    }

function MarkAsChanged(){
    $(this).addClass("changed");
}
$(":input").blur(MarkAsChanged).change(MarkAsChanged);
$(":select").change(MarkAsChanged).change(MarkAsChanged);
$(":checkbox").change(MarkAsChanged).change(MarkAsChanged);
$("input[type=button]").click(function(){
    $(":input:not(.changed)").attr("disabled", "disabled");
    $("h1").text($("#test").serialize());
});

function hashDiff(h1, h2) {
    var d = {};
    for (k in h2) {
        if (h1[k] !== h2[k]) d[k] = h2[k];
    }
    return d;
}

function convertSerializedArrayToHash(a) { 
    var r = {}; 
    for (var i = 0;i<a.length;i++) { 
        r[a[i].name] = a[i].value;
    }
    return r;
}
</script>
