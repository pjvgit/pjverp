@extends('layouts.master')
@section('title', 'Cases - Import/Export')
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
                        <div class="tab-pane fade   {{ request()->is('exports') ? 'active show' : '' }}"
                            id="tab1" role="tabpanel" aria-labelledby="profile-basic-tab2">
                            <div class="m-2">
                                @if(isset($ClientFullBackup[0]['created_at']) && date("Y-m-d",strtotime($ClientFullBackup[0]['created_at'])) == date('Y-m-d'))
                                <div class="font-italic text-muted text-center pb-2">
                                    You cannot request a new backup at this time.  LegalCase allows you to request one full backup per day.
                                </div>                           
                                @else
                                <div class="d-flex align-items-center flex-row-reverse mb-2">
                                    <a class="btn btn-primary ml-1" data-toggle="modal" data-target="#exportCourtCase"
                                        data-placement="bottom" href="javascript:;">Create Backup</a>
                                </div> 
                                @endif

                                <div class="alert alert-primary alert-dismissible fade show" role="alert">
                                    <p>
                                    When you create a full backup, we'll build a .zip file containing all of your
                                    LegalCase data so you can download and save it. You can request one full backup per day, and
                                    we'll retain your backups for seven days.  After seven days your old backups will be deleted
                                        to save space. For additional help, take a look at <a href="{{ route('ayuda') }}" target="_blank">
                                        our support pages</a> or
                                    contact us.
                                    </p>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>

                            @if(count($ClientFullBackup) == 0)
                                <div class="no_items text-center">
                                    You have not created any recent backups.<br><br>
                                    Backups that you create in LegalCase will be<br>
                                    available for download for seven days.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="display table table-striped table-bordered" id="CaseImportExportHistorty" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>id</th>
                                                <th>File</th>
                                                <th>Options</th>
                                                <th>Status</th>
                                                <th><span class="sr-only">Actions</span></th>
                                            </tr>
                                        </thead>
                                    </table>                                
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</div>
<div id="exportCourtCase" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Create Backup</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form class="backupWithOptionForm" id="backupWithOptionForm" name="backupWithOptionForm" method="POST">
                            <span id="response"></span>
                            @csrf
                            <div id="showError" class="showError" style="display:none"></div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Format</label>
                                    <div class="col-sm-8">
                                        <label for="format_mycase_csv">{{config('app.name')}} CSV</label>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Cases</label>
                                    <div class="col-sm-8">
                                        <div style="line-height: 1.5em;">
                                            <input type="radio" name="export_cases"  value="0" checked="checked">
                                            <label for="export_cases"> Only include cases I'm linked to</label><br>

                                            <input type="radio" name="export_cases"  value="1">
                                            <label for="export_cases">Include all firm cases</label><br>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Options</label>
                                    <div class="col-sm-8">
                                        <div style="line-height: 1.5em;">
                                        <input type="checkbox" name="include_archived" id="include_archived" value="1">
                                        <label for="include_archived">Include archived items</label><br>  
                                        </div>
                                        <div style="line-height: 1.5em;">
                                        <input type="checkbox" name="include_mail" id="include_mail" value="1">
                                        <label for="include_mail">Send me an email when the backup is finished</label><br>  
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-8 text-right">
                                        <div class="loader-bubble loader-bubble-primary innerLoader"></div>
                                    </div>
                                    <div class="col-sm-4 text-right">
                                        <a href="#">
                                            <button class="btn btn-secondary  m-1" type="button"
                                                data-dismiss="modal">Cancel</button>
                                        </a>
                                        <button class="btn btn-primary ladda-button example-button m-1 submit"
                                            type="submit">Create Backup</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>
@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        afterLoader();
        // CaseImportExportHistorty
        var CaseImportExportHistorty =  $('#CaseImportExportHistorty').DataTable( {
            serverSide: true,
            "dom": '<"top">rt<"bottom"p><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false,
            "order": [[0, "desc"]],
            "ajax":{
                url :baseUrl +"/imports/loadFullBackupHistory", // json datasource
                type: "post",  // method  , by default get
                data :{ 'c' :'c'},
                error: function(){  // error handling
                    $(".CaseImportExportHistorty-error").html("");
                    $("#CaseImportExportHistorty_processing").css("display","none");
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
                    
                    if(aData.status!="3"){
                       $('td:eq(0)', nRow).html('<div class="text-left">'+aData.file_name+'</div>');                    
                    }else{
                        $('td:eq(0)', nRow).html('<a title="" data-toggle="tooltip" data-placement="top" href="'+aData.download_link+'" data-original-title="Download">'+aData.file_name+'</a>');
                    }
                    $('td:eq(1)', nRow).html('<div class="text-left">'+aData.options+'</div>');
                    if(aData.status=="3"){
                        var status='<div> Completed </div>';
                    }else if(aData.status=="1"){
                        var status='<div> Pending  </div>';
                    }else if(aData.status=="4"){
                        var status='<div> Failed  </div>';
                    }else {
                        var status='<div> Processing  </div>';
                    }
                    $('td:eq(2)', nRow).html('<div class="text-left">'+status+'</div>');

                    if(aData.status=="3"){
                        $('td:eq(3)', nRow).html('<a title="" class="text-black-50" data-toggle="tooltip" data-placement="top" href="'+aData.download_link+'" data-original-title="Download"><i class="fas fa-cloud-download-alt"></i></a>');
                    }else if(aData.status=="4"){
                        $('td:eq(3)', nRow).html('');
                    }else if(aData.status=="2"){
                        $('td:eq(3)', nRow).html('<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 50%"></div></div>');
                    }else{
                        $('td:eq(3)', nRow).html('<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 10%"></div></div>');
                    }
                },
                "initComplete": function(settings, json) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
        });

        $('#backupWithOptionForm').submit(function (e) {
            $('.showError').html('');
            beforeLoader();
            e.preventDefault();
            if (!$('#backupWithOptionForm').valid()) {
                afterLoader();
                return false;
            }
            var dataString = '';
            dataString = $("#backupWithOptionForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/imports/backupCases", // json datasource
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
                        swal('Success!', res.msg, 'success');
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
    });
    
</script>
@stop
@endsection
