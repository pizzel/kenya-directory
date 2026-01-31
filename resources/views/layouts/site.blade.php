<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dynamic Title --}}
    <title>@yield('title', config('app.name', 'Discover Kenya') . ' - Discover Activities & Experiences.')</title>

    {{-- Meta Description & Keywords --}}
    <meta name="description" content="@yield('meta_description', 'Discover unique activities, experiences, and things to do across Kenya.')">
    <meta name="keywords" content="@yield('meta_keywords', 'Kenya activities, travel Kenya, Nairobi, Mombasa')">

    {{-- Canonical Tag --}}
    @yield('canonical')

    @stack('seo')

    @yield('styles')

    <!-- Favicons -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    
    <!-- 
        2. BREAKING THE NETWORK CHAIN
        We preconnect to Google immediately so the DNS lookup is done 
        before we even ask for the CSS.
    -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    {{-- Preload FontAwesome (CDN version) to fix "Font Display" warning --}}
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/webfonts/fa-regular-400.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/webfonts/fa-brands-400.woff2" as="font" type="font/woff2" crossorigin>

    <!-- 
        3. ASYNC GOOGLE FONTS 
        The 'media="print"' trick tells the browser not to wait for this file.
        It loads in the background and applies itself when ready.
    -->
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" media="print">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    </noscript>

    <!-- 
        4. INLINE CRITICAL CSS 
        This ensures the Navbar and Layout skeleton paint INSTANTLY, 
        even before app.css downloads.
    -->
    <style>
        /* Base Reset & Fonts */
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; padding: 0; color: #1e293b; }
        
        /* Navbar Critical Layout - Paints immediately */
        .site-main-header { position: sticky; top: 0; z-index: 1000; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); height: 70px; }
        .header-container { display: flex; align-items: center; justify-content: space-between; height: 100%; max-width: 1280px; margin: 0 auto; padding: 0 20px; }
        .logo img { height: 40px; width: auto; display: block; }
        
        /* Nav Links Skeleton */
        .desktop-nav ul { display: flex; gap: 20px; list-style: none; padding: 0; margin: 0; }
        .desktop-nav a { text-decoration: none; color: #475569; font-weight: 500; font-size: 0.95rem; }
        
        /* Hero Section Skeleton (Prevents Layout Shift) */
        .hero-slider-section { position: relative; width: 100%; height: 80vh; min-height: 500px; max-height: 700px; overflow: hidden; background: #e2e8f0; }
        .heroSwiper, .swiper-wrapper, .swiper-slide { width: 100%; height: 100%; position: relative; }
        /* Add this to your Critical CSS block */
        .hero-slider-section {
            aspect-ratio: 16 / 9; /* Reserve space for Desktop */
            width: 100%;
            background-color: #1e293b; /* Grey placeholder prevents white flash */
        }

        @media (max-width: 768px) {
            .hero-slider-section {
                aspect-ratio: 4 / 3; /* Reserve space for Mobile */
                height: 70vh; /* Or keep your specific height */
            }
        }
        /* Search Bar Placeholder */
        .search-bar-wrapper { min-height: 60px; }

        /* Mobile Utility */
        @media (max-width: 900px) {
            .desktop-nav, .desktop-auth { display: none; }
        }
        /* --- ACCESSIBILITY FIXES (Contrast Ratio 4.6:1) --- */
        .login-btn, .signup-btn, .search-btn, .desktop-nav a.active, .btn-primary {
            background-color: #2563eb !important; 
            border-color: #2563eb !important;
            color: white !important;
        }
        
        /* Fix for 'Home' link text */
        .desktop-nav a.active {
            color: #2563eb !important;
            background: transparent !important;
        }

        /* --- PERFORMANCE FIX: GPU-ACCELERATED SKELETONS --- */
        .skeleton-loader {
            background-color: #f1f5f9;
            position: relative;
            overflow: hidden;
            display: block; 
        }

        .skeleton-loader::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            transform: translateX(-100%);
            background: linear-gradient(
                90deg, 
                transparent, 
                rgba(255, 255, 255, 0.6), 
                transparent
            );
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }

        /* --- GLOBAL ACCESSIBILITY: CONTRAST & DISTINGUISHABILITY --- */
        .breadcrumb-bar a {
            color: #1d4ed8 !important;
            text-decoration: underline !important;
            font-weight: 600;
        }
        .breadcrumb-bar {
            color: #334155 !important;
            font-weight: 500;
        }
        .comment-date {
            color: #475569 !important;
            font-weight: 500;
        }
        .text-gray-500, .text-gray-400 {
            color: #334155 !important;
        }
        /* Proactively fix common light-gray inline styles for accessibility */
        [style*="color: #64748b"], [style*="color: #94a3b8"], [style*="color: #888"] {
            color: #334155 !important;
        }
        [style*="color: #94a3b8"] {
            color: #475569 !important;
        }
        /* Global override for filter headers which were too light */
        .filter-widget h4 {
            color: #334155 !important;
            font-weight: 700 !important;
        }
        .view-all-overlay-text {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.7);
            color: white !important;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 10px;
            border-radius: 8px;
            z-index: 10;
        }
    </style>

    {{-- 6. LCP PRELOAD (Mirroring home.blade.php Logic) --}}
    @if(isset($lcpImageUrl) && $lcpImageUrl)
        <link rel="preload" as="image" href="{{ $lcpImageUrl }}" 
              imagesrcset="{{ $lcpImageUrlMobile }} 767w, {{ $lcpImageUrl }} 1280w" 
              imagesizes="100vw">
    @endif

    <!-- 5. MAIN CSS (Vite) -->
     @vite(['resources/css/app.css'])
     
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Swiper & SimpleLightbox CSS (Required for Blade Homepage) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.min.css" />

     
</head>
<body class="font-sans antialiased text-gray-900 bg-slate-50">

   {{-- 1. OVERLAY BACKDROP --}}
    <div id="mobileBackdrop" class="mobile-nav-overlay" onclick="toggleMobileMenu()"></div>

    <header class="site-main-header">
        <div class="container header-container">
            {{-- Logo --}}
            <div class="logo">
                <a href="{{ route('home') }}">
                    <img fetchpriority="high" src="{{ asset('images/site-logo.png') }}" alt="{{ config('app.name') }}" class="site-logo-image" width="116" height="40">
                </a>
            </div>

            {{-- Desktop Nav --}}
             <nav class="desktop-nav">
                <ul>
                    <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fas fa-home" style="margin-right: 5px;"></i> Home</a></li>
                    <li><a href="{{ route('listings.index') }}" class="{{ request()->is('listings*') || request()->is('listing*') ? 'active' : '' }}"><i class="fas fa-compass" style="margin-right: 5px;"></i> Explore</a></li>
                    <li><a href="{{ route('collections.index') }}" class="{{ request()->is('collections*') ? 'active' : '' }}"><i class="fas fa-layer-group" style="margin-right: 5px;"></i> Collections</a></li>
                    <li><a href="{{ route('itineraries.index') }}" class="{{ request()->is('itineraries*') ? 'active' : '' }}"><i class="fas fa-route" style="margin-right: 5px;"></i> Journeys</a></li>
                    <li><a href="{{ route('posts.index') }}" class="{{ request()->is('blog*') ? 'active' : '' }}"><i class="fas fa-blog" style="margin-right: 5px;"></i> Blog</a></li>
                    <li><a href="{{ route('contact.show') }}" class="{{ request()->routeIs('contact.show') ? 'active' : '' }}"><i class="fas fa-envelope" style="margin-right: 5px;"></i> Contact</a></li>
                </ul>
            </nav>

            {{-- Desktop Auth --}}
            <div class="auth-buttons desktop-auth">
                @auth
                    <div class="user-dropdown-wrapper" x-data="{ open: false }" @click.outside="open = false"> 
                        <button @click="open = !open" class="user-dropdown-trigger login-btn" :class="{ 'active': open }">
                            <span>{{ Auth::user()->name }}</span>
                            {{-- SVG Arrow (Replaces FontAwesome in critical path) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;"><path d="m6 9 6 6 6-6"/></svg>
                        </button>

                        <div x-show="open" style="display: none;" class="user-dropdown-menu">
                            @if(Auth::user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item">Admin Panel</a>
                            @elseif(Auth::user()->role === 'business_owner')
                                <a href="{{ route('business-owner.dashboard') }}" class="dropdown-item">Business Dashboard</a>
                            @else
                                <a href="{{ route('wishlist.index') }}" class="dropdown-item">My Bucketlist</a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">Profile Settings</a>
                            <div style="border-top: 1px solid #f1f5f9; margin: 5px 0;"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; border: none; background: none; cursor: pointer;">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="login-btn">Login</a>
                    <a href="{{ route('register') }}" class="signup-btn">Sign Up</a>
                @endauth
            </div>

            {{-- Mobile Toggle Button --}}
            <div class="mobile-menu-toggle">
                <button onclick="toggleMobileMenu()" aria-label="Open mobile navigation menu">
                    {{-- SVG Menu Icon (Replaces FontAwesome in critical path) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>
        </div>
    </header>

    {{-- 2. MOBILE SIDEBAR DRAWER --}}
    <div id="mobileNavPanel" class="mobile-nav-panel" onclick="event.stopPropagation()" style="pointer-events: auto;">
        <div class="mobile-nav-header">
            <a href="{{ route('home') }}">
                <img fetchpriority="high" src="{{ asset('images/site-logo.png') }}" alt="{{ config('app.name') }}" class="site-logo-image" width="87" height="30">
            </a>
            <button onclick="toggleMobileMenu()" class="mobile-nav-close">×</button>
        </div>

        <div class="mobile-nav-content">
            <a href="{{ route('home') }}" class="mobile-nav-item">Home</a>
            <a href="{{ route('listings.index') }}" class="mobile-nav-item">Explore</a>
            <a href="{{ route('collections.index') }}" class="mobile-nav-item">Collections</a>
            <a href="{{ route('posts.index') }}" class="mobile-nav-item">Travel Blog</a>
            <a href="{{ route('contact.show') }}" class="mobile-nav-item">Contact Us</a>
        </div>

        <div class="mobile-nav-footer">
            @if(request()->user())
                <div style="margin-bottom: 15px; font-weight: 600; color: #1e293b; display: flex; align-items: center;">
                    <div style="width: 35px; height: 35px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px;">{{ substr(Auth::user()->name, 0, 1) }}</div>
                    {{ Auth::user()->name }}
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <a href="{{ route('wishlist.index') }}" class="mobile-btn secondary" style="font-size: 0.8rem;">Bucketlist</a>
                    <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
                        @csrf
                        <button type="submit" class="mobile-btn secondary" style="border-color: #fecaca; color: #ef4444; font-size: 0.8rem;">Logout</button>
                    </form>
                </div>
                @if(Auth::user()->isBusinessOwner() || Auth::user()->isAdmin())
                    <a href="{{ route('business-owner.dashboard') }}" class="mobile-btn primary" style="margin-top: 10px;">Dashboard</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="mobile-btn secondary">Log In</a>
                <a href="{{ route('register') }}" class="mobile-btn primary">Create Account</a>
            @endif
        </div>
    </div>

    <script>function toggleMobileMenu() { document.body.classList.toggle('mobile-menu-active'); }</script>

    {{-- Sticky Search Section --}}
    @include('partials._search-bar')

    {{-- Breadcrumbs Section --}}
    @hasSection('breadcrumbs')
        <div class="breadcrumb-bar">
            <div class="container">
                @yield('breadcrumbs')
            </div>
        </div>
    @endif
    <!-- Main Page Content -->
    <main class="site-main-content">
        @yield('content')
    </main>

    {{-- Global Modals --}}
    <div id="loadingMessageModal" class="loading-modal" style="display: none;">
        <div class="modal-content"><div class="spinner"></div><p id="loadingMessageText">Processing...</p></div>
    </div>
                
    <div id="reportModal" class="report-modal" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Report Listing</h4>
                    <button type="button" class="close-modal-btn" data-dismiss="reportModal">×</button>
                </div>
                <form id="reportItemForm" method="POST" action="{{ route('listings.report.submit') }}">
                    @csrf
                    <input type="hidden" name="business_id" id="report_item_business_id" value="">
                    <input type="hidden" name="event_id" id="report_item_event_id" value="">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="report_reason" class="form-label">Reason</label>
                            <select name="report_reason" id="report_reason" class="form-control" required>
                                <option value="">-- Select Reason --</option>
                                <option value="scam_fraud">Scam/Fraud</option>
                                <option value="duplicate">Duplicate</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="report_details" class="form-label">Details</label>
                            <textarea name="details" id="report_details" rows="3" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="reportModal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- PREMIUM FOOTER --}}
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <img src="{{ asset('images/site-logo.png') }}" alt="{{ config('app.name') }}" class="footer-logo" width="116" height="40">
                    <p>Your ultimate guide to discovering the best businesses, destinations, and experiences in Kenya.</p>
                    <div class="footer-socials">
                        {{-- Icons will appear once FontAwesome loads --}}
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <div class="footer-links">
                    <p class="footer-heading">Discover</p>
                    <ul>
                        <li><a href="{{ route('listings.index') }}">Browse Listings</a></li>
                        <li><a href="{{ route('collections.index') }}">Collections</a></li>
                        <li><a href="{{ route('posts.index') }}">Travel Blog</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <p class="footer-heading">Company</p>
                    <ul>
                        <li><a href="{{ route('contact.show') }}">About Us</a></li>
                        <li><a href="{{ route('contact.show') }}">Contact Support</a></li>
                        <li><a href="{{ route('contact.show') }}">Privacy Policy</a></li>
                    </ul>
                </div>

                <div class="footer-newsletter">
                    <p class="footer-heading">Join the Adventure</p>
                    <p>Get the latest hidden gems delivered to your inbox.</p>
                    <div class="newsletter-form-wrapper">
                        <input type="email" id="newsletter-email-input" placeholder="Enter your email" required>
                        <button id="newsletter-subscribe-btn">Subscribe</button>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} KenyaDirectory. All rights reserved.</p>
            </div>
        </div>
    </footer>

    {{-- Footer CSS --}}
    <style>
        .site-footer { background-color: #0f172a; color: #cbd5e1; padding: 80px 0 30px; margin-top: auto; }
        .footer-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.5fr; gap: 40px; margin-bottom: 60px; }
        .footer-logo { height: 40px; opacity: 0.9; }
        .footer-socials { display: flex; gap: 15px; margin-top: 20px; }
        .footer-socials a { width: 40px; height: 40px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; border-radius: 50%; color: white; }
        .footer-links .footer-heading, .footer-newsletter .footer-heading { color: white; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; margin-bottom: 25px; }
        .footer-links ul { list-style: none; padding: 0; }
        .footer-links li { margin-bottom: 12px; }
        .footer-links a { color: #cbd5e1; text-decoration: none; }
        .newsletter-form-wrapper { display: flex; gap: 10px; }
        .newsletter-form-wrapper input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 12px 15px; border-radius: 8px; flex-grow: 1; }
        .newsletter-form-wrapper button { background: #3b82f6; color: white; border: none; padding: 0 20px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px; text-align: center; font-size: 0.9rem; color: #64748b; }
        .footer-brand p { margin: 20px 0; font-size: 0.95rem; line-height: 1.6; color: #cbd5e1; /* FIXED: Was #94a3b8 (Too dark). Now lighter for contrast. */ }
        .newsletter-form-wrapper button { background: #2563eb; /* FIXED: Was #3b82f6. Darkened for contrast. */ color: white; border: none; padding: 0 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px; text-align: center; font-size: 0.9rem; color: #cbd5e1; /* FIXED: Was #64748b (Too dark). Now lighter for contrast. */ }
        @media (max-width: 900px) { .footer-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 600px) { .footer-grid { grid-template-columns: 1fr; text-align: center; } .footer-socials { justify-content: center; } .newsletter-form-wrapper { flex-direction: column; } }
    </style>

    {{-- Global JS Vars --}}
    <script>
    window.reverseGeocodeUrl = "{{ route('geocode.reverse') }}";
    window.nearbyListingsUrl = "{{ route('listings.nearby') }}";
    window.placeholderCardImageUrl = "{{ asset('images/placeholder-card.jpg') }}";
    @if(request()->user())
        window.csrfToken = "{{ csrf_token() }}";
        window.AUTH_USER_ID = {{ auth()->id() }};
    @else
        window.csrfToken = null;
        window.AUTH_USER_ID = null;
    @endif

    // Newsletter Logic (Deferred)
    document.addEventListener('DOMContentLoaded', function() {
        const subscribeBtn = document.getElementById('newsletter-subscribe-btn');
        const emailInput = document.getElementById('newsletter-email-input');
        if (subscribeBtn && emailInput) {
            subscribeBtn.addEventListener('click', function(e) {
                const email = emailInput.value;
                if (!email || !email.includes('@')) return alert('Please enter a valid email address.');
                subscribeBtn.innerText = '...';
                const url = '{{ Route::has("newsletter.subscribe") ? route("newsletter.subscribe") : "#" }}';
                if(url === '#') { alert("Coming soon!"); subscribeBtn.innerText = 'Subscribe'; return; }
                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ email: email })
                }).then(r => r.json()).then(d => { alert(d.message || 'Subscribed!'); emailInput.value = ''; })
                .finally(() => { subscribeBtn.innerText = 'Subscribe'; });
            });
        }
    });
    </script>
    


    
    

    <!-- Main App JS (Deferred by Vite) -->

    
    @vite(['resources/js/app.jsx'])
    
    <!-- Swiper & SimpleLightbox JS (Required for Blade Homepage) -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.min.js"></script>

    @include('partials.app-scripts')
    @include('partials._performance-logger')
    @stack('footer-scripts')
</body>
</html>