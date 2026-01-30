{{-- resources/views/partials/_comment-item.blade.php --}}
<div class="comment-item" data-user-id="{{ $review->user_id }}">
    <img src="{{ asset('images/profile.png') }}" alt="{{ $review->user->name ?? 'User' }} Avatar" class="comment-avatar">
    <div class="comment-content">
        <h4>
            {{ $review->user->name ?? 'Anonymous' }}
				 <span class="comment-rating">
						@if($review->rating)
							@for ($i = 1; $i <= 5; $i++)
								<i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
							@endfor
						@endif
				</span>
        </h4>
        <span class="comment-date">{{ $review->created_at->format('g:i A - M j, Y') }}</span>
        <p>{!! nl2br(e($review->comment)) !!}</p>

        @can('delete', $review)
            <form action="{{ $review instanceof \App\Models\EventReview ? route('events.reviews.destroy', $review) : route('reviews.destroy', $review) }}" method="POST" class="inline-block mt-2" onsubmit="return confirm('Delete this comment?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 reply-link">
                    Delete My {{ $review instanceof \App\Models\EventReview ? 'Feedback' : 'Comment' }}
                </button>
            </form>
        @endcan
    </div>
</div>