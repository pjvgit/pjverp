<span id="showError" class="showError" style="display: none;"></span>
<form class="saveBulkUser" id="saveBulkUser" name="saveBulkUser" method="POST">
    @csrf
    <div class="font-weight-bold mb-2 row ">
        <div class="col-5">Name</div>
        <div class="col-3">Email</div>
        <div class="col-2">User Type</div>
        <div class="col-2">Grant Access Now?</div>
    </div>
    <div class="mb-2 row">
        <div class="Row0 col-5">
            <div class="">
                <div class="row no-gutters">
                    <div class="col-5 pr-2">
                        <div>
                            <div class="">
                                <div class="input-group">
                                    <input id="first_name1" name="first_name[1]" autocomplete="off"
                                        class="form-control" type="text" placeholder="First Name"
                                        data-testid="first_name" value="">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-2">
                        <div>
                            <div class="">
                                <div class="input-group">
                                    <input id="middle_name1" autocomplete="off" name="middle_name[1]"
                                        class="form-control " type="text" placeholder="M" data-testid="middle_initial"
                                        value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-5 pl-2">
                        <div>
                            <div class="">
                                <div class="input-group">
                                    <input id="last_name1" autocomplete="off" name="last_name[1]" class="form-control "
                                        type="text" placeholder="Last Name" data-testid="last_name" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="email0 col-3">
            <div>
                <div class="">
                    <div class="input-group">
                        <input id="email1" name="email[1]" autocomplete="off" class="form-control " type="text"
                            placeholder="email" autocomplete="off" data-testid="email" value="">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-2">
            <div>
                <div class="">
                    <select id="accessLevel1" name="accessLevel[1]" class="form-control custom-select  ">
                        <option value="1">Attorney</option>
                        <option value="2">Paralegal</option>
                        <option value="3">Staff</option>
                    </select></div>
            </div>
        </div>
        <div class="pt-1 activateUser0 col-2">
            <div class="">
                <div class="d-inline-flex align-items-center">
                    <label class="switch pr-5 switch-success mr-3">
                        <input id="portalAccess1" type="checkbox" class="portalAccess" name="portal_access[]"><span
                            class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-2 row maturity_div copy-new hide maturity_div" id="optionTemplate2">
        <div class="Row0 col-5">
            <div class="">
                <div class="row no-gutters">
                    <div class="col-5 pr-2">
                        <div>
                            <div class="">
                                <div class="input-group">
                                    <input id="first_name" name="first_name[]" autocomplete="off" class="form-control "
                                        type="text" placeholder="First Name" data-testid="first_name" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-2">
                        <div>
                            <div class="">
                                <div class="input-group">
                                    <input id="middle_name" autocomplete="off" name="middle_name[]"
                                        class="form-control " type="text" placeholder="M" data-testid="middle_initial"
                                        value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-5 pl-2">
                        <div>
                            <div class="">
                                <div class="input-group">
                                    <input id="last_name" autocomplete="off" name="last_name[]" class="form-control "
                                        type="text" placeholder="Last Name" data-testid="last_name" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="email0 col-3">
            <div>
                <div class="">
                    <div class="input-group">
                        <input id="email" name="email[]" autocomplete="off" class="form-control " type="text"
                            placeholder="email" autocomplete="off" data-testid="email" value="">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-2">
            <div>
                <div class="">
                    <select id="accessLevel" name="accessLevel[]" class="form-control custom-select  ">
                        <option value="1">Attorney</option>
                        <option value="2">Paralegal</option>
                        <option value="3">Staff</option>
                    </select></div>
            </div>
        </div>
        <div class="pt-1 activateUser0 col-2">
            <div class="">
                <div class="d-inline-flex align-items-center">
                    <label class="switch pr-5 switch-success mr-3">
                        <input id="portalAccess1" type="checkbox" class="portalAccess" name="portal_access[]"><span
                            class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="after-add-more-new"></div>
    <div class="row ">
        <button type="button" id="add-five-rows-id" class="btn btn-link add-more-five">Add 5 more rows</button>
    </div>
    <div class="pt-2 pb-3"><b>Note: </b>Users without access will not receive a welcome email or any notifications via
        email, but they can still be added to items throughout MyCase. All users above will be added to your
        subscription. Adding new users may result in additional charges.<a href="#" target="_blank"
            rel="noopener noreferrer"> Please go here to learn more about new users and pricing.</a> Access can always
        be granted at a later date.</div>
    <div class="modal-footer pb-0">
        <button type="submit" name="save" value="s" id="submitbutton" class="btn btn-primary submitbutton">Create Users</button>
    </div>
    <input type="hidden" name="hideinputcount2" id="hideinputcount2" value="1" />
</form>
<style>
    .hide {
        display: none;
    }

</style>
<script type="text/javascript">
    $(document).ready(function () {
        $("#submitbutton").attr("disabled","disabled");
        $('#saveBulkUser').submit(function (e) {
                e.preventDefault();
                $('.showError').html('');
                $("#innerLoader").css('display', 'block');

                if (!$('#saveBulkUser').valid()) {
                    $("#innerLoader").css('display', 'none');
                    $('.submitbutton').removeAttr("disabled");
                    return false;
                }

                var dataString = '';
                dataString = $("#saveBulkUser").serialize();

                $.ajax({
                        type: "POST",
                        url: baseUrl + "/dashboard/saveBulkUserPopup", // json datasource

                        data: dataString,
                        success: function (res) {

                                $("#innerLoader").css('display', 'block');

                                if (res.errors != '') {
                                    $('#showError').html('');
                                    var errotHtml =
                                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';

                                    $.each(res.errors, function (key, value) {
                                            errotHtml += '<li>' + value + '</li>';
                                        }
                                    );
                                    errotHtml += '</ul></div>';
                                    $('#showError').append(errotHtml);
                                    $('#showError').show();
                                    $("#innerLoader").css('display', 'none');
                                    $('.submitbutton').removeAttr("disabled");

                                    $('#AddBulkUserModal').animate({
                                            scrollTop: 0
                                        }, 'slow');

                                    return false;
                                } else {

                                    if(res.totalUser>0){
                                        toastr.success('Successfully created '+res.totalUser+' user(s)!', "", {
                                                positionClass: "toast-top-full-width",
                                                containerId: "toast-top-full-width"
                                            }
                                        );
                                    }

                                    $("#innerLoader").css('display', 'none');
                                    $('#AddBulkUserModal').modal("hide");
                                }
                            }

                            ,
                        error: function (xhr, status, error) {
                            $('.showError').html('');
                            var errotHtml =
                                '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                            $('.showError').append(errotHtml);
                            $('.showError').show();

                            $('#AddBulkUserModal').animate({
                                scrollTop: 0
                            }, 'slow');
                            afterLoader();
                        }
                    }

                );
            }

        );

        $(".add-more-five").click(function () {
            for (var i = 1; i <= 5; i++) {
                var hideinputcount2 = $('#hideinputcount2').val();
                var $template = $('#optionTemplate2'),
                    $clone = $template.clone().removeClass('hide').removeAttr('id').attr('id', 'div' + (
                        parseInt(hideinputcount2) + parseInt(1)) + '').insertBefore($template);
                $('#hideinputcount2').val((parseInt(hideinputcount2) + parseInt(1)));
                $option2 = $clone.find('[name="first_name[]"]');
                $option2.attr('id', 'first_name' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('name', 'first_name[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                $option2 = $clone.find('[name="last_name[]"]');
                $option2.attr('id', 'last_name' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('name', 'last_name[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');


                $option2 = $clone.find('[name="middle_name[]"]');
                $option2.attr('id', 'middle_name' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('name', 'middle_name[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                $option2 = $clone.find('[name="email[]"]');
                $option2.attr('id', 'email' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('name', 'email[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                
                $option2 = $clone.find('[name="accessLevel[]"]');
                $option2.attr('id', 'accessLevel' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('name', 'accessLevel[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

               
                $option2 = $clone.find('[name="portal_access[]"]');
                $option2.attr('id', 'portal_access' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('name', 'portal_access[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                $("#saveBulkUser").validate();
            }
        });
        $(".form-control").on("keypress",function(){
            $('#submitbutton').removeAttr("disabled");
        });
    });



    function loadDefault() {
        var hideinputcount2 = $('#hideinputcount2').val();
        for (var i = hideinputcount2; i <= 4; i++) {
            var hideinputcount2 = $('#hideinputcount2').val();
            $('#hideinputcount2').val((parseInt(hideinputcount2) + parseInt(1)));
            var $template = $('#optionTemplate2'),
                $clone = $template.clone().removeClass('hide').removeAttr('id')
                .attr('id', 'div' + (parseInt(hideinputcount2) + parseInt(1)) + '')
                .insertBefore($template);

                $option2 = $clone.find('[name="last_name[]"]');
                $option2.attr('id', 'last_name' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('name', 'last_name[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');


                $option2 = $clone.find('[name="middle_name[]"]');
                $option2.attr('id', 'middle_name' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('name', 'middle_name[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                $option2 = $clone.find('[name="email[]"]');
                $option2.attr('id', 'email' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('name', 'email[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

                
                $option2 = $clone.find('[name="accessLevel[]"]');
                $option2.attr('id', 'accessLevel' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('name', 'accessLevel[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

               
                $option2 = $clone.find('[name="portal_access[]"]');
                $option2.attr('id', 'portal_access' + (parseInt(hideinputcount2) + parseInt(1)) + '');
                $option2.attr('name', 'portal_access[' + (parseInt(hideinputcount2) + parseInt(1)) + ']');

      
        }
    }
  

    loadDefault();
  
</script>
