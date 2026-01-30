{{-- resources/views/events/by-county.blade.php --}}
@extends('layouts.site')

@section('title')
    {{ $pageTitle ?? 'Events in ' . $currentCounty->name }} - {{ config('app.name') }}
@endsection

@section('breadcrumbs')
    <div class="breadcrumbs-container">
        <a href="{{ route('home') }}" class="breadcrumb-link">Home</a>
        <span class="separator">/</span>
        <a href="{{ route('events.index.public') }}" class="breadcrumb-link">All Events</a>
        <span class="separator">/</span>
        <span class="breadcrumb-current">{{ $currentCounty->name }} Events</span>
    </div>
@endsection

@section('content')
<div class="listing-page-container container">

    {{-- SIDEBAR FILTERS --}}
    <aside class="filters-sidebar" id="filtersSidebar">
        <div class="sidebar-header-mobile">
            <h3>Filters</h3>
            <button id="closeFiltersButton" class="close-filters-button" aria-label="Close filters">×</button>
        </div>

        <form action="{{ route('events.by_county', $currentCounty->slug) }}" method="GET">
             
             {{-- Search Widget --}}
             <div class="filter-widget">
                <h4 class="widget-title">Search {{ $currentCounty->name }}</h4>
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="keyword" placeholder="Concert, Festival..." value="{{ request('keyword') }}" class="modern-search-input">
                </div>
            </div>

            {{-- Timeframe Filter --}}
            <div class="filter-widget">
                <h4 class="widget-title">Timeframe</h4>
                <div class="discovery-list">
                    <a href="{{ route('events.by_county', ['countySlug' => $currentCounty->slug, 'event_timeframe' => 'upcoming']) }}" 
                       class="discovery-link {{ request('event_timeframe', 'upcoming') == 'upcoming' ? 'active' : '' }}">
                        <i class="far fa-calendar-alt fa-fw"></i> <span>Upcoming</span>
                    </a>
                    <a href="{{ route('events.by_county', ['countySlug' => $currentCounty->slug, 'event_timeframe' => 'weekend']) }}" 
                       class="discovery-link {{ request('event_timeframe') == 'weekend' ? 'active' : '' }}">
                        <i class="fas fa-glass-cheers fa-fw"></i> <span>This Weekend</span>
                    </a>
                    <a href="{{ route('events.by_county', ['countySlug' => $currentCounty->slug, 'event_timeframe' => 'past']) }}" 
                       class="discovery-link {{ request('event_timeframe') == 'past' ? 'active' : '' }}">
                        <i class="fas fa-history fa-fw"></i> <span>Past Events</span>
                    </a>
                </div>
            </div>

            {{-- Event Type Filter --}}
            @if(isset($eventCategoriesForFilter) && $eventCategoriesForFilter->isNotEmpty())
            <div class="filter-widget">
                <h4 class="widget-title">Event Types</h4>
                <div class="discovery-list">
                    @foreach($eventCategoriesForFilter as $category)
                        @php
                            // Check if active (handle array or string)
                            $isActive = is_array(request('event_categories')) 
                                ? in_array($category->slug, request('event_categories')) 
                                : request('event_categories') == $category->slug;
                        @endphp
                        <a href="{{ route('events.by_county', ['countySlug' => $currentCounty->slug, 'event_categories' => [$category->slug]]) }}" 
                           class="discovery-link {{ $isActive ? 'active' : '' }}">
                             <i class="fas fa-tag fa-fw"></i> <span>{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Clear Filters --}}
             @if(request()->hasAny(['keyword', 'event_timeframe', 'event_categories']))
                <div class="filter-reset-wrapper">
                    <a href="{{ route('events.by_county', $currentCounty->slug) }}" class="btn-reset-filters">
                        <i class="fas fa-times-circle"></i> Clear All Filters
                    </a>
                </div>
            @endif
        </form>
    </aside>

    {{-- MAIN CONTENT --}}
    <main class="listings-main-content">
        
        {{-- Page Header --}}
        <div class="listings-header">
            <h1 class="page-main-heading">
                {{ $pageTitle ?? 'Events' }}
                <span class="count-badge">{{ $events->total() }}</span>
            </h1>
            
            <div class="sort-options">
                <form action="{{ request()->url() }}" method="GET" class="sort-form">
                    {{-- Keep existing filters when sorting --}}
                    @foreach(request()->except(['sort', 'page']) as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $v)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    
                    <label for="sort" class="sort-label">Sort by:</label>
                    <select name="sort" id="sort" class="form-select sort-select" onchange="this.form.submit()">
                        <option value="start_date_asc" {{ request('sort') == 'start_date_asc' ? 'selected' : '' }}>Date (Soonest)</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    </select>
                </form>
            </div>
        </div>

        {{-- Results --}}
        @if($events->isNotEmpty())
            <div class="listings-grid event-grid">
                @foreach($events as $event)
                    <x-event-card :event="$event" />
                @endforeach
            </div>
            
            <div class="pagination-container mt-8">
                {{ $events->onEachSide(1)->appends(request()->query())->links() }}
            </div>
        @else
            {{-- PREMIUM EMPTY STATE --}}
            <div class="empty-state-container">
                <div class="empty-state-icon">
                    <i class="far fa-calendar-times"></i>
                </div>
                <h3 class="empty-state-title">No events found in {{ $currentCounty->name }}</h3>
                <p class="empty-state-text">
                    We couldn't find any events matching your criteria. Try adjusting your filters or check back later.
                </p>
                <div class="empty-state-action">
                    <a href="{{ route('events.index.public') }}" class="btn-primary-action">
                        Browse All Events in Kenya
                    </a>
                </div>
            </div>
        @endif
    </main>
</div>
@endsection