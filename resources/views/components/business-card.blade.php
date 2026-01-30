{{-- resources/views/components/business-card.blade.php --}}
@props(['business'])

@php
    // --- Logic to calculate the combined Rating for the card ---
    // Note: We use fallbacks to 0 in case the controller didn't eager load counts
    $internalCount = $business->reviews_count ?? ($business->reviews ? $business->reviews->count() : 0);
    $internalAvg = $business->reviews_avg_rating ?? ($business->reviews ? $business->reviews->avg('rating') : 0);
    
    $googleCount = $business->google_rating_count ?? 0;
    $googleAvg = $business->google_rating ?? 0;

    $totalCount = $internalCount + $googleCount;
    $displayRating = 0;

    if ($totalCount > 0) {
        $displayRating = (($internalAvg * $internalCount) + ($googleAvg * $googleCount)) / $totalCount;
    }
@endphp

<div class="listing-card group" style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: transform 0.2s, box-shadow 0.2s; border: 1px solid #f3f4f6;">
    
    <a href="{{ route('listings.show', $business->slug) }}" class="block h-full">
        <div class="relative overflow-hidden aspect-[4/3]">
             @if ($business->is_featured)
            <span style="position: absolute; top: 12px; left: 12px; background: rgba(255, 255, 255, 0.95); color: #b45309; font-size: 0.65rem; font-weight: 800; padding: 5px 10px; border-radius: 20px; z-index: 10; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); letter-spacing: 0.5px; border: 1px solid #fef3c7; display: flex; align-items: center;">
                <i class="fas fa-bolt" style="margin-right: 4px; color: #f59e0b;"></i> FEATURED
            </span>
        @endif
            
            <img src="{{ $business->getImageUrl('card') }}" 
                 alt="{{ $business->name }}"
                 loading="lazy"
                 style="width: 100%; height: 200px; object-fit: cover; transition: transform 0.5s;"
                 onmouseover="this.style.transform='scale(1.05)'"
                 onmouseout="this.style.transform='scale(1)'">
        </div>

        <div class="p-4" style="padding: 15px;">
            {{-- Title: Dark Grey, Not Blue --}}
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1a202c !important; text-decoration: none !important; margin-bottom: 5px; line-height: 1.4;">
                {{ Str::limit($business->name, 35) }}
                @if($business->is_verified)
                    <i class="fas fa-check-circle text-blue-500" style="color: #3b82f6; font-size: 0.8rem; margin-left: 4px;" title="Verified"></i>
                @endif
            </h3>
            
            {{-- Location --}}
            <p style="font-size: 0.85rem; color: #718096; margin-bottom: 12px; display: flex; align-items: center;">
                <i class="fas fa-map-marker-alt" style="color: #cbd5e0; margin-right: 6px;"></i> 
                {{ $business->county ? $business->county->name : 'Kenya' }}
            </p>

            {{-- Stats Row --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #edf2f7;">
                {{-- Rating --}}
                <div style="display: flex; align-items: center;">
                    <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem; margin-right: 4px;"></i>
                    <span style="font-weight: 700; font-size: 0.9rem; color: #2d3748;">{{ number_format($displayRating, 1) }}</span>
                    <span style="color: #a0aec0; font-size: 0.8rem; margin-left: 2px;">({{ $totalCount }})</span>
                </div>

                {{-- Views (Subtle) --}}
                <div style="font-size: 0.75rem; color: #a0aec0;">
                    {{ number_format($business->views_count ?? 0) }} views
                </div>
            </div>
        </div>
    </a>
</div>