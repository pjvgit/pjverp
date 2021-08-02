$(document).ready(function() {
    // For credit history list
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
        "ordering": false,
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
        "drawCallback": function (settings) { 
            var response = settings.json;
            $(".credit-total-balance").text(response.credit_total);
        },
    });

    // For Invoices list
    var tableName1 = 'billing_invoice_table';
    var url = $('#'+tableName1).attr('data-url');
    $('#'+tableName1).DataTable({
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
            { "data": "view", orderable: false, searchable: false},
            { "data": "invoice_number" },
            { "data": "total_amount" },
            { "data": "paid_amount"},
            { "data": "due_amount" },
            { "data": "due_date"},
            { "data": "created_at"},
            { "data": "status"},
            { "data": "viewed"},
            { "data": "action", orderable: false, searchable: false },
        ],
        columnDefs: [
            { targets: [0], orderable: false},
        ],
        initComplete: function () {
            $("[data-toggle=popover]").popover();
        },
        "ajax": {
            url: url,
            type: "get", // method  , by default get
            // global: false,
            data: function ( d ) {
                d.client_id = $('#'+tableName1).attr('data-client-id');
            },
            "error":function(){
                // window.location.reload();
            }
        },
        "drawCallback": function (settings) { 
            var response = settings.json;
            $(".credit-total-balance").text(response.credit_balance);
            $(".trust-total-balance").text(response.trust_balance);
        },
    });
});