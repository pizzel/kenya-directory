@props(['collection'])

<a href="{{ route('collections.show', ['collection' => $collection->slug]) }}" class="discovery-card group" style="display: block; position: relative; border-radius: 16px; overflow: hidden; height: 280px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); transition: transform 0.3s ease;">
    
    {{-- Image Container with Zoom Effect --}}
    <div class="discovery-card-image" style="width: 100%; height: 100%; overflow: hidden;">
        @php
            // Use the eager-loaded relation from controller (No new query!)
            $coverBusiness = $collection->businesses->first();
            $imageUrl = $coverBusiness ? $coverBusiness->getImageUrl('card') : asset('images/placeholder-card.jpg');
        @endphp

        <img src="{{ $imageUrl }}" 
             alt="{{ $collection->title }}" 
             loading="lazy"
             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"
             onmouseover="this.style.transform='scale(1.05)'"
             onmouseout="this.style.transform='scale(1)'">
    </div>

    {{-- Gradient Overlay --}}
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.1) 60%); pointer-events: none;"></div>

    {{-- Content Overlay --}}
    <div style="position: absolute; bottom: 20px; left: 20px; width: calc(100% - 40px); color: white; z-index: 10;">
        <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; display: inline-block;">
            Collection
        </span>
        <h3 style="font-size: 1.4rem; font-weight: 800; margin: 0; color:#fff; text-shadow: 0 2px 4px rgba(0,0,0,0.3); line-height: 1.2;">
            {{ $collection->title }}
        </h3>
        <p style="margin-top: 5px; font-size: 0.9rem; opacity: 0.9;">
            {{ $collection->businesses_count }} curated places
        </p>
    </div>
</a>