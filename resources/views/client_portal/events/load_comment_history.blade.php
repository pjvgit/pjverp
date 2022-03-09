{{-- <ul class="detail-view__replies">
    @forelse ($commentData as $key => $item)
    <li class="comment"><span class="author-avatar">{{ $item->createdByUser->first_name[0] ?? '' }}</span>
		<div class="comment__header">
			<p class="comment__author">{{ $item->createdByUser->full_name ?? '' }}</p>
			<p class="comment__date">{{ date('M d, Y h:i A', strtotime($item->comment_added_at)) }}</p>
		</div>
		<p class="comment__content">{{ $item->comment }}</p>
	</li>
    @empty
    @endforelse
</ul> --}}

<ul class="detail-view__replies">
    @forelse ($commentData as $key => $item)
	@php
		$createdByUser = getUserDetail($item->created_by);
	@endphp
    <li class="comment"><span class="author-avatar">{{ $createdByUser->first_name[0] ?? '' }}</span>
		<div class="comment__header">
			<p class="comment__author">{{ $createdByUser->full_name ?? '' }}</p>
			<p class="comment__date">{{ date('M d, Y h:i A', strtotime($item->created_at)) }}</p>
		</div>
		<p class="comment__content">{!! $item->comment !!}</p>
	</li>
    @empty
    @endforelse
</ul>