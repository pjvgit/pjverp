@extends('client_portal.layouts.master')

@section('main-content')
	
<main id="client_portal_content">
	<div class="app-container" id="app_container" data-active-app-section="Home">
		<div class="app-container__content">
			<section id="dashboard_view">
				<div class="dashboard-actions">
					<div class="dashboard-actions__button-container">
						<button id="send_message" class="dashboard-actions__button"><i class="far fa-envelope"></i></button>
						<label for="send_message" class="dashboard-actions__text" aria-hidden="true">Send Message</label>
					</div>
					<div class="dashboard-actions__button-container">
						<button id="add_document" class="dashboard-actions__button"><i class="far fa-file"></i></button>
						<label for="add_document" class="dashboard-actions__text" aria-hidden="true">Add Document</label>
					</div>
					<div class="dashboard-actions__button-container">
						<button id="view_bills" class="dashboard-actions__button"><i class="fas fa-dollar-sign"></i></button>
						<label for="view_bills" class="dashboard-actions__text" aria-hidden="true">View Bills</label>
					</div>
				</div>

				{{-- What's New Section --}}
				@if($totalInvoice)
				<div class="mb-3">
					<h1 class="primary-heading">What's New</h1>
					<ul class="list">
						<li class="list-row">
							<a href="{{ route('client/bills') }}"><i class="fas fa-dollar-sign list-row__icon u-color-dark-green"></i>
								<div class="list-row__body"><span class="list-row__header mt-0">{{ $totalInvoice }} unpaid invoices</span></div>
							</a>
						</li>
					</ul>
				</div>
				@endif

				{{-- Upcoming events --}}
				<div class="mb-3">
					<h1 class="primary-heading">Upcoming Events</h1>
					<ul class="list">
						@forelse ($upcomingEvents as $key => $item)
							<li class="list-row @if($item->event_read == 'no') is-unread @endif">
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
							@if($item->type == "invoices")
							<li class="list-row">
								<a href="/bills/15057828"><i class="fas fa-dollar-sign list-row__icon"></i>
									<div class="list-row__body">
										<span class="list-row__wrappable-content">{{ @$item->createdByUser->full_name}} {{ $item->activity }} 
											<span class="u-color-primary">
												@if ($item->deleteInvoice == NULL)
													<a href="{{ route('bills/invoices/view',base64_encode($item->activity_for)) }}"> #{{sprintf('%06d', $item->activity_for)}} </a>
												@else
													#{{sprintf('%06d', $item->activity_for)}}
												@endif 
											</span>
										</span><br>
										<span class="list-row__header-detail">{{ date('M d, Y h:i A', strtotime($item->formated_created_at)) }}</span>
									</div>
								</a>
							</li>
							@endif
							{{-- <li class="list-row">
								<a href="/documents/100306591"><i class="list-row__icon">description</i>
									<div class="list-row__body">
										<span class="list-row__wrappable-content">R S updated document 
											<span class="u-color-primary">Dashboard-Reminder-Popup-MyCase.png</span>
										</span><br>
										<span class="list-row__header-detail">Jul 21, 2021 11:26 PM</span>
									</div>
								</a>
							</li> --}}
						@empty
							<div class="text-center p-4"><i>No Recent Activity</i></div>
						@endforelse
						{{-- <li class="list-row"><a href="/documents/100306582"><i class="list-row__icon">description</i><div class="list-row__body"><span class="list-row__wrappable-content">R S updated document <span class="u-color-primary">id card</span></span><br><span class="list-row__header-detail">Jul 21, 2021 11:25 PM</span></div></a></li> --}}
						{{-- <li class="list-row"><a href="/documents/100306582"><i class="list-row__icon">description</i><div class="list-row__body"><span class="list-row__wrappable-content">Mary Dyer commented document <span class="u-color-primary">id card</span></span><br><span class="list-row__header-detail">Jul 21, 2021 11:24 PM</span></div></a></li> --}}
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