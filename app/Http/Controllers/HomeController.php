<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\County;
use App\Models\Category;
use App\Models\Business;
use App\Models\DiscoveryCollection;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;


class HomeController extends Controller
{
    /**
     * Display the Home Page.
     * Loads only critical "Above the Fold" content.
     */
    public function index()
    {
        $cacheDuration = 10800; // 3 hours

        // --- 1. SEARCH BAR DATA ---
        $counties = Cache::remember('home_search_counties', $cacheDuration, function () {
            return \App\Models\County::select('id', 'name')->orderBy('name')->get();
        });
        
        $searchableCategories = Cache::remember('home_search_categories_v6', $cacheDuration, function () {
            // ❌ EXCLUDE Non-Tourism / Local Utility categories to boost "Travel Authority" signal
            $excludeSlugs = [
                'car-wash', 'car-repair', 'dentist', 'school', 'secondary-school', 
                'primary-school', 'business-services', 'real-estate', 'lawyer', 
                'hospital', 'medical-center', 'pharmacy', 'gas-station', 'garage',
                'atm', 'bank', 'laundry', 'carpenter', 'electrician'
            ];

            return \App\Models\Category::whereNotIn('slug', $excludeSlugs)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        });

        // --- 2. HERO SLIDER (Optimized) ---
        // CRITICAL CHANGE: We calculate image URLs INSIDE the cache closure.
        // This ensures we store the final string, preventing repeat processing on every request.
        $heroSliderBusinesses = Cache::remember('home_hero_slider_final_v3', $cacheDuration, function () {
            // A. Fetch Paid/Eligible
            $paidBusinesses = \App\Models\Business::eligibleForHeroSlider()
                ->with(['media', 'county'])
                ->inRandomOrder()
                ->take(10) 
                ->get();
            
            $neededToFill = 10 - $paidBusinesses->count();
            
            $finalCollection = $paidBusinesses;
            
            // B. Merge with Filler if needed
            if ($neededToFill > 0) {
                $excludeIds = $paidBusinesses->pluck('id')->all();
                
                // 1. First Filler: Try to find Verified businesses with Images
                $fillerBusinesses = \App\Models\Business::where('status', 'active')
                    ->where('is_verified', true)
                    ->whereNotIn('id', $excludeIds)
                    ->whereHas('media', function ($q) {
                        $q->where('collection_name', 'images');
                    })
                    ->with(['media', 'county'])
                    ->inRandomOrder()
                    ->take($neededToFill)
                    ->get();
                
                // 2. Emergency Backup: If we still don't have 10, pick ANY active business with images
                if (($paidBusinesses->count() + $fillerBusinesses->count()) < 10) {
                    $excludeIds = array_merge($excludeIds, $fillerBusinesses->pluck('id')->all());
                    $backupCount = 10 - ($paidBusinesses->count() + $fillerBusinesses->count());
                    
                    $extraFillers = \App\Models\Business::where('status', 'active')
                        ->whereNotIn('id', $excludeIds)
                        ->whereHas('media', function ($q) {
                            $q->where('collection_name', 'images');
                        })
                        ->with(['media', 'county'])
                        ->inRandomOrder()
                        ->take($backupCount)
                        ->get();
                    
                    $fillerBusinesses = $fillerBusinesses->merge($extraFillers);
                }
                
                $finalCollection = $paidBusinesses->merge($fillerBusinesses);
            }

            // D. FINAL SAFETY CHECK: If still empty (e.g. fresh DB), get any active businesses
            if ($finalCollection->isEmpty()) {
                 $finalCollection = \App\Models\Business::where('status', 'active')
                    ->has('media')
                    ->with(['media', 'county'])
                    ->take(5)
                    ->get();
            }

            // C. Calculate URLs NOW
            // This runs once every 3 hours. Subsequent page loads use the cached string.
            $finalCollection->each(function ($business) {
                $business->hero_image_url = $business->getImageUrl('hero');
                $business->hero_image_url_mobile = $business->getImageUrl('hero-mobile');
            });

            return $finalCollection;
        });

        // Extract LCP Data from the cached collection
        // We use the properties we just calculated/cached above
        $firstHeroBusiness = $heroSliderBusinesses->first();
        $lcpImageUrl = $firstHeroBusiness?->hero_image_url;
        $lcpImageUrlMobile = $firstHeroBusiness?->hero_image_url_mobile;

        // --- 3. DISCOVERY COLLECTIONS (Above Fold) ---
        // --- 3. DISCOVERY COLLECTIONS (Above Fold) ---
        $discoveryCards = Cache::remember('home_discovery_cards_v5', $cacheDuration, function () {
            return \App\Models\DiscoveryCollection::where('is_active', true)
                ->select('id', 'title', 'slug')
                ->withCount('businesses')
                ->with(['businesses' => function ($q) {
                     $q->select('id', 'slug', 'name')
                       ->with('media'); 
                }])
                ->latest()
                ->take(10) 
                ->get()
                ->map(function($collection) {
                    $cover = $collection->businesses->first();
                    $imageUrl = $cover ? $cover->getImageUrl('card') : asset('images/placeholder-card.jpg');
                    return [
                        'id' => $collection->id,
                        'title' => $collection->title,
                        'slug' => $collection->slug,
                        'businesses_count' => $collection->businesses_count,
                        'card_image_url' => $imageUrl, // Guaranteed to be present in JSON
                    ];
                });
        });





        // --- 4. POPULAR COUNTIES (Initial View) ---
        // We cache the FULL sorted list with images, then slice it for the view.
        $allPopularCounties = Cache::remember('all_popular_counties_sorted_v2', $cacheDuration, function () {
            $counties = \App\Models\County::withCount(['businesses' => fn($q) => $q->where('status', 'active')])
                ->having('businesses_count', '>', 0)
                ->orderBy('businesses_count', 'desc')
                ->get();
            
            // Pre-calculate images inside cache
            foreach ($counties as $county) {
                $randomBusiness = \App\Models\Business::where('county_id', $county->id)
                    ->where('status', 'active')
                    ->has('media')
                    ->inRandomOrder()
                    ->select('id', 'slug', 'county_id')
                    ->with('media')
                    ->first();
                
                $county->display_image_url = $randomBusiness 
                    ? $randomBusiness->getImageUrl('card') 
                    : asset('images/placeholder-county.jpg');
            }
            return $counties;
        });

        // Slice the first 12 for the initial view render
        $popularCounties = $allPopularCounties->take(12);

        // --- 5. SEO META KEYWORDS ---
        $seoKeywords = Cache::remember('home_seo_keywords', $cacheDuration, function () {
             $noiseSlugs = [
                 'establishment', 'point-of-interest', 'tourist-attraction', 'food', 'health', 
                 'lodging', 'locality', 'political', 'store', 'premise', 'school', 'place-of-worship', 
                 'hotels', 'hotel', 'restaurants', 'restaurant', 'resorts', 'resort', 'accommodation', 'accommodations', 'stay', 'stays'
             ];
             
             return \App\Models\Category::whereNotIn('slug', $noiseSlugs)
                ->withCount('businesses')
                ->orderBy('businesses_count', 'desc')
                ->take(15)
                ->pluck('name')
                ->implode(', ');
        });

        return view('home', compact(
            'heroSliderBusinesses',
            'discoveryCards',
            'popularCounties',
            'seoKeywords',
            'counties',
            'searchableCategories',
            'lcpImageUrl',
            'lcpImageUrlMobile',
            'firstHeroBusiness'
        ));
    }


    public function debugLcp()
    {
        // 1. CACHE THE RESULT (1 Hour)
        // This ensures the Database is not touched on refresh.
        $data = Cache::remember('debug_lcp_optimized', 3600, function () {
            
            // A. FAST QUERY: Select only what is strictly needed.
            // Avoid 'select *' to prevent loading unused heavy text/json columns.
            // Hardcode ID 2187 since this is a benchmark page.
            $business = \App\Models\Business::select('id', 'slug', 'status')
                ->with(['media']) // Ensure media is loaded
                ->find(2187); 

            // Fallback if 2187 is deleted
            if (!$business) {
                $business = \App\Models\Business::select('id', 'slug', 'status')
                    ->where('status', 'active')
                    ->has('media')
                    ->with('media')
                    ->first();
            }

            // B. PRE-CALCULATE STRINGS
            // We generate the URL strings here so the View doesn't have to do any logic.
            return [
                'id'          => $business->id,
                'desktop_url' => $business->getImageUrl('hero'),
                'mobile_url'  => $business->getImageUrl('hero-mobile'),
            ];
        });

        // 2. RETURN VIEW
        return view('debug-lcp', $data);
    }

    /**
     * Handle AJAX requests for Lazy Loaded sections.
     */
    public function fetchHomeSection(Request $request)
    {
        // REMOVED strict ajax check to prevent 400 errors with fetch API
        $section = $request->input('section');
        $cacheDuration = 10800; // 3 hours

       // --- A. POPULAR COUNTIES (AJAX Load More) ---
        if ($section === 'popular-counties') {
            $page = (int) $request->input('page', 1);
            $perPage = 12; // Matches your UI Strategy C (Mobile/Desktop friendly)

            // We use the same cache key as index() ('all_popular_counties_sorted') 
            // so we don't query the DB again if the homepage just loaded.
            $allCounties = Cache::remember('all_popular_counties_sorted', $cacheDuration, function () {
                $counties = \App\Models\County::withCount(['businesses' => fn($q) => $q->where('status', 'active')])
                    ->having('businesses_count', '>', 0)
                    ->orderBy('businesses_count', 'desc')
                    ->get(); // Get ALL counties (removed take(30))
                
                // Map images
                foreach ($counties as $county) {
                    $randomBusiness = \App\Models\Business::where('county_id', $county->id)
                        ->where('status', 'active')
                        ->has('media')
                        ->inRandomOrder()
                        ->select('id', 'slug', 'county_id')
                        ->with('media')
                        ->first();
                    
                    $county->display_image_url = $randomBusiness 
                        ? $randomBusiness->getImageUrl('card') 
                        : asset('images/placeholder-county.jpg');
                }
                return $counties;
            });

            // Logic: Slice the collection for the requested page
            // e.g. Page 2 gets items 12-24
            $pagedData = $allCounties->slice(($page - 1) * $perPage, $perPage);
            
            // Render just the cards (no wrapper div)
            $html = view('partials.home-counties-loop', ['counties' => $pagedData])->render();
            
            // Serialize counties to plain arrays for React
            $countiesData = $pagedData->map(function($county) {
                return [
                    'id' => $county->id,
                    'name' => $county->name,
                    'slug' => $county->slug,
                    'businesses_count' => $county->businesses_count,
                    'display_image_url' => $county->display_image_url,
                ];
            })->values()->toArray();
            
            // Return JSON so JS can append it
            return response()->json([
                'html' => $html,
                'counties' => $countiesData,
                'hasMore' => $allCounties->count() > ($page * $perPage)
            ]);
        }



        // --- B. TRENDING ---
        if ($section === 'trending') {
            $businesses = Cache::remember('home_top_destinations', $cacheDuration, function () {
                return \App\Models\Business::where('status', 'active')
                    ->has('media')
                    ->with(['county', 'media'])
                    ->orderBy('is_featured', 'desc')
                    ->orderBy('views_count', 'desc')
                    ->take(16)->get();
            });

            if (request()->wantsJson() || request()->has('inertia')) {
                $businesses->each(function($b) { $b->card_image_url = $b->getImageUrl('card'); });
                return response()->json($businesses);
            }

            // Uses your existing: resources/views/partials/home-section-cards.blade.php
            return view('partials.home-section-cards', compact('businesses'));
        }


        // --- C. NEW ARRIVALS ---
        if ($section === 'new-arrivals') {
            $businesses = Cache::remember('home_recent_places', $cacheDuration, function () {
                return \App\Models\Business::where('status', 'active')
                    ->has('media')
                    ->with(['county', 'media'])
                    ->orderBy('created_at', 'desc')
                    ->take(16)->get();
            });

            if (request()->wantsJson() || request()->has('inertia')) {
                $businesses->each(function($b) { $b->card_image_url = $b->getImageUrl('card'); });
                return response()->json($businesses);
            }

            return view('partials.home-section-cards', compact('businesses'));
        }


        // --- D. HIDDEN GEMS ---
        if ($section === 'hidden-gems') {
            $businesses = Cache::remember('home_hidden_gems', $cacheDuration, function () {
                return \App\Models\Business::where('status', 'active')
                    ->has('media')
                    ->with(['county', 'media'])
                    ->orderBy('views_count', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->take(12)->get();
            });

            if (request()->wantsJson() || request()->has('inertia')) {
                $businesses->each(function($b) { $b->card_image_url = $b->getImageUrl('card'); });
                return response()->json($businesses);
            }

            return view('partials.home-section-cards', compact('businesses'));
        }


        return response()->noContent();
    }
    /**
     * AJAX Endpoint for Search Suggestions.
     * Returns lightweight JSON.
     */
    public function suggestions()
    {
        $cacheDuration = 10800; // 3 hours

        // 1. Activities (Categories)
        $activities = Cache::remember('search_activities_json', $cacheDuration, function () {
            return Category::select('name', 'icon_class')->get()->map(fn($c) => [
                'n' => $c->name,
                'i' => $c->icon_class ?? 'fas fa-search'
            ]);
        });

        // 2. Collections
        $collections = Cache::remember('search_collections_json', $cacheDuration, function () {
            return DiscoveryCollection::select('title', 'slug')->get()->map(fn($c) => [
                't' => $c->title,
                's' => $c->slug // Return Slug
            ]);
        });

        // 3. Posts
        $posts = Cache::remember('search_posts_json', $cacheDuration, function () {
           if (class_exists('App\Models\Post')) {
                return \App\Models\Post::latest()->take(5)->select('title', 'slug')->get()->map(fn($p) => [
                    't' => $p->title,
                    's' => $p->slug // Return Slug
                ]);
           }
           return [];
        });

        // 4. Counties
        $counties = Cache::remember('search_counties_json', $cacheDuration, function () {
            return County::select('name', 'slug')->get()->map(fn($c) => [
                'n' => $c->name,
                's' => $c->slug
            ]);
        });

        // 5. Businesses (Popular listings prioritized for autocomplete)
        $businesses = Cache::remember('search_businesses_json_v2', $cacheDuration, function () {
            return \App\Models\Business::where('status', 'active')
                ->select('id', 'name', 'slug', 'views_count')
                ->orderBy('views_count', 'desc') // Most viewed first
                ->orderBy('name', 'asc')         // Then alphabetical
                ->take(1000)                     // Increased from 100 to cover more businesses
                ->get()
                ->map(fn($b) => [
                    'id' => $b->id,
                    'n' => $b->name,
                    's' => $b->slug
                ]);
        });

        return response()->json([
            'activities' => $activities,
            'collections' => $collections,
            'posts' => $posts,
            'counties' => $counties,
            'businesses' => $businesses
        ]);
    }
}