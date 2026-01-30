<?php

namespace App\Http\Controllers\BusinessOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// No need to import Business model here if using Auth::user()->businesses()
// unless you want to type-hint or use Business model statically.

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Eager load businesses with their county, categories, AND their main image
        $businesses = $user->businesses()
                            ->with([
                                'county',
                                'categories', // If you display category info here
                                'images' => function ($query) {
                                    // Only load the image marked as main, and order by gallery_order just in case
                                    $query->where('is_main_gallery_image', true)
                                          ->orderBy('gallery_order', 'asc')
                                          ->orderBy('id', 'asc')
                                          ->limit(1); // Ensure only one is fetched if multiple accidentally marked
                                }
                            ])
                            ->latest() // Order businesses by newest first
                            ->paginate(10); // Or your preferred pagination size

        return view('business-owner.dashboard', compact('businesses'));
    }
}