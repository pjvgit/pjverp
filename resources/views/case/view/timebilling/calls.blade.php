<?php
$CommonController= new App\Http\Controllers\CommonController();
$callfor=$status=$type='';
if(isset($_GET['callfor'])){
     $callfor= $_GET['callfor'];
}
if(isset($_GET['status'])){
     $status= $_GET['status'];
}
if(isset($_GET['type'])){
     $type= $_GET['type'];
}
?>
<div class="col-md-12 ">
    <div class="float-right">
        <a data-toggle="modal" data-target="#addCaseNote" data-placement="bottom" href="javascript:;">
            Tell us what you
                think
        </a>
        <a data-toggle="modal" data-target="#addCall" data-placement="bottom" href="javascript:;">
            <button class="btn btn-primary btn-rounded m-1 px-3" type="button" onclick="addCall();">Add
                Call</button>
        </a>
    </div>
</div>

<div class="col-md-12 ">
<form class="filterBy" id="filterBy" name="filterBy" method="GET">
    <div class="row m-1">
        <div class="col-md-3 form-group mb-3">
            <label for="picker1">Call For</label>
            <select id="callfor" name="callfor" class="form-control custom-select col select2">
                <option></option>
                <?php foreach($getAllFirmUser as $key=>$val){?>
                <option <?php if($val->id==$callfor){ echo "selected=selected"; } ?> value="{{$val->id}}">
                    {{substr($val->first_name,0,50)}} {{substr($val->last_name,0,50)}}
                </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-2 form-group mb-3">
            <label for="picker1">Status</label>
            <select id="status" name="status" class="form-control custom-select col select2">
                <option></option>
                <option <?php if($status=="yes"){ echo "selected=selected"; } ?> value="yes">Resolved</option>
                <option <?php if($status=="no"){ echo "selected=selected"; } ?> value="no">Unresolved</option>
            </select>
        </div>
        <div class="col-md-2 form-group mb-3">
            <label for="picker1">Type</label>
            <select id="type" name="type" class="form-control custom-select col select2">
                <option></option>
                <option <?php if($type=="0"){ echo "selected=selected"; } ?> value="0">Incoming</option>
                <option <?php if($type=="1"){ echo "selected=selected"; } ?> value="1">Outgoing</option>
            </select>
        </div>

        <div class="col-md-3 form-group mb-3 mt-3">
            <button class="btn btn-info btn-rounded m-1" type="submit">Apply Filters</button>
            <button type="button" class="test-clear-filters text-black-50 btn btn-link">
                <a href="{{URL::to('court_cases/'.$CaseMaster['case_unique_number'].'/communications/calls')}}">Clear Filters</a>
            </button>
        </div>
        <div class="col-md-2 form-group mb-3 mt-4 text-right">
            <small class="text-muted mx-1">Text Size</small>
            <button type="button" arial-label="Decrease text size" data-testid="dec-text-size"
                class="btn-sm py-0 px-1 mx-1 btn btn-outline-light decrease "><i class="fas fa-minus fa-xs"></i>
            </button>
            <button type="button" arial-label="Increase text size" data-testid="inc-text-size"
                class="btn-sm py-0 px-1 mx-1 btn btn-outline-light increase"><i class="fas fa-plus fa-xs"></i>
            </button>
        </div>
    </div>
</form>
</div>
<?php
if($totalCalls<=0){
?>
    <div class="call-log-content w-100 text-center p-5 h-100">
        <i class="fas fa-phone-alt my-4 fa-5x"></i>
        <h1 class="font-weight-bold">Never miss a phone message </h1>
        <p>Say goodbye to message pads and spreadsheets. Increase efficiency and improve firm-wide communication by
            recording the details of all phone communication right in {{config('app.name')}}.</p>
        <a data-toggle="modal" data-target="#addCall" data-placement="bottom" href="javascript:;">
            <button class="btn btn-primary btn-rounded m-1 px-3" type="button" onclick="addCall();">Add
                Call</button>
        </a>
        <button type="button" class="btn btn-outline-secondary  btn-rounded m-1 px-3">Learn More</button>
    </div>
<?php } else{ ?>
    <div class="table-responsive">
        <table class="display table table-striped table-bordered" id="callList" style="width:100%">
            <thead>
                <tr>
                    <th width="10%">DATE/TIME</th>
                    <th width="10%">CALLER</th>
                    <th width="10%">CALL FOR</th>
                    <th width="10%">TYPE</th>
                    <th width="40%">MESSAGE</th>
                    <th width="10%"></th>
                    <th width="10%"></th>
                </tr>
            </thead>

        </table>
    </div>
<?php } ?>
<style>
.notes-container {
    border: solid 1px #f5f2f2;
}
.note-row {
    border-bottom: solid 1px #f5f2f2;
    margin-left: 1px;
    margin-right: 1px;    display: flex;
}
td,th{
    font-size: 13px;
}
.morecontent span {
    display: none;
}
.morelink {
    display: block;
}
</style>
@include('lead.details.communication.CommunicationCommonPopup')
@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        var showChar = 300;  
        var ellipsestext = "...";
        var moretext = "Show more";
        var lesstext = "Show less";
        var dataTablecallList =  $('#callList').DataTable( {
            serverSide: true,
            "dom": '<"toolbar"><"top">rt<"bottom"p><"clear">',
            responsive: false,
            processing: true,
            stateSave: true,
            searching: false, "ordering": false,
            "ajax":{
                url :baseUrl +"/leads/loadCalls", // json datasource
                type: "post", 
                data :{ 'case_id' : "{{$CaseMaster['case_id']}}",'callfor':'{{$callfor}}' ,'status':'{{$status}}','type':'{{$type}}' },
                error: function(){  
                    $(".employee-grid-error").html("");
                    $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#employee-grid_processing").css("display","none");
                }
            },
            pageResize: true,  
            pageLength:{{USER_PER_PAGE_LIMIT}},
            columns: [
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false},
                { data: 'id',sortable:false}],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {

                    $('td:eq(0)', nRow).html('<div class="text-left"><div class="nowrap">'+aData.utc_time+' </div> By <a href="'+baseUrl+'/contacts/attorneys/'+aData.created_by_decode_id+'">'+aData.created_name+'</a></div>'); 
                    
                    if(aData.caller_name==null){
                        $('td:eq(1)', nRow).html('<div class="text-left">'+aData.caller_name_text+'<br>'+aData.phone_number+'</div>');
                    }else{
                        $('td:eq(1)', nRow).html('<div class="text-left"><a href="'+baseUrl+'/contacts/clients/'+aData.caller_name+'">'+aData.caller_full_name+'</a><br>'+aData.phone_number+'</div>');
                    }

                    $('td:eq(2)', nRow).html('<div class="text-left"><a href="'+baseUrl+'/contacts/attorneys/'+aData.call_for_decode_id+'">'+aData.call_for_name+'</a></div>'); 

                    if(aData.call_type=="0"){
                        var fLabel='Incoming';
                    }else if(aData.call_type=="1"){
                        var fLabel='Outgoing';
                    }
                    $('td:eq(3)', nRow).html('<div class="text-left">'+fLabel+'</div>');

                    $('td:eq(4)', nRow).html('<div class="text-left more">'+aData.message+'</div>');

                    if(aData.call_resolved=="yes"){
                        var downloadOption='<label class="switch pr-3 switch-success mr-3"><span id="ListOresolveText_'+aData.id+'">Resolved</span><span id="ListOnonResolveText_'+aData.id+'" style="display:none;" class="error">Unresolved</span><input id="'+aData.id+'" type="checkbox" class="yes" name="call_resolved" checked="checked"><span class="slider"></span></label>';
                    }else if(aData.call_resolved=="no"){
                        var downloadOption='<label class="switch pr-3 switch-success mr-3"><span id="ListOresolveText_'+aData.id+'" style="display:none;" class="error">Resolved</span><span id="ListOnonResolveText_'+aData.id+'" >Unresolved</span><input id="'+aData.id+'" type="checkbox" class="no"  name="call_resolved" ><span class="slider"></span></label>';
                    }
                    $('td:eq(5)', nRow).html('<div class="d-flex align-items-center">'+downloadOption+'</div>');

                    var addTaskOption='<a  data-toggle="modal"  data-target="#loadAddTaskPopup" onclick="loadAddTaskPopup('+aData.case_id+')" data-placement="bottom"   href="javascript:;"  class="btn btn-link copyButton"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Add Task"><i class="fas fa-tasks  align-middle" data="MyText"></i></span></a>';


                    var editOption='<a  data-toggle="modal"  data-target="#editCall" onclick="editCall('+aData.id+')" data-placement="bottom"   href="javascript:;"  class="btn btn-link copyButton"><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Edit Call Log"><i class="fas fa-pen align-middle" data="MyText"></i></span></a>';

                    var deleteOption='<a data-toggle="modal"  data-target="#deleteCallLog" data-placement="bottom" href="javascript:;"   title="Delete" data-testid="delete-button" class="btn btn-link" onclick="deleteCallLog('+aData.id+');" ><span data-toggle="tooltip" data-trigger="hover" title="" data-content="Download" data-placement="top" data-html="true" data-original-title="Delete Call Log"><i class="fas fa-trash "></i></span></a>';


                    $('td:eq(6)', nRow).html('<div class="d-flex align-items-center float-right">'+addTaskOption+'  ' +editOption+' '+deleteOption+'</div>');
                },
                "initComplete": function(settings, json) {    
                    $("[data-toggle=popover]").popover();
                    $("[data-toggle=tooltip]").tooltip();  
                    var currentSize=localStorage.getItem("callList");
                    $('td').css('font-size', currentSize +'px'); 

                    $('.more').each(function() {
                        var content = $(this).html();
                        if(content.length > showChar) {
                            var c = content.substr(0, showChar);
                            var h = content.substr(showChar, content.length - showChar);
                            var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink ">' + moretext + '</a></span>';
                            $(this).html(html);
                        }
                    });
                    $(".morelink").click(function(){
                        if($(this).hasClass("less")) {
                            $(this).removeClass("less");
                            $(this).html(moretext);
                        } else {
                            $(this).addClass("less");
                            $(this).html(lesstext);
                        }
                        $(this).parent().prev().toggle();
                        $(this).prev().toggle();
                        return false;
                    }); 
                    $('input[name="call_resolved"]').click(function () {
                        var id=$(this).attr("id");
                        $.ajax({
                            type: "POST",
                            url:  baseUrl +"/leads/changeCallType", // json datasource
                            data: {'id':id},
                            success: function (res) {
                                if(res.report=='yes'){
                                    $("#ListOresolveText_"+id).show();
                                    $("#ListOnonResolveText_"+id).hide();
                                }else{
                                    $("#ListOresolveText_"+id).hide();
                                    $("#ListOnonResolveText_"+id).show();
                                }
                                // dataTablecallList.ajax.reload();
                            }
                        })
                    });
                }
            });

        if(localStorage.getItem("callList")==""){
            localStorage.setItem("callList","13");
        }     
        var originalSize = $('td').css('font-size');        
        var currentSize=localStorage.getItem("callList");
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
            localStorage.setItem("callList",currentFontSize);
        }
    }  

</script>
@stop