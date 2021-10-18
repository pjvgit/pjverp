@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ @$firm->firm_logo_url }}" class="logo">
@endcomponent
@endslot

<table style="padding:30px;margin:0 auto;max-width:600px;background-color:#ffff;box-sizing:border-box;border-collapse:collapse">  
<tr>
<td style="padding:30px;">
<div style=" max-width:540px;margin:0px auto;padding:20px">
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;padding-top: 20px;">Hi {{ @$user->first_name }},
</p>
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;padding-top: 0px;">This is a reminder that you have an upcoming event.
</p>
<table  style="margin-bottom: 20px; max-width: 540px;border-collapse: collapse; background-color: #ffff;">
<tr>
<td style="padding: 10px;font-weight:600;">Event Name:</td>
<td style="padding: 10px;">{{ @$event->event_title }}</td>
</tr>
<tr>
<td style="padding: 10px;font-weight:600;">Case/Lead Name:</td>
<td style="padding: 10px;">
@if($event->case_id)
<a href="{{ route('info', $event->case->case_unique_number) }}">{{ @$event->case->case_title }}</a>
@elseif($event->lead_id)
<a href="{{ url('leads/case_details/info', $event->lead_id) }}">{{ @$event->leadUser->full_name }}</a>
@else
<p class="d-inline" style="opacity: 0.7;">Not specified</p>
@endif
</td>
</tr>
<tr>
<td style="padding: 10px;font-weight:600;">Date/Time:</td>
<td style="padding: 10px;">
@php
$dt = new DateTime('now', new DateTimeZone(@$user->user_timezone));
$abbreviation = $dt->format('T');
@endphp
{{ date('D, M jS Y, h:ia', strtotime(convertUTCToUserTime(@$event->start_date." ".@$event->start_time, @$user->user_timezone))) }} — {{date('h:ia',strtotime(convertUTCToUserTime(@$event->end_date." ".@$event->end_time, @$user->user_timezone)))}} {{ @$abbreviation }}
</td>
</tr>
<tr>
<td style="padding: 10px;font-weight:600;">Attendance Required:</td>
<td style="padding: 10px;">	{{ @$attendEvent }}</td>
</tr>
<tr>
<td style="padding: 10px;font-weight:600;">Location:</td>
<td style="padding: 10px;">	{{ @$event->eventLocation->full_address }}</td>
</tr>
</table>
@if($user->user_level == 3)
<a href="{{ route('events/detail', @$event->decode_id) }}" style="background-color: #036fb7;padding: 12px;border-radius: 5px;color: #fff;">View Event</a>
@else
<a href="{{ route('client/events/detail', @$event->decode_id) }}" style="background-color: #036fb7;padding: 12px;border-radius: 5px;color: #fff;">View Event</a>
@endif
<br>
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;padding-top: 10px;">For additional details about the event, please log in to your <a href="{{route('login')}}">Account</a>.
</p>
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;">Thank you,<br>
{{ @$firm->firm_name }}
</p>
</div> 
</td>
</tr>
</table>


{{-- Footer --}}
@slot('footer')
@component('mail::footer', ['firm_name' => @$firm->firm_name])
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot

@endcomponent     

