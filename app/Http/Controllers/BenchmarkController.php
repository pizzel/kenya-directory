<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\HeroSlider;

class BenchmarkController extends Controller
{
    /**
     * Benchmark 1: Core (HTML + CSS + First Hero Image only)
     * No JavaScript, No Swiper, No Alpine, No external fonts if possible.
     */
    public function index()
    {
        // CACHE EVERYTHING for 10 minutes to eliminate DB from the equation completely
        // If TTFB is still high, it's Purely PHP/Server overhead.
        $heroSliderBusinesses = \Illuminate\Support\Facades\Cache::remember('benchmark_sim', 600, function() {
            
            // Replicate HomeController Logic to get the EXACT same type of image
            // 1. Try to get Paid Hero Slider Business first
            $business = \App\Models\Business::eligibleForHeroSlider()
                ->with(['media', 'county'])
                ->inRandomOrder()
                ->first();

            // 2. If no paid ones, get a verified active one with media (Fallback)
            if (!$business) {
                $business = \App\Models\Business::where('status', 'active')
                    ->where('is_verified', true)
                    ->has('media')
                    ->with(['media', 'county'])
                    ->inRandomOrder()
                    ->first();
            }

            // If still nothing
            if (!$business) {
                return collect([]);
            }

            // Map it
            $business->hero_image_url = $business->getImageUrl('hero');
            $business->hero_image_url_mobile = $business->getImageUrl('hero-mobile');
            
            return collect([$business]);
        });

        return view('benchmark.index', compact('heroSliderBusinesses'));
    }
}
