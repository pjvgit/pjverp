@extends('layouts.master')
@section('title', 'Lead Statuses Board')
@section('main-content')
<?php 
$ld=$pa=$ol=$at='';
if(isset($_GET['ld'])){
    $ld= $_GET['ld'];
}
if(isset($_GET['pa'])){
    $pa= $_GET['pa'];
}
if(isset($_GET['ol'])){
    $ol= $_GET['ol'];
}
if(isset($_GET['at'])){
    $at= $_GET['at'];
}
?>
<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                @include('lead.mainMenu')
                <form class="filterBy" id="filterBy" name="filterBy" method="GET">
                    <div class="row pl-4 pb-4">
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Select a Lead</label>
                            <select id="ld" name="ld" class="form-control custom-select col dropdown_list">
                                <option value="">Select...</option>
                                <?php 
                                foreach($allLeadsDropdown as $kcs=>$vcs){?>
                                <option <?php if($ld==$vcs->id){ echo "selected=selected"; }?>  value="{{$vcs->id}}">{{$vcs->first_name}} {{$vcs->last_name}}</option>
                                <?php } ?>

                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Practice Area</label>
                            <select id="pa" name="pa" class="form-control custom-select col dropdown_list">
                                <option value="">Select...</option>
                                <?php 
                                foreach($allPracticeAreaDropdown as $kcs=>$vcs){?>
                                <option  <?php if($pa==$vcs->id){ echo "selected=selected"; }?>  value="{{$vcs->id}}">{{$vcs->title}} </option>
                                <?php } ?>

                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Office Location</label>
                            <select id="ol" name="ol" class="form-control custom-select col dropdown_list">
                                <option value="">Select...</option>
                                <option value="1" <?php if($ol=="1"){ echo "selected=selected"; }?> >Primary</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label for="picker1">Show Leads Assigned To
                            </label>
                            <select id="at" name="at" class="form-control custom-select col dropdown_list">
                                <option value="">Select...</option>
                                <option <?php if($at=="all"){ echo "selected=selected"; }?> value="all">All</option>
                                <option <?php if($at=="unassigned"){ echo "selected=selected"; }?> value="unassigned">Unassigned</option>
                                <option <?php if($at=="me"){ echo "selected=selected"; }?> value="me">Me</option>
                            </select>
                        </div>
                    </div>
                </form>
                <div class="table-responsive" id="tavble">
                    <div class="opportunity-statuses-wrapper overflow-auto d-flex" id="table">
                        <?php foreach($LeadStatus as $k=>$v){?>
                        <div data-react-beautiful-dnd-draggable="0" class="opportunity-status-column p-3" id="item-{{$v->id}}">
                            <div tabindex="0" data-react-beautiful-dnd-drag-handle="0"
                                aria-roledescription="Draggable item. Press space bar to lift" draggable="false"
                                class="status-column-grip d-flex mt-n3 justify-content-center py-2 text-muted row headerMove">
                                <i class="fas fa-grip-lines"></i>
                            </div>
                            <div class="opportunity-status-column-header row ">
                                <div class="col-8">
                                    <div class="opportunity-status-name column-header">
                                        <h5 class="font-weight-bold text-truncate mb-1" title="{{$v->title}}">
                                            {{$v->title}}</h5>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="d-flex align-items-center justify-content-end">
                                        <a data-toggle="modal" onclick="editStatus({{$v->id}});" data-target="#editStatus" data-placement="bottom" href="javascript:;">
                                             <i class="fas fa-pencil-alt text-black-50 m-2"></i>
                                        </a>
                                        <a data-toggle="modal" onclick="deleteStatusFunction({{$v->id}});" data-target="#deleteStatus" data-placement="bottom" href="javascript:;">
                                                <i class="fas fa-trash text-black-50"></i>
                                        </a>  
                                    </div>
                                </div>
                            </div>
                            <div class="opportunity-status-column-subheader mb-3 font-weight-bold row ">
                                <div class="total-leads col-6"><span>{{$extraInfo[$v->id]['totalLeads']}} Leads</span></div>
                                <div class="d-flex justify-content-end col-6">
                                    <span class="total-value column-header">${{number_format($extraInfo[$v->id]['sum'],2)}}</span>
                                </div>
                            </div>
                        <div class="opportunity-status-wrapper" id="{{$v->id}}"  style="height: 100%;">
                            <?php foreach($allLEadByGroup[$v->id] as $kk=>$vv){?>
                                <div class="opportunity-card card mb-4" id="{{$vv->user_id}}">
                                    <input type="hidden" name="card_id"  id="DATA_{{$vv->user_id}}" value="{{$vv->user_id}}">
                                    <div class="px-3 py-2 card-header">
                                        <div class="m-0 align-items-center row ">
                                            <div class="pl-0 pr-2 d-flex align-items-center col-7">
                                                <div class="d-inline-block text-truncate font-weight-bold btn-link">
                                                    <a class="opportunity-detail-link" href="{{BASE_URL}}leads/{{$vv->user_id}}/case_details/info"
                                                        title="{{$vv->first_name}} {{$vv->last_name}}" style="font-size: 0.9rem;">
                                                        {{$vv->first_name}} {{$vv->last_name}}</a>
                                                       
                                                </div>
                                            </div>
                                            <div class="pl-2 pr-0 text-right col-5">
                                                <span class="opportunity-value ml-auto text-black-50 mb-0" style="font-size: 0.9rem;">${{number_format($vv->potential_case_value,2)}}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-3 py-2 card-body">
                                        <div>
                                            <div class="pb-1">
                                                <div class="opportunity-status-timestamp d-flex align-items-center text-muted">
                                                    <span class="text-truncate"> {{$v->title}}
                                                    </span>
                                                    <span class="text-nowrap">&nbsp;as of {{date('m/d/Y',strtotime($vv->date_added))}}
                                                    </span>
                                                </div>
                                            </div>
                                            <?php 
                                            if($vv->email!=""){?>
                                           <div class="d-flex align-items-center pb-1">
                                               <a class="mr-1" href="mailto:{{$vv->email}}">{{$vv->email}}</a>
                                               <i aria-hidden="true" class="fa fa-envelope icon-envelope icon" style="opacity: 0.6; width: 20px;"></i>
                                            </div>
                                            <?php } ?>
                                           <?php 
                                            if($vv->mobile_number!=""){?>
                                            <div class="d-flex align-items-center pb-1">
                                                <span class="mr-1">{{$vv->mobile_number}}</span>
                                                <i aria-hidden="true" class="fa fa-phone icon-phone icon" style="opacity: 0.6; width: 20px;"></i>
                                            </div>
                                            <?php } ?>
                                            <?php 
                                            if($vv->notes!=""){?>
                                            <a data-toggle="collapse" data-target="#collapseExampleArea{{$vv->id}}" href="#collapseExampleArea{{$vv->id}}" aria-expanded="false">
                                                <b>Details</b>
                                                <div class="expand_caret caret"></div>
                                              </a>
                                              <div id="collapseExampleArea{{$vv->id}}" class="collapse border-left"> 
                                                <p>{{substr($vv->notes,0,200)}}</p>
                                              </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="add-status-section position-fixed d-flex justify-content-end mb-5 mr-3" id="add-status-section"
        style="right: 0px; bottom: 0px;">
        <div class="d-flex flex-column align-items-center">
            {{-- <button type="button" id="add-status" class="add-status shadow py-1 px-2 btn-with-plus btn-primary">
                <i class="fas fa-plus"></i>
            </button> --}}
            <a data-toggle="modal" data-target="#addStatus" data-placement="bottom" href="javascript:;">
                <button disabled class="add-status shadow py-1 px-2 btn-with-plus btn-primary" style="cursor: pointer;" id="leadButton" type="button"
                    onclick="addStatus();"> <i class="fas fa-plus"></i></button></a>
            <label for="add-status" class="mt-1 font-weight-bold">Add Status</label>
        </div>
    </div>
</div>
<div id="printArea"></div>
<style>
    .opportunity-status-column {
        background-color: var(--white);
        display: inline-block;
        min-height: 580px;
        vertical-align: top;
        border: 1px solid #eceeef;
        -ms-flex-preferred-size: 0;
        flex-basis: 0;
        -webkit-box-flex: 1;
        -ms-flex-positive: 1;
        flex-grow: 1;
        min-width: 300px;
    }

    .opportunity-status-column:nth-child(2n) {
        background-color: #eceeef !important;
    }

    [data-react-beautiful-dnd-drag-handle="0"] {
        cursor: -webkit-grab;
        cursor: grab;
    }

    .btn-with-plus {
        border: 1px solid #639 !important;
        display: inline-block;
        font-weight: 400;
        color: #323232;
        text-align: center;
        vertical-align: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-color: transparent;
        border: 1px solid transparent;
        padding: .375rem 1rem;
        font-size: .8125rem;
        line-height: 1.5;
        border-radius: 2rem;
        -webkit-transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
    }
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
</style>

<div id="tourModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Getting Started</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <span id="response"></span>
                <div class="row">
                    <div class="col-md-12">
                        <h4>Welcome to Legalcase's Lead Management tool! </h4>

                        <p>Better track potential new clients before converting them to clients. Get a few quick
                            tips in
                            this walkthrough.</p>

                    </div>
                    <div class="col-md-12  text-center">
                        <button class="btn btn-primary" id="startTourBtn">Let's Get Started</button>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addStatus" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
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
                        <div id="showError" style="display:none"></div>
                        <div id="addStatusArea">
                        </div>
                    </div>
                </div><!-- end of main-content -->
            </div>
        </div>
    </div>
</div>

<div id="editStatus" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
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


@include('lead.commonPopup')

@endsection
@section('page-js')


<script type="text/javascript">
    $(document).ready(function () {
        $(".dropdown_list").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
        });

        $('button').attr('disabled',false);
        var options = {
            group: 'share',
            animation: 100
        };

        events = [
            'onChange',
            ].forEach(function (name) {
                options[name] = function (evt) {
                    console.log({
                    'event': name,
                    'this': this,
                    'item': evt.item,
                    'from': evt.from,
                    'to': evt.to,
                    'oldIndex': evt.oldIndex,
                    'newIndex': evt.newIndex
                    });
                    // console.log("Lead_id",evt.item.id)
                    // console.log("Target Board",evt.to.id)
                    // console.log("New index",evt.newIndex)
                    // alert(evt);
                    console.log(evt.target.textContent);
                    changeLeadOrder(evt);
                };
            });

            <?php foreach($LeadStatus as $k => $v) { ?>
                new Sortable.create(document.getElementById({{$v->id}}), options);
            <?php  } ?>
        // $("#table").sortable();
        // $("#tableInner").sortable();
        // $("#table").sortable({
        //     handle: ".headerMove"
        // });

        $('#table').sortable({
                handle: ".headerMove",
                update: function (event, ui) {
                    var datass = $(this).sortable('serialize');
                    $.ajax({
                        type: "POST",
                        url: baseUrl + "/leads/reorderStages",
                        data: datass,
                        success: function (res) {
                            window.location.reload();
                        }
                    });
                }
            });

        var tour = {
            id: "hello-hopscotch",
            steps: [{
                    title: "My Header",
                    content: "This is the header of my page.",
                    target: "header",
                    placement: "right"
                },
                {
                    title: "My content",
                    content: "Here is where I put my content.",
                    target: "bottom",
                    placement: "left"
                }
            ]
        };

        $("#startTourBtn").on("click", function (t) {
            $("#tourModal").modal("hide");
            hopscotch.startTour(tour);
        });

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
                    url: baseUrl + "/leads/deleteStatus", // json datasource
                    data: dataString,
                    beforeSend: function (xhr, settings) {
                        settings.data += '&delete=yes';
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
                            window.location.reload();
                        }
                    }
                });
            });

            $('.dropdown_list').change(function() {
                this.form.submit();
            });

            
            
    });

    // $("#tourModal").modal();

 


    function changeLeadOrder(evt) {
        console.log("Lead_id",evt.item.id)
        console.log("Target Board",evt.to.id)
        console.log("New index",evt.newIndex)
        // $("#preloader").show();
        $("#addLeadArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/changeLeadOrder", // json datasource
                data: {'lead_id':evt.item.id,'target_board':evt.to.id,'new_index':evt.newIndex},
                success: function (res) {
                $("#addLeadArea").html(res);
                    $("#preloader").hide();
                    window.location.reload();
                }
            })
        })
    }

    function addStatus() {
        $("#preloader").show();
        $("#addStatusArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/addStatus", // json datasource
                data: 'loadStep1',
                success: function (res) {
                $("#addStatusArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function editStatus(id) {
        $("#preloader").show();
        $("#editStatusArea").html('<img src="{{LOADER}}"> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/leads/editStatus", // json datasource
                data: {'status_id':id},
                success: function (res) {
                $("#editStatusArea").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }
    function deleteStatusFunction(id) {
        $("#status_id").val(id);
    }

    function printLead(section) {
        $("#preloader").show();
        $(function () {
            var dataString = '';
            dataString = $("#filterBy").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/leads/printLead",
                // data: { "section": section,'dataString':dataString },
                data: dataString + '&section=' + section,
                success: function (res) {
                    window.open(res.url, '_blank');
                    $("#preloader").hide();
                }
            })
        })
    }

    // function printData()
    // {
    //     var f=$("#tavble").html();
    //     $("#printArea").append('<h4>Leads</h4>');
    //     $("#printArea").append(f);
    //     var divToPrint=document.getElementById("printArea");
    //     newWin= window.open("");
    //     newWin.document.write(divToPrint.innerHTML);
    //     newWin.print();
    //     newWin.close();
    //     newWin.onafterprint = closePrintView(); //this is the thing that makes it work i
    // }

    // function closePrintView() { //this function simply runs something you want it to do
    //     $("#printArea").hide();
    //     document.location.reload(); //in this instance, I'm doing a re-direct

    // }
</script>

@stop
