@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ @$firm->firm_logo_url }}" class="logo">
@endcomponent
@endslot

@php
    $content = str_replace('[USER_NAME]', $user->first_name, $template->content);
    $content = str_replace('[COMMENT_ADDED_USER]', @$commentAddedUser->full_name, $content);
    $content = str_replace('[EVENT_NAME]', $event->event_title, $content);
    $dt = new DateTime('now', new DateTimeZone(@$user->user_timezone ?? 'UTC'));
    $abbreviation = $dt->format('T');
    $date = date('M jS Y, h:ia', strtotime(convertUTCToUserTime(@$event->start_date." ".@$event->start_time, @$user->user_timezone ?? 'UTC'))).' — '.date('h:ia',strtotime(convertUTCToUserTime(@$event->end_date." ".@$event->end_time, @$user->user_timezone ?? 'UTC'))).' '.@$abbreviation;
    $content = str_replace('[DATE_TIME]', $date, $content);
    $content = str_replace('[EVENT_URL]', config('app.url')."/events/".$event->decode_id, $content);
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