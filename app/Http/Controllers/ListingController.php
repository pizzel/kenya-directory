<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\County;
use App\Models\Business;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Cache;

class ListingController extends Controller
{
    /**
     * DYNAMIC SIDEBAR ENGINE
     * Calculates counts based on the current filtered results.
     */
    private function getDynamicSidebarStats(Builder $baseQuery)
    {
        // Clone the query so we don't mess up the main results
        $queryForStats = $baseQuery->clone();
        
        // Get all business IDs that match the current search/filter
        $businessIds = $queryForStats->pluck('id')->toArray();

        if (empty($businessIds)) {
            return [
                'categoriesForFilter' => collect(), 
                'facilitiesForFilter' => collect(), 
                'tagsForFilter' => collect()
            ];
        }

        // FILTER OUT NOISE
        $noiseSlugs = ['establishment', 'point-of-interest', 'tourist-attraction', 'food', 'health', 'lodging', 'locality', 'political', 'store', 'premise', 'school', 'place-of-worship'];

        // 1. Popular Categories (Filtered)
        $categoriesForFilter = Category::whereHas('businesses', fn($q) => $q->whereIn('business_id', $businessIds))
            ->whereNotIn('slug', $noiseSlugs) // <--- ADD THIS LINE
            ->withCount(['businesses' => fn($q) => $q->whereIn('business_id', $businessIds)])
            ->orderBy('businesses_count', 'desc')
            ->take(10)
            ->get();

        // 2. Popular Facilities in this View
        $facilitiesForFilter = Facility::whereHas('businesses', fn($q) => $q->whereIn('business_id', $businessIds))
            ->withCount(['businesses' => fn($q) => $q->whereIn('business_id', $businessIds)])
            ->orderBy('businesses_count', 'desc')
            ->take(10)
            ->get();

        // 3. Popular Tags in this View
        $tagsForFilter = Tag::whereHas('businesses', fn($q) => $q->whereIn('business_id', $businessIds))
            ->withCount(['businesses' => fn($q) => $q->whereIn('business_id', $businessIds)])
            ->orderBy('businesses_count', 'desc')
            ->take(10)
            ->get();

        return compact('categoriesForFilter', 'facilitiesForFilter', 'tagsForFilter');
    }

    private function applyCommonFilters(Request $request, Builder $query): Builder
    {
        $searchTerm = $request->input('query') ?? $request->input('keyword');

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('about_us', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', "%{$searchTerm}%"))
                  ->orWhereHas('categories', fn ($c) => $c->where('name', 'like', "%{$searchTerm}%"));
            });
        }
        
        // Rating Filter
        if ($request->filled('rating')) {
            $minRating = (int) $request->input('rating');
            $query->where(function($q) use ($minRating) {
                // Check Google Rating OR Internal Rating
                $q->where('google_rating', '>=', $minRating)
                  ->orWhereHas('reviews', fn($r) => $r->where('is_approved', true)->where('rating', '>=', $minRating));
            });
        }

        // Price Filter
        if ($request->filled('price_max')) {
            $query->where(function ($q) use ($request) {
                $q->where('min_price', '<=', $request->input('price_max'))
                  ->orWhereNull('min_price');
            });
        }

        return $query;
    }

    private function applySorting(Builder $query, string $sortOrder): Builder
    {
        // Featured businesses ALWAYS stay at the very top
        $query->orderBy('is_featured', 'desc'); 

        switch ($sortOrder) {
            case 'rating_desc':
                // Sort by the stored Google Rating (Official data)
                $query->orderBy('google_rating', 'desc');
                break;
            case 'views_desc':
                $query->orderBy('views_count', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            
            // THE SMART DEFAULT
            default: 
                // Verified -> Popularity -> Name
                $query->orderBy('is_verified', 'desc')
                      ->orderBy('views_count', 'desc')
                      ->orderBy('name', 'asc');
                break;
        }
        
        return $query->orderBy('id', 'desc');
    }

     public function search(Request $request)
    {
        $query = Business::query()->where('status', 'active')->withAllPublicRelations();

        if ($request->filled('county_slug')) {
            $query->whereHas('county', fn ($q) => $q->where('slug', $request->input('county_slug')));
        } elseif ($request->filled('county_search_input')) {
            $query->whereHas('county', fn ($q) => $q->where('name', 'like', "%{$request->input('county_search_input')}%"));
        }

        $query = $this->applyCommonFilters($request, $query);
        
        // Get Stats based on this filtered query
        $stats = $this->getDynamicSidebarStats($query);
        
        $query = $this->applySorting($query, $request->input('sort', 'default'));
        $businesses = $query->paginate(45)->appends($request->query());

        $currentCounty = (object)['name' => 'Search Results', 'slug' => 'search']; 

        return view('listings.county', array_merge(compact('currentCounty', 'businesses', 'request'), $stats));
    }

    public function countyListings(Request $request, string $countySlug)
    {
        $currentCounty = County::where('slug', $countySlug)->firstOrFail();

        $query = Business::where('county_id', $currentCounty->id)
                         ->where('status', 'active')
                         ->withAllPublicRelations();

        $query = $this->applyCommonFilters($request, $query);
        $stats = $this->getDynamicSidebarStats($query); // Get stats for THIS county
        
        $query = $this->applySorting($query, $request->input('sort', 'default'));
        $businesses = $query->paginate(45)->appends($request->query());

        return view('listings.county', array_merge(compact('currentCounty', 'businesses', 'request'), $stats));
    }

    public function categoryListings(Request $request, string $categorySlug)
    {
        $currentCategory = Category::where('slug', $categorySlug)->firstOrFail();

        $query = Business::whereHas('categories', fn($q) => $q->where('categories.id', $currentCategory->id))
                         ->where('status', 'active')
                         ->withAllPublicRelations();

        if ($request->filled('county_filter_slug')) {
            $query->whereHas('county', fn($q) => $q->where('slug', $request->input('county_filter_slug')));
        }

        $query = $this->applyCommonFilters($request, $query);
        $stats = $this->getDynamicSidebarStats($query);

        $query = $this->applySorting($query, $request->input('sort', 'default'));
        $businesses = $query->paginate(45)->appends($request->query());

        return view('listings.category', array_merge(compact('currentCategory', 'businesses', 'request'), $stats));
    }
    
    public function facilityListings(Request $request, Facility $facility) 
    {
        $query = Business::whereHas('facilities', fn($q) => $q->where('facilities.id', $facility->id))
                         ->where('status', 'active')
                         ->withAllPublicRelations();

        if ($request->filled('county_filter_slug')) {
            $query->whereHas('county', fn($q) => $q->where('slug', $request->input('county_filter_slug')));
        }

        $query = $this->applyCommonFilters($request, $query);
        $stats = $this->getDynamicSidebarStats($query);
        
        $businesses = $this->applySorting($query, $request->input('sort', 'default'))
                           ->paginate(45)->appends($request->query());

        return view('listings.facility', array_merge(compact('businesses', 'request'), $stats, ['currentFacility' => $facility]));
    }

    public function tagListings(Request $request, Tag $tag) 
    {
        $query = Business::whereHas('tags', fn($q) => $q->where('tags.id', $tag->id))
                         ->where('status', 'active')
                         ->withAllPublicRelations();

        if ($request->filled('county_filter_slug')) {
            $query->whereHas('county', fn($q) => $q->where('slug', $request->input('county_filter_slug')));
        }

        $query = $this->applyCommonFilters($request, $query);
        $stats = $this->getDynamicSidebarStats($query);

        $businesses = $this->applySorting($query, $request->input('sort', 'default'))
                           ->paginate(45)->appends($request->query());

        return view('listings.tag', array_merge(compact('businesses', 'request'), $stats, ['currentTag' => $tag]));
    }
    
    public function allListings(Request $request)
    {
        $query = Business::query()->where('status', 'active')->withAllPublicRelations();
        $query = $this->applyCommonFilters($request, $query);
        $stats = $this->getDynamicSidebarStats($query);
        
        $businesses = $this->applySorting($query, $request->input('sort', 'default'))
                           ->paginate(45)->appends($request->query());
                           
        $currentCounty = (object)['name' => 'All Listings', 'slug' => null];

        return view('listings.county', array_merge(compact('currentCounty', 'businesses', 'request'), $stats));
    }
    
    
    public function getNearbyListings(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:1|max:50', // Max radius in km
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius; // Kilometers

        $haversine = "(
            6371 * acos(
                cos(radians(?))
                * cos(radians(latitude))
                * cos(radians(longitude) - radians(?))
                + sin(radians(?))
                * sin(radians(latitude))
            )
        )";

            $businesses = Business::where('status', 'active')
            ->select('*')
            ->selectRaw("{$haversine} AS distance", [$latitude, $longitude, $latitude])
            ->whereRaw("{$haversine} <= ?", [$latitude, $longitude, $latitude, $radius])
            ->orderBy('is_featured', 'desc')
            ->orderBy('distance', 'asc')
            ->with('county') // We still need the county name
            ->take(52)
            ->get();

            $businesses->each(function ($business) {
                    $business->main_image_url = $business->getImageUrl('card');
                });

            return response()->json(['businesses' => $businesses]);
        }



    
}