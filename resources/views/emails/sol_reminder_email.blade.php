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
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;padding-top: 0px;">This is a reminder that you have an upcoming case SOL.
</p>
<table  style="margin-bottom: 20px; max-width: 540px;border-collapse: collapse; background-color: #ffff;">
<tr>
<td style="padding: 10px;font-weight:600;">Case Name:</td>
<td style="padding: 10px;">{{ @$case->case_title }}</td>
</tr>
<tr>
<td style="padding: 10px;font-weight:600;">SOL Date:</td>
<td style="padding: 10px;">{{ date('D, M jS Y, h:ia', strtotime(convertUTCToUserTime(@$case->case_statute_date." 00:00:00", @$user->user_timezone))) }} </td>
</tr>

</table>
<a href="{{ route('info', @$case->case_unique_number) }}" style="background-color: #036fb7;padding: 12px;border-radius: 5px;color: #fff;">View Case</a>
<br>
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;padding-top: 10px;">For additional details about the event, please log in to your <a href="">Account</a>.
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


resources/views/emails/event_reminder_email.blade.php