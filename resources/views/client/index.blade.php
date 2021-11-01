@extends('layouts.master')
@section('title','Contacts')
@section('main-content')
@include('client.submenu')
<?php
$userTitle = unserialize(USER_TITLE); 
$target=$group="";
if(isset($_GET['target']) && $_GET['target']=="active" || $_GET['target']==''){
    $target="active";
}

if(isset($_GET['target']) && $_GET['target']=="archived" ){
    $target="archived";
}
?>
<div class="row">
    
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex align-items-center ">
                    <h3>Contacts</h3>
                    <ul class="d-inline-flex nav nav-pills pl-4">
                        <li class="d-print-none nav-item">
                            <a href="{{route('contacts/client')}}?target=active"
                                class="nav-link {{ ($target=="active") ? 'active' : '' }} ">Active</a>
                        </li>
                        <li class="d-print-none nav-item">
                            <a href="{{route('contacts/client')}}?target=archived"
                                class="nav-link   {{ ($target=="archived") ? 'active' : '' }}">Archived</a>
                        </li>
                    </ul>
                    <div class="ml-auto d-flex align-items-center d-print-none">
                        <button onclick="printEntry();return false;" class="btn btn-link">
                            <i class="fas fa-print text-black-50" data-toggle="tooltip" data-placement="top"
                                    title="" data-original-title="Print"></i>
                        </button>
                        <a class="btn btn-link pr-4 d-print-none text-black-50" rel="facebox" href="{{BASE_URL}}imports/contacts">Import Contact
                        </a>
                        <div class="ml-auto d-flex align-items-center d-print-none">
                            <a data-toggle="modal"  data-target="#AddContactModal" data-placement="bottom" href="javascript:;" > <button class="btn btn-primary btn-rounded m-1" type="button" onclick="AddContactModal();">Add Contact</button></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 form-group">
                        <label for="picker1">Contact Group</label>
                        <select id="ld" name="ld" class="form-control custom-select col dropdown_list">
                            <option value="">Show All Groups</option>
                            <?php foreach($ClientGroup as $k=>$v){ ?>
                                <option  <?php if($v->id=="0"){ echo "selected=selected"; }?>  value="{{$v->id}}">{{$v->group_name}} </option>
                           <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="table-responsive" id="printHtml">
                    <h3 id="hiddenLable">Contacts</h3>
                    <table class="display table table-striped table-bordered" id="ClientListGrid" style="width:100%">
                        <thead>
                            <tr>
                                <th width="1%"></th>
                                <th width="5%"></th>
                                <th width="10%">First Name</th>
                                <th width="10%">Last Name</th>
                                <th width="10%" class="nosort">Group</th>
                                <th width="20%" class="nosort">Cases</th>
                                <th width="10%" class="nosort">Last Login</th>
                                <th width="15%" class="nosort">Added</th>
                                <th width="5%" class="nosort"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="AddContactModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="step-1-aDD-CONTACT">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="EditContactModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Contact</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="step-2-contact">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
<div id="AddCaseModel" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!--  SmartWizard html -->
                        <div id="smartwizard">
                            <ul>
                                <li class="text-center"><a href="#step-1">1<br /><small>Clients & Contacts</small></a></li>
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
            {{-- <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary ml-2" id="next-btn" type="button">Create User</button>
            </div> --}}
        </div>
    </div>
</div>

<style> .modal { overflow: auto !important; }</style>

@endsection

@section('page-js')
<script type="text/javascript">
    $(document).ready(function() {
        ClientListGrid =  $('#ClientListGrid').DataTable( {
            searching: false,
            serverSide: true,
            responsive: false,
            processing: true,
            stateSave: true,
            "order": [[0, "desc"]],
            "ajax":{
                // data :{ 'tab' : '{{$target}}','filter_on':$("#ld option:selected").val() },
                "data": function (d) {
                    d.tab ='{{$target}}';
                    d.filter_on=$("#ld option:selected").val();
                },
                url :"loadClient", // json datasource
                type: "post",  // method  , by default get
                error: function(){  // error handling
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] },{'bSortable': false,'aTargets': ['nosort']}],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            
            columns: [
                { data: 'id'},
                { data: 'id','orderable': false},
                { data: 'first_name'},
                { data: 'last_name' }, 
                { data: 'user_title'},
                { data: 'email' },
                { data: 'first_name' },
                { data: 'id','orderable': false},
                { data: 'id','orderable': false},],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                    if(aData.profile_image!=null && aData.is_published=='yes'){
                        $('td:eq(0)', nRow).html('<div class="text-center"><img class="rounded-circle" alt="" src="{{BASE_URL}}public/profile/'+aData.profile_image+'" width="32" height="32"></div>');
                    }else{

                        $('td:eq(0)', nRow).html('<div class="text-center"><i class="fas fa-2x fa-user text-black-50"></i></div>');

                    }
                    aData.user_profile
                    $('td:eq(1)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/clients/'+aData.id+'">'+aData.first_name+'</a></div>');
                    $('td:eq(2)', nRow).html('<div class="text-left"><a class="name" href="'+baseUrl+'/contacts/clients/'+aData.id+'">'+aData.last_name+'</a></div>');


                    $('td:eq(3)', nRow).html(aData.group_name);
                                
                    // var obj = JSON.parse(aData.clientwise_caselist);
                    var obj = aData.client_cases;
                    var i;
                    var urlList='';
                    for (i = 0; i < obj.length; ++i) {
                        urlList+='<a href="'+baseUrl+'/court_cases/'+obj[i].case_unique_number+'/info">'+obj[i].case_title+'</a>';
                        urlList+="<br>";
                    }
                    if(urlList==''){
                        $('td:eq(4)', nRow).html('<i class="table-cell-placeholder"></i>');
                    }else{
                        $('td:eq(4)', nRow).html('<div class="text-left">'+urlList+'</div>');
                    }
                
                    var createdbyobj = JSON.parse(aData.createdby); 
                    if(createdbyobj != null && createdbyobj.client_portal_enable=="0"){
                        $('td:eq(5)', nRow).html('<div class="text-left">Disabled</div>'); 
                    
                    }  else{
                        if(aData.last_login==null){
                            $('td:eq(5)', nRow).html('<div class="text-center">-</div>'); 
                        }else{
                            $('td:eq(5)', nRow).html('<div class="text-left">'+aData.last_login+'</div>'); 
                        }
                    }
                
                    if(createdbyobj != null){
                        var createdBy= createdbyobj.created_by_name;
                        var createdAt= createdbyobj.newFormateCreatedAt;
                        $('td:eq(6)', nRow).html('<div class="status-update"><div class="test-created-by-info">Created '+createdAt+'<small> <br>by <a class="test-created-by-link pendo-case-info-status-created-by" href="'+baseUrl+'/contacts/attorneys/'+createdbyobj.decode_user_id+'">'+createdBy+'</a></small>');
                    }else{
                        $('td:eq(6)', nRow).html('Not Specified');
                    }
                    $('td:eq(7)', nRow).html('<a data-toggle="modal"  title="Edit" data-target="#EditContactModal" data-placement="bottom" href="javascript:;"  onclick="loadClientEditBox('+aData.id+');"><i class="fas fa-pen pr-3  align-middle d-print-none"></i> </a>'); 
                },
            });

            $('#AddContactModal,#EditContactModal').on('hidden.bs.modal', function () {
                //dataTable.ajax.reload();
                // window.location = baseUrl+"/contacts/attorneys";
            });
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

            $("#ld").on("change",function(){
                ClientListGrid.ajax.reload(null,false);
            });
        });


function loadStep1(id) {
    $("#preloader").show();
    $("#step-1").html('');
    $(function () {
        $.ajax({
            type: "POST",
            url:  baseUrl +"/case/loadStep1", // json datasource
            data: {"user_id":id},
            success: function (res) {
                $("#AddContactModal").modal('hide');
               $("#step-1").html(res);
                $("#preloader").hide();
            }
        })
    })
}

function AddContactModal() {
    $("#AddCaseModel").modal('hide');
    $("#preloader").show();
    $("#step-1-aDD-CONTACT").html('');
    $(function () {
        $.ajax({
            type: "POST",
            url:  baseUrl +"/contacts/loadAddContact", // json datasource
            data: 'loadStep1',
            success: function (res) {
               $("#step-1-aDD-CONTACT").html(res);
                $("#preloader").hide();
            }
        })
    })
}
function loadClientEditBox(id) {
    
    $("#preloader").show();
    $("#step-2-contact").html('');
    $(function () {
        $.ajax({
            type: "POST",
            url:  baseUrl +"/contacts/loadEditContact", // json datasource
            data: {"user_id":id},
            success: function (res) {
               $("#step-2-contact").html(res);
                $("#preloader").hide();
            }
        })
    })
} 

function printEntry()
{
    $('#ClientListGrid_length').hide();
    $('#ClientListGrid_info').hide();
    $('#ClientListGrid_paginate').hide();
    $('#hiddenLable').show();
    var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
    $(".main-content-wrap").remove();
    window.print(canvas);
    // w.close();
    window.location.reload();
    return false;
}
$('#hiddenLable').hide();
</script>
@stop
