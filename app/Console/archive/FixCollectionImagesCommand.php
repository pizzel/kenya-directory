<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\DiscoveryCollection;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\Conversions\FileManipulator;

class FixCollectionImagesCommand extends Command
{
    protected $signature = 'collections:fix-images';
    protected $description = 'Ensures all collections have valid hero images generated';

    public function handle()
    {
        $this->info("Checking collections for missing hero images...");

        $collections = DiscoveryCollection::where('is_active', true)->get();
        $manipulator = app(FileManipulator::class);

        foreach ($collections as $collection) {
            // Logic matches DiscoveryCollection::getCoverImageUrl logic priority
            $business = $collection->relationLoaded('coverBusiness') 
                ? $collection->coverBusiness->first() 
                : ($collection->relationLoaded('businesses') ? $collection->businesses->first() : $collection->businesses()->first());

            if (!$business) continue;

            $media = $business->getFirstMedia('images');
            if (!$media) continue;

            // Check if hero exists on disk
            $heroPath = $media->getPath('hero');
            
            if (!file_exists($heroPath)) {
                $this->info("Generating hero for Collection: {$collection->title} (Business: {$business->name})");
                try {
                    $manipulator->createDerivedFiles($media, ['hero', 'hero-mobile']);
                } catch (\Exception $e) {
                    $this->error("Failed to generate for {$business->name}: " . $e->getMessage());
                }
            }
        }

        $this->info("Done!");
    }
}
