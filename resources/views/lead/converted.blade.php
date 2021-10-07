@extends('layouts.master')
@section('title', 'Leads')
@section('main-content')
@include('lead.lead_submenu')

<?php 
$ld='';
if(isset($_GET['ld'])){
    $ld= $_GET['ld'];
}

?>
<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body"  >
                <span id="responseMain"></span>
                @include('lead.mainMenu')
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <div class="row pl-4 pb-4">
                        <div class="col-md-3 form-group mb-3">
                            <select id="ld" name="ld" class="form-control custom-select col dropdownInner">
                                <option value="">Select a Lead</option>
                                <?php 
                                foreach($allLeadsDropdown as $kcs=>$vcs){?>
                                <option <?php if($ld==$vcs->id){ echo "selected=selected"; }?>  value="{{$vcs->id}}">{{$vcs->created_by_name}}</option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-6 ml-auto">
                            <div class="d-flex justify-content-end mb-2 d-print-none">
                                <span class="my-2 mr-1">
                                    <small class="text-muted mx-1">Text Size</small>
                                    <button type="button" arial-label="Decrease text size" data-testid="dec-text-size" class="btn-sm py-0 px-1 mx-1 btn btn-outline-light decrease ">
                                        <i class="fas fa-minus fa-xs"></i>
                                    </button>
                                    <button type="button" arial-label="Increase text size" data-testid="inc-text-size" class="btn-sm py-0 px-1 mx-1 btn btn-outline-light increase" >
                                        <i class="fas fa-plus fa-xs"></i>
                                    </button>
                                </span>
                            <a href={{route('leads/exportConvertedLead')}}>
                                <button type="button" id="opportunity-export-csv-button"  class="btn btn-info btn-rounded">
                                    <div class="d-flex align-items-center"><span>Export CSV</span></div>
                                </button></a>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive" id="tavble">
                    <table class="display table table-striped table-bordered"  border="1" cellpadding="3" id="employee-grid" style="width:100%;border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th width="1%">id</th>     
                                <th width="1%"></th>
                                <th width="30%">Name</th>
                                <th width="10%">Source</th>
                                <th width="10%">Practice Area</th>
                                <th width="5%">Value</th>
                                <th width="10%">Assign To</th>
                                <th width="10%" class="text-center">Date Added</th>
                                <th width="10%" class="text-center">Converted At</th>
                                <th width="15%" class="text-center">Referred By</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="printArea">
</div>
<div id="AddCaseModel" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Case 78</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!--  SmartWizard html -->
                        <div id="smartwizard">
                            <ul>
                                <li class="text-center"><a href="#step-1">1<br /><small>Convert Lead to Client </small></a></li>
                                <li class="text-center"><a href="#step-2">2<br /><small>Case Details</small></a></li>
                                <li class="text-center"><a href="#step-3">3<br /><small>Billing</small></a></li>
                                <li class="text-center"><a href="#step-4">4<br /><small>Staff</small></a>
                                </li>
                            </ul>
                            <div>
                                <div id="step-1">
                                    
                                </div>
                                <div id="step-2">
                                
                                </div>
                                <div id="step-3">
                                

                                </div>
                                <div id="step-4">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<div id="changeSource" class="modal fade" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Update Source</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="changeSourceArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
@include('lead.commonPopup')
<div id="doNotHire" class="modal fade" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Are you sure you want to mark this lead as no hire?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="doNotHireArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<div id="editLead" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Lead</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="editLeadArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>


<div id="assignLead" class="modal fade" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Assign Lead</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="assignBulkLead">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="changeStatusBulk" class="modal fade" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Change Status</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="changeStatusBulkArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="doNotHireBulk" class="modal fade" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Are you sure you want to mark this lead as no hire?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="doNotHireBulkArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="deleteBulkLead" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog  modal-lg">
        <form class="DeleteBulkLeadForm" id="DeleteBulkLeadForm" name="DeleteBulkLeadForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Lead</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <span id="response"></span>
                    <div class="row">
                        <div class="col-md-12">
                            <p class="font-weight-bold">Are you sure you want to delete this lead?</p>
                            <p>Deleting this lead will also permanently delete all of the following items associated
                                with this lead and their
                                potential case:</p>
                            <ul>
                                <li>Events</li>
                                <li>Notes</li>
                                <li>Intake Forms</li>
                                <li>Documents (both signed and unsigned documents)</li>
                                <li>Tasks</li>
                                <li>Invoices and all associated payment activity</li>
                            </ul>
                            <div class="alert alert-info show" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="w-100">Leads with recorded {{config('app.name')}} credit card or check transaction
                                        cannot be deleted</div>
                                </div>
                            </div>
                            <input type="hidden" name="user_id" id="delete_lead_id">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-12  text-center">
                        <div class="loader-bubble loader-bubble-primary" id="innerLoader" style="display: none;"></div>
                        <div class="form-group row float-right">
                            <a href="#">
                                <button class="btn btn-secondary  m-1" type="button"
                                    data-dismiss="modal">Cancel</button>
                            </a>
                            <button class="btn btn-primary ladda-button example-button m-1" id="submit"
                                type="submit">Delete Lead</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('page-js')

<script type="text/javascript">
    localStorage.setItem("currentRow","");
    if(localStorage.getItem("convertedLead")==""){
        localStorage.setItem("convertedLead","13");
    }


    $(document).ready(function () {
        $(".dropdownInner").select2({
            placeholder: "Select a lead",
            theme: "classic",
            allowClear: true
        });
        
        $('button').attr('disabled',false);
        
        "use strict";
        // Toolbar extra buttons
        var btnFinish = $('<button></button>').text('Finish')
            .addClass('btn btn-info')
            .on('click', function () { alert('Finish Clicked'); });
        var btnCancel = $('<button></button>').text('Cancel')
            .addClass('btn btn-danger')
            .on('click', function () { $('#smartwizard').smartWizard("reset"); });
            
  
        // Smart Wizard
        $('#smartwizard').smartWizard({
            selected: 0,
            theme: 'default',
            transitionEffect: 'fade',
            showStepURLhash: false,
            enableURLhash: false,
            backButtonSupport: true, // Enable the back button support
            keyNavigation: false,
            toolbarSettings: {
                toolbarPosition: 'none',
                toolbarButtonPosition: 'end',
                toolbarExtraButtons: [btnFinish, btnCancel]
            },
            anchorSettings: {
                anchorClickable: false, // Enable/Disable anchor navigation
                enableAllAnchors: false, // Activates all anchors clickable all times
                markDoneStep: true, // Add done state on navigation
                markAllPreviousStepsAsDone: true, // When a step selected by url hash, all previous steps are marked done
                removeDoneStepOnNavigateBack: false, // While navigate back done step after active step will be cleared
                enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
            },
            
        });
        $('#with-input').on('click', function () {
            // swal({
            //     title: "Update Referral Source",
            //     text: "",
            //     input: "select",
            //     inputOptions:  <?php echo json_encode($ReferalResource); ?>,
            //     showCancelButton: true,
            //     closeOnConfirm: false,
            //     inputPlaceholder: "Write something"
            // }).then(function (inputValue) {
            //     if (inputValue != "") {
            //         $.ajax({
            //             type: "POST",
            //             url: baseUrl + "/case_stages/saveCaseStages",
            //             data: {
            //                 "stage_name": inputValue
            //             },
            //             success: function (res) {
            //                 $("#preloader").hide();
            //                 dataTable.ajax.reload();
            //             }
            //         });
                
            //     }
            // });
        });
        var dataTable =  $('#employee-grid').DataTable( {
        serverSide: true,
        responsive: false,
        processing: true,
        searching:false,
        stateSave: true,
        "order": [[0, "desc"]],
        "ajax":{
            url :"loadConverted", // json datasource
            type: "post",  // method  , by default get
            data :{ "id": '{{$ld}}'},
            error: function(){  // error handling
                $(".employee-grid-error").html("");
                $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                $("#employee-grid_processing").css("display","none");
            }
        },
        "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
        pageResize: true,  // enable page resize
        pageLength:{{USER_PER_PAGE_LIMIT}},
        columns: [
            { data: 'id'},  
            { data: 'id','orderable': false},
            { data: 'id'},
            { data: 'id' }, 
            { data: 'id'},
            { data: 'id'},
            { data: 'id' }, 
            { data: 'id'},
            { data: 'id','orderable': false},
            { data: 'id','orderable': false},],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                $('td:eq(0)', nRow).html('<div class="text-center"><i class="fas fa-2x fa-user text-black-50 ml-1"></i></div>');
               
                var name=aData.created_by_name;
                var email=aData.email;
                var mobile_number=aData.mobile_number;
                var note=aData.notes;

                // var namedata='<div class="name-cell"><a href="'+baseUrl+'/leads/'+aData.user_id+'">'+name+'</a>';
                var namedata='<div class="name-cell">'+name+'';
                if(mobile_number!=null){
                    namedata+='<div class="row ml-1"><i aria-hidden="true" class="fa fa-phone icon-phone icon col- mt-1 pl-0" style="opacity: 0.6; width: 20px;"></i><span class="col-10 p-0">'+mobile_number+'</span></div>';
                }
                if(email!=null){
                    // namedata+='<div class="row ml-1"><i aria-hidden="true" class="fa fa-envelope icon-envelope-o icon col- mt-1 pl-0" style="opacity: 0.6; width: 20px;"></i><a class="col-10 p-0 text-truncate" href="mailto:'+email+'">'+email+'</a></div>';
                    namedata+='<div class="row ml-1"><i aria-hidden="true" class="fa fa-envelope icon-envelope-o icon col- mt-1 pl-0" style="opacity: 0.6; width: 20px;"></i>'+email+'</div>';
                }
                if(note!=null){
                    namedata+=' <a data-toggle="collapse" data-target="#collapseExampleArea'+aData.id+'" href="#collapseExampleArea'+aData.id+'" aria-expanded="false"><div class="expand_caret caret"></div> &nbsp;<b>Details</b></a><div id="collapseExampleArea'+aData.id+'" class="collapse border-left"><p>'+aData.notes+'</p></div>';
                }
                namedata+='</div>';
                $('td:eq(1)', nRow).html('<div class="text-left">'+namedata+'</div>');
                
                if(aData.res_title!=null){
                    var refTitle= aData.res_title +'   <a data-toggle="modal"  data-target="#changeSource" data-placement="bottom" href="javascript:;"  onclick="changeSource('+aData.id+','+aData.referal_source+');"><i class="fas fa-pen fa-sm text-black-50 c-pointer pl-1 cursor-pointer"></i></a>';
                }else{
                    var refTitle='';
                 }
                $('td:eq(2)', nRow).html('<div class="d-flex align-items-center">'+refTitle+'</div>');

               
                if(aData.practice_area_title!=null){
                    var practice_area_title=aData.practice_area_title;
                }else{
                    var practice_area_title='';
                }
                $('td:eq(3)', nRow).html('<div class="text-left">'+practice_area_title+'</div>');

                if(aData.potential_case_value!=''){
                    var s=aData.potential_case_value;
                    var g=s.toString();
                   var potential_case_value="$"+ parseFloat(g).toFixed(2);
                }else{
                    var potential_case_value='';
                }
                $('td:eq(4)', nRow).html('<div class="text-left">'+potential_case_value+'</div>');
                // // $('td:eq(7)', nRow).html('<div class="text-left">'+aData.assign_to+'</div>');
                $('td:eq(5)', nRow).html('<div class="text-left"><a class="name" title="'+aData.assign_to_title+'" href="'+baseUrl+'/contacts/clients/'+aData.user_id+'">'+aData.assign_to+'</a></div>');

                $('td:eq(6)', nRow).html('<div class="text-left">'+aData.added_date_sort+'</div>');
                $('td:eq(7)', nRow).html('<div class="text-left">'+aData.conveted_date+'</div>');
                $('td:eq(8)', nRow).html('<div class="text-left"><a class="name"  title="'+aData.refered_by_name_title+'" href="'+baseUrl+'/contacts/clients/'+aData.user_id+'">'+aData.refered_by_name+'</a></div>');
  
                        
            },
            "initComplete": function(settings, json) {
                $('td').css('font-size',parseInt(localStorage.getItem("activeLead"))+'px');  

            }
        });

     
        //Close the popup and reload the datatable via ajax
        $('#changeSource,#doNotHire,#deleteBulkLead').on('hidden.bs.modal', function () {
            dataTable.ajax.reload(null, false);
        });

        $('#actionbutton').attr('disabled', 'disabled');
        $('.dropdownInner').change(function() {
            this.form.submit();
        });

       
        var originalSize = $('td').css('font-size');        
        var currentSize=localStorage.getItem("convertedLead");
        $('td').css('font-size', currentSize);    
        //Increase the font size 
        $(".increase").click(function(){         
            modifyFontSize('increase');  
        });     
        
        //Decrease the font size
        $(".decrease").click(function(){   
            
            modifyFontSize('decrease');  
        });  

         
       
    });

    function modifyFontSize(flag) {  
        var min = 13;
        var max = 19;
        var divElement = $('td');  
        var currentFontSize = parseInt(divElement.css('font-size'));  

        if (flag == 'increase')  
            currentFontSize += 3;  
        else if (flag == 'decrease')  
            currentFontSize -= 3;  
        else  
            currentFontSize = 13;  
            if(currentFontSize>=min && currentFontSize<=max){
            divElement.css('font-size', currentFontSize); 
            localStorage.setItem("convertedLead",currentFontSize);
        }
    }  

    function loadStep1(id) {
        $("#preloader").show();
        $("#step-1").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/loadStep1", // json datasource
                data: {"id":id},
                success: function (res) {
                    $("#step-1").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }



    function changeSource(id,referal_source) {
        $("#preloader").show();
        $("#changeSourceArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/lead_setting/changeReferalResource", // json datasource
                data: {'id':id,
                'referal_source':referal_source},
                success: function (res) {
                    $("#changeSourceArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function doNotHire(id) {
        $("#preloader").show();
        $("#doNotHireArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/lead_setting/doNotHire", // json datasource
                data: {'id':id},
                success: function (res) {
                    $("#doNotHireArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function editLead(id) {
        $("#preloader").show();
        $("#editLeadArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/editLead", // json datasource
                data: {'id':id},
                success: function (res) {
                $("#editLeadArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function assignLead() {
        $("#assignLead").modal();
        $("#preloader").show();
        $("#assignBulkLead").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/loadAssignPopup", // json datasource
                data: {'id':''},
                success: function (res) {
                
                    $("#assignBulkLead").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    //Call when performed status change for bulk action
    function changeBulkStatus() {
        $("#changeStatusBulk").modal();
        $("#preloader").show();
        $("#changeStatusBulkArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/loadChangeBulkStatus", // json datasource
                data: {'id':''},
                success: function (res) {
                
                    $("#changeStatusBulkArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    //Call when performed status dont hire change for bulk action
    function doNotHireBulk() {
        $("#doNotHireBulk").modal();
        $("#preloader").show();
        $("#doNotHireBulkArea").html('');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/loadChangeBulkDonothire", // json datasource
                data: {'id':''},
                success: function (res) {
                
                    $("#doNotHireBulkArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    //Delete bulk lead
    function deleteBulkLead() {
        $("#deleteBulkLead").modal();
    }

    $('#DeleteBulkLeadForm').submit(function (e) {
        $("#submit").attr("disabled", true);
        $("#innerLoader").css('display', 'block');
        e.preventDefault();

        if (!$('#DeleteBulkLeadForm').valid()) {
            $("#innerLoader").css('display', 'none');
            $('#submit').removeAttr("disabled");
            return false;
        }
        var dataString = '';
        dataString = $("#DeleteBulkLeadForm").serialize();
        var array = [];
        $("input[class=leadRow]:checked").each(function (i) {
            array.push($(this).val());
        });
        $.ajax({
            type: "POST",
            url: baseUrl + "/leads/saveDeleteBulkLead", // json datasource
            data: dataString + '&leads_id=' + JSON.stringify(array),
            beforeSend: function (xhr, settings) {
                settings.data += '&save=yes';
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
                    toastr.success('Your leads have been updated.', "", {
                        progressBar: !0,
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
                    $("#deleteBulkLead").modal("hide");
                }
            }
        });
    });

    function printData()
    {
        var f=$("#tavble").html();
        $("#printArea").append('<h4>Conveted Leads</h4>');
        $("#printArea").append(f);
        $(".dataTables_length,.dataTables_paginate, .dataTables_info").remove();
        var divContents = document.getElementById("printArea").outerHTML;
        var a = window.open('');
        a.document.write('<html><body>');
        a.document.write(divContents);
        a.document.write('</body></html>');
        a.document.close();
        a.print();

    }

    function closePrintView() { //this function simply runs something you want it to do
        $("#printArea").hide();
        document.location.reload(); //in this instance, I'm doing a re-direct

    }
</script>
<style>
    .expand_caret {
        transform: scale(1.6);
        margin-left: 8px;
        margin-top: -4px;
    }
    a[aria-expanded='false'] > .expand_caret {
        transform: scale(1.6) rotate(-90deg);
    }
    .caret {
        display: inline-block;
        width: 0;
        height: 0;
        margin-left: 2px;
        vertical-align: middle;
        border-top: 4px dashed;
        border-top: 4px solid\9;
        border-right: 4px solid transparent;
        border-left: 4px solid transparent;
    }
    .table-info, .table-info>td, .table-info>th {
        background-color: #b8cad8;
    }

    .sw-theme-circles > ul.step-anchor > li > a {
	border: 2px solid #f5f5f5;
	background: #f5f5f5;
	width: 50px;
	height: 50px;
	text-align: center;
	padding: 25px 0;
	border-radius: 50%;
	-webkit-box-shadow: inset 0px 0px 0px 3px #fff !important;
	box-shadow: inset 0px 0px 0px 3px #fff !important;
	text-decoration: none;
	outline-style: none;
	z-index: 99;
	color: #bbb;
	background: #f5f5f5;
	line-height: 1;
}

</style>
@stop
