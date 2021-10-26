
<form class="saveEditCase" id="saveEditCase" name="saveEditCase" method="POST">
    <div id="showError2" style="display:none"></div>
    @csrf
  
    <input class="form-control" id="case_id" value="{{$CaseMaster->id}}" name="case_id" type="hidden">
    <div class=" col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Case name</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="case_name" value="{{ $CaseMaster->case_title ?? old('case_title') }}" name="case_name" type="text"
                    placeholder="E.g. John Smith - Divorce">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Case number</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control" id="case_number" value="{{ $CaseMaster->case_number ?? old('case_number') }}"" name="case_number" type="text"
                    placeholder="Enter case number">
                <small>A unique identifier for this case.</small>
            </div>
        </div>
        <div class="form-group row" id="area_dropdown111">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Practice area</label>
            <div class="col-md-6 form-group mb-3">
                <select id="practice_area" name="practice_area" class="form-control custom-select col">
                    <option value="-1"></option>
                    <?php 
                        foreach($practiceAreaList as $k=>$v){?>
                    <option <?php if($CaseMaster->practice_area==$v->id){ echo "selected=selected"; }?>  value="{{$v->id}}">{{$v->title}}</option>
                    <?php } ?>

                </select>
            </div>
            <label for="inputEmail3" class="col-sm-4 col-form-label"> <a onclick="showText();" href="javascript:;">Add
                    new practice area</a></label>
        </div>
        <div class="form-group row" id="area_text111">
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
                    <option value=""></option>
                    <?php 
                    foreach($caseStageList as $kcs=>$vcs){?>
                    <option <?php if($CaseMaster->case_status==$vcs->id){ echo "selected=selected"; }?> value="{{$vcs->id}}">{{$vcs->title}}</option>
                    <?php } ?>
                </select>
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Date opened</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control datepicker dateopen" id="case_open_date" value="{{ ($CaseMaster->case_open_date) ? date('m/d/Y',strtotime($CaseMaster->case_open_date)) : old('case_open_date') }}" name="case_open_date" type="text"
                placeholder="mm/dd/yyyy">

            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Office
            </label>
            <div class="col-md-10 form-group mb-3">
                <select id="case_office" name="case_office" class="form-control custom-select col">
                    <?php  foreach($firmAddress as $k=>$v){?>
                        <option value="{{ $v->id }}" <?php if($CaseMaster->case_office==$v->id){ echo "selected=selected"; }?> >{{ $v->office_name }}</option>
                    <?php } ?>
                    
                </select>
            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Description</label>
            <div class="col-md-10 form-group mb-3">
                <textarea name="case_description" class="form-control" rows="5">{{ $CaseMaster->case_description ?? old('case_description') }}</textarea>
            </div>
        </div>
        @if(IsCaseSolEnabled() == 'yes')
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Statute of Limitations</label>
            <div class="col-md-10 form-group mb-3">
                <input class="form-control datepicker soldate" id="case_statute_date" value="{{  ($CaseMaster->case_statute_date)?date('m/d/Y',strtotime($CaseMaster->case_statute_date)): old('case_statute') }}" name="case_statute" type="text"
                placeholder="mm/dd/yyyy">

            </div>
        </div>
       
        <div class="form-group row" id="addMoreReminder">
            <label for="sol_reminders" class="col-sm-2 col-form-label">SOL Reminders</label>
            <div class="col">
                <?php 
                    if($CaseMaster->case_statute_date!=NULL){
                        foreach($CaseSolReminder as $key=>$val){
            ?>
            <div class="row form-group fieldGroup" >
                <div class="col-md-3 form-group mb-3">
                    <select id="reminder_type" name="reminder_type[]" class="form-control custom-select  ">
                    @foreach(getEventReminderTpe() as $k =>$v)
                        <option value="{{$k}}" <?php if($val->reminder_type==$k){ echo "selected=selected"; } ?>>{{$v}}</option>
                    @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-3">
                    <input class="form-control" id="reminder_days" value="{{$val->reminer_number}}" name="reminder_days[]" type="number"> 
                </div> <span class="pt-2">Days</span>
                <div class="col-md-3 form-group mb-3">   
                    <button class="btn remove" type="button"><i class="fa fa-trash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <?php 
                   }  }else{?>
                   
                    @forelse (firmSolReminders() as $key => $item)
                        <div class="row form-group fieldGroup">
                            <div class="col-md-3 form-group mb-3">
                                <select id="reminder_type" name="reminder_type[]" class="form-control custom-select  ">
                                    @foreach(getEventReminderTpe() as $k =>$v)
                                        <option value="{{$k}}" <?php if(@$item->reminder_type == $k){ echo "selected=selected"; } ?>>{{$v}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-3">
                                <input class="form-control" id="reminder_days" value="{{ @$item->reminer_days }}" name="reminder_days[]" type="number" min="0"> 
                            </div> <span class="pt-2">Days</span>
                            <div class="col-md-3 form-group mb-3">   
                                <button class="btn remove" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    @empty
                    @endforelse
                 <?php  } ?>
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
            <div class="col-md-3 form-group mb-3">
                <input class="form-control" id="reminder_days" value="1" name="reminder_days[]" type="number" > 
            </div> <span class="pt-2">Days</span>
            <div class="col-md-3 form-group mb-3">   
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
                    <input type="checkbox" <?php if($CaseMaster->conflict_check=="1"){ echo "checked=checked"; }?> name="conflict_check" id="conflict_check"><span class="slider"></span>
                </label>

            </div>

        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Conflict Check Notes</label>
            <div class="col-md-10 form-group mb-3">
                <textarea name="conflict_check_description" placeholder="Add notes about the conflict check..."  class="form-control" rows="5">{{ $CaseMaster->conflict_check_description ?? old('conflict_check_description') }}</textarea>
            </div>
        </div>
       
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1 submit" data-style="expand-right">
                <span class="ladda-label">Save & Close</span>
            </button>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-3 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader1"></div>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
        </div>
    </div>
</form>
<script type="text/javascript">
    
    $(document).ready(function () {
        afterLoader();
        //$(".datepicker" ).datepicker();
        // $('.datepicker').datepicker({
            
        //     onSelect: function(dateText, inst) { 
        //        $("#addMoreReminder").show();
        //     },  
        //     showOn: 'focus',
        //     showButtonPanel: true,
        //     closeText: 'Clear', // Text to show for "close" button
        //     onClose: function () {
        //         var event = arguments.callee.caller.caller.arguments[0];
        //         // If "Clear" gets clicked, then really clear it
        //         if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
        //             $(this).val('');
        //             $("#addMoreReminder").hide();
        //         }
        //     }
        // });
        $('.dateopen').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true

        });

        $('.soldate').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        }).on('changeDate', function(e) {
            if($(this).val()==''){
                $("#addMoreReminder").hide();
            }else{
                $("#addMoreReminder").show();
            }
           
        }).on('clearDate', function(e) {
            $("#addMoreReminder").hide();
        });
        
        <?php if($CaseMaster->case_statute_date==NULL){?>  $("#addMoreReminder").hide();  <?php } ?>
        $("#innerLoader1").css('display', 'none');
        $("#area_text").css('display', 'none');

        $("#saveEditCase").validate({
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
        $('#saveEditCase').on('click', '.remove', function () {
            var $row = $(this).parents('.fieldGroup').remove();
        });

    });

    $('#saveEditCase').submit(function (e) {
        beforeLoader();
        $("#innerLoader1").css('display', 'block');
        e.preventDefault();

        if (!$('#saveEditCase').valid()) {
            afterLoader();
            $("#innerLoader1").css('display', 'none');
            return false;
        }

        var dataString = $("#saveEditCase").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/saveEditCase", // json datasource
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
                    afterLoader();
                    return false;
                } else {
                    window.location.reload();
                    // $('#EditCaseModel').modal('hide');
                }
            }
        });
    });

    
    function showText() {

$("#area_text111").show();
$("#area_dropdown111").hide();
return false;
}

function showDropdown() {

$("#area_text111").hide();
$("#area_dropdown111").show()
return false;
}

showDropdown();
</script>
