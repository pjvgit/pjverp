@extends('layouts.master')
@section('title', "Intake Form")
@section('main-content')
<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')

    </div>
    <div class="col-md-10">
        <div class="card mb-4 o-hidden">
            @include('pages.errors')
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-8">
                        <h3>Intake Form</h1>
                    </div>
                    <div class="col-sm-4 d-flex justify-content-end">
                        <!-- <a class="btn btn-secondry  mr-4" type="submit">Tell us what you think</a> -->
                        <a data-toggle="modal" data-target="#loadAddFeedBack" data-placement="bottom" href="javascript::void(0);">
                            <button onclick="setFeedBackForm('single','Intake Form Management');" type="button" class="btn btn-secondry mr-4">Tell us what you think</button>
                        </a>
                        <a href="{{route('form_templates/new')}}"> <button class="btn btn-primary" type="button"
                                id="with-input">New Intake Form</button></a>
                    </div>
                </div>
                <br>
                <table class="display table table-striped table-bordered" id="IntakeFormGrid" style="width:100%">
                    <thead>
                        <tr>
                            <th width="70%">Name</th>
                            <th width="30%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach($ContactUSIntakeForm as $k=>$v){?>
                        <tr>
                            <td>
                                <a href="{{ route('form_templates/view', $v->form_unique_id) }}">
                                    {{$v->form_name}}
                                </a>
                            </td>
                            <td class="text-right">
                                <a onclick="copyLink('{{$v->id}}')" link="{{ route('contact_us/view', $v->form_unique_id) }}" id="{{$v->id}}" data-placement="bottom" href="javascript:;"   title="Copy"  class="btn btn-link copyButton"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Copy Link"><i class="fas fa-link align-middle  mr-4" data="MyText"></i></span>
                                </a>
                                <a onclick="copyHTMLCode('{{$v->id}}')" link='<iframe src="{{ route('contact_us/view', $v->form_unique_id) }}" title="Contact Us Form" style="border:none;" width="600" height="800"><!-- Specify a width and height by changing the width/height properties of this iframe --></iframe>' id="Code{{$v->id}}" data-placement="bottom" href="javascript:;"   title="Copy"  class="btn btn-link copyButton"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Copy HTML Code"><i
                                    class="fas fa-code  align-middle  mr-4"></i></span>
                                </a>
                                <a href="{{ route('form_templates/view', $v->form_unique_id) }}" title="">
                                    <span data-toggle="tooltip" data-trigger="hover" title="" data-content="Edit"
                                        data-placement="top" data-html="true" data-original-title="Edit"> <i
                                            class="fas fa-pen align-middle mr-4"></i></span>
                                </a>

                              
                            </td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
                <?php 
                if(Auth::User()->contact_us_widget=="yes"){?>
                <br>
                    <div class=" border rounded text-center w-100 h-auto p-4" id="contactUsWidget">
                        <div class="d-flex justify-content-end"><button type="button" class="btn btn-link" onclick="dismissContactUs()">Dismiss</button>
                        </div><i class="fas fa-laptop-code fa-3x mb-3"></i>
                        <h5><strong>CONTACT US - the perfect intake form for your website</strong></h5>
                        <p class="w-75 d-inline-block">Use the "Contact Us" intake form on your website to collect important
                            information from your leads. All the data is saved right to MyCase, making your intake process
                            simple and seamless. You can also customize the Contact Us Form to make it work for your firm.
                        </p>
                        <p><a href="#" target="_blank" rel="noopener noreferrer">Learn more about Contact Us forms</a></p>
                    </div>
                <?php  } ?>
                <br>
                <table class="display table table-striped table-bordered" id="IntakeFormGrid" style="width:100%">
                    <thead>
                        <tr>
                            <th width="70%">Name</th>
                            <th width="30%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach($IntakeForm as $k=>$v){?>
                        <tr>
                            <td>
                                <a href="{{ route('form_templates/view', $v->form_unique_id) }}">
                                    {{$v->form_name}}
                                </a>
                            </td>
                            <td class="text-right">

                                <a data-toggle="modal" data-target="#emailIntakeForm" data-placement="bottom"
                                    href="javascript:;" title="Email" data-testid="delete-button" class="btn btn-link"
                                    onclick="emailFormFunction({{$v->id}});">
                                    <span data-toggle="tooltip" data-trigger="hover" title="" data-content="Email"
                                        data-placement="top" data-html="true" data-original-title="Email"> <i
                                            class="fas fa-fw fa-envelope mr-4"></i></span>
                                </a>

                                <a data-toggle="modal" data-target="#cloneForm" data-placement="bottom"
                                    href="javascript:;" title="Clone" data-testid="delete-button" class="btn btn-link"
                                    onclick="cloneFormFunction({{$v->id}});">
                                    <span data-toggle="tooltip" data-trigger="hover" title="" data-content="Clone"
                                        data-placement="top" data-html="true" data-original-title="Clone"> <i
                                            class="fas fa-fw fa-copy mr-4"></i></span>

                                </a>

                                <a href="{{ route('form_templates/view', $v->form_unique_id) }}" title="">
                                    <span data-toggle="tooltip" data-trigger="hover" title="" data-content="Edit"
                                        data-placement="top" data-html="true" data-original-title="Edit"> <i
                                            class="fas fa-pen align-middle mr-4"></i></span>
                                </a>
                                <a data-toggle="modal" data-target="#deleteForm" data-placement="bottom"
                                    href="javascript:;" title="Delete" data-testid="delete-button" class="btn btn-link"
                                    onclick="deleteFormFunction({{$v->id}},'{{addslashes($v->form_name)}}');">
                                    <span data-toggle="tooltip" data-trigger="hover" title="" data-content="Delete"
                                        data-placement="top" data-html="true" data-original-title="Delete"> <i
                                            class="fas fa-fw fa-trash mr-4"></i></span>

                                </a>
                            </td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
                <div class="border rounded text-center w-100 h-auto p-4"><i
                    class="fas fa-laptop-code fa-3x mb-3"></i>
                    <h5><strong>Intake is a Breeze with  {{config('app.name')}} Intake Forms</strong></h5>
                    <p>Collect important case-related information from your clients with custom intake forms.<br>Data is
                        saved right to MyCase, making your intake process simple and seamless.</p><a
                        href="#" target="_blank"
                        rel="noopener noreferrer">Learn more about Intake Forms</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="deleteForm" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSingle">Delete Intake Form</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="eventID">
                        <form class="deleteIeIntakeForm" id="deleteIeIntakeForm" name="deleteIeIntakeForm"
                            method="POST">
                            <div id="showError" style="display:none"></div>
                            @csrf
                            <input class="form-control" id="form_id" value="" name="form_id" type="hidden">
                            <div class=" col-md-12">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label">
                                        Are you sure you want to delete <b><span id="fnam"></span></b>?<br>
                                        <p></br>
                                            <p> Deleting this intake form will invalidate any associated forms that have
                                                been shared and not yet submitted</p>
                                    </label>
                                </div>
                                <div class="form-group row float-right">
                                    <a href="#">
                                        <button class="btn btn-secondary  m-1" type="button"
                                            data-dismiss="modal">Cancel</button>
                                    </a>
                                    <button class="btn btn-primary ladda-button example-button m-1 submit" id="submit"
                                        type="submit">
                                        <span class="ladda-label">Yes, Delete</span>
                                    </button>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-8 col-form-label"></label>
                                    <div class="col-md-2 form-group mb-3">
                                        <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader1"
                                            style="display: none;"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-12 col-form-label"></label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="cloneForm" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Clone Intake Form</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="cloneFormArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div id="emailIntakeForm" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Email Intake Form</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="emailFormArea">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="text" value="" id="myInput" style="opacity: 00000;">
<input type="text" value="" id="htmlCode" style="opacity: 00000;">

@endsection
@section('page-js')
<script src="{{ asset('assets\js\custom\feedback.js?').env('CACHE_BUSTER_VERSION') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('button').attr('disabled', false);
        $("#preloader").hide();
        $("[data-toggle=popover]").popover();
        $("[data-toggle=tooltip]").tooltip();

        $('#deleteIeIntakeForm').submit(function (e) {
            $(".submit").attr("disabled", true);
            $(".innerLoader").css('display', 'block');
            e.preventDefault();

            if (!$('#deleteIeIntakeForm').valid()) {
                $(".innerLoader").css('display', 'none');
                $('.submit').removeAttr("disabled");
                return false;
            }
            var dataString = '';
            dataString = $("#deleteIeIntakeForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/intake_form/deleteIntakeForm", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                    $(".innerLoader").css('display', 'block');
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
                        $(".innerLoader").css('display', 'none');
                        $('.submit').removeAttr("disabled");
                        return false;
                    } else {
                        window.location.reload();
                    }
                }
            });
        });
    });

    function deleteFormFunction(id, fnam) {
        $("#form_id").val(id);
        $("#fnam").text(fnam);
    }

    function cloneFormFunction(id) {
        $("#preloader").show();
        $("#cloneFormArea").html('');
        $("#cloneFormArea").html('<img src="{{LOADER}}""> Loading...');

        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/intake_form/cloneIntakeForm", // json datasource
                data: {
                    'form_id': id
                },
                success: function (res) {
                    $("#cloneFormArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function emailFormFunction(id) {
        $("#preloader").show();
        $("#emailFormArea").html('');
        $("#emailFormArea").html('<img src="{{LOADER}}""> Loading...');

        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/intake_form/emailIntakeForm", // json datasource
                data: {
                    'form_id': id
                },
                success: function (res) {
                    $("#emailFormArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function dismissContactUs() {
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/intake_form/dismissContactUs", // json datasource
                data: {'form_id': ''},
                success: function (res) {
                    $("#contactUsWidget").remove();
                }
            })
        })
    }
    
    function copyLink(id) {
        var links=$("#"+id).attr("link");
        $("#myInput").val(links);
        var copyText = document.getElementById("myInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999); /*For mobile devices*/
        document.execCommand("copy");
        toastr.success('Link Copied', "", {
            progressBar: !0,
            positionClass: "toast-top-full-width",
            containerId: "toast-top-full-width"
        });
    }

    function copyHTMLCode(id) {
            var links=$("#Code"+id).attr("link");
            $("#htmlCode").val(links);
            var copyText = document.getElementById("htmlCode");
            copyText.select();
            copyText.setSelectionRange(0, 99999); /*For mobile devices*/
            document.execCommand("copy");
            toastr.info('Code copied to clipboard!', "", {
                positionClass: "toast-top-full-width",
                containerId: "toast-top-full-width"
            });
        }

</script>
@stop
