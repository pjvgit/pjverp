<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\Jobs\TaskCommentEmailJob;
use App\Task;
use App\TaskChecklist;
use App\TaskComment;
use App\TaskHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
class TaskController extends Controller 
{
    /**
     * Get client portal tasks
     */
    public function index()
    {
        $status = (\Route::currentRouteName() == "client/tasks/completed") ? '1' : '0';
        $tasks = Task::whereHas("taskLinkedContact", function($query) {
                        $query->where('users.id', auth()->id());
                    })->where("status", $status)->orderBy('created_at', 'asc')
                    ->get();
        return view("client_portal.task.index", compact('tasks'));
    }

    /**
     * Show task detail
     */
    public function show($id)
    {
        $taskId = encodeDecodeId($id, 'decode');
        $task = Task::where("id", $taskId)->whereHas("taskLinkedContact", function($query) {
                    $query->where("users.id", auth()->id());
                })->with("taskCheckList")->first();
        if($task) {
            return view("client_portal.task.task_detail", compact('task'));
        } else {
            return redirect()->route("client/tasks");
        }
    }

    /**
     * update task detail
     */
    public function updateDetail(Request $request)
    {
        // return $request->all();
        try {
            $authUser = auth()->user();
            $task = Task::whereId($request->task_id)->with('taskLinkedContact')->first();
            if($request->task_type == 'subtask' && $request->sub_task_id) {
                $subTask = TaskChecklist::whereId($request->sub_task_id)->whereTaskId($request->task_id)->first();
                if($subTask) {
                    $subTask->fill(['status' => ($subTask->status == "1") ? "0" : "1"])->save();
                }
                // return $subTask->refresh();
            } else {
                $task->fill([
                    'status' => ($task->status == "1") ? "0" : "1",
                    'is_need_review' => ($task->is_need_review == "no") ? "yes" : "no",
                    'task_completed_by' => $authUser->id,
                    'task_completed_date' => Carbon::now()
                ])->save();
                
                $data=[];
                $data['task_id'] = $task['id'];
                $data['task_name'] = $task['task_title'];
                $data['user_id'] = $authUser->id;
                $data['task_for_case'] = $task['case_id'] ?? Null; 
                $data['task_for_lead'] = $task['lead_id'] ?? Null;  
                $data['activity'] = ($task->status == "1") ? 'completed task' : 'marked as incomplete task';
                $data['type'] = 'task';
                $data['action'] = ($task->status == "1") ? 'complete' : 'incomplete';
                $CommonController= new CommonController();
                $CommonController->addMultipleHistory($data);

                // For client portal activity
                if($task && $task->taskLinkedContact) {
                    foreach($task->taskLinkedContact as $key => $item) {
                        $data['user_id'] = $item->id;
                        $data['client_id'] = $item->id;
                        $data['activity'] = 'marked task';
                        $data['is_for_client'] = 'yes';
                        $CommonController->addMultipleHistory($data);
                    }
                }

                // For task history
                TaskHistory::create([
                    'task_id' => $task->id,
                    'task_action' => ($task->status == "1") ? 'Completed task' : 'Marked task as incomplete',
                    'created_by' => $authUser->id,
                    'created_at' => $task->task_completed_date,
                ]);
            }
            return response()->json(['success'=> true, 'message' => "Task updated", 'task_status' => $task->status]);
        } catch(Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Save event comment
     */
    public function saveComment(Request $request)
    {
        try {
            $authUser = auth()->user();
            $task = Task::whereId($request->task_id)->with('taskLinkedContact')->first();
            $comment = TaskComment::create([
                'task_id' => $request->task_id,
                'title' => $request->message,
                'created_by' => $authUser->id,
            ]);

            $data=[];
            $data['task_id'] = $task['id'];
            $data['task_name'] = $task['task_title'];
            $data['user_id'] = $authUser->id;
            $data['task_for_case'] = $task['case_id'] ?? Null; 
            $data['task_for_lead'] = $task['lead_id'] ?? Null;  
            $data['activity'] = 'commented on task';
            $data['type'] = 'task';
            $data['action'] = 'comment';
            $CommonController= new CommonController();
            $CommonController->addMultipleHistory($data);

            // For client portal activity
            if($task && $task->taskLinkedContact) {
                foreach($task->taskLinkedContact as $key => $item) {
                    $data['user_id'] = $item->id;
                    $data['client_id'] = $item->id;
                    $data['activity'] = 'commented task';
                    $data['is_for_client'] = 'yes';
                    $CommonController->addMultipleHistory($data);
                }
            }

            dispatch(new TaskCommentEmailJob($request->task_id, $authUser->firm_name, $comment->id));

            return response()->json(['success'=> true, 'message' => "Comment added"]);
        } catch(Exception $e) {
            return response()->json(["error" => true, "message" => $e->getMessage()]);
        }
    }

    /**
     * Get task comment history
     */
    public function taskCommentHistory(Request $request)
    {
        $commentData = TaskComment::where("task_id", $request->task_id)->orderBy('created_at')->with("createdByUser")->get();
        $view = view('client_portal.task.load_comment_history',compact('commentData'))->render();
        return response()->json(['totalComment' => $commentData->count(), 'view' => $view]);
    }  
}