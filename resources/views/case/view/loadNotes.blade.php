<h3 id="hiddenLable">{{($CaseMaster->case_title)??''}}</h3>
<div class="col-md-12">
    <div class="d-flex justify-content-end mb-2">
        <a href="javascript:void(0);" class="pt-2 pr-2" onclick="expandAllnote();" id="ex">
            <i class="fas fa-expand-arrows-alt mr-1"></i>
            Expand All Notes
        </a>
        <a href="javascript:void(0);" class="pt-2 pr-2" onclick="collapseAllnote();" id="co" style="display:none;">
            <i class="fas fa-compress-arrows-alt mr-1"></i>Collapse All Notes
        </a>
        <a data-toggle="modal" data-target="#addNoteModal" data-placement="bottom" href="javascript:;"> <button
                class="btn btn-primary btn-rounded m-1 px-5" type="button" onclick="loadAddNotBox();">Add
                Note</button></a>
    </div>
    <div class="d-flex justify-content-end">
        <span class="my-2">
            <small class="text-muted mx-1">Text Size</small>
            <button type="button" arial-label="Decrease text size" data-testid="dec-text-size"
                class="btn-sm py-0 px-1 mx-1 btn btn-outline-light decrease "><i class="fas fa-minus fa-xs"></i>
            </button>
            <button type="button" arial-label="Increase text size" data-testid="inc-text-size"
                class="btn-sm py-0 px-1 mx-1 btn btn-outline-light increase"><i class="fas fa-plus fa-xs"></i>
            </button>
        </span>
    </div>
</div>
<div class="table-responsive">
    <table class="display table table-striped table-bordered accordion" id="ClientNotesyGrid" style="width:100%">
        <thead>
            <tr>
                <th width="65%">Notes</th>
                <th width="20%">Date</th>
                <th width="15%"></th>
            </tr>
        </thead>

    </table>
</div>
@section('page-js-inner')
<script type="text/javascript">
    function printEntry()
    {
        $('#hiddenLable').show();
        var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        window.print(canvas);
        // w.close();
        $(".printDiv").html('');
        $('#hiddenLable').hide();
        return false;  
    }
    $('#hiddenLable').hide();
</script>
@endsection
