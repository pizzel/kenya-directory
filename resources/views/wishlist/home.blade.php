@extends('layouts.site')

@section('title', config('app.name', 'Discover Kenya') . ' - Activities, Experiences, and Things To Do')

@section('meta_description', 'Find exciting activities, unique experiences, and the best things to do across Kenya. Your ultimate guide to adventure, culture, and local fun in Nairobi, Mombasa, and beyond.')

@section('meta_keywords', 'Kenya activities, things to do Kenya, Kenya experiences, discover Kenya, adventures Kenya, Nairobi, Mombasa, travel Kenya, Kenya tourism, local activities')

@section('content')
    <main>
        <div id="welcomePopup" class="welcome-popup" style="display: none; position: fixed; /* ... other styles */">
            <div class="popup-content">
                <button id="closeWelcomePopup" class="close-popup-btn">×</button>
                <h2>Welcome to Discover Kenya!</h2>
                <p>Your adventure starts here. Explore amazing activities and experiences.</p>
                {{-- You can add an image/icon here if desired --}}
            </div>
        </div>

        {{-- HERO SLIDER SECTION --}}
        @if(isset($heroSliderBusinesses) && $heroSliderBusinesses->isNotEmpty())
            <section class="hero-slider-section">
                <div class="swiper heroSwiper">
                    <div class="swiper-wrapper">
                        @foreach($heroSliderBusinesses as $business)
                            @php
                                $mainImage = $business->images->firstWhere('is_main_gallery_image', true);
                                $imageUrl = $mainImage ? $mainImage->url : asset('images/E31E1931-5E06-4A68-B1CC-39273C516E22.jpeg');
                            @endphp
                            <div class="swiper-slide" style="background-image: url('{{ $imageUrl }}');">
                                <div class="slide-overlay"></div>
                                <div class="slide-content container">
                                    <h1>{{ Str::limit($business->name, 50) }}</h1>
                                    @if($business->county)
                                        <p class="slide-location"><i class="fas fa-map-marker-alt"></i> {{ $business->county->name }}</p>
                                    @endif
                                    <p class="slide-tagline">{{ Str::limit($business->about_us, 120) }}</p>
                                    <a href="{{ route('listings.show', $business->slug) }}" class="btn btn-primary slide-cta-button">Discover More</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </section>
        @else
            {{-- Fallback static hero if no businesses for slider --}}
            <section class="hero" style="background-image: url('{{ asset('images/E31E1931-5E06-4A68-B1CC-39273C516E22.jpeg') }}');">
                <div class="container">
                    <h1>Find Your Destination</h1>
                    <p>We bring you not only a stay option, but an experience in your budget to enjoy the luxury.</p>
                </div>
            </section>
        @endif
        {{-- END HERO SLIDER SECTION --}}


        {{-- ====================================================================== --}}
        {{-- START: NEW DISCOVERY COLLECTIONS SECTION (REPLACES VISUAL ACTIVITIES) --}}
        {{-- ====================================================================== --}}
        <section class="discovery-collections-section">
            <div class="container">
                <h2>Explore Collections</h2>
                <div class="discovery-scroller-wrapper">
                    {{-- Note the new IDs: discoveryScrollPrev, discoveryScroller, discoveryScrollNext --}}
                    <button class="scroll-arrow prev" id="discoveryScrollPrev" aria-label="Previous Collections">❮</button>
                    <div class="discovery-scroller" id="discoveryScroller">
                    <div class="discovery-collections-grid">
                                @if(isset($discoveryCards) && $discoveryCards->isNotEmpty())
                                    @foreach($discoveryCards as $card)
                                        {{-- Only the component call should be here --}}
                                        <x-discovery-card :collection="$card" />
                                    @endforeach
                                @endif
                            </div>
                    </div>
                    <button class="scroll-arrow next" id="discoveryScrollNext" aria-label="Next Collections">❯</button>
                </div>
            </div>
        </section>
        {{-- ====================================================================== --}}
        {{-- END: NEW DISCOVERY COLLECTIONS SECTION --}}
        {{-- ====================================================================== --}}


        {{-- TOP ACTIVITIES LIST SECTION (UNCHANGED) --}}
        <section class="top-categories-list">
            <div class="container">
                <h2>Top Activities</h2>
                <div class="top-categories-scroller-wrapper">
                    <div class="detailed-category-grid" id="topCategoriesGrid"> {{-- This is the auto-scrolling div --}}
                        
							@if(isset($visualCategories) && $visualCategories->isNotEmpty())
                            @php
                                $columnsToDisplay = $visualCategories;
                                // Duplicate for seamless CSS scroll if few items. Adjust 4 based on typical number of columns visible.
                                if ($columnsToDisplay->count() > 0 && $columnsToDisplay->count() <= 4) {
                                    $columnsToDisplay = $columnsToDisplay->merge($columnsToDisplay);
                                }
                            @endphp
                            @foreach($columnsToDisplay as $parentActivity)
                                {{-- Each .detailed-category-column is now a link to the parent activity page --}}
                                <a href="{{ route('listings.category', ['categorySlug' => $parentActivity->slug]) }}" class="detailed-category-column-link">
                                    <div class="detailed-category-column">
                                        <h4>
                                            {{-- The h4 itself is no longer a link, the parent div is --}}
                                            @if($parentActivity->icon_class)
                                                <i class="{{ $parentActivity->icon_class }} fa-fw"></i>
                                            @else
                                                <i class="fas fa-tags fa-fw"></i> {{-- Default icon if none specified --}}
                                            @endif
                                            {{ $parentActivity->name }}
                                            @if(isset($parentActivity->businesses_count) && $parentActivity->businesses_count > 0)
                                                <span class="parent-activity-count">({{ $parentActivity->businesses_count }})</span>
                                            @endif
                                        </h4>
                                        @if($parentActivity->children->isNotEmpty())
                                            @foreach($parentActivity->children as $childActivity)
                                                <div class="detailed-category-item">
                                                    {{-- This link inside is fine, or could be removed if column link is enough --}}
                                                    <a href="{{ route('listings.category', ['categorySlug' => $childActivity->slug]) }}">
                                                        {{ $childActivity->name }}
                                                        @if(isset($childActivity->businesses_count) && $childActivity->businesses_count > 0)
                                                            <span>({{ $childActivity->businesses_count }})</span>
                                                        @endif
                                                    </a>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="detailed-category-item">No sub-activities.</div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        @else
                            <p class="text-center" style="width:100%; color: #6c757d;">Top activities loading soon...</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
		
        {{-- PLACES NEAR ME SECTION --}}
                @auth {{-- Show this section only to logged-in users --}}
                <section class="places-near-me-section">
                    <div class="container">
                        <h2>Places Near Me</h2>
                        
                        {{-- This is the initial prompt, hidden by default. JS will show it if needed. --}}
                        <div id="locationPermissionMessage" class="location-permission-notice" style="display: none;">
                            <p>To find places near you, please enable location services in your browser.</p>
                            <button id="enableLocationBtn" class="btn btn-primary">Enable Location & Find Nearby</button>
                        </div>

                        {{-- This section is shown once permission is granted --}}
                        <div id="nearbyControls" style="display: none;">
                            <div class="radius-slider-container form-group">
                                <label for="radiusSlider">Distance (km): <span id="radiusValue">25</span> km</label>
                                <input type="range" min="1" max="50" value="25" class="price-slider w-full" id="radiusSlider" name="radius">
                            </div>
                            
                            {{-- We group the buttons for easy toggling --}}
                            <div class="nearby-action-buttons">
                                <button id="findNearbyBtn" class="btn btn-primary mt-2">Update Search</button>
                                <button id="hideNearbyBtn" class="btn btn-secondary-outline mt-2" style="display: none;">Hide Places Near Me</button>
                            </div>
                        </div>

                        {{-- This button appears only after the user has hidden the results --}}
                        <div id="showNearbyContainer" style="display: none;" class="text-center">
                            <button id="showNearbyBtn" class="btn btn-primary">Show Places Near Me</button>
                        </div>

                        {{-- The results grid --}}
                        <div id="nearbyResultsContainer" style="display: none;">
                            <div id="nearbyLoadingSpinner" style="display:none; text-align:center; padding: 20px;">
                                <div class="spinner"></div>
                                <p>Searching for nearby places...</p>
                            </div>
                            <div id="nearbyPlacesResults" class="listings-grid mt-6">
                                {{-- Results will be loaded here --}}
                            </div>
                        </div>

                    </div>
                </section>
                @endauth
		
        {{-- POPULAR COUNTIES SECTION --}}
        @if(isset($popularCounties) && $popularCounties->isNotEmpty())
        <section class="popular-counties">
            <div class="container">
                <h2>Most Popular Counties</h2>
                <div class="destination-grid">
                    @foreach($popularCounties as $county)
                        <div class="destination-card">
                            <a href="{{ route('listings.county', ['countySlug' => $county->slug]) }}">
                                <div class="card-image-container">
                                    <img src="{{ $county->display_image_url }}" alt="{{ $county->name }}">
                                </div>
                                <div class="destination-info">{{ $county->name }} <span>({{ $county->businesses_count ?? 0 }} Listings)</span></div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @else
        <section class="popular-counties"><div class="container"><h2>Most Popular Counties</h2><p class="text-center col-span-full">Popular counties loading soon...</p></div></section>
        @endif

        {{-- MOST POPULAR PLACES SECTION --}}
        @if(isset($topDestinationBusinesses) && $topDestinationBusinesses->isNotEmpty())
        <section class="most-popular-places">
            <div class="container">
                <h2>Most Popular Places</h2>
                <div class="listings-grid">
                    @foreach($topDestinationBusinesses as $business)
                    <div class="listing-card">
							<a href="{{ route('listings.show', $business->slug) }}" class="listing-card-link-wrapper">
								@if ($business->is_featured)
									<span class="featured-banner">Featured</span>
								@endif
                            <div class="card-image-container">
                                <img src="{{ $business->images->firstWhere('is_main_gallery_image', true)?->url ?? asset('images/placeholder-card.jpg') }}" alt="{{ $business->name }}">
                            </div>
                            <div class="card-content-area">
                                <h3>{{ Str::limit($business->name, 35) }}</h3>
                                <p class="listing-location"><i class="fas fa-map-marker-alt"></i>{{ $business->county->name ?? '' }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $business->views_count ?? 0 }} views</p>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @else
        <section class="most-popular-places"><div class="container"><h2>Most Popular Places</h2><p class="text-center col-span-full">Popular places loading soon...</p></div></section>
        @endif

        {{-- RECENTLY ADDED PLACES SECTION --}}
        @if(isset($recentlyAddedPlaces) && $recentlyAddedPlaces->isNotEmpty())
        <section class="recently-added">
            <div class="container">
                <h2>Recently Added Places</h2>
                <div class="listings-grid">
                    @foreach($recentlyAddedPlaces as $business)
                    <div class="listing-card">
						<a href="{{ route('listings.show', $business->slug) }}" class="listing-card-link-wrapper">
							@if ($business->is_featured)
								<span class="featured-banner">Featured</span>
							@endif
                            <div class="card-image-container">
                                <img src="{{ $business->images->firstWhere('is_main_gallery_image', true)?->url ?? asset('images/placeholder-card.jpg') }}" alt="{{ $business->name }}">
                            </div>
                            <div class="card-content-area">
                                <h3>{{ Str::limit($business->name, 35) }}</h3>
                                <p class="listing-description-excerpt">{{ Str::limit($business->about_us, 60) }}</p>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @else
        <section class="recently-added"><div class="container"><h2>Recently Added Places</h2><p class="text-center col-span-full">Fresh listings coming soon!</p></div></section>
        @endif

        {{-- HIDDEN GEMS SECTION --}}
        @if(isset($hiddenGems) && $hiddenGems->isNotEmpty())
        <section class="hidden-gems">
            <div class="container">
                <h2>Discover Hidden Gems</h2>
                <p class="section-subtitle">Unearth unique spots and local favorites you won't find anywhere else.</p>
                <div class="listings-grid">
                    @foreach($hiddenGems as $gem)
                    <div class="listing-card gem-card-modifier">
						<a href="{{ route('listings.show', $gem->slug) }}" class="listing-card-link-wrapper">
							{{-- Note: We check $gem->is_featured here --}}
							@if ($gem->is_featured)
								<span class="featured-banner">Featured</span>
							@endif
                            <div class="card-image-container">
                                <img src="{{ $gem->images->firstWhere('is_main_gallery_image', true)?->url ?? asset('images/placeholder-card.jpg') }}" alt="{{ $gem->name }}">
                            </div>
                            <div class="card-content-area">
                                <h3>{{ Str::limit($gem->name, 35) }}</h3>
                                <p class="gem-location">{{ $gem->county->name ?? 'Unknown Location' }}</p>
                                <p class="gem-description">{{ Str::limit($gem->about_us, 80) }}</p>
                                <span class="gem-link">Learn More →</span>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @else
        <section class="hidden-gems"><div class="container"><h2>Discover Hidden Gems</h2><p class="text-center col-span-full">Searching for hidden gems... check back soon!</p></div></section>
        @endif
    </main>
@endsection

@push('footer-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Hero Swiper
            if (document.querySelector('.heroSwiper')) {
                const heroSwiper = new Swiper('.heroSwiper', {
                    loop: true,
                    autoplay: { delay: 7000, disableOnInteraction: false },
                    effect: 'fade',
                    fadeEffect: { crossFade: true },
                    pagination: { el: '.swiper-pagination', clickable: true },
                    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                    a11y: { prevSlideMessage: 'Previous slide', nextSlideMessage: 'Next slide' },
                });
            }

            // ======================================================================
            // JAVASCRIPT FOR THE NEW DISCOVERY COLLECTIONS SCROLLER
            // ======================================================================
            const discoveryScroller = document.getElementById('discoveryScroller');
            const discoveryScrollPrev = document.getElementById('discoveryScrollPrev');
            const discoveryScrollNext = document.getElementById('discoveryScrollNext');
            const discoveryWrapper = document.querySelector('.discovery-collections-section .discovery-scroller-wrapper');

            if (discoveryScroller && discoveryScrollPrev && discoveryScrollNext && discoveryWrapper) {
                const firstItem = discoveryScroller.querySelector('.discovery-card');
                
                function checkDiscoveryScrollability() {
                    if (!firstItem) { // No items to scroll
                        discoveryWrapper.classList.add('no-scroll');
                        discoveryScrollPrev.style.display = 'none';
                        discoveryScrollNext.style.display = 'none';
                        return;
                    }

                    const canScrollLeft = discoveryScroller.scrollLeft > 5;
                    const canScrollRight = discoveryScroller.scrollLeft < (discoveryScroller.scrollWidth - discoveryScroller.clientWidth - 5);

                    discoveryScrollPrev.style.display = canScrollLeft ? 'flex' : 'none';
                    discoveryScrollNext.style.display = canScrollRight ? 'flex' : 'none';

                    if (!canScrollLeft && !canScrollRight) {
                        discoveryWrapper.classList.add('no-scroll');
                    } else {
                        discoveryWrapper.classList.remove('no-scroll');
                    }
                }

                discoveryScrollPrev.addEventListener('click', () => {
                    const scrollAmount = discoveryScroller.querySelector('.discovery-card').offsetWidth * 2;
                    discoveryScroller.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                });
                
                discoveryScrollNext.addEventListener('click', () => {
                    const scrollAmount = discoveryScroller.querySelector('.discovery-card').offsetWidth * 2;
                    discoveryScroller.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                });

                discoveryScroller.addEventListener('scroll', checkDiscoveryScrollability);
                window.addEventListener('resize', checkDiscoveryScrollability);
                
                const observer = new MutationObserver(checkDiscoveryScrollability);
                observer.observe(discoveryScroller.querySelector('.discovery-collections-grid'), { childList: true });

                checkDiscoveryScrollability(); // Initial check
            }

            // Top Categories (Activities) Scroller Pause (Unchanged)
            const topCategoriesScrollerEl = document.getElementById('topCategoriesGrid');
            const topCategoriesScrollerWrapperEl = document.querySelector('.top-categories-list .top-categories-scroller-wrapper');
            if (topCategoriesScrollerEl && topCategoriesScrollerWrapperEl) {
                topCategoriesScrollerWrapperEl.addEventListener('mouseenter', () => { topCategoriesScrollerEl.style.animationPlayState = 'paused'; });
                topCategoriesScrollerWrapperEl.addEventListener('mouseleave', () => { topCategoriesScrollerEl.style.animationPlayState = 'running'; });
            }
        });
    </script>
@endpush