<?php
// $CommonController= new App\Http\Controllers\CommonController();
?>
<!-- <div class="table-responsive">
    <table class="display table table-striped table-bordered" id="caseHistoryGrid" style="width:100%">
        <thead>
            <tr>
                <th width="100%">id</th>
            </tr>
        </thead>

    </table>
</div> -->
<div id="leadinvoiceEntry">
    No recent activity available.
</div>
<div class="files-per-page-selector float-right" style="white-space: nowrap; ">
    <label class="mr-2">Rows Per Page:</label>
    <select id="per_page" onchange="onchangeLength();" name="per_page" class="custom-select w-auto">
        <option value="10" selected="">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
    </select>
</div>
@section('page-js')
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', '.InvoiceNotify .pagination a', function(event){
            event.preventDefault(); 
            var page = $(this).attr('href').split('page=')[1];
            loadInvoiceNotification(page);
        });      
    });
    function onchangeLength(){
        let activeTav=$("ul.nav-tabs li a.active").attr("id");
        loadInvoiceNotification(1);
    }
    function loadInvoiceNotification(page=null) {
        $("#innerLoader").css('display', 'none');
        $("#leadinvoiceEntry").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/notifications/loadInvoiceNotification?per_page="+$("#per_page").val()+"&page="+page+"&client_id={{$LeadData->user_id}}", // json datasource
                data: 'bulkload',
                success: function (res) {
                    $("#leadinvoiceEntry").html(res);
                    return false;
                }
            })
        })
    }
    loadInvoiceNotification(1);
</script>
@endsection
