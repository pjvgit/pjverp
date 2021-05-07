@extends('layouts.master')
@section('title', 'Location')
@section('main-content')
<style>.morecontent span {
    display: none;
}
.morelink {
    display: block;
}
</style>

<div class="row">
   
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <span id="responseMain"></span>
                <div class="d-flex align-items-center pl-4 pb-4">
                    <h3> Locations</h3>
                    
                    <div class="ml-auto d-flex align-items-center d-print-none">
                        <a data-toggle="modal"  data-target="#addLocationModal" data-placement="bottom" href="javascript:;" > <button disabled class="btn btn-primary btn-rounded m-1" type="button" onclick="addLocationModal();">Add Location</button></a>
                    </div>   

                </div>
                
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="employee-grid" style="width:100%">
                        <thead>
                            <tr>
                                <th width="1%" class="nosort">id</th>
                                <th width="30%" class="nosort">Name</th>
                                <th width="30%" class="nosort">Address</th>
                                <th width="10%" class="text-center nosort">Added</th>
                                <th width="10%" class="text-center nosort"></th>
                            </tr>
                        </thead>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="addLocationModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Location</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="addLocation">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="editLocationModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Update Location</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="editLocation">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="deleteLocationModal" class="modal fade" tabindex="-1" role="dialog"
aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="deleteLocation" id="deleteLocation" name="deleteLocation" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Delete Location</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <span id="response"></span>
                    <div class="row">
                        <div class="col-md-12">
                            Deleting this location will remove it from all existing events.

                            <p>Are you sure you want to delete this location?</p>
                            <input class="form-control" value="" maxlength="255" id="location_id" name="location_id" type="hidden">
                        </div>
                    </div>
                </div>
                <div class="justify-content-between modal-footer">
                    <div></div>
                    <div class="mr-0">
                        <a href="#">
                            <button class="btn btn-secondary  m-1" type="button" data-dismiss="modal">Cancel</button>
                        </a>
                        <button class="btn btn-primary example-button ml-1" id="submit"  type="submit"
                        data-style="expand-left">Delete Location </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style> .modal { overflow: auto !important; }</style>
@endsection
@section('page-js')
<script type="text/javascript">
    $(document).ready(function() {
        $('button').attr('disabled',false);
       
        var dataTable =  $('#employee-grid').DataTable( {
            serverSide: true,
            responsive: false,
            processing: true,
            stateSave: true,
            "searching":false,
            "order": [[0, "desc"]],
            "ajax":{
                url :"loadLocation", // json datasource
                type: "post",  // method  , by default get
                data :{ 'pa' : "" },
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
                { data: 'id'},
                { data: 'id' }, 
                { data: 'id','orderable': false},
                { data: 'id','orderable': false},],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                
                    var address1='';
                    if(aData.address1!= null){
                        var address1=aData.address1+"<br>";
                    }
                    var address2='';
                    if(aData.address2!= null){
                        var address2=aData.address2+"<br>";
                    }
                    var city='';
                    if(aData.city!= null){
                        var city=aData.city+',';
                    } 
                    var state='';
                    if(aData.state!= null){
                        var state=aData.state+"<br>";
                    } 
                    var name='';
                    if(aData.name!= null){
                        var name=aData.name;
                    }
                    var address=address1+address2+city+state+name;
                    if(address==''){
                        address='<i class="table-cell-placeholder"></i>';
                        $('td:eq(0)', nRow).html('<div class="text-left">'+aData.location_name+'</div>');
                        addressLink='<i class="fas fa-map-marker pr-2  align-middle"></i>';
                    }else{
                        $('td:eq(0)', nRow).html('<div class="text-left"><a target="_blank" href="https://maps.google.com?daddr='+aData.map_address+'">'+aData.location_name+'</a></div>');
                        addressLink='<a target="_blank" href="https://maps.google.com?daddr='+aData.map_address+'"><i class="fas fa-map-marker pr-2  align-middle"></i></a>';
                    }

                    $('td:eq(1)', nRow).html('<div class="text-left">'+address+'</div>');

                    $('td:eq(2)', nRow).html('<div class="text-center"><div class="details">'+aData.created_new_date_only+'<small> <br>by <a href="'+baseUrl+'/contacts/attorneys/'+aData.createdby+'">'+aData.created_by_name+'</a></small></div></div>');
        
                    $('td:eq(3)', nRow).html('<div class="text-center"><span data-toggle="popover" data-trigger="hover" title="" data-content="View Map" data-placement="top" data-html="true">'+addressLink+'</span><span data-toggle="popover" data-trigger="hover" title="" data-content="Edit" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#editLocationModal" data-placement="bottom" href="javascript:;"  onclick="editLocationModal('+aData.id+');"><i class="fas fa-pen pr-2  align-middle"></i></a></span><span data-toggle="popover" data-trigger="hover" title="" data-content="Delete" data-placement="top" data-html="true"><a data-toggle="modal"  data-target="#deleteLocationModal" data-placement="bottom" href="javascript:;" onclick="deleteThisLocation('+aData.id+');"><i class="fas fa-trash pr-2  align-middle"></i></a></span></div>');
                
                },
                "initComplete": function(settings, json) {
                    $("[data-toggle=popover]").popover();
                }
            });
            
            $('#addLocationModal,#editLocationModal,#deleteLocationModal').on('hidden.bs.modal', function () {
                dataTable.ajax.reload(null, false);
            });


            $('#deleteLocation').submit(function (e) {
                $("#submit").attr("disabled", true);
                $("#innerLoader").css('display', 'block');
                e.preventDefault();

                if (!$('#deleteLocation').valid()) {
                    $("#innerLoader").css('display', 'none');
                    $('#submit').removeAttr("disabled");
                    return false;
                }

                var dataString = $("#deleteLocation").serialize();
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/deleteLocation", // json datasource
                    data: dataString,
                    success: function (res) {
                        $("#innerLoader").css('display', 'block');
                        if (res.errors != '') {
                            $('#response').html('');
                            var errotHtml =
                                '<div class="alert alert-danger">Sorry, something went wrong. Please try again.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                            $('#response').append(errotHtml);
                            $('#response').show();
                            $("#innerLoader").css('display', 'none');
                            $('#submit').removeAttr("disabled");
                            return false;
                        } else {
                            $("#deleteLocationModal").modal("hide");
                            toastr.success('Location deleted successfully.', "", {
                                progressBar: !0,
                                 positionClass: "toast-top-full-width",
                                 containerId: "toast-top-full-width"
                            });
                            $('#submit').removeAttr("disabled");
                        }
                    }
                });
            });

    });
    function deleteThisLocation(id){
       $("#location_id").val(id);
    }
    function addLocationModal() {
        $("#innerLoader").css('display', 'none');
        $("#preloader").show();
        $("#addLocation").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/loadAddLocationPopup", // json datasource
                data: 'loadStep1',
                success: function (res) {
                    $("#addLocation").html(res);
                    $("#preloader").hide();
                    $("#innerLoader").css('display', 'none');
                    return false;
                }
            })
        })
    }
    function editLocationModal(id) {
        $("#innerLoader").css('display', 'none');
        $("#preloader").show();
        $("#editLocation").html('<img src="{{LOADER}}""> Loading...');
        $(function () {
            $.ajax({
                type: "POST",
                url:  baseUrl +"/loadEditLocationPopup", // json datasource
                data: {'id':id},
                success: function (res) {
                    $("#editLocation").html(res);
                    $("#preloader").hide();
                    $("#innerLoader").css('display', 'none');
                    return false;
                }
            })
        })
    }


</script>

@stop
