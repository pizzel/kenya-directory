@foreach($counties as $county)
    <a href="{{ route('listings.county', ['countySlug' => $county->slug]) }}" class="county-card-link" style="text-decoration: none; color: inherit; display: block;">
        {{-- Wrapper matches your 'skeleton-county' class for consistent height --}}
        <div class="skeleton-county destination-card" style="position: relative; border-radius: 12px; overflow: hidden; height: 250px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
            
            {{-- Image --}}
            <div class="card-image-container" style="width: 100%; height: 100%;">
                <img src="{{ $county->display_image_url ?? asset('images/placeholder-county.jpg') }}" 
                     alt="{{ $county->name }}" 
                     loading="lazy"
                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
            </div>
            
            {{-- Gradient Overlay (Ensures text is readable) --}}
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 60%); pointer-events: none;"></div>
            
            {{-- Text Info (Matches EXACTLY the Old Partial Text Style) --}}
            <div class="destination-info" style="position: absolute; bottom: 15px; left: 15px; color: white; z-index: 2; font-weight: 700; font-size: 1.1rem; width: 90%;">
                {{ $county->name }} 
                {{-- Helper Span for count --}}
                <span style="font-size: 0.85rem; opacity: 0.9; font-weight: 400; display: block; margin-top: 2px;">
                    {{ $county->businesses_count }} Listings
                </span>
            </div>
        </div>
    </a>
@endforeach