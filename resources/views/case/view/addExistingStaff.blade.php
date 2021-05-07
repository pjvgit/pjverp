<form class="saveExistingStaff" id="saveExistingStaff" name="saveExistingStaff" method="POST">
    @csrf
    <?php 
    if($case_id!=''){?>
        <input class="form-control" value="{{$case_id}}" id="case_id" name="case_id" type="hidden">
    <?php } ?>
    <div id="showError" style="display:none"></div>
    <div class="col-md-12">
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Staff Link</label>
            <div class="col-7 form-group mb-3">
                <select onchange="selectStaff();" id="staff_user_id" name="staff_user_id" class="form-control custom-select col">
                    <option value="">Select staff member</option>
                    <?php 
                        foreach($caseStaff as $ksul=>$vsul){?>
                    <option value="{{$vsul->id}}"> {{$vsul->first_name}} {{$vsul->last_name}}  ({{$vsul->user_title}})</option>
                    <?php } ?>
                </select>
                
            </div>
            <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
            <label for="inputEmail3" class="col-sm-9 col-form-label"><span id="UserTypeError" class="error">Staff member is already linked to this case.</span></label>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Sharing</label>
            <div class="col-7 form-group mb-3">
                <label id="user_link_share_events_label">
                    <input type="checkbox" name="user_link_share_events" id="user_link_share_events">
                    Add all case events to this user's calendar
                  </label>
                  <label id="user_link_share_read_label">
                    <input type="checkbox" name="user_link_share_read" id="user_link_share_read">
                    Automatically mark all items as read
                  </label>
            </div>
            <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label">Case Rate</label>
            <div class="input-group mb-3 col-sm-9">
                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                <input class="form-control" name="default_rate" id="default_rate" value="" type="text" aria-label="Amount (to the nearest dollar)">
                <div class="input-group-append"><span class="input-group-text">/hr</span></div>
                <br><div  class="input-group col-sm-9" id="TypeError"></div>
    
            </div>
            
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
            <label for="inputEmail3" class="col-sm-9 col-form-label">It may take a few minutes for the newly added user to see linked events and documents.</label>
        </div>
       
     

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
            <label for="inputEmail3" class="col-sm-10 col-form-label"></label>
            <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
        </div>
     
        <div class="modal-footer">
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display:none;"></div>
            </div>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit_with_user"   type="submit"
            data-style="expand-left"><span class="ladda-label">Add Staff</span><span
                class="ladda-spinner"></span><span class="ladda-spinner"></span></button>
        </div>

    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#innerLoader").css('display', 'none');
        $("#UserTypeError").css('display', 'none');
        
        $("#submit_with_user").attr("disabled", true);
        $("#saveExistingStaff").validate({
            rules: {
                staff_user_id: {
                    required: false
                },
                default_rate:{
                    number:true
                }
                
            },
            messages: {
                staff_user_id: {
                    required: "Please select atleast one staff member"
                },
                default_rate:{
                    number:"Default rate is not a number"
                }
            } , errorPlacement: function (error, element) {
                if (element.is('#default_rate')) {
                    error.appendTo('#TypeError');
                } else {
                    element.after(error);
                }
            }
        });

        $('#saveExistingStaff').submit(function (e) {
            e.preventDefault();
            $("#submit").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            if (!$('#saveExistingStaff').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString = $("form").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveStaffLinkSelection", // json datasource
                data: dataString,
                success: function (res) {
                    $("#innerLoader").css('display', 'block');
                    console.log(res.errors);
                    if (res.errors != '') {
                        $('#showError').html('');
                        var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('#showError').append(errotHtml);
                        $('#showError').show();
                        $("#innerLoader").css('display', 'none');
                        $('#submit').removeAttr("disabled");
                        return false;
                    }else{
                        window.location.reload();
                    }
                }
            });
        });

    });
    
    function selectStaff() {
        
        $("#innerLoader").css('display', 'block');
        var selectdValue = $("#staff_user_id option:selected").val() // or
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/checkStaffBeforeLinking",
            data: {
                "selectdValue": selectdValue,
                "case_id" : {{$case_id}}
            },
            success: function (res) {
                if(res.count!=0){
                    $("#UserTypeError").css('display', 'block');
                    $("#submit_with_user").attr("disabled", true);

                }else{
                    $("#UserTypeError").css('display', 'none');
                    $('#submit_with_user').removeAttr("disabled");
                }
                $("#innerLoader").css('display', 'none');
            }
        })
    }

</script>
