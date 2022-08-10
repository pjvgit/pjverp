<?php

namespace App\Http\Controllers;
use App\User,App\Countries;
use Illuminate\Http\Request,DateTime;
use DB,Session;
use Illuminate\Support\Facades\Auth;
use App\CaseMaster;
use App\TempUserSelection,App\CaseStage,App\CaseClientSelection;
use App\CaseStaff;
use App\CaseEventLocation,App\EventType;
use App\Event;
use App\EventRecurring;
use Carbon\Carbon,App\CaseEventLinkedStaff;
use App\Http\Controllers\CommonController;
use DateInterval,DatePeriod,App\CaseEventComment;
use App\Task,App\CaseTaskLinkedStaff,App\TaskChecklist,App\AllHistory;
use App\TaskReminder,App\TaskActivity,App\TaskTimeEntry,App\TaskComment;
use App\TaskHistory,App\UsersAdditionalInfo,App\LeadAdditionalInfo,App\CaseEventLinkedContactLead;
use Illuminate\Support\Facades\Log;

class CalendarController extends BaseController
{
    public function __construct()
    {
       
    }
    public function newindex()
    {
        return view("calendar.index-new");
    }
    public function newloadEventCalendar (Request $request)
    {        
        $offset = Carbon::now('America/Mexico_City')->offsetHours;
        $authUser = auth()->user();
        $CaseEvent = '';
        $byuser=json_decode($request->byuser, TRUE);
        if(count($byuser)) {
            $CaseEvent = EventRecurring::whereBetween('start_date',  [$request->start, $request->end])->has('event');
            $CaseEvent = $CaseEvent->whereHas("event", function($query) use($request, $authUser) {
                $query->where('firm_id', $authUser->firm_name);
                if($request->event_type!="[]"){
                    $event_type=json_decode($request->event_type, TRUE);
                    $query->whereIn('event_type_id', $event_type);
                }
                if($request->case_id != "") {
                    $query->where('case_id',$request->case_id);
                }
            });

            $CaseEvent = $CaseEvent->when($byuser , function($query) use ($byuser) {
                $query->where(function ($query) use ($byuser) {
                    foreach($byuser as $user) {
                        $query->orWhereJsonContains('event_linked_staff', ['user_id' => (string)$user]);
                    }
                });
            });

            if($request->taskLoad=='unread'){
                $CaseEvent = $CaseEvent->whereJsonContains('event_linked_staff', ['user_id' => (string)auth()->id(), 'is_read' => 'no']);
            }
            $CaseEvent = $CaseEvent/* ->whereNull('events.deleted_at') */->with('event', 'event.eventType')->get();
        }
        $newArray = array();
        $timezone = ($request->timezone == 'local') ? 'UTC' : $authUser->user_timezone ?? 'UTC';
        if(!empty($CaseEvent)) {
            foreach($CaseEvent as $k => $v) {
                $event = $v->event;
                $resource_id = [];
                $linkedStaff = encodeDecodeJson($v->event_linked_staff);
                if($linkedStaff) {
                    $resource_id = $linkedStaff->pluck('user_id')->toArray();
                }
                if($request->timezone == 'local') {
                    $startDate = ($event->is_full_day == 'no') ? $v->start_date.' '.$event->start_time : $v->start_date;
                } else {
                    $startDate = ($event->is_full_day == 'no') ? convertUTCToUserTime($v->start_date.' '.$event->start_time, $timezone) : $v->user_start_date->format('Y-m-d');
                }
                $newArray[] = [
                    'id' => $v->id,
                    'event_id' => $event->id,
                    'event_recurring_id' => $v->id,
                    'title' => $event->event_title ?? "<No Title>",
                    'tTitle' => $event->event_title ?? "<No Title>",
                    'start' => ($event->is_full_day == 'no') ? Carbon::parse("$startDate $timezone")->format('Y-m-d\TH:i:sP') : $startDate,
                    'allDay' => ($event->is_full_day == 'yes') ? true : false,
                    'color' => ($v->event && $event->eventType) ? $event->eventType->color_code : "#159dff",
                    'textColor' => '#000000',
                    'resourceIds' => $resource_id,
                ];
            }
            // return response()->json($newArray);
        }

        // Get SOL reminders
        if(isset($request->searchbysol) && $request->searchbysol=="true" && $request->taskLoad != 'unread'){
            $CaseEventSOL = Event::leftJoin('case_master','case_master.id','=','events.case_id')->where('events.firm_id', $authUser->firm_name)
            ->select("case_master.case_unique_number as case_unique_number","case_master.sol_satisfied","events.*")
            ->where('events.created_by', $authUser->id);
            $CaseEventSOL=$CaseEventSOL->whereBetween('start_date', [$request->start, $request->end]);
            $CaseEventSOL=$CaseEventSOL->where('is_SOL','yes');
            if($request->case_id != "") {
                $CaseEventSOL=$CaseEventSOL->where('case_id',$request->case_id);
            }
            $CaseEventSOL=$CaseEventSOL->whereNull('events.deleted_at')->get();
            if(!empty($CaseEventSOL)) {
                foreach($CaseEventSOL as $k => $v) {
                    if($v->sol_satisfied == 'yes') {
                        $t = '<span class="calendar-badge d-inline-block undefined badge badge-success" style="width: 30px;"><i aria-hidden="true" class="fa fa-check"></i>SOL</span>'.' '. $v->event_title;
                    } else {
                        $t = '<span class="calendar-badge d-inline-block undefined badge badge-danger" style="width: 30px;">SOL</span>'.' '. $v->event_title;
                    }
                    $tplain = 'SOL'.' -'. $v->event_title;
                    $startDate = ($request->timezone == 'local') ? $v->start_date : $v->user_start_date->format('Y-m-d');
                    $newArray[] = [
                        'mysol' => 'yes',
                        'case_id' => $v->case_unique_number,
                        'id' => $v->id,
                        'title' => $t,
                        'tTitle' => $tplain,
                        'start' => Carbon::parse("$startDate $timezone")->format('Y-m-d'),
                        'textColor' > '#000000',
                        'backgroundColor' => 'rgb(236, 238, 239)',
                        'resourceId' => $v->created_by,
                    ];
                }
            }
        }else{
            $CaseEventSOL='';
        }

        // Get task reminders
        if(isset($request->searchbymytask) && $request->searchbymytask=="true" && $request->taskLoad != 'unread'){
            $Task = Task::whereBetween('task_due_on', [$request->start, $request->end])->where('firm_id', $authUser->firm_name)
                    ->whereHas('taskLinkedStaff', function($query) use($authUser) {
                        $query->where('users.id', $authUser->id);
                    });
                    // ->leftJoin('task_linked_staff','task.id','=','task_linked_staff.task_id');
            $Task=$Task->where('task_due_on',"!=",'9999-12-30');
            $Task = $Task->where("status", "0")->whereNotNull("task_due_on")/* ->select('task.*', 'task_linked_staff.is_read') */;
            if($request->case_id != "") {
                $Task = $Task->where('case_id',$request->case_id);
            }
            $Task=$Task->whereNull('task.deleted_at')->get();
            if(!empty($Task)) {
                foreach($Task as $k => $v) {
                    if($v->task_priority==3){
                        $cds="background-color: rgb(202, 66, 69); width: 30px;";
                    }else if($v->task_priority==2){
                        $cds="background-color: rgb(254, 193, 8); width: 30px;";
                    }else{
                        $cds="background-color: rgb(40, 167, 68); width: 30px;";
                    }
                    $startDate = ($request->timezone == 'local') ? $v->task_due_on : convertUTCToUserDate($v->task_due_on, $authUser->user_timezone);   
                    $t = '<span class="calendar-badge d-inline-block undefined badge badge-secondary" style="'.$cds.'">DUE</span>'.' ' . $v->task_title;
                    $tplain = 'DUE'.' -' . $v->task_title;
                    $newArray[] = [
                        'mytask' => 'yes',
                        'id' => $v->id,
                        'title' => $t,
                        'tTitle' => $tplain,
                        'start' => Carbon::parse("$startDate $timezone")->format('Y-m-d'),
                        'textColor' => '#000000',
                        'backgroundColor' => 'rgb(236, 238, 239)',
                        'resourceId' => $v->created_by,
                    ];
                }
            }
        }else{
            $Task='';
        }
        return response()->json($newArray);
        // return response()->json(['errors'=>'','result'=>$newarray,'sol_result'=>$CaseEventSOL,'mytask'=>$Task]);
        exit;    
    }
    public function index($calendarView = null)
    {
        $authUser = auth()->user();
        $CaseMasterData = CaseMaster::where('firm_id', $authUser->firm_name)->where('is_entry_done',"1");
        if($authUser->hasPermissionTo('access_only_linked_cases')) {
            $CaseMasterData = $CaseMasterData->whereHas('caseStaffAll', function($query) {
                            $query->where('user_id', auth()->id());
                        });
        }
        $CaseMasterData = $CaseMasterData->get();
        $EventType = EventType::where('status','1')->where('firm_id',$authUser->firm_name)->orderBy("status_order","ASC")->get();
        // $staffData = User::select("first_name","last_name","id","user_level","default_color")->where('user_level',3)->where("firm_name",Auth::user()->firm_name)->get();
        $staffData = firmUserList();

        // read all app notifications
        if(Auth::user()->parent_user == 0){
            AllHistory::where('type','event')->where('created_by', '!=' , Auth::user()->id )->update(['is_read'=>0]);
        }else{
            AllHistory::where('type','event')->where('created_by', Auth::user()->parent_user)->update(['is_read'=>0]);
        }
        // return view('calendar.indexnew',compact('CaseMasterData','EventType','staffData'));
        return view('calendar.index',compact('CaseMasterData','EventType','staffData', 'calendarView', 'authUser'));
    }
    public function loadEventCalendar (Request $request)
    {        
        $authUser = auth()->user();
        $CaseEvent = '';
        $byuser=json_decode($request->byuser, TRUE);
        if(count($byuser)) {
            $CaseEvent = EventRecurring::whereBetween('start_date',  [$request->start, $request->end])->has('event');
            $CaseEvent = $CaseEvent->whereHas("event", function($query) use($request, $authUser) {
                $query->where('firm_id', $authUser->firm_name);
                if($request->event_type!="[]"){
                    $event_type=json_decode($request->event_type, TRUE);
                    $query->whereIn('event_type_id', $event_type);
                }
                if($request->case_id != "") {
                    $query->where('case_id',$request->case_id);
                }
            });

            $CaseEvent = $CaseEvent->when($byuser , function($query) use ($byuser) {
                $query->where(function ($query) use ($byuser) {
                    foreach($byuser as $user) {
                        $query->orWhereJsonContains('event_linked_staff', ['user_id' => (string)$user]);
                    }
                });
            });

            if($request->taskLoad=='unread'){
                $CaseEvent = $CaseEvent->whereJsonContains('event_linked_staff', ['user_id' => (string)auth()->id(), 'is_read' => 'no']);
            }
            $CaseEvent = $CaseEvent/* ->whereNull('events.deleted_at') */->with('event', 'event.eventType')->get();
        }
        $newarray = array();
        $timezone = $authUser->user_timezone ?? 'UTC';
        if(!empty($CaseEvent)) {
            foreach($CaseEvent as $k=>$v){
                $event = $v->event;
                $eventData = [];
                $eventData["event_id"] = $event->id ?? Null;
                $eventData["event_recurring_id"] = $v->id;
                $eventData["event_title"] = $event->event_title ?? "<No Title>";
                // $startDateTime= ($event->is_full_day == 'no') ? convertToUserTimezone($v->start_date.' '.$event->start_time, $timezone) : convertToUserTimezone($v->start_date.' 00:01:00', $timezone);
                // $endDateTime= ($event->is_full_day == 'no') ? convertToUserTimezone($v->end_date.' '.$event->end_time, $timezone) : convertToUserTimezone($v->end_date.' 11:59:00', $timezone);
                $startDateTime= ($event->is_full_day == 'no') ? convertUTCToUserTime($v->start_date.' '.$event->start_time, $timezone) : $v->user_start_date->format('Y-m-d');
                $endDateTime= ($event->is_full_day == 'no') ? convertUTCToUserTime($v->end_date.' '.$event->end_time, $timezone) : $v->user_end_date->format('Y-m-d');
                $eventData["start_date_time"] = $startDateTime;
                $eventData["end_date_time"] = $endDateTime;
                $eventData["etext"] = ($v->event && $event->eventType) ? $event->eventType->color_code : "";
                // $eventData["start_time_user"] = $startDateTime->format('h:ia');
                $eventData["start_time_user"] = date('h:ia', strtotime($startDateTime));
                // $eventData["start_time_user"] = $event->start_time;
                $eventData["event_linked_staff"] = encodeDecodeJson($v->event_linked_staff);
                $eventData["is_all_day"] = $event->is_full_day;
                $eventData["is_read"] = $v->is_read;

                $newarray[] = $eventData;
            }
            $newarray = collect($newarray)->sortBy(function($col) {
                return $col['start_date_time'];
            })->values()->all();
        }

        // Get SOL reminders
        if(isset($request->searchbysol) && $request->searchbysol=="true" && $request->taskLoad != 'unread'){
            $CaseEventSOL = Event::leftJoin('case_master','case_master.id','=','events.case_id')->where('events.firm_id', $authUser->firm_name)
            ->select("case_master.case_unique_number as case_unique_number","case_master.sol_satisfied","events.*")
            ->where('events.created_by', $authUser->id);
            $CaseEventSOL=$CaseEventSOL->whereBetween('start_date', [$request->start, $request->end]);
            $CaseEventSOL=$CaseEventSOL->where('is_SOL','yes');
            if($request->case_id != "") {
                $CaseEventSOL=$CaseEventSOL->where('case_id',$request->case_id);
            }
            $CaseEventSOL=$CaseEventSOL->whereNull('events.deleted_at')->get();
        }else{
            $CaseEventSOL='';
        }

        // Get task reminders
        if(isset($request->searchbymytask) && $request->searchbymytask=="true" && $request->taskLoad != 'unread'){
            $Task = Task::whereBetween('task_due_on', [$request->start, $request->end])->where('firm_id', $authUser->firm_name)
                    ->whereHas('taskLinkedStaff', function($query) use($authUser) {
                        $query->where('users.id', $authUser->id);
                    });
                    // ->leftJoin('task_linked_staff','task.id','=','task_linked_staff.task_id');
            $Task=$Task->where('task_due_on',"!=",'9999-12-30');
            $Task = $Task->where("status", "0")->whereNotNull("task_due_on")/* ->select('task.*', 'task_linked_staff.is_read') */;
            if($request->case_id != "") {
                $Task = $Task->where('case_id',$request->case_id);
            }
            $Task=$Task->whereNull('task.deleted_at')->get();
        }else{
            $Task='';
        }
        return response()->json(['errors'=>'','result'=>$newarray,'sol_result'=>$CaseEventSOL,'mytask'=>$Task]);
        exit;    
    }
    /**
     * Load calendar staff view
     */
    public function loadStaffView (Request $request)
    {      
        // return $request->all()  ;
        if($request->resType == "resources" && $request->byuser) {
            $resources = [];
            $users = User::where("firm_name", auth()->user()->firm_name);
            if($request->byuser) {
                $users = $users->whereIn("id", $request->byuser);
            }
            $users = $users->get();
            foreach($users as $key => $item) {
                $resources[] = [
                    'id' => $item->id,
                    'title' => (auth()->id() == $item->id) ? "My Calendar" : @$item->full_name
                ];
            }
            return response()->json($resources);
        }        
        return response()->json([]);
        exit;    
    }

    /**
     * Load calendar agenda view
     */
    public function loadAgendaView (Request $request)
    {        
        $authUser = auth()->user();
        $authUserId = $authUser->id;
        $timezone = $authUser->user_timezone;
        $startDate = convertDateToUTCzone(date("Y-m-d", strtotime($request->start)), $timezone);
        $endDate = convertDateToUTCzone(date("Y-m-d", strtotime($request->end)), $timezone);
        $finalDataList = [];
        if($request->byuser != "[]") {
            $events = EventRecurring::whereBetween('start_date',  [$startDate, $endDate]);
            if($request->byuser != "[]") {
                $byuser = json_decode($request->byuser, TRUE);
                $events = $events->when($byuser , function($query) use ($byuser) {
                    $query->where(function ($query) use ($byuser) {
                        foreach($byuser as $user) {
                            $query->orWhereJsonContains('event_linked_staff', ['user_id' => $user]);
                        }
                    });
                });
            }
            $events = $events->whereHas("event", function($query) use($request, $authUser) {
                $query->where("is_SOL", "no")->where('firm_id', $authUser->firm_name);
                if($request->event_type != "[]"){
                    $event_type = json_decode($request->event_type, TRUE);
                    $query->whereIn('event_type_id', $event_type);
                }
                if($request->case_id != "") {
                    $query->where('case_id',$request->case_id);
                }
            });
            if($request->taskLoad=='unread'){
                $events = $events->whereJsonContains('event_linked_staff', ['user_id' => (string)auth()->id(), 'is_read' => 'no']);
            }
            $events = $events->orderBy("start_date", "ASC")->with('event', 'event.case', 'event.leadUser')->get();
            if(count($events)) {
                foreach($events as $key => $item) {
                    // $startDateTime= ($item->event->is_full_day == 'no') ? convertUTCToUserTime($item->start_date.' '.$item->event->start_time, $timezone) : $item->user_start_date;
                    // $endDateTime= ($item->event->is_full_day == 'no') ? convertUTCToUserTime($item->end_date.' '.$item->event->end_time, $timezone) : $item->user_end_date;
                    $startDateTime= ($item->event->is_full_day == 'no') ? convertToUserTimezone($item->start_date.' '.$item->event->start_time, $timezone) : $item->user_start_date;
                    $endDateTime= ($item->event->is_full_day == 'no') ? convertToUserTimezone($item->end_date.' '.$item->event->end_time, $timezone) : $item->user_end_date;
                    if($startDateTime->format('Y-m-d') >= date("Y-m-d", strtotime($request->start))) {
                    $finalDataList[] = (object)[
                        'event_id' => $item->event_id,
                        'event_recurring_id' => $item->id,
                        'event_title' => $item->event->event_title ?? "<No Title>",
                        "start_date_time" => $startDateTime->format('Y-m-d'),
                        "start_date" => $item->start_date,
                        "end_date" => $item->end_date,
                        "user_start_date" => $startDateTime->format('D, M d'),
                        "user_start_time" => $startDateTime->format('h:i A'),
                        "user_end_date" => $endDateTime->format('D, M d'),
                        "user_end_time" => $endDateTime->format('h:i A'),
                        "is_event_private" => $item->event->is_event_private,
                        "parent_event_id" => $item->event->parent_event_id,
                        "is_recurring" => $item->event->is_recurring,
                        "is_all_day" => $item->event->is_full_day,
                        "edit_recurring_pattern" => $item->event->edit_recurring_pattern,
                        "event_linked_staff" => encodeDecodeJson($item->event_linked_staff),
                        "event_linked_contact_lead" => encodeDecodeJson($item->event_linked_contact_lead),
                        "event_comments" => $item->event_comments,
                        "is_SOL" => "no",
                        "sol_satisfied" => "no",
                        "case_id" => $item->event->case_id,
                        "case_title" => ($item->event->case_id) ? $item->event->case->case_title : "",
                        "case_unique_number" => ($item->event->case_id) ? $item->event->case->case_unique_number : "",
                        'lead_id' => $item->event->lead_id,
                        "lead_user_name" => ($item->event->lead_id) ? $item->event->leadUser->full_name : "",
                        'created_by' => $item->created_by,
                        'is_read' => $item->is_read,
                        'event_data_type' => 'event',
                        's_date_time' => ($item->event->is_full_day == 'no') ? $startDateTime->format('Y-m-d H:i:s') : $item->user_start_date->format('Y-m-d').' 00:00:00',
                    ];
                    }
                }
                // return $finalDataList;
                $finalDataList = collect($finalDataList)->sortBy(function ($product, $key) {
                    return $product->s_date_time;
                })->values();
            }
        }
        
        $solEvents = [];
        if(isset($request->searchbysol) && $request->searchbysol=="true" && $request->taskLoad != 'unread') {
            $solEvents = Event::where('is_SOL','yes')->leftJoin('case_master','case_master.id','=','events.case_id')
                ->where('events.created_by', $authUserId)->where('events.firm_id', $authUser->firm_name)
                ->whereBetween('start_date', [$startDate, $endDate]);
            if($request->case_id != "") {
                $solEvents = $solEvents->where('case_id',$request->case_id);
            }
            $solEvents = $solEvents->select("case_master.case_unique_number as case_unique_number","case_master.sol_satisfied","case_master.case_title","events.*")
                ->orderBy("start_date", "ASC")->whereNull('events.deleted_at')->get();
            if(count($solEvents)) {
                foreach($solEvents as $key => $item) {
                    $finalDataList[] = (object)[
                        'event_id' => $item->id,
                        'event_title' => $item->event_title,
                        "start_date" => $item->start_date,
                        "start_date_time" => $item->user_start_date->format('Y-m-d'),
                        "user_start_date" => $item->user_start_date->format('D, M d'),
                        "is_SOL" => $item->is_SOL,
                        "case_id" => $item->case_id,
                        "case_title" => $item->case_title ?? "",
                        "case_unique_number" => $item->case_unique_number ?? "",
                        "sol_satisfied" => $item->sol_satisfied,
                        'created_by' => $item->created_by,
                        'event_data_type' => 'sol',
                        'is_read' => 'yes',
                        's_date_time' => $item->user_start_date->format('Y-m-d').' 00:00:00',
                    ];
                }
            }
        }
        $tasks = [];
        if(isset($request->searchbymytask) && $request->searchbymytask=="true" && $request->taskLoad != 'unread'){
            $tasks = Task::whereBetween('task_due_on', [$startDate, $endDate])->where('firm_id', $authUser->firm_name)
                    ->whereHas('taskLinkedStaff', function($query) use($authUserId) {
                        $query->where('users.id', $authUserId);
                    })->where('task_due_on',"!=",'9999-12-30')
                    ->where("status", "0")->whereNotNull("task_due_on");
            if($request->case_id != "") {
                $tasks = $tasks->where('case_id',$request->case_id);
            }
            $tasks = $tasks->whereNull('deleted_at')->orderBy("task_due_on", "ASC")->with('case')->get();
            if(count($tasks)) {
                foreach($tasks as $key => $item) {
                    $taskDueOn = convertUTCToUserDate($item->task_due_on, $timezone);
                    $finalDataList[] = (object)[
                        'task_id' => $item->id,
                        'task_title' => $item->task_title,
                        "start_date" => $item->task_due_on,
                        "start_date_time" => $taskDueOn->format('Y-m-d'),
                        "user_start_date" => $taskDueOn->format('D, M d'),
                        "task_priority" => $item->task_priority,
                        "case_id" => $item->case_id,
                        "case_title" => $item->case->case_title ?? "",
                        "case_unique_number" => $item->case->case_unique_number ?? "",
                        "status" => ($item->status == '1') ? 'Completed' : 'Incomplete',
                        'created_by' => $item->created_by,
                        'event_data_type' => 'task',
                        'is_read' => 'yes',
                        's_date_time' => $taskDueOn->format('Y-m-d').' 00:00:00',
                    ];
                }
            }
        }
        $finalData = collect($finalDataList)->sortBy(function($col) {
            return $col->s_date_time;
        })->values()->all();
        return view('calendar.partials.load_agenda_view', ["events" => $finalData])->render();          
        exit;    
    }

    public function loadAgendaViewNew (Request $request)
    {        
        $authUser = auth()->user();
        $authUserId = $authUser->id;
        $timezone = $authUser->user_timezone;
        $startDate = convertDateToUTCzone(date("Y-m-d", strtotime($request->start)), $timezone);
        $endDate = convertDateToUTCzone(date("Y-m-d", strtotime($request->end)), $timezone);
        $finalDataList = [];
        if($request->byuser != "[]") {
            $events = EventRecurring::whereBetween('start_date',  [$startDate, $endDate]);
            if($request->byuser != "[]") {
                $byuser = json_decode($request->byuser, TRUE);
                $events = $events->when($byuser , function($query) use ($byuser) {
                    $query->where(function ($query) use ($byuser) {
                        foreach($byuser as $user) {
                            $query->orWhereJsonContains('event_linked_staff', ['user_id' => (string)$user]);
                        }
                    });
                });
            }
            $events = $events->whereHas("event", function($query) use($request, $authUser) {
                $query/* ->where("is_SOL", "no") */->where('firm_id', $authUser->firm_name);
                if($request->event_type != "[]"){
                    $event_type = json_decode($request->event_type, TRUE);
                    $query->whereIn('event_type_id', $event_type);
                }
                if($request->case_id != "") {
                    $query->where('case_id',$request->case_id);
                }
            });
            if($request->taskLoad=='unread'){
                $events = $events->whereJsonContains('event_linked_staff', ['user_id' => (string)auth()->id(), 'is_read' => 'no']);
            }
            $events = $events->orderBy("start_date", "ASC")->with('event', 'event.case', 'event.leadUser')->get();
            $events = $events->sortBy(function ($product, $key) {
                return $product['start_date'].$product['event']['start_time'];
            })->values();
        }
        return view('calendar.partials.load_agenda_view', ["allEvents" => $events, "authUser" => $authUser])->render();          
        exit;    
    }

    /**
     * Mark event as read
     */
    public function eventMarkAsRead(Request $request)
    {
        $authUserId = (string) auth()->id();
        $eventIds = Event::whereId($request->event_id)->orWhere("parent_event_id", $request->event_id)->pluck('id')->toArray();
        if(isset($request->is_all_event) && !isset($request->event_id)) {
            $eventIds = EventRecurring::whereJsonContains('event_linked_staff', ["user_id" => $authUserId])->pluck('event_id')->toArray();
        }
        if(count($eventIds)) {
        $eventREcurrings = EventRecurring::whereIn('event_id', $eventIds)->whereJsonContains('event_linked_staff', ["user_id" => $authUserId])->get();
        foreach($eventREcurrings as $key => $item) {
            $linkStaffPivot = encodeDecodeJson($item->event_linked_staff);
            if(count($linkStaffPivot)) {
                $newArray = [];
                foreach($linkStaffPivot as $skey => $sitem) {
                    if($sitem->user_id == $authUserId) {
                        $sitem->is_read = 'yes';
                    }
                    $newArray[] = $sitem;
                }
                $item->fill(['event_linked_staff' => encodeDecodeJson($newArray, 'encode')])->save();
            }
        }
        }
        $unreadEventCount = (getUnreadEventCount() > 0) ? getUnreadEventCount() : '';
        return response()->json(['errors'=>'','msg'=>'Records successfully updated', 'unreadEventCount' => $unreadEventCount]);
    }

    // Made common code, check CaseController
    /* public function loadAddEventPageFromCalendar(Request $request)
    {
     $case_id=$lead_id='';
      $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
    //   $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
      $CaseMasterData = userCaseList();
      $country = Countries::get();
      $eventLocation = CaseEventLocation::where("location_future_use","yes")->get();
      $currentDateTime=$this->getCurrentDateAndTime();
       //Get event type 
       $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
    //    $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
       $caseLeadList = userLeadList();

      return view('calendar.event.loadAddEvent',compact('lead_id','case_id','caseLeadList','CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType'));          
   } */

   // Made common code, This code is not in use
   /* public function loadAddEventPageSpecificaDate(Request $request)
   {
        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        // $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $CaseMasterData = userCaseList();
        $country = Countries::get();
        $eventLocation = CaseEventLocation::get();
        $currentTime=date('h:i a',strtotime($this->getCurrentDateAndTime()));
        $currentDate=$request->selectedate;
        $currentDateTime=$this->getCurrentDateAndTime();
        // $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
        $caseLeadList = userLeadList();
        $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
        $case_id=$lead_id='';
        return view('calendar.event.loadAddEventSpecificDate',compact('lead_id','case_id','caseLeadList','CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType','currentDate','currentTime'));          
  } */

    // Duplicate code, Made common code, check CaseController
    /* public function loadCommentPopupFromCalendar(Request $request)
    {
        $evnt_id=$request->evnt_id;
        $evetData=CaseEvent::whereId($evnt_id)->with('case', 'leadUser', 'eventLinkedStaff', 'eventCreatedByUser', 'eventUpdatedByUser', 'eventLinkedContact', 'eventLinkedLead', 'eventLocation', 'eventType')->first();
        // $eventReminderData=CaseEventReminder::where('event_id',$evnt_id)->get();
        // $eventLocation='';
        // if($evetData->event_location_id!="0"){
        //     $eventLocation = CaseEventLocation::leftJoin('countries','countries.id','=','case_event_location.country')->where('case_event_location.id',$evetData->event_location_id)->first();
        // }
        // $CaseMasterData='';
        // if($evetData->case_id!=NULL){
        //     $case_id=$evetData->case_id;
        //     $CaseMasterData = CaseMaster::where('id',$case_id)->first();
        // }
        // $caseLinkedStaffList = CaseEventLinkedStaff::join('users','users.id','=','case_event_linked_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.user_type","case_event_linked_staff.attending")->where("case_event_linked_staff.event_id",$evnt_id)->get();

        //Event created By user name
        // $eventCreatedBy = User::select("first_name","last_name","id","user_level","user_type")->where("id",$evetData->created_by)->first();
    
        // $updatedEvenByUserData='';
        // if($evetData->updated_by!=NULL){
        //     //Event updated By user name
        //     $updatedEvenByUserData = User::select("first_name","last_name","id","user_level","user_type")->where("id",$evetData->updated_by)->first();
        // }
        // $country = Countries::get();

        // $CaseEventLinkedContactLead = CaseEventLinkedContactLead::join('users','users.id','=','case_event_linked_contact_lead.contact_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.user_type","contact_id","attending","invite")->where("case_event_linked_contact_lead.event_id",$evnt_id)->get();
        return view('calendar.event.loadEventCommentPopup',compact('evetData','eventLocation','country','CaseMasterData','caseLinkedStaffList','eventCreatedBy','updatedEvenByUserData','CaseEventLinkedContactLead'));     
        exit;    
    } */
    // This code is not in use
    /* public function loadSingleEditEventPageFromCalendar(Request $request)
    {

        $evnt_id=$request->evnt_id;
        $evetData=CaseEvent::where("id",$evnt_id)->with("eventCreatedByUser", "eventUpdatedByUser")->first();
        $eventReminderData=CaseEventReminder::where('event_id',$evnt_id)->get();
        // $case_id=$evetData->case_id;
        // $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        // $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $country = Countries::get();
        $eventLocation = CaseEventLocation::get();
        $currentDateTime=$this->getCurrentDateAndTime();
    
        //Get event type 
        $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->get();

        //Event created By user name
        $userData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->created_by)->first();
    
        $updatedEvenByUserData='';
        if($evetData->updated_by!=NULL){
            //Event updated By user name
            $updatedEvenByUserData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->updated_by)->first();
        }
    
        $getEventColorCode = EventType::select("color_code","id")->where('id',$evetData->event_type)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->pluck('color_code');

        // $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();

        return view('calendar.event.loadSingleEditEvent',compact('caseLeadList','CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType','evetData','case_id','eventReminderData','userData','updatedEvenByUserData','getEventColorCode'));          
    } */

    public function loadFirmAllStaff(Request $request)
    {
        $alreadySelected=$from=$isAttending='';
        if($request->event_id){
            $alreadySelected = CaseEventLinkedStaff::select("user_id")->where("case_event_linked_staff.event_id",$request->event_id)->pluck("user_id")->toArray();

            $isAttending= CaseEventLinkedStaff::select("user_id")->where("case_event_linked_staff.event_id",$request->event_id)->where("case_event_linked_staff.attending",'yes')->pluck("user_id")->toArray();

            $from="edit";
        }
        $staffData = User::select("first_name","last_name","id","user_level")->where('user_level',3)->where("firm_name",Auth::user()->firm_name)->get();
        return view('calendar.event.loadAllStaff',compact('staffData','alreadySelected','from','isAttending'));          
    }

    // Made common code. This code is not in use
    /* public function loadEditEventPageFromCalendarView(Request $request)
    {

          $evnt_id=$request->evnt_id;
          $evetData=CaseEvent::find($evnt_id);
          $eventReminderData=CaseEventReminder::where('event_id',$evnt_id)->get();
          $case_id=$evetData->case_id;
          $lead_id=$evetData->lead_id;
          $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        //   $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $CaseMasterData = userCaseList();
          $country = Countries::get();
          $eventLocation = CaseEventLocation::where("location_future_use","yes")->get();
          $currentDateTime=$this->getCurrentDateAndTime();
          //Get event type 
          $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
          //Event created By user name
          $userData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->created_by)->first();
          $updatedEvenByUserData='';
          if($evetData->updated_by!=NULL){
              //Event updated By user name
              $updatedEvenByUserData = User::select("first_name","last_name","id","user_level")->where("id",$evetData->updated_by)->first();
          }
          $getEventColorCode = EventType::select("color_code","id")->where('id',$evetData->event_type)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->pluck('color_code');
        //   $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
        $caseLeadList = userLeadList();
          return view('calendar.event.loadEditEvent',compact('caseLeadList','CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType','evetData','case_id','lead_id','eventReminderData','userData','updatedEvenByUserData','getEventColorCode'));          
   } */
    public function loadTask()
    {   

        // TempUserSelection::where("user_id",Auth::user()->id)->delete();
        DB::table('temp_user_selection')->where("user_id",Auth::user()->id)->delete();
        $columns = array('id', 'case_title', 'case_desc', 'case_number', 'case_status','case_unique_number');
        $requestData= $_REQUEST;
        
        $case = CaseMaster::join("users","case_master.created_by","=","users.id")->select('case_master.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid");
        
        //Filter applied for practice area
        if(isset($requestData['pa']) && $requestData['pa']!=''){
            $case = $case->where("practice_area",$requestData['pa']);
        }
        
        //Filter applied for case stage
        if(isset($requestData['cs']) && $requestData['cs']!=''){
            $case = $case->where("case_status",$requestData['cs']);
        }

        //Filter applied for lead attorney
        if(isset($requestData['la']) && $requestData['la']!=''){
            $CaseLeadAttorneySearch = CaseStaff::select("case_id")->where('lead_attorney',$requestData['la'])->get()->pluck('case_id');
            $case = $case->whereIn("case_master.id",$CaseLeadAttorneySearch);
        }

         //Load only closed case
         if(isset($requestData['i']) && $requestData['i']!=''){
            $case = $case->where("case_close_date","!=", NULL);
        }
        //Load only own created case
        if(isset($requestData['mc']) && $requestData['mc']!=''){
            $case = $case->where("case_master.created_by",Auth::user()->id); 
        }else{
            //If Parent user logged in then show all child case to parent
            if(Auth::user()->parent_user==0){
                $getChildUsers = User::select("id")->where('parent_user',Auth::user()->id)->get()->pluck('id');
                $getChildUsers[]=Auth::user()->id;
                $case = $case->whereIn("case_master.created_by",$getChildUsers);
            }else{
                $childUSersCase = CaseStaff::select("case_id")->where('user_id',Auth::user()->id)->get()->pluck('case_id');
                $case = $case->whereIn("case_master.id",$childUSersCase);
                
                
            }

        }


        //    $case = $case->where("case_master.created_by",Auth::user()->id);
     
        $case = $case->where("case_master.is_entry_done","1"); 
        $totalData=$case->count();
        $totalFiltered = $totalData; 
        if( !empty($requestData['search']['value']) ) {   
            $case = $case->where( function($q) use ($requestData){
                $q->where( function($select) use ($requestData){
                    $select->orWhere( DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%".$requestData['search']['value']."%");
                    $select->orWhere('email', 'like', "%".$requestData['search']['value']."%" );
                });
            });
        }
        if( !empty($requestData['search']['value']) ) { 
            $totalFiltered = $case->count(); 
        }
        $case = $case->offset($requestData['start'])->limit($requestData['length']);
        $case = $case->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $case = $case->get()->paginate(5);
        // $json_data = array(
        //     "draw"            => intval( $requestData['draw'] ),   
        //     "recordsTotal"    => intval( $totalData ),  
        //     "recordsFiltered" => intval( $totalFiltered ), 
        //     "data"            => $case 
        // );
        // echo json_encode($json_data);  
    }

    public function loadAddTaskPopup(Request $request)
    {
        $case_id=$request->case_id;
        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        // $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $CaseMasterData = userCaseList();
        $country = Countries::get();
        $eventLocation = CaseEventLocation::get();
        $currentDateTime=$this->getCurrentDateAndTime();
         //Get event type 
         $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
         return view('task.loadAddTaskPopup',compact('CaseMasterClient','CaseMasterData','country','currentDateTime','eventLocation','allEventType','case_id'));          
    }
    public function loadCaseLinkedStaffForTask(Request $request)
      {
          $from=$request->from;
          $case_id=$request->case_id;
          $caseLinkedStaffList = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get();
        
          $caseLinkeSaved=array();
          $caseLinkeSavedAttending=array();
          if(isset($request->task_id) && $request->task_id!=''){
            $caseLinkeSaved = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","yes")->where("task_linked_staff.task_id",$request->task_id)->get()->pluck('user_id');
            $caseLinkeSaved= $caseLinkeSaved->toArray();

            $caseLinkeSavedAttending = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","yes")->where("task_linked_staff.task_id",$request->task_id)->get()->pluck('user_id');
            $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();
          }
          return view('task.caseLinkedStaff',compact('caseLinkedStaffList','caseLinkeSaved','from','caseLinkeSavedAttending'));     
          exit;    
     }
     public function loadCaseNoneLinkedStaffForTask(Request $request)
      {
            $from=$request->from;
          $case_id=$request->case_id;
          $caseLinkedStaffList = CaseStaff::select("case_staff.user_id as case_staff_user_id")->where("case_id",$case_id)->get()->pluck('case_staff_user_id');

          $loadFirmUser = User::select("first_name","last_name","id","parent_user")->where("firm_name",Auth::user()->firm_name)->where("user_level","3")->whereNotIn('id',$caseLinkedStaffList)->where("user_status","1")->get();
       
          $caseLinkeSaved=array();
          $caseLinkeSavedAttending=array();
          if(isset($request->task_id) && $request->task_id!=''){
            $caseLinkeSavedAttending = CaseTaskLinkedStaff::select("task_linked_staff.user_id")->where("linked_or_not_with_case","no")->where("task_linked_staff.task_id",$request->task_id)->get()->pluck('user_id');
            $caseLinkeSavedAttending= $caseLinkeSavedAttending->toArray();
          }

          return view('task.caseNoneLinkedStaff',compact('loadFirmUser','caseLinkeSavedAttending','from'));     
          exit;    
     }

     public function loadCaseClientAndLeadsForTask(Request $request)
     {
         $case_id=$request->case_id;
         $caseCllientSelection = CaseClientSelection::join('users','users.id','=','case_client_selection.selected_user')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.mobile_number","case_client_selection.id as case_client_selection_id","users.id as user_id")->where("case_client_selection.case_id",$case_id)->get();
         
         return view('task.caseClientLeadSection',compact('caseCllientSelection'));     
         exit;    
    }
    public function saveSelectdUser(Request $request)
    {
        $firstCheck=TempUserSelection::where("selected_user",$request->selectdValue)->where("user_id",Auth::user()->id)->get();
        
        if($firstCheck->isEmpty()){
            $TempUserSelection = new TempUserSelection;
            $TempUserSelection->selected_user=$request->selectdValue;
            $TempUserSelection->user_id=Auth::user()->id;
            $TempUserSelection->save();
        }
        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
        // echo "<pre>";
        // print_r($selectdUSerList);

        return view('case.showSelectdUser',compact('selectdUSerList'));
    }

    public function remomeSelectedUser(Request $request)
    {
        $firstCheck=TempUserSelection::where("selected_user",$request->selectdValue)->where("user_id",Auth::user()->id)->delete();
        $selectdUSerList = TempUserSelection::join('users','users.id',"=","temp_user_selection.selected_user")->select("users.id","users.first_name","users.last_name","users.user_level")->where("temp_user_selection.user_id",Auth::user()->id)->get();
        return view('case.showSelectdUser',compact('selectdUSerList'));
    }
    public function saveAddTaskPopup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'task_name' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $TaskMaster = new Task;
           
            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { 
                    if($request->text_case_id!=''){
                        $TaskMaster->case_id=$request->text_case_id; 
                    }    
                    if($request->text_lead_id!=''){
                        $TaskMaster->lead_id=$request->text_lead_id; 
                    }    
                } 
                $TaskMaster->no_case_link="yes";
            }else{
                $TaskMaster->no_case_link="no";
            }
            if(isset($request->task_name)) { $TaskMaster->task_title=$request->task_name; }else{ $TaskMaster->task_title=NULL; }
            if(isset($request->due_date) && $request->due_date!="") { 
                $TaskMaster->task_due_on=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->due_date)))), auth()->user()->user_timezone ?? 'UTC'); 
            }else { $TaskMaster->task_due_on= "9999-12-30";}
            if(isset($request->status)) { $TaskMaster->case_status=$request->case_status; }
            if(isset($request->event_frequency)) { $TaskMaster->task_priority=$request->event_frequency; }else{$TaskMaster->task_priority=NULL;}
            if(isset($request->description)) { $TaskMaster->description=$request->description; }else{ $TaskMaster->description=NULL; }
            if(isset($request->time_tracking_enabled)) { $TaskMaster->time_tracking_enabled='yes'; }else{ $TaskMaster->time_tracking_enabled='no'; }
            $TaskMaster->created_by=Auth::User()->id; 
            $TaskMaster->firm_id = auth()->user()->firm_name; 
            $TaskMaster->save();

            $this->saveTaskReminder($request->all(),$TaskMaster->id); 
            $this->saveLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveNonLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveTaskChecklist($request->all(),$TaskMaster->id); 

            $taskHistory=[];
            $taskHistory['task_id']=$TaskMaster->id;
            $taskHistory['task_action']='Created task';
            $taskHistory['created_by']=Auth::User()->id;
            $taskHistory['created_at']=date('Y-m-d H:i:s');
            $this->taskHistory($taskHistory);
            

            //Master history
            $data=[];
            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { 
                    if($request->text_case_id!=''){
                        $data['task_for_case']=$request->text_case_id;
                    }    
                    if($request->text_lead_id!=''){
                        $data['task_for_lead']=$request->text_lead_id; ;
                    }    
                } 
            }
            $data['task_id']=$TaskMaster->id;
            $data['task_name']=$TaskMaster->task_title;
            $data['user_id']=Auth::User()->id;
            $data['activity']='added a task';
            $data['type']='task';
            $data['action']='add';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);
            

            return response()->json(['errors'=>'','user_id'=>$request->user_id]);
          exit;
        }
    }

    public function saveTaskReminder($request,$task_id)
    {
        TaskReminder::where("task_id", $task_id)->where("created_by", Auth::user()->id)->delete();

       for($i=0;$i<count($request['reminder_user_type'])-1;$i++){
           $CaseTaskReminder = new TaskReminder;
           $CaseTaskReminder->task_id=$task_id; 
           $CaseTaskReminder->reminder_type=$request['reminder_type'][$i];
           $CaseTaskReminder->reminer_number=$request['reminder_number'][$i];
           $CaseTaskReminder->reminder_frequncy=$request['reminder_time_unit'][$i];
           $CaseTaskReminder->reminder_user_type=$request['reminder_user_type'][$i];
           $CaseTaskReminder->created_by=Auth::user()->id; 
           $CaseTaskReminder->remind_at=Carbon::now(); 
           $CaseTaskReminder->save();
       }
   }

   public function saveNonLinkedStaffToTask($request,$task_id)
   {
       if(isset($request['share_checkbox_nonlinked'])){
        for($i=0;$i<count($request['share_checkbox_nonlinked']);$i++){
                $CaseTaskLinkedStaff = new CaseTaskLinkedStaff;
                $CaseTaskLinkedStaff->task_id=$task_id; 
                $CaseTaskLinkedStaff->user_id=$request['share_checkbox_nonlinked'][$i];
                if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                    $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$request['share_checkbox_nonlinked'][$i]];
                }else{
                    $CaseTaskLinkedStaff->time_estimate_total="0";
                }

                $CaseTaskLinkedStaff->linked_or_not_with_case="no";
                $CaseTaskLinkedStaff->created_by=Auth::user()->id; 
                $CaseTaskLinkedStaff->save();
            }
        }
  }
   public function saveLinkedStaffToTask($request,$task_id)
   {
       CaseTaskLinkedStaff::where("task_id", $task_id)->where("created_by", Auth::user()->id)->forceDelete();
       if(isset($request['linked_staff_checked_attend'])){
        for($i=0;$i<count($request['linked_staff_checked_attend']);$i++){
                $CaseTaskLinkedStaff = new CaseTaskLinkedStaff;
                $CaseTaskLinkedStaff->task_id=$task_id; 
                $CaseTaskLinkedStaff->user_id=$request['linked_staff_checked_attend'][$i];
                // $CaseTaskLinkedStaff->time_estimate_total="0";
                if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                    $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$request['linked_staff_checked_attend'][$i]];
                }else{
                    $CaseTaskLinkedStaff->time_estimate_total="0";
                }
                $CaseTaskLinkedStaff->linked_or_not_with_case="yes";
                $CaseTaskLinkedStaff->is_assign = "yes";                
                $CaseTaskLinkedStaff->created_by=Auth::user()->id; 
                $CaseTaskLinkedStaff->save();
            }
        }
  }

  public function saveTaskChecklist($request,$task_id)
  {
        TaskChecklist::where("task_id", $task_id)->where("created_by", Auth::user()->id)->delete();
        $orderValue=1;
        if(isset($request['checklist-item-name'])){
                for($i=0;$i<count($request['checklist-item-name'])-1;$i++){
                $TaskChecklist = new TaskChecklist;
                $TaskChecklist->task_id=$task_id; 
                $TaskChecklist->checklist_order=$orderValue; 
                $TaskChecklist->status="0"; 
                $TaskChecklist->title=$request['checklist-item-name'][$i];
                $TaskChecklist->created_by=Auth::user()->id; 
                if($request['checklist-item-name'][$i]!=''){
                    $TaskChecklist->save(); //Could not store empty checklist
                }
                $orderValue++;
            }
        }
 }
    public function hideTaskGuide(Request $request)
    {
        $userMaster = User::find(Auth::User()->id);
        $userMaster->add_task_guide="1";
        $userMaster->save();        
    }

    public function loadAllStaffMember(Request $request)
    {

          $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->get();

          $SavedStaff=$from='';
          if(isset($request->edit)){
            $SavedStaff=CaseTaskLinkedStaff::select('user_id')->where("task_id", $request->task_id)->get()->pluck('user_id')->toArray();
            $from='edit';  
        }
          return view('task.firmStaff',compact('loadFirmStaff','SavedStaff','from'));     
          exit;    
     }
     public function loadTimeEstimationUsersList(Request $request)
      {
          $userList=json_decode($request->userList, TRUE);
          if(isset($userList)){
             $loadFirmStaff = User::select("first_name","last_name","id")->whereIn("id",$userList)->get();
          }else{
            $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->get();
         }
        
          return view('task.loadTimeEstimationUsersList',compact('loadFirmStaff'));     
          exit;    
     }
     public function loadTimeEstimationCaseWiseUsersList(Request $request)
     {
         if(isset($request->userList)){
            $userList=json_decode($request->userList, TRUE);

            // $loadFirmStaff = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$request->case_id)->whereIn("users.id",$userList)->get();
            $loadFirmStaff = User::select("users.*")->whereIn("users.id",$userList)->get();
         }else{
            // $loadFirmStaff = CaseStaff::join('users','users.id','=','case_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title","lead_attorney","case_staff.rate_amount as staff_rate_amount","users.default_rate as user_default_rate","case_staff.rate_type as rate_type","case_staff.originating_attorney","case_staff.id as case_staff_id","case_staff.user_id as case_staff_user_id")->where("case_id",$request->case_id)->get();
            $loadFirmStaff = CaseTaskLinkedStaff::join('users','users.id','=','task_linked_staff.user_id')->select("users.id","users.first_name","users.last_name","users.user_level","users.email","users.user_title")->select("users.*")->where("task_linked_staff.task_id",$request->task_id)->get();

        }

        $fillsedHours='';
        if($request->edit=="edit"){
           $fillsedHours=CaseTaskLinkedStaff::select('time_estimate_total',"user_id")->where("task_id", $request->task_id)->get();
            foreach($fillsedHours as $k=>$v){
                $fillsedHours[$v->user_id]=$v->time_estimate_total;
            }
        }
         return view('task.loadTimeEstimationUsersList',compact('loadFirmStaff','fillsedHours'));     
         exit;    
    }

    public function deleteTask(Request $request)
    {
        $id=$request->task_id;
        //Master history
        $taskData=Task::find($id);
        $data=[];
        if($taskData['case_id']!=NULL) { 
            $data['task_for_case']=$taskData['case_id'];  
        }   
        if($taskData['lead_id']!=NULL) { 
            $data['task_for_lead']=$taskData['lead_id'];  
        } 
        $data['task_id']=$taskData['id'];
        $data['task_name']=$taskData['task_title'];
        $data['user_id']=Auth::User()->id;
        $data['activity']='deleted a task';
        $data['type']='task';
        $data['action']='delete';
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);

        Task::where("id", $id)->delete();
        session(['popup_success' => 'Task deleted successfully.']);

        return response()->json(['errors'=>'','id'=>$id]);
        exit;    
    }

    public function taskStatus(Request $request)
    {        
        $taskHistory=[];
        $data=[];
        $Task = Task::find($request->task_id);
        if($request->status=="0"){
            $Task->status="1";
            $taskHistory['task_id']=$Task->id;
            $taskHistory['task_action']='Completed task';

            if($Task['case_id']!=NULL) { 
                $data['task_for_case']=$Task['case_id'];  
            }   
            if($Task['lead_id']!=NULL) { 
                $data['task_for_lead']=$Task['lead_id'];  
            } 
            $data['task_id']=$Task['id'];
            $data['task_name']=$Task['task_title'];
            $data['user_id']=Auth::User()->id;
            $data['activity']='completed task';
            $data['type']='task';
            $data['action']='complete';
        }else{
            $Task->status="0";
            $taskHistory['task_id']=$Task->id;
            $taskHistory['task_action']='Marked task as incomplete';

            if($Task['case_id']!=NULL) { 
                $data['task_for_case']=$Task['case_id'];  
            }   
            if($Task['lead_id']!=NULL) { 
                $data['task_for_lead']=$Task['lead_id'];  
            } 
            $data['task_id']=$Task['id'];
            $data['task_name']=$Task['task_title'];
            $data['user_id']=Auth::User()->id;
            $data['activity']='marked as incomplete task';
            $data['type']='task';
            $data['action']='incomplete';
        }
        $Task->task_completed_by=Auth::User()->id;
        $Task->task_completed_date=date('Y-m-d h:i:s');
        $Task->save();

        
        $taskHistory['created_by']=Auth::User()->id;
        $taskHistory['created_at']=$Task->task_completed_date;
        $this->taskHistory($taskHistory);

        
        $CommonController= new CommonController();
        $CommonController->addMultipleHistory($data);


        return response()->json(['errors'=>'','id'=>$Task->id]);
        exit;    
    }


    public function loadEditTaskPopup(Request $request)
    {
        $task_id=$request->task_id;
        $CaseMasterClient = User::select("first_name","last_name","id","user_level")->where('user_level',2)->where("parent_user",Auth::user()->id)->get();
        // $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $CaseMasterData = userCaseList();
        $Task = Task::find($request->task_id);
        $TaskChecklist = TaskChecklist::select("*")->where("task_id",$task_id)->orderBy('checklist_order','ASC')->get();
        $taskReminderData = TaskReminder::select("*")->where("task_id",$task_id)->get();
        $from_view="no";
        if(isset($request->from_view) && $request->from_view=='yes'){
            $from_view="yes";
        }
         return view('task.loadEditTaskPopup',compact('CaseMasterClient','CaseMasterData','task_id','Task','TaskChecklist','taskReminderData','from_view'));          
    }
    public function loadStatus(Request $request)
    {        
      $getChildUsers=$this->getParentAndChildUserIds();
        $caseStageList = CaseStage::whereIn("created_by",$getChildUsers)->where("status","1")->get();          

        $CaseMaster = CaseMaster::where("id",$request->case_id)->get();
        return view('case.changeStatus',compact('CaseMaster','caseStageList'));
    }

    public function saveEditTaskPopup(Request $request)
    {
     
       
        $validator = \Validator::make($request->all(), [
            'task_name' => 'required',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{

            $TaskMaster =Task::find($request->task_id);
           
            if(!isset($request->no_case_link)){
                if(isset($request->case_or_lead)) { $TaskMaster->case_id=$request->case_or_lead; } 
                $TaskMaster->no_case_link="yes";
            }else{
                $TaskMaster->no_case_link="no";
            }
            if(isset($request->task_name)) { $TaskMaster->task_title=$request->task_name; }else{ $TaskMaster->task_title=NULL; }
            if(isset($request->due_date) && $request->due_date!="") { 
                $TaskMaster->task_due_on=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->due_date)))), auth()->user()->user_timezone ?? 'UTC'); 
            }else { $TaskMaster->task_due_on= "9999-12-30";}
            if(isset($request->status)) { $TaskMaster->case_status=$request->case_status; }
            if(isset($request->event_frequency)) { $TaskMaster->task_priority=$request->event_frequency; }else{$TaskMaster->task_priority=NULL;}
            if(isset($request->description)) { $TaskMaster->description=$request->description; }else{ $TaskMaster->description=NULL; }
            if(isset($request->time_tracking_enabled) && $request->time_tracking_enabled=="on") { $TaskMaster->time_tracking_enabled='yes'; }else{ $TaskMaster->time_tracking_enabled='no'; }
            $TaskMaster->updated_by=Auth::User()->id;
            $TaskMaster->firm_id = auth()->user()->firm_name;  
            $TaskMaster->save();

            $taskHistory=[];
            $taskHistory['task_id']=$TaskMaster->id;
            $taskHistory['task_action']='Updated task';
            $taskHistory['created_by']=Auth::User()->id;
            $taskHistory['created_at']=date('Y-m-d H:i:s');
            $this->taskHistory($taskHistory);

            $data=[];
            if($TaskMaster->case_id!=NULL) { 
                $data['task_for_case']=$TaskMaster->case_id;  
            }   
            if($TaskMaster->lead_id!=NULL) { 
                $data['task_for_lead']=$TaskMaster->lead_id;  
            } 
            $data['task_id']=$TaskMaster->id;
            $data['task_name']=$TaskMaster->task_title;
            $data['user_id']=Auth::User()->id;
            $data['activity']='updated a task';
            $data['type']='task';
            $data['action']='update';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            
            $this->saveEditTaskReminder($request->all(),$TaskMaster->id); 
            $this->saveEditLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveNonLinkedStaffToTask($request->all(),$TaskMaster->id); 
            $this->saveEditTaskChecklist($request->all(),$TaskMaster->id); 
            if($request->from_view=="yes"){
                Session::put('task_id', $request->task_id);
            }
            
            return response()->json(['errors'=>'','user_id'=>$request->user_id]);
          exit;
        }
    }

    public function saveEditTaskChecklist($request,$task_id)
    {
          $orderValue=1;
          $finalDataList=array();
          if(isset($request['checklist-item-name'])){
                foreach($request['checklist-item-name'] as $k=>$v){
                $TaskChecklist =TaskChecklist::where("id",$k)->where("task_id", $task_id)->count();
                if($TaskChecklist=="0"){
                    $TaskChecklist = new TaskChecklist;
                    $TaskChecklist->task_id=$task_id; 
                    $TaskChecklist->checklist_order=$orderValue; 
                    $TaskChecklist->status="0"; 
                    $TaskChecklist->title=$request['checklist-item-name'][$k];
                    $TaskChecklist->created_by=Auth::user()->id; 
                    if($request['checklist-item-name'][$k]!=''){
                        $TaskChecklist->save(); //Could not store empty checklist
                        $finalDataList[]=$TaskChecklist->id;    
                    }
                }else{
                    $TaskChecklist = TaskChecklist::find($k);
                    $TaskChecklist->checklist_order=$orderValue; 
                    $TaskChecklist->title=$request['checklist-item-name'][$k];
                    $TaskChecklist->updated_by=Auth::user()->id; 
                    if($request['checklist-item-name'][$k]!=''){
                        $TaskChecklist->save(); //Could not store empty checklist
                        $finalDataList[]=$TaskChecklist->id;    
                    }  
                }
                $orderValue++;
            }
            $ids=TaskChecklist::select("*")->whereIn("id",$finalDataList)->get()->pluck('id');
            TaskChecklist::where("task_id", $task_id)->whereNotIn("id",$ids)->delete();
        }
   }
    public function saveEditTaskReminder($request,$task_id)
    {
      
        TaskReminder::where("task_id", $task_id)->where("created_by", Auth::user()->id)->delete();
        for($i=0;$i<count($request['reminder_user_type'])-1;$i++){
           $CaseTaskReminder = new TaskReminder;
           $CaseTaskReminder->task_id=$task_id; 
           $CaseTaskReminder->reminder_type=$request['reminder_type'][$i];
           $CaseTaskReminder->reminer_number=$request['reminder_number'][$i];
           $CaseTaskReminder->reminder_frequncy=$request['reminder_time_unit'][$i];
           $CaseTaskReminder->reminder_user_type=$request['reminder_user_type'][$i];
           $CaseTaskReminder->created_by=Auth::user()->id; 
           $CaseTaskReminder->remind_at=Carbon::now(); 
           $CaseTaskReminder->save();
       }
   }
   public function saveEditLinkedStaffToTask($request,$task_id)
   {
        $orderValue=1;
        $finalDataList=array();
        if(isset($request['linked_staff_checked_attend'])){
            foreach($request['linked_staff_checked_attend'] as $k=>$v){
                $CaseTaskLinkedStaff =CaseTaskLinkedStaff::where("user_id",$v)->where("task_id", $task_id)->count();
                if($CaseTaskLinkedStaff=="0"){
                    $CaseTaskLinkedStaff = new CaseTaskLinkedStaff;
                    $CaseTaskLinkedStaff->task_id=$task_id; 
                    $CaseTaskLinkedStaff->user_id=$v; 
                    if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                        $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$v];
                    }else{
                        $CaseTaskLinkedStaff->time_estimate_total="0";
                    }
                    $CaseTaskLinkedStaff->linked_or_not_with_case="yes";
                    $CaseTaskLinkedStaff->is_assign = "yes";
                    $CaseTaskLinkedStaff->created_by=Auth::user()->id; 
                    $CaseTaskLinkedStaff->save();
                    $finalDataList[]=$CaseTaskLinkedStaff->id;
                }else{
                    $CaseTaskLinkedStaffCheck =CaseTaskLinkedStaff::select("*")->where("user_id",$v)->where("task_id", $task_id)->first();
                    if(!empty($CaseTaskLinkedStaffCheck)){
                        $CaseTaskLinkedStaff = CaseTaskLinkedStaff::find($CaseTaskLinkedStaffCheck->id);
                        $CaseTaskLinkedStaff->task_id=$task_id; 
                        $CaseTaskLinkedStaff->user_id=$v;
                        if(isset($request['time_tracking_enabled']) && $request['time_tracking_enabled']=="on"){
                            $CaseTaskLinkedStaff->time_estimate_total=$request['time_estimate_for_staff'][$v];
                        }else{
                            $CaseTaskLinkedStaff->time_estimate_total="0";
                        }
                        $CaseTaskLinkedStaff->linked_or_not_with_case="yes";
                        $CaseTaskLinkedStaff->is_assign = "yes";
                        $CaseTaskLinkedStaff->updated_by=Auth::user()->id; 
                        $CaseTaskLinkedStaff->save();
                        $finalDataList[]=$CaseTaskLinkedStaffCheck->id;
                    }
                  
                }
            }
        }
        $pluckIds =CaseTaskLinkedStaff::select("*")->where("task_id", $task_id)->whereIn("id",$finalDataList)->get()->pluck("id");
        CaseTaskLinkedStaff::where("task_id", $task_id)->whereNotIn("id",$pluckIds)->forceDelete();
   }


  public function loadTaskReminderPopupIndex(Request $request)
  {
      $task_id=$request->task_id;
      $TaskReminder = TaskReminder::where("task_id",$task_id)->get();
      $from_view="no";
      if(isset($request->from_view) && $request->from_view=='yes'){
          $from_view="yes";
      }
      return view('task.loadReminderPopupIndex',compact('task_id','TaskReminder','from_view'));     
      exit;    
  }
 

  public function saveTaskReminderPopup(Request $request)
  {
        $ses='';
        $task_id=$request->task_id;
        $this->saveEditTaskReminder($request,$task_id);
        if($request->from_view=="yes"){
            $ses=Session::put('task_id', $request->task_id);
        }
        return response()->json(['errors'=>'','msg'=>'Reminders successfully updated','setSession'=>$ses]);
        exit;    
    }

  
  public function loadTimeEntryPopup(Request $request)
  {
        $task_id=$request->task_id;
        $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $loadFirmStaff = User::select("first_name","last_name","id")->where("parent_user",Auth::user()->id)->where("user_level","3")->orWhere("id",Auth::user()->id)->get();

        $case_id="";

        $TaskActivity=TaskActivity::where('status','1')->get();
        $TaskData=Task::find($task_id);
        $from_view="no";
        if(isset($request->from_view) && $request->from_view=='yes'){
            $from_view="yes";
        }
        return view('task.loadTimeEntryPopup',compact('task_id','CaseMasterData','case_id','loadFirmStaff','TaskActivity','TaskData','from_view'));     
        exit;    
  } 

  public function saveTimeEntryPopup(Request $request)
  {
      
    $validator = \Validator::make($request->all(), [
        'case_or_lead' => 'required',
        'staff_user' => 'required',
    ]);
    if ($validator->fails())
    {
        return response()->json(['errors'=>$validator->errors()->all()]);
    }else{

        $TaskTimeEntry = new TaskTimeEntry;
        
        $TaskTimeEntry->task_id=$request->task_id;
        $TaskTimeEntry->case_id =$request->case_or_lead;
        $TaskTimeEntry->user_id =$request->staff_user;
        $TaskTimeEntry->firm_id =auth()->user()->firm_name;
        if(isset($request->activity_text)){
            $TaskAvtivity = new TaskActivity;
            $TaskAvtivity->title=$request->activity_text;
            $TaskAvtivity->status="1";
            $TaskAvtivity->created_by=Auth::User()->id; 
            $TaskAvtivity->save();
            $TaskTimeEntry->activity_id=$TaskAvtivity->id;
        }else{
            $TaskTimeEntry->activity_id=$request->activity;
        }
        if($request->time_tracking_enabled=="on"){
            $TaskTimeEntry->time_entry_billable="yes";
        }else{
            $TaskTimeEntry->time_entry_billable="no";
        }
        $TaskTimeEntry->description=$request->case_description;
        $TaskTimeEntry->entry_date=convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start_date)))), auth()->user()->user_timezone ?? 'UTC'); 
        $TaskTimeEntry->entry_rate=$request->rate_field_id;
        $TaskTimeEntry->rate_type=$request->rate_type_field_id;
        $TaskTimeEntry->duration =$request->duration_field;
        $TaskTimeEntry->created_by=Auth::User()->id; 
        $TaskTimeEntry->save();
        if($request->from_view=="yes"){
            Session::put('task_id', $request->task_id);
        }
        return response()->json(['errors'=>'','id'=>$TaskTimeEntry->id]);
      exit;
    }
  } 

    public function markasread()
  {
        Task::where('created_by',Auth::User()->id)
        ->update(['task_read'=>'yes']);
        return redirect('tasks');

      exit;    
  }
  public function bulkMarkAsRead(Request $request)
  {
        $data = json_decode(stripslashes($request->task_id));
        foreach($data as $k=>$v){
            Task::where('id',$v)->update(['task_read'=>'yes']);
        }
        return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
        exit;    
  }  
  public function markAsCompleted(Request $request)
  {
        $data = json_decode(stripslashes($request->task_id));
        foreach($data as $k=>$v){
            Task::where('id',$v)->update(['status'=>'1','task_completed_date'=>date('Y-m-d h:i:s'),'task_completed_by'=>Auth::User()->id]);
        }
        return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
        exit;    
  }
  public function changeDueDate(Request $request)
  {
        $data = json_decode(stripslashes($request->task_id));
        foreach($data as $k=>$v){
            Task::where('id',$v)->update(['task_due_on'=>date('Y-m-d',strtotime($request->duedate))]);
        }
        return response()->json(['errors'=>'','msg'=>'Records successfully updated']);
        exit;    
  }
  public function loadTaskActivity(Request $request)
  {        $TaskActivity=TaskActivity::where('status','1')->get();

    return view('task.taskActivity',compact('TaskActivity'));     
    exit;   
  }
  
//   public function getAndCheckDefaultCaseRate(Request $request)
//   {
//       $case_id=$request->case_id;
//     //   $checkDefaultCaseRate = U::leftJoin('users','users.id','=','case_staff.user_id')->select("*")->where("case_id",$case_id)->where("parent_user","0")->first();
//     //   print_r($checkDefaultCaseRate);

//     return response()->json(['errors'=>'','msg'=>'Records successfully found','data'=>Auth::User()->default_rate]);
//     exit;    
//   }

  public function loadTaskDetailPage(Request $request)
  {        
    $TaskActivity=TaskActivity::where('status','1')->get();
    $TaskData=Task::find($request->task_id);

    $TaskCreatedBy = Task::join("users","task.created_by","=","users.id")
        ->select('task.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_title")->where('task.id',$request->task_id)->first();

    $TaskAssignedTo = CaseTaskLinkedStaff::join("users","task_linked_staff.user_id","=","users.id")
        ->select(DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid","users.user_title","task_linked_staff.time_estimate_total")->where('task_linked_staff.task_id',$request->task_id)
       // ->where('task_linked_staff.linked_or_not_with_case','yes')
        ->get();
      
    if($TaskData->case_id!=''){
        $CaseMasterData = CaseMaster::find($TaskData->case_id);
    }

    $TaskReminders=TaskReminder::leftJoin("users","task_reminder.created_by","=","users.id")
    ->select("task_reminder.*",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'))
    ->where("task_id", $request->task_id)->get();

    $TaskChecklist = TaskChecklist::select("*")->where("task_id", $request->task_id)->orderBy('checklist_order','ASC')->get();
    $TaskChecklistCompleted = TaskChecklist::select("*")->where("task_id", $request->task_id)->where('status','1')->count();

    return view('task.taskView',compact('TaskData','CaseMasterData','TaskCreatedBy','TaskAssignedTo','TaskReminders','TaskChecklist','TaskChecklistCompleted'));     
    exit;   
  }

  public function saveTaskComment(Request $request)
  {
        $TaskComment = new TaskComment; 
        $TaskComment->task_id=$request->task_id;
        $TaskComment->title =$request->delta;
        $TaskComment->created_by=Auth::User()->id; 
        $TaskComment->save();
        return response()->json(['errors'=>'','id'=>$TaskComment->id]);
        exit;
  } 
  public function loadTaskComment(Request $request)
  {
        $task_id=$request->task_id;
        $TaskCommentData=TaskComment::leftJoin("users","task_comment.created_by","=","users.id")
        ->select("task_comment.*","users.first_name","users.last_name")
        ->where('task_id',$task_id)->get();
        return view('task.loadTaskComment',compact('TaskCommentData'));     
        exit;    
  } 

  public function taskHistory($historyData)
  {
        $TaskHistory = new TaskHistory; 
        $TaskHistory->task_id=$historyData['task_id'];
        $TaskHistory->task_action= $historyData['task_action'];
        $TaskHistory->created_by=$historyData['created_by'];
        $TaskHistory->created_at=$historyData['created_at'];
        $TaskHistory->save();
        return true;
  }
  
  public function loadTaskHistory(Request $request)
  {
        $task_id=$request->task_id;
        $taskHistoryData=TaskHistory::leftJoin("users","task_history.created_by","=","users.id")
        ->select("task_history.*",DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.first_name","users.last_name","users.user_type")
        ->where('task_id',$task_id)
        ->orderBy('task_history.id','DESC')->get();
        return view('task.loadTaskHistory',compact('taskHistoryData'));     
        exit;    
  } 

  public function updateCheckList(Request $request)
  {        
      $TaskChecklist = TaskChecklist::find($request->id);
      if($request->status=="0"){
          $TaskChecklist->status="1";
      }else{
          $TaskChecklist->status="0";
      }
      $TaskChecklist->updated_by=Auth::User()->id;
      $TaskChecklist->save();
      return response()->json(['errors'=>'','id'=>$TaskChecklist->id]);
      exit;    
  }
  public function loadCheckListView(Request $request)
  {
        $task_id=$request->task_id;
        $TaskChecklist = TaskChecklist::select("*")->where("task_id", $task_id)->orderBy('checklist_order','ASC')->get();
        $TaskChecklistCompleted = TaskChecklist::select("*")->where("task_id", $task_id)->where('status','1')->count();
        return view('task.loadCheckListView',compact('TaskChecklist','TaskChecklistCompleted'));     
        exit;    
  } 
  public function loadGrantAccessPage(Request $request)
  {
      $client_id=$request->client_id;
      $UserMasterData = User::find($client_id);
      if($UserMasterData->user_level=="5"){
        $UserAdditionInfo=LeadAdditionalInfo::where("user_id",$client_id)->first();
      }else{
        $UserAdditionInfo=UsersAdditionalInfo::where("user_id",$client_id)->first();
      }
      if($UserAdditionInfo->client_portal_enable=="0" && !$UserMasterData->email){
        return view('case.event.loadGrantAccessPage',compact('UserMasterData'));  
      }else{
        return view('case.event.loadGrantConfirmPage',compact('UserMasterData'));  
      }   
      exit;    
 }

 public function saveGrantAccessPage(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'client_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            User::where('id',$request->client_id)->update(['email'=> $request->email]);
            UsersAdditionalInfo::where('user_id',$request->client_id)->update(['client_portal_enable'=>"1",'grant_access'=>'yes']);
            LeadAdditionalInfo::where('user_id',$request->client_id)->update(['client_portal_enable'=>"1"]);
            return response()->json(['errors'=>'','id'=>$request->client_id]);
            exit;
        }
        
    }

    public function saveConfirmGrantAccessPage(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'client_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            UsersAdditionalInfo::where('user_id',$request->client_id)->update(['client_portal_enable'=>"1",'grant_access'=>'yes']);
            return response()->json(['errors'=>'','id'=>$request->client_id]);
            exit;
        }
        
    }

    public function itemCategories(Request $request)
    {        
        $allEventType = EventType::select("*")->where('status',1)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
        return view('item_categories.items',compact('allEventType'));          
        exit;    
    }

    /**
     * Show event detail 
     */
    public function eventDetail($event_id)
    {
        $event_recurring_id=base64_decode($event_id);
        $eventRecurring = EventRecurring::whereId($event_recurring_id)->has('event')->first();
        if($eventRecurring) {
            $event = Event::whereId($eventRecurring->event_id)->with('eventType', 'eventLocation', 'case')->first();
            $linkedStaff = encodeDecodeJson($eventRecurring->event_linked_staff);
            $linkedUser = [];
            if(count($linkedStaff)) {
                foreach($linkedStaff as $key => $item) {
                    $user = getUserDetail($item->user_id);
                    $linkedUser[] = (object)[
                        'user_id' => $item->user_id,
                        'full_name' => $user->full_name,
                        'user_type' => $user->user_type_text,
                        'attending' => $item->attending,
                        'utype' => 'staff',
                    ];
                }
            }
            $linkedContact = encodeDecodeJson($eventRecurring->event_linked_contact_lead);
            if(count($linkedContact)) {
                foreach($linkedContact as $key => $item) {
                    $user = getUserDetail(($item->user_type == 'lead') ? $item->lead_id : $item->contact_id);
                    $linkedUser[] = (object)[
                        'user_id' => ($item->user_type == 'lead') ? $item->lead_id : $item->contact_id,
                        'full_name' => $user->full_name,
                        'user_type' => $user->user_type_text,
                        'attending' => $item->attending,
                        'utype' => $item->user_type,
                    ];
                }
            }
            return view('calendar.event.event_detail', compact('event', 'eventRecurring', 'linkedUser'));
        } else {
            return redirect()->route('events/');
        }
    }

    /* public function printEvents(Request $request){
        // return $request->all();
        $request->start = $request->start ?? convertUTCToUserTime(date("Y-m-d H:i:s"), auth()->user()->user_timezone);
        $request->end = $request->end ?? convertUTCToUserTime(date("Y-m-d H:i:s"), auth()->user()->user_timezone);

        $CommonController= new CommonController();
        $CaseEvent = CaseEvent::where('created_by',Auth::User()->id);
        if($request->event_type){
            $event_type=$request->event_type;
            $CaseEvent=$CaseEvent->where('event_type',$event_type);
        }
        if($request->case_or_lead){
            $CaseEvent=$CaseEvent->where('case_id',$request->case_or_lead);
        }
        $CaseEvent=$CaseEvent->whereBetween('start_date',  [convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start)))), auth()->user()->user_timezone ?? 'UTC'), convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->end)))), auth()->user()->user_timezone ?? 'UTC')]);
        $CaseEvent=$CaseEvent->whereNull('case_events.deleted_at')->orderBy('case_events.start_date')->with('eventLinkedStaff','case','eventLinkedContact','leadUser')->get();
        // dd($CaseEvent);
        $newarray = array();

        $timezone=Auth::User()->user_timezone;
        foreach($CaseEvent as $k=>$v){
            if($v->event_type!=""){
            $typeEventText =  DB::table("event_type")->select('title','color_code')->where('status',"1")->where('id',$v->event_type)->first();
                $v->etext=$typeEventText;
            }else{
                $v->etext="";
            }
            $v->caseTitle = $v->case->case_title ?? (isset($v->leadUser->first_name) ? "Potential Case: ".$v->leadUser->first_name.' '.$v->leadUser->last_name :  '');
            $v->caseNumber = $v->case->case_number ?? (isset($v->leadUser->first_name) ? "Potential Case: ".$v->leadUser->first_name.' '.$v->leadUser->last_name :  '');
            if(count($v->eventLinkedStaff) > 0){
                $staffName = $caseAttend = [];
                foreach($v->eventLinkedStaff as $i => $j){
                    $caseAttend[$i] = ($j->attending =='yes') ? $j :'';            
                    $staffName[$i] = $j->first_name.' '.$j->last_name;
                }

                $v->staffName = implode(",",$staffName);
            }
            if(count($v->eventLinkedContact) > 0){
                $contactName = [];
                foreach($v->eventLinkedContact as $i => $j){
                    $contactName[$i] = $j->first_name.' '.$j->last_name;
                }

                $v->contactName = implode(",",$contactName);
            }
            $newarray[] = $v;
        }
        // print_r($CaseEvent);
        if(isset($request->show_sol_checkbox) && $request->show_sol_checkbox=="on"){
            $CaseEventSOL = CaseEvent::leftJoin('case_master','case_master.id','=','case_events.case_id')
            ->select("case_master.case_number","case_master.sol_satisfied","case_events.*")
            ->where('case_events.created_by',Auth::User()->id);
            $CaseEventSOL=$CaseEventSOL->whereBetween('start_date', [convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start)))), auth()->user()->user_timezone ?? 'UTC'), convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->end)))), auth()->user()->user_timezone ?? 'UTC')]);
            $CaseEventSOL=$CaseEventSOL->where('is_SOL','yes');
            $CaseEventSOL=$CaseEventSOL->whereNull('case_events.deleted_at')->orderBy('case_events.start_date')->get();
        }else{
            $CaseEventSOL='';
        }
        if(isset($request->show_task_checkbox) && $request->show_task_checkbox=="on"){
            $Task=Task::leftJoin('case_master','case_master.id','=','task.case_id');
            $Task=$Task->leftJoin('users','users.id','=','task.lead_id');
            $Task=$Task->select('task.*','case_master.case_title','users.first_name','users.last_name');
            $Task=$Task->where('task.created_by',Auth::User()->id);
            $Task=$Task->whereBetween('task.task_due_on', [convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start)))), auth()->user()->user_timezone ?? 'UTC'), convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->end)))), auth()->user()->user_timezone ?? 'UTC')]);
            $Task=$Task->where('task.task_due_on',"!=",'9999-12-30');            
            $Task=$Task->whereNull('task.deleted_at')->get();
        }else{
            $Task='';
        }

        $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
        $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
        return view('calendar.print', compact('newarray','CaseEventSOL','Task','caseLeadList','CaseMasterData','allEventType','request'));
    } */

    public function printEvents(Request $request){
        // return $request->all();
        $request->start = $request->start ?? convertUTCToUserTime(date("Y-m-d H:i:s"), auth()->user()->user_timezone);
        $request->end = $request->end ?? convertUTCToUserTime(date("Y-m-d H:i:s"), auth()->user()->user_timezone);
        $authUser = auth()->user();
        $timezone = $authUser->user_timezone ?? 'UTC';
        $CommonController= new CommonController();
        
        $CaseEvent = EventRecurring::whereBetween('start_date', [convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start)))), $timezone), convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->end)))), $timezone)]);
        $CaseEvent = $CaseEvent->whereHas("event", function($query) use($request) {
            if($request->event_type){
                $query->where('event_type_id', $request->event_type);
            }
            if($request->case_or_lead) {
                $query->where('case_id', $request->case_or_lead);
            }
        })->whereJsonContains('event_linked_staff', ['user_id' => (string)$authUser->id]);

        $CaseEvent = $CaseEvent->orderBy('start_date')->with('event', 'event.eventType')->get();
        $CaseEvent = $CaseEvent->sortBy(function ($product, $key) {
            return $product['start_date'].$product['event']['start_time'];
        })->values();

        $finalDataList = array();
        /* foreach($CaseEvent as $k=>$v){
            if($v->event_type!=""){
            $typeEventText =  DB::table("event_type")->select('title','color_code')->where('status',"1")->where('id',$v->event_type)->first();
                $v->etext=$typeEventText;
            }else{
                $v->etext="";
            }
            $v->caseTitle = $v->case->case_title ?? (isset($v->leadUser->first_name) ? "Potential Case: ".$v->leadUser->first_name.' '.$v->leadUser->last_name :  '');
            $v->caseNumber = $v->case->case_number ?? (isset($v->leadUser->first_name) ? "Potential Case: ".$v->leadUser->first_name.' '.$v->leadUser->last_name :  '');
            if(count($v->eventLinkedStaff) > 0){
                $staffName = $caseAttend = [];
                foreach($v->eventLinkedStaff as $i => $j){
                    $caseAttend[$i] = ($j->attending =='yes') ? $j :'';            
                    $staffName[$i] = $j->first_name.' '.$j->last_name;
                }

                $v->staffName = implode(",",$staffName);
            }
            if(count($v->eventLinkedContact) > 0){
                $contactName = [];
                foreach($v->eventLinkedContact as $i => $j){
                    $contactName[$i] = $j->first_name.' '.$j->last_name;
                }

                $v->contactName = implode(",",$contactName);
            }
            $newarray[] = $v;
        } */

        foreach($CaseEvent as $k=>$v){
            $event = $v->event;
            // $startDateTime= ($event->is_full_day == 'no') ? convertUTCToUserTime($v->start_date.' '.$event->start_time, $timezone) : convertUTCToUserTime($v->start_date.' 00:00:00', $timezone);
            $eventData = [];
            $eventData["event_id"] = $event->id ?? Null;
            $eventData["event_recurring_id"] = $v->id;
            $eventData["event_title"] = $event->event_title ?? "<No Title>";
            $eventData["event_description"] = $event->event_description;
            $startDateTime= ($event->is_full_day == 'no') ? convertToUserTimezone($v->start_date.' '.$event->start_time, $timezone) : convertToUserTimezone($v->start_date.' 00:00:00', $timezone);
            $endDateTime= ($event->is_full_day == 'no') ? convertToUserTimezone($v->end_date.' '.$event->end_time, $timezone) : convertToUserTimezone($v->end_date.' 00:00:00', $timezone);
            $eventData["start_date"] = $startDateTime->format('m/d/Y');
            $eventData["start_time"] = $startDateTime->format('h:ia');
            $eventData["end_date"] = $endDateTime->format('m/d/Y');
            $eventData["end_time"] = $endDateTime->format('h:ia');
            $eventData["start_date_time"] = $startDateTime->format('Y-m-d H:i:s');
            $eventData["etext"] = ($v->event && $event->eventType) ? $event->eventType->color_code : "";
            $eventData["caseTitle"] = ($event->case) ? $event->case->case_title : '';
            $eventData["caseNumber"] = ($event->case) ? $event->case->case_number : '';
            $decodeStaff = encodeDecodeJson($v->event_linked_staff);
            $eventData["staffName"] = $decodeStaff;
            $decodeContact = encodeDecodeJson($v->event_linked_contact_lead);
            $eventData["contactName"] = $decodeContact;
            $eventData["is_all_day"] = $event->is_full_day;
            $eventData["is_read"] = $v->is_read;
            $eventData['event_data_type'] = 'event';

            $finalDataList[] = (object)$eventData;
        }

        if(isset($request->show_sol_checkbox) && $request->show_sol_checkbox=="on") {
            $solEvents = Event::where('is_SOL','yes')->leftJoin('case_master','case_master.id','=','events.case_id')
                ->where('events.created_by', $authUser->id)
                // ->where('case_master.sol_satisfied', 'no')
                ->whereBetween('start_date', [convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start)))), $timezone), convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->end)))), $timezone)]);
            if($request->case_id != "") {
                $solEvents = $solEvents->where('case_id',$request->case_id);
            }
            $solEvents = $solEvents->select("case_master.case_unique_number as case_unique_number","case_master.sol_satisfied","case_master.case_title","events.*")
                ->whereNull('events.deleted_at')->get();
            if(count($solEvents)) {
                foreach($solEvents as $key => $item) {
                    $finalDataList[] = (object)[
                        'event_id' => $item->id,
                        'event_title' => $item->event_title,
                        "start_date" => $item->start_date,
                        "start_date_time" => $item->start_date,
                        "is_SOL" => $item->is_SOL,
                        "case_id" => $item->case_id,
                        "case_title" => $item->case_title ?? "",
                        "caseNumber" => $item->case_number ?? "",
                        "sol_satisfied" => $item->sol_satisfied,
                        'created_by' => $item->created_by,
                        'event_data_type' => 'sol',
                        'is_read' => 'yes',
                    ];
                }
            }
        }
        if(isset($request->show_task_checkbox) && $request->show_task_checkbox=="on") {
            $tasks = Task::whereBetween('task_due_on', [convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->start)))), $timezone), convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime($request->end)))), $timezone)])
                    ->whereHas('taskLinkedStaff', function($query) use($authUser) {
                        $query->where('users.id', $authUser->id);
                    })->where('task_due_on',"!=",'9999-12-30')
                    ->where("status", "0")->whereNotNull("task_due_on");
            if($request->case_id != "") {
                $tasks = $tasks->where('case_id',$request->case_id);
            }
            $tasks = $tasks->whereNull('deleted_at')->with('case')->get();
            if(count($tasks)) {
                foreach($tasks as $key => $item) {
                    $finalDataList[] = (object)[
                        'task_id' => $item->id,
                        'task_title' => $item->task_title,
                        "start_date" => $item->task_due_on,
                        "start_date_time" => $item->task_due_on,
                        "task_priority" => $item->task_priority,
                        "case_id" => $item->case_id,
                        "case_title" => $item->case->case_title ?? "",
                        "caseNumber" => $item->case->case_number ?? "",
                        "status" => ($item->status == '1') ? 'Completed' : 'Incomplete',
                        'created_by' => $item->created_by,
                        'event_data_type' => 'task',
                        'is_read' => 'yes',
                    ];
                }
            }
        }
        $finalData = collect($finalDataList)->sortBy(function($col) {
            return $col->start_date_time;
        })->values()->all();

        $CaseMasterData = CaseMaster::where('created_by',Auth::User()->id)->where('is_entry_done',"1")->get();
        $caseLeadList = LeadAdditionalInfo::join('users','lead_additional_info.user_id','=','users.id')->select("first_name","last_name","users.id","user_level")->where("users.user_type","5")->where("users.user_level","5")->where("parent_user",Auth::user()->id)->where("lead_additional_info.is_converted","no")->get();
        $allEventType = EventType::select("title","color_code","id")->where('status',1)->where('firm_id',Auth::User()->firm_name)->orderBy("status_order","ASC")->get();
        return view('calendar.print', compact('finalData'/* ,'CaseEventSOL','Task' */,'caseLeadList','CaseMasterData','allEventType','request'));
    }
}
  
