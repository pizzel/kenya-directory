@props(['itinerary'])

@php
    $stopsCount = $itinerary->stops->count();
    $uniqueCounties = $itinerary->stops->map(fn($s) => $s->business?->county?->name)->filter()->unique()->take(3);
    $locationSummary = $uniqueCounties->implode(', ');
    if ($itinerary->stops->map(fn($s) => $s->business?->county?->name)->filter()->unique()->count() > 3) {
        $locationSummary .= '...';
    }
    
    $startDate = $itinerary->start_date ? $itinerary->start_date->format('M j, Y') : 'Flexible';
    $endDate = $itinerary->end_date ? $itinerary->end_date->format('M j, Y') : null;
    $dateDisplay = $startDate;
    if ($endDate && $itinerary->start_date && !$itinerary->start_date->isSameDay($itinerary->end_date)) {
        $dateDisplay .= ' - ' . $endDate;
    }
@endphp

<a href="{{ route('itineraries.show', $itinerary->slug) }}" class="itinerary-card" style="text-decoration: none; display: flex; flex-direction: column;">
    <div class="card-banner">
        <img src="{{ $itinerary->display_image }}" alt="{{ $itinerary->title }}">
        <span class="card-badge">
            <i class="fas fa-circle text-{{ $itinerary->status == 'active' ? 'green' : 'blue' }}-500 mr-1"></i>
            {{ strtoupper($itinerary->status) }}
        </span>
    </div>
    <div class="card-content" style="flex: 1; display: flex; flex-direction: column;">
        <h3 class="card-title" style="margin-bottom: 8px;">{{ $itinerary->title }}</h3>
        
        {{-- DATES & LOCATIONS --}}
        <div style="margin-bottom: 15px;">
            <p style="margin: 0 0 6px; font-size: 0.85rem; color: #475569; font-weight: 600; display: flex; align-items: center;">
                <i class="far fa-calendar-alt" style="color: #3b82f6; margin-right: 8px; width: 16px; text-align: center;"></i>
                {{ $dateDisplay }}
            </p>
            @if($locationSummary)
                <p style="margin: 0; font-size: 0.8rem; color: #64748b; display: flex; align-items: center;">
                    <i class="fas fa-map-marker-alt" style="color: #94a3b8; margin-right: 8px; width: 16px; text-align: center;"></i>
                    {{ $locationSummary }}
                </p>
            @endif
        </div>

        <p class="card-desc" style="font-size: 0.85rem; line-height: 1.5; color: #64748b; margin-bottom: 20px;">
            {{ Str::limit($itinerary->description, 100) }}
        </p>
        
        <div class="card-footer" style="margin-top: auto; padding-top: 15px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 24px; height: 24px; rounded-full; background: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.65rem; font-weight: 800; color: #475569;">
                    {{ substr($itinerary->creator->name, 0, 1) }}
                </div>
                <span style="font-size: 0.75rem; font-weight: 600; color: #475569;">{{ $itinerary->creator->name }}</span>
            </div>
            <div class="footer-stats" style="display: flex; gap: 12px; font-size: 0.75rem; color: #94a3b8; font-weight: 600;">
                <span><i class="fas fa-route"></i> {{ $stopsCount }}</span>
                <span><i class="fas fa-users"></i> {{ $itinerary->participants_count ?? $itinerary->participants()->count() }}</span>
            </div>
        </div>
    </div>
</a>
