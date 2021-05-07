<?php  $CommonController= new App\Http\Controllers\CommonController(); 
$hideClass="";
$showClass=$showToolTip="";
$counterClient=$caseCllientSelection->pluck('client_linked_with_case_counter');
$toalClientCount=count($counterClient);
$linkCase=$nonLinkCase=0;
foreach($counterClient as $k=>$v){
    if($v>1){ $linkCase++; }
    if($v<=1){ $nonLinkCase++;}
}

if($nonLinkCase>0){
    $showClass="shower";
}else{
    $showClass="hider";
}
if($toalClientCount==$nonLinkCase){
    $showToolTip="shower";
}
?>
<form class="closeCaseForm" id="closeCaseForm" name="closeCaseForm" method="POST">
    <div class="showError" style="display:none"></div>
    @csrf
    <input type="hidden" value="{{$case_id}}" name="case_id">
    <div class="row">
        <div class="col-md-4 form-group mb-3">
            <label for="firstName1">Date To Close</label>
            <input class="form-control datepicker dateopen" id="case_close_date" value="{{date('m/d/Y')}}"
                name="case_close_date" type="text" placeholder="mm/dd/yyyy">
        </div>
        <?php  if($caseStat->case_event_counter > 0){?>
            <div class="col-md-8 form-group mt-2">
                <label for="firstName1">&nbsp;</label>
                <div class="d-flex case-events-archive" data-testid="remove-events-checkbox">
                    <div class="">
                        <label class="d-inline-flex align-items-center">
                            <input id="archive-appts-option" name="archive-appts" type="checkbox">
                            <span class="ml-2 "></span>
                        </label>
                    </div>
                    <span>
                        <i class="fas fa-circle pr-1 text-danger"></i>Delete all case-related Events
                    </span>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php  if(!$caseCllientSelection->isEmpty()){?>
    <div class="my-2 d-flex">
        <div class="{{$showClass}}">Archive the following Linked Contacts:</div>
        <div class="ml-auto {{$showToolTip}}">
            <span class="text-primary" id="explain_missing_contact" data-toggle="tooltip" data-placement="top"
                title="If a contact is linked to another active case, you cannot archive that contact at this time.">Where
                are the rest of my contacts?</span>
        </div>
    </div>
    <?php } ?>
    <div>
        <fieldset id="mc-table-fieldset" >
            <div class="row {{$showClass}}">
                <div class="col-12 form-group text-right">
                    <small class="text-muted mx-1">Text Size</small>
                    <button type="button" arial-label="Decrease text size" data-testid="dec-text-size"
                        class="btn-sm py-0 px-1 mx-1 btn btn-outline-light decrease "><i class="fas fa-minus fa-xs"></i>
                    </button>
                    <button type="button" arial-label="Increase text size" data-testid="inc-text-size"
                        class="btn-sm py-0 px-1 mx-1 btn btn-outline-light increase"><i class="fas fa-plus fa-xs"></i>
                    </button>
                </div>
            </div>
            <?php  if(!$caseCllientSelection->isEmpty()){?>
                    <div data-testid="mc-table-container " class="{{$showClass}}" style="font-size: small;">
                        <table class="archivable-user-list table table-md table-striped table-hover"
                            style="table-layout: auto;" > 
                            <colgroup>
                                <col style="width: 2rem;">
                                <col style="width: 70px;">
                                <col>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="jsx-3954552588 text-center" style="cursor: initial;"><label for="select-all-29"
                                            class="sr-only ">Select all rows</label><input type="checkbox" class="mx-1"
                                            id="checkall"></th>
                                    <th class="jsx-3954552588 user-avatar" style="cursor: initial;"></th>
                                    <th class="jsx-3954552588 archive-user user-name" style="cursor: initial;">Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                               
                                <?php 
                                    foreach($caseCllientSelection as $key=>$val){
                                        if($val->client_linked_with_case_counter<="1"){ ?>
                                            <tr class="">
                                                <td class="text-center">
                                                    <label for="select-row-30" class="sr-only ">Select row</label>
                                                    <input id="select-row-30" type="checkbox" name="clientRow[{{$val->id}}]"
                                                        class="client_checkbox mx-1" value="{{$val->id}}">
                                                </td>
                                                <td class="user-avatar">
                                                    <i class="fas fa-2x fa-user-circle text-black-50"></i>
                                                </td>
                                                <td class="archive-user user-name">
                                                    <div>{{$val->user_name}} ({{$CommonController->getUserTypeText($val->user_level)}})
                                                    </div>
                                                </td>
                                            </tr>
                                <?php   }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php }else{   ?>
                    <div class="mt-2">There are no contacts linked to this case that can be archived.<div
                        class="text-muted font-italic">If a contact is linked to another active case, you cannot
                        automatically archive that contact at this time.</div>
                </div>
                <?php } ?>
        </fieldset>
    </div>
    <div class="modal-footer">
        <div class="col-md-2 form-group">
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;">
            </div>
        </div>
        <a href="#">
            <button class="btn btn-secondary  btn-rounded mr-1 " type="button" data-dismiss="modal">Cancel</button>
        </a>
        <button class="btn btn-primary  btn-rounded submit " id="submitButton" value="savenote" type="submit">Close
            Case</button> 
    </div>
</form>
<style>
    .hider{display: none;}
    .shower{display: inline;}
</style>

<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
        $(".innerLoader").css('display', 'none');
        $('#case_close_date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            "orientation": "bottom",
            'todayHighlight': true
        }).on('changeDate', function (e) {}).on('clearDate', function (e) {});
        if (localStorage.getItem("closeCaseClientList") == "") {
            localStorage.setItem("closeCaseClientList", "13");
        }
        var originalSize = $('td').css('font-size');
        var currentSize = localStorage.getItem("closeCaseClientList");
        $('.archivable-user-list  td').css('font-size', currentSize + 'px');
        //Increase the font size 
        $(".increase").click(function () {
            modifyFontSize('increase');
        });
        //Decrease the font size
        $(".decrease").click(function () {
            modifyFontSize('decrease');
        });
        $('#checkall').on('change', function () {
            $('.client_checkbox').prop('checked', $(this).prop("checked"));
        });
        $('.client_checkbox').change(function () { //".checkbox" change 
            if ($('.client_checkbox:checked').length == $('.client_checkbox').length) {
                $('#checkall').prop('checked', true);
            } else {
                $('#checkall').prop('checked', false);
            }
        });
    });
    $('#closeCaseForm').submit(function (e) {
        $(".innerLoader").css('display', 'block');
        e.preventDefault();
        if (!$('#closeCaseForm').valid()) {
            $(".innerLoader").css('display', 'none');
            return false;
        }
        var dataString = $("#closeCaseForm").serialize();
        var array = [];
        $("input[class=client_checkbox]:checked").each(function (i) {
            alert($(this).val())
            array.push($(this).val());
        });
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/ProcessCloseCase", // json datasource
            data: dataString + '&client_id=' + JSON.stringify(array),
            success: function (res) {
                $(".innerLoader").css('display', 'block');
                if (res.errors != '') {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    $(".innerLoader").css('display', 'none');
                    return false;
                } else {
                    window.location.reload();
                }
            }
        });
    });

    function modifyFontSize(flag) {
        var min = 13;
        var max = 19;
        var divElement = $('.archivable-user-list  td')
        var currentFontSize = parseInt(divElement.css('font-size'));
        if (flag == 'increase')
            currentFontSize += 3;
        else if (flag == 'decrease')
            currentFontSize -= 3;
        else
            currentFontSize = 13;
        if (currentFontSize >= min && currentFontSize <= max) {
            divElement.css('font-size', currentFontSize);
            localStorage.setItem("closeCaseClientList", currentFontSize);
        }
    } 
   

</script>
