@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
    <section class="detail-view" id="event_detail_view">
        <div class="event-detail">
            <div class="detail-view__header">
                <div class="truncatable-text">
                    <div class="truncatable-text__content">{{ $event->event_title }}</div><i class="truncatable-text__icon"></i></div>
            </div>
            <div class="event-detail__info">
                <div class="event-detail__info-row">
                    <i class="fas fa-calendar-day list-row__icon"></i>
                    <span>{{date('D, M jS Y',strtotime($event->start_date_time))}} - {{date('D, M jS Y',strtotime($event->end_date_time))}}</span>
                </div>
                <div class="event-detail__info-row">
                    <i class="far fa-clock list-row__icon"></i>
                    <div>{{date('h:i A',strtotime($event->start_date_time))}} - {{date('h:i A',strtotime($event->end_date_time))}}</div>
                </div>
                <div class="event-detail__info-row">
                    <i class="fas fa-map-marker-alt list-row__icon"></i>
                    <div class="u-min-width-0">
                        <div>{{ ($event->eventLocation) ? @$event->eventLocation->location_name : 'No Location' }}</div>
                    </div>
                </div>
                <div class="event-detail__info-row">
                    <i class="fas fa-briefcase list-row__icon"></i>
                    <div class="u-min-width-0">
                        @if($event->case)
                            {{ $event->case->case_title }}
                        @elseif($event->leadUser)
                            {{ $event->full_name }}
                        @else 
                            {{ '<No Title>' }}
                        @endif
                    </div>
                </div>
                <div class="event-detail__info-row">
                    <i class="fas fa-file-alt list-row__icon"></i>
                    <div class="event-detail__description">{{ $event->event_description }}</div>
                </div>
                <div class="event-detail__info-row">
                    <i class="far fa-clock list-row__icon"></i>
                    <div class="event-reminder">
                        @forelse ($event->clientReminder as $erkey => $eritem)
                            <div>{{ ucfirst($eritem->reminder_type) }} me {{ $eritem->reminer_number}} {{ $eritem->reminder_frequncy}} before event</div>
                        @empty
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="event-detail__comments">
                <i class="fas fa-comment-alt list-row__icon"></i>
                <span>Comments (0)</span>
            </div>
            <ul class="detail-view__replies"></ul>
            <div class="comment">
                <div>
                    <form method="POST" action="javascript:void(0);" data-action="{{ route('client/events/save/comment') }}" id="event_comment_form">
                        @csrf
                        <input type="text" name="event_id" value="{{ $event->id }}" >
                        <div class="form-input is-required">
                            <textarea id="event_comment" name="message" class="form-control text-composer__textarea required" rows="2" placeholder="Add Comment"></textarea>
                        </div>
                        <button type="submit" aria-label="Please fill out all required fields." class="btn btn-primary" id="post_comment_btn">Post</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('page-js')
<script>
$("#event_comment_form").validate({
	rules: {
		"message": {
			required: true,      
		},
	},
	submitHandler: function(form) {  
		var url = $("#event_comment_form").attr('data-action');  
		$('.error').text(''); 
        var formData = new FormData($("#event_comment_form")[0]);
		$.ajax({
			url: url,
			type: "POST",
			data: formData,
			success: function( response ) {
				if(response.success) {
					swal({
						title: response.message,
						text: "",
						type: response.icon,
					});
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
</script>
@endsection