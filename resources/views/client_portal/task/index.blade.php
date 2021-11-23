@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
    <section id="tasks-view">
        <div class="tasks">
            <ul class="nav nav-tabs nav-justified text-center u-background-white" role="tablist">
                <li class="nav-item py-2 col-6">
                    <a class="btn btn-link nav-link @if(Route::currentRouteName() == "client/tasks") {{ "active" }} @endif" id="open-task-tab" href="{{ route('client/tasks') }}" role="tab" aria-controls="openTask" aria-selected="false">Open Tasks</a>
                </li>
                <li class="nav-item py-2 col-6">
                    <a class="btn btn-link nav-link @if(Route::currentRouteName() == "client/tasks/completed") {{ "active" }} @endif" id="completed-task-tab" href="{{ route('client/tasks/completed') }}" role="tab" aria-controls="completedTask" aria-selected="true">Completed</a>
                </li>
            </ul>
            <div class="tab-content task-list" id="myTabContent">
                <div class="tab-pane fade @if(Route::currentRouteName() == "client/tasks") {{ "show active" }} @endif" id="openTask" role="tabpanel" aria-labelledby="open-task-tab">
                    <ul class="list">
                        @forelse ($tasks as $key => $item)
                            <li class="list-row">
                                <a href="{{ route('client/tasks/detail', encodeDecodeId($item->id, 'encode')) }}">
                                    <div class="list-row__body list-row__body--nowrap">
                                        <div class="d-flex justify-content-between">
                                            <span class="list-row__body task-name">{{ $item->task_title }}</span>
                                            <span class=" list-row__body text-right due-date">{{ getDueText($item->task_due_on) }}</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li style="list-style: none;"><div class="text-center p-4"><i>No Tasks</i></div></li>
                        @endforelse
                    </ul>
                </div>
                <div class="tab-pane fade @if(Route::currentRouteName() == "client/tasks/completed") {{ "show active" }} @endif" id="completedTask" role="tabpanel" aria-labelledby="completed-task-tab">
                    <ul class="list">
                        @forelse ($tasks as $key => $item)
                            <li class="list-row">
                                <a href="{{ route('client/tasks/detail', encodeDecodeId($item->id, 'encode')) }}">
                                    <div class="list-row__body list-row__body--nowrap">
                                        <div class="d-flex justify-content-between">
                                            <span class="list-row__body task-name">{{ $item->task_title }}</span>
                                            <span class=" list-row__body text-right due-date">{{ getDueText($item->task_due_on) }}</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li style="list-style: none;"><div class="text-center p-4"><i>No Tasks</i></div></li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <div></div>
</div>
@endsection