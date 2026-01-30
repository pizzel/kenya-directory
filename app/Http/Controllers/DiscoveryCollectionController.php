<?php

namespace App\Http\Controllers;

use App\Models\DiscoveryCollection;
use Illuminate\Http\Request;
use App\Models\County;
use App\Models\Category;
use App\Models\Facility;

class DiscoveryCollectionController extends Controller
{
    /**
     * Display the businesses within a specific discovery collection.
     */
    public function index()
    {
        $collections = DiscoveryCollection::where('is_active', true)
            ->withCount('businesses')
            ->latest() // <--- NEWEST FIRST
            ->paginate(12);

        return view('collections.index', compact('collections'));
    }

     public function show(Request $request, DiscoveryCollection $collection)
    {
        // 1. Load Businesses with everything needed for the "Magazine Guide" look
        $businesses = $collection->businesses()
            ->where('status', 'active')
            ->with(['county', 'media', 'categories'])
            // Pre-fetch internal review counts for the UI
            ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)])
            // 2. FIXED SORTING: Priority to Featured, then Highest Rated
            ->orderBy('is_featured', 'desc')
            ->orderBy('google_rating', 'desc')
            ->get();

        // 3. Fetch other active collections for the "More Guides" sidebar widget
        $otherCollections = DiscoveryCollection::where('is_active', true)
            ->where('id', '!=', $collection->id)
            ->with(['businesses' => function($q) {
                $q->has('media')->with('media')->limit(1);
            }])
            ->take(3)
            ->get();

        return view('collections.show', compact('collection', 'businesses', 'otherCollections'));
    }
}