@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
    <section id="events_view">
        <h1 class="primary-heading">Upcoming Events</h1>
        <ul class="list">
            @forelse ($events as $key => $item)
                @php
                    $startDateTime= convertUTCToUserTime($item->start_date.' '.@$item->event->start_time, $userTimezone ?? 'UTC');
                    $endDateTime= convertUTCToUserTime($item->end_date.' '.@$item->event->end_time, $userTimezone ?? 'UTC');
                @endphp
                <li class="list-row @if($item->is_view == 'no') is-unread @endif">
                    <a href="{{ route('client/events/detail', $item->decode_id) }}"><i class="fas fa-calendar-day list-row__icon"></i>
                        <div class="list-row__body">
                            <span class="list-row__header mt-0">{{ $item->event->event_title ?? "<No Title>" }}</span><br>
                            <span class="list-row__header-detail">{{date('M d, h:iA',strtotime($startDateTime))}} - {{date('M d, h:iA',strtotime($endDateTime))}}</span><br>
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