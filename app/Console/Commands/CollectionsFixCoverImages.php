<?php

namespace App\Console\Commands;

use App\Models\DiscoveryCollection;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
// <<< IMPORT THE FILE MANIPULATOR >>>
use Spatie\MediaLibrary\Conversions\FileManipulator;

class CollectionsFixCoverImages extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'collections:fix-covers';

    /**
     * The console command description.
     */
    protected $description = 'Regenerates and updates the cover images for all existing discovery collections.';

    /**
     * Execute the console command.
     */

    public function handle(): int
            {
                $this->info('Starting to fix and regenerate cover images for Discovery Collections...');
                $collections = DiscoveryCollection::with('businesses')->get(); // Eager load businesses
                if ($collections->isEmpty()) { /* ... */ }

                $progressBar = $this->output->createProgressBar($collections->count());
                $fileManipulator = app(FileManipulator::class);
                $fixedCount = 0;

                foreach ($collections as $collection) {
                    // Find the first business in this collection that actually has a main image.
                    $businessForCover = $collection->businesses->first(fn ($business) => $business->hasMedia('images'));
                    
                    if ($businessForCover) {
                        $coverMedia = $businessForCover->getFirstMedia('images');
                        if ($coverMedia) {
                            try {
                                // 1. Regenerate the 'card' conversion to ensure it's high quality.
                                $fileManipulator->createDerivedFiles($coverMedia, ['card']);
                                
                                // 2. Get the URL of the newly generated image.
                                $newCoverUrl = $coverMedia->getUrl('card');

                                // 3. Update the database record.
                                if ($collection->cover_image_url !== $newCoverUrl) {
                                    $collection->cover_image_url = $newCoverUrl;
                                    $collection->save();
                                    $fixedCount++;
                                }
                            } catch (\Exception $e) {
                                $this->error("\nCould not process cover for '{$collection->title}': {$e->getMessage()}");
                            }
                        }
                    }
                    $progressBar->advance();
                }

                $progressBar->finish();
                if ($fixedCount > 0) {
                    $this->info("\nClearing application cache...");
                    $this->call('cache:clear');
                }
                $this->info("\nProcess complete. Updated cover images for {$fixedCount} collections.");
                return 0;
            }
}