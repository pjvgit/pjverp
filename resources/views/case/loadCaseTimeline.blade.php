
    <div class="col-md-12">
    <form class="saveHistoryForm" id="saveHistoryForm" name="saveHistoryForm" method="POST">
        @csrf
        <input type="hidden" name="case_id" value="{{$case_id}}">
        <div class="showError"></div>
        <span id="response"></span>
        <h5 class="mb-4">Case Status: Opened since {{date('m/d/Y',strtotime($CaseMaster->case_open_date))}}</h5>
        <div class="row ">
            <div class="col-3">
                <p class="ml-4">Case Stage</p>
            </div>
            <div class="col-3">
                <p>Start Date</p>
            </div>
            <div class="col-3">
                <p>End Date</p>
            </div>
            <div class="col-3">
                <p>Total Days</p>
            </div>
        </div>
        <?php 
        // print_r($AllCaseStageHistory);exit;
        // echo count($AllCaseStageHistory);exit;
        for($i=0;$i<count($AllCaseStageHistory);$i++){?>

        <div class="row fieldGroup">
            <div class="col-3 d-flex">
            <p class="mt-2 mr-1 counterIc" >{{$i+1}})</p>
            <select id="case_status" name="old_case_status[{{$AllCaseStageHistory[$i]['id']}}]"  name="case_status" 
                class="form-control custom-select col">
                <option value="0">No Stage</option>
                <?php foreach($caseStageList as $kcs=>$vcs){?>
                    <option <?php if($AllCaseStageHistory[$i]['stage_id']==$vcs->id){ echo "selected=selected"; }?> value="{{$vcs->id}}">{{$vcs->title}}</option>
                <?php } ?>
            </select>
            </div>
            <div class="col-3">
                <input type="text"  name="old_start_date[{{$AllCaseStageHistory[$i]['id']}}]" class="form-control dp"  value="{{date('m/d/Y',strtotime($AllCaseStageHistory[$i]['created_at']))}}"></p>
            </div>
            <div class="col-3">
                <input type="text"  class="form-control dp"  name="old_end_date[{{$AllCaseStageHistory[$i]['id']}}]" value="{{date('m/d/Y',strtotime($AllCaseStageHistory[$i]['updated_at']))}}"></p>
            </div>
            <input type="hidden" name="old_state_id[{{$AllCaseStageHistory[$i]['id']}}]" value="{{$AllCaseStageHistory[$i]['id']}}">

            <div class="col-2">
                <?php 
                    $start = strtotime($AllCaseStageHistory[$i]['created_at']);
                    if($i+1>=count($AllCaseStageHistory)){
                        $end = strtotime(date('m/d/Y'));
                    }else{
                        $end = strtotime($AllCaseStageHistory[$i+1]['created_at']);
                    }
                    $days_between = abs($end - $start) / 86400;

                    if($days_between>0.99){
                        $f=ceil($days_between);
                    }else{
                        $f=0.5;
                    }
                ?>
                <input type="text"  class="form-control"  disabled value="{{$f}}">
            </div>
            <div class="col-1">
                <button type="button" data-testid="row-6-delete-button" class="remove fas fa-times fa-lg text-black-50 delete-row-btn btn btn-link "></button>
            </div>
        </div>
        <?php } ?>
      
        <div class="fieldGroup"></div>

        <button type="button" class="btn btn-link btn-md add-more-index"><i class="fas fa-plus mr-2 text-black-50"></i>Add Case Stage</button>
        <div class="form-group row"></div>
        <h5 class="mb-4">Current Stage</h5>
        <?php 
        if($CaseMaster->case_status!=0){?>
        <div class="row ">
            <div class="col-3 d-flex">
            <p class="mt-2 mr-1 lastC" ></p>
            <select id="case_status"  name="case_status" disabled class="form-control custom-select col">
                <?php foreach($caseStageList as $kcs=>$vcs){?>
                    <option <?php if($CaseMaster->case_status==$vcs->id){ echo "selected=selected"; }?> value="{{$vcs->id}}">{{$vcs->title}}</option>
                <?php } ?>
            </select>
            </div>
            <div class="col-3">
                <input type="text"  class="form-control" disabled value="{{date('m/d/Y',strtotime($CaseStageHistory->created_at))}}"></p>
            </div>
            <div class="col-3">
                <input type="text"  class="form-control" disabled value="{{date('m/d/Y')}}"></p>
            </div>
            <div class="col-3">
            <?php 
                $start = strtotime($CaseStageHistory->created_at);
                $end = strtotime(date('Y-m-d'));
                $days_between = ceil(abs($end - $start) / 86400);
                if($days_between>0.99){
                    $f1=ceil($days_between);
                }else{
                    $f1=0.5;
                }
            ?>
                 <input type="text"  class="form-control" disabled value="{{$f1}}">
            </div>
        </div>
        <?php } ?>
      
        <div class="form-group row"></div>

        <p>* To make changes to the current stage, please edit in court case *<br>** Any undefined date gaps will be defaulted to "No Stage" when saved **</p>
        <div class="form-group row float-right">
            <a href="#">
                <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Close</button>
            </a>
            <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit" type="submit" data-style="expand-left">
                <span class="ladda-label">Save </span>
                <span class="ladda-spinner"></span><span class="ladda-spinner"></span>
            </button>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
            <div class="col-md-2 form-group mb-3">
                <div class="loader-bubble loader-bubble-primary" id="innerLoader"></div>
            </div>
        </div>

        </form>
    </div>

<div class="row fieldGroupCopy copy hide" style="display: none;">
    <div class="col-3 d-flex">
    <p class="mt-2 mr-1 counterIc">0)</p>
    <select id="case_status"  name="case_status" 
        class="form-control custom-select col">
        <option value="0">No Stage</option>
        <?php foreach($caseStageList as $kcs=>$vcs){?>
        <option value="{{$vcs->id}}">{{$vcs->title}}</option>
        <?php } ?>
    </select>
    </div>
    <div class="col-3">
        <input type="text"  class="form-control dp"  value=""></p>
    </div>
    <div class="col-3">
        <input type="text"  class="form-control dp"  value=""></p>
    </div>
    <div class="col-2">
        <input type="text"  class="form-control"  disabled value="">
    </div>
    <div class="col-1">
        <button type="button"  class="remove fas fa-times fa-lg text-black-50 delete-row-btn btn btn-link"></button>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.dp').datepicker({
            'format': 'mm/dd/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            "orientation": "bottom",
            'todayHighlight': true
        });
        $(".add-more-index").click(function () {
            var cur= $(".counterIc").length;
            var fieldHTML = '<div class="form-group fieldGroup"><div class="row fieldGroupCopy" ><div class="col-3 d-flex"><p class="mt-2 mr-1 counterIc">0)</p> <select id="case_status_'+cur+'" name="case_status[]" class="form-control custom-select col"><option value="0">No Stage</option> <?php foreach($caseStageList as $kcs=>$vcs){?><option value="{{$vcs->id}}">{{$vcs->title}}</option> <?php } ?> </select></div><div class="col-3"> <input type="text" id="start_date_'+cur+'"  name="start_date[]" onchange="getEndDate('+cur+')"  class="form-control dp" autocomplete="off" value=""></p></div><div class="col-3"> <input type="text" name="end_date[]" id="end_date_'+cur+'" class="form-control dp" onchange="getEndDate('+cur+')" value=""></p></div><div class="col-2"> <input type="text" autocomplete="off"class="form-control" name="days[]" id="days_'+cur+'" disabled value="0"></div><div class="col-1"> <button type="button" class="remove fas fa-times fa-lg text-black-50 delete-row-btn btn btn-link"></button></div></div></div>';
            $('body').find('.fieldGroup:last').before(fieldHTML);
            $('.dp').datepicker({
                'format': 'mm/dd/yyyy',
                'autoclose': true,
                'todayBtn': "linked",
                'clearBtn': true,
                "orientation": "bottom",
                'todayHighlight': true
            });
            getEndDate(cur);

            // $(this).html($(".counterIc").length);
            $(".remove").on("click", function() {
                $(this).parents('.fieldGroup').remove();
                var f=1;
                $(".counterIc").each(function (i) {
                    $(this).html(f +')');
                    f++;
                });
                $(".lastC").html($(".counterIc").length+')');
            });
            var f=1;
            $(".counterIc").each(function (i) {
                $(this).html(f+')');
                f++;
            });
            $(".lastC").html($(".counterIc").length +')');
        });
        $("#innerLoader").css('display', 'none');
        $("#response").hide();
    });
    $(".lastC").html($(".counterIc").length+')');

    $('#saveHistoryForm').submit(function (e) {
        e.preventDefault();
        beforeLoader();
        if (!$('#saveHistoryForm').valid()) {
            afterLoader();
            return false;
        }

        var dataString = $("#saveHistoryForm").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/saveCaseHistory", // json datasource
            data: dataString,
            success: function (res) {
                beforeLoader();
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
                    $('#caseTimelineModal').animate({ scrollTop: 0 }, 'slow');

                    afterLoader();
                    return false;
                } else {
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
    $(".remove").on("click", function() {
        $(this).parents('.fieldGroup').remove();
        var f=1;
        $(".counterIc").each(function (i) {
            $(this).html(f +')');
            f++;
        });
        $(".lastC").html($(".counterIc").length+')');
    });
    function getEndDate(cur){
        var startDate=$("#start_date_"+cur).val();
        var endDate=$("#end_date_"+cur).val();

        var start = new Date(startDate);
        var end = new Date(endDate);
        
        if (Date.parse(start) > Date.parse(end)) {
            $("#days_"+cur).val('0');
            $("#end_date_"+cur).val($("#start_date_"+cur).val());
        }else{
            var diffDate = (end - start) / (1000 * 60 * 60 * 24);
            var days = Math.round(diffDate);
            if(days==NaN){
                $("#days_"+cur).val('0');
            }else{            
                if(startDate!="" && endDate!=""){
                    $("#days_"+cur).val(days);
                }
            }
        }
    }
</script>
