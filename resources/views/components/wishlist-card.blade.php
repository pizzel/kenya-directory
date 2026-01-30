@props(['item', 'type'])

{{-- Check if the user has already reviewed this specific business --}}
@php
    $existingReview = $item->reviews->where('user_id', auth()->id())->first();
@endphp

<div class="wishlist-card group" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; align-items: center; padding: 16px; transition: transform 0.2s; position: relative; gap: 20px;">
    
    {{-- Delete Button (Subtle Trash Icon) --}}
    <button class="wishlist-action-btn" 
            data-url="{{ route('wishlist.business.toggle', $item->slug) }}" 
            data-action="remove"
            style="position: absolute; top: 12px; right: 12px; background: transparent; border: none; color: #cbd5e1; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: color 0.2s; z-index: 20;"
            title="Remove from list"
            onmouseover="this.style.color='#ef4444'"
            onmouseout="this.style.color='#cbd5e1'">
        <i class="far fa-trash-alt" style="font-size: 1rem;"></i>
    </button>

    {{-- Image (Larger & Aspect Ratio Locked) --}}
    <div style="width: 130px; height: 95px; flex-shrink: 0; border-radius: 10px; overflow: hidden; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);">
        <img src="{{ $item->getImageUrl('card') }}" alt="{{ $item->name }}" style="width: 100%; height: 100%; object-fit: cover;">
    </div>

    {{-- Info (Middle Column - Takes up space) --}}
    <div style="flex-grow: 1; min-width: 0; padding-right: 10px;">
        <div style="margin-bottom: 6px;">
            <a href="{{ route('listings.show', $item->slug) }}" style="text-decoration: none; color: #1e293b; font-size: 1.1rem; font-weight: 700; line-height: 1.2; display: flex; align-items: center;" class="hover:text-blue-600 transition">
                {{ $item->name }}
                @if($item->is_verified)
                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 0.9rem; margin-left: 6px;" title="Verified"></i>
                @endif
            </a>
        </div>
        
        <p style="margin: 0 0 6px; font-size: 0.9rem; color: #64748b; font-weight: 500; display: flex; align-items: center;">
            <i class="fas fa-map-marker-alt" style="color: #cbd5e1; margin-right: 6px; width: 14px; text-align: center;"></i> 
            {{ $item->county->name ?? 'Kenya' }}
        </p>

        <p style="margin: 0; font-size: 0.8rem; color: #94a3b8; display: flex; align-items: center;">
            @if($type === 'visited')
                {{-- FIXED ICON: Calendar Check implies 'Done' --}}
                <i class="far fa-calendar-check" style="color: #10b981; margin-right: 6px; width: 14px; text-align: center;"></i>
                <span style="font-weight: 600; color: #059669;">Visited {{ $item->pivot->updated_at->format('M j, Y') }}</span>
            @else
                {{-- Clock implies 'Waiting' --}}
                <i class="far fa-clock" style="margin-right: 6px; width: 14px; text-align: center;"></i>
                Added {{ $item->pivot->created_at->diffForHumans() }}
            @endif
        </p>
    </div>

    {{-- Actions (Right Aligned & Centered Vertically) --}}
    <div style="padding-right: 30px; display: flex; align-items: center; height: 100%;">
        @if($type === 'bucket')
            {{-- Bucket List Action --}}
            <button class="wishlist-action-btn" 
                    data-url="{{ route('wishlist.business.toggle', $item->slug) }}" 
                    data-action="toggle_done" 
                    data-target="done"
                    style="background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 30px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; box-shadow: 0 2px 5px rgba(16, 185, 129, 0.3); transition: background 0.2s;">
                <i class="fas fa-check" style="margin-right: 6px;"></i> I've Visited
            </button>
        @else
            {{-- Visited List Action --}}
            @if($existingReview)
                {{-- If Reviewed: Show their rating (Static) --}}
                <div style="display: flex; flex-direction: column; align-items: flex-end; justify-content: center;">
                    <span style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; margin-bottom: 3px; letter-spacing: 0.05em;">Your Rating</span>
                    <div style="color: #f59e0b; font-size: 0.95rem;">
                        @for($i=1; $i<=5; $i++)
                            @if($i <= $existingReview->rating) <i class="fas fa-star"></i>
                            @else <i class="far fa-star" style="color: #e2e8f0;"></i>
                            @endif
                        @endfor
                    </div>
                </div>
            @else
                {{-- If Not Reviewed: Show Button --}}
                <a href="{{ route('listings.show', $item->slug) }}#reviews" 
                   style="background: #3b82f6; color: white; border: none; padding: 10px 24px; border-radius: 30px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.4); transition: transform 0.2s;"
                   onmouseover="this.style.transform='translateY(-1px)'"
                   onmouseout="this.style.transform='translateY(0)'">
                    <i class="fas fa-star" style="margin-right: 8px;"></i> Leave Review
                </a>
            @endif
        @endif
    </div>
</div>