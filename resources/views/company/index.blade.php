@extends('layouts.master')
@section('title', 'Company')
@section('main-content')
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
                <div class="d-flex align-items-center pl-4 pb-4">
                    <h3>Companies</h3>
                    <ul class="d-inline-flex nav nav-pills pl-4">
                        <li class="d-print-none nav-item">
                            <a href="{{route('contacts/company')}}?target=active"
                                class="nav-link {{ ($target=="active") ? 'active' : '' }} ">Active</a>
                        </li>
                        <li class="d-print-none nav-item">
                            <a href="{{route('contacts/company')}}?target=archived"
                                class="nav-link   {{ ($target=="archived") ? 'active' : '' }}">Archived</a>
                        </li>
                    </ul>
                    <div class="ml-auto d-flex align-items-center d-print-none">
                        <a data-toggle="modal"  data-target="#addCompanyModel" data-placement="bottom" href="javascript:;" > <button class="btn btn-primary btn-rounded m-1" type="button" onclick="addCompany();">Add Company</button></a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="Datatable-Grid" style="width:100%">
                        <thead>
                            <tr>
                                <th width="1%"></th>
                                <th width="2%"></th>
                                <th width="20%">Name</th>
                                <th width="30%" class="nosort">Case</th>
                                <th width="30%" class="nosort">Contact</th>
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

<div id="addCompanyModel" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Company</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="companyModel">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="EditCompany" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Company</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="EditcompanyModel">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-js')
<script type="text/javascript">
   
    $(document).ready(function() {
       
    var dataTable =  $('#Datatable-Grid').DataTable( {
        searching:false,
        serverSide: true,
        responsive: false,
        processing: true,
        stateSave: true,
        "order": [[0, "desc"]],
        "ajax":{
            url :"loadCompany", // json datasource
            type: "post",  // method  , by default get
            "data": function (d) {
                    d.tab ='{{$target}}';
            },
            error: function(){  // error handling
                $(".Datatable-Grid-error").html("");
                $("#Datatable-Grid").append('<tbody class="Datatable-Grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                $("#Datatable-Grid_processing").css("display","none");
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
            { data: 'id','orderable': false},
            { data: 'id','orderable': false},],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                $('td:eq(0)', nRow).html('<div class="text-center"><i class="fas fa-2x fa-building text-black-50"></i></div>');
                
                var companyLink='<a href="'+baseUrl+'/contacts/companies/'+aData.id+'">'+aData.first_name+'</a>';
                $('td:eq(1)', nRow).html('<div class="text-left">'+companyLink+'</div>');

                var obj = JSON.parse(aData.caselist);
                var i;
                var urlList='';
                for (i = 0; i < obj.length; ++i) {
                    urlList+='<a href="'+baseUrl+'/court_cases/'+obj[i].case_unique_number+'/info">'+obj[i].case_title+'</a>';
                    urlList+="<br>";
                }
                if(urlList==''){
                    $('td:eq(2)', nRow).html('<i class="table-cell-placeholder"></i>');
                }else{
                    $('td:eq(2)', nRow).html('<div style="white-space: nowrap;" class="text-left">'+urlList+'</div>');
                }
                
                var client_obj = JSON.parse(aData.contactlist);
                var i1;
                var clientList='';
                for (i1 = 0; i1 < client_obj.length; ++i1) {
                    clientList+='<a href="'+baseUrl+'/contacts/clients/'+client_obj[i1].cid+'">'+client_obj[i1].fullname+' (client)</a>';
                    clientList+="<br>";
                }
                if(clientList==''){
                    $('td:eq(3)', nRow).html('<i class="table-cell-placeholder"></i>');
                }else{
                    $('td:eq(3)', nRow).html('<div class="text-left">'+clientList+'</div>');
                }

                var createdbyobj = JSON.parse(aData.createdby); 
                if(createdbyobj != null){
                    var createdBy= createdbyobj.created_by_name;
                    var createdAt= createdbyobj.newFormateCreatedAt;
                    $('td:eq(4)', nRow).html('<div class="status-update"><div class="test-created-by-info">Created '+createdAt+'<small> <br>by <a class="test-created-by-link pendo-case-info-status-created-by" href="'+baseUrl+'/contacts/attorneys/'+createdbyobj.decode_user_id+'">'+createdBy+'</a></small>');
                }else{
                    $('td:eq(4)', nRow).html('Not Specified');
                }
                $('td:eq(5)', nRow).html('<a data-toggle="modal"  data-target="#EditCompany" data-placement="bottom" href="javascript:;"  onclick="EditCompany('+aData.id+');"><i class="fas fa-pen pr-3  align-middle"></i> </a>');
            },
        });
        $('#addCompanyModel').on('hidden.bs.modal', function () {
            dataTable.ajax.reload(null, false);
        });

});

function addCompany() {
    $("#companyModel").html('');
    $("#companyModel").html('<img src="{{LOADER}}""> Loading...');

    $(function () {
        $.ajax({
            type: "POST",
            url:  baseUrl +"/contacts/loadAddCompany", // json datasource
            data: 'loadStep1',
            success: function (res) {
               $("#companyModel").html(res);
                $("#preloader").hide();
            }
        })
    })
}

function EditCompany(id) {
    $("#EditcompanyModel").html('');
    $("#EditcompanyModel").html('<img src="{{LOADER}}""> Loading...');
    $(function () {
        $.ajax({
            type: "POST",
            url:  baseUrl +"/contacts/loadEditCompany", // json datasource
            data: {"id":id},
            success: function (res) {
               $("#EditcompanyModel").html(res);
                $("#preloader").hide();
            }
        })
    })
}
</script>
@stop
