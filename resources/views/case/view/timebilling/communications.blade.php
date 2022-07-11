<h3 id="hiddenLable">{{($CaseMaster->case_title)??''}}</h3>
<div class="container-fluid">
    <div class="justify-content-end pt-2 d-print-none row ">
        <div class="align-self-end text-right col-6">
            @can(['messaging_add_edit', 'case_add_edit'])
            <a data-toggle="modal" data-target="#addNewMessagePopup" data-placement="bottom" href="javascript:;"
                onclick="addNewMessagePopup('case_id',{{$CaseMaster['case_id']}});">
                <button type="button" class="mx-1 btn btn-primary">New Message</button>
            </a>
            @endcan
        </div>
    </div>
</div>
<hr>
<div class="col-md-12">
    <div data-testid="mc-table-container" style="font-size: small;">
        <table class="display table table-striped table-bordered" id="messagesGrid" style="width:100%">
            <thead>
                <tr>
                    <th class="col-md-auto nosort"></th>
                    <th class="nosort" width="20%">Sender</th>
                    <th class="nosort" width="60%">Subject</th>
                    <th class="nosort" width="15%">Last Post</th>
                    <th class="col-md-auto nosort" width="5%"></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<style>
    .pagination{width: 80%;float: right;}
    </style>
@section('page-js-inner')
<script type="text/javascript">
    "use strict";
    $(document).ready(function () {
        $("#button").removeAttr('disabled');
        var messagesGrid =  $('#messagesGrid').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"pl><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false, "aaSorting": [],
            ordering: false,
            // "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/court_cases/communications/loadMessagesEntry", // json datasource
                type: "post",  // method  , by default get
                data :{ 'case_id':"{{$CaseMaster['case_id']}}"},
                error: function(){  // error handling
                     $("#messagesGrid_processing").css("display","none");
                }
            },
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] },{'bSortable': false,'aTargets': ['nosort']}],
            columns: [
                { data: 'id','sorting':false},
                { data: 'sender_name'},
                { data: 'subject'},
                { data: 'last_post'},
                { data: 'id'}
            ],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                if(aData.is_read_msg == 'no') {
                    $(nRow).addClass("font-weight-bold");
                }
                var is_global = (aData.is_global == 0) ? '<i class="fas fa-eye p-2"></i>' : '';
                $('td:eq(3)', nRow).html('<div class="text-center nowrap"><a data-toggle="modal"  data-target="#loadMessagesEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadMessagesEntryPopup('+aData.id+');">'+is_global+'</a></div>');
            }
        });
    });

    function printEntry()
    {
        $('#hiddenLable').show();
        var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        $(".main-content-wrap").remove();
        window.print(canvas);
        // w.close();
        window.location.reload();
        return false;
    }
    $('#hiddenLable').hide();
</script>
@stop
