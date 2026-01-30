@extends('layouts.site')

@php
    $schemaData = [
        "@context" => "https://schema.org",
        "@type" => "WebSite",
        "name" => "Discover Kenya",
        "url" => url('/'),
        "potentialAction" => [
            "@type" => "SearchAction",
            "target" => route('listings.search') . "?q={search_term_string}",
            "query-input" => "required name=search_term_string"
        ],
        "description" => "The ultimate guide to activities, experiences, and tourism in Kenya.",
        "publisher" => [
            "@type" => "Organization",
            "name" => "Discover Kenya",
            "url" => url('/'),
            "logo" => [
                "@type" => "ImageObject",
                "url" => asset('images/site-logo.png')
            ]
        ]
    ];
@endphp

@section('title', 'Best Things to Do in Kenya | Activities, Safaris & Experiences')
@section('meta_description', 'Plan your ultimate Kenya adventure. Discover top-rated activities, hidden gems, safaris, and local culture in Nairobi, Mombasa, and beyond.')
@section('meta_keywords', $seoKeywords)
@section('canonical')
    <link rel="canonical" href="{{ url()->current() }}" />
@endsection

@section('styles')

    <style>
        .hero {
        position: relative;
        height: 70vh;
        }

        /* Fade Up Entrance for NON-LCP elements */
        .fade-in-up {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .skeleton-county {
            height: 250px;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }
        
        .listings-grid { min-height: 300px; } 

        .lazy-section-wrapper {
            content-visibility: auto;
            contain-intrinsic-size: 500px;
        }
    </style>
@endsection

@section('content')
    <main>
        {{-- 1. HERO SLIDER SECTION (LCP Optimized) --}}
        @if(isset($heroSliderBusinesses) && $heroSliderBusinesses->isNotEmpty())
            {{-- Note: The class 'hero-slider-section' has Critical CSS in site.blade.php to reserve space --}}
            <section class="hero-slider-section">
                <div class="swiper heroSwiper" style="height: 100%;">
                    <div class="swiper-wrapper">
                        @foreach($heroSliderBusinesses as $index => $business)
                            {{-- 
                                CRITICAL: Add 'hero-slide-first' to the first item. 
                                This makes it display:block via CSS immediately (no JS wait).
                            --}}
                            <div class="swiper-slide {{ $index === 0 ? 'hero-slide-first' : '' }}">
                                
                                <picture class="hero-slide-picture" style="width: 100%; height: 100%; display: block;">
                                    {{-- Mobile: Reduced WebP --}}
                                    <source 
                                        media="(max-width: 767px)" 
                                        srcset="{{ $business->hero_image_url_mobile }}" 
                                        sizes="100vw"
                                    >
                                    {{-- Desktop: Standard WebP --}}
                                    <source 
                                        media="(min-width: 768px)" 
                                        srcset="{{ $business->hero_image_url }}" 
                                        sizes="100vw"
                                    >
                                    
                                    <img 
                                        src="{{ $business->hero_image_url }}" 
                                        alt="{{ $business->name }}" 
                                        class="hero-slide-image" 
                                        {{-- LCP Optimization Attributes --}}
                                        @if($index === 0) 
                                            fetchpriority="high"
                                            
                                            
                                        @else 
                                            fetchpriority="low"
                                        @endif
                                    >
                                </picture>
                                
                                <div class="slide-content container" style="position: absolute; bottom: 60px; left: 50%; transform: translateX(-50%); text-align: center; color: white; width: 100%; max-width: 900px; padding: 15px; z-index: 2;">
                                    {{-- 
                                       We remove 'fade-in-up' from the first slide to prevent Render Delay. 
                                       The user needs to see the text immediately when the image loads.
                                    --}}
                                    <h1 class="{{ $index === 0 ? '' : 'fade-in-up' }}" 
                                        style="font-size: clamp(1.75rem, 5vw, 3rem); font-weight: 800; margin-bottom: 10px; text-shadow: 0 4px 10px rgba(0,0,0,0.5);">
                                        {{ Str::limit($business->name, 50) }}
                                    </h1>
                                    
                                    @if($business->county)
                                        <p class="{{ $index === 0 ? '' : 'fade-in-up' }}" style="font-size: 1.2rem; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                            {{-- FIXED: Darker Gold for Map Marker --}}
                                            <i class="fas fa-map-marker-alt" style="color: #f59e0b;"></i> {{ $business->county->name }}
                                        </p>
                                    @endif
                                    
                                    {{-- Find this block inside the hero slider loop --}}
                                        <div class="hero-actions {{ $index === 0 ? '' : 'fade-in-up' }}" style="display: flex; gap: 15px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                            
                                            {{-- FIXED: Background changed to #2563eb for Accessibility --}}
                                            <a href="{{ route('listings.show', $business->slug) }}" class="btn btn-primary slide-cta-button" style="padding: 12px 30px; font-size: 1.1rem; font-weight: 600; border-radius: 50px; background: #2563eb; border: none; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4); color: white; text-decoration: none;">
                                                Discover More
                                            </a>

                                            {{-- Wishlist Button (Remains unchanged) --}}
                                            @auth
                                                @php $isWished = Auth::user()->wishlistedBusinesses->contains('id', $business->id); @endphp
                                                <button type="button" class="btn hero-wishlist-btn" data-id="{{ $business->id }}" data-url="{{ route('wishlist.business.toggle', $business->slug) }}" style="padding: 12px 25px; font-size: 1.1rem; font-weight: 600; border-radius: 50px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.6); color: white; backdrop-filter: blur(5px); display: flex; align-items: center; gap: 8px;">
                                                    <i class="{{ $isWished ? 'fas' : 'far' }} fa-heart" style="color: {{ $isWished ? '#ef4444' : 'white' }};"></i> <span class="btn-text">{{ $isWished ? 'Saved' : 'Add to Bucket List' }}</span>
                                                </button>
                                            @else
                                                <a href="{{ route('login') }}" style="padding: 12px 25px; font-size: 1.1rem; font-weight: 600; border-radius: 50px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.6); color: white; backdrop-filter: blur(5px); text-decoration: none; display: flex; align-items: center; gap: 8px;">
                                                    <i class="far fa-heart"></i> Add to Bucket List
                                                </a>
                                            @endauth
                                        </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-next" style="color: white; opacity: 0.7;"></div>
                    <div class="swiper-button-prev" style="color: white; opacity: 0.7;"></div>
                </div>
            </section>
        @endif

        {{-- 2. DISCOVERY COLLECTIONS (SSR) --}}
        @if(isset($discoveryCards) && $discoveryCards->isNotEmpty())
        <section class="discovery-collections-section" style="padding: 60px 0; background: #fff;">
            <div class="container">
                {{-- FIXED: Flex header to include "View All" link --}}
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px;">
                    <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin: 0;">Explore Collections</h2>
                    <a href="{{ route('collections.index') }}" style="color: #2563eb; font-weight: 600; text-decoration: none; font-size: 0.95rem;">
                        View All <i class="fas fa-arrow-right" style="font-size: 0.8em;"></i>
                    </a>
                </div>

                <div class="discovery-scroller-wrapper">
                    <button class="scroll-arrow prev" id="discoveryScrollPrev">❮</button>
                    <div class="discovery-scroller" id="discoveryScroller">
                        <div class="discovery-collections-grid">
                            @foreach($discoveryCards as $card)
                                <x-discovery-card :collection="$card" />
                            @endforeach
                        </div>
                    </div>
                    <button class="scroll-arrow next" id="discoveryScrollNext">❯</button>
                </div>
            </div>
        </section>
        @endif

        {{-- 3. PLACES NEAR ME --}}
        <section class="places-near-me-section" style="padding: 60px 0; background: #f8fafc;">
            <div class="container">
                <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 25px;">Places Near You</h2>
                <div id="locationPermissionMessage" class="location-permission-notice" style="display: none; background: white; padding: 30px; border-radius: 12px; text-align: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    {{-- FIXED: Darker Blue --}}
                    <i class="fas fa-location-arrow" style="font-size: 2rem; color: #2563eb; margin-bottom: 15px;"></i>
                    <p style="color: #64748b; margin-bottom: 20px;">Enable location to find hidden gems around you.</p>
                    {{-- FIXED: Button Color --}}
                    <button id="enableLocationBtn" class="btn btn-primary" style="background: #2563eb; color: white; padding: 10px 25px; border-radius: 8px;">Enable Location</button>
                </div>
                <div id="nearbyControls" style="display: none; margin-bottom: 30px;">
                    <div class="radius-slider-container form-group" style="max-width: 400px;">
                        {{-- FIXED: Darker Blue Text --}}
                        <label for="radiusSlider" style="font-weight: 600; color: #475569;">Distance: <span id="radiusValue" style="color: #2563eb;">25</span> km</label>
                        <input type="range" min="1" max="50" value="25" class="price-slider w-full" id="radiusSlider" name="radius">
                    </div>
                    {{-- FIXED: Button Color --}}
                    <button id="findNearbyBtn" class="btn btn-primary mt-2" style="background: #2563eb; color: white; padding: 8px 20px; border-radius: 6px;">Update Search</button>
                </div>
                <div id="showNearbyContainer" style="display: none;" class="text-center">
                    {{-- FIXED: Button Color --}}
                    <button id="showNearbyBtn" class="btn btn-primary" style="background: #2563eb; color: white; padding: 10px 25px; border-radius: 8px;">Show Places Near Me</button>
                </div>
                <div id="nearbyResultsContainer" style="display: none;">
                    <div id="nearbyLoadingSpinner" style="display:none; text-align:center; padding: 40px;">
                        <div class="spinner"></div>
                        <p style="color: #64748b; margin-top: 10px;">Scanning your area...</p>
                    </div>
                    <div id="nearbyPlacesResults" class="listings-grid mt-6"></div>
                </div>
            </div>
        </section>
        
        {{-- 
            DEFERRED SECTIONS (AJAX LOADED)
        --}}

       {{-- 4. POPULAR COUNTIES --}}
        @if(isset($popularCounties) && $popularCounties->isNotEmpty())
        <section class="popular-counties content-section" style="padding: 60px 0; background: #fff;">
            <div class="container">
                <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 25px;">Popular Destinations</h2>
                
                {{-- The Grid Container (ID is important for JS) --}}
                <div class="destination-grid" id="popularCountiesGrid">
                    @include('partials.home-counties-loop', ['counties' => $popularCounties])
                </div>

                {{-- Load More Button Container --}}
                <div id="loadMoreCountiesContainer" style="text-align: center; margin-top: 40px;">
                    <button id="loadMoreCountiesBtn" 
                            data-page="2" 
                            class="btn" 
                            style="background-color: #2563eb; color: white; padding: 12px 30px; border-radius: 50px; font-weight: 600; border: none; cursor: pointer; transition: background 0.2s;">
                        Load More Destinations
                    </button>
                </div>
            </div>
        </section>
        @endif

        {{-- 5. TRENDING --}}
        <section class="most-popular-places lazy-section-wrapper" style="padding: 60px 0; background: #f8fafc;">
            <div class="container">
                <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 25px;">Trending Right Now</h2>
                <div class="listings-grid lazy-section" data-section="trending">
                    @for($i = 0; $i < 4; $i++) <x-skeleton-card /> @endfor
                </div>
            </div>
        </section>

        {{-- 6. NEW ARRIVALS --}}
        <section class="recently-added lazy-section-wrapper" style="padding: 60px 0; background: #fff;">
            <div class="container">
                <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 25px;">New Arrivals</h2>
                <div class="listings-grid lazy-section" data-section="new-arrivals">
                     @for($i = 0; $i < 4; $i++) <x-skeleton-card /> @endfor
                </div>
            </div>
        </section>

        {{-- 7. HIDDEN GEMS --}}
        <section class="hidden-gems lazy-section-wrapper" style="padding: 60px 0; background: #f8fafc;">
            <div class="container">
                <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 5px; color: #1e293b;">Discover Hidden Gems</h2>
                <p class="section-subtitle" style="color: #64748b; margin-bottom: 20px;">Unearth unique spots and local favorites you won't find anywhere else.</p>
                 <div class="listings-grid lazy-section" data-section="hidden-gems">
                     @for($i = 0; $i < 4; $i++) <x-skeleton-card /> @endfor
                </div>
            </div>
        </section>
    </main>
@endsection

@push('footer-scripts')
    <script type="application/ld+json">
        {!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // --- 1. INTERSECTION OBSERVER ---
            const lazySections = document.querySelectorAll('.lazy-section');
            
            if("IntersectionObserver" in window) {
                const observer = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const container = entry.target;
                            const sectionType = container.getAttribute('data-section');
                            observer.unobserve(container);
                            fetchContent(container, sectionType);
                        }
                    });
                }, { rootMargin: "300px" });

                lazySections.forEach(section => observer.observe(section));
            } else {
                lazySections.forEach(section => {
                    fetchContent(section, section.getAttribute('data-section'));
                });
            }

            function fetchContent(container, type) {
                const endpoint = `{{ route('ajax.home-section') }}?section=${type}`;
                
                fetch(endpoint, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                })
                .then(response => {
                    if(!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    container.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching section:', type, error);
                });
            }

            // --- 2. OPTIMIZED SWIPER ---
            window.addEventListener('load', function() {
            requestIdleCallback(function() {
            if (document.querySelector('.heroSwiper')) {
                    const heroSwiper = new Swiper('.heroSwiper', {
                        loop: false, 
                        // Slight delay to ensure main thread is free before auto-sliding starts
                        autoplay: { delay: 7000, disableOnInteraction: false },
                        effect: 'fade',
                        fadeEffect: { crossFade: true },
                        pagination: { el: '.swiper-pagination', clickable: true },
                        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                        // Optimization: Do not update on every frame, save CPU
                        watchSlidesProgress: true, 
                     });
                    }
                });
            });

            // --- 3. SCROLLER LOGIC ---
            const discoveryScroller = document.getElementById('discoveryScroller');
            const prevBtn = document.getElementById('discoveryScrollPrev');
            const nextBtn = document.getElementById('discoveryScrollNext');

            if (discoveryScroller && prevBtn && nextBtn) {
                function checkScroll() {
                    const maxScroll = discoveryScroller.scrollWidth - discoveryScroller.clientWidth;
                    prevBtn.style.display = discoveryScroller.scrollLeft > 10 ? 'flex' : 'none';
                    nextBtn.style.display = discoveryScroller.scrollLeft < maxScroll - 10 ? 'flex' : 'none';
                }
                
                prevBtn.addEventListener('click', () => discoveryScroller.scrollBy({ left: -300, behavior: 'smooth' }));
                nextBtn.addEventListener('click', () => discoveryScroller.scrollBy({ left: 300, behavior: 'smooth' }));
                
                discoveryScroller.addEventListener('scroll', checkScroll);
                window.addEventListener('resize', checkScroll);
                setTimeout(checkScroll, 500);
            }

            // --- 4. WISHLIST LOGIC ---
            document.body.addEventListener('click', function(e) {
                const btn = e.target.closest('.hero-wishlist-btn');
                if(!btn) return;

                e.preventDefault();
                const icon = btn.querySelector('i');
                const textSpan = btn.querySelector('.btn-text');
                const originalIconClass = icon.className;
                
                icon.className = 'fas fa-spinner fa-spin';
                btn.disabled = true;
                
                fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ action: 'add' })
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    if(data.success) {
                        if(data.is_in_wishlist) {
                            icon.className = 'fas fa-heart';
                            icon.style.color = '#ef4444';
                            if(textSpan) textSpan.innerText = 'Saved';
                        } else {
                            icon.className = 'far fa-heart';
                            icon.style.color = 'white';
                            if(textSpan) textSpan.innerText = 'Add to Bucket List';
                        }
                    } else {
                        icon.className = originalIconClass;
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    icon.className = originalIconClass;
                });
            });
        });
    </script>
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadMoreBtn = document.getElementById('loadMoreCountiesBtn');
            const gridContainer = document.getElementById('popularCountiesGrid');
            
            if (loadMoreBtn && gridContainer) {
                loadMoreBtn.addEventListener('click', function() {
                    // 1. UI Feedback
                    const originalText = loadMoreBtn.innerText;
                    loadMoreBtn.innerText = 'Loading...';
                    loadMoreBtn.disabled = true;
                    loadMoreBtn.style.opacity = '0.7';

                    // 2. Get next page number
                    const nextPage = loadMoreBtn.getAttribute('data-page');

                    // 3. Fetch Data
                    fetch(`{{ route('ajax.home-section') }}?section=popular-counties&page=${nextPage}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // 4. Append HTML
                        if (data.html) {
                            // Create a temporary container to parse HTML string to nodes
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = data.html;
                            
                            // Append each new node to the grid with a fade-in effect
                            Array.from(tempDiv.children).forEach(child => {
                                child.style.opacity = '0';
                                child.style.animation = 'fadeInUp 0.5s forwards';
                                gridContainer.appendChild(child);
                            });
                        }

                        // 5. Manage Button State
                        if (data.hasMore) {
                            loadMoreBtn.setAttribute('data-page', parseInt(nextPage) + 1);
                            loadMoreBtn.innerText = originalText;
                            loadMoreBtn.disabled = false;
                            loadMoreBtn.style.opacity = '1';
                        } else {
                            loadMoreBtn.style.display = 'none'; // Hide if no more
                        }
                    })
                    .catch(err => {
                        console.error('Error loading counties:', err);
                        loadMoreBtn.innerText = 'Try Again';
                        loadMoreBtn.disabled = false;
                    });
                });
            }
        });
    </script>
@endpush