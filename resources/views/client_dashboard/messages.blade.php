<h2 class="mx-2 mb-0 text-nowrap hiddenLable">        {{ ucfirst($userProfile->first_name) .' '.ucfirst($userProfile->last_name) }} (Client)    </h2>
<div class="container-fluid">
    <div class="justify-content-end pt-2 d-print-none row ">
        <div class="align-self-end text-right col-6">
            <a data-toggle="modal" data-target="#addNewMessagePopup" data-placement="bottom" href="javascript:;" onclick="addNewMessagePopup('user_id',{{$userProfile->id}});"> 
                <button type="button" class="mx-1 btn btn-primary">New Message</button>
            </a>
        </div>
    </div>
</div>
<br>
<div class="col-md-12">
    <div data-testid="mc-table-container" style="font-size: small;">
        <table class="display table table-striped table-bordered" id="messagesGrid" style="width:100%">
            <thead>
                <tr>
                    <th class="col-md-auto nosort"></th>
                    <!-- <th class="nosort" width="20%">Sender</th> -->
                    <th class="nosort" width="30%">Subject</th>
                    <th class="nosort" width="30%">Case Link</th>
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
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/court_cases/communications/loadMessagesEntry", // json datasource
                type: "post",  // method  , by default get
                data :{ 'user_id':"{{$userProfile->id}}"},
                error: function(){  // error handling
                     $("#messagesGrid_processing").css("display","none");
                }
            },
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] },{'bSortable': false,'aTargets': ['nosort']}],
            columns: [
                { data: 'id','sorting':false},
                { data: 'subject'},
                { data: 'case_title'},
                { data: 'last_post'},
                { data: 'id'}
            ],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                var is_global = (aData.is_global == 0) ? '<i class="fas fa-eye p-2"></i>' : '';
                $('td:eq(3)', nRow).html('<div class="text-center nowrap"><a data-toggle="modal"  data-target="#loadMessagesEntryPopup" data-placement="bottom" href="javascript:;"  onclick="loadMessagesEntryPopup('+aData.id+');">'+is_global+'</a></div>');
            }
        });
    });
</script>
@stop