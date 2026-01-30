

@extends('layouts.site')

@section('title', $itinerary->title . ' - Journey Itinerary')

@push('seo')
<meta name="description" content="Explore the journey: {{ $itinerary->title }}. Duration: {{ $itinerary->duration_string }}. Join this epic adventure across Kenya.">
<meta property="og:title" content="{{ $itinerary->title }} - Journey Itinerary">
<meta property="og:description" content="Explore the journey: {{ $itinerary->title }}. Join this epic adventure across Kenya.">
@endpush

@push('footer-scripts')
<link rel="stylesheet" href="{{ asset('css/itineraries.css') }}?v={{ time() }}">
@endpush

@section('styles')
<style>
    :root {
        --primary-itinerary: {{ $itinerary->theme_color ?? '#3b82f6' }};
    }
    /* Added safety for the explorer view */
    .explorer-scene { transition: transform 0.1s ease-out; will-change: transform; }
    .stop-item { position: relative; }
</style>
@endsection

@section('content')
<div class="itinerary-page">
    {{-- HERO SECTION --}}
    <header id="itinerary-hero" class="itinerary-hero">
        <div class="container hero-container">
            <div class="hero-main-content">
                <div class="hero-badge">JOURNEY ITINERARY</div>
                <h1 class="itinerary-title">{{ $itinerary->title }}</h1>
                <div class="itinerary-meta">
                    <span class="meta-item"><i class="fas fa-user-circle"></i> {{ $itinerary->creator->name }}</span>
                    <span class="meta-divider"></span>
                    <span class="meta-item"><i class="fas fa-calendar-alt"></i> Flexible</span>
                    <span class="meta-divider"></span>
                    <span class="meta-item"><i class="fas fa-users"></i> <span id="participant-count">{{ $itinerary->participants_count }}</span> Joined</span>
                </div>
                @if($itinerary->description && strtolower(trim($itinerary->description)) !== strtolower(trim($itinerary->title)))
                    <p class="hero-description">{{ $itinerary->description }}</p>
                @endif
            </div>
            
            <div class="hero-actions-content">
                <button id="join-btn" class="action-btn btn-join {{ $isParticipating ? 'joined' : '' }}" 
                        onclick="toggleInteraction('join')"
                        aria-label="{{ $isParticipating ? 'Leave this journey' : 'Join this journey' }}">
                    <i class="fas {{ $isParticipating ? 'fa-check-circle' : 'fa-plus-circle' }}" aria-hidden="true"></i>
                    <span class="btn-text">{{ $isParticipating ? 'Joined' : 'Join this Journey' }}</span>
                </button>
                <div class="share-suite">
                    <a href="https://wa.me/?text={{ urlencode('Check out this journey: ' . $itinerary->title . ' on Discover Kenya: ' . route('itineraries.show', $itinerary->slug)) }}" 
                       target="_blank" class="share-icon-btn btn-whatsapp" title="Share on WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>

                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('itineraries.show', $itinerary->slug)) }}" 
                       target="_blank" class="share-icon-btn btn-facebook" title="Share on Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>

                    <a href="https://twitter.com/intent/tweet?text={{ urlencode('Check out this journey: ' . $itinerary->title) }}&url={{ urlencode(route('itineraries.show', $itinerary->slug)) }}" 
                       target="_blank" class="share-icon-btn btn-x" title="Post to X (Twitter)">
                        <i class="fab fa-x-twitter"></i>
                    </a>

                    <button id="nativeShareBtn" onclick="nativeShare()" class="share-icon-btn btn-native" title="Share via...">
                        <i class="fas fa-share-alt"></i>
                    </button>

                    <button onclick="copyToClipboard('{{ route('itineraries.show', $itinerary->slug) }}', this)" class="share-icon-btn btn-copy" title="Copy Link">
                        <i class="fas fa-link"></i>
                    </button>
                </div>

                @if(auth()->id() === $itinerary->user_id)
                    <div class="meta-divider d-none d-md-block" style="height: 30px; width: 1px; border-radius: 0; background: #e2e8f0; margin: 0 5px;"></div>
                    
                    <a href="{{ route('itineraries.edit', $itinerary->id) }}" class="action-btn btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    
                    <button type="button" onclick="document.getElementById('deleteConfirmModal').style.display='flex'" class="action-btn btn-delete">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                @endif
            </div>
        </div>
    </header>

    {{-- VIEW TOGGLE --}}
    <div id="view-toggle-container" class="view-toggle-container">
        <div class="view-toggle" role="tablist">
            <button onclick="switchView('roadmap')" id="btn-roadmap" class="toggle-btn active" role="tab" aria-selected="true" aria-controls="roadmap-view">
                <i class="fas fa-list"></i> Roadmap
            </button>
            <button onclick="switchView('explorer')" id="btn-explorer" class="toggle-btn" role="tab" aria-selected="false" aria-controls="explorer-view">
                <i class="fas fa-cube"></i> 3D Explorer
            </button>
        </div>
    </div>

    {{-- 3D EXPLORER VIEW --}}
    <div id="explorer-view" class="explorer-container" style="display: none;">
        <div class="season-controls">
            <button onclick="changeSeason('summer')" id="btn-summer" class="season-btn" title="Dry Season">☀️</button>
            <button onclick="changeSeason('winter')" id="btn-winter" class="season-btn" title="Snowy Peak">❄️</button>
            <button onclick="changeSeason('autumn')" id="btn-autumn" class="season-btn" title="Harvest">🍂</button>
            <button onclick="changeSeason('rainy')" id="btn-rainy" class="season-btn" title="Monsoon">🌧️</button>
            <div class="season-indicator" id="season-name">Dry Season</div>
        </div>

        <div class="explorer-scene">
            <div id="star-field" class="star-field"></div>
            <div id="sun" class="celestial-body sun"></div>
            <div id="moon" class="celestial-body moon">
                <div class="moon-crater"></div>
            </div>

            <div class="explorer-path-svg">
                <svg width="100%" height="100%" id="journey-svg-canvas" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="path-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" style="stop-color:var(--primary-itinerary);stop-opacity:0" />
                            <stop offset="10%" style="stop-color:var(--primary-itinerary);stop-opacity:1" />
                            <stop offset="90%" style="stop-color:var(--primary-itinerary);stop-opacity:1" />
                            <stop offset="100%" style="stop-color:var(--primary-itinerary);stop-opacity:0" />
                        </linearGradient>
                        <filter id="car-glow">
                            <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                            <feMerge>
                                <feMergeNode in="coloredBlur"/><feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>

                    <path id="dynamic-journey-path" d="" fill="none" stroke="url(#path-gradient)" stroke-width="4" />
                    <path id="dynamic-journey-pulse" d="" fill="none" stroke="#fff" stroke-width="2" stroke-dasharray="20, 150" />

                    <g id="waypoint-icons"></g>
                    <g id="path-trees"></g>
                    <g id="path-wildlife"></g>

                    <g id="safari-vehicle" filter="url(#car-glow)" style="visibility: hidden;">
                        <path d="M-15,5 L15,5 L12,-2 L-10,-2 Z" fill="#fff" />
                        <rect x="-8" y="-6" width="12" height="5" fill="#fff" rx="1" />
                        <circle cx="-10" cy="6" r="3" fill="#3b82f6" />
                        <circle cx="10" cy="6" r="3" fill="#3b82f6" />
                    </g>
                </svg>
            </div>

            <div id="parallax-particles" class="parallax-particles"></div>
            
            <div class="explorer-stops">
                @php $totalStops = $itinerary->stops->count(); @endphp
                @if($totalStops > 0)
                    @foreach($itinerary->stops as $index => $stop)
                        <div class="explorer-stop {{ $index === 0 ? 'is-start' : '' }} {{ $index === $totalStops - 1 ? 'is-finish' : '' }}" style="--index: {{ $index }};">
                            <span class="stop-date-3d top-date">{{ $stop->start_time->format('M d') }}</span>
                            
                            @if($stop->business_id)
                                <a href="{{ route('listings.show', $stop->business->slug) }}" class="stop-marker-3d" target="_blank" rel="noopener">
                            @else
                                <div class="stop-marker-3d">
                            @endif
                                @if($index === 0)
                                    <div class="stop-badge-icon start-badge"><i class="fas fa-flag-checkered"></i> START</div>
                                @elseif($index === $totalStops - 1)
                                    <div class="stop-badge-icon finish-badge"><i class="fas fa-trophy"></i> FINISH</div>
                                @endif

                                <img src="{{ $stop->display_image }}" alt="">
                                <div class="stop-info-3d">
                                    <h4 class="stop-title-3d">{{ $stop->title }}</h4>
                                </div>
                            @if($stop->business_id)
                                </a>
                            @else
                                </div>
                            @endif

                            <div class="stop-pole-3d"></div>
                            <div class="stop-pole-anchor"></div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- TIMELINE SECTION --}}
    @if(auth()->id() === $itinerary->user_id)
        <div class="roadmap-management-bar">
            <div class="roadmap-add-btn-container">
                <button onclick="document.getElementById('addStopModal').style.display='flex'" 
                        class="bg-white border-2 border-dashed border-slate-300 text-slate-500 px-10 py-4 rounded-3xl hover:border-blue-500 hover:text-blue-500 transition-all font-bold">
                    <i class="fas fa-plus-circle mr-2"></i> Add New Stop to Roadmap
                </button>
            </div>
        </div>
    @endif

    <div id="roadmap-view" class="timeline-container">
        <div class="timeline-line"></div>
        
        @if($itinerary->stops->count() > 0)
            @foreach($itinerary->stops as $stop)
                @php
                    $status = '';
                    if($stop->is_completed) $status = 'stop-completed';
                    elseif($stop->is_happening_now) $status = 'stop-active';
                @endphp

                <div class="stop-item {{ $status }}">
                    <div class="stop-bullet"></div>
                    <div class="stop-card">
                        @if($stop->is_happening_now)
                            <span class="live-badge">LIVE NOW</span>
                        @endif
                        
                        <div class="stop-header-meta">
                            <div class="stop-date-badge">
                                <i class="far fa-calendar-alt"></i>
                                {{ $stop->start_time->format('M d') }} 
                                @if($stop->end_time && !$stop->end_time->isSameDay($stop->start_time))
                                    — {{ $stop->end_time->format('M d') }}
                                @endif
                            </div>
                        </div>
                        
                        <h3 class="stop-title">{{ $stop->title }}</h3>

                        @if($stop->description && strtolower(trim($stop->description)) !== strtolower(trim($stop->title)))
                            <p class="stop-description">{{ $stop->description }}</p>
                        @endif

                        @if($stop->location_name)
                            <div class="stop-location-row">
                                <div class="location-label">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $stop->location_name }}</span>
                                </div>
                                @if($stop->business_id)
                                    <a href="{{ route('listings.show', $stop->business->slug) }}" class="view-details-link">
                                        View Details <i class="fas fa-chevron-right"></i>
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if($stop->business_id)
                            <a href="{{ route('listings.show', $stop->business->slug) }}" class="stop-image-link">
                                <img src="{{ $stop->display_image }}" alt="{{ $stop->title }}" class="stop-image">
                                <div class="image-overlay-hint"><i class="fas fa-external-link-alt"></i> View Place</div>
                            </a>
                        @else
                            <img src="{{ $stop->display_image }}" alt="{{ $stop->title }}" class="stop-image">
                        @endif

                        @if($stop->business && $stop->business->tags->count() > 0)
                            <div class="stop-amenities" style="margin-top: 18px; padding-top: 15px; border-top: 1px dashed #e2e8f0; position: relative; z-index: 5;">
                                <span class="activities-label" style="margin-bottom: 8px;">Amenities</span>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    @foreach($stop->business->tags->take(4) as $tag)
                                        <a href="{{ route('listings.tag', ['tag' => $tag->slug]) }}" class="activity-tag">#{{ $tag->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(auth()->id() === $itinerary->user_id)
                            <div class="stop-actions-overlay">
                                <button type="button" onclick="openEditStopModal({{ json_encode($stop->only(['id', 'title', 'description', 'location_name', 'business_id'])) }}, '{{ $stop->start_time->format('Y-m-d') }}', '{{ $stop->end_time ? $stop->end_time->format('Y-m-d') : '' }}', {{ $stop->business ? json_encode(['id' => $stop->business->id, 'name' => $stop->business->name]) : 'null' }})" class="stop-action-btn stop-edit-btn" title="Edit Stop">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button type="button" onclick="confirmDeleteStop({{ $stop->id }}, '{{ addslashes($stop->title) }}')" class="stop-action-btn stop-delete-btn" title="Delete Stop">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- INDEPENDENT ACTIVITY HUB: Showcases Business Categories --}}
                    @if($stop->business && $stop->business->categories->count() > 0)
                        <div class="stop-activities">
                            <div class="activities-line"></div>
                            <div class="activities-wrapper">
                                <span class="activities-label">
                                    <i class="far fa-calendar-alt" style="margin-right: 5px;"></i> {{ $stop->start_time->format('M d') }} - Top Activities
                                </span>
                                <div class="activities-tags">
                                    @foreach($stop->business->categories->take(4) as $category)
                                        <a href="{{ route('listings.category', $category->slug) }}" class="activity-tag" style="text-decoration: none;">
                                            <i class="{{ $category->icon_class ?? 'fas fa-tag' }}" style="margin-right: 4px;"></i>
                                            {{ $category->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="text-center py-20 relative z-10">
                <p class="text-slate-400 italic">No stops added yet.</p>
            </div>
        @endif
    </div>
</div>

{{-- MODALS --}}
@if(auth()->id() === $itinerary->user_id)
<div id="addStopModal" class="itinerary-stop-modal" style="display: none;">
    <div class="modal-card">
        <div class="modal-header">
            <h2 class="modal-title">Add New Stop</h2>
            <button type="button" onclick="document.getElementById('addStopModal').style.display='none'" class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('itineraries.addStop', $itinerary->id) }}" method="POST" id="stopForm">
                @csrf
                <div class="modal-input-group">
                    <label class="modal-label">Stop Title</label>
                    <input type="text" name="title" required class="modal-input" placeholder="e.g. Safari at Masai Mara">
                </div>
                <div class="date-row">
                    <div class="modal-input-group">
                        <label class="modal-label">Start Date</label>
                        <input type="date" name="start_time" id="add_stop_start" required class="modal-input">
                    </div>
                    <div class="modal-input-group">
                        <label class="modal-label">End Date</label>
                        <input type="date" name="end_time" id="add_stop_end" class="modal-input">
                    </div>
                </div>
                <div class="modal-input-group" style="position: relative;">
                    <label class="modal-label">Tagged Businesses</label>
                    <input type="text" id="biz-search-input" class="modal-input" placeholder="Search and add businesses..." autocomplete="off">
                    <div id="selected-businesses-container" class="selected-businesses"></div>
                    <input type="hidden" name="business_ids" id="business-ids-input">
                    <div id="biz-results" class="biz-search-results"></div>
                </div>
                <div class="modal-input-group">
                    <label class="modal-label">Location Name</label>
                    <input type="text" name="location_name" id="location-name-input" class="modal-input" placeholder="e.g. Nairobi National Park">
                </div>
                <div class="modal-input-group">
                    <label class="modal-label">Description</label>
                    <textarea name="description" class="modal-textarea" placeholder="What's happening at this stop?"></textarea>
                </div>
                <button type="submit" class="modal-submit-btn">Add to Itinerary</button>
            </form>
        </div>
    </div>
</div>

<div id="editStopModal" class="itinerary-stop-modal" style="display: none;">
    <div class="modal-card">
        <div class="modal-header">
            <h2 class="modal-title">Edit Stop</h2>
            <button type="button" onclick="document.getElementById('editStopModal').style.display='none'" class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editStopForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-input-group">
                    <label class="modal-label">Stop Title</label>
                    <input type="text" name="title" id="edit_stop_title" required class="modal-input">
                </div>
                <div class="date-row">
                    <div class="modal-input-group">
                        <label class="modal-label">Start Date</label>
                        <input type="date" name="start_time" id="edit_stop_start" required class="modal-input">
                    </div>
                    <div class="modal-input-group">
                        <label class="modal-label">End Date</label>
                        <input type="date" name="end_time" id="edit_stop_end" class="modal-input">
                    </div>
                </div>
                <div class="modal-input-group" style="position: relative;">
                    <label class="modal-label">Tagged Businesses</label>
                    <input type="text" id="edit-biz-search-input" class="modal-input" placeholder="Search and add businesses..." autocomplete="off">
                    <div id="edit-selected-businesses-container" class="selected-businesses"></div>
                    <input type="hidden" name="business_ids" id="edit-business-ids-input">
                    <div id="edit-biz-results" class="biz-search-results"></div>
                </div>
                <div class="modal-input-group">
                    <label class="modal-label">Location Name</label>
                    <input type="text" name="location_name" id="edit_stop_location" class="modal-input">
                </div>
                <div class="modal-input-group">
                    <label class="modal-label">Description</label>
                    <textarea name="description" id="edit_stop_description" class="modal-textarea"></textarea>
                </div>
                <button type="submit" class="modal-submit-btn">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<div id="deleteStopConfirmModal" class="itinerary-stop-modal" style="display: none;">
    <div class="modal-card mini-modal">
        <div class="modal-header danger-header">
            <h2 class="modal-title">Delete Stop</h2>
            <button type="button" onclick="document.getElementById('deleteStopConfirmModal').style.display='none'" class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body text-center">
            <div class="danger-icon-box"><i class="fas fa-trash-alt"></i></div>
            <p class="delete-warning-text">Remove <strong id="delete_stop_name">this stop</strong>?</p>
            <p class="delete-sub-text">This will permanently remove it from your journey.</p>
            <div class="modal-actions-dual">
                <button type="button" onclick="document.getElementById('deleteStopConfirmModal').style.display='none'" class="modal-cancel-btn">Cancel</button>
                <form id="deleteStopForm" method="POST" style="flex: 1;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="modal-delete-btn">Remove Stop</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="deleteConfirmModal" class="itinerary-stop-modal" style="display: none;">
    <div class="modal-card mini-modal">
        <div class="modal-header danger-header">
            <h2 class="modal-title">Delete Journey</h2>
            <button type="button" onclick="document.getElementById('deleteConfirmModal').style.display='none'" class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body text-center">
            <div class="danger-icon-box"><i class="fas fa-exclamation-triangle"></i></div>
            <p class="delete-warning-text">Are you sure you want to delete <strong>"{{ $itinerary->title }}"</strong>?</p>
            <p class="delete-sub-text">This action cannot be undone and all stops will be removed.</p>
            <div class="modal-actions-dual">
                <button type="button" onclick="document.getElementById('deleteConfirmModal').style.display='none'" class="modal-cancel-btn">Go Back</button>
                <form action="{{ route('itineraries.destroy', $itinerary->id) }}" method="POST" style="flex: 1;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="modal-delete-btn">Delete Forever</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<div id="copyToast">
    <i class="fas fa-check-circle" style="color: #4ade80;"></i>
    <span>Link copied to clipboard</span>
</div>
@endsection

@push('footer-scripts')
<script>
    function toggleInteraction(type) {
        if (!@json(auth()->check())) {
            window.location.href = "{{ route('login') }}";
            return;
        }

        const url = type === 'join' ? "{{ route('itineraries.join', $itinerary->id) }}" : "{{ route('itineraries.like', $itinerary->id) }}";
        const btn = document.getElementById(type + '-btn');

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (type === 'join') {
                    btn.classList.toggle('joined', data.joined);
                    const btnText = btn.querySelector('.btn-text');
                    if(btnText) btnText.innerText = data.joined ? 'Joined' : 'Join this Journey';
                    const btnIcon = btn.querySelector('i');
                    if(btnIcon) btnIcon.className = data.joined ? 'fas fa-check-circle' : 'fas fa-plus-circle';
                    document.getElementById('participant-count').innerText = data.count;
                }
            }
        });
    }

    function nativeShare() {
        if (navigator.share) {
            navigator.share({
                title: '{{ e($itinerary->title) }}',
                text: 'Check out this journey on Discover Kenya!',
                url: '{{ route('itineraries.show', $itinerary->slug) }}'
            })
            .then(() => console.log('Successful share'))
            .catch((error) => console.log('Error sharing', error));
        } else {
            let btn = document.getElementById('nativeShareBtn');
            copyToClipboard('{{ route('itineraries.show', $itinerary->slug) }}', btn);
        }
    }

    function copyToClipboard(text, btnElement) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                showCopyFeedback(btnElement);
            }).catch(function(err) {
                fallbackCopyToClipboard(text, btnElement);
            });
        } else {
            fallbackCopyToClipboard(text, btnElement);
        }
    }

    function fallbackCopyToClipboard(text, btnElement) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-9999px";
        textArea.style.top = "0";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            const successful = document.execCommand('copy');
            if (successful) showCopyFeedback(btnElement);
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }
        document.body.removeChild(textArea);
    }

    function showCopyFeedback(btnElement) {
        let originalIcon = btnElement.innerHTML;
        let originalBg = btnElement.style.background;
        btnElement.innerHTML = '<i class="fas fa-check"></i>';
        btnElement.style.background = '#22c55e';
        setTimeout(() => {
            btnElement.innerHTML = originalIcon;
            btnElement.style.background = originalBg; 
        }, 2000);
        let toast = document.getElementById("copyToast");
        if (toast) {
            toast.className = "show";
            setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
        }
    }

    let addSelectedBusinesses = [];
    let editSelectedBusinesses = [];
    let searchTimeout = null;

    document.addEventListener('DOMContentLoaded', function() {
        setupBusinessSearch('biz-search-input', 'biz-results', 'add');
        setupBusinessSearch('edit-biz-search-input', 'edit-biz-results', 'edit');
        
        const addStart = document.getElementById('add_stop_start');
        const addEnd = document.getElementById('add_stop_end');
        const editStart = document.getElementById('edit_stop_start');
        const editEnd = document.getElementById('edit_stop_end');

        if (addStart && addEnd) {
            addStart.addEventListener('change', () => {
                addEnd.min = addStart.value;
                if (addEnd.value && addEnd.value < addStart.value) addEnd.value = addStart.value;
            });
        }
        if (editStart && editEnd) {
            editStart.addEventListener('change', () => {
                editEnd.min = editStart.value;
                if (editEnd.value && editEnd.value < editStart.value) editEnd.value = editStart.value;
            });
        }
    });

    function setupBusinessSearch(inputId, resultsId, type) {
        const bizInput = document.getElementById(inputId);
        const bizResults = document.getElementById(resultsId);
        if (!bizInput) return;

        bizInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            if (query.length < 2) {
                bizResults.style.display = 'none';
                return;
            }
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`{{ route('ajax.businesses.search') }}?q=${encodeURIComponent(query)}`);
                    const businesses = await response.json();
                    const list = type === 'add' ? addSelectedBusinesses : editSelectedBusinesses;
                    const matches = businesses.filter(b => !list.find(s => s.id === b.id));
                    if (matches.length > 0) {
                        let html = '';
                        matches.forEach(b => {
                            html += `<div class="biz-result-item" onclick="addBusinessSet(${b.id}, '${b.n.replace(/'/g, "\\'")}', '${type}')">
                                        <i class="fas fa-store"></i> ${b.n}
                                     </div>`;
                        });
                        bizResults.innerHTML = html;
                        bizResults.style.display = 'block';
                    } else {
                        bizResults.innerHTML = '<div style="padding: 15px; text-align: center; color: #9ca3af; font-size: 0.875rem;">No businesses found</div>';
                        bizResults.style.display = 'block';
                    }
                } catch (error) { console.error('Search failed:', error); }
            }, 300);
        });
        document.addEventListener('click', (e) => {
            if (!bizInput.contains(e.target) && !bizResults.contains(e.target)) bizResults.style.display = 'none';
        });
    }

    function addBusinessSet(id, name, type) {
        let list = type === 'add' ? addSelectedBusinesses : editSelectedBusinesses;
        if (!list.find(b => b.id === id)) {
            list.push({ id, name });
            if (type === 'add') {
                renderAddBusinessTags();
                document.getElementById('biz-search-input').value = '';
                document.getElementById('biz-results').style.display = 'none';
                const locInput = document.getElementById('location-name-input');
                if (!locInput.value.trim()) locInput.value = name;
            } else {
                renderEditBusinessTags();
                document.getElementById('edit-biz-search-input').value = '';
                document.getElementById('edit-biz-results').style.display = 'none';
                const locInput = document.getElementById('edit_stop_location');
                if (!locInput.value.trim()) locInput.value = name;
            }
        }
    }

    function removeBusinessSet(id, type) {
        if (type === 'add') {
            addSelectedBusinesses = addSelectedBusinesses.filter(b => b.id !== id);
            renderAddBusinessTags();
        } else {
            editSelectedBusinesses = editSelectedBusinesses.filter(b => b.id !== id);
            renderEditBusinessTags();
        }
    }

    function renderAddBusinessTags() {
        renderTagsUI(addSelectedBusinesses, document.getElementById('selected-businesses-container'), document.getElementById('business-ids-input'), 'add');
    }

    function renderEditBusinessTags() {
        renderTagsUI(editSelectedBusinesses, document.getElementById('edit-selected-businesses-container'), document.getElementById('edit-business-ids-input'), 'edit');
    }

    function renderTagsUI(list, container, idsInput, type) {
        if (!container || !idsInput) return;
        if (list.length === 0) {
            container.innerHTML = '';
            idsInput.value = '';
            return;
        }
        let html = '';
        list.forEach(b => {
            html += `<div class="business-tag"><span>${b.name}</span>
                        <button type="button" class="business-tag-remove" onclick="removeBusinessSet(${b.id}, '${type}')">×</button>
                     </div>`;
        });
        container.innerHTML = html;
        idsInput.value = list.map(b => b.id).join(',');
    }

    function switchView(view) {
        const roadmap = document.getElementById('roadmap-view');
        const explorer = document.getElementById('explorer-view');
        const btnRoadmap = document.getElementById('btn-roadmap');
        const btnExplorer = document.getElementById('btn-explorer');

        if (view === 'roadmap') {
            roadmap.style.display = 'block';
            explorer.style.display = 'none';
            btnRoadmap.classList.add('active');
            btnExplorer.classList.remove('active');
            if (pathAnimId) cancelAnimationFrame(pathAnimId);
        } else {
            roadmap.style.display = 'none';
            explorer.style.display = 'block';
            btnRoadmap.classList.remove('active');
            btnExplorer.classList.add('active');
            // Allow display:block to paint before calculating positions
            requestAnimationFrame(() => {
                initExplorer();
            });
        }
    }

    function prepareEditStopModal(btn) {
        const stop = JSON.parse(btn.dataset.stop);
        const biz = JSON.parse(btn.dataset.biz);
        openEditStopModal(stop, btn.dataset.start, btn.dataset.end, biz);
    }

    function openEditStopModal(stop, startDate, endDate, business) {
        document.getElementById('edit_stop_title').value = stop.title;
        document.getElementById('edit_stop_start').value = startDate;
        document.getElementById('edit_stop_end').value = endDate;
        document.getElementById('edit_stop_location').value = stop.location_name || '';
        document.getElementById('edit_stop_description').value = stop.description || '';
        editSelectedBusinesses = business ? [business] : [];
        renderEditBusinessTags();
        document.getElementById('editStopForm').action = `/itinerary-stops/${stop.id}`;
        document.getElementById('edit_stop_end').min = startDate;
        document.getElementById('editStopModal').style.display = 'flex';
    }

    function confirmDeleteStop(stopId, stopTitle) {
        document.getElementById('delete_stop_name').innerText = `"${stopTitle}"`;
        document.getElementById('deleteStopForm').action = `/itinerary-stops/${stopId}`;
        document.getElementById('deleteStopConfirmModal').style.display = 'flex';
    }

    let sceneRotationX = 0, sceneRotationY = 0, isMouseMoving = false, mouseTimeout, vehicleFrame = 150, wildlifePack = [];
    let pathAnimId = null;
    let stopPositions = [];
    const biomeMap = {
        coastal: { 
            trees: ['🌴', '🌵'], 
            animals: [
                '🦀', '🐢', '🦜', '🐙', '🐬', '🐳', '🦈', '🐊', '🦩', '🐠', 
                '🐡', '🐟', '🦑', '🦐', '🪼', '🐚', '🐦', '🦢', '🦆', '🦤', 
                '🦭', '🦦', '🪿', '🐋', '⛵'
            ], 
            color: 'radial-gradient(circle at center, #0ea5e9 0%, #0c4a6e 100%)' 
        },
        forest: { 
            trees: ['🌲', '🌳', '🌿'], 
            animals: [
                '🐆', '🐒', '🐗', '🦌', '🐿️', '🐇', '🦊', '🐻', '🐼', '🦥', 
                '🦦', '🦫', '🦨', '🦘', '🦡', '🐍', '🦎', '🦋', '🐌', '🐞', 
                '🐝', '🐜', '🦗', '🕷️', '🦂'
            ], 
            color: 'radial-gradient(circle at center, #14532d 0%, #020617 100%)' 
        },
        savannah: { 
            trees: ['🎋', '🌳'], 
            animals: [
                '🦒', '🐘', '🦓', '🦁', '🐅', '🐃', '🐄', '🐂', '🐎', '🐏', 
                '🐑', '🐐', '🐪', '🐫', '🦏', '🦛', '🐀', '🐇', '🦔', '🐕', 
                '🦍', '🦧', '🐆', '🦬', '🦅'
            ], 
            color: 'radial-gradient(circle at center, #78350f 0%, #020617 100%)' 
        },
        night: { 
            trees: ['🌲'], 
            animals: [
                '🦉', '🦇', '🐺', '🦊', '🦝', '🐀', '🐍', '🕷️', '🦂', '🦗', 
                '🦋', '🐌', '🐛', '🦎', '🐈', '🐾', '🐆', '🦟', '🪰', '🪳', 
                '🪲', '🐜', '🕷️', '🦂', '🌑'
            ], 
            color: 'radial-gradient(circle at center, #1e1b4b 0%, #020617 100%)' 
        }
    };
    const seasonMap = {
        summer: { name: 'Dry Season', particleClass: 'dust', filter: 'brightness(1.1) saturate(1.2)', trees: null },
        winter: { name: 'Snowy Peak', particleClass: 'snowflake', filter: 'hue-rotate(180deg) brightness(0.9)', trees: ['❄️', '🌲', '🏔️'] },
        autumn: { name: 'Harvest', particleClass: 'leaf', filter: 'sepia(0.4) saturate(1.5)', trees: ['🍂', '🍁', '🌾'] },
        rainy: { name: 'Monsoon', particleClass: 'raindrop', filter: 'contrast(1.1) brightness(0.8)', trees: ['🌱', '🌿', '🍃'] }
    };

    const currentMonth = new Date().getMonth();
    let currentSeason = (currentMonth >= 2 && currentMonth <= 4) ? 'rainy' : 'summer';

    function changeSeason(newSeason) {
        currentSeason = newSeason;
        document.querySelectorAll('.season-btn').forEach(b => b.classList.remove('active'));
        const activeBtn = document.getElementById('btn-' + newSeason);
        if(activeBtn) activeBtn.classList.add('active');
        document.getElementById('season-name').innerText = seasonMap[newSeason].name;
        initParticles();
        drawDynamicPath();
    }

    function initExplorer() {
        const explorer = document.querySelector('.explorer-container');
        initParticles(); 
        setTimeout(drawDynamicPath, 100);
        animateScene(); 
        explorer.addEventListener('mousemove', (e) => {
            isMouseMoving = true;
            clearTimeout(mouseTimeout);
            const rect = explorer.getBoundingClientRect();
            sceneRotationY = (rect.width / 2 - (e.clientX - rect.left)) / 70;
            sceneRotationX = (rect.height / 2 - (e.clientY - rect.top)) / 70;
            mouseTimeout = setTimeout(() => { isMouseMoving = false; }, 2000);
        });
    }

    function drawDynamicPath() {
        const svgPath = document.getElementById('dynamic-journey-path');
        const svgPulse = document.getElementById('dynamic-journey-pulse');
        const anchors = document.querySelectorAll('.stop-pole-anchor');
        const treeGroup = document.getElementById('path-trees');
        const wildlifeGroup = document.getElementById('path-wildlife');
        const container = document.getElementById('journey-svg-canvas').getBoundingClientRect();

        if (anchors.length < 2) return;
        let points = [];
        anchors.forEach(anchor => {
            const rect = anchor.getBoundingClientRect();
            points.push({ x: (rect.left + rect.width / 2) - container.left, y: (rect.top + rect.height / 2) - container.top });
        });

        let d = `M ${points[0].x - 250},${points[0].y} L ${points[0].x},${points[0].y} `;
        for (let i = 0; i < points.length - 1; i++) {
            const p0 = points[i], p1 = points[i+1], cp1x = p0.x + (p1.x - p0.x) / 2;
            d += `C ${cp1x},${p0.y} ${cp1x},${p1.y} ${p1.x},${p1.y} `;
        }
        d += `L ${points[points.length - 1].x + 250},${points[points.length - 1].y}`;
        svgPath.setAttribute('d', d);
        svgPulse.setAttribute('d', d);
        const pathLength = svgPath.getTotalLength();

        stopPositions = [];
        anchors.forEach(anchor => {
            const rect = anchor.getBoundingClientRect();
            stopPositions.push({ x: (rect.left + rect.width / 2) - container.left, anchorElement: anchor, parentContainer: anchor.closest('.explorer-stop'), triggered: false });
        });

        treeGroup.innerHTML = '';
        const season = seasonMap[currentSeason];
        for(let i = 0; i < 20; i++) {
            const pos = (pathLength / 20) * i + (Math.random() * 30), pt = svgPath.getPointAtLength(pos % pathLength);
            let pool = season.trees || biomeMap.savannah.trees;
            const tree = document.createElementNS("http://www.w3.org/2000/svg", "text");
            tree.setAttribute("x", pt.x); tree.setAttribute("y", pt.y - 12);
            tree.setAttribute("font-size", "20px"); tree.textContent = pool[Math.floor(Math.random() * pool.length)];
            treeGroup.appendChild(tree);
        }

        wildlifeGroup.innerHTML = '';
        wildlifePack = [];
        for(let i = 0; i < 8; i++) {
            const pos = Math.random() * pathLength, animalPool = biomeMap.savannah.animals;
            const animalEl = document.createElementNS("http://www.w3.org/2000/svg", "text");
            animalEl.setAttribute("font-size", "18px"); 
            animalEl.textContent = animalPool[Math.floor(Math.random() * animalPool.length)];
            animalEl.setAttribute("text-anchor", "middle"); // This ensures they flip on their center axis
            wildlifeGroup.appendChild(animalEl);
            wildlifePack.push({ el: animalEl, offset: pos, speed: 0.3 + Math.random() * 0.5, direction: Math.random() > 0.5 ? 1 : -1 });
        }
        animatePathElements(svgPath);
    }

    function animatePathElements(pathElement) {
        const vehicle = document.getElementById('safari-vehicle');
        const scene = document.querySelector('.explorer-scene');
        const sun = document.getElementById('sun'), moon = document.getElementById('moon'), starField = document.getElementById('star-field');
        const length = pathElement.getTotalLength();
        if (length <= 0) return;
        
        vehicle.style.visibility = 'visible';
        if (pathAnimId) cancelAnimationFrame(pathAnimId);

        function step() {
            vehicleFrame = (vehicleFrame + 1.3) % length;
            const vPt = pathElement.getPointAtLength(vehicleFrame), vNext = pathElement.getPointAtLength((vehicleFrame + 1) % length);
            const vAngle = Math.atan2(vNext.y - vPt.y, vNext.x - vPt.x) * 180 / Math.PI;
            vehicle.setAttribute('transform', `translate(${vPt.x}, ${vPt.y - 8}) rotate(${vAngle})`);
            scene.style.filter = seasonMap[currentSeason].filter;
            const progress = vehicleFrame / length;

            if (sun && moon && starField) {
                const arcHeight = Math.sin(progress * Math.PI) * 150, horizonPos = (progress * 100); 
                sun.style.left = horizonPos + '%'; sun.style.top = (200 - arcHeight) + 'px';
                sun.style.opacity = progress < 0.7 ? (1 - (progress * 1.2)) : 0;
                moon.style.left = horizonPos + '%'; moon.style.top = (250 - arcHeight) + 'px';
                moon.style.opacity = progress > 0.7 ? ((progress - 0.7) * 3) : 0;
            }

            stopPositions.forEach(stop => {
                const distanceX = Math.abs(vPt.x - stop.x);
                if (distanceX < 70) {
                    stop.parentContainer.classList.add('is-active');
                    if (!stop.triggered && distanceX < 10) {
                        stop.triggered = true; stop.anchorElement.classList.add('passed');
                    }
                } else stop.parentContainer.classList.remove('is-active');
            });

            wildlifePack.forEach(animal => {
                animal.offset = (animal.offset + (animal.speed * animal.direction) + length) % length;
                const aPt = pathElement.getPointAtLength(animal.offset);
                
                // Logic: Emojis face Left by default. 
                // If moving Forward (direction 1), flip them to face Right.
                // If moving Backward (direction -1), keep them scale 1.
                const flip = animal.direction === 1 ? -1 : 1;
                
                animal.el.setAttribute('transform', `translate(${aPt.x}, ${aPt.y - 12}) scale(${flip}, 1)`);
            });
            pathAnimId = requestAnimationFrame(step);
        }
        pathAnimId = requestAnimationFrame(step);
    }

    function animateScene() {
        const scene = document.querySelector('.explorer-scene');
        let time = 0;
        function drift() {
            if (!isMouseMoving) {
                time += 0.01;
                scene.style.transform = `rotateY(${Math.sin(time) * 3}deg) rotateX(${Math.cos(time * 0.8) * 2}deg)`;
            } else {
                scene.style.transform = `rotateY(${sceneRotationY}deg) rotateX(${sceneRotationX}deg)`;
            }
            requestAnimationFrame(drift);
        }
        drift();
    }

    function initParticles() {
        const container = document.getElementById('parallax-particles');
        if (!container) return;
        container.innerHTML = ''; 
        const season = seasonMap[currentSeason];
        for (let i = 0; i < 50; i++) {
            const p = document.createElement('div');
            p.className = `particle ${currentSeason === 'summer' ? '' : 'seasonal ' + season.particleClass}`;
            p.style.left = Math.random() * 100 + '%'; p.style.top = Math.random() * 100 + '%';
            p.style.animationDelay = Math.random() * 8 + 's';
            container.appendChild(p);
        }
    }
</script>
@endpush
