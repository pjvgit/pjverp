<div id="printDiv">   

    <h3 id="hiddenLable">{{($CaseMaster->case_title)??''}}</h3>
    <?php 
    $t=0;
    $previous_date = null;
    foreach($mainArray as $k=>$v){
        if (date('Y-m-d', strtotime($v['created_at']))  != $previous_date) {
            $previous_date = date('Y-m-d', strtotime($v['created_at']));
        ?>
        <div class="d-flex" style="position: relative;">
            <div>
                <div class="bg-info" style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;" id="date-string-2021-09-23T00:00:00+05:30">
                </div>
                <span style="font-size: 100%;" class="badge badge-info">{{ date('M d', strtotime($v['created_at'])) }}</span>
            </div>
            <div class="w-100 ml-2"><span class="text-muted "></span><br><br></div>
        </div>
        <?php } ?>
        <div class="d-flex" style="position: relative;">
            <div style="width: 70px;">                
                <div class="text-center bg-light border border-black" style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                <?php
                    switch (true) {
                        case stristr($v['title'],'expense'):
                            echo '<i class="fa fa-receipt fa-2x text-black-50"></i>';
                            break;
                        case stristr($v['title'],'time'):
                            echo '<i class="fa fa-clock fa-2x text-black-50"></i>';
                            break;
                        case stristr($v['title'],'staff'):
                            echo '<i class="fa fa-user-circle fa-2x text-black-50"></i>';
                            break;
                        case stristr($v['title'],'client'):
                            echo '<i class="fa fa-user-circle fa-2x text-black-50"></i>';
                            break;
                        default:
                            echo '<i class="fa fa-file-invoice-dollar fa-2x text-black-50"></i>';
                            break;
                    }              
                ?>                    
                </div>
                <div class="bg-light" style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: 0; border: 1px none black;"></div>
            </div>
            <div class="w-100">
                <div class="timeline-generic-row bill-row recent-activity-row">
                    <div class="date-time text-muted">{{ date('H:i a', strtotime($v['created_at'])) }}</div>
                    <div class="d-flex align-items-center">
                    <i class="fa fa-pen-square text-info mr-1"></i>
                    <div class="d-flex flex-row"><a class="d-flex align-items-center user-link" href="{{ route('contacts/attorneys/info', base64_encode($v['created_id'])) }}"> {{$v['created_by']}} </a></div>
                    &nbsp; <strong class="mr-1">{{$v['title']}}</strong> 
                        @if($v['staff_id'] != '')
                            @if($v['activity_type'] == 'refund_payment' || $v['activity_type'] == 'accept_payment')
                                <a href="{{ route('bills/invoices/view', base64_encode($v['extra_notes']) ) }}" target="_blank">#{{ sprintf('%06d', $v['extra_notes'])}} </a>
                            @else
                                <a href="{{ route('contacts/clients/view', $v['staff_id']) }}"> {{$v['staff_name']}} </a>
                            @endif
                        @else                            
                            @if($v['extra_notes'] != '')
                                {{ $v['extra_notes']}}
                            @else
                            <a href=""> {{$v['case_name']}} </a>
                            @endif
                        @endif
                    </div>
                </div>
                <br>
            </div>
        </div>
   <?php  } ?>
   <ul class="">
        <button class="btn btn-icon-text btn-warning"><i class="i-Calendar-4"></i> Case created at {{$caseCreatedDate}}
        </button>
    </ul>
</div>

@section('page-js-inner')
<script type="text/javascript">
    
    function printEntry()
    {
        $('#hiddenLable').show();
        var canvas = document.getElementById("printDiv").innerHTML;
        window.print(canvas);
        // w.close();
        $('#hiddenLable').hide();
        return false;  
    }
    $('#hiddenLable').hide();

</script>
@endsection