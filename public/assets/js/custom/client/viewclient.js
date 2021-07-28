$(document).ready(function() {
    var tableName = 'billing_credit_history_table';
    var url = $('#'+tableName).attr('data-url');
    $('#'+tableName).DataTable({
        stateSave:true,
        "processing": false,
        // "order": [[1, "desc"]],
        "oLanguage": {
            "sProcessing":  '<img src="'+baseUrl +'/images/ajax_arrows.gif" width="40">',
            "sEmptyTable":"No Record Found",
        },
        "lengthMenu": [10, 25, 50, 75, 100 ],
        "serverSide": true,
        "info": true,
        "autoWidth": false,
        "orderCellsTop": true,
        "bPaginate":true,
        dom: '<"top">rt<"bottom"pl>',
        "columns": [
            { "data": "payment_date"},
            { "data": "related_to_invoice_id" },
            { "data": "detail" },
            { "data": "payment_method"},
            { "data": "deposit_amount" },
            { "data": "total_balance"},
            { "data": "action", orderable: false, searchable: false },
        ],
        columnDefs: [
            { targets: [0], orderable: false},
        ],
        initComplete: function () {
            $("[data-toggle=popover]").popover();
            // $('[data-toggle="popover"]').popover({
            //     placement : 'bottom',
            //     html : true,
            //     // title : function() {
            //     //     alert();
            //     //     return $(this).attr("data-title")+' <a href="#" class="close" data-dismiss="alert">&times;</a>';
            //     // },
            //     // content : '<div class="media"><img src="/examples/images/avatar-tiny.jpg" class="mr-3" alt="Sample Image"><div class="media-body"><h5 class="media-heading">Jhon Carter</h5><p>Excellent Bootstrap popover! I really love it.</p></div></div>'
            // }).on('show.bs.popover', function() { 
            //     console.log('o hai'); 
            // });
            // $(document).on("click", ".popover .close" , function(){
            //     $(this).parents(".popover").popover('hide');
            // });
        },
        "ajax": {
            url: url,
            type: "get", // method  , by default get
            // global: false,
            data: function ( d ) {
                d.client_id = $('#'+tableName).attr('data-client-id');
            },
            "error":function(){
                // window.location.reload();
            }
        },
    });
});