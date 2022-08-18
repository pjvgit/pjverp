@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
    <section id="events_view">
        <h1 class="primary-heading">Upcoming Events</h1>
        <ul class="list">
            @forelse ($events as $key1 => $item1)
                @php
                    $item = $item1->item;
                    if($item->event->is_full_day == 'no') {
                        $startDateTime= convertToUserTimezone($item->start_date.' '.$item->event->start_time, $authUser->user_timezone);
                        $endDateTime= convertToUserTimezone($item->end_date.' '.$item->event->end_time, $authUser->user_timezone);
                    } else {
                        $startDateTime= convertUTCToUserDate($item->start_date, $authUser->user_timezone);
                        $endDateTime= convertUTCToUserDate($item->end_date, $authUser->user_timezone);
                    }
                @endphp
                <li class="list-row @if($item->is_view == 'no') is-unread @endif">
                    <a href="{{ route('client/events/detail', $item->decode_id) }}"><i class="fas fa-calendar-day list-row__icon"></i>
                        <div class="list-row__body">
                            <span class="list-row__header mt-0">{{ $item->event->event_title ?? "<No Title>" }}</span><br>
                            <span class="list-row__header-detail">
                                @if($item->event->is_full_day == "yes")
                                {{ $startDateTime->format('M d') }}, All day
                                @else
                                {{ $startDateTime->format('M d, h:iA') }} - {{ $endDateTime->format('M d, h:iA') }}
                                @endif
                            </span><br>
                            <span class="list-row__header-detail"></span>
                        </div>
                    </a>
                </li>
            @empty
                <div class="text-center p-4"><i>No Events</i></div>
            @endforelse
        </ul>
    </section>
</div>
@endsection