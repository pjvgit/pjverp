@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
    <section id="notifications_view">
        <h1 class="primary-heading">Recent Activity</h1>
        <ul class="list" id="notifications_list">
            @forelse ($recentActivity as $key => $item)
                @include('client_portal.partial.load_activity_list')
            @empty
                <div class="text-center p-4"><i>No Recent Activity</i></div>
            @endforelse
        </ul>
    </section>
    <div></div>
</div>

@endsection