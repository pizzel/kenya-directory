<!DOCTYPE html>
<html lang="en">
    <title>Benchmark: Production Assets (Deferred JS)</title>
    
    <!-- Optimized Font Loading (Parallel) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Production CSS (Main) -->
    <link rel="stylesheet" href="/build/assets/app-CIJpX59o.css">
    <!-- Production CSS (Chunk from JS imports like Swiper) -->
    <link rel="stylesheet" href="/build/assets/app-BUWSi_Oh.css">

    <!-- CSS Helpers (Custom Classes not in Tailwind) -->
    <style>
        .hero-section { position: relative; width: 100%; height: 80vh; max-height: 800px; overflow: hidden; }
        @media (min-width: 768px) { .hero-section { height: 90vh; } }
        .overlay { background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.4), transparent); }
        .content-wrapper { 
            display: flex; flex-direction: column; align-items: center; justify-content: flex-end;
            text-align: center; color: white; z-index: 10; padding: 1.5rem; padding-bottom: 4rem;
        }
        .btn { 
            padding: 0.75rem 2rem; background-color: #2563eb; color: white; border-radius: 9999px; 
            font-weight: 700; font-size: 1.125rem; text-decoration: none; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .county-tag { font-size: 1.125rem; font-weight: 500; margin-bottom: 1.5rem; color: #facc15; display: flex; align-items: center; gap: 0.5rem; }
    </style>
</head>
<body class="antialiased">

    <!-- 
        BENCHMARK VERSION 7.0 (Production Simulation)
        - CSS: /build/assets/... (Real Compilation)
        - JS: /build/assets/... (Deferred at bottom)
        - Fonts: Optimized
    -->

    <main>
        @if(isset($heroSliderBusinesses) && $heroSliderBusinesses->isNotEmpty())
            @php $business = $heroSliderBusinesses->first(); @endphp
            
            <section class="hero-section relative">
                <!-- Single Static Slide (Simulating the first slide of Swiper) -->
                <div class="relative w-full h-full">
                    <picture class="absolute inset-0 w-full h-full">
                        <source media="(max-width: 767px)" srcset="{{ $business->hero_image_url_mobile }}">
                        <source media="(min-width: 768px)" srcset="{{ $business->hero_image_url }}">
                        
                        <!-- LCP Candidate -->
                        <img 
                            src="{{ $business->hero_image_url }}" 
                            alt="{{ $business->name }}" 
                            class="w-full h-full object-cover"
                            fetchpriority="high"
                            loading="eager"
                            decoding="sync"
                        >
                    </picture>
                    
                    <!-- Overlay Gradient -->
                    <div class="absolute inset-0 overlay"></div>

                    <!-- Text Content -->
                    <div class="absolute inset-0 w-full h-full content-wrapper">
                        <h1>
                            {{ \Illuminate\Support\Str::limit($business->name, 50) }}
                        </h1>
                        
                        @if($business->county)
                            <p class="county-tag">
                                <span>📍 {{ $business->county->name }}</span>
                            </p>
                        @endif
                        
                        <div>
                            <a href="#" class="btn">
                                Discover More
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @else
            <div class="h-screen flex items-center justify-center">
                <p>No Benchmark Data Available</p>
            </div>
        @endif

        <div class="container mx-auto px-4 py-12">
            <h2 class="text-2xl font-bold mb-4">Benchmark Notes</h2>
            <p class="mb-2">This page represents the <strong>Absolute Minimum</strong> performance baseline.</p>
            <ul class="list-disc pl-5 space-y-1">
                <li>No JavaScript (Alpine/Swiper/JQuery removed)</li>
                <li>No FontAwesome Icon Fonts</li>
                <li>No Navigation Bar or Footer</li>
                <li>Only 1 Hero Image (Optimized LCP)</li>
                <li>Production CSS (Linked from /build/assets/)</li>
                <li>Deferred JS (Loaded at bottom with defer)</li>
            </ul>
        </div>
    </main>

    <!-- Defer loading of Main JS Bundle -->
    <script src="/build/assets/app-CmQ7I27I.js" defer></script>
</body>
</html>
