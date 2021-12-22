@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ @$firm->firm_logo_url }}" class="logo">
@endcomponent
@endslot

@php
    $content = str_replace('[PAID_AMOUNT]', number_format($onlinePayment->amount ?? 0, 2), $template->content);
    $content = str_replace('[CLIENT_NAME]', @$invoice->client->full_name ?? '', $content);
    if(!empty($invoice->case)) {
        $content = str_replace('[CASE_TITLE]', $invoice->case->case_title ?? '', $content);
    } else {
        $content = str_replace('[CASE_TITLE]', 'None', $content);
    }
    $content = str_replace('[INVOICE_ID]', $invoice->id, $content);
    $content = str_replace('[INVOICE_LINK]', '<a href="'.route('bills/invoices/view', $invoice->decode_id).'" >View</a>', $content);
    $content = str_replace('[SITE_URL]', '<a href="'.url('/').'" >'.config('app.name').'</a>', $content);
@endphp
{!! $content !!}


{{-- Footer --}}
@slot('footer')
@component('mail::footer', ['firm_name' => @$firm->firm_name])
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot

@endcomponent     