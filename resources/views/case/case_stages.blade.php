@extends('layouts.master')
@section('title', "Case Stages")
@section('main-content')
<div class="breadcrumb">
    <h3>Case Stages</h1>
</div>
<div class="separator-breadcrumb border-top"></div>
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
                        <p> Manage your case stages. Click Edit Stages to create or reorder stages. Learn more. </p>
                    </div>

                    {{-- <div class="col-sm-1">
                        <button class="btn btn-primary" type="button" id="button" onClick="reorderStage()">Edit Stages</button>
                    </div> --}}
                    <div class="col-sm-4">
                        <button class="btn btn-primary mr-4" type="button" id="with-input">Add
                            New
                            Stages</button>
                        <button class="btn btn-secondry" type="submit">Tell us what you
                            think</button>
                    </div>
                </div>
                <br>
                <div id="stageAreaReload">
                    
                </div>
                <hr>
                <div class="customizable-list-tooltip view-tooltip enabled"
                    data-tooltip-name="tooltip_view_case_stages">
                    <div class="case-stages-view-tooltip text-center">
                        <p class="pb-3">
                            <strong>Organize your cases into stages so you can easily track them through their
                                lifecycle.</strong>
                        </p>
                        <p class="pb-3">
                            Create the list of stages that make sense for your firm.<br>
                            <strong>Pro Tip:</strong> Prefix your stages with a practice area code to organize your list
                            of stages by practice area (E.g. "PI - In Trial" or "Divorce - On Hold").
                        </p>
                        <p>
                            <a href="#" target="_blank">Learn more about Case Stages.</a>
                        </p>
                        <div class="mb-4">
                            <img alt="" src="https://assets.mycase.com/packs/empty-state/case-stages-with-practice-area-43333b997e.png">
                        </div>
                        <a href="#" class="dismiss-link">Dismiss and don't show again</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="EditCaseStageModel" class="modal fade bd-example-modal-lm show" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Edit Case Stage</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="caseStageEditArea">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('page-js')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#with-input').on('click', function () {
                swal({
                    title: "Add New Stage Name",
                    text: "",
                    input: "text",
                    showCancelButton: true,
                    closeOnConfirm: false,
                    inputPlaceholder: "E.g. Discovery",
                    confirmButtonText: 'Save',
                    cancelButtonText: "Cancel",
                    confirmButtonClass: 'btn btn-success  mr-2',
                    cancelButtonClass: 'btn btn-danger  mr-2',
                    reverseButtons: true
                }).then(function (inputValue) {
                    // if (inputValue === false) return false;
                    if (inputValue != "") {
                        $.ajax({
                            type: "POST",
                            url: baseUrl + "/case_stages/saveCaseStages",
                            data: {
                                "stage_name": inputValue
                            },
                            success: function (res) {
                                $("#groupModel").html(res);
                                $("#preloader").hide();
                                reloadCaserStages();
                                toastr.success('New stage name has been added.', "", {
                                progressBar: !0,
                                positionClass: "toast-top-full-width",
                                containerId: "toast-top-full-width"
                            });
                                // window.location.reload();
                            }
                        });
                       
                    }
                    // swal("Awesome!", "You wrote: " + inputValue, "success");
                });
            });

            $('#EditCaseStageModel').on('hidden.bs.modal', function () {
                reloadCaserStages();
            });
        });

        function deleteStage(id) {
            swal({
                title: 'Are you sure?',
                text: "Are you sure you want to delete this stage?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0CC27E',
                cancelButtonColor: '#FF586B',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'No, cancel!',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger  mr-5',
                buttonsStyling: false,
                reverseButtons: true
            }).then(function () {
                $(function () {
                    $.ajax({
                        type: "POST",
                        url: baseUrl + "/case_stages/deleteCaseStages",
                        data: {
                            "id": id
                        },
                        success: function (res) {
                            $("#preloader").hide();
                            reloadCaserStages();
                            toastr.success('Stage name has been deleted.', "", {
                                progressBar: !0,
                                positionClass: "toast-top-full-width",
                                containerId: "toast-top-full-width"
                            });
                            // window.location.reload();
                        }
                    });
                });

            }, function (dismiss) {

            });
        }

        function editCaseStageDetails(id) {  
            $("#preloader").show();
            $(function () {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/case_stages/editCaseStage", // json datasource
                    data: {'id':id},
                    success: function (res) {
                        $("#caseStageEditArea").html('');
                        $("#caseStageEditArea").html(res);
                        $("#preloader").hide();
                        reloadCaserStages();
                    }
                })
            })
        }
        function reloadCaserStages() {  
            $("#stageAreaReload").html('<img src="{{LOADER}}""> Loading...');
            $(function () {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/case_stages/reloadCaserStages", // json datasource
                    data: {},
                    success: function (res) {
                        $("#stageAreaReload").html('');
                        $("#stageAreaReload").html(res);
                        $("#preloader").hide();
                    }
                })
            })
        }
        reloadCaserStages();

        
    </script>
    @stop
    @endsection
