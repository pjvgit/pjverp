@extends('client_portal.layouts.master')
@section('title', 'Home | Client Portal')
@section('main-content')
	
<main id="client_portal_content">
	<div class="app-container" id="app_container" data-active-app-section="Home">
		<div class="app-container__content">
			<section id="dashboard_view">
				<div class="dashboard-actions">
					<div class="dashboard-actions__button-container">
						<button id="send_message" class="dashboard-actions__button" onclick="addNewMessage();"><i class="far fa-envelope"></i></button>
						<label for="send_message" class="dashboard-actions__text" aria-hidden="true">Send Message</label>
					</div>
					{{-- <div class="dashboard-actions__button-container">
						<button id="add_document" class="dashboard-actions__button"><i class="far fa-file"></i></button>
						<label for="add_document" class="dashboard-actions__text" aria-hidden="true">Add Document</label>
					</div>--}}

					<div class="dashboard-actions__button-container">
						<a href="{{ route('client/bills') }}" id="view_bills" class="dashboard-actions__button"><i class="fas fa-dollar-sign"></i></a>
						<label for="view_bills" class="dashboard-actions__text" aria-hidden="true">View Bills</label>
					</div>
				</div>

				{{-- What's New Section --}}

				@if($totalInvoice > 0 || $totalMessages > 0)
				<div class="mb-3">
					<h1 class="primary-heading">What's New</h1>
					@if($totalInvoice)
					<ul class="list">
						<li class="list-row">
							<a href="{{ route('client/bills') }}">
								{{-- <i class="fas fa-dollar-sign list-row__icon u-color-dark-green"></i> --}}
								<img src="{{ asset('icon/dollar-green.png') }}" class="green-dollar"/>
								<div class="list-row__body"><span class="list-row__header mt-0">{{ $totalInvoice }} unpaid invoices</span></div>
							</a>
						</li>
					</ul>
					@endif
					@if($totalMessages)
					<ul class="list">
						<li class="list-row">
							<a href="{{ route('client/messages') }}">
							<i class="far fa-envelope fa-2x" style="color:red;" ></i>&nbsp;&nbsp;&nbsp;
							<div class="list-row__body"><span class="list-row__header mt-0">{{$totalMessages}} new message</span></div>
							</a>
						</li>
					</ul>
					@endif
				</div>
				@endif

				{{-- Upcoming events --}}
				<div class="mb-3">
					<h1 class="primary-heading">Upcoming Events</h1>
					<ul class="list">
						@forelse ($upcomingEvents as $key => $item)
							<li class="list-row @if($item->is_view == 'no') is-unread @endif">
								<a href="{{ route('client/events/detail', $item->decode_id) }}"><i class="fas fa-calendar-day list-row__icon"></i>
									<div class="list-row__body">
										<span class="list-row__header mt-0">{{ $item->event_title }}</span><br>
										<span class="list-row__header-detail">{{date('M d, h:iA',strtotime($item->start_date_time))}} - {{date('M d, h:iA',strtotime($item->end_date_time))}}</span><br>
										<span class="list-row__header-detail"></span>
									</div>
								</a>
							</li>
						@empty
							<div class="text-center p-4"><i>No Upcoming Events</i></div>
						@endforelse
						@if(count($upcomingEvents))
						<li class="list__view-all"><a href="{{ route('client/events') }}">View all events</a></li>
						@endif
					</ul>
				</div>

				{{-- Recent activity --}}
				<div class="mb-3">
					<h1 class="primary-heading">Recent Activity </h1>
					<ul class="list" id="notifications_list">
						@forelse ($recentActivity as $key => $item)
							@include('client_portal.partial.load_activity_list')
						@empty
							<div class="text-center p-4"><i>No Recent Activity</i></div>
						@endforelse
						@if(count($recentActivity))
						<li class="list__view-all"><a href="{{ route('client/notifications') }}">View all recent activity</a></li>
						@endif
					</ul>
				</div>
			</section>
			<div></div>
		</div>
	</div>
</main>

@endsection
@section('page-js')
<script src="{{ asset('assets\client_portal\js\messages\messages.js?').env('CACHE_BUSTER_VERSION') }}" ></script>
@endsection