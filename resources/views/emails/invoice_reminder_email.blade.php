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
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;padding-top: 20px;">Payment Reminder:</p>
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;padding-top: 0px;">You have an invoice that was due {{ date("M d, Y", strtotime(convertUTCToUserDate($invoice->due_date, $user->user_timezone))) }}</p>
<a style="background-color: #036fb7;padding: 12px;border-radius: 5px;color: #fff;">View Invoice</a>
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;padding-top: 10px;">View your invoice online in just a few clicks.</p>
<p style="color: #000;    font-family: sans-serif;font-size: 15px;font-weight: 500;">Thank you,<br> {{ @$firm->firm_name }} </p>
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