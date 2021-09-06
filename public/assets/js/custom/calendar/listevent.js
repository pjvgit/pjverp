var tab1Page = 1;
$(window).scroll(function() {
    if($(window).scrollTop() + $(window).height() >= $(document).height()) {
        tab1Page++;
        var totalPage = $('#event_list_table tbody tr .event-last-page').val();
        if(tab1Page <= totalPage)
            loadMoreEvent(tab1Page, filter = null);
    }
});

// Load more events
function loadMoreEvent(page, filter = null) {
    var divId = 'event_list_table tbody';
    var upcoming = $("input:checkbox#mc:checked").val();
    $.ajax({
        url : '?page=' + page,
        data: {upcoming: upcoming},
        beforeSend: function() {
            $(".load-more-loader").show();
        }
    }).done(function (data) {
        $(".load-more-loader").parents('tr').hide();
        if(data != "") {
            if(filter) {
                $('#'+divId).html(data);
            } else {
                $('#'+divId).append(data);
            }
            $('#'+divId+' .pagination').hide();
            $('[data-toggle="popover"]').popover();
        } else {
            $('#'+divId).html('<tr><td colspan="6" class="text-center"><h4 class="all-pdng-cls">No record found</h4></td></tr>');
        }
    }).fail(function () {
        $(".load-more-loader").hide();
        $('#'+divId).append('<tr><td colspan="6" class="text-center"><h4 class="all-pdng-cls">No record found</h4></td></tr>');
    });
}