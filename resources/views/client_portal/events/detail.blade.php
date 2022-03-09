@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
    <section class="detail-view" id="event_detail_view">
        <div class="event-detail">
            <div class="detail-view__header">
                <div class="truncatable-text">
                    <div class="truncatable-text__content">{{ $event->event_title ?? "<No Title>" }}</div><i class="truncatable-text__icon"></i></div>
            </div>
            <div class="event-detail__info">
                @php
                    $userTimezone = $authUser->user_timezone;
                    $startDateTime= convertUTCToUserTime($eventRecurring->start_date.' '.$event->start_time, $userTimezone ?? 'UTC');
                    $endDateTime= convertUTCToUserTime($eventRecurring->end_date.' '.$event->end_time, $userTimezone ?? 'UTC');
                    $endOnDate = ($event->end_on && $event->is_no_end_date == 'no') ? 'until '. date('F d, Y', strtotime(convertUTCToUserDate($event->end_on, $userTimezone))) : "";
                @endphp
                <div class="event-detail__info-row">
                    <i class="fas fa-calendar-day list-row__icon"></i>
                    <span>{{date('D, M jS Y',strtotime($startDateTime))}} - {{date('D, M jS Y',strtotime($endDateTime))}}</span>
                </div>
                <div class="event-detail__info-row">
                    <i class="far fa-clock list-row__icon"></i>
                    <div>
                        {{date('h:i A',strtotime($event->startDateTime))}} - {{date('h:i A',strtotime($event->endDateTime))}}
                        <br>
                        <div><b>Repeats:</b>
                            @if($event->event_recurring_type=='DAILY')
                                {{ ($event->event_interval_day > 1) ? "Every ".$event->event_interval_day." days" : "Daily" }} {{ $endOnDate }}
                            @elseif($event->event_recurring_type=='EVERY_BUSINESS_DAY')
                                Weekly {{ $endOnDate }} on Weekdays
                            @elseif($event->event_recurring_type=='CUSTOM')
                                {{ ($event->event_interval_week > 1) ? "Every ".$event->event_interval_week." weeks" : "Weekly" }} {{ $endOnDate }} on {{ implode(", ", $event->custom_event_weekdays) }}
                            @elseif($event->event_recurring_type=='WEEKLY')
                                Weekly {{ $endOnDate }} on {{ date('l', strtotime($eventRecurring->start_date))."s" }}
                            @elseif($event->event_recurring_type=='MONTHLY')
                                {{ ($event->event_interval_month > 1) ? "Every ".$event->event_interval_month." months " : "Monthly" }} {{ $endOnDate }}
                                @if($event->monthly_frequency == "MONTHLY_ON_DAY")
                                    {{ "on the ".date("jS", strtotime($eventRecurring->user_start_date))." day of the month" }}
                                @elseif($event->monthly_frequency == "MONTHLY_ON_THE")
                                    @php
                                        $day = ceil(date('j', strtotime($eventRecurring->start_date)) / 7);
                                    @endphp
                                    {{ "on the ".$day.date("S", mktime(0, 0, 0, 0, $day, 0))." ".date('l', strtotime($eventRecurring->start_date)) }}
                                @else
                                @endif
                            @elseif($event->event_recurring_type=='YEARLY')
                                {{ ($event->event_interval_year > 1) ? "Every ".$event->event_interval_year." years " : "Yearly" }} {{ $endOnDate }}
                                @if($event->yearly_frequency == "YEARLY_ON_DAY")
                                    {{ "in ".date("F", strtotime($eventRecurring->user_start_date))." on the ".date("jS", strtotime($eventRecurring->user_start_date))." day of the month" }}
                                @elseif($event->yearly_frequency == "YEARLY_ON_THE")
                                    @php
                                        $day = ceil(date('j', strtotime($eventRecurring->start_date)) / 7);
                                    @endphp
                                    {{ "on the ".$day.date("S", mktime(0, 0, 0, 0, $day, 0))." ".date('l', strtotime($event->start_date))." in ".date("F", strtotime($eventRecurring->user_start_date)) }}
                                @else
                                @endif
                            @else
                                Never
                            @endif
                        </div>
                    </div>
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
                        @php
                            $eventReminders = encodeDecodeJson($eventRecurring->event_reminders);
                        @endphp
                        @forelse ($eventReminders as $erkey => $eritem)
                            <div>{{ ucfirst($eritem->reminder_type) }} {{ $eritem->reminder_user_type }} {{ $eritem->reminer_number}} {{ $eritem->reminder_frequncy}} before event</div>
                        @empty
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="event-detail__comments">
                <i class="fas fa-comment-alt list-row__icon"></i>
                <span>Comments (<span id="total_comment">0</span>)</span>
            </div>
            <div id="event_comment_history">
            </div>
            <div class="comment">
                <div>
                    <form method="POST" action="javascript:void(0);" data-action="{{ route('client/events/save/comment') }}" id="event_comment_form">
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $event->id }}" id="event_id">
                        <input type="hidden" name="event_recurring_id" value="{{ $eventRecurring->id }}" id="event_recurring_id">
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
$(document).ready(function() {
    loadCommentHistory();
});


$("#event_comment_form").validate({
	rules: {
		"message": {
			required: true,      
		},
	},
	submitHandler: function() {  
		var url = $("#event_comment_form").attr('data-action'); 
        var formData = new FormData($("#event_comment_form")[0]);
		$.ajax({
			url: url,
			type: "POST",
			data: $("#event_comment_form").serialize(),
			success: function( response ) {
				if(response.success) {
                    $("#event_comment").val('');
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
    var eventId = $("#event_id").val();
    var eventRecurringId = $("#event_recurring_id").val();
    $.ajax({
        url: baseUrl+"/client/events/comment/history",
        type: "GET",
        data: {event_id: eventId, event_recurring_id: eventRecurringId},
        success: function( response ) {
            if(response.view != '') {
                $("#event_comment_history").html(response.view);
                $("#total_comment").html(response.totalComment);
            }
        },
    });
}
</script>
@endsection