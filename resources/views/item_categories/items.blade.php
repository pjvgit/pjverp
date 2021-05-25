@extends('layouts.master')
@section('title', 'Event Types
')
@section('main-content')

<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <a href={{BASE_URL}}events?view=month>Back to Calendar</a>               
                <h4 class="mt-4">Event Types</h4>
             
                <div>    
                <form class="saveTypeForm" id="saveTypeForm" name="saveTypeForm" method="POST">
                            
                    @csrf
                    <div class="row ">
                        <div class="col-2">Manage your Event Types</div>
                        <div class="col-4">
                            <div class="float-right">
                                <div>
                                    <div>
                                        <button type="button" class="ocs mr-2 btn btn-link cancel" style="display:none;">Cancel</button>
                                        <button type="button" class="mr-2 btn btn-secondary" onclick="addNewType()">Add Event Type</button>
                                        <button type="button" class="ocs mr-2 btn btn-outline-primary  submit" onclick="checkBeforeSubmit()" style="display:none;">  Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="row mt-2">
                        <div class="col-6">
                        <div class="showError " role="alert"></div>      
                            <ul id="sortable">
                            <?php foreach($allEventType as $k){ ?>
                                <li id="item-{{$k->status_order}}" class="list-group-item">
                                    <div class="align-items-center category-row-details m-2  row " id="nonDeleteArea_{{$k->id}}">
                                        <div class="col-1">
                                            <div style="cursor: grab;"><i aria-hidden="true" class="fa fa-bars text-black-50"></i></div>
                                        </div>
                                        <div class="col-2">  
                                            <div id="{{$k->id}}"  class="cursor-pointer colorSelector colorSelector_{{$k->id}}" style="    background-color:{{$k->color_code}};" ></div>
                                            <input type="hidden" class="colorSelectorText" name="Ccode[{{$k->id}}]" value="{{$k->color_code}}" id="Ccode{{$k->id}}">
                                        </div>
                                        <div class="col-7">
                                            <input type="text" id="disabledTitle_{{$k->id}}" name="title[{{$k->id}}]" class="form-control" value="{{$k->title}}">
                                        </div>
                                        <div class="col-2">
                                            <div class="float-right">
                                                <i class="fas fa-trash cursor-pointer" style="height: 16px;width: 16px;" onClick="removeType({{$k->id}})" title="Delete"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="align-items-center category-row-details m-2  row " id="DeleteArea_{{$k->id}}" style="display:none;">
                                        <div class="col-2"><b>Removed</b></div>
                                        <div class="col-8"><b>{{$k->title}}</b></div>
                                        <div class="col-2">
                                            <div class="float-right">
                                                <button type="button" class="btn btn-secondary cursor-pointer" onClick="undoType({{$k->id}})"> Undo</button>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php } ?>

                                <li class="list-group-item" id="RownewType">
                                    <div class="align-items-center category-row-details m-2  row " >
                                        <div class="col-1">
                                            <div style="cursor: grab;"><i aria-hidden="true" class="fa fa-bars text-black-50"></i></div>
                                        </div>
                                        <div class="col-2">  
                                            <div id="newType"  class="cursor-pointer colorSelector colorSelector_newType"  ></div>
                                            <input type="hidden"  class="colorSelectorText" name="NewCcode" value="" id="Ccode_newType">
                                        </div>
                                        <div class="col-7">
                                            <input type="text" disabled name="Newtitle"  id="Newtitle" class="form-control" value="">
                                        </div>
                                        <div class="col-2">
                                            
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="deleteTypeBox" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="deleteStatusForm" id="deleteStatusForm" name="deleteStatusForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Event Type</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <span id="response"></span>
                    <div class="row">
                        <div class="col-md-12">
                            <h6>This event type will be removed for everyone in the firm. You will no longer have the ability to filter on this event type.

                            </h6>

                            <p>Are you sure you want to delete?</p>
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
                            <button class="btn btn-primary ladda-button example-button m-1" id="submit" type="submit">Yes, Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<style>
    .colorSelector {
        background: url('{{BASE_LOGO_URL}}/public/assets/styles/css/images/select.png');
    }
</style>
@endsection
@section('page-js')

<script type="text/javascript">
    $(document).ready(function () {
        $(".ocs").hide();
        $( "#sortable" ).sortable({
            axis: 'y',
            update: function (event, ui) {
                $(".ocs").show();

            }
        });

        $('.colorSelector_newType').ColorPicker({
            flat:false,
            livePreview:true,
            onShow: function (colpkr) {
                $(colpkr).fadeIn(500);
                return false;
            },
            onHide: function (colpkr) {
                $(colpkr).fadeOut(500);
                return false;
            },
            onChange: function (hsb, hex, rgb) {
                $('#newType').css('backgroundColor', '#' + hex);
                $("#Ccode_newType").val("#"+hex);
                $(".ocs").show();

            }
        });
        // var datass = $("#sortable").sortable('serialize');
      
        //This loop was added because there is need to intialize the color picker on page load but it cant
        <?php foreach($allEventType as $k){ ?>
            $('.colorSelector_{{$k->id}}').ColorPicker({
                flat:false,
                color: '{{$k->color_code}}',
                livePreview:true,
                onShow: function (colpkr) {
                    $(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    $(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    $('#{{$k->id}}').css('backgroundColor', '#' + hex);
                    $("#Ccode{{$k->id}}").val("#"+hex);
                    $(".ocs").show();

                }
            });
        <?php } ?>
            $("#sortable").on("keyup",function(){
                $(".ocs").show();
            });
            $(".cancel").on("click",function(){
                $(".ocs").hide();
                $("#RownewType").hide();
                $("#Newtitle").attr("disabled", true);
            });
            $("#RownewType").hide();

           
            $('#deleteStatusForm').submit(function (e) {
                $("#submit").attr("disabled", true);
                $("#innerLoader").css('display', 'block');
                e.preventDefault();

                if (!$('#saveTypeForm').valid()) {
                    $("#innerLoader").css('display', 'none');
                    $('#submit').removeAttr("disabled");
                    return false;
                }

                var sortData = $("#sortable").sortable('serialize');
                var dataString = $("#saveTypeForm").serialize();
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/case_stages/saveTypeOfCase", // json datasource
                    data: "del="+arrayVal+"&"+dataString,
                    success: function (res) {
                        $("#innerLoader").css('display', 'block');
                        if (res.errors != '') {
                            $('.showError').html('');
                            var errotHtml =
                                '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><ul>';
                            $.each(res.errors, function (key, value) {
                                errotHtml += '<li>' + value + '</li>';
                            });
                            errotHtml += '</ul></div>';
                            $('.showError').append(errotHtml);
                            $('.showError').show();
                            $('.submit').removeAttr("disabled");
                        
                            return false;
                        } else {
                            $('.submit').removeAttr("disabled");
                            window.location.reload();
                        }
                    },
                    error: function (xhr, status, error) {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        afterLoader();
                        $('#AddCompany').animate({
                                scrollTop: 0
                        }, 'slow');
                    }
                });
            });
        });
        const arrayVal=[];
        function removeType(id){
            $("#DeleteArea_"+id).show();
            $("#nonDeleteArea_"+id).hide();
            $("#disabledTitle_"+id).attr("disabled","disabled");
            $(".ocs").show();
            arrayVal.push(id);
            
        }
        function undoType(id){
            $("#DeleteArea_"+id).hide();
            $("#nonDeleteArea_"+id).show();
            $("#disabledTitle_"+id).removeAttr("disabled");
            arrayVal.splice($.inArray(id, arrayVal), 1);
        }
        function addNewType(){
            $("#RownewType").show();
            $(".ocs").show();
            $("#Newtitle").removeAttr("disabled");

        }
        function checkBeforeSubmit(){
            if(arrayVal!=""){
                $("#deleteTypeBox").modal("show");
                return false;
            }else{
                submitFormForType();
                return true;
            }
        }
        function submitFormForType(){
            var sortData = $("#sortable").sortable('serialize');
            var dataString = $("#saveTypeForm").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/case_stages/saveTypeOfCase", // json datasource
                data: dataString,
                success: function (res) {
                    $("#innerLoader").css('display', 'block');
                    if (res.errors != '') {
                        $('.showError').html('');
                        var errotHtml =
                            '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br><ul>';
                        $.each(res.errors, function (key, value) {
                            errotHtml += '<li>' + value + '</li>';
                        });
                        errotHtml += '</ul></div>';
                        $('.showError').append(errotHtml);
                        $('.showError').show();
                        $('.submit').removeAttr("disabled");
                    
                        return false;
                    } else {
                        $('.submit').removeAttr("disabled");
                        window.location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $('.showError').html('');
                    var errotHtml =
                        '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                    $('.showError').append(errotHtml);
                    $('.showError').show();
                    afterLoader();
                    $('#AddCompany').animate({
                            scrollTop: 0
                    }, 'slow');
                }
            });
        }
    </script>
@stop
