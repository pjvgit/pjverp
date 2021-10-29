@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ @$firm->firm_logo_url }}" class="logo">
@endcomponent
@endslot

@php
    $content = str_replace('[TASK_TITLE]', $task->task_title, $template->content);
    if($task->case_id) {
        $caseLead = '<a href="'.route('info', @$task->case->case_unique_number ?? $task->case_id).'">'.@$task->case->case_title.'</a>';
    } else if($task->lead_id) {
        $caseLead = '<a href="'.route("case_details/info", $task->lead_id).'">'.@$task->leadAdditionalInfo->potential_case_title.'</a>';
    } else {
        $caseLead = 'Not Specified';
    }
    $content = str_replace('[CASE_TITLE]', $caseLead, $content);
    $date = date('M d, Y', strtotime(convertUTCToUserDate(@$task->task_due_on, @$user->user_timezone)));
    $content = str_replace('[DUE_DATE]', $date, $content);
    $content = str_replace('[PRIORITY]', @$task->priority_text ?? "", $content);
    $content = str_replace('[TASK_URL]', route("tasks", ['id' => $task->id]), $content);
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