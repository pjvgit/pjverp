@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ @$firm->firm_logo_url }}" class="logo">
@endcomponent
@endslot

@php
    $content = str_replace('[PAID_AMOUNT]', number_format($onlinePayment->amount ?? 0, 2), $template->content);
    if($payableType == 'fundrequest') {
        $content = str_replace('[PAYABLE_ID]', 'Request #'.$onlinePayment->fund_request_id, $content);
        if(isset($payableRecord)) {
            $content = str_replace('[INVOICE_LINK]', route('client/bills/request/detail', base64_encode($payableRecord->id)), $content);
        }
    } else if($payableType == 'invoice'){
        $content = str_replace('[PAYABLE_ID]', 'Invoice #'.@$payableRecord->invoice_id, $content);
        if(isset($payableRecord)) {
            $content = str_replace('[INVOICE_LINK]', route('client/bills/detail', $payableRecord->decode_id), $content);
        }
    } else if($payableType == 'fund'){
        $content = str_replace('[PAYABLE_ID]', 'Client#'.$onlinePayment->user_id, $content);
        if(isset($payableRecord)) {
            $content = str_replace('[INVOICE_LINK]', "#", $content);
        }
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