<div class="col-md-8" id="printHtml">
    <h3 id="hiddenLable">{{($CaseMaster->case_title)??''}}</h3>
    <h3>
        <p class="header">Status Updates</p>
    </h3>
    <div class="mb-2"><span class="font-weight-bold mr-2">Enter a short update to keep firm members informed.</span><a
            href="#" target="_blank">Learn more</a></div>
    <form class="saveUpdateStatus" id="saveUpdateStatus" name="saveUpdateStatus" method="POST">
        <input class="form-control" id="case_id" value="{{ $CaseMaster->case_id}}" name="case_id" type="hidden">

        @csrf
        <div class="form-group row">
            <div class="col-md-10 form-group mb-3">
                <textarea name="case_update" id="case_update"
                    placeholder="E.g. Left a voicemail with opposing counsel to confirm delivery of photos and video files from them. Told her we need them by end of week."
                    class="form-control" rows="8" maxlength="511"></textarea>
            </div>
        </div>

        <div class="form-group row">
            <label for="inputEmail3" class="col-sm-8 col-form-label">Status Updates are not visible to clients.</label>

            <div class="col-md-2 form-group mb-3">
                <button type="submit" class="btn btn-primary">Save Update</button>
            </div>
        </div>
    </form>

    <?php
    foreach($allStatus as $k=>$v){
        ?>
    <hr>
    <div class="d-flex flex-column flex-sm-row align-items-sm-center mb-3" id="beforeEdit_{{$v->case_update_id}}">
        <div class="flex-grow-1">
            <div class="form-group row">
                <div class="col-md-10 form-group mb-3">
                    <h6>Created {{$v->created_new_date}}, by {{$v->first_name}} {{$v->last_name}}</h6>
                </div>
                <div class="col-md-2 form-group mb-3">
                <a href="javascript:;"  onclick="showEditBox({{$v->case_update_id}});">
                    <i class="nav-icon i-Pen-2 font-weight-bold mt-3 mb-3 m-sm-0"></i>
                </a>
                <a href="javascript:;" onclick="onClickDelete({{$v->case_update_id}});"><i class="fas fa-fw fa-trash text-black-50 ml-1"
                        data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"></i></a>
                </a>
            </div> </div><p class="m-0 text-muted">
                {!! nl2br($v->update_status) !!}
            </p>
        </div>
      
    </div>
    <div id="afterEdit_{{$v->case_update_id}}"  style="display: none;">
        <form action="{{ route('case/updateCaseUpdate',$v->case_update_id) }}" class="saveUpdateStatusInner" id="saveUpdateStatusInner" name="saveUpdateStatusInner" method="POST" >
            <input class="form-control" id="id" value="{{ $v->case_update_id}}" name="id" type="hidden" >
            @csrf
            <h6>Created {{$v->created_new_date}}, by {{$v->first_name}} {{$v->last_name}}</h6>
            <div class="form-group row">
                <div class="col-md-10 form-group mb-3">
    <textarea name="case_update" id="case_update" class="form-control" rows="8" maxlength="511">{{$v->update_status}}</textarea>
                </div>
            </div>
            <div class="form-group row">
                <a class="col-sm-8 col-form-label" href="javascript:;" onclick="hideEditBox({{$v->case_update_id}});">
                Cancel
            </a>    
                <div class="col-md-2 form-group mb-3">
                    <button type="submit" class="btn btn-primary">Save Update</button>
                </div>
            </div>
        </form>
    </div>
    <?php } ?>
</div>

@section('page-js-inner')
<script type="text/javascript">
    $(document).ready(function () {
        
        $("#statusList").hide();
        $("#saveUpdateStatus").validate({
            rules: {
                case_update:{
                    required:true
                }
            },
            messages: {
                case_update: {
                    required: "Description can't be blank"
                }
            }
        });
        $('#saveUpdateStatus').submit(function (e) {
            $("#innerLoader").css('display', 'block');
            e.preventDefault();
            if (!$('#saveUpdateStatus').valid()) {
                $("#innerLoader").css('display', 'none');
                return false;
            }
            var dataString = $("#saveUpdateStatus").serialize();
            $.ajax({
                type: "POST",
                url: baseUrl + "/case/saveCaseUpdate", // json datasource
                data: dataString,
                success: function (res) {
                    $('textarea').val('');
                    window.location.reload();
                 
                }
            });
        });
    });

    function showEditBox(id){
        $("#beforeEdit_"+id).attr("style", "display: none !important");
        $("#afterEdit_"+id).show();
    }
    
    function hideEditBox(id){
        $("#afterEdit_"+id).attr("style", "display: none !important");
        $("#beforeEdit_"+id).show();
    }

    
function onClickDelete(id){
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0CC27E',
        cancelButtonColor: '#FF586B',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
        confirmButtonClass: 'btn btn-success mr-5',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false
        }).then(function () {
            $(function () {
                $.ajax({
                    type: "POST",
                    url:  baseUrl +"/case/deleteCaseUpdate", 
                    data: {"id":id},
                    success: function (res) {
                        $("#groupModel").html(res);
                        $("#preloader").hide();
                        swal('Deleted!', 'Case update has been deleted.', 'success');
                        // $('#employee-grid').DataTable().ajax.reload();
                        window.location.reload();
                    }
                });
            });

        }, function (dismiss) {
            // dismiss can be 'overlay', 'cancel', 'close', 'esc', 'timer'
            if (dismiss === 'cancel') {
                swal('Cancelled', 'Your case update is safe :)', 'error');
            }
    });
}

function printEntry()
{
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