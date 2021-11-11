@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
    <section id="events_view">
        <h1 class="primary-heading">Upcoming Events</h1>
        <ul class="list">
            @forelse ($events as $key => $item)
                <li class="list-row @if($item->is_view == 'no') is-unread @endif">
                    <a href="{{ route('client/events/detail', $item->decode_id) }}"><i class="fas fa-calendar-day list-row__icon"></i>
                        <div class="list-row__body">
                            <span class="list-row__header mt-0">{{ $item->event_title }}</span><br>
                            <span class="list-row__header-detail">{{date('M d, h:iA',strtotime($item->start_date_time))}} - {{date('M d, h:iA',strtotime($item->end_date_time))}}</span><br>
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