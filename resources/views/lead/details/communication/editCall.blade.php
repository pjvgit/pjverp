<?php
$CommonController= new App\Http\Controllers\CommonController();
$callDateTime = date("Y-m-d H:i:s", strtotime($CommonController->convertUTCToUserTime(date('Y-m-d H:i:s',strtotime($Calls['call_date'].' '.$Calls['call_time'])),Auth::User()->user_timezone)));
?>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link  <?php if($Calls['call_type']=="0") { echo "active show"; } ?>" id="home-basic-tab" data-toggle="tab" href="#homeBasic" role="tab"
            aria-controls="homeBasic" aria-selected="false">Incoming</a>
    </li>
    <li class="nav-item">
        <a class="nav-link  <?php if($Calls['call_type']=="1") { echo "active show"; } ?>" id="profile-basic-tab" data-toggle="tab" href="#profileBasic" role="tab"
            aria-controls="profileBasic" aria-selected="true">Outgoing</a>
    </li>

</ul>
<div class="tab-content" id="myTabContent">
    <div class="showError" style="display:none"></div>
    <div class="tab-pane fade   <?php if($Calls['call_type']=="0") { echo "active show"; } ?>" id="homeBasic" role="tabpanel" aria-labelledby="home-basic-tab">
        <form class="EditIncomingCall" id="EditIncomingCall" name="EditIncomingCall" method="POST">
            <span id="response"></span>
            @csrf
            <input class="" value="{{$Calls['id']}}" maxlength="250" name="call_id" type="hidden">
            <input class="" value="0" maxlength="250" name="call_type" type="hidden">

            <div class="col-md-12">
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Date and time</label>
                    <div class="col-sm-4">
                        <input class="form-control input-date" value="{{date('m/d/Y',strtotime($callDateTime))}}" id="dateadded" maxlength="250"
                            name="call_date" type="text">
                    </div>
                    <div class="col-sm-2">
                        <input class="form-control input-time" value="{{date('h:i A',strtotime($callDateTime))}}" id="dateadded" maxlength="250" name="call_time" type="text">
                    </div>
                  
                </div>

                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Caller name</label>
                    <div class="col-md-9 form-group mb-3">
                        <select onchange="getMobileNumber()" class="form-control caller_name select2" id="caller_name" name="caller_name" style="width: 100%;" placeholder="Select or enter a name...">
                                <?php if($Calls['caller_name']==NULL){?><option>{{$Calls['caller_name_text']}}</option><?php  }?>
                              <?php foreach($ClientAndLead as $key=>$val){?>
                                <option <?php if($val->id==$Calls['caller_name']){ echo "selected=selected"; } ?> value="{{$val->id}}"> {{substr($val->first_name,0,50)}} {{substr($val->last_name,0,50)}}
                                <?php if($val->user_level=="5") echo "(Lead)"; ?> <?php if($val->user_level=="2") echo "(Client)"; ?></option>
                                <?php } ?>
                        </select>
                        <span id="callername"></span>
                        <small class="mb-0 form-text text-muted">Search for an existing contact or type a name that is not in {{config('app.name')}}.</small>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Phone number</label>
                    <div class="col-sm-9">
                        <input class="form-control" value="{{$Calls['phone_number']}}" id="phone_number" maxlength="250" placeholder=""
                            name="phone_number" type="text">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Case/Potential Case</label>
                    <div class="col-md-9 form-group mb-3">
                        <select class="form-control" id="case" name="case"
                            style="width: 100%;" placeholder="Select a called name">
                            <option></option>
                            <optgroup label="Cases">
                                <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                                <option  <?php if($Caseval->id==$Calls['case_id']){ echo "selected=selected"; } ?> uType="case" 
                                    value="{{$Caseval->id}}">{{substr($Caseval->case_title,0,100)}}</option>
                                <?php } ?>
                            </optgroup>
                            <optgroup label="Potential Cases">
                                <?php foreach($potentialCase as $caseLeadListKey=>$caseLeadListVal){ ?>
                                <option  <?php if($caseLeadListVal->id==$Calls['case_id']){ echo "selected=selected"; } ?> uType="lead" value="{{$caseLeadListVal->id}}">Potential Case: {{substr($caseLeadListVal->first_name,0,100)}} {{substr($caseLeadListVal->last_name,0,100)}}</option>
                                <?php } ?>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Call for</label>
                    <div class="col-md-9 form-group mb-3">
                        <select class="form-control caller_name select2" id="call_for" name="call_for"
                            style="width: 100%;" placeholder="Select a called name">
                            <option></option>
                             <?php foreach($getAllFirmUser as $key=>$val){?>
                                <option <?php if($val->id==$Calls['call_for']){ echo "selected=selected"; } ?> value="{{$val->id}}"> {{substr($val->first_name,0,50)}} {{substr($val->last_name,0,50)}}
                               </option>
                                <?php } ?>
                        </select>
                        <span id="callforname"></span>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Message</label>
                    <div class="col-sm-9">
                        <textarea rows="5" class="form-control" name="message" placeholder="Notes">{{$Calls['message']}}</textarea>
                    </div>
                </div>
                <br>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <label class="switch pr-5 switch-success mr-3">
                            <span id="IresolveText">This call is resolved</span>
                            <span id="InonResolveText" class="error">This call is unresolved</span>
                            <input type="checkbox" id="Icall_resolved" name="call_resolved" <?php if($Calls['call_resolved']=="yes"){ echo "checked=checked"; } ?> ><span class="slider"></span>
                        </label>
                       
                    </div>
                </div>
                </span>
                <hr>
                {{-- <button id="btn" class="btn btn-success">Start</button>
                 <div id="t" class="badge">00:00</div> --}}
               
                 {{-- <button type="button" data-testid="timer-start" id="Staclass="btn btn-link" aria-label="Start Timer"><i class="fas fa-2x fa-play-circle text-success"></i></button>
                 <button type="button" data-testid="timer-pause" class="btn btn-link" aria-label="Pause Timer"><i class="fas fa-2x fa-pause-circle text-dark"></i></button> --}}
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;"></div>
                <div class="form-group row float-right">
                    <a href="#">
                        <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                    </a>
                    <button class="btn btn-primary ladda-button example-button m-1 submit" id="submitButton" type="submit">Save</button>
                </div>
            </div>
        </form>
    </div>
    <div class="tab-pane fade  <?php if($Calls['call_type']=="1") { echo "active show"; } ?>" id="profileBasic" role="tabpanel" aria-labelledby="profile-basic-tab">
        <form class="EditOutgoingCall" id="EditOutgoingCall" name="EditOutgoingCallEditOutgoingCall" method="POST">
            <span id="response"></span>
            @csrf
            <input class="" value="{{$Calls['id']}}" maxlength="250" name="call_id" type="hidden">
            <input class="" value="1" maxlength="250" name="call_type" type="hidden">

            <div class="col-md-12">
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Date and time</label>
                    <div class="col-sm-4">
                        <input class="form-control input-date" value="{{date('m/d/Y',strtotime($callDateTime))}}" id="dateadded" maxlength="250"
                            name="call_date" type="text">
                    </div>
                    <div class="col-sm-2">
                        <input class="form-control input-time" value="{{date('h:i A',strtotime($callDateTime))}}" id="dateadded" maxlength="250" name="call_time" type="text">
                    </div>
                  
                </div>

                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Person called
                    </label>
                    <div class="col-md-9 form-group mb-3">
                        <select onchange="getMobileNumber()" class="form-control caller_name_out select2" id="caller_name_out" name="caller_name" style="width: 100%;" placeholder="Select or enter a name...">
                                <?php if($Calls['caller_name']==NULL){?><option>{{$Calls['caller_name_text']}}</option><?php  }?>
                              <?php foreach($ClientAndLead as $key=>$val){?>
                                <option <?php if($val->id==$Calls['caller_name']){ echo "selected=selected"; } ?> value="{{$val->id}}"> {{substr($val->first_name,0,50)}} {{substr($val->last_name,0,50)}}
                                <?php if($val->user_level=="5") echo "(Lead)"; ?> <?php if($val->user_level=="2") echo "(Client)"; ?></option>
                                <?php } ?>
                        </select>
                        <span id="callername"></span>
                        <small class="mb-0 form-text text-muted">Search for an existing contact or type a name that is not in {{config('app.name')}}.</small>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Phone number</label>
                    <div class="col-sm-9">
                        <input class="form-control" value="{{$Calls['phone_number']}}" id="phone_number" maxlength="250" placeholder=""
                            name="phone_number" type="text">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Case/Potential Case</label>
                    <div class="col-md-9 form-group mb-3">
                        <select class="form-control" id="case_out" name="case"
                            style="width: 100%;" placeholder="Select a called name">
                            <option></option>
                            <optgroup label="Cases">
                                <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                                <option  <?php if($Caseval->id==$Calls['case_id']){ echo "selected=selected"; } ?> uType="case" 
                                    value="{{$Caseval->id}}">{{substr($Caseval->case_title,0,100)}}</option>
                                <?php } ?>
                            </optgroup>
                            <optgroup label="Potential Cases">
                                <?php foreach($potentialCase as $caseLeadListKey=>$caseLeadListVal){ ?>
                                <option  <?php if($caseLeadListVal->id==$Calls['case_id']){ echo "selected=selected"; } ?> uType="lead" value="{{$caseLeadListVal->id}}">Potential Case: {{substr($caseLeadListVal->first_name,0,100)}} {{substr($caseLeadListVal->last_name,0,100)}}</option>
                                <?php } ?>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Firm user
                    </label>
                    <div class="col-md-9 form-group mb-3">
                        <select class="form-control caller_name select2" id="call_for_out" name="call_for"
                            style="width: 100%;" placeholder="Select a called name">
                            <option></option>
                             <?php foreach($getAllFirmUser as $key=>$val){?>
                                <option <?php if($val->id==$Calls['call_for']){ echo "selected=selected"; } ?> value="{{$val->id}}"> {{substr($val->first_name,0,50)}} {{substr($val->last_name,0,50)}}
                               </option>
                                <?php } ?>
                        </select>
                        <span id="callforname"></span>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Message</label>
                    <div class="col-sm-9">
                        <textarea rows="5" class="form-control" name="message" placeholder="Notes">{{$Calls['message']}}</textarea>
                    </div>
                </div>
                <br>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <label class="switch pr-5 switch-success mr-3">
                            <span id="OresolveText">This call is resolved</span>
                            <span id="OnonResolveText" class="error">This call is unresolved</span>
                            <input type="checkbox" id="Ocall_resolved" name="call_resolved" <?php if($Calls['call_resolved']=="yes"){ echo "checked=checked"; } ?> ><span class="slider"></span>
                        </label>
                    </div>
                </div>
                </span>
                <hr>
                {{-- <button id="btn" class="btn btn-success">Start</button>
                 <div id="t" class="badge">00:00</div> --}}
               
                 {{-- <button type="button" data-testid="timer-start" id="Staclass="btn btn-link" aria-label="Start Timer"><i class="fas fa-2x fa-play-circle text-success"></i></button>
                 <button type="button" data-testid="timer-pause" class="btn btn-link" aria-label="Pause Timer"><i class="fas fa-2x fa-pause-circle text-dark"></i></button> --}}
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;"></div>
                <div class="form-group row float-right">
                    <a href="#">
                        <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                    </a>
                    <button class="btn btn-primary ladda-button example-button m-1 submit" id="submitButton" type="submit">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
       (function(){
            //timer actions
            $("#btn").click(function(){
                switch($(this).html().toLowerCase())
                {
                    case "start":
                        s = parseInt($("input[name='s']").val());
                        if(isNaN(s))
                        {
                            s = 0;
                            $("input[name='s']").val(0);
                        }
                        //you can specify action via object or a string
                        $("#t").timer({
                            action: 'start', 
                            seconds:s
                        });
                        $(this).html("Pause");
                        $("input[name='s']").attr("disabled", "disabled");
                        $("#t").addClass("badge-important");
                        break;
                    
                    case "resume":
                        //you can specify action via string
                        $("#t").timer('resume');
                        $(this).html("Pause")
                        $("#t").addClass("badge-important");
                        break;
                    
                    case "pause":
                        //you can specify action via object
                        $("#t").timer({action: 'pause'});
                        $(this).html("Resume")
                        $("#t").removeClass("badge-important");
                        break;
                }
            });
            
            $("#get-seconds-btn").click(function(){
                console.log($("#t").timer("get_seconds"));
            });
        })();
    </script>
<script type="text/javascript">
    $(document).ready(function () {
        
        $("#caller_name").select2({
            placeholder: "Select or a enter name...",
            theme: "classic",
            dropdownParent: $("#editCall"),
            tags:true
        });
        $("#case").select2({
            allowClear: true,
            placeholder: "Select a case to associate this call with...",
            theme: "classic",
            dropdownParent: $("#editCall"),
        });
        $("#call_for").select2({
            allowClear: true,
            placeholder: "Seach...",
            theme: "classic",
            dropdownParent: $("#editCall"),
        });

        $("#caller_name_out").select2({
            placeholder: "Select or a enter name...",
            theme: "classic",
            dropdownParent: $("#editCall"),
            tags:true
        });
        $("#case_out").select2({
            allowClear: true,
            placeholder: "Select a case to associate this call with...",
            theme: "classic",
            dropdownParent: $("#editCall"),
        });
        $("#call_for_out").select2({
            allowClear: true,
            placeholder: "Seach...",
            theme: "classic",
            dropdownParent: $("#editCall"),
        });


        $('.input-time').timepicker({
            'showDuration': false,
            'timeFormat': 'g:i A',
            'forceRoundTime': false,
            'step':15
        });
        // Initialize Date Pickers
        $('.input-date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        $("#innerLoaderTime").css('display', 'none');
        $("#innerLoaderTime").hide();
        $("#EditIncomingCall").validate({
            rules: {
                call_date: {
                    required: true
                },
                call_time: {
                    required: true
                },
                caller_name: {
                    required: true
                },
                phone_number: {
                    required: true
                },
                call_for: {
                    required: true
                },
                message: {
                    required: true
                },

            },
            messages: {
                call_date: {
                    required: "Please enter a date.",
                },
                call_time: {
                    required: "Please enter a time.",
                },
                caller_name: {
                    required: "Please enter a client name.",
                },
                phone_number: {
                    required: "Please enter a phone number.",
                },
                call_for: {
                    required: "Please select a firm user.",
                },
                message: {
                    required: "Please enter a message.",
                },
            },
            errorPlacement: function (error, element) {
                if (element.is('#caller_name')) {
                    error.appendTo('#callername');
                }else if (element.is('#call_for')) {
                    error.appendTo('#callforname');
                }else {
                    element.after(error);
                }
            }
        }); 
        $("#EditOutgoingCall").validate({
            rules: {
                call_date: {
                    required: true
                },
                call_time: {
                    required: true
                },
                caller_name: {
                    required: true
                },
                phone_number: {
                    required: true
                },
                call_for: {
                    required: true
                },
                message: {
                    required: true
                },

            },
            messages: {
                call_date: {
                    required: "Please enter a date.",
                },
                call_time: {
                    required: "Please enter a time.",
                },
                caller_name: {
                    required: "Please enter a client name.",
                },
                phone_number: {
                    required: "Please enter a phone number.",
                },
                call_for: {
                    required: "Please select a firm user.",
                },
                message: {
                    required: "Please enter a message.",
                },
            },
            errorPlacement: function (error, element) {
                if (element.is('#caller_name')) {
                    error.appendTo('#callername');
                }else if (element.is('#call_for')) {
                    error.appendTo('#callforname');
                }else {
                    element.after(error);
                }
            }
        });
    });


    $('#EditIncomingCall').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        if (!$('#EditIncomingCall').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#EditIncomingCall").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/updateCall", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
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
                   
                    // $("#EditIncomingCall").scrollTop(0);
                    $('#EditIncomingCall').animate({
                        scrollTop: 0
                    }, 'slow');
                    afterLoader();
                    return false;
                } else {
                    window.location.reload();
                }
            },
            error: function(xhr, status, error) {
                $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
            }
        });
    });
    $('#EditOutgoingCall').submit(function (e) {
        beforeLoader();
        e.preventDefault();

        if (!$('#EditOutgoingCall').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#EditOutgoingCall").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/updateCall", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes&outgoing=yes';
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
                   
                    // $("#EditIncomingCall").scrollTop(0);
                    $('#EditOutgoingCall').animate({
                        scrollTop: 0
                    }, 'slow');
                    afterLoader();
                    return false;
                } else {
                    window.location.reload();
                }
            },
            error: function(xhr, status, error) {
                $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
            }
        });
    });
    $("input:checkbox#Icall_resolved").click(function () {
        if ($(this).is(":checked")) {
            $("#InonResolveText").hide();
            $("#IresolveText").show();
        } else {
            $("#InonResolveText").show();
            $("#IresolveText").hide();
        }
    });
    $("input:checkbox#Ocall_resolved").click(function () {
        if ($(this).is(":checked")) {
            $("#OnonResolveText").hide();
            $("#OresolveText").show();
        } else {
            $("#OnonResolveText").show();
            $("#OresolveText").hide();
        }
    });
    <?php if($Calls['call_resolved']=="yes"){ ?> 
        $("#InonResolveText").hide();
        $("#IresolveText").show();
        $("#OnonResolveText").hide();
        $("#OresolveText").show();
    <?php }else{ ?> 
        $("#InonResolveText").show();
        $("#IresolveText").hide();
        $("#OnonResolveText").show();
        $("#OresolveText").hide();
    <?php } ?>

    
    function getMobileNumber(){
        beforeLoader();
        var selectdValue = $("#caller_name option:selected").val() // or
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/getMobileNumber",
            data: {
                "user_id": selectdValue
            },
            success: function (res) {
                $("#phone_number").val(res.mobile_number);
                afterLoader();
            }
        })
    }
</script>
