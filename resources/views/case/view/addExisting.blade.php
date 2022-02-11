<form class="linkContactToCase" id="linkContactToCase" name="linkContactToCase" method="POST">
   
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
            <label for="inputEmail3" class="col-sm-3 col-form-label">Contact Link</label>
            <div class="col-7 form-group mb-3">
                <select onchange="selectUser();" class="form-control user_type" id="user_type" name="user_type"
                    data-placeholder="Search for an existing contact or company">
                    <option value="">Search for an existing contact or company</option>
                    <optgroup label="Client">
                        <?php foreach(userClientList() as $Clientkey=>$Clientval){ ?>
                        <option value="{{$Clientval->id}}">{{substr($Clientval->name,0,30)}}</option>
                        <?php } ?>
                    </optgroup>
                    <optgroup label="Company">
                        <?php foreach(userCompanyList() as $Companykey=>$Companyval){ ?>
                        <option value="{{$Companyval->id}}">{{substr($Companyval->name,0,50)}}</option>
                        <?php } ?>
                    </optgroup>
                </select>
                
                
            </div>
            <label for="inputEmail3" class="col-sm-1 col-form-label"></label>
        </div>
        <div id="contact_verification" style="border-top: 1px solid #cccccc;">
        </div>
        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
            <label for="inputEmail3" class="col-sm-9 col-form-label"><span id="UserTypeError" class="error">This contact is already linked to this case.</span></label>
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
            data-style="expand-left"><span class="ladda-label">Add Contact Link</span><span
                class="ladda-spinner"></span><span class="ladda-spinner"></span></button>
        </div>

    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#innerLoader").css('display', 'none');
        $("#UserTypeError").css('display', 'none');
        
        $("#user_type").select2({
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#addExisting"),
        });

       ;
        $("#submit_with_user").attr("disabled", true);
        $("#linkContactToCase").validate({
            rules: {
                user_type: {
                    required: false
                }
            },
            messages: {
                user_type: {
                    required: "You must have a contact to bill"
                }
            }
        });

        $('#linkContactToCase').submit(function (e) {
            e.preventDefault();
            $("#submit").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            if (!$('#linkContactToCase').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString = $("#linkContactToCase").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/saveLinkSelection", // json datasource
                data: dataString,
                success: function (res) {
                    $("#innerLoader").css('display', 'block');
                    console.log(res.errors);
                    if (res.errors != '') {
                        $('#showError').html('');
                        var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</button><ul><li>'+res.errors+'</li></ul></div>';
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
    
    function selectUser() {
        var unselected_value = $('#user_type').val();
        if(unselected_value==""){
            $("#submit_with_user").attr("disabled", true);
        }else{
            $("#innerLoader").css('display', 'block');
            var selectdValue = $("#user_type option:selected").val() // or
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/checkBeforeLinking",
                data: {
                    "selectdValue": selectdValue,
                    "case_id" : {{$case_id}}
                },
                success: function (res) {
                    $("#contact_verification").html('');
                    $("#contact_verification").html(res);
                    if(res.count<0){
                        $("#submit_with_user").attr("disabled", true);
                    }else{
                        $('#submit_with_user').removeAttr("disabled");
                    }
                    $("#innerLoader").css('display', 'none');
                }
            })
        }
       
    }
  
</script>
