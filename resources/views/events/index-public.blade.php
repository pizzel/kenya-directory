@extends('layouts.site')

@section('title', 'Upcoming Events in Kenya - Concerts, Festivals & More | ' . config('app.name'))
@section('meta_description', 'Discover the best upcoming events, festivals, concerts, and workshops in Nairobi, Mombasa, and across Kenya. Get tickets and plan your weekend.')

@section('content')
<div class="listing-page-container container">

    {{-- SIDEBAR FILTERS --}}
    <aside class="filters-sidebar" id="filtersSidebar">
        <div class="sidebar-header-mobile">
            <h3>Filters</h3>
            <button id="closeFiltersButton" class="close-filters-button" aria-label="Close filters">×</button>
        </div>

        <form action="{{ route('events.index.public') }}" method="GET">
             
             {{-- Search Widget --}}
             <div class="filter-widget">
                <h4 class="widget-title">Find Events</h4>
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="keyword" placeholder="Concert, Workshop..." value="{{ request('keyword') }}" class="modern-search-input">
                </div>
            </div>

            {{-- Timeframe Filter --}}
            <div class="filter-widget">
                <h4 class="widget-title">Timeframe</h4>
                <div class="discovery-list">
                    <a href="{{ route('events.index.public', ['event_timeframe' => 'upcoming']) }}" 
                       class="discovery-link {{ request('event_timeframe', 'upcoming') == 'upcoming' ? 'active' : '' }}">
                        <i class="far fa-calendar-alt fa-fw"></i> <span>Upcoming</span>
                    </a>
                    <a href="{{ route('events.index.public', ['event_timeframe' => 'weekend']) }}" 
                       class="discovery-link {{ request('event_timeframe') == 'weekend' ? 'active' : '' }}">
                        <i class="fas fa-glass-cheers fa-fw"></i> <span>This Weekend</span>
                    </a>
                    <a href="{{ route('events.index.public', ['event_timeframe' => 'past']) }}" 
                       class="discovery-link {{ request('event_timeframe') == 'past' ? 'active' : '' }}">
                        <i class="fas fa-history fa-fw"></i> <span>Past Events</span>
                    </a>
                </div>
            </div>

            {{-- Counties Filter (Scrollable) --}}
            <div class="filter-widget">
                <h4 class="widget-title">Browse by County</h4>
                <div class="discovery-list scrollable-filter-list" style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                    @foreach($countiesForFilter as $county)
                        <a href="{{ route('events.by_county', $county->slug) }}" class="discovery-link">
                             <i class="fas fa-map-marker-alt fa-fw text-gray-300"></i> <span>{{ $county->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Clear Filters --}}
             @if(request()->hasAny(['keyword', 'event_timeframe']))
                <div class="filter-reset-wrapper">
                    <a href="{{ route('events.index.public') }}" class="btn-reset-filters">
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
                {{ $pageTitle ?? 'Upcoming Events' }}
                <span class="count-badge">{{ $events->total() }}</span>
            </h1>
            
            <div class="sort-options">
                <form action="{{ request()->url() }}" method="GET" class="sort-form">
                    {{-- Keep existing filters when sorting --}}
                    @foreach(request()->except(['sort', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
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
                <h3 class="empty-state-title">No events found</h3>
                <p class="empty-state-text">
                    We couldn't find any events matching your criteria right now. Try adjusting your search filters.
                </p>
                <div class="empty-state-action">
                    <a href="{{ route('events.index.public') }}" class="btn-primary-action">
                        Clear Filters & View All
                    </a>
                </div>
            </div>
        @endif
    </main>
</div>
@endsection