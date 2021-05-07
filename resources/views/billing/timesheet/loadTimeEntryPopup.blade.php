<?php 
if($TaskTimeEntry->isEmpty()){?>
<div style="max-height: 400px; height: 400px; overflow: auto;">
    <div class="d-flex flex-column flex-grow-0 align-items-center justify-content-center" style="height: 350px;">
        <img class="d-block mx-auto mb-3" src="{{BASE_URL}}public/svg/time_entry.svg" width="42" height="42">
        <span class="mb-3">No time entry hours were recorded today</span>
        <a data-toggle="modal" data-target="#loadTimeEntryPopup" data-placement="bottom" href="javascript:;">
            <button class="btn btn-success btn-rounded m-1" onclick="loadTimeEntryPopup('{{$curDate}}');"> Add Time Entry
            </button>
        </a>

      
    </div>
</div>
<?php }else{ ?>
<div class="scrollArea">
    <div>
        <?php   
    $masterHours=$MasterTotal=0;
    foreach($TimeEntryArrayByUser as $k1=>$v1){?>
        <div class="font-weight-bolder mb-2"><b><span id="{{$k1}}">{{$nameAndTotals[$k1]['name']}}
                    ({{number_format($nameAndTotals[$k1]['totalHrs'],1)}} hrs)</span></b></div>
        <div class="time-entries-list-table-Smith-Help">
            <table class="table table-md  table-striped">
                <thead class="text-secondary border-bottom myheaderList">
                    <tr>
                        <th style="width: 25%;">Case</th>
                        <th style="width: 20%;">Activity</th>
                        <th style="width: 15%;">Rate</th>
                        <th class="text-right" style="width: 5%;">Duration</th>
                        <th class="text-right" style="width: 10%;">Total</th>
                        <th style="width: 5%;"></th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;"></th>
                    </tr>
                </thead>
                <tbody data-testid="time-entry-rows-0">
                    <?php 
               
                foreach($v1 as $k=>$v){?>
                    <tr class="time-entry-row">
                        <td class="test-case">
                            <a class="name"
                                href="{{BASE_URL}}court_cases/{{$v->case_unique_number}}/info">{{$v->ctitle}}</a>
                        </td>
                        <td class="test-activity">{{$v->activity_title}}</td>
                        <td class="test-rate">${{number_format($v->entry_rate,2)}}/{{$v->rate_type}}</td>
                        <td class="test-duration text-right">{{number_format($v->duration)}}</td>
                        <?php 
                    $total=0;
                    if($v->rate_type=="hr"){
                        $masterHours+=$v->duration;
                        $total=$v->duration*$v->entry_rate;
                    }else{
                        $total=$v->entry_rate;
                    }
                    if($v->time_entry_billable=="yes"){
                        $MasterTotal+=$total;
                    }
                    ?>
                        <td class="test-total text-right">${{number_format($total,2)}}</td>
                        <td class="test-nonbillable"><?php if($v->time_entry_billable=="no"){ ?> <span
                                class="text-muted">NB</span><?php } ?></td>
                        <td class="test-status">
                            <?php 
                        if($v->status=="paid"){
                            ?> <a target="_blank"
                                href="{{BASE_URL}}/bills/invoices/view/{{base64_encode($v->invoice_link)}}">Invoiced</a><?php
                        }else{
                                echo "Open";
                       
                         } 
                        ?>
                        </td>
                        <td>
                            <div class="d-flex invoice-row-buttons flex-row justify-content-around"
                                data-testid="action-buttons">
                                <?php if($v->status=="unpaid"){ ?>
                                <a data-toggle="modal" data-target="#loadEditTimeEntryPopup" data-placement="bottom"
                                    href="javascript:;" onclick="loadEditTimeEntryPopup('{{$v->id}}');"><i
                                        class="fas fa-pen align-middle p-2"></i></a>
                                <a data-toggle="modal" data-target="#deleteTimeEntry" data-placement="bottom"
                                    href="javascript:;" onclick="deleteTimeEntry('{{$v->id}}');"><i
                                        class="fas fa-trash align-middle p-2"></i></a>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
        <table class="table table-md table-striped">
            <tr>
                <th style="width: 25%;"></th>
                <th style="width: 20%;"></th>
                <th style="width: 15%;">DAY TOTAL:</th>
                <th class="text-right" style="width: 5%;">{{number_format($masterHours,1)}}</th>
                <th class="text-right" style="width: 10%;">${{number_format($MasterTotal,2)}}</th>
                <th style="width: 5%;"></th>
                <th style="width: 10%;"></th>
                <th style="width: 10%;"></th>
            </tr>


        </table>
    </div>
    <?php

}?>
</div>
<div class="modal-footer">
 
    
        <a class="mr-auto" data-toggle="modal" data-target="#loadTimeEntryPopup" data-placement="bottom" href="javascript:;">
            <button class="btn btn-outline-secondary  btn-rounded m-1 " onclick="loadTimeEntryPopup('{{$curDate}}');"> Add Time Entry
            </button>
        </a>

    <button class="btn btn-link text-black-50" onclick="loadTimeEntryByDate('{{$curDate}}','prev');return false;">
        <i class="fa fa-angle-left"></i>
        <span class="ml-2">Previous Day</span>
    </button>
    <button class="btn btn-link text-black-50" onclick="loadTimeEntryByDate('{{$curDate}}','next');return false;">
        <span class="mr-2">Next Day</span>
        <i class="fa fa-angle-right"></i>
    </button>
    <a href="#">
        <button class="btn btn-outline-secondary m-1" type="button" data-dismiss="modal">Close</button>
    </a>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#timeEntryTitle").html("{{$dateText}}");
    });

</script>
<style>
    .myheaderList {
        background-color: #dee2e6;
    }

    .scrollArea {
        max-height: 500px;
        overflow-y: scroll;

    }

</style>
