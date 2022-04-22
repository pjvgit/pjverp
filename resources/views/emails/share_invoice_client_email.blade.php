@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ @$firm->firm_logo_url }}" class="logo">
@endcomponent
@endslot

@php
    $invoiceLink = '<a href="'.route("client/bills/detail", $invoice->decode_id).'">View Invoice</a>';
    $content = str_replace('[INVOICE_LINK]', $invoiceLink, $template->content);
    $content = str_replace('[USER_NAME]', @$user->first_name, $content);
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