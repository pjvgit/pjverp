<div id="showError" style="display:none"></div>
<form class="EditLead" id="EditLead" name="EditLead" method="POST">
    <input class="form-control" value="{{($UserMaster->id)??''}}" id="id" maxlength="250" name="id" type="hidden">    
    <input class="form-control" value="{{($LeadAdditionalInfo->id)??''}}" id="id" maxlength="250" name="user_id" type="hidden">

    <span id="response"></span>
    @csrf
    <div class="col-md-12">
       
        <div class="form-group row addmore">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Case Name</label>
            <div class="col-md-9 form-group mb-3">
                <input class="form-control" id="potential_case_title" mexlength="255" value="{{($LeadAdditionalInfo->potential_case_title) ??''}}" name="potential_case_title" type="text">

            </div>
        </div>
        <div class="form-group row addmore">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Date Added
            </label>
            <div class="col-md-9 form-group mb-3">
                <input class="form-control datepicker" id="dateadded" value="{{($LeadAdditionalInfo->date_added) ? date('m/d/Y',strtotime($LeadAdditionalInfo->date_added)) : convertUTCToUserTimeZone('dateOnly')}}" name="date_added" type="text"
                    placeholder="mm/dd/yyyy">

            </div>
        </div>

        <div class="form-group row addmore">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Status</label>
            <div class="col-md-4 form-group mb-3">
                <select class="form-control contact_group" id="lead_status" name="lead_status">
                    <?php   foreach($LeadStatus as $kcs=>$vcs){?>
                    <option <?php if($vcs->id==$LeadAdditionalInfo->lead_status){ echo "selected=selected"; }?> value="{{$vcs->id}}">{{$vcs->title}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group row addmore">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Practice Area
            </label>
            <div class="col-md-4 form-group mb-3">
                <select class="form-control contact_group" id="practice_area" name="practice_area">
                    <?php   foreach($CasePracticeArea as $kcs=>$vcs){?>
                    <option  <?php if($vcs->id==$LeadAdditionalInfo->practice_area){ echo "selected=selected"; }?> value="{{$vcs->id}}">{{$vcs->title}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group row" id="billing_rate_text">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Potential Value of Case
            </label>
            <div class="input-group mb-4 col-sm-5">
                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                <input class="form-control case_rate number decimal" id="potential_case_value"  name="potential_case_value" value="{{$LeadAdditionalInfo->potential_case_value}}" maxlength="20" type="text"
                    aria-label="Amount (to the nearest dollar)">
            </div>
        </div>
        <div class="form-group row addmore">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Assign</label>
            <div class="col-md-9 form-group mb-3">
                <select class="form-control contact_group" id="assigned_to" name="assigned_to"
                    data-placeholder="Select Contact Group">
                    <option value="{{($LeadAdditionalInfo->cell_phone)??''}}">Select...</option>
                    <?php   foreach($firmStaff as $kcs=>$vcs){?>
                        <option <?php if($vcs->id==$LeadAdditionalInfo->assigned_to){ echo "selected=selected"; }?> value="{{$vcs->id}}">{{$vcs->first_name}} {{$vcs->last_name}} ({{$vcs->user_title}})</option>
                        <?php } ?>
                    
                </select>
            </div>
        </div>
        <div class="form-group row addmore">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Office</label>
            <div class="col-md-4 form-group mb-3">
                <select class="form-control contact_group" id="office" name="office">
                    <?php  foreach($firmAddress as $k=>$v){?>
                    <option <?php if($v->id == $LeadAdditionalInfo->office ){ echo "selected=selected"; }?> value="{{$v->id}}">{{$v->office_name}}</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group row addmore">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Potential Case Description </label>
            <div class="col-md-9 form-group mb-3">
                <textarea name="potential_case_description" class="form-control" rows="3" maxlength="512"
                    placeholder="Add notes about the potential new case...">{{$LeadAdditionalInfo->potential_case_description}}</textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Conflict Check</label>
            <div class="col-md-9 form-group mb-3">
                <label class="switch pr-5 switch-success mr-3"><span>Completed</span>
                    <input type="checkbox" <?php if($LeadAdditionalInfo->conflict_check=="yes"){ echo "checked=checked"; }?>  name="conflict_check" id="conflict_check"><span class="slider"></span>
                </label>

            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Conflict Check Notes</label>
            <div class="col-md-9 form-group mb-3">
                <textarea name="conflict_check_description" class="form-control" rows="3">{{$LeadAdditionalInfo->conflict_check_description}}</textarea>
            </div>
        </div>

        </span>
        <hr>
        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit">Save & Close</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        // $('#dateadded').datepicker({
        //     onSelect: function (dateText, inst) {},
        //     showOn: 'focus',
        //     showButtonPanel: true,
        //     closeText: 'Clear', // Text to show for "close" button
        //     onClose: function (selectedDate) {
        //         var event = arguments.callee.caller.caller.arguments[0];
        //         // If "Clear" gets clicked, then really clear it
        //         if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
        //             $(this).val('');
        //         }
        //     }
        // });
        // $('#dob').datepicker({
        //     maxDate: -0,
        //     onSelect: function (dateText, inst) {},
        //     showOn: 'focus',
        //     showButtonPanel: true,
        //     closeText: 'Clear', // Text to show for "close" button
        //     onClose: function (selectedDate) {
        //         var event = arguments.callee.caller.caller.arguments[0];
        //         // If "Clear" gets clicked, then really clear it
        //         if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
        //             $(this).val('');
        //         }
        //     }
        // });
        
        $('#dateadded').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
        $('#dob').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
             endDate: '+0d',
             'todayHighlight': true
        });

        $(".innerLoader").css('display', 'none');
        $(".innerLoader").hide();
        $("#show_contact_group_text").hide();
        $("#show_contact_group_dropdown").show();

        $("#EditLead").validate({
            rules: {
                date_added: {
                    required: true,
                },
                potential_case_title: {
                    required: true,
                    maxlength:2000
                },
            },
            messages: {
                date_added: {
                    required: "Date Added is a required field",
                }, 
                potential_case_title: {
                    required: " Case Name can't be blank",
                    maxlength:"Name is too long (maximum is 2000 characters)"
                },
            },

            errorPlacement: function (error, element) {
                if (element.is('#user_type')) {
                    error.appendTo('#UserTypeError');
                } else if (element.is('#default_rate')) {
                    error.appendTo('#TypeError');
                } else {
                    element.after(error);
                }
            }
        });
        $("#show_company_text").hide();
    });
   
    $('#EditLead').submit(function (e) {
        $(".submit").attr("disabled", true);
        $(".innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#EditLead').valid()) {
            $(".innerLoader").css('display', 'none');
            $('.submit').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#EditLead").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/savePotentailCase", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&update=yes';
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
                    // $("#editLead").scrollTop(0);
                    $('#editLead').animate({ scrollTop: 0 }, 'slow');

                    return false;
                } else {
                   window.location.reload();
                }
            }
        });

     
    });

    $('#collapsed').click(function() { 
        $("#collapsed").find('i').toggleClass('fa-sort-up align-bottom').toggleClass('fa-sort-down align-text-top'); 
    }); 


    function ontogleClass() {
        $("#addmorearea").toggle();
    }

    function openNewContactGroup() {
        $("#show_contact_group_text").show();
        $("#show_contact_group_dropdown").hide();
        return false;
    }

    function openOldContactGroup() {
        $("#show_contact_group_text").hide();
        $("#show_contact_group_dropdown").show()
        return false;
    }

    function openNewCompany() {
        $("#show_company_text").show();
        $("#show_company_dropdown").hide();
        return false;
    }

    function openOldCompany() {
        $("#show_company_text").hide();
        $("#show_company_dropdown").show();
        return false;
    } 
    // $('.decimal').keyup(function(){
    //     var val = $(this).val();
    //     if(isNaN(val)){
    //         val = val.replace(/[^0-9\.]/g,'');
    //         if(val.split('.').length>2) 
    //             val =val.replace(/\.+$/,"");
    //     }
    //     $(this).val(val); 
    // });

    $('input.decimal').keyup(function(event) {
            // skip for arrow keys
            if(event.which >= 37 && event.which <= 40) return;
            // format number
            $(this).val(function(index, value) {
                if(value.split('.').length>2) 
                    return value =value.replace(/\.+$/,"");
                return value.replace(/[^0-9\.]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            });
        });

$("#first_name").focus();
</script>
