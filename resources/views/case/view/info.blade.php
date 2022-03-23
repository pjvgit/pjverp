<?php
 $CommonController= new App\Http\Controllers\CommonController();
?>
<?php if($InvoicesOverdueCase>0){?>
    <div class="w-100 ml-n2 mr-3 alert alert-danger fade show" role="alert">
        <div class="d-flex align-items-start">
            <div class="w-100">
                <span class="font-weight-bold">This case is past due.</span> Please see the Time &amp; Billing tab for details.
            </div>
        </div>
    </div>
    <?php } ?>
<div class="col-md-8">
    <div class="case-detail-case-stage-timeline-insight d-print-none">
        <div style="height: 100%; width: 100%; min-width: 400px;"
            class="insights-card case-stage-timeline-insights mx-2 card">
            <div class="card-body">
                <div class="d-flex flex-row justify-content-between">
                    <?php
                        $fdate = date('Y-m-d');
                        $tdate = $CaseMaster->case_open_date;
                        $datetime1 = new DateTime($fdate);
                        $datetime2 = new DateTime($tdate);
                        $interval = $datetime1->diff($datetime2);
                        if($interval->format('%a')!=0){ $openDays=$interval->format('%a'); }else{ $openDays=1; }//now do whatever you like with $days
                    ?>
                    <div class="card-title"><strong>Case Timeline by Stage - Days Open:{{$openDays}}</strong></div>
                    <div>
                        <div class="pendo-case-stage-settings btn-group">
                            <button class="py-0 btn btn-link dropdown-toggle" data-toggle="dropdown" id="actionbutton"  aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-cog" aria-hidden="true"></i>
                            </button>
                            <div class="dropdown-menu bg-transparent shadow-none p-0 m-0 "
                                x-placement="top-start">
                                <div class="card">
                                    <div class="card-body">
                                    <a href="{{ route('case_stages') }}">
                                        <button type="button" tabindex="0" role="menuitem"  class="m-0 bulk-mark-tasks-as-read dropdown-item">
                                        <span> Manage Case Stages</span>
                                        </button>
                                    </a>
                                    <a href="javascript:void(0);" onclick="caseStageTimeline();"  class="align-items-center" >
                                        <button type="button" tabindex="0" role="menuitem" class="bulk-mark-tasks-as-complete dropdown-item"> 
                                            Edit Case Timeline History
                                        </button>
                                    </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="d-flex flex-row flex-wrap mt-1">
                        <div class="d-flex flex-row justify-content-between w-100">
                            <div class="d-flex flex-column">
                                <span class="font-weight-light selenium-date-opened"> Opened : <?php 
                                    if(isset($CaseMaster->case_open_date)){
                                    echo date('m/d/Y',strtotime($CaseMaster->case_open_date));
                                    }?>
                                </span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="font-weight-light selenium-date-closed">Today</span>
                            </div>
                        </div>
                    </div>
                    <div class="progress mb-3">
                        <?php
                        $defaultColorCode=['#BFD8E1'];
                        $days=$color=$startDate=$endDate=$stage=[];
                         foreach($caseStatusHistory as $key=>$val){
                            $days[$val['id']][]=$val['days'];
                            $stage[$val['id']]=$val['stage_id'];
                            $color[$val['id']]=$val['color'];
                            $startDate[$val['id']]=$val['startDate'];
                            $endDate[$val['id']]=$val['endDate'];
                         }
                        
                        foreach($days as $k=>$v){
                            $p=100;
                            if($val['days']!=0){
                               $p=(array_sum($v)/$openDays)*100;
                            }
                            ?>
                            <?php 
                            if($stage[$k]==0){?>                            
                            <div data-toggle="popover" data-trigger="hover" title="" data-content="<strong><span> No Stage </span> <br> {{array_sum($v)}}<br>Started :{{date('m/d/Y',strtotime(@$startDate[$k]))}}<br>Ended :{{date('m/d/Y',strtotime(@$endDate[$k]))}}</strong>" data-html="true" data-original-title="" class="progress-bar progress-bar-striped bar-no-stag"  role="progressbar" data-placement="top"
                            style="width:{{$p}}%;background-color:{{$color[$k]}}" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                            <?php }else{ ?>
                            <div data-toggle="popover" data-trigger="hover" title="" data-content="<strong><span> {{ @$caseStageListArray[$stage[$k]] }} </span><br> {{array_sum($v)}}<br>Started :{{ date('m/d/Y',strtotime(@$startDate[$k])) }}<br>Ended :{{ date('m/d/Y',strtotime(@$endDate[$k])) }}</strong>" data-placement="top" data-html="true" data-original-title="" data-original-title="" title="" aria-describedby="popover751901" class="progress-bar progress-bar-striped bar-no-stag"  role="progressbar"
                            style="width:{{$p}}%;background-color:{{$color[$k]}}" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                            <?php  } ?>
                    <?php } ?>
                    </div>
                    <div style="opacity: 1;" class="insights-legend d-flex flex-row undefined flex-wrap">
                        <div class="d-flex flex-wrap justify-content-start w-100">
                            <?php foreach($days as $k=>$v){ ?>
                                <div style="width: 270px;">
                                    <div class="mb-1 mx-4">
                                        <div class="row mr-2">
                                            <div>
                                                <div class="square-box-Discovery legend-box bar-no-stag"
                                                    style="width: 16px; height: 16px;background-color:{{$color[$k]}}">
                                                </div>
                                            </div>
                                            <div class="ml-2">
                                                <div class="insights-labels Discovery font-weight-bold m-0">
                                                    <?php 
                                                    if($stage[$k]==0){?>
                                                        No Stage <span class="font-weight-light ml-2"> 
                                                        <?php 
                                                        if(array_sum($v)==1) { echo "<". array_sum($v) ." Day"; } else { echo array_sum($v) ." Days"; } 
                                                        ?>  </span>
                                                    <?php }else{
                                                        
                                                    ?>
                                                        
                                                    {{@$caseStageListArray[$stage[$k]]}} <span class="font-weight-light ml-2"><?php 
                                                        if(array_sum($v)==1) { echo "<". array_sum($v) ." Day"; } else { echo array_sum($v) ." Days"; } 
                                                        ?></span>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <?php /* 

                    <div class="progress mb-3">
                       
                        <?php  
                        
                        $zero=$one=$two=$three=0;
                        $nostagSet=$discoverySet=$IntrialSet=$onholdSet=0;
                        foreach($caseStatusHistory as $key=>$val){
                            $p=100;
                            if($val['days']!=0){
                               $p=$val['days']/$openDays*100;
                            }
                           if($val['stage_id']=="0"){
                            $zero=$zero+$val['days'];
                            $nostagSet=1;
                            ?>
                        <div class="progress-bar progress-bar-striped bar-no-stag" role="progressbar"
                            style="width: {{$p}}%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                        <?php } else if($val['stage_id']=="1"){
                             $discoverySet=1;
                              $one=$one+$val['days'];
                            ?>
                        <div class="progress-bar progress-bar-striped bar-discovery" role="progressbar"
                            style="width: {{$p}}%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                        <?php } else if($val['stage_id']=="2"){
                            $IntrialSet=1;
                            $two=$two+$val['days']; ?>
                        <div class="progress-bar progress-bar-striped bar-in-trial" role="progressbar"
                            style="width: {{$p}}%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                        <?php } else if($val['stage_id']=="3"){
                                $onholdSet=1;
                              $three=$three+$val['days'];?>
                        <div class="progress-bar progress-bar-striped bar-on-hold" role="progressbar"
                            style="width: {{$p}}%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                        <?php 
                                }   
                            }
                        ?>
                    </div>
                    <div style="opacity: 1;" class="insights-legend d-flex flex-row undefined flex-wrap">
                        <div class="d-flex flex-wrap justify-content-start w-100">

                            <?php   
                            if($zero!=0 ){
                                $zeroDayText=($zero==0)?' Day':' Days';
                                $zero=$zero.$zeroDayText;
                                
                            }else{
                                $zero='<1  Day';
                            }
                                ?>
                            <div style="width: 370px;">
                                <div class="mb-1 mx-4">
                                    <div class="row mr-2">
                                        <div>
                                            <div class="square-box-Discovery legend-box bar-no-stag"
                                                style="width: 16px; height: 16px;">
                                            </div>
                                        </div>
                                        <div class="ml-2">
                                            <div class="insights-labels Discovery font-weight-bold m-0">
                                                No Stage <span class="font-weight-light ml-2">{{$zero}} </span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <?php 
                            if($discoverySet==1){
                            if($one!=0 ){
                                $oneDayText=($one==0)?' Day':' Days';
                                $one=$one.$oneDayText;
                            }else{
                                $one='<1  Day';
                            }
                             ?>
                            <div style="width: 370px;">
                                <div class="mb-1 mx-4">
                                    <div class="row mr-2">
                                        <div>
                                            <div class="square-box-Discovery legend-box bar-discovery"
                                                style=" width: 16px; height: 16px;">
                                            </div>
                                        </div>
                                        <div class="ml-2">
                                            <div class="insights-labels Discovery font-weight-bold m-0">
                                                {{$caseStageListArray[1]}} <span class="font-weight-light ml-2">{{$one}}
                                                </span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } ?>
                            <?php 
                            if($IntrialSet==1){
                                if($two!=0){
                                    $twoDayText=($two==0)?' Day':' Days';
                                    $two=$two.$twoDayText;
                                }else{
                                    $two='<1  Day';
                                }
                                ?>
                            <div style="width: 370px;">
                                <div class="mb-1 mx-4">
                                    <div class="row mr-2">
                                        <div>
                                            <div class="square-box-Discovery legend-box bar-in-trial"
                                                style="width: 16px; height: 16px;">
                                            </div>
                                        </div>
                                        <div class="ml-2">
                                            <div class="insights-labels Discovery font-weight-bold m-0">
                                                {{$caseStageListArray[2]}} <span class="font-weight-light ml-2">{{$two}}
                                                </span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <?php 
                            if($onholdSet==1){
                                if($three!=0){
                                    $threeDayText=($three==0)?' Day':' Days';
                                    $three=$three.$threeDayText;
                                }else{
                                    $three='<1  Day';
                                }
                                 ?>
                            <div style="width: 370px;">
                                <div class="mb-1 mx-4">
                                    <div class="row mr-2">
                                        <div>
                                            <div class="square-box-Discovery legend-box bar-on-hold"
                                                style="width: 16px; height: 16px;">
                                            </div>
                                        </div>
                                        <div class="ml-2">
                                            <div class="insights-labels Discovery font-weight-bold m-0">
                                                {{$caseStageListArray[3]}} <span
                                                    class="font-weight-light ml-2">{{$three}} </span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php
                             }
                             ?>
                        </div>
                    </div> <?php */?>
                </div>
            </div>
        </div>
    </div>
    <div class="case-detail-case-information m-3" id="printHtml">
        <div>
            <h3 id="hiddenLable">{{($CaseMaster->case_title)??''}}</h3>
            <div class="mb-2">
                <h5 class="card-title">Case Information</h5>
                <div class="row ">
                    <div class="col-lg-6">
                        <div class="case-info-row mt-2 row ">
                            <div class="font-weight-bold col-3 col-md-3 col-lg-5">Name</div>
                            <div class="pl-0 pr-1 col-9 col-md-9 col-lg-7"><span>
                                    {{($CaseMaster->case_title)??''}}</span></div>
                        </div>
                        <div class="case-info-row mt-2 row ">
                            <div class="font-weight-bold col-3 col-md-3 col-lg-5">Case
                                Number</div>
                            <div class="pl-0 pr-1 col-9 col-md-9 col-lg-7">
                                <span>{{($CaseMaster->case_number)??''}}</span></div>
                        </div>
                        <div class="case-info-row mt-2 row ">
                            <div class="font-weight-bold col-3 col-md-3 col-lg-5">Practice
                                Area</div>
                            <div class="pl-0 pr-1 col-9 col-md-9 col-lg-7"><span> <?php 
                                $Area="Not Specified";
                                foreach($practiceAreaList as $k=>$v){
                                  if($CaseMaster->practice_area==$v->id){ $Area= $v->title; }
                                 } 
                                 echo $Area;
                                 ?></span>
                            </div>
                        </div>
                        <div class="case-info-row mt-2 row ">
                            <div class="font-weight-bold col-3 col-md-3 col-lg-5">Case Stage
                            </div>
                            <div class="pl-0 pr-1 col-9 col-md-9 col-lg-7"><span>
                                    <?php 
                                $Stage="Not Specified";
                                foreach($caseStageList as $ks=>$vs){
                                  if($CaseMaster->case_status==$vs->id){ $Stage= $vs->title; }
                                } 
                                echo $Stage;
                                ?></span></div>
                        </div>
                        <div class="case-info-row mt-2 row ">
                            <div class="font-weight-bold col-3 col-md-3 col-lg-5">
                                Description</div>
                            <div class="pl-0 pr-1 col-9 col-md-9 col-lg-7">
                                <div class="text-wrap">
                                    <p>{{($CaseMaster->case_description)??"Not Specified"}}</p>
                                </div>
                            </div>
                        </div>
                        <div class="case-info-row mt-2 row ">
                            <div class="font-weight-bold col-3 col-md-3 col-lg-5">Conflict Check</div>
                            <div class="pl-0 pr-1 col-9 col-md-9 col-lg-7">
                                <?php
                                if($CaseMaster->conflict_check=='1' && $CaseMaster->conflict_check_at !=NULL){
                                $currentConvertedDate= $CommonController->convertUTCToUserTime($CaseMaster->conflict_check_at,Auth::User()->user_timezone??'UTC');
                                ?>
                                <span class="field-value">Marked complete {{date('m/d/Y h:i a',strtotime($currentConvertedDate))}}</span>
                                <?php } ?> 
                            </div>
                        </div>
                        <div class="case-info-row mt-2 row ">
                            <div class="font-weight-bold col-3 col-md-3 col-lg-5">Conflict Check Notes</div>
                            <div class="pl-0 pr-1 col-9 col-md-9 col-lg-7">
                                <span>{{($CaseMaster->conflict_check_description)??''}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="case-info-row mt-2 row ">
                            <div class="font-weight-bold col-3 col-md-3 col-lg-5">Date
                                Opened</div>
                            <div class="pl-0 pr-1 col-9 col-md-9 col-lg-7"><span> <?php 
                                if(isset($CaseMaster->case_open_date)){
                                  echo date('m/d/Y',strtotime($CaseMaster->case_open_date));

                                }else{
                                  echo "Not Specified";
                                }?></span>
                            </div>
                        </div>
                        <div class="case-info-row mt-2 row ">
                            <div class="font-weight-bold col-3 col-md-3 col-lg-5">Date
                                Closed</div>
                            <div class="pl-0 pr-1 col-9 col-md-9 col-lg-7"><span> <?php 
                                if(isset($CaseMaster->case_close_date)){
                                  echo date('m/d/Y',strtotime($CaseMaster->case_close_date));

                                }else{
                                  echo "Not Specified";
                                }?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
        </div>
    </div>
</div>
<div class="col-md-4">
    <div style="width: 100%;" class="case-detail-status-card insights-card d-print-none pr-2 card">
        <div class="card-body">
            <div class="card-title"><strong>Status</strong></div>
            <div>
                <div class="mt-2">

                    <div class="test-created-by-info">Created <?php 
                        if(isset($lastStatusUpdate->created_at)){

                            $CommonController= new App\Http\Controllers\CommonController();
                            $OwnDate=$CommonController->convertUTCToUserTime($lastStatusUpdate->created_at,Auth::User()->user_timezone);
                                        
                          echo date('M d,Y h:i a',strtotime($OwnDate));

                        }else{
                          echo "Not Specified";
                        }?><small> by <a class="test-created-by-link pendo-case-info-status-created-by"
                                href="#">{{($lastStatusUpdate->first_name)??''}}
                                {{($lastStatusUpdate->last_name)??''}}</a></small></div>
                    <div class="d-print-none">
                        <div class="mt-1 status-update-description text-break">
                            <small>{{($lastStatusUpdate->update_status)??''}}</small></div><button type="button"
                            class="p-0 test-add-new d-print-none align-baseline pendo-case-info-new-status-update btn btn-link"><small><a
                                    data-toggle="modal" data-target="#statusUpdate" data-placement="bottom"
                                    href="javascript:;" onclick="loadCaseUpdate('{{$CaseMaster->case_id}}');"><small>Add
                                        New Update</small></a></small></button>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div style="width: 100%;" class="case-detail-task-card insights-card pr-2 mt-2 d-print-none card">
        <div class="card-body">
            <div class="case-tasks-card">
                <div class="accordion" id="accordionRightIcon">
                    <div>
                        <div class="header-elements-inline card-title">
                            <h6 class="card-title ul-collapse__icon--size ul-collapse__right-icon mb-0">
                                <a data-toggle="collapse" class="text-default" href="#accordion-item-icon-right-coll-1"
                                    aria-expanded="true"><strong>Tasks</strong><small> (next 30 days)</small></a>
                            </h6>
                        </div>
                        <div id="accordion-item-icon-right-coll-1" class="collapse show" style="">
                            <div class="d-flex tasks-card-header mt-1">
                                <h1 class="incompleted-tasks-count">{{$taskCountNextDays}}</h1>
                                <div class="ml-auto mt-3 font-secondary">{{$taskCompletedCounter}} completed</div>
                            </div>
                            <div style="display: contents;">
                                <hr class="m-0">
                                <div class="mt-1 mb-2"><strong class="mb-2">Overdue:</strong>
                                    <?php 
                                if(!$overdueTaskList->isEmpty()){
                                foreach($overdueTaskList as $k=>$v){?>
                                    <div class="highlight-item-list">
                                        <div class="d-flex justify-content-between mt-1"><button type="button"
                                                class="p-0 pendo-case-info-task-link btn btn-link">{{$v->task_title}}</button>
                                            <div>{{date('m/d/y',strtotime($v->task_due_on))}}</div>
                                        </div>
                                    </div>
                                    <?php } }else{
                                    ?>
                                    <div class="highlight-item-list">
                                        <div class="text-muted">None</div>
                                    </div>
                                    <?php 
                                } ?>
                                </div>
                                <div><strong class="mb-2">Upcoming:</strong>
                                    <div class="highlight-item-list">
                                        <?php 
                            if(!$upcomingTaskList->isEmpty()){
                            foreach($upcomingTaskList as $k=>$v){?>
                                        <div class="d-flex justify-content-between mt-1"><button type="button"
                                                class="p-0 pendo-case-info-task-link btn btn-link">{{$v->task_title}}</button>
                                            <div>{{date('m/d/y',strtotime($v->task_due_on))}}</div>
                                        </div>
                                        <?php } }else{
                                ?>
                                        <div class="text-muted">None</div>
                                        <?php 
                            } ?>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="width: 100%;" class="case-detail-events-card insights-card pr-2 mt-2 d-print-none card">
        <div class="card-body">
            <div class="case-events-card">
                <div class="accordion" id="accordionRightIcon">
                    <div>
                        <div class="header-elements-inline card-title">
                            <h6 class="card-title ul-collapse__icon--size ul-collapse__right-icon mb-0">
                                <a data-toggle="collapse" class="text-default" href="#accordion-item-icon-right-coll-2"
                                    aria-expanded="true">
                                    <strong>Events</strong><small> (next 365 days)</small></a>
                            </h6>
                        </div>
                        <div id="accordion-item-icon-right-coll-2" class="collapse show" style="">

                            <div class="d-flex tasks-card-header">
                                <h1 class="upcoming-events-count mt-1">{{$eventCountNextDays}}</h1>
                            </div>
                            <div style="display: contents;">
                                <hr class="m-0">
                                <div class="mt-1 mb-2">
                                    <div class="events-highlight-item-list">
                                        <?php if(!$upcomingEventList->isEmpty()){
                                            foreach($upcomingEventList as $k=>$v){
                                                if($k!="3"){?>
                                                        <div class="d-flex justify-content-between mt-1">
                                                            <a class="align-items-center" data-toggle="modal" data-target="#loadCommentPopup" href="javascript:;"
                                                            onclick="loadEventComment({{$v->event_id}}, {{$v->id}});">
                                                            {{-- <a href="{{route('events/')}}" class="p-0 pendo-case-info-task-link btn btn-link"> --}}
                                                                {{$v->event->event_title ?? "<No Title>"}}
                                                            </a>
                                                            <div>{{date('m/d/y',strtotime($v->start_date))}}</div>
                                                        </div>
                                                        <?php } } }else{
                                                ?>
                                                        <div class="text-muted">None</div>
                                                        <?php 
                                            } ?>
                                            <?php
                                            if(count($upcomingEventList)=="4"){?>
                                            <div class="row show-more ml-1 mt-2">
                                                
                                                <a href="{{ route('calendars', $CaseMaster->case_unique_number) }}" tabindex="0" role="menuitem" class="btn-link">Show more</a>
                                            </div>
                                            <?php } ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="case-details-feedback-box mt-2 d-print-none pendo-case-info-feedback-button"
        style="border-width: 2px; border-radius: 5px; border-style: dashed; border-color: gainsboro; padding: 20px; text-align: center;">
        <div class="footer-info-text mb-2" style="font-weight: bold;">
            What other charts,graphs or insights do you want to see here?
        </div>
        <a data-toggle="modal" data-target="#loadAddFeedBack" data-placement="bottom" href="javascript::void(0);">
            <button type="button" class="feedback-button btn btn-secondary" onclick="setFeedBackForm('single','Case Details Tab');">Submit feedback</button>
        </a>
    </div>
</div>
<div id="statusUpdate" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Status Update</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="updateLoad">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="typeSelect" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">You have created your first case!</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="ModelData">
                        <div class="loader-bubble loader-bubble-primary" style="display: block;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<a data-toggle="modal" style="display:none;" id="forfirstcase" data-target="#typeSelect" data-placement="bottom"
    href="javascript:;"> <button class="btn btn-primary btn-rounded m-1" type="button" id="forfirstcase"
        onclick="typeSelection();"></button></a>


<div id="caseTimelineModal" class="modal fade show" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Case Timeline History</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="caseTimelineModalArea"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('case.event.event_modals')

@section('page-js-inner')
<script type="text/javascript">
    
    $(document).ready(function () {
        $('.dropdown-toggle').dropdown();  
        <?php if ($caseCount == 1) {?>
            // $("#forfirstcase").trigger('click');
            // loadAfterFirstCase(); 
            <?php } ?>
    });
    function loadCaseUpdate(id) {
        $("#preloader").show();
        $.ajax({
            type: "POST",
            url: baseUrl + "/case/loadCaseUpdate", // json datasource
            data: {
                "case_id": id
            },
            success: function (res) {
                $("#updateLoad").html(res);
                $("#preloader").hide();
            }
        })
    }
    function loadAfterFirstCase() {
        $("#preloader").show();
        $(function () {
            $.ajax({
                type: "POST",
                url: baseUrl + "/court_cases/loadAfterFirstCase", // json datasource
                data: 'loadStep1',
                success: function (res) {
                    $("#ModelData").html('');
                    $("#ModelData").html(res);
                    $("#preloader").hide();
                }
            })
        })
    }

    function caseStageTimeline(){
        $("#caseTimelineModal").modal("show");
        $("#caseTimelineModalArea").html('<img src="{{LOADER}}""> Loading...');
        $.ajax({
            type: "POST",
            url: baseUrl + "/court_cases/loadCaseTimeline", // json datasource
            data: {'case_id':{{$CaseMaster->case_id}} },
            success: function (res) {
                $("#caseTimelineModalArea").html(res);
                $("#preloader").hide();
            }
        })
     
    }

    function printEntry()
    {
        $('#hiddenLable').show();        
        var canvas = $(".printDiv").html(document.getElementById("printHtml").innerHTML);
        $(".main-content-wrap").remove();
        window.print(canvas);
        // w.close();
        $(".printDiv").html('');
        $('#hiddenLable').hide();
        window.location.reload();
        return false;  
    }
    $('#hiddenLable').hide();

</script>

@stop
