<ul class="list-group" id="list-group">
    <?php 
    foreach($caseStage as $key=>$val){?>
    <li id="item-{{$val->id}}" class="mb-2 list-group-item">
        <i class="fa fa-bars mr-2" style="cursor: grab;" aria-hidden="true"></i> {{$key+1}} . {{$val->title}}
        <a data-toggle="modal" class="float-right " href="javascript:;" title="Edit"
            onclick="deleteStage({{$val->id}});"> <i class="nav-icon i-Remove font-weight-bold"></i></a> <a
            data-toggle="modal" title="Delete" class="float-right pr-3" data-target="#EditCaseStageModel"
            data-placement="bottom" href="javascript:;" onclick="editCaseStageDetails({{$val->id}});"><i
                class="nav-icon i-Pen-2 font-weight-bold"></i></a>
    </li>
    <?php } ?>
</ul>

<script type="text/javascript">
    $(document).ready(function () {
        // $("#list-group").sortable();
        $('#list-group').sortable({
            axis: 'y',
            update: function (event, ui) {
                var datass = $(this).sortable('serialize');
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/case_stages/reorderStages",
                    data: datass,
                    success: function (res) {
                        toastr.success('Stage order has been updated.', "", {
                                progressBar: !0,
                                positionClass: "toast-top-full-width",
                                containerId: "toast-top-full-width"
                            });
                            reloadCaserStages();
                        // window.location.reload();
                    }
                });
            }
        });
    });
        
    </script>