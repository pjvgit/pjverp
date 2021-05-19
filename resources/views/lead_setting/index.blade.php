@extends('layouts.master')
@section('title', 'Lead Settings')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 
?>

<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')
    </div>
    <div class="col-md-10">
        <div class="card mb-4 o-hidden">
            <div class="card-body">
                <h4 class="card-title mb-3">Lead Settings
                </h4>
                <p></p>
                <ul class="nav nav-tabs" id="tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active show" onclick="callTab('ref')" id="ref" data-toggle="tab" href="#ReferralSources"
                            role="tab" aria-controls="homeBasic" aria-selected="false">Referral
                            Sources
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="sta" onclick="callTab('sta')" data-toggle="tab" href="#Statuses" role="tab"
                            aria-controls="profileBasic" aria-selected="false">Statuses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="did" onclick="callTab('did')" data-toggle="tab" href="#DidNotHireReasons" role="tab"
                            aria-controls="contactBasic" aria-selected="true">Did Not
                            Hire Reasons
                        </a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade  active show" id="ReferralSources" role="tabpanel"
                        aria-labelledby="home-basic-tab">
                        <div id="lead-referral-source-settings">
                            <div class="d-flex flex-row-reverse">
                                <a data-toggle="modal" onclick="addReferalResourcePopup();"
                                    data-target="#addReferalResource" data-placement="bottom" href="javascript:;">
                                    <button type="button" class="btn btn-primary">Add
                                        Referral Source</button></i>
                                </a>

                            </div>
                            <div class="w-100 mt-3 lead-referral-source-list-container">
                                <div class="w-100 p-3 bg-gray-200">
                                    <h4 class="font-weight-bold m-0">Referral Source</h4>
                                </div>
                                <?php 
                                foreach($ReferalResource as $k=>$v){?>
                                <div class="container-fluid">
                                    <div class="py-3 lead-referral-source-row border-bottom row ">
                                        <div class="col-10">{{$v->title}}</div>
                                        <div class="col-2">
                                            <div class="d-flex justify-content-end align-items-center flex-nowrap">


                                                <a data-toggle="modal" onclick="editReferalResourcePopup({{$v->id}});"
                                                    data-target="#editReferalResource" data-placement="bottom"
                                                    href="javascript:;">
                                                    <i class="fas fa-pencil-alt text-black-50 m-2"></i>
                                                </a>

                                                <a data-toggle="modal" onclick="deleteStatusFunction({{$v->id}});"
                                                    data-target="#deleteReferalResource" data-placement="bottom"
                                                    href="javascript:;">
                                                    <i class="fas fa-trash text-black-50"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>

                    </div>
                    <div class="tab-pane fade" id="Statuses" role="tabpanel" aria-labelledby="profile-basic-tab">
                        <div id="lead-referral-source-settings">
                            <div class="d-flex flex-row-reverse">
                                <a data-toggle="modal" onclick="addStatus();"
                                    data-target="#addStatus" data-placement="bottom" href="javascript:;">
                                    <button type="button" class="btn btn-primary">Add Statuses</button></i>
                                </a>

                            </div>
                            <div class="w-100 mt-3 lead-referral-source-list-container" id="table">
                                <div class="w-100 p-3 bg-gray-200">
                                    <h4 class="font-weight-bold m-0">Statuses
                                    </h4>
                                </div>
                                <?php 
                                foreach($LeadStatus as $k=>$v){?>
                                
                                <div class="container-fluid" id="item-{{$v->id}}">
                                   
                                      <div class="py-3 lead-referral-source-row border-bottom row ">
                                        <div class="col-11 grabcursor"> <i class="fas fa-bars"></i> &nbsp;{{$v->title}}</div>
                                        <div class="col-1">
                                            <div class="d-flex justify-content-end align-items-center flex-nowrap">


                                                <a data-toggle="modal" onclick="editStatusPopup({{$v->id}});"
                                                    data-target="#editStatus" data-placement="bottom"
                                                    href="javascript:;">
                                                    <i class="fas fa-pencil-alt text-black-50 m-2"></i>
                                                </a>

                                                <a data-toggle="modal" onclick="deleteStatusById({{$v->id}});"
                                                    data-target="#deleteStatus" data-placement="bottom"
                                                    href="javascript:;">
                                                    <i class="fas fa-trash text-black-50"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="DidNotHireReasons" role="tabpanel" aria-labelledby="contact-basic-tab">
                        <div id="lead-referral-source-settings">
                            <div class="d-flex flex-row-reverse">
                                <a data-toggle="modal" onclick="addReason();"
                                    data-target="#addReason" data-placement="bottom" href="javascript:;">
                                    <button type="button" class="btn btn-primary">Add No Hire Reasons</button></i>
                                </a>

                            </div>
                            <div class="w-100 mt-3 lead-referral-source-list-container" id="table">
                                <div class="w-100 p-3 bg-gray-200">
                                    <h4 class="font-weight-bold m-0">Did Not Hire Reasons

                                    </h4>
                                </div>
                                <?php 
                                foreach($HireReason as $k=>$v){?>
                                
                                <div class="container-fluid" id="item-{{$v->id}}">
                                   
                                      <div class="py-3 lead-referral-source-row border-bottom row ">
                                        <div class="col-11 grabcursor">{{$v->title}}</div>
                                        <div class="col-1">
                                            <div class="d-flex justify-content-end align-items-center flex-nowrap">


                                                <a data-toggle="modal" onclick="editReason({{$v->id}});"
                                                    data-target="#editReason" data-placement="bottom"
                                                    href="javascript:;">
                                                    <i class="fas fa-pencil-alt text-black-50 m-2"></i>
                                                </a>

                                                <a data-toggle="modal" onclick="deleteReason({{$v->id}});"
                                                    data-target="#deleteReason" data-placement="bottom"
                                                    href="javascript:;">
                                                    <i class="fas fa-trash text-black-50"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="addReferalResource" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Add Lead Referral Source</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="addReferalResourceArea">
                            </div>
                        </div>
                    </div><!-- end of main-content -->
                </div>
            </div>
        </div>
    </div>
    <div id="editReferalResource" class="modal fade " tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Edit Lead Referral Source</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="editReferalResourceArea">
                            </div>
                        </div>
                    </div><!-- end of main-content -->
                </div>
            </div>
        </div>
    </div>

    <div id="deleteReferalResource" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog">
            <form class="deleteStatusForm" id="deleteStatusForm" name="deleteStatusForm" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">Delete Lead Referral Source</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <span id="response"></span>
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Are you sure you want to delete this referral source?
                                </h6>
                                <input type="hidden" name="referral_id" id="status_id">
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-12  text-center">
                            <div class="loader-bubble loader-bubble-primary" id="innerLoader3" style="display: none;">
                            </div>
                            <div class="form-group row float-right">
                                <a href="#">
                                    <button class="btn btn-secondary  m-1" type="button"
                                        data-dismiss="modal">Cancel</button>
                                </a>
                                <button class="btn btn-primary ladda-button example-button m-1" id="submit"
                                    type="submit">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div id="addStatus" class="modal fade   " tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Add Status</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="addStatusArea">
                            </div>
                        </div>
                    </div><!-- end of main-content -->
                </div>
            </div>
        </div>
    </div>
    
    <div id="editStatus" class="modal fade " tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Edit Status</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="editStatusArea">
                            </div>
                        </div>
                    </div><!-- end of main-content -->
                </div>
            </div>
        </div>
    </div>
    
    <div id="deleteStatus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog">
            <form class="deleteStatusForm" id="deleteStatusForm" name="deleteStatusForm" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">Status</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <span id="response"></span>
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Before you delete this status you must move any leads associated with this status to a different status.
                                </h6>
    
                                <p>Do you wish to proceed?</p>
                                <input type="hidden" name="status_id" id="status_id">
                            </div>
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-12  text-center">
                            <div class="loader-bubble loader-bubble-primary" id="innerLoader3" style="display: none;"></div>
                            <div class="form-group row float-right">
                                <a href="#">
                                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                                </a>
                                <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit">Delete Status</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div id="addReason" class="modal fade   " tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Add No Hire Reason</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="addReasonArea">
                            </div>
                        </div>
                    </div><!-- end of main-content -->
                </div>
            </div>
        </div>
    </div>
    
    <div id="editReason" class="modal fade " tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Edit  No Hire Reason</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="editReasonArea">
                            </div>
                        </div>
                    </div><!-- end of main-content -->
                </div>
            </div>
        </div>
    </div>
    
    <div id="deleteReason" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog">
            <form class="deleteReasonForm" id="deleteReasonForm" name="deleteReasonForm" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">Delete No Hire Reason</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <span id="response"></span>
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Are you sure you want to delete this No Hire Reason?  </h6>
    
                              
                                <input type="hidden" name="reason_d" id="reason_d">
                            </div>
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-12  text-center">
                            <div class="loader-bubble loader-bubble-primary" id="innerLoader3" style="display: none;"></div>
                            <div class="form-group row float-right">
                                <a href="#">
                                    <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                                </a>
                                <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit">Delete Status</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {{-- Lead referral source was removed --}}
    <style>
            /* cursor: -webkit-grab; */
    .grabcursor{
        cursor: grab;
    }
    </style>

    @section('page-js')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#deleteStatusForm').submit(function (e) {
                $("#submit").attr("disabled", true);
                $("#innerLoader").css('display', 'block');
                e.preventDefault();

                if (!$('#deleteStatusForm').valid()) {
                    $("#innerLoader").css('display', 'none');
                    $('#submit').removeAttr("disabled");
                    return false;
                }
                var dataString = '';
                dataString = $("#deleteStatusForm").serialize();
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/lead_setting/deleteReferalSource", // json datasource
                    data: dataString,
                    beforeSend: function (xhr, settings) {
                        settings.data += '&delete=yes';
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
                            window.location.reload();
                        }
                    }
                });
            });

            $('#deleteReasonForm').submit(function (e) {
                $("#submit").attr("disabled", true);
                $("#innerLoader").css('display', 'block');
                e.preventDefault();

                if (!$('#deleteReasonForm').valid()) {
                    $("#innerLoader").css('display', 'none');
                    $('#submit').removeAttr("disabled");
                    return false;
                }
                var dataString = '';
                dataString = $("#deleteReasonForm").serialize();
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/lead_setting/deleteReason", // json datasource
                    data: dataString,
                    beforeSend: function (xhr, settings) {
                        settings.data += '&delete=yes';
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
                            window.location.reload();
                        }
                    }
                });
            });
            $('#table').sortable({
               
                update: function (event, ui) {
                    var datass = $(this).sortable('serialize');
                    $.ajax({
                        type: "POST",
                        url: baseUrl + "/leads/reorderStages",
                        data: datass,
                        success: function (res) {
                            // window.location.reload();
                        }
                    });
                }
            });
            var currentTab=localStorage.getItem("activeTab");
            var tab = $("#"+currentTab).attr('href');
            $("#"+currentTab).trigger("click");
         
        });

        function addReferalResourcePopup() {
            $("#addReferalResourceArea").html('<img src="{{LOADER}}""> Loading...');;
            $(function () {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/lead_setting/addReferalSource", // json datasource
                    data: '',
                    success: function (res) {
                        $("#addReferalResourceArea").html(res);
                    }
                })
            })
        }

        function editReferalResourcePopup(id) {
            $("#editReferalResourceArea").html('<img src="{{LOADER}}""> Loading...');;
            $(function () {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/lead_setting/editReferalResource", // json datasource
                    data: {
                        'id': id
                    },
                    success: function (res) {
                        $("#editReferalResourceArea").html(res);
                    }
                })
            })
        }

        function deleteStatusFunction(id) {
            $("#status_id").val(id);
        }


        function addStatus() {
            $("#addStatusArea").html('<img src="{{LOADER}}""> Loading...');
            $(function () {
                $.ajax({
                    type: "POST",
                    url:  baseUrl +"/leads/addStatus", // json datasource
                    data: 'loadStep1',
                    success: function (res) {
                    $("#addStatusArea").html(res);
                    }
                })
            })
        }
        function editStatusPopup(id) {
            $("#editStatusArea").html('<img src="{{LOADER}}""> Loading...');
            $(function () {
                $.ajax({
                    type: "POST",
                    url:  baseUrl +"/leads/editStatus", // json datasource
                    data: {'status_id':id},
                    success: function (res) {
                    $("#editStatusArea").html(res);
                    }
                })
            })
        }
        function deleteStatusById(id) {
            $("#delete_status_id").val(id);
        }

        function callTab(id){
            localStorage.setItem("activeTab", id);    
        }
        function addReason() {
            $("#addReasonArea").html('<img src="{{LOADER}}""> Loading...');
            $(function () {
                $.ajax({
                    type: "POST",
                    url:  baseUrl +"/lead_setting/addReason", // json datasource
                    data: 'loadStep1',
                    success: function (res) {
                    $("#addReasonArea").html(res);
                    }
                })
            })
        }
        function editReason(id) {
            $("#preloader").show();
            $("#editReasonArea").html('');
            $(function () {
                $.ajax({
                    type: "POST",
                    url:  baseUrl +"/lead_setting/editReason", // json datasource
                    data: {'id':id},
                    success: function (res) {
                    $("#editReasonArea").html(res);
                        $("#preloader").hide();
                    }
                })
            })
        }
        function deleteReason(id) {
            $("#reason_d").val(id);
        }

    </script>
    @stop
    @endsection
