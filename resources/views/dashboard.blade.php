@extends('layouts.site')

@section('title', 'Dashboard - Discover Kenya')

{{-- DATA FETCHING BLOCK --}}
@php
    $user = Auth::user();
    
    // Fetch last 3 bucket list items for the "Recent Activity" section
    $recentSaves = $user->wishlistedBusinesses()
                        ->wherePivot('status', 'wished')
                        ->with(['media', 'county'])
                        ->orderByPivot('created_at', 'desc')
                        ->take(3)
                        ->get();
    
    // Simple stats
    $bucketCount = $user->wishlistedBusinesses()->wherePivot('status', 'wished')->count();
    $visitedCount = $user->wishlistedBusinesses()->wherePivot('status', 'done')->count();
@endphp

@section('content')
<div class="dashboard-page" style="background-color: #f8fafc; min-height: 85vh; padding-bottom: 80px;">

    {{-- 1. HERO SECTION --}}
    <div class="dashboard-header" style="background: white; border-bottom: 1px solid #e2e8f0; padding: 50px 0 40px;">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h1 style="font-size: 2.2rem; font-weight: 800; color: #1e293b; margin-bottom: 8px; letter-spacing: -0.02em;">
                        Welcome back, {{ explode(' ', $user->name)[0] }}!
                    </h1>
                    <p style="color: #64748b; font-size: 1.05rem;">
                        Ready to discover your next adventure in Kenya?
                    </p>
                </div>
                
                {{-- Quick Stat --}}
                <div style="text-align: right; display: flex; gap: 30px;">
                    <div>
                        <span style="display: block; font-size: 1.5rem; font-weight: 800; color: #3b82f6;">{{ $bucketCount }}</span>
                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Saved</span>
                    </div>
                    <div>
                        <span style="display: block; font-size: 1.5rem; font-weight: 800; color: #10b981;">{{ $visitedCount }}</span>
                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Visited</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: -30px;">
        
        {{-- 2. QUICK ACTION CARDS (Floating over the header line) --}}
        <div class="action-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-bottom: 50px;">
            
            {{-- Card 1: Explore --}}
            <a href="{{ route('listings.index') }}" class="dashboard-card group">
                <div class="icon-circle blue">
                    <i class="fas fa-compass"></i>
                </div>
                <div>
                    <h3>Explore Listings</h3>
                    <p>Find new hotels, activities, and hidden gems near you.</p>
                </div>
                <div class="arrow-icon"><i class="fas fa-arrow-right"></i></div>
            </a>

            {{-- Card 2: Passport --}}
            <a href="{{ route('wishlist.index') }}" class="dashboard-card group">
                <div class="icon-circle green">
                    <i class="fas fa-passport"></i>
                </div>
                <div>
                    <h3>My Travel Passport</h3>
                    <p>Manage your bucket list and track visited locations.</p>
                </div>
                <div class="arrow-icon"><i class="fas fa-arrow-right"></i></div>
            </a>

            {{-- Card 3: Profile --}}
            <a href="{{ route('profile.edit') }}" class="dashboard-card group">
                <div class="icon-circle purple">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div>
                    <h3>Account Settings</h3>
                    <p>Update your password, email, and security preferences.</p>
                </div>
                <div class="arrow-icon"><i class="fas fa-arrow-right"></i></div>
            </a>

        </div>

        {{-- 3. RECENTLY SAVED (If Any) --}}
        @if($recentSaves->isNotEmpty())
            <div class="recent-section">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b;">Recently Added to Bucket List</h3>
                    <a href="{{ route('wishlist.index') }}" style="font-size: 0.9rem; color: #3b82f6; text-decoration: none; font-weight: 500;">View All &rarr;</a>
                </div>

                <div class="recent-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    @foreach($recentSaves as $item)
                        {{-- Mini Card --}}
                        <a href="{{ route('listings.show', $item->slug) }}" class="mini-card" style="text-decoration: none;">
                            <div class="mini-image">
                                <img src="{{ $item->getImageUrl('thumbnail') }}" alt="{{ $item->name }}">
                            </div>
                            <div class="mini-content">
                                <h4>{{ Str::limit($item->name, 25) }}</h4>
                                <p><i class="fas fa-map-marker-alt"></i> {{ $item->county->name ?? 'Kenya' }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Empty State CTA --}}
            <div style="background: white; border-radius: 16px; padding: 40px; text-align: center; border: 1px dashed #cbd5e1;">
                <p style="color: #64748b; margin-bottom: 15px;">You haven't saved any places yet.</p>
                <a href="{{ route('listings.index') }}" style="display: inline-block; background: #1e293b; color: white; padding: 10px 25px; border-radius: 30px; font-weight: 600; text-decoration: none;">Start Exploring</a>
            </div>
        @endif

    </div>
</div>

{{-- STYLES --}}
<style>
    /* Dashboard Cards */
    .dashboard-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        display: flex;
        align-items: center;
        gap: 20px;
        text-decoration: none;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        border-color: #cbd5e1;
    }

    .dashboard-card h3 { margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 700; color: #1e293b; }
    .dashboard-card p { margin: 0; font-size: 0.9rem; color: #64748b; line-height: 1.4; }

    /* Icons */
    .icon-circle {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .icon-circle.blue { background: #eff6ff; color: #2563eb; }
    .icon-circle.green { background: #ecfdf5; color: #10b981; }
    .icon-circle.purple { background: #f5f3ff; color: #7c3aed; }

    .arrow-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        color: #cbd5e1;
        transition: color 0.2s;
    }
    .dashboard-card:hover .arrow-icon { color: #3b82f6; }

    /* Mini Cards for Recent Activity */
    .mini-card {
        display: flex;
        align-items: center;
        background: white;
        padding: 10px;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        transition: border-color 0.2s;
    }
    .mini-card:hover { border-color: #cbd5e1; }
    
    .mini-image {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        margin-right: 15px;
    }
    .mini-image img { width: 100%; height: 100%; object-fit: cover; }
    
    .mini-content h4 { margin: 0 0 4px 0; font-size: 0.95rem; font-weight: 600; color: #334155; }
    .mini-content p { margin: 0; font-size: 0.8rem; color: #94a3b8; }
</style>
@endsection