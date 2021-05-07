<div id="showError" style="display:none"></div>

<form class="createCase" id="createCase" name="createCase" method="POST">
    @csrf
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
                <select onchange="selectUser();" class="form-control user_type" id="user_type" name="user_type"
                    data-placeholder="Search for an existing contact or company">
                    <option value="">Search for an existing contact or company</option>
                    <optgroup label="Client">
                        <?php
                    foreach($CaseMasterClient as $Clientkey=>$Clientval){
                        ?>
                        <option value="{{$Clientval->id}}">{{substr($Clientval->first_name,0,30)}} {{substr($Clientval->last_name,0,30)}}</option>
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
            <div class="no-contact-warning mr-2" style="display:none;" id="beforetext">You must have a contact to bill. Are you sure you want to continue?</div>
            <button type="button" id="beforebutton" onclick="callOnClick();" class="btn btn-primary ladda-button example-button m-1">Continue to case details</button>

            <button class="btn btn-primary ladda-button example-button m-1" id="submit"  style="display:none;" type="submit"
            data-style="expand-left"><span class="ladda-label">Continue without picking a contact</span><span
                class="ladda-spinner"></span><span class="ladda-spinner"></span></button>
            <button class="btn btn-primary ladda-button example-button m-1" id="submit_with_user"  style="display:none;" type="submit"
                data-style="expand-left"><span class="ladda-label">Continue to case details</span><span
                    class="ladda-spinner"></span><span class="ladda-spinner"></span></button>
        </div>

    </div>
</form>
<style>
    .empty-state {
        background-image: url({{BASE_URL}}public/assets/images/arrow_top_left-327431b398.png);
        background-position: 69px 15px;
        background-repeat: no-repeat;
        background-size: 90px 55px;
        padding-top: 50px;
        width: 100%;
    }

</style>
<script type="text/javascript">
<?php 
if($user_id!=''){?>
     selectUserAutoLoad({{$user_id}});
    <?php } ?>
    $(document).ready(function () {

        
        $("#user_type").select2({
            placeholder: "Select a country",
            theme: "classic",
            allowClear: true,
            dropdownParent: $("#AddCaseModel"),
        });

        $("#innerLoader").css('display', 'none');
        $("#createCase").validate({
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

        $('#createCase').submit(function (e) {
            e.preventDefault();
            $("#submit").attr("disabled", true);
            $("#innerLoader").css('display', 'block');
            if (!$('#createCase').valid()) {
                $("#innerLoader").css('display', 'none');
                $('#submit').removeAttr("disabled");
                return false;
            }
            var dataString = $("form").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/case/saveStep1", // json datasource
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
                        loadStep2(res);
                    }
                }
            });
        });

    });
    function callOnClick(){
        $("#beforebutton").hide();
        $("#beforetext").show();
        $("#submit").show();
        $("#submit_with_user").hide();
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
            data: {
                "selectdValue": selectdValue
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
