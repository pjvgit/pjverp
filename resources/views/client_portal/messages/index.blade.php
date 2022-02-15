@extends('client_portal.layouts.master')

@section('main-content')
<div class="row" bladefile="resources/views/client_portal/messages/index.blade.php">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="messages">
                    <ul class="nav nav-tabs nav-justified text-center u-background-white" role="tablist">
                        <li class="nav-item col-6">
                            <a class="btn btn-link nav-link @if($request->folder == "") {{ "active" }} @endif" id="inbox-message-tab" href="{{ route('client/messages') }}" role="tab" aria-controls="openTask" aria-selected="false">Inbox</a>
                        </li>
                        <li class="nav-item col-6">
                            <a class="btn btn-link nav-link @if($request->folder == "sent") {{ "active" }} @endif" id="sent-message-tab" href="{{ route('client/messages') }}?folder=sent" role="tab" aria-controls="messages" aria-selected="true">Sent</a>
                        </li>
                        <li class="nav-item col-6">
                            <a class="btn btn-link nav-link @if($request->folder == "archived") {{ "active" }} @endif" id="archived-message-tab" href="{{ route('client/messages') }}?folder=archived" role="tab" aria-controls="messages" aria-selected="true">Archived</a>
                        </li>
                        <li class="nav-item col-6">
                            <a class="btn btn-link nav-link @if($request->folder == "draft") {{ "active" }} @endif" id="draft-message-tab" href="{{ route('client/messages') }}?folder=draft" role="tab" aria-controls="messages" aria-selected="true">Draft</a>
                        </li>
                    </ul>
                    <div class="tab-content task-list" id="myTabContent">
                        <div class="tab-pane fade @if($request->folder == "") {{ "show active" }} @endif" id="openTask" role="tabpanel" aria-labelledby="inbox-message-tab">
                            @include('client_portal.messages.msg')
                        </div>
                        <div class="tab-pane fade @if($request->folder == "sent") {{ "show active" }} @endif" id="sent-message-tab" role="tabpanel" aria-labelledby=-task-tab">
                            @include('client_portal.messages.msg')
                        </div>
                        <div class="tab-pane fade @if($request->folder == "archived") {{ "show active" }} @endif" id="archived-message-tab" role="tabpanel" aria-labelledby=-task-tab">
                            @include('client_portal.messages.msg')
                        </div>
                        <div class="tab-pane fade @if($request->folder == "draft") {{ "show active" }} @endif" id="draft-message-tab" role="tabpanel" aria-labelledby=-task-tab">
                            @include('client_portal.messages.msg')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection