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
                    <div>{{date('h:i A',strtotime($event->start_date_time))}} - {{date('h:i A',strtotime($event->end_date_time))}}
                    <br>
                    @php
                        $startDate = strtotime($event->start_date);
                        $day = date("l", $startDate);
                    @endphp
                    @if($event->event_frequency=='DAILY')
                        <div><b>Repeats:</b> Daily</div>
                    @elseif($event->event_frequency=='EVERY_BUSINESS_DAY')
                        <div><b>Repeats:</b> Weekly on Weekdays</div>
                    @elseif($event->event_frequency=='CUSTOM')
                        <div><b>Repeats:</b> Weekly {{ ($event->end_on) ? 'until '. date('F d, Y', strtotime($event->end_on)) : '' }} on {{ $day }}</div>
                    @elseif($event->event_frequency=='WEEKLY')
                        <div><b>Repeats:</b> Weekly {{ ($event->end_on) ? 'until '. date('F d, Y', strtotime($event->end_on)) : '' }} on {{ $day }}</div>
                    @elseif($event->event_frequency=='MONTHLY')
                        <div><b>Repeats:</b> Monthly {{ ($event->end_on) ? 'until '. date('F d, Y', strtotime($event->end_on)) : '' }} on the 
                            @if($event->monthly_frequency == "MONTHLY_ON_THE")
                            {{ date("w", $startDate).date("S", mktime(0, 0, 0, 0, date("w", $startDate), 0)) }} {{ $day }}
                            @elseif($event->monthly_frequency == "MONTHLY_ON_THE_LAST")
                                last {{ $day }}
                            @else
                                {{ date('jS', $startDate) }} day of month
                            @endif
                        </div>
                    @elseif($event->event_frequency=='YEARLY')
                        <div><b>Repeats:</b> Yearly {{ ($event->end_on) ? 'until '. date('F d, Y', strtotime($event->end_on)) : '' }} 
                            @if($event->yearly_frequency == "YEARLY_ON_THE")
                                on the {{ date("w", $startDate).date("S", mktime(0, 0, 0, 0, date("w", $startDate), 0)) }} {{ $day }} in {{ date('F', $startDate)}}
                            @elseif($event->yearly_frequency == "YEARLY_ON_THE_LAST")
                                on the last {{ $day }} in {{ date('F', $startDate)}}
                            @else
                                in {{ date('F', $startDate)}} on the {{ date('jS', $startDate) }} day of the month
                            @endif
                        </div>
                    @else
                        <div><b>Repeats:</b> Never</div>
                    @endif
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
                        @forelse ($event->clientReminder as $erkey => $eritem)
                            <div>{{ ucfirst($eritem->reminder_type) }} me {{ $eritem->reminer_number}} {{ $eritem->reminder_frequncy}} before event</div>
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
    $.ajax({
        url: baseUrl+"/client/events/comment/history",
        type: "GET",
        data: {event_id: eventId},
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