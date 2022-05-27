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
        $content = str_replace('[CLIENT_NAME]', @$payableRecord->user->full_name ?? '', $content);
        $content = str_replace('[CASE_TITLE]', '-', $content);
        $content = str_replace('[PAYABLE_ID]', 'Request #'.$payableRecord->id, $content);
        $content = str_replace('[INVOICE_LINK]', '<a href="'.route('bills/retainer_requests').'" >View</a>', $content);
    } else if($payableType == 'invoice') {
        $content = str_replace('[CLIENT_NAME]', @$payableRecord->client->full_name ?? '', $content);
        if(!empty($payableRecord->case)) {
            $content = str_replace('[CASE_TITLE]', $payableRecord->case->case_title ?? '', $content);
        } else {
            $content = str_replace('[CASE_TITLE]', 'None', $content);
        }
        $content = str_replace('[PAYABLE_ID]', 'Invoice #'.@$payableRecord->unique_invoice_number, $content);
        $content = str_replace('[INVOICE_LINK]', '<a href="'.route('bills/invoices/view', base64_encode($onlinePayment->invoice_id)).'" >View</a>', $content);
    } else if($payableType == 'fund') {
        $content = str_replace('[CLIENT_NAME]', @$payableRecord->full_name ?? '', $content);
        $content = str_replace('[CASE_TITLE]', '-', $content);
        $content = str_replace('[PAYABLE_ID]', 'Client#'.@$payableRecord->id, $content);
        $content = str_replace('[INVOICE_LINK]', '<a href="'.route('contacts/clients/view', @$payableRecord->id).'" >View</a>', $content);
    }
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