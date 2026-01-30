{{-- resources/views/partials/_google-comment-item.blade.php --}}
<div class="comment-item google-review-container">
    {{-- 1. Profile Photo (Use Google's or Fallback to your default) --}}
    <img src="{{ $gReview->profile_photo_url ?? asset('images/profile.png') }}" 
         alt="{{ $gReview->author_name }} Avatar"
         loading="lazy"
         class="comment-avatar"
         referrerpolicy="no-referrer">

    <div class="comment-content" style="width: 100%;">
        {{-- Header: Name + Stars --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #202124;">
                {{ $gReview->author_name }}
            </h4>
            
            {{-- Star Rating (Right Aligned) --}}
            <span class="comment-rating" style="font-size: 0.9rem;">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="{{ $i <= $gReview->rating ? 'fas' : 'far' }} fa-star" style="color: #f59e0b;"></i>
                @endfor
            </span>
        </div>

        {{-- Sub-header: Date + Google Badge --}}
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
            <span class="comment-date" style="color: #5f6368; font-size: 0.85rem;">
                {{ \Carbon\Carbon::createFromTimestamp($gReview->time)->format('g:i A - M j, Y') }}
            </span>
            
            {{-- GOOGLE REVIEW BADGE (Pill Style) --}}
            <span style="
                background-color: #e8eaed; 
                color: #3c4043;
                border-radius: 16px; 
                padding: 4px 10px; 
                font-size: 0.75rem; 
                font-weight: 500; 
                display: inline-flex; 
                align-items: center; 
                line-height: normal;
            ">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/24px-Google_%22G%22_logo.svg.png" 
                     alt="G" 
                     loading="lazy" 
                     style="width: 14px; height: 14px; margin-right: 6px; display: block;">
                Google Review
            </span>
        </div>

        {{-- 4. Comment Text --}}
        <p>"{!! nl2br(e($gReview->text)) !!}"</p>
    </div>
</div>