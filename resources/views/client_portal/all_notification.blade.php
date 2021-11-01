@extends('client_portal.layouts.master')

@section('main-content')
<div class="app-container__content">
    <section id="notifications_view">
        <h1 class="primary-heading">Recent Activity</h1>
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
                            </span>
                            <br>
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
        </ul>
    </section>
    <div></div>
</div>

@endsection