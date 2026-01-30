<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\County;
use App\Models\Business;

class RotateCountyImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'counties:rotate-images';

    /**
     * The console command description.
     */
    protected $description = 'Force rotates random cover images for popular counties and refreshes the cache.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting county image rotation...');

        // 1. Clear the existing cache
        Cache::forget('home_popular_counties');

        // 2. Regenerate the data (Same logic as HomeController)
        $cacheDuration = 10800; // 3 hours

        $freshCounties = County::withCount(['businesses' => fn($q) => $q->where('status', 'active')])
            ->having('businesses_count', '>', 0)
            ->orderBy('businesses_count', 'desc')
            ->take(47)
            ->get();
        
        $updatedCount = 0;

        foreach ($freshCounties as $county) {
            // Find a COMPLETELY RANDOM active business in this county
            $randomBusiness = Business::where('county_id', $county->id)
                ->where('status', 'active')
                ->has('media')
                ->inRandomOrder()
                ->first();
            
            if ($randomBusiness) {
                $county->display_image_url = $randomBusiness->getImageUrl('card');
                $updatedCount++;
            } else {
                $county->display_image_url = asset('images/placeholder-county.jpg');
            }
        }

        // 3. Store the new random set back into Cache
        Cache::put('home_popular_counties', $freshCounties, $cacheDuration);

        $this->info("Successfully rotated images for {$updatedCount} counties.");
        return 0;
    }
}