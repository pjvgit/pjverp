$(document).ready(function() {

    $(".ratingButton").on("click", function() {
        $(".ratingButton").removeClass("btn-gray").addClass("btn-secondary");
        $(this).removeClass('btn-secondary').addClass("btn-gray");
        $("#rating").val($(this).text());
    });

    $('#feedback_form').submit(function(e) {
        e.preventDefault();
        $('#feedback_form_errors').html('');
        $("#preloader").show();
        var dataString = $("#feedback_form").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/saveFeedback", // json datasource
            data: dataString,
            success: function(res) {
                if (res.errors != '') {
                    $("#preloader").hide();
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function(key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#feedback_form_errors').append(errotHtml);
                    return false;
                } else {
                    $("#feedback_title").html('');
                    $("#topic").val('');
                    $("#rating").val('');
                    $("#feedback_form")[0].reset();
                    window.location.reload();
                }
            }
        });
    });
});

function setFeedBackForm(page, title) {
    if (page == 'single') {
        $(".rating-section").remove();
    }
    $("#feedback_title").html(title);
    $("#topic").val(title);
}