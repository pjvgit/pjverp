<form class="createEvent" id="createEvent" name="createEvent" method="POST">
    @csrf
    <div class="row">
        <div class="col-8">
            <div id="showError" style="display:none"></div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
            </div>

            
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Case or Lead</label>
                <div class="col-8 form-group mb-3">
                    <select class="form-control case_or_lead" id="case_or_lead" name="case_or_lead"
                        data-placeholder="Search for an existing contact or company">
                        <option value="">Search for an existing Case or Lead</option>
                        <optgroup label="Court Cases">
                            <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                            <option <?php if($case_id==$Caseval->id){ echo "selected=selected"; }?> value="{{$Caseval->id}}">{{$Caseval->case_title}}</option>
                            <?php } ?>
                        </optgroup>
                        <optgroup label="Leads">
                            <?php foreach($CaseMasterClient as $Companykey=>$Companyval){ ?>
                            <option value="{{$Companyval->id}}">{{$Companyval->first_name}}</option>
                            <?php } ?>
                        </optgroup>
                    </select>

                </div>
            </div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-md-8 form-group mb-3">
                    <label class="form-check-label">
                        <input class="mr-2 no_case_link" type="checkbox" id="no_case_link" name="no_case_link">
                        <span>This event is not linked to a case</span>
                    </label>

                    <span id="CaseListError"></span>

                </div>
            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Event name</label>
                <div class="col-md-6 form-group mb-3">
                    <input class="form-control" id="event_name" value="" name="event_name" type="text" maxlength="512">
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
                                </div><span class="Select-arrow ml-1 mt-3"></span>
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
                                data-color="#0070bb">Customize Types</div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Start</label>
                <div class="col-md-3 form-group mb-3">
                    <input class="form-control" id="start_date" value="{{date('m/d/Y')}}" name="start_date" type="text"
                        placeholder="mm/dd/yyyy">

                </div>
                <div class="col-md-2 form-group mb-3">
                    <?php 
                    $time=date('H:i',strtotime($currentDateTime));
                    $new_time= date('H:i', strtotime($time.'+1 hour')); ?>
                    <input class="form-control timepicker" id="start_time"
                        value="{{date('h:i A',strtotime($new_time))}}" name="start_time" type="text" placeholder="">

                </div>
                <div class="col-md-3 form-group mb-3 pt-2">
                    <label class="form-check-label"><input class="mr-2 all-day all_day" type="checkbox" id="all_day"
                            name="all_day"><span>All
                            day</span></label>

                </div>
            </div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">End</label>
                <div class="col-md-3 form-group mb-3">
                    <input class="form-control" id="end_date" value="{{date('m/d/Y')}}" name="end_date" type="text"
                        placeholder="mm/dd/yyyy">

                </div>
                <div class="col-md-2 form-group mb-3">
                    <?php $new_time= date('H:i', strtotime($new_time.'+1 hour')); ?>
                    <input class="form-control timepicker" id="end_time" value="{{date('h:i A',strtotime($new_time))}}"
                        name="end_time" type="text" placeholder="">

                </div>
                <div class="col-md-3 form-group mb-3 pt-2">
                    <label class="form-check-label"><input title="You can not edit recurring events"
                            class="mr-2 recuring_event" id="recuring_event" type="checkbox"><span>This event
                            repeats</span></label>

                </div>

            </div>


            <div class="form-group row pre-load-location" id="repeat_dropdown">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-md-3 form-group mb-3">
                    <select onchange="selectType()" id="event-frequency" name="event-frequency"
                        class="form-control custom-select  ">
                        <option value="DAILY">Daily</option>
                        <option value="EVERY_BUSINESS_DAY">Every Business Day</option>
                        <option value="CUSTOM">Weekly</option>
                        <option value="WEEKLY">Weekly on {{date('l')}}</option>
                        <option value="MONTHLY">Monthly</option>
                        <option value="YEARLY">Yearly</option>
                    </select>
                </div>
                <div class="col-md-5 form-group mb-3 repeat_yearly ">
                    <select id="yearly-frequency" name="yearly-frequency" class="form-control custom-select  ">
                        <option value="YEARLY_ON_DAY">On day {{date('d')}} of {{date('F')}}</option>
                        <option value="YEARLY_ON_THE">On the fourth {{date('l')}} of {{date('F')}}</option>
                        <option value="YEARLY_ON_THE_LAST">On the last {{date('l')}} of {{date('F')}}</option>
                    </select>
                </div>
                <div class="col-md-5 form-group mb-3 repeat_monthly">
                    <select id="monthly-frequency" name="monthly-frequency" class="form-control custom-select  ">
                        <option value="MONTHLY_ON_DAY">On day 24</option>
                        <option value="MONTHLY_ON_THE">On the fourth {{date('l')}}</option>
                        <option value="MONTHLY_ON_THE_LAST">On the last {{date('l')}}</option>
                    </select>
                </div>
            </div>
            <div class="form-group row" id="repeat_custom">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="custom-box">
                    <div>
                        <div class="custom-rule-form align-items-baseline mt-2 mb-2 d-flex w-50"><span>Repeat
                                every</span><input class="form-control mx-2 w-25" id="event-interval" type="number"
                                value="1"><span class="ml-1">week(s)</span></div>
                        <div>
                            <div class="filter-options d-flex">
                                <div class="mr-3"><label class="d-inline-flex align-items-center"><input id="Sun-option"
                                            name="Sun" type="checkbox"><span class="ml-2 ">Sun</span></label></div>
                                <div class="mr-3"><label class="d-inline-flex align-items-center"><input id="Mon-option"
                                            name="Mon" type="checkbox"><span class="ml-2 ">Mon</span></label></div>
                                <div class="mr-3"><label class="d-inline-flex align-items-center"><input
                                            id="Tues-option" name="Tues" type="checkbox"><span
                                            class="ml-2 ">Tues</span></label></div>
                                <div class="mr-3"><label class="d-inline-flex align-items-center"><input id="Wed-option"
                                            name="Wed" type="checkbox"><span class="ml-2 ">Wed</span></label></div>
                                <div class="mr-3"><label class="d-inline-flex align-items-center"><input
                                            id="Thurs-option" name="Thurs" type="checkbox" checked=""><span
                                            class="ml-2 ">Thurs</span></label></div>
                                <div class="mr-3"><label class="d-inline-flex align-items-center"><input id="Fri-option"
                                            name="Fri" type="checkbox"><span class="ml-2 ">Fri</span></label></div>
                                <div class="mr-3"><label class="d-inline-flex align-items-center"><input id="Sat-option"
                                            name="Sat" type="checkbox"><span class="ml-2 ">Sat</span></label></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row repeat_yearly" id="repeat_yearly">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-sm-7  d-flex flex-row align-items-center w-50"><span>Repeat every</span><input
                        class="form-control mx-2 w-25" id="event-interval" type="number" value="1"><span>year(s)</span>
                </div>
            </div>
            <div class="form-group row repeat_monthly" id="repeat_monthly">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-sm-7  d-flex flex-row align-items-center w-50"><span>Repeat every</span><input
                        class="form-control mx-2 w-25" id="event-interval" type="number"
                        value="45"><span>month(s)</span></div>
            </div>
            <div class="form-group row" id="repeat_daily">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="d-flex flex-row align-items-center w-50"><span>Repeat every</span>
                    <input class="form-control mx-2 w-25" id="event-interval" type="number"
                        value="1"><span>day(s)</span>
                </div>
            </div>
            <div class="form-group row endondiv" id="endondiv">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-sm-7  d-flex flex-row align-items-center w-50"><span>End on</span>
                    <input class="mx-2 w-50 form-control" id="end_on" value="" readonly name="end_on" type="text"
                        placeholder="mm/dd/yyyy"><label class="form-check-label">
                        <input class=" pt-2" type="checkbox" checked="checked" id="no_end_date_checkbox"
                            name="no_end_date_checkbox">
                        <span>No end date</span>
                    </label>
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
                        <option value="{{$val->id}}">{{$val->location_name}}</option>
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
                    <input class="form-control" id="location_name" value="" name="location_name" type="text"
                        placeholder="">
                </div>
                <label id="cancel_new_label" for="inputEmail3" class="col-sm-2 col-form-label">
                    <a onclick="closeAddLocation();" href="javascript:;">Cancel</a>
                </label>
            </div>
            <div class="form-group row add-new">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Address</label>
                <div class="col-md-8 form-group mb-3">
                    <input class="form-control" id="address" name="address" maxlength="255" value="" type="text"
                        placeholder="Enter address">
                </div>
            </div>
            <div class="form-group row add-new">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Address2</label>
                <div class="col-md-8 form-group mb-3">
                    <input class="form-control" id="address2" name="address2" maxlength="255" value="" type="text"
                        placeholder="Enter address2">
                </div>
            </div>

            <div class="form-group row add-new">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-md-3 form-group mb-3">
                    <input class="form-control" id="city" name="city" value="" maxlength="255" placeholder="Enter city">
                </div>
                <div class="col-md-3 form-group mb-3">
                    <input class="form-control" id="state" name="state" value="" maxlength="255"
                        placeholder="Enter state">
                </div>
                <div class="col-md-2 form-group mb-3">
                    <input class="form-control" id="postal_code" value="" maxlength="255" name="postal_code"
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
                        <option value="{{$val->id}}"> {{$val->name}}</option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
                <div class="col-md-8 form-group mb-3">
                    <textarea id="description" name="description" class="form-control " placeholder="" rows="5"
                        style="max-height: 600px; overflow: hidden; overflow-wrap: break-word; resize: none; height: 111.6px;"
                        spellcheck="false" data-gramm="false"></textarea>
                    <p class="form-text text-muted mb-1">This description will be viewable by anyone invited to this
                        event.
                    </p>
                </div>
            </div>
            <div class="form-group row fieldGroup"  >
                <label for="reminders" class="col-sm-2 col-form-label">Reminders</label>
                <div class="pb-3 reminder-row ">
                    <div class="">
                        <div class="d-flex col-10 pl-0 align-items-center">
                            <div class="pl-0 col-3">
                                <div>
                                    <div class=""><select id="reminder_user_type" name="reminder_user_type"
                                            class="form-control custom-select  ">
                                            <option value="me">Me</option>
                                            <option value="attorney">Attorneys</option>
                                            <option value="paralegal">Paralegals</option>
                                            <option value="staff">Staff</option>
                                        </select></div>
                                </div>
                            </div>
                            <div class="pl-0 col-3">
                                <div>
                                    <div class=""><select id="reminder_type" name="reminder_type"
                                            class="form-control custom-select  ">
                                            <option value="popup">popup</option>
                                            <option value="email">email</option>
                                        </select></div>
                                </div>
                            </div><input name="reminder-number" class="form-control col-2 reminder-number" value="1">
                            <div class="col-4">
                                <div>
                                    <div class=""><select id="reminder_time_unit" name="reminder_time_unit"
                                            class="form-control custom-select  ">
                                            <option value="minute">minutes</option>
                                            <option value="hour">hours</option>
                                            <option value="day">days</option>
                                            <option value="week">weeks</option>
                                        </select></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row fieldGroupCopy copy hide" style="display: none;">
                <label for="reminders" class="col-sm-2 col-form-label"></label>
                <div class="pb-3 reminder-row ">
                    <div class="">
                        <div class="d-flex col-10 pl-0 align-items-center">
                            <div class="pl-0 col-3">
                                <div>
                                    <div class=""><select id="reminder_user_type" name="reminder_user_type"
                                            class="form-control custom-select  ">
                                            <option value="me">Me</option>
                                            <option value="attorney">Attorneys</option>
                                            <option value="paralegal">Paralegals</option>
                                            <option value="staff">Staff</option>
                                        </select></div>
                                </div>
                            </div>
                            <div class="pl-0 col-3">
                                <div>
                                    <div class=""><select id="reminder_type" name="reminder_type"
                                            class="form-control custom-select  ">
                                            <option value="popup">popup</option>
                                            <option value="email">email</option>
                                        </select></div>
                                </div>
                            </div><input name="reminder-number" class="form-control col-2 reminder-number" value="1">
                            <div class="col-4">
                                <div>
                                    <div class=""><select id="reminder_time_unit" name="reminder_time_unit"
                                            class="form-control custom-select  ">
                                            <option value="minute">minutes</option>
                                            <option value="hour">hours</option>
                                            <option value="day">days</option>
                                            <option value="week">weeks</option>
                                        </select></div>
                                </div>
                            </div><button
                            class="btn remove" type="button"><i
                                class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="reminders" class="col-sm-2 col-form-label"></label>
                <div class="pb-3 reminder-row ">
                    <button type="button" class="btn btn-link p-0 test-add-new-reminder add-more">Add a
                        reminder</button>
                </div>
            </div>
            
            
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-2 col-form-label"></label>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-check-label"><input class="mr-2" type="checkbox" name="is_event_private">
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
            <section class="sharing-list">
                <div class="sharing-table clients-table">
                    <div class="table-responsive">
                        <table class="table table-lg">
                            <thead>
                                <tr class="no-border">
                                    <th class="sharing-list-header no-border w-75">Contacts &amp; Leads</th>
                                    <th class="no-border">Invite<span class="pl-1" style="cursor: pointer;"><i
                                                id="help-bubble-3" aria-hidden="true"
                                                class="fa fa-question-circle icon-question-circle icon text-primary"></i></span>
                                    </th>
                                    <th class="no-border">Attend</th>
                                </tr>
                            </thead>
                            <tbody class="no-border">
                                <tr>
                                    <td>Select All</td>
                                    <td><input name="client-share-all" type="checkbox"></td>
                                    <td><input name="client-attend-all" type="checkbox"></td>
                                </tr>
                            </tbody>
                            <tbody class="no-border">
                                <tr class="sharing-user">
                                    <td class="d-flex no-border "><span class="mr-2">[SAMPLE] John Doe</span>
                                        <div style="text-indent: 0px;">
                                            <div id="communication-19798317"><i class="texting-off-icon"></i><i
                                                    class="no-email-icon"></i></div>
                                        </div><small class="client-portal-alert float-right"
                                            style="visibility: hidden;"><span style="position: relative;"
                                                class="tooltip-wrapper"><span><i
                                                        class="tooltip-alert"></i></span></span></small>
                                    </td>
                                    <td><label class="mb-0"><input data-email-present="false"
                                                data-full-name="[SAMPLE] John Doe" data-has-edit-permission="true"
                                                data-id="19798317" name="share-checkbox" type="checkbox"
                                                class="client-login-not-enabled handler-attached"></label></td>
                                    <td><label class="mb-0"><input disabled="" name="attend-checkbox"
                                                type="checkbox"></label></td>
                                </tr>
                                <tr class="sharing-user">
                                    <td class="d-flex no-border "><span class="mr-2">Client2 l</span>
                                        <div style="text-indent: 0px;">
                                            <div id="communication-19903684"><i class="texting-off-icon"></i></div>
                                        </div><small class="client-portal-alert float-right"
                                            style="visibility: hidden;"><span style="position: relative;"
                                                class="tooltip-wrapper"><span><i
                                                        class="tooltip-alert"></i></span></span></small>
                                    </td>
                                    <td><label class="mb-0"><input data-email-present="true" data-full-name="Client2 l"
                                                data-has-edit-permission="true" data-id="19903684" name="share-checkbox"
                                                type="checkbox"
                                                class="client-login-not-enabled handler-attached"></label>
                                    </td>
                                    <td><label class="mb-0"><input disabled="" name="attend-checkbox"
                                                type="checkbox"></label></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div><button type="button" class="btn btn-link">Include staff member not linked to this
                    case</button>
                <div class="sharing-table staff-table">
                    <div class="table-responsive">
                        <table class="table table-lg">
                            <thead>
                                <tr class="no-border">
                                    <th class="sharing-list-header no-border w-75">Staff</th>
                                    <th class="no-border">Share</th>
                                    <th class="no-border">Attend</th>
                                </tr>
                            </thead>
                            <tbody class="no-border">
                                <tr>
                                    <td>Select All</td>
                                    <td><input name="staff-share-all" type="checkbox"></td>
                                    <td><input name="staff-attend-all" type="checkbox"></td>
                                </tr>
                            </tbody>
                            <tbody class="no-border">
                                <tr class="sharing-user">
                                    <td class="d-flex no-border "><span class="mr-2">user4 teat</span></td>
                                    <td><label class="mb-0"><input data-email-present="true" data-full-name="user4 teat"
                                                data-has-edit-permission="true" data-id="19798612" name="share-checkbox"
                                                type="checkbox" class="" checked=""></label></td>
                                    <td><label class="mb-0"><input name="attend-checkbox" type="checkbox"></label>
                                    </td>
                                </tr>
                                <tr class="sharing-user">
                                    <td class="d-flex no-border "><span class="mr-2">test testt</span></td>
                                    <td><label class="mb-0"><input data-email-present="true" data-full-name="test testt"
                                                data-has-edit-permission="true" data-id="19918289" name="share-checkbox"
                                                type="checkbox" class="" checked=""></label></td>
                                    <td><label class="mb-0"><input name="attend-checkbox" type="checkbox"></label>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="form-group float-right">
                <a href="#">
                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
                </a>
                <button class="btn btn-primary example-button m-1" id="submit" type="submit"
                    data-style="expand-left">Save Event </button>
            </div>
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                <div class="col-md-2 form-group mb-3">
                    <div class="loader-bubble loader-bubble-primary" id="innerLoader"></div>
                </div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#start_date").datepicker();
        $("#end_date").datepicker();
        $("#end_on").datepicker();

        $('.timepicker').timepicker({
            'timeFormat': 'h:i A'
        }); ////https://www.jonthornton.com/jquery-timepicker/
        $("#innerLoader").css('display', 'none');
        $(".add-new").hide();
        $("#cancel_new_label").hide();
        $("#add_new_label").show();
        $(".dropdown-color").on('click', function () {
            $("#dropdown-menu").toggle();
        });
        $("#endondiv").hide();
        $('#repeat_dropdown').hide();

        $("#repeat_daily").hide();
        $("#repeat_custom").hide();
        $(".repeat_monthly").hide();
        $(".repeat_yearly").hide();
        // $(".hide").hide();
        $(".add-more").click(function () {
            var fieldHTML = '<div class="row form-group fieldGroup">'+$(".fieldGroupCopy").html()+'</div>';
                $('body').find('.fieldGroup:last').after(fieldHTML);
        });	
        $('#createEvent').on('click', '.remove', function () {
			var $row = $(this).parents('.fieldGroup').remove();
		});

        $("#createEvent").validate({
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
                }
            },
            messages: {
                case_or_lead: {
                    required: "Select a court case or check not linked to a case"

                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#case_or_lead')) {
                    error.appendTo('#CaseListError');
                } else {
                    element.after(error);
                }
            }
        });

        $('#createEvent').submit(function (e) {
            e.preventDefault();
            // $("#submit").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            if (!$('#createEvent').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString = $("form").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveAddEventPage", // json datasource
                data: dataString,
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
        $("input:checkbox.all_day").click(function () {
            if ($(this).is(":checked")) {
                $("#start_time").attr("disabled", true);
                $("#end_time").attr("disabled", true);

            } else {
                $('#start_time').removeAttr("disabled");
                $('#end_time').removeAttr("disabled");

            }
        });
        $("input:checkbox.no_case_link").click(function () {
            if ($(this).is(":checked")) {
                $("#case_or_lead").attr("disabled", true);
            } else {
                $('#case_or_lead').removeAttr("disabled");
            }
        });

        $("input:checkbox#no_end_date_checkbox").click(function () {
            if ($(this).is(":checked")) {
                $("#end_on").attr("readonly", true);
            } else {
                $('#end_on').removeAttr("readonly");
            }
        });

        $("input:checkbox.recuring_event").click(function () {
            if ($(this).is(":checked")) {
                $("#repeat_dropdown").show();
                $("#endondiv").show();

            } else {
                $("#endondiv").hide();

                $('#repeat_dropdown').hide();
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

    function selectType() {
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#event-frequency option:selected").val() // or
        if (selectdValue == 'DAILY') {
            $("#repeat_daily").show();
        } else if (selectdValue == 'CUSTOM') {
            $("#repeat_custom").show();
        } else if (selectdValue == 'MONTHLY') {
            $(".repeat_yearly").hide();
            $(".repeat_monthly").show();
        } else if (selectdValue == 'YEARLY') {
            $(".repeat_yearly").show();
            $(".repeat_monthly").hide();
        } else {
            $("#repeat_daily").hide();
            $("#repeat_custom").hide();
            $(".repeat_monthly").hide();
            $(".repeat_yearly").hide();
        }
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

    function loadStep2(res) {

        console.log(res);
        $('#smartwizard').smartWizard("next");
        $("#innerLoader").css('display', 'none');
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

</script>
