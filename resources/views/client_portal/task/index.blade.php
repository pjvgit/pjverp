@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
    <section id="tasks-view">
        <div class="tasks">
            <ul class="nav nav-tabs nav-justified text-center u-background-white" role="tablist">
                <li class="active py-2 col-6"><a class="btn btn-link" href="/tasks">Open Tasks</a></li>
                <li class=" py-2 col-6"><a class="btn btn-link" href="/tasks?complete=true">Completed</a></li>
            </ul>
            <div id="filterable-tasks">
                <ul class="list">
                    <li class="list-row">
                        <a href="/tasks/22608721">
                            <div class="list-row__body list-row__body--nowrap">
                                <div class="d-flex justify-content-between"><span class="list-row__body task-name">check CP activity</span><span class=" list-row__body text-right due-date">Due in 24 days</span></div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </section>
    <div></div>
</div>
@endsection