@extends('layouts.site')

@section('title', $collection->title . ' - Discover Kenya')

@section('meta_description', Str::limit(strip_tags($collection->description), 155))

@section('canonical')
    <link rel="canonical" href="{{ route('collections.show', $collection->slug) }}" />
@endsection

@section('breadcrumbs')
    <a href="{{ route('home') }}">Home</a> /
    <a href="{{ route('collections.index') }}">Collections</a> /
    <span>{{ $collection->title }}</span>
@endsection

@section('content')
<div class="collection-show-page" style="background: #f8fafc; padding-bottom: 80px;">
    
    {{-- 1. IMMERSIVE HERO HEADER --}}
    @php
        // Use the robust helper from the model (handles custom covers + fallback + sorting)
        $heroImage = $collection->getCoverImageUrl('hero');
    @endphp
    <section class="collection-hero" style="position: relative; height: 50vh; min-height: 400px; background: #fff; overflow: hidden;">
        <img src="{{ $heroImage }}" alt="{{ $collection->title }}" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.6;">
        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);"></div>
        <div class="container" style="position: absolute; bottom: 40px; left: 0; right: 0; color: white;">
            <span style="background: #3b82f6; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 15px; display: inline-block;">Curated Collection</span>
            <h1 style="font-size: 3rem; color:#fff; font-weight: 800; margin: 0; line-height: 1.1;">{{ $collection->title }}</h1>
        </div>
    </section>

    <div class="container" style="margin-top: -40px; position: relative; z-index: 10;">
        <div style="display: grid; grid-template-columns: 1fr 320px; gap: 40px;">
            
            {{-- 2. MAIN CONTENT (The Guide) --}}
            <main style="background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                
                {{-- Intro Text --}}
                <div class="collection-intro" style="font-size: 1.2rem; line-height: 1.8; color: #475569; margin-bottom: 50px; border-left: 4px solid #3b82f6; padding-left: 25px;">
                    {!! nl2br(e($collection->description)) !!}
                </div>

                <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 30px; color: #1e293b;">Recommended Places ({{ $businesses->count() }})</h2>

                {{-- THE VERTICAL LIST (Blog Style) --}}
                <div class="collection-items-list" style="display: flex; flex-direction: column; gap: 60px;">
                    @foreach($businesses as $index => $business)
                        <article class="collection-item" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 60px;">
                            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                                {{-- Numbering --}}
                                <div style="font-size: 3rem; font-weight: 900; color: #e2e8f0; line-height: 1;">{{ sprintf('%02d', $index + 1) }}</div>
                                
                                <div style="flex: 1; min-width: 300px;">
                                    <h3 style="font-size: 1.8rem; font-weight: 800; margin: 0 0 10px; color: #1e293b;">
                                        <a href="{{ route('listings.show', $business->slug) }}" style="text-decoration: none; color: inherit;">{{ $business->name }}</a>
                                    </h3>
                                    
                                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                                        <span style="color: #f59e0b; font-weight: 700;"><i class="fas fa-star"></i> {{ number_format($business->google_rating ?? 0, 1) }}</span>
                                        
                                        {{-- FIXED: Added ?? 'Kenya' as a fallback if county is missing --}}
                                        <span style="color: #64748b; font-size: 0.9rem;"><i class="fas fa-map-marker-alt"></i> {{ $business->county->name ?? 'Kenya' }}</span>
                                    </div>

                                    <div style="margin-bottom: 20px; border-radius: 12px; overflow: hidden; height: 300px;">
                                        <img src="{{ $business->getImageUrl('card') }}" alt="{{ $business->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>

                                    <p style="font-size: 1.05rem; line-height: 1.7; color: #334155; margin-bottom: 20px;">
                                        {{ Str::limit(strip_tags($business->about_us ?: $business->description), 300) }}
                                    </p>

                                    <div style="display: flex; gap: 10px;">
                                        <a href="{{ route('listings.show', $business->slug) }}" style="background: #1e293b; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem;">View Details & Photos</a>
                                        @if($business->website)
                                            <a href="{{ $business->website }}" target="_blank" style="background: #f1f5f9; color: #475569; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem;">Official Website</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </main>

            {{-- 3. SIDEBAR (Discovery) --}}
            <aside>
                <div style="position: sticky; top: 100px;">
                    <div style="background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; margin-bottom: 30px;">
                        <h4 style="font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.05em;">More Collections</h4>
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            @foreach($otherCollections as $other)
                                <a href="{{ route('collections.show', $other->slug) }}" style="text-decoration: none; display: flex; gap: 12px; align-items: center; group">
                                    <div style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden; flex-shrink: 0;">
                                        <img src="{{ $other->businesses->first()?->getImageUrl('thumbnail') }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <span style="font-size: 0.95rem; font-weight: 600; color: #1e293b; line-height: 1.3;">{{ $other->title }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div style="background: #eff6ff; padding: 25px; border-radius: 16px; text-align: center;">
                         <i class="fas fa-envelope-open-text" style="font-size: 2rem; color: #3b82f6; margin-bottom: 15px;"></i>
                         <h4 style="color: #1e40af; font-weight: 800; margin-bottom: 10px;">Get Travel Tips</h4>
                         <p style="color: #3b82f6; font-size: 0.85rem; margin-bottom: 15px;">Join 5,000+ explorers getting weekly hidden gems in their inbox.</p>
                         
                         {{-- Added ID: newsletter-email-input --}}
                         <input type="email" id="newsletter-email-input" placeholder="Your email..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #bfdbfe; margin-bottom: 10px;">
                         
                         {{-- Added ID: newsletter-subscribe-btn --}}
                         <button id="newsletter-subscribe-btn" style="width: 100%; background: #3b82f6; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 700; cursor: pointer;">Subscribe</button>
                    </div>
                </div>
            </aside>

        </div>
    </div>
</div>

{{-- Mobile Fixes --}}
<style>
    @media (max-width: 991px) {
        .collection-hero h1 { font-size: 2rem !important; }
        .collection-show-page .container > div { grid-template-columns: 1fr !important; }
        .collection-show-page main { padding: 20px !important; }
        .collection-item > div { flex-direction: column !important; gap: 15px !important; }
    }
</style>
@endsection