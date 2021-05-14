<?php
$CommonController= new App\Http\Controllers\CommonController();
$currentTime = date("h:i A", strtotime($CommonController->convertUTCToUserTime(date('Y-m-d H:i:s'),Auth::User()->user_timezone)));
?>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link  active show" id="home-basic-tab" data-toggle="tab" href="#homeBasic" role="tab"
            aria-controls="homeBasic" aria-selected="false">Incoming</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="profile-basic-tab" data-toggle="tab" href="#profileBasic" role="tab"
            aria-controls="profileBasic" aria-selected="true">Outgoing</a>
    </li>

</ul>
<div class="tab-content" id="myTabContent">
    <div class="showError" style="display:none"></div>
    <div class="tab-pane fade  active show" id="homeBasic" role="tabpanel" aria-labelledby="home-basic-tab">
        <form class="AddIncomingCall" id="AddIncomingCall" name="AddIncomingCall" method="POST">
            <span id="response"></span>
            @csrf
            <input type="hidden" name="timer_value" class="timer_count">
            <input type="hidden" name="st" class="st">

            <div class="col-md-12">
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Date and time</label>
                    <div class="col-sm-4">
                        <input class="form-control input-date" value="{{date('m/d/Y')}}" id="dateadded" maxlength="250"
                            name="call_date" type="text">
                    </div>
                    <div class="col-sm-2">
                        <input class="form-control input-time" value="{{$currentTime}}" id="dateadded" maxlength="250" name="call_time" type="text">
                    </div>
                  
                </div>

                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Caller name</label>
                    <div class="col-md-9 form-group mb-3">
                        <select onchange="getMobileNumber()" class="form-control caller_name select2" id="caller_name" name="caller_name" style="width: 100%;" placeholder="Select or enter a name...">
                            <option></option>
                              <?php foreach($ClientAndLead as $key=>$val){?>
                                <option value="{{$val->id}}"> {{substr($val->first_name,0,50)}} {{substr($val->last_name,0,50)}}
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
                        <input class="form-control phone_number" value="" id="phone_number" maxlength="250" placeholder=""
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
                                <option uType="case" 
                                    value="{{$Caseval->id}}">{{substr($Caseval->case_title,0,100)}}</option>
                                <?php } ?>
                            </optgroup>
                            <optgroup label="Potential Cases">
                                <?php foreach($potentialCase as $caseLeadListKey=>$caseLeadListVal){ ?>
                                <option uType="lead" value="{{$caseLeadListVal->id}}">Potential Case: {{substr($caseLeadListVal->first_name,0,100)}} {{substr($caseLeadListVal->last_name,0,100)}}</option>
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
                                <option value="{{$val->id}}"> {{substr($val->first_name,0,50)}} {{substr($val->last_name,0,50)}}
                               </option>
                                <?php } ?>
                        </select>
                        <span id="callforname"></span>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Message</label>
                    <div class="col-sm-9">
                        <textarea rows="5" class="form-control" name="message" placeholder="Notes"></textarea>
                    </div>
                </div>
                <br>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <label class="switch pr-5 switch-success mr-3">
                            <span id="IresolveText">This call is resolved</span>
                            <span id="InonResolveText" class="error">This call is unresolved</span>
                            <input type="checkbox" id="Icall_resolved" name="call_resolved" checked="checked"><span class="slider"></span>
                        </label>
                    </div>
                </div>
                </span>
                <hr>
                <div class="timewidget  form-group row w-80 float-left cursor-pointer">
                    <a id="btn" class="pause"><i class="fas fa-2x fa-pause-circle text-dark"></i></a>
                     <div id="t" class="badge m-1" style="font-size:14px;">00:00:00</div>
                    </div>  <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;"></div>
                <div class="form-group row float-right">
                    <a href="#">
                        <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                    </a>
                    <button type="submit" onclick="getButton('st')" data-testid="save-and-add-time" id="save_and_add_time_entry" class="save_and_add_time_entry btn btn-secondary m-1">Save + <i class="far fa-clock fa-lg"></i></button>

                    <button class="btn btn-primary ladda-button example-button m-1 submit" id="submitButton" type="submit">Save</button>
                </div>
            </div>
        </form>
    </div>
    <div class="tab-pane fade" id="profileBasic" role="tabpanel" aria-labelledby="profile-basic-tab">
        <form class="AddOutgoingCall" id="AddOutgoingCall" name="AddOutgoingCall" method="POST">
            <span id="response"></span>
            @csrf
            <input type="hidden" name="timer_value" class="timer_count">

            <div class="col-md-12">
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Date and time</label>
                    <div class="col-sm-4">
                        <input class="form-control input-date" value="{{date('m/d/Y')}}" id="dateadded" maxlength="250"
                            name="call_date" type="text">
                    </div>
                    <div class="col-sm-2">
                        <input class="form-control input-time" value="{{$currentTime}}" id="dateadded" maxlength="250" name="call_time" type="text">
                    </div>
                  
                </div>

                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Person called</label>
                    <div class="col-md-9 form-group mb-3">
                        <select onchange="getMobileNumber()" class="form-control caller_name_out select2" id="caller_name_out" name="caller_name" style="width: 100%;" placeholder="Select or enter a name...">
                            <option></option>
                              <?php foreach($ClientAndLead as $key=>$val){?>
                                <option value="{{$val->id}}"> {{substr($val->first_name,0,50)}} {{substr($val->last_name,0,50)}}
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
                        <input class="form-control phone_number" value="" id="phone_number" maxlength="250" placeholder=""
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
                                <option uType="case" 
                                    value="{{$Caseval->id}}">{{substr($Caseval->case_title,0,100)}}</option>
                                <?php } ?>
                            </optgroup>
                            <optgroup label="Potential Cases">
                                <?php foreach($potentialCase as $caseLeadListKey=>$caseLeadListVal){ ?>
                                <option uType="lead" value="{{$caseLeadListVal->id}}">Potential Case: {{substr($caseLeadListVal->first_name,0,100)}} {{substr($caseLeadListVal->last_name,0,100)}}</option>
                                <?php } ?>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Firm user</label>
                    <div class="col-md-9 form-group mb-3">
                        <select class="form-control caller_name select2" id="call_for_out" name="call_for"
                            style="width: 100%;" placeholder="Select a called name">
                            <option></option>
                             <?php foreach($getAllFirmUser as $key=>$val){?>
                                <option value="{{$val->id}}"> {{substr($val->first_name,0,50)}} {{substr($val->last_name,0,50)}}
                               </option>
                                <?php } ?>
                        </select>
                        <span id="callforname"></span>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label">Message</label>
                    <div class="col-sm-9">
                        <textarea rows="5" class="form-control" name="message" placeholder="Notes"></textarea>
                    </div>
                </div>
                <br>
                <div class="form-group row">
                    <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <label class="switch pr-5 switch-success mr-3">
                            <span id="OresolveText">This call is resolved</span>
                            <span id="OnonResolveText" class="error">This call is unresolved</span>
                            <input type="checkbox" id="Ocall_resolved" name="call_resolved" checked="checked"><span class="slider"></span>
                        </label>
                    </div>
                </div>
                </span>
                <hr>
                <div class="timewidget form-group row w-80 float-left cursor-pointer">
                    <a id="ibtn" class="pause"><i class="fas fa-2x fa-pause-circle text-dark"></i></a>
                     <div id="it" class="badge m-1" style="font-size:14px;">00:00:00</div>
                    </div> <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoaderTime" style="display: none;"></div>
                <div class="form-group row float-right">
                    <a href="#">
                        <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                    </a>
                    <button type="submit" onclick="getButton('st')" data-testid="save-and-add-time" id="save_and_add_time_entry" class="save_and_add_time_entry btn btn-secondary m-1">Save + <i class="far fa-clock fa-lg"></i></button>

                    <button class="btn btn-primary ladda-button example-button m-1 submit" id="submitButton" type="submit">Save</button>
                </div>
            </div>
        </form>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(".timewidget").hide();
        
        $(".save_and_add_time_entry").hide();
        // $("#case").on("select2-selecting", function(e) {
        //     $(".timewidget").hide();
        //     var uType=$("#case option:selected").attr('uType');
        //     alert(uType);
        //     if(uType=="case"){
        //         $(".timewidget").show();
        //         $("#t").timer({ action: 'start' });
        //         $("#it").timer({ action: 'start' });
        //         $(".save_and_add_time_entry").show();

        //     }else{
        //         $("#t").timer({ action: 'reset' });
        //         $("#it").timer({ action: 'reset' });
        //         $(".timewidget").hide();
        //         $(".save_and_add_time_entry").hide();

        //     }
        // });
        // $("#case_out").on("select2-selecting", function(e) {
        //     $(".timewidget").hide();
        //     var uType=$("#case_out option:selected").attr('uType');
        //     if(uType=="case"){
        //         $(".timewidget").show();
        //         $("#t").timer({action: 'start' });
        //         $("#it").timer({ action: 'start' });
        //         $(".save_and_add_time_entry").show();
        //     }else{
        //         $("#t").timer({action: 'reset'});
        //         $("#it").timer({action: 'reset'});
        //         $(".timewidget").hide();
        //         $(".save_and_add_time_entry").hide();
        //     }
        // });
    

        $("#btn").click(function(){
            switch($(this).attr('class').toLowerCase())
            {
                case "start":
                    $("#t").timer({
                        action: 'start'
                    });
                    $(this).html('<i class="fas fa-2x fa-pause-circle text-dark"></i>');
                    $("#t").addClass("badge-important");
                    $("#btn").removeClass("start");
                    $("#btn").addClass("pause");
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
                    $(this).html('<i class="fas fa-2x fa-play-circle text-success"></i>')
                    $("#t").removeClass("badge-important");
                    $("#btn").addClass("start");                    
                    $("#btn").removeClass("pause");
                    break;
            }
        });
        $("#ibtn").click(function(){
            switch($(this).attr('class').toLowerCase())
            {
                case "start":
                    $("#t").timer({
                        action: 'start'
                    });
                    $(this).html('<i class="fas fa-2x fa-pause-circle text-dark"></i>');
                    $("#it").addClass("badge-important");
                    $("#ibtn").removeClass("start");
                    $("#ibtn").addClass("pause");
                    break;
                
                case "resume":
                    //you can specify action via string
                    $("#it").timer('resume');
                    $(this).html("Pause")
                    $("#it").addClass("badge-important");
                    break;
                
                case "pause":
                    //you can specify action via object
                    $("#it").timer({action: 'pause'});
                    $(this).html('<i class="fas fa-2x fa-play-circle text-success"></i>')
                    $("#it").removeClass("badge-important");
                    $("#ibtn").addClass("start");                    
                    $("#ibtn").removeClass("pause");
                    break;
            }
        });
        $("#caller_name").select2({
            placeholder: "Select or a enter name...",
            theme: "classic",
            dropdownParent: $("#addCall"),
            tags:true
        });
        $("#case").select2({
            allowClear: true,
            placeholder: "Select a case to associate this call with...",
            theme: "classic",
            dropdownParent: $("#addCall"),
        }).on("change", function (e) {
            var uType=$("#case option:selected").attr('uType');
            if(uType=="case"){
                $(".timewidget").show();
                $("#t").timer({
                    action: 'start'
                });
                $("#it").timer({
                    action: 'start'
                });
                $(".save_and_add_time_entry").show();
            }else{
                $("#t").timer({
                    action: 'reset'
                });
                $("#it").timer({
                    action: 'reset'
                });
                $(".timewidget").hide();
                $(".save_and_add_time_entry").hide();
            }
        });
        $("#call_for").select2({
            allowClear: true,
            placeholder: "Seach...",
            theme: "classic",
            dropdownParent: $("#addCall"),
        });

        $("#caller_name_out").select2({
            placeholder: "Select or a enter name...",
            theme: "classic",
            dropdownParent: $("#addCall"),
            tags:true
        });
        $("#case_out").select2({
            allowClear: true,
            placeholder: "Select a case to associate this call with...",
            theme: "classic",
            dropdownParent: $("#addCall"),
        }).on("change", function (e) {
            var uType=$("#case_out option:selected").attr('uType');
            if(uType=="case"){
                $(".timewidget").show();
                $("#t").timer({
                    action: 'start'
                });
                $("#it").timer({
                    action: 'start'
                });
                $(".save_and_add_time_entry").show();
            }else{
                $("#t").timer({
                    action: 'reset'
                });
                $("#it").timer({
                    action: 'reset'
                });
                $(".timewidget").hide();
                $(".save_and_add_time_entry").hide();
            }
        });
        

        $("#call_for_out").select2({
            allowClear: true,
            placeholder: "Seach...",
            theme: "classic",
            dropdownParent: $("#addCall"),
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
        $("#AddIncomingCall").validate({
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
        $("#AddOutgoingCall").validate({
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

    $('#AddIncomingCall').submit(function (e) {
        $(".timer_count").val($("#t").html());

        beforeLoader();
        e.preventDefault();

        if (!$('#AddIncomingCall').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#AddIncomingCall").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/saveCall", // json datasource
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
                   
                    // $("#AddIncomingCall").scrollTop(0);
                    $('#AddIncomingCall').animate({
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

    $('#AddOutgoingCall').submit(function (e) {
        $(".timer_count").val($("#it").html());

        beforeLoader();
        e.preventDefault();

        if (!$('#AddOutgoingCall').valid()) {
            afterLoader();
            return false;
        }
        var dataString = '';
        dataString = $("#AddOutgoingCall").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/saveCall", // json datasource
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
                   
                    // $("#AddIncomingCall").scrollTop(0);
                    $('#AddOutgoingCall').animate({
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
    $("#first_name").focus();
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
    $("#InonResolveText").hide();
    $("#IresolveText").show();
    $("#OnonResolveText").hide();
    $("#OresolveText").show();
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
                $(".phone_number").val(res.mobile_number);
                afterLoader();
            }
        })
    }

    function getButton(type){
        $(".st").val(type);
    }
</script>
