<?php 
$CommonController= new App\Http\Controllers\CommonController();
if(!$commentData->isEmpty()){
?>
<table class="display table table-striped table-bordered dataTable no-footer" id="caseHistoryGrid" style="width: 100%;"
    role="grid">
    <tbody>
        <?php foreach($commentData as $k=>$v){ ?>
        
        <tr role="row" class="odd">
            <td class="sorting_1" style="font-size: 13px;">
                <div class="text-left">
                    <?php 
                    $imageLink=[];
                    $imageLink["add"]="activity_expense_added.png";
                    $imageLink["update"]="activity_expense_updated.png";
                    $imageLink["delete"]="activity_expense_deleted.png";
                    $image=$imageLink[$v->action];
                ?>
                    <img src="{{BASE_URL}}public/icon/{{$image}}" width="27" height="21">
                    <a class="name"
                        href="{{BASE_URL}}/contacts/attorneys/{{base64_encode($v->user_id)}}">{{$v->first_name}}
                        {{$v->last_name}} ({{$v->user_title}})</a>   {{$v->activity}}  for <a data-toggle="modal"  data-target="#loadEditExpenseEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadEditExpenseEntryPopup({{$v->expense_id}});"> {{$v->title}} </a> <abbr
                        class="timeago" title="{{$v->all_history_created_at}}">about {{$v->time_ago}}</abbr> via web |
                    <a class="name"
                        href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->case_title}}</a>
                </div>
            </td>
        </tr><?php
        } ?>
    </tbody>
</table>
<span class="ExpensesNotify">{!! $commentData->links() !!}</span>
<?php } else { 
    echo  "No recent activity available.";
}
?>

<div id="loadEditExpenseEntryPopup" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Expense</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="loadEditExpenseEntryPopupArea">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadEditExpenseEntryPopup(id) {
        $("#preloader").show();
        $("#loadEditExpenseEntryPopupArea").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/expenses/loadEditExpenseEntryPopup", // json datasource
                data: {'entry_id': id},
                success: function (res) {
                    $("#loadEditExpenseEntryPopupArea").html('');
                    $("#loadEditExpenseEntryPopupArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    $('#deleteTimeEntryForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#deleteTimeEntryForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#deleteTimeEntryForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/bills/expenses/deleteExpenseEntryForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
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

</script>
