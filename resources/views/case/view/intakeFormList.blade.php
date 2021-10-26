<h3 id="hiddenLable">{{($CaseMaster->case_title)??''}}</h3>
<div class="col-md-12">
    <div class="float-right">
        <a href="#" target="_blank" class="mr-4"><i class="fas fa-question-circle" aria-hidden="true"></i> Learn
            about Intake Forms</a>

        <a data-toggle="modal" data-target="#addIntakeFormFromCase" data-placement="bottom" href="javascript:;">
            <button class="btn btn-primary btn-rounded m-1 px-3" type="button" onclick="addIntakeFormFromCase();">Add
                Intake Form</button>
        </a>
    </div>
</div>
<?php if(isset($totalCaseIntakeForm) && $totalCaseIntakeForm<=0){ ?>
    <div class="col-md-12 m-5 text-center">
        <i class="fas fa-clipboard-list my-4 fa-5x" data-testid="empty-state-icon"></i>
        <h1 class="font-weight-bold">Send Intake Forms to Potential Clients</h1>
        <p>Streamline collecting case and contact information during the intake process and stop wasting time manually entering information collected on intake forms.</p>
        <div>
            <a href="#" target="_blank" class="btn btn-outline-secondary">Learn More</a>
        </div>
    </div>
<?php }else{ ?>
    <div class="table-responsive">
        <table class="display table table-striped table-bordered" id="intakeFormList" style="width:100%">
            <thead>
                <tr>
                    <th width="60%">INTAKE FORM</th>
                    <th width="15%">CREATED</th>
                    <th width="15%">STATUS</th>
                    <th width="10%"></th>
                </tr>
            </thead>
        </table>
    </div>
<?php } ?>
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
