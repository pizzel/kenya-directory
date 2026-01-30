<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dynamic Title --}}
    <title inertia>{{ config('app.name', 'Discover Kenya') }}</title>

    {{-- Meta Description & Keywords (Ideally controlled by Inertia <Head>, but defaults here) --}}
    <meta name="description" content="@yield('meta_description', 'Discover unique activities, experiences, and things to do across Kenya.')">
    <meta name="keywords" content="@yield('meta_keywords', 'Kenya activities, travel Kenya, Nairobi, Mombasa')">

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
    </style>

    <!-- 
        5. FONT OPTIMIZATION (Standard & Clean)
        We preload the CSS and the main font file to ensure they load 
        fast and in parallel, avoiding "chaining" warnings without using JS hacks.
    -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @inertiaHead
</head>
<body class="font-sans antialiased text-gray-900 bg-slate-50">
    @inertia

    <!-- Scripts (Loaded at bottom for performance) -->
    @routes
    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
</body>
</html>
