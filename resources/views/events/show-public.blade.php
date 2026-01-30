@extends('layouts.site')

@section('title')
    {{ $event->title }} - Event in {{ $event->county->name ?? 'Kenya' }} | {{ config('app.name') }}
@endsection

@section('meta_description', Str::limit(strip_tags($event->description), 155) ?: "Details for {$event->title}, including date, location, and tickets.")

@php
    // Status Logic
    $statusClass = 'status-upcoming';
    $statusLabel = 'Upcoming';
    
    if ($event->display_status === 'cancelled') {
        $statusClass = 'status-cancelled';
        $statusLabel = 'Cancelled';
    } elseif ($event->display_status === 'past') {
        $statusClass = 'status-past';
        $statusLabel = 'Event Passed';
    }
@endphp

@section('content')

{{-- PAGE SPECIFIC CSS (To ensure premium look without breaking global site) --}}
<style>
    .event-container { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; font-family: 'Inter', sans-serif; color: #334155; }
    
    /* Breadcrumbs */
    .breadcrumbs { font-size: 0.9rem; color: #64748b; margin-bottom: 1.5rem; }
    .breadcrumbs a { text-decoration: none; color: #64748b; transition: color 0.2s; }
    .breadcrumbs a:hover { color: #2563eb; }
    .breadcrumbs span { margin: 0 8px; color: #cbd5e1; }

    /* Layout Grid */
    .event-grid { display: grid; grid-template-columns: 1fr 360px; gap: 3rem; align-items: start; }
    
    /* Left Column */
    .event-header { margin-bottom: 2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 2rem; }
    .event-badges { display: flex; gap: 10px; margin-bottom: 1rem; }
    .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .status-upcoming { background: #dcfce7; color: #166534; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .status-past { background: #f1f5f9; color: #64748b; }
    .badge-category { background: #eff6ff; color: #1e40af; }

    .event-title { font-size: 2.5rem; font-weight: 800; color: #0f172a; line-height: 1.2; margin-bottom: 1rem; }
    
    .event-meta { display: flex; flex-wrap: wrap; gap: 1.5rem; font-size: 1rem; color: #475569; }
    .meta-item { display: flex; align-items: center; gap: 8px; }
    .meta-item i { color: #2563eb; }

    .event-hero-image { width: 100%; height: 450px; object-fit: cover; border-radius: 16px; margin-bottom: 2.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    
    .content-section h3 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; margin-top: 2rem; }
    .content-text { line-height: 1.8; font-size: 1.05rem; }

    .organizer-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; margin-top: 3rem; }
    .organizer-info h4 { margin: 0; font-size: 1.1rem; color: #0f172a; font-weight: 700; }
    .organizer-info span { font-size: 0.85rem; color: #64748b; text-transform: uppercase; font-weight: 600; }
    .btn-outline { padding: 8px 16px; border: 1px solid #cbd5e1; background: white; border-radius: 6px; text-decoration: none; color: #334155; font-weight: 600; transition: all 0.2s; }
    .btn-outline:hover { border-color: #2563eb; color: #2563eb; }

    /* Right Sidebar (Sticky) */
    .sidebar-wrapper { position: sticky; top: 90px; }
    .sidebar-widget { background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    
    .price-box { text-align: center; margin-bottom: 1.5rem; }
    .price-label { font-size: 0.85rem; color: #64748b; text-transform: uppercase; font-weight: 700; }
    .price-amount { font-size: 2rem; font-weight: 800; color: #0f172a; }
    
    .btn-ticket { display: block; width: 100%; padding: 14px; background: #10b981; color: white; text-align: center; border-radius: 8px; font-weight: 700; text-decoration: none; transition: background 0.2s; font-size: 1.1rem; box-sizing: border-box; }
    .btn-ticket:hover { background: #059669; }
    .btn-ticket.disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; }

    .calendar-links a { display: flex; align-items: center; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 8px; text-decoration: none; color: #475569; font-weight: 500; font-size: 0.9rem; transition: background 0.2s; }
    .calendar-links a:hover { background: #f8fafc; color: #0f172a; }
    .calendar-links i { margin-right: 10px; width: 16px; }

    /* Mobile */
    @media (max-width: 900px) {
        .event-grid { grid-template-columns: 1fr; gap: 2rem; }
        .event-hero-image { height: 250px; }
        .sidebar-wrapper { position: static; }
        .event-title { font-size: 1.8rem; }
    }
</style>

<div class="event-container">
    
    {{-- BREADCRUMBS --}}
    <div class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a> <span>/</span>
        <a href="{{ route('events.index.public') }}">Events</a> <span>/</span>
        <span style="color: #0f172a;">{{ Str::limit($event->title, 40) }}</span>
    </div>

    <div class="event-grid">
        
        {{-- LEFT COLUMN: Main Content --}}
        <div class="event-main">
            
            {{-- Header --}}
            <div class="event-header">
                <div class="event-badges">
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    @foreach($event->categories as $cat)
                        <span class="badge badge-category">{{ $cat->name }}</span>
                    @endforeach
                </div>
                
                <h1 class="event-title">{{ $event->title }}</h1>
                
                <div class="event-meta">
                    <div class="meta-item">
                        <i class="far fa-calendar-alt"></i>
                        {{ $event->start_datetime->format('D, M j, Y') }}
                    </div>
                    <div class="meta-item">
                        <i class="far fa-clock"></i>
                        {{ $event->start_datetime->format('g:i A') }} - {{ $event->end_datetime->format('g:i A') }}
                    </div>
                    @if($event->county)
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $event->county->name }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Main Image --}}
            @if($event->images->isNotEmpty())
                <img src="{{ $event->images->first()->url }}" alt="{{ $event->title }}" class="event-hero-image">
            @else
                <img src="{{ asset('images/placeholder-event-large.jpg') }}" alt="Event Placeholder" class="event-hero-image">
            @endif

            {{-- Description --}}
            <div class="content-section">
                <h3>About This Event</h3>
                <div class="content-text">
                    {!! nl2br(e($event->description)) !!}
                </div>
            </div>

            {{-- Organizer Card --}}
            @if($event->business)
                <div class="organizer-card">
                    <div class="organizer-info">
                        <span>Event Organizer</span>
                        <h4>{{ $event->business->name }}</h4>
                    </div>
                    <a href="{{ route('listings.show', $event->business->slug) }}" class="btn-outline">
                        View Host Profile
                    </a>
                </div>
            @endif

            {{-- Reviews / Discussion (Simplified) --}}
            <div class="content-section" style="border-top: 1px solid #e2e8f0; padding-top: 2rem;">
                <h3>Discussion</h3>
                @auth
                    <form action="{{ route('events.reviews.store', $event->slug) }}" method="POST" style="margin-bottom: 2rem;">
                        @csrf
                        <textarea name="comment" rows="3" class="form-control" placeholder="Ask a question or leave a comment..." style="width: 100%; padding: 15px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 10px;"></textarea>
                        <button type="submit" class="btn-outline" style="background: #0f172a; color: white; border: none;">Post Comment</button>
                    </form>
                @else
                    <p style="background: #f8fafc; padding: 15px; border-radius: 8px;">
                        <a href="{{ route('login') }}" style="color: #2563eb; font-weight: 700;">Log in</a> to ask questions or leave feedback.
                    </p>
                @endauth

                {{-- Comments Loop --}}
                <div class="comments-list">
                    @forelse($event->reviews->sortByDesc('created_at') as $review)
                        <div style="margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                            <div style="font-weight: 700; font-size: 0.9rem;">{{ $review->user->name }} <span style="font-weight: 400; color: #94a3b8; font-size: 0.8rem;">• {{ $review->created_at->diffForHumans() }}</span></div>
                            <div style="color: #475569; margin-top: 5px;">{{ $review->comment }}</div>
                        </div>
                    @empty
                        <p style="color: #94a3b8; font-style: italic;">No comments yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN: Sticky Sidebar --}}
        <div class="sidebar-wrapper">
            
            {{-- Ticket / Price Widget --}}
            @if($event->display_status === 'active' && $event->start_datetime->isFuture())
                <div class="sidebar-widget">
                    <div class="price-box">
                        <div class="price-label">Ticket Price</div>
                        <div class="price-amount">
                            {{ $event->is_free ? 'Free Entry' : ($event->price ? 'Ksh ' . number_format($event->price) : 'TBA') }}
                        </div>
                    </div>

                    @if($event->ticketing_url)
                        <a href="{{ $event->ticketing_url }}" target="_blank" class="btn-ticket">
                            Get Tickets <i class="fas fa-external-link-alt" style="margin-left: 8px; font-size: 0.9em;"></i>
                        </a>
                        <p style="font-size: 0.75rem; text-align: center; color: #64748b; margin-top: 10px;">
                            You will be redirected to the ticketing site.
                        </p>
                    @else
                        <button class="btn-ticket disabled">Tickets Unavailable</button>
                    @endif
                </div>
            @endif

            {{-- Location Map Widget --}}
            @if($event->latitude && $event->longitude)
                <div class="sidebar-widget" style="padding: 0; overflow: hidden;">
                    <div style="height: 200px; width: 100%;">
                        <iframe src="https://maps.google.com/maps?q={{ $event->latitude }},{{ $event->longitude }}&hl=es&z=14&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                    <div style="padding: 15px;">
                        <div style="font-weight: 600; margin-bottom: 5px; color: #0f172a;">{{ Str::limit($event->address, 30) }}</div>
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $event->latitude }},{{ $event->longitude }}" target="_blank" class="btn-outline" style="display: block; text-align: center; width: 100%; box-sizing: border-box;">
                            <i class="fas fa-location-arrow"></i> Get Directions
                        </a>
                    </div>
                </div>
            @endif

            {{-- Calendar Widget --}}
            <div class="sidebar-widget">
                <h4 style="font-size: 0.9rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; text-transform: uppercase;">Add to Calendar</h4>
                <div class="calendar-links">
                    @php
                        $link = Spatie\CalendarLinks\Link::create($event->title, $event->start_datetime, $event->end_datetime)
                            ->description(Str::limit(strip_tags($event->description), 100));
                        if($event->address) $link->address($event->address);
                    @endphp
                    <a href="{{ $link->google() }}" target="_blank">
                        <i class="fab fa-google" style="color: #ea4335;"></i> Google Calendar
                    </a>
                    <a href="{{ route('events.ics', $event->slug) }}">
                        <i class="fab fa-apple" style="color: #0f172a;"></i> Outlook / Apple (ICS)
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection