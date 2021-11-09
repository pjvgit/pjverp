@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
	<section class="detail-view" id="task_detail_view">
		<div class="detail-view__header task-view-header">check CP activity
			<div class="u-hidden-md-down">
				<button class="btn btn-primary">Upload Document</button>
			</div>
		</div>
		<div class="task-detail">
            <input type="hidden" name="task_id" id="task_id" value="{{ $task->id }}" >
            @if($task->status == 1)
            <div class="task-detail__status-row complete"><i class="fas fa-check material-icons"></i><span> Completed! </span></div>
            @else
			<div class="task-detail__status-row" id="mark_complete_div">
                <label class="checkbox checkbox-success">
                    <input type="checkbox" class="mark_as_complete" name="mark_as_complete" value="1" data-task-type="task"><span id="check_label">Mark as complete</span><span class="checkmark"></span>
                </label>
            </div>
            @endif
			<div class="task-detail__info">
				<div class="task-detail__info-row task-detail__checklist">
					<div class="task-detail__checklist-header"><i class="fas fa-list material-icons"></i><span>Checklist</span></div>
					<ul>
                        @forelse ($task->taskCheckList as $key => $item)
                            <li class="task-detail__checklist-item">
                                <label class="checkbox checkbox-secondary">
                                    <input type="checkbox" class="subtask_as_complete" name="subtask_as_complete" id="subtask_as_complete_{{ $item->id }}" value="{{ $item->id }}" data-task-type="subtask" {{ ($item->status) ? "checked" : "" }}>
                                    <span>{{ $item->title }}</span><span class="checkmark"></span>
                                </label>
                            </li>
                        @empty
                        @endforelse
					</ul>
				</div>
				<div class="task-detail__info-row"><i class="fas fa-exclamation material-icons"></i><span>{{ $task->priority_text }} Priority</span></div>
				<div class="task-detail__info-row task-detail__due-date">
                    <i class="fas fa-calendar-day material-icons"></i><span class="task-detail__due-date">{{ $task->task_due_date }}</span>
                </div>
			</div>
			<div class="task-detail__info-row task-detail__comments">
                <i class="fas fa-comment-alt material-icons"></i>
                <span>Comments (<span id="total_comment">0</span>)</span>
            </div>
            <div id="task_comment_history">
            </div>
			<ul class="detail-view__replies"></ul>
			<div class="comment">
                <div>
                    <form method="POST" action="javascript:void(0);" data-action="{{ route('client/tasks/save/comment') }}" id="task_comment_form">
                        @csrf
                        <input type="hidden" name="task_id" value="{{ $task->id }}" id="task_id">
                        <div class="form-input is-required">
                            <textarea id="task_comment" name="message" class="form-control text-composer__textarea required" rows="2" placeholder="Add Comment"></textarea>
                        </div>
                        <button type="submit" aria-label="Please fill out all required fields." class="btn btn-primary" id="post_comment_btn">Post</button>
                    </form>
                </div>
			</div>
		</div><a class="floating-action-button floating-action-button--hidden-on-large" role="button"><i class="floating-action-button__icon">insert_drive_file</i></a></section>
	<div></div>
</div>
@endsection

@section('page-js')
<script>
$(document).ready(function() {
    loadCommentHistory();
});

$(".mark_as_complete, .subtask_as_complete").on("click", function() {
    var taskId = $("#task_id").val();
    var subTaskId = $(this).val();
    var taskType = $(this).attr("data-task-type");
    $.ajax({
        url: baseUrl+"/client/tasks/update/detail",
        type: "get",
        data: {task_id: taskId, sub_task_id: subTaskId, task_type: taskType},
        success: function( response ) {
            if(response.success) {
                if(taskType == 'task') {
                    if(response.task_status == "1") {
                        $("#check_label").text("Completed!");
                    } else {
                        $("#check_label").text("Mark as read");
                    }
                }
            }
        },
    });
})

$("#task_comment_form").validate({
	rules: {
		"message": {
			required: true,      
		},
	},
	submitHandler: function() {  
		var url = $("#task_comment_form").attr('data-action'); 
        var formData = new FormData($("#task_comment_form")[0]);
		$.ajax({
			url: url,
			type: "POST",
			data: $("#task_comment_form").serialize(),
			success: function( response ) {
				if(response.success) {
                    $("#task_comment").val('');
                    toastr.success(response.message, "", {
                        positionClass: "toast-top-full-width",
                        containerId: "toast-top-full-width"
                    });
					loadCommentHistory();
				}
			},
			error: function(response) {
				if(response.responseJSON) {
					$.each(response.responseJSON, function(ind, item) {
						$("."+ind+"_error").text(item);
					});
				}
			}
		});
		return false;
	}
});

function loadCommentHistory() {
    var taskId = $("#task_id").val();
    $.ajax({
        url: baseUrl+"/client/tasks/comment/history",
        type: "GET",
        data: {task_id: taskId},
        success: function( response ) {
            if(response.view != '') {
                $("#task_comment_history").html(response.view);
                $("#total_comment").html(response.totalComment);
            }
        },
    });
}
</script>
@endsection