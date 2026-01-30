{{-- partials/similar-listings.blade.php --}}
@if($similarListings->isNotEmpty())
    @foreach($similarListings as $similar)
        <x-business-card :business="$similar" />
    @endforeach
@else
    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #94a3b8;">
        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
        <p>No similar listings found at this time.</p>
    </div>
@endif
