<?php 
// echo $user["mail_body"];
?>

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
    $date = \Carbon\Carbon::parse($event->start_date);
    $content = str_replace('[EVENT_NAME]', $event->event_title, $content);
    $content = str_replace('[DATE_TIME]', date("M d, Y", strtotime(convertUTCToUserDate($date->format("Y-m-d"), $user->user_timezone))), $content);
    $content = str_replace('[EVENT_URL]', route("events/detail", $event->decode_id), $content);
    $content = str_replace('[FIRM_NAME]', @$firm->firm_name, $content);
    $content = str_replace('[CLIENT_PORTAL_URL]', url("login"), $content);
@endphp
{!! $content !!}


{{-- Footer --}}
@slot('footer')
@component('mail::footer', ['firm_name' => @$firm->firm_name])
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot

@endcomponent     