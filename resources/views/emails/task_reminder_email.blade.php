@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ @$firm->firm_logo_url }}" class="logo">
@endcomponent
@endslot

<table style="padding:30px;margin:0 auto;max-width:600px;background-color:#ffff;box-sizing:border-box;border-collapse:collapse">  
<tr>
<td style="padding:30px;"><div style=" max-width:540px;margin:0px auto;padding:20px">

<table  style="margin-bottom: 20px; max-width: 540px;border-collapse: collapse; background-color: #ffff;">
<tr>
<td style="padding: 10px;font-weight:600;">Task Name</td>
<td style="padding: 10px;font-weight:600;">Case/Lead</td>
<td style="padding: 10px;font-weight:600;">Due Date</td>
<td style="padding: 10px;font-weight:600;">Priority</td>
</tr>
<tr>
<td style="padding: 10px;"><a href="{{ route("tasks", ['id' => $task->id]) }}">{{ @$task->task_title }}</a></td>
<td style="padding: 10px;">
@if($task->case_id)
{{ @$task->case->case_title }}
@elseif($task->lead_id)
{{ @$task->lead->potential_case_title }}
@else
@endif
</td>
<td style="padding: 10px;">Jun 25, 2021 {{ date('M d, Y', strtotime(convertUTCToUserDate(@$task->task_due_on, @$user->user_timezone))) }}</td>
<td style="padding: 10px;">	{{ @$task->priority_text ?? "" }}</td>
</tr>
</table>
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;padding-bottom: 10px;">For additional details about the event, please log in to your <a href="">Account</a>.
</p>
<a style="background-color: #036fb7;padding: 12px;border-radius: 5px;color: #fff;">View Event</a>

<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;">Thank you,<br>
{{ @$firm->firm_name }}
</p>      
<p style="color:grey;    font-family: sans-serif;font-size: 12px;font-weight: 500;">This is an automated notification. To protect the confidentiality of these communications,<br>
<span style="font-weight:600;">PLEASE DO NOT REPLY TO THIS EMAIL.</span> 
</p>
<p style="color:grey;    font-family: sans-serif;font-size: 12px;font-weight: 500;">This email was sent to you by Franks Hammond Plc.<br>
Powered by <a href="#">MyCase</a> | 9201 Spectrum Center Blvd STE 100, San Diego, CA 92123
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