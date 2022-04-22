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
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;padding-top: 0px;">This email is to inform you of an upcoming event for your case with {{ @$firm->firm_name }}.
</p>
<table  style="margin-bottom: 20px; max-width: 540px;border-collapse: collapse; background-color: #ffff;">
<tr>
<td style="padding: 10px;font-weight:600;">Event Name:</td>
<td style="padding: 10px;">{{ @$event->event_title }}</td>
</tr>
<tr>
<td style="padding: 10px;font-weight:600;">Date/Time:</td>
<td style="padding: 10px;">
@php
$userTimezone = $user->user_timezone ?? 'UTC';
$dt = new DateTime('now', new DateTimeZone($userTimezone));
$abbreviation = $dt->format('T');
if($event->is_full_day == 'no') {
    $startDateTime= convertUTCToUserTime($eventRecurring->start_date.' '.$event->start_time, $userTimezone);
    $endDateTime= convertUTCToUserTime($eventRecurring->end_date.' '.$event->end_time, $userTimezone);
}
$endOnDate = ($event->end_on && $event->is_no_end_date == 'no') ? 'until '. date('F d, Y', strtotime(convertUTCToUserDate($event->end_on, $userTimezone))) : "";
@endphp

@if(isset($event) && $event->event_recurring_type!=NULL)
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
</div>
@endif
@if($event->is_full_day == 'no')
from {{ date('h:iA',strtotime($startDateTime)) }} to {{date('h:iA',strtotime($endDateTime))}} {{ @$abbreviation }}
@else
all day
@endif
@else
@if($event->is_full_day == 'no')
{{date('M d, Y h:iA',strtotime($startDateTime))}} — {{date('h:iA',strtotime($endDateTime))}} {{ @$abbreviation }}
@else
{{ date('M d, Y',strtotime($eventRecurring->start_date)) }}, All day
@endif
@endif
</td>
</tr>
<tr>
<td style="padding: 10px;font-weight:600;">Attendance Required:</td>
<td style="padding: 10px;">	{{ @$attendEvent }}</td>
</tr>
@if($event->event_location_id)
<tr>
<td style="padding: 10px;font-weight:600;">Location:</td>
<td style="padding: 10px;">	{{ @$event->eventLocation->full_address }}</td>
</tr>
@endif
</table>
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

