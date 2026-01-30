<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\File;

class AuditMediaCommand extends Command
{
    protected $signature = 'media:audit';
    protected $description = 'Deep scan of media and all generated conversions to identify 404 sources.';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        
        $this->info("--- STARTING SYNCHRONIZED HERO ROTATION ---");
        Log::info("--- STARTING SYNCHRONIZED HERO ROTATION ---");

        $fileManipulator = app(FileManipulator::class);

        // 1. WIPE OLD DATA
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\HeroSliderHistory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Log::info("✔ Hero history table truncated for fresh start.");

        DB::transaction(function () use ($count, $fileManipulator) {
            
            // 2. Select candidates
            $randomBusinesses = Business::where('status', 'active')
                ->where('is_verified', true)
                ->whereHas('media', function ($q) {
                    $q->where('collection_name', 'images');
                })
                ->inRandomOrder()
                ->take($count)
                ->get();

            foreach ($randomBusinesses as $business) {
                // 3. TARGET THE NEWEST IMAGE ONLY (Matches getImageUrl logic)
                $targetMedia = $business->media()
                    ->where('collection_name', 'images')
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$targetMedia) {
                    Log::warning("   > SKIP: Business [{$business->id}] has no images.");
                    continue;
                }

                Log::info("Processing Business [{$business->id}] using Media ID [{$targetMedia->id}]");

                try {
                    // 4. GENERATE WEB-P CONVERSIONS
                    $fileManipulator->createDerivedFiles($targetMedia, ['hero', 'hero-mobile']);
                    
                    // Verify the file was actually written
                    clearstatcache();
                    $checkPath = $targetMedia->getPath('hero');
                    
                    if (file_exists($checkPath)) {
                        Log::info("   > ✔ SUCCESS: WebP created at: {$checkPath}");
                        Log::info("   > ✔ URL: " . $targetMedia->getUrl('hero'));

                        // 5. CREATE HISTORY RECORD
                        \App\Models\HeroSliderHistory::create([
                            'business_id' => $business->id,
                            'activated_at' => now(),
                            'set_to_expire_at' => now()->addDays(14), 
                            'amount_paid' => 0,
                            'status' => 'active'
                        ]);
                        $this->info("✔ Added: {$business->name} (Media: {$targetMedia->id})");
                    } else {
                        Log::error("   > ❌ FAILURE: File was not written to disk: {$checkPath}");
                    }

                } catch (\Exception $e) {
                    Log::error("   > ✘ EXCEPTION: " . $e->getMessage());
                }
            }
        });

        // 6. CLEAR CACHE
        Cache::forget('home_hero_slider_final_v3');
        
        Log::info("--- ROTATION COMPLETE ---");
        $this->info("\nDone! Check storage/logs/laravel.log for IDs and Paths.");
        
        return 0;
    }
}