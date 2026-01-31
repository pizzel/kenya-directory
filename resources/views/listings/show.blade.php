@extends('layouts.site')
@php
    $currentYear = date('Y');
    
    // --- SMART CATEGORY SELECTION ---
    // 1. Define "Noise" categories to ignore
    $noiseSlugs = [
        'establishment', 'point-of-interest', 'tourist-attraction', 'food', 
        'health', 'lodging', 'locality', 'political', 'store', 'premise', 
        'school', 'place-of-worship', 'services'
    ];

    // 2. Find the first category that is NOT noise
    $bestCategory = $business->categories->first(function ($cat) use ($noiseSlugs) {
        return !in_array($cat->slug, $noiseSlugs);
    });

    // 3. Fallback: If we only have noise (or no categories), use the first available or default.
    $categoryName = $bestCategory->name ?? ($business->categories->first()?->name ?? 'Activity');

    $locationName = $business->county->name ?? 'Kenya';
    
    // --- Rating Calculation (Weighted Average) ---
    $internalCount = $business->reviews->where('is_approved', true)->count();
    $internalAvg = $business->reviews->where('is_approved', true)->avg('rating') ?? 0;
    
    $googleCount = $business->google_rating_count ?? 0;
    $googleAvg = $business->google_rating ?? 0;

    $totalCount = $internalCount + $googleCount;
    
    if ($totalCount > 0) {
        $compositeRating = (($internalAvg * $internalCount) + ($googleAvg * $googleCount)) / $totalCount;
    } else {
        $compositeRating = 0;
    }
    
    $ratingFormat = number_format($compositeRating, 1);

    // --- Keywords ---
    $keywords = collect([$business->name, 'Kenya', $business->county->name ?? null]);
    $business->categories->each(fn($cat) => $keywords->push($cat->name));
    $business->tags->each(fn($tag) => $keywords->push($tag->name));
    $pageKeywords = $keywords->filter()->unique()->implode(', ');
@endphp
@section('title')
    {{ $business->name }} - {{ $categoryName }} in {{ $locationName }} (Reviews, Photos & Prices {{ $currentYear }})
@endsection
@section('meta_description'){{ $contextSummary }} Get reviews, photos, contact details and location updated for {{ $currentYear }}.@endsection
@section('meta_keywords', $pageKeywords)
@section('canonical')
    <link rel="canonical" href="{{ route('listings.show', $business->slug) }}" />
@endsection
@section('breadcrumbs')
    <a href="{{ route('home') }}">Home</a> /
    @php
        $listingBreadcrumbSlug = $business->county ? $business->county->slug : 'nairobi-city';
    @endphp
    <a href="{{ route('listings.county', ['countySlug' => $listingBreadcrumbSlug]) }}">Listings</a> /
    @if($business->county && $business->county->slug)
        <a href="{{ route('listings.county', ['countySlug' => $business->county->slug]) }}">{{ $business->county->name }}</a> /
    @endif
    <span>{{ $business->name }}</span>
@endsection
@section('content')
    <div class="listing-detail-page container">
        
        <!-- Gallery Section -->
        @php
            $allGalleryImages = $business->getMedia('images');
            $altBase = "{$business->name} {$locationName} - {$categoryName}";
        @endphp

        <div class="listing-gallery-final">
            <div class="gallery-main-image-wrapper-final">
                <picture style="width: 100%; height: 100%;">
                    {{-- Mobile: Card Size (400x300) --}}
                    <source media="(max-width: 767px)" 
                            srcset="{{ $lcpImageUrlMobile ?? asset('images/placeholder-card.jpg') }}"
                            sizes="100vw">
                    
                    {{-- Desktop: Full Size (800x600) --}}
                    <source media="(min-width: 768px)" 
                            srcset="{{ $lcpImageUrl ?? asset('images/placeholder-large.jpg') }}"
                            sizes="100vw">
                    
                    {{-- Fallback: Use mobile image as default for better mobile performance --}}
                    <img src="{{ $lcpImageUrlMobile ?? asset('images/placeholder-card.jpg') }}" 
                        alt="{{ $altBase }} - {{ $contextSummary }}" 
                        id="galleryMainImageFinal"
                        fetchpriority="high"
                        width="400"
                        height="300">
                </picture>
            </div>
            
            @if($allGalleryImages->count() > 1)
                <div class="gallery-right-column-final">
                    <div class="right-top-image-wrapper-final">
                        <img src="{{ $thumbnail1Url ?? asset('images/placeholder-medium.jpg') }}" 
                            alt="{{ $altBase }} View 1" 
                            loading="lazy"
                            data-full-url="{{ $allGalleryImages->get(1)?->getUrl() }}"
                            onclick="window.setMainGalleryImageFinal('{{ $allGalleryImages->get(1)?->getUrl() }}')">
                    </div>
                    <div class="right-bottom-thumbnails-final">
                        <div class="small-thumbnail-item-final">
                            <img src="{{ $thumbnail2Url ?? asset('images/placeholder-small.jpg') }}" 
                                alt="{{ $altBase }} View 2" 
                                loading="lazy"
                                data-full-url="{{ $allGalleryImages->get(2)?->getUrl() }}"
                                onclick="window.setMainGalleryImageFinal('{{ $allGalleryImages->get(2)?->getUrl() }}')">
                        </div>
                        <div class="small-thumbnail-item-final view-all-trigger">
                            <img src="{{ $thumbnail3Url ?? asset('images/placeholder-small.jpg') }}" 
                                alt="{{ $altBase }} View 3 (See All)"
                                loading="lazy">
                            <div class="view-all-overlay-text">
                                View All Images ({{ $allGalleryImages->count() }})
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="business-lightbox-gallery" style="display: none;">
            @foreach($allGalleryImages as $image)
                <a href="{{ $image->getUrl() }}" title="{{ $image->getCustomProperty('caption', $business->name) }}"></a>
            @endforeach
        </div>
        <!-- End Gallery Section -->

        <!-- NEW HERO HEADER (Title, Location, Rating) -->
        <div class="listing-header-hero" style="margin-bottom: 30px; margin-top: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
                <div style="flex: 1;">
                    <h1 style="font-size: 2.2rem; font-weight: 800; color: #1a202c; margin-bottom: 8px; line-height: 1.2;">
                        {{ $business->name }}
                        @if($business->is_verified)
                            <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem; vertical-align: middle; margin-left: 8px;" title="Verified Listing"></i>
                        @endif
                    </h1>

                    <p style="color: #475569; font-size: 1rem; display: flex; align-items: center; flex-wrap: wrap;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 8px; color: #64748b;"></i> 
                        {{ $business->address ?? 'Location not provided' }}, {{ $business->county->name ?? '' }}
                        
                        <span style="margin: 0 10px; color: #cbd5e1;">|</span>
                        
                        <span style="color: #475569; font-weight: 500;">{{ $categoryName }}</span>
                    </p>
                </div>

                <div style="text-align: right;">
                    <div class="rating-badge" style="background: #f8fafc; padding: 10px 15px; border-radius: 12px; border: 1px solid #e2e8f0; display: inline-flex; flex-direction: column; align-items: flex-end;">
                        <div style="font-size: 1.3rem; font-weight: 800; color: #1a202c;">
                            <i class="fas fa-star" style="color: #f59e0b; font-size: 1.1rem;"></i> {{ $ratingFormat }}
                        </div>
                        <div style="font-size: 0.8rem; color: #475569;">
                            {{ number_format($totalCount) }} Reviews
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area - 3 COLUMNS -->
        <div class="listing-content-area-3col">
            
            {{-- COLUMN 1: Main Details, Contact, Socials & Reviews --}}
            <div class="listing-info-col1"> 
                <div class="listing-info-details">
                    
                    {{-- DYNAMIC CONTACT GRID --}}
                    @php
                        // Calculate how many items we actually have
                        $contactItems = 0;
                        if (!empty($business->phone_number)) $contactItems++;
                        if (!empty($business->website)) $contactItems++;
                        if (!empty($business->email)) $contactItems++;
                        
                        // If we have 2 or more items, use a grid. If 1, span full width.
                        $gridTemplate = $contactItems > 1 ? 'repeat(auto-fit, minmax(200px, 1fr))' : '1fr';
                    @endphp

                    @if($contactItems > 0)
                        <div class="contact-grid" style="display: grid; grid-template-columns: {{ $gridTemplate }}; gap: 15px; margin-bottom: 30px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #f1f5f9;">
                            
                            @if($business->phone_number)
                                <div>
                                    <small style="color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em;">Phone</small>
                                    <a href="tel:{{ $business->phone_number }}" style="display: block; color: #1e293b; font-weight: 600; text-decoration: none; margin-top: 2px; font-size: 1.05rem;">
                                        {{ $business->phone_number }}
                                    </a>
                                </div>
                            @endif

                            @if($business->website)
                                <div>
                                    <small style="color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em;">Website</small>
                                    <a href="{{ Str::startsWith($business->website, ['http']) ? $business->website : 'http://'.$business->website }}" target="_blank" rel="noopener noreferrer" style="display: block; color: #3b82f6; font-weight: 600; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 2px; font-size: 1.05rem;">
                                        Visit Official Site &rarr;
                                    </a>
                                </div>
                            @endif
                            
                            @if($business->email)
                                <div>
                                    <small style="color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em;">Email</small>
                                    <a href="mailto:{{ $business->email }}" style="display: block; color: #1e293b; font-weight: 600; text-decoration: none; margin-top: 2px; font-size: 1.05rem;">
                                        {{ Str::limit($business->email, 25) }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    {{-- TOPICAL SIGNALS (FOR QUICK SCAN & SEO) --}}
                    @if($business->tags->isNotEmpty())
                        <div class="topical-signals-bar" style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; border-bottom: 1px dashed #e2e8f0; padding-bottom: 15px;">
                            @foreach($business->tags->take(4) as $tag)
                                <div style="display: flex; align-items: center; gap: 6px; color: #047857; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.02em;">
                                    <i class="fas fa-tag" style="font-size: 0.75rem; color: #10b981;"></i>
                                    {{ $tag->name }}
                                </div>
                            @endforeach
                            @if($business->is_verified)
                                <div style="display: flex; align-items: center; gap: 6px; color: #0369a1; font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">
                                    <i class="fas fa-certificate" style="color: #0ea5e9;"></i>
                                    Vetted Content
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- 2. About Section --}}
                    <div class="listing-about-us" style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.3rem; font-weight: 700; color: #1e293b; margin-bottom: 15px;">About {{ $business->name }}</h2>
                        <div class="content-from-editor" style="font-size: 1rem; line-height: 1.7; color: #334155;">
                            {!! $business->about_us !!}
                        </div>
                    </div>
                    
                    @if($business->description)
                        <div class="listing-description" style="margin-bottom: 25px;">
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 10px;">More Details</h3>
                            <div class="content-from-editor" style="color: #475569;">{!! $business->description !!}</div>
                        </div>
                    @endif

                    {{-- EXPLORER KEN'S VERDICT (THE SMARTER SEO BRAIN) --}}
                    <style>
                        .explorer-verdict-box {
                            margin-bottom: 40px;
                            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
                            border-radius: 16px;
                            padding: 25px;
                            border: 1px solid #bfdbfe;
                            position: relative;
                            overflow: hidden;
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                        }
                        .explorer-verdict-inner {
                            display: flex;
                            align-items: flex-start;
                            gap: 20px;
                            position: relative;
                            z-index: 1;
                        }
                        .explorer-avatar-wrapper {
                            flex-shrink: 0;
                        }
                        .explorer-avatar-wrapper img {
                            width: 80px;
                            height: 80px;
                            border-radius: 50%;
                            border: 3px solid #ffffff;
                            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
                            object-fit: cover;
                            background: #fff;
                        }
                        .explorer-content {
                            flex: 1;
                        }
                        .explorer-header {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            margin-bottom: 8px;
                            flex-wrap: wrap;
                        }
                        .explorer-badge {
                            background: #3b82f6;
                            color: #ffffff;
                            font-size: 0.7rem;
                            font-weight: 800;
                            padding: 3px 8px;
                            border-radius: 20px;
                            text-transform: uppercase;
                            letter-spacing: 0.05em;
                        }
                        .explorer-title {
                            font-size: 1.25rem;
                            font-weight: 800;
                            color: #1e3a8a;
                            margin: 0;
                        }
                        .explorer-quote {
                            font-size: 1.05rem;
                            line-height: 1.6;
                            color: #1e40af;
                            font-style: italic;
                            font-weight: 500;
                            margin: 0;
                        }
                        .explorer-bg-icon {
                            position: absolute;
                            right: 20px;
                            bottom: 10px;
                            font-size: 4rem;
                            color: rgba(59, 130, 246, 0.05);
                            transform: rotate(10deg);
                        }

                        /* MOBILE OPTIMIZATION */
                        @media (max-width: 640px) {
                            .explorer-verdict-box {
                                padding: 20px 15px;
                            }
                            .explorer-verdict-inner {
                                flex-direction: column;
                                align-items: center;
                                text-align: center;
                                gap: 15px;
                            }
                            .explorer-avatar-wrapper img {
                                width: 70px;
                                height: 70px;
                            }
                            .explorer-header {
                                justify-content: center;
                            }
                            .explorer-title {
                                font-size: 1.1rem;
                            }
                            .explorer-quote {
                                font-size: 0.95rem;
                            }
                            .explorer-bg-icon {
                                font-size: 3rem;
                                right: 10px;
                                bottom: 5px;
                            }
                        }
                    </style>
                    <div class="explorer-verdict-box">
                        <div class="explorer-verdict-inner">
                            <div class="explorer-avatar-wrapper">
                                <img src="{{ asset('images/ken-explorer.webp') }}" alt="Explorer Ken">
                            </div>
                            <div class="explorer-content">
                                <div class="explorer-header">
                                    <span class="explorer-badge">Expert Verdict</span>
                                    <h3 class="explorer-title">Explorer Ken's Tip</h3>
                                </div>
                                <p class="explorer-quote">
                                    "{{ $contextSummary }}"
                                </p>
                            </div>
                        </div>
                        {{-- Subtle Background Decoration --}}
                        <i class="fas fa-quote-right explorer-bg-icon"></i>
                    </div>

                    {{-- 3. Social Media & Share --}}
                    <div class="social-share-wrapper" style="margin-bottom: 40px;">
                        <h3 style="font-size: 0.9rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 15px;">
                            Share & Connect
                        </h4>
                        {{-- TOAST NOTIFICATION HTML --}}
                <div id="copyToast">
                    <i class="fas fa-check-circle" style="color: #4ade80;"></i>
                    <span>Link copied to clipboard</span>
                </div>
<style>
    #copyToast {
        visibility: hidden;
        min-width: 250px;
        background-color: #1e293b; /* Slate 800 - Dark Premium Background */
        color: #f8fafc; /* Slate 50 - White Text */
        text-align: center;
        border-radius: 50px; /* Pill Shape */
        padding: 12px 24px;
        position: fixed;
        z-index: 9999;
        left: 50%;
        bottom: 30px; /* Start position */
        transform: translateX(-50%);
        font-size: 0.95rem;
        font-weight: 500;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.1); /* Nice Shadow */
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        opacity: 0;
        transition: opacity 0.3s ease-in-out, bottom 0.3s ease-in-out;
    }

    #copyToast.show {
        visibility: visible;
        opacity: 1;
        bottom: 50px; /* Slide Up Animation */
    }
</style>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            
                            {{-- ======================================================================= --}}
                            {{-- 1. SHARE ACTIONS (Share THIS page to others)                            --}}
                            {{-- ======================================================================= --}}

                            {{-- WhatsApp Share --}}
                            <a href="https://wa.me/?text={{ urlencode('Check out ' . $business->name . ' on Discover Kenya: ' . route('listings.show', $business->slug)) }}" 
                            target="_blank"
                            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #25D366; border-radius: 50%; color: white; transition: transform 0.2s; text-decoration: none;" 
                            title="Share on WhatsApp">
                                <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i>
                            </a>

                            {{-- Facebook Share --}}
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('listings.show', $business->slug)) }}" 
                            target="_blank"
                            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #1877F2; border-radius: 50%; color: white; transition: transform 0.2s; text-decoration: none;" 
                            title="Share on Facebook">
                                <i class="fab fa-facebook-f" style="font-size: 1.1rem;"></i>
                            </a>

                            {{-- Twitter/X Share --}}
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode('Check out ' . $business->name) }}&url={{ urlencode(route('listings.show', $business->slug)) }}" 
                            target="_blank"
                            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #000000; border-radius: 50%; color: white; transition: transform 0.2s; text-decoration: none;" 
                            title="Post to X (Twitter)">
                                <i class="fab fa-x-twitter" style="font-size: 1.1rem;"></i>
                                {{-- Fallback if using older FontAwesome: <i class="fab fa-twitter"></i> --}}
                            </a>

                            {{-- 3. NATIVE SHARE (The Magic Button for Instagram/TikTok users) --}}
                            {{-- This button invokes the phone's native share sheet --}}
                            <button id="nativeShareBtn" onclick="nativeShare()"
                            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #E1306C; border: none; border-radius: 50%; color: white; cursor: pointer;" 
                            title="Share via...">
                                <i class="fas fa-share-alt" style="font-size: 1.1rem;"></i>
                            </button>

                            {{-- Copy Link (Best for Instagram/TikTok Sharing) --}}
                            <button onclick="copyToClipboard('{{ route('listings.show', $business->slug) }}', this)"
                            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #475569; border: none; border-radius: 50%; color: white; transition: transform 0.2s; cursor: pointer;" 
                            title="Copy Link (for Instagram/TikTok)">
                                <i class="fas fa-link" style="font-size: 1.0rem;"></i>
                            </button>

                            {{-- Vertical Divider --}}
                            @if(!empty($business->social_links))
                                <div style="width: 1px; background: #e2e8f0; margin: 0 5px;"></div>
                            @endif

                            {{-- ======================================================================= --}}
                            {{-- 2. BUSINESS PROFILES (Visit the business's social pages)                --}}
                            {{-- ======================================================================= --}}
                            @if(!empty($business->social_links))
                                @foreach($business->social_links as $platform => $link)
                                    @if(!empty($link))
                                        @php
                                            // Determine styling based on platform
                                            $iconClass = match(true) {
                                                str_contains($platform, 'facebook') => 'fab fa-facebook-f',
                                                str_contains($platform, 'instagram') => 'fab fa-instagram',
                                                str_contains($platform, 'twitter') => 'fab fa-twitter', // or fa-x-twitter
                                                str_contains($platform, 'tiktok') => 'fab fa-tiktok',
                                                str_contains($platform, 'linkedin') => 'fab fa-linkedin-in',
                                                str_contains($platform, 'youtube') => 'fab fa-youtube',
                                                default => 'fas fa-link'
                                            };
                                            
                                            // Optional: Specific brand colors for the business links too
                                            $bgColor = match(true) {
                                                str_contains($platform, 'instagram') => '#E1306C',
                                                str_contains($platform, 'tiktok') => '#000000',
                                                default => '#f1f5f9' // Default Grey
                                            };
                                            
                                            $textColor = match(true) {
                                                str_contains($platform, 'instagram') => '#ffffff',
                                                str_contains($platform, 'tiktok') => '#ffffff',
                                                default => '#475569' // Default Dark Grey
                                            };
                                        @endphp
                                        <a href="{{ $link }}" target="_blank" rel="noopener noreferrer" 
                                        style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: {{ $bgColor }}; border-radius: 50%; color: {{ $textColor }}; transition: all 0.2s; text-decoration: none;" 
                                        title="Visit us on {{ ucfirst($platform) }}">
                                            <i class="{{ $iconClass }}"></i>
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- 4. Report Link (Subtle Footer) --}}
                    <div style="margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                        <button type="button" id="reportBusinessBtn" data-item-id="{{ $business->id }}" data-item-name="{{ e($business->name) }}" data-item-type="business"
                                style="background: none; border: none; color: #475569; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; padding: 0;">
                            <i class="fas fa-flag" style="margin-right: 6px;"></i> Report an issue with this listing
                        </button>
                    </div>

                </div>

                {{-- REVIEWS SECTION --}}
                <div class="reviews-section" style="margin-top: 50px;">
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 20px;">Reviews & Ratings</h2>
                    
                    <div class="overall-rating-summary">
                         <div class="rating-value">{{ $compositeRating > 0 ? $ratingFormat : '0.0' }}</div>
                        <div class="rating-breakdown">
                            @php
                                $approvedReviews = $business->reviews->where('is_approved', true);
                                $reviewCountInternal = $approvedReviews->count();
                            @endphp
                            @for ($r = 5; $r >= 1; $r--)
                                @php
                                    $countForRating = $approvedReviews->where('rating', $r)->count();
                                    $percentage = ($reviewCountInternal > 0) ? ($countForRating / $reviewCountInternal) * 100 : 0;
                                @endphp
                                <span>{{$r}} Stars <div class="bar"><div style="width: {{ round($percentage) }}%;"></div></div> ({{ $countForRating }})</span>
                            @endfor
                        </div>
                        <div class="total-reviews" style="color: #334155; font-weight: 500;">Based on {{ number_format($totalCount) }} verified reviews</div>
                    </div>

                    {{-- Google Reviews --}}
                    @if($business->googleReviews->isNotEmpty())
                        <div class="google-reviews-wrapper" style="margin-bottom: 40px; margin-top: 30px;">
                            <h3 style="font-size: 1.1rem; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px; color: #333; display: flex; align-items: center; font-weight: 700;">
                                <img src="/images/Google_Favicon_2025.svg.webp" alt="Google" style="width: 18px; height: 18px; margin-right: 10px;">
                                Top Reviews from Google
                            </h3>
                            
                            <div class="comments-list">
                                @foreach($business->googleReviews->take(5) as $gReview)
                                    @include('partials._google-comment-item', ['gReview' => $gReview])
                                @endforeach
                            </div>
                            
                            <div style="text-align: right; margin-top: 10px;">
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($business->name . ' ' . ($business->county->name ?? 'Kenya')) }}" target="_blank" style="font-size: 0.9rem; color: #1d4ed8; text-decoration: underline; font-weight: 600;">
                                    Read more on Google Maps &rarr;
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Community Reviews --}}
                    <div class="community-reviews-wrapper" style="margin-top: 30px;">
                        <h3 style="font-size: 1.1rem; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px; color: #333; font-weight: 700;">
                            Community Reviews
                        </h3>

                        <div class="comments-list" id="business-comments-list">
                            @forelse($business->reviews->sortByDesc('created_at') as $review)
                                @include('partials._comment-item', ['review' => $review])
                            @empty
                                @if($business->googleReviews->isNotEmpty())
                                    <p class="text-gray-500 italic mb-6">No community reviews yet. Be the first to post one below!</p>
                                @else
                                    <p id="no-business-reviews-message" class="text-gray-500">No reviews yet for this listing. Be the first to share your experience!</p>
                                @endif
                            @endforelse           
                        </div>
                    </div>
                
                    {{-- Leave a Comment --}}
                    <div class="leave-comment-section" style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 30px;">
                        <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b; margin-bottom: 15px;">Rate & Review</h3>
                        @auth
                            <form id="businessReviewForm" action="{{ route('reviews.store', $business->slug) }}" method="POST" class="bo-form">
                                @csrf
                                <div class="form-group">
                                    <label for="rating">Your Rating <span class="text-red-500">*</span></label>
                                    <select name="rating" id="rating" required class="border-gray-300 rounded-md">
                                        <option value="">Select Rating</option>
                                        <option value="5">5 Stars - Excellent</option>
                                        <option value="4">4 Stars - Very Good</option>
                                        <option value="3">3 Stars - Good</option>
                                        <option value="2">2 Stars - Fair</option>
                                        <option value="1">1 Star - Poor</option>
                                    </select>
                                    @error('rating') <span class="input-error-message">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="comment">Your Comment <span class="text-red-500">*</span></label>
                                    <textarea name="comment" id="comment" rows="4" required class="border-gray-300 rounded-md" placeholder="Share your experience..."></textarea>
                                    @error('comment') <span class="input-error-message">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" class="btn bo-button-primary">Submit Review</button>
                            </form>
                        @else
                            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #e2e8f0;">
                                <p style="color: #334155; margin-bottom: 10px;">Sign in to leave a review and help others discover great places.</p>
                                <a href="{{ route('login', ['redirect' => url()->current()]) }}" style="display: inline-block; background: #2563eb; color: white; padding: 8px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">Log In to Review</a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>

            {{-- COLUMN 2: Sidebar (Map, Wishlist, Amenities, Tags) --}}
            <div class="listing-map-col2"> 
                
                {{-- 1. PREMIUM MAP WIDGET --}}
                <div class="sidebar-widget map-widget" style="border: none; padding: 0; overflow: hidden; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; border: 1px solid #e2e8f0;">
                    <div class="map-placeholder" style="height: 220px; position: relative;"> 
                        @if($business->latitude && $business->longitude) 
                            <iframe src="https://maps.google.com/maps?q={{ $business->latitude }},{{ $business->longitude }}&hl=es&z=14&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" title="Location Map for {{ $business->name }}"></iframe> 
                        @else 
                            <div style="height: 100%; display: flex; align-items: center; justify-content: center; background: #f1f5f9; color: #94a3b8; font-size: 0.9rem;">No Map Available</div> 
                        @endif 
                    </div>
                    
                    @if($business->latitude && $business->longitude) 
                        <div style="padding: 15px; background: white;">
                            <button onclick="navigateToLocation('{{ $business->latitude }}', '{{ $business->longitude }}', '{{ e(addslashes($business->name)) }}')" 
                                    style="width: 100%; padding: 10px; background: #047857; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-directions" style="margin-right: 8px;"></i> Get Directions
                            </button>
                        </div>
                    @endif 
                </div>

                @auth
                    @if (Auth::user()->role === 'user') 
                        @php
                            $userWishlistItem = Auth::user()->wishlistedBusinesses()->where('business_id', $business->id)->first();
                            $isInWishlist = (bool) $userWishlistItem;
                            $isDone = $isInWishlist && $userWishlistItem->pivot->status === 'done';
                            
                            // Stats for Mini Card
                            $bucketCount = Auth::user()->wishlistedBusinesses()->wherePivot('status', 'wished')->count();
                            $visitedCount = Auth::user()->wishlistedBusinesses()->wherePivot('status', 'done')->count();
                        @endphp
                        
                        <div id="wishlistWidget" class="sidebar-widget" style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 25px;">
                            
                            {{-- Main Bucket List Button --}}
                            <button type="button" 
                                    id="sidebarWishlistBtn" 
                                    data-url="{{ route('wishlist.business.toggle', $business->slug) }}"
                                    data-is-active="{{ $isInWishlist ? 'true' : 'false' }}"
                                    style="width: 100%; padding: 12px 25px; border-radius: 50px; font-weight: 600; font-size: 1rem; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; transition: all 0.2s; border: {{ $isInWishlist ? '1px solid #ef4444' : '1px solid #cbd5e1' }}; background: {{ $isInWishlist ? '#fef2f2' : 'white' }}; color: {{ $isInWishlist ? '#ef4444' : '#64748b' }}; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                                <i class="{{ $isInWishlist ? 'fas' : 'far' }} fa-heart" id="wishlistIcon"></i> 
                                <span id="wishlistText">{{ $isInWishlist ? 'Saved to Bucket List' : 'Add to Bucket List' }}</span>
                            </button>

                            {{-- Visited Controls --}}
                            <div id="visitedControls" style="margin-top: 15px; display: {{ $isInWishlist ? 'block' : 'none' }}; animation: fadeIn 0.3s ease;">
                                
                                {{-- Mark as Visited Button --}}
                                <button type="button" 
                                        id="markVisitedBtn"
                                        class="visited-toggle-btn"
                                        data-target="done"
                                        style="width: 100%; padding: 10px; border-radius: 12px; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: {{ !$isDone ? 'flex' : 'none' }}; justify-content: center; align-items: center; gap: 6px; background: #f0fdf4; border: 1px solid #22c55e; color: #16a34a; transition: transform 0.1s;">
                                    <i class="fas fa-check"></i> Mark as Visited
                                </button>

                                {{-- Unmark Visited Button --}}
                                <button type="button" 
                                        id="unmarkVisitedBtn"
                                        class="visited-toggle-btn" 
                                        data-target="wished"
                                        style="width: 100%; padding: 10px; border-radius: 12px; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: {{ $isDone ? 'flex' : 'none' }}; justify-content: center; align-items: center; gap: 6px; background: #fffbeb; border: 1px solid #f59e0b; color: #d97706; transition: transform 0.1s;">
                                    <i class="fas fa-undo"></i> Unmark Visited
                                </button>
                            </div>

                            {{-- Mini Stats Card --}}
                            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #e2e8f0;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; align-items: center;">
                                    <div style="text-align: center; flex: 1;">
                                        {{-- Added ID: miniBucketCount --}}
                                        <div style="font-size: 1.2rem; font-weight: 800; color: #3b82f6; line-height: 1;">
                                            <span id="miniBucketCount">{{ $bucketCount }}</span>
                                        </div>
                                        <div style="font-size: 0.65rem; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-top: 4px;">Bucket List</div>
                                    </div>
                                    <div style="width: 1px; height: 30px; background: #f1f5f9;"></div>
                                    <div style="text-align: center; flex: 1;">
                                        {{-- Added ID: miniVisitedCount --}}
                                        <div style="font-size: 1.2rem; font-weight: 800; color: #10b981; line-height: 1;">
                                            <span id="miniVisitedCount">{{ $visitedCount }}</span>
                                        </div>
                                        <div style="font-size: 0.65rem; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-top: 4px;">Visited</div>
                                    </div>
                                </div>
                                <a href="{{ route('wishlist.index') }}" style="display: block; text-align: center; font-size: 0.85rem; font-weight: 600; color: #475569; text-decoration: none; padding: 6px; border-radius: 6px; transition: background 0.2s, color 0.2s;" onmouseover="this.style.background='#f8fafc'; this.style.color='#1e293b'" onmouseout="this.style.background='transparent'; this.style.color='#475569'">
                                    View My Travel Passport &rarr;
                                </a>
                            </div>

                        </div>
                    @endif
                @else
                    {{-- Guest View --}}
                    <div class="sidebar-widget" style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 25px;">
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" 
                           style="width: 100%; padding: 12px 25px; border-radius: 50px; font-weight: 600; font-size: 1rem; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; text-decoration: none; border: 1px solid #cbd5e1; background: white; color: #64748b; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                            <i class="far fa-heart"></i> Add to Bucket List
                        </a>
                        <p style="text-align: center; font-size: 0.8rem; color: #475569; margin-top: 12px; line-height: 1.4; font-weight: 500;">
                            Login to save your favorite places and track your travels across Kenya.
                        </p>
                    </div>
                @endauth
                
                @if($semanticPair)
                {{-- EXPLORER KEN'S PERFECT PAIRING (ITINERARY HUB) --}}
                <div class="sidebar-widget" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border-top: 4px solid #3b82f6;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                        <img src="{{ asset('images/ken-explorer.webp') }}" alt="Explorer Ken" style="width: 45px; height: 45px; border-radius: 50%; border: 2px solid #ebf2ff; background: #fff;">
                        <div>
                            <h3 style="font-size: 0.95rem; font-weight: 800; color: #1e3a8a; margin: 0;">Explorer Ken's Tip</h3>
                            <span style="font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Perfect Pairing</span>
                        </div>
                    </div>
                    
                    <p style="font-size: 0.85rem; color: #475569; margin-bottom: 15px; line-height: 1.5;">
                        "Planning a trip to {{ $business->name }}? I highly recommend checking out <strong>{{ $semanticPair->name }}</strong> while you're in {{ $business->county->name ?? 'the area' }}!"
                    </p>

                    <a href="{{ route('listings.show', $semanticPair->slug) }}" style="text-decoration: none; display: block; group">
                        <div style="border-radius: 12px; overflow: hidden; position: relative; height: 120px; margin-bottom: 10px;">
                            <img src="{{ $semanticPair->getImageUrl() }}" alt="{{ $semanticPair->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 10px;">
                                <div style="color: white; font-weight: 700; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $semanticPair->name }}
                                </div>
                            </div>
                        </div>
                        <div style="text-align: center; background: #eff6ff; color: #2563eb; font-weight: 700; font-size: 0.85rem; padding: 10px; border-radius: 8px; transition: background 0.2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                            Explore Pairing &rarr;
                        </div>
                    </a>
                </div>
                @endif
                @if($business->categories->isNotEmpty())
                    <div class="sidebar-widget business-categories-widget" style="margin-bottom: 30px;">
                        <h2 style="font-size: 0.8rem; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.05em;">Activities</h2>
                        <ul class="widget-list" style="list-style: none; padding: 0;">
                            @foreach($business->categories as $category)
                                <li style="margin-bottom: 8px;">
                                    <a href="{{ route('listings.category', $category->slug) }}" style="display: flex; align-items: center; text-decoration: none; color: #334155; font-size: 0.95rem; font-weight: 500; transition: color 0.2s;">
                                        @if($category->icon_class)
                                            <i class="{{ $category->icon_class }} fa-fw" style="color: #94a3b8; margin-right: 8px; width: 20px; text-align: center;"></i>
                                        @else
                                            <i class="fas fa-tag fa-fw" style="color: #94a3b8; margin-right: 8px; width: 20px; text-align: center;"></i> 
                                        @endif
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- 4. FACILITIES WIDGET (Checklist Style) --}}
                @if($business->facilities->isNotEmpty())
                    <div class="sidebar-widget" style="margin-bottom: 30px;">
                        <h2 style="font-size: 0.8rem; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.05em;">Amenities</h2>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($business->facilities as $facility)
                                <a href="{{ route('listings.facility', ['facility' => $facility->slug,]) }}" 
                                   style="text-decoration: none; color: #334155; display: flex; align-items: center; font-size: 0.9rem;">
                                    <i class="fas fa-check-circle" style="color: #10b981; margin-right: 8px;"></i>
                                    {{ $facility->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 5. TAGS WIDGET (Pill Style) --}}
                @if($business->tags->isNotEmpty())
                    <div class="sidebar-widget">
                        <h2 style="font-size: 0.8rem; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.05em;">Vibe</h2>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            @foreach($business->tags as $tag)
                                <a href="{{ route('listings.tag', ['tag' => $tag->slug]) }}" 
                                   style="font-size: 0.8rem; padding: 5px 12px; background: #f1f5f9; border-radius: 20px; text-decoration: none; color: #475569; font-weight: 500; transition: all 0.2s; border: 1px solid transparent;">
                                   #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- COLUMN 3: Schedule & Related --}}
            <div class="listing-schedule-col3"> 
                <div class="sidebar-widget schedule-widget">
                    <h2 style="font-size: 0.8rem; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.05em;">Schedule</h2>
                    @if(!empty($formattedSchedules))
                        <table>
                            @foreach($daysOfWeek as $day)
                                <tr>
                                    <td>{{ $day }}</td>
                                    <td> 
                                        {{ $formattedSchedules[$day]['open'] }} 
                                        @if($formattedSchedules[$day]['open'] !== 'Closed' && $formattedSchedules[$day]['open'] !== 'N/A' && $formattedSchedules[$day]['close']) 
                                            - {{ $formattedSchedules[$day]['close'] }} 
                                        @endif 
                                        @if($formattedSchedules[$day]['notes'])
                                            <span class="highlight-time">({{ $formattedSchedules[$day]['notes'] }})</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <p>Opening hours not available.</p>
                    @endif
                </div>

                @if(isset($relatedListings) && $relatedListings->isNotEmpty())
                    <div class="sidebar-widget related-listings-widget">
                        <h2 style="font-size: 0.8rem; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.05em;">You Might Also Like</h2>
                        <ul class="related-list">
                            @foreach($relatedListings as $related)
                                <li>
                                    <div class="card-image-container" style="width: 60px; height: 50px; flex-shrink:0; border-radius: 6px; overflow: hidden;">
                                        <img src="{{ $related->images->firstWhere('is_main_gallery_image', true)?->url ?? asset('images/placeholder-small.jpg') }}" alt="{{ $related->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div style="flex: 1; padding-left: 10px;">
                                        <a href="{{ route('listings.show', $related->slug) }}" style="font-weight: 600; color: #1e293b; text-decoration: none; display: block; line-height: 1.2; margin-bottom: 2px;">{{ Str::limit($related->name, 35) }}</a>
                                        <span style="font-size: 0.8rem; color: #64748b;">{{ $related->county->name ?? '' }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="sidebar-widget weather-widget" id="weatherWidgetContainer">
                    <h2 style="font-size: 0.8rem; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.05em;">Weather Forecast</h2>
                    <div id="weatherContent" class="weather-content-area">
                        <p>Loading weather...</p> 
                    </div>
                </div>
            </div>
        </div>

        {{-- SIMILAR LISTINGS: Lazy Loaded via AJAX --}}
        <section class="similar-listings-section lazy-section-wrapper" style="padding: 60px 0; background: #f8fafc;">
            <div class="container">
                <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 25px;">Similar Places</h2>
                <div class="listings-grid" id="similarListingsContainer">
                    {{-- Skeleton loaders while loading --}}
                    @for($i = 0; $i < 6; $i++) 
                        <x-skeleton-card />
                    @endfor
                </div>
            </div>
        </section>
    </div>
@endsection
@push('seo')
    @if(isset($businessSchema))
        <script type="application/ld+json">
@json($businessSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        </script>
    @endif
@endpush
@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const weatherWidgetContainer = document.getElementById('weatherWidgetContainer');
        const weatherContent = document.getElementById('weatherContent');
        const businessIdForWeather = {{ $business->id }}; 

        if (weatherWidgetContainer && weatherContent && businessIdForWeather) {
            const weatherUrl = "{{ route('listings.weather', ['business' => ':businessId_placeholder']) }}".replace(':businessId_placeholder', businessIdForWeather);

            fetch(weatherUrl)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errData => {
                            throw new Error(errData.error || `HTTP error! status: ${response.status}`);
                        }).catch(() => { 
                            throw new Error(`HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        weatherContent.innerHTML = `<p class="weather-error">${data.error}</p>`;
                    } else if (data.daily_forecasts && data.daily_forecasts.length > 0) {
                        let forecastHtml = `<div class="weather-location-city">Forecast for ${data.city}</div>`;
                        forecastHtml += '<ul class="daily-forecast-list">';
                        data.daily_forecasts.forEach(day => {
                            forecastHtml += `
                                <li class="daily-forecast-item">
                                    <span class="forecast-day">${day.date_display}</span>
                                    <img src="${day.icon_url}" alt="${day.description}" class="forecast-icon">
                                    <span class="forecast-temp">${day.temp_max}° / ${day.temp_min}°C</span>
                                </li>
                            `;
                        });
                        forecastHtml += '</ul>';
                        weatherContent.innerHTML = forecastHtml;
                    } else {
                         weatherContent.innerHTML = `<p class="weather-error">No forecast data available.</p>`;
                    }
                })
                .catch(error => {
                    weatherContent.innerHTML = `<p class="weather-error">Could not load weather...</p>`;
                });
        }
    });
    
    function setMainGalleryImageFinal(src) {
        const mainImg = document.getElementById('galleryMainImageFinal');
        if (mainImg) { 
            mainImg.src = src; 
            
            // CRITICAL FIX: Update <source> tags if inside a <picture> element
            const picture = mainImg.closest('picture');
            if (picture) {
                const sources = picture.querySelectorAll('source');
                sources.forEach(source => {
                    source.srcset = src;
                });
            }
        }
    }

    // --- SIMILAR LISTINGS: AJAX LOADER (Deferred) ---
    document.addEventListener('DOMContentLoaded', function() {
        const similarContainer = document.getElementById('similarListingsContainer');
        
        if (similarContainer && "IntersectionObserver" in window) {
            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        observer.unobserve(entry.target);
                        loadSimilarListings();
                    }
                });
            }, { rootMargin: "300px" });
            
            observer.observe(similarContainer);
        } else if (similarContainer) {
            // Fallback for browsers without IntersectionObserver
            loadSimilarListings();
        }
        
        function loadSimilarListings() {
            const businessSlug = '{{ $business->slug }}';
            const endpoint = `{{ route('ajax.similar-listings', ['businessSlug' => ':slug']) }}`.replace(':slug', businessSlug);
            
            fetch(endpoint, {
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Accept': 'text/html' 
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                similarContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading similar listings:', error);
                similarContainer.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #94a3b8;"><p>Unable to load similar listings at this time.</p></div>';
            });
        }
    });

</script>
<script>
// Sidebar Wishlist Button Logic
    const sidebarWishlistBtn = document.getElementById('sidebarWishlistBtn');
    const visitedControls = document.getElementById('visitedControls');
    const markVisitedBtn = document.getElementById('markVisitedBtn');
    const unmarkVisitedBtn = document.getElementById('unmarkVisitedBtn');
    const wishlistIcon = document.getElementById('wishlistIcon');
    const wishlistText = document.getElementById('wishlistText');

    if (sidebarWishlistBtn) {
        
        // 1. Handle Main Bucket List Toggle
        sidebarWishlistBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Animation State
            const originalIconClass = wishlistIcon.className;
            wishlistIcon.className = 'fas fa-spinner fa-spin';
            this.disabled = true;
            this.style.opacity = '0.7';

            const url = this.dataset.url;
            const action = this.dataset.isActive === 'true' ? 'remove' : 'add';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: action })
            })
            .then(response => response.json())
            .then(data => {
                this.disabled = false;
                this.style.opacity = '1';

                if (data.success) {
                    // UPDATE STATS
                    if (data.bucket_count !== undefined) document.getElementById('miniBucketCount').innerText = data.bucket_count;
                    if (data.visited_count !== undefined) document.getElementById('miniVisitedCount').innerText = data.visited_count;

                    if (data.is_in_wishlist) {
                        // STATE: SAVED
                        this.dataset.isActive = 'true';
                        wishlistIcon.className = 'fas fa-heart';
                        wishlistText.innerText = 'Saved to Bucket List';
                        
                        this.style.background = '#fef2f2';
                        this.style.borderColor = '#ef4444';
                        this.style.color = '#ef4444';
                        
                        if(visitedControls) visitedControls.style.display = 'block';
                    } else {
                        // STATE: REMOVED
                        this.dataset.isActive = 'false';
                        wishlistIcon.className = 'far fa-heart';
                        wishlistText.innerText = 'Add to Bucket List';
                        
                        this.style.background = 'white';
                        this.style.borderColor = '#cbd5e1';
                        this.style.color = '#64748b';

                        if(visitedControls) visitedControls.style.display = 'none';
                        
                        // Reset visited buttons state for next time
                        if(markVisitedBtn) markVisitedBtn.style.display = 'flex';
                        if(unmarkVisitedBtn) unmarkVisitedBtn.style.display = 'none';
                    }
                } else {
                    wishlistIcon.className = originalIconClass; // Revert on error
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.disabled = false;
                this.style.opacity = '1';
                wishlistIcon.className = originalIconClass;
            });
        });

        // 2. Handle Visited / Un-visited Toggles
        document.querySelectorAll('.visited-toggle-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetStatus = this.dataset.target; // 'done' or 'wished'
                const url = sidebarWishlistBtn.dataset.url; 

                // Visual Feedback
                const originalContent = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                this.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ action: 'toggle_done', status_target: targetStatus })
                })
                .then(res => res.json())
                .then(data => {
                    this.disabled = false;
                    this.innerHTML = originalContent; 

                    if(data.success) {
                        // UPDATE STATS
                        if (data.bucket_count !== undefined) document.getElementById('miniBucketCount').innerText = data.bucket_count;
                        if (data.visited_count !== undefined) document.getElementById('miniVisitedCount').innerText = data.visited_count;

                        if (data.is_done) {
                            if(markVisitedBtn) markVisitedBtn.style.display = 'none';
                            if(unmarkVisitedBtn) unmarkVisitedBtn.style.display = 'flex';
                        } else {
                            if(markVisitedBtn) markVisitedBtn.style.display = 'flex';
                            if(unmarkVisitedBtn) unmarkVisitedBtn.style.display = 'none';
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.disabled = false;
                    this.innerHTML = originalContent;
                });
            });
        });
    }
</script>
<script>
    function nativeShare() {
        if (navigator.share) {
            navigator.share({
                title: '{{ e($business->name) }}',
                text: 'Check out this place on Discover Kenya!',
                url: '{{ route('listings.show', $business->slug) }}'
            })
            .then(() => console.log('Successful share'))
            .catch((error) => console.log('Error sharing', error));
        } else {
            // Fallback for desktop browsers that don't support native share
            // We just trigger the copy function instead
            let btn = document.getElementById('nativeShareBtn');
            copyToClipboard('{{ route('listings.show', $business->slug) }}', btn);
        }
    }

    // 2. Copy to Clipboard Logic
   function copyToClipboard(text, btnElement) {
        navigator.clipboard.writeText(text).then(function() {
            // A. Button Visual Feedback (Turn Green)
            let originalIcon = btnElement.innerHTML;
            let originalBg = btnElement.style.background;
            
            btnElement.innerHTML = '<i class="fas fa-check"></i>';
            btnElement.style.background = '#22c55e'; // Green
            
            // Revert button after 2 seconds
            setTimeout(() => {
                btnElement.innerHTML = originalIcon;
                btnElement.style.background = originalBg; 
            }, 2000);

            // B. Trigger Premium Toast Notification
            let toast = document.getElementById("copyToast");
            toast.className = "show";
            
            // Hide Toast after 3 seconds
            setTimeout(function(){ 
                toast.className = toast.className.replace("show", ""); 
            }, 3000);
            
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
        });
    }
</script>

@endpush