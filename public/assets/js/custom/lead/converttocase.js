function loadStep1(id) {
    $("#step-1").html('<img src="{{LOADER}}"> Loading...');
    $(function () {
        $.ajax({
            type: "POST",
            url:  baseUrl +"/leads/loadStep1", // json datasource
            data: {"id":id},
            success: function (res) {
                console.log(res);
                $("#step-1").html(res);
                $("#saveStep1").validate(caseStep1ValidateOptions);
                $("#preloader").hide();
            }
        })
    })
}

var caseStep1ValidateOptions = {
    rules: {
        first_name: {
            required: true,
            minlength: 2
        },
        last_name: {
            required: true,
            minlength: 2
        },
        email: {
            email: true
        },
        website: {
            url: false
        },
        home_phone: {
            number: true
        },
        work_phone: {
            number: true
        },
        cell_phone: {
            number: true
        },
        fax_number: {
            number: true
        }
    },
    messages: {
        first_name: {
            required: "Please enter first name",
            minlength: "First name must consist of at least 2 characters"
        },
        last_name: {
            required: "Please enter last name",
            minlength: "Last name must consist of at least 2 characters"
        },
        email: {
            minlength: "Email is not formatted correctly"
        },
        website: {
            url: "Please enter valid website url"
        },
        home_phone: {
            number: "Please enter numeric value"
        },
        work_phone: {
            number: "Please enter numeric value"
        },
        cell_phone: {
            number: "Please enter numeric value"
        },
        fax_number: {
            number: "Please enter numeric value"
        }
    },

    errorPlacement: function (error, element) {
        if (element.is('#user_type')) {
            error.appendTo('#UserTypeError');
        } else if (element.is('#default_rate')) {
            error.appendTo('#TypeError');
        } else {
            element.after(error);
        }
    },
    submitHandler: function() {    
        var dataString = '';
        dataString = $("#saveStep1").serialize();
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/saveStep1", // json datasource
            data: dataString,
            beforeSend: function (xhr, settings) {
                settings.data += '&saveandaddcase=yes';
            },
            success: function (res) {
                $("#innerLoader").css('display', 'block');
                if (res.errors != '') {
                    $('#showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                    $.each(res.errors, function (key, value) {
                        errotHtml += '<li>' + value + '</li>';
                    });
                    errotHtml += '</ul></div>';
                    $('#showError').append(errotHtml);
                    $('#showError').show();
                    $("#innerLoader").css('display', 'none');
                    $('#submit').removeAttr("disabled");
                    return false;
                } else {
                    $('#submit').removeAttr("disabled");
                    loadStep2(res);
                }
            }
        });
        return false;
    }
};

function loadStep2(res) {
    console.log(res);
   
    $.ajax({
        type: "POST",
        url: baseUrl + "/leads/loadStep2", // json datasource
        data: {
            "id": res.user_id,
            "case_id":localStorage.getItem("case_id"),
        },
        success: function (res) {
            $('#smartwizard').smartWizard("next");
            $("#innerLoader").css('display', 'none');
            $("#step-2").html(res);
            $("#preloader").hide();
        }
    })

    return false;
}