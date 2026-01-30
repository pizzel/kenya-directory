@extends('layouts.site')

@section('title')
    Listings tagged with "{{ $currentTag->name }}" - Discover Kenya
@endsection

{{-- 1. SEO: Canonical Tag --}}
@section('canonical')
    <link rel="canonical" href="{{ route('listings.tag', $currentTag->slug) }}" />
@endsection

{{-- 2. SEO: Dynamic Description --}}
@section('meta_description')
    Explore Kenya's best businesses and activities tagged with "{{ $currentTag->name }}". Find top-rated locations, compare prices, and discover hidden gems featuring {{ $currentTag->name }}.
@endsection

{{-- 3. SEO: Dynamic Keywords --}}
@section('meta_keywords')
    {{ $currentTag->name }}, {{ $currentTag->name }} Kenya, businesses with {{ $currentTag->name }}, {{ $currentTag->name }} activities
@endsection

@section('breadcrumbs')
    <a href="{{ route('home') }}">Home</a> /
    <a href="{{ route('listings.index') }}">Listings</a> /
    <span>Tag: {{ $currentTag->name }}</span>
@endsection

@section('content')
    <div class="listing-page-container container">
        {{-- FILTER TOGGLE BUTTON --}}
        <button id="filterToggleButton" class="filter-toggle-button">
            <span class="filter-toggle-text">Filters</span>
        </button>

        <aside class="filters-sidebar" id="filtersSidebar">
            <button id="closeFiltersButton" class="close-filters-button" aria-label="Close filters">×</button>

            {{-- Helper Logic --}}
            @php
                $countyParam = (isset($currentCounty) && $currentCounty->slug && $currentCounty->slug !== 'search') 
                    ? ['county_filter_slug' => $currentCounty->slug] 
                    : [];
            @endphp

            {{-- 1. TOP ACTIVITIES --}}
            @if(isset($categoriesForFilter) && $categoriesForFilter->isNotEmpty())
            <div class="filter-widget">
                <h4>Top Activities</h4>
                <div class="discovery-list">
                    @foreach($categoriesForFilter as $category)
                        <a href="{{ route('listings.category', array_merge(['categorySlug' => $category->slug], $countyParam)) }}" 
                           class="discovery-link group">
                            <span class="discovery-name text-gray-700 group-hover:text-indigo-600">
                                <i class="{{ $category->icon_class ?? 'fas fa-angle-right' }} fa-fw text-gray-400 group-hover:text-indigo-500 mr-2"></i>
                                {{ $category->name }}
                            </span>
                            <span class="discovery-count bg-gray-100 text-gray-500 group-hover:bg-indigo-50 group-hover:text-indigo-600">{{ $category->businesses_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 2. FILTER BY RATING --}}
            <div class="filter-widget">
                <h4>Filter by Rating</h4>
                <div class="discovery-list">
                    @for ($i = 5; $i >= 1; $i--)
                        <a href="{{ request()->fullUrlWithQuery(['rating' => $i]) }}" 
                           class="discovery-link group {{ request('rating') == $i ? 'active-filter' : '' }}">
                            <span class="discovery-name flex items-center">
                                <span style="margin-right: 8px; font-size: 0.9rem;">
                                    @for($s=1; $s<=5; $s++) 
                                        @if($s <= $i)
                                            {{-- FILLED STAR: Green --}}
                                            <i class="fas fa-star" style="color: #10b981;"></i>
                                        @else
                                            {{-- EMPTY STAR: Light Gray --}}
                                            <i class="far fa-star" style="color: #d1d5db;"></i>
                                        @endif
                                    @endfor
                                </span>
                                <span class="text-xs text-gray-600">& Up</span>
                            </span>
                        </a>
                    @endfor
                </div>
            </div>

            {{-- 3. VIBE & STYLE --}}
            @if(isset($tagsForFilter) && $tagsForFilter->isNotEmpty())
            <div class="filter-widget">
                <h4>Vibe & Style</h4>
                <div class="discovery-list">
                    @foreach($tagsForFilter as $tag)
                        @php
                            $tagIcons = [
                                // Existing icons...
                                'scenic-view'        => 'fas fa-camera-retro',
                                'pet-friendly'       => 'fas fa-paw',
                                'romantic'           => 'fas fa-heart',
                                'family-friendly'    => 'fas fa-child',
                                'serene-environment' => 'fas fa-leaf',
                                'good-for-groups'    => 'fas fa-users',
                                'breakfast'          => 'fas fa-coffee',
                                'dinner'             => 'fas fa-utensils',
                                'trendy-vibe'        => 'fas fa-fire-alt',
                                'pocket-friendly'    => 'fas fa-wallet',
                                'hidden-gem'         => 'far fa-gem',
                                'luxury'             => 'fas fa-crown',
                                'work-friendly'      => 'fas fa-laptop',
                                'nyama-choma'        => 'fas fa-drumstick-bite',
                                'halal-options'      => 'fas fa-check-circle',
                                'vegetarian-options' => 'fas fa-carrot',
                                'rustic'             => 'fas fa-tree',
                                'alcohol-free'       => 'fas fa-ban',
                                '4x4-required'       => 'fas fa-truck-monster',
                                'outdoor-seating'    => 'fas fa-chair',
                                'happy-hour'         => 'fas fa-glass-cheers',
                                'live-music'         => 'fas fa-music',
                                '24-hours'           => 'fas fa-clock',
                                'vegan'              => 'fas fa-seedling',
                                'fast-food'          => 'fas fa-hamburger',
                                'african-cuisine'    => 'fas fa-utensil-spoon',
                            ];
                            $currentIcon = $tagIcons[$tag->slug] ?? 'fas fa-hashtag';
                        $isActive = (is_array(request('tags')) && in_array($tag->slug, request('tags'))) || 
                                        request('tag') == $tag->slug;
                        @endphp

                        <a href="{{ route('listings.tag', array_merge(['tag' => $tag->slug], $countyParam)) }}" 
                           class="discovery-link group {{ $isActive ? 'bg-blue-50 border-l-2 border-blue-500' : '' }}">
                            
                            <span class="discovery-name {{ $isActive ? 'text-blue-700 font-bold' : 'text-gray-700' }} group-hover:text-indigo-600">
                                <i class="{{ $currentIcon }} fa-fw {{ $isActive ? 'text-blue-500' : 'text-gray-400' }} group-hover:text-indigo-500 mr-2"></i>
                                {{ $tag->name }}
                            </span>
                            
                            {{-- Hide count if active, or style it differently --}}
                            <span class="discovery-count {{ $isActive ? 'bg-blue-200 text-blue-800' : 'bg-gray-100 text-gray-500' }}">
                                {{ $tag->businesses_count }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 4. AMENITIES --}}
            @if(isset($facilitiesForFilter) && $facilitiesForFilter->isNotEmpty())
            <div class="filter-widget">
                <h4>Amenities</h4>
                <div class="discovery-list">
                    @foreach($facilitiesForFilter as $facility)
                        <a href="{{ route('listings.facility', array_merge(['facility' => $facility->slug], $countyParam)) }}" 
                           class="discovery-link group">
                            <span class="discovery-name text-gray-700 group-hover:text-indigo-600">
                                <i class="{{ $facility->icon_class ?? 'fas fa-check' }} fa-fw text-gray-400 group-hover:text-indigo-500 mr-2"></i>
                                {{ $facility->name }}
                            </span>
                            <span class="discovery-count bg-gray-100 text-gray-500 group-hover:bg-indigo-50 group-hover:text-indigo-600">{{ $facility->businesses_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Reset Button --}}
            @if(request()->hasAny(['keyword', 'rating', 'price_max']))
                <div class="mt-6 text-center">
                    <a href="{{ request()->url() }}" class="text-sm text-red-500 hover:text-red-700 hover:underline">
                        <i class="fas fa-times-circle"></i> Clear All Filters
                    </a>
                </div>
            @endif
        </aside>

        <main class="listings-main-content">
            <div class="listings-header">
                <h2 class="text-lg font-medium text-gray-900">
                     Showing {{ $businesses->total() }} {{ Str::plural('place', $businesses->total()) }} tagged with "{{ $currentTag->name }}"
                </h2>
                <div class="sort-options">
                    <form action="{{ request()->url() }}" method="GET" class="flex items-center">
                        {{-- 1. Preserve ALL existing filters (Keyword, Price, Rating, etc.) --}}
                        @foreach(request()->except(['sort', 'page']) as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach

                        <label for="sort-select" class="text-sm text-gray-500 mr-2 font-medium">Sort by:</label>
                        
                        {{-- 2. New Options --}}
                        <select id="sort-select" name="sort" class="form-select" onchange="this.form.submit()" 
                                style="border-radius: 8px; border-color: #e2e8f0; font-size: 0.85rem; padding-right: 30px; cursor: pointer;">
                            <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Recommended</option>
                            <option value="rating_desc" {{ request('sort') == 'rating_desc' ? 'selected' : '' }}>Highest Rated</option>
                            <option value="views_desc" {{ request('sort') == 'views_desc' ? 'selected' : '' }}>Most Popular</option>
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest Added</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        </select>
                    </form>
                </div>
            </div>

            @if($businesses->isNotEmpty())
				<div class="listings-grid">
					@foreach($businesses as $business)
					    <x-business-card :business="$business" />			
                    @endforeach
                </div>
                {{-- FIX: Changed $request to request() --}}
                <div class="mt-8 pagination-container">{{ $businesses->appends(request()->query())->links() }}</div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No listings found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        There are currently no active business listings tagged with "{{ $currentTag->name }}" matching your criteria.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('listings.tag', $currentTag->slug) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Clear Filters & Refresh
                        </a>
                    </div>
                </div>
            @endif
        </main>
    </div>
@endsection

@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Price range slider specific to this page
        const priceSliderTag = document.getElementById('price_slider_input_tag');
        const priceValueDisplayTag = document.getElementById('priceValueDisplayTag');
        if (priceSliderTag && priceValueDisplayTag) {
            function updateTagPriceDisplay() { priceValueDisplayTag.textContent = "Ksh " + Number(priceSliderTag.value).toLocaleString(); }
            updateTagPriceDisplay();
            priceSliderTag.addEventListener('input', updateTagPriceDisplay);
        }

        // Sort select dropdown specific to this page
        const sortSelectTag = document.getElementById('sort-by-select-tag');
        const sortInputHiddenTag = document.getElementById('sort_input_tag_filter'); // Reusing filter ID for hidden input
        const filterFormTag = document.getElementById('filterSortForm');
        if (sortSelectTag && sortInputHiddenTag && filterFormTag) {
            sortSelectTag.addEventListener('change', function() {
                sortInputHiddenTag.value = this.value;
                filterFormTag.submit();
            });
        }
        
        // Off-canvas filter toggle
        const filterToggleButton = document.getElementById('filterToggleButton');
        const filtersSidebar = document.getElementById('filtersSidebar');
        const closeFiltersButton = document.getElementById('closeFiltersButton');
        const siteBodyForFilter = document.body;

        if (filterToggleButton && filtersSidebar && closeFiltersButton) {
            filterToggleButton.addEventListener('click', function() {
                filtersSidebar.classList.add('is-open');
                siteBodyForFilter.classList.add('filters-sidebar-open');
            });

            closeFiltersButton.addEventListener('click', function() {
                filtersSidebar.classList.remove('is-open');
                siteBodyForFilter.classList.remove('filters-sidebar-open');
            });

            document.addEventListener('click', function(event) {
                 if (siteBodyForFilter.classList.contains('filters-sidebar-open') &&
                     event.target !== filtersSidebar && !filtersSidebar.contains(event.target) &&
                     event.target !== filterToggleButton && !filterToggleButton.contains(event.target) ) {
                    filtersSidebar.classList.remove('is-open');
                    siteBodyForFilter.classList.remove('filters-sidebar-open');
                }
            });
        }
    });
</script>
@endpush