<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Firm,App\EmailTemplate;
use App\Mail\TaskCommentMail;
use App\Task;
use App\TaskComment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TaskCommentEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task_id,$firm_id,$task_comment_id;

    /**
     * Create a new job instance.
     *
     * @param  Podcast  $podcast
     * @return void
     */
    public function __construct($task_id,$firm_id,$task_comment_id)
    {
        $this->task_id = $task_id;
        $this->firm_id = $firm_id;
        $this->task_comment_id = $task_comment_id;   
    }

    /**
     * Execute the job.
     *
     * @param  AudioProcessor  $processor
     * @return void
     */
    public function handle()
    {
        Log::info("task comment handle");
        $taskData = Task::find($this->task_id);
        $firmData = Firm::find($this->firm_id); 
        $taskComment = TaskComment::whereId($this->task_comment_id)->first();
        $taskWithLinkedUser = Task::whereId($this->task_id)->with(["taskLinkedStaff" => function($query) use($taskComment) {
                        $query->wherePivot("user_id", "!=", $taskComment->created_by);
                    }, "taskLinkedContact" => function($query) use($taskComment) {
                        $query->wherePivot("user_id", "!=", $taskComment->created_by);
                    }])->first();
        $getTemplateData = EmailTemplate::find(28);
        if($taskWithLinkedUser) {
            if($taskWithLinkedUser->taskLinkedStaff) {
                Log::info("task linked staff");
                foreach($taskWithLinkedUser->taskLinkedStaff as $key => $item) {
                    Mail::to($item->email)->send((new TaskCommentMail($taskData, $firmData, $item, $getTemplateData, 'staff')));        
                }
            }
            if($taskWithLinkedUser->taskLinkedContact) {
                Log::info("task contact staff");
                foreach($taskWithLinkedUser->taskLinkedContact as $key => $item) {
                    Mail::to($item->email)->send((new TaskCommentMail($taskData, $firmData, $item, $getTemplateData, 'client')));        
                }
            }
        }
    }
}