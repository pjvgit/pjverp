<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('images/fav.png')}}" />
    <title>Legalcase

    </title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/styles/css/themes/lite-purple.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/styles/css/plugins/toastr.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/styles/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/styles/css/bootstrap-datepicker3.min.css')}}">

</head>
<body>
<div class="main-content  d-flex flex-column">
    <div class="main-content">
    <div class="m-4 p-2 card"><div class="col-12 ">
    <form class="createEvent" id="createEvent" name="createEvent" method="GET">
    @csrf
    
        <div class="row">
            <div class="">
                <label for="inputEmail3" class="col-sm-12">Case or Lead</label>
                <div class="col-8 mb-3">
                    <select class="form-control case_or_lead" id="case_or_lead_popup" name="case_or_lead">
                        <option value="">Search for an existing Case or Lead</option>
                        <optgroup label="Court Cases">
                            <?php foreach($CaseMasterData as $casekey=>$Caseval){ ?>
                            <option uType="case" <?php echo ($request->case_or_lead == $Caseval->id) ? 'selected' : ''; ?>
                                value="{{$Caseval->id}}">{{substr($Caseval->case_title,0,100)}} <?php if($Caseval->case_number!=''){  echo "(".$Caseval->case_number.")"; }?> <?php if($Caseval->case_close_date!=NULL){  echo "[Closed]"; }?> </option>
                            <?php } ?>
                        </optgroup>
                        <optgroup label="Leads">
                            <?php foreach($caseLeadList as $caseLeadListKey=>$caseLeadListVal){ ?>
                            <option uType="lead" <?php echo ($request->case_or_lead == $caseLeadListVal->id) ? 'selected' : ''; ?>
                            value="{{$caseLeadListVal->id}}">{{substr($caseLeadListVal->first_name,0,100)}} {{substr($caseLeadListVal->last_name,0,100)}}</option>
                            <?php } ?>
                        </optgroup>
                    </select>

                </div>
            </div>

            <div class="">
                <label for="inputEmail3" class="col-sm-12">Event type</label>
                <div class="col-8 mb-3">
                    <select class="form-control case_or_lead" id="event_type" name="event_type">
                            <option value="">Select Event Type</option>
                            <?php foreach($allEventType as $ekey=>$eval){ ?>
                                <option value="{{$eval->id}}" <?php echo ($request->event_type == $eval->id) ? 'selected' : ''; ?>>{{$eval->title}}</option>
                            <?php } ?>
                    </select>

                </div>
            </div>
            
                <div class="">
                    <label for="inputEmail3" class="col-sm-12">Start Date</label>
                    <div class="col-sm-12">
                        <input class="form-control input-date input-start" id="start_date" value="{{  date('m/d/Y', strtotime($request->start)) ?? convertUTCToUserTimeZone('dateOnly') }}" name="start" type="text"
                            placeholder="mm/dd/yyyy">

                    </div>
                </div>
                <div class="">
                    <label for="inputEmail3" class="col-sm-12">End Date</label>
                    <div class="col-sm-12">
                        <input class="form-control input-date input-end" id="end_date" value="{{ date('m/d/Y', strtotime($request->end)) ?? convertUTCToUserTimeZone('dateOnly') }}" name="end" type="text"
                            placeholder="mm/dd/yyyy">

                    </div>
                </div>
            <div class=" ">
                
                <div class="col-12">
                    <div class="filter-list d-flex flex-nowrap align-items-center mb-2">
                        <input type="checkbox" id="show-task-checkbox" name="show_task_checkbox"  <?php echo ($request->show_task_checkbox == 'on') ? 'checked' : ''; ?> >
                            <label class="mb-0 ml-2" for="show-task-checkbox">Include my tasks</label>
                        </div>
                    <div class="filter-list d-flex flex-nowrap align-items-center mb-2">
                        <input type="checkbox" id="show-sol-checkbox" name="show_sol_checkbox"  <?php echo ($request->show_sol_checkbox == 'on') ? 'checked' : ''; ?>>
                        <label class="mb-0 ml-2" for="show-sol-checkbox">Include Statute of Limitations (SOL)</label>
                    </div>
                    <div class="filter-list d-flex flex-nowrap align-items-center mb-3">
                        <input type="checkbox" id="show-description-checkbox" name="show_description_checkbox"  <?php echo ($request->show_description_checkbox == 'on') ? 'checked' : ''; ?>>
                        <label class="mb-0 ml-2" for="show-description-checkbox">Include event description</label>
                    </div>
                </div>
            </div>
        <div class="justify-content-between modal-footer">
            <div class="loader-bubble loader-bubble-primary innerLoader" id="innerLoader" style="display: none;"></div>
            <div>
                <button class="btn btn-primary example-button m-1 submit" id="submit"  type="submit"
                data-style="expand-left">Apply Filter</button>
            </div>
            <div>
                <button class="btn btn-info example-button m-1" onclick="window.print();return false;"
                data-style="expand-left">Print</button>
            </div>
            <div>
                <button class="btn m-1" onclick="window.location.href='{{ route("events/")}}';return false;"
                data-style="expand-left">Back to Calendar</button>
            </div>
        </div>
    </form> 
    </div>
    </div>

</div>
</div>


<!-- // show records -->
<div class="printDiv">
    <div class="m-4 p-2">
    <div>
        <div><h4>Events for ({{  date("Y-m-d", strtotime($request->start)) }} - {{ date("Y-m-d", strtotime($request->end)) }})</h4></div>
        <div class="print-detail-header mb-1" style="font-size: 14px;">
            <div class="name-tag header-name-tag d-flex mr-2">
                <div class="user-circle mr-1 d-inline-block" style="width: 15px; height: 15px; background-color: rgb(21, 157, 255);"></div>
                <div class="first-last-name-container text-truncate">My Calendar</div>
            </div>
        </div>
    </div>
        <div class="print-table" style="font-size: 14px;">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Date</th>
                            <th style="width: 150px;">Time</th>
                            <th style="width: 300px;">Event name</th>
                            <th style="width: 200px;">Court case / Lead</th>
                            <th style="width: 500px;">Shared / Attending</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if($CaseEventSOL)
                        @foreach ($CaseEventSOL as $key => $value)
                        <tr class="mb-1 list-event-row">
                            <td style="width: 80px;">{{$value->start_date}}</td>
                            <td style="width: 150px;">All days</td>
                            <td class="event-text-format" style="width: 300px;">
                                <div><div class="event-text-format agenda-title">
                                    <span class="mr-1 badge badge-secondary">Statute of Limitation</span>
                                    <b class="word-break">{{$value->event_title}}</b><br>
                                    {{ ($value->sol_satisfied == 'yes') ? 'Satisfied': 'Unsatisfied'}}
                                    </div></div>
                            </td>
                            <td style="width: 200px;">{{ $value->event_title }} ({{$value->case_number}})</td>
                            <td style="width: 500px;"></td>
                        </tr>
                        @endforeach
                        @endif
                        @if($Task)
                        @foreach ($Task as $key => $value)                        
                        <tr class="mb-1 list-event-row">
                            <td style="width: 80px;">{{$value->task_due_on}}</td>
                            <td style="width: 150px;">All days</td>
                            <td class="event-text-format" style="width: 300px;">
                                <div><div class="event-text-format agenda-title">
                                    <span style="background-color: rgb(202, 66, 69);" class="badge badge-secondary">TASK</span>
                                    <b class="word-break">{{$value->task_title}}</b><br>
                                    {{ ($value->status == 1 ) ? 'Complete' : 'Incomplete'}}<br>
                                    Priority: <?php if($value->task_priority == "1"){?> Low <?php }else if($value->task_priority == "2"){?> Medium <?php }else if($value->task_priority == "3") {?> High <?php }else{ ?> No Priority <?php } ?>
                                    </div>
                                </div>
                            </td>
                            <td style="width: 200px;">{{ ($value->case_id != '') ? $value->case_title  :  $value->first_name.' '.$value->last_name }}</td>
                            <td style="width: 500px;"></td>
                        </tr>
                        @endforeach
                        @endif
                        @foreach ($newarray as $key => $value)
                        <tr class="mb-1 list-event-row">
                            <td style="width: 80px;">{{$value->start_date}}</td>
                            <td style="width: 150px;"> @if($value->all_day == "no") {{ $value->start_time_user }} - {{ $value->end_time_user }} @else All Day @endif</td>
                            <td class="event-text-format" style="width: 300px;">
                                <div><div class="event-text-format agenda-title">
                                    <b class="word-break">{{$value->event_title}}</b><br>
                                    </div></div>
                            </td>
                            <td style="width: 200px;">{{ $value->caseTitle }} {{ ($value->caseNumber != null) ? '('.$value->caseNumber.')' : '' }}</td>
                            <td style="width: 500px;">
                                <?php $contactName = explode(",",$value->contactName); ?>
                                <div>
                                    @if($value->contactName != '' && count($contactName) > 0)
                                    <div><b>Clients &amp; Contacts</b>
                                    @foreach($contactName as $k => $v)
                                    <div class="print-detail-header">
                                        <div class="each-print-sharing-user d-flex flex-nowrap mb-1">
                                            <div class="name-tag d-flex mr-3">
                                                <div class="user-circle mr-1 d-inline-block" style="width: 15px; height: 15px; background-color: gray;"></div>
                                                <div class="first-last-name-container text-truncate">{{ ucfirst($v)}}</div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    </div>
                                    @endif
                                    <?php $staffName = explode(",",$value->staffName); ?>  
                                    @if($value->staffName != '' && count($staffName) > 0)
                                    <div><b>Staff</b>                                   
                                    @foreach($staffName as $k => $v)
                                    <div class="print-detail-header">
                                        <div class="each-print-sharing-user d-flex flex-nowrap mb-1">
                                            <div class="name-tag d-flex mr-3">
                                                <div class="user-circle mr-1 d-inline-block" style="width: 15px; height: 15px; background-color: rgb(21, 157, 255);"></div>
                                                <div class="first-last-name-container text-truncate">{{ ucfirst($v)}}</div>
                                                <!-- <div><b style="color: rgb(99, 108, 114); font-size: 10px;">Attending</b></div> -->
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    </div>

                                    @endif
                                </div>
                            </td>
                        </tr>
                        <?php if($request->show_description_checkbox == 'on' && $value->event_description != null){ ?> 
                        <tr class="mb-1 list-event-row">
                            <td style="width: 80px;"></td>
                            <td style="width: 150px;"></td>
                            <td class="event-text-format" colspan="3" style="width: 800px;">
                                <div><div class="event-text-format agenda-title">
                                    <b class="word-break">Description: </b></br>
                                    {{$value->event_description}}
                                    </div></div>
                            </td>
                        </tr>
                        <?php } ?>
                        @endforeach

                        </tbody>
                </table>                      
            </div>
        </div>
    </div>
</div>



<!-- jQuery -->
<script src="{{asset('assets/js/common-bundle-script.js')}}"></script>
<script src="{{asset('assets/js/script.js')}}"></script>
<script src="{{asset('assets/js/plugins/jquery-3.3.1.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/js/select2.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap-datepicker.js')}}" type="text/javascript"></script>


<script type="text/javascript">
    $(document).ready(function () {
        $(".case_or_lead, #event_type").select2({
            placeholder: "Select...",
            theme: "classic",
            allowClear: true,
           
        });

        // Initialize Date Pickers
        $('#start_date, #end_date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true,
            'todayBtn': "linked",
            'clearBtn': true,
            'todayHighlight': true
        });
    });
                 
</script>        
</body>

</html>