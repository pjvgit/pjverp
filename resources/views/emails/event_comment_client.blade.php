@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ @$firm->firm_logo_url }}" class="logo">
@endcomponent
@endslot

@php
    if($event->is_full_day == 'no') {
        $str = date('Y-m-d H:i:s', strtotime(@$eventRecurring->start_date." ".@$event->start_time));
        $startDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $str, "UTC");
        $startDateTime->setTimezone($user->user_timezone);

        $str = date('Y-m-d H:i:s', strtotime(@$eventRecurring->end_date." ".@$event->end_time));
        $endDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $str, "UTC");
        $endDateTime->setTimezone($user->user_timezone);
    }

    $content = str_replace('[USER_NAME]', $user->first_name, $template->content);
    $content = str_replace('[COMMENT_ADDED_USER]', @$commentAddedUser->full_name, $content);
    $content = str_replace('[EVENT_NAME]', $event->event_title, $content);
    $dt = new DateTime('now', new DateTimeZone(@$user->user_timezone ?? 'UTC'));
    $abbreviation = $dt->format('T');
    // $date = date('M jS Y, h:ia', strtotime(convertUTCToUserTime(@$eventRecurring->start_date." ".@$event->start_time, @$user->user_timezone ?? 'UTC'))).' — '.date('h:ia',strtotime(convertUTCToUserTime(@$eventRecurring->end_date." ".@$event->end_time, @$user->user_timezone ?? 'UTC'))).' '.@$abbreviation;
    if($event->is_full_day == 'no') {
        $date = $startDateTime->format('M jS Y, h:ia').' - '.((strtotime($eventRecurring->start_date) != strtotime($eventRecurring->end_date)) ? $endDateTime->format('M jS Y, h:ia') : $endDateTime->format('h:ia'));
    } else {
        $date = convertUTCToUserDate($eventRecurring->start_date, $user->user_timezone)->format('M jS Y').', All day';
    }
    $content = str_replace('[DATE_TIME]', $date.' '.$abbreviation, $content);
    $content = str_replace('[EVENT_URL]', route('client/events/detail', $event->decode_id), $content);
    $content = str_replace('[FIRM_NAME]', @$firm->firm_name, $content);
    $content = str_replace('[CLIENT_PORTAL_URL]', config('app.url'), $content);
@endphp
{!! $content !!}


{{-- Footer --}}
@slot('footer')
@component('mail::footer', ['firm_name' => @$firm->firm_name])
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot

@endcomponent     