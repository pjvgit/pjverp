@extends('layouts.master')
@section('title', 'Contacts & Companies - Import/Export')
@section('main-content')
<?php
$timezoneData = unserialize(TIME_ZONE_DATA); 
?>
<div class="breadcrumb">
    <h3>Settings & Preferences</h1>

</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-md-2">
        @include('layouts.submenu')
    </div>
    <div class="col-md-10">
        <div class=" mb-4 o-hidden">
            <div class="card-body">
                <div class="row">
                    <ul class="nav nav-tabs w-100" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('imports/contacts') ? 'active show' : '' }} "
                                id="profile-basic-tab" href="{{ route('imports/contacts') }}" aria-controls="tba2"
                                aria-selected="true">Contacts & Companies</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('imports/court_cases') ? 'active show' : '' }} "
                                id="profile-basic-tab2" href="{{ route('imports/court_cases') }}" aria-controls="tba2"
                                aria-selected="true">Cases</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('exports') ? 'active show' : '' }}" 
                                id="profile-basic-tab"  href="{{ route('exports') }}" aria-controls="tba2" 
                                aria-selected="true">Full Backup</a>
                        </li>
                    </ul>
                    <div class="tab-content w-100" id="myTabContent">
                        <div class="tab-pane fade   {{ request()->is('imports/contacts') ? 'active show' : '' }}"
                            id="tab1" role="tabpanel" aria-labelledby="profile-basic-tab">
                            <div class="m-2">
                                <div class="d-flex align-items-center flex-row-reverse mb-2">
                                    <a class="btn btn-primary ml-1" data-toggle="modal" data-target="#importContacts"
                                        data-placement="bottom" href="javascript:;">Import
                                        Contacts</a>

                                    <a data-toggle="modal" data-target="#exportContacts" data-placement="bottom"
                                        href="javascript:;">
                                        <button class="btn btn-outline-secondary m-1" type="button">Export
                                            Contacts</button>
                                    </a>

                                </div>

                                <div class="mb-2">
                                    Import contacts from other legal practice management software using our Contact
                                    Import Spreadsheet
                                    (<a href="javascript:void(0);" onclick="downloadFile('contact')">download template</a>)
                                    or our Company Import Spreadsheet
                                    (<a href="javascript:void(0);" onclick="downloadFile('company')">download template</a>).
                                    Or, easily import your contacts from
                                    <a href="javascript:void(0);" target="_blank">Outlook</a> or
                                    <a href="javascript:void(0);" target="_blank">Google/Gmail</a>.
                                    Learn more about
                                    <a href="javascript:void(0);" target="_blank">importing contacts</a>.
                                </div>

                                <div id="import_page_list">

                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="display table table-striped table-bordered" id="importExportHistorty" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>id</th>
                                            <th>File</th>
                                            <th>Uploaded</th>
                                            <th>Status</th>
                                            <th><span class="sr-only">Actions</span></th>
                                          </tr>
                                    </thead>
                                </table>
                                
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</div>
<div id="importContacts" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Import Contacts</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form class="importContactWithOptions" id="importContactWithOptions"
                            name="importContactWithOptions" method="POST" enctype="multipart/form-data">
                            <span id="response"></span>
                            @csrf
                            <div id="showError" class="showError" style="display:none"></div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Format</label>
                                    <div class="col-sm-8">
                                        <input autocomplete="off" type="radio" value="vcf" name="import_format"
                                            id="import_import_format_vcard_vcf">
                                        vCard (Google, Mac Address Book)<br>
                                        <input autocomplete="off" id="outlook_csv" type="radio" value="csv"
                                            name="import_format">
                                        CSV (including Outlook)
                                        <br>
                                        <label for="import_format" class="error" style="display:none;">Please choose one.</label>                                                     
                                    </div>                   
                                </div>
                                <br>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Option</label>
                                    <div class="col-sm-8">
                                        <div style="line-height: 1.5em;" id="fileupload-dropzone">
                                            <input autocomplete="off" type="file" name="upload_file" id="upload_file">
                                        </div>
                                        <span id="UserTypeError"></span>
                                        <br>
                                        After uploading, your import file will be placed in a queue<br> and processed
                                        within a few minutes.
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-8 text-right">
                                    <div class="loader-bubble loader-bubble-primary innerLoader"></div>
                                </div>
                                <div class="col-sm-4 text-right">
                                    <button class="btn btn-primary ladda-button example-button m-1 submit"
                                        id="submitButton" type="submit">Import Contacts</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="confirmRevert" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Confirm Undo Import</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form class="revertContactForm" id="revertContactForm"
                            name="revertContactForm" method="POST" enctype="multipart/form-data">
                            <span id="response"></span>
                            @csrf
                            <div id="showError" class="showError" style="display:none"></div>
                            <div class="col-md-12">
                            <input type="hidden" name="import_id" id="import_id">
        Are you sure you want to undo this import?  Any work that has been done with contacts in this import (for example invoices and payments) will no longer be linked to these contacts. <span class="font-weight-bold">
            <span id="countSuccess">0</span> contacts will be deleted.
          </span>
      
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 text-right">
                                    <div class="loader-bubble loader-bubble-primary innerLoader"></div>
                                </div>
                                <div class="col-sm-6 text-right mt-4">
                                    <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>

                                    <button class="btn btn-primary ladda-button example-button m-1 submit"
                                        id="submitButton" type="submit">Proceed</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="showErrorLog" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Import Error Details</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="errorLogData">
                        
                    </div>
                </div><!-- end of main-content -->
            </div>

            <div class="modal-footer">
                    <button class="btn btn-secondary m-1" type="button" data-dismiss="modal">Cancel</button>
                  
            </div>
        </div>
    </div>
</div>
<style>
    .nav-tabs {
        border-bottom: 2px solid #e1e1e1;
    }
    #fileupload-dropzone {
        border: 2px dashed #666;
        background-color: #f5f5f5;
    }

</style>
@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();
        $("#importContactWithOptions").validate({
            rules: {
                upload_file: {
                    required: true
                },
                import_format: {
                    required: true
                }
            },
            messages: {
                upload_file: {
                    required: "Please select a file"
                },
                import_format: {
                    required: "Please select atleast one option."
                }
            },
            errorPlacement: function (error, element) {
                if (element.is('#upload_file')) {
                    error.appendTo('#UserTypeError');
                } else {
                    element.after(error);
                }
            }
        });
        var importExportHistorty =  $('#importExportHistorty').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"p><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/imports/loadImportHistory", // json datasource
                type: "post",  // method  , by default get
                data :{ 'c' :'c'},
                error: function(){  // error handling
                    $(".importExportHistorty-error").html("");
                    $("#importExportHistorty_processing").css("display","none");
                }
            },
            "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
            pageResize: true,  // enable page resize
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false},
                { data: 'id','sorting':false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    if(aData.file_type=="1"){
                        var f='<small class="font-italic">vCard (.vcf)</small>';
                    }else{

                        var f='<small class="font-italic">Outlook (.csv)</small>';
                    }
                    $('td:eq(0)', nRow).html('<div class="text-left">'+aData.file_name+' <br> ' + f+'</div>');
                   
                    $('td:eq(1)', nRow).html('<div class="text-left">'+aData.created_new_date+' <br> By <a class="name" href="'+baseUrl+'/contacts/attorneys/'+aData.decode_id+'">'+aData.created_by_name+'</a></div>');

                    if(aData.status=="2"){
                        var status='<div> <i class="fas fa-trash align-middle"></i><span style="color: red;" class="import-status-text" data-status="Error"> Error </span> <br> Imported 0 / </div>';
                    }else if(aData.status=="1"){
                        var status='<div> Complete <br> Imported '+aData.total_record+' / '+aData.total_imported+' ('+aData.total_warning+' warnings)	 </div>';
                    }else {
                        var status='<div> <span style=" text-decoration: line-through;" class="import-status-text" data-status="Complete">Complete <small class="font-italic">&nbsp;(Undone)</small></span><br>Removed '+aData.total_imported+' contacts on undo. </div>';
                    }
                    $('td:eq(2)', nRow).html('<div class="text-left">'+status+'</div>');

                    if(aData.status=="2"){
                        $('td:eq(3)', nRow).html('<div class="text-left"><a data-toggle="modal" data-target="#showErrorLog"  href="javascript::void(0);" onclick="errorLogData('+aData.id+');" > Error Details</a></div>');
                    }else if(aData.status=="1"){
                        var revert='';
                        if(aData.total_imported>0){
                            revert='<a title="" class="text-black-50 ml-1" data-placement="top" data-toggle="tooltip" href="#"  data-original-title="Undo Import" ><i class="fas fa-undo" data-toggle="modal" data-target="#confirmRevert" data-placement="bottom" href="javascript:;" onclick="openRevertBox('+aData.id+','+aData.total_imported+');"></i></a>';
                        }
                        $('td:eq(3)', nRow).html('<a target="_blank" title="" class="text-black-50" data-placement="top" data-toggle="tooltip" href="{{ url("imports")}}/'+aData.decoder+'" data-original-title="View Log"><i class="far fa-file-alt"></i> <span class="sr-only">View Log</span></a> '+revert);
                    }else{
                        $('td:eq(3)', nRow).html('<a target="_blank" title="" class="text-black-50" data-placement="top" data-toggle="tooltip" href="{{ url("imports")}}/'+aData.decoder+'" data-original-title="View Log"><i class="far fa-file-alt"></i> <span class="sr-only">View Log</span></a>');
                    }

                },
                "initComplete": function(settings, json) {
                    $('[data-toggle="tooltip"]').tooltip();
                    // setPrintHtml(json)
                }
        });
        $('#importContacts').on('hidden.bs.modal', function () {
            document.getElementById('importContactWithOptions').reset();
            window.location.reload();
            importExportHistorty.ajax.reload(null, false);
        });
        $('#exportContactWithOptions').submit(function (e) {
            $('.showError').html('');
            beforeLoader();
            e.preventDefault();
            if (!$('#exportContactWithOptions').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#exportContactWithOptions").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/imports/createAndImports", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        errotHtml += '<li>' + res.errors + '</li>';
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        $("#exportContacts").modal("hide");
                        document.getElementById('importContactWithOptions').reset();
                        $('.showError').html('');
                        swal('Success!', res.msg, 'success');
                        window.open(res.url);
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });
        $('#importContactWithOptions').submit(function (e) {
            $('.showError').html('');
            beforeLoader();
            e.preventDefault();
            if (!$('#importContactWithOptions').valid()) {
                afterLoader();
                return false;
            }
            // var dataString = $("#importContactWithOptions").serialize();
            var dataString = new FormData(this);
            $.ajax({
                type: "POST",
                url: baseUrl + "/imports/importContacts", // json datasource
                data: dataString,
                cache:false,
                contentType: false,
                processData: false,
                success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        errotHtml += '<li>' + res.errors + '</li>';
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    window.location.reload();
                }
            });
        });
        $('#revertContactForm').submit(function (e) {
            beforeLoader();
            e.preventDefault();

            if (!$('#revertContactForm').valid()) {
                beforeLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#revertContactForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/imports/revertImport", // json datasource
                data: dataString,
                beforeSend: function (xhr, settings) {
                    settings.data += '&delete=yes';
                },
                success: function (res) {
                    beforeLoader();
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><strong>Whoops!</strong> There were some problems with your input.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        return false;
                    } else {
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><strong>Whoops!</strong> There were some internal problem, Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                }
            });
        });

    });
    function errorLogData(id){
        $("#errorLogData").html('<img src="{{LOADER}}""> Loading...');
        // $("#errorLogData").html(h);
        $.ajax({
            type: "POST",
            url: baseUrl + "/imports/loadErrorData", // json datasource
            data: {
                "id": id
            },
            success: function (res) {
                $("#preloader").hide();
                $("#errorLogData").html(res);
            }
        })
    }

    function downloadFile(section) {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/imports/download_template", // json datasource
                data: {
                    "section": section
                },
                success: function (res) {
                    $("#preloader").hide();
                    window.open(res.url);
                }
            })
        })
    }

    function openRevertBox(import_id,count){
        $("#import_id").val(import_id);
        $("#countSuccess").html(count);
    }

</script>
@stop
@endsection
