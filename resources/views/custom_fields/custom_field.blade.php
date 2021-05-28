@extends('layouts.master')
@section('title', 'Custom Fields')
@section('main-content')
<div class="breadcrumb">
    <h3>Custom Fields</h1>

</div>
<div class="border-top mb-2"></div>
<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')
    </div>
    <div class="col-md-10">
        <div class="card mb-4 o-hidden">
            <div class="card-body" id="infopage">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs w-100" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ (request()->group=='court_case') ? 'active show' : '' }} "
                                    id="caseMatterTab" href="{{ route('custom_fields') }}?group=court_case" aria-controls="tba2"
                                    aria-selected="true">Cases / Matters</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ (request()->group=='client') ? 'active show' : '' }} "
                                    id="clientTab" href="{{ route('custom_fields') }}?group=client" aria-controls="tba2"
                                    aria-selected="true">Contacts</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ (request()->group=='company') ? 'active show' : '' }}" id="compnayTab" 
                                    aria-controls="tba2" href="{{ route('custom_fields') }}?group=company" aria-selected="true">Companies</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ (request()->group=='time_and_expense') ? 'active show' : '' }}" id="time_and_expenseTab"
                                    aria-controls="tba2" href="{{ route('custom_fields') }}?group=time_and_expense" aria-selected="true">Time & Expense</a>
                            </li>
                        </ul>
                        <div class="tab-content w-100" id="myTabContent">
                            <div class="tab-pane fade   {{ (request()->group=='court_case') ? 'active show' : '' }}"
                                id="caseMatterTab" role="tabpanel" aria-labelledby="profile-basic-tab">
                                @include('custom_fields.court_case')
                            </div>
                            <div class="tab-pane fade   {{ (request()->group=='client') ? 'active show' : '' }}"
                                id="clientTab" role="tabpanel" aria-labelledby="profile-basic-tab2">
                                @include('custom_fields.client')
                            </div>
                            <div class="tab-pane fade   {{ (request()->group=='company') ? 'active show' : '' }}"
                                id="companyTab" role="tabpanel" aria-labelledby="profile-basic-tab2">
                                @include('custom_fields.company')
                            </div>
                            <div class="tab-pane fade   {{ (request()->group=='time_and_expense') ? 'active show' : '' }}"
                                id="time_and_expenseTab" role="tabpanel" aria-labelledby="profile-basic-tab2">
                                @include('custom_fields.time_and_expense')
                            </div>
                        </div>  
                    </div> 
                </div>
               
            </div>
        </div>
    </div>
</div>
    

@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        $(".country").select2({
            placeholder: "Select country",
            theme: "classic",
            allowClear: true
        });
        $('.cropper').rcrop({
            minSize : [200,200],
            preserveAspectRatio : true,
            grid : false,
        });
        $('.cropper').on('rcrop-ready', function(){
            var srcResized = $(this).rcrop('getDataURL', 130,130);
            $('#imageCode').val(srcResized);
        });
        $('.cropper').on('rcrop-changed', function(){
            var srcResized = $(this).rcrop('getDataURL', 130,130);
            $('#imageCode').val(srcResized);
        });
        $('#inputGroupFile02').on('change', function(e) {
            //get the file name
            var fileName = e.target.files[0].name;
            //replace the "Choose a file" label
            $(this).next('.custom-file-label').html(fileName);
        });

        $('#removeImageForm').submit(function (e) {
            $(".innerLoader").css('display', 'block');
            e.preventDefault();
            if (!$('#removeImageForm').valid()) {
                $(".innerLoader").css('display', 'none');
                return false;
            }
            var dataString = $("#removeImageForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/removeProfileImage", // json datasource
                data: dataString ,
                success: function (res) {
                    $(".innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        $(".innerLoader").css('display', 'none');
                        return false;
                    } else {
                        window.location.href=baseUrl+'/load_profile';
                    }
                }
            });
        });
                        

    });
    <?php if(session('page')=="password"){?>
        $('html, body').animate({
            scrollTop: $("#password").offset().top -200
        }, 1000);
    <?php } ?>

    <?php if(session('page')=="infopage"){?>
        $('html, body').animate({
            scrollTop: $("#infopage").offset().top
        }, 1000);
    <?php } ?>
    <?php if(session('page')=="email"){?>
        $('html, body').animate({
            scrollTop: $("#email").offset().top -200
        }, 1000);
    <?php } ?>
    <?php if(session('page')=="image"){?>
        $('html, body').animate({
            scrollTop: $("#image").offset().top
        }, 1000);
    <?php } ?>

    function removeImage(){
        $("#removeImageModal").modal("show");
    }
</script>
@stop
@endsection
