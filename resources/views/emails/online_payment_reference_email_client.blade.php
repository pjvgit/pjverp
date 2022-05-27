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
    if($payableType == 'fundrequest') {
        $content = str_replace('[PAYABLE_ID]', 'Request #'.$onlinePayment->fund_request_id, $content);
    } else if($payableType == 'invoice') {
        $content = str_replace('[PAYABLE_ID]', 'Invoice #'.@$payableRecord->unique_invoice_number, $content);
    } else {
        $content = str_replace('[PAYABLE_ID]', 'Client#'.$onlinePayment->user_id, $content);
    }
    $content = str_replace('[EXPIRES_DATE]', @$onlinePayment->expires_date, $content);
    $content = str_replace('[EXPIRES_TIME]', @$onlinePayment->expires_time, $content);
    $content = str_replace('[FIRM_NAME]', @$firm->firm_name, $content);

    $content = str_replace('[BANK_NAME]', @$onlinePayment->conekta_order_object['charges']['data'][0]['payment_method']['bank'], $content);
    $content = str_replace('[BENEFICIARY_FIRM_NAME]', @$firm->firm_name, $content);
    $content = str_replace('[CLABE_NUMBER]', @$onlinePayment->conekta_payment_reference_id, $content);
    $content = str_replace('[LAWYER_EMAIL]', @$payableRecord->createdByUser->email, $content);
@endphp
{!! $content !!}


{{-- Footer --}}
@slot('footer')
@component('mail::footer', ['firm_name' => @$firm->firm_name])
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot

@endcomponent     