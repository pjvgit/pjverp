<div class="d-flex flex-row w-100 mt-3">
    <div>
        <?php 
        if(in_array($type,["all","b"])){?>
        <div class="mr-auto">Billable Total:
            <strong class="test-billable-total">${{number_format($FinalArray['monthTotal'],2)}}</strong>
        </div>
        <div class="text-muted">* Billable includes flat fee time entries</div>
        <?php } ?>
    </div>
    <?php 
        if(in_array($type,["all","b"])){?>
    <div class="ml-auto text-right">
        <div id="Mtotal">Monthly Total: <strong>{{number_format($monthlyHours,1)}} hrs</strong></div>
        <div>
            <div class="d-flex">
                <div class="undefined d-flex flex-row">
                    <div class="mr-2"><span class="badge badge-success null">NEW</span></div>
                    <div></div>
                </div>
                <span class="user-billing-target mr-1">Billing Target:</span>
                <?php
                $fArray=array("daily"=>"day","monthly"=>"month","weekly"=>"week");
                if(Auth::User()->set_goal!="0.00"){
                    ?><strong>{{number_format(Auth::User()->set_goal)}}hrs/ {{$fArray[Auth::User()->goal_frequency]}}</strong><?php 
                }else{
                    ?> <strong>None</strong><?php 
                }
                ?>
            </div>
            <?php
                if(Auth::User()->set_goal!="0.00"){?>
            <button class="p-0 btn btn-link" data-toggle="modal" data-target="#editGoalActivity" data-placement="bottom"
                href="javascript:;" type="button">
                Edit goal
            </button>
            <?php 
                }else{
?>
            <button class="p-0 btn btn-link" data-toggle="modal" data-target="#addActivity" data-placement="bottom"
                href="javascript:;" type="button">
                Add goal
            </button><?php
                }?>

        </div>
    </div>
    <?php } ?>
</div>
<?php 
if(Auth::User()->set_goal=="0.00"){?>
<div id="addActivity" class="modal fixed-left fade" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Billable Target
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">

                <form class="addNewActivity" id="addNewActivity" name="addNewActivity" method="POST">
                    <span id="response"></span>
                    @csrf
                    <div id="showError" class="showError" style="display:none"></div>
                    <div class="col-md-12">

                        <p>Enter a billing target goal. This goal will appear in the Timesheet Calendar (Billing
                            tab).</p>
                        <div class="w-100 mb-4 ">
                            <div class="row">
                                <div class="col-4 pr-0"><label class="mb-0" for="hours-field">Hours</label></div>
                                <div class="col-2 px-1"></div>
                                <div class="col-4 pl-0"><label class="mb-0" for="duration-field">Time Period</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4 pr-0">
                                    <div class="pl-0 ">
                                        <div class="">
                                            <div>
                                                <div class="">
                                                    <div class="input-group"><input id="hours-field" name="hours_field"
                                                            class="form-control text-right" type="text" placeholder=""
                                                            data-testid="hours-field" value=""></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2 px-1 py-2 d-flex justify-content-center w-50"><span
                                        class="mb-0">per</span></div>
                                <div class="col-4 pl-0">
                                    <div>
                                        <div class=""><select id="duration-field" name="duration_field"
                                                class="form-control custom-select  ">
                                                <option value="daily">Day</option>
                                                <option value="weekly">Week</option>
                                                <option value="monthly">Month</option>
                                            </select></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        </span>
                        <div class="modal-footer">
                            <div class="col-md-2 form-group">
                                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                                    style="display: none;">
                                </div>
                            </div>
                            <a href="#">
                                <button class="btn btn-secondary  btn-rounded mr-1 " type="button"
                                    data-dismiss="modal">Cancel</button>
                            </a>
                            <button class="btn btn-primary  btn-rounded submit " id="submitButton" value="savenote"
                                type="submit">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<div id="editGoalActivity" class="modal fixed-left fade" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depostifundtitle">Billable Target
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">

                <form class="addNewActivity" id="addNewActivity" name="addNewActivity" method="POST">
                    <span id="response"></span>
                    @csrf
                    <div id="showError" class="showError" style="display:none"></div>
                    <div class="col-md-12">

                        <p>Enter a billing target goal. This goal will appear in the Timesheet Calendar (Billing
                            tab).</p>
                        <div class="w-100 mb-4 ">
                            <div class="row">
                                <div class="col-4 pr-0"><label class="mb-0" for="hours-field">Hours</label></div>
                                <div class="col-2 px-1"></div>
                                <div class="col-4 pl-0"><label class="mb-0" for="duration-field">Time Period</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4 pr-0">
                                    <div class="pl-0 ">
                                        <div class="">
                                            <div>
                                                <div class="">
                                                    <div class="input-group"><input id="hours-field" name="hours_field"
                                                            class="form-control text-right" type="text" placeholder=""
                                                            data-testid="hours-field" value="{{Auth::User()->set_goal}}"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2 px-1 py-2 d-flex justify-content-center w-50"><span
                                        class="mb-0">per</span></div>
                                <div class="col-4 pl-0">
                                    <div>
                                        <div class=""><select id="duration-field" name="duration_field"
                                                class="form-control custom-select  ">
                                                <option <?php if(Auth::User()->goal_frequency=="daily") { echo "Selected=selected"; } ?> value="daily">Day</option>
                                                <option <?php if(Auth::User()->goal_frequency=="weekly") { echo "Selected=selected"; } ?> value="weekly">Week</option>
                                                <option <?php if(Auth::User()->goal_frequency=="monthly") { echo "Selected=selected"; } ?> value="monthly">Month</option>
                                            </select></div>
                                    </div>
                                </div>
                                <div class="col-2 pl-0 mt-2" ><i class="fas fa-trash align-middle p-2" onclick="deleteGoalEntry();"></i></div>
                            </div>
                        </div>

                        </span>
                        <div class="modal-footer">
                            <div class="col-md-2 form-group">
                                <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader"
                                    style="display: none;">
                                </div>
                            </div>
                            <a href="#">
                                <button class="btn btn-secondary  btn-rounded mr-1 " type="button"
                                    data-dismiss="modal">Cancel</button>
                            </a>
                            <button class="btn btn-primary  btn-rounded submit " id="submitButton" value="savenote"
                                type="submit">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.dropdown-toggle').dropdown();
        $('[data-toggle="popover"]').popover();

        $("body").on("click", "#btn", function () {

            $("#myModal").modal("show");

            //appending modal background inside the blue div
            $('.modal-backdrop').appendTo('.blue');

            //remove the padding right and modal-open class from the body tag which bootstrap adds when a modal is shown
            $('body').removeClass("modal-open")
            $('body').css("padding-right", "");
        });
    });

</script>

<script type="text/javascript">
    $(document).ready(function () {
        $('.dropdown-toggle').dropdown();
        $('[data-toggle="popover"]').popover();

        $("#addNewActivity").validate({
            rules: {
                hours_field: {
                    required: true
                }
            },
            messages: {
                hours_field: {
                    required: "Can't be empty",
                }
            }
        });

        $('#addNewActivity').submit(function (e) {
            beforeLoader();
            e.preventDefault();
            if (!$('#addNewActivity').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#addNewActivity").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/dashboard/saveDailyGoal", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&saveGoal=yes';
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
                        afterLoader();
                        $('#addNewActivity').animate({
                            scrollTop: 0
                        }, 'slow');

                        return false;
                    } else {
                        afterLoader()
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });
    });
    function deleteGoalEntry() {
        beforeLoader();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/dashboard/deleteGoalEntry", 
                data: {"delete": "yes"},
                success: function (res) {
                    window.location.reload();
                }
            })
        })
    }
</script>
<style>
    .modal .modal-dialog-aside {
        margin-right: 350px;
        margin-top: 19%;
        margin: 0;
        transform: translate(0);
        transition: transform .2s;
    }

    .modal .modal-dialog-aside .modal-content {
        height: inherit;
        border: 0;
        border-radius: 0;
    }

    .modal .modal-dialog-aside .modal-content .modal-body {
        overflow-y: auto
    }

    .modal.fixed-left .modal-dialog-aside {
        margin-left: auto;
        margin-top: 19%;
        transform: translateX(100%);
    }

    .modal.fixed-right .modal-dialog-aside {
        margin-right: 450px;
        margin-top: 19%;
        transform: translateX(-100%);
    }

    .modal.show .modal-dialog-aside {
        transform: translateX(0);
    }

</style>
