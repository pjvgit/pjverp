@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ @$firm->firm_logo_url }}" class="logo">
@endcomponent
@endslot

@php
    $content = str_replace('[PAYABLE_AMOUNT]', number_format(@$onlinePayment->amount ?? 0, 2), $template->content);
    $content = str_replace('[REFERENCE_NUMBER]', @$onlinePayment->conekta_payment_reference_id ?? '', $content);
    $content = str_replace('[INVOICE_ID]', @$onlinePayment->invoice_id, $content);
    $content = str_replace('[EXPIRES_DATE]', @$onlinePayment->expires_date, $content);
    $content = str_replace('[EXPIRES_TIME]', @$onlinePayment->expires_time, $content);
    $content = str_replace('[FIRM_NAME]', @$firm->firm_name, $content);
@endphp
{!! $content !!}


{{-- Footer --}}
@slot('footer')
@component('mail::footer', ['firm_name' => @$firm->firm_name])
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot

@endcomponent     