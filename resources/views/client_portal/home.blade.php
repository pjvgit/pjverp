@extends('client_portal.layouts.master')

@section('main-content')
	
<main id="client_portal_content">
	<div class="app-container" id="app_container" data-active-app-section="Home">
		<div class="app-container__content">
			<section id="dashboard_view">
				<div class="dashboard-actions">
					<div class="dashboard-actions__button-container">
						<button id="send_message" class="dashboard-actions__button"><i class="dashboard-actions__icon">email</i></button>
						<label for="send_message" class="dashboard-actions__text" aria-hidden="true">Send Message</label>
					</div>
					<div class="dashboard-actions__button-container">
						<button id="add_document" class="dashboard-actions__button"><i class="dashboard-actions__icon">insert_drive_file</i></button>
						<label for="add_document" class="dashboard-actions__text" aria-hidden="true">Add Document</label>
					</div>
					<div class="dashboard-actions__button-container">
						<button id="view_bills" class="dashboard-actions__button"><i class="dashboard-actions__icon">monetization_on</i></button>
						<label for="view_bills" class="dashboard-actions__text" aria-hidden="true">View Bills</label>
					</div>
				</div>
				<h1 class="primary-heading">Upcoming Events</h1>
				<div class="text-center p-4"><i>No Upcoming Events</i></div>
				<h1 class="primary-heading">Recent Activity </h1>
				<ul class="list" id="notifications_list">
					<li class="list-row"><a href="/documents/100306591"><i class="list-row__icon">description</i><div class="list-row__body"><span class="list-row__wrappable-content">R S updated document <span class="u-color-primary">Dashboard-Reminder-Popup-MyCase.png</span></span><br><span class="list-row__header-detail">Jul 21, 2021 11:26 PM</span></div></a></li>
					<li class="list-row"><a href="/documents/100306582"><i class="list-row__icon">description</i><div class="list-row__body"><span class="list-row__wrappable-content">R S updated document <span class="u-color-primary">id card</span></span><br><span class="list-row__header-detail">Jul 21, 2021 11:25 PM</span></div></a></li>
					<li class="list-row"><a href="/documents/100306582"><i class="list-row__icon">description</i><div class="list-row__body"><span class="list-row__wrappable-content">Mary Dyer commented document <span class="u-color-primary">id card</span></span><br><span class="list-row__header-detail">Jul 21, 2021 11:24 PM</span></div></a></li>
					<li class="list__view-all"><a href="/notifications">View all recent activity</a></li>
				</ul>
			</section>
			<div></div>
		</div>
	</div>
</main>

@endsection