<?php

namespace App\Http\Controllers;

use App\Models\DiscoveryCollection;
use Illuminate\Http\Request;
use App\Models\County;
use App\Models\Category;
use App\Models\Facility;
use App\Services\SemanticSEOService;

class DiscoveryCollectionController extends Controller
{
    protected $seoService;

    public function __construct(SemanticSEOService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Display the businesses within a specific discovery collection.
     */
    public function index()
    {
        // 1. FOCUS ON TOURISM: Exclude "Local Service" collections from the main directory
        // This prevents "Gyms" and "Car Washes" from diluting the "Travel Authority" signal.
        $excludedKeywords = ['gym', 'dentist', 'laundry', 'car-wash', 'school', 'hospital', 'clinic', 'mechanic', 'plumber', 'electrician'];
        
        $collections = DiscoveryCollection::where('is_active', true)
            ->where(function($query) use ($excludedKeywords) {
                foreach ($excludedKeywords as $keyword) {
                    $query->where('slug', 'not like', '%' . $keyword . '%');
                }
            })
            ->withCount('businesses')
            ->latest() // <--- NEWEST FIRST
            ->paginate(12);

        return view('collections.index', compact('collections'));
    }

     public function show(Request $request, DiscoveryCollection $collection)
    {
        // 2. FIXED SORTING: Priority to Featured, then Highest Rated
        $businesses = $collection->businesses()
            ->where('status', 'active')
            ->with(['county', 'media', 'categories'])
            ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)])
            ->orderBy('is_featured', 'desc')
            ->orderBy('google_rating', 'desc')
            ->get()
            ->map(function($b) {
                // Attach a semantic verdict to each business
                $b->verdict = $this->seoService->generateContextSummary($b);
                return $b;
            });


        // 3. Fetch other active collections for the "More Guides" sidebar widget
        $otherCollections = DiscoveryCollection::where('is_active', true)
            ->where('id', '!=', $collection->id)
            ->with(['businesses' => function($q) {
                $q->has('media')->with('media')->limit(1);
            }])
            ->take(3)
            ->get();

        // 4. Generate Semantic SEO Data
        $collectionSchema = $this->seoService->generateCollectionSchema($collection, $businesses);
        
        // Generate a context summary if the description is thin (less than 100 chars)
        $contextSummary = strlen($collection->description) < 100 
            ? "Exploration guide for " . $collection->title . " in Kenya, curated for authentic experiences."
            : Str::limit(strip_tags($collection->description), 160);

        return view('collections.show', compact('collection', 'businesses', 'otherCollections', 'collectionSchema', 'contextSummary'));
    }
}