@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
	<section class="detail-view" id="task_detail_view">
		<div class="detail-view__header task-view-header">check CP activity
			<div class="u-hidden-md-down">
				<button class="desktop-new-button">Upload Document</button>
			</div>
		</div>
		<div class="task-detail">
            <input type="text" name="task_id" id="task_id" value="{{ $task->id }}" >
            @if($task->status == 1)
            <div class="task-detail__status-row needs-review alert-success" id="completed_div">
                <i class="fas fa-check-circle material-icons"></i><span> Completed! </span>
            </div>
            @else
			<div class="task-detail__status-row" id="mark_complete_div">
                {{-- <label class="radio radio-outline-dark">
                    <input type="radio" name="mark_as_complete" id="mark_as_complete" value="1"><span>Mark as complete</span><span class="checkmark"></span>
                </label> --}}
                <label class="checkbox checkbox-success">
                    <input type="checkbox" name="mark_as_complete" id="mark_as_complete" value="1"><span>Mark as complete</span><span class="checkmark"></span>
                </label>
            </div>
            @endif
			<div class="task-detail__info">
				<div class="task-detail__info-row task-detail__checklist">
					<div class="task-detail__checklist-header"><i class="fas fa-list material-icons"></i><span>Checklist</span></div>
					<ul>
                        @forelse ($task->taskCheckList as $key => $item)
                            <li class="task-detail__checklist-item">
                                <label class="radio radio-outline-dark">
                                    <input type="radio" name="mark_as_complete" value="1"><span>{{ $item->title }}</span><span class="checkmark"></span>
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

$("#mark_as_complete").on("change", function() {
    var taskId = $("#task_id").val();
    var markCompleted = $(this).val();
    $.ajax({
        url: baseUrl+"/client/tasks/update/detail",
        type: "get",
        data: {task_id: taskId, status: markCompleted},
        success: function( response ) {
            if(response.success) {
                
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