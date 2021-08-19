@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ @$firm->firm_logo_url }}" class="logo">
@endcomponent
@endslot

@php
    $content = str_replace('[INVOICE_LINK]', route("client/bills/detail", $invoice->decode_id), $template->content);
    if($template->id == 22) {
        $date = ($invoice->invoiceFirstInstallment) ? \Carbon\Carbon::parse($invoice->invoiceFirstInstallment->due_date) : \Carbon\Carbon::parse($invoice->due_date);
        $content = str_replace('[INVOICE_DUE_DATE]', date("M d, Y", strtotime(convertUTCToUserDate($date->format("Y-m-d"), $user->user_timezone))), $content);
    } else if($template->id == 24) {
        $date = ($invoice->invoiceFirstInstallment) ? \Carbon\Carbon::parse($invoice->invoiceFirstInstallment->due_date) : \Carbon\Carbon::parse($invoice->due_date);
        $txt = $date->isTomorrow() ? "tomorrow" : date("M d, Y", strtotime(convertUTCToUserDate($date->format("Y-m-d"), $user->user_timezone)));
        $content = str_replace('[INVOICE_DUE_DATE]', $txt, $content);
    } else {}
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