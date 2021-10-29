$(document).ready(function() {

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
                    window.location.reload();
                }
            }
        });
    });
});

function setFeedBackForm(page, title) {

    $("#feedback_title").html(title);
    $("#topic").val(title);
}