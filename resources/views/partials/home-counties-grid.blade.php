@foreach($popularCounties as $county)
    <div class="fade-in-up destination-card" style="animation-delay: {{ $loop->index * 50 }}ms;">
        <a href="{{ route('listings.county', ['countySlug' => $county->slug]) }}">
            <div class="card-image-container">
                <img src="{{ $county->display_image_url }}" alt="{{ $county->name }}" loading="lazy">
            </div>
            <div class="destination-info">
                {{ $county->name }} 
                <span style="font-size: 0.8rem; opacity: 0.8;">({{ $county->businesses_count }} Listings)</span>
            </div>
        </a>
    </div>
@endforeach