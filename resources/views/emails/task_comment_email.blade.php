@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ @$firm->firm_logo_url }}" class="logo">
@endcomponent
@endslot

@php
    $content = str_replace('[USER_NAME]', $user->first_name, $template->content);
    if($userType == "client") {
        $content = str_replace('[TASK_URL]', route("client/tasks/detail", encodeDecodeId($task->id, 'encode')), $content);
    } else {
        $content = str_replace('[TASK_URL]', route("tasks", ['id' => $task->id]), $content);
    }
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