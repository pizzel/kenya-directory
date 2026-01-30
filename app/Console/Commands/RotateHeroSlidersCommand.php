<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\HeroSliderHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\Conversions\FileManipulator;

class RotateHeroSlidersCommand extends Command
{
    protected $signature = 'hero:rotate {--count=10}';
    protected $description = 'Wipes old slider history, converts new images to WebP, and sets 10 active sliders.';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        
        $this->info("--- STARTING OPTIMIZED HERO ROTATION ---");
        Log::info("--- STARTING OPTIMIZED HERO ROTATION ---");

        $fileManipulator = app(FileManipulator::class);

        // 1. Get Candidates First (Outside transaction)
        $randomBusinesses = Business::where('status', 'active')
            ->where('is_verified', true)
            ->whereHas('media', function ($q) {
                $q->where('collection_name', 'images');
            })
            ->inRandomOrder()
            ->take($count)
            ->get();

        if ($randomBusinesses->isEmpty()) {
            $this->error("No eligible businesses found!");
            return 1;
        }

        $this->info("✔ Found {$randomBusinesses->count()} candidate(s). Checking images...");

        // 2. Pre-generate Images (Slow part, outside transaction)
        foreach ($randomBusinesses as $business) {
            $coverImage = $business->getFirstMedia('images');
            if (!$coverImage) continue;

            try {
                // Check if BOTH conversions exist on disk
                $heroExists = $coverImage->hasGeneratedConversion('hero') && file_exists($coverImage->getPath('hero'));
                $heroMobileExists = $coverImage->hasGeneratedConversion('hero-mobile') && file_exists($coverImage->getPath('hero-mobile'));

                if (!$heroExists || !$heroMobileExists) {
                    $this->info("   > Optimizing images for: {$business->name} (Immediate/Sync)...");
                    
                    // FORCE Synchronous generation specifically for the hero rotation
                    // to bypass the 18,000+ items currently in the background queue.
                    config(['media-library.queue_conversions_by_default' => false]);
                    
                    $fileManipulator->createDerivedFiles($coverImage, ['hero', 'hero-mobile']);
                    
                    Log::info("   > ✔ Optimized WebP for: [{$business->id}] {$business->name} (Bypassed Queue)");
                } else {
                    $this->line("   > Images already optimized for: {$business->name}");
                }
            } catch (\Exception $e) {
                Log::error("   > ✘ EXCEPTION during image processing for {$business->name}: " . $e->getMessage());
                $this->warn("   > Failed to process images for {$business->name}. Skipping.");
                // We keep going, the loop will skip this one if needed
            }
        }

        // 3. SWAP DATA (Now inside transaction for atomicity and speed)
        DB::transaction(function () use ($randomBusinesses) {
            // FORCE DELETE all records (including soft-deleted ones) to truly wipe the slate clean
            // This ensures only the new rotation is visible
            HeroSliderHistory::withTrashed()->forceDelete();

            foreach ($randomBusinesses as $business) {
                HeroSliderHistory::create([
                    'business_id' => $business->id,
                    'activated_at' => now(),
                    'set_to_expire_at' => now()->addDays(14), // Increased to 14 days so they don't disappear after a day
                    'amount_paid' => 0,
                    // Removed 'status' => 'active' - the eligibleForHeroSlider scope only checks dates
                ]);

                // Restore original logging for your reference
                $coverImage = $business->getFirstMedia('images');
                Log::info("Processing: [{$business->id}] {$business->name}");
                Log::info("   > ✔ Public URL: " . ($coverImage ? $coverImage->getUrl('hero') : 'No image found'));
            }
        });

        // 4. CLEAR HOMEPAGE CACHE
        Cache::forget('home_hero_slider_final_v3');
        
        $this->info("✔ History table updated and cache cleared.");
        Log::info("--- ROTATION COMPLETE ---");
        $this->info("\nDone! Hero rotation finished successfully.");
        
        return 0;
    }
}