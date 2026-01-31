<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\County;
use App\Models\Category;
use App\Models\Image as BusinessImage;
use App\Models\Schedule;
use App\Models\Tag;
use App\Models\Facility;
use Illuminate\Http\Request; // <<< ADD THIS IMPORT
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\SemanticSEOService;

class PublicBusinessController extends Controller
{
    protected $seoService;

    public function __construct(SemanticSEOService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Display the specified public business listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $businessSlug
     * @return \Illuminate\View\View
     */
    /**
     * Display the specified public business listing.
     * UPDATED: Weighted Relevance for "Similar Listings"
     */
     /**
     * Display the specified public business listing.
     * OPTIMIZED: Removed blocking similarity algorithm - loads via AJAX
     */
     public function show(Request $request, string $businessSlug) 
    {
        // 1. Check if trashed (410 Gone)
        $trashed = Business::onlyTrashed()->where('slug', $businessSlug)->first();
        if ($trashed) {
            abort(410);
        }

        // 2. Fetch critical above-the-fold data only
        // We use 'select' to reduce database load
        $business = Business::where('slug', $businessSlug)
            ->where('status', 'active')
            ->with(['county', 'categories', 'tags', 'media', 'schedules', 'facilities', 'reviews' => fn($q) => $q->where('is_approved', true), 'googleReviews'])
            ->firstOrFail();

        // 3. Prepare schedule data
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $formattedSchedules = []; 
        foreach ($daysOfWeek as $day) {
            $schedule = $business->schedules->firstWhere('day_of_week', $day);
            $formattedSchedules[$day] = [
                'open' => $schedule && !$schedule->is_closed_all_day && $schedule->open_time ? date('g:i A', strtotime($schedule->open_time)) : ($schedule && $schedule->is_closed_all_day ? 'Closed' : 'Not Set'),
                'close' => $schedule && !$schedule->is_closed_all_day && $schedule->close_time ? date('g:i A', strtotime($schedule->close_time)) : '',
                'notes' => $schedule->notes ?? '',
            ];
        }

        // 4. Increment view count (with session guard)
        $sessionKey = 'viewed_business_' . $business->id;
        if (!$request->session()->has($sessionKey)) {
            $business->increment('views_count');
            $request->session()->put($sessionKey, true);
        }

        // 5. LCP OPTIMIZATION - Pre-calculate image URLs
        // Cache these for 1 week to avoid repetitive media library queries
        $cacheKey = "business_images_{$business->id}_v8";
        $imageData = Cache::remember($cacheKey, 604800, function() use ($business) {
            $galleryImages = $business->getMedia('images');
            
            // Main LCP image (full size for quality)
            $firstImage = $galleryImages->first();
            $lcpImageUrl = $firstImage?->getUrl(); 

            // Mobile: Card size (400x300)
            $lcpImageUrlMobile = $firstImage && $firstImage->hasGeneratedConversion('card') 
                ? $firstImage->getUrl('card') 
                : $lcpImageUrl; // Fallback to desktop if no card conversion

            // Smart Thumbnail Extraction with Fallback
            $getSmartUrl = function($index) use ($galleryImages) {
                $media = $galleryImages->get($index);
                if (!$media) return null;

                // Priority: thumbnail -> card -> original
                if ($media->hasGeneratedConversion('thumbnail')) {
                    return $media->getUrl('thumbnail');
                }
                if ($media->hasGeneratedConversion('card')) {
                    return $media->getUrl('card');
                }
                return $media->getUrl(); // Fallback to full size
            };

            return [
                'lcp_url' => $lcpImageUrl,
                'lcp_url_mobile' => $lcpImageUrlMobile,
                'thumbnail_1' => $getSmartUrl(1),
                'thumbnail_2' => $getSmartUrl(2),
                'thumbnail_3' => $getSmartUrl(3),
                'gallery_images' => $galleryImages
            ];
        });

        $galleryImages = $imageData['gallery_images'];
        $lcpImageUrl = $imageData['lcp_url'];
        $lcpImageUrlMobile = $imageData['lcp_url_mobile'];
        $thumbnail1Url = $imageData['thumbnail_1'];
        $thumbnail2Url = $imageData['thumbnail_2'];
        $thumbnail3Url = $imageData['thumbnail_3'];

        // 7. Generate Semantic SEO Data
        $businessSchema = $this->seoService->generateBusinessSchema($business);
        $contextSummary = $this->seoService->generateContextSummary($business);

        // 6. Return view WITHOUT similarListings (loads via AJAX now)
        return view('listings.show', compact(
            'business', 
            'galleryImages', 
            'formattedSchedules', 
            'daysOfWeek', 
            'lcpImageUrl', 
            'lcpImageUrlMobile',
            'thumbnail1Url',
            'thumbnail2Url',
            'thumbnail3Url',
            'businessSchema',
            'contextSummary'
        ));
    }


    /**
     * AJAX Endpoint: Fetch Similar Listings
     * Runs the complex similarity algorithm WITHOUT blocking the main page load
     */
    public function getSimilarListings(Request $request, string $businessSlug)
    {
        $business = Business::where('slug', $businessSlug)
            ->where('status', 'active')
            ->with(['categories'])
            ->firstOrFail();

        // Cache the result for 1 week per business
        $cacheKey = "similar_listings_{$business->id}_v3";
        $similarListings = Cache::remember($cacheKey, 604800, function() use ($business) {
            return $this->calculateSimilarListings($business);
        });

        // Return HTML partial
        return view('partials.similar-listings', compact('similarListings'));
    }

    /**
     * Private method: Calculate similar listings using rarity algorithm
     * This is the heavy computation that was removed from show()
     */
    private function calculateSimilarListings($business)
    {
        // 1. CALCULATE CATEGORY RARITY
        $categoryWeights = Cache::remember('category_weights_rarity_v1', 604800, function() {
            $totalBusinesses = Business::count();
            
            $noiseSlugs = ['establishment', 'point-of-interest', 'tourist-attraction', 'food', 'health', 'lodging', 'locality', 'political', 'store', 'premise', 'school'];

            return DB::table('categories')
                ->whereNotIn('slug', $noiseSlugs)
                ->join('business_category', 'categories.id', '=', 'business_category.category_id')
                ->select('category_id', DB::raw("COUNT(*) as count"))
                ->groupBy('category_id')
                ->get()
                ->mapWithKeys(function ($item) use ($totalBusinesses) {
                    if ($item->count == 0) return [$item->category_id => 0];
                    $weight = round($totalBusinesses / ($item->count * $item->count), 4);
                    return [$item->category_id => $weight];
                });
        });

        // 2. Build scoring SQL
        $currentCategoryIds = $business->categories->pluck('id')->toArray();
        $weightSql = "0";
        foreach ($currentCategoryIds as $id) {
            $weight = $categoryWeights[$id] ?? 0;
            if ($weight > 0) {
                $points = $weight * 1000;
                $weightSql .= " + (CASE WHEN EXISTS (SELECT 1 FROM business_category WHERE business_id = businesses.id AND category_id = $id) THEN $points ELSE 0 END)";
            }
        }

        // 3. Prepare text query
        $textQuery = $business->name . ' ' . strip_tags($business->about_us);

        // 4. Execute algorithmic query
        return Business::where('status', 'active')
            ->where('id', '!=', $business->id)
            ->selectRaw("*, 
                (
                    ($weightSql) + 
                    (MATCH(name) AGAINST(? IN NATURAL LANGUAGE MODE)) * 500 +
                    (MATCH(about_us, description) AGAINST(? IN NATURAL LANGUAGE MODE)) * 50 +
                    (CASE WHEN county_id = ? THEN 10 ELSE 0 END)
                ) as algorithmic_relevance", [
                    $business->name,
                    $textQuery,
                    $business->county_id
                ])
            ->having('algorithmic_relevance', '>', 10)
            ->orderBy('algorithmic_relevance', 'desc')
            ->orderBy('views_count', 'desc')
            ->take(9)
            ->with(['county', 'media'])
            ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)])
            ->get();
    }


    public function getWeather(Request $request, Business $business)
    {
        $apiKey = config('services.openweathermap.api_key'); // Using config helper

        if (!$apiKey) {
            // For production, you might just return an error without logging if it's a config issue
            // Or log once if it happens repeatedly
            return response()->json(['error' => 'Weather service not configured.'], 500);
        }

        $locationQuery = '';
        if ($business->county && $business->county->name) {
            $locationQuery = $business->county->name . ",KE";
        } else {
            return response()->json(['error' => 'No location data (county name) for this business to fetch weather.'], 400);
        }

        $cacheKey = "weather_forecast_5day_" . Str::slug($locationQuery);

        $forecastData = Cache::remember($cacheKey, now()->addDays(7), function () use ($locationQuery, $apiKey) {
            $response = Http::get('https://api.openweathermap.org/data/2.5/forecast', [
                'q' => $locationQuery,
                'appid' => $apiKey,
                'units' => 'metric',
            ]);

            if ($response->successful() && isset($response->json()['cod']) && $response->json()['cod'] == "200") {
                return $response->json();
            }
            // In production, you might log this failure once or have monitoring
            // Log::error("OpenWeatherMap API call failed for {$locationQuery}.", ['response_status' => $response->status(), 'response_body' => $response->body()]);
            return null; // Return null to indicate failure, don't cache bad response
        });

        if ($forecastData && isset($forecastData['list']) && count($forecastData['list']) > 0) {
            $dailySummaries = $this->processDailyForecast($forecastData['list']);
            if (empty($dailySummaries)) {
                 // Log::warning("Processed daily summaries are empty for {$locationQuery}. Original data:", $forecastData);
                 return response()->json(['error' => 'Weather data available but could not be processed into daily forecast.'], 500);
            }
            return response()->json([
                'city' => $forecastData['city']['name'] ?? $business->county->name,
                'daily_forecasts' => array_slice($dailySummaries, 0, 8)
            ]);
        }

        Cache::forget($cacheKey); // Clear a potentially bad cache entry if $forecastData was null
        // Log::error("Failed to retrieve or parse 5-day forecast data for {$locationQuery}.");
        return response()->json(['error' => 'Could not retrieve valid weather forecast for ' . ($business->county->name ?? 'the location') . '.'], 502);
    }
	
	public function toggleLike(Request $request, Business $business)
    {
        $user = $request->user();

        // The toggle() method works perfectly here as well.
        $user->likedBusinesses()->toggle($business->id);

        // Return a JSON response with the new counts.
        return response()->json([
            'success' => true,
            'is_liked' => $user->likedBusinesses()->where('business_id', $business->id)->exists(),
            'likes_count' => $business->likers()->count(),
        ]);
    }

    private function processDailyForecast(array $hourlyList): array
    {
        $dailyData = [];
        foreach ($hourlyList as $forecast) {
            $date = Carbon::parse($forecast['dt_txt'])->format('Y-m-d');
            $dayName = Carbon::parse($forecast['dt_txt'])->format('D, M j');

            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [
                    'day_name' => $dayName, 'temps' => [], 'icons' => [], 'descriptions' => [],
                ];
            }
            if (isset($forecast['main']['temp'])) { // Ensure temp key exists
                $dailyData[$date]['temps'][] = $forecast['main']['temp'];
            }
            if (isset($forecast['weather'][0]['icon'])) { // Ensure icon key exists
                $dailyData[$date]['icons'][] = $forecast['weather'][0]['icon'];
            }
            if (isset($forecast['weather'][0]['main'])) { // Ensure main description key exists
                $dailyData[$date]['descriptions'][] = $forecast['weather'][0]['main'];
            }
        }

        $summaries = [];
        foreach ($dailyData as $date => $data) {
            if (empty($data['temps'])) continue; // Skip if no temperature data for the day

            // Try to pick a midday icon/description, or the most frequent, or fallback
            $icon = $data['icons'][floor(count($data['icons']) / 2)] ?? ($data['icons'][0] ?? '01d');
            $description = $data['descriptions'][floor(count($data['descriptions']) / 2)] ?? ($data['descriptions'][0] ?? 'Clear');

            $minTemp = round(min($data['temps']));
            $maxTemp = round(max($data['temps']));

            $summaries[] = [
                'date_display' => $data['day_name'],
                'temp_max' => $maxTemp,
                'temp_min' => $minTemp,
                'description' => $description,
                'icon' => $icon,
                'icon_url' => "https://openweathermap.org/img/wn/{$icon}@2x.png"
            ];
        }
        return $summaries;
    }
    /**
     * AJAX search for businesses by query.
     * Used for tagging businesses in itineraries.
     */
    public function search(Request $request)
    {
        $query = $request->query('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $businesses = Business::where('status', 'active')
            ->where('name', 'like', '%' . $query . '%')
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->take(20)
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'n' => $b->name,
                's' => $b->slug
            ]);

        return response()->json($businesses);
    }
}