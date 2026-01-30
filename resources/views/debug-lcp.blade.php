@extends('layouts.site')

@section('title', 'LCP Debug - Phase 4 (Full Page)')

@section('styles')
    <style>
        /* Hero Styles */
        .hero-slider-section { position: relative; height: 70vh; }
        .hero-slide-image { width: 100%; height: 100%; object-fit: cover; }
        
        /* Animations */
        .fade-in-up { opacity: 0; transform: translateY(20px); animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }

        /* Discovery & County Styles (Minimal for Debug) */
        .discovery-collections-section { padding: 60px 0; background: #fff; }
        .discovery-scroller-wrapper { position: relative; display: flex; align-items: center; }
        .discovery-scroller { display: flex; overflow-x: auto; gap: 20px; scroll-behavior: smooth; padding-bottom: 20px; }
        .discovery-scroller::-webkit-scrollbar { height: 8px; }
        
        .destination-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }

        .debug-info { padding: 10px; background: #0f172a; color: #34d399; text-align: center; font-family: monospace; }
    </style>
@endsection

@section('content')

    {{-- 1. HERO SLIDER (FIXED: Added loading="lazy") --}}
    @if(isset($heroSliderBusinesses) && $heroSliderBusinesses->isNotEmpty())
        <section class="hero-slider-section">
            <div class="swiper heroSwiper" style="height: 100%;">
                <div class="swiper-wrapper">
                    @foreach($heroSliderBusinesses as $index => $business)
                        <div class="swiper-slide {{ $index === 0 ? 'hero-slide-first' : '' }}">
                            <picture class="hero-slide-picture" style="width: 100%; height: 100%; display: block;">
                                <source media="(max-width: 767px)" srcset="{{ $business->hero_image_url_mobile }}" sizes="100vw">
                                <source media="(min-width: 768px)" srcset="{{ $business->hero_image_url }}" sizes="100vw">
                                <img 
                                    src="{{ $business->hero_image_url }}" 
                                    alt="{{ $business->name }}" 
                                    class="hero-slide-image" 
                                    @if($index === 0) 
                                        fetchpriority="high" loading="eager" decoding="async"
                                    @else 
                                        fetchpriority="low" loading="lazy" decoding="async"
                                    @endif
                                >
                            </picture>
                            {{-- Content omitted for brevity, logic remains the same --}}
                            <div class="slide-content container" style="position: absolute; bottom: 60px; left: 50%; transform: translateX(-50%); text-align: center; color: white;">
                                <h1 class="{{ $index === 0 ? '' : 'fade-in-up' }}">{{ Str::limit($business->name, 50) }}</h1>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 2. DISCOVERY COLLECTIONS (Added) --}}
    @if(isset($discoveryCards) && $discoveryCards->isNotEmpty())
    <section class="discovery-collections-section">
        <div class="container">
            <h2>Explore Collections</h2>
            <div class="discovery-scroller-wrapper">
                <div class="discovery-scroller" id="discoveryScroller">
                    <div class="discovery-collections-grid" style="display:flex; gap: 20px;">
                        @foreach($discoveryCards as $card)
                            {{-- Using the component exactly as Home does --}}
                            <x-discovery-card :collection="$card" />
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- 3. POPULAR COUNTIES (Added) --}}
    @if(isset($popularCounties) && $popularCounties->isNotEmpty())
    <section class="popular-counties content-section" style="padding: 60px 0; background: #fff;">
        <div class="container">
            <h2>Popular Destinations</h2>
            <div class="destination-grid" id="popularCountiesGrid">
                {{-- Using the Partial exactly as Home does --}}
                @include('partials.home-counties-loop', ['counties' => $popularCounties])
            </div>
        </div>
    </section>
    @endif

    <div class="debug-info">
        Phase 4: Full Page Render
    </div>
@endsection