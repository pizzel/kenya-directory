<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\County;
use App\Models\Category;
use App\Models\Image; // Or BusinessImage if you aliased it
use App\Models\Schedule;
use App\Models\Tag;
use App\Models\Facility;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
// Add other necessary model imports: County, Category, Image, Schedule, Tag, Facility, Review

class BusinessController extends Controller // Correct class name
{
    /**
     * Display the specified public business listing.
     */
    public function show(string $businessSlug) // This matches your route
    {
        $business = Business::where('slug', $businessSlug)
                            ->where('status', 'active')
                            ->with([
                                'owner', 'county', 'categories',
                                'schedules' => function ($query) {
                                    $query->orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
                                },
                                'facilities', 'tags',
                                'images' => function ($query) {
                                    $query->orderBy('is_main_gallery_image', 'desc')->orderBy('gallery_order', 'asc')->orderBy('id', 'asc');
                                },
                                'reviews' => function ($query) {
                                    $query->where('is_approved', true)->with('user')->latest();
                                }
                            ])
                            ->firstOrFail();

        // ... (rest of the logic from PublicBusinessController@show to prepare $galleryImages, $formattedSchedules, $similarListings) ...
        $mainImage = $business->images->firstWhere('is_main_gallery_image', true);
        $otherImages = $business->images->where('is_main_gallery_image', false);
        $galleryImages = collect();
        if ($mainImage) {
            $galleryImages->push($mainImage);
        }
        $galleryImages = $galleryImages->merge($otherImages);

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $formattedSchedules = [];
        foreach ($daysOfWeek as $day) {
            $schedule = $business->schedules->firstWhere('day_of_week', $day);
            if ($schedule) {
                $formattedSchedules[$day] = [
                    'open' => $schedule->is_closed_all_day ? 'Closed' : ($schedule->open_time ? date('g:i A', strtotime($schedule->open_time)) : 'N/A'),
                    'close' => $schedule->is_closed_all_day ? '' : ($schedule->close_time ? date('g:i A', strtotime($schedule->close_time)) : 'N/A'),
                    'notes' => $schedule->notes ?? '',
                ];
            } else {
                $formattedSchedules[$day] = ['open' => 'N/A', 'close' => '', 'notes' => ''];
            }
        }

        $similarListings = Business::where('status', 'active')
            ->where('id', '!=', $business->id)
            ->whereHas('categories', function ($query) use ($business) {
                $query->whereIn('categories.id', $business->categories->pluck('id'));
            })
            ->with(['county', 'images' => fn($q) => $q->where('is_main_gallery_image', true)])
            ->inRandomOrder()->take(4)->get();


        return view('listings.show', compact(
            'business',
            'galleryImages',
            'formattedSchedules',
            'daysOfWeek',
            'similarListings'
        ));
    }

    // You might add an index() method here later for a general public listings page
    // public function index(Request $request) { /* ... */ }
}