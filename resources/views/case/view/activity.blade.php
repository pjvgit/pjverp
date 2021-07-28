<div class="tab-pane fade active show" id="timeline" role="tabpanel" aria-labelledby="timeline-tab">
    <ul class="timeline clearfix">
        <li class="timeline-line"></li>
        <?php 
        $t=0;
        foreach($mainArray as $k=>$v){?>
        <li class="timeline-item">
            <div class="timeline-badge">
                <i class="badge-icon bg-primary text-white i-Add-User"></i>
            </div>
            <div class="timeline-card card">
                <div class="card-body"> 
                    <div class="mb-1">
                        <a href="{{ route('contacts/attorneys/info', base64_encode($v['created_id'])) }}"> {{$v['created_by']}} </a>
                        <strong class="mr-1">{{$v['title']}} </strong> 
                        @if($v['staff_id'] != '')
                        <a href="{{ route('contacts/clients/view', $v['staff_id']) }}"> {{$v['staff_name']}} </a>
                        @else
                        <a href=""> {{$v['case_name']}} </a>
                        @endif
                        <p class="text-muted">{{$v['created_at']}}</p>
                    </div>
                </div>
            </div>
        </li>
        <?php  } ?>
    </ul>
    <ul class="timeline clearfix">
        <li class="timeline-line"></li>
        <li class="timeline-group text-center">
            <button class="btn btn-icon-text btn-warning"><i class="i-Calendar-4"></i> Case created at {{$caseCreatedDate}}
            </button>
            
        </li>
    </ul>
</div>

{{-- <div class="case-activities-container" data-court-case-id="12065562">
    <div>
        <div>
            <div class="row ">
                <div class="ml-auto w-100 col-4">
                    <div class="float-right activity-type">
                        <div role="group" class="btn-group"><button type="button"
                                class="all-activities-filter btn btn-secondary active">All</button><button type="button"
                                class="text-nowrap recent-activities-filter btn btn-secondary">Recent
                                Activities</button><button type="button"
                                class="text-nowrap case-timeline-filter btn btn-secondary">Case Timeline</button></div>
                    </div>
                </div>
            </div>
        </div>
        <hr class="my-1">
        <div class="activity-timeline-container p-1">
            <div>
                <div class="d-flex" style="position: relative;">
                    <div>
                        <div class="bg-info"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;"
                            id="date-string-2020-09-28T00:00:00+05:30"></div><span style="font-size: 100%;"
                            class="badge badge-info">Sep 27</span>
                    </div>
                    <div class="w-100 ml-2"><span class="text-muted ">in 10 days</span><br><br></div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-clipboard-check fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row task-row timeline-detail-row">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row ">
                                        <div class="col-5">
                                            <h5 class="title"><strong>Task</strong> - [SAMPLE] Deadline to Submit
                                                Paperwork</h5>
                                        </div>
                                        <div class="col-2">
                                            <h5>Status</h5>
                                            <div class="text-muted"><i class="fas fa-check fa-sm mr-1 text-muted"
                                                    style="opacity: 0.2;"></i>Incomplete</div>
                                        </div>
                                        <div class="col-2">
                                            <h5>Due</h5>
                                            <div>Sep 28, 2020</div>
                                        </div>
                                        <div class="col-2">
                                            <h5>Priority</h5>
                                            <div class="text-warning">High</div>
                                        </div>
                                        <div class="col-1"><button type="button" class="p-0 undefined btn btn-link"><i
                                                    class="fas fa-eye text-muted"></i></button></div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
            </div>
            <div>
                <div class="d-flex" style="position: relative;">
                    <div>
                        <div class="bg-info"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;"
                            id="date-string-2020-09-24T00:00:00+05:30"></div><span style="font-size: 100%;"
                            class="badge badge-info">Wed, Sep 23</span>
                    </div>
                    <div class="w-100 ml-2"><span class="text-muted ">in 6 days</span><br><br></div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-calendar-alt fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row event-row timeline-detail-row">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row ">
                                        <div class="col-5">
                                            <h5 class="title"><strong>Event</strong> - [SAMPLE] Client Meeting with John
                                                Doe</h5>
                                            <div class="text-muted">Wed, Sep 23, 2020,
                                                1pm — 2:30pm</div>
                                        </div>
                                        <div class="col-2"></div>
                                        <div class="col-4"></div>
                                        <div class="col-1"><button type="button"
                                                class="p-0 view-event-details btn btn-link"><i
                                                    class="fas fa-eye text-muted"></i></button></div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
            </div>
            <div>
                <div class="d-flex" style="position: relative;">
                    <div>
                        <div class="bg-info"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;"
                            id="date-string-2020-09-21T00:00:00+05:30"></div><span style="font-size: 100%;"
                            class="badge badge-info">Sun, Sep 20</span>
                    </div>
                    <div class="w-100 ml-2"><span class="text-muted ">in 3 days</span><br><br></div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-calendar-alt fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row event-row timeline-detail-row">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row ">
                                        <div class="col-5">
                                            <h5 class="title"><strong>Event</strong> - [SAMPLE] In-Office Discovery
                                                Meeting</h5>
                                            <div class="text-muted">Mon, Sep 21, 2020,
                                                10am — 1pm</div>
                                        </div>
                                        <div class="col-2"></div>
                                        <div class="col-4"></div>
                                        <div class="col-1"><button type="button"
                                                class="p-0 view-event-details btn btn-link"><i
                                                    class="fas fa-eye text-muted"></i></button></div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
            </div>
            <div>
                <div class="d-flex" style="position: relative;">
                    <div>
                        <div class="bg-secondary"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;"
                            id="date-string-2020-09-18T00:00:00+05:30"></div><span style="font-size: 100%;"
                            class="badge badge-secondary">Today</span>
                    </div>
                    <div class="w-100 ml-2"><span class="text-muted "></span><br><br></div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-clipboard-check fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row task-row timeline-detail-row">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row ">
                                        <div class="col-5">
                                            <h5 class="title"><strong>Task</strong> - [SAMPLE] Prepare Docs</h5>
                                            <div>
                                                <div style="height: 10px;" class="my-2 progress">
                                                    <div class="progress-bar bg-info" style="width: 50%;"
                                                        role="progressbar" aria-valuenow="1" aria-valuemin="0"
                                                        aria-valuemax="2"></div>
                                                </div>
                                                <div>1/2 subtasks completed</div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <h5>Status</h5>
                                            <div class="text-muted"><i class="fas fa-check fa-sm mr-1 text-muted"
                                                    style="opacity: 0.2;"></i>Incomplete</div>
                                        </div>
                                        <div class="col-2">
                                            <h5>Due</h5>
                                            <div>Sep 18, 2020</div>
                                        </div>
                                        <div class="col-2">
                                            <h5>Priority</h5>
                                            <div class="text-warning">High</div>
                                        </div>
                                        <div class="col-1"><button type="button" class="p-0 undefined btn btn-link"><i
                                                    class="fas fa-eye text-muted"></i></button></div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
            </div>
            <div>
                <div class="d-flex" style="position: relative;">
                    <div>
                        <div class="bg-info"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;"
                            id="date-string-2020-09-17T00:00:00+05:30"></div><span style="font-size: 100%;"
                            class="badge badge-info">Yesterday</span>
                    </div>
                    <div class="w-100 ml-2"><span class="text-muted selenium-no-activity">No activity</span><br><br>
                    </div>
                </div>
            </div>
            <div>
                <div class="d-flex" style="position: relative;">
                    <div>
                        <div class="bg-info"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;"
                            id="date-string-2020-09-14T00:00:00+05:30"></div><span style="font-size: 100%;"
                            class="badge badge-info">Sep 13</span>
                    </div>
                    <div class="w-100 ml-2"><span class="text-muted ">4 days ago</span><br><br></div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-info fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row case-row recent-activity-row">
                            <div class="date-time text-muted">01:38 am</div>
                            <div class="d-flex align-items-center"><i class="fa fa-pen-square text-info mr-1"></i>
                                <div class="d-flex flex-row"><a href="/contacts/attorneys/19700868"
                                        class="d-flex align-items-center user-link">Firm User1</a></div> &nbsp;<span
                                    class="font-weight-bold">updated case</span>&nbsp;<span><a
                                        href="/court_cases/12065562">John Doe Matter</a></span>&nbsp;
                            </div>
                        </div><br>
                    </div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-info fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row case-row recent-activity-row">
                            <div class="date-time text-muted">01:38 am</div>
                            <div class="d-flex align-items-center"><i class="fa fa-pen-square text-info mr-1"></i>
                                <div class="d-flex flex-row"><a href="/contacts/attorneys/19700868"
                                        class="d-flex align-items-center user-link">Firm User1</a></div> &nbsp;<span
                                    class="font-weight-bold">updated case</span>&nbsp;<span><a
                                        href="/court_cases/12065562">[SAMPLE] John Doe Matter</a></span>&nbsp;
                            </div>
                        </div><br>
                    </div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-info-circle fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row status-update-row timeline-detail-row">
                            <div>
                                <h5 class="title"><strong>Status Update</strong></h5>
                            </div>
                            <div class="card">
                                <div id="status-update-1302939" class="card-body">
                                    <div class="row ">
                                        <div class="col-11">
                                            <div class="text-muted mb-3">Last updated by Firm User1 -
                                                Sep 14, 1:23 am</div>
                                        </div>
                                        <div class="col-1"><a href="/court_cases/12065562/status_updates"
                                                class="p-0 status_update-activity btn btn-link"><i
                                                    class="fas fa-list text-muted"></i></a></div>
                                    </div>
                                    <div>
                                        <div>asd</div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-info-circle fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row status-update-row timeline-detail-row">
                            <div>
                                <h5 class="title"><strong>Status Update</strong></h5>
                            </div>
                            <div class="card">
                                <div id="status-update-1302931" class="card-body">
                                    <div class="row ">
                                        <div class="col-11">
                                            <div class="text-muted mb-3">Last updated by Firm User1 -
                                                Sep 13, 10:16 pm</div>
                                        </div>
                                        <div class="col-1"><a href="/court_cases/12065562/status_updates"
                                                class="p-0 status_update-activity btn btn-link"><i
                                                    class="fas fa-list text-muted"></i></a></div>
                                    </div>
                                    <div>
                                        <div>TEst</div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
            </div>
            <div>
                <div class="d-flex" style="position: relative;">
                    <div>
                        <div class="bg-info"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;"
                            id="date-string-2020-09-09T00:00:00+05:30"></div><span style="font-size: 100%;"
                            class="badge badge-info">Sep 8</span>
                    </div>
                    <div class="w-100 ml-2"><span class="text-muted ">9 days ago</span><br><br></div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-info fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row case-row recent-activity-row">
                            <div class="date-time text-muted">02:16 am</div>
                            <div class="d-flex align-items-center"><i class="fa fa-pen-square text-info mr-1"></i>
                                <div class="d-flex flex-row"><a href="/contacts/attorneys/19700868"
                                        class="d-flex align-items-center user-link">Firm User1</a></div> &nbsp;<span
                                    class="font-weight-bold">updated case</span>&nbsp;<span><a
                                        href="/court_cases/12065562">[SAMPLE] John Doe Matter</a></span>&nbsp;
                            </div>
                        </div><br>
                    </div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-info fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row case-row recent-activity-row">
                            <div class="date-time text-muted">01:51 am</div>
                            <div class="d-flex align-items-center"><i class="fa fa-pen-square text-info mr-1"></i>
                                <div class="d-flex flex-row"><a href="/contacts/attorneys/19700868"
                                        class="d-flex align-items-center user-link">Firm User1</a></div> &nbsp;<span
                                    class="font-weight-bold">updated case</span>&nbsp;<span><a
                                        href="/court_cases/12065562">[SAMPLE] John Doe Matter</a></span>&nbsp;
                            </div>
                        </div><br>
                    </div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-info-circle fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row status-update-row timeline-detail-row">
                            <div>
                                <h5 class="title"><strong>Status Update</strong></h5>
                            </div>
                            <div class="card">
                                <div id="status-update-1289286" class="card-body">
                                    <div class="row ">
                                        <div class="col-11">
                                            <div class="text-muted mb-3">Last updated by Firm User1 -
                                                Sep 9, 1:18 am</div>
                                        </div>
                                        <div class="col-1"><a href="/court_cases/12065562/status_updates"
                                                class="p-0 status_update-activity btn btn-link"><i
                                                    class="fas fa-list text-muted"></i></a></div>
                                    </div>
                                    <div>
                                        <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
                                            tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                                            quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                                            consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                                            cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat
                                            non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-info-circle fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row status-update-row timeline-detail-row">
                            <div>
                                <h5 class="title"><strong>Status Update</strong></h5>
                            </div>
                            <div class="card">
                                <div id="status-update-1289282" class="card-body">
                                    <div class="row ">
                                        <div class="col-11">
                                            <div class="text-muted mb-3">Last updated by Firm User1 -
                                                Sep 9, 12:36 am</div>
                                        </div>
                                        <div class="col-1"><a href="/court_cases/12065562/status_updates"
                                                class="p-0 status_update-activity btn btn-link"><i
                                                    class="fas fa-list text-muted"></i></a></div>
                                    </div>
                                    <div>
                                        <div>test</div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
            </div>
            <div>
                <div class="d-flex" style="position: relative;">
                    <div>
                        <div class="bg-info"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;"
                            id="date-string-2020-09-08T00:00:00+05:30"></div><span style="font-size: 100%;"
                            class="badge badge-info">Sep 7</span>
                    </div>
                    <div class="w-100 ml-2"><span class="text-muted ">10 days ago</span><br><br></div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-briefcase fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row stage-row timeline-detail-row">
                            <div>
                                <h5 class="title"><strong>Case Stage</strong></h5>
                            </div>
                            <div class="card">
                                <div class="row card-body">
                                    <div class="col-5">
                                        <div class="text-muted mb-2">Updated at 06:44 PM</div>
                                        <div class="h4">Discovery <i class="fa fa-arrow-right" aria-hidden="true">
                                            </i><strong> In Trial </strong></div>
                                    </div>
                                    <div class="col-6"></div>
                                    <div class="col-1"><a href="/case_stages"
                                            class="p-0 view-case-stage-settings btn btn-link"><i
                                                class="fas fa-cog text-muted"></i></a></div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
            </div>
            <div>
                <div class="d-flex" style="position: relative;">
                    <div>
                        <div class="bg-info"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;"
                            id="date-string-2020-09-07T00:00:00+05:30"></div><span style="font-size: 100%;"
                            class="badge badge-info">Sep 6</span>
                    </div>
                    <div class="w-100 ml-2"><span class="text-muted ">11 days ago</span><br><br></div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-briefcase fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row stage-row timeline-detail-row">
                            <div>
                                <h5 class="title"><strong>Case Stage</strong></h5>
                            </div>
                            <div class="card">
                                <div class="row card-body">
                                    <div class="col-5">
                                        <div class="text-muted mb-2">Started at 06:23 PM</div>
                                        <div class="h4"> Discovery </div>
                                    </div>
                                    <div class="col-6"></div>
                                    <div class="col-1"><a href="/case_stages"
                                            class="p-0 view-case-stage-settings btn btn-link"><i
                                                class="fas fa-cog text-muted"></i></a></div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-sticky-note fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row note-row timeline-detail-row">
                            <div>
                                <h5 class="title"><strong>Note</strong></h5>
                            </div>
                            <div class="card bg-light">
                                <div id="note-31124106" class="card-body">
                                    <div class="row ">
                                        <div class="col-11">
                                            <div><strong>Subject: [SAMPLE] John Doe Phone Call</strong></div>
                                        </div>
                                        <div class="col-1"><a href="/court_cases/12065562/notes"
                                                class="p-0 note-activity btn btn-link"><i
                                                    class="fas fa-list text-muted"></i></a></div>
                                    </div>
                                    <div class="text-muted mb-3">Last updated by MyCase System -
                                        Sep 7, 5:53 am</div>
                                    <div>John Doe called in regarding the upcoming meeting. Adding a task to call him
                                        back.</div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-info-circle fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row status-update-row timeline-detail-row">
                            <div>
                                <h5 class="title"><strong>Status Update</strong></h5>
                            </div>
                            <div class="card">
                                <div id="status-update-1283860" class="card-body">
                                    <div class="row ">
                                        <div class="col-11">
                                            <div class="text-muted mb-3">Last updated by MyCase System -
                                                Sep 7, 5:53 am</div>
                                        </div>
                                        <div class="col-1"><a href="/court_cases/12065562/status_updates"
                                                class="p-0 status_update-activity btn btn-link"><i
                                                    class="fas fa-list text-muted"></i></a></div>
                                    </div>
                                    <div>
                                        <div>Sent the engagement letter and preparing for the Discovery Prep Meeting and
                                            the Pleading now in advance of the pending deadline.</div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="far fa-clock fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row time-entry-row timeline-detail-row">
                            <div class="card">
                                <div class="row card-body">
                                    <div class="col-5">
                                        <h5 class="title"><strong>Time entry</strong> - Document Preparation</h5>
                                        <div>Created by <span class="font-weight-bold">MyCase System</span></div>
                                    </div>
                                    <div class="col-2">
                                        <h5>Duration</h5>
                                        <div>0.5 hour(s)</div>
                                    </div>
                                    <div class="col-2">
                                        <h5>Rate</h5>
                                        <div>$250.00/hr</div>
                                    </div>
                                    <div class="col-2">
                                        <h5 class="font-weight-bold">Total</h5>
                                        <div>$125.00</div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-receipt fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row expense-row timeline-detail-row">
                            <div class="card">
                                <div class="row card-body">
                                    <div class="col-5">
                                        <h5 class="title"><strong>Expense</strong> - Postage</h5>
                                        <div>Created by&nbsp;<span class="font-weight-bold">MyCase System</span></div>
                                    </div>
                                    <div class="col-2">
                                        <h5>Unit</h5>
                                        <div>1</div>
                                    </div>
                                    <div class="col-2">
                                        <h5>Cost</h5>
                                        <div>$5.00</div>
                                    </div>
                                    <div class="col-2">
                                        <h5 class="font-weight-bold">Total</h5>
                                        <div>$5.00</div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
            </div>
            <div>
                <div class="d-flex" style="position: relative;">
                    <div>
                        <div class="bg-info"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;"
                            id="date-string-2020-09-06T00:00:00+05:30"></div><span style="font-size: 100%;"
                            class="badge badge-info">Sep 5</span>
                    </div>
                    <div class="w-100 ml-2"><span class="text-muted ">12 days ago</span><br><br></div>
                </div>
                <div class="d-flex" style="position: relative;">
                    <div style="width: 70px;">
                        <div class="bg-light"
                            style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                        </div>
                        <div class="text-center bg-light border border-black"
                            style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                            <i class="fas fa-clipboard-check fa-2x text-black-50"></i></div>
                    </div>
                    <div class="w-100">
                        <div class="timeline-generic-row task-row timeline-detail-row">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row ">
                                        <div class="col-5">
                                            <h5 class="title"><strong>Task</strong> - [SAMPLE] Call John Doe Back</h5>
                                        </div>
                                        <div class="col-2">
                                            <h5>Status</h5>
                                            <div class="text-muted"><i class="fas fa-check fa-sm mr-1 text-muted"
                                                    style="opacity: 0.2;"></i>Incomplete</div>
                                        </div>
                                        <div class="col-2">
                                            <h5>Due</h5>
                                            <div>Sep 06, 2020</div>
                                        </div>
                                        <div class="col-2">
                                            <h5>Priority</h5>
                                            <div class="text-secondary">Medium</div>
                                        </div>
                                        <div class="col-1"><button type="button" class="p-0 undefined btn btn-link"><i
                                                    class="fas fa-eye text-muted"></i></button></div>
                                    </div>
                                </div>
                            </div>
                        </div><br>
                    </div>
                </div>
            </div>
            <div class="d-flex" style="position: relative;">
                <div>
                    <div class="bg-info"
                        style="height: 100%; width: 3px; position: absolute; margin-left: 20px; margin-top: 5px; z-index: -10;"
                        id="date-string-2020-09-17T00:00:00+05:30"></div><span style="font-size: 100%;"
                        class="badge badge-info">Yesterday</span>
                </div>
                <div class="w-100 ml-2"><span class="text-muted "></span><br><br></div>
            </div>
            <div class="d-flex" style="position: relative;">
                <div style="width: 70px;">
                    <div class="bg-light"
                        style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                    </div>
                    <div class="text-center bg-light border border-black"
                        style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                        <i class="far fa-user-circle fa-2x text-black-50"></i></div>
                </div>
                <div class="w-100">
                    <div class="timeline-generic-row client-row recent-activity-row">
                        <div class="date-time text-muted">03:47 am</div>
                        <div class="d-flex align-items-center"><i class="fa fa-plus-square text-success mr-1"></i>
                            <div class="d-flex flex-row"><a href="/contacts/attorneys/19700868"
                                    class="d-flex align-items-center user-link">Firm111 User1</a></div> &nbsp;<span
                                class="font-weight-bold">linked client</span>&nbsp;<span>
                                <div class="d-flex flex-row"><a href="/contacts/clients/19700869"
                                        class="d-flex align-items-center user-link">[SAMPLE] John Doe</a></div>
                            </span>&nbsp;
                        </div>
                    </div><br>
                </div>
            </div>
            <div class="d-flex" style="position: relative;">
                <div style="width: 70px;">
                    <div class="bg-light"
                        style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                    </div>
                    <div class="text-center bg-light border border-black"
                        style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                        <i class="fas fa-user-circle fa-2x text-black-50"></i></div>
                </div>
                <div class="w-100">
                    <div class="timeline-generic-row staff-row recent-activity-row">
                        <div class="date-time text-muted">03:47 am</div>
                        <div class="d-flex align-items-center"><i class="fa fa-plus-square text-success mr-1"></i>
                            <div class="d-flex flex-row"><a href="/contacts/attorneys/19700868"
                                    class="d-flex align-items-center user-link">Firm111 User1</a></div> &nbsp;<span
                                class="font-weight-bold">linked staff</span>&nbsp;<span>
                                <div class="d-flex flex-row"><a href="/contacts/attorneys/19771104"
                                        class="d-flex align-items-center user-link">Test user m</a></div>
                            </span>&nbsp;
                        </div>
                    </div><br>
                </div>
            </div>
            <div class="d-flex" style="position: relative;">
                <div style="width: 70px;">
                    <div class="bg-light"
                        style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                    </div>
                    <div class="text-center bg-light border border-black"
                        style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                        <i class="fas fa-user-circle fa-2x text-black-50"></i></div>
                </div>
                <div class="w-100">
                    <div class="timeline-generic-row staff-row recent-activity-row">
                        <div class="date-time text-muted">03:47 am</div>
                        <div class="d-flex align-items-center"><i class="fa fa-plus-square text-success mr-1"></i>
                            <div class="d-flex flex-row"><a href="/contacts/attorneys/19700868"
                                    class="d-flex align-items-center user-link">Firm111 User1</a></div> &nbsp;<span
                                class="font-weight-bold">linked staff</span>&nbsp;<span>
                                <div class="d-flex flex-row"><a href="/contacts/attorneys/19798184"
                                        class="d-flex align-items-center user-link">Test Child m Child</a></div>
                            </span>&nbsp;
                        </div>
                    </div><br>
                </div>
            </div>
            <div class="d-flex" style="position: relative;">
                <div style="width: 70px;">
                    <div class="bg-light"
                        style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                    </div>
                    <div class="text-center bg-light border border-black"
                        style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                        <i class="fas fa-user-circle fa-2x text-black-50"></i></div>
                </div>
                <div class="w-100">
                    <div class="timeline-generic-row staff-row recent-activity-row">
                        <div class="date-time text-muted">03:47 am</div>
                        <div class="d-flex align-items-center"><i class="fa fa-plus-square text-success mr-1"></i>
                            <div class="d-flex flex-row"><a href="/contacts/attorneys/19700868"
                                    class="d-flex align-items-center user-link">Firm111 User1</a></div> &nbsp;<span
                                class="font-weight-bold">linked staff</span>&nbsp;<span>
                                <div class="d-flex flex-row"><a href="/contacts/attorneys/19703138"
                                        class="d-flex align-items-center user-link">Firm user2</a></div>
                            </span>&nbsp;
                        </div>
                    </div><br>
                </div>
            </div>
            <div class="d-flex" style="position: relative;">
                <div style="width: 70px;">
                    <div class="bg-light"
                        style="height: 100%; width: 3px; position: absolute; margin-left: 20px; z-index: -10; border: 1px none black;">
                    </div>
                    <div class="text-center bg-light border border-black"
                        style="display: table-cell; width: 42px; height: 42px; border-radius: 50%; vertical-align: middle;">
                        <i class="fas fa-info fa-2x text-black-50"></i></div>
                </div>
                <div class="w-100">
                    <div class="timeline-generic-row case-row recent-activity-row">
                        <div class="date-time text-muted">03:47 am</div>
                        <div class="d-flex align-items-center"><i class="fa fa-plus-square text-success mr-1"></i>
                            <div class="d-flex flex-row"><a href="/contacts/attorneys/19700868"
                                    class="d-flex align-items-center user-link">Firm111 User1</a></div> &nbsp;<span
                                class="font-weight-bold">added case</span>&nbsp;<span><a
                                    href="/court_cases/12195223">jd</a></span>&nbsp;
                        </div>
                    </div><br>
                </div>
            </div><span class="text-muted mt-2 selenium-end-of-history">End of all case activities.</span>
        </div>
    </div>
</div> --}}
