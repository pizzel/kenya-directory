@extends('layouts.site')

@section('title', 'My Travel Passport - Discover Kenya')

@push('footer-scripts')
<link rel="stylesheet" href="{{ asset('css/itineraries.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="wishlist-page" style="background-color: #f8fafc; min-height: 85vh; padding-bottom: 60px;">
    
    {{-- HEADER SECTIONS (Unchanged) --}}
    <div class="wishlist-header" style="background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 50px 0 40px;">
        <div class="container">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 30px;">
                
                {{-- User Greeting --}}
                <div>
                    <h1 style="font-size: 2.2rem; font-weight: 800; color: #1e293b; margin-bottom: 8px; letter-spacing: -0.02em;">
                        Hello, {{ Auth::user()->name }} <span style="font-size: 1.8rem;">👋🌍</span>
                    </h1>
                    <p style="color: #64748b; font-size: 1.05rem; max-width: 500px;">
                        Your personal travel passport. Track your dream destinations and celebrate your journeys.
                    </p>
                </div>

                {{-- Stats Strip --}}
                <div class="stats-strip" style="display: flex; gap: 40px; background: white; padding: 15px 30px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    <div style="text-align: center;">
                        <div style="font-size: 1.4rem; font-weight: 800; color: #3b82f6; line-height: 1;">{{ $stats['bucket_count'] }}</div>
                        <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 6px;">Bucket List</div>
                    </div>
                    <div style="width: 1px; background: #e2e8f0;"></div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.4rem; font-weight: 800; color: #10b981; line-height: 1;">{{ $stats['visited_count'] }}</div>
                        <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 6px;">Visited</div>
                    </div>
                    <div style="width: 1px; background: #e2e8f0;"></div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.4rem; font-weight: 800; color: #f59e0b; line-height: 1;">{{ $stats['counties_covered'] }}</div>
                        <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 6px;">Counties</div>
                    </div>
                </div>
            </div>

            {{-- PASSPORT STAMPS (Unchanged) --}}
            @if(isset($visitedCounties) && $visitedCounties->isNotEmpty())
                <div style="margin-top: 35px; background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <div style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px; display: flex; align-items: center;">
                        <i class="fas fa-map" style="margin-right: 8px; color: #cbd5e1;"></i> 
                        Passport Stamps <span style="background: #f1f5f9; color: #64748b; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; margin-left: 8px;">{{ $visitedCounties->count() }}</span>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        @foreach($visitedCounties as $countyName)
                            <span style="background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; transition: all 0.2s;">
                                <i class="fas fa-check-circle" style="color: #10b981; margin-right: 6px; font-size: 0.8rem;"></i> {{ $countyName }}
                            </span>
                        @endforeach
                        <span style="padding: 6px 12px; font-size: 0.8rem; color: #94a3b8; font-style: italic; display: flex; align-items: center;">
                            <a href="https://discoverkenya.co.ke/listings" style="text-decoration: none; color: inherit;">
                                    <i class="fas fa-arrow-right" style="font-size: 0.7rem; margin-right: 5px;"></i>
                                    Visit more places to unlock counties!
                                </a>
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="container" style="margin-top: 40px;">
        
        {{-- TABS (Unchanged) --}}
        <div class="custom-tabs" style="display: flex; gap: 30px; border-bottom: 2px solid #e2e8f0; margin-bottom: 30px;">
            <button class="tab-btn active" onclick="openTab(event, 'bucketList')" style="padding: 10px 0; font-weight: 600; color: #1e293b; border-bottom: 3px solid #3b82f6; margin-bottom: -2px; background: none; border-top: none; border-left: none; border-right: none; cursor: pointer; font-size: 1rem; transition: color 0.2s;">
                Bucket List
            </button>
            <button class="tab-btn" onclick="openTab(event, 'visitedPlaces')" style="padding: 10px 0; font-weight: 600; color: #94a3b8; border-bottom: 3px solid transparent; margin-bottom: -2px; background: none; border-top: none; border-left: none; border-right: none; cursor: pointer; font-size: 1rem; transition: color 0.2s;">
                Visited History
            </button>
            <button class="tab-btn" onclick="openTab(event, 'joinedJourneys')" style="padding: 10px 0; font-weight: 600; color: #94a3b8; border-bottom: 3px solid transparent; margin-bottom: -2px; background: none; border-top: none; border-left: none; border-right: none; cursor: pointer; font-size: 1rem; transition: color 0.2s;">
                My Journeys
            </button>
        </div>

        {{-- CONTENT: BUCKET LIST --}}
        <div id="bucketList" class="tab-content">
            @if($bucketList->isEmpty())
                <div class="empty-state" style="text-align: center; padding: 80px 20px; border: 2px dashed #e2e8f0; border-radius: 16px; background: white;">
                    <div style="background: #f1f5f9; width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="far fa-compass" style="font-size: 1.8rem; color: #94a3b8;"></i>
                    </div>
                    <h3 style="color: #1e293b; font-weight: 700; margin-bottom: 8px; font-size: 1.2rem;">Your bucket list is empty</h3>
                    <p style="color: #64748b; margin-bottom: 25px; max-width: 400px; margin-left: auto; margin-right: auto; line-height: 1.5;">
                        The world is waiting. Start exploring businesses and add them here to plan your next trip.
                    </p>
                    <a href="{{ route('listings.index') }}" style="background: #1e293b; color: white; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 0.95rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: background 0.2s;">
                        Start Exploring
                    </a>
                </div>
            @else
                <div class="wishlist-grid" style="display: flex; flex-direction: column; gap: 20px;">
                    @foreach($bucketList as $business)
                        <x-wishlist-card :item="$business" type="bucket" />
                    @endforeach
                </div>
                {{-- Pagination Links for Bucket List --}}
                <div class="mt-4 d-flex justify-content-center">
                    {{ $bucketList->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        {{-- CONTENT: VISITED PLACES --}}
        <div id="visitedPlaces" class="tab-content" style="display: none;">
             @if($visitedPlaces->isEmpty())
                <div class="empty-state" style="text-align: center; padding: 80px 20px; border: 2px dashed #e2e8f0; border-radius: 16px; background: white;">
                    <div style="background: #f1f5f9; width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-camera-retro" style="font-size: 1.8rem; color: #94a3b8;"></i>
                    </div>
                    <h3 style="color: #1e293b; font-weight: 700; margin-bottom: 8px; font-size: 1.2rem;">No memories yet</h3>
                    <p style="color: #64748b; line-height: 1.5;">When you visit a place, click "I've Visited This" on your bucket list to move it here.</p>
                </div>
            @else
                <div class="wishlist-grid" style="display: flex; flex-direction: column; gap: 20px;">
                    @foreach($visitedPlaces as $business)
                        <x-wishlist-card :item="$business" type="visited" />
                    @endforeach
                </div>
                {{-- Pagination Links for Visited Places --}}
                <div class="mt-4 d-flex justify-content-center">
                    {{ $visitedPlaces->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        {{-- CONTENT: JOURNEYS --}}
        <div id="joinedJourneys" class="tab-content" style="display: none;">
            @if($joinedJourneys->isEmpty())
                <div class="empty-state" style="text-align: center; padding: 80px 20px; border: 2px dashed #e2e8f0; border-radius: 16px; background: white;">
                    <div style="background: #f1f5f9; width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                         <i class="far fa-map" style="font-size: 1.8rem; color: #94a3b8;"></i>
                    </div>
                    <h3 style="color: #1e293b; font-weight: 700; margin-bottom: 8px; font-size: 1.2rem;">No journeys joined yet</h3>
                    <p style="color: #64748b; margin-bottom: 25px; max-width: 400px; margin-left: auto; margin-right: auto; line-height: 1.5;">
                        Discover community-created itineraries and join others on their Kenyan adventures.
                    </p>
                    <a href="{{ route('itineraries.index') }}" style="background: #1e293b; color: white; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 0.95rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: background 0.2s;">
                        Browse Journeys
                    </a>
                </div>
            @else
                <div class="itinerary-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">
                    @foreach($joinedJourneys as $itinerary)
                        <x-itinerary-card :itinerary="$itinerary" />
                    @endforeach
                </div>
                {{-- Pagination Links for Journeys --}}
                <div class="mt-8 d-flex justify-content-center">
                    {{ $joinedJourneys->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

{{-- IMPROVED SCRIPT ONLY --}}
<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
            tablinks[i].style.color = "#94a3b8";
            tablinks[i].style.borderBottomColor = "transparent";
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
        evt.currentTarget.style.color = "#1e293b";
        evt.currentTarget.style.borderBottomColor = "#3b82f6";
    }
    
    // AJAX Logic with Spinner
    document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('click', function(e) {
            // Check if the clicked element (or its parent) has the 'wishlist-action-btn' class
            if (e.target.closest('.wishlist-action-btn')) {
                const btn = e.target.closest('.wishlist-action-btn');
                e.preventDefault();

                const url = btn.dataset.url;
                const action = btn.dataset.action;
                const target = btn.dataset.target; 
                
                // 1. Save original content (to revert if error)
                const originalContent = btn.innerHTML;
                
                // 2. Add Spinner & Disable
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.style.opacity = '0.7';
                btn.style.cursor = 'wait';
                btn.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ action: action, status_target: target })
                })
                .then(res => {
                    if (res.ok) {
                        // 3. Reload on Success
                        window.location.reload(); 
                    } else {
                        // Revert on failure
                        btn.innerHTML = originalContent;
                        btn.style.opacity = '1';
                        btn.style.cursor = 'pointer';
                        btn.disabled = false;
                        alert('Something went wrong. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.innerHTML = originalContent;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                    btn.disabled = false;
                });
            }
        });
    });
</script>
@endsection