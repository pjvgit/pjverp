@extends('layouts.master')
@section('title', 'Leads')
@section('main-content')
@include('lead.lead_submenu')

<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                @include('lead.mainMenu')
                <div class="row">
                    <div class="col-3"></div>
                    <div class="col-3 ml-auto">
                        <div class="d-flex justify-content-end mb-2 d-print-none">
                            <div class="btn-group">
                                <span id="settingicon" class="pr-4">
                                    <button class="btn btn-secondry dropdown-toggle" id="actionbutton" disabled="disabled" type="button"  data-toggle="dropdown">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu bg-transparent shadow-none p-0 m-0" x-placement="bottom-start"
                                        style=" ">
                                        <div class="card">
                                            <div tabindex="-1" role="menu" aria-hidden="false"  class="dropdown-menu dropdown-menu-right show" x-placement="top-right">
                                                <a href="javascript:void(0);"  onclick="approveBulkLead()"  class="dropdown-item">
                                                    Approve Leads</a>
                                                <a href="javascript:void(0);"  onclick="deleteBulkLead()"  class="dropdown-item">
                                                        Delete Leads</a>
                                            </div>
                                        </div>
                                    </div>
                                </span>
                                <div class="mt-2">
                                    <small class="text-muted mx-1">Text Size</small>
                                <button type="button" arial-label="Decrease text size" data-testid="dec-text-size"
                                    class="btn-sm py-0 px-1 mx-1 btn btn-outline-light decrease "><i class="fas fa-minus fa-xs"></i>
                                </button>
                                <button type="button" arial-label="Increase text size" data-testid="inc-text-size"
                                    class="btn-sm py-0 px-1 mx-1 btn btn-outline-light increase"><i class="fas fa-plus fa-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">  
                    <?php 
                    if(count($leads)<=0){?>
                    <div class="opportunities-list" id="opportunities-list">
                        <div class="leads-empty-state m-5 d-flex flex-column justify-content-center align-items-center">
                            <h1 class="font-weight-bold display-3">Setup Your</h1>
                            <h1 class="font-weight-bold display-3">"Contact Us" Form</h1>
                            <hr class="w-50 bg-dark">
                            <p class="font-weight-bold mt-2 h1">This is where your prospective clients will display when
                            </p>
                            <p class="font-weight-bold h1">a "Contact Us" Form is submitted from your website.</p>
                            <p class="h4 mt-3 mb-4">Don't know how to use the {{config('app.name')}} contact us form? <a
                                    target="_blank" rel="noreferrer noopener" href="#">Click here</a></p><button
                                type="button"
                                class="d-flex justify-content-center align-items-center btn btn-info">Setup Contact
                                Us Form Now</button>
                        </div>
                    </div>
                    <?php }else{ ?>
                    <table class="display table table-striped table-bordered" id="onlineLeadGrid" style="width:100%">
                        <thead>
                            <tr>
                                <th width="1%">id</th>
                                <th width="1%"><input type="checkbox" id="selectAll" class="mx-1"></th>
                                <th width="1%"></th>
                                <th width="50%">Name</th>
                                <th width="10%">Date Added</th>
                                <th width="10%">Submitted Form</th>
                                <th width="10%" class="text-center"></th>
                            </tr>
                        </thead>
                    </table>
                    <?php } ?>
                </div>
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
@include('lead.commonPopup')

@endsection
@section('page-js')

<script type="text/javascript">
    $(document).ready(function () {
        $("#leadButton").removeAttr("disabled");
        var dataTable =  $('#onlineLeadGrid').DataTable( {
        serverSide: true,
        responsive: false,
        processing: true,
        "lengthChange": false,
        stateSave: true,
        searching:false,
        "order": [[0, "desc"]],
        "ajax":{
            url :baseUrl+"/leads/loadOnlineLeads", // json datasource
            type: "post",  // method  , by default get
            data :{ "id": ''},
            error: function(){  // error handling
                $(".onlineLeadGrid-error").html("");
                $("#onlineLeadGrid_processing").css("display","none");
            }
        },
        "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
        pageResize: true,  // enable page resize
        pageLength:{{USER_PER_PAGE_LIMIT}},
        columns: [
            { data: 'id'},  
            { data: 'id','orderable': false},
            { data: 'id','orderable': false},
            { data: 'id'},
            { data: 'id'},
            { data: 'id','orderable': false},
            { data: 'id','orderable': false},],
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                $('td:eq(0)', nRow).html('<div class="text-center"><input name="cLead[]" id="select-row-'+aData.id+'" type="checkbox" value="'+aData.id+'" class="leadRow checkboxRow" onclick="getGG();"> </div>');    
                $('td:eq(1)', nRow).html('<div class="text-center"><i class="fas fa-2x fa-user text-black-50 ml-1"></i></div>');

                $nameCell='<div class="name-cell"><span>'+aData.leadName+'</span>';
                $emailCell='';
                if(aData.email!=null){
                    $emailCell='<div class="row ml-1"><span style="opacity: 0.6; width: 20px;"><i aria-hidden="true" class="fa fa-envelope col- mt-1 pl-0"></i></span><a class="col-10 p-0" href="mailto:'+aData.email+'">'+aData.email+'</a></div></div>';
                }
                $('td:eq(2)', nRow).html('<div class="text-left">'+$nameCell+' '+$emailCell+'</div>');
                $('td:eq(3)', nRow).html('<div class="text-left">'+aData.added_date+'</div>');
                $('td:eq(4)', nRow).html('<div class="text-left"><a target="_blank" href="{{BASE_URL}}online_lead_forms/'+aData.unique_token+'/show_pdf" class="contact-form">Contact Us</a></div>');
                
                $('td:eq(5)', nRow).html('<div class="d-flex align-items-center"><a href="javascript:;" > <button onclick="approveLead('+aData.id+');" class="btn btn-outline-secondary m-1" type="button">Approve</button></a><a data-toggle="modal"  data-target="#deleteBulkLead" data-placement="bottom" href="javascript:;"   title="Delete" data-testid="delete-button" class="btn btn-link" onclick="deleteLeadFunction('+aData.id+');"><i class="fas fa-trash text-black-50"></i></a></div>');
                
                        
            },
            "initComplete": function(settings, json) {
                $("input:checkbox.leadRow").click(function() {
                    if ($('.leadRow:checked').length == $('.leadRow').length) {
                        $('#selectAll').prop('checked', true);
                    } else {
                        $('#selectAll').prop('checked', false);
                    }
                    if($(this).prop('checked')==false){
                        $(this).closest('tr').removeClass('table-info');
                    }else{
                        $(this).closest('tr').addClass('table-info');
                    }
                    if ($('.leadRow:checked').length == "0") {
                        $('#actionbutton').attr('disabled', 'disabled');
                        $('#element').tooltip('enable')
                    } else {
                        $('#element').tooltip('disable')
                        $('#actionbutton').removeAttr('disabled');
                    }
                });
                $('.dropdown-toggle').dropdown();  
                $('td').css('font-size',parseInt(localStorage.getItem("onlineLeadList"))+'px');  
               
            }
        });
        //When select the master checkbpx.
        $("#selectAll").click(function () {
            $(".leadRow").prop('checked', $(this).prop('checked'));
            $(".leadRow").each(function() {
                if($(this).prop('checked')==false){
                    $(this).closest('tr').removeClass('table-info');
                }else{
                    $(this).closest('tr').addClass('table-info');
                }
            });
            if ($('.leadRow:checked').length == "0") {
                $('#actionbutton').attr('disabled', 'disabled');
                $('#element').tooltip('enable')
            } else {
                $('#element').tooltip('disable')
                $('#actionbutton').removeAttr('disabled');
            }
        });
        
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
            $("input[name='cLead[]']:checked").each(function (i) {
                array.push($(this).val());
            });
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/deleteSubmittedLead", // json datasource
                data: dataString + '&leads_id=' + JSON.stringify(array),
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                    $("#innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('#showError').html('<img src="{{LOADER}}"> Loading...');
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
                        toastr.success(res.msg, "", {
                            progressBar: !0,
                            positionClass: "toast-top-full-width",
                            containerId: "toast-top-full-width"
                        });
                        
                        $("#innerLoader").css('display', 'none');
                        $('#submit').removeAttr("disabled");
                        
                        $("#deleteBulkLead").modal("hide");
                        dataTable.ajax.reload(null, false);
                    }
                }
            });
        });

        $('#deleteBulkLead').on('hidden.bs.modal', function () {
            $(".leadRow").each(function() {
              $(this).prop('checked', false);
            });
            $('#selectAll').prop('checked', false);
            $('#actionbutton').attr('disabled', 'disabled');
        });

        
        if(localStorage.getItem("onlineLeadList")==""){
            localStorage.setItem("onlineLeadList","13");
        }     
        var originalSize = $('td').css('font-size');        
        var currentSize=localStorage.getItem("onlineLeadList");
        $('td').css('font-size', currentSize +'px');    
        //Increase the font size 
        $(".increase").click(function(){         
            modifyFontSize('increase');  
        });     
        
        //Decrease the font size
        $(".decrease").click(function(){   
            modifyFontSize('decrease');  
        });

        
    });

    function getGG(){
       
        if ($('.leadRow:checked').length == $('.leadRow').length) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
        if($(this).prop('checked')==false){
            $(this).closest('tr').removeClass('table-info');
        }else{
            $(this).closest('tr').addClass('table-info');
        }
        if ($('.leadRow:checked').length <= 0) {
            $('#actionbutton').attr('disabled', 'disabled');
            $('#element').tooltip('enable')
        } else {
            $('#element').tooltip('disable')
            $('#actionbutton').removeAttr('disabled');
        }
    
    }
    function approveLead(id) {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/approveLead", // json datasource
                data: {'id':id},
                success: function (res) {
                   window.location.reload();
                }
            })
        })
    }
    function deleteLeadFunction(id) {
        if($("#select-row-"+id).is(":checked")){
            $("#select-row-"+id).prop('checked', false);
        } else {
            $("#select-row-"+id).prop('checked', true);
        }
    }
    function deleteBulkLead() {
        
        $("#innerLoader").css('display', 'none');
        $('#submit').removeAttr("disabled");

        $("#deleteBulkLead").modal();
    }
   
    function approveBulkLead() {
        $("#preloader").show();
        $(function () {
            var dataString = '';
            var array = [];
            $("input[class=leadRow]:checked").each(function (i) {
                array.push($(this).val());
            });
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/approveBulkLead", // json datasource
                data: dataString + '&leads_id=' + JSON.stringify(array),
                beforeSend: function (xhr, settings) {
                    settings.data += '&save=yes';
                },
                success: function (res) {
                   window.location.reload();
                }
            })
        })
    }
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
            localStorage.setItem("onlineLeadList",currentFontSize);
        }
    }  
    function printLead(section) {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/printLead",
                data: { "section": section },
                success: function (res) {
                    window.open(res.url, '_blank');
                    $("#preloader").hide();
                }
            })
        })
    }
</script>
<script src="{{ asset('assets\js\custom\feedback.js?').env('CACHE_BUSTER_VERSION') }}"></script>
@stop
